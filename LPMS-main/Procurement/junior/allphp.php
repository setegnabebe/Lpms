<?php
session_start();
include "../../connection/connect.php";
include "../../common/functions.php";
if(isset($_SESSION['username']))
{
    if(isset($_POST['submit_vehicle_request']))
    {
        $reqeusts = "";
        $date=date("Y-m-d");
        $date_for = date("Y-m-d H:i",strtotime($_POST['date_for']." ".$_POST['time_for'].":00"));
        $date_to = date("Y-m-d H:i",strtotime($_POST['date_to']." ".$_POST['time_to'].":00"));
        gettimeofday(true); 
        $arrays=explode(".",microtime(true));
		$datee=date("YmdHis.").$arrays[1];

        foreach($_POST['requested_items'] as $r) $reqeusts .= ($reqeusts == "")?$r:",".$r;

        $sql = "INSERT INTO `fleet_requests`(`id`,`request_ids`, `requested_by`, `no_of_travelers`, `travelers`, `date_departure`, `estimate_date_return`, `purpose`, `destination`) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt_record_fleet_request = $conn->prepare($sql);
        $stmt_record_fleet_request -> bind_param("sssisssss", $datee, $reqeusts, $_SESSION['username'], $_POST['travelers_no'], $_POST['travelers_name'], $date_for, $date_to, $_POST['purpose'], $_POST['destination']);
        if($stmt_record_fleet_request -> execute())
        {
            if($_POST['travelers_no']==1)
                $numwtravel=$_POST['travelers_name'];
            else
                $numwtravel=$_POST['travelers_no'].":".$_POST['travelers_name'];
    
            $sql_fleet="INSERT into request values(?,?,?,?,?,?,?,?,?,?)";
            $stmt_add_fleet_request = $conn_fleet -> prepare($sql_fleet);
            $stmt_add_fleet_request -> bind_param("ssssssssss", $date, $date_for, $date_to, $_POST['purpose'], $numwtravel, $_POST['destination'], $_SESSION['username'], $datee, $_SESSION['department'], $_SESSION['company']);
            $stmt_add_fleet_request -> execute();
            
            $_SESSION['success']="Vehicle Requested";
        }
        
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["batch_start"]) || isset($_GET["accept"]))
    {
        $status = "Accepted";
        $date=date("Y-m-d H:i:s");
        $all = (isset($_GET["accept"]))?[$_GET["accept"]]:explode(",",$_GET["batch_start"]);
        foreach($all as $pid)
        {
            $stmt_po->bind_param("i", $pid);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            $row = $result_po->fetch_assoc();
            $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `purchase_order_id`=? ");
            $stmt_unique -> bind_param("si", $status, $pid);
            $stmt_unique -> execute();
            $_SESSION["success"]="Accepted quota collection task assigned by <span class='text-primary'>".$row['assigned_by']."</span>";
            $sql_rep = "UPDATE `report` SET `officer_assigned_date` = ? WHERE `request_id` = ?";
            $stmt_rep_accept = $conn->prepare($sql_rep);
            $stmt_rep_accept -> bind_param("si",$date ,$row['request_id']);
            $stmt_rep_accept -> execute();
            $_SESSION['success']=true;
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "open_req_".$na_t."_".$row["request_id"]."_assigned";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $reason = "open_req_".$na_t."_".$row["request_id"]."_accepted";
            $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$_SESSION["username"]."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($email);
            $stmt2->fetch();
            $stmt2->close();
            $subject_email = "A purchase order was acccepted";
            $data_email = "
            <strong>A purchase order was accepted for proforma collection</strong><br>
            <strong>Please review all details and collect proforma as soon as possible</strong><br><br><br>
            ";
            $send_to = $email.",".$_SESSION["username"];
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; $tag = $_SESSION["username"];
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();

            $email_id = $conn->insert_id;
            $page_to = "Procurement/junior/acceptedJob.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
            updaterequest($conn,$conn_fleet,$row["request_id"],"two","","Accept");
        }
        $_SESSION['fleet_request'] = true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["batch_complete"]) || isset($_GET["complete"]))
    {
        $status = "Complete";
        $date=date("Y-m-d H:i:s");
        $all = (isset($_GET["complete"]))?[$_GET["complete"]]:explode(",",$_GET["batch_complete"]);
        foreach($all as $pid)
        {
            $stmt_po->bind_param("i", $pid);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            $row = $result_po->fetch_assoc();
            $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? , `timestamp`=? WHERE `purchase_order_id`=? ");
            $stmt_unique -> bind_param("ssi", $status, $date, $pid);
            $stmt_unique -> execute();
            $sql_rep = "UPDATE `report` SET `performa_generated_date` = ? WHERE `request_id` = ?";
            $stmt_rep_complete = $conn->prepare($sql_rep);
            $stmt_rep_complete -> bind_param("si",$date ,$row['request_id']);
            $stmt_rep_complete -> execute();
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "open_req_".$na_t."_".$row["request_id"]."_accepted";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $reason = "open_req_".$na_t."_".$row["request_id"]."_performa_collected";
            $send_to = "";
            $stmt_proc_manager -> bind_param("s", $_SESSION['company']);
            $stmt_proc_manager -> execute();
            $result_proc_manager = $stmt_proc_manager -> get_result();
            if($result_proc_manager->num_rows>0)
            while($row2 = $result_proc_manager->fetch_assoc())
            {
                $tag = $row2['Username'];
                $send_to .=($send_to == "")?$row2['email'].",".$row2['Username']:",".$row2['email'].",".$row2['Username'];
            }
            $subject_email = "Proforma was deleivered to procurement department";
            $data_email = "<strong>Please comfirm recieving proforma, open and handover for Comparison sheet creation in a timely manner<strong><br><br><br>";
            $cc =""; $bcc = "";
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo, $reason,$user);
            $stmt_email_reason -> execute();

            $email_id = $conn->insert_id;
            $page_to = "Procurement/manager/openProforma.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
            updaterequest($conn,$conn_fleet,$row["request_id"],"two","","Complete");
            $_SESSION["success"]="Quota collection tasks completed";
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["recollected"]))
    {
        $pid = $_GET["recollected"];
        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();
        $record_type = "purchase_order";
        $operation = "recollected";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $pid, $operation);
        $stmt_add_record -> execute();
        $type = $row['request_type'];
        $status = "Collected-not-comfirmed";
        $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `purchase_order_id`=? ");
        $stmt_unique -> bind_param("si", $status, $pid);
        $stmt_unique -> execute();
        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Collection");
        $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
        $stmt_unique -> bind_param("si", $status, $row['request_id']);
        $stmt_unique -> execute();
        $_SESSION["success"]="Item was Collected";
        // $sms_to = $email_to; $sms_from = $_SESSION['username'];
        // $msg = "A Purchase Order was requested in $depp and waiting Director Approval Please Visit lpms.hagbes.com";
        // include "../common/sms.php";
        $date=date("Y-m-d H:i:s");  
        $sql_rep = "UPDATE `report` SET `recollection_date` = ? WHERE `request_id` = ?";
        $stmt_rep_recollection = $conn->prepare($sql_rep);
        $stmt_rep_recollection -> bind_param("si",$date ,$row['request_id']);
        $stmt_rep_recollection -> execute();
        $_SESSION['success']="Recollected";
        $na_t=str_replace(" ","",$row['request_type']);
        $reason_close = "open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["not_found"]))
    {
        $pid = $_GET["not_found"];
        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();
        $record_type = "purchase_order";
        $operation = "item not found";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $pid, $operation);
        $stmt_add_record -> execute();
        $na_t=str_replace(" ","",$row['request_type']);
        $sql_failed = "INSERT INTO `purchase_order_recollection_failed` (`purchase_order_id`, `request_type`, `request_id`, `scale`, `purchase_officer`, `collector`, `assigned_by`, `finance_sent_by`, `performa_opened`, `status`, `settlement`, `cluster_id`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`, `payment_provider`, `timestamp`, `performa_id`, `priority`) (SELECT * FROM `purchase_order` WHERE `purchase_order_id` = ?)";
        $stmt_failed = $conn->prepare($sql_failed);
        $stmt_failed -> bind_param("i",$pid);
        $stmt_failed -> execute();

        $status = "Recollection Failed";
        $stmt_po_status -> bind_param("si",$status, $pid);
        $stmt_po_status -> execute();

        $reason_close = "open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        $_SESSION['success']="Item Sent To Procurement Manager";

        $reason ="open_req_".$na_t."_".$row['request_id']."_proc_man_recoll";
        $send_to = "";
        $stmt_proc_manager -> bind_param("s", $row['procurement_company']);
        $stmt_proc_manager -> execute();
        $result_proc_manager = $stmt_proc_manager -> get_result();
        if($result_proc_manager -> num_rows > 0)
        while($row2 = $result_proc_manager -> fetch_assoc())
        {
            $email_to = $row2['Username'];
            $send_to .=($send_to == "")?$row2['email'].",".$row2['Username']:",".$row2['email'].",".$row2['Username'];
        }
        $subject_email = "A request has failed to be collected";
        $data_email = "<strong>A request has failed to be collected and is waiting for further instructions in recollection tab</strong><br>
                        <strong>Please handle accordingly</strong><br><br><br>";
        $cc =""; $bcc = "";
        $tag = $email_to;
        
        $stmt_company -> bind_param("s", $row2["company"]);
        $stmt_company -> execute();
        $result_company = $stmt_company -> get_result();
        if($result_company -> num_rows>0)
            while($r = $result_company -> fetch_assoc())
            {
                $logo = $r["logo"];
            }
        $com_lo = $row2['company'].",".$logo;
        $user=($_SESSION['username'].":-:".$_SESSION['position']);
        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo ,$reason,$user);
        $stmt_email_reason -> execute();
        $email_id = $conn->insert_id;
        $page_to = "Procurement/manager/recollection.php";
        $stmt_email_page -> bind_param("si",$page_to, $email_id);
        $stmt_email_page -> execute();
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    $date=date("Y-m-d H:i:s");
    ////////////////////////////////////////////////for collection////////////////////////////////////////////////
    if(isset($_GET["collect_item"]))
    {
        $pid = $_GET["collect_item"];
        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();
        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Collection");
        $status = "Collected-not-comfirmed";
        $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? , `timestamp`=? WHERE `purchase_order_id`=? ");
        $stmt_unique -> bind_param("ssi", $status, $date, $pid);
        $stmt_unique -> execute();
        $type = $row['request_type'];
        $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=?");
        $stmt_unique -> bind_param("si", $status, $row['request_id']);
        $stmt_unique -> execute();
        $na_t=str_replace(" ","",$row['request_type']);
        $sql_rep = "UPDATE `report` SET `collection_date` = ? WHERE `request_id` = ?";
        $stmt_rep_collection = $conn->prepare($sql_rep);
        $stmt_rep_collection -> bind_param("si",$date ,$row['request_id']);
        $stmt_rep_collection -> execute();
        $record_type = "purchase_order";
        $operation = "Collected";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $pid, $operation);
        $stmt_add_record -> execute();
        $_SESSION["success"]="Item Collected";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    //////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////for collection////////////////////////////////////////////////
    if(isset($_GET["collect_individual_item"]))
    {
        $pid = $_GET["collect_individual_item"];
        $stmt_po -> bind_param("i", $pid);
        $stmt_po -> execute();
        $result_po = $stmt_po -> get_result();
        $row = $result_po -> fetch_assoc();
        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Collection");
        $count_items = 0;
        $stmt_po_cluster -> bind_param("i", $row['cluster_id']);
        $stmt_po_cluster -> execute();
        $result_po_cluster = $stmt_po_cluster -> get_result();
        $row = $result_po_cluster -> fetch_assoc();
        if($result_po_cluster -> num_rows>0)
            while($row2 = $result_po_cluster -> fetch_assoc())
            {
                if($row2['status'] != "Collected-not-comfirmed")
                {
                    $count_items ++ ;
                }
                if($count_items==1)
                {
                    $_GET["collected".$row['cluster_id']] = 0;
                    break;
                }
            }
            $status = "Collected-not-comfirmed";
            $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? , `timestamp`=? WHERE `purchase_order_id`=? ");
            $stmt_unique -> bind_param("ssi", $status, $date, $pid);
            $stmt_unique -> execute();
            $type = $row['request_type'];
            $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=?");
            $stmt_unique -> bind_param("si", $status, $row['request_id']);
            $stmt_unique -> execute();

            $status = "partly Collected";
            $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=?");
            $stmt_unique -> bind_param("si", $status, $row['cluster_id']);
            $stmt_unique -> execute();
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $_SESSION["success"]="Selected Item Collected Item Selected";
            $sql_rep = "UPDATE `report` SET `collection_date` = ? WHERE `request_id` = ?";
            $stmt_rep_collection = $conn->prepare($sql_rep);
            $stmt_rep_collection -> bind_param("si",$date ,$row['request_id']);
            $stmt_rep_collection -> execute();
            $record_type = "purchase_order";
            $operation = "Collected";
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $pid, $operation);
            $stmt_add_record -> execute();
            $_SESSION['success']=true;
            header("location: ".$_SERVER['HTTP_REFERER']);
    }

    ////////////////////////////////////////////////for Entire Cluster collection////////////////////////////////////////////////
    if(isset($_GET["collected"]))// || (isset($not_full) && $not_full ==$row['id']))
    {
        $c_id = explode("::-::",$_GET["collected"])[0];
        $prov_comp = explode("::-::",$_GET["collected"])[1];
        

        $date=date("Y-m-d H:i:s");
        $status = "Collected-not-comfirmed";

        $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=?");
        $stmt_unique -> bind_param("si", $status, $c_id);
        $stmt_unique -> execute();

        $sql_special_collector = "SELECT *,P.purchase_order_id as purchase_order_id,P.cluster_id as cluster_id FROM `purchase_order` as P INNER JOIN price_information AS P_i on P_i.purchase_order_id = P.purchase_order_id AND p_i.cluster_id=P.cluster_id WHERE P.cluster_id = ? AND P_i.providing_company = ? AND selected and collector = ?";
        $stmt_special_collector = $conn->prepare($sql_special_collector);
        $stmt_special_collector -> bind_param("iss",$c_id ,$prov_comp ,$_SESSION['username']);
        $stmt_special_collector -> execute();
        $result_special_collector = $stmt_special_collector -> get_result();
        if($result_special_collector -> num_rows>0)
            while($r = $result_special_collector -> fetch_assoc())
            {
                updaterequest($conn,$conn_fleet,$r['request_id'],"four","","Collection");
                $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? , `timestamp`=? WHERE `purchase_order_id`=? ");
                $stmt_unique -> bind_param("ssi", $status, $date, $r['purchase_order_id']);
                $stmt_unique -> execute();
                $record_type = "purchase_order";
                $operation = "Collected";
                $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $r['purchase_order_id'], $operation);
                $stmt_add_record -> execute();
                $type = $r['request_type'];
                $request_id = $r['request_id'];
                $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=?");
                $stmt_unique -> bind_param("si", $status, $r['request_id']);
                $stmt_unique -> execute();
                $sql_rep = "UPDATE `report` SET `collection_date` = ? WHERE `request_id` = ?";
                $stmt_rep_collection = $conn->prepare($sql_rep);
                $stmt_rep_collection -> bind_param("si",$date ,$r['request_id']);
                $stmt_rep_collection -> execute();
                $_SESSION["success"]="All items Collected";
            }
            $stmt_request -> bind_param("i", $request_id);
            $stmt_request -> execute();
            $result_request = $stmt_request->get_result();
            if($result_request -> num_rows>0)
            $r = $result_request -> fetch_assoc();
            $dep = $r['department'];

            $reason_close = "open_clust_".$c_id."_collector";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            $sql_store_account = "SELECT * FROM `account` where department = 'Property' AND `role`='Store' AND `company` = ?";
            $stmt_store_account = $conn->prepare($sql_store_account);
            $stmt_store_account -> bind_param("s", $_SESSION['company']);
            $stmt_store_account -> execute();
            $result_store_account = $stmt_store_account -> get_result();
            if($result_store_account -> num_rows>0)
                while($r_email = $result_store_account -> fetch_assoc())
                {

                    $send_to = $r_email['email'].",".$r_email['Username'];
                    $subject_email = "Items that were collected were delievered to store";
                    $data_email = "
                    <strong>Items that were collected were delievered to store </strong><br>
                    <strong>Please wait for requesting departments approval of items to comfirm accepting items</strong><br><br><br>
                    ";
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $r_email['Username'];
                    $reason = "closed";
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
                    $email_id = $conn->insert_id;
                    $page_to = "property/storeclerk.php";
                    $stmt_email_page -> bind_param("si",$page_to, $email_id);
                    $stmt_email_page -> execute();
                }


            ///////////////////////////////////////////////////////////////////////////////////////////////////////////// dep
            $stmt_manager_active -> bind_param("ss", $dep, $_SESSION['company']);
            $stmt_manager_active -> execute();
            $result_manager = $stmt_manager_active -> get_result();
            if($result_manager->num_rows>0)
                while($r_email = $result_manager->fetch_assoc())
                {
                    $phone_number = $r_email['phone'];
                    $sms_to = $r_email['Username']; $sms_from = $_SESSION['username'];
                    $msg = "POs from your department were delieved to store please review them";
                    include "../../common/sms.php";
                    
                    $send_to =$r_email['email'].",".$r_email['Username'];
                    $subject_email = "Items Were Collected Were Delievered to Store";
                    $data_email = "
                        <strong>Items were collected were delievered to store</strong><br>
                        <strong>Please review the items and approve or reject accordingly</strong><br><br><br>";
                    $reason = "open_clust_".$c_id."_dep_store";
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $r_email['Username'];
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
    
                    $email_id = $conn->insert_id;
                    $page_to = "requests/itemCheck.php";
                    $stmt_email_page -> bind_param("si",$page_to, $email_id);
                    $stmt_email_page -> execute();
                }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
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