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
                $level = "Director";
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
                $status = "Approved By Director";
                $email_stat = "Approved";
                $nxt = ($type == "Fixed Assets")?"Owner":"Performa";
                $send_to = "";
                $tag = "";
                if($type == "Fixed Assets")
                {
                    $data_for_email = "<strong>There is a purchase request in $depp department waiting for owner approval please review<strong><br>";
                    $subject_email = "There is a fixed asset purchase request In $depp department";
                    $reason = "open_req_".$na_t."_".$req_id."_owner";
                    
                    $sql_email = "SELECT * FROM `account` where `department` = 'Owner'";
                    $stmt_email_owner = $conn->prepare($sql_email);
                    $stmt_email_owner -> execute();
                    $result_email = $stmt_email_owner -> get_result();
                }
                else
                {
                    $reason = "open_req_".$na_t."_".$req_id."_GS_assign";
                    $sql_email = "SELECT * FROM `account` where ((`department` = 'Procurement' AND (role = 'manager' OR `type` LIke '%manager%')) OR `additional_role` = 1) AND company = ? and status='active'";
                    $stmt_email_proc_manager = $conn->prepare($sql_email);
                    $stmt_email_proc_manager -> bind_param("s",$procurnment_comapnt);
                    $stmt_email_proc_manager -> execute();
                    $result_email = $stmt_email_proc_manager -> get_result();
                    $subject_email = "An item was sent for purchase";
                    $data_for_email='please assign a purchase officer for Proforma gathering';
                }
                // echo $sql_email; 
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
                $status = "Rejected By Director";
                $email_stat = "Rejected";
                $nxt = "Director Rejected";
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
        
            $sql_req = "UPDATE requests SET `status`=?,`director`=?,`next_step`=? WHERE `request_id`=?";
            $stmt_req_director = $conn->prepare($sql_req);
            $stmt_req_director -> bind_param("sssi",$status ,$_SESSION['username'] ,$nxt ,$req_id);
            $stmt_req_director -> execute();
            $sql_rep = "UPDATE `report` SET `Director_approval_date` = ? WHERE `request_id` = ?";
            $stmt_rep_director = $conn->prepare($sql_rep);
            $stmt_rep_director -> bind_param("si",$date_app ,$req_id);
            $stmt_rep_director -> execute();
            $reason_close = "open_req_".$na_t."_".$req_id."_director_approve";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
        
        
            if($status == "Approved By Director")
            {
                $email_id = $conn->insert_id;
                if($type == "Fixed Assets")
                    $page_to = "committee/ownerApproval.php";
                else{
                    $page_to = "Procurement/GS/assignOfficer.php";
                }
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
            $_SESSION["success"]=$email_stat;
            if($for_spec)
            {
                $date_app=date("Y-m-d H:i:s");
                $sql_rep = "UPDATE `report` SET `sent_for_spec` = ? WHERE `request_id` = ?";
                $stmt_rep_spec = $conn->prepare($sql_rep);
                $stmt_rep_spec -> bind_param("si",$date_app ,$req_id);
                $stmt_rep_spec -> execute();
                
                $stmt_company -> bind_param("s", $_SESSION['company']);
                $stmt_company -> execute();
                $result_company = $stmt_company -> get_result();
                $row_comp = $result_company -> fetch_assoc();
                $IT_company = ($row_comp['IT'])?$row_comp['Name']:"Hagbes HQ.";
                $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where (role = 'manager' OR `type` LIke '%manager%') AND department = 'IT' And company='$IT_company'  and status='active'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email,$uname);
                $stmt2->fetch();
                $stmt2->close();
                $reason = "open_req_".$na_t."_".$req_id."_specification";
                $subject_email = "Item - $item is awaiting specification details";
                $data_email = "
                <strong>An Item - $item Purchase request was sent to your department<strong><br>
                <strong>Please enter a valid specification fitting the item </strong><br>
                ";
                $send_to = $email.",".$uname;
                $cc =""; $bcc = ""; $tag = $uname;
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
            }
            updaterequest($conn,$conn_fleet,$req_id,"one","","Director");
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