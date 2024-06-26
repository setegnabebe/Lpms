<?php
    
session_start();
include "../connection/connect.php";
include "../common/functions.php";

if(isset($_SESSION['username']))
{
    if(isset($_GET["cpv_info"]))
    {
        $withholding = explode("--",$_GET["cpv_info"])[0];
        $cheque_for = explode("--",$_GET["cpv_info"])[1];
        $cheque_no = explode("--",$_GET["cpv_info"])[2];
        $bank = explode("--",$_GET["cpv_info"])[3];
        $cluster_id = explode("--",$_GET["cpv_info"])[4];
        $po_s = explode("--",$_GET["cpv_info"])[5];
        $providing_company = explode("--",$_GET["cpv_info"])[6];

        $reqcomp = explode("--",$_GET["cpv_info"])[7];
        $cheque_percent = explode("--",$_GET["cpv_info"])[8];
        $withheld = explode("--",$_GET["cpv_info"])[9];
        if($withheld == "false" && $withholding != 0)
        {
            $cheque_for = floatval($withholding) + floatval($cheque_for);
            $withholding = 0;
        }
        $po = explode(":-:",$po_s)[0];
        $sql_prices = "SELECT * FROM `price_information` where cluster_id = ? AND purchase_order_id = ? AND selected";
        $stmt_prices_po = $conn->prepare($sql_prices);
        $stmt_prices_po->bind_param("ii", $cluster_id, $po);
        $stmt_prices_po->execute();
        $result_prices = $stmt_prices_po->get_result();
        $row = $result_prices->fetch_assoc();
        $providing_company = str_replace("'","'",$row["providing_company"]);
        $sql_cheque = "SELECT * FROM `cheque_info` where cluster_id = ? AND providing_company = ? AND void != 1";
        $stmt_cheque = $conn->prepare($sql_cheque);
        $stmt_cheque->bind_param("is", $cluster_id, $providing_company);
        $stmt_cheque->execute();
        $result_cheque = $stmt_cheque->get_result();
        $final = ($result_cheque->num_rows>0)?"1":(($cheque_percent!=100)?"0":"1");

        $sql_bank = "SELECT * FROM `banks` where id = ?";
        $stmt_bank = $conn->prepare($sql_bank);  
        $stmt_bank->bind_param("s", $bank);
        $stmt_bank->execute();
        $result_bank = $stmt_bank->get_result();
        $row = $result_bank->fetch_assoc();

        $sql = "INSERT INTO `cheque_info`(`cheque_no`, `providing_company`, `cluster_id`, `purchase_order_ids`, `bank`, `cheque_amount`, `withholding`, `prepared_percent`, `created_by`, `company`, `final`) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $stmt_add_cheque = $conn->prepare($sql);
        $stmt_add_cheque -> bind_param("ssissddissi", $cheque_no,$providing_company,$cluster_id,$po_s,$row['bank'],$cheque_for,$withholding,$cheque_percent,$_SESSION['username'],$reqcomp,$final);
        if($stmt_add_cheque -> execute())
        {
            $cpv_no = $conn->insert_id;
            echo "<li class='list-group-item list-group-item-light border-0 text-start ms-4 mt-4'><b>CPV  : <i class='text-primary'>$cpv_no</i></b></li>";
            echo "<li class='list-group-item list-group-item-light border-0 text-start ms-4 mt-4'><b>Cheque Number  : <i class='text-primary'>$cheque_no</i></b></li>";
        }
    }

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
        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Settled");
            
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
        $reason_closed = "open_req_".$na_t."_".$row['request_id']."_settlment";
        $stmt_email_close -> bind_param("s",$reason_close);
        $stmt_email_close -> execute();
        
        $_SESSION["success"]="Purchase $settlement";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["process"]) || isset($_GET["batch_approve"]))
    {
        $ids = (isset($_GET["process"]))?[$_GET["process"]]:explode(",",$_GET["batch_approve"]);
        foreach($ids as $cluster_id)
        {
            // $cluster_id=$_GET["process"];
            // $status ="Finance Approved";
            $stmt_cluster->bind_param("i", $cluster_id);
            $stmt_cluster->execute();
            $result_cluster = $stmt_cluster->get_result();
            $row2 = $result_cluster->fetch_assoc();
            $price = $row2['price'];
            $stmt_limit -> bind_param("s", $row2['company']);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
            if ($result_limit->num_rows ==0)
            {
                $other = "Others";
                $stmt_limit -> bind_param("s", $other);
                $stmt_limit -> execute();
                $result_limit = $stmt_limit->get_result();
            }
            $row2 = $result_limit->fetch_assoc();
            $petty_cash = $row2['petty_cash'];
            $pty_cash = false;
            $chq = false;
            $sql_prov_companies = "SELECT providing_company,SUM(after_vat) AS S_avat from `price_information` Where `cluster_id` = ? AND `selected` Group by providing_company";
            $stmt_prov_companies = $conn->prepare($sql_prov_companies);  
            $stmt_prov_companies->bind_param("i", $cluster_id);
            $stmt_prov_companies->execute();
            $result_prov_companies = $stmt_prov_companies->get_result();
            while($row_pc = $result_prov_companies->fetch_assoc())
            {
                if($row_pc['S_avat'] > $petty_cash)
                {
                    $status = "Finance Approved";
                    $chq = true;
                }
                else
                {
                    $status = "Finance Approved Petty Cash";
                    $pty_cash = true;
                }
                $providing_company = (strpos($row_pc['providing_company'],"'") !== false && strpos($row_pc['providing_company'],"\'") === false)?str_replace("'","'",$row_pc['providing_company']):$row_pc['providing_company'];
                $sql_prices = "SELECT * FROM `price_information` Where `cluster_id` = ? AND providing_company = ? AND `selected`";
                $stmt_prices_comp = $conn->prepare($sql_prices);  
                $stmt_prices_comp->bind_param("is", $cluster_id, $providing_company);
                $stmt_prices_comp->execute();
                $result_prices_comp = $stmt_prices_comp->get_result();
                while($row_f = $result_prices_comp->fetch_assoc())
                {
                    $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `purchase_order_id`=? ");
                    $stmt_unique -> bind_param("si", $status, $row_f['purchase_order_id']);
                    $stmt_unique -> execute();
                }
            }

            $date=date("Y-m-d H:i:s");
            $status = ($price > $petty_cash)?"Finance Approved":"Finance Approved Petty Cash";
            // $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
            // $stmt_unique -> bind_param("si", $status, $cluster_id);
            // $stmt_unique -> execute();
            
            $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=?,`Finance_approved`=? WHERE `id`=? ");
            $stmt_unique -> bind_param("ssi", $status, $_SESSION['username'], $cluster_id);
            $stmt_unique -> execute();
            $stmt_po_cluster_active->bind_param("i", $cluster_id);
            $stmt_po_cluster_active->execute();
            $result_po_cluster_active = $stmt_po_cluster_active->get_result();
            if($result_po_cluster_active->num_rows>0)
                while($row2 = $result_po_cluster_active->fetch_assoc())
                {
                    updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","Finance Approved");
                    // $l=str_replace(" ","",$row2['request_type']);
                    $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                    $stmt_unique -> bind_param("si", $status, $row2['request_id']);
                    $stmt_unique -> execute();
                    $next_step = "Finalize Payment";
                    $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                    $stmt_next_step -> execute();
                    if($price <= $petty_cash)
                    {
                        $sql2 = "UPDATE requests SET `finance_company`=? WHERE `request_id` = ?";
                        $stmt_request_finance = $conn->prepare($sql2);
                        $stmt_request_finance -> bind_param("si", $row2['company'], $row2['request_id']);
                        $stmt_request_finance -> execute();
                        $sql2 = "UPDATE purchase_order SET `finance_company`=? WHERE `request_id` = ?";
                        $stmt_po_finance = $conn->prepare($sql2);
                        $stmt_po_finance -> bind_param("si", $row2['company'], $row2['request_id']);
                        $stmt_po_finance -> execute();
                        $sql2 = "UPDATE cluster SET `finance_company`=? WHERE `id` = ?";
                        $stmt_cluster_finance = $conn->prepare($sql2);
                        $stmt_cluster_finance -> bind_param("si", $row2['company'], $cluster_id);
                        $stmt_cluster_finance -> execute();
                    }
                    $_SESSION["success"]=$status;
                    $sql_rep = "UPDATE `report` SET `finance_approval_date` = ? WHERE `request_id` = ?";
                    $stmt_rep_finance_approval = $conn->prepare($sql_rep);
                    $stmt_rep_finance_approval -> bind_param("si",$date ,$row2['request_id']);
                    $stmt_rep_finance_approval -> execute();
                }
            $reason_closed = "open_clust_".$cluster_id."_finance_2";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            if($chq)
            {
                $sql_account_cashier = "SELECT * FROM `account` where `department` = 'Finance' AND `role` = 'cashier' and `company` = ?  and status='active'";
                $stmt_account_cashier = $conn->prepare($sql_account_cashier);  
                $stmt_account_cashier->bind_param("s", $_SESSION['company']);
                $stmt_account_cashier->execute();
                $result_account_cashier = $stmt_account_cashier->get_result();
                if($result_account_cashier->num_rows>0)
                    while($row2 = $result_account_cashier->fetch_assoc())
                    {
                        $email = $row2['email'];
                        $out = $row2['Username'];
                        $reason = "open_clust_".$cluster_id."_cashier";
                        $subject_email = "A Purchase order was sent for cheque preparation";
                        $data_email = "<strong>A Purchase order was sent by for cheque preparation please review in a timely manner</strong><br><br><br>";
                        $send_to = $email.",".$out;
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = $out;
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        
                        $email_id = $conn->insert_id;

                        $page = "finance/cashier.php";
                        $stmt_email_page -> bind_param("si",$page, $email_id);
                        $stmt_email_page -> execute();
                        }
            }
            if($pty_cash)
            {
                $sql_account_disbursement = "SELECT * FROM `account` where ((department = 'Disbursement' AND (role = 'manager' OR `type` LIke '%manager%')) OR `type` LIKE '%Petty Cash Approver%') and `company` = ? and `status` = 'active'";
                $stmt_account_disbursement = $conn->prepare($sql_account_disbursement);  
                $stmt_account_disbursement -> bind_param("s", $_SESSION['company']);
                $stmt_account_disbursement -> execute();
                $result_account_disbursement = $stmt_account_disbursement -> get_result();
                if($result_account_disbursement -> num_rows>0)
                    while($row2 = $result_account_disbursement->fetch_assoc())
                    {
                        $email = $row2['email'];
                        $out = $row2['Username'];
                        $reason = "open_clust_".$cluster_id."_disbursement";
                        $subject_email = "A Purchase order was sent for petty cash approval";
                        $data_email = "<strong>A purchase order was sent by for petty cash approval please review in a timely manner</strong><br><br><br>";
                        $send_to = $email.",".$out;
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = $out;
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        
                        $email_id = $conn->insert_id;
                        $page = "disbursement/pettycash.php";
                        $stmt_email_page -> bind_param("si",$page, $email_id);
                        $stmt_email_page -> execute();
                    }
            }
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['petty_cash_only']))
    {
        $pid = $_GET["petty_cash_only"];
        $date=date("Y-m-d H:i:s");
        $status ="Payment Processed";
        // $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
        // $stmt -> bind_param("si", $status, $row['id']);
        // $stmt -> execute();
        $record_type = "purchase_order";
        $operation = "Petty Cash Approved ".$_GET['reason'];
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $pid, $operation);
        $stmt_add_record -> execute();

        $stmt_po->bind_param("i", $pid);
        $stmt_po->execute();
        $result_po = $stmt_po->get_result();
        if($result_po->num_rows>0)
            while($row2 = $result_po->fetch_assoc())
            {
                updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","give_petty_Cash");
                $officer_col = $row2['purchase_officer'];
                $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `collector`=?, `payment_provider`=? WHERE `purchase_order_id`=? ");
                $stmt -> bind_param("sssi", $status, $row2['purchase_officer'], $_SESSION['username'], $row2['purchase_order_id']);
                $stmt -> execute();
                // $l=str_replace(" ","",$row2['request_type']);
                $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                $stmt -> bind_param("si", $status, $row2['request_id']);
                $stmt -> execute();
                $next_step = "Collection";
                $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                $stmt_next_step -> execute();
                $_SESSION["success"]=$status;
                $sql_rep = "UPDATE `report` SET `cheque_signed_date` = ? WHERE `request_id` = ?";
                $stmt_rep_cheuqe = $conn->prepare($sql_rep);
                $stmt_rep_cheuqe -> bind_param("si",$date ,$row2['request_id']);
                $stmt_rep_cheuqe -> execute();
                $procurement_company = $row2['procurement_company'];
            }
            $reason_closed = "open_PO_".$pid."_cashier";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();

            $stmt_account_active -> bind_param("s", $officer_col);
            $stmt_account_active -> execute();
            $result_account_active = $stmt_account_active -> get_result();
            if($result_account_active->num_rows>0)
                while($row2 = $result_account_active->fetch_assoc())
                {
                    $phone_number = $row2['phone'];
                    $email = $row2['email'];
                    $sms_to = $officer_col; 
                    $sms_from = $_SESSION['username'];
                    $msg = "Petty cash was prepared for purchase and is now ready to be collected please visit lpms.hagbes.com";
                    include "../common/sms.php";
                    $subject_email = "A Collection task was assigned to you";
                    $data_email = "
                    <strong>A Collection task was assigned to you for collection<strong><br>
                    <strong>Please visit the website and accept as soon as possible<strong><br><br><br>
                    ";
                    $send_to = $email.",".$officer_col;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $officer_col;
                    $email_type = NULL;
                    $sent_from = "";
                    $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
                    $stmt_email -> execute();
                }
    header("location: ".$_SERVER['HTTP_REFERER']);
    }
    
    if(isset($_GET['share_comp']))
    {
        $cluster_id = explode("::-::",$_GET['share_comp'])[0];
        $po_id = explode("::-::",$_GET['share_comp'])[1];
        $poids = explode(":-:",$po_id);

        $sql = "SELECT * FROM `purchase_order` where cluster_id = ?";
        $cond = "";
        foreach($poids as $poid)
            $cond .= ($cond == "")?" AND ( purchase_order_id = '$poid'":" OR purchase_order_id = '$poid'";
        $cond .= ($cond != "")?")":$cond;
        $sql .= $cond;
        $stmt_cluster_with_po = $conn->prepare($sql);  
        $stmt_cluster_with_po -> bind_param("i", $cluster_id);
        $stmt_cluster_with_po -> execute();
        $result_cluster_with_po = $stmt_cluster_with_po -> get_result();
        while($row = $result_cluster_with_po->fetch_assoc())
        {
            $finance_comp = $row['finance_company'];
            $stmt_company -> bind_param("s", $_GET['company_selector']);
            $stmt_company -> execute();
            $result_company = $stmt_company->get_result();
            $r_comp = $result_company->fetch_assoc();
            $status = $r_comp['finance']?",`status`='Reviewed'":"";
            $sql2 = "UPDATE requests SET `finance_company`=? $status,`next_step`='Finalize Payment' WHERE `request_id` = ?";
            $stmt_request_finance = $conn->prepare($sql2);
            $stmt_request_finance -> bind_param("si", $_GET['company_selector'], $row['request_id']);
            $stmt_request_finance -> execute();

            $sql2 = "UPDATE purchase_order SET `finance_company`=?$status WHERE `request_id` = ?";
            $stmt_po_finance = $conn->prepare($sql2);
            $stmt_po_finance -> bind_param("si", $_GET['company_selector'], $row['request_id']);
            $stmt_po_finance -> execute();
        }

        $record_type = "Cluster";
        $operation = "Finance Company From $finance_comp To ".$_GET['company_selector'];
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $cluster_id, $operation);
        $stmt_add_record -> execute();

        $sql2 = "UPDATE cluster SET `finance_company`=?$status WHERE `id` = ?";
        $stmt_cluster_finance = $conn->prepare($sql2);
        $stmt_cluster_finance -> bind_param("si", $_GET['company_selector'], $cluster_id);
        $stmt_cluster_finance -> execute();

        $_SESSION['success'] = "Sent To ".$_GET['company_selector'];
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['petty_cash']))
    {
        $cluster_id = explode("::-::",$_GET["petty_cash"])[0];
        $pos = explode("::-::",$_GET["petty_cash"])[1];
        $po = explode(":-:",$pos)[0];
        $sql_price_selected = "SELECT * FROM `price_information` where cluster_id = ? AND purchase_order_id = ? AND selected";
        $stmt_price_selected = $conn->prepare($sql_price_selected);  
        $stmt_price_selected -> bind_param("ii", $cluster_id, $po);
        $stmt_price_selected -> execute();
        $result_price_selected = $stmt_price_selected -> get_result();
        $row = $result_price_selected->fetch_assoc();
        $providing_company = $row['providing_company'];
        $stmt_cluster->bind_param("i", $cluster_id);
        $stmt_cluster->execute();
        $result_cluster = $stmt_cluster->get_result();
        $row = $result_cluster->fetch_assoc();

        $date=date("Y-m-d H:i:s");
        $status ="Payment Processed";
        // $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
        // $stmt -> bind_param("si", $status, $row['id']);
        // $stmt -> execute();

        $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=?,`cashier`=? WHERE `id`=? ");
        $stmt_unique -> bind_param("ssi", $status, $_SESSION['username'], $cluster_id);
        $stmt_unique -> execute();
        $sql_price_selected = "SELECT *,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where 
        (p_i.providing_company = ? AND P.cluster_id = ?) AND selected";
        $stmt_price_selected = $conn->prepare($sql_price_selected);  
        $stmt_price_selected -> bind_param("si", $providing_company, $cluster_id);
        $stmt_price_selected -> execute();
        $result_price_selected = $stmt_price_selected -> get_result();
        if($result_price_selected->num_rows>0)
            while($row2 = $result_price_selected->fetch_assoc())
            {
                updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","give_petty_Cash");
                $officer_col = $row2['purchase_officer'];
                $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `collector`=?, `payment_provider`=? WHERE `purchase_order_id`=? ");
                $stmt -> bind_param("sssi", $status, $row2['purchase_officer'], $_SESSION['username'], $row2['purchase_order_id']);
                $stmt -> execute();
                // $l=str_replace(" ","",$row2['request_type']);
                $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                $stmt -> bind_param("si", $status, $row2['request_id']);
                $stmt -> execute();
                $next_step = "Collection";
                $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                $stmt_next_step -> execute();
                $_SESSION["success"]=$status;
                $sql_rep = "UPDATE `report` SET `cheque_signed_date` = ?,`collector_assigned_date`=? WHERE `request_id` = ?";
                $stmt_rep_cheuqe = $conn->prepare($sql_rep);
                $stmt_rep_cheuqe -> bind_param("ssi",$date ,$date ,$row2['request_id']);
                $stmt_rep_cheuqe -> execute();
                $procurement_company = $row2['procurement_company'];
            }
            $reason_closed = "open_clust_".$row['id']."_cashier";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();

            $stmt_account_active -> bind_param("s", $officer_col);
            $stmt_account_active -> execute();
            $result_account_active = $stmt_account_active -> get_result();
            if($result_account_active -> num_rows>0)
                while($row2 = $result_account_active -> fetch_assoc())
                {
                    $phone_number = $row2['phone'];
                    $email = $row2['email'];
                    $sms_to = $officer_col; 
                    $sms_from = $_SESSION['username'];
                    $msg = "Petty cash was prepared for purchase and is now ready to be collected please visit lpms.hagbes.com";
                    include "../common/sms.php";
                    $subject_email = "A Collection task was assigned to you";
                    $data_email = "
                    <strong>A Collection task was assigned to you for collection<strong><br>
                    <strong>Please visit the website and accept as soon as possible<strong><br><br><br>
                    ";
                    $send_to = $email.",".$officer_col;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $officer_col;
                    $email_type = NULL;
                    $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
                    $stmt_email -> execute();
                }
    header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["void"]))
    {
        $stmt_void = $conn->prepare("UPDATE cheque_info SET `void`='1' WHERE `cpv_no` = ?");
        $stmt_void -> bind_param("i", $_GET['void']);
        $stmt_void -> execute();
        ////////////////////////////////
        $sql_cheque_specific = "SELECT * FROM `cheque_info` Where cpv_no = ?"; //  and (`prepared_percent` = 100 OR final)
        $stmt_cheque_specific = $conn->prepare($sql_cheque_specific);  
        $stmt_cheque_specific -> bind_param("i", $_GET['void']);
        $stmt_cheque_specific -> execute();
        $result_cheque_specific = $stmt_cheque_specific -> get_result();
        $row2 = $result_cheque_specific->fetch_assoc();
        foreach(explode(":-:", $row2['purchase_order_ids']) as $pid)
        {
            $sql_po_live = "SELECT * FROM `purchase_order` where purchase_order_id = ? and status != 'canceled' and status != 'All Complete' and (status = 'Payment Processed' OR status = 'Cheque Prepared')";
            $stmt_po_live = $conn->prepare($sql_po_live);  
            $stmt_po_live -> bind_param("i", $pid);
            $stmt_po_live -> execute();
            $result_po_live = $stmt_po_live -> get_result();
            if($result_po_live -> num_rows>0)
            {
                $row = $result_po_live -> fetch_assoc();
                updaterequest($conn,$conn_fleet,$row['request_id'],"three","back");
                $stat = "Finance Approved";
                $stmt_po_status -> bind_param("si", $stat, $pid);
                $stmt_po_status -> execute();
            }
        }
        $record_type = "cheque_info";
        $operation = "CPV Voided";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_GET['void'], $operation);
        $stmt_add_record -> execute();
        ////////////////////////////////
        $_SESSION['success'] = "Cheque Voided";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    
    if(isset($_GET["prepare"]) || isset($_GET["batch_prepare"]))
    {
        $ids = (isset($_GET["prepare"]))?[$_GET["prepare"]]:explode(",",$_GET["batch_prepare"]);
        foreach($ids as $in_n_pc)
        {
            $cluster_id = explode("::-::",$in_n_pc)[0];
            $pos = explode("::-::",$in_n_pc)[1];
            $cpv_no = explode("::-::",$in_n_pc)[2];
            $date=date("Y-m-d H:i:s");
            $status ="Cheque Prepared";
            $price = 0;
            $sql_cheque_specific_active = "SELECT * FROM `cheque_info` where cpv_no = ? AND void != 1";
            $stmt_cheque_specific_active = $conn->prepare($sql_cheque_specific_active);  
            $stmt_cheque_specific_active -> bind_param("i", $cpv_no);
            $stmt_cheque_specific_active -> execute();
            $result_cheque_specific_active = $stmt_cheque_specific_active -> get_result();
            if($result_cheque_specific_active->num_rows>0)
            {
                $row2 = $result_cheque_specific_active->fetch_assoc();
                $price = $row2['cheque_amount'];
                $cheque_only = (intval($row2['prepared_percent'])<100 && $row2['final']);
                    foreach(explode(":-:",$row2['purchase_order_ids']) As $po_id)
                    {
                        if(!$cheque_only)
                        {
                            $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `payment_provider`=? WHERE `purchase_order_id`=? and `status` != 'canceled' ");
                            $stmt -> bind_param("ssi", $status, $_SESSION['username'], $po_id);
                            $stmt -> execute();
                        }
    
                        $stmt_po -> bind_param("i", $po_id);
                        $stmt_po -> execute();
                        $result_po = $stmt_po -> get_result();
                        if($result_po->num_rows>0)
                            while($row2 = $result_po->fetch_assoc())
                            {
                                // $l=str_replace(" ","",$row2['request_type']);
                                if(!$cheque_only)
                                {
                                    updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","Cheque_prepare");
                                    $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? and `status` != 'canceled' ");
                                    $stmt_unique -> bind_param("si", $status, $row2['request_id']);
                                    $stmt_unique -> execute();
                                    $next_step = "Finalize Payment";
                                    $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                                    $stmt_next_step -> execute();
                                    $sql_rep = "UPDATE `report` SET `cheque_prepared_date` = ? WHERE `request_id` = ?";
                                    $stmt_rep_cheuqe_prepare = $conn->prepare($sql_rep);
                                    $stmt_rep_cheuqe_prepare -> bind_param("si",$date ,$row2['request_id']);
                                    $stmt_rep_cheuqe_prepare -> execute();
                                }
                                $_SESSION["success"]=$status;
                                $company = $row2['company'];
                                $finance_company = $row2['finance_company'];
                            }
                    }
            }
            
                if(!$cheque_only)
                {
                    $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=?,`cashier`=? WHERE `id`=? ");
                    $stmt_unique -> bind_param("ssi", $status, $_SESSION['username'], $cluster_id);
                    $stmt_unique -> execute();
                }

                $stmt_cluster->bind_param("i", $cluster_id);
                $stmt_cluster->execute();
                $result_cluster = $stmt_cluster->get_result();
                $r_clus = $result_cluster->fetch_assoc();
                
                $stmt_limit -> bind_param("s", $finance_company);
                $stmt_limit -> execute();
                $result_limit = $stmt_limit->get_result();
                if($result_limit->num_rows == 0)
                {
                    $other = "Others";
                    $stmt_limit -> bind_param("s", $other);
                    $stmt_limit -> execute();
                    $result_limit = $stmt_limit->get_result();
                }
                $r_limit = $result_limit->fetch_assoc();
                
                $stmt_company -> bind_param("s", $company);
                $stmt_company -> execute();
                $result_company = $stmt_company->get_result();
                $r_comp = $result_company->fetch_assoc();
            if($r_comp['cheque_signatory'] && $price<=$r_limit['cheque_limit'])
            {
                $cheque_company = $company;
            }
            else
            {
                $cheque_company = $r_comp['main'];
            }
            $s = ($cheque_only)?",`status` = 'pending payment processed'":"";
            $sql_cheque_company = "UPDATE cheque_info SET `cheque_company`=?$s WHERE `cpv_no` = ?";
            $stmt_cheque_company = $conn->prepare($sql_cheque_company);
            $stmt_cheque_company -> bind_param("si", $cheque_company, $cpv_no);
            $stmt_cheque_company -> execute();
            $reason_closed = "open_clust_".$cluster_id."_cashier";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $sql_signatory_account = "SELECT * FROM `account` where type LIKE '%Cheque Signatory%' and company = ? and role != 'Owner' and status='active'";
            $stmt_signatory_account = $conn->prepare($sql_signatory_account);  
            $stmt_signatory_account -> bind_param("s", $cheque_company);
            $stmt_signatory_account -> execute();
            $result_signatory_account = $stmt_signatory_account -> get_result();
            if($result_signatory_account->num_rows>0)
                while($row2 = $result_signatory_account->fetch_assoc())
                {
                    $reason = "open_clust_".$cluster_id."_cheque_signatory";
                    $email = $row2['email'];
                    $out = $row2['Username'];
                    $subject_email = "A Cheque is waiting to be signed";
                    $data_email = "<strong>A purchase order is waiting for cheque signing please review in a timely manner</strong><br><br><br>";
                    $send_to = $email.",".$out;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $out;
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
    
                    $email_id = $conn->insert_id;
                    $page = "requests/chequeSigning.php";
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
        }
        // header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["process_disbursement"]) || isset($_GET["batch_review"]))
    {
        $ids = (isset($_GET["process_disbursement"]))?[$_GET["process_disbursement"]]:explode(",",$_GET["batch_review"]);
        foreach($ids as $id)
        {
            // $id = $_GET["process"];
            $date=date("Y-m-d H:i:s");
            $status ="Reviewed";
            $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
            $stmt_unique -> bind_param("si", $status, $id);
            $stmt_unique -> execute();
            
            $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=?,`Checked_by`=? WHERE `id`=? ");
            $stmt_unique -> bind_param("ssi", $status, $_SESSION['username'], $id);
            $stmt_unique -> execute();
            $stmt_po_cluster_active->bind_param("i", $id);
            $stmt_po_cluster_active->execute();
            $result_po_cluster_active = $stmt_po_cluster_active->get_result();
            if($result_po_cluster_active->num_rows>0)
                while($row2 = $result_po_cluster_active->fetch_assoc())
                {
                    updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","Review");
                    // $l=str_replace(" ","",$row2['request_type']);
                    $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                    $stmt_unique -> bind_param("si", $status, $row2['request_id']);
                    $stmt_unique -> execute();
                    $next_step = "Finalize Payment";
                    $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                    $stmt_next_step -> execute();
                    $_SESSION["success"]=$status;
                    $sql_rep = "UPDATE `report` SET `Disbursement_review_date` = ? WHERE `request_id` = ?";
                    $stmt_rep_review = $conn->prepare($sql_rep);
                    $stmt_rep_review -> bind_param("si",$date ,$row2['request_id']);
                    $stmt_rep_review -> execute();
                }
            $reason_closed = "open_clust_".$id."_finance_1";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $sql_account_finance_manager = "SELECT * FROM `account` where department = 'Finance' AND (role = 'manager' OR `type` LIke '%manager%') and company = ?";
            $stmt_account_finance_manager = $conn->prepare($sql_account_finance_manager);  
            $stmt_account_finance_manager -> bind_param("s", $_SESSION['company']);
            $stmt_account_finance_manager -> execute();
            $result_account_finance_manager = $stmt_account_finance_manager -> get_result();
            if($result_account_finance_manager->num_rows>0)
                while($row2 = $result_account_finance_manager->fetch_assoc())
                {
                    $email = $row2['email'];
                    $out = $row2['Username'];
                    $subject_email = "A Purchase order was sent for Financial Processing";
                    $data_email = "<strong>A Purchase order was sent by disbursement and Collection department for Financial Processing please review in a timely manner</strong><br><br><br>";
                    $send_to = $email.",".$out;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $out;
                    $reason = "open_clust_".$id."_finance_2";
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
                    
                    $email_id = $conn->insert_id;
                    $page = "finance/financeApproval.php";
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
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