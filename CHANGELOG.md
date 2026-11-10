# CHANGELOG - TheEdu Moodle Theme

Dokumen ini mencatat seluruh riwayat pembaruan, perubahan arsitektur, perbaikan bug, dan evolusi fitur tema **TheEdu** (`theme_theedu`) dari awal pengembangan hingga versi terbaru. 

Format dokumentasi ini disusun secara rinci dan terstruktur agar dapat dijadikan konteks langsung oleh AI model lain (ChatGPT, Claude, Gemini, dll.) saat melanjutkan pengembangan tema di masa mendatang.

---

## [v1.6.1] - 2026-11-10 (Build 2026111003)

### 🎯 Masalah yang Diselesaikan
1. **Pengaturan Kartu Statistik Hero Dashboard Masih Berbasis Preset Mode:**
   - Sebelumnya pengaturan statistik menggunakan mode preset dropdown kaku (`progress`, `timeline`, `mixed`), membatasi kartu hanya 3 buah tanpa fleksibilitas untuk memilih sendiri kartu mana yang aktif atau mengubah urutan tampilnya.
   - Pengguna menginginkan konfigurasi berbasis *checkbox* mandiri dengan total 5 kartu statistik aktif yang dapat dihidupkan/dimatikan sesuka hati, serta dilengkapi pengaturan nomor urutan posisi tampil (*display order*) yang dinamis.
2. **Pemilih Bahasa Footer Bertumpuk dengan Menu Lain & Belum Berfungsi:**
   - Pada bar bawah footer, komponen kapsul pengubah bahasa ditempatkan dalam satu kontainer flex bersama tautan kustom bawah dan blok output footer standar Moodle (`output.standard_footer_html`), menyebabkan elemen-elemen tersebut saling bertumpuk (*collision/overlap*) pada berbagai ukuran layar.
   - Selain itu, pergantian bahasa melalui parameter `?lang=id` tidak langsung mengubah antarmuka bagi pengguna yang sedang login karena Moodle secara bawaan memprioritaskan preferensi bahasa tersimpan pengguna dan mengabaikan parameter query string.
3. **Pembaruan Profil Organisasi di Halaman About:**
   - Profil organisasi pengembang tema di halaman *About Theme & Release Notes* perlu diperbarui dari PT Astra Daihatsu Motor menjadi tautan situs resmi pengembang: **[Gridiyans Apps - Future Technology](https://gridiyans.my.id/)**.

### ✨ Perubahan & Solusi
1. **Arsitektur Kartu Statistik Dinamis Berbasis Checkbox & Urutan Posisi (`settings.php`, `lib.php`, `templates/drawers.mustache`):**
   - Menghapus mode preset dropdown `dashboard_stats_mode`.
   - Menambahkan kendali checkbox dan input angka urutan (*order*) mandiri untuk setiap kartu statistik di Site Administration:
     - **Kartu Terdaftar (*Enrolled*):** Checkbox aktif (Default: `1`), Urutan (Default: `1`).
     - **Kartu Berjalan (*In Progress <80%*):** Checkbox aktif (Default: `1`), Urutan (Default: `2`).
     - **Kartu Selesai (*Completed ≥80%*):** Checkbox aktif (Default: `1`), Urutan (Default: `3`).
     - **Kartu Berlangsung (*Ongoing Hari Ini*):** Checkbox aktif (Default: `1`), Urutan (Default: `4`).
     - **Kartu Akan Datang (*Upcoming*):** Checkbox aktif (Default: `1`), Urutan (Default: `5`).
     - **Kartu Lewat (*Past*):** Checkbox aktif (Default: `0`), Urutan (Default: `6`).
   - Secara default, kelima kartu statistik utama langsung aktif bersamaan (total 5 card), tersusun rapi dari kiri ke kanan.
   - Logika backend pada `lib.php` mengumpulkan kartu yang aktif, mengurutkannya secara dinamis dengan `usort()` berdasarkan nomor urut, dan mengirimkannya ke `drawers.mustache` untuk dirender secara fleksibel dengan styling adaptif (`_course.scss`).
2. **Sinkronisasi Bahasa Tingkat Sesi & Early Page Hook (`config.php`, `lib.php`):**
   - Mendaftarkan fungsi hook `$THEME->page_init = 'theme_theedu_page_init'` pada `config.php`.
   - Mengimplementasikan `theme_theedu_page_init()` pada `lib.php` yang secara aktif menangkap parameter `?lang=en` atau `?lang=id`, langsung memperbarui `$SESSION->lang`, `$USER->lang`, serta database preferensi pengguna via `set_user_preference('current_language', ...)`.
   - Hal ini menjamin bahwa pergantian bahasa langsung aktif seketika (bahkan untuk pengguna yang sedang login) dan konsisten di seluruh sesi.
3. **Desain Ulang Bar Bawah Footer Bebas Tabrakan (*Anti-Overlap Layout*) (`templates/footer.mustache`, `scss/_footer.scss`):**
   - Memisahkan bar bawah footer menjadi dua tingkat terisolasi:
     - **Tingkat Utama (`theedu-footer-bottom-main`):** Memuat teks hak cipta (*copyright*) di sisi kiri, serta kapsul bahasa dan tautan kustom di sisi kanan dengan jarak dan wrap yang teratur.
     - **Tingkat Sistem (`theedu-footer-standard-row`):** Output standar Moodle ditempatkan pada baris terpisah di bagian bawah dengan garis pemisah (*divider*) tipis, menghilangkan risiko elemen bertumpuk secara permanen.
4. **Pembaruan Organisasi Pengembang (`lib.php`):**
   - Kolom organisasi pada tabel informasi *About Theme* kini menampilkan tautan aktif ke **[Gridiyans Apps - Future Technology](https://gridiyans.my.id/)**.
5. **Kenaikan Versi:**
   - Versi tema dinaikkan ke **`v1.6.1`** (Build `2026111003`).

---

## [v1.6.0] - 2026-11-10 (Build 2026111002)

### 🎯 Masalah yang Diselesaikan
1. **Perhitungan Statistik Hero Dashboard & Formula Metrik Kursus:**
   - Sebelumnya 3 kotak kartu statistik di profil/hero dashboard hanya mengandalkan pengecekan biner `$cinfo->is_course_complete($USER->id)`. Jika kursus tidak mengaktifkan tracking kelulusan formal secara ketat atau siswa baru menyelesaikan sebagian (misal 80%+), angka selesai tetap 0.
   - Pengguna membutuhkan kalkulasi matematis yang akurat untuk 6 kategori status pembelajaran:
     - **Terdaftar (*Enrolled*):** Seluruh kursus yang aktif diikuti oleh pengguna saat ini (di luar SITEID/Frontpage).
     - **Lewat (*Past*):** Kursus terdaftar yang tanggal akhirnya telah lampau sebelum hari ini (`enddate < today midnight`).
     - **Berlangsung (*Ongoing*):** Kursus terdaftar yang aktif berjalan hari ini (`startdate <= today 23:59:59 && (enddate == 0 || enddate >= today midnight)`).
     - **Akan Datang (*Upcoming*):** Kursus terdaftar yang jadwal mulainya di masa mendatang (`startdate > today 23:59:59`).
     - **Berjalan (*In Progress*):** Kursus terdaftar dengan tingkat kemajuan (*completion progress*) kurang dari 80% (`< 80%`).
     - **Complete (*Completed*):** Kursus terdaftar dengan tingkat kemajuan 80% atau lebih (`>= 80%`) atau telah ditandai tuntas oleh API kelulusan Moodle.
2. **Bahasa Campur-Campur di Area Dashboard Hero (*Mixed Language Inconsistency*):**
   - Teks sapaan (*"Selamat Datang kembali, [Nama]! 👋"*), deskripsi (*"Pantau aktivitas pembelajaran, tugas, dan kursus aktif Anda di sini."*), label role (*"Peserta Pembelajaran"*, *"Tamu"*), dan label statistik (*"Terdaftar"*, *"Selesai"*, *"Berjalan"*) sebelumnya tertulis statis (*hardcoded*) dalam bahasa Indonesia di dalam berkas template `drawers.mustache` dan `lib.php`.
   - Akibatnya, ketika antarmuka situs atau pengguna memilih bahasa Inggris (*English*), teks di hero dashboard tetap berbahasa Indonesia sehingga tampilan menjadi tidak seragam dan kurang profesional.
3. **Ketiadaan Menu Pilihan Bahasa Interaktif di Footer:**
   - Belum tersedia pengubah bahasa (*language switcher*) yang mudah dijangkau di bar bawah footer situs untuk memudahkan pengguna berganti antara bahasa Inggris (*English [default]*) dan bahasa Indonesia (*Indonesia*).

### ✨ Perubahan & Solusi
1. **Kalkulasi Metrik Kursus & Statistik Multi-Mode Presisi (`lib.php`):**
   - Menghitung persentase kemajuan siswa secara real-time via `\core_completion\progress::get_course_progress_percentage($ucourse, $USER->id)`.
   - Menetapkan aturan ambang batas tegas: kemajuan $\ge 80\%$ dihitung sebagai **Completed / Selesai**, sedangkan kemajuan $< 80\%$ dihitung sebagai **In Progress / Berjalan**.
   - Menghitung segmentasi waktu terhadap stempel waktu hari ini (`today midnight` dan `tomorrow midnight - 1`), mengelompokkan kursus secara tepat ke dalam **Ongoing / Berlangsung**, **Upcoming / Akan Datang**, dan **Past / Lewat**.
   - Menambahkan pengaturan baru di Site Administration: **Tampilan Statistik Hero Dashboard** (`theme_theedu/dashboard_stats_mode`) dengan 3 pilihan mode:
     - `progress` (Default): Menampilkan **Enrolled**, **Completed ($\ge 80\%$)**, dan **In Progress ($< 80\%$)**.
     - `timeline`: Menampilkan **Ongoing (Hari Ini)**, **Upcoming (Masa Datang)**, dan **Past (Lewat)**.
     - `mixed`: Menampilkan **Enrolled**, **Ongoing**, dan **Completed ($\ge 80\%$)**.
2. **Eliminasi Total String Hardcode & Lokalisasi Bersih (`templates/drawers.mustache`, `lib.php`):**
   - Seluruh teks pada dashboard hero kini 100% menggunakan sistem lokalisasi bawaan Moodle melalui tag `{{#str}}` dan fungsi `get_string()`.
   - Saat bahasa Inggris aktif, seluruh label tampil bersih dalam bahasa Inggris profesional: *"Welcome back, [Name]! 👋"*, *"Track your learning progress, activities, and active courses here."*, *"Enrolled"*, *"Completed"*, *"In Progress"*, *"Learner"*, *"Administrator"*, *"Guest"*.
   - Saat bahasa Indonesia aktif, seluruh label tampil rapi dan tepat dalam tata bahasa Indonesia baku.
3. **Komponen Pemilih Bahasa Cepat di Bar Bawah Footer (`templates/footer.mustache`, `scss/_footer.scss`):**
   - Menambahkan kapsul pengubah bahasa modern di bar bawah footer (`theedu-footer-lang-selector`) dengan ikon bola dunia dan tombol pill bendera:
     - 🇬🇧 **EN English** (Default)
     - 🇮🇩 **ID Indonesia**
   - Menggunakan mekanisme tautan bahasa asli Moodle (`new \moodle_url($PAGE->url, ['lang' => $langcode])`) sehingga ketika diklik, halaman aktif saat itu juga langsung dimuat ulang dalam bahasa yang dipilih tanpa kehilangan konteks halaman.
   - Tombol bahasa yang sedang aktif disorot dengan warna aksen tema (*elevated active pill*), sedangkan bahasa alternatif dapat diklik secara instan.
   - Dilengkapi opsi pengaturan di tab Footer (`theme_theedu/footer_show_language_selector`) agar administrator dapat menampilkan atau menyembunyikan pemilih bahasa sesuai kebutuhan situs.
4. **Pembaruan Berkas Rilis & Dokumentasi Teknis:**
   - Versi tema dinaikkan ke **`v1.6.0`** (Build `2026111002`) pada `version.php`.
   - Menambahkan rilis v1.6.0, v1.5.9, dan v1.5.8 ke dalam kartu catatan rilis dinamis di halaman konfigurasi *About Theme & Release Notes*.

---

## [v1.5.9] - 2026-11-10 (Build 2026111001)

### 🎯 Masalah yang Diselesaikan
1. **Kursus yang Tidak Di-enrol Muncul di Bagian "My Enrolled Courses" pada Halaman Home:**
   - Pengguna (siswa atau guru) yang login mendapati kursus yang sebenarnya **tidak mereka ikuti (*not enrolled*)** tetap muncul di daftar kartu kursus pada bagian *"My Enrolled Courses"* di halaman depan.
   - **Penyebab Teknis:**
     1. Pada `lib.php` sebelumnya terdapat logika *fallback* tak bersyarat: `if (empty($courseslist)) { $sitecourses = get_courses('all', ...); }`. Jika seorang siswa yang login belum terdaftar di kursus apa pun (atau baru terdaftar di sebagian kursus), tema secara keliru mengambil seluruh kursus aktif di situs (`get_courses`) dan menampilkannya di bawah judul statis *"My Enrolled Courses"*.
     2. Pengambilan kursus terdaftar belum dilengkapi dengan validasi konteks ganda `is_enrolled($coursecontext, $USER->id, '', true)`, sehingga status keikutsertaan yang sudah kedaluwarsa, ditangguhkan (*suspended*), atau penetapan peran tanpa pendaftaran aktif (*role assignment without enrolment*) berpotensi terbawa ke daftar kursus.

### ✨ Perubahan & Solusi
1. **Filtrasi Ketat Kursus Terdaftar (*Strict Active Enrolment Enforcement*) (`lib.php`):**
   - Memisahkan secara tegas logika pengambilan kursus antara **Pengguna Login** dan **Tamu/Pengunjung (*Guest*)**.
   - Untuk pengguna yang login, tema kini memanggil `enrol_get_users_courses($USER->id, true)` (dengan parameter `$onlyactive = true`) dan memvalidasi setiap kursus menggunakan `is_enrolled($coursecontext, $USER->id, '', true)`.
   - Menghapus sepenuhnya *fallback* liar ke `get_courses('all')` dan data demo sampel saat pengguna sedang login, sehingga **100% dipastikan hanya kursus yang aktif diikuti oleh pengguna bersangkutan yang akan tampil**.
2. **Penyediaan Status Kosong Edukatif (*Friendly Empty State*) (`templates/frontpage_courses.mustache`):**
   - Jika pengguna yang login belum terdaftar pada kursus apa pun, tema tidak lagi menampilkan kursus orang lain.
   - Sebagai gantinya, ditampilkan kartu status kosong yang elegan dan rapi:
     - Ikon topi toga (*graduation cap*).
     - Judul: *"Belum ada kursus yang diikuti"* (*No enrolled courses yet*).
     - Deskripsi informatif: *"Anda saat ini belum terdaftar di kursus apa pun. Silakan jelajahi katalog kursus kami untuk menemukan modul pembelajaran yang sesuai."*
     - Tombol aksi utama: *"Jelajahi Kursus"* (*Explore Courses*) yang langsung mengarahkan ke direktori katalog `/course/index.php`.
3. **Pengaturan Perilaku Fleksibel di Site Administration (`settings.php`):**
   - Menambahkan pengaturan baru: **Tindakan Saat Siswa Belum Mengikuti Kursus** (`theme_theedu/courses_empty_behavior`) dengan opsi:
     - `empty_state` (Direkomendasikan/Default): Tampilkan pesan informatif dengan tombol "Jelajahi Kursus".
     - `hide_section`: Sembunyikan seksi kursus sepenuhnya jika belum ada kursus yang diikuti.
     - `show_featured`: Tampilkan kursus publik yang tersedia di situs dengan judul otomatis berganti menjadi *"Kursus Unggulan"* (*Featured Courses*), sehingga tidak membingungkan siswa.
4. **Sinkronisasi Statistik Header (`lib.php`):**
   - Menambahkan filter pengecualian `SITEID` (halaman depan) pada perhitungan jumlah kursus di hero profil pengguna agar selalu sinkron dan konsisten.

---

## [v1.5.8] - 2026-11-09 (Build 2026110907)

### 🎯 Masalah yang Diselesaikan
1. **Form Login Transparan & Teks Sulit Terbaca di atas Gambar Background:**
   - Setelah background login muncul, elemen form login (logo, judul form, input username, password, dan teks guest access) menjadi transparan tanpa kartu pelindung (*card background*), sehingga teks hitam/gelap langsung berada di atas gambar sawah/pemandangan yang kontrasnya tidak merata (*degradasi kontras*).
   - Pengguna merasa bingung dan sulit membaca input form dan instruksi login.
2. **Tampilan Melebihi 1 Layar Penuh (Terdapat Scrollbar Vertikal):**
   - Padding container yang berlebihan (`py-5`) serta logo yang ditempatkan terpisah di luar area form menyebabkan total tinggi halaman melampaui 100vh pada resolusi standar (1080p, 900p, dan 768p laptop), memicu munculnya scrollbar samping kanan.
3. **Posisian Form Belum Menyerupai Kartu Melayang (*Floating Center Card*):**
   - Diinginkan layout tengah (*centered*) yang dinamis, rapi, melayang di tengah layar, tidak menutupi seluruh gambar background (gambar tetap terlihat mengelilingi kartu di 4 sisi), dengan kontras tajam dan estetika modern.

### ✨ Perubahan & Solusi
1. **Desain Ulang Kartu Melayang (*Floating Centered Card*) (`templates/login.mustache`):**
   - Mengintegrasikan Logo Perusahaan/Institusi (`theedu_logos.top_left`) langsung ke bagian atas kartu (*card header*), menciptakan satu kesatuan unit form login yang elegan dan kompak.
   - Kartu form login (`.theedu-login-card`) kini memiliki latar belakang solid dengan efek kaca modern (`background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(20px);`), radius sudut `1.5rem` (24px), serta bayangan melayang berdimensi ganda (`box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4)`).
   - Teks, judul, dan seluruh elemen di dalam kartu memiliki kontras maksimal `#0F172A` (Slate 900) dengan keterbacaan 100% sempurna tanpa terpengaruh oleh warna/kontur gambar di belakangnya.
2. **Optimalisasi 1 Layar Penuh Dinamis (*Single-Screen Viewport Fit*):**
   - Menghapus padding besar `py-5` dan menggantinya dengan layout viewport flexbox `100vh` dengan padding adaptif `1.25rem 1rem`.
   - Mengatur proporsi vertikal yang seimbang (total tinggi kartu hanya ~540px), sehingga pada layar standar 768p (laptop 14 inch) sekalipun, kartu tetap melayang sempurna di tengah dengan jarak atas 85px dan jarak bawah 133px tanpa pernah memicu scrollbar vertikal.
3. **Dukungan Dark Mode Penuh pada Kartu Melayang:**
   - Dalam Mode Gelap, kartu melayang otomatis beralih ke warna slate gelap (`rgba(24, 32, 47, 0.96)`) dengan border tipis berkilau (`rgba(255, 255, 255, 0.12)`) dan teks cerah `#F8FAFC`.
   - Menambahkan event listener instan langsung di `<script>` body untuk peralihan tema yang seketika (*zero-delay*).
4. **Penyempurnaan Integrasi Gambar Background (`lib.php`):**
   - Memastikan selector `#page-wrapper` di `lib.php` tidak tertimpa oleh rule warna latar global `body:not(.pagelayout-login)`.
   - Menghubungkan variabel context Mustache `theedu_login_bg` ke pengaturan `login_bg_url` sebagai fallback otomatis jika file belum diunggah.

---

## [v1.5.7] - 2026-11-09 (Build 2026110906)

### 🎯 Masalah yang Diselesaikan
1. **Tampilan Halaman Login Terpotong / Tumpang Tindih di Microsoft Edge:**
   - Pada browser Microsoft Edge, area bawah form login (seperti teks *"Some courses may allow guest access"* dan tombol *"Log in as a guest"*) terpotong secara horizontal oleh batas bawah kartu form login dan saling bertumpukan dengan tombol dark mode.
   - Di Google Chrome tampilan terlihat normal karena sedikit perbedaan kalkulasi margin/scrollbar, namun di Edge terjadi *layout clipping*.
   - **Penyebab Teknis:**
     1. Pada `templates/login.mustache`, `{{{ output.main_content }}}` sebelumnya dibungkus di dalam kartu tambahan `<div class="card p-4 ...">`, padahal Moodle core `loginform` sudah merender elemen `.card` miliknya sendiri (terjadi *double nested card*).
     2. Di `scss/post.scss`, selektor dasar `.card` memiliki aturan global `height: 100%; overflow: hidden;`. Di mesin layout Edge, kombinasi flexbox `min-vh-100` dan `height: 100% overflow: hidden` pada kartu bertingkat menyebabkan elemen instruksi tamu di bawah form terpotong (*clipped*).
2. **Background Halaman Login Tidak Tampil Sesuai Pengaturan:**
   - Meskipun administrator telah mengunggah gambar latar belakang pada pengaturan tema (*Login Page Background Image*), halaman login tetap berlatar belakang putih polos `#F7F9FC`.
   - **Penyebab Teknis:**
     1. Di `lib.php` pada fungsi `theme_theedu_get_inline_css()`, elemen `#page-wrapper` memiliki aturan CSS `background-color: var(--theedu-body-bg) !important;`. Elemen `#page-wrapper` membentang seluas layar penuh di atas `<body>`, sehingga menutupi gambar latar belakang `<body>` secara total dengan lapisan solid putih `#F7F9FC`.
     2. Di `lib.php`, pemanggilan `fs->get_area_files` sebelumnya mengunci parameter `itemid = 0`, sehingga file yang diunggah dengan draft itemid lain tidak terambil secara optimal.

### ✨ Perubahan & Solusi
1. **Pembersihan Struktur Kartu Login & Anti-Clipping Cross-Browser (`templates/login.mustache` & `scss/post.scss`):**
   - Menghapus pembungkus `.card` ganda di `templates/login.mustache` dan menggantinya dengan pembungkus bersih `<div class="theedu-login-box">`.
   - Menerapkan aturan `height: auto !important; min-height: auto !important; overflow: visible !important;` pada seluruh kartu di halaman login (`body.pagelayout-login .card`).
   - Merancang kontainer khusus yang terpisah dan rapi untuk area akses tamu (`.login-instructions`) dengan background transparan berdimensi (*glassmorphic card*) sehingga tidak akan pernah terpotong atau bertumpukan di Edge maupun Chrome.
2. **Perbaikan Transparansi `#page-wrapper` & Pemuatan Gambar Latar Login:**
   - Menetapkan aturan eksplisit `body.pagelayout-login #page-wrapper { background: transparent !important; background-color: transparent !important; }` di `lib.php` dan `templates/login.mustache`.
   - Gambar latar belakang login kini langsung tampil penuh (*center center / cover no-repeat fixed*) dengan overlay gradien gelap yang elegan agar teks form tetap terbaca jelas.
   - Jika belum ada gambar yang diunggah, tema otomatis menampilkan latar gradien edukasi modern (`#1E1B4B` ke `#6C5CE7`) alih-alih layar putih polos.
3. **Penambahan Opsi Alternatif URL Gambar Background Login (`login_bg_url`):**
   - Menambahkan kolom input `theme_theedu/login_bg_url` pada **Tab 1: Branding** di Site Administration, memberikan keleluasaan bagi administrator untuk memasukkan URL gambar langsung (misal: `https://...`) sebagai alternatif dari unggah berkas.

---

## [v1.5.6] - 2026-11-09 (Build 2026110905)

### 🎯 Masalah yang Diselesaikan
1. **Warna Navbar (`color_navbar_bg`) Tidak Berubah pada Moodle 4.5 LTS:**
   - Pada Moodle 4.5 LTS, saat administrator mengubah warna background navbar di *Site Administration*, warna navbar tetap putih dan tidak berganti, sedangkan di Moodle 5.3 berhasil.
   - **Penyebab Teknis:** Di `scss/_header.scss` baris 471, selector `.theedu-floating-header .navbar.fixed-top` sebelumnya menggunakan nilai hardcoded:
     `background-color: rgba(255, 255, 255, 0.92) !important;`
     Pada kompilasi SCSS Moodle 4.5, aturan ini menang atas selektor `.navbar.fixed-top` di inline CSS karena memiliki spesifisitas kelas induk ganda (`.theedu-floating-header .navbar.fixed-top`).

### ✨ Perubahan & Solusi
1. **Penggunaan Variabel CSS pada `_header.scss`:**
   - Mengubah deklarasi background di `scss/_header.scss` menjadi:
     `background-color: var(--theedu-navbar-bg, rgba(255, 255, 255, 0.92)) !important;`
2. **Penguatan Spesifisitas Selektor Inline CSS (`lib.php`):**
   - Menambahkan selektor tingkat tertinggi `#page-wrapper.theedu-floating-header .navbar.fixed-top` (spesifisitas ID + 2 Class: `1, 2, 0`) pada CSS dinamis di `lib.php`.
   - Menjamin warna background kustom langsung diterapkan secara instan pada Moodle 4.4, 4.5 LTS, maupun 5.x tanpa terblokir oleh aturan SCSS lama.

---

## [v1.5.5] - 2026-11-09 (Build 2026110904)

### 🎯 Masalah yang Diselesaikan
1. **HTML & CSS Muncul Sebagai Teks Kode Polos (Tidak Ter-compile) di Tab About Theme:**
   - Pada halaman *About Theme & Release Notes*, elemen UI tidak dirender sebagai tampilan visual (kotak gradien, pill badge, tabel), melainkan tercetak mentah sebagai teks kode:
     ```html
     <div class="theedu-about-container">
         <style>
         .theedu-about-container { ... }
     ```
   - **Penyebab Teknis:** Kelas bawaan Moodle `admin_setting_heading` selalu memproses parameter deskripsi/informasi melalui fungsi `format_text($information, FORMAT_MOODLE)`. Pemfilteran ini:
     1. Menjalankan *HTML Purifier* yang menghapus tag `<style>` dan atribut khusus.
     2. Mengubah teks yang berindentasi 4 spasi menjadi blok kode Markdown (`<pre><code>`), sehingga tag HTML di-escape menjadi entitas teks (`&lt;div...&gt;`).

### ✨ Perubahan & Solusi
1. **Pembuatan Kelas Khusus `theme_theedu_admin_setting_about` (`admin_setting`):**
   - Mendefinisikan kelas turunan resmi dari `admin_setting` di `settings.php` yang meng-override metode `output_html()`.
   - Metode `output_html()` mengembalikan string HTML murni secara langsung dari `theme_theedu_get_about_html()` ke Moodle renderer tanpa melewati `format_text()`.
   - **Hasil:** Browser langsung merender elemen HTML, efek CSS gradient hero card, pill badge, tabel spesifikasi sistem, dan riwayat changelog secara sempurna dan estetik.
2. **Dukungan Pencarian Administrasi Situs (`is_related`):**
   - Menambahkan kata kunci pencarian ('about', 'theedu', 'developer', 'release', 'notes', 'changelog', 'version') agar halaman ini mudah ditemukan melalui kolom pencarian *Site Administration*.

---

## [v1.5.4] - 2026-11-09 (Build 2026110903)

### 🎯 Masalah yang Diselesaikan
1. **Fatal Error: `Call to undefined function theme_theedu_get_about_html()` pada Site Administration:**
   - Saat membuka *Site Administration* Moodle, muncul pesan exception berwarna merah:
     ```
     Exception - Call to undefined function theme_theedu_get_about_html()
     ```
   - **Penyebab Teknis:** Loader pohon navigasi admin Moodle (`/lib/classes/plugininfo/theme.php`) menyertakan `theme/theedu/settings.php` secara terisolasi tanpa memuat berkas fungsi pustaka tema (`theme/theedu/lib.php`) terlebih dahulu. Akibatnya, pemanggilan fungsi `theme_theedu_get_about_html()` gagal karena fungsi tersebut belum didefinisikan dalam memori PHP saat `settings.php` dievaluasi.

### ✨ Perubahan & Solusi
1. **Inklusi Berkas Pustaka pada `settings.php` (`require_once`):**
   - Menambahkan `require_once(__DIR__ . '/lib.php');` di bagian paling atas berkas `settings.php`. Hal ini menjamin bahwa seluruh fungsi pembantu tema TheEdu selalu siap digunakan saat Moodle membangun navigasi administrasi.
2. **Defensive Function Guard (`function_exists`):**
   - Menyelimuti pemanggilan `theme_theedu_get_about_html()` dengan validasi defensif:
     ```php
     $about_html = function_exists('theme_theedu_get_about_html') ? theme_theedu_get_about_html() : '';
     ```
   - Mencegah kemungkinan terlemparnya PHP fatal error dalam kondisi apa pun.
3. **Penyempurnaan String Escaping:**
   - Mengganti `clean_text($CFG->release)` menjadi `s($CFG->release)` agar lebih cepat dan aman dari dependensi purifier yang belum dimuat.

---

## [v1.5.3] - 2026-11-09 (Build 2026110902)

### 🎯 Overview & User Request
- **Permintaan Pengguna:**
  "buat di halaman configurasi terkait about theme, isi ada 2 hal terkait developer & versi saat ini, sertakan change log dalam bahasa inggris (buat format provesional seperti release note setiap versi."
- **Tujuan Pengembangan:**
  1. Menambahkan tab konfigurasi khusus **"About Theme & Release Notes"** (Tab 10) di *Site Administration* Moodle (`settings.php`).
  2. Menyajikan informasi identitas developer (*Lead Developer*, Institusi/Organisasi, profil GitHub, repositori kode, dan lisensi GPL-3.0).
  3. Menampilkan spesifikasi teknis dan versi aktif secara dinamis (*dynamic version introspection* dari `version.php` dan `$CFG->release`).
  4. Menyertakan changelog / catatan rilis profesional berstandar enterprise dalam Bahasa Inggris (*English Release Notes*) dengan badge status kategorikal (`[New]`, `[Fixed]`, `[Improved]`, `[UI / UX]`, `[Security]`).

---

### ✨ Official Release Notes (English)

#### 🚀 Executive Summary
TheEdu Theme version **v1.5.3** introduces a centralized **About Theme & Release Notes** executive dashboard directly within Moodle Site Administration. This release equips administrators, institutional stakeholders, and DevOps engineers with instant visibility into developer credentials, licensing, runtime environment compatibility, offline asset guarantees, and a categorized chronological changelog spanning all historical milestones.

#### 🌟 Key Additions & Architectural Enhancements
1. **Dedicated "About Theme & Release Notes" Settings Tab (Tab 10):**
   - Registered as `theme_theedu_about` in Moodle Site Administration theme settings hierarchy.
   - Built with a modern, glassmorphic gradient Hero Banner (`#1E1B4B` to `#6C5CE7`) displaying theme branding, release tag pill, build number, maturity status, and an interactive GitHub repository pill button.
   - 100% responsive two-column metadata grid featuring:
     - **Developer & Project Information:** Author/Lead Developer (`Ferdiyanto Deni`), Organization (`PT Astra Daihatsu Motor - ADM Education Center`), GitHub Profile (`@ferdiyantodeni`), Source Code Repository, Open Source License (`GNU General Public License v3.0 or later`), and Asset Architecture verification.
     - **System Specifications & Compatibility:** Dynamically introspected version numbers, Moodle multi-branch compatibility (`Moodle 4.4, 4.5, 5.0, 5.1, 5.2, 5.3+`), active host environment, Boost core engine, Bootstrap 5 SCSS pipeline, local FontAwesome 6 Free, and instant dark mode engine.
2. **Dynamic Version Introspection (`theme_theedu_get_about_html`):**
   - Implemented dynamic file inspection of `version.php` inside `lib.php`, automatically reading `$plugin->version`, `$plugin->release`, and `$plugin->maturity` at runtime.
   - Eliminates manual version sync errors across administrative views.
3. **Professional Enterprise Changelog in English:**
   - Embedded structured release notes categorized with GitHub/Enterprise badges:
     - `[New]` / `[Feature]`: New capabilities, settings tabs, and UI modules.
     - `[Fixed]`: Critical bug resolutions, layout edge cases, and override fixes.
     - `[Improved]` / `[UI / UX]`: Design refinements, spacing harmonizations, and parity.
     - `[Security]` / `[Role]`: Access control, block isolation, and role-based views.
     - `[Offline]` / `[Core]`: Air-gapped intranet compatibility and zero-CDN guarantees.
4. **Dark Mode Integration & Scoped CSS:**
   - Full compatibility with TheEdu dark mode engine (`[data-theme="dark"]`), featuring elevated dark surface cards (`#1E2638`), subtle border contrasts, and crisp legible typography.

---

### 🇮🇩 Ringkasan Perubahan Bahasa Indonesia
1. **Tab Baru "About Theme & Release Notes" (Tab 10):**
   - Terintegrasi langsung pada menu pengaturan tema di *Administrasi Situs > Tampilan > Tema > TheEdu*.
   - Menggunakan banner gradien modern dengan logo TheEdu ("E"), badge versi dinamis, dan tombol pintas ke repositori GitHub.
2. **Kartu Informasi Developer & Organisasi:**
   - Pengembang Utama: Ferdiyanto Deni
   - Organisasi / Institusi: PT Astra Daihatsu Motor (ADM Education Center)
   - Tautan Profil GitHub: `https://github.com/ferdiyantodeni`
   - Tautan Repositori Proyek: `https://github.com/ferdiyantodeni/theme_theedu`
   - Lisensi: GNU General Public License v3.0 or later (GPL-3.0-or-later)
   - Jaminan Aset: 100% Self-Contained Offline Assets (Local Fonts & Local Icons, Zero CDN).
3. **Kartu Spesifikasi Teknis & Versi Sistem:**
   - Mengambil data versi aktif secara dinamis langsung dari `version.php` (`v1.5.3` / Build `2026110902`).
   - Kompatibilitas multi-versi Moodle 4.4, 4.5, 5.0, 5.1, 5.2, 5.3+.
   - Mendeteksi versi Moodle host aktif melalui `$CFG->release`.
4. **Riwayat Rilis Profesional dalam Bahasa Inggris (Release Notes):**
   - Disusun runtut dari versi terbaru v1.5.3 hingga versi pondasi awal v1.0.0.
   - Menggunakan badge kategori warna-warni berstandar GitHub/Enterprise (`[New]`, `[Fixed]`, `[Improved]`, `[UI / UX]`, `[Security]`).

---

## [v1.5.2] - 2026-11-09 (Build 2026110901)

### 🎯 Masalah yang Diselesaikan
1. **Penggantian Warna Background Navbar (`color_navbar_bg`) Tidak Berfungsi:**
   - Ketika administrator mengganti warna *Navbar Background Color* pada **Tab 2: Brand Colors & Presets**, perubahan warna tidak diterapkan pada bilah navigasi atas (tetap putih `#FFFFFF`).
   - **Penyebab:** Fungsi resolusi warna `theme_theedu_get_color()` sebelumnya memprioritaskan array palet `brand_preset` aktif (di mana nilai default untuk semua preset adalah `'navbar_bg' => '#FFFFFF'`). Selama `brand_preset` tidak diubah ke `'custom'`, pengaturan kustom `color_navbar_bg` diabaikan sepenuhnya.
2. **Teks Statis "THEEDU LEARNING" Muncul pada Banner Slider Beranda:**
   - Pada kartu caption slider beranda (*Slider Carousel*), terdapat badge pill merah bertuliskan `"THEEDU LEARNING"` yang tersemat secara statis di atas judul slide.
   - Tidak ada kolom pengaturan di Site Administration untuk mengubah teks tersebut atau menghilangkannya, sehingga mengganggu personalisasi portal institusi/perusahaan (seperti Daihatsu).

### ✨ Perubahan & Solusi
1. **Perbaikan Resolusi Warna Kustom Navbar & Area UI (`theme_theedu_get_color`):**
   - Memperbarui logika `theme_theedu_get_color()` di `lib.php` agar secara cerdas mendeteksi dan memprioritaskan warna yang dikonfigurasi administrator di Site Administration (`color_navbar_bg`, `color_navbar_text`, `color_body_bg`, `color_card_bg`, `color_footer_bg`, dll.) jika nilainya berbeda dari default preset, tanpa mewajibkan pengguna mengubah pilihan preset brand utama ke 'custom'.
   - Memperkuat spesifisitas selector CSS inline navbar di `theme_theedu_get_inline_css()` untuk mencakup `.navbar`, `.navbar.fixed-top`, `nav.fixed-top`, `header.navbar`, seluruh ikon pintasan, toggler, notifikasi, dan pill profil pengguna.
2. **Penghapusan Hardcoded "THEEDU LEARNING" & Penambahan Setting `slide_badge_{$i}`:**
   - Menghapus teks statis `<span class="slide-badge">TheEdu Learning</span>` dari `templates/slider.mustache`.
   - Menambahkan variabel pengaturan baru di **Tab 4 (Homepage Carousel Slider)** di Site Administration untuk setiap slide:
     - `theme_theedu/slide_badge_{$i}` (*Slide {$a} Badge / Tagline*).
   - **Hasil Default:** Kolom badge bernilai kosong secara default, sehingga badge merah "THEEDU LEARNING" **HILANG SEPENUHNYA** dan judul slide tampil bersih dan lega.
   - **Fleksibilitas Kustomisasi:** Jika pengguna ingin menambahkan badge (misalnya "DAIHATSU", "PELATIHAN", "NEWS", dll.), mereka cukup mengisi teks tersebut di pengaturan slide bersangkutan.

---

## [v1.5.1] - 2026-11-08 (Build 2026110821)

### 🎯 Masalah yang Diselesaikan
1. **Daftar Kategori / Subkategori Berbentuk Dropdown Folder yang Membosankan:**
   - Navigasi dan daftar kategori bawaan Moodle (baik di halaman katalog `/course/index.php`, pohon kategori `.course_category_tree`, maupun Beranda) sebelumnya berpenampilan seperti struktur folder Windows klasik dengan ikon folder kuning/biru (`\f07b`) dan teks berlekuk.
   - Tampilan ini kaku, tidak dinamis, dan tidak selaras dengan bahasa desain kartu modern TheEdu yang bersih dan elegan.
2. **Ketiadaan Opsi Gambar Thumbnail Kategori & Subkategori yang Fleksibel:**
   - Belum ada kemampuan untuk mengaktifkan atau menonaktifkan area gambar thumbnail (*thumbnail image banner*) pada kartu kategori dan subkategori.
   - Pengguna menginginkan opsi yang sama untuk tampilan kategori utama maupun subkategori, di mana area thumbnail dapat diaktifkan/dinonaktifkan melalui menu pengaturan tema (*Site Administration*).

### ✨ Perubahan & Solusi
1. **Model Kartu Kategori & Subkategori Modern (Pengganti Dropdown Folder):**
   - Mengemas seluruh daftar kategori dan subkategori menjadi kartu modern yang seirama dengan kartu kursus (*Course Cards*):
     - `border-radius: 1.25rem` (20px rounded card).
     - Efek hover halus `translateY(-5px)` dengan bayangan lembut berdimensi (`0 14px 32px rgba(108, 92, 231, 0.15)`).
     - Menghapus ikon folder lawas (`\f07b`) seutuhnya, digantikan oleh tata letak kartu visual yang dinamis.
     - Dilengkapi tombol aksi panah sirkular (`->`) yang responsif saat kartu disorot cursor.
2. **Area Banner Thumbnail Kategori yang Dapat Diaktifkan / Dinonaktifkan (Toggleable):**
   - **Mode Thumbnail Aktif (`enable_category_thumbnails = 1`):**
     - Menampilkan area banner gambar di bagian atas kartu.
     - **Ekstraksi Otomatis Gambar Kategori:** Mesin `theme_theedu_get_category_thumbnail_url()` secara cerdas mendeteksi gambar yang disematkan dalam deskripsi kategori (`<img>`) atau file storage Moodle (`coursecat/description`).
     - **Dynamic Colorful Education Gradient Fallback:** Jika kategori belum memiliki gambar unggahan, tema secara otomatis menghasilkan banner gradien edukasi warna-warni yang estetik (8 palet gradien dinamis berbasis ID kategori: Indigo, Emerald Teal, Violet, Amber, Ocean Blue, Rose, Slate, Crimson) lengkap dengan ikon FontAwesome.
     - **Overlaid Badges:** Menampilkan badge pill transparan elegan (*Category* / *Subcategory*) di kiri atas dan indikator jumlah kursus di kanan atas banner.
   - **Mode Thumbnail Nonaktif / Compact (`enable_category_thumbnails = 0` / style `compact`):**
     - Kartu bertransformasi menjadi model ringkas (*Compact Icon Badge Mode*) dengan kotak ikon pastel berwarna, badge tipe kategori, deskripsi singkat, jumlah kursus, dan tombol panah tanpa memakan ruang banner atas.
3. **Penyelarasan Opsi Kategori & Subkategori (Unified Features):**
   - Kategori induk maupun subkategori memiliki fitur dan opsi identik:
     - `show_category_course_count`: Menampilkan pill total kursus (`<i class="fa fa-book-open"></i> X Kursus`).
     - `show_category_subcat_count`: Menampilkan pill subkategori (`<i class="fa fa-sitemap"></i> X Sub`).
     - `show_category_desc`: Menampilkan ringkasan deskripsi kategori.
     - `category_thumbnail_height`: Mengatur ketinggian banner gambar (default 140px).
4. **Pembaruan Pengaturan Tab 5 di Site Administration:**
   - Menambahkan section baru **Category & Subcategory Card Presentation** di Tab 5 (*Homepage Categories & Highlights*):
     - `enable_category_thumbnails` (Checkbox, default: Aktif).
     - `category_card_style` (Select: *Thumbnail Banner Area* / *Compact Icon Badge*).
     - `category_thumbnail_height` (Input angka dalam pixel, default: 140px).
     - `show_category_course_count` (Checkbox, default: Aktif).
     - `show_category_subcat_count` (Checkbox, default: Aktif).
     - `show_category_desc` (Checkbox, default: Aktif).
5. **Dukungan Halaman Beranda & Katalog Kursus:**
   - Diperbarui pada `templates/frontpage_categories.mustache` untuk render server-side template.
   - Diperkaya melalui progressive enhancement script `enrichCategoryCards()` di `drawers.mustache` dan `frontpage.mustache` untuk merender pohon kategori Moodle core pada `/course/index.php` secara otomatis.
   - Dukungan penuh Dark Mode untuk seluruh elemen kartu kategori.

---

## [v1.5.0] - 2026-11-08 (Build 2026110820)

### 🎯 Masalah yang Diselesaikan
1. **Menu / Blok "Navigation" dan "Administration" Muncul untuk Guest Non-Login di Beranda:**
   - Pada halaman Beranda (Home), blok navigasi Moodle ("Navigation" block) muncul bagi pengunjung tamu (*guest* yang belum login), serta blok administrasi ("Administration" block) tampil di bawah konten sehingga mengganggu estetika dan kerapian portal.
   - Belum tersedia variabel pengaturan tema untuk menyembunyikan atau menampilkan blok-blok Beranda secara selektif berdasarkan kategori peran pengguna.
2. **Kebutuhan Pengaturan Hak Akses Multilevel Berdasarkan Kategori User:**
   - Administrator memerlukan opsi terstruktur di Site Administration untuk menentukan elemen Beranda mana saja yang boleh dilihat oleh:
     - Tamu / Belum Login (*Guest / Non-login*)
     - Pengguna Terdaftar Biasa (*Registered Users / Non-admin*)
     - Pengguna Login (*Logged-in Users: Mahasiswa, Dosen, Guru, Admin*)
     - Administrator Situs (*Site Administrators Only*)
     - Disembunyikan Sepenuhnya (*Disabled / Hidden*)

### ✨ Perubahan & Solusi
1. **Peningkatan Mesin Evaluasi Visibilitas Peran (`theme_theedu_is_visible_to_user`):**
   - Memperluas fungsi helper `theme_theedu_is_visible_to_user()` di `lib.php` dengan dukungan penuh 6 kategori akses:
     - `all`: Semua pengunjung (tamu maupun login).
     - `guest`: Khusus pengunjung tamu yang belum login.
     - `loggedin`: Semua pengguna yang sudah login (mahasiswa, pengajar, admin).
     - `registered_only`: Khusus pengguna login biasa (non-admin).
     - `admin`: Khusus Administrator Situs (`is_siteadmin()`).
     - `none`: Disembunyikan sepenuhnya.
2. **Variabel Pengaturan Baru: Visibilitas Blok Beranda (`theme_theedu/frontpage_blocks_visibility`):**
   - Ditambahkan pada Tab 6 (*Enrolled Courses & Frontpage Content*) di Site Administration.
   - Nilai default diset ke **`admin` (Site Administrators Only)**.
   - **Hasil:** Pengunjung Tamu (*Guest*) dan Mahasiswa/Karyawan biasa secara otomatis **TIDAK AKAN** melihat blok "Navigation" maupun "Administration" di Beranda. Halaman Beranda menjadi bersih, rapi, dan profesional.
3. **Variabel Pengaturan Baru: Visibilitas Konten Inti Beranda (`theme_theedu/frontpage_content_visibility`):**
   - Mengontrol siapa yang dapat melihat daftar/aktivitas bawaan Moodle di Beranda.
4. **Variabel Pengaturan Baru: Menu Utama Navbar untuk Guest (`theme_theedu/navbar_guest_visibility`):**
   - Ditambahkan pada Tab 1 (*Branding & Logos*): Admin dapat memilih untuk menampilkan menu utama pada navbar tamu (`show`), atau menyembunyikannya (`hide`) agar tampilan tamu hanya menyisakan Logo dan tombol Login yang elegan.
5. **Dukungan Moodle Core Editing Mode:**
   - Blok Beranda tetap muncul secara interaktif saat Administrator mengaktifkan **Edit mode** sehingga penambahan dan penghapusan blok dapat dilakukan tanpa kendala.

---

## [v1.4.9] - 2026-11-08 (Build 2026110819)

### 🎯 Masalah yang Diselesaikan
1. **Jarak Navbar >> Konten Masih Melebar (> 20px) di Halaman Beranda (Home/Frontpage):**
   - Pengguna melaporkan bahwa di beberapa halaman (khususnya Beranda/Home) celah antara navbar dan konten kartu masih terlihat renggang (~50px).
   - **Penyebab:** Pada rilis sebelumnya (v1.4.8), perbaikan 20px hanya dispesifikasikan untuk selector `#page.drawers`. Pada halaman Beranda/Frontpage (`theedu-frontpage-container`), kontainer masih memiliki warisan CSS `padding-top: 6rem` (96px) pada `.theedu-main-content` dan `margin-top: 1.5rem` (24px) pada `.theedu-slider-wrapper`, sehingga jarak bersih dari batas bawah navbar (70px) menjadi `120px - 70px = 50px`.
2. **Jarak Konten >> Footer Masih Melebar (> 100px) di Bawah Kartu:**
   - Di bagian bawah kartu konten menuju footer, jarak ruang kosong terlihat sangat lebar (~104px), tidak konsisten dengan target 20px.
   - **Penyebab:** Terjadi akumulasi spasi dari `margin-top: 3rem` (48px) pada `#page-footer`, `padding-bottom: 3.5rem` (56px) pada `.theedu-main-content`, `mb-5` (48px) pada seksi kartu terakhir, serta `margin-bottom: 20px` pada `.main-inner`.
3. **Pemeriksaan Menyeluruh Seluruh Halaman & Potensi Bertumpuk (*Overlapping*):**
   - Pengguna meminta agar standarisasi jarak **Navbar >> Konten (20px)** dan **Konten >> Footer (20px)** berlaku universal untuk SEMUA halaman (Frontpage, Dashboard, My courses, Course content, Site administration, Categories, Activities, dll.), dinamis mengikuti menu pengaturan Margin, serta bebas dari potensi tabrakan/tumpukan elemen (*stacking bugs*).

### ✨ Perubahan & Solusi
1. **Universal Top Spacing Tepat 20px untuk Semua Halaman:**
   - Selector diperluas mencakup semua halaman: `#page.drawers`, `#page.theedu-frontpage-container`, dan `#page:not(.drawers)`.
   - Mengatur `margin-top: 70px !important;` (dan `58px` pada mobile) dengan `padding-top: var(--theedu-content-margin-top, 20px) !important;`.
   - Menghapus `margin-top` pada `.theedu-slider-wrapper`, `.theedu-course-search-section`, `.main-inner`, dan seksi awal, serta mengatur `padding-top: 0 !important;` pada `.theedu-main-content`.
   - **Hasil:** Di SEMUA halaman, jarak antara batas bawah floating navbar dan elemen konten pertama presisi tepat **20px**.
2. **Universal Bottom Spacing Tepat 20px ke Footer untuk Semua Halaman:**
   - Menetapkan `#page-footer { margin-top: var(--theedu-content-margin-bottom, 20px) !important; margin-bottom: 0 !important; }`.
   - Mengatur `margin-bottom: 0 !important; padding-bottom: 0 !important;` pada `#page.drawers`, `.main-inner`, `#page-content`, dan `.theedu-frontpage-container`.
   - Mengatur `margin-bottom: 0 !important;` pada seksi terakhir di dalam `.theedu-main-content` (`:last-child` / `:last-of-type`) dan menonaktifkan area konten core frontpage (`.theedu-frontpage-region-main`) jika kosong.
   - **Hasil:** Di SEMUA halaman, jarak bersih antara batas bawah kartu konten dan batas atas footer presisi tepat **20px**.
3. **Audit Bebas Tabrakan & Bertumpuk (Zero Overlap & Stacking Guarantee):**
   - **Navbar vs Konten Scroll:** Navbar memiliki `position: fixed; z-index: 1040; backdrop-filter: blur(16px);`. Konten di bawahnya memiliki `z-index: 1` sehingga dapat di-scroll dengan sangat halus di bawah navbar tanpa tabrakan teks maupun gangguan klik.
   - **Dropdown & Notifikasi Navbar:** Tetap mengapung bebas di layer teratas (`z-index: 1060; overflow: visible;`), tidak terpotong oleh batas navbar maupun tertutup oleh kartu konten.
   - **Drawer Kiri & Kanan (Course Index & Blocks):** Menggunakan `z-index: 1050;`. Di desktop, kartu konten otomatis menciut (*auto-shrink*) saat drawer dibuka; di mobile, drawer hadir dengan backdrop isolasi rapi.
   - **Tombol Melayang (Edit Mode Switch):** Ditempatkan di kanan bawah dengan `z-index: 1030;`, bebas dari benturan dengan footer dan konten.

---

## [v1.4.8] - 2026-11-08 (Build 2026110818)

### 🎯 Masalah yang Diselesaikan
1. **Jarak Atas (1) Masih Terlalu Lebar:**
   - Gap antara bagian bawah *Floating Navbar* dan kartu konten utama (`.main-inner`) masih dirasa terlalu berjarak. Pengguna menginginkan jarak presisi sekitar **20px**.
2. **Kiri (2) dan Kanan (3) Kartu Konten Mengalami Kekosongan Luas:**
   - Pada halaman Dashboard, My Courses, dan Course Content (`/course/view.php`), kartu konten terpotong sempit di tengah (`max-width: 830px` akibat `body.limitedwidth` bawaan Boost), menyisakan ruang kosong raksasa di sisi kiri dan kanan.
   - Pengguna meminta agar lebar kartu konten menyamakan lebar *Floating Navbar* (1440px) dengan margin sisi kiri-kanan yang presisi (20px), serta **otomatis menciut (*auto-shrink*)** saat drawer menu kiri (Course Index) atau drawer kanan (Blocks) dibuka.
3. **Jarak Konten dengan Footer Berpotensi Melebar:**
   - Memastikan batas bawah antara kartu konten utama dan footer tetap rapi dan konsisten berjarak **20px**.
4. **Dukungan Kustomisasi Tanpa Rubah Kode (Menu Pengaturan Baru "Margin"):**
   - Pengguna meminta penambahan tab pengaturan baru di Site Administration agar seluruh nilai margin, lebar konten, padding kartu, dan jarak footer dapat disesuaikan di kemudian hari tanpa harus memodifikasi kode sumber.

### ✨ Perubahan & Solusi
1. **Perampingan Jarak Atas Tepat 20px (Navbar to Content):**
   - Floating navbar TheEdu berada di posisi `top: 14px` dengan tinggi `56px` (batas bawah navbar tepat di koordinat `70px`).
   - `#page.drawers` ditetapkan memiliki `margin-top: 70px !important;` dan `padding-top: var(--theedu-content-margin-top, 20px) !important;` serta menghapus `margin-top` bawaan Boost pada `.main-inner` (`margin-top: 0 !important;`).
   - Hasilnya, celah vertikal antara navbar dan kartu konten menjadi tepat **20px** yang presisi dan bersih.
2. **Ekspansi Lebar Konten Menyamai Navbar & Responsif Menciut (Drawer Auto-Shrink):**
   - Meng-override pembatas `max-width: 830px` bawaan Boost (`.pagelayout-standard`, `.pagelayout-course`, `.pagelayout-incourse`, `.pagelayout-mydashboard`, `body.limitedwidth`, `body.mediumwidth`).
   - Kartu konten utama `.main-inner` kini memiliki `width: 100% !important;` dan `max-width: var(--theedu-content-max-width, 1440px) !important;` dengan padding luar kiri-kanan sebesar `20px` selaras dengan *Floating Navbar*.
   - Karena kontainer `#page.drawers` secara otomatis mendapatkan `margin-left: 285px` atau `margin-right: 315px` saat drawer dibuka, maka `.main-inner` dengan `width: 100%` secara alami akan **menciut (*shrink*)** secara proporsional dan elegan tanpa merusak elemen di dalamnya.
3. **Kontrol Jarak Bawah ke Footer (20px):**
   - Menetapkan `margin-bottom: var(--theedu-content-margin-bottom, 20px) !important;` pada `.main-inner`, mencegah timbulnya celah putih berlebih di atas footer.
4. **Tab Pengaturan Baru: "Margin & Spacing" di Pengaturan Tema:**
   - Menambahkan Tab ke-7 di `settings.php` bertajuk **"Margin & Spacing"** (`tab_margin` / `theme_theedu_margin`).
   - Dilengkapi 6 opsi pengaturan intuitif:
     - **Jarak Navbar ke Konten (px)** (`theme_theedu/content_margin_top`, default: `20`)
     - **Samakan Lebar Konten dengan Navbar (Toggle)** (`theme_theedu/enable_full_width_content`, default: `1`)
     - **Lebar Maksimum Konten (px)** (`theme_theedu/content_max_width`, default: `1440`)
     - **Jarak Sisi Kiri & Kanan (px)** (`theme_theedu/content_side_padding`, default: `20`)
     - **Jarak Konten ke Footer (px)** (`theme_theedu/content_margin_bottom`, default: `20`)
     - **Padding Dalam Kartu Konten (px)** (`theme_theedu/content_card_padding`, default: `24`)
   - Nilai pengaturan ini langsung diinjeksi sebagai CSS Custom Properties (`--theedu-content-margin-top`, dll.) melalui engine `theme_theedu_get_inline_css()` di `lib.php` dan `scss/_header.scss`.

---

## [v1.4.7] - 2026-11-08 (Build 2026110817)

### 🎯 Masalah yang Diselesaikan
1. **Menu Notifikasi (Lonceng) & Profil Tidak Muncul di Mobile:**
   - Pada layar perangkat seluler / smartphone (`@media (max-width: 768px)`), dropdown notifikasi dan menu profil pengguna tidak muncul atau terpotong bagian bawahnya seolah tertimpa.
   - **Penyebab:** Pada `.navbar.fixed-top` terdapat aturan `overflow: hidden !important;` pada breakpoint mobile, sehingga elemen dropdown (`.popover-region-container` dan `.dropdown-menu`) yang muncul di bawah batas 58px navbar langsung terpotong secara permanen.
2. **Navigasi Aktivitas Kursus (Prev, Next, Jump-to) Hilang:**
   - Pada halaman aktivitas kursus (seperti membaca materi PDF `/mod/resource/view.php`, review kuis `/mod/quiz/review.php`, tugas, video, dll.), tidak tersedia tombol navigasi Sebelumnya (*Previous*), Selanjutnya (*Next*), maupun dropdown *Jump to topic/activity* di bawah konten aktivitas.

### ✨ Perubahan & Solusi
1. **Perbaikan Tuntas Dropdown Navbar Mobile:**
   - Menghapus `overflow: hidden !important;` dan menetapkan `overflow: visible !important;` pada `.navbar.fixed-top` dan `.theedu-header-actions` pada breakpoint mobile.
   - Mengatur posisi dan ukuran `.popover-region-container` dan `.usermenu .dropdown-menu` di mobile dengan `z-index: 1060`, `max-width: calc(100vw - 32px)`, bayangan lembut, dan sudut membulat elegan (*rounded pill*), menjamin menu notifikasi dan profil terbuka sempurna tanpa terpotong.
2. **Implementasi Bar Navigasi Bawah Aktivitas Kursus (Universal Course Activity Navigation):**
   - Menambahkan template [`activity_nav.mustache`](file:///d:/6.%20Project/15.%20App/TheEdu_Theme/templates/activity_nav.mustache) dan helper `theme_theedu_get_activity_navigation()` di `lib.php`.
   - **Tombol Sebelumnya (Prev):** Menampilkan nama aktivitas sebelumnya dengan tombol pill outline berikon panah kiri. Jika berada di aktivitas pertama, otomatis mengarah kembali ke Beranda Kursus (*Back to Course*).
   - **Dropdown Jump-To:** Dropdown dinamis berisi seluruh bab/topik dan aktivitas kursus dengan pengelompokan `<optgroup>` per bagian kursus serta penanda aktivitas saat ini (`▶`). Memilih topik langsung melompat (*jump*) ke aktivitas tersebut.
   - **Tombol Selanjutnya (Next):** Menampilkan tombol dengan gradien warna primer TheEdu berikon panah kanan menuju materi/kuis selanjutnya. Jika di aktivitas terakhir, menampilkan tombol sukses (*Finish / Back to Course*).
   - Dilengkapi dukungan Dark Mode penuh dan toggle pengaturan di *Site administration > Appearance > Themes > TheEdu > Enrolled Courses & Card Display*.

---

## [v1.4.6] - 2026-11-08 (Build 2026110816)

### 🎯 Masalah yang Diselesaikan
- Jarak (gap) kosong antara bagian bawah *Floating Navbar* dan kartu konten utama (*Main Inner / Site administration / Dashboard / Course*) pada layout drawers (`#page.drawers`) terlalu lebar (~108px).
- **Penyebab:** 
  - CSS bawaan Moodle Boost core sudah memiliki `margin-top: 60px` pada `#page.drawers` dan `margin-top: 1.5rem` (24px) pada `.main-inner`.
  - Tema TheEdu sebelumnya menambahkan `padding-top: 6rem` (96px) pada `#page.drawers`, sehingga total jarak akumulatif menjadi `60px + 96px + 24px - 72px (navbar) = 108px`, menimbulkan ruang kosong yang terlalu luas dan tidak proporsional di bawah navbar.

### ✨ Perubahan & Solusi
1. **Penyempitan Jarak Gap Menjadi 50%:**
   - Nilai `padding-top` pada `.theedu-floating-header #page.drawers` dipangkas dari `6rem` menjadi `2.75rem` (dan default dari `4.75rem` menjadi `1.5rem`).
   - Jarak bersih dari batas bawah floating navbar ke tepi atas kartu konten berkurang dari **~108px** menjadi **~52px** (tepat 50% lebih ramping dan proporsional).
2. **Optimalisasi Responsif Mobile:**
   - Menyesuaikan `@media (max-width: 768px)` untuk `#page.drawers` dari `5rem` menjadi `2rem`, memastikan tampilan di layar tablet dan ponsel tetap rapi tanpa scroll kosong berlebih.
3. **Sinkronisasi Langsung ke Inline CSS & SCSS:**
   - Perubahan diterapkan pada `scss/_header.scss` dan diinjeksi via `theme_theedu_get_inline_css()` di `lib.php` sehingga pengguna langsung melihat perubahan tata letak secara instan bahkan sebelum cache tema di-purge.

---

## [v1.4.5] - 2026-11-08 (Build 2026110815)

### 🎯 Masalah yang Diselesaikan
- Pada halaman kategori kursus (`/course/index.php`), tampilan kartu kursus masih berbeda dengan kartu "My Enrolled Courses" di Beranda:
  1. **Gambar terpotong/kecil (100x100px):** Pada kursus bergambar (misal: Horenso), gambar muncul terpotong kecil di sebelah kiri dengan area gradien kosong di sebelah kanannya.
     *Root cause:* CSS bawaan Moodle Boost core memiliki aturan spesifik `.coursebox .content .courseimage img { max-width: 100px; max-height: 100px; }` yang membatasi gambar secara paksa sehingga mengabaikan `width: 100%`.
  2. **Badge Tag Kategori Absen:** Pada halaman kategori, Moodle tidak merender badge tag kategori di pojok kiri atas gambar seperti pada "My Enrolled Courses".
  3. **Footer Guru Belum Seragam:** Nama pengajar tidak memiliki label subtitle "Teacher", dan tidak ada tombol panah aksi melingkar (circular arrow button `->`) di sebelah kanan footer kartu.

### ✨ Perubahan & Solusi
1. **Override Tuntas CSS Boost `max-width: 100px`:**
   - Menambahkan override `max-width: 100% !important; max-height: none !important; min-width: 100% !important; min-height: 100% !important;` pada `.coursebox .courseimage img` dan `.coursebox .content .courseimage img`.
   - Menjamin gambar sampul kursus selalu melebar penuh 100% x 180px dengan `object-fit: cover !important` di semua halaman tanpa terpotong atau menyisakan ruang kosong.
2. **Badge Tag Kategori Dinamis (`.course-tag`):**
   - Menambahkan injeksi otomatis badge kategori melalui template `drawers.mustache` dan `frontpage.mustache`.
   - Mengambil nama kategori aktif secara dinamis dari konteks halaman atau elemen kategori kursus dan merendernya dalam bentuk pil putih elegan dengan efek `backdrop-filter: blur(8px)` dan bayangan lembut di pojok kiri atas thumbnail.
3. **Penyetaraan Footer Pengajar & Tombol Panah Aksi:**
   - Menambahkan subtitle peran "Teacher" (`.coursebox .teachers li a::after { content: "Teacher" !important; }`).
   - Menambahkan tombol aksi panah melingkar modern (`.coursebox .teachers::after`) dengan efek hover interaktif (berubah menjadi warna primer bertransisi halus).
   - Menambahkan fungsi *Card Click Navigation*: mengklik area kartu atau tombol panah otomatis membuka kursus yang bersangkutan.
4. **Dukungan Dark Mode Penuh:**
   - Badge `.course-tag`, teks subtitle guru, dan tombol panah aksi secara otomatis beradaptasi dengan warna kontras pada Dark Mode.

---

## [v1.4.4] - 2026-11-08 (Build 2026110814)

### 🎯 Masalah yang Diselesaikan
- Pada daftar kursus Moodle (`.coursebox`), kursus yang memiliki gambar sampul (*Course image*) dan kursus yang tidak memiliki gambar memiliki bentuk yang berbeda secara drastis:
  - **Kursus bergambar (misal: Horenso):** Judul kursus berada di bagian paling atas, dan gambarnya muncul di tengah dalam ukuran kecil/gepeng dan menyisakan ruang kosong di kanan.
  - **Kursus tanpa gambar (misal: General Orientation):** Gradien pastel dan ikon topi toga berada di paling atas, lalu judul kursus berada di bawahnya.
- Penyebab: Struktur DOM asli Moodle `.coursebox` meletakkan `<div class="info">` (judul) di atas `<div class="content">`, dan elemen gambar `<div class="courseimage">` terperangkap di dalam `<div class="content"><div class="d-flex">`.

### ✨ Perubahan & Solusi
1. **Penyetaraan Struktur Kartu Kursus (Universal Course Card):**
   - Menerapkan CSS `display: contents !important;` pada `.coursebox > .content` dan `.coursebox > .content > .d-flex` untuk meratakan hierarki flexbox tanpa perlu memodifikasi PHP core Moodle renderer.
   - Menggunakan CSS Flexbox Reordering:
     - `order: 1 !important` untuk `.courseimage` dan `:not(:has(.courseimage))::before` (Top Banner Area).
     - `order: 2 !important` untuk `.info` (Judul Kursus).
     - `order: 3 !important` untuk `.flex-grow-1` (Ringkasan & Footer Guru).
2. **Auto Panjang x Lebar untuk Gambar Sampul:**
   - `.coursebox .courseimage`: Diberikan `width: 100% !important; height: 180px !important; overflow: hidden !important; border-top-left-radius: 1.25rem !important; border-top-right-radius: 1.25rem !important;`.
   - `.coursebox .courseimage img`: Diberikan `width: 100% !important; height: 100% !important; object-fit: cover !important;` sehingga gambar apapun otomatis memenuhi area banner kartu tanpa gepeng.
3. **Fallback Topi Toga Konsisten:**
   - Jika kursus tidak memiliki gambar (`.coursebox:not(:has(.courseimage))`), otomatis memunculkan banner gradien pastel 180px dengan topi toga (`fa-graduation-cap`) di dalam kotak putih mengambang berbayang (`backdrop-filter: blur(8px)`).
4. **Cakupan Menyeluruh:**
   - Aturan ini berlaku untuk semua coursebox di Moodle: Beranda (`#frontpage-available-course-list`), Kategori/Sub-kategori (`/course/index.php`), dan Hasil Pencarian (`/course/search.php`).

---

## [v1.4.3] & [v1.4.2] - 2026-11-08 (Build 2026110813 / 2026110812)

### 🎯 Masalah yang Diselesaikan
- Terjadi *fatal coding exception* saat membuka halaman Beranda (Home):
  ```
  Coding error detected, it must be fixed by a programmer: page layout file 
  [dirroot]/theme/theedu/layout/frontpage.php does not contain the main content placeholder, 
  please include " <?php echo $OUTPUT->main_content() ?> " in theme layout file.
  ```
- Penyebab: Mesin Moodle (`core_renderer::render_page_layout()`) mewajibkan method `$OUTPUT->main_content()` selalu dieksekusi pada setiap file layout. Pada v1.4.1, tag `{{{ output.main_content }}}` dibungkus kondisi `{{#show_main_content}}`. Ketika opsi *"Sembunyikan Konten Duplikat Moodle di Beranda"* aktif, Mustache melewati blok tersebut sehingga Moodle mendeteksi placeholder tidak pernah dipanggil.

### ✨ Perubahan & Solusi
1. **Garansi Eksekusi Wajib Moodle:**
   - Menghapus pembungkus `{{#show_main_content}}` dari luar `<section id="region-main">`.
   - Tag `{{{ output.main_content }}}` kini **selalu dieksekusi** tanpa syarat di dalam `templates/frontpage.mustache` sehingga internal flag Moodle `$this->maincontentdone` selalu bernilai `true`.
2. **Sembunyikan Secara Visual via CSS:**
   - Membungkus output konten core ke dalam `<div class="theedu-core-frontpage-content theedu-hide-core-content">`.
   - CSS `.theedu-core-frontpage-content.theedu-hide-core-content { display: none !important; }` menyembunyikan konten secara visual dari pengunjung tanpa memicu error Moodle.
3. **Kompatibilitas Mode Edit (Edit Mode ON):**
   - Menambahkan aturan CSS: `body.editing .theedu-core-frontpage-content.theedu-hide-core-content { display: block !important; }`.
   - Ketika Admin menyalakan mode edit, area aktivitas dan tombol penambahan blok Moodle otomatis muncul kembali agar admin leluasa mengelola beranda.

---

## [v1.4.1] - 2026-11-08 (Build 2026110811)

### 🎯 Permintaan Pengguna
1. Kontrol tampilan halaman depan (Home Dashboard) agar tidak terlalu banyak area bertumpuk dan tidak rapi.
2. Saat kategori kursus diklik (`/course/index.php`), sub-kategori muncul sebagai kartu (*cards*), hingga ke level kursus yang tampilannya seragam dengan kartu *"My Enrolled Courses"*.
3. Pembatasan hak akses yang jelas antara Tamu (Non-User / Guest), Pengguna Terdaftar (Siswa / Karyawan), dan Administrator.

### ✨ Perubahan & Solusi
1. **Transformasi Halaman Kategori Kursus (`/course/index.php`):**
   - Sub-kategori (`.course_category_tree .subcategories .category`) diubah menjadi grid kartu ber-ikon folder FontAwesome pastel (`fa-folder-open`) dengan badge jumlah kursus berbentuk kapsul.
   - Daftar kursus (`.courses .coursebox`) diubah menjadi layout kartu responsif dengan thumbnail gambar/toga, judul tebal, ringkasan, dan footer instruktur.
   - Toolbar atas (`#action_bar`) untuk dropdown kategori dan form pencarian kursus didesain melengkung (*pill shape*) modern.
2. **Pengaturan Visibilitas Target Audiens per Komponen:**
   - Menambahkan dropdown pengaturan visibilitas di `settings.php`:
     - `slider_visibility`: Semua / Terdaftar / Tamu / Nonaktif.
     - `search_visibility`: Semua / Terdaftar / Tamu / Nonaktif.
     - `categories_visibility`: Semua / Terdaftar / Tamu / Nonaktif.
     - `courses_visibility`: Semua / Terdaftar / Tamu / Nonaktif.
   - Dikelola melalui fungsi pembantu baru di `lib.php`: `theme_theedu_is_visible_to_user($visibility, $isloggedin)`.
3. **Pembatasan Hak Akses (Role-Based Access Boundaries):**
   - **Tamu (Guest):** Header hanya memuat Logo, Home, Dark Mode, dan tombol Login. Ikon My Courses (toga), Notifikasi (lonceng), Pesan, dan menu profil disembunyikan. Link footer yang mengarah ke `/admin/` atau `/my/` (Dashboard) otomatis difilter dan disembunyikan.
   - **User Terdaftar (Siswa):** Memiliki akses ke Dashboard, My Courses, Notifikasi, Pesan, dan profil. Tombol *Site Administration* dan tombol *Floating Edit Mode* disembunyikan.
   - **Admin:** Memiliki akses penuh ke *Site Administration* dan tombol *Floating Edit Mode* di bagian bawah tengah layar.

---

## [v1.4.0] - 2026-11-08 (Build 2026110810)

### 🎯 Masalah yang Diselesaikan
- Tampilan form pencarian kursus di beranda belum dinamis dan kurang estetis.
- Pada kartu kursus di beranda / drawer, nama pengajar (*teacher*) belum keluar.
- Sisi kanan kartu kursus menampilkan badge `[[enrolled]]` (teks string mentah yang bocor karena Moodle core string tidak ditemukan).
- Pengguna meminta ikon pengajar bawaan yang representatif agar dapat dijadikan contoh untuk role lain (*non-editing teacher*, *manager*, dsb.), dengan data ditarik dari *course contact*.

### ✨ Perubahan & Solusi
1. **Redesain Form Pencarian Kursus Beranda (`frontpage_search.mustache`):**
   - Desain kartu pencarian melayang (*floating search card*) dengan latar kaca transparan (*glassmorphism*).
   - Form pencarian berbentuk kapsul (*pill shape*) dengan ikon kaca pembesar dan tombol aksi biru pastel.
   - Tag pencarian cepat (*Quick Topic Pills*) yang dapat diklik langsung untuk memfilter kursus.
2. **Arsitektur Ekstraksi Pengajar (`theme_theedu_get_course_instructor_details`):**
   - Dibuat fungsi di `lib.php` yang secara tangguh menarik instruktur kursus:
     - Prioritas 1: Memeriksa *course contact* resmi yang diatur di Moodle `$CFG->coursecontact`.
     - Prioritas 2 (Fallback): Memeriksa penugasan peran langsung di konteks kursus pada database Moodle (`mdl_role_assignments`) untuk arketipe `editingteacher`, `teacher`, `manager`, atau `coursecreator`.
   - Memetakan peran ke ikon FontAwesome dan label peran terlokalisasi:
     - Editing Teacher: Ikon `fa-chalkboard-user`
     - Non-Editing Teacher: Ikon `fa-user-pen`
     - Manager: Ikon `fa-user-tie`
     - Course Creator: Ikon `fa-user-gear`
3. **Pembersihan Footer Kartu Kursus:**
   - Menghapus badge `[[enrolled]]` yang rusak.
   - Menggantikannya dengan tombol panah melingkar (`.course-card-arrow-btn`) menuju halaman kursus.
   - Menampilkan avatar foto pengajar (jika diunggah) atau ikon bulat peran pengajar beserta nama lengkapnya.

---

## [v1.3.5] - 2026-11-08 (Build 2026110809)

### 🎯 Masalah yang Diselesaikan
- Slider banner beranda tidak muncul penuh di situs `https://quranalbarkah.co-id.id/welearn/`, hanya muncul ujung/garis tipis di bagian atas.
- Penyebab: Layout bawaan Boost menerapkan pembatas kontainer (`limitedwidth`, `.main-inner`, drawer margin) dan offset fixed-top navbar yang menekan tinggi slider menjadi kolaps.

### ✨ Perubahan & Solusi
1. **Dedicated Frontpage Layout (`layout/frontpage.php` & `templates/frontpage.mustache`):**
   - Memisahkan layout beranda dari `drawers.php` menjadi layout beranda khusus penuh lebar (*full-width portal landing*).
2. **Spesifikasi CSS Slider Responsif:**
   - Menetapkan tinggi minimum slider: 480px di desktop, 380px di tablet, dan 320px di mobile.
   - Menetapkan `object-fit: cover` pada gambar banner slider sehingga selalu mengisi penuh frame.
   - Mengatur offset padding-top dari navbar fixed-top agar banner tidak tertimpa navbar.

---

## [v1.3.0] - 2026-11-07 (Build 2026110708)

### 🎯 Permintaan Pengguna
- Penambahan Preset Tema Warna Brand (kombinasi pastel).
- Penambahan 1 banner slider default agar tidak kosong saat pertama kali diinstal.
- Perbaikan kartu kategori yang tidak tampil.
- Penambahan opsi pengaturan footer (menu tengah dan menu kanan bawah yang sebelumnya tidak bisa diubah).

### ✨ Perubahan & Solusi
1. **Sistem Preset Warna Brand Pastel (`brand_preset`):**
   - Menambahkan 7 opsi preset warna di `settings.php`:
     - *Default*: Lavender Purple Pastel (`#6C5CE7`) & Mint (`#00CEC9`)
     - *Preset Merah*: Soft Red Coral (`#E74C3C`) & Peach Pastel (`#FAB1A0`)
     - *Preset Biru*: Cerulean Sky (`#0984E3`) & Soft Cyan (`#74B9FF`)
     - *Preset Hijau*: Emerald Mint (`#00B894`) & Soft Turquoise (`#55EFC4`)
     - *Preset White*: Minimalist Clean White & Slate Dark Accent
     - *Preset Grey*: Modern Slate Grey (`#636E72`) & Cool Steel (`#B2BEC3`)
     - *Preset Yellow*: Warm Honey Amber (`#E17055`) & Pastel Butter (`#FFEAA7`)
2. **Banner Slider Default:**
   - Menambahkan logika fallback di `lib.php` dan `settings.php`: jika belum ada slide yang diunggah, otomatis menampilkan 1 slide sampel bertema *"Welcome to WeLearn"* dengan tombol *"Explore Courses"*.
3. **Menu Kustom Footer:**
   - `footer_middle_title`: Judul kolom tautan tengah footer.
   - `footer_middle_links`: Tautan menu tengah multi-baris (`Judul|URL`).
   - `footer_bottom_right_mode`: Pilihan antara Menu Kustom, Link Bawaan Moodle, atau Keduanya.
   - `footer_bottom_right_links` & `footer_bottom_right_text`: Teks dan tautan kustom sudut kanan bawah.

---

## [v1.2.0] - 2026-11-07 (Build 2026110705)

### 🎯 Masalah yang Diselesaikan
- Pada tampilan mobile (layar HP), tombol navigasi header bertumpuk dan saling menimpa (misal: tombol switch Dark Mode menimpa ikon lain).

### ✨ Perubahan & Solusi
1. **Optimasi Navbar Mobile Responsif:**
   - Menyembunyikan teks judul situs di layar kecil (`d-none d-xxl-inline-block`) saat logo gambar aktif agar tidak menghabiskan lebar navbar.
   - Memindahkan menu navigasi utama ke dalam drawer hamburger di mobile (`d-none d-md-flex` pada navbar).
   - Menata jarak (*gap*) antar ikon di sisi kanan navbar menggunakan Bootstrap flex gap (`gap-1 gap-sm-2`).
   - Menetapkan ukuran maksimal tinggi logo di navbar (`max-height: 44px`) agar tidak mendorong tinggi header.

---

## [v1.1.0] - 2026-11-06 (Build 2026110603)

### 🎯 Masalah yang Diselesaikan
- Pengaturan warna di Site Administration tidak berefek langsung ke halaman web karena kompilasi SCSS Moodle meng-cache berkas CSS.

### ✨ Perubahan & Solusi
1. **Dynamic Inline CSS Injection (`theme_theedu_get_inline_css`):**
   - Menambahkan fungsi di `lib.php` yang membaca nilai warna langsung dari database (`theme_theedu_get_color()`).
   - Menghasilkan blok `<style id="theedu-dynamic-colors">` yang berisi CSS Variables (`:root { --theedu-primary: ... }`).
   - Disuntikkan langsung ke header HTML pada `drawers.php` dan `frontpage.php` sehingga perubahan warna langsung terlihat seketika (**Live Update**) tanpa perlu me-refresh SCSS compiler.
2. **Pencegahan Flash of Unstyled Content (FOUC):**
   - Menambahkan skrip inline di bagian atas `<body>` yang membaca `localStorage.getItem('theedu_theme_mode')` sebelum DOM selesai dirender, mencegah efek kedip putih saat membuka halaman di mode gelap.

---

## [v1.0.0] - 2026-11-06 (Build 2026110601)

### 🚀 Initial Release - Fondasi Tema TheEdu
- Basis tema mewarisi arsitektur resmi **Moodle Boost** (`theme_boost`), kompatibel dengan Moodle versi 4.4, 4.5, 5.0, 5.1, 5.2, hingga 5.3+.
- **Sistem 4 Posisi Logo Sekaligus:**
  - Logo 1: Kiri-Atas (Navbar Brand)
  - Logo 2: Kanan-Atas (Header Partner / Akreditasi)
  - Logo 3: Kiri-Bawah (Footer Main Brand)
  - Logo 4: Kanan-Bawah (Footer Sertifikasi / Mitra)
- **Sistem Dark Mode:** Sakelar instan Matahari/Bulan dengan modul AMD JavaScript `theme_theedu/darkmode`.
- **Hero Slider Carousel Beranda:** Carousel gambar dinamis dengan *glassmorphism caption* dan tombol CTA.
- **Font Lokal:** Dukungan font offline woff2 (Inter, Plus Jakarta Sans, Poppins) tanpa ketergantungan koneksi internet luar.
- **Paket Bahasa Lengkap:** Tersedia dalam Bahasa Indonesia (`lang/id/theme_theedu.php`) dan Bahasa Inggris (`lang/en/theme_theedu.php`).

---

## 📌 Ringkasan File Kunci untuk Pengembang Berikutnya

Bagi AI Agent / Programmer yang melanjutkan pemeliharaan tema ini:

1. **`lib.php`**: Berisi seluruh logika inti tema:
   - `theme_theedu_get_inline_css()`: Generator live CSS variables, dark mode styling, dan reordering kartu kursus `.coursebox`.
   - `theme_theedu_get_template_context()`: Penyiapan data konteks Mustache (logo, slider, footer, dsb.).
   - `theme_theedu_get_course_instructor_details()`: Logika ekstraksi data guru/instruktur dari database Moodle.
   - `theme_theedu_is_visible_to_user()`: Logika pemfilteran hak akses komponen (Tamu vs Siswa vs Admin).
2. **`settings.php`**: Definisi panel admin Moodle (*Site administration > Appearance > Themes > TheEdu*).
3. **`layout/frontpage.php` & `templates/frontpage.mustache`**: Halaman Beranda modern mandiri. **PENTING:** Tag `{{{ output.main_content }}}` WAJIB selalu dipanggil di sini untuk mematuhi regulasi mesin layout Moodle.
4. **`layout/drawers.php` & `templates/drawers.mustache`**: Layout drawer standar untuk Dashboard, Kursus dalam, dan Kategori.
5. **`version.php`**: Versi berkas dan versi database Moodle (`YYYYMMDDXX`). Selalu naikkan versi di sini saat merilis perbaikan agar Moodle menjalankan proses upgrade.
