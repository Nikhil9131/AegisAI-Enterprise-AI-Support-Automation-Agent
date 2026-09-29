from typing import Optional
from app.config import settings

def get_chat_model():
    """Instantiate LangChain Chat Model according to configuration."""
    if settings.LLM_PROVIDER == "openai" and settings.OPENAI_API_KEY:
        try:
            from langchain_openai import ChatOpenAI
            return ChatOpenAI(
                model=settings.DEFAULT_MODEL if "gpt" in settings.DEFAULT_MODEL else "gpt-4o-mini",
                api_key=settings.OPENAI_API_KEY,
                temperature=0.2
            )
        except Exception as e:
            print(f"[LLMFactory] OpenAI init error: {e}")

    elif settings.LLM_PROVIDER == "gemini" and settings.GEMINI_API_KEY:
        try:
            from langchain_google_genai import ChatGoogleGenerativeAI
            return ChatGoogleGenerativeAI(
                model=settings.DEFAULT_MODEL if "gemini" in settings.DEFAULT_MODEL else "gemini-1.5-pro",
                google_api_key=settings.GEMINI_API_KEY,
                temperature=0.2
            )
        except Exception as e:
            print(f"[LLMFactory] Gemini init error: {e}")

    elif settings.LLM_PROVIDER == "anthropic" and settings.ANTHROPIC_API_KEY:
        try:
            from langchain_anthropic import ChatAnthropic
            return ChatAnthropic(
                model="claude-3-5-sonnet-20241022",
                api_key=settings.ANTHROPIC_API_KEY,
                temperature=0.2
            )
        except Exception as e:
            print(f"[LLMFactory] Anthropic init error: {e}")

    return None

chat_model = get_chat_model()
