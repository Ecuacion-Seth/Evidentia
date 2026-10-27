import unittest
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))

from citation_extractor import CitationExtractor
from parser import segment_document
from ai_indicator import AIIndicator
from nli_matcher import NLIMatcher
from source_retriever import SourceRetriever

class TestEvidentiaEngine(unittest.TestCase):

    def setUp(self):
        self.citation_extractor = CitationExtractor()
        self.ai_indicator = AIIndicator()
        self.nli_matcher = NLIMatcher()

    def test_apa_reference_parsing(self):
        raw_ref = "Vaswani, A., Shazeer, N., & Parmar, N. (2017). Attention is all you need. Advances in Neural Information Processing Systems, 30. https://doi.org/10.48550/arXiv.1706.03762"
        parsed = self.citation_extractor._parse_single_reference(raw_ref)

        self.assertIn("Vaswani", parsed["authors"])
        self.assertEqual("2017", parsed["year"])
        self.assertEqual("Attention is all you need", parsed["title"])
        self.assertEqual("10.48550/arXiv.1706.03762", parsed["doi"])
        self.assertIn("Vaswani", parsed["citation_key"])

    def test_in_text_citation_extraction(self):
        text = "Deep learning architectures have evolved rapidly (Vaswani et al., 2017). According to Devlin et al. (2018), bidirectional representations excel at language understanding."
        citations = self.citation_extractor.extract_in_text_citations(text)

        self.assertEqual(len(citations), 2)
        self.assertEqual(citations[0]["marker"], "(Vaswani et al., 2017)")
        self.assertEqual(citations[0]["type"], "parenthetical")
        self.assertEqual(citations[1]["marker"], "Devlin et al. (2018)")
        self.assertEqual(citations[1]["type"], "narrative")

    def test_document_structural_segmentation(self):
        doc = (
            "This is the introduction and discussion of our research paper.\n\n"
            "References\n"
            "Vaswani, A. (2017). Attention is all you need. NeurIPS.\n"
        )
        segmented = segment_document(doc)

        self.assertIn("This is the introduction", segmented["body_text"])
        self.assertIn("Attention is all you need", segmented["references_text"])

    def test_ai_regularity_indicator_with_disclaimer(self):
        text = (
            "Academic writing requires rigorous documentation of empirical evidence. "
            "Every assertion presented in a manuscript must be anchored in reliable peer-reviewed literature. "
            "Failure to substantiate claims introduces severe threats to scholarly validity."
        )
        result = self.ai_indicator.calculate_indicator(text)

        self.assertIn("score", result)
        self.assertIn("disclaimer", result)
        self.assertIn("burstiness", result)
        self.assertIn("entropy", result)
        self.assertIn("Notice: This score is a mathematical regularity heuristic", result["disclaimer"])

    def test_nli_matcher_supported_verdict(self):
        claim = "The Transformer network relies entirely on an attention mechanism without using recurrent neural networks."
        source = {
            "retrieval_status": "RETRIEVED",
            "full_text": "The Transformer is the first transduction model relying entirely on self-attention to compute representations without using sequence-aligned RNNs or convolution.",
            "doi": "10.48550/arXiv.1706.03762"
        }
        res = self.nli_matcher.match_claim_to_source(claim, source)

        self.assertEqual(res["verdict"], "SUPPORTED")
        self.assertIsNotNone(res["evidence_quote"])
        self.assertGreater(res["confidence_score"], 0.8)

    def test_nli_matcher_unsupported_verdict(self):
        claim = "BERT eliminates all transformer encoders and achieves results using unidirectional recurrent LSTM units."
        source = {
            "retrieval_status": "RETRIEVED",
            "full_text": "BERT is designed to pre-train deep bidirectional representations from unlabeled text using Transformer encoder stacks.",
            "doi": "10.48550/arXiv.1810.04805"
        }
        res = self.nli_matcher.match_claim_to_source(claim, source)

        self.assertEqual(res["verdict"], "UNSUPPORTED")
        self.assertIn("refutes", res["reasoning_warrant"])

    def test_nli_matcher_unverifiable_when_paywalled_or_missing(self):
        claim = "Hyper-quantum particles travel backward in time at room temperature."
        source = {
            "retrieval_status": "PAYWALLED",
            "full_text": None,
            "doi": "10.1234/paywalled.2025"
        }
        res = self.nli_matcher.match_claim_to_source(claim, source)

        self.assertEqual(res["verdict"], "UNVERIFIABLE")
        self.assertIsNone(res["evidence_quote"])

if __name__ == "__main__":
    unittest.main()
