<?php
/**
 * config.php: pengaturan global situs.
 * Ubah nilai di sini tanpa perlu menyentuh file lain.
 */

// Nama situs yang tampil di judul tab dan logo
const SITE_NAME = 'Gadget.in';

// Biaya kirim standar (Rupiah)
const SHIPPING_FEE = 20000;

// Belanja minimal (Rupiah) agar ongkir gratis
const FREE_SHIPPING_MIN = 3000000;

// Jumlah maksimal satu varian HP per pesanan
const MAX_QTY = 5;

// Folder foto HP (relatif terhadap root situs)
const IMG_DIR = 'assets/img/phones';

// Ekstensi foto yang dicari, berurutan dari prioritas tertinggi
const IMG_EXT = ['webp', 'jpg', 'jpeg', 'png'];

// Nama label untuk 7 nilai spesifikasi di phones.php (urutannya harus sama)
const SPEC_LABELS = ['Layar', 'Chipset', 'RAM / Memori', 'Kamera belakang', 'Baterai', 'Sistem operasi', 'Bobot'];

// Metode pembayaran: kode => nama yang ditampilkan
const PAYMENT_METHODS = [
    'va_bca'     => 'Virtual Account BCA',
    'va_mandiri' => 'Virtual Account Mandiri',
    'qris'       => 'QRIS (GoPay, OVO, DANA)',
    'cod'        => 'Bayar di tempat (COD)',
];
