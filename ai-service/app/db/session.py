import re
import json
import sqlite3
import pymysql
from typing import List, Dict, Any, Tuple
from app.config import settings

def get_connection():
    """Get active database connection based on environment configuration."""
    if settings.DB_CONNECTION == "mysql":
        return pymysql.connect(
            host=settings.DB_HOST,
            port=settings.DB_PORT,
            user=settings.DB_USERNAME,
            password=settings.DB_PASSWORD,
            database=settings.DB_DATABASE,
            cursorclass=pymysql.cursors.DictCursor
        )
    else:
        from pathlib import Path
        db_path = Path(settings.DB_SQLITE_PATH)
        if not db_path.exists():
            for candidate in [
                Path.cwd() / "database" / "aegis.db",
                Path.cwd().parent / "database" / "aegis.db",
                Path(__file__).resolve().parent.parent.parent / "database" / "aegis.db"
            ]:
                if candidate.exists():
                    db_path = candidate
                    break
        conn = sqlite3.connect(str(db_path))
        conn.row_factory = sqlite3.Row
        return conn

def get_db_schema_summary() -> str:
    """Introspect tables and return a concise schema summary for the SQL Agent."""
    conn = get_connection()
    cursor = conn.cursor()
    summary = []
    
    try:
        if settings.DB_CONNECTION == "mysql":
            cursor.execute("SHOW TABLES")
            tables = [list(r.values())[0] for r in cursor.fetchall()]
            for table in tables:
                cursor.execute(f"DESCRIBE `{table}`")
                cols = cursor.fetchall()
                col_defs = [f"{c['Field']} ({c['Type']})" for c in cols]
                summary.append(f"Table `{table}`: {', '.join(col_defs)}")
        else:
            cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';")
            tables = [r[0] if isinstance(r, tuple) else r["name"] for r in cursor.fetchall()]
            for table in tables:
                cursor.execute(f"PRAGMA table_info(`{table}`)")
                cols = cursor.fetchall()
                col_defs = [f"{c['name']} ({c['type']})" for c in cols]
                summary.append(f"Table `{table}`: {', '.join(col_defs)}")
    finally:
        cursor.close()
        conn.close()
        
    return "\n".join(summary)

# Dangerous SQL patterns strictly prohibited in Read-Only agent
FORBIDDEN_SQL_PATTERNS = [
    r"\bDROP\b",
    r"\bDELETE\b",
    r"\bTRUNCATE\b",
    r"\bALTER\b",
    r"\bUPDATE\b",
    r"\bINSERT\b",
    r"\bCREATE\b",
    r"\bREPLACE\b",
    r"\bGRANT\b",
    r"\bREVOKE\b",
    r"\bEXEC\b",
    r"\bEXECUTE\b",
    r"\bCALL\b",
    r"\bATTACH\b",
    r"\bDETACH\b",
    r"\bPRAGMA\b\s+(?!table_info)"
]

def execute_read_only_query(sql_query: str, max_rows: int = 50) -> Tuple[bool, Any]:
    """
    Safely execute a strictly read-only SQL query against the database.
    Returns (success: bool, result: List[Dict[str, Any]] or error_message: str).
    """
    # 1. Clean query
    cleaned = sql_query.strip().rstrip(";")
    
    # 2. Check forbidden modification keywords
    for pattern in FORBIDDEN_SQL_PATTERNS:
        if re.search(pattern, cleaned, re.IGNORECASE):
            return False, f"SECURITY VIOLATION: Query contains prohibited modification command ({pattern}). Only read-only SELECT queries are permitted."

    # 3. Must start with SELECT, WITH, or EXPLAIN
    if not re.match(r"^\s*(SELECT|WITH|EXPLAIN)\b", cleaned, re.IGNORECASE):
        return False, "SECURITY VIOLATION: Only SELECT or WITH statements are allowed for read-only database exploration."

    # 4. Enforce LIMIT if not present
    if not re.search(r"\bLIMIT\b", cleaned, re.IGNORECASE):
        cleaned += f" LIMIT {max_rows}"

    conn = get_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(cleaned)
        rows = cursor.fetchall()
        
        # Normalize to list of dicts
        results = []
        for row in rows:
            if isinstance(row, dict):
                results.append(row)
            else:
                results.append(dict(row))
        return True, results
    except Exception as e:
        return False, f"SQL Execution Error: {str(e)}"
    finally:
        cursor.close()
        conn.close()

def log_agent_execution(
    query: str,
    routing_intent: str,
    tools_called: List[str],
    latency_ms: int,
    tokens_used: int,
    status: str = "SUCCESS",
    user_id: int = 1,
    model_name: str = "gemini-1.5-pro",
    error_message: str = None
):
    """Log agent execution trace for enterprise observability."""
    conn = get_connection()
    cursor = conn.cursor()
    try:
        tools_json = json.dumps(tools_called)
        success_val = 1 if status == "SUCCESS" else 0
        resp_text = error_message if error_message else f"Executed {routing_intent}"
        
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                INSERT INTO ai_agent_logs (user_id, query, agent_selected, tools_used, response, latency_ms, token_usage, model, success, created_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())
                """,
                (user_id, query, routing_intent, tools_json, resp_text, latency_ms, tokens_used, model_name, success_val)
            )
        else:
            cursor.execute(
                """
                INSERT INTO ai_agent_logs (user_id, query, agent_selected, tools_used, response, latency_ms, token_usage, model, success, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
                """,
                (user_id, query, routing_intent, tools_json, resp_text, latency_ms, tokens_used, model_name, success_val)
            )
        conn.commit()
    except Exception as e:
        print(f"Error logging agent execution: {e}")
    finally:
        cursor.close()
        conn.close()
