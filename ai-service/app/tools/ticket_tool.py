import random
from typing import Dict, Any, Optional
from app.db.session import get_connection, settings

def lookup_ticket(ticket_ref: str) -> Dict[str, Any]:
    """Look up a ticket by its Ticket Number (e.g. TKT-2026-1001) or internal ID."""
    conn = get_connection()
    cursor = conn.cursor()
    try:
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                SELECT t.*, u.full_name as creator_name, u.email as creator_email,
                       a.full_name as assigned_agent_name, c.name as category_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN users a ON t.assigned_to = a.id
                LEFT JOIN knowledge_categories c ON t.category_id = c.id
                WHERE t.ticket_number = %s OR t.id = %s
                LIMIT 1
                """,
                (ticket_ref, ticket_ref)
            )
        else:
            cursor.execute(
                """
                SELECT t.*, u.full_name as creator_name, u.email as creator_email,
                       a.full_name as assigned_agent_name, c.name as category_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN users a ON t.assigned_to = a.id
                LEFT JOIN knowledge_categories c ON t.category_id = c.id
                WHERE t.ticket_number = ? OR t.id = ?
                LIMIT 1
                """,
                (ticket_ref, ticket_ref)
            )
        row = cursor.fetchone()
        if not row:
            return {"found": False, "message": f"Ticket '{ticket_ref}' not found."}
        
        ticket_data = dict(row)
        return {"found": True, "ticket": ticket_data}
    finally:
        cursor.close()
        conn.close()

def create_support_ticket(
    title: str,
    description: str,
    category_id: int = 1,
    priority: str = "MEDIUM",
    user_id: int = 1,
    source: str = "AI_AGENT"
) -> Dict[str, Any]:
    """Create a new support ticket in the database."""
    conn = get_connection()
    cursor = conn.cursor()
    ticket_num = f"TKT-2026-{random.randint(2000, 9999)}"
    
    try:
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                INSERT INTO tickets (ticket_number, user_id, category_id, priority, status, title, description, source, created_at, updated_at)
                VALUES (%s, %s, %s, %s, 'OPEN', %s, %s, %s, NOW(), NOW())
                """,
                (ticket_num, user_id, category_id, priority, title, description, source)
            )
            ticket_id = cursor.lastrowid
        else:
            cursor.execute(
                """
                INSERT INTO tickets (ticket_number, user_id, category_id, priority, status, title, description, source, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'OPEN', ?, ?, ?, datetime('now'), datetime('now'))
                """,
                (ticket_num, user_id, category_id, priority, title, description, source)
            )
            ticket_id = cursor.lastrowid
            
        conn.commit()
        return {
            "success": True,
            "ticket_id": ticket_id,
            "ticket_number": ticket_num,
            "priority": priority,
            "status": "OPEN",
            "message": f"Successfully created support ticket #{ticket_num}."
        }
    except Exception as e:
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        conn.close()
