<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include '../connection/connect.php';
        $outgoing_id = $_SESSION['unique_id'];
        function random_string($length)
        {
            $str = random_bytes($length);
            $str = base64_encode($str);
            $str = str_replace(["+", "/", "="], "", $str);
            $str = substr($str, 0, $length);
            return $str;
        }
        if(isset($_GET['ids']))
        {
            $ids=explode('__',$_GET['ids']);
            $req_id=$_GET['req_id'];
            $group_id="".random_string(10);//uniqid(rand())
            $message = mysqli_real_escape_string($conn, nl2br($_GET['message']));
            foreach($ids as $id)
            {
                $incoming_id = mysqli_real_escape_string($conn, $id);
                if(!empty($message)){
                    $sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg,req_id,group_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt_add_msg_by_req = $conn->prepare($sql);
                    $stmt_add_msg_by_req -> bind_param("iisii", $incoming_id, $outgoing_id, $message, $req_id, $group_id);
                    $stmt_add_msg_by_req -> execute();
                }
            }
        }
        else
        {
            $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
            $message = mysqli_real_escape_string($conn, nl2br($_POST['message']));
            if(!empty($message))
            {
                $sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES (?, ?, ?)";
                $stmt_add_msg = $conn->prepare($sql);
                $stmt_add_msg -> bind_param("iis", $incoming_id, $outgoing_id, $message);
                $stmt_add_msg -> execute();
            }
        }
    }else{
        header("location: ../login.php");
    }
?>
<?php
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>