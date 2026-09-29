from typing import Dict, Any
from app.agents.state import AgentState
from app.tools.search_tool import search_enterprise_kb
from app.models.schemas import AgentTraceStep
from app.agents.llm_factory import chat_model

def run_rag_agent(state: AgentState) -> Dict[str, Any]:
    """Execute RAG Agent: query vector database and synthesize grounded answer with citations."""
    query = state["query"]
    tools_executed = list(state.get("tools_executed", []))
    trace = list(state.get("execution_trace", []))
    
    # 1. Execute vector search tool
    tools_executed.append("search_enterprise_kb")
    search_res = search_enterprise_kb(query, top_k=4)
    citations = search_res["citations"]
    context = search_res["context"]

    thought = f"Retrieved {len(citations)} relevant policy/guide chunks from Qdrant vector database."
    trace.append(
        AgentTraceStep(
            agent_name="RAG Agent",
            thought=thought,
            tool_called="search_enterprise_kb",
            tool_input={"query": query, "top_k": 4},
            tool_output={"matched_chunks": len(citations), "top_score": citations[0].score if citations else 0.0}
        )
    )

    if not citations:
        answer = "I searched our enterprise knowledge base, but could not find relevant documentation or policy matching your specific question. Please submit a support ticket to our IT Support Desk for further assistance."
        return {
            "assigned_agent": "RAG Agent",
            "tools_executed": tools_executed,
            "execution_trace": trace,
            "citations": [],
            "final_answer": answer,
            "tokens_used": state.get("tokens_used", 0) + 120
        }

    # 2. Synthesize answer using LLM or structured knowledge synthesis
    if chat_model:
        try:
            from langchain_core.messages import SystemMessage, HumanMessage
            sys_msg = SystemMessage(
                content=(
                    "You are the Aegis Enterprise Knowledge Base Agent. Answer the employee's question strictly "
                    "using the provided verified enterprise context. Always mention specific policies, guidelines, "
                    "sections, and page numbers when available. Do not hallucinate."
                )
            )
            usr_msg = HumanMessage(
                content=f"Employee Question: {query}\n\nVerified Enterprise Context:\n{context}\n\nPlease provide a clear, professional answer:"
            )
            resp = chat_model.invoke([sys_msg, usr_msg])
            answer = resp.content + search_res["citations_markdown"]
            tokens = 350
        except Exception as e:
            print(f"[RAG Agent] LLM generation error: {e}")
            answer = _build_grounded_answer(query, context, citations, search_res["citations_markdown"])
            tokens = 250
    else:
        answer = _build_grounded_answer(query, context, citations, search_res["citations_markdown"])
        tokens = 200

    return {
        "assigned_agent": "RAG Agent",
        "tools_executed": tools_executed,
        "execution_trace": trace,
        "citations": citations,
        "final_answer": answer,
        "tokens_used": state.get("tokens_used", 0) + tokens
    }

def _build_grounded_answer(query: str, context: str, citations: list, citations_md: str) -> str:
    """Deterministic high-quality synthesis when external LLM API is unavailable."""
    top_doc = citations[0].document_title if citations else "Enterprise Policy"
    top_section = citations[0].section if citations else "Guidelines"
    top_page = citations[0].page if citations else 1
    
    # Extract clean lines from top matching context
    first_chunk = context.split("---")[1] if "---" in context else context
    cleaned_lines = [l.strip() for l in first_chunk.split("\n") if l.strip() and not l.startswith("[Document:")][:8]
    summary_body = "\n".join(f"- {l}" for l in cleaned_lines)
    
    return (
        f"Based on the official **{top_doc}** (Section: *{top_section}*, Page **{top_page}**), "
        f"here is the verified guidance for your inquiry:\n\n"
        f"{summary_body}\n\n"
        f"For any exceptions or special approvals, please submit an official request through the Aegis Support Portal."
        f"{citations_md}"
    )
