import re
from typing import List, Dict, Any, Optional

class CitationExtractor:
    """
    Parses APA 7th style in-text citations and bibliographic reference lists.
    """

    # In-text parenthetical: (Author, Year) or (Author & Author, Year) or (Author et al., Year)
    # Allows page numbers, e.g. (Smith, 2020, p. 14) or (Smith & Jones, 2019, pp. 22-25)
    IN_TEXT_PARENTHETICAL_REGEX = re.compile(
        r'\(([A-Z][a-zA-Z\s\.\-]+(?:(?:,|and|&)\s+[A-Z][a-zA-Z\s\.\-]+|(?:\s+et\s+al\.?))?),\s*([12][0-9]{3}(?:[a-z])?)(?:,\s*(?:p\.|pp\.|para\.)\s*[0-9\-]+)?\)'
    )

    # In-text narrative: Author (Year) or Author et al. (Year) or Author & Author (Year)
    IN_TEXT_NARRATIVE_REGEX = re.compile(
        r'\b([A-Z][a-z]+(?:\s+(?:and|&)\s+[A-Z][a-z]+|\s+et\s+al\.?)?)\s+\(([12][0-9]{3}(?:[a-z])?)\)'
    )

    # DOI extraction pattern
    DOI_REGEX = re.compile(r'(?:https?://(?:dx\.)?doi\.org/|doi:\s*)(10\.[0-9]{4,9}/[-._;()/:A-Za-z0-9]+)', re.IGNORECASE)

    # URL extraction pattern
    URL_REGEX = re.compile(r'https?://[^\s<>"]+|www\.[^\s<>"]+', re.IGNORECASE)

    # Reference entry header pattern (Author(s) (Year).)
    REF_ENTRY_SPLIT_REGEX = re.compile(r'\n(?=[A-Z][A-Za-z\s\.\-,&]+?\s*\([12][0-9]{3}[a-z]?\)\.)')

    def parse_references(self, references_text: str) -> List[Dict[str, Any]]:
        """
        Parses raw text of the References section into structured bibliographic entries.
        """
        if not references_text.strip():
            return []

        # Split into individual reference items (by newline + author/year or blank lines)
        raw_items = self.REF_ENTRY_SPLIT_REGEX.split('\n' + references_text.strip())
        if len(raw_items) <= 1:
            # Fallback split by double newlines or hanging indents
            raw_items = [p.strip() for p in re.split(r'\n\s*\n', references_text) if p.strip()]

        parsed_references = []
        for item in raw_items:
            clean_item = " ".join(item.split()).strip()
            if len(clean_item) < 15:
                continue

            ref_data = self._parse_single_reference(clean_item)
            if ref_data:
                parsed_references.append(ref_data)

        return parsed_references

    def _parse_single_reference(self, raw_entry: str) -> Dict[str, Any]:
        """
        Extracts author, year, title, source, DOI/URL from an APA reference string.
        Standard APA: Author, A. A., & Author, B. B. (Year). Title of article. Title of Periodical, xx(x), pp-pp. https://doi.org/xxxx
        """
        doi_match = self.DOI_REGEX.search(raw_entry)
        doi = doi_match.group(1).rstrip('.') if doi_match else None

        url_match = self.URL_REGEX.search(raw_entry)
        url = url_match.group(0).rstrip('.') if url_match else None

        # Pattern: Author(s) (Year). Title...
        match = re.match(r'^([^()]+?)\s*\(([12][0-9]{3}[a-z]?)\)\.\s*(.+)$', raw_entry)

        authors_str = "Unknown Author"
        year_str = "n.d."
        rest = raw_entry
        title_str = ""
        source_str = ""

        if match:
            authors_str = match.group(1).strip()
            year_str = match.group(2).strip()
            rest = match.group(3).strip()

            # Separate Title from Source/Journal
            # In APA, article title usually ends with a period before the italicized journal name
            title_parts = rest.split('. ')
            if len(title_parts) >= 2:
                title_str = title_parts[0].strip()
                source_str = ". ".join(title_parts[1:]).strip()
            else:
                title_str = rest
        else:
            # Fallback year extraction
            year_search = re.search(r'\b([12][0-9]{3})\b', raw_entry)
            if year_search:
                year_str = year_search.group(1)
            title_str = raw_entry

        # Generate canonical citation key, e.g. "Smith et al., 2021" or "Vaswani, 2017"
        citation_key = self._generate_citation_key(authors_str, year_str)

        return {
            "citation_key": citation_key,
            "raw_text": raw_entry,
            "authors": authors_str,
            "year": year_str,
            "title": title_str,
            "source": source_str,
            "doi": doi,
            "url": url,
        }

    def _generate_citation_key(self, authors: str, year: str) -> str:
        """
        Builds a canonical key for in-text matching, e.g., 'Vaswani et al., 2017' or 'Smith & Jones, 2020'.
        """
        first_author = authors.split(',')[0].strip()
        # Clean any initials
        first_author = re.sub(r'\s+[A-Z]\.?.*', '', first_author).strip()

        if "&" in authors or " and " in authors:
            parts = re.split(r'\s*(&|and)\s*', authors)
            if len(parts) >= 3:
                second_author = parts[2].split(',')[0].strip()
                second_author = re.sub(r'\s+[A-Z]\.?.*', '', second_author).strip()
                if len(parts) > 3 or authors.count(',') > 2:
                    return f"{first_author} et al., {year}"
                return f"{first_author} & {second_author}, {year}"
        elif "et al" in authors.lower():
            return f"{first_author} et al., {year}"

        return f"{first_author}, {year}"

    def extract_in_text_citations(self, body_text: str) -> List[Dict[str, Any]]:
        """
        Locates all parenthetical and narrative in-text citations in the body text.
        """
        citations = []

        # 1. Parenthetical citations
        for match in self.IN_TEXT_PARENTHETICAL_REGEX.finditer(body_text):
            author_part = match.group(1).strip()
            year_part = match.group(2).strip()
            marker = match.group(0)

            citations.append({
                "type": "parenthetical",
                "marker": marker,
                "author": author_part,
                "year": year_part,
                "start": match.start(),
                "end": match.end(),
                "normalized_key": self._generate_citation_key(author_part, year_part)
            })

        # 2. Narrative citations
        for match in self.IN_TEXT_NARRATIVE_REGEX.finditer(body_text):
            author_part = match.group(1).strip()
            year_part = match.group(2).strip()
            marker = match.group(0)

            citations.append({
                "type": "narrative",
                "marker": marker,
                "author": author_part,
                "year": year_part,
                "start": match.start(),
                "end": match.end(),
                "normalized_key": self._generate_citation_key(author_part, year_part)
            })

        # Sort by appearance in the text
        citations.sort(key=lambda c: c["start"])
        return citations

    def match_citation_to_reference(self, citation_key: str, author: str, year: str, references: List[Dict[str, Any]]) -> Optional[Dict[str, Any]]:
        """
        Fuzzy matches an in-text citation to an entry in the Reference List.
        """
        clean_author = author.split()[0].replace(',', '').lower()

        # Exact key match
        for ref in references:
            if ref["citation_key"].lower() == citation_key.lower():
                return ref

        # Author surname + Year match
        for ref in references:
            ref_authors_lower = ref["authors"].lower()
            if clean_author in ref_authors_lower and (ref["year"] == year or year in ref["year"]):
                return ref

        # Fallback: Author surname alone if only 1 matches
        surname_matches = [ref for ref in references if clean_author in ref["authors"].lower()]
        if len(surname_matches) == 1:
            return surname_matches[0]

        return None
