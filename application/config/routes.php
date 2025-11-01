<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['post/(:any)'] = 'post/on/$1'; 
$route['default_controller'] = 'home';
$route['404_override'] = 'publik/error';
$route['translate_uri_dashes'] = FALSE;
$route['service_worker.js'] = 'developer/service_worker';
// Scan & API check-in
$route['admin_scan']               = 'admin_scan/admin_scan/index';
$route['admin_scan/checkin_api']   = 'admin_scan/admin_scan/checkin_api';
$route['admin_scan/checkout_api']  = 'admin_scan/admin_scan/checkout_api';

// Panggilan HTTP ke URL: https://silapas.onhacker.co.id/admin_dashboard/expire_bookings
$route['admin_dashboard/expire_bookings'] = 'admin_dashboard/expire_bookings_http';
$route['t/(:any)']                 = 'produk/tag/$1';
// application/config/routes.php
$route['produk/delivery'] = 'produk/set_mode_delivery';
$route['produk/walkin']   = 'produk/set_mode_walkin';
// application/config/routes.php
$route['produk/order_success/(:any)'] = 'produk/order_success/$1';
$route['produk/pay_cash/(:any)']      = 'produk/pay_cash/$1';
$route['produk/pay_qris/(:any)']      = 'produk/pay_qris/$1';
$route['produk/pay_transfer/(:any)']  = 'produk/pay_transfer/$1';
$route['produk/receipt/(:any)']       = 'produk/receipt/$1';
$route['produk/qris_png/(:any)']      = 'produk/qris_png/$1';


$route['scan'] = 'produk/scan_qr';
$route['meja_billiard'] = 'hal/jadwal_billiard';
$route['cafe'] = 'hal/jadwal';

// $route['rss'] = 'rss.xml';
// $route['sitemap'] = "kmzwa8awaa/index";
// $route['sitemap\.xml'] = "kmzwa8awaa/index";

// require_once( BASEPATH .'database/DB'. EXT );
// $db =& DB();
// $query = $db->get("modul");
// foreach ($query->result() as $row) :
// 	if ($row->link == "Admin_post") {
// 		$route[$row->static_content] = 'post/index/';
// 		$route[$row->static_content."/index"] = 'post/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'post/index/';
// 		$route[$row->static_content.'/(:any)'] = 'post/on/$1'; 
// 	} elseif ($row->link == "Admin_hal") {
// 		$route[$row->static_content] = 'hal/index/';
// 		$route[$row->static_content."/index"] = 'hal/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'hal/index/';
// 		$route[$row->static_content.'/(:any)'] = 'hal/on/$1'; 
// 	} elseif ($row->link == "Admin_kategori") {
// 		$route[$row->static_content] = 'kategori/index/';
// 		$route[$row->static_content."/index"] = 'kategori/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'kategori/index/$1';
// 		$route[$row->static_content.'/(.+)'] = 'kategori/on/$1/'; 
// 	} elseif ($row->link == "Admin_tag") {
// 		$route[$row->static_content] = 'tag/index/';
// 		$route[$row->static_content."/index"] = 'tag/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'tag/index/$1';
// 		$route[$row->static_content.'/(.+)'] = 'tag/on/$1/'; 
// 	} elseif ($row->link == "Admin_pengurus") {
// 		$route[$row->static_content] = 'pengurus/index/';
// 		$route[$row->static_content."/index"] = 'pengurus/index/';
// 		$route[$row->static_content.'/(:any)'] = 'pengurus/detail/$1'; 
// 	} elseif ($row->link == "Admin_foto") {
// 		$route[$row->static_content] = 'gallery/index/';
// 		$route[$row->static_content."/index"] = 'gallery/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'gallery/index/$1';
// 		$route[$row->static_content.'/(.+)'] = 'gallery/on/$1/'; 
// 	} elseif ($row->link == "Admin_kontak") {
// 		$route[$row->static_content] = 'kontak_kami/index/';
// 	} elseif ($row->link == "Admin_forum") {
// 		$route[$row->static_content] = 'forum/index/';
// 		$route[$row->static_content."/index"] = 'forum/index/';
// 		$route[$row->static_content."/(:any)"] = 'forum/index/$1';	
// 	} elseif ($row->link == "Admin_file") {
// 		$route[$row->static_content] = 'download/index/';
// 		$route[$row->static_content."/index"] = 'download/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'download/index/$1';
// 		$route[$row->static_content."/(.+)"] = 'download/on/$1';
// 	} elseif ($row->link == "Admin_agenda") {
// 		$route[$row->static_content] = 'agenda/index/';
// 		$route[$row->static_content."/index"] = 'agenda/index/';
// 		$route[$row->static_content."/index/(:any)"] = 'agenda/index/$1';
// 		$route[$row->static_content."/(.+)"] = 'agenda/on/$1';
// 	} elseif ($row->link == "Admin_anggaran") {
// 		$route[$row->static_content] = 'anggaran/index/';	}  
// endforeach;

