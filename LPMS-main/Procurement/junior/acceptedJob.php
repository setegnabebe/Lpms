
<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
// include "../../common/functions.php";
function divcreate($str)
{
    echo "
    <div class='pricing'>
        <div class='row'>
            <div class='section-title text-center py-2 alert-primary rounded col-10'>
                <h6 class='text-white'>All Purchase Order Pending Proforma</h6> 
            </div>
            <div class ='col-2'>
                <button data-bs-toggle='modal' data-bs-target='#fleetModal' class='float-end btn btn-sm btn-outline-primary' id='fleet_request_btn' type='button' onclick='fleet_request(this,\"performa\")'>Request Vehicle</button>
            </div>
        </div>
        <div class='row'>
            $str
        </div>
    </div>
    ";
}
?>
<script>
    set_title("LPMS | Complete Task");
    sideactive("accepted");
    function batch_select(e)
    {
        let selections = "";
        let selections_print = "";
        let to_replace = "";
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
                let avl = document.getElementById("print_"+all_batch[i].value).value;
                selections_print += (selections_print =="")? avl:","+avl;
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("batch_complete").value = selections;
        document.getElementById("batch_print").value = selections_print;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
    }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7 bg-light"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Finished Quota Collection</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Finished Quota Collection</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid px-4">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 70%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_complete' id='batch_complete'>Complete Jobs</button>
        </div>
    </form>
    <form method="GET" action="../../requests/print.php">
        <div class='mb-3'>
            <button type='submit' class='btn btn-xl btn-outline-secondary shadow mt-3' name='batch_print' id='batch_print'><i class='text-dark fas fa-print'></i></button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <?php
    $sql = "SELECT * FROM `purchase_order` WHERE `purchase_officer` = ? AND (status='Accepted' OR status='Complete') ORDER BY timestamp,priority DESC";
    $stmt_accepted_jobs = $conn->prepare($sql);  
    $stmt_accepted_jobs->bind_param("s", $_SESSION['username']);
    $stmt_accepted_jobs->execute();
    $result_accepted_jobs = $stmt_accepted_jobs->get_result();
    $str="";
    if($result_accepted_jobs->num_rows>0)
        while($row = $result_accepted_jobs->fetch_assoc())
        {
            $type=$row['request_type'];
            $na_t=str_replace(" ","",$type);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows > 0)
                while($row2 = $result_request -> fetch_assoc())
                {
                    if($row['request_type']=="Consumer Goods"){
                        if($row2['request_for'] == 0)
                        {
                            $stmt_project->bind_param("i", $row2['request_for']);
                            $stmt_project->execute();
                            $result3 = $stmt_project->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else
                        {
                            $id = explode("|",$row2['request_for'])[0];
                            $stmt_project_pms->bind_param("i", $id);
                            $stmt_project_pms->execute();
                            $result3 = $stmt_project_pms->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                    }
                    else if($row['request_type']=="Spare and Lubricant"){
                        $stmt_description->bind_param("i", $row2['request_for']);
                        $stmt_description->execute();
                        $result3 = $stmt_description->get_result();
                        $res=($result3->num_rows>0)?true:false;  
                    }
                    else if($row['request_type']=="Tyre and Battery")
                    {
                        $name="Plate Number - ".$row2['request_for'];
                        $res=false;
                    }
                    else 
                    {
                        $res=false;
                        $name="Item - ".$row2['item'];
                    }

                    if($res)
                        while($row3 = $result3->fetch_assoc())
                        {
                            if($row['request_type']=="Consumer Goods")
                            {
                                $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                            }
                            else if($row['request_type']=="Spare and Lubricant")
                                $name="Job - ".$row3['description'];
                        }
                    $stmt_stock -> bind_param("i", $row2['stock_info']);
                    $stmt_stock -> execute();
                    $result_stock = $stmt_stock -> get_result();
                    if($result_stock -> num_rows > 0)
                        while($r = $result_stock -> fetch_assoc())
                        {
                            $instock = $r['in-stock'];
                            $forpurchase = $r['for_purchase'];
                        }
                    if($row['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                    if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else $prio="";
                    $printpage = "
                    <form method='GET' action='../../requests/print.php' class='float-end'>
                        <button type='submit' class='btn btn-outline-secondary border-0' id='print_$row[purchase_order_id]' name='print' value='".$row['request_id'].":|:$type'>
                        <i class='text-dark fas fa-print'></i>
                        </button>
                    </form>";
                    $uname =str_replace("."," ",$row2['customer']);
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3 class='text-capitalize'>
                        <span class='small text-secondary float-start'>".(($row['status'] == 'Accepted')?"
                        <input value='".$row['purchase_order_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>":"")."
                        $prio</span>
                        ".$name."
                        $printpage
                        <span class='small text-secondary d-block mt-2'>$type</span></h3>
                        <ul>
                        <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                        <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                        <!-- <li class='text-start'><span class='fw-bold'>Requested Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>-->
                        <li class='text-start'><span class='fw-bold'>Purchase Quantity : </span>".$forpurchase." ".$row2['unit']."</li>
                        <form method='GET' action='allphp.php'>
                        <!--<li class='text-start'><span class='fw-bold'>Date Requested : </span>".$row2['date_requested']."</li>
                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>-->
                            <li class='text-end'>";
                            if($row['status']!='Complete')
                                $str.= "<button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-primary btn-sm shadow ms-2' name='complete' value='".$row['purchase_order_id']."'>Complete</button>
                                <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".(isset($row['purchase_requisition'])?$row['purchase_requisition']:"")."' >Chat <i class='text-white text-white fa fa-comment'></i></button>
                                </li>";

                            else 
                                $str.= "</li><li class='text-start'><i class='fw-bold text-primary text-center'>Waiting for Comfirmation</i></li>";
                            $str.= "
                        </form>
                            </ul>
                        </div>
                    </div>
                    ";
                    }
                }
                if($str !='')
                    divcreate($str);
                else
                    echo "<div class='py-5 pricing'>
                                <div class='section-title text-center py-2  alert-primary rounded'>
                                    <h3 class='mt-4'>No Requests Accepted</h3>
                                </div>
                            </div>";

$sql = "SELECT * FROM `purchase_order` WHERE `purchase_officer`= ? AND status='Accepted'";
$stmt_accepted_jobs_remaining = $conn->prepare($sql);
$stmt_accepted_jobs_remaining -> bind_param("s", $_SESSION['username']);
$stmt_accepted_jobs_remaining -> execute();
$result_accepted_jobs_remaining = $stmt_accepted_jobs_remaining->get_result();
if($result_accepted_jobs_remaining -> num_rows==0)
echo "<script>document.getElementById('fleet_request_btn').setAttribute('disabled',true);</script>";
        ?>
</div>

</div>
<script> 
    //  window.print();
</script>
<?php include '../../footer.php';?>
