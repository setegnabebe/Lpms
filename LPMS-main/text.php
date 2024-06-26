<?php 
ignore_user_abort(1); 
set_time_limit(0); 
$inrval=10; 
$getEmail = true;
include "connection/connect.php";
do{
    $sql2 = "SELECT * FROM `purchase_order` WHERE `status` = 'pending'";
    $stmt_proforma_pending = $conn->prepare($sql2);
    $stmt_proforma_pending -> execute();
    $result_proforma_pending = $stmt_proforma_pending -> get_result();
    if($result_proforma_pending -> num_rows>0)
        while($row2 = $result_proforma_pending -> fetch_assoc())
        {
            if(!isset($purchase_officer[$row2['purchase_order_id']]))
                $purchase_officer[$row2['purchase_order_id']] = 0;
        }
    $sent_emails = [];
    $sql2 = "SELECT * FROM `emails` WHERE `status`='waiting'";
    $stmt_emails_waiting = $conn->prepare($sql2);
    $stmt_emails_waiting -> execute();
    $result_emails_waiting = $stmt_emails_waiting -> get_result();
    if($result_emails_waiting -> num_rows>0)
        while($row2 = $result_emails_waiting -> fetch_assoc())
        {
            $stat = "done";
            $sql_email_batch = "SELECT * FROM `emails` WHERE `status`='waiting' And tag = ?";
            $stmt_emails_waiting_tagged = $conn->prepare($sql_email_batch);
            $stmt_emails_waiting_tagged -> bind_param("s", $row2['tag']);
            $stmt_emails_waiting_tagged -> execute();
            $result_emails_waiting_tagged = $stmt_emails_waiting_tagged -> get_result();
            $num_emails = $result_emails_waiting_tagged -> num_rows;
            $reason = "closed";
            if($row2['project'] == 'LPMS' && !is_null($row2['tag']) && $num_emails > 3)
            {
                if(in_array($row2['id'],$sent_emails)) continue;
                $subject = "You have updates from LPMS system";
                $data_email = "<strong>You Have the Following tasks on LPMS system : </strong><br><br><br>";
                while($row_email_batch = $result_emails_waiting_tagged -> fetch_assoc())
                {
                    array_push($sent_emails,$row_email_batch['id']);
                    if(strpos($data_email,$row_email_batch["data"]) === false)
                        $data_email .= $row_email_batch["data"]."<br>";
                    $stmt = $conn->prepare("UPDATE `emails` SET `status`=? WHERE id='".$row_email_batch['id']."'");
                    $stmt -> bind_param("s", $stat);
                    $stmt -> execute();
                }
                $stmt_email_reason -> bind_param("ssssssssss",$row2["project"],$row2["send_to"],$row2["cc"],$row2["bcc"],$subject,$data_email,$row2["tag"],$row2["company_logo"],$reason,$sent_from);
                $stmt_email_reason -> execute();
                $stmt_emails_waiting -> execute();
                $result_emails_waiting = $stmt_emails_waiting -> get_result();
            }
            else
            {
                if($row2['send_to'] != "" && !is_null($row2['send_to']))
                $sent = send_auto_email($row2['project'],$row2['subject'],$row2['data'],$row2['send_to'],$row2['company_logo'],$row2['cc'],$row2['tag'],$row2['to_page'],$row2['sent_from'],$row2['attachment'],$row2['attachment']);
                else $sent = 'No recipient Found';
                if($sent != "Email Sent")
                {
                    $sql_crone_error = "INSERT into crone_errors (`email_id`,`data`) VALUES (?,?)";
                    $stmt_crone_error = $conn -> prepare($sql_crone_error);
                    $stmt_crone_error -> bind_param("is",$row2['id'], $sent);
                    $stmt_crone_error -> execute();
                }
                $stmt = $conn->prepare("UPDATE `emails` SET `status`=? WHERE id = ?");
                $stmt -> bind_param("si", $stat, $row2['id']);
                $stmt -> execute();
            }
        }
        
    $sql2 = "SELECT * FROM `purchase_order` WHERE `status` = 'pending'";
    $stmt_proforma_pending = $conn->prepare($sql2);
    $stmt_proforma_pending -> execute();
    $result_proforma_pending = $stmt_proforma_pending -> get_result();
    if($result_proforma_pending -> num_rows > 0)
        while($row2 = $result_proforma_pending -> fetch_assoc())
        {
            $date=date("Y-m-d H:i:s");
            $now = strtotime($date);//now time
            $then = strtotime($row2['timestamp']);//then time
            $interval = ($now - $then)/60/60/24;
            $days = intval($interval);
            if($days > 0 && $days > $purchase_officer[$row2['purchase_order_id']])
            {
                $str_reason = $row2["purchase_order_id"]."-".$row2["purchase_officer"]."-".$days;
                $sql_email = "SELECT * FROM `emails` WHERE `reason` = ?";
                $stmt_email_with_reason = $conn->prepare($sql_email);
                $stmt_email_with_reason -> bind_param("s", $str_reason);
                $stmt_email_with_reason -> execute();
                $result_email_with_reason = $stmt_email_with_reason -> get_result();
                if($result_email_with_reason -> num_rows == 0)
                {
                    $purchase_officer[$row2['purchase_order_id']] = $days;
                    $day_assigned = date("d-M-Y", strtotime($row2['timestamp']));
                    $hour_assigned = date("H:i:s", strtotime($row2['timestamp']));
                    $subject_email = "Please accept the purchase order assigned to you in a timely manner";
                    $data_email = 
                    "<strong>
                        A purchase order was assigned to you on $day_assigned at $hour_assigned<br><br>
                        Please accept and process it on a timely manner<br>
                    </strong>
                    ";
                    $sql = "SELECT * FROM `comp` WHERE `Name`= ?";
                    $stmt_company = $conn_fleet->prepare($sql);
                    $stmt_company->bind_param("s",$row2["company"]);
                    $stmt_company->execute();
                    $result_company = $stmt_company->get_result();
                    if($result_company -> num_rows>0)
                        while($r = $result_company -> fetch_assoc())
                        {
                            $logo = $r["logo"];
                        }
                    $sql = "SELECT * FROM `account` WHERE (`Username` = ? OR `Username` = ?) and status='active'";
                    $stmt_accounts_duo = $conn -> prepare($sql);
                    $stmt_accounts_duo -> bind_param("ss",$row2["purchase_officer"], $row2["assigned_by"]);
                    $stmt_accounts_duo -> execute();
                    $result_accounts_duo = $stmt_accounts_duo -> get_result();
                    if($result_accounts_duo -> num_rows > 0)
                        while($r = $result_accounts_duo -> fetch_assoc())
                        {
                            if($r["Username"] == $row2["purchase_officer"])
                                $email[$row2["purchase_officer"]] = $r["email"];
                            else
                                $email[$row2["assigned_by"]] = $r["email"];
                        }
                    $com_lo = $row2["company"].",".$logo;
                    $cc =""; $bcc = ""; $tag = $row2["purchase_officer"];
                    $to = $email[$row2["purchase_officer"]].",".$row2["purchase_officer"];
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $to, $cc, $bcc, $subject_email, $data_email ,$tag,$com_lo, $str_reason,$sent_from);
                    $stmt_email_reason -> execute();
                    $email_id = $conn->insert_id;
                    $page = "Procurement/junior/newJobs.php";
                    $sql_email_page = "UPDATE emails SET `to_page`=? WHERE `id`= ?";
                    $stmt_email_page = $conn->prepare($sql_email_page);
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();

                    $subject_email = "Purchase officer hasn't accepted purchase order";
                    $data_email = 
                    "<strong>
                        The purchase officer named <i style='color:blue'>".$row2["purchase_officer"]."</i> Assigned by you on $day_assigned at $hour_assigned on a purchase order
                        Has not yet accepted the job <br><br>
                        Please act accordingly<br><br><br>
                    </strong>
                    ";

                    $cc =""; $bcc = ""; $tag = $row2["assigned_by"];
                    $to = $email[$row2["assigned_by"]].",".$row2["assigned_by"];
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $to, $cc, $bcc, $subject_email, $data_email ,$tag,$com_lo, $str_reason,$sent_from);
                    $stmt_email_reason -> execute();
                    $email_id = $conn->insert_id;
                    $page = "Procurement/GS/viewAssigned.php";
                    $sql_email_page = "UPDATE emails SET `to_page`=? WHERE `id`= ?";
                    $stmt_email_page = $conn->prepare($sql_email_page);
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
            }
        }
        $sent_emails = [];
        $sql_email = "SELECT * FROM `emails` WHERE `reason` LIKE 'open%'";
        $stmt_open_emails = $conn->prepare($sql_email);
        $stmt_open_emails -> execute();
        $result_open_emails = $stmt_open_emails -> get_result();
        if($result_open_emails -> num_rows>0)
            while($row_email = $result_open_emails -> fetch_assoc())
            {
                $date=date("Y-m-d H:i:s");
                $day = date("l",strtotime($date));
                $hour = date("H:i");
                $now = strtotime($date);//now time
                $then = strtotime($row_email['time']);//then time
                $interval = ($now - $then)/60/60;
                if($row_email['project'] == 'LPMS')
                    $first_cond = $interval > 24;
                else if($row_email['project'] == 'FMS')
                    $first_cond = $interval > 48;
                if($first_cond && strtotime($hour) < strtotime("16:59") && strtotime($hour) > strtotime("07:59") && $day != 'Sunday')
                {
                    if($row_email['project'] == 'LPMS' && !is_null($row_email['tag']))
                    {
                        if(in_array($row_email['id'],$sent_emails)) continue;
                        $subject = "Email reminder from LPMS system";
                        $data_email = "<strong>You have the following tasks that are not yet finished</strong><br><br><br>";
                        $reason = "closed";
                        $sql_emails_tagged = "SELECT * FROM `emails` WHERE `reason` LIKE 'open%' And tag is not null And tag = ?";
                        $stmt_emails_tagged = $conn->prepare($sql_emails_tagged);
                        $stmt_emails_tagged -> bind_param("s", $row_email['tag']);
                        $stmt_emails_tagged -> execute();
                        $result_emails_tagged = $stmt_emails_tagged -> get_result();
                        if($result_emails_tagged -> num_rows>0)
                        {
                            while($row_email_batch = $result_emails_tagged -> fetch_assoc())
                            {
                                array_push($sent_emails,$row_email_batch['id']);
                                if(strpos($data_email,$row_email_batch["data"]) === false)
                                    $data_email .= $row_email_batch["data"]."<br>";
                                $num = ($row_email_batch['copy_of'] == '' || is_null($row_email_batch['copy_of']))?2:intval($row_email_batch['copy_of'])+1;
                                $sql_count_sends = "UPDATE emails SET `copy_of` = ?,`time` = ? WHERE `id` = ?";
                                $stmt_count_sends = $conn->prepare($sql_count_sends);
                                $stmt_count_sends -> bind_param("isi", $num, $date, $row_email_batch['id']);
                                $stmt_count_sends -> execute();
                            }  
                            $stmt_email_reason -> bind_param("ssssssssss",$row_email["project"],$row_email["send_to"],$row_email["cc"],$row_email["bcc"],$subject,$data_email,$row_email["tag"],$row_email["company_logo"],$reason,$sent_from);
                            $stmt_email_reason -> execute();
                        }
                    }
                    else
                    {
                        $num = ($row_email['copy_of'] == '' || is_null($row_email['copy_of']))?2:intval($row_email['copy_of'])+1;
                        $sql_emails_count_sends = "UPDATE emails SET `status`='waiting',`copy_of` = ?,`time` = ? WHERE `id` = ?";
                        $stmt_emails_count_sends = $conn->prepare($sql_emails_count_sends);
                        $stmt_emails_count_sends -> bind_param("isi", $num, $date, $row_email['id']);
                        $stmt_emails_count_sends -> execute();
                    }
                    // $stmt_email_reason -> bind_param("sssssssss",$row_email["project"],$row_email["send_to"],$row_email["cc"],$row_email["bcc"],$row_email["subject"],$row_email["data"],$row_email["tag"],$row_email["company_logo"],$row_email["reason"]);
                    // $stmt_email_reason -> execute();
                    // $last_id = $conn -> insert_id;

                    // $sql2 = "UPDATE emails SET `to_page`='$row_email[to_page]' WHERE `id`='$last_id'";
                    // $conn->query($sql2);

                    // $sql2 = "UPDATE emails SET `reason`='closed' WHERE `id`='".$row_email['id']."'";
                    // $conn->query($sql2);
                }
            }
        
        $sql2 = "SELECT * FROM `purchase_order` WHERE `status` = 'Recollect'";
        $stmt_proforma_Recollect = $conn->prepare($sql2);
        $stmt_proforma_Recollect -> execute();
        $result_proforma_Recollect = $stmt_proforma_Recollect -> get_result();
        if($result_proforma_Recollect -> num_rows>0)
            while($row2 = $result_proforma_Recollect -> fetch_assoc())
            {
                $date=date("Y-m-d H:i:s");
                $day = date("l",strtotime($date));
                $now = strtotime($date);//now time
                $then = strtotime($row2['timestamp']);//then time
                $interval = ($now - $then)/60/60/24;
                $days = intval($interval);
                if($days >= 2 && $day != 'Sunday')
                {
                    $na_t=str_replace(" ","",$row2['request_type']);
                    $sql_failed = "INSERT INTO `purchase_order_recollection_failed` (`purchase_order_id`, `request_type`, `request_id`, `scale`, `purchase_officer`, `collector`, `assigned_by`, `finance_sent_by`, `performa_opened`, `status`, `settlement`, `cluster_id`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`, `payment_provider`, `timestamp`, `performa_id`, `priority`) (SELECT * FROM `purchase_order` WHERE `purchase_order_id` = ?)";
                    $stmt_failed = $conn->prepare($sql_failed);
                    $stmt_failed -> bind_param("i",$row2['purchase_order_id']);
                    $stmt_failed -> execute();

                    $sql_update_recollection_failed = "UPDATE purchase_order SET `status`='Recollection Failed' WHERE `purchase_order_id` = ?";
                    $stmt_update_recollection_failed = $conn->prepare($sql_update_recollection_failed);
                    $stmt_update_recollection_failed -> bind_param("i", $row2['purchase_order_id']);
                    $stmt_update_recollection_failed -> execute();

                    $reason_closed = "open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
                    $sql_email_close = "UPDATE emails SET `reason`='closed' WHERE `reason`=?";
                    $stmt_email_close = $conn->prepare($sql_email_close);
                    $stmt_email_close -> bind_param("s",$reason_close);
                    $stmt_email_close -> execute();

                    $reason ="open_req_".$na_t."_".$row['request_id']."_proc_man_recoll";
                    $send_to = "";
                    $sql_procurement_manager = "SELECT * FROM `account` where (role = 'manager' OR `type` LIke '%manager%') AND company = ? AND department = 'Procurement' and status='active'";
                    $stmt_procurement_manager = $conn->prepare($sql_procurement_manager);
                    $stmt_procurement_manager -> bind_param("s", $row['procurement_company']);
                    $stmt_procurement_manager -> execute();
                    $result_procurement_manager = $stmt_procurement_manager -> get_result();
                    if($result_procurement_manager->num_rows>0)
                        while($row2 = $result_procurement_manager->fetch_assoc())
                        {
                            $email_to = $row2['Username'];
                            $send_to .=($send_to == "")?$row2['email'].",".$row2['Username']:",".$row2['email'].",".$row2['Username'];
                            $p_no = $row2['phone'];
                        }
                    $subject_email = "A request has failed to be collected";
                    $data_email = "<strong>A request has failed to be collected and is waiting for further instructions in recollection tab</strong><br>
                                    <strong>Please handle accordingly</strong><br><br><br>";
                    $cc =""; $bcc = "";
                    $tag = $email_to;
                    $sql = "SELECT * FROM `comp` WHERE `Name`= ?";
                    $stmt_company = $conn_fleet->prepare($sql);
                    $stmt_company->bind_param("s",$row2["company"]);
                    $stmt_company->execute();
                    $result_company = $stmt_company->get_result();
                    if($result_company -> num_rows>0)
                        while($r = $result_company -> fetch_assoc())
                        {
                            $logo = $r["logo"];
                        }
                    $com_lo = $row2['company'].",".$logo;
                    
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo ,$reason,$sent_from);
                    $stmt_email_reason -> execute();
                    // $phone_number = $p_no; $sms_to = $email_to; $sms_from = "LPMS System";
                    // $msg = "A Request has failed to be Collected and Can be found in Recollection Tab";
                    // include "common/sms.php";
                    $email_id = $conn->insert_id;
                    $page = "Procurement/manager/recollection.php";
                    $sql_email_page = "UPDATE emails SET `to_page`=? WHERE `id`= ?";
                    $stmt_email_page = $conn->prepare($sql_email_page);
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
            }
    sleep($inrval); 
}while(true);
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>