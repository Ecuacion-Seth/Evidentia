import math
import re
from collections import Counter
from typing import Dict, Any

class AIIndicator:
    """
    Computes a supplementary, non-definitive linguistic regularity / burstiness metric
    with mandatory cautionary disclaimers.
    """

    MANDATORY_DISCLAIMER = (
        "Notice: This score is a mathematical regularity heuristic and does NOT constitute "
        "proof of AI generation or human authorship. Formal academic conventions often "
        "exhibit low perplexity and high structural regularity naturally. This indicator "
        "must never be used to make ground-truth accusations. Evaluate academic integrity "
        "exclusively through the inspectable Claim-Evidence audit."
    )

    def calculate_indicator(self, text: str) -> Dict[str, Any]:
        """
        Calculates sentence burstiness (variation in sentence lengths) and character/word entropy.
        """
        sentences = [s.strip() for s in re.split(r'(?<=[.?!])\s+', text) if len(s.strip()) > 10]
        words = re.findall(r'\b[a-zA-Z]+\b', text.lower())

        if len(sentences) < 3 or len(words) < 50:
            return {
                "score": 0.0,
                "label": "INSUFFICIENT_SAMPLE",
                "burstiness": 0.0,
                "entropy": 0.0,
                "disclaimer": self.MANDATORY_DISCLAIMER
            }

        # 1. Burstiness (Coefficient of variation in sentence word lengths)
        lengths = [len(s.split()) for s in sentences]
        mean_len = sum(lengths) / len(lengths)
        variance = sum((l - mean_len) ** 2 for l in lengths) / len(lengths)
        std_dev = math.sqrt(variance)
        burstiness = std_dev / mean_len if mean_len > 0 else 0.0

        # 2. Lexical Entropy / Unigram distribution
        word_counts = Counter(words)
        total_words = len(words)
        entropy = -sum((cnt / total_words) * math.log2(cnt / total_words) for cnt in word_counts.values())

        # High burstiness + high entropy is typical of natural human variation.
        # Uniform sentence length + repetitive transitions produces lower burstiness.
        # Scale to an indicative index (0 - 100)
        regularity_factor = max(0.0, min(1.0, 1.0 - (burstiness * 0.7)))
        score = round(regularity_factor * 100, 1)

        if score >= 70:
            label = "Elevated Structural Regularity"
        elif score >= 40:
            label = "Balanced Structural Variance"
        else:
            label = "High Structural Variance"

        return {
            "score": score,
            "label": label,
            "burstiness": round(burstiness, 3),
            "entropy": round(entropy, 3),
            "disclaimer": self.MANDATORY_DISCLAIMER
        }
