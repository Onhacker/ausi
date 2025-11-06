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


  let $subWrap=$("#subcat-wrap");
  if(!$subWrap.length){
    $subWrap=$('<div id="subcat-wrap" class="mb-2" role="navigation" aria-label="Subkategori"></div>');
    $(".quickmenu-wrap").first().after($subWrap);
  }
  $subWrap.hide().empty();

  function buildSkeleton(n){
    let html="";
    for(let i=0;i<n;i++){
      html+=`<div class="col-6 col-md-3 mb-3"><div class="skel-card"><div class="skel-thumb skel-shimmer"></div><div class="skel-line w80 skel-shimmer"></div><div class="skel-line w60 skel-shimmer"></div><div class="skel-price skel-shimmer"></div><div class="skel-btn skel-shimmer"></div></div></div>`;
    }
    return html;
  }
  function loading(on=true){
    if(on){$grid.html(buildSkeleton(8));$pagi.html("");}
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
  // baru: tandai #grandong di URL TANPA scroll

function scrollToGrid(){
  const url = new URL(window.location.href);
  if (url.hash !== '#grandong') {
    url.hash = 'grandong';
    history.replaceState(history.state, "", url.toString()); // update hash tanpa gerakin layar
  }
  // opsional: kasih fokus tanpa scroll (diamankan untuk Safari lawas)
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

  // update hash ke #grandong tanpa loncatan native
  const url = new URL(window.location.href);
  if (url.hash !== '#grandong') {
    url.hash = 'grandong';
    history.replaceState(history.state, "", url);
  }

  // scroll dengan kompensasi sticky header (jika ada)
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
    // fallback by label text (kalau lupa pasang data-recommended)
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

  // seed utk random
  const url=new URL(window.location.href);
  let seed=url.searchParams.get("seed");
  if(!seed&&sort==="random"){
    seed=String(Math.random()*1e9|0);
    url.searchParams.set("seed",seed);
    history.replaceState({},"",url.toString());
  }
  return{q,kategori,sub_kategori,sort,page,per_page,seed,recommended,trend,trend_days,trend_min};
}


  function loadProducts(page=1,pushUrl=true){
    loading(true);
    const params=serializeFilters(page);
      // kompat backend lama yang baca ?rec=1
params.rec = params.recommended ? 1 : 0;
// optional: bust cache
params._ = Date.now();

    $.getJSON(CFG.list_ajax,params)
    .done(function(r){
      if(!r||!r.success){
        $grid.html('<div class="col-12 alert alert-danger">Gagal memuat data.</div>');
        return;
      }
      $grid.html(r.items_html);
      $pagi.html(r.pagination_html);

  if(pushUrl){
  const url=new URL(window.location.href);
  url.searchParams.set("q",params.q);
  url.searchParams.set("kategori",params.kategori);
  url.searchParams.set("sub",params.sub_kategori);
  url.searchParams.set("sort",params.sort);
  url.searchParams.set("page",r.page);
  url.searchParams.set("seed",params.seed);
  if(params.recommended){ url.searchParams.set("rec","1"); } else { url.searchParams.delete("rec"); }

  // ⬇️ taruh di sini
  if(params.trend){ url.searchParams.set("trend", params.trend); } else { url.searchParams.delete("trend"); }
  if(params.trend_days){ url.searchParams.set("trend_days", params.trend_days); } else { url.searchParams.delete("trend_days"); }
  if(params.trend_min){ url.searchParams.set("trend_min", params.trend_min); } else { url.searchParams.delete("trend_min"); }

  history.pushState(params,"",url.toString());
}



      bindAddToCart();
      bindPagination();
      if (typeof bindDetailModal === "function") { bindDetailModal(); }
    })
    .fail(function(){
      $grid.html('<div class="col-12 alert alert-danger">Koneksi bermasalah.</div>');
    });
  }

  function bindPagination(){
    $("#pagination-wrap").off("click","a[data-page]").on("click","a[data-page]",function(e){
      e.preventDefault();
      const p=parseInt($(this).data("page")||1,10);
      loadProducts(p);
        const sticky = document.querySelector(".navbar-fixed-top,.sticky-top,.app-header");

        scrollToGridProducts({ offset: sticky ? sticky.offsetHeight : 0, smooth: true });

    });
  }

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
  const rec = ($("#recommended").val() === "1");
  const val = String($("#kategori").val() || "");

  const $all = $("#quickmenu .quickmenu-item").not('[data-action="cart"]');
  $all.removeClass("active");

  if (rec) {
    $('#quickmenu .quickmenu-item[data-recommended="1"]').addClass("active");
    return;
  }

  // HANYA item yang benar-benar punya atribut data-kategori
  $('#quickmenu .quickmenu-item[data-kategori]').filter(function(){
    return String(this.getAttribute('data-kategori')) === val;
  }).addClass("active");
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
    typingTimer=setTimeout(function(){loadProducts(1);},350);
  }).on("keydown",function(e){
    if(e.key==="Enter"){
      e.preventDefault();
      clearTimeout(typingTimer);
      loadProducts(1);
    }
  });

  $(document).on("click","#btn-search",function(e){
    e.preventDefault();
    loadProducts(1);
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

    setSortLabel("random");
    markActiveKategori();

    const url=new URL(window.location.href);
    url.searchParams.delete("seed");
    url.searchParams.delete("sub");
    url.searchParams.delete("rec"); // hapus rec di URL
    history.replaceState({},"",url.toString());

    hideSubcats();
    loadProducts(1);
  });

 $(document).on("click",".sort-opt",function(e){
  e.preventDefault();
  const val=$(this).data("sort");
  $("#sort").val(val);

  // default: kosongkan trend
  $("#trend").val("");
  $("#trend_days").val("");
  $("#trend_min").val("");

  // kalau trending, set preset dari data-trend
  if(val==="trending"){
    const preset=String($(this).data("trend")||"week"); // default 7 hari
    $("#trend").val(preset);
    if(preset==="today"){ $("#trend_days").val("1"); }
    else if(preset==="week"){ $("#trend_days").val("7"); }
    else if(preset==="month"){ $("#trend_days").val("30"); }
    $("#trend_min").val("1.0"); // default skor minimal
  }

  setSortLabel(val);
  if(val==="random"){
    const url=new URL(window.location.href);
    url.searchParams.delete("seed");
    history.replaceState({},"",url.toString());
  }
  loadProducts(1);
});


  /* === Quickmenu click: dukung Recomended === */
  $("#quickmenu").on("click",".quickmenu-item",function(e){
    if($(this).data("action")==="cart")return;
    e.preventDefault();

    // DETEKSI tombol 'Recomended'
    const isRecBtn = ($(this).data("recommended")==="1" || $(this).data("recommended")==1) ||
                     ($(this).find('.menu-label').text().trim().toLowerCase()==='recomended');

// === Quickmenu click: dukung Recommended (Andalang) TANPA trending ===
if (isRecBtn){
  setRec(true);

  // kalau sebelumnya user lagi di 'trending', kembalikan ke 'random'
  if (($("#sort").val() || "") === "trending") {
    $("#sort").val("random");
  }

  // kosongkan semua parameter trending
  $("#trend").val("");
  $("#trend_days").val("");
  $("#trend_min").val("");

  setSortLabel($("#sort").val() || "random");
  markActiveKategori();
  hideSubcats();

  // bersihkan param trending di URL saat ini (non-push)
  const url = new URL(window.location.href);
  url.searchParams.delete("trend");
  url.searchParams.delete("trend_days");
  url.searchParams.delete("trend_min");
  history.replaceState({}, "", url.toString());

  loadProducts(1);
  scrollToGrid();
  return;
}


    // kategori biasa → matikan rec
    setRec(false);
    const kat=String($(this).data("kategori")||"");
    $("#kategori").val(kat);
    $("#sub_kategori").val("");
    markActiveKategori();
    loadProducts(1);
    if(kat){fetchAndRenderSubcats(kat);}else{hideSubcats();}
    scrollToGrid();
  });

  $(document).on("click",".subcat-badge",function(e){
    e.preventDefault();
    const sid=String($(this).data("sub")||"");
    $("#sub_kategori").val(sid);
    markActiveSub(sid);
    loadProducts(1);
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

    // RECOMMENDED from URL (?rec=1 / ?recommended=1)
    if(url.searchParams.get("rec")==="1" || url.searchParams.get("recommended")==="1"){
      setRec(true);
    }else{
      setRec(false);
    }

    setSortLabel($("#sort").val()||"random");

    markActiveKategori();

    const katInit=$("#kategori").val();
    if(isRecOn()){
      hideSubcats(); // rec aktif → tanpa sub
    }else{
      if(katInit){fetchAndRenderSubcats(katInit);}else{hideSubcats();}
    }

    const firstPage=parseInt(url.searchParams.get("page")||"1",10);
    loadProducts(firstPage,false);

    // Mode info alert (tetap seperti aslinya)
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

    setSortLabel($("#sort").val());
    markActiveKategori();

    if(s.recommended){ // rec aktif → tanpa sub
      hideSubcats();
    }else{
      if(s.kategori){fetchAndRenderSubcats(s.kategori);}else{hideSubcats();}
    }
    loadProducts(parseInt(s.page||1,10),false);
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
