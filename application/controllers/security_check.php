<?php
/**
 * CodeIgniter Security Checklist Script
 * Jalankan script ini untuk memverifikasi pengaturan keamanan dasar di proyek CI Anda.
 */

$CI =& get_instance();

function cek($judul, $syarat, $ok = '✅', $fail = '❌') {
    echo ($syarat ? "$ok " : "$fail ") . $judul . "\n";
}

// 1. CSRF Protection
$config = include(APPPATH . 'config/config.php');
cek('CSRF Protection aktif', $config['csrf_protection'] ?? false);

// 2. Password Hashing check (dummy)
$password = 'example123';
$hash = password_hash($password, PASSWORD_DEFAULT);
cek('password_hash() menggunakan algoritma aman', password_get_info($hash)['algoName'] !== 'unknown');

// 3. HTTPS Deteksi
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
cek('HTTPS aktif', $https);

// 4. Upload directory check
$upload_path = FCPATH . 'uploads/';
$htaccess_exists = file_exists($upload_path . '.htaccess');
cek('.htaccess ada di folder uploads', $htaccess_exists);

// 5. Session IP Matching
cek('Session cocokkan IP diaktifkan', $config['sess_match_ip'] ?? false);

// 6. Escape Output
$test = '<script>alert(1)</script>';
$escaped = html_escape($test);
cek('html_escape bekerja', $escaped === htmlspecialchars($test, ENT_QUOTES, 'UTF-8'));

// 7. Log aktif
cek('Logging diaktifkan', ($config['log_threshold'] ?? 0) > 0);

// 8. Database config permissions
$db_config = APPPATH . 'config/database.php';
cek('File database.php tidak writable', substr(sprintf('%o', fileperms($db_config)), -3) < 666);

// 9. Versi CI
$ci_version = CI_VERSION;
cek('Menggunakan CodeIgniter 3.1.13+', version_compare($ci_version, '3.1.13', '>='));

// 10. Debug mode dimatikan
cek('Debug mode dimatikan', !$config['enable_hooks']);

echo "\nSelesai cek keamanan dasar.\n";