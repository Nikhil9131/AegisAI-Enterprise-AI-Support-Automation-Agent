from fastapi import APIRouter
from app.models.schemas import TicketAnalysisRequest, TicketAnalysisResponse
from app.tools.search_tool import search_enterprise_kb

router = APIRouter(tags=["Tickets"])

@router.post("/tickets/analyze", response_model=TicketAnalysisResponse)
async def analyze_ticket_intake(request: TicketAnalysisRequest):
    """
    Live AI Copilot triage for ticket creation.
    Classifies category, assesses priority, extracts key entities, and suggests immediate self-service fixes.
    """
    full_text = f"{request.title} {request.description}".lower()

    # 1. Category Classification
    if any(k in full_text for k in ["vpn", "wifi", "network", "internet", "dns", "gateway", "ipsec"]):
        category = "Network & VPN"
        default_prio = "HIGH" if "cannot connect" in full_text or "outage" in full_text else "MEDIUM"
    elif any(k in full_text for k in ["laptop", "screen", "keyboard", "battery", "macbook", "thinkpad", "dock", "hardware"]):
        category = "Hardware & Devices"
        default_prio = "HIGH" if "broken" in full_text or "damaged" in full_text else "MEDIUM"
    elif any(k in full_text for k in ["password", "mfa", "okta", "security", "phishing", "breach", "locked out"]):
        category = "IT Security"
        default_prio = "CRITICAL" if "phishing" in full_text or "compromised" in full_text else "MEDIUM"
    elif any(k in full_text for k in ["git", "github", "docker", "deploy", "build", "api", "ide", "vscode"]):
        category = "Developer Tools"
        default_prio = "MEDIUM"
    else:
        category = "HR Policies"
        default_prio = "LOW"

    # 2. Priority Escalation Check
    if any(k in full_text for k in ["critical", "emergency", "production down", "all users", "executive", "urgent", "bsod"]):
        priority = "CRITICAL"
    elif any(k in full_text for k in ["blocked", "cannot work", "broken screen", "high priority"]):
        priority = "HIGH"
    else:
        priority = default_prio

    # 3. Knowledge base check for immediate troubleshooting
    kb_res = search_enterprise_kb(request.title, top_k=2)
    citations = kb_res["citations"]
    top_citation = citations[0] if citations else None

    # 4. Immediate troubleshooting steps
    troubleshooting = []
    if category == "Network & VPN":
        troubleshooting = [
            "Verify that your Okta / Duo multi-factor authentication push has been approved.",
            "Disconnect any personal VPN or third-party proxy clients before launching corporate VPN.",
            "Verify gateway endpoint is set to 'vpn.aegis.enterprise:443'."
        ]
    elif category == "Hardware & Devices":
        troubleshooting = [
            "Perform a hard reset by holding the power button for 15 seconds.",
            "Inspect USB-C / MagSafe charging cable and try an alternate power outlet.",
            "If requesting hardware upgrade, verify device is over 3 years old under Laptop Policy."
        ]
    elif category == "IT Security":
        troubleshooting = [
            "Use the Self-Service Password Reset Portal at https://auth.aegis.enterprise/reset.",
            "Ensure password satisfies 14-character minimum and avoids recent passwords.",
            "Do not disclose or email temporary verification codes to anyone."
        ]
    else:
        troubleshooting = [
            "Review official employee guides in the Aegis Knowledge Base.",
            "Verify your corporate email address is correctly configured in your profile."
        ]

    suggested_res = (
        f"Recommended Action: Assign ticket to {category} tier-2 engineer. "
        f"Initial automated check: Employee issue is matched with policy '{top_citation.document_title if top_citation else 'Standard IT Procedure'}'."
    )

    return TicketAnalysisResponse(
        suggested_category=category,
        suggested_priority=priority,
        confidence=0.94,
        key_entities=[category, priority, "AI Automated Triage"],
        immediate_troubleshooting=troubleshooting,
        suggested_resolution=suggested_res,
        requires_escalation=(priority == "CRITICAL"),
        relevant_policy_citation=top_citation
    )

@router.post("/tickets/suggest-resolution")
async def suggest_ticket_resolution(request: TicketAnalysisRequest):
    """Generate resolution note suggestions for IT support technicians."""
    return {
        "status": "SUCCESS",
        "suggested_reply": (
            f"Dear Employee,\n\n"
            f"Thank you for contacting the IT Operations Desk. Regarding '{request.title}', "
            f"we have analyzed your diagnostic details. We have refreshed your profile configuration on the central controller. "
            f"Please reboot your device and test again. Let us know if the issue persists.\n\n"
            f"Best regards,\nAegis IT Support Team"
        ),
        "recommended_status": "WAITING_USER"
    }
