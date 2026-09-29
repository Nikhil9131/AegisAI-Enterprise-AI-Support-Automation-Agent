from typing import List, Dict, Any, Optional
from typing_extensions import TypedDict
from app.models.schemas import CitationItem, AgentTraceStep

class AgentState(TypedDict):
    query: str
    user_id: int
    user_role: str
    department: str
    history: List[Dict[str, str]]
    intent: str
    assigned_agent: str
    tools_executed: List[str]
    execution_trace: List[AgentTraceStep]
    citations: List[CitationItem]
    final_answer: str
    hitl_required: bool
    hitl_action: Optional[Dict[str, Any]]
    tokens_used: int
    latency_ms: int
    error: Optional[str]
