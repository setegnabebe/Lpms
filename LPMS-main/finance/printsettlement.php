<?php
include "../connection/connect.php";
require_once('../assets/tcpdf/tcpdf.php');
	$obj_pdf=new TCPDF('p',PDF_UNIT,PDF_PAGE_FORMAT,true,'utf-8',false);
	$obj_pdf->SetCreator(PDF_CREATOR);
	$obj_pdf->SetTitle("Request Form");
	$obj_pdf->SetHeaderData('','',PDF_HEADER_TITLE,PDF_HEADER_STRING);
	$obj_pdf->SetHeaderFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_MAIN));
	$obj_pdf->SetFooterFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_DATA));
	$obj_pdf->SetDefaultMonospacedFont('freeserif');
	$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$obj_pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP,PDF_MARGIN_RIGHT);
	$obj_pdf->SetPrintHeader(false);
	$obj_pdf->SetPrintFooter(true);
	$obj_pdf->SetAutoPageBreak(TRUE,'10');
	$obj_pdf->SetFont('freeserif','',7.5);
  $datee=date("Y-m-d H:i:s"); ?>
  <style>
   .fontsi{
    font-size:10px;
   }
  </style>
<?php
if(isset($_GET['print_modif'])){
   $data = $_GET['print_modif'];  
   $content = "";
   $alld = "";
   $fontsize = "font-size:12px";
   $select = "SELECT p.*, pm.id as modid, pm.status  FROM `perdiem` as p  inner join perdiemmodification as pm on p.id = pm.perdiemid WHERE p.id = $data";
   $result = $conn_fleet->query($select);            
    if($result->num_rows > 0);
      $detailrow = $result->fetch_assoc();
         $userlogo = $detailrow['company'];
         $printid = $detailrow['modid']; 
         $settlement_prepared=explode('::',$detailrow['settlementpayment_prepby']);
        $settlement_preparedby=$settlement_prepared[0];
        $settlement_checked=explode('::',$detailrow['cheque_reviwedby']);
        $settlement_checkedby=$settlement_checked[0];
        if(is_null($detailrow['settlementpaymentapp_by']))
        $settlement_approvedby='';
        else 
        {
        $settlement_approved=explode('::',$detailrow['settlementpaymentapp_by']);
        $settlement_approvedby=$settlement_approved[0];
        }
         $travel_signed=explode('::',$detailrow['travel_signed_by']);
        $travel_signedby=$travel_signed[0];
         $stat = $detailrow['status']; 
         $owner=($detailrow['traveloption']!='External')?"Hagbes":"External";
         $dest = explode('::',$detailrow['destination']);
         $destination=$dest[0];           
         for($i=0;$i<(count($dest)-1);$i++){
           $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
         }                
   $logo = "SELECT * FROM comp where `Name` = '$userlogo' ";
     $result = $conn_fleet->query($logo);            
      if($result->num_rows > 0)
           $roww = $result->fetch_assoc(); 
      $traveladvance = "SELECT * FROM traveladvance where perdime_id = $data "; 
         $resultt = $conn_fleet->query($traveladvance);          
        if($resultt->num_rows > 0)
          while($travelrow = $resultt->fetch_assoc())
           {           
          //fetch perdiem settlement 
          $emp_name=$travelrow['name_of_employee'];                   
          $traveadvanceid=$travelrow['id'];
          $perdiem_settle = "SELECT * FROM perdiemsettlement where `travel_advanceid` = ' $traveadvanceid' ";
          $rs_perdiem_settle = $conn_fleet->query($perdiem_settle);
          if($rs_perdiem_settle->num_rows > 0)
          $rw_perdiem_settle =$rs_perdiem_settle->fetch_assoc();
          $diff= $rw_perdiem_settle['birr'];
          $settlement_payment_option=$rw_perdiem_settle['payment_option'];          
          $settlement_crv=$rw_perdiem_settle['crv_number'];
          $settlement_cpv=$rw_perdiem_settle['cpv_number'];
          $settlement_pcpv=$rw_perdiem_settle['pcpv_number'];
          $settlement_cheqn=$rw_perdiem_settle['cheque_number'];
          $paymentoption = $travelrow['payment_option'];
          $chequenum = $travelrow['cheque_number'];
          $cpvno = $travelrow['cpv_number'];
          $content .= '<div style="min-height:100%;padding:500pt;">';
          $content .= '<h3 style="text-align:center;"><img src = "../img/'.$roww['logo'].'" width="50"></h3><h3 style="text-align:center;">'.$userlogo.'</h3>';
          $content .= '<h1 style="text-align:center;">Settlement of perdiem expense and performance report form</h1>';
                     // for employee detail      
          $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">
                       <tr><td style="padding-right:10px;text-align:left;"><b class="fontsi">Job ID: </b>'.$detailrow['job_id'].'</td><td style="text-align:right;"><b>Date of request: </b>'.$detailrow['dateofrequest'].'</td></tr>
                       <tr><td style="padding-right:10px;text-align:left;"><b>Name Of Employee: </b>'.$travelrow['name_of_employee'].'</td><td style="text-align:right;"><b>Department: </b>'.$detailrow['fromdepartment'].'</td></tr>
                       <tr><td style="padding-right:10px;text-align:left;colspan:2"><b>Occupation: </b>'.$detailrow['travellerroleintrip'].'</td></tr>
                       </table><br><br>';          
          $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">                              
                       <tr>
                       <td><b>Duty station: </b>'. $destination.'</td><td><b>Date of departure: </b>'.$rw_perdiem_settle['actual_departuredate'].'</td><td><b>Date of return:</b>'.$rw_perdiem_settle['actual_returndate'].'</td>
                       </tr> 
                       <tr>
                       <td><b>Vehicle user:</b>__________</td><td><b>Owner: </b>'. $owner.'</td><td><b>Plate No:</b>'.$detailrow['vehicle'].'</td><td><b>driver:</b>'.$detailrow['driver'].'</td>
                       </tr>';                                  
           $content .= '</table>';       
           $content  .= '<h3 style="text-align:center;">settlement of perdiem expense detail</h3>';                  
           $content .= '<table cellpadding="1" style="font-size:11px; margin-left:100px;width:100%;"  border="1">
                       <tr style="text-align:center">
                       <td><b>Reason</b></td><td><b>Rate</b></td><td><b>No of Days</b></td>
                       </tr>';
                       // for travel advance
                       $split_tadv1 = ($travelrow['reason'] != "")?explode('::',$travelrow['reason']):[];
                       $split_tadv2 = ($travelrow['rate'] != "")?explode('::',$travelrow['rate']):[];
                       $split_tadv3 = ($travelrow['days'] != "")?explode('::',$travelrow['days']):[];
                       $split_tadv4 = ($travelrow['birr'] != "")?explode('::',$travelrow['birr']):[];
                       $total_tadv =0;
                       for($j = 0;$j < count($split_tadv1); $j++){
                        $total_tadv += (double)$split_tadv4[$j];
                       }
                       // for settlement
                             $split1 = ($rw_perdiem_settle['reason'] != "")?explode('::',$rw_perdiem_settle['reason']):[];
                             $split2 = ($rw_perdiem_settle['rate'] != "")?explode('::',$rw_perdiem_settle['rate']):[];
                             $split3 = ($rw_perdiem_settle['days'] != "")?explode('::',$rw_perdiem_settle['days']):[];
                             $split4 = ($rw_perdiem_settle['birr'] != "")?explode('::',$rw_perdiem_settle['birr']):[];
                             $total =0;                                     
                           for($j = 0;$j < count($split1); $j++){
                            if($split1[$j]=='Fuel') $unit='Liters';
                            else $unit='';                                    
                             $content .='
                             <tr style="font-size:11px;">
                             <th>'.$split1[$j].'</th><th>'.$split2[$j].'</th><th>'.$split3[$j].$unit.'</th>
                             </tr>
                             '; 
                             
                             $total += (double)$split_tadv4[$j];    
                           }   
                           $content .='</table><br>'; 
                           $content  .='<table cellpadding="1" style="font-size:11px; margin-left:100px;">';                                
                           $content .= ' <tr>
                           <td style="padding-right:10px;text-align:left;border:none"><b>Work performed in short::</b>'.$detailrow["reasonfortrip"].'</td> <td></td></tr>';  
                          $content .= ' <tr><br><br>
                        <td style="padding-right:10px;text-align:left;border:none"><b>Employee name:</b> <u>'.$travelrow["name_of_employee"].'</u></td>                                
                        <td style="padding-right:10px;text-align:left;border:none"><b>Approved by:</b> <u>'.$travel_signedby.' </u></td>
                        </tr>';  
                     $content .='</table>';

                     // for accountant use

                    $content  .= '<br><br><h3 style="text-align:center;">for accountant use Only</h3>';
                    $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">';     
                    $content .= '<tr style="text-align:left;"><td><b>Advance payment: </b></td><td></td><td></td></tr>';  
          //advance payment row               
                  if($paymentoption == 'Petty' and $total_tadv != ''){
                  $content .= '<tr><td><b>Cash <u> '.$total_tadv.'      </u></b></td></tr>';
                  }else if($paymentoption == 'Cheque' and $chequenum != ''){
                  $content .= '<tr style="text-align:left;"><td>TOTAL:<u>'.$total_tadv.'</u></td><td>cheque number:<u>'.$chequenum.'</u></td><td>CPV number:<u>'.$cpvno.'</u></td></tr>';
                  }
                  $content .= '<tr style="text-align:left;"><td><b>Travel Advance modification payment: </b></td><td></td><td></td></tr>';  
          //travel advance modification payment row 
                  $mod = "SELECT * FROM tadvancemodification where perdime_id = '$printid' and name_of_employee='$emp_name'"; 
                  $mod_res = $conn_fleet->query($mod);          
                  if($mod_res->num_rows > 0)
                  $mod_res = $mod_res->fetch_assoc();
                  $cheq_no=$mod_res['cheque_number'];
                  $cpv_no=$mod_res['cpv_number']; 
                  $splitm1 = ($mod_res['reason'] != "")?explode('::',$mod_res['reason']):[];                      
                  $splitm4 = ($mod_res['birr'] != "")?explode('::',$mod_res['birr']):[];
                  $tv_advmod_total =0;
                  for($j = 0;$j < count($split1); $j++){                                                         
                      $tv_advmod_total += (double)$splitm4[$j];    
                    }              
                  if($paymentoption == 'Petty' and $tv_advmod_total != ''){
                  $content .= '<tr><td><b>Cash <u> '.$tv_advmod_total.'      </u></b></td></tr>';
                  }else if($paymentoption == 'Cheque' and $cheq_no != ''){
                  $content .= '<tr style="text-align:left;"><td>TOTAL:<u>'.$tv_advmod_total.'</u></td><td>cheque number:<u>'.$cheq_no.'</u></td><td>CPV number:<u>'.$cpv_no.'</u></td></tr>';
                  } 
          // end of travel advance modification payment
          //expense row
          $current_payment=0;
          $content .= '<tr style="text-align:left;"><td><b>Expenses: </b></td></tr>';  
                  for($j = 0;$j < count($splitm1); $j++)
                    {
                        $content .='<tr style="font-size:11px;"><td>'.$split1[$j].':</td>';
                        $content .='<td>';
                        $expensecost=$split2[$j]*$split3[$j];                              
                        $current_payment+=$expensecost;
                        $content .=$expensecost.'</td></tr>'; 
                       // $expensecost=($splitm1[$j]!="Perdiem")?$splitm4[$j]+$split_tadv4[$j]:$splitm4[$j]+$split_tadv4[$j];                                               
                        //$content .=$expensecost.'</td></tr>';                      
                    }  
                  $total_all= $total_tadv + $tv_advmod_total; 
                  $content .='<tr style="font-size:11px;"><td>previous payment:</td><td>'.$total_all.'</td></tr>'; 
                  $content .='<tr style="font-size:11px;"><td>Settled payment:</td><td>'.$current_payment.'</td></tr>';
                  //$content .='<tr style="font-size:11px;"><td>Total:</td><td>'.$total_all.'</td></tr>'; 
                  $content .='<tr style="font-size:11px;"><td>Difference:</td><td>'.$diff.'</td></tr>';                 
                       
            //end of expense row    
                  $content .='</table>';

                   // end for accountant use only
                         
                   $content .= '<br><br><br><hr><table cellpadding="1" style="font-size:11px;width:100%;"><tbody>
                   <tr><th colspan="3">I have settled/recieved the difference by:';
                   //<tr><th colspan="3">I have settled/recieved the difference by CRV No/CPV No:______</th><th></th><th></th>';
                     if($settlement_payment_option == 'Recieved in Cash'){
                     $content .= 'CRV number <u>: '.$settlement_crv.'      </u>';
                     }else if($settlement_payment_option == 'Paid in Cheque'){
                     $content .= 'CPV number: <u>  '.$settlement_cpv.'     </u>';
                     }else if($settlement_payment_option == 'Paid in Cash'){
                       $content .= 'PCPV number: <u>  '.$settlement_pcpv.'     </u>';
                       }else{
                     $content .= 'deducting from salary';
                     } 
                   $content .= '</th><th></th><th></th</tr>
                    <tr style="width:100%">
                    <td style="padding-right:10px;text-align:left"><b>Prepared by:<u>'.$settlement_preparedby.'</u></b></td>
                    <td colspan="2" style="text-align:left"><b>Checked by:&nbsp;<u>'.$settlement_checkedby.'</u></b>&nbsp;&nbsp;&nbsp;</td> 
                    <td colspan="2" style="text-align:right"><b>Approved by:</b><u>'.$settlement_approvedby.'</u></td>
                    </tr>';
                    $content .= '</table>';
                    $content .= '<br></div>';
                                              
                   $obj_pdf->AddPage('p','A4');
                   $obj_pdf->writeHTML($content);
                   $content = '';

          }                                         
          ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
          $obj_pdf->Output('perdiem-'.$detailrow['job_id'].'.pdf','I');
          }else if(isset($_GET['print_request'])){
            //ob_start();
            $data = $_GET['print_request'];
            $content = "";
            $alld = "";
            $fontsize = "font-size:12px";
            $select = "SELECT * from perdiem where id = $data";
            $result = $conn_fleet->query($select);                
             if($result->num_rows > 0);
               $detailrow = $result->fetch_assoc();
                  $userlogo = $detailrow['company'];
                  $printid = $detailrow['id']; 
                  $stat = $detailrow['status']; 
                  $owner=($detailrow['traveloption']!='External')?"Hagbes":"External";
                  $travel_signed=explode('::',$detailrow['travel_signed_by']);
                  $travel_signedby=$travel_signed[0];
                  $settlement_prepared=explode('::',$detailrow['settlementpayment_prepby']);
                  $settlement_preparedby=$settlement_prepared[0];
                  $settlement_checked=explode('::',$detailrow['cheque_reviwedby']);
                  $settlement_checkedby=$settlement_checked[0];
                  if(is_null($detailrow['settlementpaymentapp_by']))
                  $settlement_approvedby='';
                  else 
                  {
                  $settlement_approved=explode('::',$detailrow['settlementpaymentapp_by']);
                  $settlement_approvedby=$settlement_approved[0];
                  }
                  $dest = explode('::',$detailrow['destination']); 
                  $destination=$dest[0];                          
                  for($i=0;$i<(count($dest)-1);$i++){
                    $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
                  }                
            $logo = "SELECT * FROM comp where `Name` = '$userlogo' ";
              $result = $conn_fleet->query($logo);            
               if($result->num_rows > 0)
                    $roww = $result->fetch_assoc(); 
               $traveladvance = "SELECT * FROM traveladvance where perdime_id = $data ";             
                  $resultt = $conn_fleet->query($traveladvance);                         
               if($resultt->num_rows > 0)
                 while($travelrow = $resultt->fetch_assoc())
                  {  
                    //fetch perdiem settlement                                        
                    $traveadvanceid=$travelrow['id'];                    
                    $perdiem_settle = "SELECT * FROM perdiemsettlement where `travel_advanceid` = ' $traveadvanceid' ";
                    $rs_perdiem_settle = $conn_fleet->query($perdiem_settle);
                    if($rs_perdiem_settle->num_rows > 0)
                    $rw_perdiem_settle =$rs_perdiem_settle->fetch_assoc();
                  $diff= $rw_perdiem_settle['birr'];
                  $settlement_payment_option=$rw_perdiem_settle['payment_option'];                  
                  $settlement_crv=$rw_perdiem_settle['crv_number'];
                  $settlement_cpv=$rw_perdiem_settle['cpv_number'];
                  $settlement_pcpv=$rw_perdiem_settle['pcpv_number'];
                  $settlement_cheqn=$rw_perdiem_settle['cheque_number'];
                   $paymentoption = $travelrow['payment_option'];
                   $chequenum = $travelrow['cheque_number'];
                   $cpvno = $travelrow['cpv_number'];
                   $content .= '<div style="min-height:100%;padding:500pt;">';
                   $content .= '<h3 style="text-align:center;"><img src = "../img/'.$roww['logo'].'" width="50"></h3><h3 style="text-align:center;">'.$userlogo.'</h3>';
                   $content .= '<h1 style="text-align:center;">Settlement of perdiem expense and performance report form</h1>';
                             // for employee detail      
                   $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">
                               <tr><td style="padding-right:10px;text-align:left;"><b class="fontsi">Job ID: </b>'.$detailrow['job_id'].'</td><td style="text-align:right;"><b>Date of request: </b>'.$detailrow['dateofrequest'].'</td></tr>
                               <tr><td style="padding-right:10px;text-align:left;"><b>Name Of Employee: </b>'.$travelrow['name_of_employee'].'</td><td style="text-align:right;"><b>Department: </b>'.$detailrow['fromdepartment'].'</td></tr>
                               <tr><td style="padding-right:10px;text-align:left;colspan:2"><b>Occupation: </b>'.$detailrow['travellerroleintrip'].'</td></tr>
                               </table><br><br>';          
                  $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">                              
                               <tr>
                               <td><b>Duty station: </b>'. $destination.'</td><td><b>Date of departure: </b>'.$rw_perdiem_settle['actual_departuredate'].'</td><td><b>Date of return:</b>'.$rw_perdiem_settle['actual_returndate'].'</td>
                               </tr> 
                               <tr>
                               <td><b>Vehicle user:</b>__________</td><td><b>Owner: </b>'. $owner.'</td><td><b>Plate No:</b>'.$detailrow['vehicle'].'</td><td><b>driver:</b>'.$detailrow['driver'].'</td>
                               </tr>';                                  
                   $content .= '</table>';       
                   $content  .= '<h3 style="text-align:center;">settlement of perdiem expense detail</h3>';                  
                   $content .= '<table cellpadding="1" style="font-size:11px; margin-left:100px;width:100%;"  border="1">
                               <tr style="text-align:center">
                               <td><b>Reason</b></td><td><b>Rate</b></td><td><b>No of Days</b></td>
                               </tr>';
                               // for travel advance
                               $split_tadv1 = ($travelrow['reason'] != "")?explode('::',$travelrow['reason']):[];
                               $split_tadv2 = ($travelrow['rate'] != "")?explode('::',$travelrow['rate']):[];
                               $split_tadv3 = ($travelrow['days'] != "")?explode('::',$travelrow['days']):[];
                               $split_tadv4 = ($travelrow['birr'] != "")?explode('::',$travelrow['birr']):[];
                               $total_tadv =0;
                               for($j = 0;$j < count($split_tadv1); $j++){
                                $total_tadv += (double)$split_tadv4[$j];
                               }
                               // for settlement
                                     $split1 = ($rw_perdiem_settle['reason'] != "")?explode('::',$rw_perdiem_settle['reason']):[];
                                     $split2 = ($rw_perdiem_settle['rate'] != "")?explode('::',$rw_perdiem_settle['rate']):[];
                                     $split3 = ($rw_perdiem_settle['days'] != "")?explode('::',$rw_perdiem_settle['days']):[];
                                     $split4 = ($rw_perdiem_settle['birr'] != "")?explode('::',$rw_perdiem_settle['birr']):[];
                                     $total =0; 
                                                                                                      
                                   for($j = 0;$j < count($split1); $j++){                                   
                                    if($split1[$j]=='Fuel') $unit='Liters';
                                    else $unit='';                                    
                                     $content .='
                                     <tr style="font-size:11px;">
                                     <th>'.$split1[$j].'</th><th>'.$split2[$j].'</th><th>'.$split3[$j].$unit.'</th>
                                     </tr>
                                     '; 
                                     
                                     $total += (double)$split4[$j];    
                                   }   
                                   $content .='</table><br>'; 
                                   $content  .='<table cellpadding="1" style="font-size:11px; margin-left:100px;">';                                
                                   $content .= ' <tr>
                                   <td style="padding-right:10px;text-align:left;border:none"><b>Work performed in short::</b>'.$detailrow["reasonfortrip"].'</td> <td></td></tr>';  
                                  $content .= ' <tr><br><br>
                                <td style="padding-right:10px;text-align:left;border:none"><b>Employee name:</b> <u>'.$travelrow["name_of_employee"].'</u></td>                                
                                <td style="padding-right:10px;text-align:left;border:none"><b>Approved by:</b> <u>'.$travel_signedby.' </u></td>
                                </tr>';  
                             $content .='</table>';

                             // for accountant use

                  $content  .= '<br><br><h3 style="text-align:center;">for accountant use Only</h3>';
                  $content .= '<hr><table cellpadding="1" style="font-size:11px;width:100%;">';     
                  $content .= '<tr style="text-align:left;"><td><b>Advance payment: </b></td><td></td><td></td></tr>';  
                  //advance payment row               
                          if($paymentoption == 'Petty' and $total_tadv != ''){
                          $content .= '<tr><td><b>Cash <u> '.$total_tadv.'      </u></b></td></tr>';
                          }else if($paymentoption == 'Cheque' and $chequenum != ''){
                          $content .= '<tr style="text-align:left;"><td>TOTAL:<u>'.$total_tadv.'</u></td><td>cheque number:<u>'.$chequenum.'</u></td><td>CPV number:<u>'.$cpvno.'</u></td></tr>';
                          
                          } 
                  // end of advance payment row
                  //expense row
                  $current_payment=0;        
                  $content .= '<tr style="text-align:left;"><td><b>Expenses: </b></td></tr>';  
                          for($j = 0;$j < count($split1); $j++)
                          {
                              $content .='<tr style="font-size:11px;"><td>'.$split1[$j].':</td>';
                              $content .='<td>';                              
                              $expensecost=$split2[$j]*$split3[$j];                              
                              $current_payment+=$expensecost;
                              $content .=$expensecost.'</td></tr>';                      
                          }   
                          $content .='<tr style="font-size:11px;"><td>previous payment:</td><td>'.$total_tadv.'</td></tr>'; 
                          $content .='<tr style="font-size:11px;"><td>Settled payment:</td><td>'.$current_payment.'</td></tr>';
                          $content .='<tr style="font-size:11px;"><td>Difference:</td><td>'.$diff.'</td></tr>';                                                                
                    //end of expense row    
                   $content .='</table>';

                           // end for accountant use only
                                 
                            $content .= '<br><br><br><hr><table cellpadding="1" style="font-size:11px;width:100%;"><tbody>
                            <tr><th colspan="3">I have settled/recieved the difference by:';
                            //<tr><th colspan="3">I have settled/recieved the difference by CRV No/CPV No:______</th><th></th><th></th>';
                              if($settlement_payment_option == 'Recieved in Cash'){
                              $content .= 'CRV number <u> '.$settlement_crv.'      </u>';
                              }else if($settlement_payment_option == 'Paid in Cheque'){
                              $content .= 'CPV number <u>  '.$settlement_cpv.'     </u>';
                              }else if($settlement_payment_option == 'Paid in Cash'){
                                $content .= 'PCPV number <u>  '.$settlement_pcpv.'     </u>';
                                }else{
                              $content .= 'deducting from salary';
                              } 
                            $content .= '</th><th></th><th></th></tr>
                            <tr style="width:100%">
                            <td style="padding-right:10px;text-align:left"><b>Prepared by:&nbsp;&nbsp;&nbsp;<u>'.$settlement_preparedby.'</u></b></td>
                            <td colspan="2" style="text-align:left"><b>Checked by:<u>'.$settlement_checkedby.'</u></b>&nbsp;&nbsp;&nbsp;</td> 
                            <td colspan="2" style="text-align:right"><b>Approved by:</b><u>'.$settlement_approvedby.'</u></td>
                            </tr>';
                            $content .= '</tbody></table>';
                            $content .= '<br></div>';
                                                      
                           $obj_pdf->AddPage('p','A4');
                           $obj_pdf->writeHTML($content);
                           $content = '';
                  }  
                                                    
                 ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
                 $obj_pdf->Output('perdiem-'.$detailrow['job_id'].'.pdf','I');
          
        }
?>
