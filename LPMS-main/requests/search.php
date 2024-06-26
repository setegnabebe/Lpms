<?php
session_start();
include '../connection/connect.php';
include '../common/functions.php';
function sync($str,$issuebtn)
{
    return "
        <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
            <div class='box shadow'>
                $str
            </div>
                $issuebtn
        </div>
    ";
}
function count_data($cond=1){
    include '../connection/connect.php';
    $sql = "SELECT count(status) as req_count from requests where $cond";
    $stmt_count_status = $conn -> prepare($sql);
    $stmt_count_status -> execute();
    $result_count_status = $stmt_count_status -> get_result();
    if($result_count_status -> num_rows>0)
    {
        $row=$result_count_status -> fetch_assoc();
        return $row['req_count'];
    }else
        return 0;
}
function get_cond(){
    $cond="";
    $cond .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || $_SESSION["department"]=='Procurement' || $_SESSION["department"]=='Property' || $_SESSION["department"]=='Finance')?"":" AND company = '". $_SESSION['company']."'";
    $cond .= ($_SESSION["department"]=='Property')?" AND (property_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
    $cond .= (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property' && $_SESSION["department"]!='Finance' && strpos($_SESSION["a_type"],"Committee") === false)?" AND `department`='".$_SESSION["department"]."'":"";
    $cond .= ($_SESSION["department"]=='Procurement' && $_SESSION['company'] == 'Hagbes HQ.' &&  ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false))?"":(($_SESSION["department"]=='Procurement')?" AND (procurement_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"");
    $cond .= ($_SESSION["department"]=='Finance')?" AND (finance_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
    $cond.=  ($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property' && $_SESSION["department"]!='Finance')?" AND `customer`='".$_SESSION["username"]."' ORDER BY date_requested DESC":"";
return $cond;
}
$common_string = "";
$common_string .=(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || $_SESSION["department"]=="Finance" || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin")?"":",customer='".$_SESSION["username"]."'";
$common_string .=(strpos($_SESSION["a_type"],"manager") !== false && !isset($_SESSION["managing_department"]) && $_SESSION["department"]!="Procurement" && $_SESSION["department"]!="Property" && $_SESSION["department"]!="Finance")?" and department='".$_SESSION['department']."'":"";
$common_string .=(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || $_SESSION["department"]=="Finance")?"":($_SESSION['company']=="Hagbes HQ."?"":"and company='".$_SESSION['company']."'");
$common_string .=($_SESSION["department"]=='Procurement' && $_SESSION['company'] == 'Hagbes HQ.' &&  ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false))?"":(($_SESSION["department"]=='Procurement')?" AND (procurement_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"");
$common_string .=($_SESSION["department"]=='Finance')?" AND (finance_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
$common_string .=($_SESSION["department"]=='Property')?" AND (property_company = '". $_SESSION['company']."' OR company = '". $_SESSION['company']."')":"";
//////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_SESSION["managing_department"]) && strpos($_SESSION["a_type"],"Committee") === false)
  {
      if(!in_array("All Departments",$_SESSION["managing_department"]))
      {
          foreach($_SESSION["managing_department"] as $depp)
              $common_string .=($common_string == "")?" and department='$depp'":" or department='$depp'";
      }
  } 

$amount = 0;
$tbl_head = "#,Requested By,Item,Type,Company,Department,Date Requested,Date Needed By,Status";
$tbl_data = "";
$filter="";
$user_cond="";
$status=$_GET['status'];
$param=$_GET['param']; 
$company=$_GET['company'];
if($company!="all" &&$company!=''){
    $filter.=" and company='$company' ";
}else if($company=='' && $_SESSION['company']!="Hagbes HQ.")
$filter.=" and company='".$_SESSION['company']."' ";
if($status!="all"){
    $filter.=" and status='$status' ";
}
$date_filter='';
    if($_GET['start']!=''&&$_GET['end']==''){
        $date_filter=" and datediff('".$_GET['start']."',date_requested)<0";
    }
    if($_GET['end']!=''&&$_GET['start']==''){
        $date_filter=" and datediff('".$_GET['end']."',date_requested)>0";
    }
    if($_GET['end']!=''&&$_GET['start']!=''){
        $date_filter=" and datediff('".$_GET['start']."',date_requested)<0 and datediff('".$_GET['end']."',date_requested)>0";
    }
    if(($_GET['end']==''||$_GET['end']=='undefined')&&($_GET['start']==''||$_GET['start']=='undefined')){
        $date_filter="";
    }

    if($_GET['user']!="")
    $user_cond=" and customer='".$_GET['user']."' ";

    $filter.=$user_cond.$date_filter;
  $str = "";
  $q_cond=($_GET['from']=="All_All"||$_GET['from']=="All"?"":"request_type = '".$_GET['from']."' AND ");
//   $q_cond=($_GET['from']=='All')?"":"request_type = '".$_GET['from']."' AND";
 $sql ="SELECT * from requests WHERE $q_cond request_id IS NOT NULL $filter";

 if(isset($_SESSION["managing_department"]) && strpos($_SESSION["a_type"],"Committee") === false)
 {
      if(!in_array("All Departments",$_SESSION["managing_department"]))
     {
          $temp_cond = "";
          foreach($_SESSION["managing_department"] as $depp)
              $temp_cond .=($temp_cond == "")?"department = '$depp'":"OR department = '$depp'";
         $sql .= " AND ( $temp_cond )";
     }
  }
   
  $sql .= get_cond();
  $all_keys = "(";
  $sql_keys = "show columns from requests";
  $stmt_keys = $conn -> prepare($sql_keys);
  $stmt_keys -> execute();
  $result_keys = $stmt_keys -> get_result();
  if($result_keys->num_rows>0)
  {
      while($row_keys = $result_keys->fetch_assoc())
      {
          $all_keys .= ($all_keys == "(")?"$row_keys[Field] LIKE '%$param%'":" OR $row_keys[Field] LIKE '%$param%'";
     }
  }
 $all_keys .= ")";
 if($param!="")
  $sql .= " AND ".$all_keys." $common_string ORDER BY request_id DESC";
  else
  $sql .= " $common_string ORDER BY request_id DESC";
 $offset=isset($_GET["offset"])?$_GET["offset"]:0;
 $tbl_no =  $offset;
 if($offset != 0)
 $tbl_head = "";
 $stmt_fetch_searched = $conn -> prepare($sql);
 $stmt_fetch_searched -> execute();
 $result_fetch_searched = $stmt_fetch_searched -> get_result();
 $total_num = $result_fetch_searched->num_rows;
//  echo $sql." limit $offset ,40";
 $stmt_fetch_searched_limited = $conn -> prepare($sql." limit $offset ,40");
 $stmt_fetch_searched_limited -> execute();
 $result_fetch_searched_limited = $stmt_fetch_searched_limited -> get_result();
  if($result_fetch_searched_limited->num_rows>0)
      while($row = $result_fetch_searched_limited->fetch_assoc())
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
        $type=$row["request_type"]; 
        $stmt_po_by_request -> bind_param("i", $row['request_id']);
        $stmt_po_by_request -> execute();
        $result_po_by_request = $stmt_po_by_request -> get_result();
        if($result_po_by_request -> num_rows>0)
        {
            $row_po = $result_po_by_request -> fetch_assoc();
            $view_cs = (!is_null($row_po['cluster_id']))?"<button type='button' name='".$row_po['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>Comparision Sheet</button>":"";
        }
        else $view_cs = "";
        $na_t=str_replace(" ","",$type);
        $flag = false;
        $showed =false;
        $found_index=0;
        if(isset($found_at)) unset($found_at);
        if(isset($ty_searched)) unset($ty_searched);
        foreach($row as $key => $data)
        {
            if($key == 'date_needed_by' || $key == 'date_requested')
                $data = date("d-M-Y", strtotime($data));
            else if($key == 'request_id' || $key == 'stock_info' || $key == 'next_step' || $key == 'to_replace' || $key == 'specification') continue;
            if(!is_NULL($data) && !is_NULL($param) && stripos($data, $param) !== false)
            {
                if($key == 'status' || $key == 'date_requested' || $key == 'date_needed_by' || $key == 'requested_quantity')
                    $showed =true;
                $flag = true;
                $found_at[$found_index] = $key;
                $found_index++;
            }
            if(!is_NULL($type) && !is_NULL($param) && stripos($type, $param) !== false)
            {
                $flag = true;
                $ty_searched =true;
            }
        }
        $flag = ($param == "")?true:$flag;
        if($flag)
        {
            $temp_t_i = $type;
            $temp_t_i = $temp_t_i."::_::";
            if(isset($remove_type_table))
            {
                $temp_t_i = "";
            }
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
            //$dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger border-0 float-end' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
           
            $size = 12;
            $dlt_btn = '';
            $dlt_btn2 = '';
            $str_temp ='';
            include "chat_cond.php";
            if(isset($found_at))
            {
                $sta = (is_numeric(array_search('status',$found_at)))?"text-primary":"";
                $quan = (is_numeric(array_search('requested_quantity',$found_at)) != 0)?"text-primary":"";
                $date_req = (is_numeric(array_search('date_requested',$found_at)) != 0)?"text-primary":"";
                $date_need = (is_numeric(array_search('date_needed_by',$found_at)) != 0)?"text-primary":"";
                $ty =(isset($ty_searched))? "text-primary":"text-secondary";
            }
            else
            {
                $quan =''; $date_req =''; $date_need =''; $sta ='';
                $ty ="text-primary";
            }
            if($row['status']=='waiting' && $row['customer']==$_SESSION["username"])
            {
                $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger border-0' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                $dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger border-0 float-end' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                $size = 10;
            }
            if($row['status']=='waiting')
            {
                $str_temp .= "<h3 class='row'>$batch_print2
                <!--<button id='undo_".$na_t.$row['request_id']."' name='undo_".$na_t.$row['request_id']."' type='button' onclick='update(this)' class='btn col-2 d-none'><i class='fas fa-undo'></i></button>-->
                <span class='text-capitalize col-$size' id='title_".$row['request_id']."'>";
                $str_temp.= (($row['status']=="Rejected By Manager" || $row['status']=="Rejected" || $row['status']=="waiting") && $row['customer']==$_SESSION["username"])?
                "<button id='".$na_t."_".$row['request_id']."' type='button' class='btn btn-outline-light btn-sm' onclick='Edit_loader(this)'  data-bs-toggle='modal' data-bs-target='#EditModal'><i class='fas fa-edit text-secondary mx-auto'></i></button>":"";
                $str_temp .=  "<button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                </button>$printpage</span>$dlt_btn";
            }
            else
                $str_temp.= ($row['status']=="Rejected By Manager" || $row['status']=="Rejected")?"
                <h3 class='text-capitalize'>$batch_print2<button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                <i class='fas fa-exclamation-circle text-danger'></i></button>$printpage":"<h3 class='text-capitalize' id='title_".$row['request_id']."'>$batch_print2
                <button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                <i class='fas fa-check-circle text-success'></i></button>$printpage";
            $str_temp.="<span class='small $ty d-block mt-2 '>$type</span></h3>
            <ul>
            <li class='text-start'><span class='fw-bold'>Requested By : </span>".$row['customer']."</li>
            <li class='text-start $quan'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']."</li>
            <li class='text-start $date_req'><span class='fw-bold'>Date Requested : </span>". date("d-M-Y", strtotime($row['date_requested']))."</li>
            <li class='text-start $date_need'><span class='fw-bold'>Date Needed By : </span>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
            <li class='text-start $sta' id='stat".$row['request_id']."'><span class='fw-bold'>Status :  </span>".getNamedStatus($row['status'],$row)."</li>";
            if(isset($found_at))
                foreach($found_at as $fff)
                {
                    if($fff == 'date_needed_by' || $fff == 'date_requested' || $fff == 'status' || $fff == 'requested_quantity' || $fff == 'item') continue;
                    $amount ++;
                    $temp = str_replace('_',' ',$fff);
                    if($temp == 'request for' && strpos($row[$fff],"None|")!==false)
                    {
                        $result_found = explode("|",$row[$fff])[1];
                        $temp = "Job Number";
                    }
                    else
                        $result_found = $row[$fff];;
                        if($param!="")
                    $str_temp.="<li class='text-start text-primary' id='$fff'><span class='fw-bold'>$temp : </span>".$result_found."</li>";
                    if($amount == 1) break;
                }
                $amount = 0;
                $uname = str_replace("."," ",$row['customer']);
            $str_temp.= (strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false)?
            "<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>":"";
            $str_temp .= ($view_cs == "")?"":"<li class='text-end'>$view_cs</li>";
            $str_temp .= ($btn_close == "")?"":"<li>$btn_close</li>";
            $str_temp .= "<!--<li class='text-end'>View Details <i class='fas fa-clipboard-list fa-fw'></i></li>-->
            <li class='row' id='btn_list_".$row['request_id']."'>";
            $str_temp.= (($row['status']=="Rejected By Manager" || $row['status']=="Rejected") && $row['customer']==$_SESSION["username"])?
            "<button name='".$na_t."_redo_".$row['request_id']."' type='submit' class='btn btn-warning btn-sm col-6'>Reactivate Request</button>":"";
            $str_temp.="   
            </li></ul>";
            $issuebtn = "
            <form method='POST' action='../requests/issue.php' class='issue_btn d-none'>
                <button value='requests_".$row['request_id']."' type='submit' name='issue' class='position-absolute top-0 start-50 translate-middle btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                    <i class='fas fa-reply'></i>
                </button>
            </form>";
            $str .= sync($str_temp,$issuebtn);
            $tbl_no = (isset($tbl_no))?$tbl_no+1:1;
            $tbl_data.=($tbl_data != "")?"==":"";
            $batch = (isset($batch_print))?$batch_print:"";
            $item_container = "<button  class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
            </button>".$printpage.$dlt_btn2;
            $tbl_data.= $tbl_no."::_::".$batch.$row['customer']."::_::".$item_container."::_::".$temp_t_i.$row['company']."::_::".$row['department']."::_::".date("d-M-Y",strtotime($row['date_requested']))."::_::"
            .date("d-M-Y",strtotime($row['date_needed_by']))."::_::".getNamedStatus($row['status'],$row);
            $tbl_data .= 
            "<form method='POST' action='../requests/issue.php' class='issue_btn d-none d-inline'>
                <button value='requests_".$row['request_id']."' type='submit' name='issue' class='btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                    <i class='fas fa-reply'></i>
                </button>
            </form>";
            if(isset($view_cs))$tbl_data.= " ".$view_cs;
            if(isset($btn_close))$tbl_data.= " ".$btn_close;
        }
    }
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
    $type=$_GET['from'];
    $special="";
    $req_type=$type;
    $na_t = ($type =='All'||$type =='All_All')?"All":$na_t;
    if($req_stat != "")
    {
        $all_active =""; $comp_active =""; $pend_active =""; $rej_active ="";
        if($req_stat=='All')
            $all_active ="list-group-item-warning";
        else if($req_stat=='Completed')
            $comp_active ="list-group-item-warning";
        else if($req_stat=='Rejected')
            $rej_active ="list-group-item-warning";
        else if($req_stat=='Pending')
            $pend_active ="list-group-item-warning";
    }
    else
    {
        $all_active =""; 
        $comp_active =""; 
        $pend_active =""; 
        $rej_active ="";
    }
    $na_t=str_replace(" ","",$req_type);
    $type = $req_type;
    $completed = 0; 
    $rejected = 0; 
    $pending = 0;

  ////////////////////////////////////////Common Filters////////////////////////////////////////////////////

    $comp_filter="";$type_filter="";$status_filter="";$type_cond=""; $user_cond="";
    $type=$_GET['from'];
    $coma = ($common_string == "")?"":",";
    if($company!="all")
    $comp_filter=" company,".$company;
    if($status!="All")
    $status_filter=", status,".$type;
$cond="";
$comp_cond="";$comp_type="";$cond_status="";
 if($company){
 if($company!='all')
    $comp_cond=" and company='$company' ";
 }
else
$comp_cond=" and company='".$_SESSION['company']."' ";

 
    if($_GET['from']!="All"&&$_GET['from']!="All_All")
    $type_cond=" and request_type='".$_GET['from']."' ";

    if($_GET['user']!="")
    $user_cond=" and customer='".$_GET['user']."' ";
 

 
if($status!="" && $status!='all')
$cond_status=" and status='$status' ";
$cond.=$date_filter;
$data_Sql="show columns from requests";
$stmt_columns = $conn -> prepare($data_Sql);
$stmt_columns -> execute();
$result_columns = $stmt_columns -> get_result();
$like="";
if($param){
$like=" and (";
if($result_columns -> num_rows>0)
while($data_res=$result_columns -> fetch_assoc()){
    $like.=$data_res['Field']." LIKE '%$param%' or ";
}
$like.=" 0 )";
}
$cond=$comp_cond.$type_cond.$cond_status.$date_filter.$user_cond;
 
$All=count_data("1 ".$cond.$common_string.$like);
$completed=count_data(" recieved!='not' $cond $common_string $like");
$rejected=count_data(" (status like '%Reject%' OR status = 'canceled') $cond $common_string $like");
$pending=count_data(" recieved='not' and status NOT like '%Reject%' and status != 'canceled' $cond  $common_string $like");
 
 
    $req_counter="<select class='form-control btn-primary'   >
    <option   value ='".$na_t."_All'  id = '".$na_t."_All' >Total POs :$All</option>
    <option value = '".$na_t."_Completed' id = '".$na_t."_Completed'><h6 ><i class='fas fa-check-circle text-success'></i> Completed POs</h6>:$completed</option>
    <option value = '".$na_t."_Rejected' id = '".$na_t."_Rejected'> <h6 ><i class='far fa-window-close text-danger'></i> Rejected POs</h6>:$rejected</option>
    <option  value = '".$na_t."_Pending' id = '".$na_t."_Pending'> <h6 ><i class='fas fa-exchange-alt text-info'></i> Active POs</h6>:$pending</option>
    </select>";
    $data="";
    $diff=$total_num-$offset;
$viemore=($diff)>40?"<button type='button' id='view_more_btn' title='$total_num' class='btn btn-primary' name='$offset' value='$amount' onclick='read_more(this)'>
View More
</button>":"";
if($total_num== 0){
$str = "
<div class='py-5 pricing'>
    <div class='section-title text-center py-2  alert-primary rounded'>
        <h3 class='mt-4'>No matching request found</h3>
    </div>
</div>";
$data=$str;
}else{
    $data=table_create( $tbl_head,$tbl_data,true,false);
}
 echo $str."<input type='hidden' id='length'  name='$amount'/> "." :__: ".$data." :__: Search result for ".$param.":__:".$req_counter.":__:".$viemore.":__:".table_create("",$tbl_data,true,false);
       
?>
<?php
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>
