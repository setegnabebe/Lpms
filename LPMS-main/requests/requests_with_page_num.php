<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");

$na_t=explode("_",$_GET['request'])[0];
$type = ($na_t == "All")?$na_t:na_t_to_type($conn,$na_t);
$req_stat=explode("_",$_GET['request'])[1];
 
function divcreate($str)
{
    return "
        <div class='pricing'>
            <div class='row' id='searched'>
                $str
            </div>
        </div>
    ";
}
echo "<script>var active_type = \"$na_t"."_"."$req_stat\";</script>";
$name = ($req_stat == "All" && $type == "All")?$req_stat:$req_stat." ".$type;
?>
<script>
    set_title("LPMS | View Requests");
    sideactive("POs");
    var on=false;
    function typeclick(e)
    {
        let btn = document.getElementById('changed');
        btn.name = "request";
        btn.value = document.getElementById('req_type').value;
        btn.click()
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
        document.getElementById("batch_print").value = selections;
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
        <h2><?php echo $name?> Requests</h2>
        <ol class="breadcrumb">    
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <!-- <li class="breadcrumb-item"><a href='projectList.php' style="text-decoration: none;">Projects List</a></li> -->
            <li class="breadcrumb-item active"><?php echo $name?> Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class='row'>
    <div class='col-sm-12 col-md-8'>
        <?php req_count($conn,$conn_fleet,$type); ?>
        <!-- req_count($conn,$conn_fleet,'Spare and Lubricant', ",request_for,".$_SESSION["request_for"]) -->
    </div>
    <form method="GET" action='' class='col-sm-6 col-md-4' data-aos="fade-right">
        <div class="input-group mb-3 d-none" id="search_requests">
            <span class="input-group-text" id="basic-addon1"><i
                    class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Search"
                aria-label="Search" aria-describedby="button-addon2" onchange="query_search(this.value,'All')" onkeyup="query_search(this.value,'All')">
        </div>
        <select class='form-select text-primary mb-3' id='req_type' onchange="typeclick(this)">
            <option id="All_All" value="All_All">All</option>
            <option id="ConsumerGoods_All" value="ConsumerGoods_All">Consumer Goods</option>
            <option id="SpareandLubricant_All" value="SpareandLubricant_All">Spare / Lubricant</option>
            <option id="TyreandBattery_All" value="TyreandBattery_All">Tyre / Battery/Inner Tube</option>
            <option id="FixedAssets_All" value="FixedAssets_All">Fixed Assets</option>
            <option id="StationaryandToiletaries_All" value="StationaryandToiletaries_All">Stationary / Toiletaries</option>
            <option id="Miscellaneous_All" value="Miscellaneous_All">Miscellaneous</option>
        </select>
        <button class='d-none' id='changed'></button>
    </form>
</div>
<?php include 'tbl-div.php'?>
    <div class="container-fluid">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow" style="z-index:100; top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="print.php">
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
            $str="";
            $tbl_data = "";
            $tbl_head = "#,Requested By,Item,Type,Company,Department,Date Requested,Date Needed By,Status";
            $F_cond = "";
            $sql = "SELECT * FROM requests";
            if($req_stat=='Completed')
                {
                    $F_cond .=" WHERE `recieved`='yes'";
                }
            else if($req_stat=='All')
                {
                    $F_cond .=" WHERE `request_id` IS NOT NULL";
                }
            else
                {
                    $F_cond .=" WHERE `recieved`='not'";
                }
            if($na_t!='All')
                {
                    $F_cond .=" AND `request_type`='$type'";
                }
           
            if($req_stat=='_Rejected') $F_cond.=" AND (`status`='Rejected By Manager' or `status`='Rejected By Dep.Manager' or `status`='Rejected')";
            else if($req_stat=='_Pending') $F_cond.=" AND `status`!='Rejected By Manager' AND `status`!='Rejected By Dep.Manager' AND `status`!='Rejected'";
            $F_cond .= (strpos($_SESSION["a_type"],"manager") !== false && !isset($_SESSION["managing_department"]) && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND  department = '".$_SESSION['department']."'":"";
            if(isset($_SESSION["managing_department"]))
            {
                if(!in_array("All Departments",$_SESSION["managing_department"]))
                {
                    $temp_cond = "";
                    foreach($_SESSION["managing_department"] as $depp)
                        $temp_cond .=($temp_cond == "")?"department = '$depp'":"OR department = '$depp'";
                    $F_cond .= " AND ( $temp_cond )";
                }
            }
            $F_cond .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" ||( (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement')|| $_SESSION['additional_role']==1))?"":" AND company = '". $_SESSION['company']."'";
            $F_cond.=($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND `customer`='".$_SESSION["username"]."' ORDER BY date_needed_by ASC, request_id DESC":" ORDER BY date_needed_by ASC, request_id DESC";

            $stmt_fetch_requests = $conn -> prepare($sql.$F_cond);
            // $stmt_fetch_requests -> bind_param("s", $row['Name']);
            $stmt_fetch_requests -> execute();
            $result_fetch_requests = $stmt_fetch_requests -> get_result();
            $total_num = $result_fetch_requests->num_rows;
            $per_page=(isset($_GET['per_page']))?$_GET['per_page']:5;
            $page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
            $offset=($page_num-1)*$per_page;
            $amount = ceil($total_num/$per_page);
            $sql .= $F_cond." LIMIT $per_page OFFSET $offset";
            $ch=false;
            $stmt_fetch_requests_limited = $conn -> prepare($sql);
            $stmt_fetch_requests_limited -> execute();
            $result_fetch_requests_limited = $stmt_fetch_requests_limited -> get_result();
            if($result_fetch_requests_limited -> num_rows>0)
                while($row = $result_fetch_requests_limited -> fetch_assoc())
                {
                    $type=$row['request_type'];
                    $na_t=str_replace(" ","",$type);
                    // $tbl_data .= take_data($tbl_data,$row,$type);
                    $printpage = "<button type='button' title='item' class='btn btn-outline-secondary border-0 float-end' name='print_".$na_t."_".$row['request_id']."' onclick='print_page(this)'>
                    <i class='text-dark fas fa-print'></i>
                    </button>";
                    include 'tbl_code.php';
                    $size = 12;
                    $dlt_btn = '';
                    if($row['status']=='waiting' && $row['customer']==$_SESSION["username"])
                    {
                        $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger border-0' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                        $size = 10;
                    }
                    $str.="<div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                    <div class='box shadow'>";
                    if($row['status']=='waiting')
                    {
                        $str .= "<h3 class='row'>
                        <span class='small text-secondary float-start'>
                            <input value='".$row['request_id'].":|:$type' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span>
                        <span class='text-capitalize col-$size' id='title_".$row['request_id']."'>";
                        $str.= (($row['status']=="Rejected By Manager" || $row['status']=="Rejected" || $row['status']=="waiting") && $row['customer']==$_SESSION["username"])?
                        "<button id='".$na_t."_".$row['request_id']."' type='button' class='btn btn-outline-light btn-sm' onclick='Edit_loader(this)'  data-bs-toggle='modal' data-bs-target='#EditModal'><i class='fas fa-edit text-secondary mx-auto'></i></button>":"";
                        $str .=  "<button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']."
                        </button>$printpage</span>$dlt_btn";
                    }
                    else
                        $str.= ($row['status']=="Rejected By Manager" || $row['status']=="Rejected")?"
                        <h3 class='text-capitalize'>
                        <span class='small text-secondary float-start'>
                            <input value='".$row['request_id'].":|:$type' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']."
                        <i class='fas fa-exclamation-circle text-danger'></i></button>$printpage":"<h3 class='text-capitalize' id='title_".$row['request_id']."'>
                        <span class='small text-secondary float-start'>
                        <input value='".$row['request_id'].":|:$type' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                        </span>
                        <button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']." 
                         <i class='fas fa-check-circle text-success'></i></button>$printpage";
                        $str.="<span class='small text-secondary d-block mt-2'>$type</span></h3>
                        <ul>
                        <li class='text-start'><span class='fw-bold'>Number of Items : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='text-start'><span class='fw-bold'>Date Requested : </span>". date("d-M-Y", strtotime($row['date_requested']))."</li>
                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                        <li class='text-start' id='stat".$row['request_id']."'><span class='fw-bold'>Status :  </span>".$row['status']."</li>";
                        $uname = str_replace("."," ",$row['customer']);
                        $str.= (strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false)?
                        "<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>":"";
                        $str .= "<!--<li class='text-end'>View Details <i class='fas fa-clipboard-list fa-fw'></i></li>-->
                        <li class='row' id='btn_list_".$row['request_id']."'>";
                        $str.= (($row['status']=="Rejected By Manager" || $row['status']=="Rejected") && $row['customer']==$_SESSION["username"])?
                        "<button name='".$na_t."_redo_".$row['request_id']."' type='submit' class='btn btn-warning btn-sm col-6'>Reactivate Request</button>":"";
                    $str.=($row['status']=='waiting' && strpos($_SESSION["a_type"],"manager") !== false)?
                    "<button type='button' class='btn btn-outline-success btn-sm shadow col-6' onclick='update(this)' name='approve_".$na_t.$row['request_id']."' id='approve_".$na_t.$row['request_id']."'>Approve <i class='text-white far fa-thumbs-up fa-fw'></i></button> 
                    <button type='button' class='btn btn-outline-danger btn-sm shadow col-6' onclick='update(this)' name='reject_".$na_t.$row['request_id']."' id='reject_".$na_t.$row['request_id']."'>Reject <i class='text-white far fa-thumbs-down fa-fw'></i></button>":"";
                    $str.="   
                    </li></ul>
                    </div>
                    </div>";
                }
                ?></form><?php 
            if($str =='')
                $div_type = "
                <div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Requests Reported</h3>
                    </div>
                </div>";
            else
                $div_type = divcreate($str);
            $tbl_format = table_create($tbl_head,$tbl_data,true);

            $dis = ($page_num == 1)?" disabled":"";
            if($amount!=0)
            {
                echo "<form method='GET' action='$_SERVER[PHP_SELF]'>
                    <ul class='pagination justify-content-end' id='$amount'>
                    <div class='dataTable-dropdown me-3'>
                        <select class='dataTable-selector form-select form-select-sm' name='per_page' onchange='document.getElementById(\"active_page\").click();'>";
                        for($i=5;$i<=25;$i=$i+5)
                        {
                            $act = ($i==$per_page)?" selected=''":"";
                            echo "<option value='$i'$act>$i</option>";
                        }
                        echo "</select>
                    </div>
                        <li class='page-item$dis'><button type='submit' class='page-link me-2' name='page_num' value='".($page_num-1)."'>Previous</button></li>";
                        for($i=1;$i<=$amount;$i++)
                        {
                            $act = ($i==$page_num)?" active":"";
                            $act_id = ($i==1)?" id = 'active_page'":"";
                            echo "<li class='page-item$act'><button type='submit'$act_id class='page-link' name='page_num' value='".($i)."'>$i</button></li>";
                        }
                        $dis = ($amount==$page_num)?" disabled":"";
                        echo "<li class='page-item$dis'><button type='submit' class='page-link' name='page_num' value='".($page_num+1)."'>Next</button></li>
                    </ul>
                    <input name='request' value='$na_t"."_"."$req_stat' class='d-none'>
                </form>";
            }

            echo "
            <form method='GET' action='allphp.php'>
                <div id='tbl_view'>$tbl_format</div>
                <div class='d-none' id='div_view'>$div_type</div>
            </form>";
        ?>
    </div>
    </div>
</div>

    <script> var temp = document.getElementById('searched').innerHTML;
    document.getElementById(active_type).setAttribute("selected","");</script>

<?php include '../footer.php';?>
