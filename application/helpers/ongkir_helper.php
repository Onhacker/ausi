<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('haversine_m')) {
  function haversine_m($lat1,$lon1,$lat2,$lon2){
    $R = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
    return 2*$R*asin(sqrt($a));
  }
}

if (!function_exists('hitung_ongkir_server')) {
  /**
   * @return array {
   *   bool   ok,                // <- baru
   *   int    distance_m,
   *   string distance_kind,     // 'road' | 'air'
   *   ?string provider,         // 'osrm' | 'osmde' | null
   *   int    fee,
   *   int    fee_rounded,
   *   bool   allowed,
   *   ?string err               // 'NO_ROUTE' jika road-only & gagal
   * }
   */
  function hitung_ongkir_server(
    $store_lat,$store_lng,$dest_lat,$dest_lng,
    $base_km,$base_fee,$per_km,
    $max_radius_m = 3000,
    $prefer_road  = true,
    $road_only    = false   // <- TAMBAHAN: paksa via jalan
  ){
    $dist_m = null; $provider = null; $kind = 'air';

    if ($prefer_road){
      list($d,$prov) = osrm_driving_distance_m($store_lat,$store_lng,$dest_lat,$dest_lng);
      if (is_int($d) && $d > 0){
        $dist_m = $d; $provider = $prov; $kind = 'road';
      } else if ($road_only){
        // WAJIB via jalan: gagal = error (tanpa fallback ke haversine)
        return ['ok'=>false, 'err'=>'NO_ROUTE'];
      }
    }

    // Fallback ke haversine HANYA kalau TIDAK road_only
    if ($dist_m === null){
      $dist_m = (int) round(haversine_m($store_lat,$store_lng,$dest_lat,$dest_lng));
      $kind   = 'air';
    }

    // Tarif (kelipatan 0.5 km)
    $km  = $dist_m / 1000;
    if ($km <= (float)$base_km){
      $fee = (int)$base_fee;
    } else {
      $extraKm = ceil(max(0, $km - (float)$base_km) * 2) / 2;
      $fee = (int)$base_fee + $extraKm * (int)$per_km;
    }
    $fee_rounded = (int) ceil($fee / 1000) * 1000;

    return [
      'ok'            => true,               // <- baru
      'distance_m'    => (int)$dist_m,
      'distance_kind' => $kind,
      'provider'      => $provider,
      'fee'           => (int)$fee,
      'fee_rounded'   => (int)$fee_rounded,
      'allowed'       => ($dist_m <= (int)$max_radius_m)
    ];
  }
}


if (!function_exists('osrm_driving_distance_m')) {
  /**
   * Coba hitung jarak jalan (meter) via OSRM publik.
   * Balik [distance_m|null, provider|null]. Timeout per attempt default 9000ms.
   */
  function osrm_driving_distance_m($lat1,$lon1,$lat2,$lon2,$timeout_ms=9000){
    $providers = [
      ['name'=>'osrm',  'url'=>"https://router.project-osrm.org/route/v1/driving/$lon1,$lat1;$lon2,$lat2?overview=false&alternatives=false&steps=false"],
      ['name'=>'osmde', 'url'=>"https://routing.openstreetmap.de/routed-car/route/v1/driving/$lon1,$lat1;$lon2,$lat2?overview=false&alternatives=false&steps=false"],
    ];
    foreach ($providers as $p){
      $ch = curl_init();
      curl_setopt_array($ch, [
        CURLOPT_URL => $p['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT_MS => $timeout_ms,
        CURLOPT_TIMEOUT_MS => $timeout_ms,
        CURLOPT_USERAGENT => 'MIN-ongkir/1.0',
      ]);
      $resp = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($resp && $code >= 200 && $code < 300){
        $j = json_decode($resp, true);
        if (isset($j['code']) && $j['code']==='Ok' && isset($j['routes'][0]['distance'])){
          $d = (int) round($j['routes'][0]['distance']);
          if ($d > 0) return [$d, $p['name']];
        }
      }
    }
    return [null, null];
  }
}

