# Rencana Proyek: Company Profile + CMS + Penjualan Layanan
**Niche:** Fotografi & videografi pertambangan (darat, aerial/drone, dokumentasi operasional)
**Working name (placeholder, ganti sesuai pilihan teman Anda):** *Strata Visual*
**Referensi:** origya.com — diambil **struktur informasi & konsepnya saja** (lihat §1), bukan teks/visual/kode.

---

## 0. Catatan Penting
- Semua angka (jumlah proyek, klien, kepuasan), nama klien, testimoni, harga di dokumen ini adalah **PLACEHOLDER**. Jangan dipublikasikan sebelum diganti data nyata. Jangan pakai logo/nama klien tambang tanpa izin tertulis.
- Origya sendiri masih memakai placeholder (Client 1–8, Ahmad Rizki, dsb.). Jangan ditiru; di sini semua diisi konten niche tambang.
- Regulasi drone (izin terbang, zona terlarang, KKOP) dan aturan akses area tambang (induksi K3, SIMPER/izin masuk, aturan foto di area terbatas) **harus diverifikasi** dengan regulasi terbaru dan masing-masing klien. Dokumen ini tidak menjadi rujukan hukum.

---

## 1. Analisis Referensi (origya.com) → Yang Diadopsi vs Diubah

| Aspek di Origya | Diadopsi sebagai konsep | Diubah untuk niche tambang |
|---|---|---|
| Hero "Wujudkan Anda" + 2 CTA (Mulai Proyek / Lihat Portofolio) | Hero singkat, 1 klaim, 2 CTA | Hero visual aerial tambang; klaim fokus ke *dokumentasi yang akurat & aman* |
| Stat bar (proyek, klien, kepuasan) | Bukti sosial angka | Ganti metrik: jam terbang, site yang didokumentasikan, jam tanpa insiden |
| Marquee logo klien | Social proof | Logo kontraktor/PT tambang (hanya yang izin) |
| Showreel video | Showreel autoplay muted | Showreel 60 detik: aerial pit, hauling, plant, reklamasi |
| Portofolio unggulan | Grid proyek + halaman filter | Kategori per jenis kerja & komoditas |
| "Dua pilar" layanan | Layanan utama berpasangan | Tiga pilar: Foto, Video, Aerial/Drone |
| Halaman layanan detail | Why us / fitur / CTA | Tambah: use-case, deliverable, prasyarat lokasi |
| Paket harga (Starter/Growth/Pro/Enterprise) | Tabel paket + "custom" | Paket berbasis hari kerja/site, bukan paket branding umum |
| Berita / Artikel / Event | Konten SEO & kredibilitas | Artikel edukasi dokumentasi tambang; event = jadwal pameran/workshop |
| Tentang kami + nilai + tim | Story, values, team | Nilai: Safety first, akurasi, kerahasiaan data, tepat waktu |
| CTA WhatsApp + form kontak | Konversi cepat | Tambah form *Request Quote* terstruktur (lokasi, jenis kerja, tanggal) |
| CMS & admin (disebut di layanan web) | Konten dikelola sendiri | Admin lengkap + modul penjualan (lihat §6–8) |

**Kekurangan Origya yang tidak ditiru:** konten halaman paket & layanan masih template generik berbahasa Inggris campur Indonesia; canonical semua halaman mengarah ke homepage (buruk untuk SEO); tidak ada alur order/pembayaran nyata.

---

## 2. Positioning

**Siapa target:** (1) Perusahaan tambang & kontraktor (batubara, nikel, emas, mineral lain), (2) kontraktor jasa pertambangan (hauling, drilling, crushing), (3) vendor alat berat/dealer, (4) konsultan lingkungan/ESG, (5) humas/CSR/HSE yang butuh dokumentasi.

**Value proposition (draft):**
> Dokumentasi visual tambang yang akurat, aman, dan siap dipakai — dari laporan progres hingga company profile — oleh fotografer yang paham lapangan.

**Diferensiasi (diisi sesuai fakta teman Anda):**
- Pengalaman langsung di operasi tambang (paham K3, rute, rambu, etika di area kerja).
- Memiliki sertifikat/induksi/izin apa saja → *tanya teman Anda*.
- Aerial: model drone, sertifikasi pilot, asuransi → *tanya teman Anda*.
- Output siap pakai: orthomosaic/foto berkoordinat (jika punya kemampuan), format laporan, penamaan file terstruktur.

**Tone:** profesional, tenang, teknis tapi mudah dipahami; bukan flamboyan. Bahasa Indonesia utama, English toggle fase 2.

---

## 3. Sitemap

**Publik**
```
/                       Beranda
/layanan                Daftar layanan
/layanan/[slug]         Detail layanan (+ tombol Pesan / Minta Penawaran)
/portofolio             Grid + filter (kategori, komoditas, tahun)
/portofolio/[slug]      Studi kasus
/paket                  Paket & harga
/pesan                  Checkout/booking (pilih layanan/paket → form → konfirmasi)
/artikel                Insight (SEO)
/artikel/[slug]
/berita                 Berita perusahaan
/event                  Pameran, workshop, jadwal
/tentang                Tentang & tim
/kontak                 Kontak + peta + WhatsApp
/faq                    Pertanyaan umum (izin, K3, kerahasiaan, revisi)
/klien/masuk            Portal klien (lihat & unduh hasil)
/klien/[order]          Detail order, galeri, invoice
/legal/privasi, /legal/syarat
```

**Admin** `/admin` (lihat §6)

---

## 4. Konten Per Halaman (Draft Copy)

### 4.1 Beranda
1. **Navbar:** Logo · Layanan · Portofolio · Paket · Artikel · Tentang · Kontak · [Minta Penawaran]
2. **Hero**
   - Eyebrow: `Fotografi & Videografi Pertambangan`
   - H1: **Lihat Tambang Anda dengan Jelas.**
   - Sub: *Dokumentasi foto, video, dan aerial untuk laporan progres, company profile, K3, dan keberlanjutan — dikerjakan oleh fotografer yang terbiasa bekerja di area tambang.*
   - CTA primer: `Minta Penawaran` → /pesan · CTA sekunder: `Lihat Portofolio`
   - Background: video aerial loop muted (fallback foto), overlay gelap 50%.
3. **Stat bar (placeholder):** `XXX jam terbang` · `XX site didokumentasikan` · `X.XXX+ foto terkirim` · `0 insiden`
4. **Dipercaya oleh** — marquee logo klien (hanya yang disetujui) + teks "Ditambah pengalaman bekerja bersama kontraktor & vendor tambang".
5. **Showreel** — H2 `Bekerja di Lapangan`; sub: *Cuplikan 60 detik dari pit, hauling road, plant, hingga area reklamasi.*
6. **Layanan Utama (3 pilar)**
   - **Foto Operasional** — alat berat, aktivitas produksi, proses, potret tim & fasilitas. Fitur: foto kerja & HSE, foto produk/alat, potret korporat, retouch dan penamaan file terstruktur.
   - **Video Perusahaan & Dokumentasi** — company profile, safety/induction video, proses operasi, highlight event. Fitur: sinematik, VO, subtitle, motion graphic.
   - **Aerial & Drone** — progres pit, stockpile, hauling road, jetty/pelabuhan, reklamasi. Fitur: foto & video udara, time-lapse progres bulanan, peta orthomosaic* (jika tersedia).
   - *Tandai fitur bertanda `*` hanya jika memang ditawarkan.*
7. **Cara Kerja (5 langkah):** Konsultasi & scope → Penawaran → Perizinan & induksi lokasi → Pengambilan gambar → Penyerahan hasil via portal klien.
8. **Portofolio Pilihan** — 6 kartu (foto cover, judul, klien/lokasi (disamarkan jika perlu), kategori).
9. **Keselamatan & Kerahasiaan** — blok kepercayaan: *Induksi K3 setiap site · APD lengkap · Tidak ada publikasi tanpa persetujuan klien · Opsi watermark & galeri privat · NDA tersedia.*
10. **Testimoni** — 3 kutipan (placeholder: nama, jabatan, perusahaan — wajib izin).
11. **Artikel Terbaru** — 3 kartu.
12. **CTA akhir:** H2 `Siap mendokumentasikan site Anda?` — `Chat WhatsApp` · `Minta Penawaran`. Poin: Respons ≤ 1×24 jam kerja · Survei lokasi · Tanpa komitmen.
13. **Footer:** kontak, jam kerja, area layanan (Kalimantan/Sulawesi/… — isi), sosmed, legal.

### 4.2 Layanan (template detail)
Struktur tiap halaman `/layanan/[slug]`:
- Hero: judul, 1 kalimat manfaat, foto utama
- *Cocok untuk* (use-case list)
- *Yang Anda terima* (deliverable: jumlah foto/durasi video, resolusi, format, waktu pengerjaan)
- *Proses & Prasyarat lokasi* (induksi, izin, kondisi cuaca/drone)
- Galeri terkait (auto dari portofolio dengan kategori sama)
- Paket/harga mulai dari + `Pesan Sekarang` / `Minta Penawaran`
- FAQ layanan
- CTA

**Daftar layanan awal (7):**
1. Foto Operasional Tambang
2. Foto Produk & Alat Berat
3. Video Company Profile Perusahaan Tambang
4. Video Safety & Induksi
5. Aerial Drone — Foto & Video Site
6. Monitoring Progres Berkala (Time-lapse & Foto Udara Bulanan)
7. Dokumentasi Event, CSR & Reklamasi/Lingkungan

### 4.3 Portofolio
- Filter: Jenis (Foto/Video/Aerial) · Komoditas (Batubara/Nikel/Emas/Lainnya) · Area kerja (Pit/Hauling/Plant/Pelabuhan/Reklamasi) · Tahun
- Kartu studi kasus: cover, judul, kategori, tahun.
- Detail: **Tantangan → Pendekatan → Hasil**, galeri (foto/video/embed), spesifikasi pekerjaan (durasi, peralatan, lokasi umum), testimoni singkat, proyek terkait.
- Flag per proyek: `Tampilkan nama klien?` `Tampilkan lokasi?` (privasi).

### 4.4 Paket (harga placeholder, mengikuti struktur "paket + custom" Origya tapi basis kerja berbeda)
| Paket | Harga (placeholder) | Isi |
|---|---|---|
| **Dokumentasi Harian** | Rp X | 1 hari kerja, 1 fotografer, 80–120 foto terkurasi, retouch dasar, hasil ≤ 3 hari kerja |
| **Aerial Survey Lite** | Rp X | 1 hari, foto+video udara, 1–2 area, 30–50 foto + 1 video 60 dtk |
| **Profile Tambang** | Rp X | 2–3 hari, tim foto+video+drone, video 3–5 menit + 150 foto, VO & subtitle |
| **Monitoring Bulanan** | Rp X / bulan | Foto udara pada titik tetap, laporan perbandingan, kontrak ≥ 3 bulan |
| **Enterprise / Custom** | Hubungi kami | Multi-site, retainer tahunan, NDA, laporan khusus |

Aturan harga: biaya mobilisasi/akomodasi lokasi terpencil **terpisah** (tampilkan catatan jelas). Add-on: hari tambahan, revisi tambahan, drone tambahan, express delivery, watermark custom.

### 4.5 Tentang
- Cerita: latar belakang fotografer, pengalaman di tambang (isi fakta).
- Misi: menghadirkan dokumentasi visual yang akurat & aman untuk industri pertambangan.
- Nilai: **Safety First · Akurasi · Kerahasiaan · Tepat Waktu**
- Peralatan (kamera, lensa, drone) — sebagai bukti kemampuan.
- Sertifikasi & izin (tabel; isi sesuai kepemilikan).
- Tim (awal bisa 1 orang + jaringan freelancer: video editor, drone pilot).

### 4.6 Kontak & Pesan
- WhatsApp, email, jam kerja, area layanan, peta.
- Form **Minta Penawaran:** Nama, Perusahaan, Jabatan, Email, WA, Jenis layanan, Lokasi/site, Komoditas, Tanggal dibutuhkan, Perkiraan durasi, Kebutuhan khusus (upload brief), persetujuan privasi.

### 4.7 Artikel (ide awal, SEO)
1. Cara menyiapkan dokumentasi progres tambang untuk laporan bulanan
2. Foto udara vs survei konvensional: kapan masing-masing digunakan
3. Checklist sebelum fotografer masuk site (induksi, APD, izin)
4. Mengapa perusahaan tambang butuh video company profile
5. Dokumentasi reklamasi & pascatambang untuk pelaporan ESG
6. Tips memilih vendor dokumentasi tambang yang aman
Event: pameran/seminar pertambangan yang diikuti, jadwal liputan.

### 4.8 FAQ (draf)
- Apakah fotografer sudah memiliki induksi K3 dan APD sendiri?
- Siapa yang mengurus izin masuk lokasi dan izin terbang drone?
- Bagaimana kerahasiaan foto dijaga?
- Berapa lama hasil diterima, berapa kali revisi?
- Bagaimana jika cuaca buruk? (reschedule)
- Apakah mobilisasi/akomodasi termasuk harga?
- Hak penggunaan (lisensi) hasil foto/video?

---

## 5. Design System

**Arah visual:** industrial-editorial, gelap, bersih, foto sebagai pahlawan. Hindari kesan "agency neon" ala template.

**Palet (usulan)**
| Peran | Warna | Hex |
|---|---|---|
| Background gelap | Charcoal | `#0F1214` |
| Surface | Graphite | `#1A1F23` |
| Teks utama | Off-white | `#F2F0EB` |
| Teks sekunder | Stone | `#9AA3A8` |
| Aksen utama | Safety orange | `#F26B21` |
| Aksen sekunder | Ochre | `#C8963E` |
| Sukses / error | `#3FA66B` / `#D64545` |
Mode terang untuk halaman artikel/admin opsional. Kontras teks ≥ WCAG AA.

**Tipografi:** Heading — *Barlow Condensed* / *Oswald* (tegas, industrial); Body — *Inter*; Mono untuk metadata teknis (koordinat, EXIF) — *JetBrains Mono*. Skala: 12/14/16/20/28/40/64.

**Grid & spacing:** container 1200px, 12 kolom, spacing kelipatan 4/8. Sudut kecil (radius 4–6px) → kesan teknis.

**Komponen:** Navbar sticky transparan→solid, Hero video, StatBar, ServiceCard, PortfolioCard (hover: info), Lightbox galeri, Before/After slider (progres/reklamasi), Compare timeline (foto bulan ke bulan), PricingCard, StepProcess, Testimonial, FAQ accordion, Form bertahap, WhatsApp floating button, Toast, Badge status order.

**Motion:** halus, 200–400ms, parallax ringan pada hero, fade-up saat scroll; `prefers-reduced-motion` dihormati. Video hero tidak autoplay pada koneksi hemat data.

**Fotografi/asset:** 16:9 hero, 3:2 galeri, 4:5 kartu portofolio. Watermark halus untuk pratinjau; gambar WebP/AVIF + blur placeholder.

**Responsif:** mobile-first (sebagian besar buka dari ponsel/WA). Target Lighthouse ≥ 90.

**Wireframe beranda (ringkas)**
```
[Nav]
[HERO video ───────── H1 · sub · CTA CTA]
[Stat][Stat][Stat][Stat]
[Logo marquee]
[Showreel 16:9]
[Foto] [Video] [Aerial]   ← 3 pilar
[1 Konsultasi → 2 Penawaran → 3 Izin → 4 Shooting → 5 Hasil]
[Portofolio grid 3×2]
[Safety & Kerahasiaan]
[Testimoni ×3]
[Artikel ×3]
[CTA WhatsApp / Penawaran]
[Footer]
```

---

## 6. Admin / CMS (`/admin`)

**Peran:** Owner (semua), Editor (konten), Sales/Admin Order (inquiry, order, invoice), Klien (hanya portal klien — akun terpisah).

**Modul**
1. **Dashboard** — inquiry baru, order berjalan, pembayaran tertunda, jadwal shooting minggu ini, pendapatan bulan ini, artikel draft.
2. **Halaman & Beranda** — edit hero, stat, CTA, showreel, urutan section (blok/section builder sederhana).
3. **Layanan** — CRUD, slug, ringkasan, deliverable, FAQ, galeri, SEO fields, publish/draft.
4. **Portofolio** — CRUD, kategori/komoditas/tag, galeri multi-upload, flag privasi (nama klien/lokasi), urut, unggulan.
5. **Paket & Add-on** — harga, fitur, label "Populer", aktif/nonaktif, kuota.
6. **Artikel, Berita, Event** — editor rich text/markdown, cover, kategori, jadwal publish, SEO.
7. **Media Library** — upload (foto/video), auto-generate ukuran, alt text, folder, penghapusan aman.
8. **Testimoni & Klien (logo)** — izin tampil, urut.
9. **Tim & Sertifikasi** — profil, sertifikat, peralatan.
10. **Inquiry (Leads)** — daftar permintaan penawaran, status (Baru → Dihubungi → Survei → Penawaran → Menang/Kalah), catatan, assign, ekspor CSV.
11. **Penawaran (Quotation)** — buat dari inquiry, item+harga+mobilisasi, PDF, kirim email/WA, masa berlaku.
12. **Order & Proyek** — dari paket/penawaran → order, status (Menunggu DP → Dijadwalkan → Shooting → Editing → Review → Selesai), kalender jadwal, checklist (izin, induksi, drone), upload hasil ke galeri klien.
13. **Invoice & Pembayaran** — invoice (DP/pelunasan), status, bukti transfer manual (fase 1), gateway (fase 2), laporan.
14. **Portal Klien (galeri hasil)** — galeri privat per order, pilih favorit, unduh zip, komentar revisi, kedaluwarsa link.
15. **Pengaturan** — identitas, kontak, sosmed, SEO default, WhatsApp, template email, rekening/gateway, pajak, user & peran.
16. **Audit Log** — siapa mengubah apa (penting untuk data klien industri).

---

## 7. Model Data (ringkas)

```
User(id, name, email, role[owner|editor|sales|client], passwordHash, companyId?)
Company(id, name, industry, contact…)
Service(id, slug, title, summary, body, deliverables[], faq[], coverMediaId, seo…, status)
Package(id, name, price, priceNote, features[], isPopular, serviceIds[], isActive)
Addon(id, name, price, unit)
Project(id, slug, title, category, commodity, year, clientName?, showClient, location?, showLocation, challenge, approach, result, galleryMediaIds[], featured, status)
Media(id, url, type, alt, width, height, folder, isPrivate)
Post(id, type[article|news], slug, title, body, cover, categoryId, publishAt, seo…)
Event(id, slug, title, startsAt, endsAt, mode[online|offline], location, link, status)
Testimonial(id, author, role, company, quote, allowPublic, order)
Inquiry(id, name, company, email, phone, serviceId, site, commodity, neededDate, brief, attachments[], status, assignedTo)
Quotation(id, inquiryId, number, items[], mobilization, total, validUntil, status, pdfUrl)
Order(id, number, userId, packageId|quotationId, schedule, siteInfo, status, checklist[], notes)
Invoice(id, orderId, type[dp|final], amount, dueDate, status, paymentRef, proofUrl)
Delivery(id, orderId, galleryMediaIds[], expiresAt, downloadLog[])
AuditLog(id, userId, action, entity, entityId, diff, at)
Setting(key, value)
```

---

## 8. Alur Penjualan

**Jalur A — Paket standar (self-service):** Paket → `/pesan` → pilih tanggal & add-on → isi data site → ringkasan harga → bayar DP → konfirmasi email/WA → admin verifikasi & jadwalkan.

**Jalur B — Proyek custom (penawaran):** Form Minta Penawaran → Lead di admin → survei/diskusi → Quotation PDF → klien setuju → order + invoice DP → shooting → editing → penyerahan via portal → pelunasan → selesai.

**Aturan bisnis:**
- DP (mis. 30–50%) sebelum jadwal dikunci; pelunasan sebelum unduhan resolusi penuh (opsi).
- Reschedule karena cuaca/izin tidak menghanguskan DP (kebijakan — putuskan).
- Jumlah revisi termasuk paket (mis. 2×), selanjutnya berbayar.
- Klausul kerahasiaan/NDA & lisensi penggunaan di syarat order.

**Pembayaran:** Fase 1 transfer manual + upload bukti (cepat & cukup untuk B2B). Fase 2 payment gateway (Midtrans / Xendit) + invoice otomatis.

**Notifikasi:** email (inquiry masuk, penawaran, invoice, hasil siap); WhatsApp link/deep-link; fase 2 WA API.

---

## 9. Rekomendasi Teknis

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Framework | Next.js (App Router) + TypeScript | SSR/SEO, satu kode untuk publik & admin |
| UI | Tailwind CSS + shadcn/ui | Cepat, konsisten |
| DB | PostgreSQL + Prisma | Relasional (order/invoice) |
| Auth | Auth.js / Lucia, role-based | Admin & klien |
| Storage | S3-compatible (Cloudflare R2 / S3) + signed URL | Galeri privat, hemat bandwidth |
| Media | sharp / Cloudflare Images, `next/image` | Ukuran, WebP/AVIF |
| Video | YouTube/Vimeo unlisted untuk showreel; file langsung di R2 untuk hasil klien | Hemat beban |
| Editor | Tiptap / MDX | Artikel |
| Email | Resend / SMTP | Notifikasi |
| PDF | react-pdf / puppeteer | Penawaran & invoice |
| Deploy | VPS (Docker) atau Vercel + managed Postgres | Sesuai anggaran |
| Analytics | Plausible/GA4 + Search Console | SEO |

**SEO:** URL bersih, canonical per halaman (beda dari Origya), sitemap.xml, schema (LocalBusiness, Service, Article), OG image per halaman, kata kunci: *jasa foto tambang, jasa drone tambang, video company profile tambang, dokumentasi progres tambang*.

**Keamanan:** rate-limit form, captcha ringan, validasi upload (tipe/ukuran), signed URL galeri privat, 2FA admin (fase 2), backup DB harian, audit log.

---

## 10. Roadmap

| Fase | Isi | Estimasi kasar |
|---|---|---|
| 0 — Discovery | Wawancara teman Anda (portofolio, sertifikasi, alat, harga, area), kumpulkan foto/video, putuskan nama & domain | 1 minggu |
| 1 — Desain | Moodboard, style tile, wireframe 6 halaman kunci, hi-fi beranda + 1 layanan + portofolio + paket + pesan, review | 1–2 minggu |
| 2 — Fondasi | Repo, auth, schema DB, media library, layout & komponen | 1 minggu |
| 3 — Situs publik | Beranda, layanan, portofolio, paket, artikel, tentang, kontak, SEO | 2 minggu |
| 4 — Admin konten | CRUD semua modul konten + dashboard | 2 minggu |
| 5 — Penjualan | Inquiry→Quotation→Order→Invoice, transfer manual, portal klien | 2–3 minggu |
| 6 — QA & Rilis | Uji, performa, keamanan, isi konten nyata, deploy | 1 minggu |
| 7 — Iterasi | Payment gateway, English, WA API, reminder otomatis | berkelanjutan |

**MVP rilis (setelah Fase 3+4 + form penawaran):** sudah bisa menerima lead; modul penjualan penuh menyusul.

---

## 11. Pertanyaan Terbuka (isi bersama teman Anda)
1. Nama brand final, domain, logo?
2. Solo atau tim? Siapa pilot drone & editor video? Sertifikat/izin yang dimiliki?
3. Perusahaan/kontraktor mana yang boleh dicantumkan (atau hanya "klien industri tambang")?
4. Karya apa yang boleh dipublikasikan (sering ada pembatasan dari klien)?
5. Area layanan (provinsi/site) dan kebijakan biaya mobilisasi?
6. Daftar harga riil, DP, kebijakan revisi & reschedule?
7. Kebutuhan tambahan: orthomosaic/volumetrik, laporan terstruktur?
8. Bahasa: Indonesia saja dulu, atau langsung bilingual?
9. Pembayaran: transfer manual dulu atau langsung gateway?
10. Hosting & anggaran?
