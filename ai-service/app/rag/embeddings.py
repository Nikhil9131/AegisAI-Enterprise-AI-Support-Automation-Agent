from typing import List
from app.config import settings

class EmbeddingService:
    def __init__(self):
        self._provider = None
        self._dimension = 384
        self._init_provider()

    def _init_provider(self):
        # We use FastEmbed by default as it runs 100% locally and fast without external dependencies
        try:
            from fastembed import TextEmbedding
            self._fastembed = TextEmbedding(model_name="BAAI/bge-small-en-v1.5")
            self._provider = "fastembed"
            self._dimension = 384
            print("[EmbeddingService] Initialized FastEmbed (BAAI/bge-small-en-v1.5) - Dimension: 384")
        except Exception as e:
            print(f"[EmbeddingService] FastEmbed initialization fallback: {e}")
            self._provider = "dummy"
            self._dimension = 384

    @property
    def dimension(self) -> int:
        return self._dimension

    def embed_query(self, query: str) -> List[float]:
        """Embed a single search query."""
        if self._provider == "fastembed":
            embeddings = list(self._fastembed.embed([query]))
            return embeddings[0].tolist()
        else:
            # Deterministic hash-based embedding fallback
            import hashlib
            h = hashlib.sha256(query.encode()).digest()
            vec = [float(b) / 255.0 for b in h]
            while len(vec) < self._dimension:
                vec.extend(vec[:min(len(vec), self._dimension - len(vec))])
            return vec[:self._dimension]

    def embed_documents(self, texts: List[str]) -> List[List[float]]:
        """Embed a list of text chunks."""
        if not texts:
            return []
        if self._provider == "fastembed":
            embeddings = list(self._fastembed.embed(texts))
            return [e.tolist() for e in embeddings]
        else:
            return [self.embed_query(t) for t in texts]

embedding_service = EmbeddingService()
