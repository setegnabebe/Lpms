<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
if(isset($_GET['request']))
{
    $na_t=explode("_",$_GET['request'])[0];
    $type = ($na_t == "All")?$na_t:na_t_to_type($conn,$na_t);
    $req_stat=explode("_",$_GET['request'])[1];
}
else
{
    $na_t="All";
    $type="All";
    $req_stat="All";
}
 
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

$name = ($type == "All")?$req_stat:$req_stat." ".$type;
$requests_tab = true;
?>
<?php 
if(isset($requests_tab)) {
    $gets = "";
    foreach($_GET as $att => $val)
    {
        $gets .= $att."=".$val."&";
    }
}
    ?>
<script>
    set_title("LPMS | View Purchase Orders");
    sideactive("POs");
    var on=false;
    function typeclick(e)
    {
        let btn = document.getElementById('changed');
        btn.name = "request";
        btn.value = document.getElementById('req_type').value;
        btn.click();
    } 
    function batch_select(e)
    {
        let similar_ch = document.getElementsByClassName(e.className.split(" ")[0]);
        for(let i=0;i<similar_ch.length;i++)
        {
            if(e.checked)
                {
                    similar_ch[i].checked=true;
                }
            else
                {
                 
                    similar_ch[i].checked=false;
                }
        }
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
<?php
$requests = array(
    'All Complete'=>"Purchase from request to handover",
    'Approved'=>"Purchase for which committee has approved",
    "All Payment Processed"=>"Purchase for which payment process is done and ready for collection",
    'Approved By Dep.Manager'=>"purchase Approved By Department Manager",
    'Approved By GM'=>"Purchase Approved By GM",
    'Approved By Property'=>"Purchase Approved By Property manager",
    'canceled'=>" The purchases that has been canceled",
    'Cheque Prepared'=>"Purchase for which check has been prepared",
    'Collected-not-comfirmed'=>"Item collected but not confirmed by department manager",
    'Committee Approval'=>"Purchase that has reached committee approval",
    'Finance Approved'=>" Purchase approved by financial manager",
    'Finance Approved Petty Cash'=>"Purchaser approved by finance for pettey cash",
    'Found In Stock'=>"Item that was found in stock",
    'Generating Quote'=>"Item for that peroforma is being collected",
    'In-Stock'=>"Item has been received by property department",
    'Payment Processed'=>"Payment has been processed and purchase ready for collection",
    'Petty Cash'=>"Payment is being processed by petty cash",
    'Petty Cash Approved'=>"Petty cash has been approved by finance manager for petty cash",
    'Recollect'=>"Item has been sent for recollection",
    'Rejected By Dep.Manager'=>"Purchase that has been rejected by department manager",
    'Rejected By GM'=>"Purchase that has been rejected by GM",
    'Rejected By Director'=>"Purchase that has been rejected by director",
    'Rejected By Owner'=>"Purchase that has been rejected by memeber owners",
    'Rejected By Property'=>"Purchase that has been rejected by property manager",
    'Reviewed'=>"Purchase that has been reviewed by disburement And collection manager",
    'Sent to Finance'=>"Purchase sent to finance by procurnment manager after committees approval",
    'waiting'=>"Purchase waiting for department manager approval"
  );
  $detail="";
foreach($requests as $key=>$value){
    $detail.="$key => $value <br>";
}
?>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <ol class="breadcrumb">    
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active "><a <?php echo isset($_GET['user']) || isset($_GET['username'])?"href='requests.php'":""?> >Purchase Orders</a></li>
           <?php  
           echo isset($_GET['user'])?'<li class="breadcrumb-item">My POs</li>':""; 
           echo isset($_GET['username'])?'<li class="breadcrumb-item">My Activities</li>':""; 
           ?>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>

<div class='row mb-2'>
    <div class='col-sm-3 col-md-3'>
        <?php req_count($conn,$conn_fleet,$type,"",$req_stat); ?>
     
    </div>
    <div class='float-end col mx-4'>
    <a class='<?php echo !isset($_GET['user'])?"btn btn-sm btn-outline-success":" d-none"?>' href="<?php echo $pos?>../requests/requests.php?user=<?php echo $_SESSION['username']?>"><i class='fas fa-eye'></i> My POs</a>
    <a class='<?php echo !isset($_GET['username'])?"btn btn-sm btn-outline-info ml-8 text-dark hover::text-white":" d-none"?>' href="<?php echo $pos?>../requests/requests.php?username=<?php echo $_SESSION['username']?>"><i class="fas fa-user"></i>My Activities</a></div>
    <div class='float-end col'><a class='btn btn-sm btn-primary' href="<?php echo $pos?>../requests/comparisionSheets.php"><i class='fas fa-eye'></i> Comparision Sheets</a></div>
</div>
<div class='row'>
   <?php 
   if($_SESSION['company']=='Hagbes HQ.'){
   echo "<form method='GET'  class='col-sm-5 col-md-2' data-aos='fade-right'>".
        "<select class='form-select text-primary mb-3' id='req_company' onchange=\"query_search2(this)\">".
        '<option value="all" class="text-center">Company </option>'.
        '<option value="all"> ALL</option>'.
        $status_sql="SELECT DISTINCT (company) FROM `requests`  WHERE `processing_company` = ? or  `company` = ? ORDER BY company ASC;";
        $stmt_request_company = $conn -> prepare($status_sql);
        $stmt_request_company -> bind_param("ss", $_SESSION['company'] ,$_SESSION['company']);
        $stmt_request_company -> execute();
        $result_request_company = $stmt_request_company -> get_result();
        if($result_request_company -> num_rows>0)
        while($status_row = $result_request_company -> fetch_assoc())
        {
        echo "<option>".$status_row['company']."</option>";
        }
       echo "</select></form>";
    }
    ?>
    <div  class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-5 col-md-2':'col-sm-5 col-md-3'?>' data-aos="fade-right">
    <div class="input-group">
        <select class='form-select text-primary mb-3' data-mdb-icon="*" id='req_status' onchange="query_search2(this)">
        <option value="all" class='text-center'>Status </option>
        <?php
        $status_sql="SELECT DISTINCT(status) FROM `requests` WHERE  `processing_company` = ? or  `company` = ? ORDER BY `status` ASC";
        $stmt_request_status = $conn -> prepare($status_sql);
        $stmt_request_status -> bind_param("ss", $_SESSION['company'] ,$_SESSION['company']);
        $stmt_request_status -> execute();
        $result_request_status = $stmt_request_status -> get_result();
        if($result_request_status -> num_rows>0)
        while($status_row = $result_request_status -> fetch_assoc())
        {
        echo "<option title='".$requests[$status_row['status']]."'>".$status_row['status']."</option>";
        }
        ?>
        </select>
        <a type='button' class='fs-5 report mt-2' data-bs-toggle="modal" data-bs-target="#status_info" >
        <i class='fa fa-info-circle' title='Request status details'></i></a>
    </div>
        <button class='d-none' id='changed'></button>
    </div>
    <div class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-3'?>' data-aos="fade-right">
         <input type="text" class='form-control text-primary' name="daterange" value=""  />
        <button   class='d-none' id='date' type="button"   onclick="query_search2(this)" value=''></button>
        <button class='d-none' id='changed_start'></button>
    </div>
    <div class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-5 col-md-2':'col-sm-5 col-md-2'?>' data-aos="fade-right">
    <select class='form-select text-primary mb-3' id='req_type' value='<?php echo $key?>' onchange="query_search2(this)">
                <?php
                if($na_t == "All")
                    echo "<option id='All_All' value='All_All'>All</option>";
                else
                {
                    echo "<option id='".$na_t."_All' value='".$na_t."_All'>$type</option>";
                    echo "<option id='All_All' value='All_All'>All</option>";
                }
                $sql_category = "SELECT * from catagory";
                $stmt_catagory = $conn -> prepare($sql_category);
                $stmt_catagory -> execute();
                $result_catagory = $stmt_catagory -> get_result();
                if($result_catagory -> num_rows>0)
                    while($ree = $result_catagory -> fetch_assoc())
                    {
                        $na_t_1=str_replace(" ","",$ree['catagory']);
                        if($na_t == "All" || $na_t != $na_t_1)
                        echo "<option title='$na_t_1"."_All' id='$na_t_1"."_All' value='".$ree['catagory']."'>$ree[display_name]</option>";
                    }?>
        </select>
        <button class='d-none' id='changed'></button>
                </div>
                <div class='col-sm-5 col-md-3' data-aos="fade-right">
    <div class="input-group mb-3 ">
  <input type="text" class="form-control" placeholder="General Search" 
                aria-label="Search" id='search_value' aria-describedby="button-addon2" onkeydown='search(event)'>
  <span class="input-group-text outline-none"><a type='button'><button class="btn btn-outline-primary" type="button" id="search_btn" onclick="query_search2(this)"><i
                    class="bi bi-search"></i></button></a></span>
</div>   
<div id="user" title='<?php echo isset($_GET['user'])?$_GET['user']:""?>'></div> 
                </div>
</div>
<?php include 'tbl-div.php';?>
    <div class="container-fluid">
<div id='batch_div' class="position-fixed d-none" style="z-index:100; top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="print.php">
        <div class=''>
            <button type='submit' class=' btn btn-xl btn-primary  shadow mt-3' name='batch_print' id='batch_print'><i class='text-light fas fa-print'></i></button>
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
            $sql = "SELECT *,requests.department as department, requests.request_id as request_id FROM requests LEFT JOIN specification on requests.request_id = specification.request_id";
            if($req_stat=='Completed')
                {
                    $F_cond .=" WHERE `recieved`='yes'";
                }
            else if($req_stat=='All')
                {
                    $F_cond .=" WHERE 1";
                }
            else
                {
                    $F_cond .=" WHERE `recieved`='not'";
                }
            if($na_t!='All')
                {
                    $F_cond .=" AND `request_type`='$type'";
                }
        
            if($req_stat=='Rejected') $F_cond.=" AND (`status` LIKE 'Reject%' OR `status` = 'canceled')";
            else if($req_stat=='Pending') $F_cond.=" AND `status` NOT LIKE 'Reject%' AND `status` != 'canceled'";
            if(isset($_GET['username'])){
                $F_cond.=  " AND `customer`='".$_SESSION["username"]."' or manager='".$_SESSION["username"]."' or `GM`='".$_SESSION["username"]."' or `director`='".$_SESSION["username"]."' or `owner`='".$_SESSION["username"]."' or `property`='".$_SESSION["username"]."' ORDER BY date_requested DESC";

            }
           else if(isset($_GET['user']))
            {
                $F_cond.=  " AND `customer`='".$_SESSION["username"]."' ORDER BY date_requested DESC";
            }

            else
            {
                if(isset($_SESSION["managing_department"]) && strpos($_SESSION["a_type"],"Committee") === false)
                {
                    if(!in_array("All Departments",$_SESSION["managing_department"]))
                    {
                        $temp_cond = "";
                        foreach($_SESSION["managing_department"] as $depp)
                            $temp_cond .=($temp_cond == "")?"requests.department = '$depp'":"OR requests.department = '$depp'";
                        $temp_cond .= ($temp_cond == "")?"requests.department = '$_SESSION[department]'":"OR requests.department = '$_SESSION[department]'";
                        $F_cond .= " AND ( $temp_cond )";
                    }
                }
                $F_cond .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || $_SESSION["department"]=='Procurement' || $_SESSION["department"]=='Property' || $_SESSION["department"]=='Finance')?"":" AND company = '". $_SESSION['company']."'";
                $F_cond .= ($_SESSION["department"]=='Property')?" AND (property_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
                $F_cond .= (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property' && $_SESSION["department"]!='Finance' && strpos($_SESSION["a_type"],"Committee") === false)?" AND requests.department='".$_SESSION["department"]."'":"";
                $F_cond .= ($_SESSION["department"]=='Procurement' && $_SESSION['company'] == 'Hagbes HQ.' &&  ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false))?"":(($_SESSION["department"]=='Procurement')?" AND (procurement_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"");
                $F_cond .= ($_SESSION["department"]=='Finance')?" AND (finance_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
                $F_cond.=  ($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property' && $_SESSION["department"]!='Finance')?" AND `customer`='".$_SESSION["username"]."' ORDER BY date_requested DESC":" ORDER BY date_requested DESC";
            }
            $not_datatable = true;
            $stmt_fetch_requests = $conn -> prepare($sql.$F_cond);
            // $stmt_fetch_requests -> bind_param("ss", $_SESSION['company'] ,$_SESSION['company']);
            $stmt_fetch_requests -> execute();
            $result_fetch_requests = $stmt_fetch_requests -> get_result();
            $total_num = $result_fetch_requests -> num_rows;
            $per_page=(isset($_GET['per_page']))?$_GET['per_page']:40;
            $page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
            $offset=($page_num-1)*$per_page;
            $amount = ceil($total_num/$per_page);
            $sql .= $F_cond." LIMIT $per_page";// OFFSET $offset";
            $_SESSION['f_cond'] = $F_cond;
            $ch=false;
            $has_issue = true;
            $stmt_fetch_requests_limited = $conn -> prepare($sql);
            // $stmt_fetch_requests_limited -> bind_param("ss", $_SESSION['company'] ,$_SESSION['company']);
            $stmt_fetch_requests_limited -> execute();
            $result_fetch_requests_limited = $stmt_fetch_requests_limited -> get_result();
            if($result_fetch_requests_limited -> num_rows>0)
                while($row = $result_fetch_requests_limited -> fetch_assoc())
                {
                    $btn_close = "";
                    $avail = true;
                    $forbiden_stats = ['canceled','Rejected','Collected-not-comfirmed','Collected','In-stock','All Complete'];
                    foreach($forbiden_stats as $s)
                        if(strpos($row['status'],$s)!==false || $row['status'] == $s) $avail = false;
                    if(((($_SESSION['company'] == $row['procurement_company'] || $_SESSION['company'] == 'Hagbes HQ.') && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail)
                    {
                        $btn_close = "
                        <form method='GET' action='allphp.php' class='float-end'>
                            <button class='btn btn-outline-danger btn-sm' name='close_req' value='$row[request_id]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\",\"Red\")'>Close Request</button>
                        </form>";
                    }
                    $stmt_po_by_request -> bind_param("i", $row['request_id']);
                    $stmt_po_by_request -> execute();
                    $result_po_by_request = $stmt_po_by_request -> get_result();
                    $cluster_id="";
                    $row_po="";
                    if($result_po_by_request->num_rows>0)
                    {
                        $row_po = $result_po_by_request->fetch_assoc();
                        $view_cs = (!is_null($row_po['cluster_id']))?"<button type='button' name='".$row_po['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>Comparision Sheet</button>":"";
                    }
                    else $view_cs = "";
                    $type=$row['request_type'];
                    $na_t=str_replace(" ","",$type);
                    $dlt_btn = '';
                    $dlt_btn2= '';
                    // $tbl_data .= take_data($tbl_data,$row,$type);
                    $batch_print = "
                    <span class='small text-secondary float-start'>
                        <input value='".$row['request_id'].":|:$type' class='ch-$row[request_id] ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                    </span>";
                    $batch_print2 = "
                    <span class='small text-secondary float-start'>
                        <input value='".$row['request_id'].":|:$type' class='ch-$row[request_id] form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                    </span>";
                    $printpage = "
                    <form method='GET' action='print.php' class='float-end'>
                        <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row['request_id'].":|:$type'>
                        <i class='text-dark fas fa-print'></i>
                        </button>
                    </form>";
                    if($row['status']=='waiting' && $row['customer']==$_SESSION["username"])
                    { 
                        $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger border-0' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                        $dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger border-0 float-end' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                        $size = 10;
                    }
                    include 'tbl_code.php';

                    $size = 12;
                    $str.="<div class='col-md-6 col-lg-4 col-xl-3 my-4 focus position-relative'>
                    <div class='box shadow'>";
                    if($row['status']=='waiting')
                    {
                        $str .= "<h3>
                            $batch_print2
                        <span class='text-capitalize col-$size' id='title_".$row['request_id']."'>";
                        $str.= (($row['status']=="Rejected By Manager" || $row['status']=="Rejected" || $row['status']=="waiting") && $row['customer']==$_SESSION["username"])?
                        "<button id='".$na_t."_".$row['request_id']."' type='button' class='btn btn-outline-light btn-sm' onclick='Edit_loader(this)'  data-bs-toggle='modal' data-bs-target='#EditModal'><i class='fas fa-edit text-secondary mx-auto'></i></button>":"";
                        $str .=  "<button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                        </button>
                        $printpage</span>$dlt_btn";
                    }
                    else
                        $str.= ($row['status']=="Rejected By Manager" || $row['status']=="Rejected")?"
                        <h3 class='text-capitalize'>
                        $batch_print2<button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                        <i class='fas fa-exclamation-circle text-danger'></i></button>$printpage":"<h3 class='text-capitalize' id='title_".$row['request_id']."'>
                        $batch_print2
                        <button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']." 
                         <i class='fas fa-check-circle text-success'></i></button>
                        $chtbtn
                         $printpage";
                         $uname = str_replace("."," ",$row['customer']);
                        $str.="<span class='small text-secondary d-block mt-2'>$type</span></h3>
                        <ul>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>".$row['customer']."</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='text-start'><span class='fw-bold'>Date Requested : </span>". date("d-M-Y", strtotime($row['date_requested']))."</li>
                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                        <li class='text-start' id='stat".$row['request_id']."'><span class='fw-bold'>Status :  </span>".getNamedStatus($row['status'],$row)."</li>";
                        $str.= (strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false)?
                        "<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>":"";
                        $str .= ($view_cs == "")?"":"<li class='text-end'>$view_cs</li>";
                        $str .= ($btn_close == "")?"":"<li>$btn_close</li>";
                        $str.= ((($row['status']=="Rejected By Manager" || $row['status']=="Rejected") && $row['customer']==$_SESSION["username"])?
                        "<li class='row' id='btn_list_".$row['request_id']."'><button name='".$na_t."_redo_".$row['request_id']."' type='submit' class='btn btn-warning btn-sm col-6'>Reactivate Request</button></li>":"")."</ul>
                    </div>
                    <form method='POST' action='../requests/issue.php' class='issue_btn d-none'>
                        <button value='requests_".$row['request_id']."' type='submit' name='issue' class='position-absolute top-0 start-50 translate-middle btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                            <i class='fas fa-reply'></i>
                        </button>
                    </form>
                    </div>";
                    //  onclick='modal_optional(this,\"issue\")' aria-expanded='false' data-bs-toggle='modal' data-bs-target='#view_optionalModal' class='issue_btn d-none'
                }
            if($str =='')
                $div_type = "
                <div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Purchase Orders</h3>
                    </div>
                </div>";
            else
                $div_type = divcreate($str);
            $tbl_format = table_create($tbl_head,$tbl_data,true,false,$has_issue);
            echo "  <h5 class='mt-4 text-center ' id='search_text2'></h5>
                <div id='tbl_view'>$tbl_format</div>
                <div class='d-none' id='div_view'>$div_type</div>
                ".(($amount<=1)?"":"
                <div id='load_more' class='container-fluid text-center'>
                    <button type='button' id='view_more_btn' title=1 class='btn btn-primary' name='1' value='$amount' onclick='readmore(this)'>
                        View More
                    </button>
                </div>")."";
        ?>
    </div>
    </div>
</div>
    <script> 
function query_search2(e,offset=0)
{
    var type=document.getElementById('req_type').value;
    var comp_res="",status_res="", date_res="", type_res="";//,search_res="",, keyword_res="";
    var comp=document.getElementById("req_company");
    var status=document.getElementById("req_status").value;
    var date=document.getElementById('date').value;
    var key=document.getElementById('search_value').value;
    let view_btn = document.getElementById("view_more_btn");
    var user=document.getElementById('user').title;
    
    if(type&&type!="All_All"){
        type_res=" Type = "+type;
    }
    if(view_btn)
    var temp_name=view_btn.innerHTML;
    if(offset)
    view_btn.innerHTML = "<i class='fa fa-spinner fa-pulse'></i> Loading";
            date_res=date;
             if(comp){
               company=comp.value;
               if(company!="all")
                comp_res="Company ='"+company+"' "
              }
              else
             company="";
              if(status!=""&&status!="all"){
                  status_res=" Status ='"+status+"'  " 
              }
   
        var req = new XMLHttpRequest();
        var xx = document.getElementsByClassName('searched');
        for (let item of xx) {
            item.classList.add('d-none');
        }
        var tbl=document.getElementById('tbl_view');
        var tbl_bdy=document.getElementById('tbl_bdy');
        var req_counter=document.getElementById('req_count_body');
      
        req.onload = function(){
            var data=this.responseText.split(":__:");
            document.getElementById('searched').classList.remove('d-none');
            if(offset==0)
         {
            document.getElementById('searched').innerHTML=data[0];
            tbl.innerHTML=data[1];
        }
        else{
        document.getElementById('searched').innerHTML+=data[0] ;
        tbl_bdy.innerHTML+=data[5];
        }
 
            req_counter.innerHTML=data[3];
            if(!(type=="All_All"&&company=="all"&&status=="all"&&key==""&&date=="")){
            document.getElementById('search_text2').classList.remove('d-none');
            
            document.getElementById('search_text2').innerHTML=data[2]+"  "+comp_res+" "+status_res+" "+date_res+" "+type_res;
            }else{
                document.getElementById('search_text2').classList.add('d-none');
            }
            view_btn.innerHTML = temp_name;
            document.getElementById('load_more').innerHTML=data[4];
            setIssueBtn();
                   }
        req.open("GET", pos+"../requests/search.php?user="+user+"&offset="+offset+"&param="+key+"&from="+type+"&status="+status+"&company="+company+'&start='+date.split(" to ")[0]+'&end='+date.split(" to ")[1]);
        req.send();
}
function read_more(e)
    {
        let page_num = parseInt(e.name)+40;
        e.name = page_num;
        query_search2(e,e.name);
    }
    function search(event){
       if(event.keyCode==13){
        query_search2(event)
       }
    }
    var viewmore=document.getElementById('view_more_btn');
   if(viewmore.name >= viewmore.value)
   document.getElementById('view_more_btn').classList.add('d-none')
   else
   document.getElementById('view_more_btn').classList.remove('d-none')

    var temp = document.getElementById('searched').innerHTML;



    function readmore(e)
    {
        let temp_name = e.innerHTML;
        let page_num = parseInt(e.name)+1;
        e.name = page_num;
        if(page_num == e.value)
            e.classList.add("d-none");
        else
            e.classList.remove("d-none");
            
        e.innerHTML = "<i class='fa fa-spinner fa-pulse'></i> Loading";
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("searched").innerHTML+=this.responseText;
        e.innerHTML = temp_name;
        }
        req.open("GET", "readmore.php?page_num="+page_num);
        req.send();
        const req_tbl = new XMLHttpRequest();
        req_tbl.onload = function(){//when the response is ready
        document.getElementById("tbl_bdy").innerHTML+=this.responseText;
        setIssueBtn();
        }
        req_tbl.open("GET", "readmore.php?page_num="+page_num+"&tbl=");
        req_tbl.send();
 
    }
  
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    "showDropdowns": true,
    "linkedCalendars": false,
    "showCustomRangeLabel": false,
  }, function(start, end, label) {
    var btn=document.getElementById('date');
    btn.value=start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD');
    btn.click();
  });
});
        </script>

<?php include '../footer.php';?>
