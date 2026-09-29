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
            print(f"[LLMFactory] langchain_google_genai not found or failed: {e}. Trying direct google.generativeai...")
            try:
                import google.generativeai as genai
                genai.configure(api_key=settings.GEMINI_API_KEY)
                class DirectGeminiModel:
                    def __init__(self, model_name):
                        m_name = model_name if "gemini" in model_name else "gemini-1.5-pro"
                        self.model = genai.GenerativeModel(m_name)
                    def invoke(self, messages):
                        prompt = "\n\n".join([m.content for m in messages])
                        res = self.model.generate_content(prompt)
                        class Wrapper:
                            content = res.text
                        return Wrapper()
                return DirectGeminiModel(settings.DEFAULT_MODEL)
            except Exception as e2:
                print(f"[LLMFactory] Gemini direct init error: {e2}")

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
