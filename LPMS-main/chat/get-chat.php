<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include '../connection/connect.php';
        include '../common/functions.php';
        $outgoing_id = $_SESSION['unique_id'];
        if(isset($_POST['req_id'])){
            $uname = str_replace("."," ",$_SESSION['username']);
            $req_id = $_POST['req_id'];
            $sql="SELECT * from messages where req_id = ? ORDER BY `messages`.`timestamp` DESC LIMIT 1"; //
            $stmt_msgs_request = $conn->prepare($sql);
            $stmt_msgs_request -> bind_param("i", $req_id);
            $stmt_msgs_request -> execute();
            $result_msgs_request = $stmt_msgs_request -> get_result();
            if($result_msgs_request->num_rows>0){
            $messages=$result_msgs_request->fetch_assoc();
            echo '<div class="details border-2 bg-secondary text-light bolder incoming" style="border-radius:5px">
            <p><button class="chat_msg mb-2 btn btn-secondary"  onclick=\'get_user_detail("'.$_SESSION['username'].'")\'>'.$uname.'</button> <br>
            <span style="font-size:15px;margin-left:4px" class="ml-4">'.$messages['msg'].'<br>
            <span class="float-end text-sm text-white text-black" style="background-color:black;font-size:10;right:0;border-radius:5px">'. getDuration($messages['timestamp']).'</span>
            </span></p></div>';
            }
        }else{
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $output = "";
        $sql = "SELECT * FROM messages LEFT JOIN Account ON Account.unique_id = messages.outgoing_msg_id
                WHERE (outgoing_msg_id = ? AND incoming_msg_id = ?)
                OR (outgoing_msg_id = ? AND incoming_msg_id = ?) ORDER BY msg_id";
        $stmt_msgs = $conn->prepare($sql);
        $stmt_msgs -> bind_param("iiii", $outgoing_id, $incoming_id, $incoming_id, $outgoing_id);
        $stmt_msgs -> execute();
        $result_msgs = $stmt_msgs -> get_result();
        $seen_sql="UPDATE `messages` SET `message_status` = 1 WHERE outgoing_msg_id = ? AND incoming_msg_id = ?";
        $stmt_seen_msg = $conn->prepare($seen_sql);
        $stmt_seen_msg -> bind_param("ii", $incoming_id, $outgoing_id);
        $stmt_seen_msg -> execute();
        if(mysqli_num_rows($result_msgs) > 0){
            while($row = mysqli_fetch_assoc($result_msgs)){
                if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div class="chat outgoing">
                                <div class="details">
                                    <p class="fs-6">'. $row['msg'] .'<br>
                                    <span class="text-sm" style="font-size:10;margin-top:4px;right:0;">'. getDuration($row['timestamp']).' '.addMark($row['message_status']).'</span>
                                    </p>
                                    
                                </div>
                                  
                                </div>';
                }else{
                    $output .= '<div class="chat incoming">
                    <img src="'.$_SESSION['pos_chat'].'../chat/images/avatar.png" alt="" style="height: 40px; width: 40px;">
                                <div class="details">
                                    <p class="fs-6">'. $row['msg'] .'<br>
                                    <span class="text-sm" style="font-size:10; margin-top:4px;right:0">'. getDuration($row['timestamp']).' '.addMark($row['message_status']).'</span>
                                    </p>
                                    
                                </div>
                                </div>';
                }
            }
        }else{
            $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
        }
        echo $output;
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