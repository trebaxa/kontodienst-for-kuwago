<?PHP

$json = kd_install::curl_get_data('https://erp.kuwago.de/rest/rest.php?cmdo=get_fints_server');
$arr = json_decode($json,true);
    #kd_install::echoarr($arr);        
?>
<h1>Kontodienst.de <small>auf Ihrem eigenem Server</small></h1>
<div class="lead">
    <p>
    Dieser Dienst ermöglicht Ihnen auf Ihrem eigenen Server Kontoauszüge per REST API anderen Systeme in Echtzeit zur Verfügung zu stellen.
    </p>
</div>

<div class="row">
    <div class="col-md-6">
        <form action="install.php" method="post">
            <input type="hidden" name="cmd" value="install" />
            <div class="form-group">
                <label>Bank BLZ:</label>
                <input type="text" class="form-control" name="FORM[blz]" required="" value="<?=$_SESSION['set']['blz']?>" />
            </div>
            <div class="form-group">
                <label>Konto Nr.</label>
                <input type="text" class="form-control" name="FORM[konto]" required="" value="<?=$_SESSION['set']['konto']?>"/>
            </div>
            <div class="form-group">
                <label>Server URL (FinTS) der Bank</label>
                <!-- <input type="text" class="form-control" name="FORM[server]" required="" value="<?=$_SESSION['set']['server']?>"/> -->
                <select name="FORM[server]" id="js-server" class="form-control">
                <?PHP
                if (is_array($arr)){
                   # kd_install::<ins>echoarr</ins>($arr);
                    foreach ($arr['servers'] as $row) {
                        $sel = ($_SESSION['set']['server']==$row['fin_pintanurl']) ? 'selected' : '' ;
                        echo '<option data-id="'.$row['id'].'" '.$sel.' value="'.$row['fin_pintanurl'].'">'.$row['fin_name'].' ['.$row['fin_location'].']</option>';
                    }
                }
                ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Online-Banking Username</label>
                <input type="text" class="form-control" name="FORM[user]" required="" value="<?=$_SESSION['set']['user']?>" />
            </div>
            
            <div class="form-group">
                <label>Online-Banking PIN</label>
                <input type="password" class="form-control" name="FORM[pin]" required="" value="" />
            </div>
            
            <button class="btn btn-primary">weiter</button>
        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info">
            Der Schnittstelle verwendet das pushTAN Verfahren.
        </div>
        <div class="alert alert-info">
<p>Die Formular-Daten werden verschlüsselt und sicher gespeichert.</p>
<p>Weitere Infos unter: <a href="https://github.com/trebaxa/kontodienst-for-kuwago" target="_blank">https://github.com/trebaxa/kontodienst-for-kuwago</a></p>
<p>Kompatibles ERP System: <a target="_blank" href="https://www.kuwago.de">https://www.kuwago.de</a></p> 
        </div>
    </div>
    
     <div class="col-md-12"><br>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Bank</th>
                <th>Ort</th>
                <th>FinTs Server</th>
            </tr>
        </thead>
        <tbody>
        <?php
            
    #        print_r($arr);
            if (is_array($arr)){
            foreach ($arr['servers'] as $row) {
             echo '<tr>
                <td>'.$row['fin_name'].'</td>            
                <td>'.$row['fin_location'].'</td>
                <td>'.$row['fin_pintanurl'].'</td>
                <td><button type="button" onclick="set_server('.$row['id'].',\''.$row['fin_pintanurl'].'\')" class="btn btn-secondary">+</button></td>
             </tr>';   
            }}
        ?>
        </tbody>
    </table>
    </div>
</div>

<script>
    function set_server(id,server) {
        $("#js-server").find("option").removeAttr("selected");
        $("#js-server").find("option").prop('selected',false);
        $('#js-server').find("[data-id='"+id+"'").attr('selected','selected');
        $('#js-server').find("[data-id='"+id+"'").prop('selected',true);
        $('#js-server').find("[data-id='"+id+"'").val(server);        
         $('html,body').animate({
            scrollTop: $("#js-server").offset().top -50
        }, 500);
    }
</script>