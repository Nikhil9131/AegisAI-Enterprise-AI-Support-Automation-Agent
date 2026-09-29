from typing import List, Optional, Dict, Any
from pydantic import BaseModel, Field

class CitationItem(BaseModel):
    document_title: str
    page: Optional[int] = None
    section: Optional[str] = None
    score: Optional[float] = None
    snippet: Optional[str] = None

class AgentTraceStep(BaseModel):
    agent_name: str
    thought: Optional[str] = None
    tool_called: Optional[str] = None
    tool_input: Optional[Any] = None
    tool_output: Optional[Any] = None
    status: str = "SUCCESS"

class ChatRequest(BaseModel):
    query: str = Field(..., description="User query or message")
    user_id: Optional[int] = 1
    user_role: Optional[str] = "ADMIN"
    department: Optional[str] = "General"
    conversation_id: Optional[str] = None
    history: Optional[List[Dict[str, str]]] = []

class ChatResponse(BaseModel):
    answer: str
    conversation_id: str
    routing_intent: str
    assigned_agent: str
    citations: List[CitationItem] = []
    tools_executed: List[str] = []
    execution_trace: List[AgentTraceStep] = []
    latency_ms: int
    tokens_used: int
    hitl_required: bool = False
    hitl_action: Optional[Dict[str, Any]] = None

class FeedbackRequest(BaseModel):
    conversation_id: Optional[str] = None
    message_id: Optional[int] = None
    rating: int = 1  # 1 for positive / thumbs-up, -1 for negative
    feedback_text: Optional[str] = None

class RAGQueryRequest(BaseModel):
    query: str
    top_k: int = 4
    category: Optional[str] = None

class RAGQueryResponse(BaseModel):
    query: str
    answer: str
    citations: List[CitationItem] = []
    confidence_score: float = 0.95

class TicketAnalysisRequest(BaseModel):
    title: str
    description: str
    user_id: Optional[int] = None
    device_info: Optional[str] = None

class TicketAnalysisResponse(BaseModel):
    suggested_category: str
    suggested_priority: str
    confidence: float
    key_entities: List[str] = []
    immediate_troubleshooting: List[str] = []
    suggested_resolution: Optional[str] = None
    requires_escalation: bool = False
    relevant_policy_citation: Optional[CitationItem] = None

class MultimodalDiagnoseResponse(BaseModel):
    error_title: str
    error_type: str
    detected_error_code: Optional[str] = None
    extracted_text: str
    root_cause_analysis: str
    recommended_priority: str
    recommended_category: str
    step_by_step_troubleshooting: List[str] = []
    requires_ticket: bool = True
    confidence: float = 0.92

class AgentLogItem(BaseModel):
    id: Optional[int] = None
    user_id: Optional[int] = None
    query: str
    assigned_agent: str
    tools_called: List[str] = []
    latency_ms: int = 0
    tokens_used: int = 0
    status: str = "SUCCESS"
    created_at: Optional[str] = None
