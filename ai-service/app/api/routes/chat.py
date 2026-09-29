import uuid
from fastapi import APIRouter
from app.models.schemas import ChatRequest, ChatResponse, FeedbackRequest
from app.agents.supervisor import execute_agent_workflow
from app.db.session import get_connection, settings

router = APIRouter(tags=["Chat"])

@router.post("/chat", response_model=ChatResponse)
async def chat_endpoint(request: ChatRequest):
    """
    Main Multi-Agent conversation endpoint.
    Routes query through Supervisor Agent -> (RAG, SQL, API, Support) -> Response + Citations + Trace.
    """
    conv_id = request.conversation_id or f"conv-{uuid.uuid4().hex[:8]}"

    result = execute_agent_workflow(
        query=request.query,
        user_id=request.user_id or 1,
        user_role=request.user_role or "ADMIN",
        department=request.department or "General",
        conversation_id=conv_id,
        history=request.history or []
    )

    return ChatResponse(
        answer=result["final_answer"],
        conversation_id=conv_id,
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

@router.post("/chat/feedback")
async def chat_feedback(request: FeedbackRequest):
    """Record employee thumbs-up / thumbs-down feedback for AI evaluation."""
    conn = get_connection()
    cursor = conn.cursor()
    try:
        feedback_val = "POSITIVE" if request.rating > 0 else "NEGATIVE"
        notes = request.feedback_text or f"User submitted {feedback_val} rating"
        
        # Log to audit_logs
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, new_values, created_at)
                VALUES (%s, 'AI_FEEDBACK', 'CONVERSATION', %s, %s, NOW())
                """,
                (1, 0, f'{{"rating": "{feedback_val}", "notes": "{notes}"}}')
            )
        else:
            cursor.execute(
                """
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, new_values, created_at)
                VALUES (?, 'AI_FEEDBACK', 'CONVERSATION', ?, ?, datetime('now'))
                """,
                (1, 0, f'{{"rating": "{feedback_val}", "notes": "{notes}"}}')
            )
        conn.commit()
        return {"status": "SUCCESS", "message": f"Recorded {feedback_val} feedback successfully."}
    except Exception as e:
        return {"status": "ERROR", "message": str(e)}
    finally:
        cursor.close()
        conn.close()
