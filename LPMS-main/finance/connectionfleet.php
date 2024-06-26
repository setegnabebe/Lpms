<?php
//$con=mysqli_connect('localhost','root','','fleet');
$con=mysqli_connect('localhost','root','','fleet');
$consms=mysqli_connect('localhost','root','','sms');
$conn=mysqli_connect('localhost','root','','vis');
$conlpms = mysqli_connect('localhost','root','','project_lpms');
$conws = mysqli_connect('localhost','root','','ws');

$compt = gethostbyaddr($_SERVER['REMOTE_ADDR']);
date_default_timezone_set('Africa/Addis_Ababa');
gettimeofday(true); 
$arrays=explode(".",microtime(true));
$futuredate=date("Y-m-d");
$timing=date("H:i");
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    $link1 = "https"; 
else
    $link1 = "http"; 
$link1 .= "://"; 
$link1 .= $_SERVER['HTTP_HOST']; 
$link1 .= $_SERVER['REQUEST_URI']; 

$project_name = "FMS";
$stmt_email_reason = $conlpms->prepare("INSERT INTO `emails`(`project`, `send_to`, `cc`, `bcc`, `subject`, `data`, `tag`, `company_logo`, `reason`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt_email_report = $conlpms->prepare("INSERT INTO `emails`(`project`, `send_to`, `cc`, `bcc`, `subject`, `data`, `tag`, `company_logo`, `reason`, `attachment`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

?>

