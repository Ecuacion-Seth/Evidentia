import sys
import json
from pathlib import Path
from parser import extract_text_from_pdf, extract_text_from_docx, extract_text_from_raw
from main import _process_evidentia_pipeline

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Missing input file path"}))
        sys.exit(1)

    file_path = Path(sys.argv[1])
    title = sys.argv[2] if len(sys.argv) > 2 else file_path.stem

    if not file_path.exists():
        print(json.dumps({"error": f"File not found: {file_path}"}))
        sys.exit(1)

    suffix = file_path.suffix.lower()
    if suffix == ".pdf":
        doc = extract_text_from_pdf(str(file_path))
    elif suffix in [".docx", ".doc"]:
        doc = extract_text_from_docx(str(file_path))
    else:
        text = file_path.read_text(encoding="utf-8", errors="ignore")
        doc = extract_text_from_raw(text)

    result = _process_evidentia_pipeline(doc, title)
    # Output single-line JSON to stdout
    print(json.dumps(result))

if __name__ == "__main__":
    main()
