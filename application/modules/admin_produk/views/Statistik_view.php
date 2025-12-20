<div class="container-fluid">
  <div class="row"></div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="header-title"><?= $title; ?></h4>

          <figure class="highcharts-figure">
            <div id="container"></div>
            <p class="highcharts-description">
              Statistik produk terlaris berdasarkan kolom <b>terlaris</b> (produk aktif).
            </p>
          </figure>

          <style>
            .highcharts-figure,
            .highcharts-data-table table {
              min-width: 310px;
              max-width: 100%;
              margin: 1em auto;
            }

            /* Tinggi akan di-set otomatis via JS (biar pas jumlah item) */
            #container { height: 520px; }

            .highcharts-data-table table {
              font-family: Verdana, sans-serif;
              border-collapse: collapse;
              border: 1px solid var(--highcharts-neutral-color-10, #e6e6e6);
              margin: 10px auto;
              text-align: center;
              width: 100%;
              max-width: 800px;
            }

            .highcharts-data-table caption {
              padding: 1em 0;
              font-size: 1.2em;
              color: var(--highcharts-neutral-color-60, #666);
            }

            .highcharts-data-table th { font-weight: 600; padding: 0.5em; }

            .highcharts-data-table td,
            .highcharts-data-table th,
            .highcharts-data-table caption { padding: 0.5em; }

            .highcharts-data-table thead tr,
            .highcharts-data-table tbody tr:nth-child(even) {
              background: var(--highcharts-neutral-color-3, #f7f7f7);
            }

            .highcharts-description { margin: 0.3rem 10px; }
          </style>

          <?php
            $chart_categories = $chart_categories ?? [];
            $chart_values     = $chart_values ?? [];
          ?>

          <!-- ====== HIGHCHARTS ONLY ====== -->
          <script src="<?= base_url('/assets/admin/chart/highcharts.js'); ?>"></script>
          <script src="<?= base_url('/assets/admin/chart/exporting.js'); ?>"></script>
          <script src="<?= base_url('/assets/admin/chart/export-data.js'); ?>"></script>
          <script src="<?= base_url('/assets/admin/chart/accessibility.js'); ?>"></script>

          <script>
            window.addEventListener('load', function () {
              if (!window.Highcharts) { console.error('Highcharts belum load'); return; }

              const categories0 = <?= json_encode(array_values($chart_categories), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
              const values0     = <?= json_encode(array_values($chart_values), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;

              // validasi data
              const hasData = Array.isArray(values0) && values0.length > 0;

              // build pairs + sort DESC (biar benar2 terlaris #1)
              let pairs = [];
              if (hasData) {
                for (let i = 0; i < Math.min(categories0.length, values0.length); i++) {
                  pairs.push({
                    name: String(categories0[i] ?? ''),
                    y: Number(values0[i] ?? 0) || 0
                  });
                }
                pairs.sort((a, b) => b.y - a.y);
              } else {
                pairs = [{ name: 'Belum ada data', y: 0 }];
              }

              // categories dengan nomor urut di depan: "1. Produk"
              const categories = pairs.map((p, idx) => (idx + 1) + '. ' + p.name);
              const data       = pairs.map(p => p.y);

              // auto-height: makin banyak item, container makin tinggi
              const h = Math.max(520, categories.length * 34);
              const el = document.getElementById('container');
              if (el) el.style.height = h + 'px';

              // bikin chart
              const chart = Highcharts.chart('container', {
                chart: {
                  type: 'bar',
                  events: {
                    load: function () {
                      // ✅ Pastikan #1 berada di paling atas (auto flip jika kebalik)
                      const s = this.series && this.series[0];
                      if (!s || !s.points || s.points.length < 2) return;

                      const first = s.points[0];
                      const last  = s.points[s.points.length - 1];

                      // plotY lebih kecil = posisi lebih atas di layar
                      if (typeof first.plotY === 'number' && typeof last.plotY === 'number') {
                        const firstIsBelow = first.plotY > last.plotY;
                        if (firstIsBelow) {
                          this.xAxis[0].update({ reversed: true }, false);
                          this.redraw();
                        }
                      }
                    }
                  }
                },
                title: { text: '<?= addslashes($title) ?> Terlaris' },
                credits: { enabled: false },

                xAxis: {
                  categories: categories,
                  title: { text: null },
                  labels: {
                    style: { fontSize: '12px' }
                  }
                  // reversed akan otomatis diset di event load jika perlu
                },

                yAxis: {
                  min: 0,
                  allowDecimals: false,
                  title: { text: 'Jumlah terjual (kolom terlaris)' },
                  labels: {
                    formatter: function () {
                      return Highcharts.numberFormat(this.value, 0, ',', '.');
                    }
                  }
                },

                tooltip: {
                  useHTML: true,
                  formatter: function () {
                    // this.key sudah "1. Nama Produk"
                    return '<b>' + this.key + '</b><br/>Terjual: <b>' +
                      Highcharts.numberFormat(this.y, 0, ',', '.') + ' terjual</b>';
                  }
                },

                exporting: { enabled: true },

                plotOptions: {
                  series: {
                    colorByPoint: true,     // ✅ warna-warni
                    borderWidth: 0
                  },
                  bar: {
                    dataLabels: {
                      enabled: true,
                      formatter: function () {
                        return Highcharts.numberFormat(this.y, 0, ',', '.') + ' terjual';
                      }
                    }
                  }
                },

                legend: { enabled: false },

                series: [{
                  name: 'Terlaris',
                  data: data
                }]
              });
            });
          </script>

        </div>
      </div>
    </div>
  </div>
</div>
