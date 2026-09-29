import time
import re
from typing import Dict, Any
from app.agents.state import AgentState
from app.agents.guardrails import check_prompt_security
from app.agents.rag_agent import run_rag_agent
from app.agents.sql_agent import run_sql_agent
from app.agents.api_agent import run_api_agent
from app.agents.support_agent import run_support_agent
from app.models.schemas import AgentTraceStep
from app.db.session import log_agent_execution

def route_query_intent(query: str) -> str:
    """Classify user query into target specialized agent."""
    q_low = query.lower()

    # 1. Direct greetings
    if q_low in ["hi", "hello", "hey", "good morning", "good afternoon", "who are you", "what can you do"]:
        return "DIRECT_RESPONSE"

    # 2. Hardware replacement / IT Support issues
    if any(k in q_low for k in ["replace my laptop", "laptop replacement", "upgrade laptop", "new laptop", "broken screen", "vpn troubleshooting", "vpn not working", "cannot connect to vpn"]):
        return "SUPPORT_AGENT"

    # 3. SQL / Database aggregations, counts, listings
    if any(k in q_low for k in [
        "how many open tickets", "highest priority", "critical ticket", "tickets by status",
        "show all tickets", "count of", "total tickets", "tickets assigned to", "devices in maintenance",
        "employees by department", "database query", "open issues"
    ]):
        return "SQL_AGENT"

    # 4. API lookups (specific codes, names, serials)
    if re.search(r'\b(EMP-\d+|DEV-\d+|SN-[A-Z0-9]+|TKT-\d{4}-\d{4})\b', query, re.IGNORECASE) or \
       any(k in q_low for k in ["who is rahul", "employee profile", "device info", "asset details", "who is priya", "who is sarah"]):
        return "API_AGENT"

    # 5. Policies & Guides (RAG)
    if any(k in q_low for k in ["policy", "guideline", "leave", "attendance", "password", "vpn", "onboarding", "security", "wifi", "handbook"]):
        return "RAG_AGENT"

    # Default to RAG for enterprise assistance
    return "RAG_AGENT"

def execute_agent_workflow(
    query: str,
    user_id: int = 1,
    user_role: str = "ADMIN",
    department: str = "General",
    conversation_id: str = "conv-default",
    history: list = None
) -> Dict[str, Any]:
    """
    Supervisor Agent Workflow orchestrator.
    Evaluates prompt injection, routes to specialized subagent, tracks execution trace and latency,
    and logs the trace to the database for observability.
    """
    start_time = time.time()
    
    # Initialize AgentState
    state: AgentState = {
        "query": query,
        "user_id": user_id,
        "user_role": user_role,
        "department": department,
        "history": history or [],
        "intent": "UNKNOWN",
        "assigned_agent": "Supervisor",
        "tools_executed": [],
        "execution_trace": [],
        "citations": [],
        "final_answer": "",
        "hitl_required": False,
        "hitl_action": None,
        "tokens_used": 0,
        "latency_ms": 0,
        "error": None
    }

    # Step 1: Prompt Injection Security Check
    is_safe, sec_reason = check_prompt_security(query)
    if not is_safe:
        latency = int((time.time() - start_time) * 1000)
        state["intent"] = "SECURITY_BLOCKED"
        state["assigned_agent"] = "Security Guardrails"
        state["final_answer"] = sec_reason
        state["latency_ms"] = latency
        state["execution_trace"].append(
            AgentTraceStep(
                agent_name="Security Guardrails",
                thought="Detected potential prompt injection / policy violation. Blocked execution.",
                status="BLOCKED"
            )
        )
        log_agent_execution(query, "Security Guardrails", ["security_filter"], latency, 50, "BLOCKED", user_id, error_message="Prompt injection attempt blocked")
        return state

    # Step 2: Route to specialized agent
    intent = route_query_intent(query)
    state["intent"] = intent

    state["execution_trace"].append(
        AgentTraceStep(
            agent_name="Supervisor",
            thought=f"Analyzed employee query. Classified routing intent as `{intent}`.",
            status="ROUTED"
        )
    )

    # Step 3: Execute target agent
    if intent == "DIRECT_RESPONSE":
        state["assigned_agent"] = "Supervisor"
        state["final_answer"] = (
            "👋 Hello! I am **Aegis AI**, your Enterprise Support & Business Automation Agent.\n\n"
            "Here are some ways I can assist you today:\n"
            "- 📄 **Company Policies & Docs:** Ask about the VPN guide, Leave Policy, or Laptop Replacement Policy (with exact page citations).\n"
            "- 📊 **Database Analytics:** Ask questions like *'How many open tickets are there?'* or *'What is the highest priority issue?'*\n"
            "- 👤 **Enterprise Directory:** Look up employee profiles or assigned devices (`EMP-1004`, `Rahul Sharma`, `DEV-1001`).\n"
            "- 🔧 **IT Troubleshooting & Automation:** Diagnose technical issues or request hardware upgrades with Human-in-the-Loop approval.\n"
            "- 📷 **Visual Diagnostics:** Upload screenshots of errors or blue-screens for automatic root-cause analysis."
        )
        state["tokens_used"] = 120

    elif intent == "RAG_AGENT":
        rag_res = run_rag_agent(state)
        state.update(rag_res)

    elif intent == "SQL_AGENT":
        sql_res = run_sql_agent(state)
        state.update(sql_res)

    elif intent == "API_AGENT":
        api_res = run_api_agent(state)
        state.update(api_res)

    elif intent == "SUPPORT_AGENT":
        support_res = run_support_agent(state)
        state.update(support_res)

    # Finalize execution metadata
    latency = int((time.time() - start_time) * 1000)
    state["latency_ms"] = latency

    # Log to database for AI observability
    log_agent_execution(
        query=query,
        routing_intent=f"Supervisor -> {state['assigned_agent']}",
        tools_called=state["tools_executed"],
        latency_ms=latency,
        tokens_used=state["tokens_used"],
        status="SUCCESS",
        user_id=user_id,
        model_name="gemini-1.5-pro"
    )

    return state
