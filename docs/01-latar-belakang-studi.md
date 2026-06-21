# Latar Belakang Studi

## 1. Pendahuluan

Media sosial telah menjadi salah satu saluran komunikasi yang digunakan organisasi untuk menyampaikan informasi, membangun identitas brand, dan menjaga interaksi dengan audiens. Aktivitas tersebut membutuhkan perencanaan konten yang teratur karena setiap brand dapat memiliki jadwal publikasi, tipe konten, platform, materi visual, dan status pengerjaan yang berbeda.

Dalam proses kerja yang belum terpusat, data perencanaan konten sering tersebar pada catatan pribadi, dokumen, aplikasi pesan, atau lembar kerja yang berbeda. Kondisi ini menimbulkan beberapa kendala, antara lain sulitnya mengetahui jadwal publikasi terdekat, risiko data antarbrand tercampur, ketidakjelasan status pengerjaan, serta kesulitan menemukan kembali gambar dan dokumen pendukung. Ketika jumlah brand dan jadwal bertambah, pencarian dan pembaruan data secara manual menjadi semakin tidak efisien.

Permasalahan tersebut menunjukkan kebutuhan terhadap aplikasi berbasis basis data yang mampu menyimpan dan menghubungkan data pengguna, brand, tipe konten, jadwal publikasi, serta media pendukung secara terstruktur. Basis data relasional diperlukan agar setiap jadwal tetap terhubung dengan brand dan pemilik yang benar, sementara object storage digunakan untuk menyimpan file media tanpa membebani basis data dengan data biner berukuran besar.

IMM Content Planner dikembangkan sebagai aplikasi internal untuk memenuhi kebutuhan tersebut. Aplikasi menyediakan workspace terpisah untuk setiap brand, kalender bulanan, tampilan timeline dan feed, filter berdasarkan tipe serta status, pengingat jadwal mendatang, pengelolaan media, dan keluaran PDF. Dengan pendekatan ini, data perencanaan konten dapat dikelola dalam satu sistem yang konsisten, mudah dicari, dan memiliki kontrol akses berdasarkan pengguna.

## 2. Identifikasi Masalah

Masalah yang menjadi dasar pengembangan aplikasi adalah:

1. Data perencanaan konten belum tersimpan dalam struktur yang terpusat dan konsisten.
2. Jadwal dan aset beberapa brand berisiko tercampur apabila tidak memiliki pemisahan data yang jelas.
3. Pengguna kesulitan memantau konten yang sudah dibuat dan yang masih harus dikerjakan.
4. Jadwal publikasi terdekat tidak mudah diketahui dari kumpulan data yang tersebar.
5. Gambar, logo, dan dokumen pendukung sulit dikaitkan kembali dengan jadwal yang sesuai.
6. Penyusunan ringkasan konten untuk ditinjau atau dibagikan masih membutuhkan pekerjaan manual.
7. Data perlu dilindungi agar hanya pemilik yang terautentikasi dan aktif yang dapat mengaksesnya.

## 3. Rumusan Masalah

Rumusan masalah pada proyek ini adalah:

1. Bagaimana merancang basis data yang dapat memisahkan data setiap pengguna dan brand?
2. Bagaimana mengelola jadwal, tipe konten, platform, status, dan media pendukung dalam satu aplikasi?
3. Bagaimana menyajikan data bulanan melalui kalender, timeline, feed, statistik, dan pengingat?
4. Bagaimana menyediakan akses media dan dokumen PDF secara aman dan mudah digunakan?
5. Bagaimana menjaga integritas data ketika brand, jadwal, atau media diubah dan dihapus?

## 4. Tujuan Pengembangan

Tujuan pengembangan IMM Content Planner adalah:

1. Membangun aplikasi pengelolaan perencanaan konten berbasis basis data.
2. Membuat pemisahan workspace dan data berdasarkan pemilik brand.
3. Menyediakan proses tambah, lihat, ubah, dan hapus untuk brand serta jadwal konten.
4. Menampilkan jadwal secara informatif melalui kalender, timeline, feed, statistik, dan pengingat.
5. Mendukung tipe konten bawaan maupun tipe kustom pada setiap brand.
6. Mengelola gambar dan logo melalui filesystem lokal atau Cloudflare R2.
7. Menghasilkan preview dan file PDF sebagai ringkasan jadwal konten.
8. Menerapkan autentikasi, otorisasi, validasi, dan sanitasi data.

## 5. Batasan Sistem

Ruang lingkup proyek dibatasi sebagai berikut:

- Sistem digunakan sebagai aplikasi internal, bukan platform publik untuk klien.
- Pengguna harus login dan memiliki status akun aktif.
- Registrasi mandiri tidak tersedia; akun disediakan oleh administrator sistem.
- Setiap brand hanya dimiliki oleh satu pengguna.
- Jadwal berisi tanggal, waktu, tipe konten, platform, headline, detail, catatan, status, media, dan tautan dokumen.
- Platform yang tersedia pada implementasi saat ini adalah Instagram dan TikTok.
- Setiap jadwal dapat memiliki maksimal 12 gambar dengan ukuran maksimal 5 MB per file.
- File media disimpan di object storage atau filesystem; basis data hanya menyimpan metadata dan object key.
- Aplikasi tidak melakukan publikasi otomatis ke media sosial.
- PDF berfungsi sebagai ringkasan atau dokumen kerja, bukan dokumen transaksi.

## 6. Manfaat Sistem

### Bagi Pengguna

- Memusatkan seluruh perencanaan konten dalam satu aplikasi.
- Mempermudah pencarian jadwal dan media berdasarkan brand serta periode.
- Memperjelas prioritas pekerjaan melalui status dan pengingat.
- Mengurangi penginputan ulang ketika menyusun ringkasan konten.

### Bagi Organisasi

- Menjaga struktur data perencanaan konten agar lebih konsisten.
- Mempermudah pemantauan pekerjaan beberapa brand.
- Mengurangi risiko kehilangan atau tercampurnya aset konten.
- Menyediakan dasar data yang dapat dikembangkan untuk pelaporan lebih lanjut.

### Bagi Pembelajaran Basis Data

- Menerapkan relasi satu-ke-banyak, foreign key, indeks, dan unique constraint.
- Menerapkan operasi CRUD dan transaksi basis data.
- Memisahkan data terstruktur pada basis data dari file pada object storage.
- Menerapkan integritas referensial melalui penghapusan berantai.
- Menghubungkan basis data dengan autentikasi, otorisasi, filter, agregasi, dan pembuatan laporan.

## 7. Kesimpulan

IMM Content Planner dibuat untuk mengatasi pengelolaan jadwal konten yang tersebar dan sulit dipantau. Sistem mengintegrasikan data pengguna, brand, tipe konten, jadwal, serta media pendukung dalam rancangan basis data yang terstruktur. Implementasi ini diharapkan meningkatkan keteraturan proses kerja sekaligus menjadi penerapan nyata konsep pengembangan aplikasi basis data.
