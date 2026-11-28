<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---- Publik ----
$routes->get('/', 'Home::index');
$routes->get('tentang', 'Home::tentang');
$routes->get('penginapan', 'Hotel::index');
$routes->get('penginapan/(:segment)', 'Hotel::show/$1');
$routes->get('peta', 'Peta::index');
$routes->get('kuliner', 'Kuliner::index');
$routes->get('kuliner/(:segment)', 'Kuliner::show/$1');
$routes->get('cari', 'Kuliner::cari');

$routes->get('bandingkan', static function () {
    return redirect()->to(site_url('penginapan'));
});
$routes->get('bandingkan/(:any)', static function () {
    return redirect()->to(site_url('penginapan'));
});

// ---- Akun ----
$routes->match(['GET', 'POST'], 'masuk', 'Auth::masuk');
$routes->match(['GET', 'POST'], 'daftar', 'Auth::daftar');
$routes->match(['GET', 'POST'], 'lupa-sandi', 'Auth::lupa');
$routes->get('keluar', 'Auth::keluar');
$routes->get('akun', 'Akun::index');
$routes->post('akun/update', 'Akun::update');

// ---- Aksi pengunjung ----
$routes->post('favorit/(:num)', 'Interaksi::favorit/$1');
$routes->post('penginapan/(:num)/ulasan', 'Interaksi::ulasan/$1');
$routes->post('penginapan/(:num)/ulasan/hapus', 'Interaksi::hapusUlasan/$1');
$routes->post('penginapan/(:num)/pesan', 'Interaksi::pesan/$1');
$routes->post('penginapan/(:num)/tanya', 'Interaksi::tanya/$1');
$routes->post('tanya/(:num)/edit', 'Interaksi::editPertanyaan/$1');
$routes->post('tanya/(:num)/hapus', 'Interaksi::hapusPertanyaan/$1');
$routes->post('tanya/(:num)/jawab', 'Interaksi::jawab/$1');
$routes->post('tanya/(:num)/jawab/(:segment)/edit', 'Interaksi::editJawaban/$1/$2');
$routes->post('tanya/(:num)/jawab/(:segment)/hapus', 'Interaksi::hapusJawaban/$1/$2');
$routes->post('notifikasi/baca', 'Interaksi::bacaNotifikasi');

// ---- Admin ----
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function (RouteCollection $routes) {
    $routes->match(['GET', 'POST'], '/', 'Dashboard::index');

    $routes->get('hotel', 'Hotels::index');
    $routes->get('hotel/baru', 'Hotels::baru');
    $routes->match(['GET', 'POST'], 'hotel/impor', 'Hotels::impor');
    $routes->post('hotel/simpan', 'Hotels::simpan');
    $routes->get('hotel/(:num)/ubah', 'Hotels::ubah/$1');
    $routes->post('hotel/(:num)/hapus', 'Hotels::hapus/$1');

    $routes->get('ulasan', 'Ulasan::index');
    $routes->post('ulasan/(:num)/hapus', 'Ulasan::hapus/$1');

    $routes->get('pengguna', 'Pengguna::index');
    $routes->post('pengguna/(:num)/peran', 'Pengguna::peran/$1');
    $routes->post('pengguna/(:num)/hapus', 'Pengguna::hapus/$1');

    $routes->get('apikey', 'ApiKeys::index');
    $routes->post('apikey/buat', 'ApiKeys::buat');
    $routes->post('apikey/(:num)/toggle', 'ApiKeys::toggle/$1');
    $routes->post('apikey/(:num)/hapus', 'ApiKeys::hapus/$1');

    $routes->get('kuliner', 'KulinerAdmin::index');
    $routes->post('kuliner/sinkron', 'KulinerAdmin::sinkron');

    // ---- Laporan Cetak / PDF ----
    $routes->get('laporan/hotel', 'Laporan::hotel');
});

// ---- API tukar data & client (JSON) — mendukung /api/* dan /api/v1/* ----
$apiRoutes = static function (RouteCollection $routes): void {
    $routes->options('(:any)', 'Hotels::options');

    // Autentikasi terpadu (JWT)
    $routes->post('auth/login', 'Auth::login');
    $routes->post('auth/register', 'Auth::register');
    $routes->get('auth/me', 'Auth::me');

    // Penginapan (CRUD & Kecamatan)
    $routes->get('hotels', 'Hotels::index');
    $routes->get('hotels/(:num)', 'Hotels::show/$1');
    $routes->post('hotels', 'Hotels::create');
    $routes->put('hotels/(:num)', 'Hotels::update/$1');
    $routes->patch('hotels/(:num)', 'Hotels::update/$1');
    $routes->delete('hotels/(:num)', 'Hotels::delete/$1');
    $routes->get('kecamatan', 'Hotels::kecamatan');

    // Ulasan (CRUD + locking)
    $routes->get('reviews', 'Reviews::index');
    $routes->post('reviews', 'Reviews::create');
    $routes->put('reviews/(:num)', 'Reviews::update/$1');
    $routes->patch('reviews/(:num)', 'Reviews::update/$1');
    $routes->delete('reviews/(:num)', 'Reviews::delete/$1');

    // Integrasi Kuliner & Ekspor
    $routes->get('kuliner', 'Kuliner::index');
    $routes->post('kuliner/push', 'Kuliner::push');
    $routes->get('export/hotels', 'Export::hotels');
    $routes->get('export/ulasan', 'Export::ulasan');
};

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], $apiRoutes);
$routes->group('api', ['namespace' => 'App\Controllers\Api'], $apiRoutes);
