<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_pos_riwayat extends Admin_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('M_admin_pos_riwayat','dm');
        cek_session_akses(get_class($this), $this->session->userdata('admin_session')); // jika dipakai
    }

    public function index(){
        $data["controller"] = get_class($this);
        $data["title"]      = "Riwayat Transaksi Cafe";
        $data["subtitle"]   = "Transaksi Lunas (Arsip)";
        $data["content"]    = $this->load->view('Admin_pos_riwayat_view',$data,true);
        $this->render($data);
    }

    /** DataTables server-side */
    public function get_data(){
    try{
        // === ambil filter ===
        $metode = $this->input->post('metode', true) ?: 'all';
        $mode   = $this->input->post('mode', true)   ?: 'all';

        // dari filter periode (kirim dari front-end dalam format 'Y-m-d H:i:s')
        $from_raw = trim((string)$this->input->post('dt_from', true));
        $to_raw   = trim((string)$this->input->post('dt_to', true));

        // normalisasi aman (biarkan kosong jika gagal parse)
        $from_dt = $from_raw !== '' ? date('Y-m-d H:i:s', strtotime($from_raw)) : null;
        $to_dt   = $to_raw   !== '' ? date('Y-m-d H:i:s', strtotime($to_raw))   : null;

        // === set filter ke model ===
        $this->dm->set_max_rows(500);
        $this->dm->set_paid_method_filter($metode);
        $this->dm->set_mode_filter($mode);
        $this->dm->set_period_filter($from_dt, $to_dt); // <-- baru

        // === ambil data ===
        $list = $this->dm->get_data();
        $data = [];

        foreach($list as $r){
            // Nomor
            $nomor = htmlspecialchars($r->nomor ?: ('#'.$r->src_id), ENT_QUOTES, 'UTF-8');

            // Mode badge
            $mode_raw = strtolower(trim((string)$r->mode));
            $badge='secondary'; $mode_label='-';
            if ($mode_raw==='dinein' || $mode_raw==='dine-in'){ $badge='info'; $mode_label='Makan di Tempat'; }
            elseif ($mode_raw==='delivery'){ $badge='warning'; $mode_label='Antar/Kirim'; }
            else { $badge='primary'; $mode_label='Bungkus'; }
            $mode_html = '<span class="badge badge-pill badge-'.$badge.'">'.htmlspecialchars($mode_label,ENT_QUOTES,'UTF-8').'</span>';

            // Meja / Nama (dinamis)
            $meja_html = '—';
            if ($mode_raw==='delivery'){
                $nama  = trim((string)$r->nama);
                $phone = trim((string)$r->customer_phone);
                $meja_html  = htmlspecialchars($nama !== '' ? $nama : '-', ENT_QUOTES, 'UTF-8');
                if ($phone !== ''){
                    $meja_html .= '<div class="text-muted small">'.htmlspecialchars($phone, ENT_QUOTES, 'UTF-8').'</div>';
                }
            } elseif ($mode_raw==='dinein' || $mode_raw==='dine-in'){
                $meja = $r->meja_nama ?: ($r->meja_kode ?: '—');
                $meja_html  = htmlspecialchars($meja, ENT_QUOTES, 'UTF-8');
                if (!empty($r->nama)){
                    $meja_html .= '<div class="text-muted small">'.htmlspecialchars($r->nama,ENT_QUOTES,'UTF-8').'</div>';
                }
            } else {
                $meja_html = htmlspecialchars($r->nama ?: '-', ENT_QUOTES, 'UTF-8');
            }

            // Waktu (archived_at → Pembayaran Diterima)
            $archived  = $r->archived_at ? date('d-m-Y H:i', strtotime($r->archived_at)) : '-';
            $arch_html = htmlspecialchars($archived,ENT_QUOTES,'UTF-8');

            // Uang
            $subtotal = (int)$r->total;
            $grand    = (int)$r->grand_total;
            $subtotal_html = 'Rp '.number_format($subtotal,0,',','.');
            $grand_html    = 'Rp '.number_format($grand,0,',','.');

            // Metode + icon
            $mraw = strtolower(trim((string)$r->paid_method));
            $icon = 'fe-help-circle'; $clr='secondary'; $label = strtoupper($mraw ?: '-');
            if ($mraw==='cash'){ $icon='fe-dollar-sign'; $clr='success'; $label='CASH'; }
            elseif ($mraw==='qris'){ $icon='fe-smartphone'; $clr='dark'; $label='QRIS'; }
            elseif ($mraw==='transfer'){ $icon='fe-credit-card'; $clr='primary'; $label='TRANSFER'; }
            $metode_html = '<span class="badge badge-pill badge-'.$clr.'"><i class="'.$icon.' mr-1"></i>'.$label.'</span>';

            $row = [];
            $row['id']          = (int)$r->id;
            $row['no']          = '';
            $row['nomor']       = '<span class="badge badge-dark">'.$nomor.'</span>';
            $row['mode']        = $mode_html;
            $row['meja']        = $meja_html;
            $row['archived_at'] = $arch_html;
            $row['subtotal']    = $subtotal_html;
            $row['grand']       = $grand_html;
            $row['metode']      = $metode_html;

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

    /** Detail (HTML partial) dari tabel paid */
    public function detail($id=null){
        $id = (int)$id;
        $bundle = $this->dm->get_paid_with_items($id);
        if (!$bundle){
            echo json_encode(["success"=>false,"title"=>"Gagal","pesan"=>"Data tidak ditemukan"]); return;
        }
        $o = $bundle['order'];
        $items = $bundle['items'];

        // HTML ringkas
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-sm table-striped mb-3">';
        $add = function($k,$v) use (&$html){
            $html .= '<tr><th style="width:180px">'.$k.'</th><td>'.$v.'</td></tr>';
        };
        $add('Nomor', htmlspecialchars($o->nomor ?? ('#'.$o->src_id),ENT_QUOTES,'UTF-8'));
        $add('Mode', htmlspecialchars($o->mode ?? '-',ENT_QUOTES,'UTF-8'));

        // dinamis untuk detail juga (optional, biar konsisten)
        $mode_raw = strtolower(trim((string)$o->mode));
        if ($mode_raw==='delivery'){
            if (!empty($o->nama)) $add('Nama', htmlspecialchars($o->nama,ENT_QUOTES,'UTF-8'));
            if (!empty($o->customer_phone)) $add('HP', htmlspecialchars($o->customer_phone,ENT_QUOTES,'UTF-8'));
            if (!empty($o->alamat_kirim)) $add('Alamat', nl2br(htmlspecialchars($o->alamat_kirim,ENT_QUOTES,'UTF-8')));
        } elseif ($mode_raw==='dinein' || $mode_raw==='dine-in'){
            if (!empty($o->meja_nama) || !empty($o->meja_kode)){
                $add('Meja', htmlspecialchars($o->meja_nama ?: $o->meja_kode,ENT_QUOTES,'UTF-8'));
            }
            if (!empty($o->nama)) $add('Nama', htmlspecialchars($o->nama,ENT_QUOTES,'UTF-8'));
        } else {
            if (!empty($o->nama)) $add('Nama', htmlspecialchars($o->nama,ENT_QUOTES,'UTF-8'));
        }

        $add('Subtotal', 'Rp '.number_format((int)$o->total,0,',','.'));
        if ((int)$o->delivery_fee>0) $add('Ongkir', 'Rp '.number_format((int)$o->delivery_fee,0,',','.'));
        if ((int)$o->kode_unik>0)    $add('Kode Unik', 'Rp '.number_format((int)$o->kode_unik,0,',','.'));
        $add('<b>Total Bayar</b>', '<b>Rp '.number_format((int)$o->grand_total,0,',','.').'</b>');
        $add('Metode', htmlspecialchars($o->paid_method ?? '-',ENT_QUOTES,'UTF-8'));

        // gunakan archived_at sebagai "Pembayaran Diterima"
        if (!empty($o->archived_at)) $add('Pembayaran Diterima', htmlspecialchars($o->archived_at,ENT_QUOTES,'UTF-8'));
        if (!empty($o->updated_at))  $add('Update',  htmlspecialchars($o->updated_at,ENT_QUOTES,'UTF-8'));

        $html .= '</table>';

        // Items
        $html .= '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
        $html .= '<thead><tr><th>#</th><th>Produk</th><th class="text-right">Harga</th><th class="text-center">Qty</th><th class="text-right">Subtotal</th></tr></thead><tbody>';
        $i=1;
        foreach($items as $it){
            $html .= '<tr>'
                . '<td>'.($i++).'</td>'
                . '<td>'.htmlspecialchars($it->nama ?? '-',ENT_QUOTES,'UTF-8').'</td>'
                . '<td class="text-right">Rp '.number_format((int)$it->harga,0,',','.').'</td>'
                . '<td class="text-center">'.(int)$it->qty.'</td>'
                . '<td class="text-right">Rp '.number_format((int)$it->subtotal,0,',','.').'</td>'
                . '</tr>';
        }
        $html .= '</tbody></table></div>';
        $html .= '</div>';

        echo json_encode(["success"=>true, "html"=>$html, "title"=>'Detail Riwayat #'.($o->nomor ?? $o->src_id)]);
    }
}
