import uuid
from typing import List, Dict, Any, Optional
from qdrant_client import QdrantClient
from qdrant_client.http import models as qmodels
from app.config import settings
from app.rag.embeddings import embedding_service

class QdrantRetriever:
    def __init__(self):
        self.collection_name = settings.QDRANT_COLLECTION
        self._init_client()
        self._ensure_collection()

    def _init_client(self):
        """Initialize Qdrant client based on configuration."""
        if settings.QDRANT_URL.startswith("http"):
            print(f"[QdrantRetriever] Connecting to remote Qdrant at {settings.QDRANT_URL}")
            self.client = QdrantClient(
                url=settings.QDRANT_URL,
                api_key=settings.QDRANT_API_KEY if settings.QDRANT_API_KEY else None
            )
        elif settings.QDRANT_URL == ":memory:":
            print("[QdrantRetriever] Running Qdrant in in-memory mode")
            self.client = QdrantClient(location=":memory:")
        else:
            print(f"[QdrantRetriever] Running Qdrant in local storage mode at {settings.QDRANT_STORAGE_PATH}")
            self.client = QdrantClient(path=settings.QDRANT_STORAGE_PATH)

    def _ensure_collection(self):
        """Create collection if it does not already exist."""
        try:
            collections = self.client.get_collections().collections
            exists = any(c.name == self.collection_name for c in collections)
            
            if not exists:
                print(f"[QdrantRetriever] Creating collection: {self.collection_name} (dim: {embedding_service.dimension})")
                self.client.create_collection(
                    collection_name=self.collection_name,
                    vectors_config=qmodels.VectorParams(
                        size=embedding_service.dimension,
                        distance=qmodels.Distance.COSINE
                    )
                )
            else:
                print(f"[QdrantRetriever] Collection {self.collection_name} already exists.")
        except Exception as e:
            print(f"[QdrantRetriever] Error verifying collection: {e}")

    def index_chunks(self, chunks: List[Dict[str, Any]], batch_size: int = 32) -> int:
        """Embed and upsert chunks into Qdrant."""
        if not chunks:
            return 0

        total_indexed = 0
        texts = [c["content"] for c in chunks]

        # Process in batches
        for i in range(0, len(chunks), batch_size):
            batch_chunks = chunks[i : i + batch_size]
            batch_texts = texts[i : i + batch_size]
            batch_vectors = embedding_service.embed_documents(batch_texts)

            points = []
            for chunk, vector in zip(batch_chunks, batch_vectors):
                point_id = str(uuid.uuid5(uuid.NAMESPACE_DNS, f"{chunk['document_title']}_{chunk['chunk_index']}"))
                points.append(
                    qmodels.PointStruct(
                        id=point_id,
                        vector=vector,
                        payload={
                            "document_title": chunk["document_title"],
                            "page": chunk["page"],
                            "section": chunk["section"],
                            "content": chunk["content"],
                            "chunk_index": chunk["chunk_index"]
                        }
                    )
                )

            self.client.upsert(
                collection_name=self.collection_name,
                points=points
            )
            total_indexed += len(points)

        print(f"[QdrantRetriever] Successfully indexed {total_indexed} points.")
        return total_indexed

    def search(
        self,
        query: str,
        top_k: int = 4,
        score_threshold: float = 0.20,
        filter_document: Optional[str] = None
    ) -> List[Dict[str, Any]]:
        """Dense semantic search against Qdrant vector database."""
        query_vector = embedding_service.embed_query(query)
        
        query_filter = None
        if filter_document:
            query_filter = qmodels.Filter(
                must=[
                    qmodels.FieldCondition(
                        key="document_title",
                        match=qmodels.MatchValue(value=filter_document)
                    )
                ]
            )

        # Execute query supporting both modern query_points and legacy search
        try:
            if hasattr(self.client, "query_points"):
                resp = self.client.query_points(
                    collection_name=self.collection_name,
                    query=query_vector,
                    limit=top_k,
                    score_threshold=score_threshold,
                    query_filter=query_filter
                )
                results = resp.points
            else:
                results = self.client.search(
                    collection_name=self.collection_name,
                    query_vector=query_vector,
                    limit=top_k,
                    score_threshold=score_threshold,
                    query_filter=query_filter
                )
        except Exception as e:
            print(f"[QdrantRetriever] Search error: {e}")
            return []

        formatted = []
        for hit in results:
            payload = hit.payload or {}
            formatted.append({
                "document_title": payload.get("document_title", "Unknown"),
                "page": payload.get("page", 1),
                "section": payload.get("section", "General"),
                "content": payload.get("content", ""),
                "score": round(float(hit.score), 4)
            })
            
        return formatted

    def count(self) -> int:
        """Return total vector count in the collection."""
        try:
            info = self.client.get_collection(self.collection_name)
            return info.points_count or 0
        except Exception:
            return 0

retriever = QdrantRetriever()
