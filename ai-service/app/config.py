import os
from pathlib import Path
from typing import Optional
from pydantic_settings import BaseSettings

# Resolve paths
ROOT_DIR = Path(__file__).resolve().parent.parent.parent
AI_SERVICE_DIR = Path(__file__).resolve().parent.parent
ENV_FILE = ROOT_DIR / ".env"

class Settings(BaseSettings):
    # App
    APP_ENV: str = "development"
    APP_DEBUG: bool = True
    APP_URL: str = "http://localhost:8000"
    AI_SERVICE_URL: str = "http://localhost:8001"

    # Database
    DB_CONNECTION: str = "sqlite"
    DB_HOST: str = "127.0.0.1"
    DB_PORT: int = 3306
    DB_DATABASE: str = "aegis_ai"
    DB_USERNAME: str = "root"
    DB_PASSWORD: str = "rootpassword"
    DB_SQLITE_PATH: str = str(ROOT_DIR / "database" / "aegis.db")

    # LLM Provider
    LLM_PROVIDER: str = "gemini"
    OPENAI_API_KEY: Optional[str] = None
    GEMINI_API_KEY: Optional[str] = None
    ANTHROPIC_API_KEY: Optional[str] = None
    DEFAULT_MODEL: str = "gemini-1.5-pro"

    # Vector DB
    QDRANT_URL: str = ":memory:"
    QDRANT_API_KEY: Optional[str] = None
    QDRANT_COLLECTION: str = "aegis_enterprise_kb"
    QDRANT_STORAGE_PATH: str = str(AI_SERVICE_DIR / "data" / "qdrant")

    # Security & Guardrails
    JWT_SECRET: str = "aegis_enterprise_super_secret_jwt_key_2026_change_in_production"
    JWT_EXPIRATION_HOURS: int = 24
    CODEIGNITER_ENCRYPTION_KEY: str = "aegis_ci3_aes256_encryption_key_2026"
    HITL_APPROVAL_THRESHOLD: str = "HIGH"

    # Directories
    SAMPLE_DOCS_DIR: str = str(ROOT_DIR / "sample_docs")

    class Config:
        env_file = str(ENV_FILE)
        env_file_encoding = "utf-8"
        extra = "ignore"

settings = Settings()
