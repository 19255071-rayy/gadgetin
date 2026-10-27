<?php
/**
 * functions.php: kumpulan fungsi bantu (format, data HP, foto, keranjang, pesanan).
 */

/* ============================== FORMAT ============================== */

// Escape teks agar aman ditampilkan di HTML (mencegah XSS)
function e($text): string
{
    // Ubah karakter khusus HTML menjadi entitas
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Ubah angka menjadi format Rupiah, contoh 8999000 menjadi Rp8.999.000
function rupiah(int $amount): string
{
    // Pakai titik sebagai pemisah ribuan
    return 'Rp' . number_format($amount, 0, ',', '.');
}

// Ubah teks menjadi slug untuk nama folder/file, contoh "Titanium Black" menjadi "titanium-black"
function slug(string $text): string
{
    // Huruf kecil semua
    $text = strtolower($text);
    // Ganti semua karakter selain huruf dan angka dengan tanda hubung
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Buang tanda hubung di awal dan akhir
    return trim($text, '-');
}

/* ============================== DATA HP ============================== */

// Ambil semua HP dari phones.php (dibaca sekali lalu disimpan di memori)
function load_phones(): array
{
    // Variabel static menyimpan hasil antar pemanggilan
    static $phones = null;
    // Baca file hanya jika belum pernah dibaca
    if ($phones === null) {
        $phones = require ROOT_PATH . '/includes/phones.php';
    }
    // Kembalikan seluruh daftar HP
    return $phones;
}

// Cari satu HP berdasarkan id, kembalikan null bila tidak ada
function find_phone(int $id): ?array
{
    // Telusuri semua HP satu per satu
    foreach (load_phones() as $phone) {
        // Kembalikan HP yang idnya cocok
        if ($phone['id'] === $id) {
            return $phone;
        }
    }
    // Tidak ditemukan
    return null;
}

// Daftar merek unik, berurutan sesuai kemunculan
function brand_list(): array
{
    // Ambil kolom merek, buang duplikat, rapikan indeks
    return array_values(array_unique(array_column(load_phones(), 'brand')));
}

// Saring HP berdasarkan kata kunci dan merek
function filter_phones(string $query, string $brand): array
{
    // Simpan hanya HP yang lolos kedua syarat
    return array_filter(load_phones(), function (array $p) use ($query, $brand): bool {
        // Syarat merek: kosong berarti semua merek
        $okBrand = ($brand === '' || $p['brand'] === $brand);
        // Syarat kata kunci: cocok di nama, merek, atau seri
        $okQuery = ($query === '' || stripos($p['name'] . ' ' . $p['brand'] . ' ' . $p['series'], $query) !== false);
        // Lolos bila keduanya terpenuhi
        return $okBrand && $okQuery;
    });
}

// Urutkan daftar HP: murah, mahal, rating, atau terbaru (default)
function sort_phones(array $list, string $sort): array
{
    // Bandingkan dua HP sesuai pilihan urutan
    usort($list, fn(array $a, array $b): int => match ($sort) {
        'murah'  => $a['price'] <=> $b['price'],
        'mahal'  => $b['price'] <=> $a['price'],
        'rating' => $b['rating'] <=> $a['rating'],
        default  => $b['id'] <=> $a['id'],
    });
    // Kembalikan daftar yang sudah terurut
    return $list;
}

// Gabungkan label spesifikasi dengan nilainya menjadi pasangan label => nilai
function phone_specs(array $phone): array
{
    // Label dari config, nilai dari phones.php
    return array_combine(SPEC_LABELS, $phone['specs']);
}

/* ============================== FOTO HP ============================== */

// Cari file foto di folder tertentu; kembalikan URL relatif atau null
function find_image_file(string $folder, string $basename): ?string
{
    // Coba setiap ekstensi sesuai prioritas
    foreach (IMG_EXT as $ext) {
        // Alamat file di server
        $path = ROOT_PATH . '/' . IMG_DIR . "/$folder/$basename.$ext";
        // Jika file ada, kembalikan alamat untuk dipakai di browser
        if (is_file($path)) {
            return IMG_DIR . "/$folder/$basename.$ext";
        }
    }
    // Tidak ada file dengan nama itu
    return null;
}

// Foto sebuah HP untuk warna tertentu: foto warna, lalu foto "utama", lalu null
function phone_photo(array $phone, string $colorName): ?string
{
    // Nama folder berasal dari nama HP
    $folder = slug($phone['name']);
    // Coba foto sesuai warna, jika tidak ada pakai foto "utama"
    return find_image_file($folder, slug($colorName)) ?? find_image_file($folder, 'utama');
}

// Ilustrasi HP berwarna (SVG) dipakai bila foto belum tersedia
function phone_svg(string $hex): string
{
    // Pastikan kode warna aman di dalam atribut HTML
    $c = e($hex);
    // Gambar bodi, layar, dan kamera depan
    return '<svg viewBox="0 0 200 150" role="img" aria-label="Ilustrasi handphone">'
        . '<rect class="tint" x="64" y="8" width="72" height="134" rx="14" fill="' . $c . '" stroke="rgba(0,0,0,.18)"/>'
        . '<rect x="70" y="16" width="60" height="118" rx="9" fill="#fff" opacity=".92"/>'
        . '<circle cx="100" cy="24" r="2.5" fill="' . $c . '"/></svg>';
}

// Tampilan HP: foto bila ada, jika tidak ilustrasi SVG
function phone_visual(array $phone, int $colorIndex = 0): string
{
    // Ambil nama dan kode hex warna yang dipilih
    [$colorName, $hex] = $phone['colors'][$colorIndex];
    // Cari foto untuk warna tersebut
    $photo = phone_photo($phone, $colorName);
    // Jika foto ada, tampilkan sebagai gambar
    if ($photo !== null) {
        return '<img src="' . e($photo) . '" alt="' . e($phone['name'] . ' warna ' . $colorName) . '" loading="lazy">';
    }
    // Jika tidak ada, tampilkan ilustrasi
    return phone_svg($hex);
}

/* ============================== KERANJANG ============================== */

// Daftar isi keranjang lengkap dengan data HP, warna, dan subtotal
function cart_items(): array
{
    // Hasil akhir
    $items = [];
    // Telusuri isi keranjang di session
    foreach (($_SESSION['cart'] ?? []) as $key => $row) {
        // Cari data HP-nya
        $phone = find_phone($row['id']);
        // Lewati bila HP atau warna sudah tidak valid
        if ($phone === null || !isset($phone['colors'][$row['c']])) {
            continue;
        }
        // Susun satu baris keranjang
        $items[] = [
            'key'   => $key,
            'phone' => $phone,
            'color' => $phone['colors'][$row['c']],
            'qty'   => $row['q'],
            'sub'   => $phone['price'] * $row['q'],
        ];
    }
    // Kembalikan seluruh baris
    return $items;
}

// Total jumlah unit di keranjang (untuk lencana di menu)
function cart_count(): int
{
    // Jumlahkan kolom qty
    return (int) array_sum(array_column($_SESSION['cart'] ?? [], 'q'));
}

// Ongkir: gratis bila keranjang kosong atau belanja mencapai batas
function shipping_fee(int $subtotal): int
{
    // Tentukan apakah gratis ongkir
    return ($subtotal === 0 || $subtotal >= FREE_SHIPPING_MIN) ? 0 : SHIPPING_FEE;
}

// Proses aksi keranjang dari form POST; kembalikan halaman tujuan redirect
function cart_apply_action(array $post): string
{
    // Aksi yang diminta: add, buy, remove, atau qty
    $action = $post['action'] ?? '';
    // Kunci baris keranjang (untuk remove/qty)
    $key = $post['key'] ?? '';

    // Tambah ke keranjang atau beli sekarang
    if (in_array($action, ['add', 'buy'], true)) {
        // Id HP dan indeks warna yang dipilih
        $id = (int) ($post['id'] ?? 0);
        $ci = (int) ($post['c'] ?? 0);
        // Pastikan HP dan warna valid
        $phone = find_phone($id);
        if ($phone !== null && isset($phone['colors'][$ci])) {
            // Kunci unik per kombinasi HP dan warna
            $k = $id . '-' . $ci;
            // Tambah satu unit, dibatasi MAX_QTY
            $qty = min(MAX_QTY, ($_SESSION['cart'][$k]['q'] ?? 0) + 1);
            // Simpan ke session
            $_SESSION['cart'][$k] = ['id' => $id, 'c' => $ci, 'q' => $qty];
        }
    } elseif ($action === 'remove') {
        // Hapus satu baris dari keranjang
        unset($_SESSION['cart'][$key]);
    } elseif ($action === 'qty' && isset($_SESSION['cart'][$key])) {
        // Jumlah baru dari form
        $qty = (int) ($post['q'] ?? 1);
        // Jumlah di bawah 1 berarti hapus, selain itu simpan (maksimal MAX_QTY)
        if ($qty < 1) {
            unset($_SESSION['cart'][$key]);
        } else {
            $_SESSION['cart'][$key]['q'] = min(MAX_QTY, $qty);
        }
    }

    // "Beli sekarang" langsung ke checkout, aksi lain kembali ke keranjang
    return $action === 'buy' ? 'checkout.php' : 'cart.php';
}

/* ============================== KEAMANAN ============================== */

// Input tersembunyi berisi token CSRF untuk dipasang di setiap form POST
function csrf_field(): string
{
    // Token diambil dari session
    return '<input type="hidden" name="t" value="' . e($_SESSION['t']) . '">';
}

// Cek token CSRF yang dikirim form sama dengan token di session
function csrf_ok(): bool
{
    // hash_equals aman terhadap serangan timing
    return hash_equals($_SESSION['t'], $_POST['t'] ?? '');
}

/* ============================== PESANAN ============================== */

// Validasi data checkout; kembalikan daftar pesan error (kosong berarti valid)
function validate_checkout(array $f): array
{
    // Daftar error
    $errors = [];
    // Nama minimal 3 huruf
    if (mb_strlen($f['name']) < 3) {
        $errors['name'] = 'Isi nama lengkap (minimal 3 huruf).';
    }
    // Nomor HP Indonesia: hapus spasi/strip lalu cocokkan pola 08xx / 62xx / +62xx
    if (!preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', preg_replace('/[\s-]/', '', $f['phone']))) {
        $errors['phone'] = 'Isi nomor HP yang valid, contoh 081234567890.';
    }
    // Alamat minimal 10 karakter
    if (mb_strlen($f['address']) < 10) {
        $errors['address'] = 'Isi alamat lengkap (minimal 10 karakter).';
    }
    // Metode pembayaran harus salah satu dari daftar
    if (!isset(PAYMENT_METHODS[$f['pay']])) {
        $errors['pay'] = 'Pilih metode pembayaran.';
    }
    // Kembalikan hasil
    return $errors;
}

// Simpan pesanan ke session dan kembalikan nomor pesanan
function create_order(array $f, array $items): string
{
    // Hitung subtotal, ongkir, dan total
    $sub   = array_sum(array_column($items, 'sub'));
    $ship  = shipping_fee($sub);
    // Nomor pesanan: GI + tanggal + 6 karakter acak
    $ref   = 'GI' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
    // Simpan hanya data yang perlu ditampilkan di struk
    $_SESSION['orders'][$ref] = [
        'ref'    => $ref,
        'time'   => time(),
        'cust'   => $f,
        'items'  => array_map(fn(array $i): array => [
            'name'  => $i['phone']['name'],
            'color' => $i['color'][0],
            'q'     => $i['qty'],
            'sub'   => $i['sub'],
        ], $items),
        'sub'    => $sub,
        'ship'   => $ship,
        'total'  => $sub + $ship,
        'pay'    => $f['pay'],
        // COD langsung dianggap berhasil dibuat, metode lain menunggu pembayaran
        'status' => $f['pay'] === 'cod' ? 'cod' : 'pending',
        // Nomor Virtual Account contoh (acak, bukan nomor asli)
        'va'     => '8077' . random_int(10000000, 99999999),
    ];
    // Kosongkan keranjang setelah pesanan dibuat
    unset($_SESSION['cart']);
    // Kembalikan nomor pesanan
    return $ref;
}
