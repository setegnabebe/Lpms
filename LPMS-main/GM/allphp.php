<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
if(isset($_SESSION['username']))
{
    if(isset($_GET['btntype']) || isset($_GET['batch_approve']) || isset($_GET['batch_reject']))
    {
        if(isset($_GET['batch_approve']) || isset($_GET['batch_reject']))
        {
            $all_req = (isset($_GET['batch_approve']))?explode(",",$_GET['batch_approve']):explode(",",$_GET['batch_reject']);
        }
        else
            $all_req = [explode("_",$_GET['btntype'])[2]];
        foreach($all_req as $req_id)
        {
            if(isset($_GET['reason']) && $_GET['reason'] != "")
            {
                $level = "GM";
                $stmt_remark -> bind_param("ssss",$req_id,$_GET['reason'],$_SESSION['username'],$level);
                $stmt_remark -> execute();
            }
            if(isset($_GET['btntype']))
                $app =(strpos($_GET['btntype'],"approve") !==false)?true:false;
            else
                $app =(isset($_GET['batch_approve']))?true:false;
                
            $date_app=date("Y-m-d H:i:s");
            $approved_by = $_SESSION['username'];
            $for_spec = false;
            $stmt_request->bind_param("i", $req_id);
            $stmt_request->execute();
            $result_request = $stmt_request->get_result();
            if($result_request->num_rows>0)
            while($row_temp = $result_request->fetch_assoc())
            {
                $type = $row_temp['request_type'];
                $na_t = str_replace(" ","",$type);
                $requested_by = $row_temp['customer'];
                $item = $row_temp['item'];
                $depp = $row_temp['department'];
                $req_quan = $row_temp['requested_quantity'];
                $unit = $row_temp['unit'];
                $date_n_b = $row_temp['date_needed_by'];
                $specification = ($row_temp['spec_dep'] == 'IT' && is_null($row_temp['specification']));
                $property_company = $row_temp['property_company'];
                $procurnment_comapnt=$row_temp['procurement_company'];
                if(!is_null($row_temp['spec_dep']) && is_null($row_temp['specification']))
                {
                    $for_spec = true;
                }
                // $for_spec = false;
            }
            if($app)
            {
                $status = "Approved By GM";
                $email_stat = "Approved";
                $next= ($specification)?"IT Specification":"Store";
                $nxt=($type=="agreement" || $type=="Agreement")?"directors":$next;
                echo $nxt;
                $send_to = "";
                $tag = "";
                if($specification)
                {
                    $date_app=date("Y-m-d H:i:s");
                    $sql_rep = "UPDATE `report` SET `sent_for_spec` = ? WHERE `request_id` = ?";
                    $stmt_rep_spec = $conn->prepare($sql_rep);
                    $stmt_rep_spec -> bind_param("si",$date_app ,$req_id);
                    $stmt_rep_spec -> execute();

                    $stmt_company -> bind_param("s", $_SESSION['company']);
                    $stmt_company -> execute();
                    $result_company = $stmt_company -> get_result();
                    $row_comp = $result_company->fetch_assoc();
                    $IT_company = ($row_comp['IT'])?$row_comp['Name']:"Hagbes HQ.";

                    $data_for_email = "<strong>There is a purchase request in $depp department waiting for IT Specification</strong><br>
                    <strong>Please enter a valid specification fitting the item </strong><br>";
                    $subject_email = "There is a purchase request In $depp department waiting for IT Specification";
                    $reason = "open_req_".$na_t."_".$req_id."_specification";
                    $sql_IT_manager = "SELECT * FROM `account` where `department` = 'IT' AND company = ? and (role = 'manager' OR `type` LIke '%manager%') and status='active'";
                    $stmt_IT_manager = $conn->prepare($sql_IT_manager);  
                    $stmt_IT_manager->bind_param("s", $IT_company);
                    $stmt_IT_manager->execute();
                    $result_email = $stmt_IT_manager->get_result();
                }
                else
                {
                    $data_for_email = "<strong>$item purchase request In $depp department waiting for store check please review in a timely manner<strong><br>";
                    $subject_email = "$item purchase request In $depp department waiting for store check";
                    $reason = "open_req_".$na_t."_".$req_id."_store";
                    $sql_store_manager = "SELECT * FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = ?  and status='active'";
                    $stmt_store_manager = $conn->prepare($sql_store_manager);
                    $stmt_store_manager->bind_param("s", $property_company);
                    $stmt_store_manager->execute();
                    $result_email = $stmt_store_manager->get_result();
                }
                if($result_email->num_rows>0)
                    while($row_email = $result_email->fetch_assoc())
                    {
                        $email = $row_email['email'];
                        $email_to = $row_email['Username'];
                        $tag = $email_to;
                        $send_to = $email.",".$email_to;
                        $uname =str_replace("."," ",$requested_by);
                        $data_email = "
                        $data_for_email
                        <ul>
                        <li><strong> Catagory - </strong> $type <br></li>
                        <li><strong> Item - </strong> $item <br></li>
                        <li><strong> Requested By - </strong> $uname <br></li>
                        <li><strong> Quantity - </strong> $req_quan $unit <br></li>
                        <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($date_n_b))." <br></li>
                        </ul>
                        <br><br><br>";
                        
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; 
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                    }
            }
            else
            {
                $email_to = $requested_by;
                $status = "Rejected By GM";
                $email_stat = "Rejected";
                $nxt = "GM Rejected";
                $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$requested_by."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email);
                $stmt2->fetch();
                $stmt2->close();
                $send_to = $email.",".$requested_by;
                
                $subject_email = "$item Purchase Request In ".$_SESSION['department']." Department";
                $data_for_email = "<strong>Your $type Purchase Request for $item Was $email_stat by $_SESSION[username] </strong><br>";
                $reason = "closed";
               
                $tag = $requested_by;
                $uname =str_replace("."," ",$requested_by);
                $data_email = "
                $data_for_email
                <ul>
                <li><strong> Catagory - </strong> $type <br></li>
                <li><strong> Item - </strong> $item <br></li>
                <li><strong> Requested By - </strong> $uname <br></li>
                <li><strong> Quantity - </strong> $req_quan $unit <br></li>
                <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($date_n_b))." <br></li>
                </ul>
                <br><br><br>";
                
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = ""; 
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
            }
        
            $sql_request_update = "UPDATE requests SET `status` = ?,GM = ?,`next_step` = ? WHERE `request_id` = ?";
            $stmt_request_update = $conn->prepare($sql_request_update);
            $stmt_request_update -> bind_param("sssi", $status, $_SESSION['username'], $nxt, $req_id);
            $stmt_request_update -> execute();

            $sql_rep = "UPDATE `report` SET `GM_approval_date`= ? WHERE `request_id` = ?";
            $stmt_rep_GM = $conn->prepare($sql_rep);
            $stmt_rep_GM -> bind_param("si",$date_app ,$req_id);
            $stmt_rep_GM -> execute();
            
            $reason_close = "open_req_".$na_t."_".$req_id."_gm_approve";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
        
            if($status == "Approved By GM")
            {
                $email_id = $conn->insert_id;
                if($type == "Fixed Assets")
                    $page = "committee/ownerApproval.php";
                else
                    $page = "property/storeclerk.php";
                $stmt_email_page -> bind_param("si",$page, $email_id);
                $stmt_email_page -> execute();
            }
            $_SESSION["success"]=$email_stat;
            updaterequest($conn,$conn_fleet,$req_id,"one","","GM");
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