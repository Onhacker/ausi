<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Publik extends Onhacker_Controller {
	function __construct(){
		parent::__construct();
        $this->output->set_header("X-Robots-Tag: noindex, nofollow", true);

	}

	function index(){
		echo "fuck";
	}

	function error(){
        $data['title'] = "Halaman Tidak Ditemukan - ".$this->fm->web_me()->nama_website;
        $this->load->view(onhacker_view("Error_view"),$data); 
    }

    function imunisasi($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf/").$id);
        }
        $this->db->where("id_imunisasi", $id);
        $this->db->join("im_agama", "im_agama.id_agama = imunisasi.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = imunisasi.id_desa");

        $data["res"] = $this->db->get("imunisasi")->row();
        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksinx"] = $red->nama_penyakit;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Data Vaksin ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = site_url("admin_imunisasi/pdf/".$id); //data yang akan di jadikan QR CODE
        // $data['data'] = $id."-".$data["res"]->jenis_vaksin; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/imunisasi/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        


        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_imunisasi_biodata_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

    function imunisasi_ibu($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf_ibu/").$id);
        }
        $this->db->where("id_imunisasi_ibu", $id);
        $this->db->join("im_agama", "im_agama.id_agama = imunisasi_ibu.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = imunisasi_ibu.id_desa");

        $data["res"] = $this->db->get("imunisasi_ibu")->row();
        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksinx"] = arr_vaksin_ibu_p($data["res"]->jenis_vaksin);

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Data Vaksin ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = $id."-".$data["res"]->jenis_vaksin; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/imunisasi_ibu/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        


        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_imunisasi_ibu_biodata_view",$data,true);
        // exit();
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
        
    } 

    function kartu_imun_anak($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_anak/pdf/").$id);
        }
        $this->db->where("id_anak", $id);
        $this->db->join("im_agama", "im_agama.id_agama = im_anak.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = im_anak.id_desa");

        $data["res"] = $this->db->get("im_anak")->row();
        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Kartu Imunisasi ".$data["res"]->nama;
        
        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = $id."-".$data["res"]->jenis_vaksin; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/kartu_imun_anak/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        

        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_anak_biodata_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

    function kartu_imun_ibu($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_ibu/pdf/").$id);
        }
        $this->db->where("id_ibu", $id);
        $this->db->join("im_agama", "im_agama.id_agama = im_ibu.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = im_ibu.id_desa");

        $data["res"] = $this->db->get("im_ibu")->row();
        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Biodata ".$data["res"]->nama;
        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = $id."-".$data["res"]->jenis_vaksin; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/kartu_imun_ibu/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_ibu_biodata_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

    function imunisasi_luar($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf_luar/").$id);
        }
        $this->db->where("id_imunisasi", $id);
        $data["res"] = $this->db->get("imunisasi_luar")->row();
        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksinx"] = $red->nama_penyakit;
        $data["title"] = "Data Vaksin ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = site_url("admin_imunisasi/pdf/".$id); //data yang akan di jadikan QR CODE
        // $data['data'] = "L-".$id."-".$data["res"]->jenis_vaksin; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/imunisasi_luar/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        

        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_imunisasi_biodata_luar_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    }

    function imunisasi_kipi($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf_kipi/").$id);
        }
        $this->db->where("urutan", $id);
        // $this->db->join("im_agama", "im_agama.id_agama = imunisasi_kipi.id_agama");
        // $this->db->join("master_desa", "master_desa.id_desa = imunisasi_kipi.id_desa");

        $data["res"] = $this->db->get("imunisasi_kipi")->row();
        // echo $this->db->last_query();

        $this->db->where("id_desa", $data["res"]->id_desa);
        $data["nama_desa"] = $this->db->get("master_desa")->row();

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin_1);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksin_1"] = $red->nama_penyakit;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin_2);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksin_2"] = $red->nama_penyakit;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Data KIPI ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = site_url("admin_imunisasi/pdf/".$id); //data yang akan di jadikan QR CODE
        // $data['data'] = $id."-".$data["jenis_vaksin_1"]."-".$data["res"]->nama; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/imunisasi_kipi/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE
        


        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Kipi_biodata_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

    function riwayat_anak($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf_riwayat/").$id);
        }
        $this->db->where("id_anak", $id);
        $this->db->join("im_agama", "im_agama.id_agama = im_anak.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = im_anak.id_desa");

        $data["res"] = $this->db->get("im_anak")->row();

        // $this->db->order_by("tahun","DESC");
        // $this->db->order_by("bulan","DESC");
        $this->db->order_by("tgl_suntik","DESC");
        $this->db->where("id_anak",$id);
        $data["record"] = $this->db->get("imunisasi");


        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksinx"] = $red->nama_penyakit;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Riwayat Vaksin ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = $data["res"]->nama."_".tgl_view($data["res"]->tgl_lahir)."_".$id; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/riwayat_anak/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE

        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_imunisasi_pdf_riwayat_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

    function riwayat_ibu($id) {
        if ($this->session->userdata("admin_login") == true) {
            redirect(site_url("admin_imunisasi/pdf_riwayat_ibu/").$id);
        }
        $this->db->where("id_ibu", $id);
        $this->db->join("im_agama", "im_agama.id_agama = im_ibu.id_agama");
        $this->db->join("master_desa", "master_desa.id_desa = im_ibu.id_desa");

        $data["res"] = $this->db->get("im_ibu")->row();

        $this->db->order_by("tahun","DESC");
        $this->db->order_by("bulan","DESC");
        $this->db->order_by("tgl_suntik","DESC");
        $this->db->where("id_ibu",$id);
        $data["record"] = $this->db->get("imunisasi_ibu");


        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ayah);
        $p = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ayah"] = $p->pekerjaan;

        $this->db->where("id_pekerjaan", $data["res"]->id_pekerjaan_ibu);
        $pa = $this->db->get("im_pekerjaan")->row();
        $data["pekerjaan_ibu"] = $pa->pekerjaan;

        $this->db->where("id_penyakit", $data["res"]->jenis_vaksin);
        $red = $this->db->get("master_penyakit")->row();
        $data["jenis_vaksinx"] = $red->nama_penyakit;

        $this->db->where("id_desa",$data["res"]->id_desa);
        $de = $this->db->get("master_desa")->row();

        $this->db->where("id_kecamatan", $de->id_kecamatan);
        $data["kec"] = $this->db->get("master_kecamatan")->row();
    
        $data["title"] = "Riwayat Vaksin ".$data["res"]->nama;
          

        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$id.'.png'; //buat name dari qr code sesuai dengan nim
 
        // $data['data'] = $data["res"]->nama."_".tgl_view($data["res"]->tgl_lahir)."_".$id; //data yang akan di jadikan QR CODE
        $data['data'] = site_url("publik/riwayat_ibu/"). $id; 
        $data['level'] = 'H'; //H=High
        $data['size'] = 10;
        $data['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($data); // fungsi untuk generate QR CODE

        $data['header'] = $data["title"];
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'F4', true, 'UTF-8', false);
        $pdf->SetTitle( $data['header']);
        
        $pdf->SetMargins(20, 10, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetAuthor('Onhacker.net');

        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
     // add a page
        $pdf->AddPage("P", "F4");

        $html = $this->load->view("Admin_imunisasi_pdf_riwayat_ibu_view",$data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
        unlink($data['savename']);
        $pdf->Output($data['header'] .'.pdf', 'I');
    } 

}
