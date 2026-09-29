import time
from fastapi import APIRouter
from app.config import settings
from app.rag.retriever import retriever
from app.db.session import get_connection

router = APIRouter(tags=["Health"])

START_TIME = time.time()

@router.get("/health")
async def health_check():
    """Health check endpoint validating DB and Vector DB connectivity."""
    db_status = "UNKNOWN"
    try:
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.fetchone()
        cursor.close()
        conn.close()
        db_status = "CONNECTED"
    except Exception as e:
        db_status = f"ERROR: {str(e)}"

    vector_count = retriever.count()
    uptime_sec = int(time.time() - START_TIME)

    return {
        "status": "HEALTHY",
        "service": "Aegis Enterprise AI Microservice",
        "version": "1.0.0",
        "uptime_seconds": uptime_sec,
        "database": {
            "driver": settings.DB_CONNECTION,
            "status": db_status
        },
        "vector_database": {
            "engine": "Qdrant",
            "collection": settings.QDRANT_COLLECTION,
            "total_vectors": vector_count,
            "mode": "in-memory" if settings.QDRANT_URL == ":memory:" else "persistent"
        },
        "llm_provider": {
            "configured_provider": settings.LLM_PROVIDER,
            "model": settings.DEFAULT_MODEL
        }
    }
