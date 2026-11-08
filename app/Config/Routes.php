<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('tentang', 'Home::tentang');
$routes->get('penginapan', 'Hotel::index');
$routes->get('penginapan/(:segment)', 'Hotel::show/$1');

// API untuk tukar data dengan kelompok lain (read-only, JSON)
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->options('(:any)', 'Hotels::options');
    $routes->get('hotels', 'Hotels::index');
    $routes->get('hotels/(:num)', 'Hotels::show/$1');
    $routes->get('kecamatan', 'Hotels::kecamatan');
});
