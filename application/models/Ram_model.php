<?php 
class Ram_model extends CI_model{
    
    function web_me(){
        $this->db->select("*")->from("identitas");
        $this->db->where("id_identitas", "1");
        $this->db->where("id_logo", "1");
        $this->db->join("logo", "id_identitas = id_logo");
        $ident = $this->db->get()->row();
        return $ident;
    }

    // function nama_desa(){
    //     $this->db->where("level", "user");
    //     $this->db->where("deleted", "N");
    //     // $this->db->where("users.id_dusun", "0");
    //     $this->db->where("tiger_desa.id",$this->session->userdata("id_desa"))
    //     $this->db->join("tiger_desa", 'tiger_desa.id = users.id_desa');
    //     $this->db->join("tiger_kecamatan", 'tiger_desa.id_kecamatan = tiger_kecamatan.id');
    // }

    function identitas(){
        $res =  $this->db->query('SELECT desa,master_dusun.id_dusun,kecamatan, nama_dusun from master_desa JOIN master_dusun on master_dusun.id_dusun = master_desa.id_dusun JOIN master_kecamatan on master_desa.id_kecamatan = master_kecamatan.id_kecamatan where master_dusun.id_dusun = '.$this->session->userdata("admin_dusun").' GROUP by master_dusun.id_dusun')->row();
        return $res;
    }

    function identitas_general($id){
     
            $res =  $this->db->query('SELECT desa,master_dusun.id_dusun,kecamatan, nama_dusun from master_desa JOIN master_dusun on master_dusun.id_dusun = master_desa.id_dusun JOIN master_kecamatan on master_desa.id_kecamatan = master_kecamatan.id_kecamatan where master_dusun.id_dusun = '.$id.' GROUP by master_dusun.id_dusun')->row();
    
        
        return $res;

    }

    function identitas_general_l_a($id){
        $res =  $this->db->query('select * from master_dusun where id_dusun = '.$id.' ')->row();
        return $res;
        
    }

    function bentuk_admin($id,$str){
        $res =  $this->db->query('select * from master_dusun where id_dusun = '.$id.' ')->row();
        if ($str == "p") {
            return $this->bentuk_p($res->bentuk);
        } else {
            return $this->bentuk($res->bentuk);
        }
        
    }
    
    function bentuk($b){
        if ($b == "1") {
            return "dusun";
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

    function arr_tahun_manual(){
        $arr[""]  = "== Pilih Tahun Awal == ";
        for($i=date('Y'); $i>=date('Y')-1; $i-=1){
            $arr[$i-1]  = $i-1;
        }
        return $arr;

    }

    function arr_tahun_manual2(){
        $arr[""]  = "== Pilih Tahun Akhir == ";
        for($i=date('Y'); $i>=date('Y')-1; $i-=1){
            $arr[$i]  = $i;
        }
        return $arr;

    }

    function arr_tahun_manual_imunisasi(){
        $arr[""]  = "== Pilih Tahun Imunisasi == ";
        for($i=date('Y'); $i>=date('Y')-1; $i-=1){
            $arr[$i]  = $i;
        }
        return $arr;

    }

    function user(){
        $this->db->where("blokir", "N");
        $this->db->where("username", $this->session->userdata("admin_username"));
        $this->db->from("users");
        // $this->db->join("lokasi", "lokasi.id_desa = users.id_desa"); // Join condition added here
        $this->db->select("*");
        $user = $this->db->get()->row();
        // echo $this->db->last_query();
        return $user;
    }

    function user_faskes(){
        $this->db->where("blokir", "N");
        $this->db->where("username", $this->session->userdata("admin_username"));
        $this->db->from("users"); // Join condition added here
        $this->db->select("*");
        $user = $this->db->get()->row();
        // echo $this->db->last_query();
        return $user;
    }

    function user_general($id){
        $this->db->where("blokir", "N");
        $this->db->where("users.id_dusun",$id);
        $this->db->from("users");
        $this->db->select("foto,nama_lengkap");
        $user = $this->db->get()->row();
        return $user;
    }

    public function view($table){
        if ($this->session->userdata("admin_level")=='admin'){
            return $this->db->get($table);
        } else {
            $this->db->where("username", $this->session->userdata("admin_username"));
            return $this->db->get($table);
        }
        
    }

    function valid($query){
        if ($this->session->userdata("admin_level")=='admin'){
            return $query;
        } else {
            $this->db->where("username", $this->session->userdata("admin_username"));
            return $query;
        }
    }

    public function insert($table,$data){
        return $this->db->insert($table, $data);
    }

    public function edit($table, $data){
        return $this->db->get_where($table, $data);
    }

    public function profil()
    {
        $id_session = (string) $this->session->userdata('admin_session');
        $username   = (string) $this->session->userdata('admin_username');
        $level      = (string) $this->session->userdata('admin_level');

    // Alias tabel biar rapi
        $this->db->from('users u');

    // Kolom umum
        $this->db->select('
            u.username,
            u.nama_lengkap,
            u.email,
            u.no_telp,
            u.foto,
            u.level,
            u.blokir,
            u.permission_publish,
            u.tanggal_reg,
          
            ');

    // Tambahan lokasi untuk level user (LEFT JOIN -> tetap dapat data walau belum terisi)
        if ($level === 'user') {
             $this->db->select('u.id_unit, COALESCE(tu.nama_unit, "") AS nama_unit', false);
        $this->db->join('unit_tujuan tu', 'tu.id = u.id_unit', 'left');
        }

    // Filter utama: pakai id_session jika ada, jika tidak pakai username
        if ($id_session !== '') {
            $this->db->where('u.id_session', $id_session);
        } else {
            $this->db->where('u.username', $username);
        }

    // (Opsional) jika punya kolom deleted
    // $this->db->where('u.deleted', 'N');

        $this->db->limit(1);
    return $this->db->get(); // CI_DB_result -> panggil ->row() di view/pemanggil
}

    //done
    public function update($table, $data){
        if ($this->session->userdata("admin_level") == "admin") {
            return $this->db->update($table, $data); 
        } else {
            $this->db->where("username",$this->session->userdata("admin_username"));
            return $this->db->update($table, $data); 
        }   
    }

    // done
    public function delete($table){
        if ($this->session->userdata("admin_level") == "admin") {
           return $this->db->delete($table);
        } else {
            $this->db->where("username",$this->session->userdata("admin_username"));
            return $this->db->delete($table);
        }
        
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
    // done
    public function view_ordering($table,$order,$ordering){
        $this->db->select('*');
        $this->db->from($table);
        $this->db->order_by($order,$ordering);
        return $this->db->get();
    }

    public function view_where_ordering($table,$data,$order,$ordering){
        $this->db->where($data);
        $this->db->order_by($order,$ordering);
        $query = $this->db->get($table);
        return $query->result_array();
    }

    // done
    public function view_join_one($table1,$table2,$field){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
    }

    // done
    public function view_join_tow($table1,$table2,$table3,$field,$field1){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->join($table3, $table1.'.'.$field1.'='.$table3.'.'.$field1);
    }

    public function view_join_tre($table1,$table2,$table3,$table4,$field,$field1,$field2){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->join($table3, $table1.'.'.$field1.'='.$table3.'.'.$field1);
        $this->db->join($table4, $table1.'.'.$field2.'='.$table4.'.'.$field2,"left");
        // untuk menampilkand data dusun aja makanya pake left (kebutuhan edit)
    }

    // done
    public function view_join_where($table1,$table2,$field,$where,$order,$ordering){
        $this->db->select('*');
        $this->db->from($table1);
        $this->db->join($table2, $table1.'.'.$field.'='.$table2.'.'.$field);
        $this->db->where($where);
        $this->db->order_by($order,$ordering);
    }
    // done
    function engine_nama_menu($link){
        $this->db->where(array("link" => $link, "publish" => "Y", "aktif" => "Y"));
        $res = $this->db->get("modul")->row();
        return $res->nama_modul;
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
    // done
    function validasiOnLogin(){
        $this->db->where("blokir", "N");
        $this->db->where("username", $this->session->userdata("admin_username"));
        $res = $this->db->get("users");
        $rec = $res->row();
        if ($this->session->userdata("admin_attack") <> $rec->attack or $res->num_rows() == 0) {
            $out = redirect('on_login/logout');
        }
    }

    function validasiOpLogin(){
        $this->db->where("npsn", $this->session->userdata("op_username"));
        $res = $this->db->get("sekolah");
        $rec = $res->row();
        if ($this->session->userdata("op_login") == false) {
            $out = redirect('op_login/logout');
        }
    }

}