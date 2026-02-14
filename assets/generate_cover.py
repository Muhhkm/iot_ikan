#!/usr/bin/env python3
"""
Generate cover.png from SVG for AquaSmart website
Requires: pip install cairosvg pillow
"""

import os
import sys

try:
    import cairosvg
    from PIL import Image
    import io
except ImportError:
    print("❌ Error: Pastikan sudah install dependencies:")
    print("   pip install cairosvg pillow")
    sys.exit(1)

def generate_cover_png():
    """Generate PNG from SVG"""
    svg_path = os.path.join(os.path.dirname(__file__), 'cover.svg')
    png_path = os.path.join(os.path.dirname(__file__), 'cover.png')
    
    if not os.path.exists(svg_path):
        print(f"❌ File tidak ditemukan: {svg_path}")
        return False
    
    try:
        print(f"📦 Membaca: {svg_path}")
        
        # Convert SVG to PNG
        print("🎨 Mengonversi SVG ke PNG (512x512)...")
        cairosvg.svg2png(
            url=svg_path,
            write_to=png_path,
            output_width=512,
            output_height=512
        )
        
        print(f"✅ Berhasil membuat: {png_path}")
        
        # Verify
        if os.path.exists(png_path):
            size = os.path.getsize(png_path)
            print(f"📊 Ukuran file: {size / 1024:.1f} KB")
            return True
        else:
            print("❌ File gagal dibuat")
            return False
            
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == '__main__':
    success = generate_cover_png()
    sys.exit(0 if success else 1)
