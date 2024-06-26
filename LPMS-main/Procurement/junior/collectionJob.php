<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>All POs Waiting Collection</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Collection of Task");
    sideactive("collection");
    var element,db,prov_comp;
    function loader_collect(e)
    {
        let temp = e.id.replace("view_","");
        let data = temp.split('_');
        element = data[0];
        prov_comp = data[1];
        prov_comp = prov_comp.replace("&","\\and");
        db = data[1];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("itemsview_body").innerHTML=this.responseText;
        }
        req.open("GET", "Ajax_show.php?cl_id="+element+"&prov_comp="+prov_comp);
        req.send();
    }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Collection of Items</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Collection of Items</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<form method='GET' action='allphp.php'>
    <?php
        $str="";
        $sql_collections_batch = "SELECT *,SUM(P_i.total_price) AS price,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from
         `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where selected and
          (P.status = 'Payment Processed' OR P.status = 'Collected-not-comfirmed') AND collector = ? AND P.procurement_company = ?
           Group by providing_company,P.cluster_id";
        $stmt_collections_batch = $conn->prepare($sql_collections_batch);
        $stmt_collections_batch -> bind_param("ss",$_SESSION['username'] ,$_SESSION['company']);
        $stmt_collections_batch -> execute();
        $result_collections_batch = $stmt_collections_batch -> get_result();
        if($result_collections_batch -> num_rows>0)
        while($r_clus = $result_collections_batch -> fetch_assoc())
        {
            $stmt_cluster -> bind_param("i", $r_clus['cluster_id']);
            $stmt_cluster -> execute();
            $result_cluster = $stmt_cluster->get_result();
            $clus_row=$result_cluster->fetch_assoc();
            $stmt_limit -> bind_param("s", $clus_row['company']);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
            if ($result_limit->num_rows ==0)
            {
                $other = "Others";
                $stmt_limit -> bind_param("s", $other);
                $stmt_limit -> execute();
                $result_limit = $stmt_limit->get_result();
            }
            $row_limit = $result_limit->fetch_assoc();
            $price = ($row_limit['Vat']*$r_clus['price'])+$r_clus['price'];
            $stmt_request -> bind_param("i", $r_clus['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row_req = $result_request -> fetch_assoc();
            $r_type = $r_clus['request_type'];
            $na_t=str_replace(" ","",$r_type);
            $str.= "
                <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                    <div class='box'>
                        <h3>Company - ".$r_clus['providing_company']."
                        </h3>
                        <ul>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Requesting Department : </span>".$row_req['department']."</li>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Requesting Company : </span>".$row_req['company']."</li>
                        <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($price, 2, ".", ",")."</li>";
                    $str.= ($r_clus['status']=='Collected-not-comfirmed')?"<li><i class='text-primary fw-bold'>Waiting for Comfirmation</i></li>":"";//</li>
                    $str.= "<li class='row mx-auto'>
                            <button class='col-sm-9 mb-2 col-md-5 btn btn-outline-primary btn-sm shadow me-2' type='button' id='view_".$r_clus['cluster_id']."_".$r_clus['providing_company']."' onclick='loader_collect(this)' data-bs-toggle='modal' data-bs-target='#itemsview'>View Items</button>";
                    $str.= ($r_clus['status']!='Collected-not-comfirmed')?"<button type='button' onclick = 'prompt_confirmation(this)' class='col-sm-9 mb-2 col-md-5 btn btn-outline-success btn-sm shadow' name='collected' value= '".$r_clus['cluster_id']."::-::".$r_clus['providing_company']."'>All Collected</button>":"";
                    $str.= "<button type='button' class='btn btn-outline-primary mb-2 btn-sm shadow col-sm-5 me-2' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['cluster_id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                    </li></ul>
                    </div>
                </div>
                ";
        }
        $sql_collections = "SELECT *,P.request_id as request_id FROM purchase_order AS P Inner Join requests
         AS R on P.request_id = R.request_id WHERE (P.status = 'Payment Processed' 
         OR P.status = 'Collected-not-comfirmed') AND collector = ?
          AND P.procurement_company = ? and P.cluster_id is NULL";
        $stmt_collections = $conn->prepare($sql_collections);
        $stmt_collections -> bind_param("ss",$_SESSION['username'] ,$_SESSION['company']);
        $stmt_collections -> execute();
        $result_collections = $stmt_collections -> get_result();
        // $sql_clus = "SELECT * FROM `cluster` where `status`='Finance Approved Petty Cash' AND finance_company = '".$_SESSION['company']."'";
        if($result_collections -> num_rows > 0)
        while($row = $result_collections -> fetch_assoc())
        {
            $type = $row['request_type'];
            $na_t=str_replace(" ","",$type);
            if($type=="Consumer Goods"){
                if($row['request_for'] == 0)
                {
                    $stmt_project->bind_param("i", $row['request_for']);
                    $stmt_project->execute();
                    $result3 = $stmt_project->get_result();
                    $res=($result3->num_rows>0)?true:false;
                }
                else
                {
                    $idd = explode("|",$row['request_for'])[0];
                    $stmt_project_pms->bind_param("i", $idd);
                    $stmt_project_pms->execute();
                    $result3 = $stmt_project_pms->get_result();
                    $res=($result3->num_rows>0)?true:false;
                }
            }
            else if($type=="Spare and Lubricant"){
                $stmt_description->bind_param("i", $row['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;
            }
            else if($type=="Tyre and Battery")
            {
                $name=$row['request_for'];
                $res=false;
            }
            else 
            {
                $res=false;
                $name=$row['item'];
            }
            if($res)
                while($row3 = $result3->fetch_assoc())
                {
                    if($type=="Consumer Goods")
                    {
                        $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                    }
                    else if($type=="Spare and Lubricant")
                        {
                            $name=$row3['description'];
                        }
                }
                $uname =str_replace("."," ",$row['customer']);
            if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
            $str.="
            <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4'>
                <div class='box shadow'>
                    <h3 class='row'>
                        <span class='text-capitalize col-12'>".$name."
                        <!--<span class='small text-secondary float-start'>
                            <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span>--></span>
                        <i class='text-primary fw-bold text-sm'>No Proforma</i>
                    </h3>
                    <ul>
                        <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                        ".$row['item']."</button></li>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        ";
                        $str .=($row['status']!='Collected' && $row['status']!='Collected-not-comfirmed')?
                        "<li class='mt-3'>
                        <button class='btn btn-outline-primary list-group-item-primary' onclick = 'prompt_confirmation(this)' name='collect_item' value ='".$row['purchase_order_id']."'>Item Collected</button>
                        <button class='btn btn-outline-danger list-group-item-danger' type='button' onclick = 'prompt_confirmation(this)' name='not_found' value='".$row['purchase_order_id']."'>Not Found</button>
                        </li>":"<li class='mt-3'><i class='text-primary fw-bold'>Waiting for Comfirmation</i></li>";
                    $str .="<button type='button' class='btn btn-outline-primary mb-2 btn-sm shadow col-sm-5 me-2' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                    </ul>
                </div>
            </div>";
        }
        if($str !='')
            divcreate($str);
        else
            echo "<div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests to be Collected</h3>
                        </div>
                    </div>";
    ?>
</form>
<form method="GET" action="allphp.php">
<div class="modal fade" id="itemsview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemsview_body">
                    <!-- Company And Items Form -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id='close_modal_1'>Close</button>
                </div>
        </div>
    </div>
</div> 
</form>
    
</div>
<?php include '../../footer.php';?>
