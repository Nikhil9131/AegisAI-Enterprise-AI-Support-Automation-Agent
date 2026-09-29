import re
from typing import Dict, Any, List
from app.agents.state import AgentState
from app.tools.mysql_tool import run_readonly_sql, get_schema_info
from app.models.schemas import AgentTraceStep
from app.agents.llm_factory import chat_model

def run_sql_agent(state: AgentState) -> Dict[str, Any]:
    """Execute SQL Agent: translate natural language to read-only SQL, execute, and explain results."""
    query = state["query"]
    tools_executed = list(state.get("tools_executed", []))
    trace = list(state.get("execution_trace", []))
    
    schema_info = get_schema_info()
    sql_query = _generate_sql_query(query, schema_info)
    
    tools_executed.append("mysql_readonly_sql")
    result = run_readonly_sql(sql_query)
    
    trace.append(
        AgentTraceStep(
            agent_name="SQL Agent",
            thought=f"Generated read-only SQL query based on database schema: `{sql_query}`",
            tool_called="mysql_readonly_sql",
            tool_input={"query": sql_query},
            tool_output={"success": result["success"], "rows_returned": result.get("row_count", 0)}
        )
    )

    if not result["success"]:
        answer = f"⚠️ I attempted to query the enterprise database, but encountered an error: {result['error']}"
        return {
            "assigned_agent": "SQL Agent",
            "tools_executed": tools_executed,
            "execution_trace": trace,
            "final_answer": answer,
            "tokens_used": state.get("tokens_used", 0) + 150
        }

    rows = result.get("data", [])
    answer = _format_sql_answer(query, sql_query, rows)

    return {
        "assigned_agent": "SQL Agent",
        "tools_executed": tools_executed,
        "execution_trace": trace,
        "final_answer": answer,
        "tokens_used": state.get("tokens_used", 0) + 280
    }

def _generate_sql_query(query: str, schema_info: str) -> str:
    """Generate safe read-only SQL using LLM or rule-based matching."""
    if chat_model:
        try:
            from langchain_core.messages import SystemMessage, HumanMessage
            sys_msg = SystemMessage(
                content=(
                    "You are the Aegis SQL AI Agent. Convert the user's natural language question into a strictly read-only SQL SELECT statement. "
                    "Only output the raw SQL query with NO markdown fences, NO comments, and NO semicolons. "
                    "Database schema:\n" + schema_info
                )
            )
            usr_msg = HumanMessage(content=query)
            resp = chat_model.invoke([sys_msg, usr_msg])
            cleaned = resp.content.strip().replace("```sql", "").replace("```", "").strip().rstrip(";")
            if cleaned.upper().startswith("SELECT") or cleaned.upper().startswith("WITH"):
                return cleaned
        except Exception as e:
            print(f"[SQL Agent] LLM SQL generation error: {e}")

    # Deterministic SQL rules
    q_low = query.lower()
    if "open ticket" in q_low or "ticket status" in q_low:
        return "SELECT status, COUNT(*) as ticket_count FROM tickets GROUP BY status"
    elif "highest priority" in q_low or "critical ticket" in q_low:
        return "SELECT ticket_number, title, priority, status, created_at FROM tickets WHERE priority IN ('CRITICAL', 'HIGH') AND status != 'CLOSED' ORDER BY CASE priority WHEN 'CRITICAL' THEN 1 ELSE 2 END LIMIT 5"
    elif "sarah" in q_low and "ticket" in q_low:
        return "SELECT t.ticket_number, t.title, t.priority, t.status FROM tickets t JOIN users u ON t.assigned_to = u.id WHERE u.full_name LIKE '%Sarah%' LIMIT 5"
    elif "engineering" in q_low and "ticket" in q_low:
        return "SELECT t.ticket_number, t.title, t.priority, t.status, u.full_name as requester FROM tickets t JOIN users u ON t.user_id = u.id WHERE u.department LIKE '%Engineering%' LIMIT 5"
    elif "device" in q_low and ("maintenance" in q_low or "status" in q_low or "how many" in q_low):
        return "SELECT status, COUNT(*) as device_count FROM devices GROUP BY status"
    elif "employee" in q_low or "department" in q_low:
        return "SELECT department, COUNT(*) as employee_count FROM employees GROUP BY department"
    else:
        return "SELECT ticket_number, title, priority, status FROM tickets ORDER BY id DESC LIMIT 5"

def _format_sql_answer(user_query: str, sql_query: str, rows: List[Dict[str, Any]]) -> str:
    """Format SQL query results into clean, executive-level markdown tables."""
    if not rows:
        return f"I executed the query `{sql_query}`, but found 0 matching records in the database."

    # Build markdown table
    headers = list(rows[0].keys())
    header_line = "| " + " | ".join(h.replace("_", " ").title() for h in headers) + " |"
    separator_line = "| " + " | ".join(["---"] * len(headers)) + " |"
    
    table_rows = []
    for r in rows:
        row_str = "| " + " | ".join(str(r.get(h, "")) for h in headers) + " |"
        table_rows.append(row_str)

    table_md = "\n".join([header_line, separator_line] + table_rows)
    
    return (
        f"📊 **Database Query Results:**\n\n"
        f"{table_md}\n\n"
        f"*Executed Read-Only SQL Query:* `{sql_query}`"
    )
