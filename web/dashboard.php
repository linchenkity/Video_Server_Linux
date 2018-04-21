<?php
include("../config/config.php");
include("include/function.php");
if (!Login_Status()) {
    header("Location:login.php");
    exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Video Encode Server</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    Server Status
                </div>
                <div class="card-body">
                    CPU:
                    <div class="progress">
                        <div id="cpu-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">Loading
                        </div>
                    </div>
                    Memory:
                    <div class="progress">
                        <div id="mem-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">Loading
                        </div>
                    </div>
                    Disk:(Free:<span id="disk-free">Loading</span>/Total:<span id="disk-total">Loading</span>)
                    <div class="progress">
                        <div id="disk-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">Loading
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    Worker Status
                </div>
                <div class="card-body">
                    <span class="badge badge-success">&nbsp;&nbsp;</span>:Free&nbsp;&nbsp;
                    <span class="badge badge-danger">&nbsp;&nbsp;</span>:Busy&nbsp;&nbsp;
                    <span class="badge badge-secondary">&nbsp;&nbsp;</span>:Down<br>
                    <?php
                    for ($i=1;$i<=Get_Config('worker_thread');$i++){
                        ?>
                        <span id="worker_<?php echo $i;?>" class="badge badge-info"><?php echo $i;?>#</span>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    Encode Control
                </div>
                <div class="card-body">
                    <?php
                    if (empty(Get_Config('video_folder'))||empty(Get_Config('upload_folder'))){
                        echo '<div class="alert alert-danger">Please set video folder and upload folder before start encode!</div>';
                    }else{
                        if (is_dir(Get_Config('video_folder'))&&is_dir(Get_Config('upload_folder'))){
                            echo '<button id="start_encode" type="button" class="btn btn-primary btn-lg" onclick="Start_Encode()">Start Encode</button>';
                        }else{
                            echo '<div class="alert alert-danger">Can\'t Read Video/Upload Folder</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    function Update_System_Info() {
        var cpu_progress = document.getElementById('cpu-progress');
        var mem_progress = document.getElementById('mem-progress');
        var disk_progress = document.getElementById('disk-progress');
        var ajax = new XMLHttpRequest();
        ajax.open('GET', 'ajax/load.php', true);
        ajax.send();
        ajax.onreadystatechange = function () {
            if (ajax.readyState == 4 && ajax.status == 200) {
                var result = JSON.parse(ajax.responseText);
                cpu_progress.innerHTML = result['data']['cpu'] + '%';
                cpu_progress.style.width = result['data']['cpu'] + '%';
                cpu_progress.setAttribute('aria-valuenow', result['data']['cpu']);
                mem_progress.innerHTML = result['data']['mem'] + '%';
                mem_progress.style.width = result['data']['mem'] + '%';
                mem_progress.setAttribute('aria-valuenow', result['data']['mem']);
                disk_progress.innerHTML = result['data']['disk_per'] + '%';
                disk_progress.style.width = result['data']['disk_per'] + '%';
                disk_progress.setAttribute('aria-valuenow', result['data']['disk_per']);
                document.getElementById('disk-free').innerHTML = result['data']['disk_free'] + 'GB';
                document.getElementById('disk-total').innerHTML = result['data']['disk_total'] + 'GB';
                for (var i=1;i<=<?php echo Get_Config('worker_thread');?>;i++){
                    if (result['data']['worker'][i]==1){
                        document.getElementById('worker_'+i).setAttribute('class','badge badge-success');
                    }
                    if (result['data']['worker'][i]==2){
                        document.getElementById('worker_'+i).setAttribute('class','badge badge-danger');
                    }
                    if (result['data']['worker'][i]==3){
                        document.getElementById('worker_'+i).setAttribute('class','badge badge-secondary');
                    }
                }
            }
        }
    }
    function Start_Encode() {
        var ajax=new XMLHttpRequest();
        ajax.open('GET','ajax/encode.php?action=start',true);
        ajax.send();
        ajax.onreadystatechange = function () {
            if (ajax.readyState == 4 && ajax.status == 200) {
                var result = JSON.parse(ajax.responseText);
                if (result['code']==201){
                    document.getElementById('start_encode').disabled=true;
                    document.getElementById('start_encode').innerHTML='Starting...';
                }
            }
        }
    }
    setInterval(Update_System_Info, 5000);
    window.onload = Update_System_Info;
</script>
</html>