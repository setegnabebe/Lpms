<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
require_once('../assets/tcpdf/tcpdf.php');
$logo = $_SESSION['logo'];
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
// $obj_pdf->AddPage('L','A4');
$datee=date("Y-m-d H:i:s");
$all = (isset($_GET["batch_print"]))?explode(",",$_GET["batch_print"]):[$_GET["print"]];
$type = explode(":|:",$all[0])[1];
$req_id = explode(":|:",$all[0])[0];
$sql_company = ($type == 'cluster' || $type == 'all')?'SELECT company FROM `cluster` where `id`="'.$all[0].'"':'SELECT company FROM `requests` where `request_id`="'.$req_id.'"';
if($type == 'cluster' || $type == 'all')
{
    $sql_company = "SELECT company,finance_company,`type` FROM `cluster` where `id` = ?";
    $stmt_get_company = $conn -> prepare($sql_company);
    $stmt_get_company -> bind_param("i", $all[0]);
}
else
{
    $sql_company = "SELECT company,finance_company FROM `requests` where `request_id` = ?";
    $stmt_get_company = $conn -> prepare($sql_company);
    $stmt_get_company -> bind_param("i", $req_id);
}
$stmt_get_company -> execute();
$result_get_company = $stmt_get_company -> get_result();
$row_company = $result_get_company->fetch_assoc();
$finance_company = $row_company["finance_company"];
$content.=$row_company["finance_company"]."->";
if(isset($row_company['type']))
{
    $content.=$row_company['type']."->";
    $sql = "SELECT company FROM `vehicle` WHERE `plateno` =? ";
    $stmt_company_vehicle = $conn_fleet -> prepare($sql);
    $stmt_company_vehicle -> bind_param("s", $row_company['type']);
    $stmt_company_vehicle -> execute();
    $result_company_vehicle = $stmt_company_vehicle -> get_result();
    if($result_company_vehicle -> num_rows > 0)
    {
        // $content.=$row_company['type']."->";
        $row_company_vehicle = $result_company_vehicle -> fetch_assoc();
        $finance_company = $row_company_vehicle['company'];
    }
}
$sql_logo="SELECT `logo`,`type` FROM `comp` where `Name` = ?";
$stmt_get_logo = $conn_fleet -> prepare($sql_logo);
$stmt_get_logo -> bind_param("s", $finance_company);
$stmt_get_logo -> execute();
$result_get_logo = $stmt_get_logo -> get_result();
$r = $result_get_logo -> fetch_assoc();
$logo = $r['logo'];
$company_name = $r['type'] == "Branch"?$finance_company:$finance_company." Pvt. Ltd. Co.";
$img = ($logo == "ultimate_logo.gif")?"l".$logo:$logo;
$img = ($logo == "Hagbeslogo.jpg")?"long_".$logo:$logo;
if($logo == "ultimate_logo.gif") $img=str_replace("gif","png",$img);
$specifications = "";
$content = "";
$once = true;
$new_page = (sizeof($all)>3)?'':'';//style="page-break-after: always;"
$content.='
<div style="">
<table><tr><td><div style="text-align:center;"><img src="../img/'.$img.'" width="100" height="300%"><br><b>'.$company_name.'<br>LPMS request</b><br>'.$datee.'</div></td></tr></table>
<table cellspacing="2" '.$new_page.'>';
// $content.='<tr>';
$ii=0;
foreach($all as $pid)
{
    $req_id = explode(":|:",$pid)[0];
    $type = explode(":|:",$pid)[1];
    if($type == 'cluster' || $type == 'all')
    {
        $obj_pdf->AddPage('L','A4');
        $obj_pdf->SetFont('freeserif','',8);
        $comp_count=0;
        $content.= '
        <table cellspacing="15" >
            <thead ><tr><th rowspan="2">No</th><th rowspan="2" >Item</th><!--<th rowspan="2" >Requested Qty</th>--><th rowspan="2" >Qty to be Purchased</th>';
                    $sql = "SELECT DISTINCT `providing_company` FROM `price_information` where `cluster_id` = ?";
                    $stmt_providing_company = $conn -> prepare($sql);
                    $stmt_providing_company -> bind_param("i", $pid);
                    $stmt_providing_company -> execute();
                    $result_providing_company = $stmt_providing_company -> get_result();
                    if($result_providing_company -> num_rows > 0)
                        while($row = $result_providing_company -> fetch_assoc())
                        {
                            $companies[$comp_count] = $row["providing_company"];
                            $comp_count++;
                            $content.= '<th colspan="2" style = "text-align:center;">'.$row["providing_company"].'<hr></th>';
                        }
                    $content.= '</tr><tr>'; 
                    for($i=1;$i<=$comp_count;$i++)
                        $content.= '<th >Unit Price</th><th >Total Price</th>';
                    $content.= '<hr></tr></thead><tbody>';
            $count = 0;
            $sql_all = "SELECT DISTINCT `purchase_order_id`,`quantity`,`cluster_id` FROM `price_information` where `cluster_id` = ?";
            $stmt_prices = $conn -> prepare($sql_all);
            $stmt_prices -> bind_param("i", $pid);
            $stmt_prices -> execute();
            $result_prices = $stmt_prices -> get_result();
            if($result_prices -> num_rows>0)
            while($row_all = $result_prices -> fetch_assoc())
            {
                $stmt_po -> bind_param("i", $row_all["purchase_order_id"]);
                $stmt_po -> execute();
                $result_po = $stmt_po -> get_result();
                if($result_po->num_rows>0)
                while($row_po = $result_po->fetch_assoc())
                {
                    if($row_po["status"] == "canceled") continue;
                    $count++;
                    $content.= '<tr><td>'.$count.'</td>';
                    $na_t=str_replace(' ','',$row_po["request_type"]);
                    $stmt_request -> bind_param("i", $row_po["request_id"]);
                    $stmt_request -> execute();
                    $result_request = $stmt_request -> get_result();
                    if($result_request -> num_rows>0)
                    while($row_req = $result_request -> fetch_assoc())
                    {
                        $content.= '<td>'.$row_req["item"].'</td><!--<td>'.$row_req["requested_quantity"].'</td>-->
                        <td id="purchasequan_'.$row_po["request_id"].'">'.$row_all['quantity']." ".$row_req['unit'].'</td>';
                        for($i=0;$i<$comp_count;$i++)
                        {
                            $sql_specific_price = 'SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`=? AND providing_company=?';
                            $stmt_specific_price = $conn -> prepare($sql_specific_price);
                            $stmt_specific_price -> bind_param("iis", $row_all['cluster_id'], $row_all["purchase_order_id"], $companies[$i]);
                            $stmt_specific_price -> execute();
                            $result_specific_price = $stmt_specific_price -> get_result();
                            if($result_specific_price -> num_rows > 0)
                            while($row_spec = $result_specific_price -> fetch_assoc())
                            {
                                $selected_price = $row_spec["selected"];
                                // $spec_data = ($row_spec["specification"] == "" || is_null($row_spec["specification"]))?'No Specifcation':$row_spec["specification"];
                                $colors = ($selected_price)?' style="background-color:grey"':'';
                                // $front_bord = ($selected_price)?'border-start-0 ':'';
                                // $back_bord = ($selected_price)?'border-end-0 ':'';
                                $content.= 
                                '<td '.$colors.'>'.number_format($row_spec['price'], 2, ".", ",").'</td>
                                <td '.$colors.'>'.number_format($row_spec['total_price'], 2, ".", ",").'</td>';
                            }
                            else {
                                $content.= '<td colspan="2" style="background-color:black"></td>';
                            }
                        }
                    }
                    $content.= '</tr>';
                }
            }
            $content.= '<tr><hr><th colspan="2" style="text-align:center;">Total Price</th><!--<td>-</td>--><td>-</td>';
            for($i=0;$i<$comp_count;$i++)
            {
                $stmt2 = $conn->prepare('SELECT SUM(total_price) AS sum_t,vat,SUM(after_vat) as after_vat FROM `price_information` where `cluster_id`="'.$pid.'" AND `providing_company`="'.$companies[$i].'" and selected');
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($total_p[$i],$vats[$i] , $av[$i]);
                $stmt2->fetch();
                $stmt2->close();
                if(!isset($total_p[$i]))$total_p[$i] = 0;
                if(!isset($vats[$i]))$vats[$i] = 0.15;
                if(!isset($av[$i]))$av[$i] = 0;
        
                $content.= '  <td>-</td> <th>'.number_format($total_p[$i], 2, ".", ",").'</th> ';
            }
            $content.= '</tr>
            <tr><th colspan="2" style="text-align:center;">15% VAT</th><!--<td>-</td>--><td>-</td>';
            for($i=0;$i<$comp_count;$i++)
            {
                $vat_only[$i] =$av[$i] - ($total_p[$i]);
                $content.= ' <td> - </td> <th>'.number_format($vat_only[$i], 2, ".", ",").'</th> ';
            }
            $content.= '</tr>
            <tr><th colspan="2" style="text-align:center;">Grand Total</th><!--<td>-</td>--><td>-</td>';
            for($i=0;$i<$comp_count;$i++)
            {
                $gt = $av[$i];
                $content.= '  <td> - </td> <th>'.number_format($gt, 2, ".", ",").'</th> ';
            }
        $content.= '</tr>
        </tbody>
        </table>';
        $stmt_cluster->bind_param("i", $pid);
        $stmt_cluster->execute();
        $result_cluster = $stmt_cluster->get_result();
        if($result_cluster->num_rows>0)
        while($row = $result_cluster->fetch_assoc())
        {
            $content.= 
            "<hr><p>Compiled by : ".$row['compiled_by']."</p>
            <p>Remarks : ".$row['Remarks']."</p>";
        }
        if($type != 'cluster') 
        {
            $temp_all = $all;
            $content.= '</table><table cellspacing="2" style="page-break-before: always;">';
            $i_all = 0;
            $stmt_po_cluster -> bind_param("i", $pid);
            $stmt_po_cluster -> execute();
            $result_po_cluster = $stmt_po_cluster -> get_result();
            if($result_po_cluster -> num_rows>0)
            while($row = $result_po_cluster -> fetch_assoc())
            {
                $new_array[$i_all] = $row['request_id'].":|:".$row['request_type'];
                $i_all++;
            }
        }
    }
    if($type != 'cluster')
    {
        if(!isset($temp_all) && $once)
        {
            $once =false;
            $obj_pdf->AddPage('L','A4');
            $obj_pdf->SetFont('freeserif','',10);
        }
        // $obj_pdf->SetFont('freeserif','',10);
        // $obj_pdf->AddPage('L','A4');
        $requests_a = (isset($new_array))?$new_array:[$req_id.":|:".$type];
        foreach($requests_a as $reqs)
        {
            $req_id = explode(":|:",$reqs)[0];
            $type = explode(":|:",$reqs)[1];
        $new_page_tbl = ($ii != 0 && $ii%6==0 && !isset($new_array))?'style="page-break-after: always;"':'';
        if($ii%3==0)
            {$content.='<tr '.$new_page_tbl.'>';}
        $stmt_request -> bind_param("i", $req_id);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        $row2 = $result_request -> fetch_assoc();
        $stmt_po_by_request -> bind_param("i", $req_id);
        $stmt_po_by_request -> execute();
        $result_po_by_reques = $stmt_po_by_request -> get_result();
        if($result_po_by_reques -> num_rows>0)
            $row_po = $result_po_by_reques -> fetch_assoc();
        if(!is_null($row2['specification']) && $row2['spec_dep'] == 'IT')
        {
            $spec_str = "Spec Recieved from ".$row2['spec_dep'];
            $spec_found = true;
            $sql_specification_filled = "SELECT * FROM `specification` WHERE `id` = ? and (pictures != '' OR details !='')";
            $stmt_specification_filled = $conn -> prepare($sql_specification_filled);
            $stmt_specification_filled -> bind_param("i", $row2['specification']);
            $stmt_specification_filled -> execute();
            $result_specification_filled = $stmt_specification_filled -> get_result();
            if($result_specification_filled -> num_rows>0)
                while($r = $result_specification_filled -> fetch_assoc())
                {
                    $spec = $r['details'];
                    $spec_date = date("d-M-Y",strtotime($r['date']));
                    $spec_giver = $r['given_by'];
                    $spec_pic = explode(":_:",$r['pictures']);
                    $specifications.='
                    <div style="display:block;height:100%;">
                        <table cellspacing="5" style="margin-top:20px;">
                        <thead>
                            <tr>
                                <th colspan="3" style="text-align:center;margin:15px 0px;"><b>Specifications For '.$row2["item"].'</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    <b>Specification Given Date : </b>'.$spec_date.'
                                </th>
                                <th></th>
                                <th>
                                    <b>Specification Given By : </b >'.$spec_giver.'
                                </th>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:center">
                                    '.$spec.'
                                </td>
                            </tr>';
                            if(!is_null($r['pictures']))
                            {
                                $pics_found = 0;
                                foreach($spec_pic as $s_pic)
                                {
                                    $ext = explode(".",$s_pic)[sizeof(explode(".",$s_pic))-1];
                                    if(!($ext == 'pdf' || $ext == 'docx'))
                                    {
                                        $pics_found++;
                                    }
                                }
                                if($pics_found)
                                {
                                    $specifications.='<tr>';
                                    $pics_c = 0;
                                    foreach($spec_pic as $s_pic)
                                    {
                                        if($pics_c%3==0)
                                            $specifications.='</tr><tr>';
                                        $specifications.='<td><img src="https://portal.hagbes.com/lpms_uploads/'.$s_pic.'" width="700"></td>';
                                        $pics_c++;
                                    }
                                    $specifications.='</tr>';
                                }
                            }
                            $specifications.='
                        </tbody>
                        </table>';
                }
        }
        // $spec
        // $instock 
        // $outstock 
        $job_no = "<br>";
        if($row2['request_type'] == 'Spare and Lubricant')
        {
            $job_no = '<h3 style="background-color:#5e5e5e;color:#ffffff;text-align:center;">'.((explode("|",$row2['request_for'])[1] == 0)?"No Job Number":"Job Number - ".explode("|",$row2['request_for'])[1]).'</h3><br>';
        }
        else if($row2['request_type'] == 'Tyre and Battery')
        {
            $job_no = '<h3 style="background-color:#5e5e5e;color:#ffffff;text-align:center;">'."Plate Number - ".$row2['request_for'].'</h3><br>';
        }
        else if($row2['request_type'] == 'Consumer Goods')
        {
            if($row2['request_for'] != 0)
            {
                $id = explode('|', $row2['request_for'])[0];
                $id_task = explode('|', $row2['request_for'])[1];
                $stmt_project_pms -> bind_param("i", $id);
                $stmt_project_pms -> execute();
                $result_project_pms = $stmt_project_pms -> get_result();
                if ($result_project_pms -> num_rows > 0) {
                    $r_project = $result_project_pms -> fetch_assoc();
                    $job_no = '<h3 style="text-align:center;">'."Project - ".$r_project['project_name'].'</h3>';
                    $sql_task = "SELECT * FROM task where `id` = ?";
                    $stmt_task_pms = $conn_pms -> prepare($sql_task);
                    $stmt_task_pms -> bind_param("i", $id_task);
                    $stmt_task_pms -> execute();
                    $result_task_pms = $stmt_task_pms -> get_result();
                    if ($result_task_pms->num_rows > 0) {
                        $row_task = $result_task_pms->fetch_assoc();
                        $job_no .= '<h4 style="text-align:center;">'."Task - ".$row_task['task_name'].'</h4><br>';
                    }
                }
            }
        }
        if(!is_null($row2['stock_info']))
        {
            $stmt_stock -> bind_param("i", $row2['stock_info']);
            $stmt_stock -> execute();
            $result_stock = $stmt_stock -> get_result();
            if($result_stock -> num_rows>0)
                while($r = $result_stock -> fetch_assoc())
                {
                    $instock = $r['in-stock'];
                    $forpurchase = $r['for_purchase'];
                }
        }
        $remark = ($row2['Remark'] == "#")?"No Remarks":$row2['Remark'];
        $uname = str_replace("."," ",$row2['customer']);
        $desc = ($row2['description'] == "#")?"No Descriptions":$row2['description'];
        $content.='<td><h3 style="background-color:#5e5e5e;color:#ffffff;text-align:center;">Item Name:'.$row2['item'].'&nbsp;(&nbsp;'.$type.'&nbsp;)</h3>
        '.$job_no.'
        <b>Requested By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$uname.'<br>
        ';// 
        $content.='<b>Quantity:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$row2["requested_quantity"].' '.$row2["unit"].'<br>';
        if(!is_null($row2['stock_info']))
        {
            if($forpurchase != 0)
                $content.='&nbsp;<b>Purchase Quantity:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$forpurchase.' '.$row2["unit"].'<br>';
            if($instock != 0)
                $content.='&nbsp;&nbsp;<b>Instock Quantity:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$instock.' '.$row2["unit"].'<br>';
        }
        $content.='&nbsp;&nbsp;<b>Date Requested:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.date("M d,Y",strtotime($row2['date_requested'])).'<br>
        <b>Date Needed By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.date("M d,Y",strtotime($row2['date_needed_by'])).'<br>
        <b>Department:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$row2['department'].'
        <br>
        <b>Description:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$desc.'<br>
        <b>Remark:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>'.$remark.'<br></td>';
        if($ii%3==2)
            {$content.='</tr>';}
        $ii++;		   
            
    } 					
}
} 					

if($ii%3!=0)
    $content.='</tr>';
// $content.='</tr>';
$content.='</table>';
$content .= '<hr>';
if(isset($row_po['purchase_officer']))
$content .= '<hr><p style="text-align:center;"><b>Assigned Purchase Officer : </b>'.$row_po['purchase_officer'].'<br><hr>';
$content .= '
</div>';

$content .= $specifications;
$obj_pdf->writeHTML($content);
ob_end_clean();//To Solve TCPDF ERROR: So   me data has already been output, can't send PDF file
$obj_pdf->Output('request_form.pdf','I');
// echo $content;
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