<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->post('register', 'Auth::register');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout');
$routes->post('contact', 'Contact::send');
$routes->get('portal', 'Portal::index');
$routes->post('portal/profile', 'Portal::profile');
$routes->post('portal/password', 'Portal::password');
$routes->post('portal/book', 'Portal::book');
$routes->post('portal/bookings/cancel', 'Portal::cancelBooking');
$routes->post('portal/tickets', 'Portal::ticket');
$routes->post('portal/tickets/reply', 'Portal::ticketReply');
$routes->post('portal/users/create', 'Portal::createEmployee');
$routes->post('portal/users/update', 'Portal::updateUser');
$routes->post('portal/users/status', 'Portal::userStatus');
$routes->post('portal/schedule/update', 'Portal::scheduleUpdate');
$routes->post('portal/check-in', 'Portal::checkIn');
$routes->post('portal/work-report', 'Portal::workReport');
$routes->post('portal/announcements', 'Portal::announcement');
$routes->post('portal/services', 'Portal::service');
$routes->post('portal/products', 'Portal::product');
$routes->post('portal/payments', 'Portal::payment');
$routes->post('portal/notifications/read', 'Portal::notificationsRead');
