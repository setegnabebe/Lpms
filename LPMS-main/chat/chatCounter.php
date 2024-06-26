<?php
session_start();
include "../connection/connect.php";
$outgoing_id = (isset($_SESSION['unique_id']))?$_SESSION['unique_id']:0;
$sql="SELECT * FROM messages WHERE message_status=0 and incoming_msg_id = ? GROUP by outgoing_msg_id";
$stmt_msgs_incoming = $conn->prepare($sql);
$stmt_msgs_incoming -> bind_param("i", $outgoing_id);
$stmt_msgs_incoming -> execute();
$result_msgs_incoming = $stmt_msgs_incoming -> get_result();
$row = mysqli_fetch_assoc($result_msgs_incoming);
echo mysqli_num_rows($result_msgs_incoming);
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>