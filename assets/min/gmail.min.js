(function (w, $) {
  'use strict';

  w.gmailInitAndOpen = function (cfg) {

    if (w.__GMAIL_UI__) {
      if (w.gmailOpenInbox) w.gmailOpenInbox();
      else $('#gmail-inbox-modal').modal('show');
      return;
    }
    w.__GMAIL_UI__ = true;

    cfg = cfg || (w.GMAIL_CFG || {});
    var URL_LIST   = cfg.URL_LIST   || '';
    var URL_SYNC   = cfg.URL_SYNC   || '';
    var URL_DETAIL = cfg.URL_DETAIL || '';

    if (!URL_LIST || !URL_SYNC || !URL_DETAIL) {
      console.error('GMAIL_CFG belum lengkap', cfg);
      if (w.Swal) Swal.fire('Error', 'Konfigurasi Gmail belum lengkap.', 'error');
      return;
    }

    // ✅ STATE pagination
    var CUR = { page: 1, pages: 1, limit: 10 };

    var autoSyncTimer = null;
    var isSyncing = false;
    var toastTimer = null;

    function esc(s) {
      return (s || '').toString().replace(/[&<>"']/g, function (m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
      });
    }

    function uiState(state) {
      var $loading = $('#gmail-loading');
      var $list = $('#gmail-list');
      var $empty = $('#gmail-empty');

      if (state === 'loading') { $loading.show(); $list.hide(); $empty.hide(); return; }
      if (state === 'empty')   { $loading.hide(); $list.hide(); $empty.show(); return; }

      $loading.hide(); $empty.hide(); $list.show();
    }

    function setSubtitle(text) {
      var el = document.getElementById('gmail-subtitle');
      if (el) el.textContent = text || 'Tarik email terbaru & cari cepat';
    }

    function hideToast() {
      var el = document.getElementById('gmail-toast');
      if (!el) return;
      el.classList.remove('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function () {
        try { el.style.display = 'none'; } catch (e) {}
      }, 200);
    }

    function showToast(msg, sub, type) {
      type = type || 'info';

      var el = document.getElementById('gmail-toast');
      if (!el) {
        el = document.createElement('div');
        el.id = 'gmail-toast';
        el.className = 'gmail-toast';
        el.innerHTML =
          '<div class="trow">' +
            '<div class="ticon"><i class="mdi mdi-bell-outline"></i></div>' +
            '<div style="min-width:0">' +
              '<div class="tmsg" id="gmail-toast-msg"></div>' +
              '<div class="tsub" id="gmail-toast-sub" style="display:none"></div>' +
            '</div>' +
            '<div class="tclose" title="Tutup">&times;</div>' +
          '</div>';
        document.body.appendChild(el);
        el.querySelector('.tclose').addEventListener('click', hideToast);
      }

      el.classList.remove('success', 'info', 'warn', 'error');
      el.classList.add(type);

      var msgEl = document.getElementById('gmail-toast-msg');
      var subEl = document.getElementById('gmail-toast-sub');

      if (msgEl) msgEl.textContent = msg || '';
      if (sub) {
        subEl.style.display = '';
        subEl.textContent = sub;
      } else {
        subEl.style.display = 'none';
        subEl.textContent = '';
      }

      clearTimeout(toastTimer);
      el.style.display = 'block';
      requestAnimationFrame(function () { el.classList.add('show'); });

      toastTimer = setTimeout(hideToast, 10000);
    }

    function gmailSyncing(on) {
      isSyncing = !!on;
      var $btn = $('#gmail-sync-btn');
      if ($btn.length) {
        $btn.prop('disabled', on);
        $btn.find('i').toggleClass('spin', on);
        $btn.find('.btn-text').text(on ? ' Sinkr…' : ' Refresh Email');
      }
      setSubtitle(on ? 'Sinkronisasi Gmail…' : 'Tarik email terbaru & cari cepat');
    }

    // ✅ pager UI (sesuai id HTML kamu)
    function renderPager(meta) {
      meta = meta || {};
      CUR.page  = parseInt(meta.page, 10)  || 1;
      CUR.pages = parseInt(meta.pages, 10) || 1;
      CUR.limit = parseInt(meta.limit, 10) || CUR.limit;

      $('#gmail-page-info').text(CUR.page + ' / ' + CUR.pages);

      var hasPrev = !!meta.has_prev || CUR.page > 1;
      var hasNext = !!meta.has_next || CUR.page < CUR.pages;

      $('#gmail-prev-page').prop('disabled', !hasPrev);
      $('#gmail-next-page').prop('disabled', !hasNext);

      if (meta.from != null && meta.to != null && meta.filtered != null) {
        $('#gmail-count').text(meta.from + '-' + meta.to + ' dari ' + meta.filtered + ' email');
      } else {
        $('#gmail-count').text('0 email');
      }
    }

    function renderInbox(rows) {
      var $list = $('#gmail-list');
      rows = rows || [];

      if (!rows.length) { uiState('empty'); return; }

      $list.empty();

      rows.forEach(function (r) {
        var badge = (r.status === 'diproses')
          ? '<span class="badge badge-success">Diproses</span>'
          : (r.status === 'dilihat')
            ? '<span class="badge badge-secondary">Dilihat</span>'
            : '<span class="badge badge-warning">Baru</span>';

        var id = parseInt(r.id, 10) || 0;

        $list.append(
          '<button type="button" class="list-group-item list-group-item-action" data-gmail-id="' + id + '">' +
            '<div class="gmail-item__top">' +
              '<div class="gmail-from">' + esc(r.from_email || '-') + '</div>' +
              '<div class="gmail-date">' + esc(r.received_at || '') + '</div>' +
            '</div>' +
            '<div class="gmail-subject text-truncate">' + esc(r.subject || '(tanpa subject)') + '</div>' +
            '<div class="d-flex align-items-center justify-content-between" style="gap:.5rem">' +
              '<div class="gmail-snippet text-truncate" style="min-width:0">' + esc(r.snippet || '') + '</div>' +
              '<div class="gmail-badge">' + badge + '</div>' +
            '</div>' +
          '</button>'
        );
      });

      uiState('list');
    }

    function loadInbox(opts) {
      opts = opts || {};
      var q = ($('#gmail-q').val() || '').trim();

      var page  = (opts.page  != null) ? opts.page  : CUR.page;
      var limit = (opts.limit != null) ? opts.limit : CUR.limit;

      if (!opts.silent) uiState('loading');

      return $.getJSON(URL_LIST, { limit: limit, page: page, q: q })
        .done(function (res) {
          var ok = !!(res && (res.ok === true || res.success === true));
          if (!ok) {
            renderInbox([]);
            renderPager({page:1,pages:1,limit:limit,filtered:0,from:0,to:0,has_prev:false,has_next:false});
            if (w.Swal) Swal.fire('Gagal', (res && (res.msg || res.pesan)) || 'Tidak bisa memuat inbox', 'error');
            return;
          }
          renderPager(res.meta || {});
          renderInbox(res.data || []);
        })
        .fail(function (xhr) {
          renderInbox([]);
          renderPager({page:1,pages:1,limit:limit,filtered:0,from:0,to:0,has_prev:false,has_next:false});
          if (w.Swal) Swal.fire('Error', 'Koneksi bermasalah saat memuat Gmail', 'error');
          console.error('LIST FAIL:', xhr && xhr.responseText);
        });
    }

    function syncInbox(opts) {
      opts = opts || {};
      if (isSyncing) return;

      gmailSyncing(true);

      $.getJSON(URL_SYNC, { limit: opts.limit || CUR.limit })
        .done(function (res) {
          if (!res || res.ok !== true) {
            showToast('Sync gagal', 'Response tidak valid', 'error');
            return;
          }
          var sync = res.sync || null;
          if (!sync || sync.ok !== true) {
            showToast('Sync error', (sync && sync.msg) ? sync.msg : 'Tidak bisa ambil email', 'error');
            return;
          }

          var inserted = sync.inserted ? parseInt(sync.inserted, 10) : 0;
          if (inserted > 0) {
            showToast('✅ ' + inserted + ' email baru masuk', sync.last_sync_new ? ('Last sync: ' + sync.last_sync_new) : '', 'success');
            CUR.page = 1; // ✅ balik ke halaman 1 biar yang baru kelihatan
            loadInbox({ silent: true, page: CUR.page, limit: CUR.limit });
          } else {
            showToast('Tidak ada email baru', sync.last_sync_new ? ('Last sync: ' + sync.last_sync_new) : '', 'info');
          }
        })
        .fail(function (xhr) {
          showToast('Sync gagal', 'Koneksi/Server bermasalah', 'error');
          console.error('SYNC FAIL:', xhr && xhr.responseText);
        })
        .always(function () {
          gmailSyncing(false);
        });
    }

    function openInbox() {
      CUR.limit = 10;   // ✅ load awal 10
      CUR.page  = 1;

      $('#gmail-inbox-modal').modal('show');

      loadInbox({ silent: false, limit: CUR.limit, page: CUR.page }).always(function () {
        clearTimeout(autoSyncTimer);
        autoSyncTimer = setTimeout(function () {
          syncInbox({ limit: CUR.limit });
        }, 500);
      });

      setTimeout(function () {
        try { document.getElementById('gmail-q').focus(); } catch (e) {}
      }, 150);
    }

    w.gmailOpenInbox = openInbox;

    function openDetail(id) {
      $('#gmail-detail-modal').modal('show');
      $('#gmail-detail-body').html('<div class="text-center text-muted py-5">Memuat…</div>');

      $.get(URL_DETAIL + id, function (html) {
        $('#gmail-detail-body').html(html);
      }).fail(function (xhr) {
        $('#gmail-detail-body').html('<div class="text-danger">Gagal memuat detail.</div>');
        console.error('DETAIL FAIL:', xhr && xhr.responseText);
      });
    }

    w.gmailOpenDetail = openDetail;

    // ✅ event bindings
    $('#gmail-inbox-modal').off('hidden.bs.modal.gmail')
      .on('hidden.bs.modal.gmail', function () { clearTimeout(autoSyncTimer); });

    $(document).off('click.gmail', '#gmail-sync-btn')
      .on('click.gmail', '#gmail-sync-btn', function () { syncInbox({ limit: CUR.limit }); });

    $(document).off('click.gmail', '#gmail-clear')
      .on('click.gmail', '#gmail-clear', function () {
        $('#gmail-q').val('');
        CUR.page = 1;
        loadInbox({ silent: false, limit: CUR.limit, page: CUR.page });
      });

    // ✅ prev/next (sesuai id HTML)
    $(document).off('click.gmail', '#gmail-prev-page')
      .on('click.gmail', '#gmail-prev-page', function () {
        if (CUR.page > 1) {
          CUR.page--;
          loadInbox({ silent: false, limit: CUR.limit, page: CUR.page });
        }
      });

    $(document).off('click.gmail', '#gmail-next-page')
      .on('click.gmail', '#gmail-next-page', function () {
        if (CUR.page < CUR.pages) {
          CUR.page++;
          loadInbox({ silent: false, limit: CUR.limit, page: CUR.page });
        }
      });

    var t = null;
    $(document).off('input.gmail', '#gmail-q')
      .on('input.gmail', '#gmail-q', function () {
        clearTimeout(t);
        t = setTimeout(function () {
          CUR.page = 1; // ✅ search harus reset page
          loadInbox({ silent: false, limit: CUR.limit, page: CUR.page });
        }, 250);
      });

    $(document).off('click.gmail', '#gmail-list .list-group-item')
      .on('click.gmail', '#gmail-list .list-group-item', function () {
        var id = $(this).data('gmail-id');
        if (id) openDetail(id);
      });

    // ✅ buka sekali saja
    openInbox();
  };

})(window, window.jQuery);
