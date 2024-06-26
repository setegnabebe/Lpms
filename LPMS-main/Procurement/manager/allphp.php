<?php
    
session_start();
include "../../connection/connect.php";
include "../../common/functions.php";
if(isset($_SESSION['username']))
{
if(isset($_GET['current_scale'])){
    $c_id = $_GET["newselection"];
    $stmt_cluster -> bind_param("i", $c_id);
    $stmt_cluster -> execute();
    $result_cluster = $stmt_cluster -> get_result();
    $row = $result_cluster -> fetch_assoc();
    $procurement_company = $row['procurement_company'];
    $comp_count = 0;
    $sql_committee_name = "SELECT username FROM `committee_approval` inner Join account on account.username=committee_approval.committee_member Where `cluster_id` = ? AND committee_approval.`status` = 'Approved'";
    $stmt_committee_name = $conn->prepare($sql_committee_name);
    $stmt_committee_name -> bind_param("i",$row['id']);
    $stmt_committee_name -> execute();
    $result_committee_name = $stmt_committee_name -> get_result();
    $users="";
    if($result_committee_name -> num_rows>0)
    while($comms = $result_committee_name -> fetch_assoc())
    {
     $users.=str_replace("."," ",$comms['username']).",";
    }
    $users=rtrim($users,",");
    $sql_providing_cluster = "SELECT DISTINCT `providing_company` FROM `price_information` where `cluster_id` = ?";
    $stmt_providing_cluster = $conn->prepare($sql_providing_cluster);
    $stmt_providing_cluster -> bind_param("i",$row['id']);
    $stmt_providing_cluster -> execute();
    $result_providing_cluster = $stmt_providing_cluster -> get_result();
    if($result_providing_cluster->num_rows>0)
        while($r = $result_providing_cluster->fetch_assoc())
        {
            // echo $r['providing_company']."<br>";
            $companies[$comp_count] = $r['providing_company'];
            $comp_count++;
        }
    $stmt_po_cluster -> bind_param("i",$row['id']);
    $stmt_po_cluster -> execute();
    $result_po_cluster = $stmt_po_cluster -> get_result();
    if($result_po_cluster -> num_rows>0)
        while($row2 = $result_po_cluster -> fetch_assoc())
        {
            $selected = false;
            $stmt = $conn->prepare("UPDATE `price_information` SET `selected`=? WHERE
            `cluster_id` = ? AND `purchase_order_id`=?");
            $stmt -> bind_param("iii", $selected, $row['id'], $row2['purchase_order_id']);
            $stmt -> execute();
            if(isset($_GET["Item-".$row2['request_id']]) || isset($_GET["Item-".$row2['request_id']."_half"]))
            {
                $selected = true;
                if(isset($_GET["Item-".$row2['request_id']]))
                {
                    $stmt = $conn->prepare("UPDATE `price_information` SET `selected`=? WHERE
                    `cluster_id` = ? AND `purchase_order_id`=? AND `providing_company` = ?");
                    $stmt -> bind_param("iiis", $selected, $row['id'], $row2['purchase_order_id'], $companies[$_GET["Item-".$row2['request_id']]]);
                    $stmt -> execute();
                }
                else
                {
                    foreach($_GET["Item-".$row2['request_id']."_half"] AS $comp_num)
                    {
                        $stmt = $conn->prepare("UPDATE `price_information` SET `selected`=? WHERE
                        `cluster_id` = ? AND `purchase_order_id`=? AND `providing_company` = ?");
                        $stmt -> bind_param("iiis", $selected, $row['id'], $row2['purchase_order_id'], $companies[$comp_num]);
                        $stmt -> execute();
                    }
                }
                $stmt2 = $conn->prepare("SELECT SUM(`after_vat`) AS total FROM `price_information` WHERE `cluster_id`=? AND selected");
                $stmt2 -> bind_param("i",$row['id']);
                $stmt2->execute();
                $stmt2->store_result(); 
                $stmt2->bind_result($total);
                $stmt2->fetch();
                $stmt2->close();

                // $total = ($Vat * $total) + $total;
                $stmt = $conn->prepare("UPDATE `cluster` SET `price`=? WHERE `id`=?");
                $stmt -> bind_param("si", $total, $row['id']);
                $stmt -> execute();
            }
            updaterequest($conn,$conn_fleet,$row2['request_id'],"two","","Committee_sent");
        }
        
    $selections = "";
    $stmt_prices_selected -> bind_param("i",$row['id']);
    $stmt_prices_selected -> execute();
    $result_prices_selected = $stmt_prices_selected -> get_result();
    if($result_prices_selected -> num_rows>0)
        while($r = $result_prices_selected -> fetch_assoc())
        {
            $selections .= ($selections=="")?$r["id"]:",".$r["id"];
        }
    // echo $selections;
    $stmt_selections_specific -> bind_param("is", $row['id'] ,$_SESSION['username']);
    $stmt_selections_specific -> execute();
    $result_selections_specific = $stmt_selections_specific -> get_result();
    if($result_selections_specific->num_rows>0)
    {
        $sql_update_selections = "UPDATE `selections` SET selection = ? WHERE `user` = ? AND `cluster_id` = ?";
        $stmt_update_selections = $conn->prepare($sql_update_selections);
        $stmt_update_selections -> bind_param("ssi",$selections ,$_SESSION['username'], $row['id']);
        $stmt_update_selections -> execute();
    }
    else
    {
        $sql_add_selections = "INSERT into `selections` (`user`, `cluster_id`, `selection`) VALUES (?,?,?)";
        $stmt_add_selections = $conn->prepare($sql_add_selections);
        $stmt_add_selections -> bind_param("sis",$_SESSION['username'] ,$row['id'], $selections);
        $stmt_add_selections -> execute();
    }


    $status = "Approved";
    if(isset($_GET['reason']))
    $reason = ($_GET['reason']=='')?"#":$_GET['reason'];
    else $reason = "#";
    $date=date("Y-m-d H:i:s");
    $sql_approval_id = "SELECT id FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
    $stmt_approval_id = $conn->prepare($sql_approval_id);
    $stmt_approval_id -> bind_param("si", $_SESSION['username'] ,$row['id']);
    $stmt_approval_id -> execute();
    $result_approval_id = $stmt_approval_id -> get_result();
    if($result_approval_id -> num_rows>0)
        while($row_temp = $result_approval_id -> fetch_assoc())
        {
            $stmt = $conn->prepare("UPDATE `committee_approval` SET `status`=?, `remark`=? , `timestamp`=? WHERE `id`=? ");
            $stmt -> bind_param("sssi", $status, $reason, $date, $row_temp['id']);
            $stmt -> execute();
        }
    else 
    {
        $stmt = $conn->prepare("INSERT INTO `committee_approval` (`committee_member`,`status`,`remark`,`cluster_id`,`timestamp`) VALUES (?, ?, ?, ?, ?)");
        $stmt -> bind_param("sssis",$_SESSION['username'],$status,$reason,$row['id'],$date);
        if($stmt -> execute())
            $_SESSION["success"]=$status;
    }
 
    /////////////////////////sent for approval//////////////////////
    $stmt_cluster -> bind_param("i", $_GET['newselection']);
    $stmt_cluster -> execute();
    $result_cluster = $stmt_cluster -> get_result();
    $r = $result_cluster -> fetch_assoc();
    $status_update = "Generated";   
    $stmt_cluster_status -> bind_param("si", $status_update ,$_GET['newselection']);
    $stmt_cluster_status -> execute();
    if($r['status'] != "updated")
    {
        $stmt_po_cluster -> bind_param("i",$_GET['newselection']);
        $stmt_po_cluster -> execute();
        $result_po_cluster = $stmt_po_cluster -> get_result();
        $r = $result_po_cluster -> fetch_assoc();
        $stmt_request -> bind_param("i",$r['request_id']);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        $row = $result_request -> fetch_assoc();

        $record_type = "reqeusts";
        $operation = "Sent to Committee";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $operation);
        $stmt_add_record -> execute();

        $date=date("Y-m-d H:i:s");
        $stmt = $conn->prepare("UPDATE `report` SET `sent_to_committee_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();
        $reason_close = "open_clust_".$c_id."_review_cs";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        if($r['scale'] != 'procurement')
            if($r['scale'] == 'HO')
                $sql = "SELECT * FROM `account` where (type LIKE '%".$r['scale']."%') ";
            else if($r['scale'] == 'Owner')
                    $sql = "SELECT * FROM `account` where (role = '$r[scale]' OR (((department = 'Procurement' AND (role = 'manager' OR `type` LIke '%manager%')) OR `additional_role` = 1) AND company = '$row[procurement_company]')) ";
            else
                $sql = "SELECT * FROM `account` where (type LIKE '%".$r['scale']."%' AND company='$_SESSION[company]') ";
        else
            $sql = "SELECT * FROM `account` where (department = 'Procurement' AND role='manager' AND company = 'Hagbes HQ.') ";
        $sql .= " and status = 'active'";
        $send_to = "";
        $branch_dep = ($r['scale'] == 'HO' && $r['company'] != 'Hagbes HQ.' && $row['department'] != 'GM')?"(department = '$row[department]' OR department = 'GM')":"department = '$row[department]'";
        $sql .= ($row['department'] == "Owner" || $row['role'] == "Director")?"OR (Username = '$row[customer]')":
        "OR ($branch_dep AND company = '$_SESSION[company]' AND ((role = 'manager' OR `type` LIke '%manager%') OR  role = 'Director'))";
        $sql_items = "SELECT * FROM `purchase_order` inner join requests on `purchase_order`.`request_id`=requests.request_id where  cluster_id=?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items -> bind_param("i", $c_id);
        $stmt_items -> execute();
        $result_items = $stmt_items -> get_result();
        $cell_style="border:1px solid #dddddd;text-align: left; padding:8px";
        $table="<table style='font-family:arial,sans-serif; font-size:14px; border-collapse: collapse; width: 100%;'>
        <tr>
        <th style='$cell_style'>NO.</th>
        <th style='$cell_style'>Item</th>
        <th style='$cell_style'>Quantity</th>
        <th style='$cell_style'>Unit</th>
        </tr>
        ";
        $x=1;
        if($result_items->num_rows>0)
        while($item_row=$result_items->fetch_assoc()){
         $comp=$item_row['comp'];
        $table.=" <tr>
         <td style='$cell_style'>$x</td>
         <td style='$cell_style' >".$item_row['item']."</td>
         <td style='$cell_style' >".$item_row['requested_quantity']."</td>
         <td style='$cell_style' >".$item_row['unit']."</td>
       </tr>";
       $x++;
        }
        $table.='</table>';
   
        $stmt_special_fetch = $conn->prepare($sql);
        $stmt_special_fetch -> execute();
        $result_special_fetch = $stmt_special_fetch -> get_result();
        if($result_special_fetch -> num_rows>0)
            while($r = $result_special_fetch -> fetch_assoc())
            {
               
                $send_to = $r['email'].",".$r['Username'];
                 
                $reason = "open_clust_".$c_id."_committee_levl";
                $subject_email = "Committee Approval for Requests";
                $data_email = "
                <strong>Requests listed in the table are ready for committee approval <strong><br>
                Please review the request as soon as possible<br> 
                ";
                $data_email.=$table;
                $cc =""; $bcc = ""; $tag = $r['Username'];
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                 
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                $email_id = $conn->insert_id;
                $page_to = "Committee/Approval.php";
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
                $phone_number = $r['phone']; $sms_to = $r['Username']; $sms_from = "LPMS System";
                $msg = "Purchase orders are awaiting committee approval please visit lpms.hagbes.com";
                include "../../common/sms.php";    
            }
    }
    $_SESSION['success'] = 'Comparison Sheet sent';


    header("location: ".$_SERVER['HTTP_REFERER']);
}
/////////////////////////////////OPEN PEROFORMA/////////////////////////////////////////////////////
if(isset($_GET["opened_Performa"]) || isset($_GET["batch_recieve"]))
{
    $status = "Performa Comfirmed";
    $date=date("Y-m-d H:i:s");
    if(isset($_GET["opened_Performa"]))
        $pids = [$_GET["opened_Performa"]];
    else
        $pids = explode(",",$_GET["batch_recieve"]);
    foreach($pids as $pid)
    {
        // $pid=$_GET["opened_Performa"];
        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();

        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?,`performa_opened`=? WHERE `purchase_order_id`=?");
        $stmt -> bind_param("ssi", $status, $_SESSION['username'], $pid);
        $stmt -> execute();
        $nxt_step = "Comparision Sheet Generation";
        $stmt_next_step->bind_param("si", $nxt_step, $row['request_id']);
        $stmt_next_step->execute();
        $stmt = $conn->prepare("UPDATE `report` SET `performa_confirm_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();

        $na_t=str_replace(" ","",$row['request_type']);
        $reason_close = "open_req_".$na_t."_".$row["request_id"]."_performa_collected";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        $reason = "open_req_".$na_t."_".$row["request_id"]."_performa_opened";
        $send_to = "";
        updaterequest($conn,$conn_fleet,$row['request_id'],"two","","Open_proforma");
        // $sql2 = "SELECT * FROM `account` where `department` = 'Procurement' AND (`role`='Senior Purchase officer' OR (role = 'manager' OR `type` LIke '%manager%')) AND company= '".$_SESSION['company']."'";
        $nxt_dep = "Procurement";
        $nxt_comp = $_SESSION['company'];
        $nxt_role = "Senior Purchase officer";
        $stmt_accounts_role_based -> bind_param("sss", $nxt_dep, $nxt_comp, $nxt_role);
        $stmt_accounts_role_based -> execute();
        $result_accounts_role_based = $stmt_accounts_role_based -> get_result();
        if($result_accounts_role_based->num_rows>0)
        while($row2 = $result_accounts_role_based->fetch_assoc())
        {
            $tag = $row2['Username'];
            $send_to = $row2['email'].",".$row2['Username'];
            $subject_email = "Comparison Sheet Creation";
            $data_email = "<strong>A request is ready for Comparison sheet creation please complete the Comparison sheet in a timely manner<strong><br><br><br>";
            $cc =""; $bcc = "";
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo ,$reason,$user);
            $stmt_email_reason -> execute();
            $email_id = $conn->insert_id;
            $page_to = "Procurement/senior/createComparision.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
        }
    }
    $_SESSION["success"]=true;
    header("location: ".$_SERVER['HTTP_REFERER']);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////Petty Cash//////////////////////////////////////////////////////////
if(isset($_GET["petty_cash"]) || isset($_GET["batch_petty_cash"]))
{
    $status = "Petty Cash";
    $date=date("Y-m-d H:i:s");
    if(isset($_GET["petty_cash"]))
        $pids = [$_GET["petty_cash"]];
    else
        $pids = explode(",",$_GET["batch_petty_cash"]);
    foreach($pids as $pid)
    {
        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        $row = $result_po->fetch_assoc();
        // updaterequest($conn,$conn_fleet,$row['request_id'],"two","","Petty_cash");

        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?,`performa_opened`=? WHERE `purchase_order_id`=?");
        $stmt -> bind_param("ssi", $status, $_SESSION['username'], $pid);
        $stmt -> execute();
        $nxt_step = "Petty Cash Approval";
        $stmt_status_next->bind_param("ssi", $status, $nxt_step, $row['request_id']);
        $stmt_status_next->execute();
        $stmt = $conn->prepare("UPDATE `report` SET `performa_confirm_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();

        $na_t=str_replace(" ","",$row['request_type']);
        $reason_close = "open_req_".$na_t."_".$row["request_id"]."_performa_collected";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        // $sql2 = "SELECT * FROM `account` where `department` = 'Procurement' AND (`role`='Senior Purchase officer' OR (role = 'manager' OR `type` LIke '%manager%')) AND company= '".$_SESSION['company']."'";
        $sql2 = "SELECT * FROM `account` where ((department = 'Disbursement' AND (role = 'manager' OR `type` LIke '%manager%')) OR `type` LIKE '%Petty Cash Approver%') and company = ?  and status='active'";
        $stmt_petty_approver = $conn->prepare($sql2);
        $stmt_petty_approver -> bind_param("s", $row['finance_company']);
        $stmt_petty_approver -> execute();
        $result_petty_approver = $stmt_petty_approver -> get_result();
        if($result_petty_approver -> num_rows>0)
        while($row2 = $result_petty_approver -> fetch_assoc())
        {
            $email = $row2['email'];
            $out = $row2['Username'];
            $reason = "open_".$na_t."_".$row["request_id"]."_disbursement";
            $subject_email = "A purchase order was sent for petty cash approval";
            $data_email = "<strong>A purchase order was sent by for petty cash approval please review in a timely manner</strong><br><br><br>";
            $send_to = $email.",".$out;
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; $tag = $out;
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();
            
            $email_id = $conn->insert_id;
            $page_to = "disbursement/pettycash.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
        }
    }
    $_SESSION["success"]="Sent for Approval";
    header("location: ".$_SERVER['HTTP_REFERER']); 
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////To Committee///////////////////////////////////////////////////////////////
if(isset($_GET['for_approval']))
{
    $stmt_cluster->bind_param("i", $_GET['for_approval']);
    $stmt_cluster->execute();
    $result_cluster = $stmt_cluster->get_result();
    $r = $result_cluster->fetch_assoc();
    $status = "Generated";
    $stmt_cluster_status->bind_param("si", $status, $_GET['for_approval']);
    $stmt_cluster_status->execute();
    if($r['status'] != "updated")
    {
        $stmt_po_cluster->bind_param("i", $_GET['for_approval']);
        $stmt_po_cluster->execute();
        $result_po_cluster = $stmt_po_cluster -> get_result();
        $r = $result_po_cluster -> fetch_assoc();
        $stmt_request->bind_param("i", $r['request_id']);
        $stmt_request->execute();
        $result_request = $stmt_request -> get_result();
        $row = $result_request -> fetch_assoc();
        // updaterequest($conn,$conn_fleet,$row['request_id'],"two","","Committee_sent");
        $date=date("Y-m-d H:i:s");
        $stmt = $conn->prepare("UPDATE `report` SET `sent_to_committee_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();
        $reason_close = "open_clust_".$_GET['for_approval']."_review_cs";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        if($r['scale'] != 'procurement')
            if($r['scale'] == 'HO')
                $sql = "SELECT * FROM `account` where (type LIKE '%".$r['scale']."%') ";
            else if($r['scale'] == 'Owner')
                    $sql = "SELECT * FROM `account` where (role = '$r[scale]' OR (((department = 'Procurement' AND (role = 'manager' OR `type` LIke '%manager%')) OR `additional_role` = 1) AND company = '$row[procurement_company]')) ";
            else
                $sql = "SELECT * FROM `account` where (type LIKE '%".$r['scale']."%' AND company='$_SESSION[company]') ";
        else
            $sql = "SELECT * FROM `account` where (department = 'Procurement' AND role='manager' AND company = 'Hagbes HQ.') ";
        $send_to = "";
        $branch_dep = ($r['scale'] == 'HO' && $r['company'] != 'Hagbes HQ.' && $row['department'] != 'GM')?"(department = '$row[department]' OR department = 'GM')":"department = '$row[department]'";
        $sql .= ($row['department'] == "Owner" || $row['role'] == "Director")?"OR (Username = '$row[customer]')":
        "OR ($branch_dep AND company = '$_SESSION[company]' AND ((role = 'manager' OR `type` LIke '%manager%') OR  role = 'Director'))";
        $sql_items = "SELECT * FROM `purchase_order` inner join requests on `purchase_order`.`request_id`=requests.request_id where  cluster_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items -> bind_param("i", $_GET['for_approval']);
        $stmt_items -> execute();
        $result_items = $stmt_items -> get_result();
        $cell_style="border:1px solid #dddddd;text-align: left; padding:8px";
        $table="<table style='font-family:arial,sans-serif; font-size:14px; border-collapse: collapse; width: 100%;'>
        <tr>
        <th style='$cell_style'>NO.</th>
        <th style='$cell_style'>Item</th>
        <th style='$cell_style'>Quantity</th>
        <th style='$cell_style'>Unit</th>
        </tr>
        ";
        $x=1;
        if($result_items->num_rows>0)
        while($item_row=$result_items->fetch_assoc()){
         $comp=$item_row['comp'];
        $table.=" <tr>
         <td style='$cell_style'>$x</td>
         <td style='$cell_style' >".$item_row['item']."</td>
         <td style='$cell_style' >".$item_row['requested_quantity']."</td>
         <td style='$cell_style' >".$item_row['unit']."</td>
       </tr>";
       $x++;
        }
        $table.='</table>';
        $stmt_special_fetch = $conn->prepare($sql);
        $stmt_special_fetch -> execute();
        $result_special_fetch = $stmt_special_fetch -> get_result();
        if($result_special_fetch->num_rows>0)
            while($r = $result_special_fetch->fetch_assoc())
            {
               
                $send_to = $r['email'].",".$r['Username'];
                 
                $reason = "open_clust_".$_GET['for_approval']."_committee_levl";
                $subject_email = "Committee Approval for Requests";
                $data_email = "
                <strong>Requests listed in the table are ready for committee approval <strong><br>
                Please review the request as soon as possible<br> 
                ";
                $data_email.=$table;
                $cc =""; $bcc = ""; $tag = $r['Username'];
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                 
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                $email_id = $conn->insert_id;
                $page_to = "Committee/Approval.php";
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
                $phone_number = $r['phone']; $sms_to = $r['Username']; $sms_from = "LPMS System";
                $msg = "Purchase orders are awaiting committee approval please visit lpms.hagbes.com";
                include "../../common/sms.php";    
            }
    }
        $_SESSION['success'] = 'Comparison Sheet sent';
        header("location: ".$_SERVER['HTTP_REFERER']);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////Redo Comparision Sheet///////////////////////////////////////////////////////////////
if(isset($_GET['redo_compSheet']))
{
    //tmrw save Performa ID on Cluster too on Comp Sheet Create
    $status = "Changed";
    $stmt_cluster_status->bind_param("si", $status, $_GET['redo_compSheet']);
    $stmt_cluster_status->execute();
    $reason_close = "open_clust_".$_GET['redo_compSheet']."_review_cs";
    $stmt_email_close -> bind_param("s",$reason_close);
    $stmt_email_close -> execute();
    $stmt_po_cluster->bind_param("i", $_GET['redo_compSheet']);
    $stmt_po_cluster->execute();
    $result_po_cluster = $stmt_po_cluster -> get_result();
    while($r = $result_po_cluster->fetch_assoc())
    {
        $status = "Generating Quote";
        $nxt_step = "Comparision Sheet Generation";
        $stmt_status_next->bind_param("ssi", $status, $nxt_step, $r['request_id']);
        $stmt_status_next->execute();
        $stmt_request->bind_param("i", $r['request_id']);
        $stmt_request->execute();
        $result_request = $stmt_request -> get_result();
        $row = $result_request -> fetch_assoc();
        $record_type = "Clsuter";
        $operation = "Comparission Redo";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_GET['redo_compSheet'], $operation);
        $stmt_add_record -> execute();
        $na_t = str_replace(" ","",$row['request_type']);
        $reason = "open_req_".$na_t."_".$row["request_id"]."_performa_opened";
        $nxt_dep = "Procurement";
        $nxt_comp = $_SESSION['company'];
        $nxt_role = "Senior Purchase officer";
        $stmt_accounts_role_based -> bind_param("sss", $nxt_dep, $nxt_comp, $nxt_role);
        $stmt_accounts_role_based -> execute();
        $result_accounts_role_based = $stmt_accounts_role_based -> get_result();
        if($result_accounts_role_based->num_rows>0)
        while($row2 = $result_accounts_role_based->fetch_assoc())
        {
            $tag = $row2['Username'];
            $send_to = $row2['email'].",".$row2['Username'];
            $subject_email = "Comparison Sheet Creation";
            $data_email = "<strong>A Request is ready for Comparison Sheet Creation Please Complete the Comparison Sheet In a Timely Manner<strong><br><br><br>";
            $cc =""; $bcc = "";
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo ,$reason,$user);
            $stmt_email_reason -> execute();
            $email_id = $conn->insert_id;
            $page_to = "Procurement/senior/createComparision.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
        }
    }
    $new_scale = "not set";
    $status = "Performa Comfirmed";
    $sql2 = "UPDATE purchase_order SET `status`=?, scale=?, cluster_id = NULL, performa_id = NULL WHERE `cluster_id` = ?";
    $stmt_items = $conn->prepare($sql2);
    $stmt_items -> bind_param("ssi", $status, $new_scale, $_GET['redo_compSheet']);
    $stmt_items -> execute();
    // $date=date("Y-m-d H:i:s");
    ////////######################################################//////
    ////////######################################################//////
        $_SESSION['success'] = 'Send To Senior Purchase Officers';
        header("location: ".$_SERVER['HTTP_REFERER']);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////Not Found Send to///////////////////////////////////////////////////////////////
if(isset($_GET['goto']))
{
    $stmt_po->bind_param("i", $_GET['goto']);
    $stmt_po->execute();
    $result_po = $stmt_po->get_result();
    $row = $result_po->fetch_assoc();
    $na_t=str_replace(" ","",$row['request_type']);
    $stmt_request->bind_param("i", $row['request_id']);
    $stmt_request->execute();
    $result_request = $stmt_request->get_result();
    $row2 = $result_request->fetch_assoc();
    if($_GET[$na_t."_".$row['purchase_order_id']] == 'Performa collection')
    {
        $record_type = "purchase_order";
        $operation = "Deleted";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
        $stmt_add_record -> execute();
        $record_type = "purchase_order";
        $operation = "Moved back to Performa collection";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
        $stmt_add_record -> execute();
        $sql_add_deleted_po = "INSERT INTO `purchase_order_deleted` (SELECT * FROM `purchase_order` WHERE `purchase_order_id` = ?)";
        $stmt_add_deleted_po = $conn->prepare($sql_add_deleted_po);
        $stmt_add_deleted_po -> bind_param("i", $row['purchase_order_id']);
        $stmt_add_deleted_po -> execute();
        $sql_delete_po = "DELETE FROM `purchase_order` WHERE `purchase_order_id` = ?";
        $stmt_delete_po = $conn->prepare($sql_delete_po);
        $stmt_delete_po -> bind_param("i", $row['purchase_order_id']);
        if (!($stmt_delete_po -> execute())) echo "Error: " . $sql2 . "<br>" . $conn->error. "<br>";
        $status = 
        ($row['request_type'] == "Fixed assets")?"Approved By Owner":(
        ($row['company'] == "Hagbes HQ." && $row['request_type'] != "Fixed assets")?"Approved By Director":(
        (($row['request_type'] == "Spare and Lubricant" && ($row2['mode']=="External" || ($row2['mode']=="Internal" && $row2['type']=="Lubricant"))) || 
        ($row['request_type'] == "Tyre and Battery" && $row2['mode']=="External") || 
        $row['request_type'] == "Miscellaneous" || $row['request_type'] == "Consumer Goods"
        )?"Store Checked":"Approved By Property"
        ));
        $stmt_status_update->bind_param("i", $status, $row2['request_id']);
        $stmt_status_update->execute();
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if($_GET[$na_t."_".$row['purchase_order_id']] == 'Comparsion Sheet Creation')
    {
        $record_type = "purchase_order";
        $operation = "Moved back to Comparsion Sheet Creation";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
        $stmt_add_record -> execute();
        $status = "Generating Quote";
        $stmt_status_update->bind_param("si", $status, $row2['request_id']);
        $stmt_status_update->execute();
        $status = "Complete";
        $sql_update_po = "UPDATE purchase_order SET `status` = ?, `cluster_id` = NULL, `collector` = NULL WHERE `purchase_order_id`=?";
        $stmt_update_po = $conn->prepare($sql_update_po);
        $stmt_update_po -> bind_param("si", $status, $row['purchase_order_id']);
        $stmt_update_po -> execute();
        $isset = $row['purchase_order_id'];
    }
    if($_GET[$na_t."_".$row['purchase_order_id']] == 'Committee Approval')
    {
        $record_type = "purchase_order";
        $operation = "Moved back to Committee Approval";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
        $stmt_add_record -> execute();
        $status = "Generating Quote";
        $stmt_status_update->bind_param("si", $status, $row2['request_id']);
        $stmt_status_update->execute();
        
        $status = "Generated";
        $stmt_po_status->bind_param("si", $status, $row['purchase_order_id']);
        $stmt_po_status->execute();

        $stmt_cluster_status->bind_param("si", $status, $row['cluster_id']);
        $stmt_cluster_status->execute();
        $status = "Reactivated";
        $sql_reactivate_approval = "UPDATE committee_approval SET `status` = ? WHERE `cluster_id`=?";
        $stmt_reactivate_approval = $conn->prepare($sql_reactivate_approval);
        $stmt_reactivate_approval -> bind_param("si", $status, $row['cluster_id']);
        $stmt_reactivate_approval -> execute();
    }
    if($_GET[$na_t."_".$row['purchase_order_id']] == 'Collection')
    {
        $record_type = "purchase_order";
        $operation = "Moved back to Collection";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
        $stmt_add_record -> execute();
        $status = "Payment Processed";
        $stmt_status_update->bind_param("si", $status, $row2['request_id']);
        $stmt_status_update->execute();
        $status = "Payment Processed";
        $stmt_po_status->bind_param("si", $status, $row['purchase_order_id']);
        $stmt_po_status->execute();
        $status = "Partly Collected";
        $stmt_cluster_status->bind_param("si", $status, $row['cluster_id']);
        $stmt_cluster_status->execute();
    }
    $_SESSION["success"]=true;
    header("location: ".$_SERVER['HTTP_REFERER']);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////close_request///////////////////////////////////////////////////////////////
if(isset($_GET['close_request']))
{
    $stmt_po->bind_param("i", $_GET['close_request']);
    $stmt_po->execute();
    $result_po = $stmt_po->get_result();
    $row = $result_po->fetch_assoc();
    $status = "canceled";
    $stmt_status_update->bind_param("si", $status, $row['request_id']);
    $stmt_status_update->execute();
    $stmt_po_status->bind_param("si", $status, $_GET['close_request']);
    $stmt_po_status->execute();
    $na_t=str_replace(" ","",$row['request_type']);
    $stmt_request->bind_param("i", $row['request_id']);
    $stmt_request->execute();
    $result_request = $stmt_request->get_result();
    $row = $result_request->fetch_assoc();
    $reason_close = "%".$na_t."_".$row['request_id']."_%";
    $sql_email_close_like = "UPDATE emails SET `reason`='closed' WHERE `reason`LIKE ?";
    $stmt_email_close_like = $conn->prepare($sql_email_close_like);
    $stmt_email_close_like -> bind_param("s",$reason_close);
    $stmt_email_close_like -> execute();
    $sql2 = "SELECT * FROM `account` where `Username` = ? OR ((role = 'manager' OR `type` LIke '%manager%') AND company = ? AND department = ?)";
    $stmt_accounts_email_customer = $conn->prepare($sql2);
    $stmt_accounts_email_customer -> bind_param("sss", $row['customer'], $row['company'], $row['department']);
    $stmt_accounts_email_customer -> execute();
    $result_accounts_email_customer = $stmt_accounts_email_customer -> get_result();
    if($result_accounts_email_customer -> num_rows>0)
    while($row2 = $result_accounts_email_customer -> fetch_assoc())
    {
        $send_to =$row2['email'].",".$row2['Username'];
        $subject_email = "$row[item] Request Was canceled";
        $data_email = "<strong>The $row[item] that was requested on $row[date_requested] was canceled by procurement department ($_SESSION[username])</strong><br><br><br>";
        $cc =""; $bcc = "";
        $tag = $row['Username'];
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $email_type = NULL;
        $sent_from='';
        $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo, $sent_from, $email_type);
        $stmt_email -> execute();
    }
    $_SESSION["success"]="Request Closed";
    header("location: ".$_SERVER['HTTP_REFERER']);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////Send To Finance///////////////////////////////////////////////////////////////
        if(isset($_GET["process_to_finance"]) || isset($_GET["batch_send"]))
        {
            $ids = (isset($_GET["process_to_finance"]))?[$_GET["process_to_finance"]]:explode(",",$_GET["batch_send"]);
            foreach($ids as $id)
            {
                $date=date("Y-m-d H:i:s");
                $status ="Sent to Finance";
                $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
                $stmt_unique -> bind_param("si", $status, $id);
                $stmt_unique -> execute();
                $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=? ");
                $stmt_unique -> bind_param("si", $status, $id);
                $stmt_unique -> execute();
                
                $stmt_po_by_cluster_active -> bind_param("i", $id);
                $stmt_po_by_cluster_active -> execute();
                $result_po_by_cluster_active = $stmt_po_by_cluster_active -> get_result();
                if($result_po_by_cluster_active->num_rows>0)
                    while($row2 = $result_po_by_cluster_active->fetch_assoc())
                    {
                        updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","sent_to_finance");
                        $sql = "UPDATE `report` SET `sent_to_finance_date`=? where `request_id`=?";
                        $stmt_reportComp = $conn->prepare($sql);
                        $stmt_reportComp -> bind_param("si",$date ,$row2['request_id']);
                        $stmt_reportComp -> execute();
                        $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                        $stmt_unique -> bind_param("si", $status, $row2['request_id']);
                        $stmt_unique -> execute();
                        $nxt_step = 'Finance';
                        $stmt_next_step -> bind_param("si", $nxt_step, $row2['request_id']);
                        $stmt_next_step -> execute();
                        $_SESSION["success"]=$status;
                        $finance_company = $row2['finance_company'];
                    }
                $nxt_dep = "finance";
                $nxt_comp = $finance_company;
                $nxt_role = "Disbursement";
                $stmt_accounts_role_based -> bind_param("sss", $nxt_dep, $nxt_comp, $nxt_role);
                $stmt_accounts_role_based -> execute();
                $result_accounts_role_based = $stmt_accounts_role_based -> get_result();
                if($result_accounts_role_based -> num_rows > 0)
                    while($row2 = $result_accounts_role_based -> fetch_assoc())
                    {
                        $reason_close = "open_clust_".$id."_committee_approved";
                        $stmt_email_close -> bind_param("s",$reason_close);
                        $stmt_email_close -> execute();
    
                        $email = $row2['email'];
                        $out = $row2['Username'];
                        $subject_email = "A Purchase order was sent for Financial Processing";
                        $data_email = "<strong>A purchase order was sent by procurement department for financial processing please review in a timely manner</strong><br><br><br>";
                        $send_to = $email.",".$out;
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = $out;
                        $reason = "open_clust_".$id."_finance_1";
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        
                        $email_id = $conn->insert_id;
                        $page_to = "finance/review.php";
                        $stmt_email_page -> bind_param("si",$page_to, $email_id);
                        $stmt_email_page -> execute();
                    }
            }
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
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