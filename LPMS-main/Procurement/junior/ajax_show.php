<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include "../../common/functions.php";
    function divcreate($str,$n)
    {
        echo "
        <div class='card'>
        <div class='card-header'>
            <h4 class='text-center'>Details For $n Request</h4>
        </div>
        <div class='card-body'>
            $str
        </div>
        <button type='button' name='".$_GET['cl_id']."' onclick='compsheet_loader(this)' class='form-control form-control-small btn-outline-primary shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet<i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
        </div>
        ";
    }
    $str='';
    $i=1;
    $name = str_replace("\and","&",$_GET['prov_comp']);
        $sql = "SELECT *,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from
        `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where selected and
        (P.status = 'Payment Processed' OR P.status = 'Collected-not-comfirmed') AND collector = ? AND P.procurement_company=? AND P.cluster_id = ? AND providing_company = ?";
        $stmt_collection_waiting = $conn->prepare($sql);  
        $stmt_collection_waiting -> bind_param("ssis", $_SESSION['username'], $_SESSION['company'], $_GET['cl_id'], $name);
        $stmt_collection_waiting -> execute();
        $result_collection_waiting = $stmt_collection_waiting->get_result();
        if($result_collection_waiting->num_rows>0)
        while($row = $result_collection_waiting->fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $sql_price = "SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`=? AND selected";
            $stmt_selected_price = $conn->prepare($sql_price);  
            $stmt_selected_price -> bind_param("ii", $row['cluster_id'], $row['purchase_order_id']);
            $stmt_selected_price -> execute();
            $result_selected_price = $stmt_selected_price->get_result();
            if($result_selected_price->num_rows>0)
                while($row_price = $result_selected_price->fetch_assoc())
                {
                    $u_price = $row_price['price'];
                    $total_price = $row_price['total_price'];
                    
                    $stmt_cluster -> bind_param("i", $row['cluster_id']);
                    $stmt_cluster -> execute();
                    $result_cluster = $stmt_cluster -> get_result();
                    $clus_row = $result_cluster -> fetch_assoc();
                    $sql_limits = "SELECT * FROM `limit_ho` where company= ? ORDER BY id DESC limit 1";
                    $stmt_limits = $conn->prepare($sql_limits);
                    $stmt_limits->bind_param("s", $clus_row['company']);
                    $stmt_limits->execute();
                    $result_limits = $stmt_limits->get_result();
                    if ($result_limits->num_rows ==0)
                    {
                        $comps = "Others";
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
                    $price_VAT = round((($row_price['total_price']*$Vat)+$row_price['total_price']),3);
                }
                $str .= "<ul class= 'list-group list-group-flush'>";
                $stmt_request -> bind_param("i", $row['request_id']);
                $stmt_request -> execute();
                $result_request = $stmt_request -> get_result();
                if($result_request->num_rows>0)
                    while($row2 = $result_request->fetch_assoc())
                    {
                        $stmt_account -> bind_param("s", $row2['customer']);
                        $stmt_account -> execute();
                        $result_account = $stmt_account -> get_result();
                        if($result_account->num_rows>0)
                            while($row_dep = $result_account->fetch_assoc())
                            {
                                $dep = $row_dep['department'];
                            }
                            
                        $form_req = date("d-M-Y", strtotime($row2['date_requested']));
                        $form_need = date("d-M-Y", strtotime($row2['date_needed_by']));
                        $str .="
                                <div class='row'>
                                <li data-bs-toggle='collapse' data-bs-target='#content$i' role='button' aria-expanded='false' aria-controls='content$i' class='col-11 list-group-item list-group-item-success mb-3 text-capitalize'>
                                    <span class='text-primary text-capitalize'>Item $i - </span>".$row2['item'];
                                    $str .=($row['status']=='Collected' || $row['status']=='Collected-not-comfirmed')?" <span class='fw-bold'><i class='fa fa-check-circle text-primary'></i> Item Collected</span>":"";
                                    $str .="
                                </li>
                            <li class='col-1 list-group-item border-0 mb-3 text-capitalize'>
                                <button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)'>
                                <i class='fas fa-external-link-alt'></i></button></li>
                                <div class='row m-auto w-100'>
                                <li class='list-group-item list-group-item-primary mb-4 m-auto collapse' id='content$i'>
                                    <ul class= 'list-group list-group-flush'>
                                        <li class='list-group-item list-group-item-light'><i class='text-primary'>Quantity  -  </i>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                        <div class='row m-auto w-100'>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Unit Price  -  </i>".number_format($u_price, 2, ".", ",")." Birr</li>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Total Price  -  </i>".number_format($total_price, 2, ".", ",")." Birr</li>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Price With VAT -  </i>".number_format($price_VAT, 2, ".", ",")." Birr</li>
                                        </div>";
                                        $str .=($row['status']!='Collected' && $row['status']!='Collected-not-comfirmed')?
                                        "<li class='list-group-item list-group-item-light text-center'><button class='btn btn-outline-primary list-group-item-primary' name='collect_individual_item' value ='".$row['purchase_order_id']."'>Item Collected</button></li>
                                        <li class='list-group-item list-group-item-light text-center'><button class='btn btn-outline-danger list-group-item-danger' type='button' onclick = 'prompt_confirmation(this)' name='not_found' value='".$row['purchase_order_id']."'>Not Found</button>":
                                        "";
                                    $str .="
                                    </ul>
                                </li>
                                </div>";
                        // <div class='row m-auto w-100'>
                        //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Requested By - </i>".$row2['customer']."</li>
                        //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Requesting Department - </i>$dep</li>
                        // </div>
                        // <div class='row m-auto w-100'>
                        //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Requested  -  </i>$form_req</li>
                        //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Date Needed By  -  </i>$form_need</li>
                        // </div>
                        
                    }
                    $str .= "</ul>";
                    $i++;
        }

    //     <div class='row m-auto w-100'>
    //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Purchase Officer - </i>".$row['purchase_officer']."</li>
    //     <li class='list-group-item list-group-item-light col-sm-12 col-md-6'><i class='text-primary'>Assigned By - </i>".$row['assigned_by']."</li>
    // </div>
    divcreate($str,$name);
}
else
{
    echo $go_home;
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