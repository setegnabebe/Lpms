<?php
session_start();
include '../connection/connect.php';
include '../common/functions.php';
// include '../connection/connect_fms.php';
///////////////For cluster
if(isset($_GET['current_scale']))
    $_SESSION['current_scale'] = $_GET['current_scale'];
$comp_count=0;
$data = $_GET['data'];
$sql2 = "SELECT * FROM `purchase_order` WHERE `cluster_id`= ?";
$stmt_po = $conn->prepare($sql2);  
$stmt_po->bind_param("i", $data);
$stmt_po->execute();
$result_po = $stmt_po->get_result();
if($result_po->num_rows>0)
    while($r = $result_po->fetch_assoc())
    {
        $purchase_order_id = $r['purchase_order_id'];
        $performa_id = $r['performa_id'];
        $req_type=$r['request_type'];
    }
$person = (isset($_GET['name']))?$_GET['name']:$_SESSION['username'];
$sql2 = "SELECT * FROM `selections` WHERE user = ? AND `cluster_id`= ?";
$stmt_selection = $conn->prepare($sql2);  
$stmt_selection->bind_param("si", $person, $data);
$stmt_selection->execute();
$result_selection = $stmt_selection->get_result();
if($result_selection->num_rows>0)
    while($r = $result_selection->fetch_assoc())
    {
        $person_selections = explode(",",$r['selection']);
    }
    $all_selectsss = "";
    $selections_match = 1;
    $sql = "SELECT * FROM `cluster` Where `id` = ?";
    $stmt_cluster = $conn->prepare($sql);  
    $stmt_cluster->bind_param("i", $data);
    $stmt_cluster->execute();
    $result_cluster = $stmt_cluster->get_result();
    if($result_cluster->num_rows>0)
    $row_temp = $result_cluster->fetch_assoc();
    $active = ($row_temp['status'] != 'canceled');
    $compiled_by = $row_temp['compiled_by'];
    $sql = "SELECT * FROM `selections` Where `cluster_id` = ?";
    $stmt_selection_all = $conn->prepare($sql);  
    $stmt_selection_all->bind_param("i", $data);
    $stmt_selection_all->execute();
    $result_selection_all = $stmt_selection_all->get_result();
    if($result_selection_all->num_rows>0)
    while($row_temp = $result_selection_all->fetch_assoc())
    {
        if($compiled_by != $row_temp['user'])
            if($all_selectsss=="")$all_selectsss = $row_temp["selection"];
            else
            {
                    if($all_selectsss != $row_temp["selection"])
                    {
                        $set[$row_temp['user']] = (isset($set[$row_temp['user']]))?$set[$row_temp['user']]++:0;
                        $selections_match = 0;
                    }
            }
    }
echo "
<h3 class='text-center'>$person Selections</h3>
<div class='noPrint'>";
// echo (strpos($_SESSION['a_type'],'Committee')!==false || $_SESSION['department'] =='Owner' || $_SESSION['department'] =='Procurement')?
// "<button type='button' class='btn btn-outline-primary float-start' onclick='view_performa(this,\"comp_sheet\",\"committee\")' data-bs-toggle='modal' data-bs-target='#view_performa' id='$performa_id' name='$purchase_order_id'>
//     Performa
// </button>":"";
echo 
"<button type='button' class='btn btn-outline-primary float-start' onclick='view_performa(this,\"comp_sheet\",\"committee\")' data-bs-toggle='modal' data-bs-target='#view_performa' id='$performa_id' name='$purchase_order_id'>
Proforma
</button>";
echo "<div class='text-center mx-auto mb-4' style='width: 200px;'>
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
<div id='tbl_cs_view'>
    <div class='card'>
    <div class='card-body'>
    <table class='table text-center'>
        <thead>
            <tr>
            <th rowspan='2'>No</th>
            <th rowspan='2'>Item</th>
            <th rowspan='2'>Requested (For purchase) Qty</th>";
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
                $sql_all = "SELECT DISTINCT `purchase_order_id` FROM `price_information` where `cluster_id`= ?";
                $stmt_all = $conn->prepare($sql_all);  
                $stmt_all->bind_param("i", $data);
                $stmt_all->execute();
                $result_all = $stmt_all->get_result();
                if($result_all->num_rows>0)
                while($row_all = $result_all->fetch_assoc())
                {
                    $count++;
                    $sql_po = "SELECT * FROM `purchase_order` where `purchase_order_id`= ?";
                    if($active)
                    {
                        $stat = "canceled";
                        $sql_po .= " AND `status` != ?";
                        $stmt_po = $conn->prepare($sql_po);  
                        $stmt_po->bind_param("is", $row_all['purchase_order_id'], $stat);
                    }
                    else
                    {
                        $stmt_po = $conn->prepare($sql_po);  
                        $stmt_po->bind_param("i", $row_all['purchase_order_id']);
                    }
                    $stmt_po->execute();
                    $result_po = $stmt_po->get_result();
                    if($result_po->num_rows>0)
                    while($row_po = $result_po->fetch_assoc())
                    {
                        echo "<tr><td>$count</td>";
                        $na_t=str_replace(" ","",$row_po['request_type']);
                        $stmt_request->bind_param("i", $row_po['request_id']);
                        $stmt_request->execute();
                        $result_request = $stmt_request->get_result();
                        if($result_request->num_rows>0)
                        while($row_req = $result_request->fetch_assoc())
                        {
                            $sql_stock = "SELECT * FROM `stock` where `id`= ?";
                            $stmt_stock = $conn->prepare($sql_stock);  
                            $stmt_stock->bind_param("i", $row_req['stock_info']);
                            $stmt_stock->execute();
                            $result_stock = $stmt_stock->get_result();
                            if($result_stock->num_rows>0)
                            while($row_q = $result_stock->fetch_assoc())
                            {
                                $to_purchase = $row_q['for_purchase'];
                            }
                            else $to_purchase = "Stock Data not Found";
                            $cursor = "style = 'cursor: pointer;'";
                            $loc_pos = ($_SESSION['department'] == 'Procurement')?"yes":"";
                            echo "<td><button type='button' title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this,\"$loc_pos\")' >
                            ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button></td>
                            <td id='reqquan_".$row_po['request_id']."'>".$row_req['requested_quantity']." (<span id='purchasequan_".$row_po['request_id']."'>".$to_purchase."</span>) ".$row_req['unit']."</td>";
                            for($i=0;$i<$comp_count;$i++)
                            {
                                if(strpos($companies[$i],"'") !== false && strpos($companies[$i],"\'") === false) $companies[$i] = str_replace("'","'",$companies[$i]);
                                $sql_specific_price = "SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`= ? AND providing_company= ?";
                                $stmt_specific_price = $conn->prepare($sql_specific_price);  
                                $stmt_specific_price->bind_param("iis", $row_po['cluster_id'], $row_all['purchase_order_id'], $companies[$i]);
                                $stmt_specific_price->execute();
                                $result_specific_price = $stmt_specific_price->get_result();
                                if($result_specific_price->num_rows>0)
                                while($row_spec = $result_specific_price->fetch_assoc())
                                {
                                    if($row_spec['quantity'] == $row_req['requested_quantity']) {$btn_type = 'radio'; $btn_name = "";}
                                    else {$btn_type = 'checkbox'; $btn_name = "_half[]";}
            echo "<input id='".$row_po['request_id']."_$i' type='$btn_type' class='itemsss d-none' value='$i' name='Item-".$row_po['request_id']."$btn_name' ".(($req_type=="agreement")?"checked":"").">";
                                    if(isset($person_selections))
                                    {
                                        $selected_price = in_array($row_spec['id'], $person_selections);
                                    }
                                    else
                                    {
                                        $selected_price = $row_spec['selected'];
                                    }
                                    $spec_data = ($row_spec['specification'] == '' || is_null($row_spec['specification']))?"No Specifcation":$row_spec['specification'];
                                    $colors = ($selected_price)?(($req_type=="agreement")?"text-white bg-success border-3 ":"text-success border-success border-3"):"";
                                    $front_bord = ($selected_price)?"border-start-0 ":"";
                                    $back_bord = ($selected_price)?"border-end-0 ":"";
                                    $clicker = (!isset($_GET['viewofcomparision'])&&$req_type!="agreement")?"onclick='clicked(this,\"$count\")'":"";
                                    if($row_spec['specification'] == '' || is_null($row_spec['specification']))
                                        $spec_show = "<td $cursor title='".$row_po['request_id']."_$i' $clicker class='has $colors $front_bord'>".number_format($row_spec['total_price'], 2, ".", ",")."</td>";
                                    else 
                                        $spec_show = "
                                            <td $cursor title='".$row_po['request_id']."_$i' $clicker class='has position-relative $colors $front_bord'>".number_format($row_spec['total_price'], 2, ".", ",")."
                                                <span data-bs-html='true' class='btn btn-sm position-absolute top-0 start-100 translate-middle  badge rounded-pill alert-primary' data-bs-toggle='popover' title='Specification' 
                                                data-bs-content='$spec_data'> <i class='fa fa-info-circle' title='Details'></i></span>
                                            </td>";
                                    echo "
                                    <td  $cursor id='prov_".$row_po['request_id']."_".$i."' title='".$row_po['request_id']."_$i' $clicker class='has $colors $back_bord'>".$row_spec['quantity']."</td>
                                    <td $cursor title='".$row_po['request_id']."_$i' $clicker class='has $colors $front_bord $back_bord'>".number_format($row_spec['price'], 2, ".", ",")."</td>
                                    $spec_show";
                                    // if($row_spec['selected'])
                                    //     echo "<td $cursor id='prov_".$row_po['request_id']."_".$i."' title='".$row_po['request_id']."_$i' class='has text-success border-3 border-end-0 border-success' $clicker>".$row_spec['quantity']."</td>
                                    //         <td $cursor title='".$row_po['request_id']."_$i' class='has text-success border-success border-3 border-end-0 border-start-0' $clicker>".$row_spec['price']."</td>
                                    //         <td $cursor title='".$row_po['request_id']."_$i' class='has text-success border-success border-3 border-start-0' $clicker>".$row_spec['total_price']."</td>";
                                    // else
                                    //     echo "<td $cursor id='prov_".$row_po['request_id']."_".$i."' title='".$row_po['request_id']."_$i' $clicker class='has'>".$row_spec['quantity']."</td>
                                    //         <td $cursor title='".$row_po['request_id']."_$i' $clicker class='has'>".$row_spec['price']."</td>
                                    //         <td $cursor title='".$row_po['request_id']."_$i' $clicker class='has'>".$row_spec['total_price']."</td>";
                                }
                                else {
                                    echo "<td colspan='3' class='bg-secondary'></td>";
                                }
                            }
                        }
                    echo "</tr>";
                    }
                }
                echo"<tr><th colspan='2'>Total Price</th><td> - </td>";
                for($i=0;$i<$comp_count;$i++)
                {
                    
                    $stmt2 = $conn->prepare("SELECT SUM(total_price) AS sum_t FROM `price_information` where `cluster_id`=? AND `providing_company`=? And selected");
                    $stmt2->bind_param("is", $data, $companies[$i]);
                    $stmt2->execute();
                    $stmt2->store_result();
                    $stmt2->bind_result($total_p[$i]);
                    $stmt2->fetch();
                    $stmt2->close();
                    $total_p[$i] = (isset($total_p[$i]))?$total_p[$i]:0;

                    $stmt2 = $conn->prepare("SELECT SUM(after_vat) AS sum_v FROM `price_information` where `cluster_id`=? AND `providing_company`=? AND selected");
                    $stmt2->bind_param("is", $data, $companies[$i]);
                    $stmt2->execute();
                    $stmt2->store_result();
                    $stmt2->bind_result($after_vat[$i]);
                    $stmt2->fetch();
                    $stmt2->close();
                    echo" <td>-</td> <td>-</td> <th id='tp_$i'>".number_format($total_p[$i], 2, ".", ",")."</th> ";
                }
                echo"</tr>
                <tr><th colspan='2'>15% VAT</th><td> - </td>";
                for($i=0;$i<$comp_count;$i++)
                {
                    $vat[$i] = $after_vat[$i]- $total_p[$i];
                    echo" <td>-</td><td>-</td> <th id='vat_$i'>".number_format($vat[$i], 2, ".", ",")."</th> ";
                }
                echo"</tr>
                <tr><th colspan='2'>Grand Total</th><td> - </td>";
                for($i=0;$i<$comp_count;$i++)
                {
                    $gt = (!is_null($after_vat[$i]))?$after_vat[$i]:0;
                    echo" <td> - </td><td> - </td><th id='gt_$i'>".number_format($gt, 2, ".", ",")."</th> ";
                }
    echo "
    </tr>
    </tbody>
    </table>

    </div>";
    $sql_cluster = "SELECT * FROM `cluster` where `id`= ?";
    $stmt_cluster = $conn->prepare($sql_cluster);
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
    $sql_approval = "SELECT * FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
    $stmt_approval = $conn->prepare($sql_approval);
    $stmt_approval->bind_param("si", $_SESSION['username'], $data);
    $stmt_approval->execute();
    $result_approval = $stmt_approval->get_result();
    if($result_approval->num_rows>0)
        while($row_temp = $result_approval->fetch_assoc())
        {
            $status = $row_temp["status"];
        }
echo ((isset($status) && $status!='Reactivated') || isset($_GET['viewofcomparision']))?"":"<div class='text-center'><span class='text-danger text-sm d-block my-2' id='warn_selection'></span>
        <button class='btn btn-outline-success rounded-pill' type='button' onclick='selections_committee(this)' name='newselection' value='$data' >Approve Selection</button><!-- type='button' onclick='reason(this)' data-bs-toggle='modal' data-bs-target='#reason'-->
        <button class='btn btn-outline-danger rounded-pill' type='button' data-bs-toggle='modal' data-bs-target='#reason_committee' onclick='committee_reasons(this)' id='Rejected' name='reject' value='$data' >Reject</button><!-- type='button' onclick='reason(this)' data-bs-toggle='modal' data-bs-target='#reason'-->
    </div>";
echo "</div>";


echo "<div class='card d-none noPrint' id='list_cs_view'>
    <div class='card-body'>
    <ul class= 'list-group list-group-flush'>
    <li class='list-group-item list-group-item-primary mb-4'>
        <ul class= 'list-group list-group-flush'>";
    $sql_companies = "SELECT DISTINCT `providing_company` FROM `price_information` where `cluster_id`= ?";
    $stmt_companies = $conn->prepare($sql_companies);
    $stmt_companies->bind_param("i", $data);
    $stmt_companies->execute();
    $result_companies = $stmt_companies->get_result();
    if($result_companies->num_rows>0)
    while($row = $result_companies->fetch_assoc())
    {
        echo "<h5 class='text-capitalize mb-2'>Company - ".$row['providing_company']."</h5>";$sql_all = "SELECT DISTINCT `purchase_order_id` FROM `price_information` where `cluster_id`='".$data."'";
        $sql_all2 = "SELECT DISTINCT `purchase_order_id` FROM `price_information` where `cluster_id`=? ";
        $stmt_all2 = $conn->prepare($sql_all2);  
        $stmt_all2->bind_param("i", $data);
        $stmt_all2->execute();
        $result_all2 = $stmt_all2->get_result();
        if($result_all2->num_rows>0)
        while($row_all = $result_all2->fetch_assoc())
        {
            $sql_po = "SELECT * FROM `purchase_order` where `purchase_order_id`= ?";
            $stmt_po = $conn->prepare($sql_po);
            $stmt_po->bind_param("i", $row_all['purchase_order_id']);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            if($result_po->num_rows>0)
            while($row_po = $result_po->fetch_assoc())
            {
                $na_t=str_replace(" ","",$row_po['request_type']);
                $sql_req = "SELECT * FROM requests where `request_id`= ?";
                $stmt_req = $conn->prepare($sql_req);
                $stmt_req->bind_param("i", $row_po['request_id']);
                $stmt_req->execute();
                $result_req = $stmt_req->get_result();
                if($result_req->num_rows>0)
                while($row_req = $result_req->fetch_assoc())
                {
                    $sql_stock = "SELECT * FROM `stock` where `id`= ?";
                    $stmt_stock = $conn->prepare($sql_stock);
                    $stmt_stock->bind_param("i", $row_req['stock_info']);
                    $stmt_stock->execute();
                    $result_stock = $stmt_stock->get_result();
                    if($result_stock->num_rows>0)
                    while($row_q = $result_stock->fetch_assoc())
                    {
                        $to_purchase = $row_q['for_purchase'];
                    }
                    $company_prov = $row['providing_company'];
                    if(strpos($company_prov,"'") !== false && strpos($company_prov,"\'") === false) $company_prov = str_replace("'","'",$company_prov);
                    $sql_price = "SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`= ? AND providing_company= ?";
                    $stmt_price = $conn->prepare($sql_price);
                    $stmt_price->bind_param("iis", $row_po['cluster_id'], $row_all['purchase_order_id'], $company_prov);
                    $stmt_price->execute();
                    $result_price = $stmt_price->get_result();
                    if($result_price->num_rows>0)
                    while($row_spec = $result_price->fetch_assoc())
                    {
                        $loc_pos = ($_SESSION['department'] == 'Procurement')?"yes":"";
                        $spec_data = ($row_spec['specification'] == '' || is_null($row_spec['specification']))?"No Specifcation":$row_spec['specification'];
                        if(isset($person_selections))
                        {
                            $selected_price = in_array($row_spec['id'], $person_selections);
                        }
                        else
                        {
                            $selected_price = $row_spec['selected'];
                        }
                        $colors = ($selected_price)?"success ":"light";
                        if($row_spec['specification'] == '' || is_null($row_spec['specification']))
                            $spec_show = "
                            <li class='list-group-item list-group-item-$colors my-2 w-75 mx-auto' type='button' data-bs-toggle='collapse' data-bs-target='#".$row_spec['id']."'> Item - <button type='button'  title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this,\"$loc_pos\")' >
                            ".$row_req['item']." <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>";
                        else
                            $spec_show = "<li class='list-group-item list-group-item-$colors my-2 position-relative w-75 mx-auto'> Item - <button type='button'  title='".$row_req['description']."' value='".$row_req['recieved']."' name='specsfor_".$na_t."_".$row_po['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this,\"$loc_pos\")' >
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
                        echo "<td colspan='3' class='bg-secondary'></td>";
                    }
                }
            }
            $company_prov = $row['providing_company'];
            // if(strpos($company_prov,"'") !== false && strpos($company_prov,"'") === false) $company_prov = str_replace("'","\'",$company_prov);
            $stmt2 = $conn->prepare("SELECT SUM(total_price) AS sum_t FROM `price_information` where `cluster_id`=? AND `providing_company`=?");
            $stmt2->bind_param("is", $data, $company_prov);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($total_p);
            $stmt2->fetch();
            $stmt2->close();
            $sql_cluster = "SELECT * FROM `cluster` where `id`= ?";
            $stmt_cluster = $conn->prepare($sql_cluster);
            $stmt_cluster->bind_param("i", $data);
            $stmt_cluster->execute();
            $result_cluster = $stmt_cluster->get_result();
            $clus_row=$result_cluster->fetch_assoc();
            $sql_limits = "SELECT * FROM `limit_ho` where company= ? ORDER BY id DESC limit 1";
            $stmt_limits = $conn->prepare($sql_limits);
            $stmt_limits->bind_param("s", $clus_row['company']);
            $stmt_limits->execute();
            $result_limits = $stmt_limits->get_result();
            if ($result_limits->num_rows ==0)
            {
                $sql_limits = "SELECT * FROM `limit_ho` where company = ? ORDER BY id DESC limit 1";
                $comps = "Others";
                $stmt_limits = $conn->prepare($sql_limits);
                $stmt_limits->bind_param("s", $comps);
                $stmt_limits->execute();
                $result_limits = $stmt_limits->get_result();
            }
            if($result_limits->num_rows>0)
            {
                $r_new = $result_limits->fetch_assoc();
                $Vat = $r_new['Vat'];
            }
            else $Vat = 0.15;
            $vat = $Vat*($total_p);
            $gt = $vat + $total_p;
        }
        echo "
        <div class='m-auto w-100 my-4'>
            <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>Total Price  -  </i>".number_format($total_p, 2, ".", ",")."</li>
            <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>VAT Price  -  </i>".number_format($vat, 2, ".", ",")."</li>
            <li class='list-group-item list-group-item-warning col-sm-12 mx-auto'><i class='text-primary'>Grand total  -  </i>".number_format($gt, 2, ".", ",")."</li>
        </div>
        ";
        }
echo"</ul>
    </li>
    </ul>
    </div>
</div>
<button class='d-none' type ='button' id='committee_reason' data-bs-toggle='modal' data-bs-target='#reason_committee' onclick='committee_reasons(this)'></button>
";
$sql_approval = "SELECT * FROM `committee_approval` WHERE `cluster_id`= ?";// AND `status` != 'Reactivated'
$stmt_approval = $conn->prepare($sql_approval);
$stmt_approval->bind_param("i", $data);
$stmt_approval->execute();
$result_approval = $stmt_approval->get_result();
if($result_approval->num_rows>0)
{
    $clicker = (isset($_GET['viewofcomparision']))?"title='view_comparision'":"";
    echo "
    <!--<button class='btn btn-primary' type='button' data-bs-toggle='collapse' data-bs-target='#approval_stat' aria-expanded='true' aria-controls='approval_stat'>
    Approval
    </button> collapse-->
    <div class='m-auto w-100 mt-4 row border border-3 border-secondary' id='approval_stat'>
    <h4 class='text-primary text-center my-2'> Approval</h4>
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
                <button type='button' name='".$_GET['data']."' $clicker onclick='compsheet_loader(this,this.name,\"".$r['committee_member']."\")' class='btn btn-outline-primary btn-sm shadow my-2'>
                View Selection
                </button>
                </li>
            ";
        }
    echo "
    </ul>
    </div>";
}
?>