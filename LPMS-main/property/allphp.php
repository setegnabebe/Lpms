<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";




if(isset($_SESSION['username']))
{
//////////////////////////////Store Check///////////////////////////////////////////////
    if(isset($_GET['replacement']))
    {
        $status = explode("_",$_GET['replacement'])[1];
        $request_id = explode("_",$_GET['replacement'])[0];
        $status = ($status == "Rejected")?"Not Found":$status;
        $sql2 = "INSERT INTO `replacements_collected` (`request_id`, `status`, `remarks`, `collected_by`) VALUES (?,?,?,?)";
        $stmt_replacements_collected = $conn->prepare($sql2);
        $stmt_replacements_collected -> bind_param("isss",$request_id, $status, $_GET['reason'], $_SESSION['username']);
        $stmt_replacements_collected -> execute();
        $last_id = $conn->insert_id;
        $sql2 = "UPDATE requests SET `replaced_items` = ? WHERE `request_id` = ?";
        $stmt_replaced_items = $conn->prepare($sql2);
        $stmt_replaced_items -> bind_param("ii", $last_id, $request_id);
        $stmt_replaced_items -> execute();
        $_SESSION["success"]="Reqeusts ".$status;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_GET['in_stock']) || isset($_GET['out_of_stock']) || isset($_GET['batch_instock']) || isset($_GET['batch_outofstock']))
    {
        if(isset($_GET['batch_instock']) || isset($_GET['batch_outofstock']))
        {
            $idss = (isset($_GET['batch_instock']))?explode(",",$_GET['batch_instock']):explode(",",$_GET['batch_outofstock']);
            $batch_stock = (isset($_GET['batch_instock']));
        }
        else
        {
            $idss = (isset($_GET['out_of_stock']))?[$_GET['out_of_stock']]:[$_GET['in_stock']];
        }
        foreach($idss as $rid)
        {
            $stmt_request -> bind_param("i", $rid);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row_temp = $result_request -> fetch_assoc();
            $type = $row_temp['request_type'];
            $na_t=str_replace(" ","",$row_temp['request_type']);
            $full = $row_temp['requested_quantity'];
            if(isset($batch_stock))
                $amount = ($batch_stock)?$full:0;
            else
                $amount = $_GET['stock_amount'];
            $av_price = 0;
            $tp =  $av_price * $amount;
            $left = $full - $amount;
            $zero = 0;

            $stmt = $conn->prepare("INSERT INTO `stock`(`request_id`, `type`, `check_by`, `requested_quantity`, `in-stock`, `for_purchase`, `average_price`, `total_price`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("issdddii",$rid , $type, $_SESSION["username"], $full, $amount, $left, $zero,$zero);
            $stmt -> execute();
            $last_id = $conn->insert_id;
            if($left == 0)
                $status = 'Found In Stock';
            else
                $status = "Store Checked";
            $sql2 = "UPDATE requests SET `stock_info` = ?,`status` = ? WHERE `request_id` = ?";
            $stmt_stock_info = $conn->prepare($sql2);
            $stmt_stock_info -> bind_param("isi",$last_id, $status, $rid);
            $stmt_stock_info -> execute();
            $date_app=date("Y-m-d H:i:s");
            $sql_rep = "UPDATE `report` SET `stock_check_date` = ? WHERE `request_id` = ?";
            $stmt_report = $conn->prepare($sql_rep);
            $stmt_report -> bind_param("si",$date_app, $rid);
            $stmt_report -> execute();
            if(($type == "Tyre and Battery" && $row_temp['mode'] == 'Internal') || 
            ($type == "Spare and Lubricant" && $row_temp['mode'] == 'Internal' && $row_temp['type'] == 'Spare') || 
            $type != "Tyre and Battery" && $type != "Spare and Lubricant" && $type != "Miscellaneous" && $type != "Consumer Goods")
            $prop = true;
            else
            $prop = false;
            $reason_close = "open_req_".$na_t."_".$rid."_store";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            if($left != $full || $prop)
            {
                //Email Property manager
                $send_to = "";
                $stmt_manager_active -> bind_param("ss", $dep, $row_temp['property_company']);
                $stmt_manager_active -> execute();
                $result_manager_active = $stmt_manager_active -> get_result();
                if($result_manager_active -> num_rows > 0)
                    while($row_email = $result_manager_active -> fetch_assoc())
                    {
                        $email = $row_email['email'];
                        $email_to = $row_email['Username'];
                        $send_to = $email.",".$email_to;
                        $reason = "open_req_".$na_t."_".$rid."_prop_manager";
            
                        $subject_email = "An item is waiting for property approval";
                        $uname = str_replace("."," ",$row_temp['customer']);
                        $data_email = "
                        <strong>$subject_email<strong><br>
                        <ul>
                            <li><strong> Catagory - </strong> $type <br></li>
                            <li><strong> Item - </strong> $row_temp[item] <br></li>
                            <li><strong> Requested By - </strong>$uname<br></li>
                            <li><strong> Quantity - </strong> $row_temp[requested_quantity] $row_temp[unit] <br></li>
                            <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($row_temp['date_needed_by']))." <br></li>
                        </ul>
                        <br><br><br>
                        ";
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = isset($email_to)?$email_to:"";
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        
                        $email_id = $conn->insert_id;
                        $page_to = "property/propertyApproval.php";
                        $stmt_email_page -> bind_param("si",$page_to, $email_id);
                        $stmt_email_page -> execute();
                    }
    
                $nxt_step = "Property";
                $stmt_next_step -> bind_param("si",$nxt_step , $rid);
                $stmt_next_step -> execute();
                        
            }
            if(!$prop)
            {
                $send_to = "";
                if($row_temp['company'] == 'Hagbes HQ.' && $row_temp["department"] != 'Owner' && $row_temp["customer"] != $row_temp["director"])
                {
                    $next = "Director";
                    $reason = "open_req_".$na_t."_".$rid."_director_approve";
                    $dep_like = "%$row_temp[department]%";
                    $sql_email = "SELECT * FROM `account` where (`managing` LIKE ? OR `managing` LIKE '%All Departments%') and role = 'Director' AND company = ? and status='active'";
                    $stmt_managing_director = $conn->prepare($sql_email);
                    $stmt_managing_director -> bind_param("ss", $dep_like, $row_temp['company']);
                    $stmt_managing_director -> execute();
                    $result_managing_director = $stmt_managing_director -> get_result();
                    if($result_managing_director -> num_rows>0)
                        while($row_email = $result_managing_director -> fetch_assoc())
                        {
                            $email = $row_email['email'];
                            $email_to = $row_email['Username'];
                            $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                        }
                        $subject_email = "An Item was sent For Direcor Approval";
                        $data_for_email='Please Approve the following request';
                        $topage='';
                }
                else
                {
                    if($row_temp['company'] == 'Hagbes HQ.' && $row_temp["customer"] == $row_temp["director"])
                        echo updaterequest($conn,$conn_fleet,$rid,"one","","Director");
                    $next = 'Performa';
                    $reason = "open_req_".$na_t."_".$rid."_GS_assign";
                    $stmt_proc_manager -> bind_param("s", $row_temp['procurement_company']);
                    $stmt_proc_manager -> execute();
                    $result_proc_manager = $stmt_proc_manager -> get_result();
                    if($result_proc_manager -> num_rows>0)
                        while($row_email = $result_proc_manager -> fetch_assoc())
                        {
                            $email = $row_email['email'];
                            $email_to = $row_email['Username'];
                            $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                        }
                        $subject_email = "An Item was sent For Purchase";
                        $data_for_email='please assign a purchase officer for proforma gathering';
                        $topage='Procurement/GS/assignOfficer.php';
                }
                $stmt_next_step -> bind_param("si",$next , $rid);
                $stmt_next_step -> execute();
                
                $uname = str_replace("."," ",$row_temp['customer']);
                $data_email = "
                <strong>$subject_email $data_for_email<strong><br>
                <ul>
                    <li><strong> Catagory - </strong> $type <br></li>
                    <li><strong> Item - </strong> $row_temp[item] <br></li>
                    <li><strong> Requested By - </strong>$uname<br></li>
                    <li><strong> Quantity - </strong> $row_temp[requested_quantity] $row_temp[unit] <br></li>
                    <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($row_temp['date_needed_by']))." <br></li>
                </ul>
                <br><br><br>
                ";
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = ""; $tag = "";
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                $email_id = $conn->insert_id;
                $stmt_email_page -> bind_param("si",$topage, $email_id);
                $stmt_email_page -> execute();
        }
        if($row_temp['company'] != 'Hagbes HQ.' || $row_temp["customer"] != $row_temp["director"] || $prop)
            updaterequest($conn,$conn_fleet,$rid,"one","","Store");
        }
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

//////////////////////////////////////////////////////////////////Prop Approval/////////////////////////////////////////////////////////////

    if(isset($_GET["property_approval"]) || isset($_GET['property_rejection']) || isset($_GET["batch_approve"]) || isset($_GET["batch_reject"])) //
    {
        $reason = $_GET["reason"];
        if(isset($_GET["property_approval"]) || isset($_GET['property_rejection']))
        {
            $idss = (isset($_GET["property_approval"]))?[$_GET["property_approval"]]:[$_GET['property_rejection']];
        }
        else
        {
            $idss = (isset($_GET["batch_approve"]))?explode(",",$_GET["batch_approve"]):explode(",",$_GET["batch_reject"]);
        }
        foreach($idss as $request_id)
        {
            unset($stock_approved);
            unset($purchase_approved);
            $status = (isset($_GET["property_approval"]) || isset($_GET["batch_approve"]))?"Approved":"Rejected";
            $sql_request_stock = "SELECT *, R.status AS `rstatus`, S.status AS `sstatus` FROM requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id where R.request_id = ?";
            $stmt_request_stock = $conn->prepare($sql_request_stock);
            $stmt_request_stock -> bind_param("i", $request_id);
            $stmt_request_stock -> execute();
            $result_request_stock = $stmt_request_stock -> get_result();
            if($result_request_stock -> num_rows>0)
                while($row_temp = $result_request_stock -> fetch_assoc()) 
                {
                    $type = $row_temp['request_type'];
                    $na_t = str_replace(" ","",$row_temp['request_type']);
                    $item = $row_temp['item'];
                    $cust = $row_temp['customer'];
                    $dep = $row_temp['department'];
                    $comp = $row_temp['company'];
                    $processing_comp = $row_temp['processing_company'];
                    $procurement_company = $row_temp['procurement_company'];
                    $property_company = $row_temp['property_company'];
                    $date_requested = date("d-M-Y", strtotime($row_temp['date_requested']));
                    $km = (isset($row_temp['current_km']))?$row_temp['current_km']:"";
                    if(isset($_GET["property_approval"]))
                    {
                        $purchase_quantity = ($_GET['purchase_quantity'] != $row_temp['for_purchase'])?$_GET['purchase_quantity']:$row_temp['for_purchase'];
                        $stock_quantity = ($_GET['stock_quantity'] != $row_temp['in-stock'])?$_GET['stock_quantity']:$row_temp['in-stock'];
                    }
                    else
                    {
                        $purchase_quantity = $row_temp['for_purchase'];
                        $stock_quantity = $row_temp['in-stock'];
                    }
                    if($stock_quantity != $row_temp['in-stock'] || $purchase_quantity != $row_temp['for_purchase'])
                    {
                        $total_update = $purchase_quantity + $stock_quantity;
                        $total_old = $row_temp['for_purchase'] + $row_temp['in-stock'];
                        $total_text = ($total_update != $total_old)?"Total from $total_old to $total_update,":"";
                        $record_type = "reqeusts";
                        $record = "Property Value Change $total_text instock from ".$row_temp['in-stock']." to $stock_quantity, purchase quantity from ".$row_temp['for_purchase']." to $purchase_quantity";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $request_id, $record);
                        $stmt_add_record -> execute();

                        $sql_update_quantity_stock = "UPDATE stock SET `requested_quantity` = ?, `in-stock` = ?, `for_purchase` = ? WHERE `request_id` = ?";
                        $stmt_update_quantity_stock = $conn->prepare($sql_update_quantity_stock);
                        $stmt_update_quantity_stock -> bind_param("dddi", $total_update, $stock_quantity, $purchase_quantity, $request_id);
                        $stmt_update_quantity_stock -> execute();
                        if($total_update != $total_old)
                        {
                            $sql_quantity_request = "UPDATE requests SET `requested_quantity` = ? WHERE `request_id` = ?";
                            $stmt_quantity_request = $conn->prepare($sql_quantity_request);
                            $stmt_quantity_request -> bind_param("di", $total_update, $request_id);
                            $stmt_quantity_request -> execute();
                        }
                    }
                    if($stock_quantity > 0) $stock_approved = true;
                    if($purchase_quantity != 0) $purchase_approved = true;
                }

                if(isset($_GET['reason']) && $_GET['reason'] != "")
                {
                    $remark = str_replace("'","'",$_GET['reason']);
                    $level = "Property";
                    $stmt_remark -> bind_param("isss", $request_id, $remark, $_SESSION['username'], $level);
                    $stmt_remark -> execute();
                }
            $stmt_request -> bind_param("i", $request_id);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows>0)
                $row_temp = $result_request -> fetch_assoc();
            if(isset($stock_approved))
            {
                $next = 'Department Approval';
                $sql_stock_status = "UPDATE stock SET `status`=?, `remark`=? WHERE `request_id` = ?";
                $stmt_stock_status = $conn->prepare($sql_stock_status);
                $stmt_stock_status -> bind_param("ssi", $status, $remark, $request_id);
                $stmt_stock_status -> execute();
                if(!isset($purchase_approved))
                {
                    $status_request = "Found In Stock";
                    $stmt_status_update -> bind_param("si", $status_request, $request_id);
                    $stmt_status_update -> execute();
                }
                $sql_request_update = "UPDATE requests SET `property` = ?, `next_step` = ? WHERE `request_id` = ?";
                $stmt_request_update = $conn->prepare($sql_request_update);
                $stmt_request_update -> bind_param("ssi", $_SESSION['username'], $next, $request_id);
                $stmt_request_update -> execute();
                $_SESSION["success"]="Item ".$status;
                $reason_close = "open_req_".$na_t."_".$request_id."_prop_manager";
                $stmt_email_close -> bind_param("s",$reason_close);
                $stmt_email_close -> execute();
                $reason = "open_req_".$na_t."_".$request_id."_instock_approval";

                if($status == "Approved")
                    $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `department`='".$dep."' AND `company`='$comp' AND (role = 'manager' OR `type` LIke '%manager%') AND `status` = 'active'");
                else
                    $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `Username`='".$cust."'  and status='active'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email,$cust);
                $stmt2->fetch();
                $stmt2->close();
                $subject_email = "In-stock approval of $item requested on $date_requested";
                if($status == "Approved") $data_temp = " Please gotopProperty department and review the item<strong><br><br>";
                if($status == "Approved") $data_temp = "";
                $uname = str_replace("."," ",$row_temp['customer']);
                $data_email = "
                <strong>The $item requested was $status by property department $data_temp</strong><br><br>
                <ul>
                    <li><strong> Catagory - </strong> $type <br></li>
                    <li><strong> Item - </strong> $row_temp[item] <br></li>
                    <li><strong> Requested By - </strong> $uname <br></li>
                    <li><strong> Quantity - </strong> $row_temp[requested_quantity] $row_temp[unit] <br></li>
                    <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($row_temp['date_needed_by']))." <br></li>
                </ul><br>
                ";
                $send_to = $email.",".$cust;
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = "";
                $tag = (isset($cust))?$cust:"";
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                
                $email_id = $conn->insert_id;
                $page_to = "requests/itemCheck.php";
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
                
            }
            
            if(isset($purchase_approved))
            {
                $status = $status." By Property";
                $reason_close = "open_req_".$na_t."_".$request_id."_prop_manager";
                $stmt_email_close -> bind_param("s",$reason_close);
                $stmt_email_close -> execute();
                $send_to = "";
                $topage='';
                if($status == "Approved By Property")
                {
                    if($row_temp['company'] == 'Hagbes HQ.' && $row_temp["department"] != 'Owner' && $row_temp["customer"] != $row_temp["director"])
                    {
                        $next = "Director";
                        $reason = "open_req_".$na_t."_".$request_id."_director_approve";
                        $like_department = "%$row_temp[department]%";
                        $sql_managing_director = "SELECT * FROM `account` where (`managing` LIKE ? OR `managing` LIKE '%All Departments%') and role = 'Director' AND company = ? and status='active'";
                        $stmt_managing_director = $conn->prepare($sql_managing_director);
                        $stmt_managing_director -> bind_param("ss", $like_department, $row_temp['company']);
                        $stmt_managing_director -> execute();
                        $result_managing_director = $stmt_managing_director -> get_result();
                        if($result_managing_director -> num_rows>0)
                            while($row_email = $result_managing_director -> fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                            }
                            $subject_email = "An Item was sent For Direcor Approval";
                            $data_for_email='Please Approve the following request';
                    }
                    else if($row_temp['request_type'] == 'Fixed Assets' && $row_temp["department"] != 'Owner')
                    {
                        if($row_temp["customer"] == $row_temp["director"])
                        {
                            updaterequest($conn,$conn_fleet,$request_id,"one","","Director");
                        }
                        $next = 'Owner';
                        $reason = "open_req_".$na_t."_".$request_id."_owner";
                        $sql_owner = "SELECT * FROM `account` where role = 'Owner' and status = 'active'";
                        $stmt_owner = $conn->prepare($sql_owner);
                        $stmt_owner -> execute();
                        $result_owner = $stmt_owner -> get_result();
                        if($result_owner -> num_rows > 0)
                            while($row_email = $result_owner -> fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                            }
                            $subject_email = "An Item was sent For Owner Approval";
                            $data_for_email='Please Approve the following request';
                            $topage='';
                    }
                    else
                    {
                        if(($row_temp["department"] == 'Owner' && $row_temp['request_type'] == 'Fixed Assets') || $row_temp["customer"] == $row_temp["director"])
                        {
                            updaterequest($conn,$conn_fleet,$request_id,"one","","Owner");
                        }
                        $next = 'Performa';
                        $reason = "open_req_".$na_t."_".$request_id."_GS_assign";
                        $stmt_proc_manager -> bind_param("s", $row_temp['procurement_company']);
                        $stmt_proc_manager -> execute();
                        $result_proc_manager = $stmt_proc_manager -> get_result();
                        if($result_proc_manager -> num_rows > 0)
                            while($row_email = $result_proc_manager -> fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                            }
                            $subject_email = "An Item was sent For Purchase";
                            $data_for_email='please assign a purchase officer for proforma gathering';
                            $topage='Procurement/GS/assignOfficer.php';
                    }
                }
                else
                {
                    $next = $status;
                    $reason = "closed";
                    $sql_email_customer = "SELECT `email`,`Username` FROM `account` where `Username` = ?";
                    $stmt_email_customer = $conn->prepare($sql_email_customer);
                    $stmt_email_customer -> bind_param("s", $cust);
                    $stmt_email_customer -> execute();
                    $result_email_customer = $stmt_email_customer -> get_result();
                    if($result_email_customer -> num_rows>0)
                        while($row = $result_email_customer -> fetch_assoc()) 
                        {
                            $send_to .= ($send_to == "")?$row['email'].",".$row['Username']:",".$row['email'].",".$row['Username'];
                            $email_to = $row['Username'];
                        }
                        $subject_email = "Item was rejected by $_SESSION[username]";
                        $data_for_email = "";
                }
                $sql_request_update = "UPDATE requests SET `status`=?, `property` = ?, `next_step` = ? WHERE `request_id` = ?";
                $stmt_request_update = $conn->prepare($sql_request_update);
                $stmt_request_update -> bind_param("sssi", $status, $_SESSION['username'], $next, $request_id);
                $stmt_request_update -> execute();
                $uname = str_replace("."," ",$row_temp['customer']);
                $data_email = "
                <strong>$subject_email $data_for_email<strong><br>
                <ul>
                    <li><strong> Catagory - </strong> $type <br></li>
                    <li><strong> Item - </strong> $row_temp[item] <br></li>
                    <li><strong> Requested By - </strong>$uname<br></li>
                    <li><strong> Quantity - </strong> $row_temp[requested_quantity] $row_temp[unit] <br></li>
                    <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($row_temp['date_needed_by']))." <br></li>
                </ul>
                <br><br><br>
                ";
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = ""; $tag = "";
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                $email_id = $conn->insert_id;
                $stmt_email_page -> bind_param("si",$topage, $email_id);
                $stmt_email_page -> execute();
                $_SESSION["success"]="Item ".$status;
            }
            $date=date("Y-m-d H:i:s");
            $sql_rep = "UPDATE `report` SET `property_approval_date` = ? WHERE `request_id` = ?";
            $stmt_report = $conn->prepare($sql_rep);
            $stmt_report -> bind_param("si",$date, $request_id);
            $stmt_report -> execute();
            if((!($row_temp["department"] == 'Owner' && $row_temp['request_type'] == 'Fixed Assets') && $row_temp["customer"] != $row_temp["director"]) || $status != "Approved By Property")
            updaterequest($conn,$conn_fleet,$request_id,"one","","Property");
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_GET['share_comp']))
    {
        $request_id = $_GET['share_comp'];
        $stmt_po_by_request -> bind_param("i", $request_id);
        $stmt_po_by_request -> execute();
        $result_po_by_request = $stmt_po_by_request -> get_result();
        while($row = $result_po_by_request->fetch_assoc())
        {
            $property_comp = $row['property_company'];
            $sql_update_property_po = "UPDATE purchase_order SET `property_company` = ? WHERE `request_id` = ?";
            $stmt_update_property_po = $conn->prepare($sql_update_property_po);
            $stmt_update_property_po -> bind_param("si",$_GET['company_selector'], $row['request_id']);
            $stmt_update_property_po -> execute();
            $sql_update_property_request = "UPDATE requests SET `property_company` = ? WHERE `request_id` = ?";
            $stmt_update_property_request = $conn->prepare($sql_update_property_request);
            $stmt_update_property_request -> bind_param("si",$_GET['company_selector'], $row['request_id']);
            $stmt_update_property_request -> execute();
        }
        $record_type = "reqeusts";
        $record = "Property Company From $property_comp To ".$_GET['company_selector'];
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $request_id, $record);
        $stmt_add_record -> execute();
        $_SESSION['success'] = "Sent To ".$_GET['company_selector'];
        header("location: ".$_SERVER['HTTP_REFERER']);
    }



    //\\\\\\\\\\\\\\\\\Confirmation/////////////////////////\\
    $date=date("Y-m-d H:i:s");
    if(isset($_GET["confirm_handover"]) || isset($_GET["batch_confirm_handover"]))
    {
        $pids = (isset($_GET["confirm_handover"]))?[$_GET['confirm_handover']]:explode(",",$_GET["batch_confirm_handover"]);
        foreach($pids as $pid) {
            $stmt_po -> bind_param("i", $pid);
            $stmt_po -> execute();
            $result_po = $stmt_po -> get_result();
            $row = $result_po -> fetch_assoc();
            updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Store_confirm");
            $status = "In-Stock";
            $nxt = "Ready";
            $Settlement = "requested";
            $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?,`settlement`=? WHERE `purchase_order_id`=?");
            $stmt -> bind_param("ssi", $status, $Settlement, $pid);
            $stmt -> execute();
            $purchase_amount = 0;
            $sql_specific_price = "SELECT * FROM `price_information` where cluster_id = ? AND purchase_order_id = ? AND selected";
            $stmt_specific_price = $conn->prepare($sql_specific_price);
            $stmt_specific_price -> bind_param("ii", $row['cluster_id'], $pid);
            $stmt_specific_price -> execute();
            $result_specific_price = $stmt_specific_price -> get_result();
            if($result_specific_price -> num_rows > 0)
                while($row_purchase_info = $result_specific_price -> fetch_assoc())
                {
                    $purchase_amount += $row_purchase_info['quantity'];
                }
            /////email settlement
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $reason_close = "open_clust_".$row['cluster_id']."_dep_store";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $reason = "open_req_".$na_t."_".$row['request_id']."_settlment";
            $send_to = "";
            $dep = "Finance";
            $stmt_manager_active -> bind_param("ss", $dep, $row['finance_company']);
            $stmt_manager_active -> execute();
            $result_manager_active = $stmt_manager_active -> get_result();
            if($result_manager_active -> num_rows > 0)
                while($r_email = $result_manager_active -> fetch_assoc())
                {
                    $tag = $r_email['Username'];
                    $send_to .= ($send_to=="")?$r_email['email'].",".$r_email['Username']:",".$r_email['email'].",".$r_email['Username'];
                }
            $subject_email = "Settlement needed for complete purchase orders";
            $data_email = "
            <strong>Settlement needed for complete purchase orders</strong><br>
            <strong>Please settle as soon as possible</strong><br><br><br>
            ";
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; 
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();
    
            $email_id = $conn->insert_id;
            $page_to = "finance/settlement.php";
            $stmt_email_page -> bind_param("si",$page_to, $email_id);
            $stmt_email_page -> execute();
            
    
            $type = $row['request_type'];
            $stmt_unique = $conn->prepare("UPDATE `requests` SET `status`=?,`next_step`=?,`purchased_amount`=? WHERE `request_id`=?");
            $stmt_unique -> bind_param("ssii", $status, $nxt, $purchase_amount, $row['request_id']);
            $stmt_unique -> execute();
            
            $record_type = "reqeusts";
            $record = "Store Confirmed";
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
            $stmt_add_record -> execute();
    
            $stmt = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=?");
            $stmt -> bind_param("si", $status, $row['cluster_id']);
            $stmt -> execute();
            
            $stmt = $conn->prepare("UPDATE `report` SET `handover_comfirmed`=? WHERE `request_id`=? AND `type`=?");
            $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
            $stmt -> execute();
            $_SESSION["success"]=true;
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
    }

    if(isset($_GET["give"]) || isset($_GET["give_batch"]))
    {
        $batch = (isset($_GET["give_batch"]));
        $date=date("Y-m-d H:i:s");
        $vals = ($batch)?explode("_",$_GET["give_batch"]):explode("_",$_GET["give"]);
        $pid = $vals[0];
        $amount_recieved = $vals[1];
        $stmt_po -> bind_param("i", $pid);
        $stmt_po -> execute();
        $result_po = $stmt_po -> get_result();
        $row = $result_po -> fetch_assoc();
        $type = $row['request_type'];
        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Handover");

        $nxt = "Finished";
        $status = "All Complete";
        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `purchase_order_id`=?");
        $stmt -> bind_param("ssi", $status, $date, $pid);
        $stmt -> execute();

        $rec ="yes";
        $stmt_unique = $conn->prepare("UPDATE requests SET `status`=?,`next_step`=?,`recieved`=? WHERE `request_id`=?");
        $stmt_unique -> bind_param("sssi", $status, $nxt, $rec, $row['request_id']);
        $stmt_unique -> execute();

        $record_type = "reqeusts";
        $record = "Handover";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
        $stmt_add_record -> execute();

        $stmt = $conn->prepare("UPDATE `report` SET `final_recieved_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();
        $stmt_request -> bind_param("i", $row['request_id']);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        if($result_request->num_rows>0)
            while($row_temp = $result_request->fetch_assoc()) 
            {
                $item = $row_temp['item'];
                $cust = $row_temp['customer'];
                $dep = $row_temp['department'];
                $comp = $row_temp['company'];
                $date_requested = date("d-M-Y", strtotime($row_temp['date_requested']));
                $km = (isset($row_temp['current_km']))?$row_temp['current_km']:"";
                $request_for = (isset($row_temp['request_for']))?$row_temp['request_for']:"";
            }
                
        /////////////////////////////////////////////////////Can use Sms////////////////////////////////////////////////
        $serials = (isset($_GET['no_serial']))?[]:$_GET['serial'];
        $data = $_GET['data'];
        $temp_am = $amount_recieved;
        for($i=0;$i<sizeof($data);$i++)
        {
            // isset($serial[$i])
            $serial = (isset($_GET['no_serial']))?"No Serial":$serials[$i];
            if($batch)
            {
                if($temp_am>$_GET["per_batch"])
                {
                    $amount = $_GET["per_batch"];
                    $temp_am -=$_GET["per_batch"];
                }
                else
                    $amount = $temp_am;
            }
            else
                $amount = 1;
            $stmt = $conn->prepare("INSERT INTO `purchase history`
            (`request_id`, `type`, `request_for`, `item`, `amount`, `customer`, `department`, `Serial`, `data`, `date`, `kilometer`, `company`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("isssisssssss",$row['request_id'] , $type , $request_for , $item , $amount , $cust, $dep, $serial, $data[$i], $date, $km, $comp);
            $stmt -> execute();
        }
        if($type == "Tyre and Battery")
        {
            $date_purchased=date("Y-m-d H:i:s");
            for($i=0;$i<sizeof($data);$i++)
            {
                $serial = (isset($_GET['no_serial']))?"No Serial":$serials[$i];
                $sql = "INSERT INTO `history_jacket` (`vehicle`,`item`,`serial`,`date_purchased`,`kilometer`,`description`,`inserted_by`) VALUES (? ,? ,? ,? ,? ,? ,?)";
                $stmt_history_jacket = $conn->prepare($sql);
                $stmt_history_jacket -> bind_param("ssssiss", $request_for, $item, $serial, $date_purchased, $km, $data[$i], $_SESSION['username']);
                $stmt_history_jacket -> execute();
            }
        }
        $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$cust."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($email);
        $stmt2->fetch();
        $stmt2->close();
        $subject_email = "$item Purchase Request Requested on $date_requested is Ready";
        $data_email = "
        <strong>The $item purchase order you requested was bought and can be found at property department<strong><br><br>
        <strong>Please collect in a timly manner and verify once you do so<strong><br>
        ";
        $send_to = $email.",".$cust;
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $cc = ""; $bcc = "";
        $email_type = NULL;
        $sent_from = '';
        $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $cust ,$com_lo, $sent_from, $email_type);
        $stmt_email -> execute();
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if(isset($_GET["handover_instock"]) || isset($_GET["handover_instock_batch"]))//Not Started
    {
        $batch = (isset($_GET["handover_instock_batch"]));
        $date=date("Y-m-d H:i:s");
        $serial = $_GET['serial'];
        $data = $_GET['data'];
        $all_data = ($batch)?explode("_",$_GET["handover_instock_batch"]):explode("_",$_GET["handover_instock"]);
        $type = na_t_to_type($conn,$all_data[0]);
        $req_id = $all_data[1];
        $stat = $all_data[2];
        $stock_id = $all_data[3];
        $amount_recieved = $all_data[4];
        $status = "Complete";
        $sql_stock_request = "SELECT * FROM stock WHERE `request_id` = ?";
        $stmt_stock_request = $conn->prepare($sql_stock_request);
        $stmt_stock_request -> bind_param("i", $req_id);
        $stmt_stock_request -> execute();
        $result_stock_request = $stmt_stock_request -> get_result();
        $row_temp = $result_stock_request -> fetch_assoc();
        if($row_temp['for_purchase'] == 0)
        {
            $nxt = "Finished";
            $status = "All Complete";
            $rec ="yes";

            $stmt_unique = $conn->prepare("UPDATE requests SET `status`=?,`next_step`=?,`recieved`=? WHERE `request_id`=?");
            $stmt_unique -> bind_param("sssi", $status, $nxt, $rec, $req_id);
            $stmt_unique -> execute();
        
            $stmt = $conn->prepare("UPDATE `report` SET `final_recieved_date`=? WHERE `request_id`=?");
            $stmt -> bind_param("si", $date, $req_id);
            $stmt -> execute();
        }
        $stmt_unique = $conn->prepare("UPDATE `stock` SET `status`=? WHERE `id`=?");
        $stmt_unique -> bind_param("si", $status, $stock_id);
        $stmt_unique -> execute();
        $stmt = $conn->prepare("UPDATE `report` SET `final_instock_recieved_date`=? WHERE `request_id`=?");
        $stmt -> bind_param("si", $date, $req_id);
        $stmt -> execute();
        $sql_temp = "SELECT * FROM requests WHERE `request_id`='$req_id'";
        $stmt_request -> bind_param("i", $req_id);
        $stmt_request -> execute();
        $result_request = $stmt_request -> get_result();
        if($result_request -> num_rows > 0)
            while($row_temp = $result_request -> fetch_assoc()) 
            {
                $item = $row_temp['item'];
                $cust = $row_temp['customer'];
                $dep = $row_temp['department'];
                $comp = $row_temp['company'];
                $date_requested = date("d-M-Y", strtotime($row_temp['date_requested']));
                $km = (isset($row_temp['current_km']))?$row_temp['current_km']:"";
                $request_for = (isset($row_temp['request_for']))?$row_temp['request_for']:"";
            }
        $purchased = 0;
        $serials = (isset($_GET['no_serial']))?[]:$_GET['serial'];
        $data = $_GET['data'];
        $temp_am = $amount_recieved;
        for($i=0;$i<sizeof($data);$i++)
        {
            // isset($serial[$i])
            $serial = (isset($_GET['no_serial']))?"No Serial":$serials[$i];
            if($batch)
            {
                if($temp_am>$_GET["per_batch"])
                {
                    $amount = $_GET["per_batch"];
                    $temp_am -=$_GET["per_batch"];
                }
                else
                    $amount = $temp_am;
            }
            else
                $amount = 1;
            $stmt = $conn->prepare("INSERT INTO `purchase history`
            (`request_id`, `type`, `request_for`, `item`,`purchased`,`amount`, `customer`, `department`, `Serial`, `data`, `date`, `kilometer`, `company`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("isssiisssssss",$req_id , $type , $request_for , $item , $purchased, $amount, $cust, $dep, $serial, $data[$i], $date, $km, $comp);
            $stmt -> execute();
        }
        
        if($type == "Tyre and Battery")
        {
            $date_purchased=date("Y-m-d H:i:s");
            for($i=0;$i<sizeof($data);$i++)
            {
                $serial = (isset($_GET['no_serial']))?"No Serial":$serials[$i];
                $sql = "INSERT INTO `history_jacket` (`vehicle`,`item`,`serial`,`date_purchased`,`kilometer`,`inserted_by`) VALUES (? ,? ,? ,? ,? ,?)";
                $stmt_history_jacket = $conn->prepare($sql);
                $stmt_history_jacket -> bind_param("ssssis", $request_for, $item, $serial, $date_purchased, $km, $_SESSION['username']);
                $stmt_history_jacket -> execute();
            }
        }
        $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$cust."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($email);
        $stmt2->fetch();
        $stmt2->close();
        $subject_email = "$item Purchase request requested on $date_requested is ready";
        $data_email = "
        <strong>The $item purchase order you requested can be found at property department<strong><br><br>
        <strong>Please collect in a timly manner and verify once you do so<strong><br>
        ";
        $send_to = $email.",".$cust;
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $cc =""; $bcc = "";
        $email_type = NULL;
        $sent_from='';
        $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $cust ,$com_lo, $sent_from, $email_type);
        $stmt_email -> execute();
        $_SESSION["success"]=true;
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

 