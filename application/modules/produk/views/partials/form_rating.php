<script>
(function(){
  if (window.__RATING_UNIFIED__) return;
  window.__RATING_UNIFIED__ = true;

  /* ====== CSRF (CodeIgniter) ====== */
  var CSRF = <?php
    if ($this->config->item('csrf_protection')) {
      echo json_encode([
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
      ]);
    } else {
      echo 'null';
    }
  ?>;

  /* ====== Bridge fokus: izinkan fokus ke dalam Swal walau ada Bootstrap modal ====== */
  document.addEventListener('focusin', function(e){
    var swal = document.querySelector('.swal2-container');
    if (swal && swal.contains(e.target)) e.stopPropagation();
  }, true);

  function withModalFocusLift(run){
    var restore = function(){};
    if (window.jQuery && $.fn && $.fn.modal && $.fn.modal.Constructor){
      var prev = $.fn.modal.Constructor.prototype._enforceFocus;
      $.fn.modal.Constructor.prototype._enforceFocus = function(){};
      restore = function(){ $.fn.modal.Constructor.prototype._enforceFocus = prev; };
    } else if (window.bootstrap && window.bootstrap.Modal){
      var modalEl = document.querySelector('.modal.show');
      if (modalEl){
        var inst = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
        var oldFocus = inst._config ? inst._config.focus : undefined;
        try{ if(inst._focustrap) inst._focustrap.deactivate(); }catch(_){}
        if (inst._config) inst._config.focus = false;
        restore = function(){
          if (inst._config) inst._config.focus = (oldFocus !== undefined) ? oldFocus : true;
          try{ if(inst._focustrap) inst._focustrap.activate(); }catch(_){}
        };
      }
    }
    run(restore);
  }

  /* ====== Utils ====== */
  function postJSON(url, data){
    var fd = new FormData();
    for (var k in data){ fd.append(k, data[k]); }
    if (CSRF){ fd.append(CSRF.name, CSRF.hash); }
    return fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
      .then(function(r){ return r.json(); });
  }
  function escapeHtml(str){
    return String(str||'').replace(/[&<>"']/g, function(m){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
  }
  function roundHalf(v){ var rr=Math.round(v*2)/2; return Math.max(0,Math.min(5,rr)); }
  function applyAvgToCard(card, avg, count){
    var meter = card.querySelector('.star-meter');
    if (meter){
      var r = roundHalf(avg), full=Math.floor(r), half=(r-full)===0.5, empty=5-full-(half?1:0);
      var html=''; for(var i=0;i<full;i++) html+='<i class="mdi mdi-star full"></i>';
      if(half) html+='<i class="mdi mdi-star-half-full full"></i>';
      for(var j=0;j<empty;j++) html+='<i class="mdi mdi-star-outline empty"></i>';
      meter.innerHTML = html;
    }
    var avgEl = card.querySelector('.avg-label');  if (avgEl) avgEl.textContent = avg.toFixed(1).replace('.',',');
    var cntEl = card.querySelector('.count-label'); if (cntEl) cntEl.textContent = parseInt(count||0,10);
    var soldLbl = card.querySelector('.sold-label');
    if (soldLbl){
      var txtTerjual = (soldLbl.textContent.match(/·\s[\d\.]+\s+terjual/i)||[''])[0];
      soldLbl.textContent = avg.toFixed(1).replace('.',',') + '/5'
        + (count ? ' · '+count+' ulasan' : '')
        + (txtTerjual ? ' ' + txtTerjual : '');
    }
  }

  /* ====== Modal rating ====== */
  function openRatingModal(prodId, prodName){
    if (!window.Swal){
      var val  = prompt('Beri rating (1–5) untuk: '+prodName, 5);
      var star = parseInt(val||'0',10);
      if (!star||star<1||star>5) return;
      var nm   = prompt('Nama (opsional):','') || '';
      if (nm.length > 60) nm = nm.slice(0,60);
      var rev  = prompt('Tulis review (opsional):','') || '';
      return submitRating(prodId, star, rev, nm);
    }

    var selected = 0, maxLenReview = 1000, maxLenNama = 60;

    withModalFocusLift(function(restoreFocusTrap){
      Swal.fire({
        target: document.querySelector('.modal.show') || document.body,
        heightAuto: false,
        title: 'Kasih Rating',
        html:
          '<div style="margin-top:.25rem; font-weight:600;">'+escapeHtml(prodName)+'</div>'+
          '<div class="swal-rate-wrap" style="margin:.75rem 0 .5rem; display:flex; justify-content:center; gap:8px;">' +
            [1,2,3,4,5].map(function(n){
              return '<i class="mdi mdi-star-outline rate-star" data-n="'+n+'" style="font-size:28px; cursor:pointer;"></i>';
            }).join('') +
          '</div>'+
          '<div class="form-group" style="text-align:left;">' +
            '<label class="small mb-1" for="swal-nama">Nama (opsional)</label>' +
            '<input id="swal-nama" class="form-control" type="text" maxlength="'+maxLenNama+'" placeholder="Namamu disensor kok...">' +
            '<div class="small text-muted mt-1" id="swal-nama-count">0/'+maxLenNama+'</div>' +
          '</div>'+
          '<div class="form-group" style="text-align:left;">' +
            '<label class="small mb-1" for="swal-review">Review (opsional)</label>' +
            '<textarea id="swal-review" class="form-control" rows="3" placeholder="Tulis review singkat (boleh kosong)" style="resize:vertical;"></textarea>'+
            '<div class="small text-muted mt-1" id="swal-count">0/'+maxLenReview+'</div>' +
          '</div>',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Nanti',
        focusConfirm: false,
         // focusConfirm: false,
   focusCancel: false,
  focusDeny: false,
        didOpen: function(modal){
          var wrap = modal.querySelector('.swal-rate-wrap');
          var ta   = modal.querySelector('#swal-review');
          var cnt  = modal.querySelector('#swal-count');
          var inNm = modal.querySelector('#swal-nama');
          var cntN = modal.querySelector('#swal-nama-count');

          var hint = document.createElement('div');
          hint.className = 'small text-muted';
          hint.style.marginTop = '-.25rem';
          hint.style.textAlign = 'center';
          hint.id = 'swal-rate-hint';
          wrap.parentNode.insertBefore(hint, wrap.nextSibling);

          function render(){
            var stars = wrap.querySelectorAll('.rate-star');
            stars.forEach(function(el){
              var n = parseInt(el.getAttribute('data-n')||'0',10);
              el.className = 'mdi rate-star ' + (n<=selected ? 'mdi-star' : 'mdi-star-outline');
              el.style.color = (n<=selected ? '#f59e0b' : '');
            });
            hint.textContent = selected ? (selected+'/5') : 'Pilih 1–5 bintang';
          }
          wrap.addEventListener('click', function(e){
            var icon = e.target.closest('.rate-star'); if (!icon) return;
            selected = parseInt(icon.getAttribute('data-n')||'0',10);
            render();
          });

          ta.addEventListener('input', function(){
            if (this.value.length > maxLenReview) this.value = this.value.slice(0, maxLenReview);
            cnt.textContent = this.value.length + '/' + maxLenReview;
          });
          inNm.addEventListener('input', function(){
            if (this.value.length > maxLenNama) this.value = this.value.slice(0, maxLenNama);
            cntN.textContent = this.value.length + '/' + maxLenNama;
          });
             setTimeout(function(){
     if (document.activeElement && document.activeElement !== document.body) {
       try { document.activeElement.blur(); } catch(_){}
     }
   }, 0);

          // setTimeout(function(){ inNm && inNm.focus(); }, 0);
          cnt.textContent  = '0/'+maxLenReview;
          cntN.textContent = '0/'+maxLenNama;
          render();
        },
        preConfirm: function(){
          var ta   = document.getElementById('swal-review');
          var inNm = document.getElementById('swal-nama');
          var review = ta ? ta.value.trim() : '';
          var nama   = inNm ? inNm.value.trim() : '';
          if (!selected){
            Swal.showValidationMessage('Pilih minimal 1 bintang');
            return false;
          }
          return {stars:selected, review:review, nama:nama};
        },
        willClose: function(){ restoreFocusTrap(); }
      }).then(function(res){
        if (res.isConfirmed && res.value){
          submitRating(prodId, res.value.stars, res.value.review||'', res.value.nama||'');
        }
      });
    });
  }

  /* ====== Submit rating ====== */
  function submitRating(prodId, stars, review, nama){
    var boxes = document.querySelectorAll(
      '[data-rate-box][data-id="'+prodId+'"], [data-rate-box-detail][data-id="'+prodId+'"]'
    );

    postJSON('<?= site_url('produk/rate') ?>', { id: prodId, stars: stars, review: review, nama: nama })
      .then(function(res){
        if (!res || !res.success){
          if (window.Swal) Swal.fire('Gagal', (res&&res.pesan)||'Gagal menyimpan rating', 'error');
          else alert((res&&res.pesan)||'Gagal menyimpan rating');
          return;
        }

        // Update rata-rata & jumlah di kartu/grid/detail
        var avg = parseFloat(res.avg||0), count = parseInt(res.count||0,10);
        boxes.forEach(function(box){
          var card = box.closest('.modal-product') || box.closest('.product-box') || document;
          applyAvgToCard(card, avg, count);
        });

        // ⬇️ KUNCI PERBAIKAN: refresh daftar ulasan dari server,
        // bukannya menambah baris baru secara lokal.
        if (document.getElementById('rv-list')){
          document.dispatchEvent(new CustomEvent('reviews:refresh', { detail:{ produkId: prodId } }));
        }

        if (window.Swal){
          Swal.fire({ icon:'success', title:'Thankyou! ✨', text:'Rating & review tersimpan.', timer:1300, showConfirmButton:false });
        }
      })
      .catch(function(){
        if (window.Swal) Swal.fire('Gagal', 'Terjadi kesalahan jaringan.', 'error');
        else alert('Terjadi kesalahan jaringan.');
      });
  }

  // Klik bintang / link rate (GRID + DETAIL + MODAL)
  document.addEventListener('click', function(e){
    var el = e.target.closest('[data-rate-box] .star-meter, [data-rate-box] .rate-link, [data-rate-box-detail] .star-meter, [data-rate-box-detail] .rate-link');
    if (!el) return;
    var box  = el.closest('[data-rate-box],[data-rate-box-detail]');
    var id   = parseInt(box.getAttribute('data-id')||'0',10);
    var name = box.getAttribute('data-name') || 'Produk';
    if (!id) return;
    openRatingModal(id, name);
  }, {passive:true});
})();
</script>
