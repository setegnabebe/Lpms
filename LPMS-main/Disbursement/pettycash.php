<?php 

session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Director" && strpos($_SESSION["a_type"],"PettyCashApprover") === false) header("Location: ../");
    $string_inc = "../".$_SESSION['loc'].'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>POs waiting Review for Finance</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Approve Petty Cash");
    sideactive("petty_cash");
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
        document.getElementById("batch_review").value = selections;
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
        <h2>Process Payment for Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Process Payment for Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_review' id='batch_review'>Approve Petty Cash</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <?php
        $str="";
        $sql_petty_cash = "SELECT *,SUM(P_i.after_vat) AS price,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where (P.status = 'Finance Approved Petty Cash') AND selected AND P.finance_company = ? Group by providing_company,P.cluster_id";
        $stmt_petty_cash_companies = $conn->prepare($sql_petty_cash);  
        $stmt_petty_cash_companies->bind_param("s", $_SESSION['company']);
        $stmt_petty_cash_companies->execute();
        $result_petty_cash = $stmt_petty_cash_companies->get_result();
        if($result_petty_cash -> num_rows>0)
        while($r_clus = $result_petty_cash -> fetch_assoc())
        {
            $stmt_cluster -> bind_param("i", $r_clus['cluster_id']);
            $stmt_cluster -> execute();
            $result_cluster = $stmt_cluster -> get_result();
            $clus_row=$result_cluster -> fetch_assoc();
            $price = $r_clus['price'];
            $stmt_request -> bind_param("i", $r_clus['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row_req = $result_request -> fetch_assoc();
            $printpage = "
                <form method='GET' action='../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['cluster_id'].":|:all'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>
                <span class='small text-secondary float-start'>
                <input value='".$r_clus['cluster_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                </span>".$r_clus['providing_company']."
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
                        <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-success' value = '$r_clus[cluster_id]::-::$r_clus[providing_company]' name='process_pettycash'>Approve Petty Cash</button>
                        </li>
                    </ul>
                </form>
                </div>
            </div>
                ";
        }
        $sql_petty_cash = "SELECT *,P.request_id as request_id FROM purchase_order AS P Inner Join requests AS R on P.request_id = R.request_id WHERE P.status = 'Petty Cash' AND P.finance_company = ?";
        // $sql_clus = "SELECT * FROM `cluster` where `status`='Finance Approved Petty Cash' AND finance_company = '".$_SESSION['company']."'";
        $stmt_petty_cash = $conn->prepare($sql_petty_cash);  
        $stmt_petty_cash->bind_param("s", $_SESSION['company']);
        $stmt_petty_cash->execute();
        $result_petty_cash = $stmt_petty_cash->get_result();
        if($result_petty_cash->num_rows>0)
        while($row = $result_petty_cash->fetch_assoc())
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
                        <i class='text-primary fw-bold text-sm'>No proforma</i>
                    </h3>
                    <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                        ".$row['item']."</button></li>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='mt-3'>
                            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-primary' name='petty_cash_approval' value='".$row['purchase_order_id']."'>Approve Petty Cash</button> 
                        </li>
                        ";
                    $str .="
                    </ul>
                    </form>
                </div>
            </div>";
        }
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Payment</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>
    
</div>
</div>
<?php include '../footer.php';?>