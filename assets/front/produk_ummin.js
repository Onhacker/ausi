/* === animasi ikon quickmenu on tap === */
(function(){
  const animClassMap={all:"qm-anim-pulse",food:"qm-anim-food",drink:"qm-anim-drink",cart:"qm-anim-cart"};
  function playAnim(iconEl){
    if(!iconEl)return;
    const type=iconEl.getAttribute("data-anim");
    const cls=animClassMap[type]||"qm-anim-pulse";
    iconEl.classList.remove(cls);
    void iconEl.offsetWidth;
    iconEl.classList.add(cls);
    iconEl.addEventListener("animationend",function handler(){
      iconEl.classList.remove(cls);
      iconEl.removeEventListener("animationend",handler);
    });
  }
  document.querySelectorAll(".quickmenu-item").forEach(function(item){
    item.addEventListener("click",function(e){
      let iconEl=item.querySelector("[data-anim]");
      if(!iconEl&&e.target&&e.target.closest("[data-anim]")){iconEl=e.target.closest("[data-anim]");}
      playAnim(iconEl);
    },{passive:true});
  });
})();

/* === FAB Cart: spinner feeling (listener #1) === */
(function(){
  var fab=document.getElementById("fab-cart");
  if(!fab)return;
  fab.addEventListener("click",function(e){
    if(fab.classList.contains("fab-loading")){
      e.preventDefault();return;
    }
    fab.classList.add("fab-loading");
    var spinEl=fab.querySelector(".spinner-border");
    var iconEl=fab.querySelector(".icon-default");
    if(spinEl)spinEl.classList.remove("d-none");
    if(iconEl)iconEl.classList.add("d-none");
    var lbl=fab.querySelector(".fab-label");
    if(lbl&&!lbl.dataset.orig){
      lbl.dataset.orig=lbl.textContent;
      lbl.textContent="Memuat…";
    }
  },{passive:true});
})();

/* === Core logic: filter, list produk, cart badge, subkategori, mode alert, dll === */
(function(){
    const CFG=window.AUSI_CFG||{};
  const SUB_API=((CFG.sub_api||"").replace(/\/?$/,"/"));

  const $grid=$("#grid-products");
  const $pagi=$("#pagination-wrap");
  const $cartCount=$("#cart-count");
  const $fabCount=$("#fab-count");

  // ===== Infinite scroll config =====
  const INFINITE_SCROLL = true;
  const INFINITE_ROOT_MARGIN = "0px 0px 450px 0px";

  let __hasMore = true;
  let __loadingMore = false;
  let __io = null;
  let __scrollFallbackBound = false;
  let __mainSeq = 0;
let __moreSeq = 0;

  // kalau infinite: sembunyikan pagination dari awal
  if (INFINITE_SCROLL) { $pagi.empty().hide(); }

 // sentinel
let $sentinel = $("#infinite-sentinel");
if (!$sentinel.length){
  $sentinel = $(`
    <div class="col-12">
    <div id="infinite-sentinel" class="col-12 text-center py-3">
      <div class="d-inline-flex align-items-center small text-muted">
        <span class="spinner-border spinner-border-sm mr-2 d-none" role="status"
              style="width:.95rem;height:.95rem;border-width:.15rem;border-right-color:transparent;"></span>
        <span class="sentinel-text">Scroll untuk muat lagi…</span>
      </div>
    </div></div>
  `);

  if ($pagi.length) $pagi.after($sentinel);
  else $grid.after($sentinel);
}

function sentinelLoading(on, txt){
  const $spin = $sentinel.find(".spinner-border");
  const $txt  = $sentinel.find(".sentinel-text");
  if (on){
    $spin.removeClass("d-none");
    $txt.text(txt || "Memuat…");
  } else {
    $spin.addClass("d-none");
    if (txt) $txt.text(txt);
  }
}


  function stopObserving(){
    if (__io) {
      try { __io.disconnect(); } catch(e){}
    }
    __io = null;
  }

  // --- anti-nyalip request + simpan page terakhir ---
  let __reqSeq  = 0;
  let __xhr     = null;
  let __lastPage = 1;
  let __mainLoading  = false;   // load utama (replace)
let __firstLoaded  = false;   // page 1 sudah masuk produk
let __loadTimer = null;
let __pendingMain = null;

function scheduleMainLoad(page=1, pushUrl=true){
  __pendingMain = { page, pushUrl };

  // ✅ kunci sejak dini (biar load-more gak sempat jalan)
  __mainLoading = true;
  __firstLoaded = false;
  stopObserving();
  clearMoreSkeleton();

  clearTimeout(__loadTimer);
  __loadTimer = setTimeout(function(){
    const req = __pendingMain;
    __pendingMain = null;
    loadProducts(req.page, req.pushUrl, { append:false });
  }, 180);
}


  (function(){
    function toast(txt, type){
      if (window.Swal) {
        Swal.fire({toast:true, position:'top', icon:(type||'info'), title:txt, showConfirmButton:false, timer:1800});
      }
    }
    window.addEventListener('offline', function(){ toast('Kamu offline. Cek koneksi ya.', 'warning'); });
    window.addEventListener('online',  function(){ toast('Kembali online ✨', 'success'); });
  })();


  /* HIDDEN inputs (pastikan ada) */
  if(!$("#sub_kategori").length){
    $("<input>",{type:"hidden",id:"sub_kategori",name:"sub_kategori",value:""}).appendTo("#filter-form");
  }
  if(!$("#recommended").length){
    $("<input>",{type:"hidden",id:"recommended",name:"recommended",value:"0"}).appendTo("#filter-form");
  }
  if(!$("#trend").length){
    $("<input>",{type:"hidden",id:"trend",name:"trend",value:""}).appendTo("#filter-form");
  }
  if(!$("#trend_days").length){
    $("<input>",{type:"hidden",id:"trend_days",name:"trend_days",value:""}).appendTo("#filter-form");
  }
  if(!$("#trend_min").length){
    $("<input>",{type:"hidden",id:"trend_min",name:"trend_min",value:""}).appendTo("#filter-form");
  }
    // ⬇️ Tambahan: filter tipe (paket / dll)
  if(!$("#tipe").length){
    $("<input>",{type:"hidden",id:"tipe",name:"tipe",value:""}).appendTo("#filter-form");
  }



  let $subWrap=$("#subcat-wrap");
  if(!$subWrap.length){
    $subWrap=$('<div id="subcat-wrap" class="mb-2" role="navigation" aria-label="Subkategori"></div>');
    $(".quickmenu-wrap").first().after($subWrap);
  }
  $subWrap.hide().empty();

  function buildSkeleton(n){
  let html="";
  for(let i=0;i<n;i++){
    html+=`
      <div class="col-6 col-md-3 js-skel-main">
        <div class="skel-card">
          <div class="skel-thumb skel-shimmer"></div>
          <div class="skel-line w80 skel-shimmer"></div>
          <div class="skel-line w60 skel-shimmer"></div>
          <div class="skel-price skel-shimmer"></div>
          <div class="skel-btn skel-shimmer"></div>
        </div>
      </div>`;
  }
  return html;
}

function clearMainSkeleton(){
  $grid.find(".js-skel-main").remove();
}

 function buildMoreSkeleton(n){
  let html = "";
  for (let i=0;i<n;i++){
    html += `
      <div class="col-6 col-md-3 js-skel-more" data-skel="more">
        <div class="skel-card">
          <div class="skel-thumb skel-shimmer"></div>
          <div class="skel-line w80 skel-shimmer"></div>
          <div class="skel-line w60 skel-shimmer"></div>
          <div class="skel-price skel-shimmer"></div>
          <div class="skel-btn skel-shimmer"></div>
        </div>
      </div>`;
  }
  return html;
}

  function showMoreSkeleton(n=8){
    $grid.append(buildMoreSkeleton(n));
  }
function clearMoreSkeleton(){
  // global remove (aman kalau DOM berubah / pindah parent)
  $('.js-skel-more, [data-skel="more"]').remove();
}



function loading(on=true){
  if(on){
    $grid.html(buildSkeleton(8));     // ✅ skeleton lama kamu
    clearMoreSkeleton();
    if (INFINITE_SCROLL) $pagi.empty().hide();
    else $pagi.empty().show();
  }
}


  function updateAllCartBadges(n){
    if($cartCount&&$cartCount.length)$cartCount.text(n);
    if($fabCount&&$fabCount.length)$fabCount.text(n);
  }
  function btnStartLoading($btn,loadingText){
    if(!$btn||!$btn.length)return;
    if($btn.hasClass("btn-loading"))return;
    $btn.addClass("btn-loading");
    $btn.find(".spinner-border").removeClass("d-none");
    $btn.find(".icon-default").addClass("d-none");
    const $txt=$btn.find(".btn-text");
    if($txt.length){
      if(!$txt.data("orig")){$txt.data("orig",$txt.text());}
      $txt.text(loadingText||"Menambah...");
    }
  }
  function btnStopLoading($btn){
    if(!$btn||!$btn.length)return;
    if(!$btn.hasClass("btn-loading"))return;
    $btn.find(".spinner-border").addClass("d-none");
    $btn.find(".icon-default").removeClass("d-none");
    const $txt=$btn.find(".btn-text");
    if($txt.length){
      const origText=$txt.data("orig");
      if(origText){$txt.text(origText);}
    }
    $btn.removeClass("btn-loading");
  }
  function safeQty(v){
    v=parseInt(v,10);
    return(isNaN(v)||v<1)?1:v;
  }
  function notifySuccess(produk, text){
    if (window.Swal){
      Swal.fire({
        title: produk,
        text:  text  || "",
        timer: 1500,
        showConfirmButton: false,
        iconHtml:
          '<div class="cart-anim-outer"><div class="cart-anim-wrapper">'+
            '<div class="drop-item drop-plate"></div>'+
            '<div class="drop-item drop-drink"></div>'+
            '<div class="cart-svg-wrap">'+
              '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48" fill="none" stroke="#dc3545" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">'+
                '<path d="M6 12h7.5a2 2 0 0 1 1.9 1.5l2.2 8.5" />'+
                '<path d="M17 22h22.5a2 2 0 0 1 1.9 2.6l-3 9a2 2 0 0 1-1.9 1.4H22.5a2 2 0 0 1-1.9-1.5L17 22Z" />'+
                '<path d="M20 26h18" />'+
                '<path d="M21.5 30h15" />'+
                '<circle class="cart-wheel-shape" cx="22" cy="38" r="3.5" />'+
                '<circle class="cart-wheel-shape" cx="36" cy="38" r="3.5" />'+
                '<path d="M28 14l8 -4" stroke="#dc3545" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'+
              '</svg>'+
            '</div>'+
          '</div></div>',
        customClass:{popup:'swal-cart-popup',icon:'swal-cart-icon'}
      });
    } else {
      alert((produk ? produk + ": " : "") + (text || ""));
    }
  }
  function notifyError(title,text){
    if(window.Swal){
      Swal.fire({icon:"error",title:title||"Gagal",text:text||""});
    }else{alert((title?title+": ":"")+(text||""));}
  }

  // ===== Tampilan error dengan tombol reload =====
  function renderErrorCard(message){
    const msg = message || "Koneksi bermasalah. Silakan coba lagi.";
    $grid.html(

      '<div class="col-12 mb-2">' +
        '<div class="alert alert-danger text-center">' +
          '<div class="mb-2">'+ msg +'</div>' +
          '<button type="button" class="btn btn-sm btn-blue js-reload-products">' +
            '<i class="mdi mdi-refresh mr-1"></i> Muat ulang' +
          '</button>' +
        '</div>' +
      '</div>'
    );
    $pagi.empty();
  }

  // baru: tandai #grandong di URL TANPA scroll
  function scrollToGrid(){
    const url = new URL(window.location.href);
    if (url.hash !== '#grandong') {
      url.hash = 'grandong';
      history.replaceState({}, "", url.toString());
    }
    const el = document.getElementById("grandong");
    if (el) {
      if (!el.hasAttribute("tabindex")) el.setAttribute("tabindex","-1");
      const x = window.scrollX, y = window.scrollY;
      try { el.focus({preventScroll:true}); } 
      catch(e){ el.focus(); window.scrollTo(x,y); }
    }
  }

  function scrollToGridProducts({offset=0, smooth=true} = {}){
    const el = document.getElementById("grandong");
    if(!el) return;

    const url = new URL(window.location.href);
    if (url.hash !== '#grandong') {
      url.hash = 'grandong';
      history.replaceState(history.state, "", url);
    }

    const y = el.getBoundingClientRect().top + window.pageYOffset - (offset||0);
    window.scrollTo({ top: Math.max(0, y), behavior: smooth ? "smooth" : "auto" });
  }


  /* ==== RECOMMENDED HELPERS ==== */
  function isRecOn(){ return ($("#recommended").val()==="1"); }
  function setRec(on){
    $("#recommended").val(on ? "1":"0");
    if(on){ // saat rec ON, kosongkan kategori & sub
      $("#kategori").val("");
      $("#sub_kategori").val("");
    }
  }
  function $recItem(){ // cari item quickmenu "Recomended"
    const $byAttr = $('#quickmenu .quickmenu-item[data-recommended="1"]');
    if($byAttr.length) return $byAttr.first();
    return $('#quickmenu .quickmenu-item').filter(function(){
      return ($(this).find('.menu-label').text().trim().toLowerCase()==='recomended');
    }).first();
  }

  function serializeFilters(page=1){
    const q=$("#q").val()||"";
    const kategori=$("#kategori").val()||"";
    const sub_kategori=$("#sub_kategori").val()||"";
    const sort=$("#sort").val()||"random";
    const recommended=isRecOn()?1:0;
    const per_page=12;

    // trending
    const trend=$("#trend").val()||"";
    const trend_days=$("#trend_days").val()||"";
    const trend_min=$("#trend_min").val()||"";
     const tipe=$("#tipe").val()||"";
    // seed utk random
    const url=new URL(window.location.href);
    let seed=url.searchParams.get("seed");
    if(!seed&&sort==="random"){
      seed=String(Math.random()*1e9|0);
      url.searchParams.set("seed",seed);
      history.replaceState({},"",url.toString());
    }
    return{q,kategori,sub_kategori,sort,page,per_page,seed,recommended,trend,trend_days,trend_min,tipe};
  }


 function loadProducts(page=1, pushUrl=true, opts){
 opts = opts || {};
const append = !!opts.append;

if (append) pushUrl = false;

// ✅ blokir load-more kalau load utama belum selesai / belum pernah sukses
if (append && (!__firstLoaded || __mainLoading)) return;

if (!append) {
  __mainLoading = true;
  loading(true);          // ✅ skeleton lama kamu utk load utama
  __hasMore = true;
  if (INFINITE_SCROLL) $pagi.empty().hide();
} else {
  if (__loadingMore) return;
  __loadingMore = true;

  // ✅ skeleton load-more (yang kamu buat)
  showMoreSkeleton(8);
}



  const params = serializeFilters(page);
  params.rec = params.recommended ? 1 : 0;
  params._   = Date.now();

  // abort request sebelumnya (aman, tapi kalau append sedang jalan ya kita biarkan)
  if (!append && __xhr && __xhr.readyState !== 4) {
    try{ __xhr.abort(); }catch(e){}
  }

  const mySeq = ++__reqSeq;
  if (!append) __mainSeq = mySeq;
else __moreSeq = mySeq;

  __xhr = $.ajax({
    url: CFG.list_ajax,
    method: "GET",
    dataType: "json",
    data: params,
    timeout: 12000
  })
 .done(function(r){

  // ✅ 0) Anti-nyalip: response lama langsung dibuang
  if (mySeq !== __reqSeq) return;

  // ✅ 1) Bersihkan skeleton yg relevan utk request ini
  // - load-more: hapus placeholder load-more
  // - main: gak wajib clear skeleton, karena akan di-replace oleh $grid.html(...)
  clearMoreSkeleton();

  // ✅ 2) Validasi response
  if (!r || !r.success) {
    const msg = (r && (r.pesan || r.message)) ? (r.pesan || r.message) : "Gagal memuat data.";
    if (!append) renderErrorCard(msg);
    else sentinelLoading(false, "Gagal memuat. Klik untuk coba lagi.");
    return;
  }

  // ✅ 3) Render produk
  __lastPage = r.page || page || 1;

  if (append) {
    $grid.append(r.items_html);
  } else {
    $grid.html(r.items_html);    // ✅ ini otomatis menghapus skeleton main
    __firstLoaded = true;
  }

  // ✅ 4) Pagination (kalau infinite aktif: hide)
  if (INFINITE_SCROLL) $pagi.empty().hide();
  else $pagi.html(r.pagination_html || "").show();

  // ✅ 5) Deteksi next page (tanpa ubah server)
  let hasNext = false;
  try {
    const nextPage = (__lastPage || 1) + 1;
    const $tmp = $("<div/>").html(r.pagination_html || "");
    hasNext = $tmp.find('a[data-page="' + nextPage + '"]').length > 0;
  } catch(e){}
  __hasMore = hasNext;

  // ✅ 6) UI sentinel
  if (INFINITE_SCROLL) {
    if (__hasMore) sentinelLoading(false, "Scroll untuk muat lagi…");
    else { sentinelLoading(false, "✅ Semua produk sudah tampil"); stopObserving(); }
  }

  // ✅ 7) Update URL (tetap pakai blok punyamu)
  if (pushUrl){
    const url = new URL(window.location.href);
    url.searchParams.set("q", params.q);
    url.searchParams.set("kategori", params.kategori);
    url.searchParams.set("sub", params.sub_kategori);
    url.searchParams.set("sort", params.sort);
    url.searchParams.set("page", __lastPage);
    url.searchParams.set("seed", params.seed);

    if (params.recommended) url.searchParams.set("rec","1");
    else url.searchParams.delete("rec");

    if (params.trend) url.searchParams.set("trend", params.trend);
    else url.searchParams.delete("trend");

    if (params.trend_days) url.searchParams.set("trend_days", params.trend_days);
    else url.searchParams.delete("trend_days");

    if (params.trend_min) url.searchParams.set("trend_min", params.trend_min);
    else url.searchParams.delete("trend_min");

    if (params.tipe) url.searchParams.set("tipe", params.tipe);
    else url.searchParams.delete("tipe");

    history.pushState(params, "", url.toString());
  }

  // ✅ 8) Rebind
  bindAddToCart();
  if (INFINITE_SCROLL) setupInfiniteScroll();
  else bindPagination();

  if (typeof bindDetailModal === "function") bindDetailModal();
})


 .fail(function(jq, text, err){

  // ✅ jangan tampilkan error kalau request dibatalkan / cancel / status=0
  if (text === "abort" || text === "canceled" || (jq && jq.status === 0)) return;
  if (mySeq !== __reqSeq) return;

  // ✅ opsional: kalau ini bukan request terbaru, abaikan errornya
  if (!append && mySeq !== __mainSeq) return;
  if (append  && mySeq !== __moreSeq) return;

  const statusPart = jq && jq.status ? ` (HTTP ${jq.status})` : "";
  const errPart    = err ? `: ${err}` : "";
  const msg        = `Koneksi bermasalah${statusPart}${errPart}.`;

  if (append) {
    sentinelLoading(false, "Gagal memuat. Klik untuk coba lagi.");
    $sentinel.off("click.retry").on("click.retry", function(){
      $sentinel.off("click.retry");
      loadProducts(__lastPage + 1, false, {append:true});
    });
  } else {
    renderErrorCard(msg);
  }
})

.always(function(){
  $grid.attr("aria-busy","false");

  clearMoreSkeleton();

  // ✅ hanya request TERAKHIR yang boleh reset flag
  if (!append && mySeq === __mainSeq) __mainLoading = false;
  if (append && mySeq === __moreSeq) __loadingMore = false;

  if (INFINITE_SCROLL) sentinelLoading(false);
});



  $grid.attr("aria-busy","true");
}

  // Tombol "Muat ulang" saat error
  $(document).on('click', '.js-reload-products', function(e){
    e.preventDefault();
    const page = __lastPage || 1;
    loadProducts(page, false);
  });


  function bindPagination(){
    $("#pagination-wrap").off("click","a[data-page]").on("click","a[data-page]",function(e){
      e.preventDefault();
      const p=parseInt($(this).data("page")||1,10);
      loadProducts(p);
      const sticky = document.querySelector(".navbar-fixed-top,.sticky-top,.app-header");
      scrollToGridProducts({ offset: sticky ? sticky.offsetHeight : 0, smooth: true });
    });
  }

  (function(){
    const FAB_TOOLTIP_KEY = 'fabCartTooltipDismissed';

    // Dipanggil SETELAH sukses insert ke cart
    window.showFabCartTooltip = function(){
      try {
        if (localStorage.getItem(FAB_TOOLTIP_KEY) === '1') {
          return; // sudah pernah "OK, saya paham"
        }
      } catch (e) {
        // kalau localStorage error, lanjut aja
      }

      var tip = document.getElementById('fab-cart-tooltip');
      if (!tip) return;
      tip.classList.add('show');
    };

    // Klik tombol OK
    document.addEventListener('click', function(e){
      var btn = e.target.closest && e.target.closest('#fab-cart-tooltip-ok');
      if (!btn) return;

      var tip = document.getElementById('fab-cart-tooltip');
      if (tip) tip.classList.remove('show');

      try {
        localStorage.setItem(FAB_TOOLTIP_KEY, '1');
      } catch (err) {}
    });
  })();

  function bindAddToCart(){
    $("#grid-products").off("click",".btn-add-cart").on("click",".btn-add-cart",function(e){
      e.preventDefault();
      const $btn=$(this);
      if($btn.hasClass("btn-loading"))return;
      if($btn.is(":disabled"))return;
      const id=$btn.data("id");
      const qty=safeQty($btn.data("qty"));

      btnStartLoading($btn,"Menambah...");

      $.ajax({
        url:CFG.add_to_cart,
        type:"POST",
        dataType:"json",
        data:{id,qty}
      })
      .done(function(r){
        if(!r||!r.success){
          notifyError(r?.produk||"Oops!",r?.pesan||"Gagal menambahkan");
          return;
        }
        showFabCartTooltip();
        updateAllCartBadges(r.count);
        notifySuccess(r.produk||"Mantap!",r.pesan||"Item masuk keranjang");
      })
      .fail(function(){
        notifyError("Error","Gagal terhubung ke server");
      })
      .always(function(){
        btnStopLoading($btn);
      });
    });
  }
  function setupInfiniteScroll(){
  if (!INFINITE_SCROLL) return;

  stopObserving();

  // fallback scroll (bind sekali saja)
  if (!("IntersectionObserver" in window)) {
    if (__scrollFallbackBound) return;
    __scrollFallbackBound = true;

    window.addEventListener("scroll", function(){
      if (__loadingMore || !__hasMore) return;
      const nearBottom = (window.innerHeight + window.pageYOffset) >= (document.body.offsetHeight - 600);
      if (nearBottom) loadProducts(__lastPage + 1, false, {append:true});
    }, {passive:true});
    return;
  }

  __io = new IntersectionObserver(function(entries){
    if (!entries || !entries.length) return;
    if (!entries[0].isIntersecting) return;
    if (__loadingMore || !__hasMore) return;
    loadProducts(__lastPage + 1, false, {append:true});
  }, { root: null, rootMargin: INFINITE_ROOT_MARGIN, threshold: 0 });

  if ($sentinel[0]) __io.observe($sentinel[0]);
}


  function loadCartCount(){
    $.getJSON(CFG.cart_count).done(function(r){
      if(r&&r.success){updateAllCartBadges(r.count);}
    });
  }

  function setSortLabel(val){
    const map={random:"Untukmu","new":"Terbaru",bestseller:"Terlaris",price_low:"Harga Rendah",price_high:"Harga Tinggi",sold_out:"Habis",trending:"Favorit"};
    let txt=map[val]||"Urutkan";

    if(val==="trending"){
      const t=($("#trend").val()||"").toLowerCase();
      const d=parseInt($("#trend_days").val()||"0",10);
      if(t==="today"||d===1) txt="Favorit (Hari ini)";
      else if(t==="week"||d===7) txt="Favorit (7 hari)";
      else if(t==="month"||d===30) txt="Favorit (30 hari)";
    }
    $("#sortBtnLabel").text(txt);
  }


 function markActiveKategori(){
  const rec      = ($("#recommended").val() === "1");
  const kategori = String($("#kategori").val() || "");
  const tipe     = String($("#tipe").val() || "");

  const $items = $("#quickmenu .quickmenu-item").not('[data-action="cart"]');
  $items.removeClass("active");

  // 1) Kalau Andalang / Recommended
  if (rec) {
    $('#quickmenu .quickmenu-item[data-recommended="1"]').addClass("active");
    return;
  }

  // 2) Kalau filter tipe (misal: Paket Hemat)
  if (tipe !== "") {
    $('#quickmenu .quickmenu-item[data-tipe="'+tipe+'"]').addClass("active");
    return;
  }

  // 3) Kalau kategori biasa
  $('#quickmenu .quickmenu-item[data-kategori]').filter(function(){
    return String(this.getAttribute('data-kategori')) === kategori;
  }).addClass("active");

  // 4) Fallback: kalau kategori kosong & belum ada yang aktif → pilih "Semua"
  if (!kategori && !$("#quickmenu .quickmenu-item.active").length) {
    $('#quickmenu .quickmenu-item[data-kategori=""]').addClass("active");
  }
}


  function hideSubcats(){
    $subWrap.hide().empty();
  }

  function markActiveSub(subId){
    const sid=String(subId||"");
    $subWrap.find(".subcat-badge").removeClass("badge-dark text-white active").addClass("badge-blue");
    if(sid===""){
      $subWrap.find('.subcat-badge[data-sub=""]').removeClass("badge-blue").addClass("badge-dark text-white active");
    }else{
      $subWrap.find('.subcat-badge[data-sub="'+sid+'"]').removeClass("badge-blue").addClass("badge-dark text-white active");
    }
  }

  function renderSubBadges(list,selectedId){
    let html=`<a href="#" class="badge badge-pill subcat-badge badge-dark text-white mr-1" data-sub="">Semua</a>`;
    (list||[]).forEach(it=>{
      html+=`<a href="#" class="badge badge-pill subcat-badge badge-blue mr-1" data-sub="${it.id}">${it.nama}</a>`;
    });
    $subWrap.html(html).show();
    markActiveSub(selectedId);
  }

  function fetchAndRenderSubcats(kategoriId){
    $subWrap.html('<div class="d-inline-flex align-items-center rounded px-2 py-1 bg-light border small text-muted" style="line-height:1.2;"><span class="spinner-border spinner-border-sm mr-2" role="status" style="width:0.9rem;height:0.9rem;border-width:0.15rem;border-right-color:transparent;"></span><span>Memuat subkategori…</span></div>').show();

    $.getJSON(SUB_API+String(kategoriId))
    .done(function(r){
      const currentSelected=$("#sub_kategori").val()||"";
      if(r&&r.success&&Array.isArray(r.data)&&r.data.length){
        renderSubBadges(r.data,currentSelected);
      }else{
        hideSubcats();
      }
    })
    .fail(function(){
      hideSubcats();
    });
  }

  /* input search (debounce) */
  let typingTimer=null;
  $("#q").on("input",function(){
    clearTimeout(typingTimer);
    typingTimer=setTimeout(function(){scheduleMainLoad(1);
},350);
  }).on("keydown",function(e){
    if(e.key==="Enter"){
      e.preventDefault();
      clearTimeout(typingTimer);
      scheduleMainLoad(1);

    }
  });

  $(document).on("click","#btn-search",function(e){
    e.preventDefault();
    scheduleMainLoad(1);

  });

  $(document).on("click","#btn-reset",function(e){
    e.preventDefault();
    $("#q").val("");
    $("#kategori").val("");
    $("#sub_kategori").val("");
    $("#recommended").val("0"); // RESET rec
    $("#sort").val("random");
    $("#trend").val("");
    $("#trend_days").val("");
    $("#trend_min").val("");
     $("#tipe").val("");
    setSortLabel("random");
    markActiveKategori();

    const url=new URL(window.location.href);
    url.searchParams.delete("seed");
    url.searchParams.delete("sub");
    url.searchParams.delete("rec");
    history.replaceState({},"",url.toString());

    hideSubcats();
    scheduleMainLoad(1);

  });

  $(document).on("click",".sort-opt",function(e){
    e.preventDefault();
    const val=$(this).data("sort");
    $("#sort").val(val);

    $("#trend").val("");
    $("#trend_days").val("");
    $("#trend_min").val("");

    if(val==="trending"){
      const preset=String($(this).data("trend")||"week");
      $("#trend").val(preset);
      if(preset==="today"){ $("#trend_days").val("1"); }
      else if(preset==="week"){ $("#trend_days").val("7"); }
      else if(preset==="month"){ $("#trend_days").val("30"); }
      $("#trend_min").val("1.0");
    }

    setSortLabel(val);
    if(val==="random"){
      const url=new URL(window.location.href);
      url.searchParams.delete("seed");
      history.replaceState({},"",url.toString());
    }
    scheduleMainLoad(1);

  });


  /* === Quickmenu click: dukung Recomended === */
  $("#quickmenu").on("click",".quickmenu-item",function(e){
  if ($(this).data("action")==="cart") return;
  e.preventDefault();

  const $item   = $(this);
  const isRec   = $item.data("recommended")==="1" || $item.data("recommended")==1;
  const tipeBtn = ($item.data("tipe") || "").toString();

  // === Andalang / Recommended ===
  if (isRec){
    setRec(true);
    $("#tipe").val("");            // jangan pakai tipe saat recommended
    $("#kategori").val("");
    $("#sub_kategori").val("");

    $("#trend").val("");
    $("#trend_days").val("");
    $("#trend_min").val("");

    setSortLabel($("#sort").val() || "random");
    hideSubcats();
    markActiveKategori();

    const url = new URL(window.location.href);
    url.searchParams.delete("trend");
    url.searchParams.delete("trend_days");
    url.searchParams.delete("trend_min");
    url.searchParams.delete("tipe");
    url.searchParams.delete("kategori");
    url.searchParams.delete("sub");
    history.replaceState({}, "", url.toString());

    // scheduleMainLoad(1);

    scheduleMainLoad(1);

    scrollToGrid();
    return;
  }

  // === Paket Hemat (punya data-tipe) ===
  if (tipeBtn !== "") {
    setRec(false);
    $("#tipe").val(tipeBtn);   // biasanya "paket"
    $("#kategori").val("");
    $("#sub_kategori").val("");
    hideSubcats();
    markActiveKategori();
    scheduleMainLoad(1);

    scrollToGrid();
    return;
  }

  // === Kategori biasa ===
  setRec(false);
  $("#tipe").val("");          // reset tipe kalau pilih kategori
  const kat = String($item.data("kategori") || "");
  $("#kategori").val(kat);
  $("#sub_kategori").val("");
  markActiveKategori();
  scheduleMainLoad(1);

  if (kat){ fetchAndRenderSubcats(kat); } else { hideSubcats(); }
  scrollToGrid();
});



  $(document).on("click",".subcat-badge",function(e){
    e.preventDefault();
    const sid=String($(this).data("sub")||"");
    $("#sub_kategori").val(sid);
    markActiveSub(sid);
    scheduleMainLoad(1);

    scrollToGrid();
  });

  /* FAB Cart listener duplikasi (listener #2 tetap dipertahankan) */
  (function(){
     var fab=document.getElementById("fab-cart");
     if(!fab)return;
     fab.addEventListener("click",function(e){
       if(fab.classList.contains("fab-loading")){
         e.preventDefault();return;
       }
       fab.classList.add("fab-loading");
       var spinEl=fab.querySelector(".spinner-border");
       var iconEl=fab.querySelector(".icon-default");
       if(spinEl)spinEl.classList.remove("d-none");
       if(iconEl)iconEl.classList.add("d-none");
       var lbl=fab.querySelector(".fab-label");
       if(lbl&&!lbl.dataset.orig){
         lbl.dataset.orig=lbl.textContent;
         lbl.textContent="Memuat…";
       }
     },{passive:true});
  })();

  $(document).on("click",".js-leave-table",function(e){
    e.preventDefault();
    const url=this.href;
    if(window.Swal){
      Swal.fire({
        icon:"warning",
        title:"Keluar dari Meja?",
        html:'Santai, kamu bisa lanjut belanja dari rumah — pesanan bisa kami <b>antar</b> (Delivery) atau <b>dibungkus</b> (Takeaway). 😉<br><br><small style="display:inline-block;margin-top:.25rem;color:#6b7280">Kalau masih mau makan di tempat, <b>scan ulang barcode di meja</b> ya. 🍽️📱</small>',
        showCancelButton:true,
        confirmButtonText:"Iya, keluar",
        cancelButtonText:"Batal",
        reverseButtons:true,
        focusCancel:true
      }).then((res)=>{
        if(res.isConfirmed){
          Swal.fire({icon:"success",title:"Keluar dari Dine-in",text:"Mode diubah. Lanjut belanja sebagai Delivery/Takeaway. 🙌",timer:900,showConfirmButton:false});
          setTimeout(()=>{window.location.href=url;},300);
        }
      });
    }else{
      if(confirm("Keluar dari mode Dine-in? Kalau masih mau makan di tempat, scan ulang barcode di meja ya.")){
        window.location.href=url;
      }
    }
  });

  $(document).off("click","#btn-add-cart-modal").on("click","#btn-add-cart-modal",function(e){
    e.preventDefault();

    const $btn = $(this);
    if ($btn.hasClass("btn-loading") || $btn.is(":disabled")) return;

    const id  = $btn.data("id");
    const qty = safeQty($("#qty-modal").val());

    btnStartLoading($btn,"Menambah...");

    $.ajax({
      url: CFG.add_to_cart,
      type: "POST",
      dataType: "json",
      data: { id, qty }
    })
    .done(function(r){
      if(!r || !r.success){
        notifyError(r?.title||"Oops!", r?.pesan||"Gagal menambahkan");
        return;
      }
      const n = r.count || 0;
      if($("#fab-count").length)   { $("#fab-count").text(n); }
      if($("#cart-count").length)  { $("#cart-count").text(n); }

      const $modal = $("#modalProduk");
      if($modal.length){
        $modal.one("hidden.bs.modal", function(){
          notifySuccess(r.produk||"Mantap!", r.pesan||"Item masuk keranjang");
        });
        $modal.modal("hide");
      }else{
        notifySuccess(r.produk||"Mantap!", r.pesan||"Item masuk keranjang");
      }
    })
    .fail(function(){
      notifyError("Error","Gagal terhubung ke server");
    })
    .always(function(){
      btnStopLoading($btn);
    });
  });

  /* ===== Initial load ===== */
  $(function(){
    loadCartCount();
    $("#dropdownSortBtn").dropdown();

    const url=new URL(window.location.href);
    if(url.searchParams.has("q"))   $("#q").val(url.searchParams.get("q"));
    if(url.searchParams.has("kategori")) $("#kategori").val(url.searchParams.get("kategori"));
    if(url.searchParams.has("sub")) $("#sub_kategori").val(url.searchParams.get("sub"));
    if(url.searchParams.has("sort"))$("#sort").val(url.searchParams.get("sort"));
    if(url.searchParams.has("trend"))      $("#trend").val(url.searchParams.get("trend"));
    if(url.searchParams.has("trend_days")) $("#trend_days").val(url.searchParams.get("trend_days"));
    if(url.searchParams.has("trend_min"))  $("#trend_min").val(url.searchParams.get("trend_min"));
        if(url.searchParams.has("tipe")) $("#tipe").val(url.searchParams.get("tipe"));
            if($("#tipe").val()==="paket"){
      $("#kategori").val("");
      $("#recommended").val("0");
    }

    if(url.searchParams.get("rec")==="1" || url.searchParams.get("recommended")==="1"){
      setRec(true);
    }else{
      setRec(false);
    }

    setSortLabel($("#sort").val()||"random");
    markActiveKategori();

       const katInit  = $("#kategori").val();
    const tipeInit = $("#tipe").val();

    if (isRecOn() || tipeInit === "paket") {
      hideSubcats();
    }else{
      if(katInit){fetchAndRenderSubcats(katInit);}else{hideSubcats();}
    }


    const firstPage=parseInt(url.searchParams.get("page")||"1",10);
    __lastPage = firstPage || 1;
    loadProducts(firstPage,false);
        // setupInfiniteScroll();

    const $modeInfo=$("#mode-info");
    const curModeRaw=($modeInfo.data("mode")||"").toString().toLowerCase();
    const mejaLabel=($modeInfo.data("meja")||"").toString();

    let modeNice="";
    if(curModeRaw==="dinein"||curModeRaw==="dine-in"){
      modeNice=(mejaLabel!==""?"Dine-in di "+mejaLabel:"Dine-in");
    }else if(curModeRaw==="delivery"){
      modeNice="Delivery";
    }else if(curModeRaw==="walkin"){
      modeNice="Takeaway/Bungkus";
    }else{
      modeNice="Belanja biasa";
    }

    let htmlMsg="";
    if(curModeRaw==="dinein"||curModeRaw==="dine-in"){
      htmlMsg=`Kamu saat ini <b>${modeNice}</b> 👋<br>Pesanan akan dicatat ke meja kamu.<br><br><small style="color:#6b7280;display:inline-block;margin-top:.25rem;">Mau pindah jadi Delivery / Takeaway? Pakai tombol keluar di atas (ikon keluar meja).</small>`;
    }else if(curModeRaw==="delivery"){
      htmlMsg=`Kamu saat ini mode <b>${modeNice}</b> 🚚<br>Kami bisa antar pesananmu ke alamat kamu.`;
    }else if(curModeRaw==="walkin"){
      htmlMsg=`Kamu saat ini mode <b>${modeNice}</b> 👜<br>Pesananmu akan dibungkus untuk diambil.`;
    }else{
      htmlMsg=`Kamu belanja sebagai <b>${modeNice}</b> 🛍️`;
    }

    let lastShown="";
    try{lastShown=localStorage.getItem("lastModeShown")||"";}catch(e){}
    if(typeof window.__MODE_ALERT_SHOWN==="undefined"){window.__MODE_ALERT_SHOWN=false;}
    const shouldShowAlert=(!window.__MODE_ALERT_SHOWN&&curModeRaw!==lastShown);

    if(shouldShowAlert&&window.Swal){
      Swal.fire({icon:"info",title:modeNice,html:htmlMsg,confirmButtonText:"Oke",width:320});
      window.__MODE_ALERT_SHOWN=true;
      try{localStorage.setItem("lastModeShown",curModeRaw);}catch(e){}
    }
  });

  /* ===== history back/forward ===== */
  window.addEventListener("popstate",function(e){
    const s=e.state||{};
    $("#q").val(s.q||"");
    $("#kategori").val(s.kategori||"");
    $("#sub_kategori").val(s.sub_kategori||"");
    $("#sort").val(s.sort||"random");
    $("#recommended").val(s.recommended? "1":"0");
    $("#trend").val(s.trend||"");
    $("#trend_days").val(s.trend_days||"");
    $("#trend_min").val(s.trend_min||"");
    $("#tipe").val(s.tipe || "");
    setSortLabel($("#sort").val());
    markActiveKategori();

    if(s.recommended){
      hideSubcats();
    }else{
      if(s.kategori){fetchAndRenderSubcats(s.kategori);}else{hideSubcats();}
    }

    const p = parseInt(s.page||1,10);
    __lastPage = p || 1;
    loadProducts(p,false);
  });
})();

/* === Scroll hint quickmenu (fade kiri/kanan + nudge awal) === */
(function(){
  const q=document.getElementById("quickmenu");
  if(!q)return;
  const wrap=q.closest(".quickmenu-wrap");
  function updateQuickmenuShadows(){
    const maxScroll=q.scrollWidth-q.clientWidth;
    const x=Math.round(q.scrollLeft);
    wrap.classList.toggle("show-left",x>0);
    wrap.classList.toggle("show-right",x<(maxScroll-1));
  }
  q.addEventListener("scroll",updateQuickmenuShadows,{passive:true});
  window.addEventListener("resize",updateQuickmenuShadows);
  document.addEventListener("DOMContentLoaded",updateQuickmenuShadows);
  setTimeout(updateQuickmenuShadows,600);
  let nudged=false;
  setTimeout(function(){
    const maxScroll=q.scrollWidth-q.clientWidth;
    if(maxScroll>8&&!nudged){
      nudged=true;
      q.scrollBy({left:48,behavior:"smooth"});
      setTimeout(()=>q.scrollBy({left:-48,behavior:"smooth"}),350);
    }
  },800);
})();

/* === killer masker legacy === */
window.killMasks=function(){
  $(".window-mask, .messager-mask, .datagrid-mask, .easyui-mask, .mm-wrapper__blocker")
    .css("pointer-events","none").hide();
};
