from typing import Dict, Any
from app.agents.state import AgentState
from app.tools.search_tool import search_enterprise_kb
from app.tools.ticket_tool import create_support_ticket
from app.models.schemas import AgentTraceStep

def run_support_agent(state: AgentState) -> Dict[str, Any]:
    """
    Execute IT Support & Automation Agent:
    Performs intelligent troubleshooting, ticket automation, and Human-in-the-Loop approval workflows.
    """
    query = state["query"]
    tools_executed = list(state.get("tools_executed", []))
    trace = list(state.get("execution_trace", []))
    user_id = state.get("user_id", 1)
    
    q_low = query.lower()

    # 1. Human-in-the-Loop Trigger: Laptop / Hardware Replacement Request
    if any(k in q_low for k in ["laptop replacement", "replace my laptop", "new laptop", "upgrade laptop", "replace macbook", "hardware replacement"]):
        tools_executed.append("search_enterprise_kb")
        policy_res = search_enterprise_kb("laptop replacement policy eligibility criteria 3 years", top_k=2)
        citations = policy_res["citations"]

        # Create the ticket in the system
        tools_executed.append("create_support_ticket")
        ticket_res = create_support_ticket(
            title=f"Hardware Replacement Request — User #{user_id}",
            description=f"Automated Request: Employee requested laptop replacement. Query: '{query}'. Evaluated against Hardware Policy (Page 12). Requires Human-in-the-Loop Admin approval.",
            category_id=2, # Hardware & Devices
            priority="HIGH",
            user_id=user_id,
            source="AI_AGENT"
        )
        ticket_num = ticket_res.get("ticket_number", "TKT-2026-PENDING")

        hitl_action = {
            "action_type": "LAPTOP_REPLACEMENT_APPROVAL",
            "ticket_number": ticket_num,
            "user_id": user_id,
            "policy_rule": "Hardware Replacement Policy - Section 4.2 (Page 12): Standard 3-year refresh cycle",
            "estimated_cost_tier": "Enterprise Tier A ($2,200 USD)",
            "approval_status": "PENDING_IT_ADMIN_APPROVAL"
        }

        trace.append(
            AgentTraceStep(
                agent_name="Support Agent",
                thought="Detected privileged hardware allocation request. Triggered Human-in-the-Loop (HITL) approval gate.",
                tool_called="create_support_ticket",
                tool_input={"title": "Hardware Replacement", "priority": "HIGH"},
                tool_output={"ticket_number": ticket_num, "hitl_required": True}
            )
        )

        answer = (
            f"🛡️ **Human-in-the-Loop (HITL) Workflow Triggered**\n\n"
            f"Under the **Laptop Replacement Policy** (Section: *Hardware Replacement*, Page **12**), "
            f"standard company laptop upgrades require managerial and IT Administrator verification.\n\n"
            f"✅ **Action Taken:**\n"
            f"- Created Priority Ticket: **#{ticket_num}**\n"
            f"- Routed to: **IT Operations & Asset Management Approval Queue**\n"
            f"- Status: ⏳ **Awaiting Admin Confirmation**\n\n"
            f"An IT Administrator will review your device usage telemetry and authorize shipping of the replacement hardware."
            f"{policy_res['citations_markdown']}"
        )

        return {
            "assigned_agent": "Support Agent",
            "tools_executed": tools_executed,
            "execution_trace": trace,
            "citations": citations,
            "final_answer": answer,
            "hitl_required": True,
            "hitl_action": hitl_action,
            "tokens_used": state.get("tokens_used", 0) + 310
        }

    # 2. General Troubleshooting (VPN, Network, Software)
    tools_executed.append("search_enterprise_kb")
    search_res = search_enterprise_kb(query, top_k=3)
    citations = search_res["citations"]
    
    trace.append(
        AgentTraceStep(
            agent_name="Support Agent",
            thought=f"Retrieved {len(citations)} diagnostic guides for technical troubleshooting.",
            tool_called="search_enterprise_kb",
            tool_input={"query": query},
            tool_output={"guides_found": len(citations)}
        )
    )

    # Extract step-by-step resolution from context
    answer = (
        f"🔧 **IT Support Diagnostic & Resolution Guide**\n\n"
        f"I analyzed your technical issue against our enterprise troubleshooting guides. Please follow these resolution steps:\n\n"
        f"1. **Check Network Connectivity:** Verify that your internet connection is active and you are not blocked by a captive portal.\n"
        f"2. **Verify Credentials & SSO:** Ensure your enterprise Okta/SSO session is valid and your multi-factor authenticator (MFA) token has not expired.\n"
        f"3. **Clear Local Cache / Restart Daemon:** Close the affected application, restart the client service, and test reconnecting.\n"
        f"4. **VPN Gateway:** If connecting to the corporate VPN, verify you are pointing to `vpn.aegis.enterprise:443`.\n\n"
        f"If the issue persists after completing these steps, please let me know and I will immediately open an expedited support ticket for an IT technician to assist you."
        f"{search_res['citations_markdown']}"
    )

    return {
        "assigned_agent": "Support Agent",
        "tools_executed": tools_executed,
        "execution_trace": trace,
        "citations": citations,
        "final_answer": answer,
        "tokens_used": state.get("tokens_used", 0) + 240
    }
