<?php
session_start();
include "../../connection/connect.php";
include "../../common/functions.php";
require_once('../../assets/tcpdf/tcpdf.php');
$logo = $_SESSION['logo'];
$img = ($logo == "Hagbeslogo.jpg" || $logo == "ultimate_logo.gif")?"l".$logo:$logo;
if($logo == "ultimate_logo.gif") $img=str_replace("gif","png",$img);
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
$obj_pdf->SetFont('freeserif','',10);
$obj_pdf->AddPage('L','A4');
$datee=date("Y-m-d H:i:s");
$all = explode(",",$_GET["batch_print"]);
$content = "";
$content.='<table><tr><td><p style="text-align:center;"><img src="../../img/'.$img.'" width="100" height="300%"></p></td><td><b>'.$_SESSION["company"].' Pvt. Ltd. Co.<br>LPMS request</b><br>'.$datee.'</td></tr></table><table cellspacing="2">';
// $content.='<tr>';
$ii=0;
foreach($all as $pid)
{
    if($ii%3==0)
        {$content.='<tr>';}
    $stmt_po -> bind_param("i", $pid);
    $stmt_po -> execute();
    $result_po = $stmt_po -> get_result();
    $row = $result_po->fetch_assoc();
    $stmt_request -> bind_param("i", $row['request_id']);
    $stmt_request -> execute();
    $result_request = $stmt_request -> get_result();
    $row2 = $result_request->fetch_assoc();
    // $spec
    // $instock 
    // $outstock 
    $remark = ($row2['Remark'] == "#")?"No Remarks":$row2['Remark'];
    $desc = ($row2['description'] == "#")?"No Descriptions":$row2['description'];
    $uname =str_replace("."," ",$row2['customer']);
    $content.='<td><h3 style="background-color:#5e5e5e;color:#ffffff;text-align:center;">Item Name:'.$row2['item'].'&nbsp;(&nbsp;'.$row['request_type'].'&nbsp;)</h3><br>
    <b>Requested By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$uname.'<br>
    <b>Quantity:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$row2["requested_quantity"].' '.$row2["unit"].'<br>
    <b>Date Requested:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.date("M d,Y",strtotime($row2['date_requested'])).'<br>
    <b>Date Needed By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.date("M d,Y",strtotime($row2['date_needed_by'])).'<br>
    <b>Department:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$row2['department'].'
    <br>
    <b>Description:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$desc.'<br>
    <b>Remark:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$remark.'<br></td>';
    if($ii%3==2)
        {$content.='</tr>';}
    $ii++;		    					
}
if($ii%3!=0)
    $content.='</tr>';
// $content.='</tr>';
$content.='</table>';
$content .= '<hr><p style="text-align:center;"><b>Assigned Purchase Officer : </b>'.$row['purchase_officer'].'<br><hr></p>';
$obj_pdf->writeHTML($content);
ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
$obj_pdf->Output('request_form.pdf','I');

$conn->close();
$conn_fleet->close();
$conn_ws->close();
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