from PIL import Image
import sys

try:
    img_path = "C:/Users/USERR/.gemini/antigravity/brain/89321786-bfeb-4cf8-a7ac-a5266886dd5d/uploaded_media_1770136734106.png"
    img = Image.open(img_path)
    # Get top left pixel color
    pixel = img.getpixel((0, 0))
    # Convert to hex
    if len(pixel) == 4:
        r, g, b, a = pixel
    else:
        r, g, b = pixel
    
    hex_color = '#{:02x}{:02x}{:02x}'.format(r, g, b)
    print(f"HEX: {hex_color}")
except Exception as e:
    print(f"Error: {e}")
