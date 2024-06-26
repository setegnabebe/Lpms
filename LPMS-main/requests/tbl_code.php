<?php
 
    // $temp_t_i = (isset($r["project_id"]))?$r["Name"]:((isset($r["description"]))?$r["description"]:((isset($row["request_for"]))?$row["request_for"]:$type));
    $temp_t_i = $type;
    // $temp_t_i .= (isset($r["project_id"]))?" | ".$r["Name"]:((isset($r["description"]))?" | ".$r["description"]:((isset($row["request_for"]))?" | ".$row["request_for"]:""));
    $temp_t_i = $temp_t_i."::_::";
    if(isset($remove_type_table))
    {
        $temp_t_i = "";
    }
    include "chat_cond.php";
    $tbl_no = (isset($tbl_no))?$tbl_no+1:1;
    $tbl_data.=($tbl_data != "")?"==":"";
    $batch = (isset($batch_print))?$batch_print:"";
    $item_container = "<button  class='btn btn-sm btn-outline-primary' type='button' name='".$row['purchase_requisition']."' onclick='purchase_requisition(this)' data-bs-toggle='modal' data-bs-target='#purchase_requisitions'  value='".$row['recieved']."'>".$row['item']."
    </button>
    $chtbtn ".$printpage;
    $tbl_data.= $tbl_no."::_::".$batch.$row['customer']."::_::".$item_container."::_::".$temp_t_i.$row['company']."::_::".$row['department']."::_::".date("d-M-Y",strtotime($row['date_requested']))."::_::"
    .date("d-M-Y",strtotime($row['date_needed_by']))."::_::".getNamedStatus($row['status'],$row);
    if(isset($has_issue))
    {
        $tbl_data .= 
        "<form method='POST' action='../requests/issue.php' class='issue_btn d-none d-inline'>
            <button value='requests_".$row['request_id']."' type='submit' name='issue' class='btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='Raise Issue if any'>
                <i class='fas fa-reply'></i>
            </button>
        </form>";
        //  onclick='modal_optional(this,\"issue\")' aria-expanded='false' data-bs-toggle='modal' data-bs-target='#view_optionalModal' class='issue_btn d-none'
    }
    if(isset($view_cs))$tbl_data.= " ".$view_cs;
    if(isset($btn_close))$tbl_data.= " ".$btn_close;
?>
<!-- position-absolute top-0 start-50 translate-middle  -->
 
 