<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
// $tbl_data = "";
// $tbl_head = "#,Requested By,Item,Type,Department,Date Requested,Date Needed By,Status";
$per_page=(isset($_GET['per_page']))?$_GET['per_page']:40;
$page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
$has_issue = true;
$offset=($page_num-1)*$per_page;
$t_num = $offset;
$ch=false;
$sql = "SELECT * FROM requests".$_SESSION['f_cond']." LIMIT $per_page OFFSET $offset";
$stmt_requests_fetch = $conn -> prepare($sql);
$stmt_requests_fetch -> execute();
$result_requests_fetch = $stmt_requests_fetch -> get_result();
if($result_requests_fetch -> num_rows > 0)
    while($row = $result_requests_fetch -> fetch_assoc())
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
        $t_num++;
        $stmt_po_by_request -> bind_param("i", $row["request_id"]);
        $stmt_po_by_request -> execute();
        $result_po_by_request = $stmt_po_by_request -> get_result();
        if($result_po_by_request->num_rows>0)
        {
            $row_po = $result_po_by_request->fetch_assoc();
            $view_cs = (!is_null($row_po['cluster_id']))?"<button type='button' name='".$row_po['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>Comparision Sheet</button>":"";
        }
        else $view_cs = "";
        $type=$row['request_type'];
        $na_t=str_replace(" ","",$type);
        // $tbl_data .= take_data($tbl_data,$row,$type);
        $size = 12;
        $dlt_btn = '';
        $dlt_btn2 = '';
        include "chat_cond.php";
        $printpage = "
        <form method='GET' action='print.php' class='float-end'>
            <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row['request_id'].":|:$type'>
            <i class='text-dark fas fa-print'></i>
            </button>
        </form>";
        $batch_print = "
        <span class='small text-secondary float-start'>
            <input value='".$row['request_id'].":|:$type' class='ch-$row[request_id] ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
        </span>";
        $batch_print2 = "
        <span class='small text-secondary float-start'>
            <input value='".$row['request_id'].":|:$type' class='ch-$row[request_id] form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
        </span>";
        if($row['status']=='waiting' && $row['customer']==$_SESSION["username"])
        { 
            $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger border-0' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
            $dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger border-0 float-end' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
            $size = 10;
        }
        if(isset($_GET['tbl']))
        {
            $item_container = "<button class='btn btn-sm btn-outline-primary me-2' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
            </button>$chtbtn".$printpage.$dlt_btn2;
            echo "<tr class='position-relative focus'>
                    <td class='text-capitalize'>
                        $t_num
                    </td>
                    <td class='text-capitalize'>
                        $batch_print
                        $row[customer]
                    </td>
                    <td class='text-capitalize'>
                        $item_container
                    </td>
                    <td class='text-capitalize'>
                        $type
                    </td>
                    <td class='text-capitalize'>
                        $row[company]
                    </td>
                    <td class='text-capitalize'>
                        $row[department]
                    </td>
                    <td class='text-capitalize'>
                    ".date("d-M-Y", strtotime($row['date_requested']))."
                    </td>
                    <td class='text-capitalize'>
                    ".date("d-M-Y", strtotime($row['date_needed_by']))."
                    </td>
                    <td class='text-capitalize issue_holder'>
                        ".getNamedStatus($row['status'],$row)."
                        <form method='POST' action='../requests/issue.php' class='issue_btn d-none d-inline'>
                            <button value='requests_".$row['request_id']."' type='submit' name='issue' class='btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                                <i class='fas fa-reply'></i> 
                            </button>
                        </form>$view_cs $btn_close
                    </td>
                </tr>";
                //  onclick='modal_optional(this,\"issue\")' aria-expanded='false' data-bs-toggle='modal' data-bs-target='#view_optionalModal' class='issue_btn d-none'
        }
        else
        {
            //  include 'tbl_code.php';
            if($row['status']=='waiting' && $row['customer']==$_SESSION["username"])
            {
                $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger border-0' id='Delete_".$na_t.$row['request_id']."'  name='Delete_".$na_t."_".$row['request_id']."'  onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";
                $size = 10;
            }
            echo "<div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
            <div class='box shadow'>";
            if($row['status']=='waiting')
            {
                echo  "<h3> 
                $batch_print2
                <span class='text-capitalize col-$size' id='title_".$row['request_id']."'>";
                echo  ($row['customer']==$_SESSION["username"])?
                "<button id='".$na_t."_".$row['request_id']."' type='button' class='btn btn-outline-light btn-sm' onclick='Edit_loader(this)'  data-bs-toggle='modal' data-bs-target='#EditModal'><i class='fas fa-edit text-secondary mx-auto'></i></button>":"";
                echo  "<button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                </button>$printpage</span>$dlt_btn";
            }
            else
                echo  ($row['status']=="Rejected By Manager" || $row['status']=="Rejected")?"
                <h3 class='text-capitalize'>
                $batch_print2
                <button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
                <i class='fas fa-exclamation-circle text-danger'></i></button>$printpage":"<h3 class='text-capitalize' id='title_".$row['request_id']."'>
                $batch_print2
                <button class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
               
                <i class='fas fa-check-circle text-success'></i></button>$printpage";
                echo "<span class='small text-secondary d-block mt-2'>$type</span></h3>
                <ul>
                <li class='text-start'><span class='fw-bold'>Requested By : </span>".$row['customer']."</li>
                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                <li class='text-start'><span class='fw-bold'>Date Requested : </span>". date("d-M-Y", strtotime($row['date_requested']))."</li>
                <li class='text-start'><span class='fw-bold'>Date Needed By : </span>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                <li class='text-start' id='stat".$row['request_id']."'><span class='fw-bold'>Status :  </span>".getNamedStatus($row['status'],$row)."</li>";
                $uname = str_replace("."," ",$row['customer']);
                echo  (strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false)?
                "<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>":"";
                echo ($view_cs == "")?"":"<li class='text-end'>$view_cs</li>";
                echo ($btn_close == "")?"":"<li>$btn_close</li>";
                echo  (($row['status']=="Rejected By Manager" || $row['status']=="Rejected") && $row['customer']==$_SESSION["username"])?
                "<li class='row' id='btn_list_".$row['request_id']."'><button name='".$na_t."_redo_".$row['request_id']."' type='submit' class='btn btn-warning btn-sm col-6'>Reactivate Request</button></li>":"";
            echo "   
            </ul>
            </div>
            <form method='POST' action='../requests/issue.php' class='issue_btn d-none'>
                <button value='requests_".$row['request_id']."' type='submit' name='issue' class='position-absolute top-0 start-50 translate-middle btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                    <i class='fas fa-reply'></i>
                </button>
            </form>
            </div>";
            //  onclick='modal_optional(this,\"issue\")' aria-expanded='false' data-bs-toggle='modal' data-bs-target='#view_optionalModal' class='issue_btn d-none'
        }
    }
    ?>