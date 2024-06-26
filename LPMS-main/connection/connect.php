<?php
$pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
$first =(isset($_SESSION["username"]))?"../":"";
date_default_timezone_set('Africa/Addis_Ababa');
if(isset($getEmail))
include_once $pos.$first."common/email.php";
$servername = "localhost";
$username = "root";
$password = "";//4hR3XnqZaTcg3hf.

$conn = new mysqli($servername, $username, $password,"project_lpms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn_ws = new mysqli($servername, $username, $password,"ws");
if ($conn_ws->connect_error) {
    die("Connection failed: " . $conn_ws->connect_error);
}

$conn_fleet = new mysqli($servername, $username, $password,"fleet");
if ($conn_fleet->connect_error) {
    die("Connection failed: " . $conn_fleet->connect_error);
}

$conn_pms = new mysqli($servername, $username, $password,"pms_new");
if ($conn_pms->connect_error) {
    die("Connection failed: " . $conn_pms->connect_error);
}

$conn_ais = new mysqli($servername, $username, $password,"vis");
if ($conn_ais->connect_error) {
    die("Connection failed: " . $conn_ais->connect_error);
}

$conn_sms = new mysqli($servername, $username, $password,"sms");
if ($conn_sms->connect_error) {
    die("Connection failed: " . $conn_sms->connect_error);
}

$conn_mrf = new mysqli($servername, $username, $password,"mrf");
if ($conn_mrf->connect_error) {
    die("Connection failed: " . $conn_mrf->connect_error);
}

$dateee = date("Y-m-d",strtotime("+3 day"));
$date_last = date("Y-m-d",strtotime("+3 month"));
$project_name = "LPMS";
$stmt_email = $conn->prepare("INSERT INTO `emails`(`project`, `send_to`, `cc`, `bcc`, `subject`, `data`, `tag`, `company_logo`,`sent_from`,`email_type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)");
$stmt_email_reason = $conn->prepare("INSERT INTO `emails`(`project`, `send_to`, `cc`, `bcc`, `subject`, `data`, `tag`, `company_logo`, `reason`,`sent_from`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
?>
