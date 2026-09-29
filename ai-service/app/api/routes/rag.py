from fastapi import APIRouter
from app.models.schemas import RAGQueryRequest, RAGQueryResponse
from app.tools.search_tool import search_enterprise_kb
from app.rag.citations import format_citations_markdown

router = APIRouter(tags=["RAG"])

@router.post("/rag/query", response_model=RAGQueryResponse)
async def query_knowledge_base(request: RAGQueryRequest):
    """Direct dense vector semantic retrieval with exact source citations."""
    res = search_enterprise_kb(request.query, top_k=request.top_k)
    citations = res["citations"]
    
    if not citations:
        return RAGQueryResponse(
            query=request.query,
            answer="No relevant documentation found in enterprise vector index.",
            citations=[],
            confidence_score=0.0
        )

    # Format synthesized answer
    top_doc = citations[0].document_title
    top_sec = citations[0].section
    top_page = citations[0].page
    
    answer = (
        f"**Official Enterprise Knowledge Base Match:**\n\n"
        f"According to **{top_doc}** (Section: *{top_sec}*, Page **{top_page}**):\n\n"
        f"{citations[0].snippet}\n\n"
        f"For full compliance details, refer to the verified citation below."
        f"{res['citations_markdown']}"
    )

    return RAGQueryResponse(
        query=request.query,
        answer=answer,
        citations=citations,
        confidence_score=citations[0].score or 0.95
    )
