 <div class="form-group">
    <label>Blokir/ batalkan blokir</label>
    <?php if($ref->blokir == "N") { 
        $cek = ""; 
        $b = "0"; 
        $kal = "unblock";
        $cr = "success";
    } else { 
        $cek = "checked"; 
        $b = "1"; 
        $kal = "block";
        $cr = "danger";
    } ?>
    <p><a href="javascript:void(0)" onclick="blokir(<?php echo  $this->uri->segment(3).",".$b ?>)"><?php echo $kal ?></a></p>
    <div class="custom-control custom-switch" >
        <a href="javascript:void(0)" onclick="blokir(<?php echo  $this->uri->segment(3).",".$b ?>)">
            <input type="checkbox" id="ceka" <?php echo $cek ?>   style="cursor: pointer !important;" class="custom-control-input">
            <label class="custom-control-label" for="ceka"><span id="kal" class='badge badge-<?php echo $cr ?> p-1'><?php echo $kal ?></span></label></a>
        </div>
        <small>Jika user diblokir, maka user tidak akan bisa login </small>
    </div>

    <div class="form-group">
     <label>Aktifkan permission untuk user jika diperbolehkan langsung menerbitkan postingan tanpa melalui verifikasi admin</label>
     <br>

     <div class="custom-control custom-switch" ><a href="javascript:void(0)" onclick="pub('.$res->id_modul.','.$id.')">
        <input type="checkbox"  style="cursor: pointer !important;" class="custom-control-input">
        <label class="custom-control-label" for="cek"></label>
    </a></div>
</div>