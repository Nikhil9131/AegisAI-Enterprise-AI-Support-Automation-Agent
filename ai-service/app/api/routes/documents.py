import shutil
from pathlib import Path
from fastapi import APIRouter, UploadFile, File, Form, HTTPException
from app.config import settings
from app.rag.ingestion import ingest_file
from app.rag.retriever import retriever
from app.db.session import get_connection

router = APIRouter(tags=["Documents"])

UPLOAD_DIR = Path(__file__).resolve().parent.parent.parent.parent / "uploads"
UPLOAD_DIR.mkdir(parents=True, exist_ok=True)

@router.post("/documents/upload")
async def upload_document(
    file: UploadFile = File(...),
    category_id: int = Form(1),
    title: str = Form(None)
):
    """Upload PDF, DOCX, or TXT document, chunk, embed, and index into Qdrant."""
    allowed_exts = [".txt", ".pdf", ".docx", ".md"]
    file_ext = Path(file.filename).suffix.lower()
    if file_ext not in allowed_exts:
        raise HTTPException(status_code=400, detail=f"Unsupported format. Allowed: {allowed_exts}")

    target_path = UPLOAD_DIR / file.filename
    with open(target_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    # 1. Parse and chunk document
    doc_title = title or target_path.stem.replace("_", " ")
    chunks = ingest_file(target_path)
    
    # 2. Index into Qdrant
    indexed_count = retriever.index_chunks(chunks)

    # 3. Store in database
    conn = get_connection()
    cursor = conn.cursor()
    try:
        file_size = target_path.stat().st_size
        if settings.DB_CONNECTION == "mysql":
            cursor.execute(
                """
                INSERT INTO documents (category_id, title, file_name, file_path, file_size, mime_type, chunk_count, is_indexed, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, 1, NOW(), NOW())
                """,
                (category_id, doc_title, file.filename, str(target_path), file_size, file.content_type, len(chunks))
            )
        else:
            cursor.execute(
                """
                INSERT INTO documents (category_id, title, file_name, file_path, file_size, mime_type, chunk_count, is_indexed, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))
                """,
                (category_id, doc_title, file.filename, str(target_path), file_size, file.content_type, len(chunks))
            )
        conn.commit()
    except Exception as e:
        print(f"[Documents] DB Insert Error: {e}")
    finally:
        cursor.close()
        conn.close()

    return {
        "success": True,
        "message": f"Successfully indexed '{doc_title}' into Qdrant vector database.",
        "filename": file.filename,
        "chunks_indexed": indexed_count,
        "total_collection_vectors": retriever.count()
    }

@router.get("/documents")
async def list_documents():
    """List all indexed enterprise documents from database."""
    conn = get_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT id, title, file_name, file_size, chunk_count, is_indexed, created_at FROM documents ORDER BY id DESC")
        rows = cursor.fetchall()
        docs = [dict(r) for r in rows]
        return {
            "total_documents": len(docs),
            "vector_store_points": retriever.count(),
            "documents": docs
        }
    finally:
        cursor.close()
        conn.close()
