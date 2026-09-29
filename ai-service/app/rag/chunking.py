import re
from typing import List, Dict, Any

class DocumentChunker:
    def __init__(self, chunk_size: int = 500, chunk_overlap: int = 60):
        self.chunk_size = chunk_size
        self.chunk_overlap = chunk_overlap

    def split_text_with_metadata(
        self,
        text: str,
        document_title: str,
        default_page: int = 1,
        default_section: str = "General"
    ) -> List[Dict[str, Any]]:
        """
        Split raw text into chunks while tracking page markers and section headers.
        Recognizes headers like:
        - `== Page 12 ==` or `[Page 3]`
        - `## Section Title` or `SECTION 4: ...`
        """
        lines = text.split("\n")
        current_page = default_page
        current_section = default_section

        sections = []
        current_buffer = []

        for line in lines:
            line_str = line.strip()
            
            # Detect page markers (e.g., '== Page 12 ==' or '--- Page 2 ---' or 'Page 12')
            page_match = re.search(r'(?:==\s*Page\s*(\d+)\s*==|---\s*Page\s*(\d+)\s*---|\[Page\s*(\d+)\]|^Page\s+(\d+)\b)', line_str, re.IGNORECASE)
            if page_match:
                # Flush current buffer
                if current_buffer:
                    sections.append({
                        "content": "\n".join(current_buffer).strip(),
                        "page": current_page,
                        "section": current_section
                    })
                    current_buffer = []
                page_num = next(p for p in page_match.groups() if p is not None)
                current_page = int(page_num)
                continue

            # Detect section headings (e.g., '## Hardware Replacement' or '1.0 Introduction' or 'SECTION: ...')
            header_match = re.match(r'^(?:#{1,4}\s+|(?:SECTION|\d+\.\d+)\s*[:\-\.]?\s*)(.+)$', line_str, re.IGNORECASE)
            if header_match:
                if current_buffer:
                    sections.append({
                        "content": "\n".join(current_buffer).strip(),
                        "page": current_page,
                        "section": current_section
                    })
                    current_buffer = []
                current_section = header_match.group(1).strip()
                continue

            current_buffer.append(line)

        if current_buffer:
            sections.append({
                "content": "\n".join(current_buffer).strip(),
                "page": current_page,
                "section": current_section
            })

        chunks = []
        chunk_idx = 0

        for sec in sections:
            sec_text = sec["content"]
            if not sec_text:
                continue

            # Sliding window over section text
            start = 0
            while start < len(sec_text):
                end = min(start + self.chunk_size, len(sec_text))
                
                # Try to break at newline or space
                if end < len(sec_text):
                    last_space = sec_text.rfind(" ", start, end)
                    if last_space > start + (self.chunk_size // 2):
                        end = last_space

                chunk_content = sec_text[start:end].strip()
                if chunk_content:
                    chunks.append({
                        "chunk_index": chunk_idx,
                        "document_title": document_title,
                        "page": sec["page"],
                        "section": sec["section"],
                        "content": chunk_content
                    })
                    chunk_idx += 1

                if end >= len(sec_text):
                    break
                start = max(start + 1, end - self.chunk_overlap)

        return chunks

chunker = DocumentChunker()
