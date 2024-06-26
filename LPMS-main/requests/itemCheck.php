<?php 

session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
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
    sideactive("item_check");
    set_title("LPMS | Item Approval");
    var element,db;
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
        <h2>Approve Item Bought</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Item Bought</li>
        </ol>
    </div> 
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
    
    <div class='nav bg-light btn-group'>
    <button type='button' class='btn btn-white border-primary  mx-2 rounded-pill' onclick="show(this)">All Requests</button>
    <?php
    $emp =true;
    $departments = "";
    if(($_SESSION["role"]=="Director" || $_SESSION["role"]=="GM") && ($_SESSION["department"]=='GM' || in_array("All Departments",$_SESSION["managing_department"])) && $_SESSION["company"]!='Hagbes HQ.')
    {
        $sql_account = "SELECT * FROM `account` WHERE department not in (SELECT department from account where (role = 'manager' OR `type` LIke '%manager%') and company = ? group by department) and company = ?";
        $stmt_no_manager = $conn -> prepare($sql_account);
        $stmt_no_manager -> bind_param("ss", $_SESSION["company"], $_SESSION["company"]);
        $stmt_no_manager -> execute();
        $result_no_manager = $stmt_no_manager -> get_result();
        if($result_no_manager -> num_rows > 0)
            while($row_account = $result_no_manager -> fetch_assoc())
            {
                $departments .= ($departments == "")?"`department` = '".$row_account['department']."'":" OR `department` = '".$row_account['department']."'";
            }
    }
    if($departments == "")
        $departments = "`department` = '".$_SESSION['department']."'";
    $query = "SELECT request_type FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id WHERE ((R.flag = 0 AND R.status='Collected-not-comfirmed') OR (S.flag = 0 AND S.status = 'Approved')) AND `company` = ? AND ($departments) Group By request_type";
    $stmt_dep_check_request_type = $conn -> prepare($query);
    $stmt_dep_check_request_type -> bind_param("s", $_SESSION["company"]);
    $stmt_dep_check_request_type -> execute();
    $result_dep_check_request_type = $stmt_dep_check_request_type -> get_result();
    if($result_dep_check_request_type -> num_rows>0)
        while($re = $result_dep_check_request_type -> fetch_assoc())
        {
            $type = $re['request_type'];
            $query = "SELECT count(*) AS num FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id WHERE ((R.flag = 0 AND R.status='Collected-not-comfirmed') OR (S.flag = 0 AND S.status = 'Approved')) AND `company` = ? AND ($departments) AND request_type = ?";
            $stmt_dep_check_count = $conn -> prepare($query);
            $stmt_dep_check_count -> bind_param("ss", $_SESSION["company"], $re['request_type']);
            $stmt_dep_check_count -> execute();
            $result_dep_check_count = $stmt_dep_check_count -> get_result();
            if($result_dep_check_count -> num_rows>0)
                while($r = $result_dep_check_count -> fetch_assoc())
                {
                    if($r['num']>0)
                    {
                        $emp=false;
                        echo "<button type='button' class='btn btn-primary border-primary mx-2 rounded-pill' onclick='show(this)'>".$type."</button>";
                    }
                }
        }
    ?>
    </div>
    <div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
        <form method="GET" action="allphp.php">
            <div class=''>
                <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../requests")' class='btn btn-outline-success shadow mt-3' name='batch_approve_item_dep' id='batch_approve'>Approve</button>
                <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../requests","remove")' class='btn btn-outline-danger shadow mt-3' name='batch_reject_item_dep' id='batch_reject'>Reject</button>
            </div>
        </form>
        <div class="mt-3 form-check">
            <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
            <label class="form-check-label" for="checkboxAll">Select All</label>
        </div>
    </div>
    <form method="GET" action="allphp.php">
        <?php
        $query = "SELECT request_type FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id WHERE ((R.flag = 0 AND R.status='Collected-not-comfirmed') OR (S.flag = 0 AND S.status = 'Approved')) AND `company` = ? AND ($departments) Group By request_type";
        $stmt_dep_check_request_type = $conn -> prepare($query);
        $stmt_dep_check_request_type -> bind_param("s", $_SESSION["company"]);
        $stmt_dep_check_request_type -> execute();
        $result_dep_check_request_type = $stmt_dep_check_request_type -> get_result();
        if($result_dep_check_request_type -> num_rows>0)
            while($re = $result_dep_check_request_type -> fetch_assoc())
            {
                $type = $re['request_type'];
                $str = "";
                $na_t=str_replace(" ","",$type);
                $empty=true;
                $sql2 = "SELECT *, R.status AS `rstatus`, S.status AS `sstatus`, R.flag AS `rflag`, S.flag AS `sflag` FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id WHERE ((R.flag = 0 AND R.status='Collected-not-comfirmed') OR (S.flag = 0 AND S.status = 'Approved')) AND `company` = ? AND ($departments) AND request_type = ?";
                $stmt_dep_check = $conn -> prepare($sql2);
                $stmt_dep_check -> bind_param("ss", $_SESSION["company"], $type);
                $stmt_dep_check -> execute();
                $result_dep_check = $stmt_dep_check -> get_result();
                if($result_dep_check -> num_rows > 0)
                    while($row2 = $result_dep_check -> fetch_assoc())
                    {
                        $instocktag = "";
                        if($row2['sstatus'] == "Approved" && $row2['sflag'] == "0" && $row2['rstatus'] == "Collected-not-comfirmed" && $row2['rflag'] == "0")
                        {
                            $instocktag = "both";
                        }
                        else
                        {
                            if($row2['sstatus'] == "Approved" && $row2['sflag'] == "0")
                            $instocktag ="instock";
                        }
                        if($instocktag == "") $instocktag = "Purchase";
                        if($type=="Consumer Goods"){
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
                        else if($type=="Spare and Lubricant"){
                            $id=$row2['request_for'];
                            $stmt_description->bind_param("i", $row2['request_for']);
                            $stmt_description->execute();
                            $result3 = $stmt_description->get_result();
                            $res=($result3->num_rows>0)?true:false;  
                        }
                        else if($type=="Tyre and Battery")
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
                                if($type=="Consumer Goods")
                                {
                                    $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                                }
                                else if($type=="Spare and Lubricant")
                                    $name=$row3['description'];
                            }
                            if($type=="Spare and Lubricant" && $row2['request_for']==0) $name = "General Request";
                            $str.= "
                            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                                <div class='box'>
                                    <h3 class='text-capitalize'>
                                    <span class='small text-secondary float-start'>
                                    <input value='".$row2['request_id'].":-:$instocktag' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                                    </span>".$name."</h3>
                                    <ul>
                                        <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                                        <li class='text-start'><span class='fw-bold'>Company : </span>".$row2['company']."</li>
                                        <li class='text-start'><span class='fw-bold'>Requsted Item : </span>".$row2['item']."</li>
                                        <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                        <li class='text-start'><span class='fw-bold'>Status : </span>".$row2['rstatus']."</li>
                                        <li class='text-end'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal'
                                        data-bs-target='#item_details' onclick='openmodal(this)' > View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                                        <li class='text-start'>
                                            <button type='button' class='btn btn-outline-success btn-sm shadow' onclick='give_reason(this,\"../requests\")'  data-bs-toggle='modal' data-bs-target='#give_reason' name='approve_item_dep' value='".$row2['request_id'].":-:$instocktag'>Accept <i class='text-white text-white far fa-thumbs-up fa-fw'></i></button> 
                                            <button type='button' class='btn btn-outline-danger btn-sm shadow' onclick='give_reason(this,\"../requests\",\"remove\")'  data-bs-toggle='modal' data-bs-target='#give_reason' name='reject_item_dep' value='".$row2['request_id'].":-:$instocktag' id='reject_item_".$na_t.$row2['request_id']."'>Reject <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                            <button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            ";
                        }
                        if($str!='')
                            divcreate($str,$type);
            }
            if($emp)
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests at this Time</h3>
                        </div>
                    </div>";
        ?>
    </form>
</div>
<?php include '../footer.php';?>