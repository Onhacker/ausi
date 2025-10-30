<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('slugify_mod')) {
  function slugify_mod($s){
    $s = strtolower(trim((string)$s));
    $s = preg_replace('~[^\pL\d]+~u','-',$s);
    $s = trim($s,'-');
    return $s;
  }
}

if (!function_exists('allowed_module_slugs')) {
  function allowed_module_slugs(){
    static $cache = null;
    if ($cache !== null) return $cache;

    $CI  = &get_instance();
    $u   = $CI->session->userdata('admin_username');
    $lvl = $CI->session->userdata('admin_level');

    // ✅ pakai id_session (fallback admin_session jika memang itu yg diset)
    $sid = $CI->session->userdata('id_session');
    if (!$sid) $sid = $CI->session->userdata('admin_session');

    // Super admin: bebas
    if ($lvl === 'admin' && $u === 'admin') {
      $cache = '__ALL__';
      return $cache;
    }

    // Ambil modul yang diizinkan utk user sekarang
    $CI->db->select('m.nama_modul, m.link')
           ->from('modul m')
           ->join('users_modul um','um.id_modul=m.id_modul')
           ->where('um.id_session', $sid)
           ->where('m.aktif','Y')
           ->where('m.publish','Y');
    $rows = $CI->db->get()->result();

    $set  = [];
    foreach($rows as $r){
      // izinkan dengan slug nama modul
      if (!empty($r->nama_modul)) {
        $set[ slugify_mod($r->nama_modul) ] = true;
      }
      // izinkan dengan slug link (mis. 'admin_permohonan' / 'admin_dashboard/monitor')
      if (!empty($r->link)) {
        $link = trim($r->link, '/');
        $set[ slugify_mod($link) ] = true;  // 'admin-permohonan' dsb
        $set[ $link ] = true;               // exact match 'admin_permohonan' / 'admin_dashboard/monitor'
      }
    }
    $cache = $set;
    return $cache;
  }
}

if (!function_exists('user_can_mod')) {
  // $require: string|array; boleh nama modul, slug, link, atau slug link
  function user_can_mod($require){
    $allow = allowed_module_slugs();
    if ($allow === '__ALL__') return true;

    $reqs = is_array($require) ? $require : [$require];
    foreach($reqs as $r){
      $r = trim((string)$r, '/');
      // cek berbagai bentuk: exact, slug, serta versi slash→dash
      if (isset($allow[$r])) return true;
      $slug = slugify_mod($r);
      if (isset($allow[$slug])) return true;
    }
    return false;
  }
}

if (!function_exists('build_menu')) {
  function build_menu(array $items, array $opts = []){
    $CI   = &get_instance();
    $uri  = trim($CI->uri->uri_string(),'/');

    $o = array_merge([
      'li_has_child_class' => 'has-submenu',
      'li_active_class'    => 'active-menu',
      'child_ul_class'     => 'submenu',
      'a_class'            => '',
    ], $opts);

    $renderItems = function($items) use (&$renderItems, $o, $uri){
      $html = '';
      foreach($items as $it){
        // filter by akses
        if (isset($it['require']) && !user_can_mod($it['require'])) continue;

        $label = $it['label'] ?? '';
        $url   = $it['url']   ?? '#';
        $icon  = $it['icon']  ?? '';
        $kids  = $it['children'] ?? [];

        // render anak (jika semua anak ketendang → parent ikut hilang)
        $childHtml = '';
        if ($kids){
          $childHtml = $renderItems($kids);
          if ($childHtml === '') continue;
        }

        // active?
        $isActive = false;
        if (!empty($it['active_match'])) {
          foreach((array)$it['active_match'] as $pat){
            if (strpos($uri, trim($pat,'/')) === 0){ $isActive=true; break; }
          }
        } else {
          $route = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
          if ($route !== '' && strpos($uri, $route) === 0) $isActive = true;
        }

        $liClsArr = [];
        if ($kids)    $liClsArr[] = $o['li_has_child_class'];
        if ($isActive)$liClsArr[] = $o['li_active_class'];
        $liCls = $liClsArr ? ' class="'.implode(' ',$liClsArr).'"' : '';

        $iconHtml = $icon ? '<i class="'.htmlspecialchars($icon).'"></i> ' : '';

        if ($kids){
          $html .= '<li'.$liCls.'>';
          $html .= '<a href="#">'.$iconHtml.htmlspecialchars($label).'<div class="arrow-down"></div></a>';
          $html .= '<ul class="'.htmlspecialchars($o['child_ul_class']).'">'.$childHtml.'</ul>';
          $html .= '</li>';
        } else {
          $html .= '<li'.$liCls.'><a href="'.htmlspecialchars($url).'">'.$iconHtml.htmlspecialchars($label).'</a></li>';
        }
      }
      return $html;
    };

    return $renderItems($items);
  }
}
