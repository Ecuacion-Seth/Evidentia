import os
from pathlib import Path
from typing import Dict, Any, List, Optional
from fastapi import FastAPI, HTTPException, UploadFile, File, Form
from pydantic import BaseModel

from parser import extract_text_from_pdf, extract_text_from_docx, extract_text_from_raw
from citation_extractor import CitationExtractor
from source_retriever import SourceRetriever
from claim_extractor import ClaimExtractor
from nli_matcher import NLIMatcher
from ai_indicator import AIIndicator

app = FastAPI(
    title="Evidentia Academic Evidence Integrity Engine",
    description="NLP microservice for Toulmin claim extraction, open-access source verification, and RAG claim-evidence matching.",
    version="1.0.0"
)

# Initialize engines
citation_extractor = CitationExtractor()
source_retriever = SourceRetriever()
claim_extractor = ClaimExtractor()
nli_matcher = NLIMatcher()
ai_indicator = AIIndicator()

class RawTextRequest(BaseModel):
    text: str
    title: Optional[str] = "Untitled Paper"

class FileAssessRequest(BaseModel):
    file_path: str
    title: Optional[str] = "Untitled Paper"

@app.get("/health")
def health():
    return {
        "status": "healthy",
        "service": "evidentia-engine",
        "ollama_host": os.getenv("OLLAMA_HOST", "http://localhost:11434")
    }

@app.post("/api/v1/assess-file")
def assess_file(request: FileAssessRequest):
    """
    Runs the full Evidentia pipeline on a file stored on disk.
    """
    file_path = Path(request.file_path)
    if not file_path.exists():
        raise HTTPException(status_code=404, detail=f"File not found: {request.file_path}")

    suffix = file_path.suffix.lower()
    if suffix == ".pdf":
        doc = extract_text_from_pdf(str(file_path))
    elif suffix in [".docx", ".doc"]:
        doc = extract_text_from_docx(str(file_path))
    else:
        text = file_path.read_text(encoding="utf-8", errors="ignore")
        doc = extract_text_from_raw(text)

    return _process_evidentia_pipeline(doc, request.title or file_path.stem)

@app.post("/api/v1/assess-text")
def assess_text(request: RawTextRequest):
    """
    Runs the full Evidentia pipeline on raw paper text.
    """
    doc = extract_text_from_raw(request.text)
    return _process_evidentia_pipeline(doc, request.title)

def _process_evidentia_pipeline(doc: Dict[str, Any], title: str) -> Dict[str, Any]:
    body_text = doc["body_text"]
    references_text = doc["references_text"]
    full_text = doc["full_text"]

    # 1. Parse Reference list (APA 7th)
    references = citation_extractor.parse_references(references_text)

    # 2. Extract In-Text Citations from Body
    in_text_citations = citation_extractor.extract_in_text_citations(body_text)

    # 3. Extract Toulmin Claims anchored to citations
    extracted_claims = claim_extractor.extract_claims(body_text, in_text_citations, references)

    # 4. Source Verification & Multi-tier Open Access Retrieval (Parallel Execution)
    retrieved_sources = {}
    retrieved_count = 0
    total_sources = len(references)

    if references:
        from concurrent.futures import ThreadPoolExecutor
        with ThreadPoolExecutor(max_workers=min(8, len(references))) as executor:
            future_to_ref = {executor.submit(source_retriever.verify_and_retrieve, ref): ref for ref in references}
            for future in future_to_ref:
                ref = future_to_ref[future]
                key = ref["citation_key"]
                try:
                    retrieval_res = future.result()
                except Exception as e:
                    retrieval_res = {
                        "retrieval_status": "ERROR",
                        "reason": f"Retrieval failed: {str(e)}",
                        "full_text": None,
                        "doi": ref.get("doi")
                    }
                retrieved_sources[key] = {**ref, **retrieval_res}
                if retrieval_res.get("retrieval_status") == "RETRIEVED":
                    retrieved_count += 1

    practical_coverage = round((retrieved_count / total_sources * 100), 1) if total_sources > 0 else 0.0

    # 5. Claim-Evidence Matching (Retrieval-Augmented NLI)
    verified_claims = []
    verdict_counts = {"SUPPORTED": 0, "PARTIALLY_SUPPORTED": 0, "UNSUPPORTED": 0, "UNVERIFIABLE": 0}

    for claim in extracted_claims:
        cit_key = claim.get("citation_key")
        matched_source = retrieved_sources.get(cit_key) or claim.get("matched_reference") or {}

        # Evaluate Toulmin claim against retrieved ground
        match_result = nli_matcher.match_claim_to_source(claim["claim_text"], matched_source)

        verdict = match_result["verdict"]
        verdict_counts[verdict] = verdict_counts.get(verdict, 0) + 1

        verified_claims.append({
            "claim_text": claim["claim_text"],
            "in_text_citation": claim["in_text_citation"],
            "citation_key": cit_key,
            "paragraph_index": claim.get("paragraph_index", 0),
            "verdict": verdict,
            "evidence_quote": match_result["evidence_quote"],
            "reasoning_warrant": match_result["reasoning_warrant"],
            "confidence_score": match_result["confidence_score"],
            "source_doi": match_result["source_doi"] or matched_source.get("doi"),
            "source_open_access_url": matched_source.get("open_access_url"),
            "matched_reference": {
                "citation_key": matched_source.get("citation_key"),
                "authors": matched_source.get("authors"),
                "year": matched_source.get("year"),
                "title": matched_source.get("title"),
                "doi": matched_source.get("doi"),
                "open_access_url": matched_source.get("open_access_url"),
                "retrieval_status": matched_source.get("retrieval_status", "NOT_FOUND"),
                "raw_text": matched_source.get("raw_text")
            } if matched_source else None
        })

    # 6. Calculate Supplementary AI Indicator
    ai_metrics = ai_indicator.calculate_indicator(body_text)

    return {
        "title": title,
        "word_count": len(full_text.split()),
        "paragraph_count": len(doc.get("paragraphs", [])),
        "body_text": body_text,
        "references_text": references_text,
        "practical_coverage": practical_coverage,
        "total_references": total_sources,
        "retrieved_references_count": retrieved_count,
        "verdict_summary": verdict_counts,
        "ai_indicator": ai_metrics,
        "references": list(retrieved_sources.values()),
        "claims": verified_claims
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
