<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    // include '../common/details.php';
                                                            // if(approved)
    $comp_count=0;
    $data = $_GET['data'];

    $stmt_cluster -> bind_param("i", $data);
    $stmt_cluster -> execute();
    $result_cluster = $stmt_cluster->get_result();
    if($result_cluster->num_rows>0)
    $row_temp = $result_cluster->fetch_assoc();
    $active = ($row_temp['status'] != 'canceled');
    $canEdit = $row_temp['procurement_company'] == $_SESSION['company'] && (($_SESSION['department'] == "Procurement" && $_SESSION["role"]=='manager') || ($_SESSION['additional_role']==1)) && !(in_array($row_temp['status'],["closed","Changed","canceled","Payment Processed","Collected-not-comfirmed","Cheque Prepared","All Payment Processed","partly Collected"]));
    if(!$active) echo "<div class='divider fw-bold'><div class='divider-text text-danger'>Canceled</div></div>";

    $stmt_po_cluster->bind_param("i", $data);
    $stmt_po_cluster->execute();
    $result_po = $stmt_po_cluster->get_result();
    if($result_po->num_rows>0)
        while($r = $result_po->fetch_assoc())
        {
            $purchase_order_id = $r['purchase_order_id'];
            $performa_id = $r['performa_id'];
            $type=$r['request_type'];
            $request_id=$r['request_id'];
        }
    else
    { 
        $stmt_prices->bind_param("i", $data);
        $stmt_prices->execute();
        $result_prices = $stmt_prices->get_result();
        if($result_prices->num_rows>0)
            while($r = $result_prices->fetch_assoc())
            {
                $purchase_order_id = $r['purchase_order_id'];
            }
        $stmt_po->bind_param("i", $purchase_order_id);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        if($result_po->num_rows>0)
            while($r = $result_po->fetch_assoc())
            {
                $purchase_order_id = $r['purchase_order_id'];
                $type=$r['request_type'];
                $request_id=$r['request_id'];
            }
        $performa_id = $data;
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
    <div class='noPrint'>
    <h3 class='text-center'>$title Selections</h3>";
    // if(strpos($_SESSION['a_type'],'Committee')!==false || $_SESSION['department'] =='Owner' || $_SESSION['department'] =='Procurement' || $_SESSION['department'] =='Finance' || $_SESSION['role'] =='Admin'){
    if(1){
        if($type=='agreement')
            echo "
            <button type='button' class='btn btn-outline-primary float-start' onclick='view_performa(this,\"comp_sheet\")' data-bs-toggle='modal' data-bs-target='#view_performa' id='$performa_id' title='1' name='$request_id'>
                Bincard
            </button>";
        else
            echo "
            <button type='button' class='btn btn-outline-primary float-start' onclick='view_performa(this,\"comp_sheet\")' data-bs-toggle='modal' data-bs-target='#view_performa' id='$performa_id' title='2' name='$purchase_order_id'>
                Proforma
            </button>
            <button type='button' name='$data' class='d-none' id='reloadComparison'>
                Reload
            </button>";
            if($canEdit)
        echo "
        <button type='button' class='btn btn-outline-info float-start ms-2 d-none' id='pullOut' name='pullOut' onclick='pullOutRequest(this,$data)'>
            Create New Comparison
        </button>";
    }
    

    echo "
        
        <div class='text-center mx-auto mb-4' style='width: 200px;'>
            <ul class='nav nav-tabs'>
                <li class='nav-item'>
                    <button type='button' class='btn nav-link active' id='tbl_toggle' onclick='change_ch_view(this)'>
                        <i class='fas fa-table'></i>
                    </button>
                </li>
                <li class='nav-item'>
                    <button type='button' class='btn nav-link' id='list_toggle' onclick='change_ch_view(this)'>
                        <i class='fas fa-th-list'></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class='card' id='tbl_cs_view'>
        <div class='card-body'>
    <table class='table text-center' cellspacing='15'>
        <thead>
            <tr>
            <th rowspan='2'>No</th>
            <th rowspan='2'>Item</th>
            <th rowspan='2'>Requested (for Purchase) Qty</th>";
            // <th rowspan='2'>Qty to be Purchased</th>
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
            $stmt_po->bind_param("i", $row_all['purchase_order_id']);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            if($result_po->num_rows>0)
            while($row_po = $result_po->fetch_assoc())
            {
                $canceled_color = ($active && $row_po['status'] == 'canceled')?"class='border border-4 border-danger'":"";
                echo "<tr $canceled_color><td>";
                if($canEdit)
                    echo "<input class='form-check-input generalEdit' type='checkbox' name='generalEdit[]' onclick='pulloutActive()' value='".$row_po['request_id'].",".$row_all['purchase_order_id']."'>";
                echo "$count</td>";
             
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
                    echo "<td><button type='button' title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                    ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button></td><td>".$row_req['requested_quantity']." (".$to_purchase.") ".$row_req['unit']."</td>";
                    for($i=0;$i<$comp_count;$i++)
                    {
                        if(strpos($companies[$i],"'") !== false && strpos($companies[$i],"\'") === false) $companies[$i] = str_replace("'","'",$companies[$i]);
                        $sql_specific_price = "SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`= ? AND providing_company= ?";
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
                                $spec_show = "<td class=' $colors $front_bord'>".number_format($row_spec['total_price'], 2, ".", ",")."</td>";
                            else
                                $spec_show = "<td class='position-relative $colors $front_bord'>".number_format($row_spec['total_price'], 2, ".", ",")."
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
                echo "</tr>
                ";
            }
        }
        echo"<tr><th colspan='2'>Total Price</th><td>-</td>";
        for($i=0;$i<$comp_count;$i++)
        {

            $stmt2 = $conn->prepare("SELECT SUM(total_price) AS sum_t FROM `price_information` where `cluster_id` = ? AND `providing_company` = ? AND selected");
            $stmt2->bind_param("is", $data, $companies[$i]);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($total_p[$i]);
            $stmt2->fetch();
            $stmt2->close();

            $stmt2 = $conn->prepare("SELECT SUM(after_vat) AS sum_v FROM `price_information` where `cluster_id` = ? AND `providing_company` = ? AND selected");
            $stmt2->bind_param("is", $data, $companies[$i]);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($after_vat[$i]);
            $stmt2->fetch();
            $stmt2->close();
            $total_p[$i] = (!is_null($total_p[$i]))?$total_p[$i]:0;
            echo" <td>-</td> <td>-</td> <th>".number_format($total_p[$i], 2, ".", ",")."</th> ";
        }
        echo"</tr>
        <tr><th colspan='2'>15% VAT</th><td>-</td>";
        for($i=0;$i<$comp_count;$i++)
        {
            // $sql = "SELECT * FROM `limit_ho` ORDER BY id ASC";
            // $res = $conn->query($sql);
            // if($res->num_rows>0)
            // {
            //     while($r_new = $res->fetch_assoc())
            //     {
            //         if($res->num_rows == 1 || $r_new['date']<=$row_po['timestamp'])
            //             $Vat = $r_new['Vat'];
            //     }
            // }
            // else $Vat = 0.15;
            // $vat[$i] = $Vat*($total_p[$i]);
            $vat[$i] = $after_vat[$i]- $total_p[$i];
            echo" <td> - </td><td> - </td> <th>".number_format($vat[$i], 2, ".", ",")."</th> ";
        }
        echo"</tr>
        <tr><th colspan='2'>Grand Total</th><td>-</td>";
        for($i=0;$i<$comp_count;$i++)
        {
            $gt = (!is_null($after_vat[$i]))?$after_vat[$i]:0;
            echo" <td> - </td> <td>- </td> <th>".number_format($gt, 2, ".", ",")."</th> ";
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
    echo "<div class='card d-none noPrint' id='list_cs_view'>
        <div class='card-body'>
        <ul class= 'list-group list-group-flush'>
        <li class='list-group-item list-group-item-primary mb-4'>
            <ul class= 'list-group list-group-flush'>";
        $stmt_companies->bind_param("i", $data);
        $stmt_companies->execute();
        $result_companies = $stmt_companies->get_result();
        if($result_companies->num_rows>0)
        while($row = $result_companies->fetch_assoc())
        {
            echo "<h5 class='text-capitalize mb-2'>Company - ".$row['providing_company']."</h5>";
            $stmt_pos->bind_param("i", $data);
            $stmt_pos->execute();
            $result_pos = $stmt_pos->get_result();
            if($result_pos->num_rows>0)
            while($row_all = $result_pos->fetch_assoc())
            {
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
                        $prov_company = str_replace("'","'",$row['providing_company']);
                        $stmt_specific_price->bind_param("iis", $row_po['cluster_id'], $row_all['purchase_order_id'], $prov_company);
                        $stmt_specific_price->execute();
                        $result_specific_price = $stmt_specific_price->get_result();
                        if($result_specific_price->num_rows>0)
                        while($row_spec = $result_specific_price->fetch_assoc())
                        {
                            if(isset($person_selections))
                                $selected_price = in_array($row_spec['id'], $person_selections);
                            else
                                $selected_price = $row_spec['selected'];
                            $colors = ($selected_price)?"success ":"light";
                            $spec_data = ($row_spec['specification'] == '' || is_null($row_spec['specification']))?"No Specifcation":$row_spec['specification'];
                            if($row_spec['specification'] == '' || is_null($row_spec['specification']))
                                $spec_show = "<li class='list-group-item list-group-item-$colors my-2 w-75 mx-auto' type='button' data-bs-toggle='collapse' data-bs-target='#".$row_spec['id']."'> Item - <button type='button'  title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>";
                            else
                                $spec_show = "<li class='list-group-item list-group-item-$colors my-2 position-relative w-75 mx-auto'> Item - <button type='button'  title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button><span data-bs-html='true' class='btn btn-sm position-absolute top-0 start-100 translate-middle  badge rounded-pill alert-primary' data-bs-toggle='popover' title='Specification' 
                                    data-bs-content='$spec_data'> <i class='fa fa-info-circle' title='Details'></i></span></li>
                                ";
                            echo "
                            $spec_show  
                            <div class='m-auto w-100'>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6 mx-auto'><i class='text-primary'>Provided Quantity  -  </i>".$row_spec['quantity']."</li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6 mx-auto'><i class='text-primary'>Unit Price  -  </i>".number_format($row_spec['price'], 2, ".", ",")."</li>
                                <li class='list-group-item list-group-item-light col-sm-12 col-md-6 mx-auto'><i class='text-primary'>Total Price  -  </i>".number_format($row_spec['total_price'], 2, ".", ",")."</li>
                            </div>
                            ";
                        }
                        else {
                            //echo "<td colspan='3' class='bg-secondary'></td>";
                        }
                    }
                }
                $stmt2 = $conn->prepare("SELECT SUM(total_price) AS sum_t FROM `price_information` where `cluster_id`='".$data."' AND `providing_company`='".$row['providing_company']."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($total_pr);
                $stmt2->fetch();
                $stmt2->close();

                $stmt_cluster->bind_param("i", $data);
                $stmt_cluster->execute();
                $result_cluster = $stmt_cluster->get_result();
                $clus_row=$result_cluster->fetch_assoc();

                $stmt_limit->bind_param("i", $clus_row['company']);
                $stmt_limit->execute();
                $result_limit = $stmt_limit->get_result();

                if ($result_limit->num_rows ==0)
                {
                    $others = "Others";
                    $stmt_limit->bind_param("i", $others);
                    $stmt_limit->execute();
                    $result_limit = $stmt_limit->get_result();
                }
                if($result_limit->num_rows>0)
                {
                    $r_new = $result_limit->fetch_assoc();
                    $Vat = $r_new['Vat'];
                }
                else $Vat = 0.15;
                $vat = $Vat*($total_pr);
                $gt = $vat + $total_pr;
            }
            echo "
            <div class='m-auto w-100 my-4'>
                <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>Total Price  -  </i>".number_format($total_pr, 2, ".", ",")."</li>
                <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>VAT Price  -  </i>".number_format($vat, 2, ".", ",")."</li>
                <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>Grand total  -  </i>".number_format($gt, 2, ".", ",")."</li>
            </div>
            ";

        }
    echo"</ul>
        </li>
        </ul>
        </div>
    </div>";
    // echo "
    // <!--<button class='btn btn-primary' type='button' data-bs-toggle='collapse' data-bs-target='#approval_stat' aria-expanded='true' aria-controls='approval_stat'>
    // Approval
    // </button> collapse-->
    // <div class='m-auto w-100 row border border-3 border-secondary' id='approval_stat'>
    // <h4 class='text-primary text-center my-2'> Approval</h4>
    // <ul class= 'list-group list-group-flush my-2 mx-auto col-sm-12 col-md-6 '>
    // ";
    // $sql2 = "SELECT * FROM `committee_approval` WHERE `cluster_id`='".$data."'";
    // $result2 = $conn->query($sql2);
    // if($result2->num_rows>0)
    //     while($r = $result2->fetch_assoc())
    //     {
    //         $stat = ($r['status']!="Approved" && $r['status']!="Rejected")?"Waiting":$r['status'];
    //         $remark = ($r['remark']=="#" || $r['remark']=="")?"No Remark":$r['remark'];
    //         echo "
    //             <li class='list-group-item list-group-item-primary border border-warning mb-2'>
    //             Name : <i class='text-primary'>".$r['committee_member']."</i><br>
    //             Status : <i class='text-primary'>".$stat."</i><br>
    //             Remark : <i class='text-primary'>".$remark."</i><br>
    //             Date : <i class='text-primary' title='".$r['timestamp']."'>".date("d-M-Y", strtotime($r['timestamp']))."</i><br>
    //             </li>
    //         ";
    //     }
    // echo "
    // </ul>
    // </div>";
    $sql_approval = "SELECT * FROM `committee_approval` WHERE `cluster_id`= ?";
    $stmt_approval = $conn->prepare($sql_approval);
    $stmt_approval->bind_param("i", $data);
    $stmt_approval->execute();
    $result_approval = $stmt_approval->get_result();
    if($result_approval->num_rows>0)
    {
        echo "
        <!--<button class='btn btn-primary' type='button' data-bs-toggle='collapse' data-bs-target='#approval_stat' aria-expanded='true' aria-controls='approval_stat'>
        Approval
        </button> collapse-->
        <div class='m-auto w-100 mt-4 row border border-3 border-secondary noPrint' id='approval_stat'>
        <h4 class='text-primary text-center my-2'>Committee Level Approval</h4>
        <ul class= 'list-group list-group-flush my-2 mx-auto col-sm-12 col-md-6 '>
        ";
            while($r = $result_approval->fetch_assoc())
            {
                $stat = ($r['status']!="Approved" && $r['status']!="Rejected")?"Waiting":$r['status'];
                $remark = ($r['remark']=="#" || $r['remark']=="")?"No Remark":$r['remark'];
            echo "  
            <li class='list-group-item list-group-item-primary border border-warning mb-2'>
            Name : <i class='text-primary'>".$r['committee_member']."</i><br>
            Status : <i class='text-primary'>".$stat."</i><br>
            Remark : <i class='text-primary'>".$remark."</i><br>
            Date : <i class='text-primary' title='".$r['timestamp']."'>".date("d-M-Y", strtotime($r['timestamp']))."</i><br>
            </li>
                ";
            }
        echo "
        </ul>
        </div>";
    }
    $stmt_cheques_active->bind_param("i", $data);
    $stmt_cheques_active->execute();
    $result_cheques = $stmt_cheques_active->get_result();
    if($result_cheques->num_rows>0)
    {
        echo "
        <div class='m-auto w-100 mt-4 row border border-3 border-secondary noPrint' id='approval_stat'>
        <h4 class='text-primary text-center my-2'>Cheque Signatories</h4>
        <ul class= 'list-group list-group-flush my-2 mx-auto col-sm-12 col-md-6'>";
        while($r = $result_cheques->fetch_assoc())
        {
            if(!is_null($r['signatory']) && $r['signatory'] != "")
            {
                echo "<li class='list-group-item'><ul class= 'list-group list-group-flush my-2 mx-auto col-sm-12 col-md-12 row'>
                    <li class='list-group-item list-group-item-primary border border-primary mb-2 col-sm-12 col-md-12 text-center'>
                    $r[providing_company]
                    </li>
                ";
                    $sigs = explode(",",$r['signatory']);
                    foreach($sigs as $ss)
                    {
                    echo "  
                        <li class='list-group-item list-group-item-primary border border-warning mb-2 col-sm-12 col-md-6 mx-auto'>
                        Name : <i class='text-primary'>".$ss."</i><br>
                        </li>
                        ";
                    }
                echo "</li></ul>";
            }
        }
        echo "
        </ul>
        </div>";
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
?>