from typing import List, Dict, Any
from app.models.schemas import CitationItem

def build_citations(search_results: List[Dict[str, Any]]) -> List[CitationItem]:
    """Convert raw retriever results into CitationItem schemas."""
    citations = []
    seen = set()

    for r in search_results:
        key = (r["document_title"], r["page"], r["section"])
        if key in seen:
            continue
        seen.add(key)

        snippet = r["content"][:200] + "..." if len(r["content"]) > 200 else r["content"]
        citations.append(
            CitationItem(
                document_title=r["document_title"],
                page=r.get("page", 1),
                section=r.get("section", "General"),
                score=r.get("score", 0.0),
                snippet=snippet
            )
        )
    return citations

def format_citations_markdown(citations: List[CitationItem]) -> str:
    """Format citations into a standardized enterprise markdown citation footer."""
    if not citations:
        return ""

    lines = ["\n\n**Verified Sources & Citations:**"]
    for c in citations:
        score_pct = f" ({int(c.score * 100)}% match)" if c.score else ""
        lines.append(f"- 📄 **{c.document_title}** — Section: *{c.section}*, Page: **{c.page}**{score_pct}")
    return "\n".join(lines)
