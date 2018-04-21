<?php
//开启Session
session_start();
//设置时区
date_default_timezone_set("Asia/Shanghai");
function Random_String($length)
{
    $str = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    shuffle($str);
    $str = implode('', array_slice($str, 0, $length));
    return $str;
}

//Redis
function Redis_Link()
{
    global $redis_address;
    global $redis_port;
    global $redis_auth;
    $redis = new Redis();
    $redis->pconnect($redis_address, $redis_port);
    $redis->auth($redis_auth);
    $redis->select(1);
    return $redis;
}

//Mysql
function DB_Link()
{
    global $mysql_address;
    global $mysql_port;
    global $mysql_username;
    global $mysql_password;
    global $mysql_db_name;
    $db_link = mysqli_connect($mysql_address, $mysql_username, $mysql_password, $mysql_db_name, $mysql_port);
    if (!$db_link) {
        echo "Mysql Error";
        exit;
    } else {
        mysqli_query($db_link, "SET NAMES utf8");
        return $db_link;
    }
}

//Get Config
function Get_Config($name)
{
    $redis = Redis_Link();
    $result = $redis->get('Config_' . $name);
    if (empty($result)) {
        $db_link = DB_Link();
        $row_config = mysqli_fetch_array(mysqli_query($db_link, "SELECT * FROM setting WHERE name = '" . $name . "'"));
        if (empty($row_config)) {
            return "";
        } else {
            $redis->set('Config_' . $name, $row_config['data']);
            return $row_config['data'];
        }
    } else {
        return $result;
    }
}
//登录状态检查
function Login_Status(){
    if ($_SESSION['login_status']==1){
        return true;
    }else{
        return false;
    }
}
//获取系统负载
function get_used_status(){
    $fp = popen('top -b -n 2 | grep -E "^(Cpu|Mem|Tasks)"',"r");//获取某一时刻系统cpu和内存使用情况
    $rs = "";
    while(!feof($fp)){
        $rs .= fread($fp,1024);
    }
    pclose($fp);
    $sys_info = explode("\n",$rs);
    $cpu_info = explode(",",$sys_info[4]); //CPU占有量 数组
    $mem_info = explode(",",$sys_info[5]); //内存占有量 数组
//CPU占有量
    $cpu_usage = trim(trim($cpu_info[0],'Cpu(s): '),'%us'); //百分比

//内存占有量
    $mem_total = trim(trim($mem_info[0],'Mem: '),'k total');
    $mem_used = trim($mem_info[1],'k used');
    $mem_usage = round(100*intval($mem_used)/intval($mem_total),2); //百分比

    return array('cpu_usage'=>$cpu_usage,'mem_usage'=>$mem_usage);
}
//API鉴权
function API_Auth($get,$post){
    if (empty($get)&&empty($post)){
        return false;
    }elseif(empty($get)){
        $api_key=$post;
    }else{
        $api_key=$get;
    }
    $real_api_key=Get_Config('api_key');
    if ($api_key==$real_api_key){
        return true;
    }else{
        return false;
    }
}
//删除目录及文件
function Delete_Dir($dirName)
{
    if(! is_dir($dirName))
    {
        return false;
    }
    $handle = @opendir($dirName);
    while(($file = @readdir($handle)) !== false)
    {
        if($file != '.' && $file != '..')
        {
            $dir = $dirName . '/' . $file;
            is_dir($dir) ? Delete_Dir($dir) : @unlink($dir);
        }
    }
    closedir($handle);

    return rmdir($dirName) ;
}
//更改系统设置
function Change_Config($name,$value){
    $db_link=DB_Link();
    $redis=Redis_Link();
    $row_config=mysqli_fetch_array(mysqli_query($db_link,"SELECT * FROM `setting` WHERE `name` = '".$name."'"));
    if (empty($row_config['ID'])){
        return false;
    }else{
        mysqli_query($db_link,"UPDATE `setting` SET `data` = '".$value."' WHERE `name` = '".$name."'");
        $redis->del('Config_'.$name);
        return true;
    }
}
