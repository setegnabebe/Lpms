<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
include '../connection/connect.php';
include '../common/functions.php';
// include '../common/details.php';
$comp_count=0;
$data = $_GET['data'];
$stmt_po_cluster->bind_param("i", $data);
$stmt_po_cluster->execute();
$result_po_cluster = $stmt_po_cluster->get_result();
if($result_po_cluster->num_rows>0)
    while($r = $result_po_cluster->fetch_assoc())
    {
        $type = $r['request_type'];
        $purchase_order_id = $r['purchase_order_id'];
        $performa_id = $r['performa_id'];
    }
if(isset($_GET['name']))
{
$stmt_selections_specific->bind_param("is", $data, $_GET['name']);
$stmt_selections_specific->execute();
$result_selections_specific = $stmt_selections_specific->get_result();
if($result_selections_specific->num_rows>0)
    while($r = $result_selections_specific->fetch_assoc())
    {
        $person_selections = explode(",",$r['selection']);
    }
$title = $_GET['name'];
}
else $title = "Selected";


echo "
<div class='card h-100' id='tbl_cs_view' style = 'overflow:scroll'>
    <div class='card-body'>
<table class='table text-center' cellspacing='15'>
    <thead>
        <tr>
        <th rowspan='2'>No</th>
        <th rowspan='2'>Item</th>
        <th rowspan='2'>Requested Qty</th>
        <th rowspan='2'>Qty to be Purchased</th>";
        $sql = "SELECT DISTINCT `providing_company` FROM `price_information` where `cluster_id`= ?";
        $stmt_companies = $conn->prepare($sql);
        $stmt_companies->bind_param("i", $data);
        $stmt_companies->execute();
        $result_companies = $stmt_companies->get_result();
        if($result_companies->num_rows>0)
        while($row = $result_companies->fetch_assoc())
        {
            $companies[$comp_count] = $row['providing_company'];
            $comp_count++;
            echo "<th colspan='3'>".$row['providing_company']."</th>";
        }   
        echo "
        </tr><tr>"; 
        for($i=1;$i<=$comp_count;$i++)
            echo "
            <th>Proforma Qty</th><th>Unit Price</th><th>Total Price</th>";
        echo"
        </tr>
    </thead>
    <tbody>";
    $count = 0;
    $sql = "SELECT DISTINCT `purchase_order_id` FROM `price_information` where `cluster_id`= ?";
    $stmt_pos = $conn->prepare($sql);  
    $stmt_pos->bind_param("i", $data);
    $stmt_pos->execute();
    $result_pos = $stmt_pos->get_result();
    if($result_pos->num_rows>0)
    while($row_all = $result_pos->fetch_assoc())
    {
        $count++;
        echo "<tr><td>$count</td>";
        $stmt_po->bind_param("i", $row_all['purchase_order_id']);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        if($result_po->num_rows>0)
        while($row_po = $result_po->fetch_assoc())
        {
            $na_t=str_replace(" ","",$row_po['request_type']);
            $stmt_request->bind_param("i", $row_po['request_id']);
            $stmt_request->execute();
            $result_request = $stmt_request->get_result();
            if($result_request->num_rows>0)
            while($row_req = $result_request->fetch_assoc())
            {
                $stmt_stock->bind_param("i", $row_req['stock_info']);
                $stmt_stock->execute();
                $result_stock = $stmt_stock->get_result();
                if($result_stock->num_rows>0)
                while($row_q = $result_stock->fetch_assoc())
                {
                    $to_purchase = $row_q['for_purchase'];
                }
                echo "<td><button type='button'  title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button></td><td>".$row_req['requested_quantity']."</td>
                <td id='purchasequan_".$row_po['request_id']."'>".$to_purchase."</td>";
                for($i=0;$i<$comp_count;$i++)
                {
                    $sql_specific_price = "SELECT * FROM `price_information` where `cluster_id` = ? AND `purchase_order_id`= ? AND `providing_company`= ?";
                    $stmt_specific_price = $conn->prepare($sql_specific_price);  
                    $stmt_specific_price->bind_param("iis", $data, $row_all['purchase_order_id'], $companies[$i]);
                    $stmt_specific_price->execute();
                    $result_specific_price = $stmt_specific_price->get_result();
                    if($result_specific_price->num_rows>0)
                    while($row_spec = $result_specific_price->fetch_assoc())
                    {
                        if(isset($person_selections))
                            $selected_price = in_array($row_spec['id'], $person_selections);
                        else
                            $selected_price = $row_spec['selected'];
                        $spec_data = ($row_spec['specification'] == '' || is_null($row_spec['specification']))?"No Specifcation":$row_spec['specification'];
                        $colors = ($selected_price)?"text-success border-success border-3 ":"";
                        $front_bord = ($selected_price)?"border-start-0 ":"";
                        $back_bord = ($selected_price)?"border-end-0 ":"";
                        if($row_spec['specification'] == '' || is_null($row_spec['specification']))
                            $spec_show = "<td class=' $colors $front_bord'>".$row_spec['total_price']."</td>";
                        else
                            $spec_show = "<td class='position-relative $colors $front_bord'>".$row_spec['total_price']."
                            <span data-bs-html='true' class='btn btn-sm position-absolute top-0 start-100 translate-middle  badge rounded-pill alert-primary' data-bs-toggle='popover' title='Specification' 
                            data-bs-content='$spec_data'> <i class='fa fa-info-circle' title='Details'></i></span>
                            </td>";
                        echo "<td class='$colors $back_bord'>".$row_spec['quantity']."</td>
                        <td class='$colors $front_bord $back_bord'>".number_format($row_spec['price'], 2, ".", ",")."</td>
                        $spec_show
                        ";
                    }
                    else {
                        echo "<td colspan='3' class='bg-secondary'></td>";
                    }
                }
            }
        }
        echo "</tr>
        ";
    }
    echo"<tr><th colspan='2'>Total Price</th><td>-</td><td>-</td>";
    for($i=0;$i<$comp_count;$i++)
    {
        
        $stmt2 = $conn->prepare("SELECT SUM(total_price) AS sum_t FROM `price_information` where `cluster_id`='".$data."' AND `providing_company`='".$companies[$i]."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($total_p[$i]);
        $stmt2->fetch();
        $stmt2->close();

        echo" <td>-</td> <td>-</td> <th>".$total_p[$i]."</th> ";
    }
    echo"</tr>
    <tr><th colspan='2'>15% VAT</th><td>-</td><td>-</td>";
    for($i=0;$i<$comp_count;$i++)
    {
        $stmt_limit->bind_param("s", $row_po['company']);
        $stmt_limit->execute();
        $result_limit = $stmt_limit->get_result();
        if($result_limit->num_rows>0)
        {
            while($r_new = $result_limit->fetch_assoc())
            {
                $Vat = $r_new['Vat'];
            }
        }
        else $Vat = 0.15;
        $vat[$i] = $Vat*($total_p[$i]);
        echo" <td> - </td><td> - </td> <th>".$vat[$i]."</th> ";
    }
    echo"</tr>
    <tr><th colspan='2'>Grand Total</th><td>-</td><td>-</td>";
    for($i=0;$i<$comp_count;$i++)
    {
        $gt = $vat[$i] + $total_p[$i];
        echo" <td> - </td> <td>- </td> <th>$gt</th> ";
    }
echo "
</tr>
</tbody>
</table>

</div>";
$stmt_cluster->bind_param("i", $data);
$stmt_cluster->execute();
$result_cluster = $stmt_cluster->get_result();
if($result_cluster->num_rows>0)
while($row = $result_cluster->fetch_assoc())
{
    echo "<span class='small text-secondary float-end'>Compiled by : ".$row['compiled_by']."</span>
        <span class='small text-secondary float-end'>Remarks : ".$row['Remarks']."</span>";
}
echo "</div>";
$na_t=str_replace(" ","",$type);
$date=date("Y-m-d H:i:s");

$stmt_po_cluster->bind_param("i", $data);
$stmt_po_cluster->execute();
$result_po_cluster = $stmt_po_cluster->get_result();
if($result_po_cluster->num_rows>0)
while($row6 = $result_po_cluster->fetch_assoc())
{
$rep = false;
$spec = '';
$spec_date = '';
$spec_giver = '';
$stmt_request->bind_param("i", $data);
$stmt_request->execute();
$result_request = $stmt_request->get_result();
if($result_request->num_rows>0)
    while($row = $result_request->fetch_assoc())
    {
        if($rep && $row['to_replace']!=null)
            $replacement = explode(",",$row['to_replace']);
        $stmt_manager->bind_param("ss", $_SESSION['department'], $_SESSION['company']);
        $stmt_manager->execute();
        $result_manager = $stmt_manager->get_result();
        if($result_manager->num_rows>0)
            while($r = $result_manager->fetch_assoc())
            {
                $manager = $r['Username'];
            }
        $stmt_stock->bind_param("i", $row['stock_info']);
        $stmt_stock->execute();
        $result_stock = $stmt_stock->get_result();
        if($result_stock->num_rows>0)
            while($r = $result_stock->fetch_assoc())
            {
                $instock = $r['in-stock'];
                $outofstock = $r['for_purchase'];
                $price_instock = $r['total_price'];
                if(isset($r['remark']) || !is_null($r['remark']))
                    $property_reason = $r['remark'];
                if($r['status'] == 'Approved' || $r['status'] == 'Rejected')
                    $property_approval = $r['status'];
                    
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
            $stmt_spec->bind_param("i", $row['specification']);
            $stmt_spec->execute();
            $result_spec = $stmt_spec->get_result();
            if($result_spec->num_rows>0)
                while($r = $result_spec->fetch_assoc())
                {
                    $spec = $r['details'];
                    $spec_date = $r['date'];
                    $spec_giver = $r['given_by'];
                }
        }
        $purchase_officer = $row6['purchase_officer'];
        $collector = $row6['collector'];
        $cluster = $row6['cluster_id'];
        $purchase_order_id = $row6['purchase_order_id'];
        $performa_id = $row6['performa_id'];
        $prices = 0;
        $sql = "SELECT * FROM `price_information` WHERE `cluster_id`=? and `purchase_order_id`=? AND selected";
        $stmt_selected = $conn->prepare($sql);  
        $stmt_selected->bind_param("ii", $cluster, $purchase_order_id);
        $stmt_selected->execute();
        $result_selected = $stmt_selected->get_result();
        if($result_selected->num_rows>0)
            while($r = $result_selected->fetch_assoc())
            {
                $stmt_cluster->bind_param("i", $cluster);
                $stmt_cluster->execute();
                $result_cluster = $stmt_cluster->get_result();
                $clus_row=$result_cluster->fetch_assoc();

                $stmt_limit->bind_param("s", $clus_row['company']);
                $stmt_limit->execute();
                $result_limit = $stmt_limit->get_result();
                if ($result_limit->num_rows ==0)
                {
                    $others = 'Others';
                    $stmt_limit->bind_param("s", $others);
                    $stmt_limit->execute();
                    $result_limit = $stmt_limit->get_result();
                }
                if($result_limit->num_rows>0)
                {
                    $r_new = $result_limit->fetch_assoc();
                    $Vat = $r_new['Vat'];
                }
                else $Vat = 0.15;
                $providing_company[$prices] = $r['providing_company'];
                $provided_quan[$prices] = $r['quantity'];
                $prov_unit_price[$prices] = $r['price'];
                $prov_total_price[$prices] = $r['total_price'];
                $after_tax[$prices] = (intval($r['total_price'])*$Vat) + intval($r['total_price']);
                $prices++;
            }
            $mode = "";
            if($type=="Consumer Goods")
            {
                if($row['request_for'] == 0)
                {
                    $stmt_project->bind_param("i", $row['request_for']);
                    $stmt_project->execute();
                    $result_project = $stmt_project->get_result();
                    if($result_project->num_rows>0)
                        while($r = $result_project->fetch_assoc())
                        {
                            $for_name = $r['Name']." Project";
                        }
                }
                else
                {
                    $id = explode("|",$row['request_for'])[0];
                    $stmt_project_pms->bind_param("i", $id);
                    $stmt_project_pms->execute();
                    $result_project = $stmt_project_pms->get_result();
                    if($result_project->num_rows>0)
                        while($r = $result_project->fetch_assoc())
                        {
                            $stmt_task->bind_param("i", $id);
                            $stmt_task->execute();
                            $result_task = $stmt_task->get_result();
                            if($result_task->num_rows>0)
                            {
                                while($row_task = $result_task->fetch_assoc())
                                {
                                    $for_name = $r['project_name']." Project | ".$row_task['task_name']." Task";
                                }
                            }
                        }
                }
            }
            if($type == 'Spare and Lubricant')
            {
                $mode = "<span class='text-secondary small'>( Mode - $row[mode] / $row[type])</span>";
                $stmt_description->bind_param("i", $row['request_for']);
                $stmt_description->execute();
                $result_description = $stmt_description->get_result();
                if($result_description->num_rows>0)
                    while($r = $result_description->fetch_assoc())
                    {
                        $for_name = $r['description']." Job";
                    }
            }
            $plate_incase = "";
            if($type == 'Tyre and Battery')
            {
                $mode = "<span class='text-secondary small'>( Mode - $row[mode] )</span>";
                $sql = "SELECT driver FROM `vehicle` WHERE `plateno` =? ";
                
                $stmt2 = $conn_fleet->prepare($sql);
                $stmt2->bind_param("s", $row['request_for']);
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($driver);
                $stmt2->fetch();
                $stmt2->close();
                if(!isset($driver) || $driver == NULL) $driver = "External Customer";
                $plate_incase = "
                <div class='row m-auto w-100'>
                    <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Plate Number  -  </i>".$row['request_for']."</li>
                    <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Driver  -  </i>".$driver."</li>
                </div>
                ";
            }
            $color_btn = ($row['recieved']=='yes')?"success":"primary";
                echo "<ul class= 'list-group list-group-flush'>";
                echo "<h4 class='text-capitalize mb-2 text-center text-primary'> ".$row['company']."</h4>";
                echo "<h5 class='text-capitalize mb-2 text-center'>Item - ".$row['item']." $mode
                </h5>
                <span class='text-capitalize text-secondary mb-2 text-center'><i class='text-primary'>Catagory </i>- $type";
                echo (isset($for_name))?"<span class='text-capitalize text-secondary ms-2'><i class='text-primary'>For </i> $for_name</span></span>":"</span>";
                echo "<div class='row m-auto w-100'>
                <li class='list-group-item list-group-item-primary mb-4 m-auto'>
                    <ul class= 'list-group list-group-flush'>
                        <div class='row m-auto w-100'>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Quantity  -  </i>".$row['requested_quantity']." ".$row['unit']."
                            <button id='toreplace' data-bs-toggle='collapse' data-bs-target='#replace' role='button' aria-expanded='false' aria-controls='replace' type='button' class='
                            ";
                            echo ($rep && $row['to_replace']!=null)?"":"d-none ";
                            $uname =str_replace("."," ",$row['customer']);
                            echo "noPrint btn btn-sm btn-outline-$color_btn text-end'>To be Replaced</button></li>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Requested By : </i>$uname</li>
                        </div>
                        <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='replace'>
                                <li class='list-group-item list-group-item-light p-3'>
                                    <h5 class='text-center'>Items To Be Replaced</h5>
                                    <ul class= 'list-group list-group-flush'>";
                                    $i=0;
                                    if(isset($replacement))
                                    foreach($replacement as $rep_val)
                                    {
                                        $i++;
                                        echo " <li class='list-group-item list-group-item-light text-center border-0'><i class='text-primary'>Serial Number - $i :</i> $rep_val</li>";
                                        $sql = "SELECT * from `purchase history` where `Serial` = ? and item = ? AND company = ?";
                                        $stmt_history = $conn->prepare($sql);  
                                        $stmt_history->bind_param("sss", $rep_val, $row['item'], $_SESSION['company']);
                                        $stmt_history->execute();
                                        $result_history = $stmt_history->get_result();
                                        if($result_history->num_rows>0)
                                        while ($row_hist = $result_history->fetch_assoc()) {
                                            echo "
                                            <ul class= 'list-group list-group-flush border-0'> 
                                             <li class='list-group-item list-group-item-light text-center ms-2 border-0'><i class='text-primary'>
                                                Purchase Date - :</i> ".date("d-M-Y", strtotime($row_hist['date']))."
                                            </li>";
                                            if($row['item']== 'tyre' || $row['item']== 'battery' || $row['item']== 'inner tube' )
                                            {
                                                $km_difference = intval($row['current_km']) - intval($row_hist['kilometer']);
                                                $warn = ($km_difference < 50000)?"<i class='fas fa-exclamation-circle text-danger'></i>":"<i class='fas fa-check-circle text-success'></i>";
                                                echo " <li class='list-group-item list-group-item-light text-center ms-2'><i class='text-primary'>
                                                    Driven Km Since Purchase - :</i> ".$km_difference." Kilometer  $warn
                                                </li>";
                                            }
                                            else
                                            {
                                                $origin = new DateTime($row_hist['date']);
                                                $target = new DateTime($date);
                                                $interval = $origin->diff($target);
                                                $date_difference = intval($interval->format('%a'));
                                                $warn = ($date_difference<30)?"<i class='fas fa-exclamation-circle text-danger'></i>":"<i class='fas fa-check-circle text-success'></i>";
                                                echo " <li class='list-group-item list-group-item-light text-center ms-2'><i class='text-primary'>
                                                    Previous Purchase was Before - :</i> ".$date_difference." Days $warn
                                                </li>";
                                            }
                                            echo "</ul>";
                                        }
                                        else {
                                            echo " <li class='list-group-item list-group-item-light text-center ms-2'>
                                                ---- Not Found ----
                                            </li>";
                                        }
                                    }
                            echo "  </ul>
                                </li>
                        </div>
                        $plate_incase
                        <div class='row m-auto w-100'>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Requested  -  </i>". date("d-M-Y", strtotime($row['date_requested']))."</li>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Needed By  -  </i>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                        </div>
                        <div class='row m-auto w-100'>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Specifications : </i>$spec_str 
                            <button id='specs_show' data-bs-toggle='collapse' data-bs-target='#content' role='button' aria-expanded='false' aria-controls='content' type='button' class='
                            ";
                            echo (isset($spec_found))?"":"d-none ";
                            echo "noPrint btn btn-sm btn-outline-$color_btn'>View</button></li>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Requesting Department : </i>".$row['department']."</li>
                        </div>";
                        echo (isset($spec_found))?"<div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='content'>
                            <li class='list-group-item list-group-item-light border-0'><i class='text-dark fw-bold'>Date of Specification Given : </i>".date("d-M-Y", strtotime($spec_date))."</li>
                            <li class='list-group-item list-group-item-light border-0'><i class='text-dark fw-bold'>Given By : </i>$spec_giver</li>
                                <li class='list-group-item list-group-item-light p-3 text-break'>
                                    <h5 class='text-center'>Specification Details</h5>
                                    ".$spec."
                                </li>
                        </div>":"";
                        $green = (!is_null($row['stock_info']) && $outofstock==0)?"border border-3 border-success":"";
                        echo (is_null($row['stock_info']))?"":"
                        <div class='row m-auto w-100 $green'>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>In-Stock  -  </i>". $instock."<!--<span class='float-end'><i class='text-primary'>Total price  -  </i> $price_instock</span>--></li>
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Out-Of-Stock  -  </i>". $outofstock."</li>
                        </div>";
                        echo ($row['Remark']=='#')?"":"<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Remark  -  </i>".$row['Remark']."</li>";
                        echo ($row['description']=='#')?"":"<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Description  -  </i>".$row['description']."</li>";
                        echo ($row['description']=='#' && $row['Remark']=='#')?"<li class='list-group-item list-group-item-light'><i class='text-primary text-center'>No Description and Remark Given By ".$row['customer']."</i></li>":"";
                        if(isset($property_approval))
                        {
                            echo "<li class='list-group-item list-group-item-light'><i class='text-primary'>
                            Property Approaval  -  </i> ".$property_approval." 
                            <button id='toreplace' data-bs-toggle='collapse' data-bs-target='#prop_reason' role='button' aria-expanded='false' aria-controls='prop_reason' type='button' class='
                            ";
                            echo (isset($property_reason))?"":"d-none ";
                            echo "noPrint btn btn-sm btn-outline-$color_btn text-end'>Reason</button></li>
                            <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='prop_reason'>
                                <li class='list-group-item list-group-item-light p-3'>
                                    <h5 class='text-center'>Reason </h5>
                                    <ul class= 'list-group list-group-flush'>
                                        <li class='list-group-item list-group-item-light text-center border-0'>
                                            $property_reason
                                        </li>
                                    </ul>
                                </li>
                            </div>";
                        }

                        echo (isset($purchase_officer) && isset($collector))?"<div class='row m-auto w-100'>":"";
                        echo (!isset($purchase_officer))?"":"
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Proforma Gathered By  -  </i>". $purchase_officer."</li>";
                        echo (!isset($collector))?"":"
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Collector  -  </i>". $collector."</li>";
                        echo (isset($purchase_officer) && isset($collector))?"</div>":"";
                        echo (!is_null($row['manager_description']))?
                        "<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Manager Remark  -  </i>".$row['manager_description']."</li>":(($_SESSION["a_type"] == 'manager' && $_SESSION['department']==$row['department'] && $row['status'] == 'waiting')?
                        "<li class='list-group-item list-group-item-light text-center' id='man_rem_set'><i class='text-primary'><button data-bs-toggle='collapse' data-bs-target='#manager_desc' role='button' aria-expanded='false' aria-controls='manager_desc' type='button' class='btn btn-sm btn-outline-primary' id='close_man_rem'>Add Additional Information</button></i></li>
                        <div class='m-auto w-75 collapse p-3 my-3' id='manager_desc'>
                            <span class='w-100 text-danger' id='warnin'></span>
                            <textarea class='w-100' rows='2' id='manager_remark'></textarea>
                            <button type='button' class='btn btn-sm btn-primary form-control' id='desc_man_".$na_t."_".$row['request_id']."' onclick='desc_man(this)'>Add Remark</button>
                        </div>":"");
                        if(isset($cluster) && $cluster!='none')
                        {
                            echo "<div class='divider fw-bold'><div class='divider-text'>Purchasing Information</div></div>";
                        for($j=0;$j<$prices;$j++)
                        echo "
                            <div class='row m-auto w-100 mt-3'>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Providing Company  -  </i>". $providing_company[$j]."</li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Quantity Being Provided  -  </i>". $provided_quan[$j]."</li>
                            </div>
                            <div class='row m-auto w-100'>
                                <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>Unit Price  -  </i>". $prov_unit_price[$j]."</li>
                                <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>Total Price  -  </i>". $prov_total_price[$j]."</li>
                                <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>After Tax  -  </i>". $after_tax[$j]."</li>
                            </div>
                        ";
                        }
                echo "</ul>
                </li>
                </div>";
                }
            }
        }
        else
        {
            echo $go_home;
        }
        $conn->close();
        $conn_pms->close();
        $conn_fleet->close();
        $conn_ws->close();
        $conn_ais->close();
        $conn_sms->close();
        $conn_mrf->close();