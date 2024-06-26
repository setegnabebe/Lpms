<?php 
  session_start();
  include '../connection/connect.php';
  include '../common/functions.php';
?>
<?php 
$uname = str_replace("."," ",$_SESSION['username']);
if(isset($_GET['req_id'])&&$_GET['req_id']!=0){
    $outgoing_id = $_SESSION['unique_id'];
    $data=[];
    $type=$_GET['type'];
    if($type=='cluster')
        $sql = "SELECT *,cluster.status as status from cluster INNER join purchase_order on purchase_order.cluster_id=cluster.id where id = ? limit 1";
    else
        $sql = "SELECT * from requests where purchase_requisition = ?";
    $stmt_custom_change = $conn->prepare($sql);
    $stmt_custom_change -> bind_param("i", $_GET['req_id']);
    $stmt_custom_change -> execute();
    $result_custom_change = $stmt_custom_change -> get_result();
    $rows = $result_custom_change -> fetch_assoc();
    $cond="";
echo "<div class='wrapper'>
<section class='users'>
  <header> 
  <div class='content w-100'><img src='".$_GET['pos']."../chat/images//avatar.png' id='profile_pic' alt='image preview'>
        <div class='details' style='width:90%'>
          <b>$uname</b>
          <p  style='font-size:12px;color:darkgray;'><i class='fas fa-circle text-success'></i>".$_SESSION['user_status']."</p>
        </div>
        <button type='button' class='btn btn-danger btn-sm border-0 ms-5' data-bs-dismiss='modal'>X</button>
      </div>
</header> 
<div>
<div class='wrapper'><section class='chat-area'> 
<div class='chat-box2' id='chbox' style='padding:10px;font-size:12px;color:darkgray;max-height:200px'>
";
echo '<div class="chat" id="ch_bdy">';
$data_sql="SELECT * FROM messages LEFT JOIN Account ON Account.unique_id = messages.outgoing_msg_id where req_id = ?  group by group_id ORDER BY msg_id";
$stmt_msg_inbound = $conn->prepare($sql);
$stmt_msg_inbound -> bind_param("i", $_GET['req_id']);
$stmt_msg_inbound -> execute();
$result_msg_inbound = $stmt_msg_inbound -> get_result();
if($result_msg_inbound -> num_rows>0)
    while($messages = $result_msg_inbound -> fetch_assoc()){
        $uname=str_replace("."," ",$messages['Username']);
        echo '<div class="details border-2 bg-secondary text-light bolder incoming" style="border-radius:10px">
        <p><button class="chat_msg mb-2 btn btn-secondary"  onclick=\'get_user_detail("'.$messages['Username'].'")\'>'.$uname.'</button> <br>
        <span style="font-size:15px;margin-left:4px" class="ml-4">'.$messages['msg'].'<br>
        <span class="float-end text-sm text-white text-black" style="background-color:black;font-size:10;right:0;border-radius:5px">'. getDuration($messages['timestamp']).'</span>
        </span>
        </p>
    </div>';
    }
echo '</div>';
echo "</div></section></div><div class='input-group'>
  <textarea class='form-control' rows=1 id='text_area' aria-label='With textarea'></textarea>
  <div class='input-group-prepend'>
  <button class='input-group-text text-primary pt-3' name='".$_GET['req_id']."' onclick='send_msg(this)'><i class='fab fa-telegram-plane'></i></button>
</div>
</div>
<div class='text-success text-center mt-2' id='msg'></div>
<input type='text' class='incoming_id' name='incoming_id' value='' hidden>
</div>
<div class='search'>
    <span class='text'>List of users related to this purchase</span>
 </div>";
 echo "<div class='users-list'>";
 if($type=='cluster'){

    $sql_scale_po = "SELECT DISTINCT `scale` FROM `purchase_order` where `cluster_id` = ?";
    $stmt_scale_po = $conn->prepare($sql_scale_po);
    $stmt_scale_po -> bind_param("i", $_GET['req_id']);
    $stmt_scale_po -> execute();
    $result_scale_po = $stmt_scale_po -> get_result();
    if($result_scale_po -> num_rows>0)
        while($row = $result_scale_po -> fetch_assoc())
        {
            $scale = $row['scale'];
        }
        $procurement_company = $rows['procurement_company'];
        $finance_company = $rows['finance_company'];
        $req_company = $rows['company'];
        $ssscale=$scale;
        switch($rows['status']){
            case "Generated":
            case "Approved":
                // $cond="((department='procurement' and role='manager') or  (`role`='Purchase officer'))";
        if($scale != 'Owner' && $scale != 'procurement') 
            $scale .= ' Committee';
        if($scale == 'Owner')
            $cond = "(`department` = 'Owner' OR (`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = '$procurement_company'))";
        else if($scale == 'procurement') 
            $cond = "`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = 'Hagbes HQ.'";
        else if(strpos($scale,"HO")!==false) 
            $cond = "`type` LIKE '%".$scale."%' AND company = 'Hagbes HQ.'";
        else 
            $cond = "((`type` LIKE '%".$scale."%' AND company = '$req_company') OR ((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%')) or `additional_role`=1 ) AND company = '$procurement_company')";

        $stmt_po_cluster -> bind_param("i", $_GET['req_id']);
        $stmt_po_cluster -> execute();
        $result_po_cluster = $stmt_po_cluster -> get_result();
        if($result_po_cluster->num_rows>0)
            while($row = $result_po_cluster->fetch_assoc())
            {
                $req_id = $row["request_id"];
            }
        $stmt_request -> bind_param("i", $req_id);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        // echo $sql;
        if($result_request->num_rows>0)
            while($row = $result_request->fetch_assoc())
            {
                $req_by = $row['customer'];
                $req_com = $row['company'];
                $dep = $row['department'];
                $comp = $row['company'];
                $stmt_account -> bind_param("s", $req_by);
                $stmt_account -> execute();
                $result_account = $stmt_account -> get_result();
                if($result_account -> num_rows > 0)
                    while($row_man = $result_account -> fetch_assoc())
                        $role = $row_man['role'];
            }
            $man_dep = ((strpos($scale,"HO")!==false || $scale == 'Owner') && $req_company != 'Hagbes HQ.' && $dep != 'GM' && $dep != 'Director')?"(department = '$dep' OR department = 'GM' OR department = 'Director')":"department = '$dep'";
            if($dep == "Owner" || $role == "Director")
            {
                $stmt_account_active -> bind_param("s", $req_by);
                $stmt_account_active -> execute();
                $result_account_fetch = $stmt_account_active -> get_result();
            }
            else
            {
                $scale_like = "%$ssscale%";
                $sql_account_fetch = "SELECT * FROM `account` where $man_dep AND company = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role = 'Director') and status = 'active' and type NOT LIKE ? and type NOT LIKE '%Owner%'";
                $stmt_account_fetch = $conn->prepare($sql_account_fetch);
                $stmt_account_fetch -> bind_param("ss", $comp, $scale_like);
                $stmt_account_fetch -> execute();
                $result_account_fetch = $stmt_account_fetch -> get_result();
            }
            $req_dep_man = [];
            $new_sql = "";
            $same = false;
            if($result_account_fetch -> num_rows > 0)
            while($row = $result_account_fetch -> fetch_assoc())
            {
                array_push($req_dep_man,$row['Username']); 
            }
            if(isset($req_dep_man))
            {
                foreach ($req_dep_man as $value_dep) {
                    $cond.= " or Username = '$value_dep'";
                }
            }
            break;
            case "Sent to Finance":
                $cond="(((`department` = 'procurement' or `department` = 'finance') AND (role = 'manager' OR `type` LIke '%manager%') or role='Disbursement')AND company = '$procurement_company')";
                break;
            case "Reviewed":
                $cond="((`department` = 'finance' AND (role = 'manager' OR `type` LIke '%manager%') or role='Disbursement') AND company = '$finance_company')";
                break;
            case "Finance Approved":
            case "Cheque Prepared":
            case "In-Stock":
                $cond="((role='cashier' or   type LIKE '%Cheque Signatory%') and company=(SELECT cheque_company from cheque_info WHERE cluster_id=".$_GET['req_id']." LIMIT 1))";
                break;
        }
 }else
 switch($rows['status']){
    case "waiting":
    $cond="((role='director' and managing like '%".$rows['department']."%') or username='".$rows['customer']."' or (department='".$rows['department']."' and role='manager'))";
    break;
    case "Approved By Dep.Manager":
        $cond="((role='director' and managing like '%".$rows['department']."%') or (department='".$rows['department']."' and role='manager'))";
    break;
    case "Approved By GM":
    if($rows['next_step']=="Store")
        $cond="username='".$rows['customer']."' or ((role='director' and managing like '%".$rows['department']."%') or (department='".$rows['department']."' and role='manager') or role='store')";
    else if($rows['next_step']=="Property")
        $cond="username='".$rows['customer']."' or ((role='director' and managing like '%".$rows['department']."%') or (department='".$rows['department']."' and role='manager') or role='store' or (department='Property' and role='manager'))";
    else if($rows['next_step']=="Performa")
        $cond="((department='".$rows['department']."' and role='manager') or  (department='Property' and role='manager'))";
    else if($rows['next_step']=="Owner")
        $cond="((role='director' and (managing like '%".$rows['department']."%' or managing='All Departments')) and company='".$rows['company']."') or role='Owner' ";
        break;
    case "Approved By Property":
        if($rows['next_step']=="Performa")
        $cond="((department='procurement' and role='manager') or  (department='Property' and role='manager'))";
        break;
    case "Approved By Owner":
        $cond="(role='Owner'or role='store' or (department='Property' and role='manager'))";
        break;
    case "Generating Quote":
        if($rows['next_step']=="Performa")
        $cond="((department='procurement' and role='manager') or  (`role`='Purchase officer'))";
        else if($rows['next_step']=='Comparision Sheet Generation')
        $cond="((department='procurement' and role='manager') or  (`role`='Senior Purchase officer'))";  
        break;
    case "Payment Processed":
        if($rows['next_step']=='Collection')
        $cond="((department='procurement' and role='manager') or  (`role`='Purchase officer'))";  
        break;
    case "Collected-not-comfirmed":
        if($rows['next_step']=="Collection")
        $cond="(`role`='store' or (department='".$rows['department']."' or department='property') and role='manager')";
        else
        $cond="((department='procurement' and role='manager') or `role`='Purchase officer' or (department='".$rows['department']."' and role='manager'))";
        break;
        case "In-Stock":
    $cond="(`role`='store' or (department='".$rows['department']."' or department='property') and role='manager')";
    }
    $cond = ($cond == "")?"":$cond." and ";
    if($type=='cluster')
    {
        $acc_sql="SELECT * from account where $cond status='active'";
        $stmt_account_fetch_2 = $conn->prepare($acc_sql);
        $stmt_account_fetch_2 -> execute();
        $acc_res = $stmt_account_fetch_2 -> get_result();
    }
    else
    {
        $acc_sql="SELECT * from account where $cond company = ? and status='active'";
        $stmt_account_fetch_2 = $conn->prepare($acc_sql);
        $stmt_account_fetch_2 -> bind_param("s", $_SESSION['company']);
        $stmt_account_fetch_2 -> execute();
        $acc_res = $stmt_account_fetch_2 -> get_result();
    }
    while($rows = $acc_res->fetch_assoc())
    {
        $sql3 = "SELECT COUNT(*) as count FROM `messages` where outgoing_msg_id = ? AND incoming_msg_id = ? and message_status=0;";
        $stmt_msg_count = $conn->prepare($sql3);
        $stmt_msg_count -> bind_param("ii", $rows['unique_id'], $outgoing_id);
        $stmt_msg_count -> execute();
        $query3 = $stmt_msg_count -> get_result();
        $row3 = mysqli_fetch_assoc($query3);
        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = ? OR outgoing_msg_id = ?) AND (outgoing_msg_id = ? OR incoming_msg_id = ?) ORDER BY msg_id DESC LIMIT 1";
        $stmt_msgs = $conn->prepare($sql2);
        $stmt_msgs -> bind_param("iii", $rows['unique_id'], $outgoing_id, $outgoing_id);
        $stmt_msgs -> execute();
        $query2 = $stmt_msgs -> get_result();
        $row2 = mysqli_fetch_assoc($query2);
        (mysqli_num_rows($query2) > 0) ? $result = $row2['msg'] : $result ="No message available";
        (strlen($result) > 28) ? $msg =  substr($result, 0, 28) . '...' : $msg = $result;
        if(isset($row2['outgoing_msg_id']))
        {
            ($outgoing_id == $row2['outgoing_msg_id']) ? $you = "You: " : $you = "";
        }
        else
        {
            $you ="";
        }
        $uname = str_replace("."," ",$rows['Username']);
        ($rows['user_status'] == "Offline" || is_null($rows['user_status'])) ? $offline = " text-secondary" : $offline = " text-success";
        if($rows['unique_id']!=$_SESSION['unique_id'])
        {
            array_push($data,$rows['unique_id']);
            echo  '<button type="button" class="btn w-100" onclick="chat_person(this,'.$_GET['req_id'].')"  name='.$rows['unique_id'] .'>
            <div class="content">
                <img src="'.$_GET['pos'].'../chat/images/avatar.png" alt="" style="height: 50px; width: 50px;">
                <span class="me-0 status-dot'. $offline .'" style="margin-top:29px;margin-left:-19px;font-size:12px;"><i class="fas fa-circle"></i></span>
                <div class="details" style="width:90%;">
                    <b>'. $uname.'</b>
                </div>
                <span class="badge rounded-pill badge-notification bg-danger">'.($row3['count']!=0?$row3['count']:'').'</span>
            </div>
            </button>';
        }
    }
 $data=implode('__',$data);
 echo "</div><input id='ids_holder' type='hidden'value='$data'/>"; 
 echo "</div><input id='req_holder' type='hidden'value='".$_GET['req_id']."'/>"; 
}
else{
$_SESSION['pos_chat'] = $_GET['pos'];
$str="";
//include_once $_GET['pos']."../chat/header.php";  
 $str="<div class='wrapper'>
    <section class='users'>
      <header> 
      <div class='content w-100'><img src='".$_GET['pos']."../chat/images//avatar.png' id='profile_pic' alt='image preview'>
            <div class='details' style='width:90%'>
              <b>$uname</b>
              <p  style='font-size:12px;color:darkgray;'><i class='fas fa-circle text-success'></i>".$_SESSION['user_status']."</p>
            </div>
            <button type='button' class='btn btn-danger btn-sm border-0 ms-5' data-bs-dismiss='modal'>X</button>
          </div>
    </header> 
    <div class='search'>
        <span class='text'>Select an user to start chat</span>
        <input type='text' placeholder='Select an user to start chat'>
        <button><i class='fas fa-search'></i></button>
     </div>
      <div class='users-list'>";
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
                   
                  WHERE Not unique_id = ? Group by Username ORDER BY maxtime desc,Username Asc;";
               
                  }

                  $stmt_msgs_timestamp = $conn->prepare($sql);
                  $stmt_msgs_timestamp -> bind_param("iii", $_SESSION['unique_id'], $_SESSION['unique_id'], $outgoing_id);
                  $stmt_msgs_timestamp -> execute();
                  $query = $stmt_msgs_timestamp -> get_result();
                  $output = "";
                  if(mysqli_num_rows($query) == 0){
                      $output .= "No Account are available to chat";
                  }elseif(mysqli_num_rows($query) > 0){
                      while($row = mysqli_fetch_assoc($query)){
                        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = ?
                                OR outgoing_msg_id = ?) AND (outgoing_msg_id = ? 
                                OR incoming_msg_id = ?) ORDER BY msg_id DESC LIMIT 1";
                        $stmt_msgs_chats = $conn->prepare($sql2);
                        $stmt_msgs_chats -> bind_param("iiii", $row['unique_id'], $row['unique_id'], $outgoing_id, $outgoing_id);
                        $stmt_msgs_chats -> execute();
                        $query2 = $stmt_msgs_chats -> get_result();
                        $sql3="SELECT COUNT(*) as count FROM `messages` where outgoing_msg_id=? AND incoming_msg_id = ? and message_status=0;";
                        $stmt_msgs_chats_count = $conn->prepare($sql3);
                        $stmt_msgs_chats_count -> bind_param("ii", $row['unique_id'], $outgoing_id);
                        $stmt_msgs_chats_count -> execute();
                        $query3 = $stmt_msgs_chats_count -> get_result();

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
                  echo  $str.$output."</div> </section></div>";
          
            }
            $conn->close();
            $conn_pms->close();
            $conn_fleet->close();
            $conn_ws->close();
            $conn_ais->close();
            $conn_sms->close();
            $conn_mrf->close();
     
   ?>
 