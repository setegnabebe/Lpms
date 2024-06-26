<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
// include '../connection/connect_ws.php';
function divcreate($str,$n)
{
    if($str=='') return 0;
    echo "
        <div class='py-5 pricing' id='$n'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>All Requests for $n</h6> 
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
    sideactive("fixed_approval");
    var time,countdown,temp;
    var data_hold;
    function spec_check(e)
    {
        if(document.getElementById('spec_'+e.name).value !='')
        {
            var dep = document.getElementById('spec_'+e.name).value;
            var req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
                document.getElementById("dep_show"+e.name).classList.remove('d-none');
                document.getElementById("dep_options"+e.name).classList.add('d-none');
                document.getElementById("btns"+e.name).classList.add('d-none');
                document.getElementById("dep_show"+e.name).innerHTML= "<span class='fw-bold'>Awaiting Specification From : </span> "+dep+" Department";
            }
            req.open("GET", "allphp.php?speccheck="+e.name+"&dep="+dep);
            req.send();
        }
    }
    function show(e)
    {
        var arraycont = e.parentElement.children;
        if(e.innerHTML=='All Requests')
        {
            for($i=0;$i<arraycont.length;$i++)
            {
                arraycont[$i].classList.replace('btn-white','btn-primary');
                arraycont[$i].classList.remove('shadow');
                if($i!=0)
                    document.getElementById(arraycont[$i].innerHTML).classList.remove('d-none');
            }
        }
        else
        {
            for($i=0;$i<arraycont.length;$i++)
            {
                arraycont[$i].classList.replace('btn-white','btn-primary');
                if($i!=0)
                    document.getElementById(arraycont[$i].innerHTML).classList.add('d-none');
            }
            document.getElementById(e.innerHTML).classList.remove('d-none');
        }
        e.classList.replace('btn-primary','btn-white');
        e.classList.add('shadow','btn-white');
    }
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
        <h2>Approve Fixed Asset Purcahse Order Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Fixed Asset Purcahse Order Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
    <div id='batch_div' class="position-fixed d-none my-4 p-4 shadow list-group-item-light" style="top: 80%; left: 90%; z-index:1;">
        <form method="GET" action="allphp.php">
            <div class=''>
                <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../Committee")' class='btn btn-outline-success shadow mt-3' name='batch_owner_approve' id='batch_approve'>Approve</button>
                <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../Committee","remove")' class='btn btn-outline-danger shadow mt-3' name='batch_owner_reject' id='batch_reject'>Reject</button>
            </div>
        </form>
        <div class="mt-3 form-check">
            <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
            <label class="form-check-label" for="checkboxAll">Select All</label>
        </div>
    </div>
    <div class='nav bg-light btn-group'>
    <button type='button' class='btn btn-white border-primary  mx-2 rounded-pill' onclick="show(this)">All Requests</button>
    <?php
    $emp =true;
    $sql_for_owner_count = "SELECT count(*) AS num FROM requests WHERE ((`status` = 'Approved by Property' and (company != 'Hagbes HQ.' OR (director IS NOT NULL AND customer = director))) OR `status` = 'Approved By Director') AND `request_type` = 'Fixed Assets' and department != 'Owner'";
    $stmt_for_owner_count = $conn->prepare($sql_for_owner_count);
    $stmt_for_owner_count -> execute();
    $result_for_owner_count = $stmt_for_owner_count->get_result();
    if($result_for_owner_count->num_rows>0)
        while($r2 = $result_for_owner_count->fetch_assoc())
        {
            $type = "Fixed Assets";
            if($r2['num']>0)
            {
                $emp=false;
                echo "<button type='button' class='btn btn-primary border-primary mx-2 rounded-pill' onclick='show(this)'>".$type."</button>";
            }
        }
    ?>
    </div>
    <form method="GET" action="allphp.php">
        <!-- <h3 id='Success_Message'></h3> -->
    <div class="row m-auto" id='reload'>
        <?php
            $sql_for_owner = "SELECT * FROM requests WHERE ((`status` = 'Approved by Property' and (company != 'Hagbes HQ.' OR (director IS NOT NULL AND customer = director))) OR `status` = 'Approved By Director') AND `request_type` = 'Fixed Assets' and department != 'Owner' ORDER BY date_needed_by ASC";
            $stmt_for_owner = $conn->prepare($sql_for_owner);
            $stmt_for_owner -> execute();
            $result_for_owner = $stmt_for_owner->get_result();
            $str="";
            $empty=true;
            if($result_for_owner->num_rows>0)
                while($row = $result_for_owner->fetch_assoc())
                {
                    $type = "Fixed Assets";
                    $na_t=str_replace(" ","",$type);
                    $name=$row['item'];
                    $uname =str_replace("."," ",$row['customer']);
                    $str.="
                    <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4'>
                        <div class='box shadow'>
                            <h3 class='row'>
                                <button id='undo_".$na_t.$row['request_id']."' name='undo_".$na_t.$row['request_id']."' type='button' onclick='update(this)' class='btn col-2 d-none'><i class='fas fa-undo'></i></button>
                                <span class='text-capitalize col-12'>".$name."
                                <span class='small text-secondary float-start'>
                                    <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                                </span></span>
                            </h3>
                            <ul>
                                <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                ".$row['item']."</button></li>
                                <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                <li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>
                                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                                    <div class='mb-3 collapse mx-5 text-start' id='remark_view".$row['request_id']."'>
                                        <p style='word-wrap: break-word'>Remark : ".$row['Remark']."</p>
                                    </div>".
                                ((!is_null($row['spec_dep']) && is_null($row['specification']))?
                                "<li class='text-start'><span class='fw-bold'>Awaiting Specification From : </span><i class='text-primary'>".$row['spec_dep']." Department</i></li>":
                                "<li id='btns".$na_t."_".$row['request_id']."'>
                                    <button type='button' class='btn btn-outline-success btn-sm shadow' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../Committee\")' name='btntype' value='approve_".$na_t."_".$row['request_id']."' id='approve_".$na_t.$row['request_id']."'>Approve <i class='text-white text-white far fa-thumbs-up fa-fw'></i></button> 
                                    <button type='button' class='btn btn-outline-danger btn-sm shadow' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../Committee\",\"remove\")' name='btntype' value='reject_".$na_t."_".$row['request_id']."' id='reject_".$na_t.$row['request_id']."'>Reject <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                    <button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                                    </li>").
                                "<li id='loading_".$na_t.$row['request_id']."' class='d-none'> <div class='spinner-border text-primary'></div> </li>";
                            $str .="
                            </ul>
                        </div>
                    </div>";
                }
            divcreate($str,$type);
            if($emp)
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests Waiting Approval at this Time</h3>
                        </div>
                    </div>";
        ?>
        
    </div>
    </form>
</div>
</div>
<?php include '../footer.php';?>