<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
require_once('../assets/tcpdf/tcpdf.php');
$logo = $_SESSION['logo'];
$img = ($logo == "Hagbeslogo.jpg" || $logo == "ultimate_logo.gif")?"l".$logo:$logo;
if($logo == "ultimate_logo.gif") $img=str_replace("gif","png",$img);
$obj_pdf=new TCPDF('p',PDF_UNIT,PDF_PAGE_FORMAT,true,'utf-8',false);
$obj_pdf->SetCreator(PDF_CREATOR);
// $obj_pdf->SetTitle("CPV for Purchase");
$obj_pdf->SetHeaderData('','',PDF_HEADER_TITLE,PDF_HEADER_STRING);
$obj_pdf->SetHeaderFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_MAIN));
$obj_pdf->SetFooterFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_DATA));
$obj_pdf->SetDefaultMonospacedFont('freeserif');
$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$obj_pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP,PDF_MARGIN_RIGHT);
$obj_pdf->SetPrintHeader(false);
$obj_pdf->SetPrintFooter(true);
$obj_pdf->SetAutoPageBreak(TRUE,'10');
$obj_pdf->AddPage('P','A4');
$obj_pdf->SetFont('freeserif','',8);
//$tagvs = array('div' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
//$obj_pdf->setHtmlVSpace($tagvs);
$obj_pdf->setCellMargins(5, 0, 5, 0);
$obj_pdf->setCellPaddings(5, 0, 5, 0);

$datee=date("Y-m-d H:i:s");
if(sizeof(explode(",",$_GET["print_cpv"]))>1)
{
    $general = false;
    $cluster_id = explode(",",$_GET["print_cpv"])[0];
    $pos = explode(":-:",explode(",",$_GET["print_cpv"])[1]);
}
else
{
    $general = true;
    $cluster_id = $_GET["print_cpv"];
}
$count = 0;
$specifications = "";
$content = "";
$new_page = '';//style="page-break-after: always;"
$content.='
<div style="">
';
$cpv_nums = "";
$stmt_cluster -> bind_param("i", $cluster_id);
$stmt_cluster -> execute();
$result_cluster = $stmt_cluster->get_result();
$row = $result_cluster->fetch_assoc();
$sql_prov = "SELECT * FROM `price_information` where cluster_id='$cluster_id' and selected";
if(!$general)
{
    $sql_prov .= " AND (";
    $first = true;
    foreach($pos as $po)
    {
        $sql_prov .= ($first)?" purchase_order_id = '$po'":" OR purchase_order_id = '$po'";
        $first = false;
    }
    $sql_prov .= ")";
}
$sql_prov .= " group by providing_company";
$stmt_prices_selected = $conn->prepare($sql_prov);
$stmt_prices_selected -> execute();
$result_prices_selected = $stmt_prices_selected -> get_result();
$no_of_form = $result_prices_selected -> num_rows;
while($row_prov = $result_prices_selected -> fetch_assoc())
{
    $count++;
    $new_page = ($no_of_form == $count)?"":'style="page-break-after: always;"';
    $items = "";
    $price_before_vat = 0;
    $pos = "";
    $price_after_vat = 0;
    $sql_price = "SELECT * FROM `price_information` where cluster_id = ? and selected and providing_company = ?";
    $stmt_prices_selected_comps = $conn->prepare($sql_price);
    $stmt_prices_selected_comps -> bind_param("is", $cluster_id ,$row_prov['providing_company']);
    $stmt_prices_selected_comps -> execute();
    $result_prices_selected_comps = $stmt_prices_selected_comps -> get_result();
    while($row_price = $result_prices_selected_comps -> fetch_assoc())
    {
        $pos .= ($pos == "")?$row_price['purchase_order_id']:":-:".$row_price['purchase_order_id'];
        $stmt_po -> bind_param("i", $row_price['purchase_order_id']);
        $stmt_po -> execute();
        $result_po = $stmt_po -> get_result();
        $row_po = $result_po->fetch_assoc();
    
        $stmt_request -> bind_param("i", $row_po['request_id']);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        $row_req = $result_request -> fetch_assoc();
        
        if($row_po['status'] != "canceled")
        {
            $items .= ($items=="")?$row_req['item']:" AND ".$row_req['item'];
            $price_before_vat += $row_price['total_price'];
            $price_after_vat += $row_price['after_vat'];
        }
    }
    $sql_cheque = "SELECT * FROM `cheque_info` where cluster_id = ? AND purchase_order_ids = ? AND void = 0";
    if(isset($_GET['advance'])) 
    {
        $fisrt_cpv = $sql_cheque;
        $sql_cheque .=" AND final = 1";
    }
    $stmt_cheques_active = $conn->prepare($sql_cheque);
    $stmt_cheques_active -> bind_param("is", $cluster_id ,$pos);
    $stmt_cheques_active -> execute();
    $result_cheques_active = $stmt_cheques_active -> get_result();
    if($result_cheques_active->num_rows > 0)
    {
        $row_cheque = $result_cheques_active->fetch_assoc();
        $date = date("d/m/Y",strtotime($row_cheque['creation_date']));
        $found_cpv_no = $row_cheque['cpv_no'];  
        $cpv_nums .= ($cpv_nums=="")?$found_cpv_no:" ,".$found_cpv_no;
        $cheque_no = $row_cheque['cheque_no'];  
        $percent = $row_cheque['prepared_percent'];
        $cheque_for = $row_cheque['cheque_amount'];
        $withholding = $row_cheque['withholding']; 
        $bank = $row_cheque['bank'];  
        $company = $row_cheque['company'];
        $stmt_company -> bind_param("s", $company);
        $stmt_company -> execute();
        $result_company = $stmt_company -> get_result();
        $r = $result_company -> fetch_assoc();
        $comp_type = $r['type'];
        $main_comp = $r['main'];
        $img = $r['logo'];
        $img = ($img == "Hagbeslogo.jpg" || $img == "ultimate_logo.gif")?"l".$img:$img;
        $prepared_by = $row_cheque['created_by'];  
    }
    if(isset($_GET['advance']))
    {
        $stmt_cheques_active = $conn->prepare($fisrt_cpv);
        $stmt_cheques_active -> bind_param("is", $cluster_id ,$pos);
        $stmt_cheques_active -> execute();
        $result_cheques_active = $stmt_cheques_active -> get_result();
        $row_cheque = $result_cheques_active->fetch_assoc();
        $prev_cheque = $row_cheque['cheque_amount'];
        $new_percent = 100 - floatVal($row_cheque['prepared_percent']);
    }
    $advance = (isset($percent) && $percent != 100)?"( Advance Payment )":"";
    $company = (isset($company) && $company != "")?$company:$_SESSION["company"];
    if(isset($comp_type))
        $company .= ($comp_type != "Branch")?" Pvt. Ltd. Co.":" Branch";
    $main_co = (isset($main_comp) && $comp_type == "Branch")?$main_comp." Pvt. Ltd. Co.<br>":"";
    $prepared_by = (isset($prepared_by) && $prepared_by != "")?$prepared_by:$_SESSION["username"];
    // $hide = ($count != 1)?" d-none":"";  id='payment_req_$count'
    // <h1 style="text-align:center;margin-bottom:100px;">'.$_SESSION['company'].'</h1>
    $content .= '<h3 style="text-align:center;"><img src="../img/'.$img.'" width="100"></h3>
                 <h3 style="text-align:center;"><b>'.$main_co.$company.'<br>LPMS request</b>
                 </h3>
                 <div '.$new_page.'>
                    <h1 style="text-align:center;margin:100px">Cheque, C.P.O & Tansfer Payment Requisition Form</h1>
                    <br><br><br><br>
                    <h2 style="text-align:right;margin:70px">Date : '.$date.'</h2>
                    <br><br><br><br><br><br><br><br><br><br>
                    <h2 style="text-align:left;margin-left:10px;margin-right:10px">
                        <b>Pay For : </b><u> '.$row_prov['providing_company'].'</u>
                        <br><br>
                        <b>In Figure : </b><u>'.number_format($cheque_for, 2, ".", ",").'</u>
                        <br><br>
                        <b>In Words : </b><u>'.number_converter($cheque_for).'</u>
                        <br><br>
                        <b>Purpose : </b><u style="line-height: 2;">for Purchase of '.$items.' as per the attached document '.$advance.'</u>
                        <br><br><br><br><hr><br><br><br><br>';
                        if($withholding != 0){
                    $content .=  '<p>Cash Withholding :<span class="text-dark">  2% Of ('.number_format($price_before_vat, 2, ".", ",").')  = '.number_format($withholding, 2, ".", ",").'</span></p>';             
                               }  
                    if(isset($percent) && $percent != 100 && !isset($_GET['advance']))
                        $content .= '<p>Cheque Prepared For :<span class="text-dark">  '.$percent.' % Of ('.number_format($price_before_vat, 2, ".", ",").') = '.number_format($cheque_for, 2, ".", ",").'</span></p>';  
                    else if(isset($_GET['advance']))
                        $content .= '<p>Cheque Prepared For :<span class="text-dark">  '.number_format($price_after_vat, 2, ".", ",").' - '.number_format($withholding, 2, ".", ",")." - ".number_format($prev_cheque, 2, ".", ",")." ( Advance )".' = '.number_format($cheque_for, 2, ".", ",").'</span></p>';
                    else
                        $content .= '<p>Cheque Prepared For :<span class="text-dark">  '.number_format($price_after_vat, 2, ".", ",").' - '.number_format($withholding, 2, ".", ",").' = '.number_format($cheque_for, 2, ".", ",").'</span></p>';
                    $content .=  '
                        <br><br><br><br><br><br><br><br><br><br><hr><br><br><br><br><br><br><br><br><br><br>
                        <p><b>Prepare By  : <i class="text-primary">'.$prepared_by.'</i></b></p>
                        <br><br><br><br><br><br><br><br><br><br><hr><br><br><br><br><br><br><br><br><br><br>
                        <p><b>CPV  : <i class="text-primary">'.$found_cpv_no.'</i></b></p>
                        <p><b>Cheque Number  : <i class="text-primary">'.$cheque_no.'</i></b>  <b>'.$bank.'    </b></p>
                        <hr>
                    </h2>
                </div>';
}
$content .= '</div>';
$obj_pdf->SetTitle('CPV-No.'.$cpv_nums);

$obj_pdf->writeHTML($content);
ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
$obj_pdf->Output('CPV-No.'.$cpv_nums.'.pdf','I');
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