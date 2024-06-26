<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str,$title)
{
    $id=str_replace(" ","",$title);
    echo "
        <div class='pricing' id='$id'>
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
    set_title("LPMS | Recieve Items");
    sideactive("purchased");
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
                all_batch[i].parentElement.parentElement.parentElement.classList.add("border");
                all_batch[i].parentElement.parentElement.parentElement.classList.add("border-2");
                all_batch[i].parentElement.parentElement.parentElement.classList.add("border-primary");
                indicator=true;
                selections += (selections =="")?all_batch[i].value:","+all_batch[i].value;
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("batch_confirm").value = selections;
        // document.getElementById("batch_reject").value = selections;
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
        <h2>Items collected</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Items collected</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 50%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' name='batch_confirm_handover' id='batch_confirm' class='btn btn-outline-primary mt-3'>Confirm Recieved</button>
            <!-- <button type='button' onclick='prop_reason_batch(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-success shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button>
            <button type='button' onclick='prop_reason_batch(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-danger shadow mt-3' name='batch_reject' id='batch_reject'>Reject</button> -->
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
<!-- <div class='text-center mx-auto mb-4' style="width: 500px;">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="btn nav-link active" href="#ItemsPurchased">
                Purchased
            </a>
        </li>
        <li class="nav-item">
            <a class="btn nav-link active" href="#FromStock">
                From Stock
            </a>
        </li>
    </ul>
</div> -->
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql = "SELECT * FROM `purchase_order` where (`status` = 'Collected-not-comfirmed' OR `status` = 'Collected' OR `status` = 'In-stock') AND property_company = ? ORDER BY `timestamp`,`priority` DESC";
        $stmt_purchased_items = $conn->prepare($sql);
        $stmt_purchased_items -> bind_param("s", $_SESSION['company']);
        $stmt_purchased_items -> execute();
        $result_purchased_items = $stmt_purchased_items -> get_result();
        if($result_purchased_items -> num_rows > 0)
        while($row = $result_purchased_items -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows>0)
                while($row2 = $result_request -> fetch_assoc())
                {
                    if($row['request_type']=="Consumer Goods")
                    {
                        $id=$row2['request_for'];
                        if($row2['request_for'] == 0)
                        {
                            $stmt_project->bind_param("i", $row2['request_for']);
                            $stmt_project->execute();
                            $result3 = $stmt_project->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else
                        {
                            $idd = explode("|",$row2['request_for'])[0];
                            $stmt_project_pms->bind_param("i", $idd);
                            $stmt_project_pms->execute();
                            $result3 = $stmt_project_pms->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                    }
                    else if($row['request_type']=="Spare and Lubricant")
                    {
                        $id=$row2['request_for'];
                        $stmt_description->bind_param("i", $row2['request_for']);
                        $stmt_description->execute();
                        $result3 = $stmt_description->get_result();
                        $res=($result3->num_rows>0)?true:false;  
                    }
                    else if($row['request_type']=="Tyre and Battery")
                    {
                        $id=$row2['request_for'];
                        $name=$row2['request_for'];
                        $res=false;
                    }
                    else 
                    {
                        $id=$row2['request_id'];
                        $res=false;
                        $name=$row2['item'];
                    }
    
                    if($res)
                        while($row3 = $result3->fetch_assoc())
                        {
                            if($row['request_type']=="Consumer Goods")
                            {
                                $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                            }
                            else if($row['request_type']=="Spare and Lubricant")
                                $name=$row3['description'];
                        }
                        if($row['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                        $str.= "
                        <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                            <div class='box'>
                            <h3 class='text-capitalize'>";
                            if($row['status'] == 'Collected-not-comfirmed' && $row2['flag'] != 0)
                            $str .= "
                            <span class='small text-secondary float-start'>
                            <input value='".$row['purchase_order_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                            </span>";
                            $str .= "
                            <span class='small text-secondary float-start'>
                            <button type='button' value='".$row['request_id']."' class='btn btn-sm' data-bs-toggle='modal' data-bs-target='#company_select' onclick='comp_selector(this,\"".$row['company']."\")'><i class='fa fa-share'></i></button>
                            </span>";
                            $str .= $name."</h3>
                            <ul>
                                <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                                <li class='text-start'><span class='fw-bold'>Company : </span>".$row2['company']."</li>
                                <li class='text-start'><span class='fw-bold'>Requsted Item : </span><span id='pur_$row2[request_id]'>".$row2['item']."</span></li>
                                <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                ".((!is_null($row2['purchased_amount']))?"<li class='text-start'><span class='fw-bold'>Purchased Quantity : </span>".$row2['purchased_amount']." ".$row2['unit']."</li>":"").
                                "<li class='text-end'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                                <li>";
                                if($row2['flag']==0)
                                    $str.= "Item Being Checked By <i class='text-primary'>".$row2['department']." Department</i>";
                                else if($row['status'] == 'Collected-not-comfirmed')
                                    $str.= "
                                    <button type='button' onclick = 'prompt_confirmation(this)' value='".$row['purchase_order_id']."' name='confirm_handover'
                                    class='btn btn-sm btn-outline-primary'>Confirm Recieved</button>
                                    ";
                                // else if($row['status'] == 'Complete-uncomfirmed')
                                //     $str.= "Waiting For Comfirmation from <i class='text-primary'>".$row2['customer']."</i>";
                                else 
                                    $str.= "<input type='button' value='Finished' name='give_".$row['purchase_order_id']."' onclick='serial(this,\"$row2[purchased_amount]\",\"pur_$row2[request_id]\")' data-bs-toggle='modal' data-bs-target='#item_registration' class='btn btn-sm btn-outline-primary' class='btn btn-sm btn-outline-primary'>
                                    ";
/////////////////////////////////////////////////////Can use Sms////////////////////////////////////////////////
                                $str.= "
                                <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>
                                 
                                </li>
                            </ul>
                            </div>
                        </div>
                        ";
                    }
            }
            if($str=='') $first_empty =true;
            else 
                divcreate($str,"Items Purchased");
    ?>
    
    <?php
        $str="";
        $sql = "SELECT *, R.status AS `rstatus`, S.status AS `sstatus` FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id where (S.status = 'Approved' OR S.status = 'Complete-uncomfirmed') AND property_company = ?";
        $stmt_fromStock_items = $conn->prepare($sql);
        $stmt_fromStock_items -> bind_param("s", $_SESSION['company']);
        $stmt_fromStock_items -> execute();
        $result_fromStock_items = $stmt_fromStock_items -> get_result();
        if($result_fromStock_items -> num_rows>0)
        while($row = $result_fromStock_items -> fetch_assoc())
        {
            $type=$row['type'];
            $na_t=str_replace(" ","",$type);
            if($row['type']=="Consumer Goods")
            {
                $id=$row['request_for'];
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
            else if($row['type']=="Spare and Lubricant")
            {
                $id=$row['request_for'];
                $stmt_description->bind_param("i", $row['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;  
            }
            else if($row['type']=="Tyre and Battery")
            {
                $id=$row['request_for'];
                $name=$row['request_for'];
                $res=false;
            }
            else 
            {
                $id=$row['request_id'];
                $res=false;
                $name=$row['item'];
            }
            if($res)
                while($row3 = $result3->fetch_assoc())
                {
                    if($row['type']=="Consumer Goods")
                    {
                        $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                    }
                    else if($row['type']=="Spare and Lubricant")
                        $name=$row3['description'];
                }
                if($row['type']=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3 class='text-capitalize'>";
                        $str .= "
                        <span class='small text-secondary float-start'>
                        <button type='button' value='".$row['request_id']."' class='btn btn-sm' data-bs-toggle='modal' data-bs-target='#company_select' onclick='comp_selector(this,\"".$row['company']."\")'><i class='fa fa-share'></i></button>
                        </span>";
                        $str .= $name."</h3>
                        <ul>
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Company : </span>".$row['company']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Item : </span><span id='Req_$row[request_id]'>".$row['item']."</span></li>
                            <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                            <li class='text-start'><span class='fw-bold'>From Stock : </span>".$row['in-stock']." ".$row['unit']."</li>
                            <li class='text-end'><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                            <li>";
                            if($row['flag']==0)
                                $str.= "Item Being Checked By <i class='text-primary'>".$row['department']." Department</i>";
                            else if($row['status'] == 'Complete-uncomfirmed')
                                $str.= "Waiting For Comfirmation from <i class='text-primary'>".$row['customer']."</i>";
                            else 
                                $str.= "<button type='button' name='handover_instock' value='".$na_t."_".$row['request_id']."_".$row["rstatus"]."_".$row['id']."' onclick='serial(this,\"".$row['in-stock']."\",\"Req_$row[request_id]\")' data-bs-toggle='modal' data-bs-target='#item_registration' class='btn btn-sm btn-outline-primary'>Finished</button>";// onClick='serial(this)'
    /////////////////////////////////////////////////////Can use Sms////////////////////////////////////////////////
                            $str.= "                                 
                            </li>
                        </ul>
                        </div>
                    </div>
                    ";
            }
            if($str=='') $second_empty =true;
            else 
                divcreate($str,"From Stock");
            if(isset($first_empty) && isset($second_empty))
                echo "<div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Purchases made</h3>
                    </div>
                </div>";
    ?>
    </form>
</div>
<!-- needs work -->
<div class="modal fade" id="item_registration">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header">
                    <h3 id='top_text'>Item Details<span class='small text-secondary'>(optional)</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="item_registration_body">
                    <!-- Company And Items Form -->
                    <p class='text-primary text-sm text-center'>Amount : <span id='amount_data'></span></p>    
                    <div class="row" id='sno_type'>
                        <label for="sno_type"><span class='text-danger'>*</span>Insert Data Number for : </label>    
                        <div class='ms-3 form-check mb-3 col-5'>
                            <input class='form-check-input' type='radio' name='sno' id='sno_all' value='All' onclick="in_sno(this)" required>
                            <label class='form-check-label' for='sno_all'>
                                All items
                            </label>
                        </div>
                        <div class='ms-3 form-check mb-3 col-5'>
                            <input class='form-check-input' type='radio' name='sno' id='sno_batch' value='Batch' onclick="in_sno(this)" required>
                            <label class='form-check-label' for='sno_batch'>
                                Batch of Items
                            </label>
                        </div>
                    </div>
                    <form method="GET" action="allphp.php">
                        <div id='batch_into' class="d-none">
                            <div class="mb-3">
                                <label for="per_batch" class="form-label">Items Per Batch</label>
                                <input type="number" class="form-control" id="per_batch" name="per_batch" onchange = "batch_up(this)" placeholder="** Enter Number of items per Batch **">
                            </div>
                        </div>
                        <div class="d-none">
                            <div id='batched_purchased'>
                            </div>
                            <div class='mx-auto form-check mb-3'>
                                <input class='form-check-input' type='checkbox' name = 'no_serial' id='no_serial_b' onclick="serial_change(this,'batch')">
                                <label class='form-check-label' for='no_serial_b'>
                                    Doesn't Have a Serial
                                </label>
                            </div>
                            <button class='mx-auto d-block btn btn-outline-success mt-3' id='item_registration_btn_batch' name='give_batch'>Proceed</button>
                        </div>
                    </form> 
                    <form method="GET" action="allphp.php" class="d-none">
                        <div id='items_purchased'>
                            <h6>Item - xx</h6>
                            <div class="mb-3">
                                <label for="serial" class="form-label">Serial Number</label>
                                <input type="text" class="form-control serial_name" id="serial_xx" name='serial[]' placeholder="** Hagbes Tag **" required>
                            </div>
                            <div class="mb-3">
                                <label for="item_details" class="form-label">Details for Item</label>
                                <textarea class="form-control" id="item_details_xx" rows="3" name='data[]' required></textarea>
                            </div>
                        </div>
                        <div class='mx-auto form-check mb-3'>
                            <input class='form-check-input' type='checkbox' name = 'no_serial' id='no_serial' onclick="serial_change(this,'indiv')">
                            <label class='form-check-label' for='no_serial'>
                                Doesn't Have a Serial
                            </label>
                        </div>
                        <button class='mx-auto d-block btn btn-outline-success mt-3' id='item_registration_btn' name='give'>Proceed</button>
                     </form> 
                </div>
        </div>
    </div>
</div>
<script>
    var temporary_details = document.getElementById("items_purchased").innerHTML;
    function serial_change(e,type)
    {
        let ss = document.getElementsByClassName("serial_"+type);
        if(e.checked)
        {
            for(let i =0; i<ss.length;i++)
                ss[i].disabled = true;
        }
        else
        {
            for(let i =0; i<ss.length;i++)
                ss[i].disabled = false;
        }
    }
    function in_sno(e)
    {
        let checked = (e.checked)?e.value:((e.value == "All")?"Batch":"All");
        let quan = parseInt(document.getElementById("amount_data").innerHTML);
        let item = document.getElementById("amount_data").name;
        let no_s = document.getElementsByName("no_serial");
        for(let i =0; i<no_s.length;i++)
            no_s[i].checked = false;
        document.getElementById("per_batch").value = "";
        if(checked == "All")
        {
            let serialss = "";
            for(let i =1; i<=quan;i++)
            {
                serialss+= temporary_details.replaceAll("xx",i).replaceAll("Item",item).replaceAll("serial_name","serial_indiv");
            }
            document.getElementById("items_purchased").innerHTML = serialss;
            document.getElementById("items_purchased").parentElement.classList.remove("d-none");
            document.getElementById("batch_into").classList.add("d-none");
            document.getElementById("batched_purchased").parentElement.classList.add("d-none");
            document.getElementById("batched_purchased").innerHTML = "";
        }
        else
        {
            document.getElementById("items_purchased").innerHTML = "";
            document.getElementById("items_purchased").parentElement.classList.add("d-none");
            document.getElementById("batch_into").classList.remove("d-none");
        }
    }
    function batch_up(e)
    {
        let t_quan = e.title.split("_")[0];
        let item = e.title.split("_")[1];
        let per_batch = parseInt(e.value);
        // let t_quan = parseInt(e.name);
        let batches = Math.ceil(t_quan/per_batch); 
        let serialss = "";
        for(let i =1; i<=batches;i++)
        {
            serialss+= temporary_details.replaceAll("xx",i).replaceAll("Item",item).replaceAll("serial_name","serial_batch");
        }
        document.getElementById("batched_purchased").innerHTML = serialss;
        document.getElementById("batched_purchased").parentElement.classList.remove("d-none");
    }
    function serial(e,quan,item)
    {
        item =document.getElementById(item).innerHTML;
        let btns = document.getElementsByName("sno");
        for(let i=0;i<btns.length;i++)
            btns[i].checked = false;

        document.getElementById("items_purchased").innerHTML = "";
        document.getElementById("items_purchased").parentElement.classList.add("d-none");

        document.getElementById("batched_purchased").parentElement.classList.add("d-none");
        document.getElementById("batched_purchased").innerHTML = "";
        document.getElementById("per_batch").value = "";
        document.getElementById("batch_into").classList.add("d-none");
        if(e.name == "handover_instock")
        {
            document.getElementById("item_registration_btn").name = "handover_instock";
            document.getElementById("item_registration_btn_batch").name = "handover_instock_batch";
            document.getElementById("item_registration_btn").value = e.value+"_"+quan;
            document.getElementById("item_registration_btn_batch").value = e.value+"_"+quan;
        }
        else
        {
            document.getElementById("item_registration_btn").name = "give";
            document.getElementById("item_registration_btn_batch").name = "give_batch";
            document.getElementById("item_registration_btn").value = e.name.split("_")[1]+"_"+quan;
            document.getElementById("item_registration_btn_batch").value = e.name.split("_")[1]+"_"+quan;
        }
        quan = parseInt(quan);
        document.getElementById("amount_data").innerHTML = quan;
        document.getElementById("amount_data").name = item;
        document.getElementById("per_batch").title = quan+"_"+item;
    }

</script>
<?php include "../footer.php"; ?>