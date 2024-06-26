<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
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
    set_title("LPMS | Assign For Collection");
    sideactive("collection");
    var element,db;
    function loader_collect(e)
    {
        let temp = e.id.replace("view_","");
        let data = temp.split('_');
        element = data[0];
        db = data[1];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("itemsview_body").innerHTML=this.responseText;
        }
        req.open("GET", "Ajax_show.php?cl_id="+element);
        req.send();
    }
    // function comp_sh()
    // {
    //     document.getElementById('close_modal_1').click();
    //     const req = new XMLHttpRequest();
    //     req.onload = function(){//when the response is ready
    //     document.getElementById("itemsview_body2").innerHTML=this.responseText;
    //     }
    //     req.open("GET", "ajax_comp_sheet.php?cl_id="+element+"&db="+db);
    //     req.send();
    // }
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
    <div id='batch_div' class="position-fixed d-none my-4 p-4 shadow shadow-warning alert-primary" style="top: 80%; left: 85%;">
        <form method="GET" action="allphp.php">
                <div class='mb-3'>
                    <select class='form-select form-select-sm' name='collector_name' id='collector_name'>
                        <option value=''>--Select Purchase Officer--</option>
                        <?php
                            $qq = "(company = '".$_SESSION['company']."'";
                            $qq .= ")";
                            $sql_purchase_officers = "SELECT * FROM account WHERE `role`='Purchase officer' AND `status` = 'active' AND $qq";
                            $stmt_purchase_officers = $conn->prepare($sql_purchase_officers);
                            $stmt_purchase_officers->execute();
                            $result_purchase_officers = $stmt_purchase_officers->get_result();
                            if($result_purchase_officers->num_rows>0)
                            {
                                while($row = $result_purchase_officers->fetch_assoc())
                                {
                                    $officer=$row['Username'];
                                    echo "<option value='$officer'>$officer (Company : $row[company])</option>";
                                }
                            }
                        ?>
                    </select>
                    <button  class='btn btn-sm btn-outline-warning mt-3' id='Assign_batch_collector' name='Assign_batch_collector'>
                        Assign
                    </button>
                </div>
        </form>
        <div class="mt-3 form-check">
            <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
            <label class="form-check-label" for="checkboxAll">Select All</label>
        </div>
    </div>
    <?php
        $str="";
        $emp1=true;
        $sql_cheque_signed = "SELECT *,P.status as `status`,P.request_id as `request_id` FROM `purchase_order` AS P INNER JOIN requests AS R on P.request_id = R.request_id where R.request_type!='agreement' and (P.status='Payment Processed' OR P.status='Collected-not-comfirmed') AND collector is null AND R.procurement_company = ?";
        $stmt_cheque_signed = $conn->prepare($sql_cheque_signed);
        $stmt_cheque_signed -> bind_param("s", $_SESSION['company']);
        $stmt_cheque_signed->execute();
        $result_cheque_signed = $stmt_cheque_signed->get_result();
        if($result_cheque_signed->num_rows>0)
        while($r_clus = $result_cheque_signed->fetch_assoc())
        {
            $emp1=false;
            $r_type = $r_clus['request_type'];
            $na_t=str_replace(" ","",$r_type);
            $uname =str_replace("."," ",$r_clus['customer']);
            $str.= "
                <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                    <div class='box'>
                    <h3>
                    <input value='".$r_clus['request_id']."' class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                    <button type='button'  title='".$r_clus['recieved']."' name='specsfor_".$na_t."_".$r_clus['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                    <span class='fw-bold'>Item : </span>".$r_clus['item']."</button>
                    </h3>
                    <form method='GET' action='allphp.php'>
                    <ul>
                    <li class='text-start'><span class='fw-bold'>Item : </span>".$r_clus['item']."</button></li>
                    <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                    <li class='text-start'><span class='fw-bold'>Quantity : </span>".$r_clus['requested_quantity']." ".$r_clus['unit']."</li>
                    <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$r_clus['date_needed_by']."</li>
                    <li class='row'>
                    <span id='btns_assign_collector'>
                        <div class='input-group mb-3'>
                            <select class='form-select form-select-sm' name='collector_name'>
                                <option value=''>--Select Purchase Officer--</option>
                                    ";
                                    $qq = "(company = '".$_SESSION['company']."'";
                                    $qq .= ")";
                                    $sql_purchase_officers = "SELECT * FROM account WHERE `role`='Purchase officer' AND `status` = 'active' AND $qq";
                                    $stmt_purchase_officers = $conn->prepare($sql_purchase_officers);
                                    $stmt_purchase_officers->execute();
                                    $result_purchase_officers = $stmt_purchase_officers->get_result();
                                    if($result_purchase_officers->num_rows>0)
                                    {
                                        while($row = $result_purchase_officers->fetch_assoc())
                                        {
                                            $officer=$row['Username'];
                                            $str.= "<option value='$officer'>$officer (Company : $row[company])</option>";
                                        }
                                    }
                    $str.=" </select>
                    
                            <button type='button' onclick = 'prompt_confirmation(this)' value='".$r_clus['request_id']."' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_collector_i'>
                                Assign
                            </button>
                            </span>
                        </div>
                    </li>
                    </ul>
                    </form>
                    </div>
                </div>
                ";
        }
        $str2="";
        $emp2=true;
        $sql_cheque_signed_agreement = "SELECT *,P.status as `status`,P.request_id as `request_id` FROM `purchase_order` AS P INNER JOIN requests AS R on P.request_id = R.request_id where R.request_type='agreement' and (P.status='Payment Processed' OR P.status='Collected-not-comfirmed') AND collector is null AND R.procurement_company = ?";
        $stmt_cheque_signed_agreement = $conn->prepare($sql_cheque_signed_agreement);
        $stmt_cheque_signed_agreement -> bind_param("s", $_SESSION['company']);
        $stmt_cheque_signed_agreement->execute();
        $result_cheque_signed_agreement = $stmt_cheque_signed_agreement->get_result();
        if($result_cheque_signed_agreement->num_rows>0)
        while($r_clus = $result_cheque_signed_agreement->fetch_assoc())
        {
            $emp2=false;
            $r_type = $r_clus['request_type'];
            $na_t=str_replace(" ","",$r_type);
            $uname =str_replace("."," ",$r_clus['customer']);
            $str2.= "
                <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                    <div class='box'>
                    <h3>
                    <input value='".$r_clus['request_id']."' class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                    <button type='button'  title='".$r_clus['recieved']."' name='specsfor_".$na_t."_".$r_clus['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                    <span class='fw-bold'>Item : </span>".$r_clus['item']."</button>
                    </h3>
                    <form method='GET' action='allphp.php'>
                    <ul>
                    <li class='text-start'><span class='fw-bold'>Item : </span>".$r_clus['item']."</button></li>
                    <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                    <li class='text-start'><span class='fw-bold'>Quantity : </span>".$r_clus['requested_quantity']." ".$r_clus['unit']."</li>
                    <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$r_clus['date_needed_by']."</li>
                     
                    <li><button type='button' onclick = 'prompt_confirmation(this)' 
                    value='".$r_clus['purchase_order_id']."' class='btn btn-outline-primary btn-sm shadow ms-2' name='complete_settlement'>Complete Settlement</button></li>
                    </li>
                    </ul>
                    </form>
                    </div>
                </div>
                ";
        }
       
        if($str !='')
            divcreate($str,"POs waiting Collecter to be Assigned"); 
            if($str2 !='')
            divcreate($str2,"Agreement POs waiting for settelment");
        if($emp2&$emp1)
            echo "<div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Requests to be Collected$emp1$emp2</h3>
                    </div>
                </div>";
    ?>
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
<script>
    function add_btn(e){
document.getElementById('optional_btn').innerHTML= " <form method='GET' action='allphp.php'>"+document.getElementById('btns_assign_collector').innerHTML+"</form>";
openmodal(e);
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
        document.getElementById("Assign_batch_collector").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
    }
</script>
<?php include '../../footer.php';?>