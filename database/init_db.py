"""
AEGIS AI Enterprise Platform - Database Initializer
Supports MySQL and SQLite with full schema migration and seed population.
"""

import os
import sys
import re
from pathlib import Path
from sqlalchemy import create_engine, text

BASE_DIR = Path(__file__).resolve().parent.parent
SCHEMA_FILE = BASE_DIR / "database" / "schema.sql"
SEEDS_FILE = BASE_DIR / "database" / "seeds.sql"
SQLITE_DB = BASE_DIR / "database" / "aegis.db"

def get_db_url():
    db_type = os.getenv("DB_CONNECTION", "sqlite").lower()
    if db_type == "mysql":
        host = os.getenv("DB_HOST", "localhost")
        port = os.getenv("DB_PORT", "3306")
        user = os.getenv("DB_USERNAME", "root")
        password = os.getenv("DB_PASSWORD", "rootpassword")
        dbname = os.getenv("DB_DATABASE", "aegis_ai")
        return f"mysql+pymysql://{user}:{password}@{host}:{port}/{dbname}"
    else:
        return f"sqlite:///{SQLITE_DB.as_posix()}"

def split_sql_statements(sql_text: str) -> list[str]:
    """Split SQL script into statements respecting quoted strings and comments."""
    statements = []
    current = []
    in_single_quote = False
    in_double_quote = False
    in_line_comment = False
    i = 0
    n = len(sql_text)
    
    while i < n:
        c = sql_text[i]
        
        # Line comment check
        if not in_single_quote and not in_double_quote:
            if c == '-' and i + 1 < n and sql_text[i + 1] == '-':
                # Skip to end of line
                while i < n and sql_text[i] != '\n':
                    i += 1
                current.append('\n')
                i += 1
                continue
            elif c == '#' and (i == 0 or sql_text[i - 1] in ('\n', ' ', '\t')):
                while i < n and sql_text[i] != '\n':
                    i += 1
                current.append('\n')
                i += 1
                continue

        # Single quote toggle
        if c == "'" and not in_double_quote:
            # Check if escaped
            if i > 0 and sql_text[i - 1] == '\\':
                pass
            else:
                in_single_quote = not in_single_quote
            current.append(c)
            i += 1
            continue

        # Double quote toggle
        if c == '"' and not in_single_quote:
            if i > 0 and sql_text[i - 1] == '\\':
                pass
            else:
                in_double_quote = not in_double_quote
            current.append(c)
            i += 1
            continue

        # Semicolon outside quotes
        if c == ';' and not in_single_quote and not in_double_quote:
            stmt = "".join(current).strip()
            if stmt:
                statements.append(stmt)
            current = []
            i += 1
            continue

        current.append(c)
        i += 1

    remaining = "".join(current).strip()
    if remaining:
        statements.append(remaining)
        
    return statements

def sanitize_statement_for_sqlite(stmt: str) -> str:
    s = stmt
    # Remove MySQL engine and charset directives
    s = re.sub(r"ENGINE\s*=\s*\w+", "", s, flags=re.IGNORECASE)
    s = re.sub(r"DEFAULT\s+CHARSET\s*=\s*\w+", "", s, flags=re.IGNORECASE)
    s = re.sub(r"COLLATE\s*=\s*\w+", "", s, flags=re.IGNORECASE)
    # Convert AUTO_INCREMENT
    s = re.sub(r"\bINT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b", "INTEGER PRIMARY KEY AUTOINCREMENT", s, flags=re.IGNORECASE)
    s = re.sub(r"\bAUTO_INCREMENT\b", "", s, flags=re.IGNORECASE)
    # Convert ENUM(...) to VARCHAR(50)
    s = re.sub(r"\bENUM\s*\([^)]*\)", "VARCHAR(50)", s, flags=re.IGNORECASE)
    # Convert MEDIUMTEXT to TEXT
    s = re.sub(r"\bMEDIUMTEXT\b", "TEXT", s, flags=re.IGNORECASE)
    # Convert ON UPDATE CURRENT_TIMESTAMP
    s = re.sub(r"ON\s+UPDATE\s+CURRENT_TIMESTAMP", "", s, flags=re.IGNORECASE)
    # Convert ON DUPLICATE KEY UPDATE to INSERT OR REPLACE
    if "ON DUPLICATE KEY UPDATE" in s.upper():
        s = re.sub(r"\s+ON\s+DUPLICATE\s+KEY\s+UPDATE.*$", "", s, flags=re.IGNORECASE | re.DOTALL)
        s = re.sub(r"^INSERT\s+INTO", "INSERT OR REPLACE INTO", s, flags=re.IGNORECASE)
    # Convert DATE_SUB(NOW(), INTERVAL X UNIT) to datetime('now', '-X unit')
    def replace_date_sub(match):
        val = match.group(1)
        unit = match.group(2).lower()
        return f"datetime('now', '-{val} {unit}')"
    
    s = re.sub(r"DATE_SUB\s*\(\s*NOW\(\)\s*,\s*INTERVAL\s+(\d+)\s+([A-Z]+)\s*\)", replace_date_sub, s, flags=re.IGNORECASE)
    s = re.sub(r"\bNOW\(\)", "datetime('now')", s, flags=re.IGNORECASE)

    return s.strip()

def init_database():
    db_url = get_db_url()
    print(f"[*] Initializing database at: {db_url}")
    
    is_sqlite = db_url.startswith("sqlite")
    
    if is_sqlite:
        SQLITE_DB.parent.mkdir(parents=True, exist_ok=True)
        if SQLITE_DB.exists():
            SQLITE_DB.unlink()
            
    engine = create_engine(db_url, echo=False)
    
    with open(SCHEMA_FILE, "r", encoding="utf-8") as f:
        schema_sql = f.read()
        
    with open(SEEDS_FILE, "r", encoding="utf-8") as f:
        seeds_sql = f.read()
        
    schema_stmts = split_sql_statements(schema_sql)
    seeds_stmts = split_sql_statements(seeds_sql)
    
    with engine.connect() as conn:
        for stmt in schema_stmts:
            if is_sqlite:
                stmt = sanitize_statement_for_sqlite(stmt)
            if stmt:
                conn.execute(text(stmt))
                
        for stmt in seeds_stmts:
            if is_sqlite:
                stmt = sanitize_statement_for_sqlite(stmt)
            if stmt:
                conn.execute(text(stmt))
                
        conn.commit()
        print(f"[+] Successfully initialized {len(schema_stmts)} schema statements and {len(seeds_stmts)} seed statements!")

if __name__ == "__main__":
    init_database()
