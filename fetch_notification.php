<?php 
require_once('connect.php');
$db = new Connnect;
$data = [];


$notification = $db ->prepare("SELECT * FROM $tbl_Notifications order by id desc limit 10");
$notifications -> execute();
$n_notifications = $notifications -> rowCount();
if ($n_notifications >0) {

    $_number = $db->prepare("SELECT * FROM $tbl_Notifications WHERE")

}




}





















>