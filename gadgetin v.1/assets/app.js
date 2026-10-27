/* app.js: interaksi sisi browser (ganti foto/warna, simulasi proses bayar) */

// ---------- 1. Ganti foto dan warna di halaman detail HP ----------

// Ambil semua pilihan warna (kosong jika bukan halaman detail)
const swatches = document.querySelectorAll('.colors input');
// Ambil kotak gambar utama
const art = document.getElementById('art');
// Ambil elemen foto asli
const photo = document.getElementById('photo');
// Ambil pembungkus ilustrasi SVG
const drawing = document.getElementById('drawing');
// Ambil teks nama warna di atas pilihan
const colorName = document.getElementById('cname');

// Tampilkan foto, atau ilustrasi berwarna bila foto tidak ada
function showVariant(imgUrl, hex, name) {
  // Ubah warna latar tipis kotak gambar
  art.style.setProperty('--tint', hex);
  // Ubah teks nama warna
  colorName.textContent = name;
  // Cat ulang bodi ilustrasi dengan warna terpilih
  drawing.querySelectorAll('.tint').forEach(el => el.setAttribute('fill', hex));
  // Jika ada foto untuk warna ini
  if (imgUrl) {
    // Pasang alamat foto
    photo.src = imgUrl;
    // Munculkan foto dan sembunyikan ilustrasi
    photo.hidden = false;
    drawing.hidden = true;
  } else {
    // Sembunyikan foto dan munculkan ilustrasi
    photo.hidden = true;
    drawing.hidden = false;
  }
}

// Jalankan hanya bila elemen yang dibutuhkan ada di halaman
if (art && photo && drawing && colorName) {
  // Pasang pendengar pada setiap pilihan warna
  swatches.forEach(radio => {
    // Saat warna dipilih, ganti tampilan
    radio.addEventListener('change', () => showVariant(radio.dataset.img, radio.dataset.hex, radio.dataset.name));
  });
  // Jika file foto rusak atau gagal dimuat, kembali ke ilustrasi
  photo.addEventListener('error', () => {
    // Sembunyikan foto
    photo.hidden = true;
    // Tampilkan ilustrasi
    drawing.hidden = false;
  });
}

// ---------- 2. Simulasi proses pembayaran ----------

// Ambil form bayar dan layar loading (hanya ada di halaman pembayaran)
const payForm = document.getElementById('payform');
const procScreen = document.getElementById('proc');

// Jalankan hanya bila keduanya ada
if (payForm && procScreen) {
  // Saat form bayar dikirim
  payForm.addEventListener('submit', event => {
    // Tahan pengiriman langsung
    event.preventDefault();
    // Tampilkan layar loading
    procScreen.hidden = false;
    // Setelah 1,8 detik, kirim form sungguhan
    setTimeout(() => payForm.submit(), 1800);
  });
}
