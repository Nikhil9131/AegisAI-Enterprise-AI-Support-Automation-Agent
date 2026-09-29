from typing import Dict, Any, List
from app.db.session import get_connection, settings

def get_employee(identifier: str) -> Dict[str, Any]:
    """
    Allowlisted API tool to fetch employee record by employee_code, name, or email.
    """
    conn = get_connection()
    cursor = conn.cursor()
    term = f"%{identifier.strip()}%"
    try:
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                SELECT e.*, u.status as user_status
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.employee_code = %s OR e.full_name LIKE %s OR e.email LIKE %s
                LIMIT 5
                """,
                (identifier.strip(), term, term)
            )
        else:
            cursor.execute(
                """
                SELECT e.*, u.status as user_status
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.employee_code = ? OR e.full_name LIKE ? OR e.email LIKE ?
                LIMIT 5
                """,
                (identifier.strip(), term, term)
            )
        rows = cursor.fetchall()
        if not rows:
            return {"found": False, "message": f"No employee found matching '{identifier}'."}
        
        employees = [dict(r) for r in rows]
        
        # Also fetch devices assigned to these employees
        for emp in employees:
            if settings.DB_CONNECTION == "mysql":
                cursor.execute("SELECT * FROM devices WHERE employee_id = %s", (emp["id"],))
            else:
                cursor.execute("SELECT * FROM devices WHERE employee_id = ?", (emp["id"],))
            devs = cursor.fetchall()
            emp["assigned_devices"] = [dict(d) for d in devs]

        return {"found": True, "count": len(employees), "employees": employees}
    finally:
        cursor.close()
        conn.close()

def get_device(identifier: str) -> Dict[str, Any]:
    """
    Allowlisted API tool to fetch device record by serial_number or device_name.
    """
    conn = get_connection()
    cursor = conn.cursor()
    term = f"%{identifier.strip()}%"
    try:
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                SELECT d.*, e.full_name as assigned_to_name, e.email as assigned_to_email, e.department
                FROM devices d
                LEFT JOIN employees e ON d.employee_id = e.id
                WHERE d.serial_number = %s OR d.device_name LIKE %s
                LIMIT 5
                """,
                (identifier.strip(), term)
            )
        else:
            cursor.execute(
                """
                SELECT d.*, e.full_name as assigned_to_name, e.email as assigned_to_email, e.department
                FROM devices d
                LEFT JOIN employees e ON d.employee_id = e.id
                WHERE d.serial_number = ? OR d.device_name LIKE ?
                LIMIT 5
                """,
                (identifier.strip(), term)
            )
        rows = cursor.fetchall()
        if not rows:
            return {"found": False, "message": f"No device found matching '{identifier}'."}
        
        devices = [dict(r) for r in rows]
        return {"found": True, "count": len(devices), "devices": devices}
    finally:
        cursor.close()
        conn.close()
