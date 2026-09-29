import re
from typing import Dict, Any
from app.agents.state import AgentState
from app.tools.api_tool import get_employee, get_device
from app.tools.ticket_tool import lookup_ticket
from app.models.schemas import AgentTraceStep

def run_api_agent(state: AgentState) -> Dict[str, Any]:
    """Execute API Agent: query allowlisted enterprise internal systems."""
    query = state["query"]
    tools_executed = list(state.get("tools_executed", []))
    trace = list(state.get("execution_trace", []))
    
    q_low = query.lower()

    # 1. Ticket lookup
    ticket_match = re.search(r'\b(TKT-\d{4}-\d{4})\b', query, re.IGNORECASE)
    if ticket_match or "ticket" in q_low and any(char.isdigit() for char in query):
        ticket_ref = ticket_match.group(1) if ticket_match else query.split()[-1]
        tools_executed.append("lookup_ticket")
        res = lookup_ticket(ticket_ref)
        trace.append(
            AgentTraceStep(
                agent_name="API Agent",
                thought=f"Looking up ticket details for reference: {ticket_ref}",
                tool_called="lookup_ticket",
                tool_input={"ticket_ref": ticket_ref},
                tool_output=res
            )
        )
        if res["found"]:
            t = res["ticket"]
            answer = (
                f"🎫 **Ticket Details — #{t['ticket_number']}**\n\n"
                f"- **Title:** {t['title']}\n"
                f"- **Category:** {t.get('category_name', 'General IT')}\n"
                f"- **Priority:** `{t['priority']}`\n"
                f"- **Current Status:** `{t['status']}`\n"
                f"- **Requester:** {t.get('creator_name', 'Employee')} ({t.get('creator_email', '')})\n"
                f"- **Assigned Agent:** {t.get('assigned_agent_name') or 'Unassigned'}\n"
                f"- **Created At:** {t['created_at']}\n\n"
                f"**Description:**\n> {t['description']}"
            )
        else:
            answer = f"No support ticket found matching '{ticket_ref}'."

        return {
            "assigned_agent": "API Agent",
            "tools_executed": tools_executed,
            "execution_trace": trace,
            "final_answer": answer,
            "tokens_used": state.get("tokens_used", 0) + 160
        }

    # 2. Device lookup
    device_match = re.search(r'\b(DEV-\d+|SN-[A-Z0-9]+)\b', query, re.IGNORECASE)
    if device_match or "device" in q_low or "laptop" in q_low or "asset" in q_low:
        dev_ref = device_match.group(1) if device_match else query.split()[-1]
        tools_executed.append("get_device")
        res = get_device(dev_ref)
        trace.append(
            AgentTraceStep(
                agent_name="API Agent",
                thought=f"Querying hardware asset inventory for: {dev_ref}",
                tool_called="get_device",
                tool_input={"identifier": dev_ref},
                tool_output=res
            )
        )
        if res["found"]:
            d = res["devices"][0]
            vpn_status = "Enabled" if d.get("vpn_access_enabled") else "Disabled"
            answer = (
                f"💻 **Device Asset Record — {d['device_name']}**\n\n"
                f"- **Serial Number:** `{d['serial_number']}`\n"
                f"- **Device Type:** {d['device_type']}\n"
                f"- **Operating System:** {d['os_version']}\n"
                f"- **Assigned To:** {d.get('assigned_to_name') or 'Inventory Pool'}\n"
                f"- **Compliance Status:** `{d['compliance_status']}`\n"
                f"- **VPN Gateway Access:** `{vpn_status}`\n"
                f"- **IP / MAC Address:** {d.get('ip_address') or 'N/A'} ({d.get('mac_address') or 'N/A'})"
            )
            return {
                "assigned_agent": "API Agent",
                "tools_executed": tools_executed,
                "execution_trace": trace,
                "final_answer": answer,
                "tokens_used": state.get("tokens_used", 0) + 170
            }

    # 3. Employee lookup
    emp_match = re.search(r'\b(EMP-\d+)\b', query, re.IGNORECASE)
    emp_ref = emp_match.group(1) if emp_match else query.split()[-1]
    # If query mentions common names
    for name in ["rahul", "priya", "alex", "sarah", "marcus", "elena", "david", "fatima", "chloe", "alexander"]:
        if name in q_low:
            emp_ref = name
            break

    tools_executed.append("get_employee")
    res = get_employee(emp_ref)
    trace.append(
        AgentTraceStep(
            agent_name="API Agent",
            thought=f"Looking up enterprise employee directory for: {emp_ref}",
            tool_called="get_employee",
            tool_input={"identifier": emp_ref},
            tool_output=res
        )
    )
    if res["found"]:
        emp = res["employees"][0]
        dev_lines = ""
        if emp.get("assigned_devices"):
            dev_lines = "\n\n**Assigned Hardware Assets:**\n" + "\n".join(
                [f"- {d['device_name']} (`{d['serial_number']}`) — OS: {d['os_version']} [{d['compliance_status']}]" for d in emp["assigned_devices"]]
            )
            
        answer = (
            f"👤 **Employee Profile — {emp['full_name']}**\n\n"
            f"- **Employee Code:** `{emp['employee_code']}`\n"
            f"- **Job Title:** {emp['job_title']}\n"
            f"- **Department:** {emp['department']}\n"
            f"- **Email:** {emp['email']}\n"
            f"- **Office Location:** {emp['office_location']}\n"
            f"- **Manager:** {emp['manager_name']}\n"
            f"- **Account Status:** `{emp['status']}`"
            f"{dev_lines}"
        )
    else:
        answer = f"No enterprise employee or resource found matching query '{query}'."

    return {
        "assigned_agent": "API Agent",
        "tools_executed": tools_executed,
        "execution_trace": trace,
        "final_answer": answer,
        "tokens_used": state.get("tokens_used", 0) + 180
    }
