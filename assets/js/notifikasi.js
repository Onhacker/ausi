document.addEventListener("DOMContentLoaded", function () {
    const modal = $('#kontakModal');
    let isClosing = false;

    modal.on('hide.bs.modal', function (e) {
      if (isClosing) return; 
      e.preventDefault();

      const dialog = modal.find('.modal-dialog');

      dialog
      .removeClass('animated fadeInUp')
      .addClass('animated fadeOutDown');

      isClosing = true;

      setTimeout(() => {
        modal.modal('hide'); 
        dialog.removeClass('animated fadeOutDown'); 
        isClosing = false;
      }, 500); 
    });

    modal.on('show.bs.modal', function () {
      const dialog = modal.find('.modal-dialog');
      dialog
      .removeClass('fadeOutDown')
      .addClass('animated fadeInUp');
    });
  });

function updateIconColor() {
    const icon = document.getElementById('notification-icon');
    if (!icon) return; 
    if (window.innerWidth <= 767.98) {
        icon.style.color = 'white';
    } else {
        icon.style.color = 'black';
    }
}

function debounce(func, wait = 100) {
    let timeout;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(func, wait);
    };
}

window.onload = updateIconColor;
window.onresize = debounce(updateIconColor, 150);

let lastNotifCount = 0;
let isLoadingNotif = false;

function updateNotifCount() {
    $.getJSON(base_url + "admin_dashboard/cek_total_notif", function(data) {
        const badge = $('#notif-count');

        if (data.total > 0) {
            badge.text(data.total).show();

            if (data.total !== lastNotifCount) {
                badge.addClass('bounce');
                setTimeout(() => {
                    badge.removeClass('bounce');
                }, 500);
            }

            if ('setAppBadge' in navigator) {
                navigator.setAppBadge(data.total);
            }
        } else {
            badge.hide();
            if ('clearAppBadge' in navigator) {
                navigator.clearAppBadge();
            }
        }

        lastNotifCount = data.total;
    });
}

function loadNotifikasi() {
    if (isLoadingNotif) return;
    isLoadingNotif = true;

    $('#notif-container').html('<li class="text-center p-2">Memuat...</li>');

    $.getJSON(base_url + "admin_dashboard/cek_notifikasi", function(data) {
        let html = '';

        if (data.length === 0) {
            html = '<li class="text-center p-2 text-muted">Tidak ada notifikasi</li>';

            if ('clearAppBadge' in navigator) {
                navigator.clearAppBadge();
            }
        } else {
            data.forEach(item => {
                let alertClass = 'alert-warning';
                if (item.status == 3) alertClass = 'alert-success';
                else if (item.status == 4) alertClass = 'alert-danger';

                html += `
                <div class="alert ${alertClass} mb-2 alert_notif" role="alert">
                    <a href="${item.link ?? 'javascript:void(0)'}" class="dropdown-item notify-item p-0" style="text-decoration: none;" onclick="markAsRead('${item.tabel}', '${item.id_paket}')">
                        <p class="notify-details mb-1"><i class="fe-clock mr-1"></i>${item.text}</p>
                        <p class="text-dark mb-0 user-msg"><small>${item.message}</small></p>
                    </a>
                </div>`;
            });

            if ('setAppBadge' in navigator) {
                navigator.setAppBadge(data.length);
            }
        }

        $('#notif-container').html(html);
        isLoadingNotif = false;
    }).fail(function() {
        $('#notif-container').html('<li class="text-center p-2 text-danger">Gagal memuat notifikasi</li>');
        isLoadingNotif = false;
    });
}

function markAsRead(tabel, id_paket) {
    $.ajax({
        url: base_url + "admin_dashboard/mark_as_read",
        method: "POST",
        data: { tabel: tabel, id_paket: id_paket },
        success: function(response) {
            const result = JSON.parse(response);
            if (result.success) {
                updateNotifCount();
                loadNotifikasi();
            } else {
                alert("Gagal memperbarui status notifikasi");
            }
        },
        error: function() {
            alert("Terjadi kesalahan saat memperbarui status notifikasi");
        }
    });
}


$(document).ready(function() {
    updateNotifCount();
    setInterval(updateNotifCount, 3000000); // Setiap 5 menit (300.000 ms)
    // setInterval(updateNotifCount, 10000); //10 detik
});
