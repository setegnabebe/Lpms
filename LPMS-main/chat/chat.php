<?php 
session_start();
  include '../connection/connect.php';
  include '../common/functions.php';
  $sql = "SELECT * FROM Account WHERE unique_id = ?";
  $stmt_account_by_unique = $conn->prepare($sql);
  $stmt_account_by_unique -> bind_param("i", $_GET['user_id']);
  $stmt_account_by_unique -> execute();
  $result_account_by_unique = $stmt_account_by_unique -> get_result();
  if(mysqli_num_rows($result_account_by_unique) > 0){
    $row = mysqli_fetch_assoc($result_account_by_unique);
    $uname = str_replace("."," ",$row['Username']);
  }
        ?>
 <div class="wrapper"><section class="chat-area"> <header>
    <button type="button"  onclick="chat_box('<?php echo $_GET['data'] ?>')" class="back-icon btn"><i class="fas fa-arrow-left"></i></button>
    <img src="<?= $_GET['pos']?>../chat/images/avatar.png" alt="">
     <div class="details">
       <span><?php echo $uname  ?></span>
       <p  style="font-size:12px;color:darkgray;"><?php echo  $row['user_status']; ?></p>
     </div>
   </header>
   <div class="chat-box" style="padding:10px;font-size:12px;color:darkgray;">
   <?php 
$outgoing_id = $_SESSION['unique_id'];
$incoming_id = mysqli_real_escape_string($conn, $_GET['user_id']);
$output = "";
$sql = "SELECT * FROM messages LEFT JOIN Account ON Account.unique_id = messages.outgoing_msg_id
        WHERE (outgoing_msg_id = ? AND incoming_msg_id = ?)
        OR (outgoing_msg_id = ? AND incoming_msg_id = ?) ORDER BY msg_id";
$stmt_msgs = $conn->prepare($sql);
$stmt_msgs -> bind_param("iiii", $outgoing_id, $incoming_id, $incoming_id, $outgoing_id);
$stmt_msgs -> execute();
$result_msgs = $stmt_msgs -> get_result();
$seen_sql="UPDATE `messages` SET `message_status` = 1 WHERE outgoing_msg_id = ? AND incoming_msg_id = ?";
$stmt_seen_msgs = $conn->prepare($seen_sql);
$stmt_seen_msgs -> bind_param("ii", $incoming_id, $outgoing_id);
$stmt_seen_msgs -> execute();
 
if(mysqli_num_rows($result_msgs) > 0){
    while($row = mysqli_fetch_assoc($result_msgs)){
      if($row['outgoing_msg_id'] === $outgoing_id){
        $output .= '<div class="chat outgoing">
                    <div class="details">
                        <p class="fs-6">'. $row['msg'] .'<br>
                        <span class="text-sm " style="font-size:10;margin-top:4px;margin-bottom:0px">'. getDuration($row['timestamp']).' '.addMark($row['message_status']).'</span>
                        </p>
                        
                    </div>
                      
                    </div>';
    }else{
        $output .= '<div class="chat incoming">
        <img src="'.$_SESSION['pos_chat'].'../chat/images/avatar.png" alt="" style="height: 40px; width: 40px;">
                    <div class="details">
                        <p class="fs-6">'. $row['msg'] .'<br>
                        <span class="text-sm" style="font-size:10;margin-top:4px;margin-bottom:0px">'. getDuration($row['timestamp']).' '.addMark($row['message_status']).'</span>
                        </p>
                        
                    </div>
                    </div>';
    }
    }
}else{
    $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
}
echo $output;
   ?>
  </div>
  <form action="#" class="typing-area">
  <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $incoming_id; ?>" hidden>
<textarea type="text" id='txt_area' name="message" class="input-field w-100" placeholder="Type a message here..." autocomplete="off"></textarea>
  <button><i class="fab fa-telegram-plane"></i></button>
 
</form>
<p style='margin-top: -34px;margin-left: 10%;'><span class='text-primary'>Enter</span> to send, <span class='text-primary'>shift+Enter</span> to move to next line</p>
</section></div>
<?php
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>