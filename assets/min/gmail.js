(function(){
  if (window.__GMAIL_UI__) return; window.__GMAIL_UI__ = true;

  const URL_LIST   = "<?= site_url('admin_pos/gmail_inbox') ?>";
  const URL_SYNC   = "<?= site_url('admin_pos/gmail_sync') ?>";
  const URL_DETAIL = "<?= site_url('admin_pos/gmail_detail') ?>/";

  let autoSyncTimer = null;
  let isSyncing = false;

  function esc(s){ return (s||'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }


    let toastTimer = null;

    function markAsSeenInList(id){
      const btn = document.querySelector('#gmail-list [data-gmail-id="'+id+'"]');
      if (!btn) return;
      const badge = btn.querySelector('.gmail-badge');
      if (badge) badge.innerHTML = '<span class="badge badge-secondary">Dilihat</span>';
    }

  function showToast(msg, sub, type){
    type = type || 'info';

    let el = document.getElementById('gmail-toast');
    if (!el){
      el = document.createElement('div');
      el.id = 'gmail-toast';
      el.className = 'gmail-toast';
      el.innerHTML = `
        <div class="trow">
          <div class="ticon"><i class="mdi mdi-bell-outline"></i></div>
          <div style="min-width:0">
            <div class="tmsg" id="gmail-toast-msg"></div>
            <div class="tsub" id="gmail-toast-sub" style="display:none"></div>
          </div>
          <div class="tclose" title="Tutup">&times;</div>
        </div>
      `;
      document.body.appendChild(el);

      el.querySelector('.tclose').addEventListener('click', function(){
        hideToast();
      });
    }

    // set variant + icon
    el.classList.remove('success','info','warn','error');
    el.classList.add(type);

    const icon = el.querySelector('.ticon i');
    if (icon){
      icon.className = 'mdi ' + (
        type === 'success' ? 'mdi-check-circle-outline' :
        type === 'error'   ? 'mdi-alert-circle-outline' :
        type === 'warn'    ? 'mdi-alert-outline' :
                             'mdi-bell-outline'
      );
    }

    const msgEl = document.getElementById('gmail-toast-msg');
    const subEl = document.getElementById('gmail-toast-sub');
    if (msgEl) msgEl.textContent = msg || '';
    if (sub){
      subEl.style.display = '';
      subEl.textContent = sub;
    }else{
      subEl.style.display = 'none';
      subEl.textContent = '';
    }

    clearTimeout(toastTimer);
    el.style.display = 'block';
    requestAnimationFrame(()=> el.classList.add('show'));

    toastTimer = setTimeout(hideToast, 10000);

  }

  function hideToast(){
    const el = document.getElementById('gmail-toast');
    if (!el) return;
    el.classList.remove('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>{ try{ el.style.display='none'; }catch(e){} }, 200);
  }


  function uiState(state){ // 'loading' | 'list' | 'empty'
    const $loading = $('#gmail-loading');
    const $list    = $('#gmail-list');
    const $empty   = $('#gmail-empty');

    if (state === 'loading'){
      $loading.show();
      $list.hide();
      $empty.hide();
      return;
    }
    if (state === 'empty'){
      $loading.hide();
      $list.hide();
      $empty.show();
      return;
    }
    // list
    $loading.hide();
    $empty.hide();
    $list.show();
  }

  function setSubtitle(text){
    const el = document.getElementById('gmail-subtitle');
    if (el) el.textContent = text || 'Tarik email terbaru & cari cepat';
  }

  function gmailSyncing(on){
    isSyncing = !!on;
    const $btn = $('#gmail-sync-btn');
    if ($btn.length){
      $btn.prop('disabled', on);
      $btn.find('i').toggleClass('spin', on);
      $btn.find('.btn-text').text(on ? ' Sinkr…' : ' Cek Email'); // pastikan tombol punya span.btn-text
    }
    setSubtitle(on ? 'Sinkronisasi Gmail… (background)' : 'Tarik email terbaru & cari cepat');
  }

  function renderInbox(rows){
    const $list  = $('#gmail-list');
    const $count = $('#gmail-count');

    rows = rows || [];
    $count.text(rows.length + ' email');

    if (!rows.length){
      uiState('empty');
      return;
    }

    $list.empty();

    rows.forEach(r=>{
      const badge = (r.status === 'diproses')
      ? '<span class="badge badge-success">Diproses</span>'
      : (r.status === 'dilihat')
        ? '<span class="badge badge-secondary">Dilihat</span>'
        : '<span class="badge badge-warning">Baru</span>';

    const html = `
      <button type="button"
              class="list-group-item list-group-item-action"
              data-gmail-id="${parseInt(r.id,10)}"
              onclick="gmailOpenDetail(${parseInt(r.id,10)})">
        <div class="gmail-item__top">
          <div class="gmail-from">${esc(r.from_email||'-')}</div>
          <div class="gmail-date">${esc(r.received_at||'')}</div>
        </div>
        <div class="gmail-subject text-truncate">${esc(r.subject||'(tanpa subject)')}</div>
        <div class="d-flex align-items-center justify-content-between" style="gap:.5rem">
          <div class="gmail-snippet text-truncate" style="min-width:0">${esc(r.snippet||'')}</div>
          <div class="gmail-badge">${badge}</div>
        </div>
      </button>`;

      $list.append(html);
    });

    uiState('list');
  }

  // ===== READ TABLE ONLY =====
  function loadInbox(opts){
    opts = opts || {};
    const q = ($('#gmail-q').val() || '').trim();

    if (!opts.silent) uiState('loading');

    return $.getJSON(URL_LIST, { limit: opts.limit || 20, q })
      .done(function(res){
        const ok = (res && (res.ok === true || res.success === true));
        if (!ok){
          renderInbox([]);
          if (window.Swal) Swal.fire(res?.title||'Gagal', res?.msg||res?.pesan||'Tidak bisa memuat inbox', 'error');
          return;
        }
        renderInbox(res.data || []);
      })
      .fail(function(xhr){
        renderInbox([]);
        if (window.Swal) Swal.fire('Error','Koneksi bermasalah saat memuat Gmail','error');
        console.error(xhr?.responseText);
      });
  }

  // ===== SYNC BACKGROUND =====
  function syncInbox(opts){
    opts = opts || {};
    if (isSyncing) return;

    gmailSyncing(true);

    $.getJSON(URL_SYNC, { limit: opts.limit || 20 })
      .done(function(res){
      if (!res || res.ok !== true) {
        showToast('Sync gagal', 'Response tidak valid', 'error');
        console.warn('Sync gagal', res);
        return;
      }
      const sync = res.sync || null;
      if (!sync || sync.ok !== true) {
        showToast('Sync error', sync?.msg || 'Tidak bisa ambil email', 'error');
        console.warn('Sync error', sync);
        return;
      }

      const inserted = sync.inserted ? parseInt(sync.inserted,10) : 0;

      if (inserted > 0){
        showToast(`✅ ${inserted} email baru masuk`, sync.last_sync_new ? ('Last sync: '+sync.last_sync_new) : '', 'success');
        loadInbox({silent:true, limit: opts.limit || 20});
      } else {
        showToast('Tidak ada email baru', sync.last_sync_new ? ('Last sync: '+sync.last_sync_new) : '', 'info');
      }
    })
    .fail(function(xhr){
      showToast('Sync gagal', 'Koneksi/Server bermasalah', 'error');
      console.error(xhr?.responseText);
    })

      .fail(function(xhr){
        console.error(xhr?.responseText);
      })
      .always(function(){
        gmailSyncing(false);
      });
  }

  // ===== OPEN MODAL: table dulu, lalu autosync =====
  window.openGmailInbox = function(){
    $('#gmail-inbox-modal').modal('show');

    loadInbox({silent:false, limit:20}).always(function(){
      clearTimeout(autoSyncTimer);
      autoSyncTimer = setTimeout(function(){
        syncInbox({limit:20});
      }, 500);
    });

    setTimeout(()=>{ try{ document.getElementById('gmail-q').focus(); }catch(e){} }, 150);
  };

  $('#gmail-inbox-modal').on('hidden.bs.modal', function(){
    clearTimeout(autoSyncTimer);
  });

  window.gmailOpenDetail = function(id){
    $('#gmail-detail-modal').modal('show');
    $('#gmail-detail-body').html('<div class="text-center text-muted py-5">Memuat…</div>');
    document.getElementById('gmail-detail-subtitle').textContent = 'Memuat isi email…';
    document.getElementById('gmail-detail-badge').textContent = 'Loading';

    $.get(URL_DETAIL + id, function(html){
      $('#gmail-detail-body').html(html);
      document.getElementById('gmail-detail-subtitle').textContent = 'Email berhasil dimuat';
document.getElementById('gmail-detail-badge').textContent = 'OK';

  // ✅ update badge langsung (backend juga sudah update)
  markAsSeenInList(parseInt(id,10));
})
    .fail(function(xhr){
      $('#gmail-detail-body').html('<div class="text-danger">Gagal memuat detail.</div>');

      console.error(xhr?.responseText);
    });
  };

  // tombol refresh = paksa sync
  $(document).on('click', '#gmail-sync-btn', function(){
    syncInbox({limit:20});
  });

  // clear search
  $(document).on('click', '#gmail-clear', function(){
    $('#gmail-q').val('');
    loadInbox({silent:false, limit:20});
    try{ document.getElementById('gmail-q').focus(); }catch(e){}
  });

  // search debounce: hanya baca table
  let t=null;
  $(document).on('input', '#gmail-q', function(){
    clearTimeout(t);
    t = setTimeout(()=>loadInbox({silent:false, limit:20}), 250);
  });

})();

$(document).on('click', '#gmail-clear', function(){
  $('#gmail-q').val('');
  loadInbox({silent:false, limit:20});
  try{ document.getElementById('gmail-q').focus(); }catch(e){}
});