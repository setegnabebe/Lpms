<?php 
session_start();
include "../connection/connect.php";
//if(isset($_POST['detail2'])){
  $sid = 118;
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
    $selectall = "SELECT *,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
    p.birr as settlementbirr, p.payment_option as poption,p.receipt as receipt,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?
    UNION ALL SELECT *,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
    p.birr as settlementbirr, p.payment_option as poption,,p.pcpv_number as pettycpv,p.crv_number as crvnumber from tadvancemodification t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
    $stmt_all_perdiem = $conn_fleet->prepare($selectall);
    $stmt_all_perdiem -> bind_param("ii", $allid, $allid2);
  }
  else
  {
    $selectall = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
    p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt,p.pcpv_number as pettycpv,p.crv_number as crvnumber from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
    $stmt_all_perdiem = $conn_fleet->prepare($selectall);
    $stmt_all_perdiem -> bind_param("i", $allid);
  }
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
        <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st != 'Payment processed'){ ?>
        <input  name="rate[]" id="rate_<?php echo $employee ?>::<?php echo $j ?>"   onkeyup="Find_birr(this)" type="Number" step="any"  aria-describedby="basic-add" class="form-control border border-primary" Required> 
        <?php  }else{ ?>
        <input value="<?php echo $split22[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add" class="form-control" readonly>                   
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
                $numofday = $day;
              }else{
                $numofday = $split23[$j];
              }  
       ?>
        <div class="col-sm-3">
          <div class="input-group shadow box mb-3"> 
            <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st != 'Payment processed'){  ?>
        <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?> name="days[]" type="Number" step="any"  aria-describedby="basic-add1" class="form-control" required>  
             <?php }else{ ?>
        <input value="<?php echo $numofday ?>" id="days_<?php echo $employee ?>::<?php echo $j ?>" <?php echo $function ?>  type="Number" step="any"  aria-describedby="basic-add1" class="form-control" readonly>                        
          <?php   }  ?>  
        <span class="input-group-text" id="basic-add1"><?php echo $span ?></span>             
        </div>
        </div>

        <div class="col-sm-3">
          <div class="form-floating shadow box input-group mb-3"> 
          <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st != 'Payment processed'){ ?>
          <input name="birr[]" id="birr_<?php echo $employee ?>::<?php echo $j ?>" class="birr_<?php echo $employee ?> form-control" type="Number" step="any"  aria-describedby="basic-add2" readonly>  
          <?php  }else{ ?>
          <input value="<?php echo $split24[$j] ?>"  type="Number" step="any"  aria-describedby="basic-add2" class="form-control" readonly>  
                <?php 
               $totalexpense2 += $split24[$j]; 
               } ?>  
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
            <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st != 'Payment processed'){ ?>  
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
            <?php if(strpos($_SESSION["a_type"],"Perdiem") !== false and $st != 'Payment processed'){ ?> 
              <input  id="netdifference_<?php echo $employee ?>" type="Number"  aria-describedby="basic-add2" class="form-control" readonly> 
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
    <?php if($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false and $st == 'Settlement payment approved'){  $buttonname = 'Prepare Settlement'; ?>
      <div class="row mt-3">
       <?php
          $selectplimit = $conn->prepare("SELECT * FROM `limit_ho` where company = ?"); 
          $selectplimit->bind_param("s", $_SESSION["company"]);
          $selectplimit->execute();
          $limitres = $selectplimit->get_result();
          if(mysqli_num_rows($limitres)>0){
          $row = $limitres->fetch_assoc();
          $pittycash = $row['perdiem_pettycash']; 
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
              <input type="text" name="chequeno[]" class="form-control border border-primary shadow checkinfo_<?php echo $employee ?>" required>                           
          </div>                          
         <div class="col-sm-6  mb-2">
            <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
             <select type="text" name="bankname[]" class="form-select border border-primary shadow checkinfo_<?php echo $employee ?>" required> 
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
            <input type="text" id="cpv_<?php echo $allrow['travel_advanceid'] ?>" name="cpvno[]" onchange="set_cpv(this)" onkeyup="set_cpv(this)"  class="form-control checkinfo2_<?php echo $employee ?> border shadow border-primary" required>                           
        </div> 
        <?php }else if($netdiff > 0 && $netdiff < $pittycash){ ?>
        <div class="col-sm-6 col-md-4 mb-2" id="cpv_<?php echo $employee ?>">
          <label for="pcpvno" class="form-label me-3"><b>PCPV Number:</b></label>             
            <input type="text" id="pcpv_<?php echo $allrow['travel_advanceid'] ?>" name="pcpvno[]"  class="form-control checkinfo2_<?php echo $employee ?> border shadow border-primary" required>                           
        </div> 
         <?php }else if($netdiff < 0){  ?>
          <div class="col-sm-6 col-md-4 mb-2" id="crv_<?php echo $employee ?>">
          <label for="crvno" class="form-label me-3"><b>CRV Number:</b></label>               
            <input type="text" id="crv_<?php echo $allrow['travel_advanceid'] ?>" name="crvno[]"  class="form-control checkinfo3_<?php echo $employee ?> border shadow border-primary" required>                           
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
              <input value="<?php echo  $chequenum2  ?>" type="text" name="chequeno" class="form-control" readonly>                           
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
        if($netdiff <= 1000){
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
//}           
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