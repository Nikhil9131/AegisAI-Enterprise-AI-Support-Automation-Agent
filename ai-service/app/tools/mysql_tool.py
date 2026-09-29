from typing import Dict, Any, List
from app.db.session import execute_read_only_query, get_db_schema_summary

def run_readonly_sql(query: str) -> Dict[str, Any]:
    """
    Execute a read-only SQL SELECT query on the enterprise database.
    Strictly forbids DROP, ALTER, DELETE, UPDATE, INSERT, TRUNCATE.
    """
    success, result = execute_read_only_query(query)
    if not success:
        return {
            "success": False,
            "error": result,
            "data": []
        }
    return {
        "success": True,
        "row_count": len(result),
        "data": result
    }

def get_schema_info() -> str:
    """Return database schema information for SQL Agent prompt."""
    return get_db_schema_summary()
