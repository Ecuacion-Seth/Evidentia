import re
from pathlib import Path
from typing import Dict, Any, List

def extract_text_from_pdf(pdf_path: str) -> Dict[str, Any]:
    """
    Extracts text and page-by-page content from a PDF file using pdfplumber.
    """
    import pdfplumber

    pages_text = []
    full_text = []

    with pdfplumber.open(pdf_path) as pdf:
        for idx, page in enumerate(pdf.pages):
            text = page.extract_text() or ""
            pages_text.append({"page_number": idx + 1, "text": text})
            full_text.append(text)

    combined_text = "\n\n".join(full_text)
    return segment_document(combined_text, pages_text)

def extract_text_from_docx(docx_path: str) -> Dict[str, Any]:
    """
    Extracts paragraphs and structure from a DOCX file using python-docx.
    """
    import docx

    doc = docx.Document(docx_path)
    paragraphs = [p.text for p in doc.paragraphs if p.text.strip()]
    combined_text = "\n\n".join(paragraphs)
    return segment_document(combined_text, [{"page_number": 1, "text": combined_text}])

def extract_text_from_raw(raw_text: str) -> Dict[str, Any]:
    """
    Handles raw plain text input.
    """
    return segment_document(raw_text, [{"page_number": 1, "text": raw_text}])

def segment_document(full_text: str, pages_metadata: List[Dict[str, Any]] = None) -> Dict[str, Any]:
    """
    Separates the document into the main body text and the References section.
    Standard academic papers place references at the end under headings like 'References' or 'Bibliography'.
    """
    # Regex to locate the References section heading (case-insensitive, standalone line)
    ref_patterns = [
        r'\n\s*(?:#{1,3}\s*)?(?:REFERENCES|References|BIBLIOGRAPHY|Bibliography|Works Cited)\s*\n',
        r'\n\s*(?:[0-9]+\.?\s*)?(?:REFERENCES|References|BIBLIOGRAPHY|Bibliography)\s*\n'
    ]

    body_text = full_text
    references_text = ""
    split_index = -1

    for pat in ref_patterns:
        match = re.search(pat, full_text)
        if match:
            split_index = match.start()
            # The body is everything before the match, references everything after
            body_text = full_text[:split_index].strip()
            references_text = full_text[match.end():].strip()
            break

    # Paragraph breakdown for coordinate/index tracking
    paragraphs = [p.strip() for p in re.split(r'\n\s*\n', body_text) if p.strip()]

    return {
        "full_text": full_text,
        "body_text": body_text,
        "references_text": references_text,
        "paragraphs": paragraphs,
        "pages": pages_metadata or []
    }
