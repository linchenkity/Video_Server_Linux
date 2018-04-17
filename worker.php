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
echo "[Worker] Create VM\n";
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
    exec('title Worker ' . $worker_no . '# [Busy]');
    echo "[Worker] Get Work.\n";
    echo "[Worker] Find Work Data\n";
    $row_work = mysqli_fetch_array(mysqli_query($db_link, "SELECT * FROM video_list WHERE ID = '" . $work. "'"));
    echo "[Encode] Filename:" . $row_work['filename'] . "\n";
    //创建文件夹
    $today = $row_work['day'];
    if (!file_exists("video/" . $today)) {
        mkdir("video/" . $today, 0777, true);
        echo "[File]Create Dir '" . $today . "'\n";
    }
    $hls_dir = $row_work['random'];
    echo "[File] Create Dir '" . $hls_dir . "'\n";
    mkdir("video/" . $today . "/" . $hls_dir, 0777, true);
    //计算文件名
    $file_type = end(explode(".", $row_work['filename']));
    $filename = $row_work['random'] . '.' . $file_type;
    //预置分辨率命令
    if (!empty($encode_res)) {
        $video_res = ' -s ' . $encode_res;
    } else {
        $video_res = '';
    }
    //预置帧率命令
    if (!empty($encode_framerate)) {
        $video_framerate = ' -r ' . $encode_framerate;
    } else {
        $video_framerate = '';
    }
    //预置命令
    $common = "ffmpeg -i \"encoding/" . $filename . "\" -b:v " . $encode_bitrate_video . "K -b:a " . $encode_bitrate_audio . "K -c:v libx264 -c:a aac -keyint_min " . $encode_ts_frame . " -g " . $encode_ts_frame . $video_res . $video_framerate . " -sc_threshold 0 -strict -2 -f hls -hls_list_size 0 -hls_init_time " . $encode_ts_time . " -hls_time " . $encode_ts_time . " -hls_key_info_file video/" . $today . "/" . $hls_dir . "/key_info -hls_segment_filename video/" . $today . "/" . $hls_dir . "/" . $hls_dir . "%03d.ts video/" . $today . "/" . $hls_dir . "/index.m3u8";
    //设置加密文件
    echo "[Encode] Setting Encryption Key\n";
    $en_file = fopen("video/" . $today . "/" . $hls_dir . "/key.key", 'w');
    fwrite($en_file, Random_String(16));
    fclose($en_file);
    $en_file = fopen("video/" . $today . "/" . $hls_dir . "/key_info", 'w');
    fwrite($en_file, "key.key\r\nvideo/" . $today . "/" . $hls_dir . "/key.key");
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
        if (!file_exists("video/" . $today . "/" . $hls_dir . "/screenshots")) {
            mkdir("video/" . $today . "/" . $hls_dir . "/screenshots", 0777, true);
        }
        //计算截图参数
        $start_time = Get_Config('sc_jpeg_start_time');
        $jpeg_num = Get_Config('sc_jpeg_number');
        $jpeg_int = Get_Config('sc_jpeg_int');
        $sc_t = $jpeg_num * $jpeg_int;
        $sc_r = 1 / $jpeg_int;
        $jpeg_common = "ffmpeg -i encoding/" . $filename . " -ss " . $start_time . " -t " . $sc_t . " -r " . $sc_r . $jpeg_res . " -f image2 video/" . $today . "/" . $hls_dir . "/screenshots/%1d.jpg";
        sleep(2);
        exec($jpeg_common);
        //截图完成 扫描截图生成文件 TODO:截图超过150张处理
        $sc_file = getFile("video/" . $today . "/" . $hls_dir . "/screenshots");
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
        if (!file_exists("video/" . $today . "/" . $hls_dir . "/screenshots")) {
            mkdir("video/" . $today . "/" . $hls_dir . "/screenshots", 0777, true);
        }
        $gif_start_time = Get_Config('sc_gif_start_time');
        $gif_time = Get_Config('sc_gif_time');
        $gif_res = Get_Config('sc_gif_res');
        $gif_framerate = Get_Config('sc_gif_framerate');
        $gif_common = "ffmpeg -i encoding/" . $filename . " -ss " . $gif_start_time . " -t " . $gif_time . " -s " . $gif_res . " -r " . $gif_framerate . " video/" . $today . "/" . $hls_dir . "/screenshots/1.gif";
        sleep(2);
        exec($gif_common);
        //截图完成 扫描截图生成文件
        if (file_exists("video/" . $today . "/" . $hls_dir . "/screenshots/1.gif")) {
            $gif_file[0] = '1.gif';
            $gif_file = json_encode($gif_file);
            mysqli_query($db_link, "INSERT INTO `screenshot` (`ID`, `video_id`, `type`, `files`) VALUES (NULL, '" . $row_work['ID'] . "', '2', '" . $gif_file . "')");
        }
    }
    //开始转码
    echo "[Encode] Starting FFMPEG..........\n";
    sleep(2);
    exec($common);
    echo "\n";
    echo "[Encode] Encode Done!\n";
    echo "[File] Delete File " . $row_work['filename'] . "\n";
    unlink("encoding/" . $filename);
    echo "[Worker] Done!\n";
    $redis->del('Work_Info_' . $worker_no);
    $redis->del('Worker_Status_' . $worker_no);
    mysqli_query($db_link, "UPDATE `video_list` SET `status` = '2' WHERE `ID` = " . $work. ";");
    exit;
} else {
    echo "[Worker] Error!";
    exit;
}