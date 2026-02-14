# Cara Membuat Cover.png untuk AquaSmart

Ada beberapa cara untuk membuat cover.png:

## Cara 1: Online Converter (Paling Mudah)
1. Buka https://cloudconvert.com/svg-to-png
2. Upload file `assets/cover.svg`
3. Set resolusi ke 512x512 pixels
4. Download hasilnya sebagai `cover.png`
5. Ganti file di `assets/cover.png`

## Cara 2: Menggunakan ImageMagick (Jika terinstall)
```bash
convert assets/cover.svg -density 150 -resize 512x512 assets/cover.png
```

## Cara 3: Menggunakan Inkscape
1. Buka `assets/cover.svg` dengan Inkscape
2. File → Export As
3. Set format ke PNG
4. Set resolusi ke 512x512
5. Simpan sebagai `assets/cover.png`

## Cara 4: Browser Method
1. Buka `assets/create_icon.html` di browser
2. File akan otomatis di-download sebagai `cover.png`
3. Pindahkan ke folder `assets/`

## Hasil yang Diharapkan
- Ukuran: 512x512 pixels atau minimal 192x192
- Format: PNG dengan background transparent atau gradient
- Konten: Logo AquaSmart dengan ikan dan gelembung air
- Warna: Blue gradient (#0c50fd ke #4a7bd8)

## File yang Sudah Disiapkan
- ✅ `assets/cover.svg` - Source file SVG
- ✅ `assets/create_icon.html` - HTML Canvas generator
- ✅ `dashboard.php` - Sudah menambahkan favicon link

Setelah cover.png siap, favicon akan muncul di:
- Tab browser
- Bookmarks
- History
- iOS Home Screen
