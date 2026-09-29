import json
from fastapi import APIRouter
from app.models.schemas import ChatRequest, ChatResponse
from app.agents.supervisor import execute_agent_workflow
from app.db.session import get_connection

router = APIRouter(tags=["Agents"])

@router.post("/agent/run", response_model=ChatResponse)
async def run_agent_workflow(request: ChatRequest):
    """Trigger agent execution workflow."""
    result = execute_agent_workflow(
        query=request.query,
        user_id=request.user_id or 1,
        user_role=request.user_role or "ADMIN",
        department=request.department or "General",
        conversation_id=request.conversation_id or "conv-direct"
    )
    return ChatResponse(
        answer=result["final_answer"],
        conversation_id=request.conversation_id or "conv-direct",
        routing_intent=result["intent"],
        assigned_agent=result["assigned_agent"],
        citations=result.get("citations", []),
        tools_executed=result.get("tools_executed", []),
        execution_trace=result.get("execution_trace", []),
        latency_ms=result.get("latency_ms", 0),
        tokens_used=result.get("tokens_used", 0),
        hitl_required=result.get("hitl_required", False),
        hitl_action=result.get("hitl_action")
    )

@router.get("/agent/logs")
async def get_agent_logs(limit: int = 50):
    """Retrieve multi-agent execution traces for observability dashboard."""
    conn = get_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            """
            SELECT l.*, u.full_name as user_name
            FROM ai_agent_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT %s
            """ if "mysql" in str(type(cursor)).lower() else
            """
            SELECT l.*, u.full_name as user_name
            FROM ai_agent_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT ?
            """,
            (limit,)
        )
        rows = cursor.fetchall()
        logs = []
        for r in rows:
            item = dict(r)
            if item.get("tools_called") and isinstance(item["tools_called"], str):
                try:
                    item["tools_called"] = json.loads(item["tools_called"])
                except Exception:
                    pass
            logs.append(item)
            
        return {
            "total": len(logs),
            "traces": logs
        }
    finally:
        cursor.close()
        conn.close()
