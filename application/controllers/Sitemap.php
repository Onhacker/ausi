<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sitemap Controller — RAW Output (bypass minifier & hooks)
 * - /sitemap.xml                : index -> menunjuk static + produk (paged)
 * - /sitemap-static.xml         : URL statis
 * - /sitemap-products-{N}.xml   : produk per halaman
 * - /robots.txt                 : robots + lokasi sitemap
 */
class Sitemap extends Onhacker_Controller
{
    const PER_CHUNK = 5000;

    public function __construct(){
        parent::__construct();
        $this->load->helper(['url','text']);
        $this->load->database();
    }

    /* ===== Helpers ===== */
    private function _h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

    private function _abs($path){
        if (!$path || $path === '/') return rtrim(base_url(), '/');
        if (strpos($path, 'http') === 0) return rtrim($path, '/');
        return rtrim(site_url($path), '/');
    }

    private function _lastmod($row, $fallback=null){
        foreach (['updated_at','review_at','modified_at','edited_at','created_at'] as $c){
            if (!empty($row->$c)) return date('c', strtotime($row->$c));
        }
        return $fallback ? date('c', strtotime($fallback)) : date('c');
    }

    /** Kirim body mentah: matikan semua output buffering & hook minifier */
    private function _raw_send($body, $contentType='application/xml; charset=utf-8', $maxAge=3600){
        // Bersihkan SEMUA buffer supaya tidak lewat filter/minifier
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: '.$contentType);
        header('X-Raw-Output: 1');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age='.$maxAge);
        header('X-Sitemap-File: '.__FILE__);
        echo $body;
        exit;
    }

    /* ========== /sitemap.xml (INDEX) ========== */
    public function index(){
        $totalProduk = (int)$this->db
            ->where('IFNULL(is_active,1)=', 1, false) // sesuaikan bila tidak ada kolom is_active
            ->count_all_results('produk');

        $chunks = max(1, (int)ceil($totalProduk / self::PER_CHUNK));
        $nowIso = date('c');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        $xml .= '  <sitemap>'."\n";
        $xml .= '    <loc>'.$this->_h($this->_abs('sitemap-static.xml')).'</loc>'."\n";
        $xml .= '    <lastmod>'.$this->_h($nowIso).'</lastmod>'."\n";
        $xml .= '  </sitemap>'."\n";
        for ($i=1; $i <= $chunks; $i++){
            $xml .= '  <sitemap>'."\n";
            $xml .= '    <loc>'.$this->_h($this->_abs("sitemap-products-$i.xml")).'</loc>'."\n";
            $xml .= '    <lastmod>'.$this->_h($nowIso).'</lastmod>'."\n";
            $xml .= '  </sitemap>'."\n";
        }
        $xml .= '</sitemapindex>';

        $this->_raw_send($xml);
    }

    /* ========== /sitemap-static.xml (STATIS) ========== */
   public function static_pages(){
        $rows = [
            // Home
            ['loc'=>$this->_abs('/'),                       'last'=>'2025-11-01', 'chg'=>'daily',  'prio'=>'1.00'],
            // Billiard main page
            ['loc'=>$this->_abs('billiard'),               'last'=>'2025-11-01', 'chg'=>'hourly', 'prio'=>'0.90'],
            // Daftar Meja Billiard
            ['loc'=>$this->_abs('meja_billiard'),          'last'=>'2025-11-01', 'chg'=>'hourly', 'prio'=>'0.85'],
            // Booking list
            ['loc'=>$this->_abs('billiard/daftar_booking'),'last'=>'2025-11-01', 'chg'=>'hourly', 'prio'=>'0.85'],
            // Voucher list
            ['loc'=>$this->_abs('billiard/daftar_voucher'),'last'=>'2025-11-01', 'chg'=>'daily',  'prio'=>'0.80'],
            // Produk / menu
            ['loc'=>$this->_abs('produk'),                 'last'=>'2025-11-01', 'chg'=>'daily',  'prio'=>'0.80'],
            // Cafe
            ['loc'=>$this->_abs('cafe'),                   'last'=>'2025-11-01', 'chg'=>'daily',  'prio'=>'0.75'],
            // Scan QR
            ['loc'=>$this->_abs('scan'),                   'last'=>'2025-11-01', 'chg'=>'always', 'prio'=>'0.90'],
            // Pijat
            ['loc'=>$this->_abs('pijat'),                  'last'=>'2025-11-01', 'chg'=>'always', 'prio'=>'0.90'],
            // Review
            ['loc'=>$this->_abs('hal/review'),             'last'=>'2025-11-01', 'chg'=>'always', 'prio'=>'0.90'],
            // Hal
            ['loc'=>$this->_abs('hal'),                    'last'=>'2025-11-01', 'chg'=>'weekly', 'prio'=>'0.60'],
            // Kontak
            ['loc'=>$this->_abs('hal/kontak'),             'last'=>'2025-11-01', 'chg'=>'monthly','prio'=>'0.50'],
            // Pengumuman
            ['loc'=>$this->_abs('hal/pengumuman'),         'last'=>'2025-11-01', 'chg'=>'daily',  'prio'=>'0.70'],
            // Privacy Policy
            ['loc'=>$this->_abs('hal/privacy_policy'),     'last'=>'2025-11-01', 'chg'=>'yearly', 'prio'=>'0.30'],
            // Monitor (ditambahkan)
            ['loc'=>$this->_abs('monitor'),                'last'=>'2025-11-01', 'chg'=>'always', 'prio'=>'0.90'],
            ['loc'=>$this->_abs('produk/reward'),                'last'=>'2025-11-01', 'chg'=>'always', 'prio'=>'0.90'],
        ];

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($rows as $u){
            $xml .= "  <url>\n";
            $xml .= "    <loc>".$this->_h($u['loc'])."</loc>\n";
            $xml .= "    <lastmod>".$this->_h($u['last'])."</lastmod>\n";
            $xml .= "    <changefreq>".$this->_h($u['chg'])."</changefreq>\n";
            $xml .= "    <priority>".$this->_h($u['prio'])."</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        $this->_raw_send($xml);
    }

    /* ========== /sitemap-products-{page}.xml (PRODUK SLUG) ========== */
    public function products($page = 1){
        $page   = max(1, (int)$page);
        $limit  = self::PER_CHUNK;
        $offset = ($page - 1) * $limit;

        $q = $this->db->select('id, link_seo, gambar, nama, updated_at, created_at')
                      ->from('produk')
                      ->where('IFNULL(is_active,1)=', 1, false)
                      ->order_by('id','ASC')
                      ->limit($limit, $offset)
                      ->get();
        $rows = $q->result();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        foreach ($rows as $r){
            $slug = trim($r->link_seo ?: $r->id);
            $loc  = $this->_abs('produk/detail/'.$slug);
            $last = $this->_lastmod($r);
            $img  = !empty($r->gambar) ? $this->_abs($r->gambar) : '';
            $ttl  = !empty($r->nama)   ? $r->nama : '';

            $xml .= "  <url>\n";
            $xml .= "    <loc>".$this->_h($loc)."</loc>\n";
            $xml .= "    <lastmod>".$this->_h($last)."</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.80</priority>\n";
            if ($img){
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>".$this->_h($img)."</image:loc>\n";
                if ($ttl){
                    $xml .= "      <image:title>".$this->_h($ttl)."</image:title>\n";
                }
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        $this->_raw_send($xml);
    }

    /* ========== /robots.txt ========== */
    public function robots(){
        $body  = "User-agent: *\n";
        $body .= "Allow: /\n";
        $body .= "Sitemap: ".$this->_abs('sitemap.xml')."\n";
        $this->_raw_send($body, 'text/plain; charset=utf-8', 3600);
    }
}
