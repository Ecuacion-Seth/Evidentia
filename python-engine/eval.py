import json
from pathlib import Path
from typing import Dict, Any, List

from citation_extractor import CitationExtractor
from source_retriever import SourceRetriever
from nli_matcher import NLIMatcher

def run_evaluation(dataset_path: str = "test_dataset.json") -> Dict[str, Any]:
    """
    Evaluates Evidentia's claim-evidence matching against a human-annotated test set of real claim-citation pairs.
    Computes Precision, Recall, F1 per class, and retrieval accuracy.
    """
    path = Path(__file__).parent / dataset_path
    if not path.exists():
        raise FileNotFoundError(f"Test dataset not found at {path}")

    with open(path, "r", encoding="utf-8") as f:
        pairs = json.load(f)

    citation_extractor = CitationExtractor()
    source_retriever = SourceRetriever()
    nli_matcher = NLIMatcher()

    classes = ["SUPPORTED", "PARTIALLY_SUPPORTED", "UNSUPPORTED", "UNVERIFIABLE"]
    confusion_matrix = {c: {pred: 0 for pred in classes} for c in classes}
    retrieval_success_count = 0
    total = len(pairs)

    print(f"\n=======================================================")
    print(f"EVIDENTIA HUMAN-ANNOTATED BENCHMARK EVALUATION")
    print(f"Testing {total} verified claim-citation pairs")
    print(f"=======================================================\n")

    import sys
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

    for item in pairs:
        pair_id = item["id"]
        claim_text = item["claim_text"]
        ref_raw = item["reference_raw"]
        ground_truth = item["ground_truth_label"]
        doi = item.get("doi")

        # Parse reference
        parsed_ref = citation_extractor._parse_single_reference(ref_raw)
        if doi:
            parsed_ref["doi"] = doi

        # Source retrieval
        retrieved_source = source_retriever.verify_and_retrieve(parsed_ref)
        if retrieved_source.get("retrieval_status") == "RETRIEVED":
            retrieval_success_count += 1

        # Match claim
        result = nli_matcher.match_claim_to_source(claim_text, retrieved_source)
        predicted = result["verdict"]

        if predicted not in classes:
            predicted = "UNVERIFIABLE"

        confusion_matrix[ground_truth][predicted] += 1

        is_match = "[MATCH]" if predicted == ground_truth else "[MISMATCH]"
        print(f"[{pair_id}] True: {ground_truth:<19} | Pred: {predicted:<19} | {is_match}")
        if result.get("evidence_quote"):
            print(f"       Quote: {result['evidence_quote'][:90]}...")
        print(f"       Warrant: {result.get('reasoning_warrant')}\n")

    # Metrics calculation
    per_class_metrics = {}
    total_correct = 0

    for c in classes:
        tp = confusion_matrix[c][c]
        fp = sum(confusion_matrix[other][c] for other in classes if other != c)
        fn = sum(confusion_matrix[c][other] for other in classes if other != c)

        precision = tp / (tp + fp) if (tp + fp) > 0 else 0.0
        recall = tp / (tp + fn) if (tp + fn) > 0 else 0.0
        f1 = (2 * precision * recall) / (precision + recall) if (precision + recall) > 0 else 0.0

        per_class_metrics[c] = {
            "true_positives": tp,
            "false_positives": fp,
            "false_negatives": fn,
            "precision": round(precision, 3),
            "recall": round(recall, 3),
            "f1_score": round(f1, 3)
        }
        total_correct += tp

    overall_accuracy = round(total_correct / total, 3) if total > 0 else 0.0
    retrieval_rate = round(retrieval_success_count / total, 3) if total > 0 else 0.0

    print("-------------------------------------------------------")
    print(f"BENCHMARK SUMMARY RESULTS:")
    print(f"Overall Accuracy: {overall_accuracy * 100:.1f}%")
    print(f"Retrieval Success Rate: {retrieval_rate * 100:.1f}%")
    print("-------------------------------------------------------")
    for c in classes:
        m = per_class_metrics[c]
        print(f"{c:<20}: Precision={m['precision']:.2f}, Recall={m['recall']:.2f}, F1={m['f1_score']:.2f}")
    print("=======================================================\n")

    return {
        "total_pairs": total,
        "overall_accuracy": overall_accuracy,
        "retrieval_rate": retrieval_rate,
        "per_class_metrics": per_class_metrics,
        "confusion_matrix": confusion_matrix
    }

if __name__ == "__main__":
    run_evaluation()
