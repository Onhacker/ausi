<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hal extends MX_Controller {
	function __construct(){
		parent::__construct();
		$this->load->helper("front");
		$this->load->model("front_model",'fm');
	}

	function index(){
		$data["rec"] = $this->fm->web_me();
		$data["title"] = "Syarat dan Ketentuan";
		$data["deskripsi"] = "Dokumen ini mengatur ketentuan penggunaan aplikasi/laman ".$data["rec"]->nama_website." ".$data["rec"]->kabupaten." (“Aplikasi”). Dengan membuat booking, menggunakan fitur check-in/check-out, atau mengakses Aplikasi, Anda (“Pengguna”) menyatakan telah membaca, memahami, dan menyetujui Syarat & Ketentuan ini.";
		$data["prev"] = base_url("assets/images/icon_app.png");
		
		$this->load->view('hal_syarat',$data);

	}

	function jadwal(){
		$this->load->model('M_billiard','mbi');
		$data["rec"] = $this->fm->web_me();
		$data["title"] = "Cafe " . $data["rec"]->nama_website;
		$data["deskripsi"] = "Nikmati suasana santai dan berbagai menu terbaik di Cafe " . $data["rec"]->nama_website . ". Tempat nongkrong asik dengan cita rasa istimewa!";

		$data["prev"] = base_url("assets/images/nongki.webp");
		$data["mejas"]      = $this->mbi->get_all_mejas();
		$this->load->view('jadwal',$data);

	}

	function app(){
		// $this->load->model('M_billiard','mbi');
		$data["rec"] = $this->fm->web_me();
		$data["title"] = "Download Aplikasi";
		$data["deskripsi"] = $data["rec"]->nama_website;
		$data["prev"] = base_url("assets/images/nongki.webp");
		// $data["mejas"]      = $this->mbi->get_all_mejas();
		$this->load->view('app',$data);

	}

	function cara_hapus(){
		// $this->load->model('M_billiard','mbi');
		$data["rec"] = $this->fm->web_me();
		$data["title"] = "Cara Hapus Data";
		$data["deskripsi"] = $data["rec"]->nama_website;
		$data["prev"] = base_url("assets/images/nongki.webp");
		// $data["mejas"]      = $this->mbi->get_all_mejas();
		$this->load->view('cara_hapus',$data);

	}


	

	function jadwal_billiard(){
		$this->load->model('M_billiard','mbi');
		$data["rec"] = $this->fm->web_me();
		$data["title"] = "Meja Billiard & Tarif";
		$data["deskripsi"] = "Daftar meja billiard dan tarif lengkap di " . $data["rec"]->nama_website . ". Pilih meja favoritmu dan nikmati permainan seru bersama teman!";

		$data["prev"] = base_url("assets/images/billiard.webp");
		$data["mejas"]      = $this->mbi->get_all_mejas();
		$this->load->view('jadwal_billiard',$data);

	}


	 public function pengumuman()
    {
        $rec = $this->fm->web_me();

        $data["rec"]       = $rec;
        $data["title"]     = "Pengumuman";
        $data["deskripsi"] = "Pengumuman ".$rec->nama_website.".";
        $data["prev"]      = base_url("assets/images/pengumuman.webp");

        $this->load->view('pengumuman', $data);
    }


	 public function pijat()
    {
        $rec = $this->fm->web_me();

        $data["rec"]       = $rec;
		$data["title"]     = "Kursi Pijat Elektrik";
		$data["deskripsi"] = "Nikmati kursi pijat elektrik di " . $rec->nama_website . " — rileks dan nyaman untuk tubuhmu.";
        $data["prev"]      = base_url("assets/images/pijat_icon.webp");

        $this->load->view('pijat', $data);
    }

     public function review()
    {
        $rec = $this->fm->web_me();

        $data["rec"]       = $rec;
        $data["title"]      = "Google Review";
        $data["deskripsi"]  = "Review " . $rec->nama_website . " di Google.";
        $data["prev"]      = base_url("assets/images/mark.webp");
        $data["rating_avg"]    = 4.9;   // float
	    $data["rating_total"]  = 120;   // int, total ulasan
	    $data["gmaps_url"]     = $rec->maps;
        $this->load->view('review', $data);
    }


    public function review_app(){
    $data["rec"]       = $this->fm->web_me();
    $data["title"]     = "Review";
    $data["deskripsi"] = $data["rec"]->nama_website ?? '';
    $data["prev"]      = base_url("assets/images/nongki.webp");

    $cls = $this->router->class;
    $data['review_submit_url']  = site_url("$cls/review_submit");
    $data['review_list_url']    = site_url("$cls/review_list");
    $data['review_captcha_url'] = site_url("$cls/review_captcha");

    // CSRF
    $data['csrf_name'] = $this->security->get_csrf_token_name();
    $data['csrf_hash'] = $this->security->get_csrf_hash();

    // Captcha awal
    [$words, $value] = $this->_gen_captcha4();
    $this->session->set_userdata('review_captcha_val', $value);
    $data['captcha_words'] = $words;

    // Stats untuk header & JSON-LD
    $stat = $this->db->select('COUNT(*) AS cnt, COALESCE(AVG(bintang),0) AS avg')->get('review_app')->row();
    $data['review_count'] = (int)($stat->cnt ?? 0);
    $data['review_avg']   = round((float)($stat->avg ?? 0), 2);

    // Ambil max 10 review terbaru utk JSON-LD
    $data['jsonld_reviews'] = $this->db->select('nama,bintang,ulasan,created_at')
        ->from('review_app')->order_by('created_at','DESC')->limit(10)->get()->result_array();

    $this->load->view('review_app',$data);
}

public function review_submit(){
    $this->_nocache_headers();
    $this->output->set_content_type('application/json');

    $nama    = trim((string)$this->input->post('nama', true));
    $bintang = (int)$this->input->post('bintang', true);
    $ulasan  = trim((string)$this->input->post('ulasan', true));
    $captcha = trim((string)$this->input->post('captcha', true));

    $errors = [];
    if ($nama === '' || mb_strlen($nama) < 2 || mb_strlen($nama) > 100) $errors['nama'] = 'Nama 2–100 karakter.';
    if ($bintang < 1 || $bintang > 5) $errors['bintang'] = 'Pilih 1–5 bintang.';
    if ($ulasan === '' || mb_strlen($ulasan) < 10) $errors['ulasan'] = 'Ulasan minimal 10 karakter.';
    $expect = (string)$this->session->userdata('review_captcha_val');
    if ($expect === '' || $captcha !== $expect) $errors['captcha'] = 'Captcha salah.';

    // throttle 25 detik / IP
    $ip = $this->input->ip_address();
    $recent = $this->db->select('created_at')->from('review_app')->where('ip',$ip)
        ->order_by('created_at','DESC')->limit(1)->get()->row();
    if ($recent && (time() - strtotime($recent->created_at)) < 25) {
        $errors['limit'] = 'Terlalu cepat, coba lagi sebentar.';
    }

    // refresh captcha untuk respon berikutnya
    [$wordsNew, $valueNew] = $this->_gen_captcha4();
    $this->session->set_userdata('review_captcha_val', $valueNew);

    if (!empty($errors)) {
        return $this->output->set_output(json_encode([
            'ok' => false,
            'errors' => $errors,
            'captcha_words' => $wordsNew,
            'csrf' => $this->security->get_csrf_hash(),
        ]));
    }

    $this->db->insert('review_app', [
        'nama'       => $nama,
        'bintang'    => $bintang,
        'ulasan'     => $ulasan,
        'ip'         => $ip,
        'user_agent' => substr((string)$this->input->user_agent(), 0, 255),
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return $this->output->set_output(json_encode([
        'ok' => true,
        'msg' => 'Terima kasih! Review kamu tersimpan.',
        'captcha_words' => $wordsNew,
        'csrf' => $this->security->get_csrf_hash(),
    ]));
}

public function review_list(){
    $this->_nocache_headers();
    $this->output->set_content_type('application/json');

    $page     = max(1, (int)$this->input->get('page'));
    $per_page = max(1, min(50, (int)($this->input->get('per_page') ?: 10)));
    $offset   = ($page - 1) * $per_page;

    $total = (int)$this->db->count_all_results('review_app');

    $items = $this->db->select('id,nama,bintang,ulasan,created_at')
        ->from('review_app')->order_by('created_at','DESC')
        ->limit($per_page, $offset)->get()->result();

    $list = [];
    foreach ($items as $r) {
        $list[] = [
            'id'         => (int)$r->id,
            'nama'       => htmlspecialchars($r->nama, ENT_QUOTES, 'UTF-8'),
            'bintang'    => (int)$r->bintang,
            'ulasan'     => $r->ulasan,
            'created_at' => date('d M Y H:i', strtotime($r->created_at)),
        ];
    }

    // aggregate utk header
    $stat = $this->db->select('COUNT(*) AS cnt, COALESCE(AVG(bintang),0) AS avg')
        ->from('review_app')->get()->row();
    $agg = [
        'count' => (int)($stat->cnt ?? 0),
        'avg'   => round((float)($stat->avg ?? 0), 2),
    ];

    return $this->output->set_output(json_encode([
        'ok'          => true,
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => max(1, (int)ceil($total / $per_page)),
        'items'       => $list,
        'agg'         => $agg,
    ]));
}

public function review_captcha(){
    $this->_nocache_headers();
    $this->output->set_content_type('application/json');

    [$words, $value] = $this->_gen_captcha4();
    $this->session->set_userdata('review_captcha_val', $value);

    return $this->output->set_output(json_encode([
        'ok' => true,
        'captcha_words' => $words,
        'csrf' => $this->security->get_csrf_hash(),
    ]));
}

private function _terbilang_digit($d){
    static $m = ['nol','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan'];
    return $m[$d] ?? '';
}

private function _gen_captcha4(){
    $ds = [];
    for ($i=0; $i<4; $i++) $ds[] = random_int(0,9);
    $words = array_map(function($d){ return $this->_terbilang_digit($d); }, $ds);
    return [implode(' ', $words), implode('', $ds)];
}

private function _nocache_headers(){
    $this->output
        ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
        ->set_header('Cache-Control: post-check=0, pre-check=0', false)
        ->set_header('Pragma: no-cache');
}



    /** Endpoint JSON untuk listing (AJAX) */
    public function pengumuman_data()
    {
    	$this->load->model('M_pengumuman', 'mpg');
    	 $this->load->driver('cache', ['adapter' => 'file']);
        $this->load->helper(['url', 'text']);
        $q        = trim((string)$this->input->get('q', true));
        $page     = (int)$this->input->get('page');     if ($page <= 0) $page = 1;
        $per_page = (int)$this->input->get('per_page'); if ($per_page <= 0) $per_page = 5;

        // Versi cache dari admin (dibump saat CRUD). Fallback: last_changed_fallback()
        $ver = (int)$this->cache->get('pengumuman_ver');
        if (!$ver) $ver = (int)$this->mpg->last_changed_fallback();

        // ETag per kombinasi konten & query
        $etag   = 'W/"pgm-'.$ver.'-'.md5($q.'|'.$page.'|'.$per_page).'"';
        $ifNone = trim((string)$this->input->server('HTTP_IF_NONE_MATCH'));
        if ($ifNone === $etag) {
            $this->output
                ->set_status_header(304)
                ->set_header('ETag: '.$etag)
                ->set_header('Cache-Control: public, max-age=30, stale-while-revalidate=120');
            return;
        }

        // Cache server-side untuk payload JSON
        $ckey    = 'pgm_list_'.$ver.'_'.md5($q).'_'.$page.'_'.$per_page;
		$payload = $this->cache->get($ckey);

		if ($payload === false) {
		    list($rows, $total) = $this->mpg->list_with_total($q, $page, $per_page);

		    $items = [];
		    foreach ($rows as $r) {
		        $excerpt = $this->_excerpt(
		            strip_tags(html_entity_decode($r['isi'] ?? '', ENT_QUOTES, 'UTF-8')),
		            180
		        );
		        $items[] = [
		            'id'           => (int)$r['id'],
		            'judul'        => $r['judul'],
		            'tanggal'      => $r['tanggal'],
		            'tanggal_view' => date('d M Y', strtotime($r['tanggal'])),
		            'excerpt'      => $excerpt,
		            'link_seo'     => $r['link_seo'],
		        ];
		    }

		    $pages   = max(1, (int)ceil($total / $per_page));
		    $payload = [
		        'success' => true,
		        'q'       => $q,
		        'page'    => $page,
		        'perPage' => $per_page,
		        'pages'   => $pages,
		        'total'   => $total,
		        'items'   => $items,
		    ];

		    // ⬇️ Cache tanpa kedaluwarsa; akan “diganti” (overwrite) otomatis jika dipanggil lagi dengan key yang sama
		    $this->cache->save($ckey, $payload, 0);
		}


        $this->output
            ->set_content_type('application/json')
            ->set_header('ETag: '.$etag)
            ->set_header('Cache-Control: public, max-age=30, stale-while-revalidate=120')
            ->set_output(json_encode($payload));
    }

    /** Detail pengumuman publik: terima slug | id | id-slug */
    public function detail_pengumuman($key = null)
    {
        if (!$key) show_404();

        $item = null;

        // Pola id atau id-slug (contoh "123" atau "123-judul-seo")
        if (preg_match('/^(\d+)(?:-.+)?$/', (string)$key, $m)) {
            $id   = (int)$m[1];
            $item = $this->db->get_where('pengumuman', ['id' => $id])->row();
            if (!$item) show_404();

            // Jika sudah punya slug dan URL bukan slug murni → redirect 301 ke SEO
            if (!empty($item->link_seo) && $key !== $item->link_seo) {
                redirect(site_url('hal/pengumuman/'.$item->link_seo), 'location', 301);
                return;
            }
        } else {
            // Anggap slug murni
            $item = $this->db->get_where('pengumuman', ['link_seo' => $key])->row();
            if (!$item) show_404();
        }

        // Meta
        $rec = $this->fm->web_me();
        $data["rec"]       = $rec;
        $data["title"]     = $item->judul;
        $data["deskripsi"] = $this->_excerpt(strip_tags($item->isi), 160);
        $data["prev"]      = base_url("assets/images/flow_icon.png");
        $data["item"]      = $item;

        $this->load->view('pengumuman_detail', $data);
    }

    /** Potong teks rapi */
    private function _excerpt(string $text, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u',' ', $text));
        if (mb_strlen($text) <= $limit) return $text;
        $cut = mb_substr($text, 0, $limit);
        $sp  = mb_strrpos($cut, ' ');
        if ($sp !== false) $cut = mb_substr($cut, 0, $sp);
        return rtrim($cut, ",.;:-— ").'…';
    }


	// function alur(){
	// 	$data["rec"] = $this->fm->web_me();
	// 	$data["title"] = "Alur Permohonan Kunjungan";
	// 	$data["deskripsi"] = "Alur permohonan Kunjungan ".$data["rec"]->nama_website." ".$data["rec"]->kabupaten." merupakan rangkaian tahapan yang harus dilalui oleh pemohon untuk mengajukan suatu permohonan kunjungan kepada Lapas Kelas I Makassar.";
	// 	$data["prev"] = base_url("assets/images/flow_icon.jpg");
		
	// 	$this->load->view('hal_view',$data);
	// }

	function privacy_policy(){
		$data["title"] = "Privacy Policy";
		$data["deskripsi"] = "Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi data pribadi pengguna";
		$data["prev"] = base_url("assets/images/icon_app.png");
		$data["rec"] = $this->fm->web_me();
		$this->load->view('privacy',$data);
	}

	function kontak(){
		$data["title"] = "Kontak ".$this->fm->web_me()->nama_website;
		$data["deskripsi"] = "Kontak ".$this->fm->web_me()->nama_website." memuat informasi lengkap mengenai nomor penting dan alamat.";

		$data["prev"] = base_url("assets/images/icon_app.png");
		$data["rec"] = $this->fm->web_me();
		$this->load->view('kontak_view',$data);
	}

	

}
