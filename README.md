# TheEdu - Moodle Theme (Moodle 4.4 s/d 5.3+)

**TheEdu** adalah tema Moodle modern, responsif, dan elegan berbasis `theme_boost` dengan sentuhan estetika **pastel yang lembut**, dukungan **Dark Mode** instan, sistem **4 Posisi Logo Sekaligus**, serta **Slider Carousel Dinamis** yang dapat dikelola langsung melalui Site Administration Moodle.

> 📝 **Riwayat Versi & Catatan Pembaruan Lengkap**: Silakan baca berkas [**CHANGELOG.md**](CHANGELOG.md) untuk melihat seluruh catatan perubahan per versi dari awal (v1.0.0 s/d v1.4.4) beserta solusi arsitektur teknisnya.

---

## 🌟 Fitur Utama

1. **Kompatibilitas Luas (Moodle 4.4 s/d 5.3+)**:
   - Mewarisi arsitektur resmi Moodle Boost (`theme_boost`).
   - Menggunakan Bootstrap 5, Mustache templating, dan AMD JavaScript.
   - Aman untuk update versi Moodle masa depan.

2. **Dukungan 4 Posisi Logo Sekaligus**:
   - **Logo 1 (Kiri-Atas / Top-Left)**: Logo utama situs pada bilah navigasi header.
   - **Logo 2 (Kanan-Atas / Top-Right)**: Logo sekunder/mitra/akreditasi pada sisi kanan bilah navigasi header.
   - **Logo 3 (Kiri-Bawah / Bottom-Left)**: Logo identitas situs pada kolom utama footer.
   - **Logo 4 (Kanan-Bawah / Bottom-Right)**: Logo sertifikasi/kemitraan/kementerian pada sisi kanan footer.
   - Masing-masing posisi logo dapat diaktifkan/dinonaktifkan secara independen, diatur tingginya (height), diberikan tautan kustom (link), dan teks alt untuk aksesibilitas.

3. **Warna Pastel & Kustomisasi Area**:
   - Palet bawaan bernuansa pastel modern:
     - *Primary*: Lavender Pastel (`#6C5CE7`)
     - *Secondary*: Mint Pastel (`#00CEC9`)
     - *Accent*: Koral Pastel (`#FF7675`)
     - *Body Background*: Ice/Soft White (`#F7F9FC`)
   - Opsi Color Picker di Admin Settings untuk mengatur warna masing-masing area:
     - Header / Navbar (Latar & Teks/Ikon)
     - Latar Belakang Halaman (Body Background)
     - Kartu & Blok Konten (Card Background)
     - Footer (Latar & Teks)

4. **Dark Mode (Mode Gelap)**:
   - Tombol switch Matahari/Bulan yang elegan pada navbar.
   - Penyimpanan status preferensi di `localStorage` browser pengguna.
   - Deteksi otomatis preferensi OS (`prefers-color-scheme`).
   - Dilengkapi script pencegah *Flash of Unstyled Content (FOUC)* saat halaman dimuat.
   - Penyesuaian warna dark mode dapat diatur dari pengaturan admin.

5. **Slider Carousel Dinamis di Beranda (Frontpage)**:
   - Dapat diaktifkan/dinonaktifkan dari pengaturan tema.
   - Pilihan jumlah slide aktif (1 hingga 5 slide).
   - Pengaturan interval waktu autoplay.
   - Setiap slide mendukung:
     - Unggah file gambar kustom (format WebP, PNG, JPG).
     - Judul slide (Title).
     - Deskripsi/Caption dengan kartu efek *glassmorphism*.
     - Tombol Call-to-Action (Teks tombol & URL tujuan).

6. **Footer Lengkap & Media Sosial**:
   - Kolom deskripsi instansi / tentang situs.
   - Tautan navigasi cepat.
   - Tautan akun media sosial (Facebook, X/Twitter, Instagram, YouTube, LinkedIn).
   - Penyesuaian teks hak cipta (Copyright).

---

## 📂 Struktur Berkas Tema

```
TheEdu_Theme/
├── amd/
│   ├── build/
│   │   └── darkmode.min.js      # Modul JS dark mode yang telah diminifikasi
│   └── src/
│       └── darkmode.js          # Sumber script AMD dark mode & local persistence
├── lang/
│   ├── en/
│   │   └── theme_theedu.php     # Bahasa Inggris lengkap
│   └── id/
│       └── theme_theedu.php     # Bahasa Indonesia lengkap
├── layout/
│   ├── drawers.php              # Layout utama drawer (Moodle 4.4+)
│   ├── login.php                # Layout halaman login
│   └── secure.php               # Layout mode ujian aman (Safe Exam Browser/Quiz)
├── pix/
│   ├── logo.png                 # Logo standar TheEdu Portal
│   └── screenshot.png           # Thumbnail preview tema untuk Moodle Theme Selector
├── scss/
│   ├── preset/
│   │   └── default.scss         # Variabel inti Bootstrap & font Plus Jakarta Sans
│   ├── _darkmode.scss           # Styling lengkap tema gelap [data-theme="dark"]
│   ├── _footer.scss             # Styling footer & logo 3 & 4
│   ├── _header.scss             # Styling navbar header & logo 1 & 2
│   ├── _slider.scss             # Styling banner carousel beranda
│   ├── _variables.scss          # Token warna pastel & transisi
│   └── post.scss                # Polish UI kartu, tombol, badge, dan drawer
├── templates/
│   ├── dark_mode_toggle.mustache# Komponen tombol switch tema
│   ├── drawers.mustache         # Template utama Moodle 4.4 s/d 5.3+
│   ├── footer.mustache          # Template footer dengan multi-logo
│   ├── login.mustache           # Template login minimalis
│   ├── navbar.mustache          # Template header navbar dengan multi-logo
│   ├── secure.mustache          # Template mode secure
│   └── slider.mustache          # Template dynamic carousel
├── config.php                   # Konfigurasi layout & SCSS theme Moodle
├── lib.php                      # Fungsi callback SCSS, pluginfile & context data
├── settings.php                 # Halaman panel administrasi Moodle
├── version.php                  # Versi tema dan spesifikasi kompatibilitas
└── README.md                    # Dokumentasi lengkap
```

---

## 🚀 Panduan Instalasi ke Moodle

1. **Salin Folder ke Direktori Tema Moodle**:
   - Ubah nama folder menjadi `theedu` (huruf kecil semua sesuai konvensi Moodle).
   - Letakkan folder tersebut di dalam direktori `theme/` instalasi Moodle Anda:
     ```
     moodle_root/theme/theedu
     ```

2. **Lakukan Notifikasi / Upgrade Moodle**:
   - Masuk ke Moodle sebagai **Site Administrator**.
   - Buka menu **Site administration -> Notifications** (Administrasi situs -> Pemberitahuan).
   - Moodle akan mendeteksi tema baru **TheEdu (theme_theedu)**. Klik tombol **Upgrade Moodle database now**.
   - Atau melalui terminal/CLI:
     ```bash
     php admin/cli/upgrade.php
     ```

3. **Pilih Tema TheEdu**:
   - Masuk ke **Site administration -> Appearance -> Themes -> Theme selector**.
   - Pada baris **Default**, klik **Change theme**.
   - Cari **TheEdu** dan klik **Use theme**.

---

## ⚙️ Panduan Konfigurasi di Moodle Admin

Buka **Site administration -> Appearance -> Themes -> TheEdu**:

### 1. Tab "Branding & 4 Posisi Logo"
- **Logo 1 (Kiri-Atas)**: Centang aktifkan, unggah logo instansi Anda, atur tinggi (misal: 46px), dan isi teks alt.
- **Logo 2 (Kanan-Atas)**: Centang aktifkan untuk menampilkan logo mitra/akreditasi di samping kanan navigasi.
- **Logo 3 (Kiri-Bawah)**: Centang aktifkan untuk logo di kolom footer kiri.
- **Logo 4 (Kanan-Bawah)**: Centang aktifkan untuk logo sertifikasi di sisi kanan footer.
- **Gaya Floating Header**: Mengaktifkan bilah navbar mengambang (*pill-style sticky floating*) dengan efek kaca transparan (*glassmorphism blur*) yang tetap menempel di atas saat layar digulir.
- **Pintasan Menu Kanan Atas**: Kontrol on/off untuk ikon Kursus Saya (topi toga), Lonceng Notifikasi (dengan badge angka), Gelembung Pesan, Profil Pengguna (Student/Avatar), dan Tombol Edit Mode.
- **Favicon & Latar Login**: Unggah favicon situs (.ico/.png) dan gambar latar belakang halaman login kustom.

### 2. Tab "Warna Pastel & Tipografi"
- **Pilihan Font Modern (Google Fonts)**:
  - *Plus Jakarta Sans* (bawaan)
  - *Inter*
  - *Poppins*
  - *Nunito*
  - *Outfit*
  - *Roboto*
  - *Custom Font* (bebas memasukkan nama font Google Fonts lainnya)
- **Pilihan Warna Pastel**:
  - Warna Utama (Primary)
  - Warna Sekunder (Secondary)
  - Warna Aksen / Highlight
  - Latar & Teks Navbar
  - Latar Halaman (Body Background)
  - Latar Kartu & Blok Konten (Card Background)
  - Latar & Teks Footer

### 3. Tab "Dark Mode (Mode Gelap)"
- Aktifkan fitur switch Dark Mode.
- Tentukan mode default untuk pengunjung baru (*Auto* / *Light* / *Dark*).
- Sesuaikan warna kontras dark mode jika diperlukan.

### 4. Tab "Slider Beranda"
- Centang **Aktifkan Slider di Halaman Depan**.
- Pilih jumlah slide aktif (1 sampai 5).
- Tentukan waktu tayang (*interval*) dalam milidetik (misal 5000 untuk 5 detik).
- Untuk masing-masing slide:
  - Unggah gambar banner (rekomendasi: 1920x650 px).
  - Tulis judul, deskripsi/keterangan, teks tombol, dan URL tautan.

### 5. Tab "Kategori & Sorotan Beranda" (Dynamic Column Grid)
- **Maksimal Kolom Sebelum Turun ke Bawah**: Pilih batas kolom dalam 1 baris (2, 3, 4, 5, atau 6 kolom).
- **Sumber Data Kategori**:
  - *Kategori Kustom Tetap (Fix)*: Atur kartu kategori sendiri (ikon FontAwesome, judul kategori, deskripsi kursus, dan link).
  - *Otomatis dari Kategori Kursus Moodle*: Otomatis menarik daftar kategori kursus resmi dari database Moodle.
- **Auto-Wrapping**: Jika jumlah kategori melebihi batas maksimal kolom (misal ada 6 kategori dan disetel 4 kolom), 4 kategori akan mengisi baris pertama dan 2 sisanya otomatis turun ke baris berikutnya secara rapi dan proporsional.

### 6. Tab "Kursus yang Diikuti & Tampilan Kartu" (Enrolled Courses & Card Details)
- **Bilah Pencarian Kursus di Beranda (Course Search Bar)**: Sakelar On / Off untuk menampilkan atau menyembunyikan kotak pencarian kursus instan antara slider dan kategori.
- **Daftar Kursus yang Diikuti**: Untuk siswa/user yang login, otomatis memuat kursus yang mereka ikuti (*enrolled courses*) beserta persentase progress kelulusan. Untuk pengunjung tamu, menampilkan kursus unggulan yang tersedia.
- **Pengaturan Thumbnail & Ikon**:
  - *Mode Thumbnail*: Pilihan antara menampilkan gambar sampul kursus (*Image*), ikon pastel elegan (*Icon*), atau otomatis fallback ke ikon jika kursus belum memiliki gambar.
  - *Ikon Bawaan*: Bebas memilih ikon FontAwesome (misal: `fa-laptop-code`, `fa-graduation-cap`, `fa-book-open`, `fa-chalkboard-user`).
- **Elemen Bagian Bawah Kartu (*Footer Card*)**:
  - *Tampilkan Pengajar (Teacher)*: Menampilkan avatar dan nama dosen/instruktur pengampu di bagian bawah kartu.
  - *Tampilkan Bar Kemajuan (Progress Bar)*: Menampilkan persentase progress belajar siswa.
  - *Tampilkan Label Kategori & Ringkasan*: Kontrol on/off untuk badge kategori dan ringkasan materi.

### 7. Tab "Footer & Media Sosial"
- **Format Logo**: Dianjurkan menggunakan format **PNG transparan** atau **SVG** agar menyatu rapi dengan warna latar, baik di mode terang maupun mode gelap.
- **Gambar Slider**: Gunakan rasio 16:9 atau 21:9 dengan resolusi minimal 1600x600 px untuk ketajaman optimal di layar resolusi tinggi (Retina/4K).
