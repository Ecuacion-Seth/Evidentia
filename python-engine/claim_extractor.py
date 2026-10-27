import os
import re
import json
import requests
from typing import List, Dict, Any, Optional

class ClaimExtractor:
    """
    Extracts Toulmin claims and maps them to in-text citations.
    Integrates with local Ollama (Llama 3.2 / 3.1) with rule-based sentence segmentation fallback.
    """

    def __init__(self, ollama_url: Optional[str] = None, model: str = "llama3.2:3b"):
        self.ollama_url = ollama_url or os.getenv("OLLAMA_HOST", "http://localhost:11434")
        self.model = os.getenv("OLLAMA_MODEL", model)

    def extract_claims(self, body_text: str, in_text_citations: List[Dict[str, Any]], references: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Extracts claim-citation pairs. Attempts local Ollama LLM extraction, falling back
        to structural sentence segmentation.
        """
        if not in_text_citations:
            return []

        # Try Ollama structured extraction first if service is reachable
        ollama_claims = self._extract_via_ollama(body_text, in_text_citations)
        if ollama_claims:
            return self._enrich_and_link(ollama_claims, references)

        # Fallback to precise sentence extraction around each citation marker
        return self._extract_via_heuristics(body_text, in_text_citations, references)

    def _extract_via_ollama(self, body_text: str, in_text_citations: List[Dict[str, Any]]) -> Optional[List[Dict[str, Any]]]:
        """
        Calls local Ollama instance with structured JSON prompt.
        """
        try:
            # Sample first ~3000 words if body is very long
            truncated_text = body_text[:12000]

            prompt = f"""You are an academic argumentation parser using the Toulmin model.
Extract all factual assertions or argumentative claims that cite external sources from the text below.
Format your response as a valid JSON object with a single "claims" array.
Each element in "claims" must have:
- "claim_text": The complete, self-contained claim or assertion being made in the paper.
- "in_text_citation": The exact citation text (e.g. "(Smith, 2020)" or "Vaswani et al. (2017)").
- "citation_key": Author and year (e.g. "Smith, 2020").

Text to analyze:
\"\"\"
{truncated_text}
\"\"\"

Output JSON:"""

            resp = requests.post(
                f"{self.ollama_url}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "format": "json",
                    "stream": False,
                    "options": {"temperature": 0.1}
                },
                timeout=8
            )

            if resp.status_code == 200:
                result = resp.json()
                raw_response = result.get("response", "{}")
                data = json.loads(raw_response)
                claims = data.get("claims", [])
                if isinstance(claims, list) and len(claims) > 0:
                    return claims
        except Exception:
            pass

        return None

    def _extract_via_heuristics(self, body_text: str, in_text_citations: List[Dict[str, Any]], references: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Rule-based Toulmin claim extraction by splitting paragraphs into sentences and
        isolating the exact sentence and clause that hosts each citation.
        """
        claims = []

        # Sentence tokenizer regex that handles abbreviations (et al., etc.)
        sentence_end = re.compile(r'(?<!\bet\s al)(?<!\bi\.e)(?<!\be\.g)(?<!\bDr)(?<!\bMr)(?<!\bMs)(?<=[.?!])\s+(?=[A-Z])')

        # Split text into paragraphs first to record paragraph index
        paragraphs = [p.strip() for p in re.split(r'\n\s*\n', body_text) if p.strip()]

        for p_idx, para in enumerate(paragraphs):
            sentences = sentence_end.split(para)
            for sent in sentences:
                clean_sent = sent.strip()
                # Check which citations exist in this sentence
                for cit in in_text_citations:
                    marker = cit["marker"]
                    if marker in clean_sent:
                        matched_ref = self._find_matching_reference(cit, references)
                        claims.append({
                            "claim_text": clean_sent,
                            "in_text_citation": marker,
                            "citation_key": cit.get("normalized_key") or f"{cit['author']}, {cit['year']}",
                            "paragraph_index": p_idx,
                            "matched_reference": matched_ref
                        })

        # Deduplicate identical (claim_text, in_text_citation)
        seen = set()
        unique_claims = []
        for c in claims:
            key = (c["claim_text"], c["in_text_citation"])
            if key not in seen:
                seen.add(key)
                unique_claims.append(c)

        return unique_claims

    def _enrich_and_link(self, ollama_claims: List[Dict[str, Any]], references: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Links extracted LLM claims back to bibliographic references.
        """
        enriched = []
        for c in ollama_claims:
            cit_key = c.get("citation_key", "")
            in_text = c.get("in_text_citation", "")
            claim_text = c.get("claim_text", "")

            matched_ref = None
            for ref in references:
                if ref["citation_key"].lower() in cit_key.lower() or ref["citation_key"].lower() in in_text.lower():
                    matched_ref = ref
                    break

            enriched.append({
                "claim_text": claim_text,
                "in_text_citation": in_text,
                "citation_key": cit_key,
                "paragraph_index": 0,
                "matched_reference": matched_ref
            })

        return enriched

    def _find_matching_reference(self, citation: Dict[str, Any], references: List[Dict[str, Any]]) -> Optional[Dict[str, Any]]:
        """
        Locates the reference matching the citation.
        """
        norm_key = citation.get("normalized_key", "").lower()
        author = citation.get("author", "").lower()
        year = citation.get("year", "")

        for ref in references:
            if ref.get("citation_key", "").lower() == norm_key:
                return ref

        for ref in references:
            ref_auth = ref.get("authors", "").lower()
            if author.split()[0] in ref_auth and (year in ref.get("year", "")):
                return ref

        return None
