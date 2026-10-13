# Design Workspace (static HTML prototype)

Tujuan: desain **general** (belum dinamis) untuk disetujui sebelum implementasi Next.js. Detail acuan: `../docs/PLAN.md` §4–§6.

## Struktur
```
design/
  assets/css/tokens.css      ← DILARANG diubah subagent (milik PM)
  assets/css/base.css        ← DILARANG diubah subagent (milik PM)
  assets/css/<page>.css      ← CSS khusus halaman, milik tiap subagent
  assets/js/<page>.js        ← opsional, vanilla JS minimal
  public/*.html              ← situs publik
  admin/*.html               ← admin CMS
  index.html                 ← indeks semua halaman (milik PM)
```

## Aturan wajib
1. Setiap HTML memuat: `<link rel="stylesheet" href="../assets/css/tokens.css">` lalu `base.css`, lalu CSS halaman sendiri.
2. Warna/spacing/font hanya via CSS variable dari `tokens.css`. Tidak ada hex baru kecuali di file CSS halaman dengan alasan jelas.
3. Pakai class dari `base.css` (`.btn`, `.card`, `.badge`, `.grid--3`, `.section`, `.container`, `.ph`, `.field`, `.input`, `.table`, dll). Baca file itu dulu.
4. Tidak ada foto asli: gunakan `.ph` (+ `data-label`) atau SVG inline sederhana. Tidak ada gambar eksternal.
5. Semua teks Bahasa Indonesia, mengikuti copy di `docs/PLAN.md` §4. Angka/nama klien/harga = placeholder, tulis jelas seperti `XXX`, `Rp X`, `Klien 1`.
6. Mobile-first, harus rapi di 375px dan 1280px. Semantik HTML (header/nav/main/section/footer), alt/aria, fokus terlihat.
7. Navbar & footer HTML identik di semua halaman publik (salin dari `public/_partials/nav-footer.html`).
8. Tidak ada framework/build step/CDN selain Google Fonts yang sudah di base.css. JS vanilla, seminimal mungkin.
9. Jangan menyentuh file di luar slice milikmu.
10. Jangan jalankan formatter/lint/build. Selesai → balas ringkas: file dibuat, keputusan desain, hal yang belum selesai.
