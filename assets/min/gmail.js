(function (w, $) {
  'use strict';

  // ini akan di-set di openGmailInbox() di halaman
  w.gmailInitAndOpen = function (cfg) {
  // kalau sudah init, panggil open inbox versi global (reload + autosync)
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

    var autoSyncTimer = null;
    var isSyncing = false;
    var toastTimer = null;

    function esc(s) {
      return (s || '').toString().replace(/[&<>"']/g, function (m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
      });
    }

    function markAsSeenInList(id) {
      var btn = document.querySelector('#gmail-list [data-gmail-id="' + id + '"]');
      if (!btn) return;
      var badge = btn.querySelector('.gmail-badge');
      if (badge) badge.innerHTML = '<span class="badge badge-secondary">Dilihat</span>';
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

        el.querySelector('.tclose').addEventListener('click', function () {
          hideToast();
        });
      }

      el.classList.remove('success', 'info', 'warn', 'error');
      el.classList.add(type);

      var icon = el.querySelector('.ticon i');
      if (icon) {
        icon.className = 'mdi ' + (
          type === 'success' ? 'mdi-check-circle-outline' :
          type === 'error'   ? 'mdi-alert-circle-outline' :
          type === 'warn'    ? 'mdi-alert-outline' :
                               'mdi-bell-outline'
        );
      }

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

    function gmailSyncing(on) {
      isSyncing = !!on;
      var $btn = $('#gmail-sync-btn');
      if ($btn.length) {
        $btn.prop('disabled', on);
        $btn.find('i').toggleClass('spin', on);
        $btn.find('.btn-text').text(on ? ' Sinkr…' : ' Cek Email');
      }
      setSubtitle(on ? 'Sinkronisasi Gmail…' : 'Tarik email terbaru & cari cepat');
    }

    function renderInbox(rows) {
      var $list = $('#gmail-list');
      var $count = $('#gmail-count');

      rows = rows || [];
      $count.text(rows.length + ' email');

      if (!rows.length) { uiState('empty'); return; }

      $list.empty();

      rows.forEach(function (r) {
        var badge = (r.status === 'diproses')
          ? '<span class="badge badge-success">Diproses</span>'
          : (r.status === 'dilihat')
            ? '<span class="badge badge-secondary">Dilihat</span>'
            : '<span class="badge badge-warning">Baru</span>';

        var id = parseInt(r.id, 10) || 0;

        var html =
          '<button type="button" class="list-group-item list-group-item-action" ' +
                  'data-gmail-id="' + id + '">' +
            '<div class="gmail-item__top">' +
              '<div class="gmail-from">' + esc(r.from_email || '-') + '</div>' +
              '<div class="gmail-date">' + esc(r.received_at || '') + '</div>' +
            '</div>' +
            '<div class="gmail-subject text-truncate">' + esc(r.subject || '(tanpa subject)') + '</div>' +
            '<div class="d-flex align-items-center justify-content-between" style="gap:.5rem">' +
              '<div class="gmail-snippet text-truncate" style="min-width:0">' + esc(r.snippet || '') + '</div>' +
              '<div class="gmail-badge">' + badge + '</div>' +
            '</div>' +
          '</button>';

        $list.append(html);
      });

      uiState('list');
    }

    function loadInbox(opts) {
      opts = opts || {};
      var q = ($('#gmail-q').val() || '').trim();

      if (!opts.silent) uiState('loading');

      return $.getJSON(URL_LIST, { limit: opts.limit || 20, q: q })
        .done(function (res) {
          var ok = !!(res && (res.ok === true || res.success === true));
          if (!ok) {
            renderInbox([]);
            if (w.Swal) Swal.fire((res && (res.title || 'Gagal')) || 'Gagal',
                                 (res && (res.msg || res.pesan)) || 'Tidak bisa memuat inbox',
                                 'error');
            return;
          }
          renderInbox(res.data || []);
        })
        .fail(function (xhr) {
          renderInbox([]);
          var msg = 'Koneksi bermasalah saat memuat Gmail';
          if (xhr) {
            msg += '\nHTTP: ' + xhr.status + ' ' + (xhr.statusText || '');
          // kalau server balas HTML (login/error), tampilkan sedikit
          var t = (xhr.responseText || '').toString();
          if (t) msg += '\nResp: ' + t.substring(0, 200);
        }
        if (w.Swal) Swal.fire('Error', msg, 'error');
        console.error(xhr && xhr.responseText);
      });

    }

    function syncInbox(opts) {
      opts = opts || {};
      if (isSyncing) return;

      gmailSyncing(true);

      $.getJSON(URL_SYNC, { limit: opts.limit || 20 })
        .done(function (res) {
          if (!res || res.ok !== true) {
            showToast('Sync gagal', 'Response tidak valid', 'error');
            console.warn('Sync gagal', res);
            return;
          }
          var sync = res.sync || null;
          if (!sync || sync.ok !== true) {
            showToast('Sync error', (sync && sync.msg) ? sync.msg : 'Tidak bisa ambil email', 'error');
            console.warn('Sync error', sync);
            return;
          }

          var inserted = sync.inserted ? parseInt(sync.inserted, 10) : 0;

          if (inserted > 0) {
            showToast('✅ ' + inserted + ' email baru masuk', sync.last_sync_new ? ('Last sync: ' + sync.last_sync_new) : '', 'success');
            loadInbox({ silent: true, limit: opts.limit || 20 });
          } else {
            showToast('Tidak ada email baru', sync.last_sync_new ? ('Last sync: ' + sync.last_sync_new) : '', 'info');
          }
        })
        .fail(function (xhr) {
          showToast('Sync gagal', 'Koneksi/Server bermasalah', 'error');
          console.error(xhr && xhr.responseText);
        })
        .always(function () {
          gmailSyncing(false);
        });
    }

   function openInbox() {
  $('#gmail-inbox-modal').modal('show');

  loadInbox({ silent: false, limit: 20 }).always(function () {
    clearTimeout(autoSyncTimer);
    autoSyncTimer = setTimeout(function () {
      syncInbox({ limit: 20 });
    }, 500);
  });

  setTimeout(function () {
    try { document.getElementById('gmail-q').focus(); } catch (e) {}
  }, 150);
}

// expose supaya bisa dipanggil lagi saat klik tombol
w.gmailOpenInbox = openInbox;

// setelah init → langsung buka inbox
openInbox();

    function openDetail(id) {
      $('#gmail-detail-modal').modal('show');
      $('#gmail-detail-body').html('<div class="text-center text-muted py-5">Memuat…</div>');

      var s1 = document.getElementById('gmail-detail-subtitle');
      var b1 = document.getElementById('gmail-detail-badge');
      if (s1) s1.textContent = 'Memuat isi email…';
      if (b1) b1.textContent = 'Loading';

      $.get(URL_DETAIL + id, function (html) {
        $('#gmail-detail-body').html(html);
        if (s1) s1.textContent = 'Email berhasil dimuat';
        if (b1) b1.textContent = 'OK';
        markAsSeenInList(parseInt(id, 10));
      })
      .fail(function (xhr) {
        $('#gmail-detail-body').html('<div class="text-danger">Gagal memuat detail.</div>');
        console.error(xhr && xhr.responseText);
      });
    }

    // expose global (dipakai render list)
    w.gmailOpenDetail = openDetail;

    // === event binding (pakai namespace biar ga dobel) ===
    $('#gmail-inbox-modal').off('hidden.bs.modal.gmail')
      .on('hidden.bs.modal.gmail', function () { clearTimeout(autoSyncTimer); });

    $(document).off('click.gmail', '#gmail-sync-btn')
      .on('click.gmail', '#gmail-sync-btn', function () { syncInbox({ limit: 20 }); });

    $(document).off('click.gmail', '#gmail-clear')
      .on('click.gmail', '#gmail-clear', function () {
        $('#gmail-q').val('');
        loadInbox({ silent: false, limit: 20 });
        try { document.getElementById('gmail-q').focus(); } catch (e) {}
      });

    var t = null;
    $(document).off('input.gmail', '#gmail-q')
      .on('input.gmail', '#gmail-q', function () {
        clearTimeout(t);
        t = setTimeout(function () { loadInbox({ silent: false, limit: 20 }); }, 250);
      });

    // klik item (delegation) → detail
    $(document).off('click.gmail', '#gmail-list .list-group-item')
      .on('click.gmail', '#gmail-list .list-group-item', function () {
        var id = $(this).data('gmail-id');
        if (id) openDetail(id);
      });

    // setelah init → langsung buka inbox
    openInbox();
  };

})(window, window.jQuery);
