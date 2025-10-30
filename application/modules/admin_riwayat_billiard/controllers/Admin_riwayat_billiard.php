<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_riwayat_billiard extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_riwayat_billiard','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai
        
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Riwayat Billiard";
        $data["subtitle"]   = "Transaksi Lunas (Arsip)";
        $data["content"]    = $this->load->view('Admin_riwayat_billiard_view',$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data(){
        try{
            $metode = $this->input->post('metode', true) ?: 'all';
            $this->dm->set_max_rows(500);
            $this->dm->set_paid_method_filter($metode);

            $list = $this->dm->get_data();
            $data = [];

            foreach ($list as $r){
                // Kode Booking
                $kode = htmlspecialchars($r->kode_booking ?: '-', ENT_QUOTES, 'UTF-8');

                // Meja / Nama
                $meja = $r->nama_meja ?: ('Meja #'.$r->meja_id);
                $meja_html = htmlspecialchars($meja, ENT_QUOTES, 'UTF-8');
                if (!empty($r->nama)){
                    $meja_html .= '<div class="text-muted small">'.htmlspecialchars($r->nama,ENT_QUOTES,'UTF-8').'</div>';
                }

                // Waktu Bayar
                $paid_html = $r->paid_at ? htmlspecialchars(date('d-m-Y H:i:s', strtotime($r->paid_at)), ENT_QUOTES, 'UTF-8') : '-';

                // Waktu Main (tanggal + jam + durasi)
                $tgl = $r->tanggal ? date('d-m-Y', strtotime($r->tanggal)) : '-';
                $jam = "Jam ".trim(($r->jam_mulai ?: '').' - '.($r->jam_selesai ?: ''));
                $durasi_html = '<b>'.(int)$r->durasi_jam.' jam</b>';
                $waktu_html = $tgl.' ('.$durasi_html.')' .'<div class="text-blue small">'.htmlspecialchars($jam,ENT_QUOTES,'UTF-8').'</div>';

                // Harga / Grand Total
                $harga_html = 'Rp '.number_format((int)$r->harga_per_jam,0,',','.');
                $grand_html = 'Rp '.number_format((int)$r->grand_total,0,',','.');

                // Metode
                $metode_html = htmlspecialchars($r->metode_bayar ?: '-', ENT_QUOTES, 'UTF-8');

                // Aksi: hanya detail
                $idInt = (int)$r->id_paid;
                $actionsHtml = '<div class="btn-group btn-group-sm" role="group">'
                             . '<button type="button" class="btn btn-info" onclick="show_detail('.$idInt.')"><i class="fe-eye"></i></button>'
                             . '</div>';

                $row = [];
                $row['id']        = $idInt;
                $row['no']        = '';
                $row['kode']      = '<span class="badge badge-dark">'.$kode.'</span>';
                $row['meja']      = $meja_html;
                $row['paid_at']   = $paid_html;
                $row['waktu']     = $waktu_html;
                $row['harga']     = $harga_html;
                $row['grand']     = $grand_html;
                $row['metode']    = $metode_html;
                $row['aksi']      = $actionsHtml;

                $data[] = $row;
            }

            $out = [
                "draw"            => (int)$this->input->post('draw'),
                "recordsTotal"    => $this->dm->count_all(),
                "recordsFiltered" => $this->dm->count_filtered(),
                "data"            => $data,
            ];
            return $this->output->set_content_type('application/json')->set_output(json_encode($out));
        } catch(\Throwable $e){
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    "draw" => (int)$this->input->post('draw'),
                    "recordsTotal"=>0,"recordsFiltered"=>0,"data"=>[],
                    "error"=>"Server error: ".$e->getMessage()
                ]));
        }
    }

    /** Detail sederhana (HTML partial) dari billiard_paid */
    public function detail($id=null){
        $id = (int)$id;
        $row = $this->dm->get_paid_row($id);
        if (!$row){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }

        $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">';
        $add = function($k,$v) use (&$html){
            $html .= '<tr><th style="width:180px">'.$k.'</th><td>'.$v.'</td></tr>';
        };

        $add('Kode Booking', htmlspecialchars($row->kode_booking,ENT_QUOTES,'UTF-8'));
        if (!empty($row->nama))  $add('Nama', htmlspecialchars($row->nama,ENT_QUOTES,'UTF-8'));
        if (!empty($row->no_hp)) $add('No. HP', htmlspecialchars($row->no_hp,ENT_QUOTES,'UTF-8'));

        $add('Meja', htmlspecialchars($row->nama_meja ?: ('Meja #'.$row->meja_id),ENT_QUOTES,'UTF-8'));
        $add('Tanggal', date('d-m-Y', strtotime($row->tanggal)));
        $add('Jam', htmlspecialchars(($row->jam_mulai.' - '.$row->jam_selesai),ENT_QUOTES,'UTF-8'));
        $add('Durasi', (int)$row->durasi_jam.' jam');
        $add('Harga/Jam', 'Rp '.number_format((int)$row->harga_per_jam,0,',','.'));
        $add('Subtotal', 'Rp '.number_format((int)$row->subtotal,0,',','.'));
        if ((int)$row->kode_unik > 0) $add('Kode Unik', number_format((int)$row->kode_unik,0,',','.'));
        $add('Grand Total', '<b>Rp '.number_format((int)$row->grand_total,0,',','.').'</b>');
        $add('Metode Bayar', htmlspecialchars($row->metode_bayar ?: '-',ENT_QUOTES,'UTF-8'));
        $add('Dibayar', htmlspecialchars($row->paid_at,ENT_QUOTES,'UTF-8'));
        $add('Sumber', htmlspecialchars($row->source,ENT_QUOTES,'UTF-8'));

        $html .= '</table></div>';

        echo json_encode(["success"=>true, "html"=>$html, "title"=>'Detail #'.$row->kode_booking]);
    }
}
