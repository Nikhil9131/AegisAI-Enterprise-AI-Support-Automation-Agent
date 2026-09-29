import sys
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

import io
import urllib.request
import json
from PIL import Image, ImageDraw, ImageFont

def create_mock_error_screenshot() -> bytes:
    """Create a realistic diagnostic error dialog image using Pillow."""
    img = Image.new("RGB", (640, 360), color="#1e293b")
    draw = ImageDraw.Draw(img)
    
    # Title bar
    draw.rectangle([0, 0, 640, 40], fill="#0f172a")
    draw.text((20, 12), "Cisco AnyConnect Secure Mobility Client — Connection Error", fill="#f8fafc")
    
    # Error box
    draw.rectangle([40, 70, 600, 290], fill="#334155", outline="#ef4444", width=2)
    draw.text((60, 100), "ERROR: The VPN connection failed due to unsuccessful", fill="#fca5a5")
    draw.text((60, 130), "user authentication or server certificate validation.", fill="#fca5a5")
    draw.text((60, 180), "Diagnostic Code: NET-VPN-800-AUTH", fill="#cbd5e1")
    draw.text((60, 210), "Gateway: vpn.aegis.enterprise:443", fill="#cbd5e1")
    draw.text((60, 240), "Tunnel Protocol: SSL-VPN / DTLS over UDP 443", fill="#94a3b8")
    
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    return buf.getvalue()

def test_multimodal_diagnose():
    img_bytes = create_mock_error_screenshot()
    
    boundary = "----WebKitFormBoundary7MA4YWxkTrZu0gW"
    body = (
        f"--{boundary}\r\n"
        f'Content-Disposition: form-data; name="file"; filename="vpn_error.png"\r\n'
        f"Content-Type: image/png\r\n\r\n"
    ).encode("utf-8") + img_bytes + (
        f"\r\n--{boundary}\r\n"
        f'Content-Disposition: form-data; name="notes"\r\n\r\n'
        f"VPN Error 800 while attempting to connect from home Wi-Fi\r\n"
        f"--{boundary}--\r\n"
    ).encode("utf-8")

    req = urllib.request.Request(
        "http://127.0.0.1:8001/api/v1/multimodal/diagnose",
        data=body,
        headers={"Content-Type": f"multipart/form-data; boundary={boundary}"}
    )

    with urllib.request.urlopen(req) as resp:
        data = json.loads(resp.read().decode("utf-8"))
        print("\n[MULTIMODAL DIAGNOSTIC RESPONSE]:")
        print("Error Title:", data["error_title"])
        print("Detected Code:", data["detected_error_code"])
        print("Category:", data["recommended_category"])
        print("Priority:", data["recommended_priority"])
        print("Root Cause:", data["root_cause_analysis"])
        print("Troubleshooting Steps:")
        for step in data["step_by_step_troubleshooting"]:
            print("  ", step)
        assert data["detected_error_code"] == "NET-VPN-800-AUTH"
        assert data["recommended_priority"] == "HIGH"
        print("\n[PASS] Multimodal image diagnosis passed successfully!")

if __name__ == "__main__":
    test_multimodal_diagnose()
