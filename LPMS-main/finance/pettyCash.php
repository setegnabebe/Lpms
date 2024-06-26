<?php 

session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement") header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    // $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str,$title)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>$title</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Petty Cash");
    sideactive("pettycash");
    function batch_select(e)
    {
        let selections = "";
        let indicator = false;
        let all_batch = document.getElementsByClassName("ch_boxes");
        for(let i=0;i<all_batch.length;i++)
        {
            if(all_batch[i].checked) 
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-primary");
                indicator=true;
                selections += (selections =="")?all_batch[i].value:","+all_batch[i].value;
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("batch_prepare").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
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
        <h2>Prepare Cheque</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Prepare Cheque</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_prepare' id='batch_prepare'>Prepare Cheque</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    
    <?php
        $str="";
        $sql_clus = "SELECT *,SUM(P_i.total_price) AS price,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where P.status = 'Petty Cash Approved' AND selected AND finance_company = ? Group by providing_company,P.cluster_id";
        $stmt_pettycash_fetch = $conn->prepare($sql_clus);  
        $stmt_pettycash_fetch -> bind_param("s", $_SESSION['company']);
        $stmt_pettycash_fetch -> execute();
        $result_pettycash_fetch = $stmt_pettycash_fetch -> get_result();
        if($result_pettycash_fetch->num_rows>0)
        while($r_clus = $result_pettycash_fetch->fetch_assoc())
        {
            $pos = "";
            $sql_pos = "SELECT *,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where id = ?";
            $stmt_pos_custom = $conn->prepare($sql_pos);  
            $stmt_pos_custom -> bind_param("i", $r_clus['id']);
            $stmt_pos_custom -> execute();
            $result_pos_custom = $stmt_pos_custom -> get_result();
            $r_pos = $result_pos_custom->fetch_assoc();
            $pos .= ($pos == "")?$r_pos['purchase_order_id']:":-:".$r_pos['purchase_order_id'];
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
            $result_request = $stmt_request->get_result();
            $row_req = $result_request->fetch_assoc();
            $printpage = "
                <form method='GET' action='../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['cluster_id'].":|:all'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>";
                $str .= ($r_clus['status'] == "Finance Approved")?"
                <span class='small text-secondary float-start'>
                <input value='".$r_clus['cluster_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                </span>":"";
                $str .= $r_clus['providing_company'].(($r_clus['status'] == 'Petty Cash Approved')?" <i class='text-primary fw-bold text-sm'>Petty Cash</i>":"")."
                $printpage
                </h3>
                <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Department : </span>".$row_req['department']."</li>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Company : </span>".$row_req['company']."</li>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Total Price : </span>".number_format($price, 2, ".", ",")."</li>
                        <button type='button' name='".$r_clus['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                        <li class='mt-3'>
                            <button class='btn btn-sm  btn-outline-success' type='button' onclick = 'prompt_confirmation(this)' value='".$r_clus['cluster_id']."::-::".$pos."' name='petty_cash'>Petty Cash Ready</button>
                        </li>
                    </ul>
                </form>
                </div>
            </div>
                ";
        }
        
        $sql_clus = "SELECT *,P.request_id as request_id FROM purchase_order AS P Inner Join requests AS R on P.request_id = R.request_id WHERE P.status = 'Petty Cash Approved' AND P.finance_company = ? and cluster_id IS NULL";
        $stmt_pettycash_give = $conn->prepare($sql_clus);  
        $stmt_pettycash_give -> bind_param("s", $_SESSION['company']);
        $stmt_pettycash_give -> execute();
        $result_pettycash_give = $stmt_pettycash_give -> get_result();
        // $sql_clus = "SELECT * FROM `cluster` where `status`='Finance Approved Petty Cash' AND finance_company = '".$_SESSION['company']."'";
        if($result_pettycash_give -> num_rows>0)
        while($row = $result_pettycash_give -> fetch_assoc())
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
                
            if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
            $str.="
            <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4'>
                <div class='box shadow'>
                    <h3 class='row'>
                        <span class='text-capitalize col-12'>".$name."
                        <!--<span class='small text-secondary float-start'>
                            <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span>--></span>
                        <i class='text-primary fw-bold text-sm'>No proforma</i>
                    </h3>
                    <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                        ".$row['item']."</button></li>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>".str_replace("."," ", $row['customer'])."</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='mt-3'>
                        <button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../finance\")' type='button' value='".$row['purchase_order_id']."' name='petty_cash_only'>Petty Cash Ready</button>
                        </li>
                        ";
                    $str .="
                    </ul>
                    </form>
                </div>
            </div>";
        }
        $title = "POs waiting Petty Cash";
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Petty cash</h3>
                </div>
            </div>";
        else
            divcreate($str,$title);
    ?>
    
</div>
</div>
<?php include '../footer.php';?>