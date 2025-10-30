<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_modul extends Admin_Controller {
	function __construct(){
		parent::__construct();
		cek_session_admin();
		$this->load->model("M_admin_modul", "dm");
	}

	function index(){
		$data["controller"] = get_class($this);		
		$data["title"] = "Modul";
		$data["subtitle"] = "Manajemen Modul" ;
		$data["content"] = $this->load->view($data["controller"]."_view",$data,true); 
		$this->render($data);
	}

	function get_data(){   
        $list = $this->dm->get_data();
        $data = array();
        $no = $_POST['start'];
        $tes = "'#p1'";
        foreach ($list as $res) {
            $no++;
            $row = array();
            $row["id"] = $res->id_modul;
            $row["nama_modul"] = $res->nama_modul;
            $row["status"] = $res->status;
            $row["aktif"] = $res->aktif;
            $cp = "'#".$res->id_modul."'";
            $row["link"] = '<span style="display:none;" id="'.$res->id_modul.'">'.site_url().strtolower($res->link).'</span> 
		            			<span class="float-right">
		            				<a href="'.site_url(strtolower($res->link)).'" target="_BLANK"><i class="fe-link" data-toggle="tooltip" title="Kunjungi Link" ></i></a>  / &nbsp;
		            				<a href="javascript:void(0)" onclick="copy_link('.$cp.')"><i class="fe-copy" data-toggle="tooltip" title="Copy" ></i></a>
		            			</span>';
            $row['cek'] = '<div class="checkbox checkbox-primary checkbox-single"> <input type="checkbox" class="data-check" value="'.$res->id_modul.'"><label></label></div>';

            $data[] = $row;
        }

        $output = array(
        	"draw" => $_POST['draw'],
        	"recordsTotal" => $this->dm->count_all(),
        	"recordsFiltered" => $this->dm->count_filtered(),
        	"data" => $data,
        );
        
        echo json_encode($output);
    }

    function edit($id){
        $data = array();
        $res = $this->dm->get_by_id($id);
        if($res->num_rows() > 0 ){
            $data = $res->row_array();
        } else {
            $data = array();
        }
        echo json_encode($data);
    }

    function get_modul($id){
    	$this->db->where("id_modul", $id);
 		$rec = $this->db->get("modul")->row();
 		$outfile = strtolower($rec->link);
 		$ed = explode(",", $rec->link_seo);
 		if (empty($ed[1])) {
 			$sql = array($ed[0]);
 		} else {
 			$sql = array($ed[0],$ed[1]);
 		}
    	$this->load->dbutil();
    	$prefs = array(
	        'tables'        => $sql,   // Array of tables to backup.
	        'ignore'        => array(),                     // List of tables to omit from the backup
	        'format'        => 'txt',                       // gzip, zip, txt
	        'filename'      => 'mybackup.sql',              // File name - NEEDED ONLY WITH ZIP FILES
	        'add_drop'      => TRUE,                        // Whether to add DROP TABLE statements to backup file
	        'add_insert'    => FALSE,                        // Whether to add INSERT data to backup file
	        'newline'       => "\n"                         // Newline character used in backup file
   		 );
    	$backup = $this->dbutil->backup($prefs);
		// Load the file helper and write the file to your server
    	$this->load->helper('file');
    	write_file('upload/temp/'.$outfile.'.sql', $backup);
        write_file('upload/temp/'.$outfile.'.txt', $rec->link_seo);
		// Load the download helper and send the file to your desktop
    	// $this->load->helper('download');
    	// force_download('mybackup.sql', $backup);
		$this->load->library('zip');
		$file_path = "application/modules/$outfile";
		$db_path = 'upload/temp/'.$outfile.'.sql';
        $db_text = 'upload/temp/'.$outfile.'.txt';
		$this->zip->read_dir($file_path, false);  // kasi boelaan false utk memotong sub folder sebelumnya
		$this->zip->read_file($db_path);
        $this->zip->read_file($db_text); 
		$x = $this->zip->download($outfile); 
		// hapus temp
		$path_del = 'upload/temp/';
		$db_temp =  $path_del.$outfile.".sql";
		unlink($db_temp);
		 // echo $db_temp;
	}



    function add(){
        $data = $this->db->escape_str($this->input->post());
        $data2 = $this->input->post();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama_modul','Nama Modul','required'); 
        $this->form_validation->set_message('required', '* %s Harus diisi ');
        $this->form_validation->set_error_delimiters('<br> ', ' ');
        if($this->form_validation->run() == TRUE ) { 
            $data["static_content"] = linker($data["static_content"], "0");
            $config['upload_path'] = 'upload/temp/';
            $config['allowed_types'] = 'zip';
            $config['max_size'] = '500'; // kb
            $config['detect_mime'] = TRUE;
            $this->load->library('upload', $config);
   			
   			$this->db->where("link", ucfirst(str_replace(".zip", "", $_FILES['link']['name'])));
   			$cek = $this->db->get("modul");
   			$ter = $cek->row();
   			if ($cek->num_rows() == 1) {
   				$rules1 = "<br>Modul Sudah ada dengan  menggunakan nama ". $ter->nama_modul. " Silahkan dicek kembali";
   			} else {

	            if (! $this->upload->do_upload('link')) {
	                $rules = "<hr>Tipe file (".str_replace("|", ", ", $config['allowed_types']).")<br>Max file (".($config['max_size']/1000)." Mb)";

	            } else {
	            	$fdata =  $this->upload->data();
	                $data['link'] = ucfirst(str_replace(".zip", "", $fdata['file_name']));  
	                $data["username"] = $this->session->userdata("admin_username");
	                $zip = new ZipArchive;
	                if ($zip->open("upload/temp/".$_FILES['link']['name']) === TRUE) {
						$zip->extractTo('application/modules/');
						$zip->close();
						
						// hapus temp
						$path = 'upload/temp/';
						$filename =  $path.$_FILES['link']['name'];
						unlink($filename);
						
						$isi_file = file_get_contents('application/modules/'.strtolower($data['link']).".sql");
						$string_query = rtrim( $isi_file, "\n;" );
						$array_query = explode(";", $string_query);
                        $table_name = file_get_contents('application/modules/'.strtolower($data['link']).".txt");
						$data["link_seo"] = $table_name;
                        $data["status"] = $data["dada"];
                        unset($data["dada"]);

						$res  = $this->db->insert("modul",$data);
						// rec(get_class($this));  

						foreach($array_query as $query)
						{
							$this->db->query($query);
						}
						// hapus temp folde yg dimodule
						$path_temp = 'application/modules/';
						$filename =  $path_temp.strtolower($data['link']).".sql";
						unlink($filename);
                        $filename2 =  $path_temp.strtolower($data['link']).".txt";
                        unlink($filename2);
						
					}       
	            }
            }
            if($res) {    
                $ret = array("success" => true,
                    "title" => "Berhasil",
                    "pesan" => "Data berhasil Disimpan");
            } else {
                $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => "Data Gagal Disimpan ".$rules1.$this->upload->display_errors("<br>",$rules));
            }

        } else {
            $ret = array("success" => false,
                    "title" => "Gagal",
                    "pesan" => validation_errors());
        }
        echo json_encode($ret);
    }

   

	function update(){
		$data = $this->db->escape_str($this->input->post());
		$data2 = $this->input->post();
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nama_modul','Nama Modul','required'); 
		$this->form_validation->set_message('required', '* %s Harus diisi ');
		$this->form_validation->set_error_delimiters('<br> ', ' ');
		if($this->form_validation->run() == TRUE ) { 
            $data["status"] = $data["dada"];
            unset($data["dada"]);
            $data["static_content"] = linker($data["static_content"], "0");
            $this->db->where("id_modul",$data["id_modul"]);
            $res  = $this->om->update("modul",$data);
            // rec(get_class($this));
            
			if($res) {    
				$ret = array("success" => true,
					"title" => "Berhasil",
					"pesan" => "Data berhasil diupdate");
			} else {
				$ret = array("success" => false,
					"title" => "Gagal",
					"pesan" => "Data Gagal diupdate ");
			}

		} else {
			$ret = array("success" => false,
					"title" => "Gagal",
					"pesan" => validation_errors());
		}
		echo json_encode($ret);
	}

	function hapus_data(){
        $list_id = $this->input->post('id');
            foreach ($list_id as $id) {
            	$this->db->where("id_modul", $id);
                $gbr = $this->db->get("modul")->row();
                $path = 'application/modules/';
                $filename =  $path.strtolower($gbr->link);
                unlink($filename);
                $this->db->query("drop table ".$gbr->link_seo." ");
                $this->db->where("id_modul",$id);
                $res =$this->om->delete("modul");
                // rec(get_class($this));
            }
	        if($res) {    
	        	$ret = array("success" => true,
	        		"title" => "Berhasil",
	        		"pesan" => "Data berhasil dihapus");
	        } else {
	        	$ret = array("success" => false,
	        		"title" => "Gagal",
	        		"pesan" => "Data Gagal dihapus".$filename);
	        }

        echo json_encode($ret);
    } 


	
}
