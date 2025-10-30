<?php 
class Front_model extends CI_model{

    function web_me(){
        $this->db->select("*")->from("identitas");
        $this->db->where("id_identitas", "1");
        $this->db->where("id_logo", "1");
        $this->db->join("logo", "id_identitas = id_logo");
        $ident = $this->db->get()->row();
        return $ident;
    }

   
    // done
    function engine_akses_menu($link,$id){
        $this->db->select('*');
        $this->db->from("modul");
        $this->db->join('users_modul', 'modul.id_modul = users_modul.id_modul');
        $this->db->where("link", $link);
        $this->db->where("id_session", $id);
        return $this->db->get()->num_rows();
    }

    // warga area

    function identitas_general($id){
     
            $res =  $this->db->query('SELECT desa,master_pkm.id_pkm,kecamatan, nama_pkm from master_desa JOIN master_pkm on master_pkm.id_pkm = master_desa.id_pkm JOIN master_kecamatan on master_desa.id_kecamatan = master_kecamatan.id_kecamatan where master_pkm.id_pkm = '.$id.' GROUP by master_pkm.id_pkm')->row();
    
        
        return $res;

    }

    function identitas_general_l_a($id){
        $res =  $this->db->query('select * from master_pkm where id_pkm = '.$id.' ')->row();
        return $res;
        
    }

    function bentuk_admin($id,$str){
        $res =  $this->db->query('select * from master_pkm where id_pkm = '.$id.' ')->row();
        if ($str == "p") {
            return $this->bentuk_p($res->bentuk);
        } else {
            return $this->bentuk($res->bentuk);
        }
        
    }
    
    function bentuk($b){
        if ($b == "1") {
            return "PKM";
        } else {
            return "";
        }
    }

    function bentuk_p($b){
        if ($b == "1") {
            return "Puskesmas";
        } else {
            return "";
        }
    }
    function user(){
        $this->db->where("blokir", "N");
        // $this->db->where("username",$this->session->userdata("admin_username"));
        $this->db->from("users");
        $this->db->select("foto,nama_lengkap,pimpinan,nip_pimpinan");
        $user = $this->db->get()->row();
        return $user;
    }
    
    function user_general($id){
        $this->db->where("blokir", "N");
        $this->db->where("users.id_pkm",$id);
        $this->db->from("users");
        $this->db->select("foto,nama_lengkap,pimpinan,nip_pimpinan");
        $user = $this->db->get()->row();
        return $user;
    }
    // end of warga arean
    

    function profil(){
        $this->db->select("username,nama_lengkap,email,no_telp,foto,level,blokir,permission_publish,tanggal_reg")->where("username", $this->session->userdata("admin_username"))->from("users");
        $res = $this->db->get();
        return $res;
    }

    function profil_web(){
        $this->db->select("username,nama_lengkap,email,no_telp,foto,level,blokir,permission_publish,tanggal_reg")->where("email", $this->session->userdata("web_username"))->from("users_web");
        $res = $this->db->get();
        return $res;
    }


    
    function engine_nama_menu($link){
        $this->db->where(array("link" => $link, "publish" => "Y", "aktif" => "Y"));
        $res = $this->db->get("modul")->row();
        return $res->nama_modul;
    }


    public function view($table){
        return $this->db->get($table);
    }

    public function view_where($table,$data){
        $this->db->where($data);
        return $this->db->get($table);
    }

    public function view_ordering_limit($table,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get($table);
    }

    public function view_where_ordering_limit($table,$data,$order,$ordering,$baris,$dari){
        $this->db->where($data);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get($table);
    }

    public function view_single($table,$data,$order,$ordering){
        $this->db->where($data);
        $this->db->order_by($order,$ordering);
        return $this->db->get($table);
    }

    public function view_join($table1,$table2,$field,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get();
    }

     // done
    public function view_join_where($table1,$table2,$field,$where,$order,$ordering){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->where($where);
        $this->db->order_by($order,$ordering);
        return $this->db->get();
    }


    public function view_join_one($table1,$table2,$field,$where,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->where($where);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get();
    }

    public function view_joinn($table1,$table2,$table3,$field,$field1,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->join($table3, $table1.'.'.$field1.'='.$table3.'.'.$field1);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get();
    }

    public function view_join_two($table1,$table2,$table3,$field,$field1,$where,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->join($table3, $table1.'.'.$field1.'='.$table3.'.'.$field1);
        $this->db->where($where);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get();
    }

     public function view_join_two_left($table1,$table2,$table3,$field,$field1,$where,$order,$ordering,$baris,$dari){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->join($table3, $table1.'.'.$field1.'='.$table3.'.'.$field1,'left');
        $this->db->where($where);
        $this->db->order_by($order,$ordering);
        $this->db->limit($dari, $baris);
        return $this->db->get();
    }

    function cari_berita($kata){
        $pisah_kata = explode(" ",$kata);
        $jml_katakan = (integer)count($pisah_kata);
        $jml_kata = $jml_katakan-1;
        $cari = "SELECT * FROM berita a join users b on a.username=b.username
                    join kategori c on a.id_kategori=c.id_kategori
                       WHERE a.status='Y' AND";
            for ($i=0; $i<=$jml_kata; $i++){
              $cari .= " a.judul LIKE '%".$pisah_kata[$i]."%'";
                if ($i < $jml_kata ){
                    $cari .= " OR "; 
                } 
            }
        $cari .= " ORDER BY a.id_berita DESC LIMIT 15";
        return $this->db->query($cari);
    }

    public function insert($table,$data){
        return $this->db->insert($table, $data);
    }

    public function update($table, $data, $where){
        return $this->db->update($table, $data, $where); 
    }

    public function polling_sum(){
        return $this->db->query("SELECT SUM(rating) as jml_vote FROM poling WHERE aktif='Y'"); 
    }

    function kunjungan(){
        $ip      = $_SERVER['REMOTE_ADDR'];
        $tanggal = date("Y-m-d");
        $waktu   = time(); 
        $cekk = $this->db->query("SELECT * FROM statistik WHERE ip='$ip' AND tanggal='$tanggal'");
        $rowh = $cekk->row_array();
        if($cekk->num_rows() == 0){
            $datadb = array('ip'=>$ip, 'tanggal'=>$tanggal, 'hits'=>'1', 'online'=>$waktu);
            $this->db->insert('statistik',$datadb);
        }else{
            $hitss = $rowh['hits'] + 1;
            $datadb = array('ip'=>$ip, 'tanggal'=>$tanggal, 'hits'=>$hitss, 'online'=>$waktu);
            $array = array('ip' => $ip, 'tanggal' => $tanggal);
            $this->db->where($array);
            $this->db->update('statistik',$datadb);
        }
    }

    

}