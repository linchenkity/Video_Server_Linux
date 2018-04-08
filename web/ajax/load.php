<?php
include ("../../config/config.php");
include ("../include/function.php");
$redis=Redis_Link();
$info = new SystemInfoWindows();
$mem = $info -> getMemoryUsage();
exec('wmic cpu get LoadPercentage',$cpu_exec);
$disk_total=round(disk_total_space("./")/1024/1024/1024,2);
$disk_free=round(disk_free_space("./")/1024/1024/1024,2);
$disk_per=ceil(round($disk_free/$disk_total*100,2));
$disk_per=100-$disk_per;
if ($cpu_exec[2]!=""){
    $return['data']['cpu']=$cpu_exec[2]+$cpu_exec[1];
    $return['data']['cpu']=round($return['data']['cpu']/2,2);
}else{
    $return['data']['cpu']=$cpu_exec[1];
}
$return['data']['mem']=$mem['usage'];
$return['data']['disk_total']=$disk_total;
$return['data']['disk_free']=$disk_free;
$return['data']['disk_per']=$disk_per;
for ($i=1;$i<=Get_Config('worker_thread');$i++){
    $status=$redis->get('Worker_Status_'.$i);
    if (empty($status)){
        $return['data']['worker'][$i]='3';
    }elseif ($status==1){
        $return['data']['worker'][$i]='1';
    }elseif ($status==2){
        $return['data']['worker'][$i]='2';
    }else{
        $return['data']['worker'][$i]='3';
    }
    $status = 0;
}
echo json_encode($return);
?>