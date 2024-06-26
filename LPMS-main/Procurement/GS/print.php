<?php

include '../../connection/connect.php';
include '../../common/functions.php';
$rep = false;
$content ='';
?>

<!DOCTYPE html>
	<head>
<?php
	require_once('../../assets/tcpdf/tcpdf.php');
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
	$obj_pdf->AddPage('L','A5');
	$datee=date("Y-m-d H:i:s");
$data = explode("_",$_GET['print']);
$type = na_t_to_type($conn,$data[0]);
$na_t=$data[0];
$spec = '';
$spec_date = $manager = '';
$spec_giver = '';
$stmt_request -> bind_param("i", $data[1]);
$stmt_request -> execute();
$result_request = $stmt_request -> get_result();
if($result_request -> num_rows>0)
    while($row = $result_request -> fetch_assoc())
    {
        if($rep)
            $replacement = explode(",",$row['to_replace']);
        $stmt_manager -> bind_param("ss", $_SESSION['department'], $_SESSION['company']);
        $stmt_manager->execute();
        $result_manager = $stmt_manager -> get_result();
        if($result_manager -> num_rows>0)
            while($r = $result_manager -> fetch_assoc())
            {
                $manager = $r['Username'];
            }
        if(is_null($row['specification']))
        {
            if(is_null($row['spec_dep']))
                $spec_str = "Specification not Requested";
            else
                $spec_str = "Waiting for Specs from ".$row['spec_dep'];
        }
        else
        {
            $spec_str = "Spec Recieved from ".$row['spec_dep'];
            $spec_found = true;
            $stmt_specification -> bind_param("i", $row['specification']);
            $stmt_specification->execute();
            $result_specification = $stmt_specification -> get_result();
            if($result_specification -> num_rows>0)
                while($r = $result_specification -> fetch_assoc())
                {
                    $spec = $r['details'];
                    $spec_date = $r['date'];
                    $spec_giver = $r['given_by'];
                }
        }
                $content .='<h1 style="text-align:center;">Request Form</h1>';
                
                $content .='<ul class= "list-group list-group-flush">';
                $uname =str_replace("."," ",$row['customer']);
                $content .='<h5 class="text-capitalize mb-2 text-center">Item - '.$row["item"].'</h5>
                <div class="row m-auto w-100">
                <li class="list-group-item list-group-item-primary mb-4 m-auto">
                    <ul class= "list-group list-group-flush">
                        <div class="row m-auto w-100">
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Quantity  -  </i>'.$row["requested_quantity"].' '.$row["unit"].'</li>
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Requested By : </i>'.$uname.'</li>
                        </div>';
                        if($rep && count($replacement)>0)
                        {
                            $content .='<div class="m-auto w-100 p-3 my-3 alert-primary" id="replace">
                            <li class="list-group-item list-group-item-light p-3">
                            <h5 class="text-center">Items To Be Replaced</h5>
                            <ul class= "list-group list-group-flush">';
                            $i=0;
                            foreach($replacement as $rep_val)
                            {
                                $i++;
                                $content .=' <li class="list-group-item list-group-item-light text-center"><i class="text-primary">Serial Number - $i :</i> $rep_val</li>';
                            }
                            $content .='  </ul>
                                </li>
                            </div>';
                        }

                        $content .='<div class="row m-auto w-100">
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Date Requested  -  </i>'. date('d-M-Y', strtotime($row["date_requested"])).'</li>
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Date Needed By  -  </i>'. date('d-M-Y', strtotime($row["date_needed_by"])).'</li>
                        </div>';
                        $content .=(!isset($spec_found))?'':'
                        <div class="row m-auto w-100">
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Specifications : </i>$spec_str 
                            <li class="list-group-item list-group-item-light col-sm-12 col-md-6"><i class="text-primary">Requesting Department : </i>'.$row["department"].'</li>
                        </div>
                        <div class="m-auto w-100 p-3 my-3 alert-primary" id="content">
                            <li class="list-group-item list-group-item-light border-0"><i class="text-dark fw-bold">Date of Specification Given : </i>'.date('d-M-Y', strtotime($spec_date)).'</li>
                            <li class="list-group-item list-group-item-light border-0"><i class="text-dark fw-bold">Given By : </i>$spec_giver</li>
                                <li class="list-group-item list-group-item-light p-3">
                                    <h5 class="text-center">Specification Details</h5>
                                    '.$spec.'
                                </li>
                        </div>';
                        $content .=($row["Remark"]=="#")?'':'<li class="list-group-item list-group-item-light"><i class="text-primary">Remark  -  </i>'.$row["Remark"].'</li>';
                        $content .=($row["description"]=="#")?'':'<li class="list-group-item list-group-item-light"><i class="text-primary">Description  -  </i>'.$row["description"].'</li>';
                        $content .=($row["description"]=="#" && $row["Remark"]=="#")?'<li class="list-group-item list-group-item-light"><i class="text-primary text-center">No Description and Remark Given By '.$row["customer"].'</i></li>':'';
                        
                $content .=(!is_null($row["manager_description"]))?
                '<li class="list-group-item list-group-item-light"><i class="text-primary">Manager Remark  -  </i>'.$row["manager_description"].'</li>':'';
                $content .='</ul>
                </li>
                </div>';
    }
    $obj_pdf->writeHTML($content);
	ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
	$obj_pdf->Output('request_form.pdf','I');
?>
    </head>
    </html>
<?php
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>