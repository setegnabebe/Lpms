
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
    sideactive("director_approval");
    var time,countdown,temp;
    var data_hold;
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
                console.log(arraycont[$i].classList)
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
        <h2>Approve Purcahse Order Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Purcahse Order Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow list-group-item-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../GM")' class='btn btn-outline-success shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button>
            <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../GM","remove")' class='btn btn-outline-danger shadow mt-3' name='batch_reject' id='batch_reject'>Reject</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <div class='nav bg-light btn-group'>
    <form  method='GET'>
    <button type='submit' name='type' value='' class='btn btn-primary border-primary mx-2 rounded-pill' >All Requests</button>
    <?php
    $emp =true;
    $cond2 = "";
    if(!in_array("All Departments",$_SESSION["managing_department"]))
    {
        foreach($_SESSION["managing_department"] as $d) $cond2 .= ($cond2 == "")?" AND (`department` = '$d'":" OR `department` = '$d'";
        $cond2 = ($cond2 == "")?$cond2:$cond2.")";
    }
    $cond3 = " AND (`company` = '".$_SESSION['company']."'";
    if(isset($_SESSION['included_company']))
    {
        foreach($_SESSION["included_company"] as $c) $cond3 .= " OR `company` = '$c'";
    }
    $cond3 = ($cond3 == "")?$cond3:$cond3.")";
    $cond2 .= $cond3;
    $cond = "`status` = 'Approved By Dep.Manager' $cond2";// || $_SESSION['role'] == 'GM'
    // $cond .= " AND request_type != 'Fixed Assets'";
    $sql_GM_type = "SELECT request_type FROM requests WHERE $cond group by request_type";
    $stmt_GM_type = $conn->prepare($sql_GM_type);
    $stmt_GM_type ->execute();
    $result_GM_type = $stmt_GM_type->get_result();
    if($result_GM_type->num_rows>0)
        while($r = $result_GM_type->fetch_assoc())
        {
            $type = $r['request_type'];
            $sql_GM_type_count = "SELECT count(*) AS num FROM requests WHERE $cond AND request_type = ?";
            $stmt_GM_type_count = $conn->prepare($sql_GM_type_count);
            $stmt_GM_type_count ->bind_param("s", $type);
            $stmt_GM_type_count ->execute();
            $result_GM_type_count = $stmt_GM_type_count->get_result();
            if($result_GM_type_count -> num_rows>0)
                while($r2 = $result_GM_type_count -> fetch_assoc())
                {
                    if($r2['num']>0)
                    {
                        $emp=false;
                        $color=isset($_GET['type'])&&$type==$_GET['type']?" bg-white text-dark":"";
                        echo "<button type='submit' name='type' value='$type' class='btn btn-primary border-primary mx-2 rounded-pill $color' onclick='show(this)'>".$type."</button>";
                    }
                }
        }
    ?>
    </form>
    </div>
    <form method="GET" action="allphp.php">
        <!-- <h3 id='Success_Message'></h3> -->
    <div class="row m-auto" id='reload'>
        <?php
        $req_type=isset($_GET['type'])&&$_GET['type']!=''?" and request_type ='".$_GET['type']."'":"";
        $sql_GM_type_two = "SELECT request_type FROM requests WHERE $cond $req_type group by request_type";
        $stmt_GM_type_two = $conn->prepare($sql_GM_type_two);
        $stmt_GM_type_two ->execute();
        $result_GM_type_two = $stmt_GM_type_two->get_result();
        if($result_GM_type_two->num_rows>0)
            while($rs = $result_GM_type_two->fetch_assoc())
            {
                $type = $rs['request_type'];
                $str="";
                $empty=true;
                $na_t=str_replace(" ","",$type);
                $sql_GM_type_data = "SELECT * FROM requests WHERE $cond AND request_type = ? ORDER BY date_needed_by ASC";
                $stmt_GM_type_data = $conn->prepare($sql_GM_type_data);
                $stmt_GM_type_data ->bind_param("s", $type);
                $stmt_GM_type_data ->execute();
                $result_GM_type_data = $stmt_GM_type_data->get_result();
                if($result_GM_type_data->num_rows>0)
                    while($row = $result_GM_type_data->fetch_assoc())
                    {
                        if($type=="Consumer Goods")
                        {
                            if($row['request_for'] == 0)
                            {
                                $stmt_project->bind_param("i", $row['request_for']);
                                $stmt_project->execute();
                                $result3 = $stmt_project->get_result();
                                $res=($result3->num_rows>0)?true:false;
                            }
                            else
                            {
                                $id = explode("|",$row['request_for'])[0];
                                $stmt_project_pms->bind_param("i", $id);
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
                                    $name = ($row['request_for'] == 0)?$row3['Name']:$row3['project_name'];
                                }
                                else if($type=="Spare and Lubricant")
                                {
                                    $name=$row3['description'];
                                }
                            }
                        $empty=false;
                        if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                        // <option value=''>-</option>
                        $spec_dep = "<select class='form-select bg-light' id='spec_".$na_t."_".$row['request_id']."'>";
                        $sql_department = "SELECT * FROM `department` WHERE `Name` = 'IT'";
                        $stmt_department = $conn->prepare($sql_department);
                        $stmt_department->execute();
                        $result_department = $stmt_department->get_result();
                        if($result_department->num_rows>0)
                            while($r= $result_department->fetch_assoc())
                            {
                                $spec_dep .= "<option value='".$r['Name']."'>".$r['Name']."</option>";
                            }
                        // $spec_dep .= ($_SESSION["role"] == "Owner")?"<option value='".$_SESSION["username"]."'>".$_SESSION["username"]."</option>":"";
                        $spec_dep .= "</select>";
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
                                    <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                                    ".$row['item']."</button></li>
                                    <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                    <li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>
                                    <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>";
                                    $str.="
                                        <div class='mb-3 collapse mx-5 text-start' id='remark_view".$row['request_id']."'>
                                            <p style='word-wrap: break-word'>Remark : ".$row['Remark']."</p>
                                        </div>
                                        ";
                                    $str .="
                                    <li name='btns_dir_app' id='btns".$na_t."_".$row['request_id']."'>
                                        <button type='button' class='btn btn-outline-success btn-sm shadow' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../GM\")' name='btntype' value='approve_".$na_t."_".$row['request_id']."' id='approve_".$na_t.$row['request_id']."'>Approve <i class='text-white text-white far fa-thumbs-up fa-fw'></i></button> 
                                        <button type='button' class='btn btn-outline-danger btn-sm shadow' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../GM\",\"remove\")' name='btntype' value='reject_".$na_t."_".$row['request_id']."' id='reject_".$na_t.$row['request_id']."'>Reject <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                        <button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>
                                        </li>
                                    <li id='loading_".$na_t.$row['request_id']."' class='d-none'> <div class='spinner-border text-primary'></div> </li>
                                    ";
                                $str .="
                                </ul>
                            </div>
                        </div>";
                    }
                divcreate($str,ucwords($type));
            }
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
<script>
function add_btn(e){
 document.getElementById('optional_btn').innerHTML=document.getElementsByName('btns_dir_app')[0].innerHTML;
openmodal(e);
}
</script>