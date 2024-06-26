<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
if(isset($_SESSION['username']))
{
    if(isset($_GET["process_pettycash"]) || isset($_GET["batch_review"]))
    {
        $ids = (isset($_GET["process_pettycash"]))?[$_GET["process_pettycash"]]:explode(",",$_GET["batch_review"]);
        foreach($ids as $id_prov)
        {
            $id = explode("::-::",$id_prov)[0];
            $providing_company = explode("::-::",$id_prov)[1];
            // $id = $_GET["process"];
            $date=date("Y-m-d H:i:s");
            $status ="Petty Cash Approved";
            
            // $stmt_unique = $conn->prepare("UPDATE `cluster` SET `status`=?,`cheque_signatories`=? WHERE `id`=? ");
            // $stmt_unique -> bind_param("ssi", $status, $_SESSION['username'], $id);
            // $stmt_unique -> execute();
            $sig = "Petty Cash - ".$_SESSION['username'];
            $stmt_unique = $conn->prepare("UPDATE `cluster` SET `cheque_signatories`=? WHERE `id`=? ");
            $stmt_unique -> bind_param("si", $sig, $id);
            $stmt_unique -> execute();
            $sql_price_company = "SELECT * FROM `price_information` where cluster_id = ? AND providing_company = ? AND selected";
            $stmt_price_company = $conn->prepare($sql_price_company);  
            $stmt_price_company->bind_param("is", $id, $providing_company);
            $stmt_price_company->execute();
            $result_price_company = $stmt_price_company->get_result();
            if($result_price_company->num_rows>0)
            {
                while($row_pi = $result_price_company->fetch_assoc())
                {
                    $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `purchase_order_id`=? ");
                    $stmt_unique -> bind_param("si", $status, $row_pi['purchase_order_id']);
                    $stmt_unique -> execute();
                    $stmt_po->bind_param("i", $row_pi['purchase_order_id']);
                    $stmt_po->execute();
                    $result_po = $stmt_po->get_result();
                    if($result_po->num_rows>0)
                        while($row2 = $result_po->fetch_assoc())
                        {
                            // $l=str_replace(" ","",$row2['request_type']);
                            $stmt_unique = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                            $stmt_unique -> bind_param("si", $status, $row2['request_id']);
                            $stmt_unique -> execute();
                            $next_step = "Finalize Payment";
                            $stmt_next_step -> bind_param("si", $next_step, $row2['request_id']);
                            $stmt_next_step -> execute();
                            updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","Petty Cash");
                            $_SESSION["success"]=$status;
                            $sql_rep = "UPDATE `report` SET `cheque_signed_date` = ? WHERE `request_id` = ?";
                            $stmt_rep_cheque = $conn->prepare($sql_rep);
                            $stmt_rep_cheque -> bind_param("si",$date ,$row2['request_id']);
                            $stmt_rep_cheque -> execute();
                        }
                }
            }
            $reason_closed = "open_clust_".$id."_disbursement";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
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
                    $subject_email = "A Purchase order was sent for petty cash";
                    $data_email = "<strong>A Purchase order was sent by for petty cash please hand over in a timely manner</strong><br><br><br>";
                    $send_to = $email.",".$out;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $out;
                    $reason = "open_clust_".$id."_cashier";
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
                    
                    $email_id = $conn->insert_id;
                    $page = "finance/cashier.php";
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    ///////////////////////////////////////////Petty Cash//////////////////////////////////////////////////////////
    if(isset($_GET["petty_cash_approval"]) || isset($_GET["batch_petty_cash_approval"]))
    {
        $status ="Petty Cash Approved";
        $date=date("Y-m-d H:i:s");
        if(isset($_GET["petty_cash_approval"]))
            $pids = [$_GET["petty_cash_approval"]];
        else
            $pids = explode(",",$_GET["batch_petty_cash_approval"]);
        foreach($pids as $pid)
        {
            // $pid=$_GET["opened_Performa"];
            $stmt_po->bind_param("i", $pid);
            $stmt_po->execute();
            $result_po = $stmt_po->get_result();
            $row = $result_po->fetch_assoc();
            $record_type = "purchase_order";
            $operation = "Petty Cash Approved";
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['purchase_order_id'], $operation);
            $stmt_add_record -> execute();
            $next_step = "Cashier";

            $stmt_po_status -> bind_param("si", $status,  $pid);
            $stmt_po_status -> execute();

            $stmt_status_update -> bind_param("si", $status, $row['request_id']);
            $stmt_status_update -> execute();

            $stmt_next_step -> bind_param("si", $next_step,  $row['request_id']);
            $stmt_next_step -> execute();
            
            $sql_rep = "UPDATE `report` SET `cheque_signed_date` = ? WHERE `request_id` = ?";
            $stmt_rep_cheque = $conn->prepare($sql_rep);
            $stmt_rep_cheque -> bind_param("si",$date ,$row['request_id']);
            $stmt_rep_cheque -> execute();
    
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "open_".$na_t."_".$row["request_id"]."_disbursement";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
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
                    $subject_email = "A Purchase order was sent for petty cash";
                    $data_email = "<strong>A Purchase order was sent by for petty cash please hand over in a timely manner</strong><br><br><br>";
                    $send_to = $email.",".$out;
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $cc =""; $bcc = ""; $tag = $out;
                    $reason = "open_PO_".$pid."_cashier";
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();
                    
                    $email_id = $conn->insert_id;
                    $page = "finance/cashier.php";
                    $stmt_email_page -> bind_param("si",$page, $email_id);
                    $stmt_email_page -> execute();
                }
    
        }
        $_SESSION["success"]="Sent for Approval";
        header("location: ".$_SERVER['HTTP_REFERER']); 
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
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