<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    // include "../common/details.php";
    $rep = false;
    $no_property = false;
    $data = explode('_', $_GET['info']);
    $type = na_t_to_type($conn,$data[0]);
    $na_t = str_replace(' ', '', $type);
    $date = date('Y-m-d H:i:s');
    $spec = '';
    $spec_date = '';
    $spec_giver = '';
    $purchase_order_id='';
    $cluster='';
    $checked_by = '';
    $loader = '<i class="fa fa-spinner fa-pulse text-danger text-sm"></i>';
    $row_cluster = [];
    $sql = "SELECT * FROM catagory  WHERE catagory = ? AND replacements = 1";
    $stmt_specific_category = $conn -> prepare($sql);
    $stmt_specific_category -> bind_param("s", $type);
    $stmt_specific_category -> execute();
    $result_specific_category = $stmt_specific_category -> get_result();
    if ($result_specific_category -> num_rows > 0)
    {
        $rep = true;
    }
    $stmt_request -> bind_param("i", $data[1]);
    $stmt_request -> execute();
    $result = $stmt_request -> get_result();
    function createAlert($user,$title='the title'){ 
        $uname =str_replace("."," ",$user);
        return " <span data-bs-html='true' data-bs-trigger='focus' tabindex='0'  class='report'  data-bs-toggle='popover' title='$title' 
        data-bs-content='$uname'> <i class='fa fa-info-circle' title='Request Details'></i></span>";
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $stmt_company -> bind_param("s", $row["company"]);
            $stmt_company -> execute();
            $result_company = $stmt_company -> get_result();
            if($result_company->num_rows > 0)
            {
                while($r = $result_company->fetch_assoc()) 
                {
                    $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
                }
            }
            $has_gm = in_array($row['department'],$GMs) || in_array("All",$GMs);
            $sql_report = "SELECT * FROM report WHERE request_id = ?";
            $stmt_report = $conn->prepare($sql_report);
            $stmt_report -> bind_param("i", $data[1]);
            $stmt_report -> execute();
            $result_report = $stmt_report -> get_result();
            $row_report = $result_report->fetch_assoc();

            if(($type == 'Spare and Lubricant' && ($row['mode'] == 'External' || $row['type'] == 'Lubricant')) || ($type == 'Tyre and Battery' && ($row['mode'] == 'External')) || $type == 'Miscellaneous' || $type == 'Consumer Goods') {
                $no_property = true;
            }

            if(!is_null($row['stock_info'])) {
                $stmt_stock -> bind_param("i", $row['stock_info']);
                $stmt_stock -> execute();
                $result_stock = $stmt_stock -> get_result();
                if($result_stock -> num_rows > 0) {
                    while($r = $result_stock -> fetch_assoc()) {
                        $status_stock = $r['status'];
                        $instock = $r['in-stock'];
                        if($instock > 0) $no_property = false;
                        $outofstock = $r['for_purchase'];
                        $checked_by = str_replace("."," ",$r['check_by']);
                        $price_instock = $r['total_price'];
                        if ($r['remark'] != '' && !is_null($r['remark'])) {
                            $property_reason = $r['remark'];
                        }
                        if ($r['status'] == 'Approved' || $r['status'] == 'Rejected') {
                            $property_approval = $r['status'];
                        }
                    }
                    $sql_value_change_record = "SELECT * FROM recorded_time WHERE `for_id` = ? and `database_name`='reqeusts' and opperation LIKE 'Property Value Change%'";
                    $stmt_value_change_record = $conn->prepare($sql_value_change_record);
                    $stmt_value_change_record -> bind_param("i", $data[1]);
                    $stmt_value_change_record -> execute();
                    $result_value_change_record = $stmt_value_change_record -> get_result();
                    if($result_value_change_record->num_rows > 0)
                    {
                        if($instock + $outofstock != $row['requested_quantity'])
                            $property_change = $instock + $outofstock;
                    }
                }
            } else {
                $instock = null;
                $outofstock = null;
            }

            if ($rep && $row['to_replace'] != null && $row['to_replace'] != '') {// $rep &&
                $replacement = explode(',', $row['to_replace']);
            }
            $stmt_manager -> bind_param("ss", $row['department'], $row['company']);
            $stmt_manager -> execute();
            $result2 = $stmt_manager -> get_result();
            if ($result2 -> num_rows > 0) {
                while ($r = $result2 -> fetch_assoc()) {
                    $manager = $r['Username'];
                }
            }
            $spec_needed = ($row['spec_dep'] == 'IT' && $row['request_type'] != 'Spare and Lubricant');
            if (is_null($row['specification']) && $row['request_type'] != 'agreement') {
                if (is_null($row['spec_dep'])) {
                    $spec_str = 'Specification not Requested';
                } else {
                    $spec_str = 'Waiting for Specs from '.$row['spec_dep'];
                }
            } else {
                if($row['request_type'] == 'agreement')
                    $spec_str = 'Stock Balance Recieved from '.$row['department'];
                else
                    $spec_str = 'Spec Recieved from '.$row['spec_dep'];
                $spec_found = true;
                if($row['request_type'] == 'agreement')
                {
                    $sql_spec_with_request = "SELECT * FROM `specification` WHERE `request_id` = ?";
                    $stmt_spec_with_request = $conn->prepare($sql_spec_with_request);
                    $stmt_spec_with_request -> bind_param("i", $row['request_id']);
                    $stmt_spec_with_request -> execute();
                    $result_specification = $stmt_spec_with_request -> get_result();
                }
                else
                {
                    $stmt_specification -> bind_param("i", $row['specification']);
                    $stmt_specification -> execute();
                    $result_specification = $stmt_specification -> get_result();
                }
                if ($result_specification -> num_rows > 0) {
                    while ($r = $result_specification -> fetch_assoc()) {
                        $spec = $r['details'];
                        if (!is_null($r['pictures']) && $r['pictures'] != '') {
                            $spec .= "
                            <div class='row gallery noPrint'>
                            <h6 class='text-center'>Pictures/PDF</h6>";

                            $allfiles = explode(':_:', $r['pictures']);
                            foreach ($allfiles as $file) {
                                if (strpos($file, 'pdf')) {
                                    $spec .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                        </div>";
                                } else {
                                    $spec .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' >
                                                <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                            </a>
                                        </div>";
                                }
                            }
                            $spec .= '</div>';
                        }
                        $spec_date = $r['date'];
                        $spec_giver = $r['given_by'];
                    }
                }
            }
            $stmt_po_by_request -> bind_param("i", $data[1]);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if ($result_po_by_request -> num_rows > 0) {
                while ($r = $result_po_by_request -> fetch_assoc())
                {
                    $purchase_officer = (is_null($r['purchase_officer']))?"":str_replace("."," ",$r['purchase_officer']); 
                    $finance_sent=(is_null($r['finance_sent_by']))?"":str_replace("."," ",$r['finance_sent_by']);
                    $assigned_by=(is_null($r['assigned_by']))?"":str_replace("."," ",$r['assigned_by']);
                    $collector = (is_null($r['collector']))?"":str_replace("."," ",$r['collector']);
                    $cluster = (is_null($r['cluster_id'])) ? 'none' : $r['cluster_id']; 
                    $purchase_order_id = $r['purchase_order_id'];
                    $performa_id = $r['performa_id'];
                    $timestamp = $r['timestamp'];
                    $po_status = $r['status'];
                    $performa_opened =str_replace("."," ",$r['performa_opened']) ;
                }
            }
        
            if (isset($cluster) && $cluster != 'none') { 
                $prices = 0;
                $stmt_cluster -> bind_param("i", $cluster);
                $stmt_cluster -> execute();
                $result_cluster = $stmt_cluster -> get_result();
                if ($result_cluster->num_rows > 0) {
                    $row_cluster = $result_cluster->fetch_assoc();
                }
                $sql_pi_specific = "SELECT * FROM `price_information` WHERE `cluster_id` = ? and `purchase_order_id` = ? AND selected";
                $stmt_pi_specific = $conn->prepare($sql_pi_specific);
                $stmt_pi_specific -> bind_param("ii", $cluster, $purchase_order_id);
                $stmt_pi_specific -> execute();
                $result_pi_specific = $stmt_pi_specific -> get_result();
                if ($result_pi_specific -> num_rows > 0) {
                    while ($r = $result_pi_specific -> fetch_assoc())
                    {
                        $providing_company[$prices] = $r['providing_company'];
                        $provided_quan[$prices] = $r['quantity'];
                        $prov_unit_price[$prices] = $r['price'];
                        $prov_total_price[$prices] = $r['total_price'];
                        $after_tax[$prices] = $r['after_vat'];
                        ++$prices;
                    }
                }
            }
            $mode = '';
            $boq_incase = '';
            $color_btn = ($row['recieved'] == 'yes') ? 'success' : 'primary';
            if ($type == 'Consumer Goods') {
                if ($row['request_for'] == 0) {
                    if ($result_project -> num_rows > 0) {
                        while ($r = $result_project -> fetch_assoc()) {
                            $for_name = $r['Name'].' Project';
                        }
                    }
                } else {
                    $id = explode('|', $row['request_for'])[0];
                    $id_task = explode('|', $row['request_for'])[1];
                    $id_item = explode('|', $row['request_for'])[2];
                    $stmt_project_pms -> bind_param("i", $id);
                    $stmt_project_pms -> execute();
                    $result_project_pms = $stmt_project_pms -> get_result();
                    if ($result_project_pms -> num_rows > 0) {
                        while ($r = $result_project_pms -> fetch_assoc()) {
                            $sql_tasks = "SELECT * FROM task where `id` = ?";
                            $stmt_tasks = $conn_pms->prepare($sql_tasks);
                            $stmt_tasks -> bind_param("i", $id_task);
                            $stmt_tasks -> execute();
                            $result_tasks = $stmt_tasks -> get_result();
                            if ($result_tasks -> num_rows > 0) {
                                while ($row_task = $result_tasks -> fetch_assoc()) {
                                    $for_name = "<span class='text-info'>".$r['project_name']." Project </span><span class='text-danger'> | </span><span class='text-info'> ".$row_task['task_name'].' Task</span>';
                                }
                                $sql_item = "SELECT * FROM item where `id` = ?";
                                $stmt_item = $conn_pms->prepare($sql_item);
                                $stmt_item -> bind_param("i", $id_item);
                                $stmt_item -> execute();
                                $result_item = $stmt_item -> get_result();
                                if($result_item->num_rows>0)
                                {
                                    while($row_item = $result_item->fetch_assoc())
                                    {
                                        $boq_amt = $row_item['total_quantity']." ".$row_item['unit'];
                                    }
                                }
                                $boq_incase = "
    <li class='list-group-item list-group-item-light col-sm-12 col-md-12 text-center'><i class='text-primary'>Planned BOQ  -  </i>".$boq_amt."
    <button id='boq_btn' data-bs-toggle='collapse' data-bs-target='#boq_div' role='button' aria-expanded='false' aria-controls='boq_div' type='button' class='noPrint btn btn-sm btn-outline-$color_btn text-end'>View History</button></li>

                                <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn noPrint'  id='boq_div'>
                        <li class='list-group-item list-group-item-light p-3'>
                            <h5 class='text-center'>Previous Purchase History</h5>
                            <ul class= 'list-group list-group-flush' style = 'overflow:scroll'>";
                $sql_boq = "SELECT * FROM requests where request_for = ? AND request_id != ?";
                $stmt_boqs = $conn->prepare($sql_boq);
                $stmt_boqs -> bind_param("si", $row['request_for'], $row['request_id']);
                $stmt_boqs -> execute();
                $result_boqs = $stmt_boqs -> get_result();
                if ($result_boqs->num_rows > 0) {
                    $all_quan = 0;
                    $boq_incase .= "<table class='table table-striped mt-3' id='table1'>
                                        <thead class='table-primary'>
                                            <tr>
                                                <th>Quantity</th>
                                                <th>Date Requested</th>
                                                <th>Status</th>
                                                <th>Last Update</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                    while ($row_boq = $result_boqs->fetch_assoc()) {

                        $sql_rep = "SELECT * FROM report where request_id = ?";
                        $stmt_report = $conn->prepare($sql_rep);
                        $stmt_report -> bind_param("i", $row_boq['request_id']);
                        $stmt_report -> execute();
                        $result_report = $stmt_report -> get_result();
                        $row_rep = $result_report -> fetch_assoc();
                        $row_rep = array_reverse($row_rep);
                        foreach($row_rep as $r_key => $r_val)
                        {
                            if(!is_null($r_val))
                            {
                                $lastupdate = date('d-M-Y', strtotime($r_val));
                                break;
                            }
                        }
                        if(strpos($row_boq['status'],"eject") === false && $row_boq['status'] != 'canceled')
                            $all_quan += $row_boq['requested_quantity'];
                        $boq_incase .= "
                                        <tr id='row_".$row_boq['request_id']."'>
                                            <td class='text-capitalize' id='quantity_".$row_boq['request_id']."'>".$row_boq['requested_quantity']." ".$row_boq['unit']."</td>
                                            <td class='text-capitalize' id='drq_".$row_boq['request_id']."'>".date('d-M-Y', strtotime($row_boq['date_requested']))."</td>
                                            <td class='text-capitalize' id='status_".$row_boq['request_id']."'>".$row_boq['status']."</td>
                                            <td class='text-capitalize' id='_".$row_boq['request_id']."'>".$lastupdate."</td>
                                        </tr>";
                    }
                    $rem_quan = floatval($boq_amt) - floatval($all_quan) - floatval($row['requested_quantity']);
                    $boq_incase .= "</tbody>
                                    </table>
                                    <li class='list-group-item list-group-item-light col-sm-12 col-md-12 text-center'><i class='text-primary'>Remaining Amount after Current Purchase  -  </i>".$rem_quan."</li>
                                    ";
                } else {
                    $boq_incase .= " <li class='list-group-item list-group-item-light text-center ms-2'>---- Not Found ----</li>";
                }
                $boq_incase .= '  </ul>
                        </li>
                    </div>

                    ';
                            }
                        }
                    }
                }
            }
            if ($type == 'Spare and Lubricant') {
                $mode = "<span class='text-secondary small'>( Mode - $row[mode] / $row[type])</span>";
                if(strpos($row['request_for'],"None|")!==false)
                {
                    $for_name = (explode("|",$row['request_for'])[1] == 0 || explode("|",$row['request_for'])[1] == 1)?"<span class='text-danger'>No Job Number</span>":"<span class='text-info'>Job Number - </span><span class='text-info'>".explode("|",$row['request_for'])[1]."</span>";
                }
                else
                {
                    $for_name = 'Job Number - '.$row['request_for'];
                }
            }
            $plate_incase = '';
            if ($type == 'Tyre and Battery') {
                $mode = "<span class='text-secondary small'>( Mode - $row[mode] )</span>";
                $sql = 'SELECT driver FROM `vehicle` WHERE `plateno` =? ';

                $stmt2 = $conn_fleet->prepare($sql);
                $stmt2->bind_param('s', $row['request_for']);
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($driver);
                $stmt2->fetch();
                $stmt2->close();
                if (!isset($driver) || $driver == null) {
                    $driver = 'External Customer';
                }
                $plate_incase = "
                    <div class='row m-auto w-100'>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Plate Number  -  </i>".$row['request_for']."
                        <button id='hist_jacket' data-bs-toggle='collapse' data-bs-target='#jacket' role='button' aria-expanded='false' aria-controls='jacket' type='button' class='noPrint btn btn-sm btn-outline-$color_btn text-end'>History Jacket</button></li>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Driver  -  </i>".$driver."</li>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Registered kilometer  -  </i>".$row['current_km']."</li>
                    </div>
                    <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn noPrint'  id='jacket'>
                        <li class='list-group-item list-group-item-light p-3'>
                            <h5 class='text-center'>History Jacket</h5>
                            <ul class= 'list-group list-group-flush' style = 'overflow:scroll'>";
                $sql_histo = "SELECT * FROM history_jacket where vehicle = ?";
                $stmt_history_jacket = $conn->prepare($sql_histo);
                $stmt_history_jacket -> bind_param("s", $row['request_for']);
                $stmt_history_jacket -> execute();
                $result_history_jacket = $stmt_history_jacket -> get_result();
                if ($result_history_jacket->num_rows > 0) {
                    $plate_incase .= "<table class='table table-striped mt-3' id='table1'>
                                        <thead class='table-primary'>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Serial Number</th>
                                                <th>Date Purchased</th>
                                                <th>Kilometer</th>
                                                <th>Description</th>
                                                <th>Kilometer Difference</th>
                                                <th>Time Difference</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                    while ($row_histo = $result_history_jacket->fetch_assoc()) {
                        $plate_incase .= "
                                                    <tr id='row_".$row_histo['id']."'>
                                                        <td class='text-capitalize' id='Item_".$row_histo['id']."'>".$row_histo['item']."</td>
                                                        <td class='text-capitalize' id='quantity_".$row_histo['id']."'>".$row_histo['quantity']."</td>
                                                        <td class='text-capitalize' id='Serial_".$row_histo['id']."'>".$row_histo['serial']."</td>
                                                        <td class='text-capitalize' id='DatePurchased_".$row_histo['id']."'>".date('d-M-Y', strtotime($row_histo['date_purchased']))."</td>
                                                        <td class='text-capitalize' id='Kilometer_".$row_histo['id']."'>".$row_histo['kilometer']."</td>
                                                        <td class='text-capitalize' id='description_".$row_histo['id']."'>".$row_histo['description']."</td>
                                                        <td class='text-capitalize' id='kmdiff_".$row_histo['id']."'>".$row_histo['km_diff']."</td>
                                                        <td class='text-capitalize' id='timediff_".$row_histo['id']."'>".$row_histo['time_diff']."</td>
                                                    </tr>";
                    }
                    $plate_incase .= '</tbody>
                                    </table>';
                } else {
                    $plate_incase .= " <li class='list-group-item list-group-item-light text-center ms-2'>---- Not Found ----</li>";
                }
                $plate_incase .= '  </ul>
                        </li>
                    </div>

                    ';
            }
            $space = 6;
            $new_value = "";
            if(isset($property_change))
            {
                $space = 4;
                $new_value = "<li class='list-group-item list-group-item-light col-sm-12 col-md-$space'><i class='text-primary'>Property Approved Quantity : </i>".$property_change.' '.$row['unit']."</li>";
            }
            $avail = true;
            $btn_close = "";
            $forbiden_stats = ['canceled','Rejected','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            foreach($forbiden_stats as $s)
                if(strpos($row['status'],$s)!==false || $row['status'] == $s) $avail = false;
            if(((($_SESSION['company'] == $row['procurement_company'] || $_SESSION['company'] == 'Hagbes HQ.') && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail)
            $btn_close = "
            <div class='text-end'>
                <form method='GET' action='".$_GET['pos_temp']."../requests/allphp.php'>
                    <button class='btn btn-outline-danger btn-sm' name='close_req' value='$row[request_id]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"".$_GET['pos_temp']."../requests\",\"remove\",\"Red\")'>Close Request</button>
                </form>
            </div>";
            echo "
            $btn_close
            <ul class= 'list-group list-group-flush'>
            <div class='text-center'>
                <!--<button value='requests_".$row['request_id']."' type='submit' name='issue' class='btn btn-sm btn-danger float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                    <i class='fas fa-reply'></i>
                </button>-->";
            echo "<h4 class='text-capitalize mb-2 text-center text-primary'> ".$row['company']."</h4>";
            echo "<h5 class='text-capitalize mb-2 text-center'>Item - ".$row['item']." $mode</h5>
                    <span class='text-capitalize text-secondary mb-2 text-center'><i class='text-primary'>Catagory </i>- $type";
            echo (isset($for_name)) ? "<span class='text-capitalize text-secondary ms-2'><i class='text-primary'>For </i> $for_name</span></span>" : '</span>';
            echo "
            </div>
            <div class='row m-auto w-100'>
                    <li class='list-group-item list-group-item-primary mb-4 m-auto'>
                        <ul class= 'list-group list-group-flush'>
                            <div class='row m-auto w-100'>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-$space'><i class='text-primary'>Quantity  -  </i>".$row['requested_quantity'].' '.$row['unit']."
                                <button id='toreplace' data-bs-toggle='collapse' data-bs-target='#replace' role='button' aria-expanded='false' aria-controls='replace' type='button' class='
                                ";
            echo ($rep && $row['to_replace'] != null && $row['to_replace'] != '') ? '' : 'd-none '; // $rep &&
            echo "noPrint btn btn-sm btn-outline-$color_btn text-end'>To be Replaced</button></li>$new_value
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-$space'><i class='text-primary'>Requested By : </i>".str_replace("."," ",$row['customer'])."</li>
                            </div>
                            <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='replace'>
                                    <li class='list-group-item list-group-item-light p-3'>
                                        <h5 class='text-center'>Items To Be Replaced</h5>
                                        <ul class= 'list-group list-group-flush'>";
            $i = 0;
            if (isset($replacement)) {
                foreach ($replacement as $rep_val) {
                    ++$i;
                    echo " <li class='list-group-item list-group-item-light text-center border-0'><i class='text-primary'>Serial Number - $i :</i> $rep_val</li>";
                    // $sql_hist = "SELECT * from `purchase history` where `Serial` = '$rep_val' and item = '".$row['item']."' AND company = '".$_SESSION['company']."'";
                    $rep_value = trim($rep_val);
                    if ($type == 'Tyre and Battery') 
                    {
                        $serial_like = "%$rep_value%";
                        $sql_hist = "SELECT * from `history_jacket` where `serial` LIKE ? and item = ? AND vehicle = ?";
                        $stmt_history_jacket_serial = $conn->prepare($sql_hist);
                        $stmt_history_jacket_serial -> bind_param("sss", $serial_like, $row['item'], $row['request_for']);
                        $stmt_history_jacket_serial -> execute();
                        $result_history = $stmt_history_jacket_serial -> get_result();
                    }
                    else 
                    {
                        $sql_hist = "SELECT * from `purchase history` where `Serial` = ? and item = ? AND company = ?";
                        $stmt_purchase_history = $conn->prepare($sql_hist);
                        $stmt_purchase_history -> bind_param("sss", $rep_value, $row['item'], $_SESSION['company']);
                        $stmt_purchase_history -> execute();
                        $result_history = $stmt_purchase_history -> get_result();
                    }
                    if ($result_history->num_rows > 0) {
                        while ($row_hist = $result_history->fetch_assoc()) {
                            $date = ($type == 'Tyre and Battery') ? $row_hist['date_purchased'] : $row_hist['date'];
                            echo "
                                                    <ul class= 'list-group list-group-flush border-0'> 
                                                    <li class='list-group-item list-group-item-light text-center ms-2 border-0'><i class='text-primary'>
                                                        Purchase Date - :</i> ".date('d-M-Y', strtotime($date)).'
                                                    </li>';
                            if ($type == 'Tyre and Battery') {
                                $km_difference = intval($row['current_km']) - intval($row_hist['kilometer']);
                                $warn = ($km_difference < 50000) ? "<i class='fas fa-exclamation-circle text-danger'></i>" : "<i class='fas fa-check-circle text-success'></i>";
                                echo " 
                                                        <li class='list-group-item list-group-item-light text-center ms-2 border-0'><i class='text-primary'>
                                                            Km at Purchase - :</i> ".$row_hist['kilometer']." Kilometer
                                                        </li> 
                                                        <li class='list-group-item list-group-item-light text-center ms-2'><i class='text-primary'>
                                                            Driven Km Since Purchase - :</i> ".$km_difference." Kilometer  $warn
                                                        </li>";
                            } else {
                                $origin = new DateTime($row_hist['date']);
                                $target = new DateTime($date);
                                $interval = $origin->diff($target);
                                $date_difference = intval($interval->format('%a'));
                                $warn = ($date_difference < 30) ? "<i class='fas fa-exclamation-circle text-danger'></i>" : "<i class='fas fa-check-circle text-success'></i>";
                                echo "
                                                        <li class='list-group-item list-group-item-light text-center ms-2 border-0'><i class='text-primary'>
                                                            Amount - :</i> ".$row_hist['amount'].'
                                                        </li>';
                                echo " <li class='list-group-item list-group-item-light text-center ms-2'><i class='text-primary'>
                                                            Previous Purchase was Before - :</i> ".$date_difference." Days $warn
                                                        </li>";
                            }
                            echo '</ul>';
                        }
                    } else {
                        echo " <li class='list-group-item list-group-item-light text-center ms-2'>
                                                        ---- Not Found ----
                                                    </li>";
                    }
                }
            }
            echo "  </ul>
                                    </li>
                            </div>
                            $plate_incase
                            $boq_incase
                            <div class='row m-auto w-100'>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Requested  -  </i>".date('d-M-Y', strtotime($row['date_requested']))."</li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Needed By  -  </i>".date('d-M-Y', strtotime($row['date_needed_by'])).'</li>
                            </div>';
            echo "
                            <div class='row m-auto w-100'>";
            echo (!isset($spec_found)) ? '' : "
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>".(($row['request_type'] == 'agreement')?"Stock Balance":"Specifications")." : </i>$spec_str 
                                <button id='specs_show' data-bs-toggle='collapse' data-bs-target='#content' role='button' aria-expanded='false' aria-controls='content' type='button' class='
                                ".((isset($spec_found)) ? '' : 'd-none ')."noPrint btn btn-sm btn-outline-$color_btn'>View</button></li>";
            echo "
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Requesting Department : </i>".$row['department'].'</li>
                            </div>';
            echo (isset($spec_found)) ? "<div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='content'>
                                <li class='list-group-item list-group-item-light border-0'><i class='text-dark fw-bold'>Date of Specification Given : </i>".date('d-M-Y', strtotime($spec_date))."</li>
                                <li class='list-group-item list-group-item-light border-0'><i class='text-dark fw-bold'>Given By : </i>$spec_giver</li>
                                    <li class='list-group-item list-group-item-light p-3 text-break'>
                                        <h5 class='text-center'>Specification Details</h5>
                                        ".$spec.'
                                    </li>
                            </div>' : '';
            $green = (!is_null($row['stock_info']) && isset($outofstock) && $outofstock == 0) ? 'border border-3 border-success' : '';
            echo (is_null($row['stock_info'])) ? '' : "
                            <div class='row m-auto w-100 $green'>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>In-Stock  -  </i>".$instock.' '.$row['unit']."<!--<span class='float-end'><i class='text-primary'>Total price  -  </i> $price_instock</span>--></li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Out-Of-Stock  -  </i>".$outofstock.' '.$row['unit']."</li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Stock Checked By  -  </i>".$checked_by.'</li>
                            </div>';
            echo ($row['Remark'] == '#') ? '' : "<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Remark  -  </i>".$row['Remark'].'</li>';
            echo ($row['description'] == '#') ? '' : "<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Description  -  </i>".$row['description'].'</li>';
            echo ($row['description'] == '#' && $row['Remark'] == '#') ? "<li class='list-group-item list-group-item-light'><i class='text-primary text-center'>No Description and Remark Given By ".$row['customer'].'</i></li>' : '';
            if (isset($property_approval)) {
                echo "<li class='".((isset($property_reason)) ? '' : 'd-none ')."list-group-item list-group-item-light'><i class='text-primary'>
                                Property Approaval  -  </i> ".$property_approval." 
                                <button id='toreplace' data-bs-toggle='collapse' data-bs-target='#prop_reason' role='button' aria-expanded='false' aria-controls='prop_reason' type='button' class='
                                ";
                echo (isset($property_reason)) ? '' : 'd-none ';
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

            echo (isset($purchase_officer) && isset($collector)) ? "<div class='row m-auto w-100'>" : '';
            echo (!isset($purchase_officer)) ? '' : "
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Proforma Gathered By  -  </i>".$purchase_officer.'</li>';
            echo (!isset($collector)) ? '' : "
                            <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Collector  -  </i>".$collector.'</li>';
            echo (isset($purchase_officer) && isset($collector)) ? '</div>' : '';
            if (!is_null($row_report['collection_date']) || !is_null($row_report['handover_comfirmed'])) {
                echo "<div class='row m-auto w-100'>";
                echo (!is_null($row_report['collection_date'])) ? '' : "
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Item Colleted Date  -  </i>".$row_report['collection_date'].'</li>';
                echo (!is_null($row_report['handover_comfirmed'])) ? '' : "
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Store Accepted Date  -  </i>".$row_report['handover_comfirmed'].'</li>';
                echo '</div>';
            }
            // echo (!is_null($row['manager_description'])) ?
            // "<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Manager Remark  -  </i>".$row['manager_description'].'</li>' : (($_SESSION['a_type'] == 'manager' && $_SESSION['department'] == $row['department'] && $row['status'] == 'waiting') ?
            // "<li class='list-group-item list-group-item-light text-center' id='man_rem_set'><i class='text-primary'><button data-bs-toggle='collapse' data-bs-target='#manager_desc' role='button' aria-expanded='false' aria-controls='manager_desc' type='button' class='btn btn-sm btn-outline-primary' id='close_man_rem'>Add Additional Information</button></i></li>
            //                 <div class='m-auto w-75 collapse p-3 my-3' id='manager_desc'>
            //                     <span class='w-100 text-danger' id='warnin'></span>
            //                     <textarea class='w-100' rows='2' id='manager_remark'></textarea>
            //                     <button type='button' class='btn btn-sm btn-primary form-control' id='desc_man_".$na_t.'_'.$row['request_id']."' onclick='desc_man(this)'>Add Remark</button>
            //                 </div>" : '');
            if (isset($cluster) && $cluster != 'none') {
                echo "<div class='divider fw-bold'><div class='divider-text'>Purchasing Information</div></div>";
                for ($j = 0; $j < $prices; ++$j) {
                    echo "
                                <div class='row m-auto w-100 mt-3'>
                                    <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Providing Company  -  </i>".$providing_company[$j]."</li>
                                    <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Quantity Being Provided  -  </i>".$provided_quan[$j]."</li>
                                </div>
                                <div class='row m-auto w-100'>
                                    <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>Unit Price  -  </i>".number_format($prov_unit_price[$j], 2, '.', ',')."</li>
                                    <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>Total Price  -  </i>".number_format($prov_total_price[$j], 2, '.', ',')."</li>
                                    <li class='list-group-item list-group-item-light col-4'><i class='text-primary'>After Tax  -  </i>".number_format($after_tax[$j], 2, '.', ',').'</li>
                                </div>';
                }
            }
            if (isset($performa_id) && !is_null($performa_id) && (strpos($_SESSION['a_type'], 'Committee') !== false || $_SESSION['department'] == 'Owner' || $_SESSION['department'] == 'Procurement')) {
                echo "<li class='list-group-item list-group-item-light text-break'><i class='text-primary'>Proforma Gathered And Uploaded  -  </i>
                            <button onclick='view_performa(this,\"item_details\")' id='$performa_id' name='$purchase_order_id' type='button' class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#view_performa'>
                                View
                            </button>
                        </li>";
            }
            echo "<div class='row m-auto w-100'>
            <li class='list-group-item list-group-item-light col-sm-12 text-center border-0'><i class='text-danger fw-bold'>Handling Companies</i></li>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-6 col-lg-4'><i class='text-primary'>Property (Store) at  -  </i>".$row['property_company']."</li>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-6 col-lg-4'><i class='text-primary'>Procurement at  -  </i>".$row['procurement_company']."</li>
                        <li class='list-group-item list-group-item-light col-sm-12 col-md-6 col-lg-4'><i class='text-primary'>Finance at  -  </i>".$row['finance_company']."</li>
                    </div>";
                        $sql_remark = "SELECT * FROM remarks where request_id = ?";
                        $stmt_remarks = $conn->prepare($sql_remark);
                        $stmt_remarks -> bind_param("i", $row['request_id']);
                        $stmt_remarks -> execute();
                        $result_remarks = $stmt_remarks -> get_result();
                        if ($result_remarks -> num_rows > 0) {
                            echo "<div class='row m-auto w-100 my-3'>
                            <li class='list-group-item list-group-item-light col-sm-12 text-center border-0'><i class='text-danger fw-bold'>Remarks</i></li>
                                        ";
                            while ($row_remark = $result_remarks -> fetch_assoc()) {
                                $date_remark = (isset($row_remark["timestamp"]))?date('d-M-Y', strtotime($row_remark["timestamp"])):"";
                                echo "
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6 col-lg-4 mt-2'>
                                By <i class='text-primary'>$row_remark[level] - $row_remark[user]</i>
                                <button class='btn btn-outline-primary btn-sm float-end' type='button' id='btnremark-$row_remark[id]' data-bs-toggle='collapse' data-bs-target='#remark-$row_remark[id]' role='button' aria-expanded='false' aria-controls='remark-$row_remark[id]'>Reason</button>
                                </li>
                                <div class='m-auto w-100 collapse p-3 my-3 alert-$color_btn' id='remark-$row_remark[id]'>
                                ".((isset($row_remark["timestamp"]))?"<li class='list-group-item list-group-item-light border-0'><i class='text-dark fw-bold'>Date of Remark : </i>".$date_remark."</li>":"").
                                    "<li class='list-group-item list-group-item-light p-3 text-break'>
                                        ".$row_remark["remark"]."
                                    </li>
                                </div>
                                ";
                            }
                            echo "</div>";
                        }
            $colorrr = (strpos($row['status'], 'eject') !== false) ? 'danger' : 'success';
            $status = ($row['status'] == 'Approved By Dep.Manager') ? 'Approved By '.str_replace("."," ",$row['manager']).'' :
            (($row['status'] == 'Approved By GM') ? 'Approved By '.str_replace("."," ",$row['GM']).'' :
            (($row['status'] == 'Approved By Director') ? 'Approved By '.str_replace("."," ",$row['director']).'' :
            (($row['status'] == 'Approved By Owner') ? 'Approved By '.str_replace("."," ",$row['owner']).'' :
            (($row['status'] == 'Approved By Property') ? 'Approved By '.str_replace("."," ",$row['property']).'' :
            $row['status']))));
            if (isset($po_status)) {
                $status = ($po_status == 'pending') ? "Purchase Officer Assigned ($purchase_officer)" :
                (($po_status == 'Accepted') ? "Gathering Performa ($purchase_officer)" :
                (($po_status == 'Complete') ? "Performa Gathered($purchase_officer)" :
                (($po_status == 'Performa Comfirmed') ? "Performa Opened($performa_opened)" :
                $status)));
            }
    /////////////////////next step////////////////
    // Users List for Next Approval 
    $department_manager =''; $director_GM='';$next='';$Store_checks='';$Owners='';$sepc_manager='';$proc_manager='';$sinour_po='';$po_officer='';$po_collector='';
    $property_mgr='';$HO_Comm=''; $finance_staff=''; $disbursment='';$fin_mgr='';$disbursment1='';$cashier='';  $cheque_signatorys=''; $disbursment_dep=''; $BR_Comm='';
    $report_sql=""; $directors=""; $spec_provider = '';
    $next_time="";
    
    $AccountStatment="SELECT Username from account WHERE status='active' and ";
    $stmnt=$AccountStatment."company = ? and (type LIKE 'manager%' OR role='director' OR role='GM') and department = ?";
        $stmt_manager_fetch = $conn->prepare($stmnt);
        $stmt_manager_fetch -> bind_param("ss", $row['company'], $row['department']);
        $stmt_manager_fetch -> execute();
        $result_manager_fetch = $stmt_manager_fetch -> get_result();
        if ($result_manager_fetch->num_rows > 0) {
            while ($rr = $result_manager_fetch->fetch_assoc()) {
                $department_manager=str_replace("."," ",$rr['Username']);
            }
        }
        if($row['department'] == 'Owner')$department_manager=str_replace("."," ",$row['manager']);
        $stmnt=$AccountStatment." type LIKE 'manager%'   and department='IT'";
        $stmt_IT_manager = $conn->prepare($stmnt);
        // $stmt_IT_manager -> bind_param("ss", $row['company'], $row['department']);
        $stmt_IT_manager -> execute();
        $result_IT_manager = $stmt_IT_manager -> get_result();
        if ($result_IT_manager->num_rows > 0) {
            while ($rr = $result_IT_manager->fetch_assoc()) {
                $sepc_manager=str_replace("."," ",$rr['Username']);
            }
        }
        
        $stmnt=$AccountStatment." company = ? and department='Property' and role='store'";
        $stmt_store_man = $conn->prepare($stmnt);
        $stmt_store_man -> bind_param("s", $row['property_company']);
        $stmt_store_man -> execute();
        $result_store_man = $stmt_store_man -> get_result();
        if ($result_store_man ->num_rows > 0) {
            while ($rr = $result_store_man ->fetch_assoc()) {
                $Store_checks.=str_replace("."," ",$rr['Username']).',';
            }
        }
        $stmnt=$AccountStatment." company='Hagbes HQ.' and role='director'";
        $stmt_directors = $conn->prepare($stmnt);
        // $stmt_directors -> bind_param("s", $row['property_company']);
        $stmt_directors -> execute();
        $result_directors = $stmt_directors -> get_result();
        if ($result_directors ->num_rows > 0) {
            while ($rr = $result_directors ->fetch_assoc()) {
                $directors.=str_replace("."," ",$rr['Username']).',';
            }
            $directors=rtrim($directors,',');
        }

        $stmnt=$AccountStatment." company='Hagbes HQ.' and role='manager' and department = 'IT'";
        $stmt_spec_provider = $conn->prepare($stmnt);
        // $stmt_spec_provider -> bind_param("s", $row['property_company']);
        $stmt_spec_provider -> execute();
        $result_spec_provider = $stmt_spec_provider -> get_result();
        if ($result_spec_provider ->num_rows > 0) {
            while ($rr = $result_spec_provider ->fetch_assoc()) {
                $spec_provider = str_replace("."," ",$rr['Username']).',';
            }
        }
        $like_department = "%".$row['department']."%";
        $stmnt=$AccountStatment." company = ? and (managing LIKE '%All%' or managing LIKE ?) and role = 'GM'";
        $stmt_GM = $conn->prepare($stmnt);
        $stmt_GM -> bind_param("ss", $row['company'], $like_department);
        $stmt_GM -> execute();
        $result_GM = $stmt_GM -> get_result();
        if ($result_GM ->num_rows > 0) {
            while ($rr = $result_GM ->fetch_assoc()) {
                $GM_approval=str_replace("."," ",$rr['Username']);
            }
        }

        $stmnt=$AccountStatment." company = ? and (managing LIKE '%All%' or managing LIKE ?) and role = 'Director'";
        $stmt_director = $conn->prepare($stmnt);
        $stmt_director -> bind_param("ss", $row['company'], $like_department);
        $stmt_director -> execute();
        $result_director = $stmt_director -> get_result();
        if ($result_director ->num_rows > 0) {
            while ($rr = $result_director ->fetch_assoc()) {
                $director_GM=str_replace("."," ",$rr['Username']);
            }
        }
        $stmnt=$AccountStatment." role='Owner'";
        $stmt_Owner = $conn->prepare($stmnt);
        // $stmt_Owner -> bind_param("ss", $row['company'], $like_department);
        $stmt_Owner -> execute();
        $result_Owner = $stmt_Owner -> get_result();
        if ($result_Owner ->num_rows > 0) {
            while ($rr = $result_Owner ->fetch_assoc()) {
                $Owners.=str_replace("."," ",$rr['Username']).',';
            }
        }

        $stmnt=$AccountStatment." company = ? and  department='Property' and type='manager'";
        $stmt_property_manager = $conn->prepare($stmnt);
        $stmt_property_manager -> bind_param("s", $row['property_company']);
        $stmt_property_manager -> execute();
        $result_property_manager = $stmt_property_manager -> get_result();
        if ($result_property_manager ->num_rows > 0) {
            while ($rr = $result_property_manager ->fetch_assoc()) {
                $property_mgr=str_replace("."," ",$rr['Username']);
            }
        }
    
        $stmnt=$AccountStatment." company = ? and ((department='procurement' and role='manager') or additional_role='1')";
        $stmt_procurement_manager = $conn->prepare($stmnt);
        $stmt_procurement_manager -> bind_param("s", $row['procurement_company']);
        $stmt_procurement_manager -> execute();
        $result_procurement_manager = $stmt_procurement_manager -> get_result();
        if ($result_procurement_manager ->num_rows > 0) {
            while ($rr = $result_procurement_manager ->fetch_assoc()) {
                $proc_manager=str_replace("."," ",$rr['Username']);
            }
        }
        $stmnt=$AccountStatment." company = ? and department='procurement' and role='Senior Purchase officer'";
        $stmt_procurement_spo = $conn->prepare($stmnt);
        $stmt_procurement_spo -> bind_param("s", $row['procurement_company']);
        $stmt_procurement_spo -> execute();
        $result_procurement_spo = $stmt_procurement_spo -> get_result();
        if ($result_procurement_spo ->num_rows > 0) {
            while ($rr = $result_procurement_spo ->fetch_assoc()) {
                $sinour_po.=str_replace("."," ",$rr['Username']).',';
            }
        }

    
        $stmnt=$AccountStatment." company = ? and  role='Disbursement'";
        $stmt_Disbursement = $conn->prepare($stmnt);
        $stmt_Disbursement -> bind_param("s", $row['finance_company']);
        $stmt_Disbursement -> execute();
        $result_Disbursement = $stmt_Disbursement -> get_result();
        if ($result_Disbursement ->num_rows > 0) {
            while ($rr = $result_Disbursement ->fetch_assoc()) {
                    $disbursment_dep=str_replace("."," ",$rr['Username']);
                    $disbursment='Review By '.str_replace("."," ",$rr['Username']);
                }
        }
        $stmnt=$AccountStatment." company = ? and  department='Disbursement' and (role = 'manager' OR `type` LIke '%manager%')";
        $stmt_Disbursement_manager = $conn->prepare($stmnt);
        $stmt_Disbursement_manager -> bind_param("s", $row['finance_company']);
        $stmt_Disbursement_manager -> execute();
        $result_Disbursement_manager = $stmt_Disbursement_manager -> get_result();
        if ($result_Disbursement_manager ->num_rows > 0) {
            while ($rr = $result_Disbursement_manager ->fetch_assoc()) {
                $disbursment1='Approve Petty Cash By '.str_replace("."," ",$rr['Username']);
            }
        }
        $stmnt=$AccountStatment." company = ? and  department='Finance' and type='manager'";
        $stmt_finance_manager = $conn->prepare($stmnt);
        $stmt_finance_manager -> bind_param("s", $row['finance_company']);
        $stmt_finance_manager -> execute();
        $result_finance_manager = $stmt_finance_manager -> get_result();
        if ($result_finance_manager ->num_rows > 0) {
            while ($rr = $result_finance_manager ->fetch_assoc()) {
                $fin_mgr='Approval By '.str_replace("."," ",$rr['Username']);
            }
        }
        $stmnt=$AccountStatment." company = ? and  department='Finance'";
        $stmt_finance_staff = $conn->prepare($stmnt);
        $stmt_finance_staff -> bind_param("s", $row['finance_company']);
        $stmt_finance_staff -> execute();
        $result_finance_staff = $stmt_finance_staff -> get_result();
        if ($result_finance_staff ->num_rows > 0) {
            while ($rr = $result_finance_staff ->fetch_assoc()) {
                $finance_staff.=str_replace("."," ",$rr['Username']).',';
            }
        }
        if (isset($cluster) && $cluster != ''&&$cluster != 'none')
        {
            $stmt=$AccountStatment." type LIKE '%Cheque Signatory%' and company = (SELECT cheque_company from cheque_info WHERE cluster_id = ? LIMIT 1) ";
            $stmt_cheque_signatory = $conn->prepare($stmnt);
            $stmt_cheque_signatory -> bind_param("i", $cluster);
            $stmt_cheque_signatory -> execute();
            $result_cheque_signatory = $stmt_cheque_signatory -> get_result();
            if ($result_cheque_signatory ->num_rows > 0) {
                while ($rr = $result_cheque_signatory ->fetch_assoc()) {
                    $cheque_signatorys.=str_replace("."," ",$rr['Username']).',';
                }
            }
        }

        $stmnt=$AccountStatment." role = 'cashier' and company = ?";
        $stmt_finance_cheque = $conn->prepare($stmnt);
        $stmt_finance_cheque -> bind_param("s", $row['finance_company']);
        $stmt_finance_cheque -> execute();
        $result_finance_cheque = $stmt_finance_cheque -> get_result();
        if ($result_finance_cheque ->num_rows > 0) {
            while ($rr = $result_finance_cheque ->fetch_assoc()) {
                $cashier.=str_replace("."," ",$rr['Username']).',';
            }
        }
    
        $stmnt=$AccountStatment." type like '%HO Committee%';";
        $stmt_ho_committee = $conn->prepare($stmnt);
        // $stmt_ho_committee -> bind_param("s", $row['finance_company']);
        $stmt_ho_committee -> execute();
        $result_ho_committee = $stmt_ho_committee -> get_result();
        if ($result_ho_committee ->num_rows > 0) {
            while ($rr = $result_ho_committee ->fetch_assoc()) {
                $HO_Comm.=str_replace("."," ",$rr['Username']).',';
            }
        }
        $stmnt=$AccountStatment." type LIKE '%Branch Committee%' and company = ?";
        $stmt_branch_committee = $conn->prepare($stmnt);
        $stmt_branch_committee -> bind_param("s", $row['company']);
        $stmt_branch_committee -> execute();
        $result_branch_committee = $stmt_branch_committee -> get_result();
        if ($result_branch_committee ->num_rows > 0) {
            while ($rr = $result_branch_committee ->fetch_assoc()) {
                $BR_Comm.=str_replace("."," ",$rr['Username']).',';
            }
        }


    if($row['next_step'] =='Property' || $row['status'] == 'Approved By Owner'||$row['status'] == 'Approved By GM'){
    $next=$property_mgr;
    $date=$row_report['Director_approval_date'];
    if($row['next_step']=='directors')
    $next=$directors;
    if($row['status'] == 'Approved By GM')
    $next_time=date('d/m/y H:i', strtotime($date));
    else if ($row['status'] == 'Found In Stock')
        $next_time=date('d/m/y H:i', strtotime($row_report['stock_check_date']));
    else
    $next_time=date('d/m/y H:i', strtotime($row_report['Owner_approval_date']));
    }

    if($row['next_step'] == 'Owner'){
        $next=$Owners;
        $date = ($row['status'] == 'Approved By Director')?$row_report['Director_approval_date']:$row_report['property_approval_date'];
        $next_time = date('d/m/y H:i',strtotime($date));
    }
    if($row['status']=='directors'){
        $next=$proc_manager; 
        $next_time=date('d/m/y H:i', strtotime($row_report['Directors_approval_date']));
    }
    if($row['next_step']=='Owner Committee'){
    $next=$Owners.','.$department_manager;
    $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_committee_date']));
    }
    if($row['next_step']=='Department Approval'){
    $next=$department_manager;
    $next_time=date('d/m/y H:i',strtotime($row_report['property_approval_date']));
    }
    if($row['next_step'] =='IT Specification') 
    {
        $next=$spec_provider;
        $date = ($row['status'] == 'Approved By GM')?$row_report['GM_approval_date']:$row_report['manager_approval_date'];
        $next_time = date('d/m/y H:i',strtotime($date));
    }
    
    if(isset($row_cluster['status']) && ($row_cluster['status']=='Pending' || $row_cluster['status']=='Updated')){
        $next='Send for Approval By '.$proc_manager;
        $next_time=date('d/m/y H:i',strtotime($row_report['compsheet_generated_date']));
    }
    else 
    { 
        if($row['next_step']=='procurement Committee'){
        $next=$proc_manager.",".$department_manager;
        $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_committee_date']));
        }
        if($row['next_step']=='Owner Committee'){
        $next=$Owners.$department_manager.','.$proc_manager;
        if($row['spec_dep'] == "IT")
            $next.=','.$spec_provider;
            $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_committee_date']));
        }
        if($row['next_step']=='HO Committee'){ 
        $next=$HO_Comm.$department_manager;
        $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_committee_date']));
        if($type=='agreement')
        $next_time=date('d/m/y H:i',strtotime($row_report['compsheet_generated_date']));
        if($row['company'] != "Hagbes HQ.")
            $next.=','.$GM_approval;
        }
        if($row['next_step']=='Branch Committee'){ 
            $next=$BR_Comm.((strpos($BR_Comm,$department_manager) === False)?$department_manager:"");
            $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_committee_date']));
            if($row['company'] != "Hagbes HQ.")
                $next.=','.$GM_approval;
        } 
    }
    $stmt_po_by_request -> bind_param("i", $row['request_id']);
    $stmt_po_by_request -> execute();
    $result_po_by_request = $stmt_po_by_request -> get_result();
    if ($result_po_by_request -> num_rows > 0) {
        while ($po_res = $result_po_by_request -> fetch_assoc()) {
    $po_officer=(is_null($po_res['purchase_officer']))?"":str_replace("."," ",$po_res['purchase_officer']);
    $po_collector=(is_null($po_res['collector']))?"":str_replace("."," ",$po_res['collector']);

    if($row['next_step'] == 'Performa' && $row['status']=='Generating Quote'){

            if($po_res['status']=='pending'){
            $next='Start processing By '.$po_officer;
            $next_time=date('d/m/y H:i',strtotime($row_report['officer_assigned_date']));
            }
            if($po_res['status']=='Accepted'){
            $next='Complete Proforma By '.$po_officer;
            $next_time=date('d/m/y H:i',strtotime($row_report['officer_acceptance_date']));
            }
            if($po_res['status']=='Complete'){
            $next='Proforma Opened By '.$proc_manager; 
            $next_time=date('d/m/y H:i',strtotime($row_report['performa_generated_date']));
            }
    }
            if($row['next_step']=='procurement Committee'||$row['next_step']=='Owner Committee'||$row['next_step']=='HO Committee'||$row['next_step']=='Finance'|| $row['next_step']=='Finalize Payment'||$row['next_step']=='Collection'||$row['next_step']=='Finished'||$row['next_step']=='Ready'){
                $sent="SELECT * FROM `report` where `request_id` = ?";
                $stmt_report = $conn->prepare($sent);
                $stmt_report -> bind_param("i", $row['request_id']);
                $stmt_report -> execute();
                $result_report = $stmt_report -> get_result();
                    if ($result_report->num_rows > 0) {
                        while($rs = $result_report->fetch_assoc()){
        
                        if(!is_null($rs['committee_approval_date']) && is_null($rs['sent_to_finance_date'])){
                        $next='Send to Finance by '.$proc_manager;
                        $next_time=date('d/m/y H:i',strtotime($row_report['committee_approval_date']));
                        }
                        if(!is_null($rs['sent_to_finance_date']) && is_null($rs['finance_approval_date'])){
                        $next=$disbursment;
                        $next_time=date('d/m/y H:i',strtotime($row_report['sent_to_finance_date']));
                        }
                        if($row['status']=='Finance Approved Petty Cash' && is_null($rs['cheque_prepared_date'])){
                        $next=$disbursment1;
                        $next_time=date('d/m/y H:i',strtotime($row_report['finance_approval_date']));
                        }
                        if($row['status']=='Finance Approved'){
                            $next='Prepare Requestion By '.($cashier!=""?$cashier:'TBA')."<span style='color:#7B3F00'  title='optional role'>".$disbursment_dep."</span>";;
                            $next_time=date('d/m/y H:i',strtotime($row_report['finance_approval_date']));
                            }
        if($row['status']=='Reviewed' && $row['next_step']=='Finalize Payment'){  
            $next=$fin_mgr;
            $next_time=date('d/m/y H:i',strtotime($row_report['Disbursement_review_date']));
        }
        if($row['status']=='Petty Cash Approved' && $row['next_step']=='Finalize Payment'){  
            $next=$cashier.", <span style='color:#7B3F00'  title='optional role'>".rtrim($disbursment,',')."</span>";
            $next_time=date('d/m/y H:i',strtotime($row_report['cheque_signed_date']));
        }
        if($row['next_step']=='Collection'&&$row['status']=='Payment Processed' && is_null($rs['collection_date']) ){
            if($collector != "")
                $next=" Collection By ".$collector;
            else
                $next=" Assign collector by ".$proc_manager;
        $next_time=date('d/m/y H:i',strtotime($row_report['collector_assigned_date']));
        }
        if($row['status']=='Collected-not-comfirmed' && is_null($rs['dep_check_date'])){
        $next='Approval by '.$department_manager;
        $next_time=date('d/m/y H:i',strtotime($row_report['collection_date']));
        }
        if($row['status']=='Collected-not-comfirmed' && !is_null($rs['dep_check_date'])||$row['next_step']=='Ready'){
        $next=$Store_checks."<span style='color:#7B3F00'  title='optional role'>$property_mgr</span>"; 
        $next_time=date('d/m/y H:i',strtotime($row_report['dep_check_date']));
        }
        if($row['next_step']=='Finished' && is_null($rs['settlement_date'])){
        $next='Settelment By '.$fin_mgr;
        $next_time=date('d/m/y H:i',strtotime($row_report['handover_comfirmed']));
        }
        if(!is_null($rs['settlement_date']))
        $next='Purcahse Completed';
    } }}}} 

    if($row['status']=='Cheque Prepared'){ 
    $next= 'Cheque Signing By :-'.$cheque_signatorys;
    $next_time=date('d/m/y H:i',strtotime($row_report['cheque_prepared_date']));
    }

    if($row['next_step'] == 'Manager'){
    $next=$department_manager!=''?$department_manager.','."<span style='color:#7B3F00'  title='optional role'>$director_GM</span>":$director_GM;
    $next_time=date('d/m/y H:i', strtotime($row_report['request_date']));
    }

    if($row['next_step'] == 'GM'){
        $next=$GM_approval;
        $next_time=date('d/m/y H:i',strtotime($row_report['manager_approval_date']));
        }
    
    if($row['next_step'] == 'Director'){
    $next=$director_GM;
    $date = ($row['status'] == 'Approved By Property')?$row_report['property_approval_date']:$row_report['stock_check_date'];
    $next_time=date('d/m/y H:i',strtotime($date));
    }

    if($row['next_step'] == 'Performa' && $row['status']!='Generating Quote' ){
    $next_time=date('d/m/y H:i',strtotime($row_report['property_approval_date']?$row_report['property_approval_date']:$row_report['stock_check_date']));
    $next="Assign PO. By:- ".$proc_manager.", <span style='color:#7B3F00'  title='optional role'>".rtrim($sinour_po,',')."</span>";
    }

    if($row['next_step'] == 'Store'){
    $next=$Store_checks."<span style='color:#7B3F00'  title='optional role'>$property_mgr</span>";
    $date=($row['status'] == 'Approved By Dep.Manager')?$row_report['manager_approval_date']:(($row['status'] == 'Approved By GM')?$row_report['GM_approval_date']:$row_report['spec_recieved']);
    $next_time=date('d/m/y H:i', strtotime($date));
    }

    if($row['next_step']=='Comparision Sheet Generation'){
    $next=$sinour_po." <span style='color:#7B3F00'  title='optional role'>".rtrim($proc_manager,',')."</span>";
    $next_time=date('d/m/y H:i',strtotime($row_report['performa_confirm_date']));
    }
    if($row['status']=='Petty Cash'||$row['next_step']=='Petty Cash Approval'){
    $next=$disbursment1?$disbursment1:'TBA';
    $next_time=date('d/m/y H:i',strtotime($row_report['performa_confirm_date']));
    }

    $stmnt = "SELECT * FROM `requests` WHERE `status` LIKE '%Rejected%' and request_id = ?";
    $stmt_rejection = $conn->prepare($stmnt);
    $stmt_rejection -> bind_param("i", $row['request_id']);
    $stmt_rejection -> execute();
    $result_rejection = $stmt_rejection -> get_result();
    if ($result_rejection->num_rows > 0) {
        while($rows= $result_rejection->fetch_assoc()){
            $next='purchase Rejected';
        }
    }


            echo "
                        <div class='m-auto w-50 my-2 alert-primary'>
                            <li class='list-group-item list-group-item-warning border-0'><i class='text-dark fw-bold'>Status : </i><span class='text-$colorrr'>".ucfirst($status)."</span></li>
                        ".(($row['status']!='canceled'&&$next!='Purcahse Completed'&&$row['status']!='All Complete'&& $next!='purchase Rejected')?"<li class='list-group-item list-group-item-warning border-0'><i class='text-dark fw-bold'>Next Step : </i><span class='text-$colorrr'>$row[next_step](".ucfirst(rtrim($next,',')).") <sub> <i class='bi bi-clock'></i> $next_time</sub></span></li> ":'')."
                        </div>
                    ";
            echo '</ul>
                    </li>
                    </div>';


            $passed1 = false;
            $passed2 = false;
            $passed3 = false;

            $compiled_by = '';
            $handover_by = '';
            $dep_check = '';
            $store_recieve = '';
            $cashier = "";
            $cheque_signatory = "";
            $Finance_approved='';
            $procManager='';
            $set_to_committee='';
            $finance_checked='';
            $committees ='';
            $tag_col = '';

            $success_1 = '';
            $success_2 = '';
            $success_3 = '';
            $success_4 = '';

            $icon1 = '';
            $icon2 = '';
            $icon3 = '';
            $icon4 = '';

            $tag_performa = '';
            $tag_finance = '';
            $tag_col = '';
            $tag_comp = '';

            $assign_officer = 0;
            $accept_job = 0;
            $complete_job = 0;
            $confirm_proforma = 0;
            $spo = 0; 
            $confirm_comparision = 0; // Phase 2
            $confirm_comparision2 = 0; // Phase 2

            $performa_prog = 0;

            $committee = 0;
            $finalize = 0;
            $finance_review = 0;
            $finance_approve = 0;
            $cheque_prepare = 0;
            $cheque_sign = 0;
            $dis = 0; // Phase 3

            $col = 0;
            $dep_item_approval = 0;
            $store_confirmation = 0;
            $comp = 0;
            $settlement = 0; // Phase 4
        

            $stmt_po_by_request -> bind_param("i", $data[1]);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if ($result_po_by_request -> num_rows > 0) {
                $r = $result_po_by_request -> fetch_assoc();
                $assigned_by=$r['assigned_by'];
                $procManager=$r['performa_opened'];
                // $settled_by=$r['settlement'];
            } 
            if(isset($r['cluster_id']))
            {
                $stmt_cluster -> bind_param("i", $r['cluster_id']);
                $stmt_cluster -> execute();
                $result_cluster = $stmt_cluster -> get_result();
                if ($result_cluster -> num_rows > 0) {
                    $resultData = $result_cluster -> fetch_assoc();
                    $compiled_by = $resultData['compiled_by'];
                    $cashier = $resultData['cashier'];
                    $stmt_cheques -> bind_param("i", $r['cluster_id']);
                    $stmt_cheques -> execute();
                    $result_cheques = $stmt_cheques -> get_result();
                    if ($result_cheques->num_rows > 0) {
                        while($row_cheque = $result_cheques->fetch_assoc())
                        {
                            if($row_cheque['signatory'] != "")
                            {
                                $signas = explode(",",$row_cheque['signatory']);
                                foreach($signas as $sig)
                                {
                                    if(strpos($cheque_signatory,$sig) === False)
                                    $cheque_signatory .= ($cheque_signatory == "")?$sig:", ".$sig;
                                }
                            }
                        }
                    }
                    $Finance_approved=$resultData['Finance_approved'];
                    $finance_checked=$resultData['Checked_by'];
                }
                $sql = "SELECT * FROM `committee_approval` WHERE cluster_id = ?";
                $stmt_committee_approval = $conn->prepare($sql);
                $stmt_committee_approval -> bind_param("i", $r['cluster_id']);
                $stmt_committee_approval -> execute();
                $result_committee_approval = $stmt_committee_approval -> get_result();
                if($result_committee_approval->num_rows > 0)
                {
                    while($resultDatas = $result_committee_approval->fetch_assoc())
                        $committees.= $resultDatas['committee_member'].'<br/>';
                }
            }
            //$committee=implode($committee,',');
            $sql = "SELECT * FROM `recorded_time` WHERE for_id = ? and `database_name` = 'reqeusts' and opperation = 'Handover' order by timestamp DESC LIMIT 1";
            $stmt_recorded_time_reqeusts = $conn->prepare($sql);
            $stmt_recorded_time_reqeusts -> bind_param("i", $data[1]);
            $stmt_recorded_time_reqeusts -> execute();
            $result_recorded_time_reqeusts = $stmt_recorded_time_reqeusts -> get_result();
            if ($result_recorded_time_reqeusts -> num_rows > 0) {
                while( $resultDatas = $result_recorded_time_reqeusts -> fetch_assoc())
                    $handover_by = $resultDatas['user'];
            }
            else $handover_by = $checked_by;

            $sql = "SELECT * FROM `recorded_time` WHERE for_id = ? and database_name = 'reqeusts' and (opperation = 'Item In stock Approved' Or opperation = 'Item In stock Rejected') order by timestamp DESC LIMIT 1";
            $stmt_recorded_time_item_instock = $conn->prepare($sql);
            $stmt_recorded_time_item_instock -> bind_param("i", $data[1]);
            $stmt_recorded_time_item_instock -> execute();
            $result_recorded_time_item_instock = $stmt_recorded_time_item_instock -> get_result();
            if ($result_recorded_time_item_instock->num_rows > 0) {
                while( $resultDatas = $result_recorded_time_item_instock->fetch_assoc())
                    $dep_check = $resultDatas['user'];
            }
            else $dep_check = $row['manager'];

            $sql = "SELECT * FROM `recorded_time` WHERE for_id = ? and database_name = 'reqeusts' and (opperation = 'Store Confirmed') order by timestamp DESC LIMIT 1";
            $stmt_recorded_time_Store = $conn->prepare($sql);
            $stmt_recorded_time_Store -> bind_param("i", $data[1]);
            $stmt_recorded_time_Store -> execute();
            $result_recorded_time_Store = $stmt_recorded_time_Store -> get_result();
            if ($result_recorded_time_Store -> num_rows > 0) {
                while( $resultDatas = $result_recorded_time_Store -> fetch_assoc())
                    $store_recieve = $resultDatas['user'];
            }
            else $store_recieve = $checked_by;

            $sql = "SELECT * FROM `recorded_time` WHERE for_id = ? and database_name = 'reqeusts' and (opperation = 'Sent to Committee') order by timestamp DESC LIMIT 1";
            $stmt_recorded_time_committee = $conn->prepare($sql);
            $stmt_recorded_time_committee -> bind_param("i", $data[1]);
            $stmt_recorded_time_committee -> execute();
            $result_recorded_time_committee = $stmt_recorded_time_committee -> get_result();
            if ($result_recorded_time_committee -> num_rows > 0) {
                while( $resultDatas = $result_recorded_time_committee -> fetch_assoc())
                    $set_to_committee = $resultDatas['user'];
            }
            else $set_to_committee = $procManager;

            $sql = "SELECT * FROM `recorded_time` WHERE for_id = ? and database_name = 'purchase_order' and (opperation = 'Settlement') order by timestamp DESC LIMIT 1";
            $stmt_recorded_time_Settlement = $conn->prepare($sql);
            $stmt_recorded_time_Settlement -> bind_param("i", $purchase_order_id);
            $stmt_recorded_time_Settlement -> execute();
            $result_recorded_time_Settlement = $stmt_recorded_time_Settlement -> get_result();
            if ($result_recorded_time_Settlement -> num_rows > 0) {
                while( $resultDatas = $result_recorded_time_Settlement -> fetch_assoc())
                    $settled_by = $resultDatas['user'];
            }
            else $settled_by = "";

            $initials = getTotalPhase($conn,$conn_fleet,$row['request_id'],1);
            // //////////////////////////////////////////////////////////Phase 1/////////////////////////////////////////////////////////////////
            $one_val = $start_val = 100/$initials;
            $total_one = $row['phase_one']*$one_val;
    
            if ($initials <= $row['phase_one']) {
                $total_one = 100;
                $success_1 = 'bg-success';
                $passed1 = true;
                $icon1 = "<i class='ms-3 text-success fas fa-check-circle'></i>";
            }
            else
                $icon1 = "<span class='badge rounded-pill bg-primary ms-2'> Current</span>";
            
            // //////////////////////////////////////////////////////////Phase 2/////////////////////////////////////////////////////////////////
            if ($passed1 && $outofstock > 0) {
                $initials_2 = getTotalPhase($conn,$conn_fleet,$row['request_id'],2);
                $one_val = $start_val = 100/$initials_2;
                $total_two = $row['phase_two']*$one_val;

                if (($initials_2 <= $row['phase_two'])) {
                    $total_two = 100;
                    $success_2 = 'bg-success';
                    $icon2 = "<i class='ms-3 text-success fas fa-check-circle'></i>";
                    $passed2 = true;
                }
                else 
                    $icon2 = "<span class='badge rounded-pill bg-primary ms-2'> Current</span>";
            }
            
            // ////////////////////////////////////////////////////////////phase 3//////////////////////////////////////////////////////////////////////////// 
            if ($passed2) {
                $initials_3 = getTotalPhase($conn,$conn_fleet,$row['request_id'],3);
                $one_val = $start_val = 100/$initials_3;
                $total_three = $row['phase_three']*$one_val;

                if (($initials_3 <= $row['phase_three'])) {
                    $total_three = 100;
                    $success_3 = 'bg-success';
                    $icon3 = "<i class='ms-3 text-success fas fa-check-circle'></i>";
                    $passed3 = true;
                }
                else 
                    $icon3 = "<span class='badge rounded-pill bg-primary ms-2'> Current</span>";
            }
            if ($passed3) {
                $initials_4 = getTotalPhase($conn,$conn_fleet,$row['request_id'],4);
                $one_val = $start_val = 100/$initials_4;
                $total_four = $row['phase_four']*$one_val;

                if (($initials_4 <= $row['phase_four'])) {
                    $total_four = 100;
                    $success_4 = 'bg-success';
                    $icon4 = "<i class='ms-3 text-success fas fa-check-circle'></i>";
                    $passed4 = true;
                }
                else 
                    $icon4 = "<span class='badge rounded-pill bg-primary ms-2'> Current</span>";
            }
    
    
    

            echo "
                <button class='form-control btn btn-$color_btn noPrint' type='button' data-bs-toggle='collapse' data-bs-target='#progress' aria-expanded='false' aria-controls='progress' onclick='view_progress_coll(this)' name='false'><i id='toogle_progress' class='fa fa-plus-circle'></i> View Progress</button>
                <div class='container border border-4 border-$color_btn collapse' id='progress'>
                <!-- <button class='btn container-fluid' type='button' data-bs-toggle='collapse' data-bs-target='#phase1' aria-expanded='false' aria-controls='phase1'> -->
                    <div class='divider fw-bold'>
                        <div class='divider-text'>Phase 1 - Initial Approval $icon1";

            echo "</div>
                    </div>
                    <!-- </button> -->
                    <!-- collapse -->
                <div class='' id='phase1'>
                    <div class='progress my-3'>
                        <div class='progress-bar $success_1 progress-label' role='progressbar' style='width: $total_one%'
                            aria-valuenow='$total_one' aria-valuemin='0' aria-valuemax='100'></div>";
            echo "
                    </div>
                    <div class='row'>
                        <div class='col small'>Requested
                        <div><span class='text-success' style='font-size:10px'>".date('d/m/y H:i', strtotime($row_report['request_date']))."</span>".createAlert($row['customer'],"Requested By")."
                        </div> 
                        </div>
                        <div class='col small'>Manager Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['manager_approval_date'] ? date('d/m/y H:i', strtotime($row_report['manager_approval_date']))."</span>".createAlert($row['manager'],"Manager Approval") : $loader)."</div>
                        </div>
                        ".(($has_gm)?"
                        <div class='col small'>GM Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['GM_approval_date'] ? date('d/m/y H:i', strtotime($row_report['GM_approval_date']))."</span>".createAlert($row['GM'],"GM Approval"): $loader)."</div>
                        </div>":"")."

                        ".(($spec_needed)?"
                        <div class='col small'>Specification Gave
                        <div><span class='text-success' style='font-size:10px'>".($row_report['spec_recieved'] ? date('d/m/y H:i', strtotime($row_report['spec_recieved']))."</span>".createAlert($spec_giver,"Spec Given By"): $loader)."</div>
                        </div>
                        ":"")."
                        
                        ".(($type!='agreement')?"
                            <div class='col small'>Stock Check
                            <div><span class='text-success' style='font-size:10px'>".($row_report['stock_check_date'] ? date('d/m/y H:i', strtotime($row_report['stock_check_date']))."</span>".createAlert($checked_by,"Store Check") : $loader)."</div>
                            </div>

                            ".((!$no_property)?"
                            <div class='col small'>Property Approval
                            <div><span  class='text-success' style='font-size:10px'>".($row_report['property_approval_date'] ? date('d/m/y H:i', strtotime($row_report['property_approval_date']))."</span>".createAlert($row['property'],"Property Approval") : $loader)."</div>
                            </div>
                            ":"")."
                        ":"")."

                        ".((isset($outofstock) && $outofstock == 0)?"
                        <div class='col small'>Department Item Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['dep_check_date'] ? date('d/m/y H:i', strtotime($row_report['dep_check_date']))."</span>".createAlert($dep_check,"Department Item Approval") : $dep_check)."</div>
                        </div>
                        <div class='col small'>Handover
                        <div><span  class='text-success' style='font-size:10px'>".($row_report['final_recieved_date'] ? date('d/m/y H:i', strtotime($row_report['final_recieved_date']))."</span>".createAlert($handover_by,"Handover by") : $loader)."</div>                  
                        </div>
                        ":"")."

                        ".(($row["company"] == 'Hagbes HQ.' && $row["department"] != 'Owner' && ($outofstock != 0 || !isset($outofstock)))?"
                        <div class='col small'>Director Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['Director_approval_date'] ? date('d/m/y H:i', strtotime($row_report['Director_approval_date']))."</span>".createAlert($row['director'],"Director Approval"): $loader)."</div>
                        </div>
                        ":"")."

                        ".(($type=='agreement' && ($outofstock != 0 || !isset($outofstock)))?"
                        <div class='col small'>Directors Agreement Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['Directors_approval_date'] ? date('d/m/y H:i', strtotime($row_report['Directors_approval_date']))."</span>".createAlert($row['directors'],"Ho Director Approval") : $loader)."</div>
                        </div>
                        ":"")."
                        ".(($type == 'Fixed Assets' && ($outofstock != 0 || !isset($outofstock)))?"
                        <div class='col small'>Owner Approval
                        <div><span class='text-success' style='font-size:10px'>".($row_report['Owner_approval_date'] ? date('d/m/y H:i', strtotime($row_report['Owner_approval_date']))."</span>".createAlert($row['owner'],"Owner Approval") : $loader)."</div>
                        </div>
                        ":"");
            echo "</div> 
                </div>";
            if (is_null($row['stock_info']) || $outofstock > 0) {
                echo "
                        <!-- <button class='btn container-fluid' type='button' data-bs-toggle='collapse' data-bs-target='#phase2' aria-expanded='false' aria-controls='phase2'> -->
                        <div class='divider'>
                            <div class='divider-text fw-bold'>Phase 2 - Procurement Process $icon2
                        </div>
                        </div>
                        <!-- </button> -->
                        <div class='' id='phase2'>
                            <div class='progress my-3'>";
                            if($type!='agreement')
                            echo "
                                <div class='progress-bar $success_2 progress-label' role='progressbar' style='width: $total_two%'
                                    aria-valuenow='$total_two' aria-valuemin='0' aria-valuemax='100'></div>";
                                    if($type=='agreement')
                                    echo " <div class='progress-bar $success_2 progress-label' role='progressbar' style='width:".(6*$confirm_comparision2)."%'
                                    aria-valuenow='$confirm_comparision2' aria-valuemin='0' aria-valuemax='100'>
                                    </div>";
                            echo "</div>
                            <div class='row'>";
                            if($type!='agreement')
                            echo"
                                <div class='col small'>Assign Purchaser
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['officer_assigned_date'] ? date('d/m/y H:i', strtotime($row_report['officer_assigned_date']))."</span>".createAlert($assigned_by,"Assign Purchaser") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Accept Proforma Job
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['officer_acceptance_date'] ? date('d/m/y H:i', strtotime($row_report['officer_acceptance_date']))."</span>".createAlert($purchase_officer,"Performa Generator") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Complete Proforma Job
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['performa_generated_date'] ? date('d/m/y H:i', strtotime($row_report['performa_generated_date']))."</span>".createAlert($purchase_officer,"Performa Generator") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Confirm Proforma
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['performa_confirm_date'] ? date('d/m/y H:i', strtotime($row_report['performa_confirm_date']))."</span>".createAlert($procManager,"Performa Generator") : $loader)."</div>                  
                                </div>
                                <div class='col small'>".(($type=='agreement')?"Agreement ":"")."Comparision Sheet
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['compsheet_generated_date'] ? date('d/m/y H:i', strtotime($row_report['compsheet_generated_date']))."</span>".createAlert($compiled_by ,"Comparison Sheet Generator") : $loader)."</div>                  
                                </div>";
                                echo "
                                <div class='col small'>Sent to Committee
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['sent_to_committee_date'] ? date('d/m/y H:i', strtotime($row_report['sent_to_committee_date']))."</span>".createAlert($set_to_committee ,"Comparison Sheet Generator") : $loader)."</div>                  
                                </div>
                            </div>   
                        </div>
                        <!-- <button class='btn container-fluid' type='button' data-bs-toggle='collapse' data-bs-target='#phase3' aria-expanded='false' aria-controls='phase3'> -->
                            <div class='divider fw-bold'>
                                <div class='divider-text'>Phase 3 - Committee Approval and Finance $icon3
                                </div>
                            </div>
                            <!-- </button> -->
                        <div class='' id='phase3'>
                            <div class='progress my-3'>
                            <div class='progress-bar $success_3 progress-label' role='progressbar' style='width: $total_three%'
                                aria-valuenow='$total_three' aria-valuemin='0' aria-valuemax='100'></div>
                            </div>
                            
                            <div class='row'>

                                <div class='col small'>Committee Approval
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['committee_approval_date'] ? date('d/m/y H:i', strtotime($row_report['committee_approval_date']))."</span>".createAlert( $committees,"Committee Approval") : $loader)."</div>                  
                                </div>
                                
                                <div class='col small'>Sent to Finance
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['sent_to_finance_date'] ? date('d/m/y H:i', strtotime($row_report['sent_to_finance_date']))."</span>".createAlert(($finance_sent?$finance_sent:$assigned_by),"Sent to Finance By") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Finance Review
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['Disbursement_review_date'] ? date('d/m/y H:i', strtotime($row_report['Disbursement_review_date']))."</span>".createAlert($finance_checked,"Finance Review") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Finance Approval
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['finance_approval_date'] ? date('d/m/y H:i', strtotime($row_report['finance_approval_date']))."</span>".createAlert($Finance_approved,"Finance Approval") : $loader)."</div>                  
                                </div>";
                                $new_date=false;
                                if($row_report['cheque_prepared_date'])
                                $new_date=$row_report['cheque_prepared_date'];
                                else if(!$row_report['cheque_prepared_date'] &&$row_report['cheque_signed_date'])
                                $new_date=$row_report['cheque_signed_date'];
                                $cashier='not found';
                                if(!is_null($resultData['cashier']))
                                $cashier=$resultData['cashier'];
                                if(sizeof(explode(" - ",$resultData['cheque_signatories']))>1)
                                    $cheque_signatory=str_replace("."," ",explode(" - ",$resultData['cheque_signatories'])[1]);


                               echo "<div class='col small'>Cashier (Cheque Prepare)
                                <div><span  class='text-success' style='font-size:10px'>".($new_date? date('d/m/y H:i', strtotime($new_date))."</span>".createAlert($cashier,"Cashier") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Cheque Signatory
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['cheque_signed_date'] ? date('d/m/y H:i', strtotime($row_report['cheque_signed_date']))."</span>".createAlert($cheque_signatory,"Cheque Signatory") : $loader)."</div>                  
                                </div>
                            </div>
                        </div>
                        <!-- <button class='btn container-fluid' type='button' data-bs-toggle='collapse' data-bs-target='#phase3' aria-expanded='false' aria-controls='phase3'> -->";
                          
                      echo $type=='agreement'?"<div class='divider fw-bold'>
                                <div class='divider-text'>Phase 4 -Settelment $icon4
                                </div>
                            </div>":"<div class='divider fw-bold'>
                            <div class='divider-text'>Phase 4 - Collection and Store $icon4
                            </div>
                        </div>";

                           echo " <!-- </button> -->
                        <div class='' id='phase3'>
                            <div class='progress my-3'>
                            <div class='progress-bar $success_4 progress-label' role='progressbar' style='width: $total_four%'
                                aria-valuenow='$total_four' aria-valuemin='0' aria-valuemax='100'></div>
                            </div>
                            </div>
                            
                            <div class='row'>
                            ";
                            echo $type!='agreement'?"
                                <div class='col small'>Collection
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['collection_date'] ? date('d/m/y H:i', strtotime($row_report['collection_date']))."</span>".createAlert($collector,"Collector") : $loader)."</div>                  
                                </div>

                                <div class='col small'>Department Item Approval
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['dep_check_date'] ? date('d/m/y H:i', strtotime($row_report['dep_check_date']))."</span>".createAlert($dep_check,"Department Item Approval") : $loader)."</div>                  
                                </div>
                                
                                <div class='col small'>Store Confirmation
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['handover_comfirmed'] ? date('d/m/y H:i', strtotime($row_report['handover_comfirmed']))."</span>".createAlert($store_recieve,"Store Recieved By") : $loader)."</div>                  
                                </div>
                                
                                <div class='col small'>Handover
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['final_recieved_date'] ? date('d/m/y H:i', strtotime($row_report['final_recieved_date']))."</span>".createAlert($handover_by,"Handover by") : $loader)."</div>                  
                                </div>":"";
                                $center=$type=="agreement"?' text-center':"";

                                echo "
                                <div class='col small $center'>Settlement
                                <div><span  class='text-success' style='font-size:10px'>".($row_report['settlement_date'] ? date('d/m/y H:i', strtotime($row_report['settlement_date']))."</span>".createAlert($settled_by,"Settled By") : $loader)."</div>                  
                                </div>
                            </div>
                        </div>
                    </div>";

            } else {
                echo "
                    <div class='divider fw-bold'>
                        <div class='divider-text text-success'>
                            ".(($row['status'] == 'All Complete')?"Items Received from stock <i class='ms-3 text-success fas fa-check-circle'></i>":"In Stock Items being checked")."
                        </div>
                    </div>";
            }
            // }
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