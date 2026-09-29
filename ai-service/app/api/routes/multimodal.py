from typing import Optional
from fastapi import APIRouter, UploadFile, File, Form, HTTPException
from app.models.schemas import MultimodalDiagnoseResponse
from app.multimodal.vision import analyze_error_screenshot

router = APIRouter(tags=["Multimodal"])

@router.post("/multimodal/diagnose", response_model=MultimodalDiagnoseResponse)
async def diagnose_screenshot(
    file: UploadFile = File(...),
    notes: Optional[str] = Form(None)
):
    """
    Multimodal visual diagnostic endpoint.
    Accepts error screenshots, BSOD captures, or dialog photos, and returns root-cause analysis and troubleshooting.
    """
    allowed_types = ["image/jpeg", "image/png", "image/webp", "image/bmp", "image/gif"]
    if file.content_type and file.content_type not in allowed_types:
        raise HTTPException(status_code=400, detail=f"Invalid image format: {file.content_type}")

    image_bytes = await file.read()
    if len(image_bytes) == 0:
        raise HTTPException(status_code=400, detail="Uploaded file is empty.")

    diagnosis = analyze_error_screenshot(
        image_bytes=image_bytes,
        filename=file.filename or "screenshot.png",
        user_notes=notes
    )

    return diagnosis
