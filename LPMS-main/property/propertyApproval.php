<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Items Requested</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Approval");
    sideactive("property_approval");
    var temp_lbl = [];
    temp_lbl[0] = "Purchase Quantity";
    temp_lbl[1] = "Instock Quantity";
    function validate_quantity(e)
    {
        let purchase = document.getElementById('purchase_quantity');
        let stock = document.getElementById('stock_quantity');
        if(stock.value == "" || purchase.value == "")
            document.getElementById('warn_quantity').innerHTML ="*Please Set All Values*";
        else if((purchase.value == 0 && stock.value == 0))
            document.getElementById('warn_quantity').innerHTML ="*You cannot set both values to 0*";
        else
        {
            document.getElementById('warn_quantity').innerHTML ="";
            e.type="submit";
            e.click();
        }
    }
    function prop_reason(e)
    {
        // document.getElementById('stat_btn').name=e.name;
        document.getElementById('prop_stat_btn').value=e.value;
        document.getElementById('prop_stat_btn').name=e.name;
        if(e.innerHTML.includes("Reject"))
        {
            document.getElementById('prop_stat_btn').setAttribute("onClick","prompt_confirmation(this)");
            document.getElementById('quantity_update').classList.add("d-none");
            document.getElementById("reason_prop").setAttribute("required",true);
            document.getElementById('prop_stat_btn').classList.replace('btn-outline-success','btn-outline-danger');
        }
        else
        {
            document.getElementById('prop_stat_btn').setAttribute("onClick","validate_quantity(this)");
            document.getElementById('quantity_update').classList.remove("d-none");
            document.getElementById('purchase_quantity').value= document.getElementById("purchase_qunatity_"+e.id.split("_")[2]).innerHTML;
            document.getElementById('total_quantity').innerHTML= document.getElementById("total_quantity_"+e.id.split("_")[2]).innerHTML+" "+document.getElementById("unit_"+e.id.split("_")[2]).innerHTML;
            document.getElementById('stock_quantity').value= document.getElementById("stock_qunatity_"+e.id.split("_")[2]).innerHTML;
            let lbls = document.getElementsByClassName("lbl_purchase");
            for(let i=0; i<lbls.length; i++)
                lbls[i].innerHTML = temp_lbl[i]+" ("+document.getElementById("unit_"+e.id.split("_")[2]).innerHTML+")";
            document.getElementById("reason_prop").removeAttribute("required");
            document.getElementById('prop_stat_btn').classList.replace('btn-outline-danger','btn-outline-success');
        }
    }
    function prop_reason_batch(e)
    {
        document.getElementById('prop_stat_btn').setAttribute("onClick","prompt_confirmation(this)");
        // document.getElementById('stat_btn').name=e.name;
        document.getElementById('quantity_update').classList.add("d-none");
        document.getElementById('prop_stat_btn').name=e.name;
        document.getElementById('prop_stat_btn').value=e.value;
        if(e.innerHTML.includes("Reject"))
        {
            document.getElementById("reason_prop").setAttribute("required",true);
            document.getElementById('prop_stat_btn').classList.replace('btn-outline-success','btn-outline-danger');
        }
        else
        {
            document.getElementById("reason_prop").removeAttribute("required");
            document.getElementById('prop_stat_btn').classList.replace('btn-outline-danger','btn-outline-success');
        }
    }
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
        document.getElementById("batch_approve").value = selections;
        document.getElementById("batch_reject").value = selections;
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
        <h2>Property Approval</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Property Approval</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick='prop_reason_batch(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-success shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button>
            <button type='button' onclick='prop_reason_batch(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-danger shadow mt-3' name='batch_reject' id='batch_reject'>Reject</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $condition = "((R.request_type = 'Spare and Lubricant' AND R.type = 'Spare' AND `mode` = 'Internal') OR 
        (R.request_type = 'Tyre and Battery' AND `mode` = 'Internal') OR 
        (R.request_type != 'Consumer Goods' AND R.request_type != 'Tyre and Battery' AND R.request_type != 'Spare and Lubricant' AND R.request_type != 'Miscellaneous')) AND";

        $sql = "SELECT *, R.status AS `rstatus`, S.status AS `sstatus` FROM requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id where `property_company` = ? AND ($condition (R.status = 'Store Checked') OR (S.status = 'not approved' AND `in-stock` > 0))";
        $stmt_property_approval = $conn->prepare($sql);
        $stmt_property_approval -> bind_param("s", $_SESSION['company']);
        $stmt_property_approval -> execute();
        $result_property_approval = $stmt_property_approval -> get_result();
        if($result_property_approval -> num_rows > 0)
            while($row = $result_property_approval -> fetch_assoc())
            {
                $type = $row["request_type"];
                $na_t=str_replace(" ","",$type);
                if($row['request_type']=="Consumer Goods")
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
                else if($type=="Spare and Lubricant")
                {
                    $id=$row['request_for'];
                    $stmt_description->bind_param("i", $row['request_for']);
                    $stmt_description->execute();
                    $result3 = $stmt_description->get_result();
                    $res=($result3->num_rows>0)?true:false;  
                }
                else if($type=="Tyre and Battery")
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
                        if($row['request_type']=="Consumer Goods")
                        {
                            $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                        }
                        else if($type=="Spare and Lubricant")
                            $name=$row3['description'];
                    }
                    if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3 class='text-capitalize'>
                        <span class='small text-secondary float-start'>
                        <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span>".$name."</h3>
                        <ul>
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Item : </span>".$row['item']."</li>
                            <li class='text-start text-primary'><span class='fw-bold'>Requsted Quantity : </span><span id='total_quantity_".$row['request_id']."'>".$row['requested_quantity']."</span> - <span id='unit_".$row['request_id']."'>".$row['unit']."</span></li>
                            <li class='text-start text-primary d-none'><span class='fw-bold'>Purchase Quantity : </span><span id='purchase_qunatity_".$row['request_id']."'>".$row['for_purchase']."</span> - ".$row['unit']."</li>
                            <li class='text-start text-primary'><span class='fw-bold'>Quantity In Stock : </span><span id='stock_qunatity_".$row['request_id']."'>".$row['in-stock']."</span> - ".$row['unit']."</li>
                            <li class='text-end'><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                            <li class='mt-1'>
                                <button type='button' onclick='prop_reason(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-success btn-sm shadow' value='".$row['request_id']."' id='approve_".$na_t."_".$row['request_id']."' name='property_approval'>Proceed</i></button> 
                                <button type='button' onclick='prop_reason(this)' data-bs-toggle='modal' data-bs-target='#prop_reason' class='btn btn-outline-danger btn-sm shadow' value='".$row['request_id']."' id='reject_".$na_t."_".$row['request_id']."' name='property_rejection'>Reject <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>

                                </li>";
/////////////////////////////////////////////////////Can use Sms////////////////////////////////////////////////
                            $str.= "
                        </ul>
                        </div>
                    </div>
                    ";
            }
        // }
        if($str=='')
            echo "
                <div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Purchases made</h3>
                    </div>
                </div>";
        else 
            divcreate($str);
    ?>
    </form>
</div>
<div class="modal fade" id="prop_reason">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="GET" action="allphp.php">
                <div class="modal-header alert-primary">
                    <h3 id='top_text' class="text-white w-100 text-center">Purchase Updates / Remarks
                    <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h3>
                </div>
                <div class="modal-body" id="reason_body">
                    <!-- Company And Items Form -->
                    <div class="mb-3" id="quantity_update">
                        <h5 class="my-1 text-center">Total requested Quantity : <span id="total_quantity"></span></h5>
                        <div class="mb-3">
                            <label for="quantity_prop" class="form-label lbl_purchase">Approved Purchase Quantity</label>
                            <input type="number" class="form-control rounded-4" min='0' id="purchase_quantity" placeholder="Purchase Quantity" name='purchase_quantity' step='any'>
                        </div>
                        <div class="mb-3">
                            <label for="quantity_prop" class="form-label lbl_purchase">Approved Instock Quantity</label>
                            <input type="number" class="form-control rounded-4" min='0' id="stock_quantity" placeholder="Instock Quantity" name='stock_quantity' step='any'>
                        </div>
                        <p id="warn_quantity" class="text-danger text-center"></p>
                    </div>
                    <div class="mb-3">
                        <label for="reason_prop" class="form-label">Remark</label>
                        <textarea class='form-control' rows='4' name='reason' id="reason_prop"></textarea>
                    </div>
                    <button class='mx-auto d-block btn btn-outline-success mt-3' type="button" onclick="validate_quantity(this)" id='prop_stat_btn'>Proceed</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<?php include "../footer.php"; ?>