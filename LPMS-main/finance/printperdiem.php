<?php
include '../connection/connect.php';
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
  if(isset($_GET['print_request'])){
   $data = $_GET['print_request'];
   $content = "";
   $alld = "";
   $fontsize = "font-size:12px";
  $select = "SELECT * from perdiem where id = ?";
  $stmt_prediem = $conn_fleet -> prepare($select);
  $stmt_prediem -> bind_param("i", $data);
  $stmt_prediem -> execute();
  $result_prediem = $stmt_prediem -> get_result();
  if($result_prediem -> num_rows > 0)
    $detailrow = $result_prediem -> fetch_assoc();
    $userlogo = $detailrow['company'];
    $printid = $detailrow['id']; 
    $stat = $detailrow['status']; 
    $dest = explode('::',$detailrow['destination']);           
    for($i=0;$i<(count($dest)-1);$i++){
      $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
    } 

          //  if(isset($_GET['print_request'])){
          //   $data = $_GET['print_request'];
          //   $content = "";
          //   $alld = "";
          //   $fontsize = "font-size:12px";
          //  $select = "SELECT * from perdiem where id = '$data'";
          //     $result = $conn_fleet->query($select);            
          //      if($result->num_rows > 0);
          //        $detailrow = $result->fetch_assoc();
          //           $userlogo = $detailrow['company'];
          //           $printid = $detailrow['id']; 
          //           $stat = $detailrow['status']; 
          //           $dest = explode('::',$detailrow['destination']);           
          //           for($i=0;$i<(count($dest)-1);$i++){
          //             $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
          //           }         
    $logo = "SELECT * FROM comp where `Name` = ? ";
    $stmt_company = $conn_fleet -> prepare($logo);  
    $stmt_company -> bind_param("s", $userlogo);
    $stmt_company -> execute();
    $result_company = $stmt_company -> get_result();
    if($result_company->num_rows > 0)
      $roww = $result_company->fetch_assoc(); 
    $select = "SELECT * FROM traveladvance where perdime_id = ? "; 
    $stmt_traveladvance = $conn_fleet -> prepare($select);  
    $stmt_traveladvance -> bind_param("i", $data);
    $stmt_traveladvance -> execute();
    $result_traveladvance = $stmt_traveladvance -> get_result();
        if($result_traveladvance->num_rows > 0)
          while($travelrow = $result_traveladvance->fetch_assoc())
           {           
            $paymentoption = $travelrow['payment_option'];
            $chequenum = $travelrow['cheque_number'];
            $content .= '<div style="min-height:100%;padding:500pt;">';
            $content .= '<h3 style="text-align:center;"><img src = "images/'.$roww['logo'].'" width="50"></h3><h3 style="text-align:center;">'.$userlogo.'</h3>';
            $content .= '<h1 style="text-align:center;">Perdiem and Travel Advance Request Form</h1>';
                            
            $content .= '<hr><table cellpadding="3" style="font-size:11px;width:100%;">
                        <tr><td style="padding-right:10px;text-align:left;"><b class="fontsi">Job ID: </b>'.$detailrow['job_id'].'</td><td style="text-align:right;"><b>Date of request: </b>'.$detailrow['dateofrequest'].'</td></tr>
                        <tr><td style="padding-right:10px;text-align:left;"><b>Name Of Employee: </b>'.$travelrow['name_of_employee'].'</td><td style="text-align:right;"><b>Department: </b>'.$detailrow['fromdepartment'].'</td></tr>
                      ';
                                
                      $content .= '<tr><td colspan="2"><h1 style="text-align:center">Travel Details</h1><hr></td></tr>';  

            $content .= '<tr><td><b>Name of customer: </b>'.$detailrow['customer_name'].'</td><td><b>Destination: </b>'.$alld.'</td></tr>
                        <tr><td><b>Purpose of Travel: </b>'.$detailrow['reasonfortrip'].'</td><td><b>Means of Travel: </b>'.$detailrow['meansoftravel'].'</td></tr>
                        <tr><td><b>Date of departure: </b>'.$detailrow['departure_date'].'</td><td><b>Date of return:</b>'.$detailrow['return_date'].'</td></tr> 
                        <tr><td></td><td></td></tr> ';                                  
            $content .= '</table><br><br>';

            $content  .= '<br><br><h1 style="text-align:center;">Amount Required</h1>';

            $content .= '<table cellpadding="3" style="font-size:11px; margin-left:100px;width:100%;"  border="1">
                        <tr style="text-align:center">
                        <td><b>Reason</b></td><td><b>Rate</b></td><td><b>Days</b></td><td><b>Birr</b></td>
                        </tr>';
                              $split1 = explode('::',$travelrow['reason']);
                              $split2 = explode('::',$travelrow['rate']);
                              $split3 = explode('::',$travelrow['days']);
                              $split4 = explode('::',$travelrow['birr']);
                              $total =0;
                            for($j = 0;$j < count($split1); $j++){
                              $content .='
                              <tr style="font-size:11px;">
                              <th>'.$split1[$j].'</th><th>'.$split2[$j].'</th><th>'.$split3[$j].'</th><th>'.$split4[$j].'</th>
                              </tr>
                              '; 
                              $total += $split4[$j];    
                            }  
                            $content .= '<tr style="font-size:11px;"><th>Total</th><th></th><th></th><th>'.$total.'</th></tr>';
                           
                      $content .='</table><br><br><br>';
                           $explode1 = explode('::',$detailrow['travel_approved_by']);
                           $travelby = $explode1[0];
                           $traveldate = $explode1[1];
                           $explode2 = explode('::',$detailrow['senior_accountant']);
                           $senioracc = $explode2[0];
                           $date = $explode2[1];
                   
                     $content .= '<br><br><br><table cellpadding="3" style="font-size:11px;width:100%;"><tbody>
                     <tr><th colspan="2">I certified that I have received the sum of Birr ';
                       if($paymentoption == 'Petty' and $total != ''){
                       $content .= 'Cash <u> '.$total.'      </u>';
                       }else if($paymentoption == 'Cheque' and $chequenum != ''){
                       $content .= 'By cheque number <u>  '.$chequenum.'     </u>';
                       }else{
                       $content .= 'Cash _________________ or by Cheque number __________________';
                       } 
                     $content .= ' Dated ________________ Being advance for Per diem and travel Expenses, and I shall account for the above within a period of one week of my return.</th></tr>';
                     
                     $content .= '<br><br><br>
                     <tr>
                     <td style="padding-right:10px;text-align:left"><b>Employee name</b> <u>    '.$travelrow["name_of_employee"].'            </u></td>
                     <td style="text-align:right"><b>Signature</b> ____________________</td>
                     </tr>';
                     $content .= '</tbody></table>';
                     $content .= '<br><br></div>';
                                               
                    $obj_pdf->AddPage('p','A4');
                    $obj_pdf->writeHTML($content);
                    $content = '';
           }                                         
ob_end_clean();
$obj_pdf->Output('perdiem-'.$detailrow['job_id'].'.pdf','I');
          }else if(isset($_GET['settlement_request'])){
            $data = $_GET['settlement_request'];
            $content = "";
            $alld = "";
            $fontsize = "font-size:12px";
            $select = "SELECT * from perdiem where id = ?";
            $stmt_prediem = $conn_fleet -> prepare($select);
            $stmt_prediem -> bind_param("i", $data);
            $stmt_prediem -> execute();
            $result_prediem = $stmt_prediem -> get_result();
            if($result_prediem -> num_rows > 0)
              $detailrow = $result_prediem -> fetch_assoc();
              $userlogo = $detailrow['company'];
              $printid = $detailrow['id']; 
              $stat = $detailrow['status']; 
              $dest = explode('::',$detailrow['destination']);           
              for($i=0;$i<(count($dest)-1);$i++){
                $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
              } 
                 
                  $logo = "SELECT * FROM comp where `Name` = ? ";
                  $stmt_company = $conn_fleet -> prepare($logo);  
                  $stmt_company -> bind_param("s", $userlogo);
                  $stmt_company -> execute();
                  $result_company = $stmt_company -> get_result();
                  if($result_company -> num_rows > 0)
                    $roww = $result_company -> fetch_assoc(); 
                 $traveladvance = "SELECT *,t.cheque_number as tcheque_number,t.cpv_number as tcpv_number,t.bank as tbank,t.payment_option as toption,p.cheque_number as pcheque_number,p.cpv_number as pcpv_number,
                 p.bank as pbank,p.jv as jvno,t.reason as travelreason,t.rate as travelrate,t.days as traveldays,t.birr as travelbirr,p.reason as settlementreason,p.rate as settlementrate,p.days as settlementdays,
                 p.birr as settlementbirr,p.payment_option as poption,p.receipt as receipt,p.workdone as workdone from traveladvance t JOIN perdiemsettlement p ON p.travel_advanceid = t.id where perdime_id = ?";
                $stmt_traveladvance_complex = $conn_fleet -> prepare($traveladvance);
                $stmt_traveladvance_complex -> bind_param("i", $data);
                $stmt_traveladvance_complex -> execute();
                $result_traveladvance_complex = $stmt_traveladvance_complex -> get_result();             
                 if($result_traveladvance_complex -> num_rows > 0)
                   while($travelrow = $result_traveladvance_complex -> fetch_assoc())
                    {  
                     $adpay = 0;
                     $exp = 0; 
                     $totaladvancepayment = ($travelrow['travelbirr'] != '')?explode('::',$travelrow['travelbirr']):"";  
                     for($i=0;$i < count($totaladvancepayment); $i++){
                      $adpay += $totaladvancepayment[$i];
                     }
                      $totalexpense = ($travelrow['settlementbirr'] != '')?explode('::',$travelrow['settlementbirr']):"";
                      for($i=0;$i < count($totalexpense); $i++){
                        $exp += $totalexpense[$i];
                      }
                     $netdiff =  $exp - $adpay;
                     $settledby = ($travelrow['cpv_number'] != '')?$travelrow['cpv_number']:(($travelrow['crv_number'] != '')?$travelrow['crv_number']:""); 
                     $prepby = ($detailrow['settlementpayment_prepby'] != "")?explode('::',$detailrow['settlementpayment_prepby']):""; 
                     $chekby = ($detailrow['settlementpaymentckd_by'] != "")?explode('::',$detailrow['settlementpaymentckd_by']):"";  
                     $reviewedby = ($detailrow['settlementreviewed_by'] != "")?explode('::',$detailrow['settlementreviewed_by']):"";
                     $appby = ($detailrow['settlementpaymentapp_by'] != "")?explode('::',$detailrow['settlementpaymentapp_by']):"";   
                     $paymentoption = $travelrow['payment_option'];
                     $chequenum = $travelrow['cheque_number'];
                     $content .= '<div style="min-height:100%;padding:500pt;">';
                     $content .= '<h3 style="text-align:center;"><img src = "images/'.$roww['logo'].'" width="50"></h3><h3 style="text-align:center;">'.$userlogo.'</h3>';
                     $content .= '<h1 style="text-align:center;">Settlement of Perdiem Expenses And Performance Report Form</h1>';
                                     
                     $content .= '<hr><table cellpadding="3" style="font-size:11px;width:100%;">';
                     $content .= '<tr><td>Name of Employee: '.$travelrow['name_of_employee'].'</td><td>Department: '.$detailrow['fromdepartment'].'</td></tr>
                                  <tr><td>Date of Departure: '.$travelrow['actual_departuredate'].'</td><td>Date of Arrival: '.$travelrow['actual_returndate'].'</td></tr>
                                  <tr><td>Role in Trip : '.$travelrow['roleonthistrip'].'</td><td>Customer Name: '.$detailrow['customer_name'].'</td></tr>';
                      
                                  $content .= '<hr><table cellpadding="3" style="font-size:11px;width:100%;">'; 
                                  $content  .= '<br><br><h1 style="text-align:center;">Amount Required</h1>';                                   
                                  $content .= '</table><br><br>';
                                  
                     $content .= '<hr><table cellpadding="3" style="font-size:11px;width:100%;">';
                     $content .= '<tr style="text-align:center">
                                  <td><b>Reason</b></td> <td><b>Date</b></td> <td><b>From</b></td> <td><b>To</b></td> <td><b>No of Days</b></td> <td><b>Rate</b></td>
                                  </tr>';                                
                                  $split1 = date("Y-m-d H:i",strtotime($detailrow['dateofrequest']));
                                  $split2 = $travelrow['actual_departuredate'];
                                  $split3 = $travelrow['actual_returndate'];                                                                  
                                  $reason = explode('::',$travelrow['settlementreason']);
                                  $numofdays = ($travelrow['settlementdays'] != "")?explode('::',$travelrow['settlementdays']):"";
                                  $rate = ($travelrow['settlementrate'] != "")?explode('::',$travelrow['settlementrate']):"";
                                  
                                  for($i=0;$i < count($reason);$i++){   
                                  $content .='
                                  <tr style="text-align:center;font-size:11px;">
                                  <td>'.$reason[$i].'</td> <td>'.$split1.'</td> <td>'.$split2.'</td> <td>'.$split3.'</td> <td>'.$numofdays[$i].'</td> <td>'.$rate[$i].'</td>
                                  </tr>
                                  '; 
                                  }                           
                     $content .= '</table><br><br>';  
                     $content .= '<table cellpadding="3" style="font-size:11px;width:100%;">
                                         <tr><td>Work Performed in short: '.$travelrow['workdone'].'</td></tr></table>
                                         <table cellpadding="3" style="font-size:11px;width:100%;"><tr><td>Employees Signature: ____________________</td><td>Reviewed by: '.(($reviewedby != "")?str_replace(""," ",$reviewedby[0]):"").'</td></tr>
                                         </table><br>';  
                     $content .=  '<hr><table cellpadding="3" style="font-size:11px;width:100%;">
                                   <tr><td>Advance Payment: '.$adpay.'</td><td>CPV/PCPV NO: '.$settledby.'</td><td>Cheque NO: '.$chequenum.'</td></tr></table>'; 

                     $content .= '<table cellpadding="3" style="font-size:11px;width:100%;"><tr><td>Expenses:</td></tr>';                     
                              for($i=0;$i < count($reason);$i++){   
                                $content .='
                                <tr>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;'.$reason[$i].'=  '.$rate[$i].'  x '.$numofdays[$i].'</td>
                                </tr>
                                '; 
                                  }
                     $content .= '<tr><td style="padding-left:10px;text-align:center;">Total:  '.$exp.' </td></tr>
                                  <tr><td style="padding-left:10px;text-align:center;">Net difference:  '.$netdiff.' </td></tr></table>'; 
                     $content .= '<h3>I have settled/received the difference by CRV/CPV No  '.$settledby.' </h3>'; 
                     
                     $content .= '<table><tr><td>Prepared by: '.(($prepby != "")?str_replace("."," ",$prepby[0]):"").'</td><td>Checked by: '.(($chekby != "")?str_replace(""," ",$chekby[0]):"").'</td><td>Approved by: '.(($appby != "")?str_replace(""," ",$appby[0]):"").'</td></tr></table>';
                             $content .= '<br><br></div>';                                                                                    
                             $obj_pdf->AddPage('p','A4');
                             $obj_pdf->writeHTML($content);
                             $content = '';
                    }                                         
         ob_end_clean();
         $obj_pdf->Output('perdiem-'.$detailrow['job_id'].'.pdf','I');            
          }else if(isset($_GET['print_modif'])){
            $data = $_GET['print_modif'];
            $content = "";
            $alld = "";
            $fontsize = "font-size:12px";
            $select = "SELECT p.*, pm.id, pm.dateofmodification as dateofmodification, pm.status,pm.departure_date,pm.return_date,pm.travel_approved_by,pm.senior_accountant  FROM `perdiem` as p  inner join perdiemmodification as pm on p.id = pm.perdiemid WHERE pm.id = '$data'";
            $result = $conn_fleet->query($select);            
             if($result->num_rows > 0);
               $detailrow = $result->fetch_assoc();
                  $userlogo = $detailrow['company'];
                  $printid = $detailrow['id']; 
                  $stat = $detailrow['status']; 
                  $dest = explode('::',$detailrow['destination']);           
                  for($i=0;$i<(count($dest)-1);$i++){
                    $alld .= ($alld == '')?$dest[$i]:','.$dest[$i];
                  } 
               
            $logo = "SELECT * FROM comp where `Name` = '$userlogo' ";
              $result = $conn_fleet->query($logo);            
               if($result->num_rows > 0)
                    $roww = $result->fetch_assoc(); 
               $traveladvance = "SELECT * FROM tadvancemodification where perdime_id = '$printid' "; 
                  $resultt = $conn_fleet->query($traveladvance);            
               if($resultt->num_rows > 0)
                 while($travelrow = $resultt->fetch_assoc())
                  {           
                   $paymentoption = $travelrow['payment_option'];
                   $chequenum = $travelrow['cheque_number'];
                   $content .= '<div style="min-height:100%;padding:500pt;">';
                   $content .= '<h3 style="text-align:center;"><img src = "images/'.$roww['logo'].'" width="50"></h3><h3 style="text-align:center;">'.$userlogo.'</h3>';
                   $content .= '<h1 style="text-align:center;">Perdiem and Travel Advance Request Form</h1>';
                                   
                   $content .= '<hr><table cellpadding="3" style="font-size:11px;width:100%;">
                               <tr><td style="padding-right:10px;text-align:left;"><b class="fontsi">Job ID: </b>'.$detailrow['job_id'].'</td><td style="text-align:right;"><b>Date of request: </b>'.$detailrow['dateofmodification'].'</td></tr>
                               <tr><td style="padding-right:10px;text-align:left;"><b>Name Of Employee: </b>'.$travelrow['name_of_employee'].'</td><td style="text-align:right;"><b>Department: </b>'.$detailrow['fromdepartment'].'</td></tr>
                             ';
                                       
                             $content .= '<tr><td colspan="2"><h1 style="text-align:center">Travel Details</h1><hr></td></tr>';  
       
                   $content .= '<tr><td><b>Name of customer: </b>'.$detailrow['customer_name'].'</td><td><b>Destination: </b>'.$alld.'</td></tr>
                               <tr><td><b>Purpose of Travel: </b>'.$detailrow['reasonfortrip'].'</td><td><b>Means of Travel: </b>'.$detailrow['meansoftravel'].'</td></tr>
                               <tr><td><b>Date of departure: </b>'.$detailrow['departure_date'].'</td><td><b>Date of return:</b>'.$detailrow['return_date'].'</td></tr> 
                               <tr><td></td><td></td></tr> ';                                  
                   $content .= '</table><br><br>';
       
                   $content  .= '<br><br><h1 style="text-align:center;">Amount Required</h1>';
       
                   $content .= '<table cellpadding="3" style="font-size:11px; margin-left:100px;width:100%;"  border="1">
                               <tr style="text-align:center">
                               <td><b>Reason</b></td><td><b>Rate</b></td><td><b>Days</b></td><td><b>Birr</b></td>
                               </tr>';
                                     $split1 = ($travelrow['reason'] != "")?explode('::',$travelrow['reason']):[];
                                     $split2 = ($travelrow['rate'] != "")?explode('::',$travelrow['rate']):[];
                                     $split3 = ($travelrow['days'] != "")?explode('::',$travelrow['days']):[];
                                     $split4 = ($travelrow['birr'] != "")?explode('::',$travelrow['birr']):[];
                                     $total =0;
                                   for($j = 0;$j < count($split1); $j++){
                                     $content .='
                                     <tr style="font-size:11px;">
                                     <th>'.$split1[$j].'</th><th>'.$split2[$j].'</th><th>'.$split3[$j].'</th><th>'.$split4[$j].'</th>
                                     </tr>
                                     '; 
                                     $total += (double)$split4[$j];    
                                   }  
                                   $content .= '<tr style="font-size:11px;"><th>Total</th><th></th><th></th><th>'.$total.'</th></tr>';
                                  
                             $content .='</table><br><br><br>';
                                  $explode1 = explode('::',$detailrow['travel_approved_by']);
                                  $travelby = $explode1[0];
                                  $traveldate = $explode1[1];
                                  $explode2 = explode('::',$detailrow['senior_accountant']);
                                  $senioracc = $explode2[0];
                                  $date = $explode2[1];
                                 //  $explode3 = explode('::',$detailrow['payment_approved_by']);
                                 //  $paymentby = $explode3[0];
                                 //  $paymentdate = $explode3[1];
       
                            $content .= '<br><br><br><table cellpadding="3" style="font-size:11px;width:100%;"><tbody>
                            <tr><th colspan="2">I certified that I have received the sum of Birr ';
                              if($paymentoption == 'Petty' and $total != ''){
                              $content .= 'Cash <u> '.$total.'      </u>';
                              }else if($paymentoption == 'Cheque' and $chequenum != ''){
                              $content .= 'By cheque number <u>  '.$chequenum.'     </u>';
                              }else{
                              $content .= 'Cash _________________ or by Cheque number __________________';
                              } 
                            $content .= ' Dated ________________ Being advance for Per diem and travel Expenses, and I shall account for the above within a period of one week of my return.</th></tr>';
                            
                            $content .= '<br><br><br>
                            <tr>
                            <td style="padding-right:10px;text-align:left"><b>Employee name</b> <u>    '.$travelrow["name_of_employee"].'            </u></td>
                            <td style="text-align:right"><b>Signature</b> ____________________</td>
                            </tr>';
                            $content .= '</tbody></table>';
                            $content .= '<br><br></div>';
                                                      
                           $obj_pdf->AddPage('p','A4');
                           $obj_pdf->writeHTML($content);
                           $content = '';
                  }                                         
                 ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
                 $obj_pdf->Output('perdiem-'.$detailrow['job_id'].'.pdf','I');
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