from typing import Dict, Any, List
from app.rag.retriever import retriever
from app.rag.citations import build_citations, format_citations_markdown

def search_enterprise_kb(query: str, top_k: int = 4) -> Dict[str, Any]:
    """
    Search enterprise knowledge base (company policies, hardware guides, IT manuals) in Qdrant.
    """
    hits = retriever.search(query, top_k=top_k)
    citations = build_citations(hits)
    citations_md = format_citations_markdown(citations)
    
    context_chunks = []
    for h in hits:
        context_chunks.append(
            f"--- [Document: {h['document_title']} | Section: {h['section']} | Page: {h['page']}] ---\n{h['content']}"
        )
    
    return {
        "hit_count": len(hits),
        "citations": citations,
        "citations_markdown": citations_md,
        "context": "\n\n".join(context_chunks),
        "raw_hits": hits
    }
