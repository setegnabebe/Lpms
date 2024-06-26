<?php 
    session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement" && $_SESSION["department"] != "GM" && strpos($_SESSION["a_type"],"Perdiem") === false) 
    header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
$buttonname = "";
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center mt-3 py-2  alert-primary rounded'>
                <h6 class='text-white'>Perdiem requests</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
         ";
}
function divcreate5($str)
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
    set_title("LPMS | perdiem");
    sideactive("perdiem");
</script>
 <div id="main">
   <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Perdiem</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Prepare check</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
          <?php
          //save pety cash pcpv 
        if(isset($_POST['savepetty'])){
            $printid = $_POST['savepetty'];
        }
        if(isset($_POST['savepetty'])){
              $checkid = $_POST['checkid'];
              $table = isset($_POST['savepetty'])?'perdiem':'perdiemmodification';
              $table2 = isset($_POST['savepetty'])?'traveladvance':'tadvancemodification';                  
              $preparedby = $_SESSION["username"].'::'.date("Y-m-d H:i:s");                                       
              $cond ="status='Payment done',`paymentdone_by` = ?";
              foreach($_POST['travelid'] as $tiid => $value){                      
                $id = $_POST['travelid'][$tiid];                     
                $cpvnumber = $_POST['cpvno'][$tiid];
                $update = "UPDATE $table2 SET cpv_number ='".$cpvnumber."' where id = '".$id."'";
                $stmt_update_custom_perdiem = $conn_fleet->query($update);                        
              }
              $check = "UPDATE $table SET  $cond, chequenumber='both petty and cheque payment done'  where id = ?";
              $stmt_update_custom_travel = $conn_fleet->prepare($check);
              $stmt_update_custom_travel -> bind_param("si", $preparedby, $printid);
              $query = $stmt_update_custom_travel -> execute();

              if($stmt_update_custom_perdiem){
                $_SESSION['success'] = true;
              }
          }                 
        //end save pettycash pcpv
        ?>          
                  <?php
                  if(isset($_POST['registercheck'])){
                      $printid = $_POST['registercheck'];
                  }
                  if(isset($_POST['registercheck']) or isset($_POST['modsubmitchange'])){
                       $checkid = $_POST['checkid'];
                       $table = isset($_POST['registercheck'])?'perdiem':'perdiemmodification';
                       $table2 = isset($_POST['registercheck'])?'traveladvance':'tadvancemodification';
                    if ($_SESSION["role"] == "Cashier"){
                        $preparedby = $_SESSION["username"].'::'.date("Y-m-d H:i:s");
                        $detail = "SELECT * FROM perdiem where id = ?";
                        $stmt_perdiem_by_id = $conn_fleet->prepare($detail);  
                        $stmt_perdiem_by_id -> bind_param("i", $checkid);
                        $stmt_perdiem_by_id -> execute();
                        $result_perdiem_by_id = $stmt_perdiem_by_id->get_result();
                        if($result_perdiem_by_id -> num_rows > 0)
                        $srow = $result_perdiem_by_id->fetch_assoc();
                        // settlement final payment by casher
                        if(isset($srow['status']) and $srow['status']=="Settlement cheque approved" )
                        $cond = isset($_POST['signature'])?"`status` = 'Setlement payment done', `paymentdone_by` = ?":"paymentpreparedby = ?, `status` = 'Cheque prepared'";
                        //end for settlement final payment by casher
                        else
                        $cond = isset($_POST['signature'])?"`status` = 'Payment done', `paymentdone_by` = ?":"paymentpreparedby = ?, `status` = 'Cheque prepared'";
                        
                        if(!isset($_POST['signature'])){
                          $check = "UPDATE $table SET  $cond  where id = ?";                                                
                        foreach($_POST['travelid'] as $tiid => $value){
                          if($_POST['checkno'][$tiid] == '') continue;
                        $id = $_POST['travelid'][$tiid];
                        $chequenumber = $_POST['checkno'][$tiid];
                        $cpvnumber = $_POST['cpvno'][$tiid];
                        $bankname = $_POST['bankname'][$tiid];
                        $update = "UPDATE $table2 SET cheque_number = ?, cpv_number = ?, bank = ? where id = ?";
                        $stmt_update_custom_perdiem = $conn_fleet->prepare($update);
                        $stmt_update_custom_perdiem -> bind_param("sssi", $chequenumber, $cpvnumber, $bankname, $id);
                        $query = $stmt_update_custom_perdiem -> execute();
                        }
                        
                        }else{
                          $check = "UPDATE $table SET $cond,chequenumber='cheque payment done'  where id = ?";
                        }
                        $stmt_update_custom_travel = $conn_fleet->prepare($check);
                        $stmt_update_custom_travel -> bind_param("si", $preparedby, $checkid);
                        $query = $stmt_update_custom_travel -> execute();

                        if($query){
                          $_SESSION['success'] = true;
                        }
                    }
                    else if(($_SESSION["role"] == "Disbursement" or $_SESSION["role"] == "manager") AND $_SESSION["department"] == "Finance"){
                        $approvedby = $_SESSION["username"].'::'.date("Y-m-d H:i:s");
                        //echo"<script>alert('".$approvedby."')</script>";
                        $status = 'Cheque reviewed';
                        $action = 'reviwed';
                        $paymentapproval = "UPDATE $table SET cheque_reviwedby = ?, `status` = ? where id = ?";
                        $stmt_update_custom_perdiem2 = $conn_fleet->prepare($paymentapproval);
                        $stmt_update_custom_perdiem2 -> bind_param("ssi", $approvedby, $status, $checkid);
                        $query = $stmt_update_custom_perdiem2 -> execute();

                        $payment = "UPDATE $table2 SET cheque_reviwedby = ? WHERE perdime_id = ?";
                        $stmt_update_custom_travel2 = $conn_fleet->prepare($payment);
                        $stmt_update_custom_travel2 -> bind_param("si", $approvedby, $checkid);
                        $query = $stmt_update_custom_travel2 -> execute();
                        }

                        if($query){
                        $_SESSION['success'] = 'Perdiem request approved successfully';
                        }
                  }
          ?> 
          <!-- --------------------------------------------------------------------------------------------------- -->
          <?php 
              if(isset($_POST['approvesettlement']))
              {                 
                  $approvaldate = date("Y-m-d H:i:s");
                  $by = $_SESSION['username'].'::'.$approvaldate;
                  $sid = $_POST['sid'];
                  $counter = 0;
                  if((strpos($_SESSION["a_type"],"Perdiem") !== false and $_POST['identifier'] != "Settlement payment checked") || (($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false) and $_POST['identifier'] == "Settlement reviewed")){ 
                    if(isset($_POST['signature']) and strpos($_SESSION["a_type"],"Perdiem") !== false){ 
                      $paymentapproval1 = "UPDATE perdiem SET `status` = 'Closed' where id = ?";
                      $stmt_perdiem_finish = $conn_fleet->prepare($paymentapproval1);
                      $stmt_perdiem_finish -> bind_param("i", $sid);
                      $query = $stmt_perdiem_finish -> execute();                                     
                      foreach($_POST['tid'] as $tid)
                      {
                        $option = $_POST['signature_'.$tid];
                        $jvno = (isset($_POST['jvno'][$tid]) and $_POST['jvno'][$tid] != '')?$_POST['jvno'][$tid]:Null;
                        $checksettlementapproved = "UPDATE perdiemsettlement SET jv = ? ,closed_by = ? , `status` = 'closed' where travel_advanceid = ?";
                        $stmt_settlement_finish = $conn_fleet->prepare($checksettlementapproved);
                        $stmt_settlement_finish -> bind_param("ssi",$jvno, $by, $tid);
                        $query2 = $stmt_settlement_finish -> execute();
                      } 
                      if($query and $query2)
                      $_SESSION['success'] = true;
                    }else{
                    $paymentapproval1 = "UPDATE perdiem SET  `status` = 'Settlement payment prepared', settlementpayment_prepby = ? where id = ?";
                    $stmt_settlement_prepare = $conn_fleet->prepare($paymentapproval1);
                    $stmt_settlement_prepare -> bind_param("si", $by, $sid);
                    $query = $stmt_settlement_prepare -> execute();
                      foreach($_POST['tid'] as $tid => $value)
                      { 
                        $id = $_POST['tid'][$tid];
                        $start = $counter;
                        $counter += $_POST['count'][$tid];
                        $rate =  $birr = $days = "";
                        for($i=$start;$i<$counter;$i++){
                        $rate .= ($rate == '')?$_POST['rate'][$i]:'::'.$_POST['rate'][$i];
                        $days .= ($days == '')?$_POST['days'][$i]:'::'.$_POST['days'][$i];
                        
                        }  
                        $birr .= ($birr == '')?$_POST['netdifference'][$tid]:'::'.$_POST['netdifference'][$tid];          
                         $paymentapproval2 = "UPDATE perdiemsettlement SET `rate` = ? , `days` = ? , `birr` = ?, `status` = 'Settlement payment prepared' where travel_advanceid = ?";
                         $stmt_set_settlement = $conn_fleet->prepare($paymentapproval2);
                         $stmt_set_settlement -> bind_param("sssi", $rate, $days, $birr, $id);
                         $query2 = $stmt_set_settlement -> execute();
                          if($query and $query2)
                          $_SESSION['success'] = true;
                      }
                    }
                  }
                  else if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance"){
                    $identifier = $_POST['identifier'];
                    if($identifier == "Settlement payment prepared"){
                      $stmt_settlement_cheque_check = $conn_fleet->prepare("UPDATE perdiem SET  `status` = 'Settlement payment checked', settlementpaymentckd_by = ? where id = ?");
                      $stmt_settlement_cheque_check -> bind_param("si", $by, $sid);
                      foreach($_POST['tid'] as $tid)
                      {
                        $checksettlementchecked = "UPDATE perdiemsettlement SET  `status` = 'Settlement payment checked' where travel_advanceid = ?";
                        $stmt_settlement_cheque_check2 = $conn_fleet->prepare($checksettlementchecked);
                        $stmt_settlement_cheque_check2 -> bind_param("i", $tid);
                        $query2 = $stmt_settlement_cheque_check2 -> execute();
                      }
                    }else{
                      //setlement finance disbursment reviewing is goes here

                      if($identifier == "Settlement checked"){
                      $stmt_settlement_cheque_check = $conn_fleet->prepare("UPDATE perdiem SET  `status` = 'Settlement payment checked', settlementchequechkd_by = ? where id = ?");
                      $stmt_settlement_cheque_check -> bind_param("si", $by, $sid); 
                      foreach($_POST['tid'] as $tid)
                      {
                        $checksettlementchecked = "UPDATE perdiemsettlement SET  `status` = 'Settlement payment checked' where travel_advanceid = ?";
                        $stmt_settlement_cheque_check2 = $conn_fleet->prepare($checksettlementchecked);
                        $stmt_settlement_cheque_check2 -> bind_param("i", $tid);
                        $query2 = $stmt_settlement_cheque_check2 -> execute();
                      }    
                      }

                      //end setlement finance disbursment reviewing is goes here
                     else{ $stmt_settlement_cheque_check = $conn_fleet->prepare("UPDATE perdiem SET  `status` = 'Settlement cheque checked', settlementchequechkd_by = ? where id = ?");
                      $stmt_settlement_cheque_check -> bind_param("si", $by, $sid); 
                      foreach($_POST['tid'] as $tid)
                      {
                        $checksettlementchecked = "UPDATE perdiemsettlement SET  `status` = 'Settlement cheque checked' where travel_advanceid = ?";
                        $stmt_settlement_cheque_check2 = $conn_fleet->prepare($checksettlementchecked);
                        $stmt_settlement_cheque_check2 -> bind_param("i", $tid);
                        $query2 = $stmt_settlement_cheque_check2 -> execute();
                      }                     
                    }
                  }
                    $query1 = $stmt_settlement_cheque_check -> execute();
                      if($query1 and $query2)
                      $_SESSION['success'] = true;
                  }
                  //  settlement finance manger approval goes here...........
                  else if($_SESSION["role"] == "manager" AND $_SESSION["department"] == "Finance"){                    
                      $stmt_settlement_cheque_check = $conn_fleet->prepare("UPDATE perdiem SET  `status` = 'Settlement payment approved', settlementpaymentapp_by = ? where id = ?");
                      $stmt_settlement_cheque_check -> bind_param("si", $by, $sid);
                      foreach($_POST['tid'] as $tid)
                      {
                        $checksettlementchecked = "UPDATE perdiemsettlement SET  `status` = 'Settlement payment checked' where travel_advanceid = ?";
                        $stmt_settlement_cheque_check2 = $conn_fleet->prepare($checksettlementchecked);
                        $stmt_settlement_cheque_check2 -> bind_param("i", $tid);
                        $query2 = $stmt_settlement_cheque_check2 -> execute();
                      }   
                      $query1 = $stmt_settlement_cheque_check -> execute();  
                      if($query1) $_SESSION['success'] = true;               
                  }
                  //  end sttlement finance manger approval
                  else if(($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") or ($_SESSION['perdiem'] == true AND  ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor" )))){
                    $netdifference = $_POST['netdifference'];
                      foreach($netdifference as $net)
                      {                       
                        if($net != 0){
                        $status = 'Settlement payment approved';
                        break;
                        }else{
                        $status = 'Payment processed'; 
                        }
                      }                     
                    $checksettlementapproved = "UPDATE perdiem SET  `status` = ?, settlementchequeapp_by = ? where id = ?";
                    $stmt_settlement_cheque_approved = $conn_fleet->prepare($checksettlementapproved);
                    $stmt_settlement_cheque_approved -> bind_param("ssi",$status,$by, $sid);
                    $query1 = $stmt_settlement_cheque_approved -> execute();
                      foreach($_POST['tid'] as $tid)
                      { 
                        $checksettlementapproved2 = "UPDATE perdiemsettlement SET  `status` = ? where travel_advanceid = ?";
                        $stmt_settlement_cheque_approved2 = $conn_fleet->prepare($checksettlementapproved2);
                        $stmt_settlement_cheque_approved2 -> bind_param("si",$status, $tid);
                        $query2 = $stmt_settlement_cheque_approved2 -> execute();
                      } 
                      if($query1 and $query2)
                      $_SESSION['success'] = true;
                    if(isset($_POST['vehicle']) &&  $query1  && $query2 ){
                      $driver = $_POST['driver'];
                      $vehicle = $_POST['vehicle'];
                      $newfuel = $_POST['fuel'];
                      $date = $_POST['actualdate'];
                      $datedate[0] = '';
                      $datedate[1] = '';
                      $sum = 0;
                      $chgkm = 0;
                      $a = '';
                      $index = 0;
                      $first = true;
                      $dateee = '';
                      $select = "SELECT * FROM `fmstotalreport` WHERE drivername = ? AND platenumber = ?  AND `filleddateandtime` > ? order by `filleddateandtime` ASC limit 2";
                      $stmt_fmstotalreport = $conn_fleet->prepare($select);  
                      $stmt_fmstotalreport->bind_param("sss", $driver, $vehicle, $date);
                      $stmt_fmstotalreport->execute();
                      $result_fmstotalreport = $stmt_fmstotalreport->get_result();
                        if($result_fmstotalreport->num_rows > 0){                       
                          while($row = $result_fmstotalreport->fetch_assoc()){                                              
                            if($first){
                              $checkifadded = $row['addedfuel'];
                              $fuel = $row['fuelfilledamount'];
                              $first = false;
                            } 
                          $kmpl = $row['fuelconsumedbyfmsconsumption'];
                          $datedate[$index] = $row['filleddateandtime'];
                          $index++;
                          }         
                            if($checkifadded != '' || $checkifadded != NULL){  
                              $checkifadded = explode("_",$checkifadded);
                              $dateee = $checkifadded[1];
                              $fuell=  $checkifadded[0];
                            }           
                            if($dateee != $date || $dateee == ''){                      
                              $sum = $fuel + $newfuel;
                              $chgkm = round(($kmpl * $fuel)/$sum,2);
                              $added = $newfuel.'_'.$date;

                              $update = "UPDATE fmstotalreport set `fuelfilledamount` = ?, `addedfuel` = ? WHERE filleddateandtime = ? AND platenumber = ?";
                              $stmt_update_fmstotalreport = $conn_fleet->prepare($update);
                              $stmt_update_fmstotalreport -> bind_param("ssss", $sum, $added, $datedate[0], $vehicle);
                              $query = $stmt_update_fmstotalreport -> execute();

                              $update2 = "UPDATE fmstotalreport set `fuelconsumedbyfmsconsumption` = ? WHERE filleddateandtime = ? AND platenumber = ?";
                              $stmt_update_fmstotalreport2 = $conn_fleet->prepare($update2);
                              $stmt_update_fmstotalreport2 -> bind_param("sss", $chgkm, $datedate[1], $vehicle);
                                if($query){
                                  $query2 = $stmt_update_fmstotalreport2 -> execute();
                                  $a = 'Fuel added successfully';
                                }else{
                                  $a = 'Fuel not added';
                                } 
                            }else{
                              $a = "Data already updated";
                            }
                        }else{
                          $a = 'No data found';
                        }
                    }
                  }else if($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false){ 
                     $counter_cash_crv=$counter_cheque=$counter_cashp_cpv=0;                                  
                    foreach($_POST['tid'] as $index => $tid)
                    {  
                                    
                      $option = $_POST['signature_'.$tid];
                      $start = $counter;
                      $counter += intval($_POST['recieptcounter_'.$tid]);
                      $description =  $image = "";
                      for($i=$start;$i<$counter;$i++){ 
                        $description .= (isset($_POST['description'][$i]))?(($description == '')?$_POST['description'][$i]:'::'.$_POST['description'][$i]):Null;
                        if(isset($_POST['image'][$i]) and $_POST['image'][$i] != ''){
                        $binary_data = base64_decode($_POST['image'][$i]);
                        $photoname = uniqid().'.jpeg';
                        $image .= ($image == '')?$photoname :'::'.$photoname;
                        $result = file_put_contents('../../receiptphoto/'.$photoname, $binary_data);
                        }else{
                          $description .= NULL;
                          $image .= Null;
                        } 
                      }   
                      if($option=="Recieved in Cash"){
                        
                        $crvno = (isset($_POST['scrvno'][$counter_cash_crv]) and $_POST['scrvno'][$counter_cash_crv] != '')?$_POST['scrvno'][$counter_cash_crv]:Null;
                        $checksettlementapproved = "UPDATE perdiemsettlement SET `crv_number` = ?, `payment_option` = ?, `description` = ?, `image` = ?, `status` = 'Payment processed', paymentsigned_by = ? where travel_advanceid = ?";
                       $stmt_update_perdiems_ettlement = $conn_fleet->prepare($checksettlementapproved);
                       $stmt_update_perdiems_ettlement -> bind_param("sssssi",$crvno, $option, $description, $image, $by, $tid);
                       $query2 = $stmt_update_perdiems_ettlement -> execute();
                       $counter_cash_crv++;
                      }else if($option == "Paid in Cheque")
                      {
                        $chequeno = (isset($_POST['schequeno'][$counter_cheque]) and $_POST['schequeno'][$counter_cheque] != '')?$_POST['schequeno'][$counter_cheque]:Null;
                        $cpvno = (isset($_POST['scpvno'][$counter_cheque]) and $_POST['scpvno'][$counter_cheque] != '')?$_POST['scpvno'][$counter_cheque]:Null;
                        $bankk = (isset($_POST['sbankname'][$counter_cheque]) and $_POST['sbankname'][$counter_cheque] != '')?$_POST['sbankname'][$counter_cheque]:Null;
                       
                      $checksettlementapproved = "UPDATE perdiemsettlement SET `cheque_number` = ?,`cpv_number` = ?,`bank` = ?,  `payment_option` = 'Paid in Cheque', `description` = ?, `image` = ?, `status` = 'Payment processed', paymentsigned_by = ? where travel_advanceid = ?";
                       $stmt_update_perdiems_ettlement = $conn_fleet->prepare($checksettlementapproved);
                       $stmt_update_perdiems_ettlement -> bind_param("ssssssi",$chequeno, $cpvno, $bankk, $description, $image, $by, $tid);
                       $query2 = $stmt_update_perdiems_ettlement -> execute();
                       $counter_cheque++;
                      }  
                         
                      else if($option=="Paid in Cash"){
                        $pcvp = (isset($_POST['spcpvno'][$counter_cashp_cpv]) and $_POST['spcpvno'][$counter_cashp_cpv] != '')?$_POST['spcpvno'][$counter_cashp_cpv]:Null;
                        $checksettlementapproved = "UPDATE perdiemsettlement SET `pcpv_number` = ?, `payment_option` = ?, `description` = ?, `image` = ?, `status` = 'Payment processed', paymentsigned_by = ? where travel_advanceid = ?";
                       $stmt_update_perdiems_ettlement = $conn_fleet->prepare($checksettlementapproved);
                       $stmt_update_perdiems_ettlement -> bind_param("sssssi",$pcvp, $option, $description, $image, $by, $tid);
                       $query2 = $stmt_update_perdiems_ettlement -> execute();
                       $counter_cashp_cpv++;
                      }    
                      else{
                        $checksettlementapproved = "UPDATE perdiemsettlement SET `payment_option` = ?, `description` = ?, `image` = ?, `status` = 'Payment processed', paymentsigned_by = ? where travel_advanceid = ?";
                       $stmt_update_perdiems_ettlement = $conn_fleet->prepare($checksettlementapproved);
                       $stmt_update_perdiems_ettlement -> bind_param("ssssi",$option, $description, $image, $by, $tid);
                       $query2 = $stmt_update_perdiems_ettlement -> execute();
                       $counter_cashp_cpv++;
                      }               
                        // $chequeno = (isset($_POST['schequeno'][$index]) and $_POST['schequeno'][$index] != '')?$_POST['schequeno'][$index]:Null;
                        // $cpvno = (isset($_POST['scpvno'][$index]) and $_POST['scpvno'][$index] != '')?$_POST['scpvno'][$index]:Null;
                        // $bankk = (isset($_POST['sbankname'][$index]) and $_POST['sbankname'][$index] != '')?$_POST['sbankname'][$index]:Null;
                        // $pcvp = (isset($_POST['spcpvno'][$index]) and $_POST['spcpvno'][$index] != '')?$_POST['spcpvno'][$index]:Null;
                        // $crvno = (isset($_POST['scrvno'][$index]) and $_POST['scrvno'][$index] != '')?$_POST['scrvno'][$index]:Null;

                        

                      
   
                      if($option == 'Paid in Cheque' || $option == 'Paid in cash'){
                       $status = "Settlement cheque prepared";
                      }else if($option == 'Deduct from Salary in Cheque'){
                       $status = "Payment processed";
                      }else if(!isset($status) || ($status != "Payment processed" && $status != "Settlement cheque prepared")){
                       $status = "Closed";                     
                      }                    
                  }
                  $checksettlement = "UPDATE perdiem SET  `status` = ?, `settlementpayment_prepby` = ? where id = ?";
                  $stmt_update_perdiem = $conn_fleet->prepare($checksettlement);
                  $stmt_update_perdiem -> bind_param("ssi",$status,$by, $sid);
                  $query1 = $stmt_update_perdiem -> execute();
                  if($query2 and $query1)
                  $_SESSION['success'] = true;
                } 
              }      
          ?>  
  <?php 
$active = (($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") OR (strpos($_SESSION["a_type"],"Perdiem") !== false) OR ($_SESSION['perdiem'] == true AND ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor"))))?'active':'';
$active_2 = (($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") OR (strpos($_SESSION["a_type"],"Perdiem") !== false) OR ($_SESSION['perdiem'] == true AND ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor"))))?'':'active';
  
  if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance" OR ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)){ ?>
    <div class="row">
        <ul class="nav nav-tabs d-flex nav-tabs-bordered  shadow  mx-auto mt-3" id="pills-tab" role="tablist">
         <?php if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance" OR ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)){    ?> 
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-home" style="width:500px;" class="nav-link w-100 active" id="pills-home-tab" data-bs-toggle="pill" data-target="#home" role="tab" aria-controls="home" aria-selected="false">Perdiem Request Form</a>
          </li>
          <?php } ?>
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-profile" style="width:500px;" class="nav-link w-100 <?php echo $active ?>" id="pills-profile-tab" data-bs-toggle="pill" data-target="#profile" role="tab" aria-controls="profile" aria-selected="false">Perdiem expense settlement</a>
          </li>
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-history" style="width:500px;" class="nav-link w-100" id="pills-history-tab" data-bs-toggle="pill" data-target="#history" role="tab" aria-controls="history" aria-selected="false">Perdiem history</a>
          </li>
        </ul> 
      </div>
  <?php } else if(($_SESSION["department"] == "Finance" and $_SESSION["role"] == "manager")){ ?>
    <div class="row">
        <ul class="nav nav-tabs d-flex nav-tabs-bordered  shadow  mx-auto mt-3" id="pills-tab" role="tablist">         
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-pediem-settlement" style="width:500px;" class="nav-link w-100 <?php echo $active ?>" id="pills-profile-tab" data-bs-toggle="pill" data-target="#profile" role="tab" aria-controls="profile" aria-selected="false">Perdiem expense settlement</a>
          </li>
          <li class="nav-item flex-fill text-center" role="presentation">
            <a href="#pills-history" style="width:500px;" class="nav-link w-100" id="pills-history-tab" data-bs-toggle="pill" data-target="#history" role="tab" aria-controls="history" aria-selected="false">Perdiem history</a>
          </li>
        </ul> 
      </div>
  <?php }
 ?>         
<!-- -------------------------------------------------------------------------Perdiem request-------------------------------------------------------------------------------------------- -->
      <?php
              $str="";
              $str2="";
              $str3="";
              $str5="";
              $settlement_str="";
     if(($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false) OR ($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance")){    
      if($_SESSION["role"] == "Cashier"){
        if($_SESSION['company'] == 'Hagbes HQ.')
      $sql_clus =  "SELECT * FROM perdiem where (senior_accountant is not null OR (`status` = 'Senior accountant approved' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Cheque')) OR `status` = 'Payment approved') and  (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
        else
       $sql_clus =  "SELECT * FROM perdiem where (senior_accountant is not null OR (`status` = 'Senior accountant approved' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Cheque')) OR `status` = 'Payment approved') and company = ? ORDER BY dateofrequest desc";
    }else if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance"){
        if($_SESSION['company'] == 'Hagbes HQ.')
      $sql_clus =  "SELECT * FROM perdiem where (`status` = 'Cheque prepared' or paymentpreparedby is not null) and (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc"; 
        else
      $sql_clus =  "SELECT * FROM perdiem where (`status` = 'Cheque prepared' or paymentpreparedby is not null) and company = ? ORDER BY dateofrequest desc";
    }
      $stmt_perdiem_fetch = $conn_fleet->prepare($sql_clus);  
      $stmt_perdiem_fetch -> bind_param("s", $_SESSION['company']);
      $stmt_perdiem_fetch -> execute();
      $result_perdiem_fetch = $stmt_perdiem_fetch->get_result();
        if($result_perdiem_fetch->num_rows>0)
        while($row = $result_perdiem_fetch->fetch_assoc())
        {   
            $printid = $row['id'];
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
            $stat = $row['status'];
            $cashire = $row['paymentpreparedby'];
            $settlementprepby = $row['settlementpayment_prepby'];
            if($_SESSION["role"] == "Cashier")        
            $modification = "SELECT * FROM perdiemmodification where perdiemid = ? and ((`status` = 'Senior accountant approved' || paymentpreparedby is not null) AND `id` IN (SELECT perdime_id FROM tadvancemodification where payment_option = 'Cheque') OR `status` = 'Payment approved') ";
            else
            $modification = "SELECT * FROM perdiemmodification where perdiemid = ? and (`status` = 'Cheque prepared' || paymentpreparedby is not null)";
       
            $stmt_modification_fetch = $conn_fleet->prepare($modification);  
            $stmt_modification_fetch -> bind_param("i", $id);
            $stmt_modification_fetch -> execute();
            $result_modification_fetch = $stmt_modification_fetch->get_result();
                if($result_modification_fetch->num_rows > 0){
                  $row22 = $result_modification_fetch->fetch_assoc(); 
                  $mid = $row22['id'];
                  $mstatus = $row22['status']; 
                  $paymentprepby = $row22['paymentpreparedby']; 
                }else{
                  unset($mid);
                }
          if((($stat == 'Senior accountant approved' || $stat == 'Payment approved') and $_SESSION["role"] == "Cashier") || ($stat == 'Cheque prepared' and $_SESSION["role"] == "Disbursement")){
            $tag = ($stat == "Senior accountant approved" )?'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">prepare payment</span>':'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">'.(($stat == "Payment approved")?"complete cheque payment":"review cheque").'</span>';
            $str.= '
            <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
            <div class="box">
             <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
            
                <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>

                <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

                <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
                <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.' <i class="fa fa-arrow-right"></i> '.str_replace('::'," <i class='fa fa-arrow-right'></i> ",$destination).'</i></p>           
              

                <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

                <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>';                
                $str.= '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail" value="'.$id.'">View Detail</button>';
           $str .= ' 
                </div>
            </div> 
                ';
          }
          // if((($stat == 'Cheque reviewed'and is_null($row['settlementsent_by'])) or ($stat =='Payment done' and (isset($row['chequenumber']) and $row['chequenumber']=='cheque payment done'))or $stat =='Payment approved')and $_SESSION["role"] == "Cashier" ){
          //   $tag = '<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">complete petty cash payment</span>';               
          //   //$tag="complete petty cash payment";
          //   $str5.= '
          //   <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
          //   <div class="box">
           
          //   <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
            
          //       <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
          //       <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>

          //       <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
          //       <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

          //       <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
          //       <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
          //       <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.' <i class="fa fa-arrow-right"></i> '.str_replace('::'," <i class='fa fa-arrow-right'></i> ",$destination).'</i></p>           
              

          //       <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
          //       <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

          //       <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
          //       <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
          //       <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>';                                           
          //       $str5.= '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail5" value="'.$id.'">View Detail</button>';
          //  $str5 .= ' 
          //       </div>
          //   </div> 
          //       ';
          // }
            $str3.= '
            <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
            <div class="box">
            <h3>
            Job Id - '.$row['job_id'].' | '.$request_date.'';  
            if(isset($mid) && ((($mstatus == 'Senior accountant approved' || $mstatus == 'Payment approved') and $_SESSION["role"] == "Cashier") || ($mstatus == 'Cheque prepared' and $_SESSION["role"] == "Disbursement"))){  
              $str3 .=  '<button type="submit" name="approvemodify" value="'.$id.'" title="Modified Request" class="btn bg-white ms-3 btn-outline-info mx-auto shadow btn-sm"><i class="fas fa-edit"></i></button>';
                  }
              $str3 .=
                '</h3><p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>

                <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

                <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
                <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.' <i class="fa fa-arrow-right"></i> '.str_replace('::'," <i class='fa fa-arrow-right'></i> ",$destination).'</i></p>           
              

                <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

                <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>                             
              <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="history" value="'.$id.'">View Detail</button>';
              if($cashire != '')                                           
                    $str3 .= "<a href='printperdiem.php?print_request=$printid' target='_blank'  class='btn btn-outline-success btn-sm shadow ms-2' id='print_perdiem' name='print'><i class='fa fa-print'></i></a>";                                             
               if(isset($mid) && $paymentprepby != ""){                                          
                $str3 .= "<a href='printperdiem.php?print_modif=$mid' target='_blank'  class='btn btn-outline-success btn-sm shadow ms-2' id='printmodif' name='print'>Print modification</a>";                       
                       }      
                                         

                
                if(($stat=="Settlement cheque approved" || $stat=="Settlement cheque checked" || $stat=="Setlement payment done" || $stat=="Settlement cheque prepared" || $stat=="Settlement payment prepapred" || $stat=="Closed")){
                if(isset($mid)){
                 $str3 .="<a href='printsettlement.php?print_modif=".$id."' class='btn btn-outline-success btn-sm shadow ms-2' id='printmodif_".$id."' name='print'><i>print settlement</i></a>";
                } 
                else {
                $str3 .="<a href='printsettlement.php?print_request= ".$id."' class='btn btn-outline-success btn-sm shadow ms-2' id='printperdiem_".$id."' name='print'><i>print settlement</i></a>";   
                
                }//inner if
              }//outer if

              $str3 .= '</div>
              </div> 
              ';    
             } 
          }
          ?>
          <?php         
            if($_SESSION["role"] == "Cashier"){
            if($_SESSION['company'] == 'Hagbes HQ.')        
              $perdiem_petty =  "SELECT * FROM perdiem where (((`status` = 'Payment done' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty') AND `chequenumber`='cheque payment done') OR `status` = 'Payment approved' OR `status` = 'Cheque reviewed') AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty'))  and  (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
             else
              $perdiem_petty =  "SELECT * FROM perdiem where (((`status` = 'Payment done' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty') AND `chequenumber`='cheque payment done') OR `status` = 'Payment approved' OR `status` = 'Cheque reviewed') AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty'))  and  company = ? ORDER BY dateofrequest desc";
              $perdiem_petty_fetch = $conn_fleet->prepare($perdiem_petty);
              $perdiem_petty_fetch -> bind_param("s", $_SESSION['company']);
              $perdiem_petty_fetch -> execute();
              $result_perdiem_petty = $perdiem_petty_fetch->get_result();
                if($result_perdiem_petty->num_rows>0)
                while($row = $result_perdiem_petty->fetch_assoc())
                {   
                    $printid = $row['id'];
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
                    $stat = $row['status'];
                    $cashire = $row['paymentpreparedby'];
                    $settlementprepby = $row['settlementpayment_prepby'];
           
              if(($stat == 'Cheque reviewed'and is_null($row['settlementsent_by'])) or $stat =='Payment done' or $stat =='Payment approved' ){
            $tag = '<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">complete petty cash payment</span>';               
            //$tag="complete petty cash payment";
            $str5.= '
            <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
            <div class="box">
           
            <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
            
                <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>

                <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

                <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                <p class="text-start mb-2"><span>Destination : </span><i>'.str_replace('::',',',$destination).'</i></p>
                <p class="text-start mb-2"><span>Route : </span><i>'.$departureplace.' <i class="fa fa-arrow-right"></i> '.str_replace('::'," <i class='fa fa-arrow-right'></i> ",$destination).'</i></p>           
              

                <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

                <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>';                                           
                $str5.= '<button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail5" value="'.$id.'">View Detail</button>';
           $str5 .= ' 
                </div>
            </div> 
                ';
          }
          }
        }
          ?>
      <?php 
      if(($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance") OR ($_SESSION["role"] == "Cashier") OR  ($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") OR (strpos($_SESSION["a_type"],"Perdiem") !== false) OR ($_SESSION['perdiem'] == true AND ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor")))){          
          if(strpos($_SESSION["a_type"],"Perdiem") !== false)
          {
            if($_SESSION['company'] == 'Hagbes HQ.')
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
            else
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND  company = ?  ORDER BY dateofrequest desc";
          }
          else if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance")
          { 
            if($_SESSION['company'] == 'Hagbes HQ.')     
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement payment prepared' OR `status` = 'Settlement cheque prepared' OR `status` = 'Settlement checked') AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?)  ORDER BY dateofrequest desc";
            else
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement payment prepared' OR `status` = 'Settlement cheque prepared') AND `next-id` IS NULL AND company = ?";
          }
          else  if(($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") or ($_SESSION['perdiem'] == true AND ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor"))))
          { 
            if($_SESSION["company"] == "Hagbes HQ.")
          $sql_clus = "SELECT * FROM perdiem where `status` = 'Settlement payment checked' AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
            else
          $sql_clus = "SELECT * FROM perdiem where `status` = 'Settlement payment checked' AND `next-id` IS NULL AND company = ? ORDER BY dateofrequest desc";
          }
          else if(($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false))
          { 
            if($_SESSION['company'] == 'Hagbes HQ.')
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement payment approved' OR `status` = 'Settlement cheque approved') AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
            else 
          $sql_clus = "SELECT * FROM perdiem where (`status` = 'Settlement payment approved' OR `status` = 'Settlement reviewed' OR `status` = 'Settlement cheque approved') AND `next-id` IS NULL AND company = ? ORDER BY dateofrequest desc";
          }
        
          $stmt_perdiem_fetch2 = $conn_fleet->prepare($sql_clus);  
          $stmt_perdiem_fetch2 -> bind_param("s", $_SESSION['company']);
          $stmt_perdiem_fetch2 -> execute();
          $result_perdiem_fetch2 = $stmt_perdiem_fetch2->get_result();
          if($result_perdiem_fetch2->num_rows>0)
          while($row = $result_perdiem_fetch2->fetch_assoc())
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
              $tag = '';
              $modification = "SELECT * FROM perdiemmodification where perdiemid='$id' and (`status` = 'Settlement approved' || senior_accountant is not null)";     
              $modresult = $conn_fleet->query($modification); 
              if( $modresult!=false)
              if($modresult->num_rows > 0){ 
              $row22 = $modresult->fetch_assoc(); 
              $mid = $row22['id'];
              $mstatus = $row22['status']; 
              $msenioracc = $row22['senior_accountant'];              
            }else{
               unset($mid);
            }
              if(strpos($_SESSION["a_type"],"Perdiem") !== false || ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)){      
                $tag = ($status == 'Settlement reviewed')?'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">Settle pediem expenses</span>':'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-success shadow border border-success">'.((strpos($_SESSION["a_type"],"Perdiem") !== false)?"Close Settlement":"Prepare settlement payment").'</span>'; 
              } 
              if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance"){
                $tag = ($status == 'Settlement payment prepared')?'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">Approve settlement payment</span>':'<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-success shadow border border-success">Review Settlement Cheque</span>';               
              }
              if($status == 'Settlement cheque approved')
              {
// for settlement payement
              $str2.= '
              <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
              <div class="box">
              <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
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
  
                <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail" value="'.$id.'">View Detail</button>   
                  </div>
              </div> 
                  ';
// end settlement payment string
              }else{
              $str2.= '
              <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
              <div class="box">
              <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
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
  
                <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail2" value="'.$id.'">View Detail</button>   
                  </div>
              </div> 
                  ';
                $str2 .= '<a href="../printperdiem.php?print_request='.$id.'" class="btn btn-outline-success btn-sm shadow d-none ms-2" id="printperdiem_'.$id.'" name="print"><i class="fa fa-print"></i></a>';   
                if(isset($mid)){
                $str2 .= '<a href="../printperdiem.php?print_modif='.$mid.'" class="btn btn-outline-success btn-sm shadow d-none ms-2" id="printmodif_'.$mid.'" name="print">Print modification</a>';
                  }
          } 
          // $hide = (($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance") OR ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false))?' style="display:none"':"";
          
        }
        }  
                // perdiem settlement query

        if(($_SESSION["department"]=="Finance"  and $_SESSION["role"] == "manager") or ($_SESSION['perdiem'] == true AND ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor"))))
          { 
            if($_SESSION["company"] == "Hagbes HQ.")
          $perdiem_settlement_query = "SELECT * FROM perdiem where `status` = 'Settlement payment checked' AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) ORDER BY dateofrequest desc";
            else
          $perdiem_settlement_query = "SELECT * FROM perdiem where `status` = 'Settlement payment checked' AND `next-id` IS NULL AND company = ? ORDER BY dateofrequest desc";
          $perdiem_settlement_stmt = $conn_fleet->prepare($perdiem_settlement_query);  
          $perdiem_settlement_stmt -> bind_param("s", $_SESSION['company']);
          $perdiem_settlement_stmt -> execute();
          $rs_perdiem_settlement = $perdiem_settlement_stmt->get_result();
          if($rs_perdiem_settlement->num_rows>0)
          while($rw = $rs_perdiem_settlement->fetch_assoc())
          {
              $id = $rw['id'];
              $jobid = $rw['job_id'];
              $request_date = $rw['dateofrequest'];
              $company = $rw['company'];
              $role = $rw['role'];
              $department =  $rw['fromdepartment'];
              $reason = $rw['reasonfortrip'];
              $subject = $rw['subject'];
              $customer = $rw['customer_name'];
              $departuredate = $rw['departure_date'];
              $returndate = $rw['return_date'];
              $departureplace = $rw['departure_place'];
              $destination = $rw['destination'];
              $driver = $rw['driver'];
              $travellers = $rw['travellers'];
              $preparedby = $rw['prepared_by'];
              $status = $rw['status'];
              $tag = '';
              $tag = '<span class="position-absolute top-100 start-50 translate-middle badge bg-white text-primary shadow border border-primary">Approve perdiem settlement</span>';                
              $settlement_str.= '
              <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
              <div class="box">
              <div class="position-relative"><h3 style="font-family:Gabriola" class="card-title text-center">Job Id:'. $jobid.' .  |  . '.$request_date.'</h3>' .$tag.' </div>
                  <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$rw['fromdepartment'].'</u></i></p>
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
  
                <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail2" value="'.$id.'">View Detail</button>   
                  </div>
              </div> 
                  ';
          } //end of while
          }// end if 
        ///  end of perdiem settlement query

     $active = ($active != "")?"show active":"";
     $active_2 = ($active_2 != "")?"show active":"";
     ?>  
      <div class="tab-content pt-2" id="myTabContent">        
        <div class="tab-pane fade <?=$active_2?>" id="pills-home" role="tabpanel" aria-labelledby="home-tab">     
              <form  method="POST" action="perdiem.php#pills-home">
              <?php 
              if($str=='') 
                  echo "<div class='py-5 pricing'>
                      <div class='section-title text-center py-2  alert-primary rounded'>
                          <h3 class='mt-4'>There are no perdiem requests</h3>
                      </div>
                  </div>";
              else
                  divcreate($str);
              if($str5=='');
                  // echo "<div class='py-5 pricing'>
                  //     <div class='section-title text-center py-2  alert-primary rounded'>
                  //         <h3 class='mt-4'>There are no perdiem requests</h3>
                  //     </div>
                  // </div>";
              else
                  divcreate5($str5);
          ?>   
          <button data-bs-toggle="modal" id='modal_open' type="button" data-bs-target="#fullscreenModal" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
          <button data-bs-toggle="modal" id='modal_open5' type="button" data-bs-target="#fullscreenModal5" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
        </form>      
        </div>
       
        <!-----------------finance manager perdieme settlement approval----------------------------------->

        <div class="tab-pane fade <?=$active?>" id="pills-pediem-settlement" role="tabpanel" aria-labelledby="profile-tab">        
            <form  method="POST" action="perdiem.php#pills-pediem-settlement">
              <?php 
              if($settlement_str=='') 
                  echo "<div class='py-5 pricing'>
                      <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>There are no settlement to be processed</h3>
                      </div>
                  </div>";
              else
                  divcreate($settlement_str);
               ?>   
            <button data-bs-toggle="modal" id='modal_open2' type="button" data-bs-target="#fullscreenModal2" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
            </form>
        </div>

        <!----end finance manager perdiem settlement  approval---->
        <div class="tab-pane fade show" id="pills-profile" role="tabpanel" aria-labelledby="profile-tab">        
            <form  method="POST" action="perdiem.php#pills-profile">
              <?php 
              if($str2=='') 
                  echo "<div class='py-5 pricing'>
                      <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>There are no settlement to be processed</h3>
                      </div>
                  </div>";
              else
                  divcreate($str2);
               ?>   
            <button data-bs-toggle="modal" id='modal_open2' type="button" data-bs-target="#fullscreenModal2" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
            </form>
        </div>
        <div class="tab-pane fade show" id="pills-history" role="tabpanel" aria-labelledby="home-tab">     
              <form  method="POST" action="perdiem.php#pills-history">
              <?php 
              if($str3=='') 
                  echo "<div class='py-5 pricing'>
                      <div class='section-title text-center py-2  alert-primary rounded'>
                          <h3 class='mt-4'>There are no perdiem records</h3>
                      </div>
                  </div>";
              else
                  divcreate($str3);
          ?>   
          <button data-bs-toggle="modal" id='open_history_modal' type="button" data-bs-target="#fullscreenModal3" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
          <button data-bs-toggle="modal" id='modmodal_open' type="button" data-bs-target="#modificationModal" class="btn btn-outline-primary btn-sm shadow box d-none">modification</button>
          </form>      
        </div>
      </div>
    </div>


<?php

//perdiem petty  payment
?>
<div class="modal fade" id="fullscreenModal5" tabindex="-1">
<div class="modal-dialog modal-xl">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Perdiem request and travel advance</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

<form method="POST" action="perdiem.php#pills-home" class="row">
<div class="modal-body">                    
<div class="card">
<div class="card-body">
  <h5 class="card-title mb-3 text-center">Perdiem request</h5>
<div class="row">
<!-- Vertical Form -->          
<?php 
  if(isset($_POST['detail5'])){
    $buttonname = 'Proceed';
    $i = $_POST['detail5'];
    $totalkm = 0;
    $detail = "SELECT * FROM perdiem where id = ?";
    $stmt_perdiem_by_id = $conn_fleet->prepare($detail);  
    $stmt_perdiem_by_id -> bind_param("i", $i);
    $stmt_perdiem_by_id -> execute();
    $result_perdiem_by_id = $stmt_perdiem_by_id->get_result();
      if($result_perdiem_by_id->num_rows > 0);
      while($detailrow = $result_perdiem_by_id->fetch_assoc()){
      $printid = $detailrow['id'];
      $status = $detailrow['status'];
      $cashire = $detailrow['paymentpreparedby'];
      $km = explode(',',$detailrow['customersite_km']);
      $tripd = explode(',',$detailrow['customersite_km']);
      $days = explode(',',$detailrow['customersite_km']);                      
        ?>
<input type="hidden" name='checkid' value="<?php echo $detailrow['id'] ?>">        
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
<input id="departuredate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['departure_date']))  ?>" class="form-control shadow" readonly>  
</div>
<div class="col-sm-4 mb-2">
  <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
  <input id="returndate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['return_date']))  ?>" class="form-control shadow" readonly>                
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
  for($s = 0;$s < count($split1);$s++){
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
      for($j=0;$j < count($explode1);$j++){           
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
<div class="col-sm-12 mb-2">
<fieldset class="row border bg-white rounded-3">
  <legend class="col-form-label float-none w-auto">Invoices and reciepts</legend>                  
<?php if($detailrow['image'] != ''){
    $split1 = explode('::',$detailrow['description']);
    $split2 = explode('::',$detailrow['image']);
?>
<ul class="list-group">  
   <?php 
   $num = 1;
   $tittle = '';
   for($i = 0;$i < count($split2);$i++){ ?>  
<li class="list-group-item d-flex justify-content-between align-items-center mx-4">
<?php if(isset($split1[$i]) and $split1[$i] != '') $tittle = $split1[$i];?>
  <span class="btn" data-bs-toggle="popover" title="<?php echo $tittle ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
  <span class="ms-2 col-sm-1">               
  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
  <?php if(strpos($split2[$i],'.pdf')===false){ ?>
  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
 <?php } ?>
</span>
</li>                                        
     <?php  $num++;} ?> 
     </ul><!-- End List Group With Contextual classes -->             

<?php }else{ ?>
<h3 class="text-center">No supporting documents uploaded</h3>
<?php   } ?>
</fieldset>
</div>
<div class="row mt-2">
<div class="col-sm-3 mb-2">
   <label for="inputAddress" class="form-label me-3"><b>Prepared By:</b></label>
  <input id="preparedby" type="text" value="<?php echo $detailrow['prepared_by']   ?>" class="form-control shadow" readonly>
</div> 
<div class="col-sm-3 mb-2">
   <label for="inputAddress" class="form-label me-3"><b>Date:</b></label>
  <input id="preparedbydate" type="text" value="<?php echo date('d-M-Y H:i', strtotime($detailrow['dateofrequest'])) ?>" class="form-control shadow" readonly>
</div> 
</div> 

 <?php  
$totalcash = 0;
$traveladvance = "SELECT * from traveladvance where perdime_id = ?";
$stmt_traveladvance_by_perdiem = $conn_fleet->prepare($traveladvance);  
$stmt_traveladvance_by_perdiem -> bind_param("i", $printid);
$stmt_traveladvance_by_perdiem -> execute();
$result_traveladvance_by_perdiem = $stmt_traveladvance_by_perdiem->get_result();
  if($result_traveladvance_by_perdiem->num_rows > 0);
    while($travelrow = $result_traveladvance_by_perdiem->fetch_assoc()){
      //$totalcost = 0;
      $idd = $travelrow['id'];
      $reason = $travelrow['reason'];
      $rate = $travelrow['rate'];
      $day = $travelrow['days'];
      $tv_birr = $travelrow['birr'];     
      $paymentoption = $travelrow['payment_option'];      
      $buttonname="Proceed";    
      if($travelrow['payment_option']=='Petty'){
          ?>
<div style = "border-color: #719ECE;box-shadow: 0 0 10px" >
     
<div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
  <div class="card-body">
<h5 class="card-title mt-3 mb-3 text-center">Travel advance </h5>
<input type="hidden" name="travelid[]" value="<?php echo $travelrow['id'] ?>">
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
    <?php $split1 = ($reason != "")?explode('::',$reason):"";
          $split2 = ($rate != "")?explode('::',$rate):"";
          $split3 = ($day != "")?explode('::',$day):"";
          $split4 = ($tv_birr != "")?explode('::',$tv_birr):"";
          $numlen = is_countable($split1)?count($split1):0;
          for($j = 0;$j < $numlen;$j++){ 
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
    <span class="input-group-text" id="basic-add1"><?php echo ($split1[$j] != 'Fuel')?'Days':'Liter' ?></span>             
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
 <?php  
 //$totalcost += $split4[$j];
 $totalcash += $split4[$j]; 
}  ?>
 </fieldset>
 </div> 
 </div>
 <div class="col-sm-6 col-md-4 m-3">
  <label for="cpvno" class="form-label me-3"><b>PCPV number:</b></label>               
  <input value=" " type="text"   name="cpvno[]" class="form-control" require>                           
</div>               
 </div>
   
<?php  
    }//end if
} // end while  ?>           
</div>
</div>                                            
<?php    
   } 
    }
   ?>                           
  <div class="form-check mt-4 mb-3">
    <label class="form-check-label" for="comfirm">
    I have given total <?php echo  $totalcash ?> birr for the mentioned employees <u id='pettycash' class="text-primary"></u>
    </label>
    <input class="form-check-input checkinfo" name='signature' type="checkbox" value="Payment approved" id="comfirm" required>
  </div>                                                            
         

</div>
</div> 
<div class="modal-footer">
<a type="button" class="btn btn-danger"  data-bs-dismiss="modal">Close</a>
<?php  //if((($stat != "Payment approved" and $cashire == '')  or ($cashire != '' and  $status == 'Cheque prepared')  or ($cashire == '' and $stat == "Payment approved"))){ ?>
  <button type="button" onclick='prompt_confirmation(this)' name = 'savepetty' class="btn btn-primary" value=<?php echo $printid ?> ><?php echo $buttonname ?></button>              
  
</div> 

</div>
</form><!-- Take this print to request perdiem -->  
</div>
</div></div>
</div>
</div><!-- End Full Screen Modal-->
</div>

<?php
// end perdiem pety payment
?>
     <div class="modal fade" id="fullscreenModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Perdiem request and travel advance</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

          <form method="POST" action="perdiem.php#pills-home" class="row">
            <div class="modal-body">                    
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title mb-3 text-center">Perdiem request</h5>
             <div class="row">
              <!-- Vertical Form -->          
              <?php 
                  if(isset($_POST['detail'])){
                    $buttonname = 'Proceed';
                    $i = $_POST['detail'];
                    $totalkm = 0;
                    $detail = "SELECT * FROM perdiem where id = ?";
                    $stmt_perdiem_by_id = $conn_fleet->prepare($detail);  
                    $stmt_perdiem_by_id -> bind_param("i", $i);
                    $stmt_perdiem_by_id -> execute();
                    $result_perdiem_by_id = $stmt_perdiem_by_id->get_result();
                      if($result_perdiem_by_id->num_rows > 0);
                      while($detailrow = $result_perdiem_by_id->fetch_assoc()){
                      $printid = $detailrow['id'];
                      $status = $detailrow['status'];
                      $cashire = $detailrow['paymentpreparedby'];
                      $km = explode(',',$detailrow['customersite_km']);
                      $tripd = explode(',',$detailrow['customersite_km']);
                      $days = explode(',',$detailrow['customersite_km']);                      
                        ?>
                <input type="hidden" name='checkid' value="<?php echo $detailrow['id'] ?>">        
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
                <input id="departuredate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['departure_date']))  ?>" class="form-control shadow" readonly>  
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
                  <input id="returndate" type="text" value="<?php echo date("d-m-Y H:i",strtotime($detailrow['return_date']))  ?>" class="form-control shadow" readonly>                
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
                  for($s = 0;$s < count($split1);$s++){
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
                      for($j=0;$j < count($explode1);$j++){           
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
                <div class="col-sm-12 mb-2">
                <fieldset class="row border bg-white rounded-3">
                  <legend class="col-form-label float-none w-auto">Invoices and reciepts</legend>                  
             <?php if($detailrow['image'] != ''){
                    $split1 = explode('::',$detailrow['description']);
                    $split2 = explode('::',$detailrow['image']);
              ?>
              <ul class="list-group">  
                   <?php 
                   $num = 1;
                   $tittle = '';
                   for($i = 0;$i < count($split2);$i++){ ?>  
                <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                <?php if(isset($split1[$i]) and $split1[$i] != '') $tittle = $split1[$i];?>
                  <span class="btn" data-bs-toggle="popover" title="<?php echo $tittle ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                  <span class="ms-2 col-sm-1">               
                  <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                  <?php if(strpos($split2[$i],'.pdf')===false){ ?>
                  <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                  <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                 <?php } ?>
                </span>
                </li>                                        
                     <?php  $num++;} ?> 
                     </ul><!-- End List Group With Contextual classes -->             
             
              <?php }else{ ?>
                <h3 class="text-center">No supporting documents uploaded</h3>
                <?php   } ?>
                </fieldset>
                </div>
             <div class="row mt-2">
                <div class="col-sm-3 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Prepared By:</b></label>
                  <input id="preparedby" type="text" value="<?php echo $detailrow['prepared_by']   ?>" class="form-control shadow" readonly>
                </div> 
                <div class="col-sm-3 mb-2">
                   <label for="inputAddress" class="form-label me-3"><b>Date:</b></label>
                  <input id="preparedbydate" type="text" value="<?php echo date('d-M-Y H:i', strtotime($detailrow['dateofrequest'])) ?>" class="form-control shadow" readonly>
                </div> 
                </div> 
            
                 <?php  
                  $totalcash = 0;
                $traveladvance = "SELECT * from traveladvance where perdime_id = ?";
                $stmt_traveladvance_by_perdiem = $conn_fleet->prepare($traveladvance);  
                $stmt_traveladvance_by_perdiem -> bind_param("i", $printid);
                $stmt_traveladvance_by_perdiem -> execute();
                $result_traveladvance_by_perdiem = $stmt_traveladvance_by_perdiem->get_result();
                  if($result_traveladvance_by_perdiem->num_rows > 0);
                    while($travelrow = $result_traveladvance_by_perdiem->fetch_assoc()){
                      $totalcost=0;
                      $tv_birr=0;// trvale advance birr
                      $idd = $travelrow['id'];
                      $reason = $travelrow['reason'];
                      $rate = $travelrow['rate'];
                      $day = $travelrow['days'];
                      $totalcost = $travelrow['birr'];
                      $paymentoption = $travelrow['payment_option'];
                      $buttonname="Proceed";

// perdiem settlement payment by casher

                          $perdiemsettlement = "SELECT * from perdiemsettlement where travel_advanceid = ?"; 
                          $stmt_perdiemsettlement = $conn_fleet -> prepare($perdiemsettlement);                      
                          $stmt_perdiemsettlement -> bind_param("i", $idd);
                          $stmt_perdiemsettlement -> execute();                       
                          $result_settlement = $stmt_perdiemsettlement -> get_result();    
                          $strow = $result_settlement->fetch_assoc();
                          ?>
                <div style = "border-color: #719ECE;box-shadow: 0 0 10px" >
                <?php if($result_settlement->num_rows > 0 and $strow['payment_option'] !="Paid in Cheque"); //$ps=true;// perdiem settlement have data for this perdiem request?
                //if(is_null($strow['payment_option']) and isset($strow['payment_option']));
                else if(isset($strow['payment_option']) and $strow['payment_option']=='Paid in Cheque' and $status == 'Settlement cheque approved')
                {
                  
                    $chqnumber=$strow['cheque_number'];
                    $cpvnmbr=$strow['cpv_number'];
                    $bank=$strow['bank'];
                    $rate=$strow['rate'];
                    $totalcost=$strow['birr']; 
                    $dy=explode('::',$strow['days'])[0];   
                    $req_day=$strow['daysby_requester']; 
                    $setled_day= (int)$req_day-(int)$dy;    
                    $buttonname="Settlement payed"                                   
                    
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
                  <input value="<?php echo  $setled_day ?>"  type="text" aria-describedby="basic-add1" class="form-control" readonly>  
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
                  <?php $totalcash += $totalcost;  ?>
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
                      <input type="hidden" name="pettyexists[]" value="<?php echo  $travelrow['id']  ?>">
                  
                </div>
              </div>
           </div>                                     
                                                                             
              <?php                
                }else{

/// end

                  ?>            
                <div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
                  <div class="card-body">
              <h5 class="card-title mt-3 mb-3 text-center">Travel advance </h5>
              <input type="hidden" name="travelid[]" value="<?php echo $travelrow['id'] ?>">
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
                    <?php $split1 = ($reason != "")?explode('::',$reason):"";
                          $split2 = ($rate != "")?explode('::',$rate):"";
                          $split3 = ($day != "")?explode('::',$day):"";
                          $split4 = ($totalcost != "")?explode('::',$totalcost):"";
                          $numlen = is_countable($split1)?count($split1):0;
                          for($j = 0;$j < $numlen;$j++){ 
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
                    <span class="input-group-text" id="basic-add1"><?php echo ($split1[$j] != 'Fuel')?'Days':'Liter' ?></span>             
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
                 <?php if($travelrow['payment_option'] == 'Cheque')$totalcash += $split4[$j];
                $tv_birr += $split4[$j];}  ?>
                 </fieldset>
                 </div>
                 <?php                 
                 $selectplimit = $conn->prepare("SELECT * FROM `limit_ho` where company = ?"); 
                 $selectplimit->bind_param("s", $_SESSION["company"]);
                 $selectplimit->execute();
                 $limitres = $selectplimit->get_result();
                 if(mysqli_num_rows($limitres)>0){
                 $row = $limitres->fetch_assoc();
                 $pittycash = $row['perdiem_pettycash']; 
                // echo "<script>alert('".$pittycash."')</script>";
                 }else{
                   $selectplimit = $conn->prepare("SELECT * FROM `limit_ho` where company = 'Others'");                      
                   $selectplimit->execute();
                   $limitres = $selectplimit->get_result();
                   $row = $limitres->fetch_assoc();
                   $pittycash = $row['perdiem_pettycash'];
                 }
                 //if()
                 if($_SESSION["role"] == "Cashier" and ($status != "Payment approved" and $tv_birr>$pittycash))// AND ($travelrow['payment_option'] == 'Cheque'))
                  { $buttonname = 'Proceed';
                   ?>
                   <div class="row mt-3"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number: </b></label>               
                       <input type="text" name="checkno[]" class="form-control" required>                           
                        </div> 
                  <div class="col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input type="text"  name="cpvno[]" class="form-control" required>                           
                    </div>      
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <select type="text" name="bankname[]" class="form-select" required> 
                        <option value="">Select Bank</option>
                         <?php 
                         $bank = "SELECT * FROM `banks`";
                         $stmt_banks = $conn -> prepare($bank);
                         $stmt_banks -> execute();
                         $result_banks = $stmt_banks->get_result();
                          if($result_banks->num_rows > 0);
                            while($bankrow = $result_banks->fetch_assoc()){ ?>
                          <option value="<?php echo $bankrow['bank'] ?>"><?php echo $bankrow['bank'] ?></option>
                           <?php  }
                         ?>                      
                       </select>
                        </div>              
                   </div>
                   <?php  }
                   else if(($_SESSION["role"] == "Cashier" and ($status == "Payment approved") and $travelrow['payment_option'] == 'Cheque') OR ($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance" AND $travelrow['payment_option'] == 'Cheque')){ $buttonname = 'Reviewed';?>
                                     <div class="row mt-3"> 
                                     <div class="col-sm-4 mb-2">
                                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                                         <input type="text" value="<?php echo $travelrow['cheque_number'] ?>"  class="form-control" readonly>                                                                   
                                          </div> 
                                    <div class="col-md-4 mb-2">
                                        <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                                        <input type="text" value="<?php echo $travelrow['cpv_number'] ?>"   class="form-control" readonly>                           
                                      </div>      
                                     <div class="col-sm-4 mb-2">
                                        <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                                      <input type="text" value="<?php echo $travelrow['bank'] ?>"  class="form-control" readonly>
                                          </div>              
                                     </div>
                 <?php  }else{  ?>
                  <div class="row mt-3 d-none"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                       <input type="text" name="checkno[]" class="form-control">                           
                        </div> 
                  <div class="col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input type="text"  name="cpvno[]" class="form-control">                           
                    </div>      
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <select type="text" name="bankname[]" class="form-select"> 
                        <option value="">Select Bank</option>
                        <?php 
                         $bank = "SELECT * FROM `banks`";
                         $stmt_banks = $conn -> prepare($bank);
                          $stmt_banks -> execute();
                          $result_banks = $stmt_banks->get_result();
                            if($result_banks->num_rows > 0);
                              while($bankrow = $result_banks->fetch_assoc()){ ?>
                          <option value="<?php echo $bankrow['bank'] ?>"><?php echo $bankrow['bank'] ?></option>
                           <?php  }
                         ?>
                       
                       </select>
                        </div>              
                   </div>
                <?php   }   ?>           
              </div>
           </div>                                             
              
              <?php    
                   } }
                   ?> 
            <?php if($_SESSION["role"] == "Cashier" and ($status == "Payment approved" or $status == "Settlement cheque approved") ){ ?>                          
                  <div class="form-check mt-4 mb-3">
                    <label class="form-check-label" for="comfirm">
                    I have given total <?php echo  $totalcash ?> birr for the mentioned employees <u id='pettycash' class="text-primary"></u>
                    </label>
                    <input class="form-check-input checkinfo" name='signature' type="checkbox" value="Payment approved" id="comfirm" required>
                  </div>                                                            
              <?php } 
                 }
               }   
                 ?>             
          
        </div>
      </div> 
              <div class="modal-footer">
                <a type="button" class="btn btn-danger"  data-bs-dismiss="modal">Close</a>
                <?php  //if((($stat != "Payment approved" and $cashire == '')  or ($cashire != '' and  $status == 'Cheque prepared')  or ($cashire == '' and $stat == "Payment approved"))){ ?>
                  <button type="button" onclick='prompt_confirmation(this)' name = 'registercheck' class="btn btn-primary" value=<?php echo $printid ?> ><?php echo $buttonname ?></button>              
               <?php //} ?>
                </div> 

             </div>
            </form><!-- Take this print to request perdiem -->  
                </div>
                </div></div>
          </div>
        </div><!-- End Full Screen Modal-->
        </div>


<script>        
<?php 
if(isset($_POST['registercheck']))
{?>
document.getElementById('print_perdiem').click();
<?php
unset($_POST['registercheck']);
}?>
</script> 

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
                                    $stmt_prediem_complex_id = $conn_fleet->prepare($detail);
                                    $stmt_prediem_complex_id -> bind_param("i", $i);
                                    $stmt_prediem_complex_id -> execute();
                                    $result_prediem_complex_id = $stmt_prediem_complex_id->get_result();
                                        if($result_prediem_complex_id->num_rows > 0);
                                        while($row = $result_prediem_complex_id->fetch_assoc()){  
                                          $i1 = $row['pid'];
                                          $i2 = $row['id'];
                                          $status = $row['status'];
                                          ?>                                 
                                  <input type="hidden" id="statusid" name="hiddenstat" value="<?php echo $row['status']  ?>">                                                    
                                  <input type="hidden" name='checkid' value="<?php echo $row['id'] ?>"> 
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
                                    for($s = 0;$s < count($split1);$s++){
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
                                  for ($i = 0;$i < count($splitt);$i++) { ?>
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
                                    <!-- <div class="row mb-3"> -->
                                  <label class="col-sm-2 col-form-label">Added Customer site km:</label> 
                                     <?php   
                                        $explode1 = explode(',',$row['customersite_km']);
                                        $explode2 = explode(',',$row['tripperday']);
                                        $explode3 = explode(',',$row['daysof_stay']);

                                        $explode11 = explode(',',$row['customersite_km']);
                                        $explode22 = explode(',',$row['tripperday']);
                                        $explode33 = explode(',',$row['daysof_stay']);
                                        for($j=0;$j < count($explode1);$j++){           
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
                                    for($i = 0;$i < count($split2);$i++){ ?>  
                                  <li class="list-group-item d-flex justify-content-between align-items-center mx-4">
                                  <?php if(isset($split1[$i])) $tittle = $split1[$i];?>
                                    <span class="btn" data-bs-toggle="popover" title="Popover title" data-bs-content="<?php echo $tittle  ?>">File <?php echo $num ?> - <span class="text-primary"><?php echo $split2[$i]  ?></span></span>
                                    <span class="ms-2 col-sm-1">               
                                    <a href="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" target='_blank'  class="btn btn-outline-primary btn-sm mt-1" tittle="Download file"><i class="bi bi-download"></i></a>
                                    <button type="button" id="viewid_<?php echo $num ?>" onclick="displayimage(this)" class="popup btn btn-outline-primary btn-sm mt-1" tittle="View file"><i class="bi bi-eye"></i></button>                  
                                    <img src="https://portal.hagbes.com/receiptphoto/<?php  echo $split2[$i] ?>" id="vieww_<?php echo $num ?>" class="d-none" loading="lazy">                          
                                  </span>
                                  </li>                                        
                                      <?php  $num++;} ?> 
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
                  if($row['mreturndate'] != $row['preturndate'])
                  {
                   $traveladvance = "SELECT * from tadvancemodification where perdime_id = ?";
                   $stmt_travelmodifcation = $conn_fleet->prepare($traveladvance);
                   $stmt_travelmodifcation -> bind_param("i", $i2);
                  }
                  else
                  {
                   $traveladvance = "SELECT * from tadvancemodification where name_of_employee NOT IN (SELECT name_of_employee FROM traveladvance where perdime_id = ?) and  perdime_id = ?";
                   $stmt_travelmodifcation = $conn_fleet->prepare($traveladvance);
                   $stmt_travelmodifcation -> bind_param("ii", $i1, $i2);
                  }
                  $stmt_travelmodifcation -> execute();
                  $result_travelmodifcation = $stmt_travelmodifcation->get_result();
                   if($result_travelmodifcation->num_rows > 0);
                    while($travelrow = $result_travelmodifcation->fetch_assoc()){
                      $idd = $travelrow['id'];
                      $reason = $travelrow['reason'];
                      $rate = $travelrow['rate'];
                      $days = $travelrow['days'];
                      $totalcost = $travelrow['birr'];
                      $paymentoption = $travelrow['payment_option'];
                  ?>            
                <div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
                  <div class="card-body">
              <h5 class="card-title mt-3 mb-3 text-center">Travel Advance</h5>
              <input type="hidden" name="travelid[]" value="<?php echo $travelrow['id'] ?>">
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
                        for($j = 0;$j < count($split1);$j++){
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
                    <span class="input-group-text" id="basic-add1"><?php echo ($split1[$j] != 'Fuel')?'Days':'Liter' ?></span>             
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
                 <?php $totalcash += $split4[$j];}  ?>
                 </fieldset>
                 </div>
                 <?php                               
                   if($_SESSION["role"] == "Cashier" AND ($status != "Payment approved") AND ($travelrow['payment_option'] == 'Cheque')){ $buttonname = 'Proceed';
                   ?>
                   <div class="row mt-3"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                       <input type="text" name="checkno[]" class="form-control" required>                           
                        </div> 
                  <div class="col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input type="text"  name="cpvno[]" class="form-control" required>                           
                    </div>      
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <select type="text" name="bankname[]" class="form-select" required> 
                        <option value="">Select Bank</option>
                         <?php 
                         $bank = "SELECT * FROM `banks`";
                         $stmt_banks = $conn -> prepare($bank);
                         $stmt_banks -> execute();
                         $result_banks = $stmt_banks->get_result();
                          if($result_banks->num_rows > 0);
                            while($bankrow = $result_banks->fetch_assoc()){ ?>
                          <option value="<?php echo $bankrow['bank'] ?>"><?php echo $bankrow['bank'] ?></option>
                           <?php  }
                         ?>                       
                       </select>
                        </div>              
                   </div>
                   <?php  }
                   else if(($_SESSION["role"] == "Cashier" and $status == "Payment approved" and $travelrow['payment_option'] == 'Cheque') OR ($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance" AND $travelrow['payment_option'] == 'Cheque')){ $buttonname = 'Reviewed';?>
                                     <div class="row mt-3"> 
                                     <div class="col-sm-4 mb-2">
                                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                                         <input type="text" value="<?php echo $travelrow['cheque_number'] ?>"  class="form-control" readonly>                                                                   
                                          </div> 
                                    <div class="col-md-4 mb-2">
                                        <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                                        <input type="text" value="<?php echo $travelrow['cpv_number'] ?>"   class="form-control" readonly>                           
                                      </div>      
                                     <div class="col-sm-4 mb-2">
                                        <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                                      <input type="text" value="<?php echo $travelrow['bank'] ?>"  class="form-control" readonly>
                                          </div>              
                                     </div>
                 <?php  }else{  ?>
                  <div class="row mt-3 d-none"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                       <input type="text" name="checkno[]" class="form-control">                           
                        </div> 
                  <div class="col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>CPV number:</b></label>               
                      <input type="text"  name="cpvno[]" class="form-control">                           
                    </div>      
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <select type="text" name="bankname[]" class="form-select"> 
                        <option value="">Select Bank</option>
                         <?php 
                         $bank = "SELECT * FROM `banks`";
                         $stmt_banks = $conn -> prepare($bank);
                          $stmt_banks -> execute();
                          $result_banks = $stmt_banks->get_result();
                            if($result_banks->num_rows > 0);
                             while($bankrow = $result_banks->fetch_assoc()){ ?>
                          <option value="<?php echo $bankrow['bank'] ?>"><?php echo $bankrow['bank'] ?></option>
                           <?php  }
                         ?>
                       
                       </select>
                        </div>              
                   </div>
                <?php   }   ?>           
              </div>
           </div>                                             
              
              <?php    
                   } 
                   ?> 
            <?php if($_SESSION["role"] == "Cashier" and $status == "Payment approved"){ $buttonname = 'Proceed';?>                          
                  <div class="form-check">
                    <label class="form-check-label" for="comfirm">
                      I have given total <?php echo  $totalcash ?> birr  for the mentioned employees <u id='pettycash' class="text-primary"></u>
                    </label>
                    <input class="form-check-input checkinfo" name='signature' type="checkbox" value="Payment approved" id="comfirm" required>
                  </div>                                                            
              <?php }  ?>                              
                                
                                  <?php  
                                    } 
                                  }  
                                  ?>
                                </div>                               
                              </div>
                            </div>
              <div class="text-center col-sm-12 mb-2">
              <button type="button" data-bs-dismiss="modal" class="btn btn-outline-danger"> Close </button>
              <button type="button" onclick="prompt_confirmation(this)" name="modsubmitchange" id="approved" value="Approved" class="btn btn-outline-success" ><?php echo $buttonname ?> <i class="fas fa-check-circle"></i></button>
              </div> 
                          </div>
                       </form>
                </div> 
            </div>
      </div> 
<!---modal settlement paying---->



<!----end for modal settlement paying---->


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
           if(isset($_POST['detail2'])){
            $sid = $_POST['detail2'];
            $mid = '';
            $detail = "SELECT * FROM perdiem where id = ?";
            $stmt_perdiem_by_id = $conn_fleet->prepare($detail);  
            $stmt_perdiem_by_id -> bind_param("i", $sid);
            $stmt_perdiem_by_id -> execute();
            $result_perdiem_by_id = $stmt_perdiem_by_id->get_result();
            if($result_perdiem_by_id -> num_rows > 0)
             while($srow = $result_perdiem_by_id->fetch_assoc()){ 
              $pid = $srow['id'];                   
              $modification = "SELECT * FROM perdiemmodification where perdiemid= $pid and (`status` = 'Settlement approved' || senior_accountant is not null)";     
              $modresult = $conn_fleet->query($modification); 
              if( $modresult != false)
              if($modresult->num_rows > 0){ 
              $row22 = $modresult->fetch_assoc(); 
              $mid = $row22['id'];              
              $mstatus = $row22['status']; 
              $msenioracc = $row22['senior_accountant'];              
              }else{
               unset($mid);
              }                 
            //    $modification = "SELECT * FROM perdiemmodification where perdiemid='$id' and (`status` = 'Settlement approved' || senior_accountant is not null)";     
            //   $modresult = $conn->query($modification); 
            //   if( $modresult!=false)
            //   if($modresult->num_rows > 0){ 
            //   $row22 = $modresult->fetch_assoc(); 
            //   $mid = $row22['id'];
            //   $mstatus = $row22['status']; 
            //   $msenioracc = $row22['senior_accountant'];              
            // }else{
            //    unset($mid);
            // }
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
            <?php
            // perdiem petty cash payment
             
            // end petty cash payment
            ?>
            <div class="row"> 
            <div class="col-sm-12 card p-3 mt-2">            
                 <div class="card-body">                   
            <?php
            $allid = $srow['id'];
            $allid2 = isset($mrow['id'])?$mrow['id']:null;
            $netdiff = 0;
            $totaldiff = 0;
            $counterrr = 1;
            $perdiem_modification_num_rw=0;
            if($result_perdiemmodification->num_rows > 0)
            {
              //echo"<script>alert('".$allid."+".$allid2."')</script>";
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
                  if($perdiem_modification_num_rw > 0)
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

                  //$travel_modandtravel_adv_payedcost=
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
                
                <!----------------------------------------------------------------------------------------------------->
                <?php                
                $totalexpense = 0;
                $Fuelfound = false;
                $split_val=count($split11);
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
                    // condition for if fuel payment is payed first
                  }else if(isset($splitm11[$j]))
                     {?>

                      <div class="row">               
                      <!-- <div class="col-md-6"> -->
                      <div class="col-md-3">
                        <div class="form-floating shadow box mb-3">
                          <input type="text" value="<?php echo "Travel Advance ".$splitm11[$j] ?>"  class="form-control" id="reason" aria-label="State"  readonly>                                     
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
                    
                    // end condition for fuel first payed
                    
                    } //end outer for loop ?> 
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
                      $all_payedexpense=0;
                      $totalfuel = 0;
                      $current_payment=0;
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
                  <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked') || ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?>
                  <input  name="rate[]" id="rate_<?php echo $employee ?>::<?php echo $j ?>"   onkeyup="Find_birr(this)" type="Number" step="any"  aria-describedby="basic-add" class="form-control border border-primary" Required> 
                  <?php  } else{ ?>
                  <input value="<?php echo $split22[$j] ?>"  id="rate_<?php echo $employee ?>::<?php echo $j ?>" type="Number" step="any"  aria-describedby="basic-add" class="form-control" readonly>                   
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
                          $function = 'onkeyup="Find_birr(this)"';
                        } 
                        if($split21[$j] == 'Perdiem')
                        {
                          $numofday = $split23[$j];
                        }else{
                          $numofday = $split23[$j];
                        }  
                 ?>
                  <div class="col-sm-3">
                    <div class="input-group shadow box mb-3"> 
                      <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked') || ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){  ?>
                  <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?> name="days[]" type="Number" step="any"  aria-describedby="basic-add1" class="form-control" required>  
                       <?php }else{  ?>
                  <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?>  type="Number" step="any"  aria-describedby="basic-add1" class="form-control" readonly>                        
                    <?php   }  ?>  
                  <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating shadow box input-group mb-3"> 
                    <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked') || ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?>
                    <input name="birr[]"  id="birr_<?php echo $employee ?>::<?php echo $j ?>" class="birr_<?php echo $employee ?> form-control" type="Number" step="any"  aria-describedby="basic-add2" readonly>  
                    <?php  }else { ?>
                    <input value="<?php echo $numofday* $split22[$j]?>"  id="birr_<?php echo $employee ?>::<?php echo $j ?>" type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>  
                          <?php 
                        //$totalexpense2 += $split24[$j]; 
                        $netdiff=$allrow['settlementbirr'];
                        $current_payment+=$numofday* $split22[$j];
                        
                         } //echo "<script>alert('".$split24+$allrow['travelbirr']."')</script>";?>  
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
                      <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked')|| ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?>  
                      <input  name="currentpayment[]" id="currpayment_<?php echo $employee ?>" type="Number"  aria-describedby="basic-add2" class="form-control" readonly>
                      <?php  }else{ ?>
                        <input value="<?php echo $current_payment ?>" name="currentpayment[]" type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>
                        <?php  } ?>  
                      <span class="input-group-text" id="basic-add2">Current Payment</span> 
                      <label for="floatingSelect">In ETB</label>            
                        </div>
                        </div>
                           <?php 
                              // $netdiff = $totalexpense2 - $totalexpense;
                              // $totaldiff += $netdiff;
                           ?>
                        <div class="col-sm-6">
                      <div class="form-floating shadow box input-group mb-3"> 
                      <?php if(((strpos($_SESSION["a_type"],"Perdiem") !== false and $st!='Settlement payment checked')|| ($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false)) and $st != 'Settlement payment approved'){ ?> 
                        <input  id="netdifference_<?php echo $employee ?>" name='netdifference[]' type="Number"  aria-describedby="basic-add2" class="form-control" readonly> 
                        <?php  }else{ ?>                         
                      <input value="<?php echo  $allrow['settlementbirr']  ?>" name='netdifference[]'  type="Number"  aria-describedby="basic-add2" class="form-control" readonly>
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
              <?php if($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false and ($st == 'Cheque reviewed' or $st == 'Settlement payment approved')){  $buttonname = 'Prepare Settlement'; ?>
                <div class="row mt-3">
                 <?php
                    $selectplimit = $conn->prepare("SELECT * FROM `limit_ho` where company = ? ORDER BY `date` desc LIMIT 1"); 
                    $selectplimit->bind_param("s", $_SESSION["company"]);
                    $selectplimit->execute();
                    $limitres = $selectplimit->get_result();
                    if(mysqli_num_rows($limitres)>0){
                    $row = $limitres->fetch_assoc();
                    $pittycash = $row['perdiem_pettycash']; 
                   // echo "<script>alert('".$pittycash."')</script>";
                    }else{
                      $selectplimit = $conn->prepare("SELECT * FROM `limit_ho` where company = 'Others'");                      
                      $selectplimit->execute();
                      $limitres = $selectplimit->get_result();
                      $row = $limitres->fetch_assoc();
                      $pittycash = $row['perdiem_pettycash'];
                    }                    
                    if($netdiff >= $pittycash){   ?>
                  <div class="row col-sm-12 col-md-8" id="checkid_<?php echo $employee ?>"> 
                    <div class="col-sm-6 mb-2">
                        <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                        <input type="text" id="schequeno_<?php echo $allrow['travel_advanceid'] ?>" name="schequeno[]" class="form-control border border-primary shadow checkinfo_<?php echo $employee ?>" required>                           
                    </div>                          
                   <div class="col-sm-6  mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                       <select type="text" id="sbank_<?php echo $allrow['travel_advanceid'] ?>" name="sbankname[]" class="form-select border border-primary shadow checkinfo_<?php echo $employee ?>" required> 
                        <option value="">Select Bank</option>
                         <?php 
                         $bank = "SELECT * FROM `banks`";
                         $stmt_banks = $conn -> prepare($bank);
                          $stmt_banks -> execute();
                          $result_banks = $stmt_banks->get_result();
                          if($result_banks->num_rows > 0);
                            while($bankrow = $result_banks->fetch_assoc()){ ?>
                          <option value="<?php echo $bankrow['bank'] ?>"><?php echo $bankrow['bank'] ?></option>
                           <?php 
                            }
                           ?>                       
                       </select>
                     </div>
                  </div>
                  <div class="col-sm-6 col-md-4 mb-2" id="cpv_<?php echo $employee ?>">
                    <label for="cpvno" class="form-label me-3"><b>CPV Number:</b></label>             
                      <input type="number" id="cpv_<?php echo $allrow['travel_advanceid'] ?>" name="scpvno[]"  class="form-control checkinfo2_<?php echo $employee ?> border shadow border-primary" required>                           
                  </div> 
                  <?php }else if($netdiff > 0 && $netdiff < $pittycash){ ?>
                  <div class="col-sm-6 col-md-4 mb-2" id="cpv_<?php echo $employee ?>">
                    <label for="pcpvno" class="form-label me-3"><b>PCPV Number:</b></label>             
                      <input type="text" id="pcpv_<?php echo $allrow['travel_advanceid'] ?>" name="spcpvno[]"  class="form-control checkinfo2_<?php echo $employee ?> border shadow border-primary" required>                           
                  </div> 
                   <?php }else if($netdiff < 0){  ?>
                    <div class="col-sm-6 col-md-4 mb-2" id="crv_<?php echo $employee ?>">
                    <label for="crvno" class="form-label me-3"><b>CRV Number:</b></label>               
                      <input type="text" id="crv_<?php echo $allrow['travel_advanceid'] ?>" name="scrvno[]"  class="form-control checkinfo3_<?php echo $employee ?> border shadow border-primary" required>                           
                  </div> 
                   <?php } ?>                 
                  </div>                             
                  <?php }else{ ?>
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
                    <?php 
                          } 
                       if($pcpv2 != "" and $pcpv2 != 0)
                          { ?> 
                    <div class="col-sm-6 col-md-4 mb-2">
                      <label for="cpvno" class="form-label me-3"><b>PCPV number:</b></label>               
                      <input value="<?php echo  $pcpv2  ?>" type="text"   name="cpvno" class="form-control" readonly>                           
                    </div> 
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
                 <?php  } ?>
                 </div>
             <!----------------------------------------------------------------------------------------------------->
                </div>
                 <?php                  
                 if($st == 'Settlement payment approved' AND $_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false){ $buttonname = 'Proceed';                 
                 if($netdiff > 0){ 
                  if($netdiff <= $pittycash){
                ?>
                  <div class="form-check mt-3">
                    <label class="form-check-label" for="comfirm">
                      I Have Settled the difference in Cash 
                    </label>
                    <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="radio" value="Paid in Cash" id="Cash" required>
                  </div> 
                  <?php }else{ ?> 
                  <div class="form-check">
                    <label class="form-check-label" for="comfirm">
                      I Have Settled the difference in Cheque
                    </label>
                    <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="radio" value="Paid in Cheque" id="Cheque" required>
                  </div>                 
                  <?php } 
                          }
                      else if($netdiff != 0)
                          { ?>
                          <div class="form-check mt-3">
                            <label class="form-check-label" for="comfirm">
                              I Have recieved the difference in cash
                            </label>
                            <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="radio" value="Recieved in Cash" id="Cash" required>
                          </div>  
                          <div class="form-check">
                            <label class="form-check-label" for="comfirm">
                              Deduct from salary
                            </label>
                            <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="radio" value="Deduct from Salary" id="Salary" required>
                          </div> 
                      <?php } ?>
                <!--***********************************************************************************************************************************-->  
                 <?php if($netdiff != 0){   ?>   
                  <fieldset  class="row col-sm-6 border shadow box rounded-3 p-3 mt-3 mb-3">
                  <legend class="col-form-label float-none w-auto"><b>Reciepts(Optional)</b></legend>
                  <input type="hidden" name="recieptcounter_<?php echo $allrow['travel_advanceid'] ?>" id="coun_<?php echo  $counterrr ?>" value="1">
                  <div class="row  mb-3">
                  <div id="root" class="col-sm-11">
                    <div class="row">
                  <div style="display:inline-block;position:relative;" class="col-sm-6 me-5 mb-3">   
                  <textarea style="display:block;" class="form-control" name="description[]" id="description_<?php echo $counterrr?>::1" rows ="3" placeholder="Reciepts..."></textarea><!-- data-bs-toggle="modal"--> <!--data-bs-target="#exampleModal"-->
                  <a href="#" type="button" style="position:absolute;top:60px;right:10px;" class="text-light alert-dark px-2 shadow rounded-circle"><i class="bi bi-upload fa-2x"></i></a>
                  <input type="file" style="position:absolute;top:75px;right:10px;opacity:0;" accept="image/* application/pdf" id="invoicecamera_<?php echo $counterrr?>::1" onclick='get_id(this)' class="form-control"  capture="capture" onchange ='loadFile(event)'>  
                  </div>
              
                  <div class="col-sm-4 col-sm-3 float-end mb-3 mx-auto">
                  <div class="card w-50 h-50 card-img-top">
                  <img src="../img/gallery.jpg" class="popup rounded camera--output img-fluid img-thumbnail" style="cursor:zoom-in" loading="lazy" alt="" id="camera--output_<?php echo $counterrr?>::1"> 
                  <button type='button' id="actualname_<?php echo $counterrr?>::1" class='btn border-secondary text-secondary'></button>
                    <input type="hidden" name="image[]" id="actualimage_<?php echo $counterrr?>::1">
                    </div>
                  </div>
                  </div>
                  </div>
                  <div class="col-sm-1 mt-3"><button class='btn btn-outline-info mt-3' onclick='moreCamera(this)' name="1_<?php echo $counterrr?>" id="addcamera_<?php echo $counterrr?>" type='button'><i class="bi bi-plus"></i></button></div>
                  <div class="col-12" id="addedcamera_<?php echo $counterrr?>"></div>
                </div>
              </fieldset> 
              <?php  } ?>
              <!--***********************************************************************************************************************************--> 
              <?php }else if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st == 'Payment processed'){ $buttonname = 'Proceed';?>
                   <input type="hidden" name="signature[]">
               <?php   if($potion == 'Deduct from Salary'){ ?>
                      <div class="form-check mt-3">
                            <label class="form-check-label" for="comfirm">
                              <b>I have deducted payment from salary</b>
                            </label>
                            <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="checkbox" id="Cash" required>
                          </div> 
                    <?php   
                      }else{ ?>
                    <?php if($netdiff == 0){  ?>
                  <div class="col-sm-6 col-md-4 mb-2" id="jv_<?php echo $employee ?>">
                    <label for="cpvno" class="form-label me-3"><b>JV Number:</b></label>               
                      <input type="text" id="jv_<?php echo $allrow['travel_advanceid'] ?>" name="jvno[]"  class="form-control checkinfo3_<?php echo $employee ?> border shadow border-primary" required>                           
                  </div>
                       <?php } ?>
                          <div class="form-check mt-3">
                            <label class="form-check-label" for="comfirm">
                              <b>Settlement done</b>
                            </label>
                            <input class="form-check-input" name="signature_<?php echo $allrow['travel_advanceid'] ?>" type="checkbox" id="Cash" required>
                          </div>  
                      <?php 
                      }
                      ?>
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
             <input type="hidden" name = 'perid' value="<?php echo   $pid ?>">                    
            </div> 
              <!--approval goes here -->                        
               <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="submit" name='approvesettlement' class="btn btn-success btn-sm"><?php echo $buttonname ?></button>
                <?php 
                if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance"){
                if(isset($mid)){ ?>
                <a href="printsettlement.php?print_modif='<?php echo $pid ?>'" class="btn btn-outline-success btn-sm shadow d-flex ms-2" id="printmodif_'<?php echo $pid ?>'" name="print"><i class="fa fa-print"></i></a>';
                <?php } 
                else {?>
                <a href="printsettlement.php?print_request='<?php echo $pid ?>'" class="btn btn-outline-success btn-sm shadow d-flex ms-2" id="printperdiem_'<?php echo $pid ?>'" name="print"><i class="fa fa-print"></i></a>';   
                
                <?php }}?>                
               <!-- <a href="printsettlement.php?print_request='<?php echo   $pid ?>'" class="btn btn-outline-success btn-sm shadow d-block ms-2" id="printsettlement_'<?php echo   $pid ?>'" name="print">testprint</i></a> -->     
              </div>             
            </div> 
            </form> 
        </div> 
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
               $modification = "SELECT * FROM perdiemmodification where perdiemid = ? and `status` not like '%rejected' ";
               $stmt_perdiemmodification = $conn_fleet->prepare($modification);
               $stmt_perdiemmodification -> bind_param("i", $pid);
               $stmt_perdiemmodification -> execute();
               $result_perdiemmodification = $stmt_perdiemmodification->get_result();
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
            $stmt_all_perdiem2 = $conn_fleet->prepare($selectall);
            $stmt_all_perdiem2 -> bind_param("ii", $allid, $allid2);
            }else{
            $selectall = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
            p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt from traveladvance t LEFT JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
            $stmt_all_perdiem2 = $conn_fleet->prepare($selectall);
            $stmt_all_perdiem2 -> bind_param("i", $allid);
            }
           
            $stmt_all_perdiem2 -> execute();
            $result_all_perdiem2 = $stmt_all_perdiem2->get_result();
            if($result_all_perdiem2 -> num_rows > 0)
              while($allrow = $result_all_perdiem2->fetch_assoc()){ 
                $employee = str_replace(' ','',$allrow['name_of_employee']);
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
                    <input type="text"  value="<?php echo ($allrow['actual_departuredate'] != null)?date("d-m-Y H:i",strtotime($allrow['actual_departuredate'])):""  ?>" name="actualdeparturedate[]" class="form-control shadow box" readonly>                           
                  </div> 
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Return Date:</b></label>               
                    <input type="text" value="<?php echo date("d-m-Y H:i",strtotime($srow['return_date']))  ?>" name="returndate[]" class="form-control shadow box" readonly>                           
                  </div>  
                  <div class="col-sm-6 mb-2">
                <label for="jobid" class="form-label me-3"><b>Actual Return date:</b></label>               
                    <input type="text"  value="<?php echo ($allrow['actual_returndate'] != null)?date("d-m-Y H:i",strtotime($allrow['actual_returndate'])):"" ?>" name="actualreturndate[]" class="form-control shadow box" readonly>                           
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
                $totalexpense = 0;
                $Fuelfound = false;
                for ($j = 0;$j < $split_val;$j++) { 
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
                      <?php $totalexpense += $split14[$j];} }  ?> 
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
                         $totalexpense2 += (is_numeric($split24[$j]))?$split24[$j]:0;} ?> 
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
                    if($allrow['closed_by'] != ''){ $buttonname = 'Proceed';?>        
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
                $counterrr++;
                   }
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

      <div class="modal fade mm"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header alert-secondary">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
              <img id="popup-img" loading="lazy" src="" alt="Image" style="max-height:100%;max-width: 100%;">
            </div>
          </div>
        </div>
      </div>
            
<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<?php include '../footer.php';?>
<script>
  // settlement print report
 <?php if(isset($_POST['approvesettlement'])){
  $perid = $_POST['peridd'];     
  ?>  
document.getElementById('printperdiem_<?php echo $perid  ?>').click();
  <?php 
   }  ?>
</script>>
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
?>
</script>
<script>
<?php 
if(isset($_POST['detail2']))
{?>
  document.getElementById('modal_open2').click();
  <?php
  unset($_POST['detail2']);
}
if(isset($_POST['detail5']))
{?>
  document.getElementById('modal_open5').click();
  <?php
  unset($_POST['detail2']);
}
if(isset($_POST['approvemodify'])){  ?>
  document.getElementById('modmodal_open').click();
  <?php unset($_POST['approvemodify']);
  } 
?>  
</script>
<script>
  function displayimage(e){     
  let n = parseInt(e.id.split("_")[1]);
  var src = document.getElementById('vieww_'+n).src;
  $('.mm').modal('show');
	$('#popup-img').attr('src',src);
  }
  function show(e){
var form1 =  document.getElementById('perdiemform');
var form2 =  document.getElementById('perdiemsettlement');
if(e.id == 'form'){
  form1.style.display = "block";
  form2.style.display = "none";
}else if(e.id == 'status'){
  form1.style.display = "none";
  form2.style.display = "block";
}
  }
</script> 
<script>
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

  // if(netdifference == 0 || netdifference <= 1000){
  // document.getElementById('checkid_'+first.split("::")[0]).classList.add("d-none");
  //    if(netdifference == 0){
  // document.getElementById('jv_'+first.split("::")[0]).classList.remove("d-none");
  // document.getElementById('cpv_'+first.split("::")[0]).classList.add("d-none");
  // document.getElementById('jv_'+first.split("::")[0]).children[1].required = true;
  // document.getElementById('cpv_'+first.split("::")[0]).children[1].required = false;
  //    }else{
  // document.getElementById('jv_'+first.split("::")[0]).classList.add("d-none");
  // document.getElementById('cpv_'+first.split("::")[0]).classList.remove("d-none");
  // document.getElementById('jv_'+first.split("::")[0]).children[1].required  = false;
  // document.getElementById('cpv_'+first.split("::")[0]).children[1].required = true;
  //    }
  //   let chek = document.getElementsByClassName('checkinfo_'+first.split("::")[0]);
  //   for(let i = 0;i < chek.length;i++){
  //     chek[i].required = false;
  //   }
  // }else{
  //   document.getElementById('checkid_'+first.split("::")[0]).classList.remove("d-none");
  //     let chek = document.getElementsByClassName('checkinfo_'+first.split("::")[0]);
  //     for(let i = 0;i < chek.length;i++){
  //       chek[i].required = true;
  //     }
  //  }
  }
}
</script>
<script>
    function view_type(e,t)
    {
        let o = (t=='div')?'request':'settlement';
        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
    }
    var increment = 1;
    var temp_data4 = document.getElementById('root').innerHTML;
  
function moreCamera(e){  
      let n = parseInt(e.name.split("_")[0])+1;
      e.name =  n+"_"+e.name.split("_")[1];
      document.getElementById('coun_'+e.name.split("_")[1]).value = n;
         var strrr = temp_data4.replaceAll('_1::1','_'+(parseInt(e.name.split("_")[1]))+'::'+n);
        const div = document.createElement('div');
            div.id = 'camount_'+e.name;
            div.className='row';
            div.innerHTML = "<div class='col-11'>"+strrr+"</div>"+"<div class='col-1 mt-3'><button class='btn btn-outline-danger mt-3' id='rem_"+e.name.split("_")[1]+"' type='button' onclick='removecamera(this)'><i class='bi bi-x'></i></button></div>";
            document.getElementById('addedcamera_'+e.name.split("_")[1]).appendChild(div);
            document.getElementById('camount_'+e.name).children[0].children[0].children[0].children[0].value = "";
            document.getElementById('camount_'+e.name).children[0].children[0].children[1].children[0].children[0].src = "../img/gallery.jpg";
            document.getElementById('camount_'+e.name).children[0].children[0].children[1].children[0].children[1].innerHTML = "";
            document.getElementById('camount_'+e.name).children[0].children[0].children[1].children[0].children[2].value = "";
            increment++;
    }
function removecamera(e)
      {
        let name = document.getElementById("addcamera_"+e.id.split("_")[1]).name.split("_");
        let n = parseInt(name[0])-1;
        document.getElementById('coun_'+name[1]).value = n;
      document.getElementById("addcamera_"+e.id.split("_")[1]).name = n+"_"+name[1];
      e.parentElement.parentElement.remove();
      } 
</script>
<script>
  let img_id = '';
  let img_id2 = '';
  let iddiff = '';
  mainimg_id  = '';
function get_id(e)
{
  mainimg_id = e.id.split('_')[1];
  img_id =  mainimg_id.split('::')[0];
  img_id2 = mainimg_id .split('::')[1];
  iddiff = e.id.split('_')[0];
}

var loadFile = function(event){
var reader = new FileReader();
reader.onload = function(){
  var output = document.getElementById('camera--output_'+img_id+'::'+img_id2);
var raw_image_data =  reader.result.replace(/^data\:image\/\w+\;base64\,/,'');

var file_name = document.getElementById('invoicecamera_'+img_id+'::'+img_id2).value.split('\\');
file_name = file_name[file_name.length - 1];
document.getElementById('actualimage_'+img_id+'::'+img_id2).value =  raw_image_data;
document.getElementById('actualname_'+img_id+'::'+img_id2).innerHTML = file_name;

output.src = reader.result;
document.getElementById('closebtn').click();
};
reader.readAsDataURL(event.target.files[0]);
};
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
  jQuery(document).ready(function($){
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