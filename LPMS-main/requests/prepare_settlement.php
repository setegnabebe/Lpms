<?php 
session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "Owner" && $_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement" && strpos($_SESSION["a_type"],"Perdiem") === false) header("Location: ../");
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
            <li class="breadcrumb-item active">Prepare Settlement</li>
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
<!-------->

<?php if(isset($_POST['approvesettlement']))
{                 
    $approvaldate = date("Y-m-d H:i:s");
    $by = $_SESSION['username'].'::'.$approvaldate;
    $sid = $_POST['sid'];
    $counter = 0;   
    $status="Settlement checked";  
    $j=0;                                               
    foreach($_POST['tid'] as $index => $tid)
    {                                    
        $option = (isset($_POST['signature_'.$tid]))?$_POST['signature_'.$tid]:"";
        $travel_advance_id=$tid;
        $start = $counter;
        $counter += $_POST['count'][$index];
        $description =  $image = "";
        $rate =  $birr = $days = "";
        for($i=$start;$i<$counter;$i++){ 
        $rate .= ($rate == '')?$_POST['rate'][$i]:'::'.$_POST['rate'][$i];
        $days .= ($days == '')?$_POST['days'][$i]:'::'.$_POST['days'][$i];
        }
        $birr .= ($birr == '')?$_POST['netdifference'][$j]:'::'.$_POST['netdifference'][$j];   
        $paymentapproval2 = "UPDATE perdiemsettlement SET `rate` = ? , `days` = ? , `birr` = ?, `status` = 'Settlement payment prepared' where travel_advanceid = ?";
        $stmt_set_settlement = $conn_fleet->prepare($paymentapproval2);
        $stmt_set_settlement -> bind_param("sssi", $rate, $days, $birr, $travel_advance_id);
        $query2 = $stmt_set_settlement -> execute();       
                                    
        // $chequeno = (isset($_POST['schequeno'][$index]) and $_POST['schequeno'][$index] != '')?$_POST['schequeno'][$index]:Null;
        // $cpvno = (isset($_POST['scpvno'][$index]) and $_POST['scpvno'][$index] != '')?$_POST['scpvno'][$index]:Null;
        // $bankk = (isset($_POST['sbankname'][$index]) and $_POST['sbankname'][$index] != '')?$_POST['sbankname'][$index]:Null;
        // $pcvp = (isset($_POST['spcpvno'][$index]) and $_POST['spcpvno'][$index] != '')?$_POST['spcpvno'][$index]:Null;
        // $crvno = (isset($_POST['scrvno'][$index]) and $_POST['scrvno'][$index] != '')?$_POST['scrvno'][$index]:Null;                       

        // $checksettlementapproved = "UPDATE perdiemsettlement SET `cheque_number` = ?,`cpv_number` = ?,`bank` = ?,`pcpv_number` = ?,`crv_number` = ?,  `payment_option` = ?, `description` = ?, `image` = ?, `status` = 'Payment processed', paymentsigned_by = ? where travel_advanceid = ?";
        // $stmt_update_perdiems_ettlement = $conn_fleet->prepare($checksettlementapproved);
        // $stmt_update_perdiems_ettlement -> bind_param("sssssssssi",$chequeno, $cpvno, $bankk, $pcvp, $crvno, $option, $description, $image, $by, $tid);
        // $query2 = $stmt_update_perdiems_ettlement -> execute();
        $i++;
        if($option == 'Deduct from Salary'){
        $status = "Payment processed";
        }                  
    }
    $checksettlement = "UPDATE perdiem SET  `status` = ?, `settlementpayment_prepby` = ? where id = ?";
    $stmt_update_perdiem = $conn_fleet->prepare($checksettlement);
    $stmt_update_perdiem -> bind_param("ssi",$status,$by, $sid);
    $query1 = $stmt_update_perdiem -> execute();
    if($query1) $_SESSION['success'] = true;
                   
}      
          ?> 

<!----->
    <div class="row">
        <ul class="nav nav-tabs d-flex nav-tabs-bordered  shadow  mx-auto mt-3" id="pills-tab" role="tablist">        
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-home" style="width:500px;" class="nav-link w-100 active btn-primary rounded" id="pills-home-tab" data-bs-toggle="pill" data-target="#home" role="tab" aria-controls="home" aria-selected="false">Settlement Requests</a>
          </li>       
          
        </ul> 
      </div>
        <?php
        $str="";        
        $result="";
        $status="Settlement reviewed";
        $sql_clus =  "SELECT * FROM perdiem AS p where company =? and status = ? ORDER BY dateofrequest desc";        
        $stmt_perdiem_conditional_fetch = $conn_fleet -> prepare($sql_clus);
        $stmt_perdiem_conditional_fetch -> bind_param("ss", $_SESSION["company"],$status);
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
            $cheque_or_petty1=$row['cheque_reviwedby'];
            $cheque_or_petty2=$row['settlementreviewed_by'];                       
            $tag ="prepare settlement";        
                       
            if($status == 'Settlement reviewed' ){
              //
              $t_sql = "SELECT * FROM traveladvance WHERE perdime_id = ?";
              $stmt_tadvance_fetch = $conn_fleet -> prepare($t_sql);
              $stmt_tadvance_fetch -> bind_param("i", $id);
              $stmt_tadvance_fetch -> execute();
              $res_tadvance_fetch = $stmt_tadvance_fetch -> get_result();
              $count=0;             
                //$chequen=(isset($tadv_rw['payment_option']))?$tadv_rw['cheque_number']:"";                           
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

                  
                    $str.=  '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail" value="'.$id.'" data-bs-toggle="modal" data-bs-target="#fullscreenModal">View Detail</button>';   
                                  
              $str .= '</div>
            </div> 
                ';       
          }
         } ?>
      <div class="tab-content pt-2" id="myTabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="home-tab">    
      <form method="POST" action="prepare_settlement.php#pills-home">
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
    </form>
      </div>     
      </div>

     <!--fullscreen modal starting-->
        

     <div class="modal fade" id="fullscreenModal" tabindex="-1">
     <div class="modal-dialog modal-fullscreen">
     <form method="POST" action="prepare_settlement.php#pills-home"> 
        <div class="modal-content">      
          <div class="modal-header">
             <h2 style="font-family:Gabriola" class="modal-title text-center mx-auto">Settlement Approval</h2>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>          
              <div class="modal-body">          
           <?php 
           if(isset($_POST['detail'])){
            $sid = $_POST['detail'];
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
            //echo"<script>alert('".$allid."')</script>";
            $allid2 = isset($mrow['id'])?$mrow['id']:null;
            $netdiff = 0;
            $totaldiff = 0;
            $counterrr = 1;
            $perdiem_modification_num_rw=0;
            $is_mod=false;
            if($result_perdiemmodification->num_rows > 0)
            {
              $selectall = "SELECT *,t.reason as travelreason,t.rate as travelrate,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr, p.payment_option as poption,p.receipt as receipt,t.cheque_number as tcheque_number,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $perdimemodandtadvmod_qry="SELECT *,t.reason as travelreason,t.rate as travelrate,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,t.days as traveldays,t.birr as travelbirr,t.cheque_number as tcheque_number from tadvancemodification t JOIN perdiemmodification p ON t.perdime_id = p.id where perdime_id = ?";
              $stmt_all_perdiem = $conn_fleet->prepare($selectall);
              $stmt_all_perdiem -> bind_param("i", $allid);
              $stmt_perdiem_modification = $conn_fleet->prepare($perdimemodandtadvmod_qry);
              $stmt_perdiem_modification -> bind_param("i",$allid2);
              $stmt_perdiem_modification -> execute();                     
            $result_perdiem_modification = $stmt_perdiem_modification->get_result();       
            $perdiem_modification_num_rw=$result_perdiem_modification -> num_rows;
            $is_mod=true;
              // $TTYY=$stmt_perdiem_modification -> execute();
              // if($TTYY)echo "<script>alert('".$allid2."')</script>";
            }
            else
            {
              $selectall = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
              p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
              $stmt_all_perdiem = $conn_fleet->prepare($selectall);
              $stmt_all_perdiem -> bind_param("i", $allid);              
            }             
            $perdiem_modification_settlement=false;            
            $split11=$split21="";
            //echo "<script>alert('".$allid2."')</script>";            
           
            $stmt_all_perdiem -> execute();
            $result_all_perdiem = $stmt_all_perdiem->get_result();
              if($result_all_perdiem -> num_rows > 0)
                while($allrow = $result_all_perdiem->fetch_assoc()){  
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
                  if($perdiem_modification_num_rw > 0 and $is_mod==true)
                    {
                      $perdiem_modification_rw=$result_perdiem_modification->fetch_assoc();
                      
                        $split11 = ($perdiem_modification_rw['travelreason'] != "")?explode('::',$perdiem_modification_rw['travelreason']):"";
                        $split12 = ($perdiem_modification_rw['travelrate'] != "")?explode('::',$perdiem_modification_rw['travelrate'] ):"";
                        $split13 = ($perdiem_modification_rw['traveldays'] != "")?explode('::',$perdiem_modification_rw['traveldays'] ):"";
                        $split14 = ($perdiem_modification_rw['travelbirr'] != "")?explode('::',$perdiem_modification_rw['travelbirr'] ):""; 
                        //$returndate =  $perdimemodandtadvmod_rw['modified_returndate'];
                        $perdiem_modification_settlement=true;
                      
                    }

                  if($perdiem_modification_settlement==false)
                    {
                    $split11 = ($allrow['travelreason'] != "")?explode('::',$allrow['travelreason']):"";
                    $split12 = ($allrow['travelrate'] != "")?explode('::',$allrow['travelrate'] ):"";
                    $split13 = ($allrow['traveldays'] != "")?explode('::',$allrow['traveldays'] ):"";
                    $split14 = ($allrow['travelbirr'] != "")?explode('::',$allrow['travelbirr'] ):""; 
                    }
                    if($perdiem_modification_settlement==true)
                          {                          
                          $splitm14 = ($allrow['travelbirr'] != "")?explode('::',$allrow['travelbirr'] ):""; 
                          $splitm13 = ($allrow['traveldays'] != "")?explode('::',$allrow['traveldays'] ):"";
                          $splitm11 = ($allrow['travelreason'] != "")?explode('::',$allrow['travelreason']):"";
                          $splitm12 = ($allrow['travelrate'] != "")?explode('::',$allrow['travelrate'] ):"";
                          }                      
                  // $split11 = ($allrow['travelreason'] != "")?explode('::',$allrow['travelreason']):"";
                  //$split12 = ($allrow['travelrate'] != "")?explode('::',$allrow['travelrate'] ):"";
                  //$split13 = ($allrow['traveldays'] != "")?explode('::',$allrow['traveldays'] ):"";
                  //$split14 = ($allrow['travelbirr'] != "")?explode('::',$allrow['travelbirr'] ):"";

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
                    <input type="text" value="<?php echo $returndate  ?>" name="returndate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Return date:</b></label>               
                    <input type="text" id="arrival_<?php echo $employee ?>" value="<?php echo date("d-m-Y H:i",strtotime($allrow['actual_returndate'])) ?>" name="actualreturndate[]" class="form-control shadow box" readonly>                           
                  </div>
                  </div>
                <div class="row">  
                <div class="col-md-6">
                <?php $split_val = (count($split11) > count($split21)) ? count($split11) : count($split21);  ?>
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
                  <input value="<?php echo ($perdiem_modification_settlement==true and isset($split13[$j]) and isset($splitm13[$j]))?$split13[$j]+$splitm13[$j]:$split13[$j] ?>" type="text"  aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3">                                                
                      <input value="<?php echo ($perdiem_modification_settlement==true and isset($split13[$j]) and isset($splitm13[$j]))?$split14[$j]+$splitm14[$j]:$split14[$j] ?>"  type="text"  aria-describedby="basic-add2" class="form-control" readonly>                                       
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In ETB</label>            
                      </div>
                      </div>
                      <!-- </div> -->
                      </div>
                      <?php if($perdiem_modification_settlement==true and isset($splitm14[$j]))
                    {
                      $totalexpense = $totalexpense+$split14[$j]+$splitm14[$j];
                    }else $totalexpense += $split14[$j];  

                    // condition for if some body recieve fuel money before 
                  } else if(isset($splitm11[$j]))
                     {?>

                      <div class="row">               
                      <!-- <div class="col-md-6"> -->
                      <div class="col-md-3">
                        <div class="form-floating shadow box mb-3">
                          <input type="text" value="<?php echo $splitm11[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
                          <label for="reason">Reason</label>
                        </div>
                      </div>
                    
                      <div class="col-sm-3">
                        <div class="input-group shadow box mb-3">                
                        <input  type="text" value="<?php echo $splitm12[$j] ?>"   aria-describedby="basic-add" class="form-control" readonly>                               
                          <span class="input-group-text" id="basic-add">Rate</span>             
                         </div>
                        </div>
                        <?php
                        if($splitm11[$j] == 'Fuel')
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
                        <input value="<?php echo $splitm13[$j] ?>" type="text"  aria-describedby="basic-add1" class="form-control" readonly>  
                          <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                        </div>
                        </div>
      
                        <div class="col-sm-3">
                          <div class="form-floating shadow box input-group mb-3">                                                
                            <input value="<?php echo $splitm14[$j] ?>"  type="text"  aria-describedby="basic-add2" class="form-control" readonly>                                       
                          <span class="input-group-text" id="basic-add2">Total cost</span> 
                          <label for="floatingSelect">In ETB</label>            
                            </div>
                            </div>
                            <!-- </div> -->
                            </div>
                            <?php 
                            $totalexpense = $totalexpense+$splitm14[$j];                          
                      //end
                     }
                     }// end for loop ?> 
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
                  <input  name="rate[]" id="rate_<?php echo $employee ?>::<?php echo $j ?>"   onkeyup="Find_birr(this)" type="Number" step="any"  aria-describedby="basic-add" class="form-control border border-primary" Required> 
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
                  <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?> name="days[]" type="Number" step="any"  aria-describedby="basic-add1" class="form-control" required>  
                       <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3"> 
                    <input name="birr[]" id="birr_<?php echo $employee ?>::<?php echo $j ?>" class="birr_<?php echo $employee ?> form-control" type="Number" step="any"  aria-describedby="basic-add2" readonly>  
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
                     } 
                      }  ?>
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
                      <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked')|| ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?>  
                      <input  name="currentpayment[]" id="currpayment_<?php echo $employee ?>" type="Number"  aria-describedby="basic-add2" class="form-control" readonly>
                      <?php  }else{ ?>
                        <input value="<?php echo $totalexpense2 ?>" name="currentpayment[]" type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>
                        <?php  } ?>  
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
                      <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked')|| ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?> 
                        <input  id="netdifference_<?php echo $employee ?>" type="Number"  name='netdifference[]' aria-describedby="basic-add2" class="form-control" readonly> 
                        <?php  }else{ ?>                         
                      <input value="<?php echo  $netdiff  ?>" name='netdifference[]'  type="Number"  aria-describedby="basic-add2" class="form-control" readonly>
                         <?php  } ?> 
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
              
             <!----------------------------------------------------------------------------------------------------->
                </div>                   
                                                          
                    <?php if($netdiff == 0){  ?>
                  <div class="col-sm-6 col-md-4 mb-2" id="jv_<?php echo $employee ?>">
                    <label for="cpvno" class="form-label me-3"><b>JV Number:</b></label>               
                      <input type="text" id="jv_<?php echo $allrow['travel_advanceid'] ?>" name="jvno[]"  class="form-control checkinfo3_<?php echo $employee ?> border shadow border-primary" required>                           
                  </div>
                       <?php } ?>
                                         
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
          <script>
            function set_cpv(e)
            {
              let identifier1 = parseInt(e.id.split("_")[1]);
              document.getElementById('cpv_view_'+identifier1).innerHTML = e.value;
            }
          </script>      
       <?php    
          }           
           ?>    
             <input type="hidden" name = 'sid' value="<?php echo   $sid ?>">                   
            </div> 
              <!--approval goes here -->                        
               <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="submit" name='approvesettlement' class="btn btn-success btn-sm"><?php echo $buttonname ?></button>     
              </div>             
            </div> 
            </form> 
        </div> 
      </div> 

    <!--end fullscreen modal starting-->
        


       

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
function Find_birr(e){  
  let first = e.id.split("_")[1];
  let totalbirr = 0;
  let second =  parseInt(e.id.split("::")[1]) + 1;
  const rate = document.getElementById('rate_'+first).value;
  const days = document.getElementById('days_'+first).value;
  const birr = rate * days;
if(days !== "" && rate !== ""){ 
  document.getElementById('birr_'+first).value =  birr;
    let rates = document.getElementsByClassName("birr_"+first.split("::")[0]);       
    for(let i = 0;i < rates.length;i++){
    totalbirr += parseFloat(rates[i].value);
    }
  document.getElementById('currpayment_'+first.split("::")[0]).value = totalbirr;
   netdifference = totalbirr -  document.getElementById('advancepayment_'+first.split("::")[0]).value
  document.getElementById('netdifference_'+first.split("::")[0]).value = netdifference;
}
}

</script>