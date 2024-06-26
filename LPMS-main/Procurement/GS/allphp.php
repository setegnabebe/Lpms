<?php
session_start();
include "../../connection/connect.php";
include "../../common/functions.php";


if(isset($_SESSION['username']))
{
    if(isset($_GET["complete_settlement"]))
    {
        $settlement = "Settled";
        $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `settlement`=? WHERE `purchase_order_id`=? ");
        $stmt_unique -> bind_param("si", $settlement, $_GET["complete_settlement"]);
        $stmt_unique -> execute();

        $stmt_po->bind_param("i", $_GET["complete_settlement"]);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();
            
        $record_type = "purchase_order";
        $operation = "Settlement";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_GET['complete_settlement'], $operation);
        $stmt_add_record -> execute();
        $na_t=str_replace(" ","",$row['request_type']);
        $date=date("Y-m-d H:i:s");
        $sql_rep = "UPDATE `report` SET `settlement_date` = ? WHERE `request_id` = ?";
        $stmt_rep_settlement = $conn->prepare($sql_rep);
        $stmt_rep_settlement -> bind_param("si",$date ,$row['request_id']);
        $stmt_rep_settlement -> execute();
        $reason_close = "open_req_".$na_t."_".$row['request_id']."_settlment";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        $sql_rep = "UPDATE `report` SET `settlement_date` = ? WHERE `request_id` = ?";
        $stmt_rep_settlement = $conn->prepare($sql_rep);
        $stmt_rep_settlement -> bind_param("si",$date ,$row['request_id']);
        $stmt_rep_settlement -> execute();
        $sql_complete_requests = "UPDATE requests set `status`='All Complete', `next_step`='All Complete' where request_id = ?";
        $stmt_complete_requests = $conn->prepare($sql_complete_requests);
        $stmt_complete_requests -> bind_param("i", $row['request_id']);
        $stmt_complete_requests -> execute();
        $sql_complete_po = "UPDATE purchase_order set status='All Complete' where purchase_order_id = ?";
        $stmt_complete_po = $conn->prepare($sql_complete_po);
        $stmt_complete_po -> bind_param("i", $row['purchase_order_id']);
        $stmt_complete_po -> execute();
        $_SESSION["success"]="Purchase $settlement";

        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////Assign Officer///////////////////////////////////////////////////////////////
    if(isset($_GET['assign_btn']))
    {
        $na_t = explode("_",$_GET['assign_btn'])[1];
        $type = na_t_to_type($conn,$na_t);
        $request_id = explode("_",$_GET['assign_btn'])[2];
        $out=$_GET[$na_t."_".$request_id];
        if($out!='')
        {
            $stmt_po_by_request->bind_param("i", $request_id);
            $stmt_po_by_request->execute();
            $result_po_by_request = $stmt_po_by_request->get_result();
            if($result_po_by_request -> num_rows == 0)
            {
                $stmt_request->bind_param("i", $request_id);
                $stmt_request->execute();
                $result_request = $stmt_request->get_result();
                if($result_request->num_rows>0)
                while($row2 = $result_request->fetch_assoc())
                {
                    $item = $row2['item'];
                    $company = $row2['company'];
                    $processing_company = $row2['processing_company'];
                    $property_company = $row2['property_company'];
                    $procurement_company = $row2['procurement_company'];
                    $finance_company = $row2['finance_company'];
                }
                $priority = explode("_",$_GET['assign_btn'])[3];
                $stmt = $conn->prepare("INSERT INTO `purchase_order` (`request_type`, `request_id`, `scale`, `purchase_officer`,`assigned_by`,`company`,`processing_company`, `property_company`, `procurement_company`, `finance_company`,`timestamp`,`priority`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $scale="not set";
                $date=date("Y-m-d H:i:s");
                $stmt -> bind_param("sisssssssssd",$type, $request_id, $scale, $out, $_SESSION['username'], $company, $processing_company, $property_company, $procurement_company, $finance_company, $date, $priority);
                if($stmt -> execute())
                {
                    $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=?");
                    $status="Generating Quote";
                    $stmt -> bind_param("si", $status, $request_id);
                    $stmt -> execute();
                    $_SESSION["success"]="Assigned $out for Quota Collection for ".$item." Item";
                    $sql_rep = "UPDATE `report` SET `officer_assigned_date` = ? WHERE `request_id` = ?";
                    $stmt_rep_assign = $conn->prepare($sql_rep);
                    $stmt_rep_assign -> bind_param("si",$date ,$request_id);
                    $stmt_rep_assign -> execute();
                    $stmt2 = $conn->prepare("SELECT `email`,`phone` FROM `account` where `Username`='".$out."'  and status='active'");
                    $stmt2->execute();
                    $stmt2->store_result();
                    $stmt2->bind_result($email,$phone_number);
                    $stmt2->fetch();
                    $stmt2->close();
                    $subject_email = "A purchase order For $item was assigned to you";
                    $data_email = "
                    <strong>A Purchase order for <span style='color:blue'>$item</span> was assigned to you for proforma collection<strong><br>
                    <strong>Please visit the website and accept as soon as possible<strong><br><br><br>
                    ";
                    $reason_close = "open_req_".$na_t."_".$request_id."_GS_assign";
                    $stmt_email_close -> bind_param("s",$reason_close);
                    $stmt_email_close -> execute();
                    $reason = "open_req_".$na_t."_".$request_id."_assigned";
                    $send_to = $email.",".$out;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $out;
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
                    
                    $sms_to = $out; $sms_from = $_SESSION['username'];
                    $msg = "A Purchase order was assigned to you for proforma collection please visit lpms.hagbes.com";
                    include "../../common/sms.php";
                    
                    $email_id = $conn->insert_id;
                    $page_to = "Procurement/junior/newJobs.php";
                    $stmt_email_page -> bind_param("si",$page_to, $email_id);
                    $stmt_email_page -> execute();
                }
            }
            updaterequest($conn,$conn_fleet,$request_id,"two","","Assign");
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////send_mail To Vendor///////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////// Email Vendor ////////////////////////////////////////////////////
 
    if(isset($_POST['email_vendor']))
    {
        $email_list=explode(',',$_POST['emails']);
        $request_ids=explode(',',$_POST['email_vendor']);
        foreach($request_ids as $id){
            $stmt_request -> bind_param("i", $id);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row = $result_request -> fetch_assoc();
            if(!is_null($row['specification']))
            {
                $stmt_specification -> bind_param("i", $row['specification']);
                $stmt_specification -> execute();
                $result_specification = $stmt_specification -> get_result();
                $row_spec = $result_specification -> fetch_assoc();
                if(!is_null($row_spec["pictures"]) && $row_spec["pictures"] != "")
                {
                    $spec = '<div style = "width:100%;display:block">
                    <h4 style = "align:center">Attached Specifications</h4>';

                    $allfiles = explode(":_:",$row_spec['pictures']);
                    foreach($allfiles as $file)
                    {
                        if(strpos($file,"pdf"))
                            $spec .= "
                                <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                    <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                </div>";
                        else
                            $spec .= "
                                <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                    <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank'>
                                        <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."' alt = 'Specifcation pictures'>
                                    </a>
                                </div>";
                    }
                    $spec .= "</div>";
                }
            }
        }
    $subject_email = $_POST['subject'];
    foreach ($email_list as $email_id){
        $stmt_vendor_specific -> bind_param("i", $email_id);
        $stmt_vendor_specific -> execute();
        $result_vendor_specific = $stmt_vendor_specific -> get_result();
        if($result_vendor_specific -> num_rows>0)
            $row_vendor=$result_vendor_specific -> fetch_assoc();
        $data_email = "Dear ".$row_vendor['vendor']."<br> ".$_POST['email_body'];
        if(isset($spec))
            $data_email=str_replace('[attachment]',$spec,$data_email);
        $send_to = explode(',',$row_vendor['email'])[0].",".$row_vendor['vendor'];
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $cc =""; $bcc = ""; $tag = $row_vendor['vendor'];
        $email_type='vendor_'.$_POST['email_vendor'].':-:'.$email_id;
        $sent_from='';
        $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo,$sent_from,$email_type);
        $stmt_email -> execute();

        $reason = "Vendor_".$row_vendor['vendor'].":-:".$_POST['email_vendor'];
        $email_id= $conn->insert_id;
        $stmt_email_reason = $conn->prepare("UPDATE emails SET `reason`=? WHERE `id`=?");
        $stmt_email_reason -> bind_param("si", $reason, $email_id);
        $stmt_email_reason -> execute();
       
    }
    $_SESSION["success"]="Email has been sent to vendors";
    header("location: ".$_SERVER['HTTP_REFERER']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////////////////////////////Use Vendors///////////////////////////////////////////////////////////////
    if(isset($_GET['use_vendor']))
    {
        $request_ids=explode(',',$_GET['use_vendor']);
        $vendors=$_GET['vendors'];
        foreach($request_ids as $request_id){
        $stmt_request -> bind_param("i", $request_id);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        if($result_request -> num_rows>0)
        while($row2 = $result_request -> fetch_assoc())
        {
            $item = $row2['item'];
            $company = $row2['company'];
            $processing_company = $row2['processing_company'];
            $property_company = $row2['property_company'];
            $procurement_company = $row2['procurement_company'];
            $finance_company = $row2['finance_company'];
            $type=$row2['request_type'];
        }
        $na_t=str_replace(" ","",$type);
        $out=$_SESSION['username'];
        $priority = "";
        $status = "Complete";
        $stmt = $conn->prepare("INSERT INTO `purchase_order` (`request_type`, `request_id`, `scale`, `purchase_officer`,`assigned_by`,`status`,`company`,`processing_company`, `property_company`, `procurement_company`, `finance_company`,`timestamp`,`priority`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $scale="not set";
        $date=date("Y-m-d H:i:s");
        $stmt -> bind_param("sissssssssssd",$type, $request_id, $scale, $out, $_SESSION['username'], $status, $company, $processing_company, $property_company, $procurement_company, $finance_company, $date, $priority);
        if($stmt -> execute())
        {
            $status="Generating Quote";
            $stmt_2 = $conn->prepare("UPDATE requests SET `status`=? ,`vendor`=? WHERE `request_id`=?");
            $stmt_2 -> bind_param("ssi", $status, $vendors, $request_id);
            $stmt_2 -> execute();
            $sql_rep = "UPDATE `report` SET `officer_assigned_date` = ?, `performa_generated_date` = ? WHERE `request_id` = ?";
            $stmt_rep_assign = $conn->prepare($sql_rep);
            $stmt_rep_assign -> bind_param("ssi",$date ,$date ,$request_id);
            $stmt_rep_assign -> execute();
            $send_to = "";
            $stmt_proc_manager->bind_param("s", $_SESSION['company']);
            $stmt_proc_manager->execute();
            $result_proc_manager = $stmt_proc_manager->get_result();
            if($result_proc_manager->num_rows>0)
            while($row2 = $result_proc_manager->fetch_assoc())
            {
                $tag = $row2['Username'];
                $send_to .=($send_to == "")?$row2['email'].",".$row2['Username']:",".$row2['email'].",".$row2['Username'];
            }
            $subject_email = "Proforma for $item was deleivered to procurement department";
            $data_email = "<strong>Please comfirm recieving proforma for $item, open and handover for Comparison sheet creation in a timely manner<strong><br><br><br>";
            $cc =""; $bcc = "";
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];

            $reason_close = "open_req_".$na_t."_".$request_id."_GS_assign";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $reason = "open_req_".$na_t."_".$request_id."_performa_collected";
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo ,$reason,$user);
            $stmt_email_reason -> execute();

            $email_id = $conn->insert_id;
            $page_to = "Procurement/manager/openProforma.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
        }
    }
        $_SESSION["success"]="Request sent for proforma opening";
        header("location: assignOfficer.php");
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////Assign_batch_officer///////////////////////////////////////////////////////////////
    if(isset($_GET['Assign_batch']))
    {
        $out=$_GET["officer"];
        if($out!='')
        {
            $priority = $_GET['Assign_batch'];
            $all_requests = explode(",",$_GET['selections']);
            foreach($all_requests as $req)
            {
                $na_t = explode("_",$req)[0];
                $request_id = explode("_",$req)[1];
                $type = na_t_to_type($conn,$na_t);

                $stmt_po_by_request->bind_param("i", $request_id);
                $stmt_po_by_request->execute();
                $result_po_by_request = $stmt_po_by_request->get_result();
                if($result_po_by_request -> num_rows==0)
                {
                    $stmt_request->bind_param("i", $request_id);
                    $stmt_request->execute();
                    $result_request = $stmt_request->get_result();
                    if($result_request->num_rows>0)
                    while($row2 = $result_request->fetch_assoc())
                    {
                        $item = $row2['item'];
                        $company = $row2['company'];
                        $processing_company = $row2['processing_company'];
                        $property_company = $row2['property_company'];
                        $procurement_company = $row2['procurement_company'];
                        $finance_company = $row2['finance_company'];
                    }
                    $reason_close = "open_req_".$na_t."_".$request_id."_GS_assign";
                    $stmt_email_close -> bind_param("s",$reason_close);
                    $stmt_email_close -> execute();
                    $reason = "open_req_".$na_t."_".$request_id."_assigned";

                    $stmt = $conn->prepare("INSERT INTO `purchase_order` (`request_type`, `request_id`, `scale`, `purchase_officer`,`assigned_by`,`company`,`processing_company`, `property_company`, `procurement_company`, `finance_company`,`timestamp`,`priority`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $scale="not set";
                    $date=date("Y-m-d H:i:s");
                    $stmt -> bind_param("sisssssssssd",$type, $request_id, $scale, $out, $_SESSION['username'], $company, $processing_company, $property_company, $procurement_company, $finance_company, $date, $priority);
                    if($stmt -> execute())
                    {
                        $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=?");
                        $status="Generating Quote";
                        $stmt -> bind_param("si", $status, $request_id);
                        $stmt -> execute();
                        $_SESSION["success"]="All Selected Requests assigned to $out";
                        $sql_rep = "UPDATE `report` SET `officer_assigned_date` = ? WHERE `request_id` = ?";
                        $stmt_rep_assign = $conn->prepare($sql_rep);
                        $stmt_rep_assign -> bind_param("si",$date ,$request_id);
                        $stmt_rep_assign -> execute();
                    }
                    
                }
                updaterequest($conn,$conn_fleet,$request_id,"two","","Assign");

            }
            $stmt2 = $conn->prepare("SELECT `email`,`phone` FROM `account` where `Username`='$out'  and status='active'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email,$phone_number);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was assigned to you";
            $data_email = "
            <strong>A Purchase order was assigned to you for proforma collection<strong><br>
            <strong>Please visit the website and accept as soon as possible<strong><br><br><br>
            ";
            $send_to = $email.",".$out;
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; $tag = $out;
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();
            
            $email_id = $conn->insert_id;
            $page_to = "Procurement/junior/newJobs.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
            $sms_to = $out; $sms_from = $_SESSION['username'];
            $msg = "Purchase orders was assigned to you for proforma collection please visit lpms.hagbes.com";
            include "../../common/sms.php";
            
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////Reassign_collector///////////////////////////////////////////////////////////////
    if(isset($_GET['Reassign_collector']))
    {
        $out=$_GET[$_GET['Reassign_collector']];
        if($out!='')
        {
            $stmt_po -> bind_param("i", $_GET['Reassign_collector']);
            $stmt_po -> execute();
            $result_po = $stmt_po -> get_result();
            if($result_po -> num_rows>0)
            while($row2 = $result_po -> fetch_assoc())
            {
                $record_type = "purchase_order";
                $operation = "Reassign Collector ($row2[collector] -> $out)";
                $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_GET['Reassign_collector'], $operation);
                $stmt_add_record -> execute();
            }
            
            $sql_po_collector = "UPDATE `purchase_order` SET `collector` = ? WHERE `purchase_order_id` = ?";
            $stmt_po_collector = $conn->prepare($sql_po_collector);
            $stmt_po_collector -> bind_param("si",$out ,$_GET['Reassign_collector']);
            $stmt_po_collector -> execute();
            $stmt_po->bind_param("i", $_GET["Reassign_collector"]);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            $row2 = $result_po->fetch_assoc();
            $changed_po = $row2['collector'];
            $date=date("Y-m-d H:i:s");
            $sql_rep = "UPDATE `report` SET `collector_assigned_date` = ? WHERE `request_id` = ?";
            $stmt_rep_assign_collector = $conn->prepare($sql_rep);
            $stmt_rep_assign_collector -> bind_param("si",$date ,$row2['request_id']);
            $stmt_rep_assign_collector -> execute();
            $stmt2 = $conn->prepare("SELECT `email`,`phone` FROM `account` where `Username`='".$out."'  and status='active'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email,$phone_number);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was assigned to you";
            $data_email = "
                <strong>A Purchase Order was assigned to you for collection<strong><br>
                <strong>Please Visit the website and Accept as soon as possible<strong><br><br><br>
            ";
            $send_to = $email.",".$out;
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; $tag = $out;
            $email_type = NULL;
            $sent_from='';
            $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
            $stmt_email -> execute();

            $sms_to = $out; $sms_from = $_SESSION['username'];
            $msg = "A Purchase order was assigned to you for collection please visit the site and accept";
            include "../../common/sms.php";

            
            $stmt2 = $conn->prepare("SELECT `email`,`phone` FROM `account` where `Username`='".$changed_po."'  and status='active'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email,$phone_number);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was reassigned";
            $data_email = "
            <strong>A purchase order that was assigned to you have been reassigned<strong><br><br><br>
            ";
            $tag = $changed_po;
            $send_to = $email.",".$changed_po;
            $email_type = NULL;
            $sent_from='';
            $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
            $stmt_email -> execute();

            $sms_to = $changed_po; $sms_from = $_SESSION['username'];
            $msg = "A Purchase order that was assigned to you have been reassigned";
            include "../../common/sms.php";
            $_SESSION["success"]="assigned $out for collection task";
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////Reassign_officer///////////////////////////////////////////////////////////////
    if(isset($_GET['Reassign_officer']))
    {
        $request_id = $_GET['Reassign_officer'];
        $out=$_GET["officer"];
        if($out!='')
        {
            $stmt_po_by_request -> bind_param("i", $request_id);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if($result_po_by_request -> num_rows>0)
                while($row_prior = $result_po_by_request -> fetch_assoc())
                {
                    $record_type = "purchase_order";
                    $operation = "Reassign performa ($row_prior[purchase_officer] -> $out)";
                    $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row_prior['purchase_order_id'], $operation);
                    $stmt_add_record -> execute();
                    // echo $sql2."<br>".$conn->error."<br><br>";
                    $changed_po = $row_prior['purchase_officer'];
                }
            $stmt_request -> bind_param("i", $request_id);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row = $result_request -> fetch_assoc();
            $company = $row['company'];
            $processing_company = $row['processing_company'];
            $property_company = $row['property_company'];
            $procurement_company = $row['procurement_company'];
            $finance_company = $row['finance_company'];
            
            $stmt = $conn->prepare("UPDATE `purchase_order` SET `purchase_officer`=? WHERE `request_id`=?");
            $stmt -> bind_param("si",$out, $request_id);
            $stmt -> execute();
            $_SESSION["success"]="Assigned $out for Quota Collection for ".$row['item']." Item";
            $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$out."' and status='active'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was assigned to you";
            $data_email = "
                <strong>A Purchase Order was assigned to you for proforma collection<strong><br>
                <strong>Please Visit the website and Accept as soon as possible<strong><br><br><br>
            ";
            $send_to = $email.",".$out;
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; $tag = $out;
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $email_type = NULL;
            $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo,$user,$email_type);
            $stmt_email -> execute();

            
            $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$changed_po."'  and status='active'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was reassigned";
            $data_email = "
            <strong>A Purchase order that was assigned to you have been reassigned<strong><br><br><br>
            ";
            $tag = $changed_po;
            $send_to = $email.",".$changed_po;
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $email_type = NULL;
            $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $user, $email_type);
            $stmt_email -> execute();
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////Assign_Collector_individual///////////////////////////////////////////////////////////////
    if(isset($_GET['Assign_collector_i']) || isset($_GET['Assign_batch_collector']))
    {
        $request_ids = (isset($_GET['Assign_collector_i']))?[$_GET['Assign_collector_i']]:explode(",",$_GET['Assign_batch_collector']);
        foreach($request_ids as $request_id)
        {
            $stmt_request -> bind_param("i", $request_id);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row = $result_request->fetch_assoc();
    
            $officer_col=$_GET["collector_name"];
            if($officer_col!='')
            {
                // $priority = $_GET['priority_val_'.$na_t.'_'.$request_id];
                $stmt = $conn->prepare("UPDATE `purchase_order` SET `collector`=? WHERE `request_id`=?");
                $stmt -> bind_param("si",$officer_col, $request_id);
                $stmt -> execute();
                $_SESSION["success"]="Assigned $officer_col for Item Collection for ".$row['item']." Item";
    
                $date=date("Y-m-d H:i:s");
                $sql_rep = "UPDATE `report` SET `collector_assigned_date` = ? WHERE `request_id` = ?";
                $stmt_rep_assign_collector = $conn->prepare($sql_rep);
                $stmt_rep_assign_collector -> bind_param("si",$date ,$request_id);
                $stmt_rep_assign_collector -> execute();
                $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$officer_col."'  and status='active'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email);
                $stmt2->fetch();
                $stmt2->close();
                $subject_email = "A Collection task was assigned to you for Per";
                $data_email = "
                <strong>A Collection task was assigned to you for proforma collection<strong><br>
                <strong>Please visit the website and accept as soon as possible<strong><br><br><br>
                ";
                $send_to = $email.",".$officer_col;
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = ""; $tag = $officer_col;
                $email_type = NULL;
                $sent_from='';
                $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
                $stmt_email -> execute();
            }
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else
    header("location: ".$_SERVER['HTTP_REFERER']);
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