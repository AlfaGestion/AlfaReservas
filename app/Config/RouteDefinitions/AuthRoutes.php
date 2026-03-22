<?php

$routes->group('auth', function ($routes) {
    $routes->post('register', 'Auth::dbRegister');
    $routes->get('logOut', 'Auth::log_out');
    $routes->get('login', 'Auth::index');
    $routes->post('login', 'Auth::login');
    $routes->get('register', 'Auth::register');
});
