<?php
include("../../config/config.php");
include("../include/function.php");
if ($_GET['action'] == "update") {
    if ($_GET['type'] == "encode") {
        //
        //
        //Encode Config
        //
        //
        if (!Login_Status()) {
            $return['code'] = "101";
            $return['data']['message'] = "Login Status Error!";
            echo json_encode($return);
            exit;
        }
        $db_link = DB_Link();
        $redis = Redis_Link();
        $bitrate_video = mysqli_real_escape_string($db_link, $_POST['encode_bitrate_video']);
        $bitrate_audio = mysqli_real_escape_string($db_link, $_POST['encode_bitrate_audio']);
        $ts_time = mysqli_real_escape_string($db_link, $_POST['encode_ts_time']);
        $ts_frame = mysqli_real_escape_string($db_link, $_POST['encode_ts_frame']);
        $framerate = mysqli_real_escape_string($db_link, $_POST['encode_framerate']);
        $res = mysqli_real_escape_string($db_link, $_POST['encode_res']);
        $worker_thread = mysqli_real_escape_string($db_link, $_POST['worker_thread']);
        if ($bitrate_video == "" || $bitrate_audio == "" || $framerate=="" || $res == "" || empty($ts_frame) || empty($ts_time) || empty($worker_thread)) {
            $return['code'] = "101";
            $return['data']['message'] = "Empty Value!";
            echo json_encode($return);
            exit;
        }
        Change_Config('encode_bitrate_video', $bitrate_video);
        Change_Config('encode_bitrate_audio', $bitrate_audio);
        Change_Config('encode_framerate',$framerate);
        Change_Config('encode_res',$res);
        Change_Config('encode_ts_frame', $ts_frame);
        Change_Config('encode_ts_time', $ts_time);
        Change_Config('worker_thread', $worker_thread);
        $return['code'] = "201";
        $return['data']['message'] = "Update Encode Config Successful!";
        echo json_encode($return);
        exit;
    } elseif ($_GET['type'] == "api") {
        //
        //
        //API Config
        //
        //
        if (!Login_Status()) {
            $return['code'] = "101";
            $return['data']['message'] = "Login Status Error!";
            echo json_encode($return);
            exit;
        }
        $db_link = DB_Link();
        $redis = Redis_Link();
        $api_key = mysqli_real_escape_string($db_link, $_POST['api_key']);
        Change_Config('api_key', $api_key);
        $return['code'] = "201";
        $return['data']['message'] = "Update API Config Successful!";
        if (empty($api_key)) {
            $return['data']['message'] = "You set API Key empty.API will disable";
        }
        echo json_encode($return);
        exit;
    } elseif ($_GET['type'] == "video_service") {
        //
        //
        //Video Service Config
        //
        //
        if (!Login_Status()) {
            $return['code'] = "101";
            $return['data']['message'] = "Login Status Error!";
            echo json_encode($return);
            exit;
        }
        $db_link = DB_Link();
        $redis = Redis_Link();
        $video_port = (int)mysqli_real_escape_string($db_link, $_POST['video_port']);
        $video_domain = mysqli_real_escape_string($db_link, $_POST['video_domain']);
        $play_secure = mysqli_real_escape_string($db_link, $_POST['play_secure']);
        $allow_domain = mysqli_real_escape_string($db_link, $_POST['allow_domain']);
        $jump_link = mysqli_real_escape_string($db_link, $_POST['jump_link']);
        if (empty($video_port) || empty($video_domain) || $play_secure == "") {
            $return['code'] = "101";
            $return['data']['message'] = "Empty Value!";
            echo json_encode($return);
            exit;
        } elseif ($play_secure == 1) {
            if (empty($allow_domain)) {
                $return['code'] = "101";
                $return['data']['message'] = "Must set allow domain when Play Secure enable!";
                echo json_encode($return);
                exit;
            }
        }
        Change_Config('video_port', $video_port);
        Change_Config('video_domain', $video_domain);
        Change_Config('play_secure', $play_secure);
        Change_Config('allow_domain', $allow_domain);
        Change_Config('jump_link', $jump_link);
        //Update
        Nginx_Update_Video_Service();
        $return['code'] = "201";
        $return['data']['message'] = "Update Video Service Config Successful!";
        echo json_encode($return);
        exit;
    } elseif ($_GET['type'] == "screenshot") {
        //
        //
        //ScreenShot
        //
        //
        if (!Login_Status()) {
            $return['code'] = "101";
            $return['data']['message'] = "Login Status Error!";
            echo json_encode($return);
            exit;
        }
        $db_link = DB_Link();
        $redis = Redis_Link();
        $jpeg = (int)mysqli_real_escape_string($db_link, $_POST['jpeg']);
        $jpeg_start_time = mysqli_real_escape_string($db_link, $_POST['jpeg_start_time']);
        $jpeg_number = (int)mysqli_real_escape_string($db_link, $_POST['jpeg_number']);
        $jpeg_res = mysqli_real_escape_string($db_link, $_POST['jpeg_res']);
        $jpeg_int = (int)mysqli_real_escape_string($db_link, $_POST['jpeg_int']);
        $gif = (int)mysqli_real_escape_string($db_link, $_POST['gif']);
        $gif_start_time = mysqli_real_escape_string($db_link, $_POST['gif_start_time']);
        $gif_time = (int)mysqli_real_escape_string($db_link, $_POST['gif_time']);
        $gif_res = mysqli_real_escape_string($db_link, $_POST['gif_res']);
        $gif_framerate = (int)mysqli_real_escape_string($db_link, $_POST['gif_framerate']);
        if ($jpeg == 1) {
            if (empty($jpeg_start_time) || empty($jpeg_number) || empty($jpeg_int)) {
                $return['code'] = "101";
                $return['data']['message'] = "Empty JPEG Value!";
                echo json_encode($return);
                exit;
            }
        }
        if ($gif == 1) {
            if (empty($gif_start_time) || empty($gif_time)) {
                $return['code'] = "101";
                $return['data']['message'] = "Empty GIF Value!";
                echo json_encode($return);
                exit;
            }
        }
        Change_Config('sc_jpeg', $jpeg);
        Change_Config('sc_jpeg_start_time', $jpeg_start_time);
        Change_Config('sc_jpeg_number', $jpeg_number);
        Change_Config('sc_jpeg_res', $jpeg_res);
        Change_Config('sc_jpeg_int', $jpeg_int);
        Change_Config('sc_gif', $gif);
        Change_Config('sc_gif_start_time', $gif_start_time);
        Change_Config('sc_gif_time', $gif_time);
        Change_Config('sc_gif_res', $gif_res);
        Change_Config('sc_gif_framerate', $gif_framerate);
        $return['code'] = "201";
        $return['data']['message'] = "Update ScreenShot Config Successful!";
        echo json_encode($return);
        exit;
    }elseif ($_GET['type']=="storage"){
        if (!Login_Status()) {
            $return['code'] = "101";
            $return['data']['message'] = "Login Status Error!";
            echo json_encode($return);
            exit;
        }
        $db_link = DB_Link();
        $redis = Redis_Link();
        $upload_folder=mysqli_real_escape_string($db_link,$_POST['upload']);
        $video_folder=mysqli_real_escape_string($db_link,$_POST['video']);
        if (empty($upload_folder)||empty($video_folder)){
            $return['code'] = "101";
            $return['data']['message'] = "Empty Value";
            echo json_encode($return);
            exit;
        }
        Change_Config('upload_folder',$upload_folder);
        Change_Config('video_folder',$video_folder);
        //Update
        Nginx_Update_Video_Service();
        $return['code'] = "201";
        $return['data']['message'] = "Update Storage Config Successful!";
        echo json_encode($return);
        exit;
    }
}