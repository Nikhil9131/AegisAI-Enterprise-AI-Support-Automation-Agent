import re
from typing import Tuple

INJECTION_PATTERNS = [
    r"ignore\s+(all\s+)?(previous|prior|above)\s+instructions?",
    r"disregard\s+(all\s+)?(previous|prior|above)\s+instructions?",
    r"reveal\s+(your\s+)?(system\s+prompt|core\s+instructions|developer\s+mode)",
    r"what\s+(is|are)\s+your\s+(initial|system)\s+instructions?",
    r"jailbreak",
    r"dan\s+mode",
    r"you\s+are\s+now\s+in\s+developer\s+mode",
    r"\b(drop|truncate|delete\s+from|alter)\s+table\b",
    r"\bgrant\s+all\s+privileges\b",
    r"\bmake\s+me\s+an?\s+admin(istrator)?\b",
    r"rm\s+-rf\s+/",
    r"format\s+[a-z]:"
]

def check_prompt_security(query: str) -> Tuple[bool, str]:
    """
    Evaluate user prompt against prompt injection and malicious override patterns.
    Returns (is_safe: bool, reason_or_cleaned: str).
    """
    query_lower = query.lower()
    for pattern in INJECTION_PATTERNS:
        if re.search(pattern, query_lower):
            return False, (
                "🛡️ **AEGIS Security Shield Alert:** Your request has been blocked by enterprise security guardrails. "
                "Attempts to override system instructions, elevate privileges, or perform unauthorized database alterations "
                "are logged and reported to the IT Security & Compliance Operations team."
            )
    return True, query
