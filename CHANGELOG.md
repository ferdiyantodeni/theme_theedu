# CHANGELOG - TheEdu Moodle Theme

Dokumen ini mencatat seluruh riwayat pembaruan, perubahan arsitektur, perbaikan bug, dan evolusi fitur tema **TheEdu** (`theme_theedu`) dari awal pengembangan hingga versi terbaru. 

Format dokumentasi ini disusun secara rinci dan terstruktur agar dapat dijadikan konteks langsung oleh AI model lain (ChatGPT, Claude, Gemini, dll.) saat melanjutkan pengembangan tema di masa mendatang.

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
