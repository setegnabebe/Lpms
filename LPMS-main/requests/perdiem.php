<?php 
session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "Owner" && $_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "GM" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement" && strpos($_SESSION["a_type"],"Perdiem") === false && strpos($_SESSION["a_type"],"ChequeSignatory") === false) header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
// include "../connection/connect.php";
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Perdiem requests</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Sign perdiem cheque");
    sideactive("perdiemcheque");
</script>
 <div id="main">
   <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Perdiem and travel advance</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve check</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<script>
function btncolor(e){
   e.classList.add("list-group-item-warning");
}

</script>

<?php
if(isset($_POST['travelapproval']) or isset($_POST['modsubmitchange'])){
  $actiontaken = isset($_POST['travelapproval'])?$_POST['travelapproval']:$_POST['modsubmitchange'];  
  $table = isset($_POST['travelapproval'])?'perdiem':'perdiemmodification';
  $table2 = isset($_POST['travelapproval'])?'traveladvance':'tadvancemodification';
  $approvedby = $_SESSION["username"].'::'.date("Y-m-d H:i:s"); 
    if($actiontaken == 'Approved'){
    $checkid = $_POST['checkid'];
        $pediemstq = "SELECT * FROM $table where id = ?";
        $pediemst = $conn_fleet -> prepare($pediemstq);
        $pediemst -> bind_param("i", $checkid);
        $pediemst -> execute();
        $perdiemstres = $pediemst -> get_result();
        $psrow=$perdiemstres -> fetch_assoc();
        $status=$psrow['status'];
        //echo "<input type='text' value='".$status."'>";
        //$checkid = $_POST['checkid'];        
        $full = true;
        $sql = "SELECT * FROM $table2 WHERE perdime_id = ? AND payment_option is not NULL";
        $stmt_traveladvance_payment = $conn_fleet -> prepare($sql);
        $stmt_traveladvance_payment -> bind_param("i", $checkid);
        $stmt_traveladvance_payment -> execute();
        $result_traveladvance_payment = $stmt_traveladvance_payment -> get_result();  
    // settlement signatory
    if($status=="Settlement cheque prepared"){      
        $status = 'Settlement cheque approved';        
     while ($row_chq = $result_traveladvance_payment -> fetch_assoc())
        {
          $mainid = $row_chq['id'];           
          // fetch perdiemsettlement table...
          $s_sql = "SELECT * FROM perdiemsettlement WHERE travel_advanceid = ?";
          $s_stm = $conn_fleet -> prepare($s_sql);
          $s_stm -> bind_param("i", $mainid);
          $s_stm-> execute();
          $s_res = $s_stm -> get_result();  
          $s_row = $s_res -> fetch_assoc();          
$stmt_account -> bind_param("s", $_SESSION['username']);
$stmt_account -> execute();
$result_account = $stmt_account -> get_result();  
$row2 = $result_account -> fetch_assoc();
$chequep =$row2['cheque_percent'];
//echo "<script>alert('".$chequep."')</script>";
$petty = (isset($_POST['pettyexists']) and in_array($mainid,$_POST['pettyexists']));
$cheque = isset($_POST['chequeexists']) && (in_array($mainid,$_POST['chequeexists']));
if($petty) $chequep = 'p_100';
$percent = intval(explode("_",$chequep)[1]);
$priority = explode("_",$chequep)[0];
if(is_null($s_row['cheque_signatory']) || $s_row['cheque_signatory'] =="")
{ 
  $cheque_sig = $_SESSION['username'];
  if($petty || $cheque)
    {
      //echo "<input type='text' value='".$mainid."'>";
      $signatory = "$_SESSION[username]".'::'.date("Y-m-d H:i:s");
      $sql2 = "UPDATE perdiemsettlement SET `cheque_signatory` = ?, `cheque_percent` = ? where travel_advanceid = ?";
      $stmt_update_signatory = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory -> bind_param("ssi", $signatory, $chequep, $mainid);
      $stmt_update_signatory -> execute();
    }
    if($priority != "p" || $percent != 100)
      {
        $full = false;
      }
}
else
{
    if($petty || $cheque)
    {
      $current_percent = floatval(explode("_",$s_row['cheque_percent'])[1]);
      $current_priority = ($priority == "p")?$priority:explode("_",$s_row['cheque_percent'])[0];
      $percent_agg = $percent + $current_percent;
    }
    else
    {
      $current_priority = $priority;
      $percent_agg = $percent;
    }
    if($s_row['cheque_percent'] == "p_100")
    {
      $current_priority = "p";
      $percent_agg = 100;
    }
    if($percent_agg >= 100)
    {
        $percent_agg = 100;
        if($current_priority != "p") 
        {
          $full = false;
        }
    }
    else
    {
        $full = false;
    }
    $cheque_sig = (!is_null($s_row['cheque_signatory']) && $s_row['cheque_signatory'] != "")?$s_row['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");
    $cheque_percent = $current_priority."_".$percent_agg;
    if($s_row['cheque_percent'] != "p_100")
    {
      $sql2 = "UPDATE perdiemsettlement SET `cheque_signatory` = ?, `cheque_percent` = ? where travel_advanceid = ?";
      $stmt_update_signatory = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory -> bind_param("ssi", $cheque_sig, $cheque_percent, $mainid);
      $stmt_update_signatory -> execute();
    }
}
  $cheque_appby = (!is_null($s_row['cheque_signatory']) && $s_row['cheque_signatory'] != "")?$s_row['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");

        }
      if($full)
      {
        $checkapproval = "UPDATE $table SET payment_approved_by = ?, `status` = ? where id = ?";
        $stmt_payment_approval = $conn_fleet -> prepare($checkapproval);
        $stmt_payment_approval -> bind_param("ssi", $cheque_appby, $status, $checkid);
        $query = $stmt_payment_approval -> execute();

        $check = "UPDATE perdiemsettlement SET payment_approval = ? WHERE travel_advanceid = ?";
        $stmt_payment_approval_traveladvance = $conn_fleet -> prepare($check);
        $stmt_payment_approval_traveladvance -> bind_param("si", $actiontaken, $checkid);
        $query2 = $stmt_payment_approval_traveladvance -> execute();
        if($query && $query2){
          $_SESSION['success'] = 'Perdiem request approved successfully';
          }
      }
// perdiem signatory
    } else {
        
      $status = 'Payment approved';      
        while ($row_chq = $result_traveladvance_payment -> fetch_assoc())
        {
          $mainid = $row_chq['id']; 
$stmt_account -> bind_param("s", $_SESSION['username']);
$stmt_account -> execute();
$result_account = $stmt_account -> get_result();  
$row2 = $result_account -> fetch_assoc();
$chequep = $row2['cheque_percent'];
$petty = (isset($_POST['pettyexists']) and in_array($mainid,$_POST['pettyexists']));
$cheque = isset($_POST['chequeexists']) && (in_array($mainid,$_POST['chequeexists']));
if($petty) $chequep = 'p_100';
$percent = intval(explode("_",$chequep)[1]);
$priority = explode("_",$chequep)[0];
if(is_null($row_chq['cheque_signatory']) || $row_chq['cheque_signatory'] =="")
{ 
  $cheque_sig = $_SESSION['username'];
    if($petty || $cheque)
    {
      $signatory = "$_SESSION[username]".'::'.date("Y-m-d H:i:s");
      $sql2 = "UPDATE $table2 SET `cheque_signatory` = ?, `cheque_percent` = ? where id = ?";
      $stmt_update_signatory = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory -> bind_param("ssi", $signatory, $chequep, $mainid);
      $stmt_update_signatory -> execute();
    }
    if($priority != "p" || $percent != 100)
      {
        $full = false;
      }
}
else
{
    if($petty || $cheque)
    {
      $current_percent = floatval(explode("_",$row_chq['cheque_percent'])[1]);
      $current_priority = ($priority == "p")?$priority:explode("_",$row_chq['cheque_percent'])[0];
      $percent_agg = $percent + $current_percent;
    }
    else
    {
      $current_priority = $priority;
      $percent_agg = $percent;
    }
    if($row_chq['cheque_percent'] == "p_100")
    {
      $current_priority = "p";
      $percent_agg = 100;
    }
    if($percent_agg >= 100)
    {
        $percent_agg = 100;
        if($current_priority != "p") 
        {
          $full = false;
        }
    }
    else
    {
        $full = false;
    }
    $cheque_sig = (!is_null($row_chq['cheque_signatory']) && $row_chq['cheque_signatory'] != "")?$row_chq['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");
    $cheque_percent = $current_priority."_".$percent_agg;
    if($row_chq['cheque_percent'] != "p_100")
    {
      $sql2 = "UPDATE $table2 SET `cheque_signatory` = ?, `cheque_percent` = ? where id = ?";
      $stmt_update_signatory = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory -> bind_param("ssi", $cheque_sig, $cheque_percent, $mainid);
      $stmt_update_signatory -> execute();
    }
}
  $cheque_appby = (!is_null($row_chq['cheque_signatory']) && $row_chq['cheque_signatory'] != "")?$row_chq['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");

        }
      if($full)
      {
        $checkapproval = "UPDATE $table SET payment_approved_by = ?, `status` = ? where id = ?";
        $stmt_payment_approval = $conn_fleet -> prepare($checkapproval);
        $stmt_payment_approval -> bind_param("ssi", $cheque_appby, $status, $checkid);
        $query = $stmt_payment_approval -> execute();

        $check = "UPDATE $table2 SET payment_approval = ? WHERE perdime_id = ?";
        $stmt_payment_approval_traveladvance = $conn_fleet -> prepare($check);
        $stmt_payment_approval_traveladvance -> bind_param("si", $actiontaken, $checkid);
        $query2 = $stmt_payment_approval_traveladvance -> execute();
        if($query && $query2){
          $_SESSION['success'] = 'Perdiem request approved successfully';
          }
      }
    }
  } else if($actiontaken == 'Rejected'){ 
    $chec = $_POST['hiddenn'];
    $status = 'Payment rejected';
    $remark = $_POST['reason'];
   
    $checkapproval = "UPDATE $table SET payment_approved_by = ?, `status` = ?, `reject_remark` = ? where id = ?";
    $stmt_payment_rejected = $conn_fleet -> prepare($checkapproval);
    $stmt_payment_rejected -> bind_param("sssi", $approvedby, $status, $remark, $chec);
    $query = $stmt_payment_rejected -> execute();

    $check = "UPDATE $table2 SET payment_approval = ? WHERE perdime_id = ?";
    $stmt_payment_rejected_traveladvance = $conn_fleet -> prepare($check);
    $stmt_payment_rejected_traveladvance -> bind_param("si", $actiontaken, $chec);
    $query2 = $stmt_payment_rejected_traveladvance -> execute();
    if($query && $query2){
      $_SESSION['success'] = 'Perdiem request approved successfully';
    }
  }
  
}
?>
<?php
if(isset($_POST['approvesettlement'])){
  $sid = $_POST['sid'];
  $status = 'closed';
  $full = true;
  $sql = "SELECT p.* FROM traveladvance as t inner join perdiemsettlement as p on t.id = p.travel_advanceid WHERE perdime_id = ? AND p.payment_option is not NULL";
  $stmt_perdiemsettlement_fetch = $conn_fleet -> prepare($sql);
  $stmt_perdiemsettlement_fetch -> bind_param("i", $sid);
  $stmt_perdiemsettlement_fetch -> execute();
  $result_perdiemsettlement_fetch = $stmt_perdiemsettlement_fetch -> get_result();   
  while ($row_chq = $result_perdiemsettlement_fetch -> fetch_assoc()){
      $mainid = $row_chq['id']; 
   
$stmt_account -> bind_param("s", $_SESSION['username']);
$stmt_account -> execute();
$result_account = $stmt_account -> get_result();  
$row2 = $result_account -> fetch_assoc();
$chequep = $row2['cheque_percent'];
$petty = (isset($_POST['pettyexists']) and in_array($mainid,$_POST['pettyexists']));
$cheque = isset($_POST['chequeexists']) && (in_array($mainid,$_POST['chequeexists']));
if($petty) $chequep = 'p_100';
$percent = intval(explode("_",$chequep)[1]);
$priority = explode("_",$chequep)[0];
if(is_null($row_chq['cheque_signatory']) || $row_chq['cheque_signatory'] =="")
{ 
  $cheque_sig = $_SESSION['username'];
    if($petty || $cheque)
    {
      $signatory = "$_SESSION[username]".'::'.date("Y-m-d H:i:s");
      $sql2 = "UPDATE perdiemsettlement SET `cheque_signatory` = ?,`cheque_percent` = ? where id = ?";
      $stmt_update_signatory_perdiemsettlement = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory_perdiemsettlement -> bind_param("ssi", $signatory, $chequep, $mainid);
      $stmt_update_signatory_perdiemsettlement -> execute();
    }
    if($priority != "p" || $percent != 100)
      {
        $full = false;
      }
}
else
{
    if($petty || $cheque)
    {
      $current_percent = floatval(explode("_",$row_chq['cheque_percent'])[1]);
      $current_priority = ($priority == "p")?$priority:explode("_",$row_chq['cheque_percent'])[0];
      $percent_agg = $percent + $current_percent;
    }
    else
    {
      $current_priority = $priority;
      $percent_agg = $percent;
    }
    if($row_chq['cheque_percent'] == "p_100")
    {
      $current_priority = "p";
      $percent_agg = 100;
    }
    if($percent_agg >= 100)
    {
        $percent_agg = 100;
        if($current_priority != "p") 
        {
          $full = false;
        }
    }
    else
    {
        $full = false;
    }
    $cheque_sig = (!is_null($row_chq['cheque_signatory']) && $row_chq['cheque_signatory'] != "")?$row_chq['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");
    $cheque_percent = $current_priority."_".$percent_agg;
    if($row_chq['cheque_percent'] != "p_100")
    {
      $sql2 = "UPDATE perdiemsettlement SET `cheque_signatory` = ?, `cheque_percent` = ? where id = ?";
      $stmt_update_signatory_perdiemsettlement = $conn_fleet -> prepare($sql2);
      $stmt_update_signatory_perdiemsettlement -> bind_param("ssi", $cheque_sig, $cheque_percent, $mainid);
      $stmt_update_signatory_perdiemsettlement -> execute();
    }
}
  $cheque_appby = (!is_null($row_chq['cheque_signatory']) && $row_chq['cheque_signatory'] != "")?$row_chq['cheque_signatory'].",".$_SESSION['username'].'::'.date("Y-m-d H:i:s"):$_SESSION['username'].'::'.date("Y-m-d H:i:s");
}
if($full)
{
  $checkapproval = "UPDATE perdiem SET settlementchequeapp_by = ?, `status` = ? where id = ?";
  $stmt_cheque_approved_perdiemsettlement = $conn_fleet -> prepare($checkapproval);
  $stmt_cheque_approved_perdiemsettlement -> bind_param("ssi", $cheque_appby, $status, $sid);
  $query = $stmt_cheque_approved_perdiemsettlement -> execute();

  $sql = "SELECT p.* FROM traveladvance as t inner join perdiemsettlement as p on t.id = p.travel_advanceid WHERE perdime_id = ? AND p.payment_option is not NULL";
  $stmt_perdiemsettlement_fetch = $conn_fleet -> prepare($sql);
  $stmt_perdiemsettlement_fetch -> bind_param("i", $sid);
  $stmt_perdiemsettlement_fetch -> execute();
  $result_perdiemsettlement_fetch = $stmt_perdiemsettlement_fetch -> get_result();   
  while ($row_chq = $result_perdiemsettlement_fetch->fetch_assoc())
  {
    $mainid = $row_chq['id']; 
    $check = "UPDATE perdiemsettlement SET `status` = ? WHERE id = ?";
    $stmt_update_perdiemsettlement = $conn_fleet -> prepare($check);
    $stmt_update_perdiemsettlement -> bind_param("si", $status, $mainid);
    $query2 = $stmt_update_perdiemsettlement -> execute();
  }
  if($query && $query2){
    $_SESSION['success'] = 'Perdiem request approved successfully';
  }
}
  
}
?>
    <div class="row">
        <ul class="nav nav-tabs d-flex nav-tabs-bordered  shadow  mx-auto mt-3" id="pills-tab" role="tablist">        
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-home" style="width:500px;" class="nav-link w-100 active" id="pills-home-tab" data-bs-toggle="pill" data-target="#home" role="tab" aria-controls="home" aria-selected="false">Perdiem Request Form</a>
          </li>       
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-history" style="width:500px;" class="nav-link w-100" id="pills-history-tab" data-bs-toggle="pill" data-target="#history" role="tab" aria-controls="history" aria-selected="false">Perdiem history</a>
          </li>
        </ul> 
      </div>
        <?php
        $str="";
        $str2="";
        $result="";
        if($_SESSION["role"] != "Disbursement"){  
          //echo $_SESSION['company'];
          if($_SESSION['company'] == 'Hagbes HQ.')            
          $sql_clus =  "SELECT p.*, pm.`status` as mstatus,pm.id as mid  FROM perdiem AS p LEFT JOIN perdiemmodification AS pm ON p.id = pm.perdiemid 
          where (p.company in (SELECT `Name` from comp where cheque_signatory = 0) or p.company in (SELECT `Name` from comp where perdiem = 0) or p.company = ?) and 
          (p.`status` = 'Settlement payment approved' or p.`status` = 'Settlement cheque checked' or p.`status` = 'Cheque reviewed' or pm.`status` = 'Cheque reviewed' 
           or p.`paymentpreparedby` is not null"; 
          else
          $sql_clus =  "SELECT p.*, pm.`status` as mstatus,pm.id as mid  FROM perdiem AS p LEFT JOIN perdiemmodification AS pm ON p.id = pm.perdiemid where  p.company = ? and (p.`status` = 'Cheque reviewed' or pm.`status` = 'Cheque reviewed' or p.`status` = 'Settlement cheque checked' or p.`paymentpreparedby` is not null";                   
          if(($_SESSION["department"] == "Disbursement" and $_SESSION["role"] == "manager") || strpos($_SESSION["a_type"],"Petty Cash Approver") !== false) 
          $sql_clus .= " OR (p.id IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty' AND (cheque_percent != 'p_100' OR cheque_percent is NULL)))";   
          $sql_clus .= " ) ORDER BY dateofrequest desc";         
        }else{          
          $sql_clus =  "SELECT p.*, pm.`status` as mstatus,pm.id as mid  FROM perdiem AS p LEFT JOIN perdiemmodification AS pm ON p.id = pm.perdiemid 
          where (`status` = 'Cheque reviewed' or pm.`status` = 'Cheque reviewed' or `status` = 'Settlement cheque checked' or `paymentpreparedby` is not null) and (company = ?"; 
          if($_SESSION['company'] == 'Hagbes HQ.')  
          $sql_clus .= " or company in (SELECT `Name` from comp where perdiem = 0)";  
          $sql_clus .= " ) ORDER BY dateofrequest desc";     
        }        
        $stmt_perdiem_conditional_fetch = $conn_fleet -> prepare($sql_clus);
        $stmt_perdiem_conditional_fetch -> bind_param("s", $_SESSION["company"]);
        $stmt_perdiem_conditional_fetch -> execute();
        $result_perdiem_conditional_fetch = $stmt_perdiem_conditional_fetch -> get_result(); 
        if($result_perdiem_conditional_fetch -> num_rows>0)
        while($row = $result_perdiem_conditional_fetch -> fetch_assoc())
          {   
            $id = $row['id'];
            $jobid = $row['job_id'];
            $request_date = $row['dateofrequest'];
            $company = $row['company'];
            $role = $row['role'];
            $department =  $row['fromdepartment'];
            $reason = $row['reasonfortrip'];
            $subject = $row['subject'];
            $customer = $row['customer_name'];
            $departuredate = $row['departure_date'];
            $returndate = $row['return_date'];
            $departureplace = $row['departure_place'];
            $destination = $row['destination'];
            $driver = $row['driver'];
            $travellers = $row['travellers'];
            $preparedby = $row['prepared_by'];
            $status = $row['status'];
            $mstatus = $row['mstatus']; 
            $cheque_or_petty1=$row['cheque_reviwedby'];
            $cheque_or_petty2=$row['settlementreviewed_by'];                       
            $tag = ($status == 'Cheque reviewed' || $mstatus == 'Cheque reviewed')?'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">Perdiem Cheque</span>':'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-success shadow border border-success">Settlement Cheque</span>';              
             if($mstatus != '' and $mstatus == 'Cheque reviewed'){              
              $mid = $row['mid']; 
            }else{
              unset($mid);
            }
            if($status == 'Cheque reviewed' || $status == 'Settlement cheque prepared' || $status == 'Settlement cheque checked' || $mstatus != '' && $mstatus == 'Cheque reviewed'){
              if($status == "Cheque reviewed" || $status == "Settlement cheque checked" || $status == "Settlement cheque prepared")
              {
                $sql = "SELECT p.cheque_signatory FROM traveladvance as t inner join perdiemsettlement as p on t.id = p.travel_advanceid WHERE perdime_id = ? AND p.payment_option is not NULL";       
                $stmt_perdiemsettlement_cheque = $conn_fleet -> prepare($sql);
                $stmt_perdiemsettlement_cheque -> bind_param("i", $id);
                $stmt_perdiemsettlement_cheque -> execute();
                $result_perdiemsettlement_cheque = $stmt_perdiemsettlement_cheque -> get_result(); 
                $row_stlmt_chq = $result_perdiemsettlement_cheque -> fetch_assoc();
                //echo "<input type='text' value='".$row_stlmt_chq['cheque_signatory']."'>";                  
              }
              //
              $t_sql = "SELECT * FROM traveladvance WHERE perdime_id = ?";
              $stmt_tadvance_fetch = $conn_fleet -> prepare($t_sql);              
              $stmt_tadvance_fetch -> bind_param("i", $id);
              $stmt_tadvance_fetch -> execute();
              $res_tadvance_fetch = $stmt_tadvance_fetch -> get_result();
              $count=0;
              $is_display_before=false;
              while($tadv_rw = $res_tadvance_fetch -> fetch_assoc()){                
                $perdimeid=$tadv_rw['perdime_id'];                
                $count++;                
                //$chequen=(isset($tadv_rw['payment_option']))?$tadv_rw['cheque_number']:"";
              if(($tadv_rw['payment_option']=='Cheque' and ($id==$perdimeid and $count>1 )and $is_display_before==true) || (isset($row_stlmt_chq['cheque_signatory']) and strpos($row_stlmt_chq['cheque_signatory'],$_SESSION['username']) !== false) || ($tadv_rw['payment_option']=='Petty') || (isset($tadv_rw['cheque_signatory']) and $status != "Settlement cheque prepared" and strpos($tadv_rw['cheque_signatory'],$_SESSION['username']) !== false));
              else {
                $is_display_before=true;                
                  $str.= '           
                  <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
                  <div class="box">
                  <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
                      <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                      <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                      <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

                      <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                      <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
                      <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.'<i class="fa fa-arrow-right"></i> '.str_replace('::',"<i class='fa fa-arrow-right'></i>",$destination).'</i></p>           
                    
                      <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                      <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

                      <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                      <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                      <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>';    

                  if($status == 'Cheque reviewed' || $status='Settlement cheque prepared'|| $status == 'Settlement cheque checked'){
                    $str.=  '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail" value="'.$id.'" data-bs-toggle="modal" data-bs-target="#fullscreenModal">View Detail</button>';   
                  }else if($mstatus == 'Cheque reviewed'){
                    $str .=  '<button class="mx-auto btn btn-outline-success btn-sm mb-3" type="submit" name="approvemodify" value="'.$id.'" title="Modified Request">View Detail</button>';                 
                  }else{
                    $str.=  '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="settlementdetail" value="'.$id.'" data-bs-toggle="modal" data-bs-target="#fullscreenModal">View Detail</button>';               
                  }
                
              $check = "SELECT * FROM traveladvance where perdime_id =  ?"; 
              $stmt_traveladvance = $conn_fleet -> prepare($check);
              $stmt_traveladvance -> bind_param("i", $id);
              $stmt_traveladvance -> execute();
              $result_traveladvance = $stmt_traveladvance -> get_result();
              $roww = $result_traveladvance -> fetch_assoc();
              if(isset($roww['cheque_signatory']))
              {
              if((isset($row_stlmt_chq) && strpos($row_stlmt_chq['cheque_signatory'],$_SESSION['username']) !== false ) || (isset($row_stlmt_chq) && !is_null($row_stlmt_chq['cheque_signatory']) && strpos($row_stlmt_chq['cheque_signatory'],$_SESSION['username']) !== false))
              $str .= "<br><span class='badge bg-success'>Cheque Approved</span>";
            }
              $str .= '</div>
            </div> 
                ';
               }
              
              
            }
          }

                $str2.= '           
                <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
                <div class="box">
                <h3>
                Job Id - '.$row['job_id'].' | '.$request_date.'';          
                if($mstatus != '' and $mstatus == 'Cheque reviewed'){  
                  $str2 .=  '<button type="submit" name="approvemodify" value="'.$id.'" title="Modified Request" class="btn bg-white ms-3 btn-outline-info mx-auto shadow btn-sm"><i class="fas fa-edit"></i></button>';
                      }
                $str2.= '</h3>
                    <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                    <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>
    
                    <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                    <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>
    
                    <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                    <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
                    <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.'<i class="fa fa-arrow-right"></i> '.str_replace('::',"<i class='fa fa-arrow-right'></i>",$destination).'</i></p>           
                  
                    <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                    <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 
    
                    <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                    <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                    <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>                             
    
                  <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="history" value="'.$id.'" data-bs-toggle="modal" data-bs-target="#fullscreenModal">View Detail</button>';   
                  $checker = false;                 
                  if(isset($mid)){
                  $check = "SELECT * FROM tadvancemodification where perdime_id = ?"; 
                  $stmt_tadvancemodification = $conn_fleet -> prepare($check);
                  $stmt_tadvancemodification -> bind_param("i", $mid);
                  $stmt_tadvancemodification -> execute();
                  $result_tadvancemodification = $stmt_tadvancemodification -> get_result();
                  while($roww = $result_tadvancemodification -> fetch_assoc()){
                  if(($roww['cheque_signatory'] != "") && strpos($roww['cheque_signatory'],$_SESSION['username']) !== false)
                  {                    
                  $checker = true;                                 
                  }
                 }
                   }                   
                  if($checker == true)
                  $str2 .= "<br><span class='badge bg-success'>Modification Cheque Approved</span>";
                  $str2 .= '</div>
                            </div> 
                                ';
        } ?>
      <div class="tab-content pt-2" id="myTabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="home-tab">    
      <form method="POST" action="perdiem.php#pills-home">
        <?php 
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no perdiem requests</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>   
    <button data-bs-toggle="modal" id='modal_open' type="button" data-bs-target="#fullscreenModal" class="btn btn-outline-success float-end btn-sm me-5 box shadow d-none">View Detail</button>
    <button data-bs-toggle="modal" id='modal_open2' type="button" data-bs-target="#fullscreenModal2" class="btn btn-outline-success float-end btn-sm me-5 box shadow d-none">View Detail</button>
     </form>
      </div>
      <div class="tab-pane fade show" id="pills-history" role="tabpanel" aria-labelledby="home-tab"> 
      <form method="POST" action="perdiem.php#pills-history">
        <?php 
        if($str2=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no perdiem records</h3>
                </div>
            </div>";
        else
            divcreate($str2);
    ?>   
    <button data-bs-toggle="modal" id='open_history_modal' type="button" data-bs-target="#fullscreenModal3" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
    <button data-bs-toggle="modal" id='modmodal_open' type="button" data-bs-target="#modificationModal" class="btn btn-outline-primary btn-sm shadow box d-none">modification</button>
     </form>

      </div>
      </div>


      <div class="modal fade" id="fullscreenModal2" tabindex="-1">
     <div class="modal-dialog modal-fullscreen">
     <form method="POST" action="perdiem.php#pills-profile"> 
        <div class="modal-content">      
          <div class="modal-header">
             <h2 style="font-family:Gabriola" class="modal-title text-center mx-auto">Settlement Approval</h2>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>          
              <div class="modal-body">          
           <?php 
           if(isset($_POST['settlementdetail'])){
            $sid = $_POST['settlementdetail'];
            $detail = "SELECT * FROM perdiem where id = ?";
            $stmt_perdiem_by_id = $conn_fleet->prepare($detail);  
            $stmt_perdiem_by_id -> bind_param("i", $sid);
            $stmt_perdiem_by_id -> execute();
            $result_perdiem_by_id = $stmt_perdiem_by_id->get_result();
            if($result_perdiem_by_id -> num_rows > 0)
             while($srow = $result_perdiem_by_id->fetch_assoc()){ 
               $pid = $srow['id'];
               $returndate = $srow['return_date'];
               $departuredate =  $srow['departure_date'];
               $meansoftravel = $srow['meansoftravel'];
               $driver = $srow['driver'];
               $vehicle = $srow['vehicle'];
               $st = $srow['status'];
               $buttonname = 'Approve settlement';
               $modification = "SELECT * FROM perdiemmodification where perdiemid = ?";
               $stmt_perdiemmodification = $conn_fleet->prepare($modification);
               $stmt_perdiemmodification -> bind_param("i", $pid);
               $stmt_perdiemmodification -> execute();
               $result_perdiemmodification = $stmt_perdiemmodification->get_result();
               if($result_perdiemmodification -> num_rows > 0){                      
                 $mrow = $result_perdiemmodification -> fetch_assoc();
                 $mid = $mrow['id'];
                 $returndate =  $mrow['return_date'];
               }     
               ?>
               <div class="container mt-3"> 
                    <div class="card-body">
                    <div class="row">
                   <?php
                    if($result_perdiemmodification->num_rows > 0){ ?>
               <input type="hidden" name="modifid" value="<?php echo  $mid ?>">
                 <?php
                   }
                     ?>
                <input type="hidden" value="<?php echo $st ?>" name="identifier">     
                <div class="col-sm-6">
                 <label for="jobid2" class="form-label me-3"><b>Job Id:</b></label>               
                 <span id="jobid2"><?php echo $srow['job_id']   ?></span>                            
               </div>
               <div class="col-sm-6">
                 <label for="inputEmail4" class="form-label me-3"><b>Date of request:</b></label>
                 <span id="requestdate"><?php echo $srow['dateofrequest']   ?></span> 
               </div>
               <div class="col-sm-6">
                 <label for="inputPassword4" class="form-label me-3"><b>From:</b></label>
                 <span  id="from"><?php echo $srow['role']   ?>,<?php echo $srow['company']   ?>,<?php echo $srow['fromdepartment']   ?></span>
               </div>
               <div class="col-sm-10">
                 <label for="inputAddress" class="form-label me-3"><b>Subject:</b></label>
                 <span id="subject"><?php echo $srow['subject']   ?></span>
               </div>
               <div class="col-sm-10">
                 <label for="inputAddress" class="form-label me-3"><b>Customer Name:</b></label>
                 <span id="customername"><?php echo $srow['customer_name']   ?></span>
               </div>
               <div class="col-sm-6 me-1">
                  <label for="inputAddress" class="form-label me-3"><b>Reason For Travel:</b></label>
                 <textarea style = "border-color: #719ECE;box-shadow: 0 0 10px" class="form-control"   rows = "2" id="reasonfortravel" readonly><?php echo $srow['reasonfortrip']  ?></textarea>
                </div>
               <div class="col-sm-6">
               <label for="inputAddress" class="form-label me-3"><b>Travellers:</b></label>
                <span id="travellers"><?php echo $srow['travellers']   ?></span>
                </div>
               <div class="col-sm-6">
                <label for="inputAddress" class="form-label me-3"><b>Driver:</b></label>
               <span id="travellers"><?php echo $srow['driver']   ?></span>
               </div>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Departure Date:</b></label>
                 <span id="departuredate"><?php echo  $departuredate   ?></span>
               </div>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
                 <span id="returndate"><?php echo $returndate   ?></span>
               </div>
               <?php
                 $depart = strtotime($departuredate);
                 $return = strtotime($returndate);
                 $ptotaldate = round(abs($return - $depart) / 86400,2);
               ?>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Days of stay:</b></label>
                 <span id="daysofstay"><?php echo $ptotaldate   ?></span>
               </div>
               <div class="col-sm-12">
                <div class="row mb-3">
                 <div class="col-sm-7">                 
                 <label for="inputDate" class="col-form-label">Departure</label>
                 <div class="input-group mb-3"> 
                 <span class="input-group-text" id="basic-addon4"><i class="ri-checkbox-blank-circle-line"></i></span> 
                   <input name="editdepartplace" onchange="add('editsubject')" value="<?php echo $srow['departure_place'] ?>"  placeholder="Please Write down a specific departure place" type="text" class="form-control values_edit" readonly>
                 </div>
                 </div>             
               </div>
               </div>             
                 <?php 
                 $split = explode('::',$srow['destination']);
                 $split2 = explode('::',$srow['round_distance_km']);
                 $sumofdistance =0;
                 for($i = 0;$i < count($split);$i++){
                 ?> 
                 <div id="outterid2" class="col-12">             
                 <div class="row">
                 <div class="col-sm-7">                 
                 <label for="inputDate" class="col-form-label">Destination</label>
                 <div class="input-group"> 
                 <span class="input-group-text" id="basic-addon4"><i class="ri-user-location-line"></i></span> 
                   <input name="editdestiplace[]" onchange="add('editsubject')" value="<?php echo $split[$i] ?>" placeholder="Please Write down a specific  destination" type="text" class="form-control values_edit" readonly>
                 </div> 
                 </div> 
                 <div  class="col-sm-5">             
                 <label for="inputDate" class="col-form-label">Distance</label>
                 <div class="input-group"> 
                 <input name = "editdistancekm[]" onchange="add('editsubject')" value="<?php echo $split2[$i] ?>" step="any"  id="distance_1" type="Number" class="form-control distancekm values_edit" name="individualkm" readonly>
                 <span class="input-group-text" id="basic-addon4">Km</span>  
                 </div>
                 </div>
                 </div> 
                 </div> 
                 <?php $sumofdistance += $split2[$i];} ?>    
                 <div class="col-sm-4 float-end me-5 mt-2 text-end">
                 <div class="input-group shadow"> 
                 <input id="edittotaldistancekm" value="<?php echo $sumofdistance?>" step="any" type="Number" class="form-control  w-25 text-end  values_edit" readonly>
                 <span class="input-group-text" id="basic-addon4">Total Km</span>  
                 </div>
                 </div>  

                 <div class="col-sm-12 mb-2">
               <fieldset class="row border rounded-3 p-3">
                 <legend class="col-form-label float-none w-auto">Km Calculation</legend>
                 <div class="row">
               <?php               
                 $exp1 = explode(",",$srow['customersite_km']);
                 $exp2 = explode(",",$srow['tripperday']);
                 $exp3 = explode(",",$srow['daysof_stay']);
                 $sumofsitekm = 0;
               for($i = 0;$i < count($exp1);$i++){  ?>                                 
                 <div class="row">
                 <label class="col-sm-2 col-form-label">@Customer site:</label> 
               <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                   <input name="editkm1[]" onchange="add('editsubject')" value="<?php echo $exp1[$i]  ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Km</span>             
                   </div>
                 </div>     
               <div class="col-sm-3">
                 <div class="input-group mb-3"> 
                   <input name="edittripperday[]" onchange="add('editsubject')" value="<?php echo  $exp2[$i] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                 </div>
               </div>
             <div class="col-sm-3">
               <div class="input-group"> 
                 <input name="editdays[]" onchange="add('editsubject')" value="<?php echo  $exp3[$i] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                   <span class="input-group-text" id="basic-addon4">Days</span>             
                 </div>
               </div> 
              </div>                                     
               <?php  
                 $multiple[$i] = ($exp1[$i] *  $exp2[$i] * $exp3[$i]);
                   $sumofsitekm +=  $multiple[$i];}         
               ?>
               <?php
                 if($result_perdiemmodification->num_rows > 0 and ($mrow['customersite_km'] or $mrow['daysof_stay'])){
                   $exp21 = explode(",",$mrow['customersite_km']);
                   $exp22 = explode(",",$mrow['tripperday']);
                   $exp23 = explode(",",$mrow['daysof_stay']);
                   $sumofsitekm = 0;
                   for($i = 0;$i < count($exp21);$i++){
                   ?>
                 <div class="row">
                 <label class="col-sm-2 col-form-label">@Customer site:</label> 
               <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                   <input name="editkm1[]" onchange="add('editsubject')" value="<?php echo $exp21[$i]  ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Km</span>             
                   </div>
                 </div>     
               <div class="col-sm-3">
                 <div class="input-group mb-3"> 
                   <input name="edittripperday[]" onchange="add('editsubject')" value="<?php echo  $exp22[$i] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                 </div>
               </div>
             <div class="col-sm-3">
               <div class="input-group"> 
                 <input name="editdays[]" onchange="add('editsubject')" value="<?php echo  $exp23[$i] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                   <span class="input-group-text" id="basic-addon4">Days</span>             
                 </div>
               </div> 
              </div> 
               <?php 
               $multiple2[$i] = ($exp21[$i] *  $exp22[$i] * $exp23[$i]);
               $sumofsitekm +=  $multiple2[$i];
                   }
                }
               ?>        
                     <?php
           $totalkm =  $sumofsitekm + $sumofdistance;
          ?>
        <div class="col-sm-4">
           <div class="input-group mb-3">              
              <input id="totalkm" value="<?php echo $totalkm  ?>"  onchange ="changevehicle(this,'totalkm')" type="text" aria-describedby="basic-addon4" class="form-control two" readonly>  
                <span class="input-group-text" id="basic-addon4">Total km</span>             
          </div>
        </div>                   
             </div>
               </fieldset>
            </div>

        <div class="col-sm-4 mt-2">
           <label for="inputAddress" class="col-form-label"><b>Prepared By:</b></label>
             <span id="customername"><?php echo $srow['prepared_by']   ?></span>
         </div> 

                </div>              
             </div>
           </div>
            <hr class="col-sm-12">
            <div class="row"> 
            <div class="col-sm-12 card p-3 mt-2">            
                 <div class="card-body">                   
            <?php 
            $allid = $srow['id'];
            $allid2 = isset($mrow['id'])?$mrow['id']:null;
            $netdiff = 0;
            $totaldiff = 0;
            $counterrr = 1;
            if($result_perdiemmodification->num_rows > 0)
            {
              $selectall = "SELECT *,p.id as pid,p.cheque_signatory as pcheque_signatory,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr, p.payment_option as poption,p.receipt as receipt,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?
              UNION ALL SELECT *,p.id as pid,p.cheque_signatory as pcheque_signatory,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr, p.payment_option as poption,p.pcpv_number as pettycpv,p.crv_number as crvnumber from tadvancemodification t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $stmt_all_perdiem = $conn_fleet->prepare($selectall);
              $stmt_all_perdiem -> bind_param("ii", $allid, $allid2);
            }
            else
            {
              $selectall = "SELECT *,p.id as pid,p.cheque_signatory as pcheque_signatory,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $stmt_all_perdiem = $conn_fleet->prepare($selectall);
              $stmt_all_perdiem -> bind_param("i", $allid);
            }
            $stmt_all_perdiem -> execute();
            $result_all_perdiem = $stmt_all_perdiem->get_result();
              if($result_all_perdiem -> num_rows > 0)
                while($allrow = $result_all_perdiem->fetch_assoc()){  
              
                  if(!is_null($allrow['pcheque_signatory']))
                  $signatory = $allrow['pcheque_signatory'];
                  $day =0;
                  $percent = 0;
                  $employee = str_replace(' ', '',$allrow['name_of_employee']);
                  $chequenum =   $allrow['tcheque_number'];
                  $chequenum2 =   $allrow['pcheque_number'];
                  $cpv =  $allrow['tcpv_number'];
                  $cpv2 =  $allrow['pcpv_number'];
                  $baank =  $allrow['tbank'];
                  $baank2 =  $allrow['pbank'];
                  $potion =  $allrow['toption'];
                  $potion2 =  $allrow['poption'];
                  $jvno = $allrow['jvno'];

                  $pcpv2 = $allrow['pettycpv']; 
                  $crvnum2 = $allrow['crvnumber']; 
                                          
                  $split11 = ($allrow['travelreason'] != "")?explode('::',$allrow['travelreason']):"";
                  $split12 = ($allrow['travelrate'] != "")?explode('::',$allrow['travelrate'] ):"";
                  $split13 = ($allrow['traveldays'] != "")?explode('::',$allrow['traveldays'] ):"";
                  $split14 = ($allrow['travelbirr'] != "")?explode('::',$allrow['travelbirr'] ):"";

                  $split21 = ($allrow['settlementreason'] != "")?explode('::',$allrow['settlementreason']):"";
                  $split22 = ($allrow['settlementrate'] != "")?explode('::',$allrow['settlementrate'] ):"";
                  $split23 = ($allrow['settlementdays'] != "")?explode('::',$allrow['settlementdays'] ):"";
                  $split24 = ($allrow['settlementbirr'] != "")?explode('::',$allrow['settlementbirr'] ):"";
                  
                  $daparturedate = date("d-m-Y",strtotime($allrow['actual_departuredate']));
                  $arrivaldate = date("d-m-Y",strtotime($allrow['actual_returndate']));

                  $gethour1 = date("H",strtotime($allrow['actual_departuredate']));
                  $gethour1 += date("i",strtotime($allrow['actual_departuredate']))/60;
                  $gethour2 = date("H",strtotime($allrow['actual_returndate']));
                  $gethour2 += date("i",strtotime($allrow['actual_returndate']))/60;
                                                
                  if($daparturedate == $arrivaldate)
                  {                           
                        if($gethour1 < 7 && $gethour2 > 7)
                        {
                        $percent += 0.10;
                        }
                        else if($gethour1 < 12.5 && $gethour2 > 12.5)
                        {
                        $percent += 0.25;
                        }
                        else if($gethour1 < 20 && $gethour2 > 20)
                        {                                 
                          $percent += 0.25;
                        } 
                        $day += $percent;
                  }
                  else
                  {
                    if($gethour1 < 7)
                    {
                      $percent += 0.10;
                    }
                    if($gethour1 < 12.5)
                    {
                      $percent += 0.25;
                    }
                    if($gethour1 < 20)
                    {                              
                      $percent += 0.25;
                    }
                    if($gethour1 < 24)
                    {
                      $percent += 0.40;
                    }

                  if($gethour2 > 7)
                    {
                      $percent += 0.10;
                    }
                    if($gethour2 > 12.5)
                    {
                      $percent += 0.25;
                    }
                    if($gethour2 > 20)
                    {                             
                      $percent += 0.25;
                    }
                    if($gethour2 > 24)
                    {
                      $percent += 0.40;
                    } 
                    $date_differnece = (new DateTime($daparturedate))->diff(new DateTime($arrivaldate));
                    $date_jumped = intval($date_differnece->format("%a"));
                    $day = $date_jumped - 1;
                    $day += $percent;
                  }                  
                  ?> 
               <input type="hidden" name = 'tid[]' value="<?php echo $allrow['travel_advanceid'] ?>">           
              <div class="col-sm-12 me-2">         
           <fieldset class="row border rounded-3 p-3 mb-5">
             <legend class="col-form-label float-none text-center w-auto"><?php echo $allrow['name_of_employee'] ?></legend>              
             <div class="row">                         
             <div class='col-sm-6 divider text-center fw-bold'>
                    <div class='divider-text'>Travel Advance
                </div>
              </div>            
              
              <div class='col-sm-6 divider text-center fw-bold'>
                    <div class='divider-text'>Settlement of perdiem expense and performance report
                </div>
              </div>             
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Departure date:</b></label>               
                    <input type="text" value="<?php echo date("d-m-Y H:i",strtotime($srow['departure_date'])) ?>" name="departuredate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Departure date:</b></label>               
                    <input type="text" id="depdate_<?php echo $employee ?>" value="<?php echo date("d-m-Y H:i",strtotime($allrow['actual_departuredate']))  ?>" name="actualdeparturedate[]" class="form-control shadow box" readonly>                           
                  </div> 
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Return Date:</b></label>               
                    <input type="text" value="<?php echo date("d-m-Y H:i",strtotime($srow['return_date']))  ?>" name="returndate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Return date:</b></label>               
                    <input type="text" id="arrival_<?php echo $employee ?>" value="<?php echo date("d-m-Y H:i",strtotime($allrow['actual_returndate'])) ?>" name="actualreturndate[]" class="form-control shadow box" readonly>                           
                  </div>
                  </div>
                <div class="row">  
                <div class="col-md-6">
                <?php $split_val = (count($split11) > count($split21)) ? count($split11) : count($split21);?>
                <!----------------------------------------------------------------------------------------------------->
                <?php                
                $totalexpense = 0;
                $Fuelfound = false;
                for ($j = 0;$j < $split_val;$j++) { 
                  if(isset($split11[$j]))
                  {
                  ?> 
                    <div class="row">               
                <!-- <div class="col-md-6"> -->
                <div class="col-md-3">
                  <div class="form-floating shadow box mb-3">
                    <input type="text" value="<?php echo $split11[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
                    <label for="reason">Reason</label>
                  </div>
                </div>
              
                <div class="col-sm-3">
                  <div class="input-group shadow box mb-3">                
                  <input  type="text" value="<?php echo $split12[$j] ?>"   aria-describedby="basic-add" class="form-control" readonly>                               
                    <span class="input-group-text" id="basic-add">Rate</span>             
                   </div>
                  </div>
                  <?php
                  if($split11[$j] == 'Fuel')
                         { 
                          $span = 'Liter';
                         }
                        else 
                        {
                          $span = 'Days';
                        }         
                   ?>
                  <div class="col-sm-3">
                    <div class="input-group shadow box mb-3"> 
                  <input value="<?php echo $split13[$j] ?>" type="text"  aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3">                                                
                      <input value="<?php echo $split14[$j] ?>"  type="text"  aria-describedby="basic-add2" class="form-control" readonly>                                       
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In ETB</label>            
                      </div>
                      </div>
                      <!-- </div> -->
                      </div>
                      <?php $totalexpense += $split14[$j]; } }  ?> 
                    <div class="col-sm-6">
                    <div class="form-floating shadow box input-group mb-3"> 
                    <input value="<?php echo $totalexpense ?>" id="advancepayment_<?php echo $employee ?>" name="totalexpense[]" type="text"  aria-describedby="basic-add2" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add2">Advance Payment</span> 
                    <label for="floatingSelect">In ETB</label>            
                      </div>
                      </div>
                      <div class="row mt-3"> 
                   <?php                     
                    if($chequenum != ""){ ?>                                            
                    <div class="col-sm-6 col-md-4 mb-2">
                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input value="<?php echo  $chequenum  ?>" type="text" name="chequeno" class="form-control" readonly>                           
                    </div> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                    <input value="<?php echo  $baank  ?>" type="text"   name="bank" class="form-control" readonly> 
                  </div> 
                  <?php } if($cpv != ""){ ?>                        
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input value="<?php echo  $cpv  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div> 
                     <?php } ?>                      
                  </div> 
                      </div>
                <div class="col-md-6">
                 <?php 
                      $totalexpense2 = 0;
                      $totalfuel = 0;
                      $fuelset = false;
                 for ($j = 0;$j < $split_val;$j++) { 
                  if(isset($split21[$j]))
                  {                    
                    ?> 
                    <div class="row">                            
                <div class="col-md-3">
                  <div class="form-floating shadow box mb-3">
                    <input type="text" value="<?php echo $split21[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
                    <label for="reason">Reason</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="input-group shadow box mb-3">           
                  <input value="<?php echo $split22[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add" class="form-control" readonly>                                      
                    <span class="input-group-text" id="basic-add">Rate</span>             
                  </div>
                  </div>
                 <?php  
                    if($split21[$j] == 'Fuel')
                         { 
                          $span = 'Liter';
                          $state = '';
                          $function = 'onkeyup="Find_birr(this)"';
                         }
                        else 
                        {
                          $span = 'Days';
                          $state = 'readonly';
                          $function = 'onkeyup="Find_birr(this)"';
                        } 
                        if($split21[$j] == 'Perdiem')
                        {
                          $numofday = $day;
                        }else{
                          $numofday = $split23[$j];
                        }  
                 ?>
                  <div class="col-sm-3">
                    <div class="input-group shadow box mb-3">               
                  <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?>  type="Number" step="any"  aria-describedby="basic-add1" class="form-control" readonly>                                         
                  <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3"> 
                    <input value="<?php echo $split24[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>  
                          <?php 
                         $totalexpense2 += $split24[$j]; 
                          ?>  
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In ETB</label>            
                  </div>
                  </div>
                      <!-- </div> -->
                      </div>
                      <?php
                        if(isset($split21[$j]) and $split21[$j] == 'Fuel'){
                          $totalfuel += (isset($split22[$j]) and isset($split23[$j]))?((float)$split22[$j] * (float)$split23[$j]):0;
                          $fuelset = true;
                          }
                          $amount_iter = $j+1;
                     } }  ?>
               <input type="hidden" name = 'count[]' value="<?php echo  $amount_iter ?>">           
                   <?php  if($fuelset == true AND $driver != null AND $vehicle != null AND $allrow['name_of_employee'] == $driver){
                      $actualdate = '';
                        $actualdate = $allrow['actual_departuredate'];
                      ?>
                      <input type="hidden" name="driver" value="<?php echo $driver  ?>">
                      <input type="hidden" name="vehicle" value="<?php echo $vehicle  ?>"> 
                      <input type="hidden" name="fuel" value="<?php echo  $totalfuel  ?>">
                      <input type="hidden" name="actualdate" value="<?php echo  $actualdate  ?>"> 
                  <?php 
                     }
                    ?> 
                      <div class="col-sm-6">
                      <div class="form-floating shadow box input-group mb-3">     
                        <input value="<?php echo $totalexpense2 ?>" name="currentpayment[]" type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>                      
                      <span class="input-group-text" id="basic-add2">Current Payment</span> 
                      <label for="floatingSelect">In ETB</label>            
                        </div>
                        </div>
                           <?php 
                              $netdiff = $totalexpense2 - $totalexpense;
                              $totaldiff += $netdiff;
                           ?>
                        <div class="col-sm-6">
                      <div class="form-floating shadow box input-group mb-3">                        
                      <input value="<?php echo  $netdiff  ?>" name='netdifference[]'  type="Number"  aria-describedby="basic-add2" class="form-control" readonly>                  
                      <span class="input-group-text" id="basic-add2">Net difference</span> 
                      <label for="floatingSelect">In ETB</label>            
                        </div>
                        </div>
                        <div class="row">
                 <?php                            
                   if($allrow['receipt'] != Null or $allrow['receipt'] != ''){ ?>  
                <div class="col-md-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Reciept or supporting file:</b></label>                                 
                 <ul class="mb-3 list-group">  
                 <?php 
                   $num = 1;
                   $tittle = '';
                   $reciept = explode('::',$allrow['receipt']);
                   for($i = 0;$i < count($reciept);$i++){  ?>            
                <li class="list-group-item d-flex justify-content-between align-items-center">              
                  <span class="btn" data-bs-toggle="popover" data-bs-content="Supporting document or receipt ">File <?php echo $num ?> - <span class="text-primary"><?php echo $reciept[$i]  ?></span></span>
                  <span class="col-sm-3">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $reciept[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $reciept[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                </span>
                </li>  
                  <?php } ?>                                                        
                     </ul><!-- End List Group With Contextual classes -->
                     </div> 
                     <?php  $num++;} ?> 
                     <?php if($allrow['workdone'] != ''){ ?>
                     <div class="col-md-6 mb-2">                 
                    <label for="jobid" class="form-label me-3"><b>Work done:</b></label>               
                    <input type="textarea" row="2" value="<?php echo $allrow['workdone'] ?>"  class="form-control shadow box" readonly>                           
                   </div>
                   <?php } ?>
                     </div> 
            <!--***************************************************************************************************************************************************-->
           <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st == 'Payment processed'){ ?>
            <div class="row">               
                   <?php if($allrow['image'] != ''){
                    $split1 = explode('::',$allrow['description']);
                    $split2 = explode('::',$allrow['image']);
              ?>
              <ul class="list-group">  
                   <?php 
                   $num = 1;
                   $tittle = '';
                   for($i = 0;$i < count($split2);$i++){ ?>  
                <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                <?php if(isset($split1[$i])) $tittle = $split1[$i];?>
                  <span class="btn" data-bs-toggle="popover" title="<?php echo $split1[$i] ?>" data-bs-content="<?php echo $tittle  ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                  <span class="ms-3 col-sm-2">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                </span>
                </li>                                        
                     <?php  $num++;} ?> 
                     </ul><!-- End List Group With Contextual classes -->                  
                 <?php }else{ ?>
                <h3 class="text-center">No supporting documents uploaded</h3>
                 <?php   }  ?>
                   </div>  
                  <?php } ?>
            <!-- ***************************************************************************************************************************************************-->                                              
                    <div class="row mt-3"> 
                       <?php                     
                    if($chequenum2 != '')
                        {                          
                       ?>                                            
                    <div class="col-sm-6 col-md-4 mb-2">
                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input value="<?php echo  $chequenum2  ?>" type="text" class="form-control" readonly>                           
                    </div> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                    <input value="<?php echo  $baank2  ?>" type="text"   name="bank" class="form-control" readonly> 
                   </div> 
                   <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input value="<?php echo  $cpv2  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div>                                    
                    <input type="hidden" name="chequeexists[]" value="<?php echo  $allrow['pid']  ?>">
                    <?php 
                    
                          } 
                       if($pcpv2 != "" and $pcpv2 != 0)
                          { ?> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>PCPV number:</b></label>               
                      <input value="<?php echo  $pcpv2  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div> 
                    <input type="hidden" name="pettyexists[]" value="<?php echo  $allrow['pid']  ?>">
                    <?php }
                      if($crvnum2 != 0){  ?>
                      <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CRV number:</b></label>               
                      <input value="<?php echo  $crvnum2  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                      </div> 
                    <?php  }
                     if($jvno != "")
                          { ?> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>JV:</b></label>               
                      <input value="<?php echo  $jvno  ?>" type="text"   name="jvno" class="form-control" readonly>                           
                    </div> 
                    <?php } ?>                           
                  </div>             
                 </div>
             <!----------------------------------------------------------------------------------------------------->
                </div>
              </fieldset>
              </div>                 
               <?php 
                $counterrr++;}
                 ?>                                
               </div>
             </div>             
            </div> 
            
          <?php  } ?>
          <!-- ///////////////////////////////////////////////////////////////////////////////////// -->   
       <?php    
          }           
           ?>    
             <input type="hidden" name = 'sid' value="<?php echo   $sid ?>">                   
            </div> 
              <!--approval goes here -->                        
               <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                <?php if(!isset($signatory) || strpos($signatory,$_SESSION['username']) === false){ ?>
                  <button type="submit" name='approvesettlement' class="btn btn-success btn-sm"><?php echo $buttonname ?></button>     
                <?php } ?>
              </div>             
            </div> 
            </form> 
        </div> 
      </div> 

     <div class="modal fade" id="fullscreenModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Perdiem/settlement process and travel advance</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
          <form method="POST" action="perdiem.php" class="row">
            <div class="modal-body">                    
              <div class="card">
                <div class="card-body">
             <div class="row">
              <!-- Vertical Form -->          
              <?php 
                  if(isset($_POST['detail'])){
                    $ii = $_POST['detail'];
                    $totalkm = 0;
                  $detail = "SELECT * FROM perdiem where id = ?";
                  $stmt_perdiem = $conn_fleet -> prepare($detail);
                  $stmt_perdiem -> bind_param("i", $ii);
                  $stmt_perdiem -> execute();
                  $result_perdiem = $stmt_perdiem -> get_result();
                  if($result_perdiem->num_rows > 0);
                    while($detailrow = $result_perdiem->fetch_assoc()){ 
                      $stats=$detailrow['status'];               
                        ?>
                <input type="hidden" id="checkid" name='checkid' value="<?php echo $detailrow['id'] ?>">        
                <div class="col-sm-6 mb-2">
                  <label for="jobid2" class="form-label me-3"><b>Job Id:</b></label>                
                  <input id="job" type="text" value="<?php echo $detailrow['job_id']   ?>" class="form-control shadow" readonly>                        
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputEmail4" class="form-label me-3"><b>Date of request:</b></label>
                  <input id="dateofrequest" type="text" value="<?php echo $detailrow['dateofrequest']   ?>" class="form-control shadow" readonly> 
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputPassword4" class="form-label me-3"><b>From:</b></label>
                  <input type="text"  id="from" value="<?php echo $detailrow['role'] ?>, <?php echo $detailrow['company'] ?>, <?php echo $detailrow['fromdepartment']   ?>" class="form-control shadow" readonly>
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Subject:</b></label>
                  <input id="subject" type="text" value="<?php echo $detailrow['subject']   ?>" class="form-control shadow" readonly> 
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Customer Name:</b></label>
                  <input id="customername" type="text" value="<?php echo $detailrow['customer_name']   ?>" class="form-control shadow" readonly>
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Reason For Travel:</b></label>
                 <textarea style = "border-color: #719ECE;box-shadow: 0 0 10px" class="form-control shadow"   rows = "3" id="reasonfortravel" readonly><?php echo $detailrow['reasonfortrip']  ?></textarea>
                </div>            
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Departure Date:</b></label>
                  <span id="departuredate"></span>
                <input id="departuredate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['departure_date']))    ?>" class="form-control shadow" readonly>  
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
                  <input id="returndate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['return_date']))    ?>" class="form-control shadow" readonly>                
                </div>
                <?php 
               $depart = strtotime($detailrow['departure_date']);
               $return = strtotime($detailrow['return_date']);              
               $totaldate = round(abs($return - $depart) / 86400,2);
                ?>                
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Days of stay:</b></label>
                  <input id="totaldays" type="text" value="<?php echo  $totaldate  ?>" class="form-control shadow" readonly>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Departure:</b></label>
                 <input id="Departureplace" type="text" value="<?php echo $detailrow['departure_place']  ?>" class="form-control shadow" readonly>
                </div>
                <?php   
                  $split1 = explode('::',$detailrow['destination']);
                  $split2 = explode('::',$detailrow['round_distance_km']);
                  for($s = 0; $s < count($split1); $s++){
                ?>             
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Destination:</b></label>
                <input id="Destination" type="text" value="<?php echo  $split1[$s]  ?>" class="form-control shadow" readonly> 
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Distance:</b></label>
                  <input id="Distance" type="text" value="<?php echo $split2[$s] ?>" class="form-control shadow" readonly>
                </div>  
                <?php if($s != count($split1) - 1) {?>
                <div class="col-sm-4 mb-2">
                <label for="inputAddress" class="form-label me-3"><b>Departure:</b></label>
                 <input id="Departureplace" type="text" value="<?php echo $split1[$s]  ?>" class="form-control shadow" readonly>             
                </div>
                <?php }
              } ?>
                 
                <div class="col-sm-12 mb-2">
                <fieldset class="row border rounded-3 p-3">
                  <legend class="col-form-label float-none w-auto">Km Calculation</legend>
                  <!-- <div class="row mb-3"> -->
                <label class="col-sm-2 col-form-label">@Customer site:</label> 
               <?php  $explode1 = explode(',',$detailrow['customersite_km']);
                      $explode2 = explode(',',$detailrow['tripperday']);
                      $explode3 = explode(',',$detailrow['daysof_stay']);   
                      for($j=0; $j < count($explode1); $j++){           
               ?>
                <div class="col-sm-3">
                   <div class="input-group mb-3"> 
                    <input value="<?php echo $explode1[$j] ?>" type="Number" aria-describedby="basic-addon4" class="form-control two" readonly>  
                      <span class="input-group-text" id="basic-addon4">Km</span>             
                    </div>
                  </div>     
                <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                    <input value="<?php echo $explode2[$j] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two" readonly>  
                      <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                  </div>
                </div>
              <div class="col-sm-3">
                <div class="input-group"> 
                  <input value="<?php echo $explode3[$j] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two" readonly>  
                    <span class="input-group-text" id="basic-addon4">Days</span>             
                </div>
              </div>
              <div class="col-sm-2"></div>
              <?php } ?>
                </fieldset>
                <fieldset class="row border rounded-3">
                  <legend class="col-form-label float-none w-auto">Invoices and reciepts</legend>
                  
             <?php if($detailrow['image'] != ''){
                    $split1 = explode('::',$detailrow['description']);
                    $split2 = explode('::',$detailrow['image']); 
              ?>
              <ul class="list-group mb-2">  
                   <?php 
                   $num = 1;
                   $tittle = '';
                   for($i = 0; $i < count($split2); $i++){ ?>  
                <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                <?php if(isset($split1[$i])) $tittle = $split1[$i];  ?>
                  <span class="btn" data-bs-toggle="popover" title="Popover title" data-bs-content="<?php echo $tittle  ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                  <span class="ms-2 col-sm-1">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <?php if(strpos($split2[$i],'.pdf')===false){ ?>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                  <?php } ?>
                </span>
                </li>                                        
                     <?php  $num++; } ?> 
                     </ul><!-- End List Group With Contextual classes -->             
             
              <?php }else{ ?>
                <h5 class="text-center">No supporting documents uploaded</h5>
                <?php   } ?>
                </fieldset>
             <div class="row mt-2">
             <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Prepared By:</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($detailrow['dateofrequest'])) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="preparedby" type="text" value="<?php echo $detailrow['prepared_by']   ?>" class="form-control shadow" readonly>
                </div> 
              
                <?php if($detailrow['travel_approved_by'] != Null){ 
                    $split = explode('::',$detailrow['travel_approved_by']);
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Travel approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div> 
            
              <?php  } ?>
              <?php if($detailrow['request_approved_by'] != Null){ 
                    $split = explode('::',$detailrow['request_approved_by']);
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Request approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>           
              <?php  } ?>  
              <?php if($detailrow['finance_approved_by'] != Null){ 
                    $split = explode('::',$detailrow['finance_approved_by']);
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Finance approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>           
              <?php  } 
                  if($detailrow['senior_accountant'] != Null){
                    $split = explode('::',$detailrow['senior_accountant']);
                    $by = $split[0];
                    $date = $split[1];  ?>
                <div class="col-sm-4 mb-2">
                 <label for="inputAddress" class="form-label me-3"><b>Travel advance filled by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>    
               <?php   
                  } if($detailrow['paymentpreparedby'] != Null){
                    $split = explode('::',$detailrow['paymentpreparedby']);
                    $by = $split[0];
                    $date = $split[1]; 
                ?> 
              <div class="col-sm-4 mb-2">
                 <label for="inputAddress" class="form-label me-3"><b>Cheque Prepared by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>  
                <?php } ?>
                </div> 
                 <?php 
                 $signatory="";
                $traveladvance = "SELECT * from traveladvance where perdime_id = ?";                  
                if(($detailrow['status'] != 'Cheque reviewed') and ($detailrow['status'] != 'Settlement cheque prepared' ))
                $traveladvance .= " and payment_option != 'Cheque'";
                $stmt_traveladvance = $conn_fleet -> prepare($traveladvance);
                $stmt_traveladvance -> bind_param("i", $ii);
                $stmt_traveladvance -> execute();
                $result_traveladvance = $stmt_traveladvance -> get_result();    
                   if($result_traveladvance->num_rows > 0);
                    while($travelrow = $result_traveladvance->fetch_assoc()){

                      $idd = $travelrow['id'];
                      $reason = $travelrow['reason'];
                      $rate = $travelrow['rate'];
                      $days = $travelrow['days'];
                      $totalcost = $travelrow['birr'];  
                      $signatory .= ($travelrow['payment_option'] != 'Petty')?$travelrow['cheque_signatory']:"";
                      $paymentmethod = $travelrow['payment_option']; 
                      $perdiemsettlement = "SELECT * from perdiemsettlement where travel_advanceid = ?"; 
                      $stmt_perdiemsettlement = $conn_fleet -> prepare($perdiemsettlement);                      
                      $stmt_perdiemsettlement -> bind_param("i", $idd);
                      $stmt_perdiemsettlement -> execute();                       
                      $result_settlement = $stmt_perdiemsettlement -> get_result();  
                      $perdiem_settlement=false;                       
                      $strow = $result_settlement->fetch_assoc();                    
                      
                        $chqnumber=$travelrow['cheque_number'];
                          $cpvnmbr=$travelrow['cpv_number'];
                          $bank=$travelrow['bank'];
                          $rate=$travelrow['rate'];
                          $dy=$travelrow['days'];
                          $totalcost=$travelrow['birr'];
                          $perdiem_or_setlement="perdiem";                     
                      
                    
                  ?>            
                <div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
                <?php if($result_settlement->num_rows > 0 and $strow['payment_option'] !="Paid in Cheque"); //$ps=true;// perdiem settlement have data for this perdiem request?
                //if(is_null($strow['payment_option']) and isset($strow['payment_option']));
                else if(isset($strow['payment_option']) and $strow['payment_option']=='Paid in Cheque')
                {                  
                    $chqnumber=$strow['cheque_number'];
                    $cpvnmbr=$strow['cpv_number'];
                    $bank=$strow['bank'];
                    $rate=$strow['rate'];
                    $totalcost=$strow['birr']; 
                    $dy=explode('::',$strow['days'])[0];   
                    $req_day=$strow['daysby_requester']; 
                    $setled_day= (int)$req_day-(int)$dy;                                       
                    
                   ?> <div class="card-body">
              <h5 class="card-title mt-3 mb-3 text-center">Settlement Payment</h5>
              <div class="row mb-2">              
                <div class="col-sm-6 mb-2">
                  <label for="nameofemployee" class="form-label me-3"><b>Name of Employee:</b></label>               
                 <span id="nameofemployee"><?php echo $travelrow['name_of_employee']   ?></span>                         
              </div>
               
              <div class="col-sm-6 mb-2">               
                <label for="inputEmail" class="col-form-label"><b>Role in this trip:</b></label>
                  <span id="roleinthistrip"><?php echo $travelrow['roleonthistrip']   ?></span>   
                 </div> 
              </div> 
                 
                 <div class="col-sm-12 mb-2" >
                <fieldset class="row border rounded-3 p-3">
                  <legend class="col-form-label float-none w-auto">Amount Required</legend>                                
                    <?php $split1 = explode('::',$reason);
                          $split2 = explode('::',$rate);
                          $split3 = explode('::',$dy);
                          $split4 = explode('::',$travelrow['birr']);                       
                ?>
              <div class="row mb-2"> 
                <div class="col-md-3" style="width:40%">
                  <div class="form-floating mb-3">
                    <input value="Total perdiem settlement payment"  type="text"  class="form-control" id="reason" aria-label="State" readonly>                 
                    <label for="reason">Reason</label>
                  </div>
                </div>
                  <div class="col-sm-3">
                    <div class="input-group mb-3"> 
                  <input value="<?php echo  -($setled_day) ?>"  type="text" aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1">Settled days</span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating input-group mb-3"> 
                    <input value="<?php echo $totalcost ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>  
                    <!--<input value="<?php //echo (isset($strow['cheque_number']))? $total : $split4[$j] ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>-->  
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In Birr</label>            
                  </div>
                  </div>
                 </div>                 
                </fieldset>
                </div>                 
                  <input type="hidden" name="chequeexists[]" value="<?php echo  $travelrow['id']  ?>">
                  <div class="row mt-3"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>                          
                       <input type="text" value="<?php echo $chqnumber ?>"  class="form-control" readonly>                           
                        </div> 
                        <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cpv Number:</b></label>               
                       <input type="text" value="<?php echo $cpvnmbr  ?>"  class="form-control" readonly>                           
                        </div> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                      <input type="text" value="<?php echo  $bank  ?>"  class="form-control" readonly>  
                        </div>              
                   </div>                   
                     
                  
                
              </div>
           </div>                       
                <?php                   
                }else{?>
                  <div class="card-body">
              <h5 class="card-title mt-3 mb-3 text-center">Travel Advance</h5>
              <div class="row mb-2">              
                <div class="col-sm-6 mb-2">
                  <label for="nameofemployee" class="form-label me-3"><b>Name of Employee:</b></label>               
                 <span id="nameofemployee"><?php echo $travelrow['name_of_employee']   ?></span>                         
              </div>
               
              <div class="col-sm-6 mb-2">               
                <label for="inputEmail" class="col-form-label"><b>Role in this trip:</b></label>
                  <span id="roleinthistrip"><?php echo $travelrow['roleonthistrip']   ?></span>   
                 </div> 
              </div> 
                 
                 <div class="col-sm-12 mb-2">
                <fieldset class="row border rounded-3 p-3">
                  <legend class="col-form-label float-none w-auto">Amount Required</legend>                                
                    <?php $split1 = explode('::',$reason);
                          $split2 = explode('::',$rate);
                          $split3 = explode('::',$dy);
                          $split4 = explode('::',$travelrow['birr']);                        
                        for($j = 0;$j < count($split1); $j++){ //echo"<script>alert('".$split2[$j].$split4[$j].$perdiem_settlement."')</script>";
                    ?>
              <div class="row mb-2"> 
                <div class="col-md-3">
                  <div class="form-floating mb-3">
                    <input value="<?php echo $split1[$j] ?>"  type="text"  class="form-control" id="reason" aria-label="State" readonly>                 
                    <label for="reason">Reason</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                  <input value="<?php echo $split2[$j] ?>"  type="text"  aria-describedby="basic-add" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add">Rate</span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="input-group mb-3"> 
                  <input value="<?php echo $split3[$j] ?>"  type="text" aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1">Days</span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating input-group mb-3"> 
                    <input value="<?php echo $split4[$j] ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>  
                    <!--<input value="<?php //echo (isset($strow['cheque_number']))? $total : $split4[$j] ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>-->  
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In Birr</label>            
                  </div>
                  </div>
                 </div>
                 <?php } ?>

                </fieldset>
                </div> 
                <?php if($travelrow['payment_option'] != 'Petty'){ ?>
                <?php if($detailrow['status'] == 'Cheque reviewed' || $detailrow['status'] == 'Settlement cheque prepared'){ ?>
                  <input type="hidden" name="chequeexists[]" value="<?php echo  $travelrow['id']  ?>">
                  <?php } ?>
                <div class="row mt-3"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>                          
                       <input type="text" value="<?php echo $chqnumber ?>"  class="form-control" readonly>                           
                        </div> 
                        <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cpv Number:</b></label>               
                       <input type="text" value="<?php echo $cpvnmbr  ?>"  class="form-control" readonly>                           
                        </div> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                      <input type="text" value="<?php echo  $bank  ?>"  class="form-control" readonly>  
                        </div>              
                   </div>
                   <?php }else if($travelrow['cheque_percent'] != 'p_100' && (($_SESSION["department"] == "Disbursement" and 
                   $_SESSION["role"] == "manager") || strpos($_SESSION["a_type"],"Petty Cash Approver") !== false)){ 
                      $petty_on = true;                      
                    ?>
                      <input type="hidden" name="pettyexists[]" value="<?php echo  $travelrow['id']  ?>">
                  <?php } ?>
                
              </div>
           </div>                       
                <?php  
                   }   
                      $status = $detailrow['status'];                  
                 }
               }  } 
                 ?>
            </div>
          </div>
        </div>
      </div> 
    
              <div class="modal-footer">
              <?php  if((!(isset($travelrow['cheque_signatory']) and strpos($travelrow['cheque_signatory'],$_SESSION['username']) !== false) and ($status  == 'Cheque reviewed' || $status == 'Settlement cheque prepared' || $status='Settlement payment approved'|| $status == 'Settlement cheque checked')) or (isset($petty_on))){  
                ?>
                <button type="button" onclick="prompt_confirmation(this)" name="travelapproval" id="approved" value="Approved" class="btn btn-outline-success" >Approve <i class="fas fa-check-circle"></i></button>
                <button type="button" onclick="writeReason(this)"  name="reject" id="reqrejected"  class="btn btn-outline-danger" >Reject <i class="bi bi-x-circle"></i></button>             
                   <?php } else { ?>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>             
                   <?php    } ?> 
              </div>         
             </div>
            </form><!-- Vertical Form -->    
          </div>
        </div><!-- End Full Screen Modal-->
        </div>

        <div class="modal fade" id="fullscreenModal3" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content p-3">
                    <div class="modal-header col-12 text-center">
                      <h6 class="modal-title">Perdiem History</h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>  
                    <div class="modal-body">          
           <?php 
           if(isset($_POST['history'])){
            $sid = $_POST['history'];
            $query = "SELECT * from perdiem where id = ?";
            $stmt_perdiem = $conn_fleet -> prepare($query);
            $stmt_perdiem -> bind_param("i", $sid);
            $stmt_perdiem -> execute();
            $result_perdiem = $stmt_perdiem -> get_result();
            if($result_perdiem -> num_rows > 0)
             while($srow = $result_perdiem->fetch_assoc()){ 
               $pid = $srow['id'];
               $returndate = $srow['return_date'];
               $departuredate =  $srow['departure_date'];
               $meansoftravel = $srow['meansoftravel'];
               $driver = $srow['driver'];
               $vehicle = $srow['vehicle'];
               $st = $srow['status'];
               $buttonname = 'Approve settlement';                                        
               $modification = "SELECT * FROM perdiemmodification where perdiemid = ?";       
               $stmt_perdiemmodification = $conn_fleet -> prepare($modification);
               $stmt_perdiemmodification -> bind_param("i", $pid);
               $stmt_perdiemmodification -> execute();
               $result_perdiemmodification = $stmt_perdiemmodification -> get_result();
               if($result_perdiemmodification->num_rows > 0){                      
                 $mrow = $result_perdiemmodification->fetch_assoc();                                
                 $mid = $mrow['id']; 
                 $returndate =  $mrow['return_date'];                   
               }                 
               ?>
               <div class="container mt-3"> 
                    <div class="card-body">
                    <div class="row">
                   <?php
                    if($result_perdiemmodification->num_rows > 0){ ?>
               <input type="hidden" name="modifid" value="<?php echo  $mid ?>">
                 <?php
                   }
                     ?>
                <div class="col-sm-6">
                 <label for="jobid2" class="form-label me-3"><b>Job Id:</b></label>               
                 <span id="jobid2"><?php echo $srow['job_id']   ?></span>                            
               </div>
               <div class="col-sm-6">
                 <label for="inputEmail4" class="form-label me-3"><b>Date of request:</b></label>
                 <span id="requestdate"><?php echo $srow['dateofrequest']   ?></span> 
               </div>
               <div class="col-sm-6">
                 <label for="inputPassword4" class="form-label me-3"><b>From:</b></label>
                 <span  id="from"><?php echo $srow['role']   ?>,<?php echo $srow['company']   ?>,<?php echo $srow['fromdepartment']   ?></span>
               </div>
               <div class="col-sm-10">
                 <label for="inputAddress" class="form-label me-3"><b>Subject:</b></label>
                 <span id="subject"><?php echo $srow['subject']   ?></span>
               </div>
               <div class="col-sm-10">
                 <label for="inputAddress" class="form-label me-3"><b>Customer Name:</b></label>
                 <span id="customername"><?php echo $srow['customer_name']   ?></span>
               </div>
               <div class="col-sm-6 me-1">
                  <label for="inputAddress" class="form-label me-3"><b>Reason For Travel:</b></label>
                 <textarea style = "border-color: #719ECE;box-shadow: 0 0 10px" class="form-control"   rows = "2" id="reasonfortravel" readonly><?php echo $srow['reasonfortrip']  ?></textarea>
                </div>
               <div class="col-sm-6">
               <label for="inputAddress" class="form-label me-3"><b>Travellers:</b></label>
                <span id="travellers"><?php echo $srow['travellers']   ?></span>
                </div>
               <div class="col-sm-6">
                <label for="inputAddress" class="form-label me-3"><b>Driver:</b></label>
               <span id="travellers"><?php echo $srow['driver']   ?></span>
               </div>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Departure Date:</b></label>
                 <span id="departuredate"><?php echo  $departuredate   ?></span>
               </div>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
                 <span id="returndate"><?php echo $returndate   ?></span>
               </div>
               <?php
                 $depart = strtotime($departuredate);
                 $return = strtotime($returndate);              
                 $ptotaldate = round(abs($return - $depart) / 86400,2);
               ?>
               <div class="col-sm-4">
                 <label for="inputAddress" class="form-label me-3"><b>Days of stay:</b></label>
                 <span id="daysofstay"><?php echo $ptotaldate   ?></span>
               </div>
               <div class="col-sm-12">
                <div class="row mb-3">
                 <div class="col-sm-7">                 
                 <label for="inputDate" class="col-form-label">Departure</label>
                 <div class="input-group mb-3"> 
                 <span class="input-group-text" id="basic-addon4"><i class="ri-checkbox-blank-circle-line"></i></span> 
                   <input name="editdepartplace" onchange="add('editsubject')" value="<?php echo $srow['departure_place'] ?>"  placeholder="Please Write down a specific departure place" type="text" class="form-control values_edit" readonly>
                 </div>
                 </div>             
               </div>
               </div>             
                 <?php 
                 $split = explode('::',$srow['destination']);
                 $split2 = explode('::',$srow['round_distance_km']);
                 $sumofdistance =0;
                 for($i = 0; $i < count($split); $i++){
                 ?> 
                 <div id="outterid2" class="col-12">             
                 <div class="row">
                 <div class="col-sm-7">                 
                 <label for="inputDate" class="col-form-label">Destination</label>
                 <div class="input-group"> 
                 <span class="input-group-text" id="basic-addon4"><i class="ri-user-location-line"></i></span> 
                   <input name="editdestiplace[]" onchange="add('editsubject')" value="<?php echo $split[$i] ?>" placeholder="Please Write down a specific  destination" type="text" class="form-control values_edit" readonly>
                 </div> 
                 </div> 
                 <div  class="col-sm-5">             
                 <label for="inputDate" class="col-form-label">Distance</label>
                 <div class="input-group"> 
                 <input name = "editdistancekm[]" onchange="add('editsubject')" value="<?php echo $split2[$i] ?>" step="any"  id="distance_1" type="Number" class="form-control distancekm values_edit" name="individualkm" readonly>
                 <span class="input-group-text" id="basic-addon4">Km</span>  
                 </div>
                 </div>
                 </div> 
                 </div> 
                 <?php $sumofdistance += $split2[$i]; } ?>    
                 <div class="col-sm-4 float-end me-5 mt-2 text-end">
                 <div class="input-group shadow"> 
                 <input id="edittotaldistancekm" value="<?php echo $sumofdistance?>" step="any" type="Number" class="form-control  w-25 text-end  values_edit" readonly>
                 <span class="input-group-text" id="basic-addon4">Total Km</span>  
                 </div>
                 </div>  

                 <div class="col-sm-12 mb-2">
               <fieldset class="row border rounded-3 p-3">
                 <legend class="col-form-label float-none w-auto">Km Calculation</legend>
                 <div class="row">
               <?php               
                 $exp1 = explode(",",$srow['customersite_km']);
                 $exp2 = explode(",",$srow['tripperday']);
                 $exp3 = explode(",",$srow['daysof_stay']);
                 $sumofsitekm = 0; 
               for($i = 0;$i < count($exp1); $i++){  ?>                                 
                 <div class="row">
                 <label class="col-sm-2 col-form-label">@Customer site:</label> 
               <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                   <input name="editkm1[]" onchange="add('editsubject')" value="<?php echo $exp1[$i]  ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Km</span>             
                   </div>
                 </div>     
               <div class="col-sm-3">
                 <div class="input-group mb-3"> 
                   <input name="edittripperday[]" onchange="add('editsubject')" value="<?php echo  $exp2[$i] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                 </div>
               </div>
             <div class="col-sm-3">
               <div class="input-group"> 
                 <input name="editdays[]" onchange="add('editsubject')" value="<?php echo  $exp3[$i] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                   <span class="input-group-text" id="basic-addon4">Days</span>             
                 </div>
               </div> 
              </div>                                     
               <?php  
                 $multiple[$i] = ($exp1[$i] *  $exp2[$i] * $exp3[$i]);
                   $sumofsitekm +=  $multiple[$i];  }                     
               ?>
               <?php
                 if($result_perdiemmodification->num_rows > 0 and ($mrow['customersite_km'] or $mrow['daysof_stay'])){
                   $exp21 = explode(",",$mrow['customersite_km']);
                   $exp22 = explode(",",$mrow['tripperday']);
                   $exp23 = explode(",",$mrow['daysof_stay']);
                   $sumofsitekm = 0;
                   for($i = 0;$i < count($exp21); $i++){
                   ?>
                 <div class="row">
                 <label class="col-sm-2 col-form-label">@Customer site:</label> 
               <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                   <input name="editkm1[]" onchange="add('editsubject')" value="<?php echo $exp21[$i]  ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Km</span>             
                   </div>
                 </div>     
               <div class="col-sm-3">
                 <div class="input-group mb-3"> 
                   <input name="edittripperday[]" onchange="add('editsubject')" value="<?php echo  $exp22[$i] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                     <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                 </div>
               </div>
             <div class="col-sm-3">
               <div class="input-group"> 
                 <input name="editdays[]" onchange="add('editsubject')" value="<?php echo  $exp23[$i] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two values_edit" readonly>  
                   <span class="input-group-text" id="basic-addon4">Days</span>             
                 </div>
               </div> 
              </div> 
               <?php 
               $multiple2[$i] = ($exp21[$i] *  $exp22[$i] * $exp23[$i]);
               $sumofsitekm +=  $multiple2[$i];
                   }
                }
               ?>        
                     <?php
           $totalkm =  $sumofsitekm + $sumofdistance;                         
          ?>
        <div class="col-sm-4">
           <div class="input-group mb-3">              
              <input id="totalkm" value="<?php echo $totalkm  ?>"  onchange ="changevehicle(this,'totalkm')" type="text" aria-describedby="basic-addon4" class="form-control two" readonly>  
                <span class="input-group-text" id="basic-addon4">Total km</span>             
          </div>
        </div>                   
             </div>
               </fieldset>
            </div>

            <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Prepared By:</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($srow['dateofrequest'])) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="preparedby" type="text" value="<?php echo $srow['prepared_by']   ?>" class="form-control shadow" readonly>
                </div> 
                <?php 
                  $statuss = (strpos($srow['status'],'rejected') === false)?'approved':'rejected';                 
                  if($srow['checked_by'] != Null){ 
                    $split = explode('::',$srow['checked_by']);                                                        
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Checked by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>            
              <?php  }else if($srow['status'] == 'Requested'){  ?>
                <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Department manager approval :</b></label><a href="#" data-toggle="tooltip" title="Waiting for department manager approval"  class="bi bi-info-circle text-primary"></a><br>                
                   <div class="spinner-border text-primary" role="status">
                   <span class="visually-hidden">Loading...</span>
                   </div>
                 </div>
               <?php } if($srow['travel_approved_by'] != Null){ 
                    $split = explode('::',$srow['travel_approved_by']);
                       $by = $split[0];
                       $date = $split[1];                  
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Travel <?php echo $statuss ?> by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>            
              <?php  }else if($srow['status'] == 'Request checked'){ $waiting = ($com == 'Hagbes HQ.')?"Waiting for department director approval":"Waiting for General manager approval";?>
                   <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Travel approval :</b></label><a href="#" data-toggle="tooltip" title="<?php echo  $waiting ?>"  class="bi bi-info-circle text-primary"></a><br>                
                   <div class="spinner-border text-primary" role="status">
                   <span class="visually-hidden">Loading...</span>
                   </div>
                 </div>
               <?php   }  if($srow['request_approved_by'] != Null){ 
                    $split = explode('::',$srow['request_approved_by']);
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Request <?php echo $statuss ?> by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>           
              <?php  } else if($srow['status'] == 'Travel approved'){ ?>
                <div class="col-sm-4 mb-2">
                <label for="inputAddress" class="form-label me-3"><b>Request approval :</b></label><a href="#" data-toggle="tooltip" title="Waiting for Operation manager approval"  class="bi bi-info-circle text-primary"></a><br>                
                <div class="spinner-border  text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
                </div>
              </div>
                <?php }   if($srow['finance_approved_by'] != Null){ 
                    $split = explode('::',$srow['finance_approved_by']);
                       $by = $split[0];
                       $date = $split[1];
                  ?>
                  <div class="col-sm-4 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Finance <?php echo $statuss ?> by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                  <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                </div>           
              <?php  }else if($srow['status'] == 'Request approved'){ ?>
                <div class="col-sm-4 mb-2">
                <label for="inputAddress" class="form-label me-3"><b>Finance approval :</b></label><a href="#" data-toggle="tooltip" title="Waiting for finance director approval"  class="bi bi-info-circle text-primary"></a><br>                
                <div class="spinner-border  text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <?php } ?>
             
                </div>              
             </div>
           </div>
            <hr class="col-sm-12">
            <?php if(strpos($srow['status'],'rejected') === false){   ?>
            <div class="row"> 
            <div class="col-sm-12 card p-3 mt-2">            
                 <div class="card-body">                   
            <?php
            $allid = $srow['id'];
            $allid2 = isset($mrow['id'])?$mrow['id']:null;
            $netdiff = 0;
            $totaldiff = 0;
            $counterrr = 1; 
            
            if($result_perdiemmodification->num_rows > 0){
              $selectall = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt from traveladvance t LEFT JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?
              UNION ALL SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt from tadvancemodification t LEFT JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $stmt_complex_settlement = $conn_fleet->prepare($selectall);
              $stmt_complex_settlement -> bind_param("ii", $allid, $allid2);
              }else{
              $selectall = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt from traveladvance t LEFT JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $stmt_complex_settlement = $conn_fleet->prepare($selectall);
              $stmt_complex_settlement -> bind_param("i", $allid);
              }
            $stmt_complex_settlement -> execute();
            $result_complex_settlement = $stmt_complex_settlement -> get_result();
            if($result_complex_settlement -> num_rows > 0)
              while($allrow = $result_complex_settlement->fetch_assoc()){  
                          $chequenum =   $allrow['tcheque_number']; 
                          $chequenum2 =   $allrow['pcheque_number'];
                          $cpv =  $allrow['tcpv_number']; 
                          $cpv2 =  $allrow['pcpv_number'];
                          $baank =  $allrow['tbank']; 
                          $baank2 =  $allrow['pbank'];
                          $potion =  $allrow['toption'];
                          $potion2 =  $allrow['poption'];
                          $jvno = $allrow['jvno'];
                                                 
                          $split11 = ($allrow['travelreason'] != "")?explode('::',$allrow['travelreason']):"";
                          $split12 = ($allrow['travelrate'] != "")?explode('::',$allrow['travelrate'] ):"";
                          $split13 = ($allrow['traveldays'] != "")?explode('::',$allrow['traveldays'] ):"";
                          $split14 = ($allrow['travelbirr'] != "")?explode('::',$allrow['travelbirr'] ):""; 

                          $split21 = ($allrow['settlementreason'] != "")?explode('::',$allrow['settlementreason']):"";
                          $split22 = ($allrow['settlementrate'] != "")?explode('::',$allrow['settlementrate'] ):"";
                          $split23 = ($allrow['settlementdays'] != "")?explode('::',$allrow['settlementdays'] ):"";
                          $split24 = ($allrow['settlementbirr'] != "")?explode('::',$allrow['settlementbirr'] ):"";
                        
                          ?> 
               <input type="hidden" name = 'tid[]' value="<?php echo $allrow['travel_advanceid'] ?>">           
              <div class="col-sm-12 me-2">         
           <fieldset class="row border rounded-3 p-3 mb-5">
             <legend class="col-form-label float-none text-center w-auto"><?php echo $allrow['name_of_employee'] ?></legend>              
             <div class="row">                         
             <div class='col-sm-6 divider text-center fw-bold'>
                    <div class='divider-text'>Travel Advance
                </div>
              </div>            
              
              <div class='col-sm-6 divider text-center fw-bold'>
                    <div class='divider-text'>Settlement of perdiem expense and performance report
                </div>
              </div>             
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Departure date:</b></label>               
                    <input type="text" value="<?php echo date("d-m-Y H:i",strtotime($srow['departure_date'])) ?>" name="departuredate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Departure date:</b></label>               
                    <input type="text" value="<?php echo ($allrow['actual_departuredate'] != null)?date("d-m-Y H:i",strtotime($allrow['actual_departuredate'])):""  ?>" name="actualdeparturedate[]" class="form-control shadow box" readonly>                           
                  </div> 
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Return Date:</b></label>               
                    <input type="text" value="<?php echo date("d-m-Y H:i",strtotime($srow['return_date']))  ?>" name="returndate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Return date:</b></label>               
                    <input type="text" value="<?php echo ($allrow['actual_returndate'] != null)?date("d-m-Y H:i",strtotime($allrow['actual_returndate'])):"" ?>" name="actualreturndate[]" class="form-control shadow box" readonly>                           
                  </div>
                  </div>
                <div class="row">  
                <div class="col-md-6">
                <?php 
                                        $lensplit11 = is_countable($split11)?count($split11):0;
                                        $lensplit21 = is_countable($split21)?count($split21):0;
                                        $split_val = ($lensplit11 > $lensplit21)?$lensplit11:$lensplit21;
                                        ?>
                <!----------------------------------------------------------------------------------------------------->
                <?php
                 $employee = str_replace(' ', '',$allrow['name_of_employee']);
                $totalexpense = 0;
                $Fuelfound = false;
                for ($j = 0; $j < $split_val; $j++) { 
                  if(isset($split11[$j]))
                  {
                  ?> 
              <div class="row">               
                <div class="col-md-3">
                  <div class="form-floating shadow box mb-3">
                    <input type="text" value="<?php echo $split11[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
                    <label for="reason">Reason</label>
                  </div>
                </div>
              
                <div class="col-sm-3">
                  <div class="input-group shadow box mb-3">                
                  <input  type="text" value="<?php echo $split12[$j] ?>"   aria-describedby="basic-add" class="form-control" readonly>                               
                    <span class="input-group-text" id="basic-add">Rate</span>             
                   </div>
                </div>
                  <?php
                  if($split11[$j] == 'Fuel')
                         { 
                          $span = 'Liter';
                         }
                        else 
                        {
                          $span = 'Days';
                        }   
                   ?>
                  <div class="col-sm-3">
                    <div class="input-group shadow box mb-3"> 
                  <input value="<?php echo $split13[$j] ?>" type="text"  aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3">                                                
                      <input value="<?php echo $split14[$j] ?>"  type="text"  aria-describedby="basic-add2" class="form-control" readonly>                                       
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In ETB</label>            
                      </div>
                      </div>                   
                    </div>
                      <?php $totalexpense += $split14[$j]; } }  ?> 
                    <div class="col-sm-6">
                    <div class="form-floating shadow box input-group mb-3"> 
                    <input value="<?php echo $totalexpense ?>" id="advancepayment_<?php echo $employee ?>" name="totalexpense[]" type="text"  aria-describedby="basic-add2" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add2">Advance Payment</span> 
                    <label for="floatingSelect">In ETB</label>            
                      </div>
                      </div>
                      <div class="row mt-3"> 
                   <?php                     
                    if($chequenum != ""){ ?>                                            
                    <div class="col-sm-6 col-md-4 mb-2">
                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input value="<?php echo  $chequenum  ?>" type="text" name="chequeno" class="form-control" readonly>                           
                    </div> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                    <input value="<?php echo  $baank  ?>" type="text"   name="bank" class="form-control" readonly> 
                  </div> 
                  <?php } if($cpv != ""){ ?>                        
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input value="<?php echo  $cpv  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div> 
                     <?php } ?>                      
                  </div> 
                      </div>
                <div class="col-md-6">
                 <?php 
                      $totalexpense2 = 0;
                      $amount_iter = 0;
                      $totalfuel = 0;
                      $fuelset = false;
                 for ($j = 0; $j < $split_val; $j++) { 
                  if(isset($split21[$j]))
                  {                    
                    ?> 
              <div class="row">                            
                <div class="col-md-3">
                  <div class="form-floating shadow box mb-3">
                    <input type="text" value="<?php echo $split21[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
                    <label for="reason">Reason</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="input-group shadow box mb-3"> 
                  <?php if(isset($split22[$j])){ ?>
                  <input value="<?php echo $split22[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add" class="form-control" readonly>                   
                  <?php  }else{ ?>
                  <input value="Not filled yet"   step="any"  aria-describedby="basic-add" class="form-control" readonly>                   
                    <?php  } ?>                                                                  
                   <span class="input-group-text" id="basic-add">Rate</span>             
                  </div>
                </div>
                 <?php  
                    if($split21[$j] == 'Fuel')
                         { 
                          $span = 'Liter';
                          $state = '';
                          $function = 'onkeyup="Find_birr(this)"';
                         }
                        else 
                        {
                          $span = 'Days';
                          $state = 'readonly';
                          $function = '';
                        }   
                 ?>
                  <div class="col-sm-3">
                    <div class="input-group shadow box mb-3"> 
                  <input value="<?php echo $split23[$j] ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?> name="days[]" type="Number" step="any"  aria-describedby="basic-add1" class="form-control" required <?php echo $state ?> >  
                    <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3">  
                    <?php if(!isset($split24[$j])){ ?>
                    <input  value="Not filled yet" name="birr[]" id="birr_<?php echo $employee ?>::<?php echo $j ?>" class="birr_<?php echo $employee ?> form-control"  step="any"  aria-describedby="basic-add2" readonly>  
                    <?php  }else{ ?>
                    <input value="<?php echo $split24[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>  
                          <?php 
                         $totalexpense2 += (is_numeric($split24[$j]))?$split24[$j]:0; } ?> 
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In ETB</label>            
                  </div>
                  </div>
                      <!-- </div> -->
                      </div>
                      <?php                                                             
                       } 
                      }  
                      ?>       
                    
                      <div class="col-sm-6">
                      <div class="form-floating shadow box input-group mb-3">                  
                        <input value="<?php echo ($allrow['actual_returndate'] != null)?$totalexpense2:"" ?>" name="currentpayment[]" type="Number"  aria-describedby="basic-add2" class="form-control" readonly>                       
                      <span class="input-group-text" id="basic-add2">Current Payment</span> 
                      <label for="floatingSelect">In ETB</label>            
                        </div>
                        </div>
                        <?php $netdiff = $totalexpense2 - $totalexpense;                      
                              $totaldiff += $netdiff;                                                        
                        ?>
                        <div class="col-sm-6">
                      <div class="form-floating shadow box input-group mb-3">                                           
                      <input value="<?php echo ($allrow['actual_returndate'] != null)?$netdiff:""  ?>"  type="Number"  aria-describedby="basic-add2" class="form-control" readonly>                         
                      <span class="input-group-text" id="basic-add2">Net difference</span> 
                      <label for="floatingSelect">In ETB</label>            
                        </div>
                        </div>
                        
                        <div class="row">
                 <?php                            
                   if($allrow['receipt'] != Null or $allrow['receipt'] != ''){ ?>  
                <div class="col-md-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Reciept or supporting file:</b></label>                                 
                 <ul class="mb-3 list-group">  
                 <?php 
                   $num = 1;
                   $tittle = '';
                   $reciept = ($allrow['receipt'] != '')?explode('::',$allrow['receipt']):"";  
                   for($i = 0; $i < count($reciept); $i++){  ?>            
                <li class="list-group-item d-flex justify-content-between align-items-center">              
                  <span class="btn" data-bs-toggle="popover" data-bs-content="Supporting document or receipt ">File <?php echo $num ?> - <span class="text-primary"><?php echo $reciept[$i]  ?></span></span>
                  <span class="col-sm-3">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $reciept[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $reciept[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                </span>
                </li>  
                  <?php } ?>                                                        
                     </ul><!-- End List Group With Contextual classes -->
                     </div> 
                     <?php  $num++; } ?> 
                     <?php if($allrow['workdone'] != ''){ ?>
                     <div class="col-md-6 mb-2">                 
                    <label for="jobid" class="form-label me-3"><b>Work done:</b></label>               
                    <input type="textarea" row="2" value="<?php echo $allrow['workdone'] ?>"  class="form-control shadow box" readonly>                           
                   </div>
                   <?php } ?>
                     </div> 
            <!--***************************************************************************************************************************************************-->
           <?php if($st == 'Payment processed'){ ?>
            <div class="row">               
                   <?php if($allrow['image'] != ''){
                    $split1 = explode('::',$allrow['description']);
                    $split2 = explode('::',$allrow['image']); 
              ?>
              <ul class="list-group">  
                   <?php 
                   $num = 1;
                   $tittle = '';
                   for($i = 0; $i < count($split2); $i++){ ?>  
                <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                <?php if(isset($split1[$i])) $tittle = $split1[$i];  ?>
                  <span class="btn" data-bs-toggle="popover" title="<?php echo $split1[$i] ?>" data-bs-content="<?php echo $tittle  ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                  <span class="ms-3 col-sm-2">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                </span>
                </li>                                        
                     <?php  $num++; } ?> 
                </ul><!-- End List Group With Contextual classes -->                  
                 <?php }else{ ?>
                <h3 class="text-center">No supporting documents uploaded</h3>
                 <?php   }  ?>
                   </div>  
                  <?php } ?>
            <!-- ***************************************************************************************************************************************************-->                                                                                
                    <div class="row mt-3"> 
                       <?php                     
                    if($chequenum2 != '')
                        { 
                       ?>                                            
                    <div class="col-sm-6 col-md-4 mb-2">
                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input value="<?php echo  $chequenum2  ?>" type="text" name="chequeno" class="form-control" readonly>                           
                    </div> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                    <input value="<?php echo  $baank2  ?>" type="text"   name="bank" class="form-control" readonly> 
                   </div>                   
                    <?php 
                          } 
                       if($cpv2 != "" and $cpv2 != 0)
                          { ?> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input value="<?php echo  $cpv2  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div> 
                    <?php }
                     if($jvno != "")
                          { ?> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>Jv:</b></label>               
                      <input value="<?php echo  $jvno  ?>" type="text"   name="jvno" class="form-control" readonly>                           
                    </div> 
                    <?php } ?>                           
                  </div>             
                
                 </div>
             <!----------------------------------------------------------------------------------------------------->
                      </div>
                      <hr>
                 <?php if($allrow['paymentsigned_by'] != ''){ $buttonname = 'Proceed';                 
                 if($netdiff > 0){ 
                  if($allrow['payment_option'] == 'Paid in Cash'){
                ?>              
                  <div class="form-check mt-3">
                    <label class="form-check-label" for="comfirm">
                      payment done in Cash 
                    </label>
                    <input class="form-check-input" type="checkbox" checked id="Cash" disabled>
                  </div> 
                  <?php }else if($allrow['payment_option'] == 'Paid in Cash'){ ?> 
                  <div class="form-check">
                    <label class="form-check-label" for="comfirm">
                      payment done in Cheque
                    </label>
                    <input class="form-check-input"  type="checkbox" checked id="Cheque" disabled>
                  </div>                 
                  <?php } 
                          }
                      else if($netdiff != 0)
                          {  
                          if($allrow['payment_option'] == 'Recieved in Cash'){ ?>
                          <div class="form-check mt-3">
                            <label class="form-check-label" for="comfirm">
                             Recieved the difference in cash
                            </label>
                            <input class="form-check-input"  type="checkbox" value="Recieved in Cash" id="Cash" disabled>
                          </div> 
                          <?php }else if($allrow['payment_option'] == 'Deduct from Salary'){  ?>
                          <div class="form-check">
                            <label class="form-check-label" for="comfirm">
                            Deducted from salary
                            </label>
                            <input class="form-check-input"  type="checkbox" value="Deduct from Salary" id="Salary" disabled>
                          </div> 
                      <?php } } ?>
              <!--***********************************************************************************************************************************--> 
              <?php }
                    if($allrow['closed_by'] != ''){ $buttonname = 'Proceed'; ?>        
                      <div class="form-check mt-3">
                            <label class="form-check-label" for="comfirm">
                              Settlement done
                            </label>
                            <input class="form-check-input" value="Closed" type="checkbox" id="Cash" checked disabled>
                          </div>                   
                <?php } ?> 
              </fieldset>
              </div>                 
               <?php 
                $counterrr++; }            
            ?>                                
               </div>
             </div>             
            </div>             
               <?php 
                } 
              }     
            }           
           ?>                
          </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div> 
                </div>   
              </div> 
            </div><!----End status Modal------------------> 
        
        <div class="modal fade" id="modificationModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header text-center">
                      <h4 class="modal-title ">Perdiem Modification Approval</h4>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                       <form method="POST" action="perdiem.php#pills-history" class="row">
                          <div class="modal-body">                    
                            <div class="card">
                              <div class="card-body">             
                                <div class="row mt-3">
                                <?php 
                                    if(isset($_POST['approvemodify'])){
                                      $i = $_POST['approvemodify'];                                     
                                    $detail = "SELECT *, perdiemmodification.id as id, perdiem.id as pid, perdiemmodification.travellers as mtravellers, perdiemmodification.travellerroleintrip as mtravellerroleintrip,
                                    perdiemmodification.return_date as mreturndate, perdiemmodification.departure_date as mdeparture, perdiem.departure_date as pdeparture, perdiem.return_date as preturndate,
                                    perdiem.travellers as ptravellers, perdiem.travellerroleintrip as ptravellerroleintrip, perdiem.departure_place as departure_place, perdiem.company as company,
                                    perdiemmodification.destination as destination, perdiem.destination as pdestination,perdiemmodification.status as `status`
                                    FROM perdiem JOIN perdiemmodification ON perdiem.id = perdiemmodification.perdiemid where perdiemmodification.perdiemid = ? and perdiemmodification.status not like '%rejected'";  
                                    $stmt_complex_perdiemmodification = $conn_fleet -> prepare($detail);
                                    $stmt_complex_perdiemmodification -> bind_param("i", $i);
                                    $stmt_complex_perdiemmodification -> execute();
                                    $result_complex_perdiemmodification = $stmt_complex_perdiemmodification -> get_result();
                                    if($result_complex_perdiemmodification->num_rows > 0);
                                      while($row = $result_complex_perdiemmodification->fetch_assoc()){  
                                          $i1 = $row['pid'];
                                          $i2 = $row['id'];
                                          $status = $row['status'];
                                          ?>                                 
                                  <input type="hidden" id="statusid" name="hiddenstat" value="<?php echo $row['status']  ?>">                                                    
                                  <input type="hidden" id='checkid' name='checkid' value="<?php echo $row['id'] ?>"> 
                                  <input type="hidden" id="requestercompany" name="requestercompany" value="<?php echo $row['company']   ?>">
                                  <div class="col-lg-12 col-md-12 col-xl-12 col-sm-12 mb-2">
                                    <div class="row">
                                    <div class="col-lg-6 col-md-6 col-xl-6 col-sm-6 mb-2">  
                                  <div class="row">
                                  <div class="col-lg-12 col-md-12 col-xl-12 col-sm-12 mb-2"> 
                                    <label for="inputEmail4" class="form-label me-3"><b>Job id:</b></label>
                                    <input id="dateofrequest" type="text" value="<?php echo $row['job_id']   ?>" class="form-control shadow" readonly> 
                                  </div> 
                                  <div class="col-lg-12 col-md-12 col-xl-12 col-sm-12 mb-2"> 
                                    <label for="inputEmail4" class="form-label me-3"><b>Date of Modification:</b></label>
                                    <input id="dateofrequest" type="text" value="<?php echo $row['dateofmodification']   ?>" class="form-control shadow" readonly> 
                                  </div> 
                                  </div>  
                                  </div>
                                  <div class="col-lg-6 col-md-6 col-xl-6 col-sm-6 mb-2">                                                 
                                    <label for="inputAddress" class="form-label me-3"><b>Reason For Modification:</b></label>
                                  <textarea style = "border-color: #719ECE;box-shadow: 0 0 10px" class="form-control shadow"  rows = "4" id="reasonfortravel" readonly><?php echo $row['reasonformodification']  ?></textarea>                                
                                  </div>
                                  </div>
                                  </div>           
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Previous Departure Date:</b></label>                                   
                                  <input id="departuredate" type="text" value="<?php echo $row['pdeparture']   ?>" class="form-control shadow" readonly>  
                                  </div>
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Previous Return Date:</b></label>
                                    <input id="returndate" type="text" value="<?php echo $row['preturndate']   ?>" class="form-control shadow" readonly>                
                                  </div>
                                  <?php 
                                $depart = strtotime($row['pdeparture']);
                                $return = strtotime($row['preturndate']);              
                                $ptotaldate = round(abs($return - $depart) / 86400,2);
                                  ?>  
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Previous Total day:</b></label>
                                    <input id="totaldays" type="text" value="<?php echo  $ptotaldate  ?>" class="form-control shadow" readonly>
                                  </div>
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Extended Departure Date:</b></label>                                    
                                  <input id="departuredate" type="text" value="<?php echo $row['mdeparture']   ?>" class="form-control text-dark shadow alert-danger" readonly>  
                                  </div>
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Extended Return Date:</b></label>
                                    <input id="returndate" type="text" value="<?php echo $row['mreturndate']   ?>" class="form-control text-dark shadow alert-danger" readonly>                
                                  </div>
                                  <?php 
                                $depart = strtotime($row['mdeparture']);
                                $return = strtotime($row['mreturndate']);              
                                $mtotaldate = round(abs($return - $depart) / 86400,2);
                                  ?>                
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Extended Total day:</b></label>
                                    <input id="totaldays" type="text" value="<?php echo  $mtotaldate  ?>" class="form-control text-dark shadow alert-danger" readonly>
                                  </div>
                                  <?php if($row['destination'] != ''){  ?>
                                  <fieldset class="row border rounded-3 mx-auto bg-white p-3">
                                    <legend class="col-form-label float-none w-auto">Added Destination</legend>
                                  <!-- <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Departure:</b></label>
                                  <input id="Departureplace" type="text" value="<?php //echo $row['departure_place']  ?>" class="form-control shadow" readonly>
                                  </div> -->
                                  <?php   
                                    $split1 = explode('::',$row['destination']);
                                    $split2 = explode('::',$row['round_distance_km']);
                                    for($s = 0; $s < count($split1); $s++){
                                  ?>             
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Destination:</b></label>
                                  <input id="Destination" type="text" value="<?php echo  $split1[$s]  ?>" class="form-control shadow" readonly> 
                                  </div>
                                  <div class="col-sm-4 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Distance:</b></label>
                                    <input id="Distance" type="text" value="<?php echo $split2[$s] ?>" class="form-control shadow" readonly>
                                  </div>  
                                  <?php if($s != count($split1) - 1) {?>
                                  <div class="col-sm-4 mb-2">
                                  <label for="inputAddress" class="form-label me-3"><b>Departure:</b></label>
                                  <input id="Departureplace" type="text" value="<?php echo $split1[$s]  ?>" class="form-control shadow" readonly>             
                                  </div>
                                  <?php }
                                } ?>
                                  </fieldset>
                              <?php     }
                              if($row['mtravellers'] != ''){  ?>
                                  <div class="col-sm-12 mb-2">
                                  <fieldset class="row border rounded-3 bg-white p-3">
                                    <legend class="col-form-label float-none w-auto">Travellers Information</legend>                  
                              <?php 
                                  $splitt = explode(',',$row['mtravellers']);
                                  $splitr = explode(',',$row['mtravellerroleintrip']); 
                                  $msplitt = explode(',',$row['travellers']); 
                                  $msplitr = explode(',',$row['travellerroleintrip']);      
                                  for ($i = 0; $i < count($splitt); $i++) { ?>
                                  <div class="col-sm-6 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Traveller:</b></label>
                                    <input class="form-control" type ="text" id="travellers" value="<?php echo $splitt[$i] ?>" readonly>
                                  </div>
                                  <div class="col-sm-6 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Role of traveller in this trip:</b></label>
                                    <input class="form-control" type="text" id="travellerroleintrip" value="<?php echo $splitr[$i] ?>" readonly>
                                  </div>
                                  <?php } ?> 
                                  <?php  if($row['driver'] != '' OR $row['driver'] != Null){ ?>
                                    <div class="col-sm-6 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Traveller:</b></label>
                                    <input class="form-control" type ="text" id="travellers" value="<?php echo $row['driver'] ?>" readonly>
                                  </div>
                                  <div class="col-sm-6 mb-2">
                                    <label for="inputAddress" class="form-label me-3"><b>Role of traveller in this trip:</b></label>
                                    <input class="form-control" type="text" id="travellerroleintrip" value="Driver" readonly>
                                  </div>
                              <?php   } ?>         
                                  </fieldset>
                                  </div>
                                  <?php  }
                                  if($row['customersite_km'] != ''){
                                  ?>                                 
                                  <div class="col-sm-12 mb-2">
                                  <fieldset class="row border bg-white rounded-3 p-3">
                                  <legend class="col-form-label float-none w-auto">Added Customer site km</legend> 
                                    <!-- <div class="row mb-3"> -->
                                  <label class="col-sm-2 col-form-label">Customer site km:</label> 
                                     <?php   
                                        $explode1 = explode(',',$row['customersite_km']);
                                        $explode2 = explode(',',$row['tripperday']);
                                        $explode3 = explode(',',$row['daysof_stay']); 

                                        $explode11 = explode(',',$row['customersite_km']);
                                        $explode22 = explode(',',$row['tripperday']);
                                        $explode33 = explode(',',$row['daysof_stay']);  
                                        for($j=0; $j < count($explode1); $j++){           
                                     ?>
                                  <div class="col-sm-3">
                                    <div class="input-group mb-3"> 
                                      <input value="<?php echo $explode1[$j] ?>" type="Number" aria-describedby="basic-addon4" class="form-control two" readonly>  
                                        <span class="input-group-text" id="basic-addon4">Km</span>             
                                      </div>
                                    </div>     
                                  <div class="col-sm-3">
                                    <div class="input-group mb-3"> 
                                      <input value="<?php echo $explode2[$j] ?>" type="Number" min='0' step = "any" aria-describedby="basic-addon4" class="form-control two" readonly>  
                                        <span class="input-group-text" id="basic-addon4">Trip/day</span>             
                                    </div>
                                  </div>
                                <div class="col-sm-3">
                                  <div class="input-group"> 
                                    <input value="<?php echo $explode3[$j] ?>" type="Number" min='0' step="any" aria-describedby="basic-addon4" class="form-control two" readonly>  
                                      <span class="input-group-text" id="basic-addon4">Days</span>             
                                  </div>
                                </div>
                                <div class="col-sm-2"></div>
                                <?php } ?>
                                  </fieldset>
                              </div>
                              <?php } ?>
                              <div class="col-sm-12 mb-2">
                                  <fieldset class="row border bg-white rounded-3">
                                    <legend class="col-form-label float-none w-auto">Invoices and reciepts</legend>                  
                              <?php if($row['image'] != ''){
                                      $split1 = explode('::',$row['description']);
                                      $split2 = explode('::',$row['image']); 
                                ?>
                                <ul class="list-group">  
                                    <?php 
                                    $num = 1;
                                    $tittle = '';
                                    for($i = 0; $i < count($split2); $i++){ ?>  
                                  <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                                  <?php if(isset($split1[$i])) $tittle = $split1[$i];  ?>
                                    <span class="btn" data-bs-toggle="popover" title="Popover title" data-bs-content="<?php echo $tittle  ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                                    <span class="ms-2 col-sm-1">               
                                    <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                                    <?php if(strpos($split2[$i],'.pdf')===false){ ?>
                                    <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                                    <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                                    <?php } ?>
                                  </span>
                                  </li>                                        
                                      <?php  $num++; } ?> 
                                      </ul><!-- End List Group With Contextual classes -->             
                              
                                <?php }else{ ?>
                                  <h3 class="text-center">No supporting documents uploaded</h3>
                                  <?php   } ?>
                                  </fieldset>
                                  </div>
                                  <div class="col-sm-4 mb-2">
                                  <label for="inputAddress" class="form-label me-3"><b>Modified By:</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($row['dateofmodification'])) ?>"  class="bi bi-info-circle text-primary"></a>
                                  <input id="preparedby" type="text" value="<?php echo $row['modified_by']   ?>" class="form-control shadow" readonly>
                                  </div>  
                                  <?php if($row['travel_approved_by'] != Null){ 
                                      $split = explode('::',$row['travel_approved_by']);
                                        $by = $split[0];
                                        $date = $split[1];
                                    ?>
                                  <div class="col-sm-4 mb-2">
                                  <label for="inputAddress" class="form-label me-3"><b>Travel approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                                  <input id="preparedby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                                  </div> 
                                <?php  } ?>
                                <?php if($row['request_approved_by'] != Null){ 
                                      $split = explode('::',$row['request_approved_by']);
                                        $by = $split[0];
                                        $date = $split[1];
                                    ?>                           
                                  <div class="col-sm-4 mb-2">
                                  <label for="inputAddress" class="form-label me-3"><b>Request approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                                  <input id="preparedby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                                  </div> 
                                <?php  }
                                 if($row['finance_approved_by'] != Null){ 
                                  $split = explode('::',$row['finance_approved_by']);
                                     $by = $split[0];
                                     $date = $split[1];
                                ?>
                                <div class="col-sm-4 mb-2">
                                 <label for="inputAddress" class="form-label me-3"><b>Finance approved by :</b></label><a href="#" data-toggle="tooltip" title="<?php echo date('d-M-Y H:i', strtotime($date)) ?>"  class="bi bi-info-circle text-primary"></a>
                                <input id="travelappdby" type="text" value="<?php echo $by   ?>" class="form-control shadow" readonly>
                              </div>           
                            <?php  }                               
                                ?> 
                  <?php  
                  $totalcash = 0;           
                  $unicounter = 1; 
                  $signatory="";
                    if($row['mreturndate'] != $row['preturndate'])
                    {
                    $traveladvance = "SELECT * from tadvancemodification where perdime_id = ?";
                    }
                    else
                    {
                    $traveladvance = "SELECT * from tadvancemodification where name_of_employee NOT IN (SELECT name_of_employee FROM traveladvance where perdime_id = '$i1') and  perdime_id = ?";
                    }                                                        
               if($row['status'] != 'Cheque reviewed') $traveladvance .= " and payment_option != 'Cheque'";   
                   $stmt_tadvancemodification = $conn_fleet -> prepare($traveladvance);
                   $stmt_tadvancemodification -> bind_param("i", $i2);
                   $stmt_tadvancemodification -> execute();
                   $result_tadvancemodification = $stmt_tadvancemodification -> get_result();       
                    if($result_tadvancemodification->num_rows > 0);
                     while($travelrow = $result_tadvancemodification->fetch_assoc()){
                       $idd = $travelrow['id'];
                       $reason = $travelrow['reason'];
                       $rate = $travelrow['rate'];
                       $days = $travelrow['days'];
                       $totalcost = $travelrow['birr'];  
                       $signatory .= ($travelrow['payment_option'] != 'Petty')?$travelrow['cheque_signatory']:"";
                       $paymentmethod = $travelrow['payment_option'];                          
                   ?>            
                 <div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
                   <div class="card-body">
               <h5 class="card-title mt-3 mb-3 text-center">Travel Advance</h5>
               <div class="row mb-2">              
                 <div class="col-sm-6 mb-2">
                   <label for="nameofemployee" class="form-label me-3"><b>Name of Employee:</b></label>               
                  <span id="nameofemployee"><?php echo $travelrow['name_of_employee']   ?></span>                         
               </div>
                
               <div class="col-sm-6 mb-2">               
                 <label for="inputEmail" class="col-form-label"><b>Role in this trip:</b></label>
                   <span id="roleinthistrip"><?php echo $travelrow['roleonthistrip']   ?></span>   
                  </div> 
               </div> 
                  
                  <div class="col-sm-12 mb-2">
                 <fieldset class="row border rounded-3 p-3">
                   <legend class="col-form-label float-none w-auto">Amount Required</legend>
                                  
                     <?php $split1 = explode('::',$reason);
                           $split2 = explode('::',$rate);
                           $split3 = explode('::',$days);
                           $split4 = explode('::',$totalcost);
                         
                         for($j = 0;$j < count($split1); $j++){
                     ?>
               <div class="row mb-2"> 
                 <div class="col-md-3">
                   <div class="form-floating mb-3">
                     <input value="<?php echo $split1[$j] ?>"  type="text"  class="form-control" id="reason" aria-label="State" readonly>                 
                     <label for="reason">Reason</label>
                   </div>
                 </div>
 
                 <div class="col-sm-3">
                   <div class="input-group mb-3"> 
                   <input value="<?php echo $split2[$j] ?>"  type="text"  aria-describedby="basic-add" class="form-control" readonly>  
                     <span class="input-group-text" id="basic-add">Rate</span>             
                   </div>
                   </div>
 
                   <div class="col-sm-3">
                     <div class="input-group mb-3"> 
                   <input value="<?php echo $split3[$j] ?>"  type="text" aria-describedby="basic-add1" class="form-control" readonly>  
                     <span class="input-group-text" id="basic-add1">Days</span>             
                   </div>
                   </div>
 
                   <div class="col-sm-3">
                     <div class="form-floating input-group mb-3"> 
                     <input value="<?php echo $split4[$j] ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>  
                     <span class="input-group-text" id="basic-add2">Total cost</span> 
                     <label for="floatingSelect">In Birr</label>            
                   </div>
                   </div>
                  </div>
                  <?php } ?>
 
                 </fieldset>
                 </div> 
                 <?php if($travelrow['payment_option'] != 'Petty'){ ?>
                 <?php if($row['status'] == 'Cheque reviewed'){ ?>
                   <input type="hidden" name="chequeexists[]" value="<?php echo  $travelrow['id']  ?>">
                   <?php } ?>
                 <div class="row mt-3"> 
                    <div class="col-sm-4 mb-2">
                       <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input type="text" value="<?php echo  $travelrow['cheque_number']  ?>"  class="form-control" readonly>                           
                         </div> 
                         <div class="col-sm-4 mb-2">
                       <label for="jobid" class="form-label me-3"><b>Cpv Number:</b></label>               
                        <input type="text" value="<?php echo  $travelrow['cpv_number']  ?>"  class="form-control" readonly>                           
                         </div> 
                    <div class="col-sm-4 mb-2">
                       <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <input type="text" value="<?php echo  $travelrow['bank']  ?>"  class="form-control" readonly>  
                         </div>              
                    </div>
                    <?php }else if($travelrow['cheque_percent'] != 'p_100' && (($_SESSION["department"] == "Disbursement" and $_SESSION["role"] == "manager") || strpos($_SESSION["a_type"],"Petty Cash Approver") !== false)){ 
                       $petty_on = true;
                     ?>
                       <input type="hidden" name="pettyexists[]" value="<?php echo  $travelrow['id']  ?>">
                   <?php } ?>
                 
               </div>
            </div>                       
                 <?php  
                    }   
                          $status = $row['status'];                                   
                                    } 
                                  }  
                                  ?>
                                </div>                               
                              </div>
                            </div>
                            <div class="modal-footer">
                            <?php  if(($status  == 'Cheque reviewed' AND (strpos($signatory,$_SESSION['username']) === false)) or (isset($petty_on))){  
                              ?>
                              <button type="button" onclick="prompt_confirmation(this)" name="modsubmitchange" id="modapproved" value="Approved" class="btn btn-outline-success" >Approve <i class="fas fa-check-circle"></i></button>
                              <button type="button" onclick="writeReason(this)"  name="reject" id="modreqrejected"  class="btn btn-outline-danger" >Reject <i class="bi bi-x-circle"></i></button>             
                                <?php }else{ ?>
                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>             
                                <?php    } ?> 
                            </div> 
                          </div>
                       </form>
                </div> 
            </div>
           </div> 


        <div class="modal fade rejectreasonreq" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="perdiem.php" id='reqform_reasons'>
                        <div class="modal-header">
                            <h4 id='top_text' class="w-100 text-center"><span class='small text-secondary' id='rem_optional'>Reason/Remark</span>
                            <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                        </div>
                        <div class="modal-body" id="reason_body">
                            <input type="hidden" name="hiddenn" id='reason_btn'> 
                           <textarea class='form-control col-sm-12' rows='2' placeholder="Write a reason or remark for rejecting the request" id='reason_field' name='reason'></textarea>
                            <button  onclick="prompt_confirmation(this)" id="rejectbutton" name="travelapproval" value="Rejected" class='form-control btn btn-outline-primary mt-3' >Proceed</button>
                        </div>
                    </form> 
                </div>
            </div>
       </div>

       <div class="modal fade mm"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header alert-secondary">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
              <img id="popup-img" loading="lazy" src="" alt="Image" style="max-height:100%; max-width: 100%;">
            </div>
          </div>
        </div>
      </div>

        <?php include '../footer.php';?>
        <script>
   function writeReason(e){ 
   var id = document.getElementById('checkid').value; 
    $('.rejectreasonreq').modal('show');
    document.getElementById('reason_btn').value = id; 
    document.getElementById('rejectbutton').name = (e.id == 'reqrejected')?'travelapproval':'modsubmitchange';      
  }
</script>        
<script>
<?php 
if(isset($_POST['detail']))
{?>
  document.getElementById('modal_open').click();
  <?php
  unset($_POST['detail']);
}
if(isset($_POST['history']))
{?>
  document.getElementById('open_history_modal').click();
  <?php
  unset($_POST['history']);
}
if(isset($_POST['settlementdetail']))
{?>
  document.getElementById('modal_open2').click();
  <?php
  unset($_POST['history']);
}
if(isset($_POST['approvemodify'])){  ?>
  document.getElementById('modmodal_open').click();
  <?php unset($_POST['approvemodify']); 
  }  
?>
  function displayimage(e){     
  let n = parseInt(e.id.split("_")[1]);   
  var src = document.getElementById('vieww_'+n).src; 
  $('.mm').modal('show');
	$('#popup-img').attr('src',src);
  }
</script>
<script>
      function prompt_confirmation(e)
    {
        if(e.type!="submit")
        {
            Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            })
            .then((result) => {
                if (result.isConfirmed) {
                    e.type = "submit";
                    e.click();
                    e.setAttribute("disable","true");
                }
            });
        }
    }
</script>
<script type="text/javascript">
  jQuery(document).ready(function($) {
// Javascript to enable link to tab
var url = document.location.toString();
if (url.match('#')) {
    $('.nav-pills a[href="#' + url.split('#')[1] + '"]').tab('show');
} 
// Change hash for page-reload
$('.nav-pills a').on('shown.bs.tab',function (e){
    window.location.hash = e.target.hash;
  });

if (url.match('#')) {
    $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
} 
// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
  });
});
</script>