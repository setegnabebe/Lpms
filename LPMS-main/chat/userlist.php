<?php 
  session_start();
  include '../connection/connect.php';
?>
<?php 
$uname = str_replace("."," ",$_SESSION['username']);
//include_once $_GET['pos']."../chat/header.php"; ?>
      <div class="users-list">
            <?php 
                  $outgoing_id = $_SESSION['unique_id'];
                  if(isset($_POST['searchTerm']))
                  {
                      $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);
                      $sql = "SELECT *,max(timestamp) as maxtime FROM account as u
                      left JOIN
                      (
                          SELECT *  FROM messages WHERE (incoming_msg_id = ?
                                  OR outgoing_msg_id = ?)) m ON u.unique_id = m.incoming_msg_id or  u.unique_id = m.outgoing_msg_id  
                      
                     WHERE Not unique_id = ? AND (Username LIKE '%{$searchTerm}%') Group by Username ORDER BY maxtime DESC,Username Asc;";
                  }
                  else
                  {
                   //   $sql = "SELECT * FROM Account WHERE NOT unique_id = {$outgoing_id} ORDER BY unique_id DESC";
                    $sql = "SELECT *,max(timestamp) as maxtime FROM account as u
                    left JOIN
                    (
                        SELECT *  FROM messages WHERE (incoming_msg_id = ?
                                OR outgoing_msg_id = ?)) m ON u.unique_id = m.incoming_msg_id or  u.unique_id = m.outgoing_msg_id  
                    WHERE Not unique_id = ? Group by Username ORDER BY maxtime DESC,Username Asc;";
                  }
                  $stmt_msgs_latest = $conn->prepare($sql);
                  $stmt_msgs_latest -> bind_param("iii", $_SESSION['unique_id'], $_SESSION['unique_id'], $outgoing_id);
                  $stmt_msgs_latest -> execute();
                  $result_msgs_latest = $stmt_msgs_latest -> get_result();
                  $output = "";
                  if(mysqli_num_rows($result_msgs_latest) == 0){
                      $output .= "No Account are available to chat";
                  }elseif(mysqli_num_rows($result_msgs_latest) > 0){
                      while($row = mysqli_fetch_assoc($result_msgs_latest)){
                        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = ?
                                OR outgoing_msg_id = ?) AND (outgoing_msg_id = ? 
                                OR incoming_msg_id = ?) ORDER BY msg_id DESC LIMIT 1";
                        $stmt_msgs_all = $conn->prepare($sql2);
                        $stmt_msgs_all -> bind_param("iiii", $row['unique_id'], $row['unique_id'], $outgoing_id, $outgoing_id);
                        $stmt_msgs_all -> execute();
                        $query2 = $stmt_msgs_all -> get_result();
                        $sql3="SELECT COUNT(*) as count FROM `messages` where outgoing_msg_id=? AND incoming_msg_id=? and message_status=0;";
                        $stmt_msgs_count = $conn->prepare($sql3);
                        $stmt_msgs_count -> bind_param("ii", $row['unique_id'], $outgoing_id);
                        $stmt_msgs_count -> execute();
                        $query3 = $stmt_msgs_count -> get_result();
                        $row2 = mysqli_fetch_assoc($query2);
                        $row3 = mysqli_fetch_assoc($query3);
                    (mysqli_num_rows($query2) > 0) ? $result = $row2['msg'] : $result ="No message available";
                          (strlen($result) > 28) ? $msg =  substr($result, 0, 28) . '...' : $msg = $result;
                          if(isset($row2['outgoing_msg_id'])){
                              ($outgoing_id == $row2['outgoing_msg_id']) ? $you = "You: " : $you = "";
                          }else{
                              $you ="";
                          }
                          ($row['user_status'] == "Offline" || is_null($row['user_status'])) ? $offline = " text-secondary" : $offline = " text-success";
                          ($outgoing_id == $row['unique_id']) ? $hid_me = "hide" : $hid_me = "";
                          $uname = str_replace("."," ",$row['Username']);
                          $output .= '<button type="button" class="btn w-100" onclick="chat_person(this)" name='.$row['unique_id'] .'>
                                      <div class="content">
                                          <img src="'.$_SESSION['pos_chat'].'../chat/images/avatar.png" alt="" style="height: 50px; width: 50px;">
                                          <span class="me-0 status-dot'. $offline .'" style="margin-top:29px;margin-left:-19px;font-size:12px;"><i class="fas fa-circle"></i></span>
                                          <div class="details" style="width:90%;">
                                              <b>'. $uname.'</b>
                                              <p style=" font-size:16px;">'. $you . $msg .'</p>
                                          </div>
                                          <span class="badge rounded-pill badge-notification bg-danger">'.($row3['count']!=0?$row3['count']:'').'</span>
   
                                      </div>
                                  </button>';
                      }
                  }
                  echo $output;
            ?>
      </div>     
<?php
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>