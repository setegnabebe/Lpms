
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
        <h6 class='text-white'>Proformas received</h6> 
        </div>
        <div class='row'>
            $str
        </div>
    </div>
    ";
}
?>

<script>
    set_title("LPMS | Open Proforma");
    sideactive("performa");
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
        document.getElementById("batch_recieve").value = selections;
        document.getElementById("batch_petty_cash").value = selections;
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
        <h2>Receive and Open Proforma</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Receive and Open Proforma</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid px-4">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_recieve' id='batch_recieve'>Open Proformas</button>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-warning shadow mt-3' name='batch_petty_cash' id='batch_petty_cash'>Petty Cash</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
<?php
$sql = "SELECT * FROM `purchase_order` WHERE `status`='Complete'  AND `procurement_company` = ? ORDER BY priority,`timestamp` DESC";
$stmt_for_open = $conn->prepare($sql);
$stmt_for_open -> bind_param("s", $_SESSION['company']);
$stmt_for_open -> execute();
$result_for_open = $stmt_for_open -> get_result();
$str="";
if($result_for_open->num_rows>0)
    while($row = $result_for_open->fetch_assoc())
    {
        $type=$row['request_type'];
        $na_t=str_replace(" ","",$type);
        $stmt_request -> bind_param("i", $row['request_id']);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        if($result_request -> num_rows>0)
            while($row2 = $result_request -> fetch_assoc())
            {
                if($row['request_type']=="Consumer Goods"){
                    if($row2['request_for'] == 0)
                    {
                        $id = $row2['request_for'];
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
                    if($row2['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) 
                    {
                        $name =(explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                    }
                    if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else $prio="";
                    $printpage = "
                        <form method='GET' action='../../requests/print.php' class='float-end'>
                            <button type='submit' class='btn btn-outline-secondary border-0' id='print_$row[purchase_order_id]' name='print' value='".$row['request_id'].":|:$type'>
                            <i class='text-dark fas fa-print'></i>
                            </button>
                        </form>";
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'><h3 class='text-capitalize'>
                        <span class='small text-secondary float-start'>
                        <input value='".$row['purchase_order_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        $prio</span>";
                        $str.=($res || $row['request_type']=="Tyre and Battery")?$name:"<button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                        $name</button>";
                        $str.= "
                        $printpage
                        <span class='small text-secondary d-block mt-2'>$type</span>
                        </h3>
                        <form method='GET' action='allphp.php'>
                        <ul>
                            <!--<li class='d-none'>dbs</li>
                            <li class='d-none'>$id</li>
                            <li class='d-none'>$name</li>-->";
                            $str.=($res || $row['request_type']=="Tyre and Battery")?"
                            <li class='text-start'><span class='fw-bold'>Item : </span>
                            <button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                            ".$row2['item']."</li></button>":"";
                        
                            $str.="
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                            <li>";
                            $str.= "<span id='btns_pro_opened'><button type='submit' name='opened_Performa' value='".$row['purchase_order_id']."' class='btn btn-sm btn-outline-primary mb-2'> Open Proforma </button>
                            <button type='submit' name='petty_cash' value='".$row['purchase_order_id']."' class='btn btn-sm btn-outline-warning mb-2'> petty Cash </button>
                            <button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                                <!--<input type='submit' value='To Committee' name='to_Committee_".$row['purchase_order_id']."' class='btn btn-sm btn-outline-primary'>-->";
                            $str.= "
                            <span></li>
                            
                        </ul>
                        </form>
                        </div>
                    </div>
                    ";
            }
    }
        
    if($str=='')
    echo "
        <div class='py-5 pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h3 class='mt-4'>No Requests Awaiting Comparision sheets</h3>
            </div>
        </div>";
else 
    divcreate($str);
    ?>
   
</div>

</div>
<?php include "../../footer.php"; ?>
<script>
function add_btn(e){
document.getElementById('optional_btn').innerHTML= "<form method='GET' action='allphp.php'>"+document.getElementById('btns_pro_opened').innerHTML+"</form>";
openmodal(e);
}
</script>
