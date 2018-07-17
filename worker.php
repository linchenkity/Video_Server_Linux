<?php
echo "[Worker] Initialization\n";
echo "[Worker] Loading Function Database\n";
include("include/function.php");
include("config/config.php");
echo "[Worker] Connecting to Redis\n";
$redis = Redis_Link();
echo "[Worker] Connecting to Mysql\n";
$db_link = DB_Link();
echo "[Worker] Register Worker Thread\n";
$worker_no = $argv[1];
if (empty($worker_no)){
    echo "[Worker]Empty VALUE!";
    sleep(10);
    exit;
}
$redis->set('Worker_Status_' . $worker_no, '1');
echo "[Worker] Register Success\n";
start:
//Dynamic Load Config
$encode_bitrate_video = Get_Config('encode_bitrate_video');
$encode_bitrate_audio = Get_Config('encode_bitrate_audio');
$encode_framerate = Get_Config('encode_framerate');
$encode_res = Get_Config('encode_res');
$encode_ts_time = Get_Config('encode_ts_time');
$encode_ts_frame = Get_Config('encode_ts_frame');
$worker_thread = Get_Config('worker_thread');
//
$work = $argv[2];
if (!empty($work)) {
    $redis->set('Worker_Status_' . $worker_no, '2');
    Add_Log('Worker #'.$worker_no,'Worker Starting.....','INFO');
    echo "[Worker] Get Work.\n";
    echo "[Worker] Find Work Data\n";
    $row_work = mysqli_fetch_array(mysqli_query($db_link, "SELECT * FROM video_list WHERE ID = '" . $work. "'"));
    echo "[Encode] Filename:" . $row_work['filename'] . "\n";
    //创建文件夹
    $today = $row_work['day'];
    if (!file_exists(Get_Config('video_folder')."/" . $today)) {
        mkdir(Get_Config('video_folder')."/" . $today, 0777, true);
        echo "[File]Create Dir '" . $today . "'\n";
        Add_Log('Worker #'.$worker_no,'Create Dir "'.$today.'"','INFO');
    }
    $hls_dir = $row_work['random'];
    echo "[File] Create Dir '" . $hls_dir . "'\n";
    Add_Log('Worker #'.$worker_no,'Create Dir "'.$hls_dir.'"','INFO');
    mkdir(Get_Config('video_folder')."/" . $today . "/" . $hls_dir, 0777, true);
    //计算文件名
    $file_type = end(explode(".", $row_work['filename']));
    $filename = $row_work['random'] . '.' . $file_type;
    //
    //
    //
    //转码
    //获取视频比特率
    $video_info=Get_Video_Info("encoding/".$filename);
    if ($video_info==false){
        echo "[INFO] Failed Get Video Info\n";
    }else{
        echo "[INFO] Video Bitrate :".$video_info['bitrate']."\n";
        if ($encode_bitrate_video>$video_info['bitrate']){
            $encode_bitrate_video=$video_info['bitrate'];
            echo "[INFO] Bitrate Changed\n";
        }else{
            echo "[INFO] Bitrate Not Change\n";
        }
        sleep(2);
    }
    //预置Video Filter
    $vf_init=0;
    $vf_setting="";
    //DELogo
    if (Get_Config('delogo')==1){
        //预置切除视频头命令
        if (!empty(Get_Config('delogo_start_cut'))) {
            $delogo_start_cut = " -ss " . Get_Config('delogo_start_cut');
        } else {
            $delogo_start_cut = "";
        }
        //[Video Filter] 设置DELogo
        if ($vf_init==0){
            $vf_setting=" -vf delogo=".Get_Config('delogo_pos');
            $vf_init=1;
        }else{
            $vf_setting=$vf_setting.",delogo=".Get_Config('delogo_pos');
        }
    }
    //[Video Filter] 设置分辨率
    if ($vf_init==0){
        $vf_setting=" -vf scale=".$encode_res;
        $vf_init=1;
    }else{
        $vf_setting=$vf_setting.",scale=".$encode_res;
    }
    //预置帧率命令
    if (!empty($encode_framerate)) {
        $video_framerate = ' -r ' . $encode_framerate;
    } else {
        $video_framerate = '';
    }
    $common_encode = "ffmpeg -i encoding/" . $filename . $delogo_start_cut . $video_framerate . " -c:v libx264 -c:a aac -b:v " . $encode_bitrate_video . "K -b:a " . $encode_bitrate_audio . "K".$vf_setting." -keyint_min " . $encode_ts_frame . " -g " . $encode_ts_frame . " -sc_threshold 0 -strict -2 encoding/encode_" . $row_work['random'] . ".mp4 > cache/worker_".$worker_no." 2>&1";
    echo "[Encode] Start Encode.........\n";
    echo "[DEBUG] Encode Common:" . $common_encode;
    sleep(2);
    $redis->set('Worker_Monitor_'.$worker_no,'1');
    Add_Log('Worker #'.$worker_no,'Start Encode','INFO');
    exec($common_encode);
    //判断转码结果
    if (!file_exists("encoding/encode_".$row_work['random'].".mp4")){
        //判断为转码失败
        Add_Log('Worker #'.$worker_no,'Encode Failed','INFO');
        Col_echo("[Encode] Encode Error\n",'light_red');
        $log_name="Error_".$worker_no."_".$row_work['random'].".log";
        Col_echo("[Logs] Dump Log to ".$log_name."\n",'yellow');
        rename("cache/worker_".$worker_no,"logs\\".$log_name);
        $redis->del('Work_Info_' . $worker_no);
        $redis->del('Worker_Status_' . $worker_no);
        $redis->del('Worker_Monitor_'.$worker_no);
        mysqli_query($db_link, "UPDATE `video_list` SET `status` = '3' WHERE `ID` = " . $work . ";");
        Col_echo("[Worker] Exiting\n",'light_red');
        sleep(3);
        exit;
    }
    unlink("encoding/" . $filename);
    rename("encoding/encode_" . $row_work['random'] . ".mp4", "encoding/" . $row_work['random'] . ".mp4");
    $filename = $row_work['random'] . ".mp4";
    //
    //
    //
    //切片
    //预置命令
    $common = "ffmpeg -i \"encoding/" . $filename . "\" -c:v copy -c:a copy -f hls -hls_list_size 0 -hls_init_time " . $encode_ts_time . " -hls_time " . $encode_ts_time . " -hls_key_info_file " .Get_Config('video_folder')."/". $today . "/" . $hls_dir . "/key_info -hls_segment_filename " .Get_Config('video_folder')."/". $today . "/" . $hls_dir . "/" . $hls_dir . "%03d.ts ".Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/index.m3u8";
    //设置加密文件
    echo "[Encode] Setting Encryption Key\n";
    Add_Log('Worker #'.$worker_no,'Setting Encryption Key','INFO');
    $en_file = fopen(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/key.key", 'w');
    fwrite($en_file, Random_String(16));
    fclose($en_file);
    $en_file = fopen(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/key_info", 'w');
    fwrite($en_file, "key.key\r\n".Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/key.key");
    fclose($en_file);
    //开始截图
    if (Get_Config('sc_jpeg') == 1) {
        //分辨率更改
        if (Get_Config('sc_jpeg_res') != 0) {
            $jpeg_res = " -s " . Get_Config('sc_jpeg_res');
        } else {
            $jpeg_res = "";
        }
        echo "[ScreenShot] JPEG-Working...\n";
        Add_Log('Worker #'.$worker_no,'JPEG-Working','INFO');
        if (!file_exists(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots")) {
            mkdir(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots", 0777, true);
        }
        //计算截图参数
        $start_time = Get_Config('sc_jpeg_start_time');
        $jpeg_num = Get_Config('sc_jpeg_number');
        $jpeg_int = Get_Config('sc_jpeg_int');
        $sc_t = $jpeg_num * $jpeg_int;
        $sc_r = 1 / $jpeg_int;
        $jpeg_common = "ffmpeg -i encoding/" . $filename . " -ss " . $start_time . " -t " . $sc_t . " -r " . $sc_r . $jpeg_res . " -f image2 ".Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots/%1d.jpg";
        sleep(2);
        exec($jpeg_common);
        //截图完成 扫描截图生成文件 TODO:截图超过150张处理
        $sc_file = getFile(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots");
        for ($num = $file_num = 0; !empty($sc_file[$num]); $num++) {
            $sc_file_type = end(explode(".", $sc_file[$num]));
            if ($sc_file_type == "jpg") {
                $jpeg_file[$file_num] = $sc_file[$num];
                $file_num++;
            }
        }
        if (!empty($jpeg_file[0])) {
            $jpeg_file = json_encode($jpeg_file);
            mysqli_query($db_link, "INSERT INTO `screenshot` (`ID`, `video_id`, `type`, `files`) VALUES (NULL, '" . $row_work['ID'] . "', '1', '" . $jpeg_file . "')");
        }
    }
    //动态图
    if (Get_Config('sc_gif') == 1) {
        //分辨率更改
        if (Get_Config('sc_gif_res') != 0) {
            $gif_res = " -s " . Get_Config('sc_gif_res');
        } else {
            $gif_res = "";
        }
        echo "[ScreenShot] GIF-Working...\n";
        Add_Log('Worker #'.$worker_no,'GIF-Working','INFO');
        if (!file_exists(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots")) {
            mkdir(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots", 0777, true);
        }
        $gif_start_time = Get_Config('sc_gif_start_time');
        $gif_time = Get_Config('sc_gif_time');
        $gif_res = Get_Config('sc_gif_res');
        $gif_framerate = Get_Config('sc_gif_framerate');
        $gif_common = "ffmpeg -i encoding/" . $filename . " -ss " . $gif_start_time . " -t " . $gif_time . " -s " . $gif_res . " -r " . $gif_framerate ." ".Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots/1.gif";
        sleep(2);
        exec($gif_common);
        //截图完成 扫描截图生成文件
        if (file_exists(Get_Config('video_folder')."/" . $today . "/" . $hls_dir . "/screenshots/1.gif")) {
            $gif_file[0] = '1.gif';
            $gif_file = json_encode($gif_file);
            mysqli_query($db_link, "INSERT INTO `screenshot` (`ID`, `video_id`, `type`, `files`) VALUES (NULL, '" . $row_work['ID'] . "', '2', '" . $gif_file . "')");
        }
    }
    //开始转码
    echo "[Encode] Starting FFMPEG..........\n";
    Add_Log('Worker #'.$worker_no,'Start Segmenting','INFO');
    sleep(2);
    exec($common);
    echo "\n";
    echo "[Encode] Encode Done!\n";
    Add_Log('Worker #'.$worker_no,'Done','INFO');
    echo "[File] Delete File " . $row_work['filename'] . "\n";
    Add_Log('Worker #'.$worker_no,'Delete File "'.$row_work['filename'].'"','INFO');
    unlink("encoding/" . $filename);
    echo "[Worker] Done!\n";
    $redis->del('Work_Info_' . $worker_no);
    $redis->del('Worker_Status_' . $worker_no);
    mysqli_query($db_link, "UPDATE `video_list` SET `status` = '2' WHERE `ID` = " . $work. ";");
    exit;
} else {
    echo "[Worker] Error!";
    Add_Log('Worker','Exiting','ERROR');
    exit;
}