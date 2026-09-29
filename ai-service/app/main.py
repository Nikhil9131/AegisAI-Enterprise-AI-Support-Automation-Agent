from pathlib import Path
from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import RedirectResponse

from app.config import settings
from app.rag.retriever import retriever
from app.rag.ingestion import ingest_directory

import sys
if hasattr(sys.stdout, "reconfigure"):
    try:
        sys.stdout.reconfigure(encoding="utf-8")
    except Exception:
        pass

# Import routers
from app.api.routes import health, chat, rag, documents, tickets, multimodal, agents

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application startup and shutdown lifecycle management."""
    print("=" * 70)
    print("[START] Initializing Aegis AI Enterprise Microservice...")
    print(f"[ENV] Environment: {settings.APP_ENV} | Debug: {settings.APP_DEBUG}")
    print(f"[DB] Database: {settings.DB_CONNECTION} | Vector Engine: Qdrant ({settings.QDRANT_COLLECTION})")
    
    # Check if vector DB needs initial sample documents ingestion
    current_vectors = retriever.count()
    print(f"[VECTORS] Current vectors in collection: {current_vectors}")
    
    if current_vectors == 0:
        sample_dir = Path(settings.SAMPLE_DOCS_DIR)
        if not sample_dir.exists():
            for candidate in [
                Path.cwd() / "sample_docs",
                Path.cwd().parent / "sample_docs",
                Path(__file__).resolve().parent.parent.parent / "sample_docs",
                Path("/sample_docs")
            ]:
                if candidate.exists():
                    sample_dir = candidate
                    break

        if sample_dir.exists():
            print(f"[INGEST] Indexing sample enterprise knowledge base from: {sample_dir}")
            chunks = ingest_directory(sample_dir)
            if chunks:
                indexed = retriever.index_chunks(chunks)
                print(f"[OK] Successfully seeded {indexed} knowledge chunks into Qdrant!")
        else:
            print(f"[WARN] Sample docs directory not found at: {sample_dir}")
    else:
        print(f"[OK] Knowledge base already indexed with {current_vectors} vectors.")
        
    print("[READY] Aegis AI Microservice is ready for enterprise requests!")
    print("=" * 70)
    yield
    print("[STOP] Shutting down Aegis AI Microservice...")

app = FastAPI(
    title="AEGIS AI — Enterprise AI Support & Automation Service",
    description="Multi-Agent AI Platform featuring LangGraph, RAG with Qdrant, SQL Agent, Multimodal Diagnostics, and HITL Guardrails.",
    version="1.0.0",
    lifespan=lifespan
)

# CORS middleware for CodeIgniter 3 frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount API Routers
app.include_router(health.router, prefix="/api/v1")
app.include_router(chat.router, prefix="/api/v1")
app.include_router(rag.router, prefix="/api/v1")
app.include_router(documents.router, prefix="/api/v1")
app.include_router(tickets.router, prefix="/api/v1")
app.include_router(multimodal.router, prefix="/api/v1")
app.include_router(agents.router, prefix="/api/v1")

@app.get("/", include_in_schema=False)
async def root():
    """Redirect root to OpenAPI interactive documentation."""
    return RedirectResponse(url="/docs")
