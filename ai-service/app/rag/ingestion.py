import os
from pathlib import Path
from typing import List, Dict, Any
from app.rag.chunking import chunker

def parse_txt_file(file_path: Path) -> List[Dict[str, Any]]:
    """Parse TXT or Markdown file."""
    with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    title = file_path.stem.replace("_", " ")
    return chunker.split_text_with_metadata(content, document_title=title)

def parse_pdf_file(file_path: Path) -> List[Dict[str, Any]]:
    """Parse PDF file with exact page attribution using pypdf."""
    from pypdf import PdfReader
    
    reader = PdfReader(str(file_path))
    title = file_path.stem.replace("_", " ")
    all_chunks = []
    
    for page_idx, page in enumerate(reader.pages, start=1):
        page_text = page.extract_text() or ""
        if page_text.strip():
            page_chunks = chunker.split_text_with_metadata(
                page_text,
                document_title=title,
                default_page=page_idx,
                default_section=f"Page {page_idx}"
            )
            all_chunks.extend(page_chunks)
            
    return all_chunks

def parse_docx_file(file_path: Path) -> List[Dict[str, Any]]:
    """Parse DOCX file with heading-based section detection using python-docx."""
    from docx import Document
    
    doc = Document(str(file_path))
    title = file_path.stem.replace("_", " ")
    
    lines = []
    for para in doc.paragraphs:
        if para.style.name.startswith("Heading"):
            lines.append(f"## {para.text}")
        else:
            lines.append(para.text)
            
    full_text = "\n".join(lines)
    return chunker.split_text_with_metadata(full_text, document_title=title)

def ingest_file(file_path: Path) -> List[Dict[str, Any]]:
    """Dispatch to appropriate parser based on file extension."""
    suffix = file_path.suffix.lower()
    if suffix in [".txt", ".md"]:
        return parse_txt_file(file_path)
    elif suffix == ".pdf":
        return parse_pdf_file(file_path)
    elif suffix in [".docx", ".doc"]:
        return parse_docx_file(file_path)
    else:
        # Default fallback to text parsing
        return parse_txt_file(file_path)

def ingest_directory(dir_path: Path) -> List[Dict[str, Any]]:
    """Ingest all supported documents from a directory."""
    chunks = []
    if not dir_path.exists():
        return chunks
        
    for p in dir_path.glob("*.*"):
        if p.suffix.lower() in [".txt", ".md", ".pdf", ".docx"]:
            print(f"[Ingestion] Parsing: {p.name}")
            file_chunks = ingest_file(p)
            chunks.extend(file_chunks)
            
    print(f"[Ingestion] Completed parsing {len(chunks)} total chunks from {dir_path}")
    return chunks
