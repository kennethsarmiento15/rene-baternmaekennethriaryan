<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->post('register', 'Auth::register');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout');
$routes->post('contact', 'Contact::send');
