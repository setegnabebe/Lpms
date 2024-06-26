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
        <div class='section-title text-center py-2 alert-primary rounded'>
            <h6 class='text-white'>All Purchase Order Assigned by Supervisor</h6>
            <button data-bs-toggle='modal' data-bs-target='#fleetModal' class='d-none' id='fleet_request_btn' type='button' onclick='fleet_request(this,\"performa\")'>Request Vehicle</button>
        </div>
        <div class='row'>
            $str
        </div>
    </div>
    ";
}
function selector()
{
    include "../../connection/connect.php";
    $sql_po_pending = "SELECT * FROM `purchase_order` WHERE `status`='pending'";
    $stmt_po_pending = $conn -> prepare($sql_po_pending);
    $stmt_po_pending -> execute();
    $result_po_pending = $stmt_po_pending -> get_result();
    if($result_po_pending -> num_rows>0)
        while($row = $result_po_pending -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            echo "<script>document.getElementById('".$na_t."_".$row['request_id']."').value='".$row["purchase_officer"]."'</script>";
        }
}
?>
<script>
    set_title("LPMS | Accept Tasks");
    sideactive("assigned");
    function batch_select(e)
    {
        let selections = "";
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
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("batch_start").value = selections;
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
        <h2>Accept Quota Collection</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Accept Quota Collection</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid px-4">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow" style="top: 80%; left: 85%;">
    <form method="GET" action="allphp.php">
        <div class='mb-3'>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_start' id='batch_start'>Start Jobs</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
<form method="GET" action="allphp.php">
    <?php
    $sql_assigned_tasks = "SELECT * FROM `purchase_order` WHERE `purchase_officer`= ? AND status='pending' ORDER BY priority DESC";
    $stmt_assigned_tasks = $conn->prepare($sql_assigned_tasks);
    $stmt_assigned_tasks -> bind_param("s",$_SESSION['username']);
    $stmt_assigned_tasks -> execute();
    $result_assigned_tasks = $stmt_assigned_tasks -> get_result();
    $str="";
    if($result_assigned_tasks -> num_rows>0)
        while($row = $result_assigned_tasks -> fetch_assoc())
        {
            $type=$row['request_type'];
            $na_t=str_replace(" ","",$type);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request->num_rows>0)
                while($row2 = $result_request->fetch_assoc())
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
                        if($row['request_type']=="Spare and Lubricant" && $row2['request_for']==0) $name = "General Request";
                        if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                        else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                        else $prio="";
                        $uname =str_replace("."," ",$row2['customer']);
                        $str.= "
                        <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                            <div class='box'>
                                <h3 class='text-capitalize row'>
                                    <span class='col-10'>
                                    <input value='".$row['purchase_order_id']."' class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                                    ".$name."
                                        <span class='small text-secondary d-block mt-2'>$type</span>
                                    </span>
                                <span class='small text-secondary d-block col-2'>$prio</span>
                                </h3>
                            <ul>
                                <li class='text-start'><span class='fw-bold'>Item : </span>".$row2['item']."</li>
                                <li class='text-start' title='".$row['timestamp']."'><span class='fw-bold'>Date Assigned : </span>".date("d-M-Y", strtotime($row['timestamp']))."</li>
                                <!--<li class='text-start'><span class='fw-bold'>Priority : </span>".$row2['item']."</li>
                                <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li class='text-start'><span class='fw-bold'>Date Requested : </span>".$row2['date_requested']."</li>
                                <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>-->
                                <li class='text-end'>
                                <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-primary btn-sm shadow' name='accept' value='".$row['purchase_order_id']."'>Start Proccessing</button>
                                <button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-primary fa fa-comment'></i></button>

                                </li>
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
                            <h3 class='mt-4'>No Requests Assigned to you</h3>
                            <button data-bs-toggle='modal' data-bs-target='#fleetModal' class='d-none' id='fleet_request_btn' type='button' onclick='fleet_request(this,\"performa\")'>Request Vehicle</button>
                        </div>
                    </div>";
        selector();
        ?>
</form>
</div>
</div>
<?php include '../../footer.php';?>
