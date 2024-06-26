<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
if(isset($_POST['submit_price'])){
    $stmt_request -> bind_param("i", $_POST['id']);
    $stmt_request -> execute();
    $result_request = $stmt_request -> get_result();
    if($result_request -> num_rows > 0)
    while($row2 = $result_request -> fetch_assoc())
    {
        $item = $row2['item'];
        $company = $row2['company'];
        $processing_company = $row2['processing_company'];
        $property_company = $row2['property_company'];
        $procurement_company = $row2['procurement_company'];
        $finance_company = $row2['finance_company'];
        $qty_full=$row2['requested_quantity'];

    }
    $date=date("Y-m-d H:i:s");
    $remarks_creator=$_POST['remark'];
    $item=$_POST['item'];
    $price=$_POST['price'];
    $qty=$_POST['quantity'];
    $total=$price*$qty;
    $providing_company=$_POST['providing_company'];
    $status='Generated';
    $type='agreement';
    $zero=0;
    $date_cs=date("Y-m-d H:i:s");
    $sql_add_cluster = "INSERT INTO `cluster` (`price`,`status`,`type`, `Remarks` , `company`, `processing_company`, `procurement_company`, `finance_company`, `cheque_company`, `compiled_by`, `Checked_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_add_cluster = $conn->prepare($sql_add_cluster);
    $stmt_add_cluster->bind_param("dssssssssss", $total, $status, $item, $remarks_creator, $company, $processing_company, $procurement_company, $finance_company, $finance_company, $_SESSION['username'], $_SESSION['username']);
    $res = $stmt_add_cluster->execute();
    if($res){
        $stmt2 = $conn->prepare("SELECT MAX(`id`) AS cluster_id FROM `cluster`");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($c_id);
        $stmt2->fetch();
        $stmt2->close();


        $file_name = array_filter($_FILES['performa']['name']);
        $total_count = count($_FILES['performa']['name']);
        $file_size=0;
        $error = "";
        $new_file = "";
        for( $j=0 ; $j < $total_count ; $j++ ) {
            $file_size = $file_size+$_FILES['performa']['size'][$j];
        }
        for( $i=0 ; $i < $total_count ; $i++ ) 
        {
            $file_tmp = $_FILES['performa']['tmp_name'][$i];
            if ($file_tmp != "")
            {
                $uniq = uniqid();
                $extention = ".".explode('.',$_FILES['performa']['name'][$i])[(sizeof(explode('.',$_FILES['performa']['name'][$i]))-1)];
                $newFilePath = "../../lpms_uploads/Performa-Batch-".$c_id."-".$uniq.$extention;
                if(move_uploaded_file($file_tmp, $newFilePath))
                {
                    $new_file .= ($new_file=="")?"":":_:";
                    $new_file.="Performa-Batch-".$c_id."-".$uniq.$extention;
                }
                else
                {
                    $error .= ($error=="")?"":"";
                    $error .= "'".$_FILES['performa']['name'][$i]."'";
                    echo $error;
                }
            }
        }
        $error .= ($error=="")?"":" not Uploaded Successfully";
        $error_count = count(explode(", ",$error));
        $_SESSION["success"]=($error == "")?"Uploaded Successfully":$error_count." files not Uploaded";
        $sql_add_performa = "INSERT INTO `performa` (`files`) VALUES (?)";
        $stmt_add_performa = $conn -> prepare($sql_add_performa);
        $stmt_add_performa -> bind_param("s", $new_file);
        $stmt_add_performa -> execute();
        // echo $conn -> error;
        $performa_id = $conn->insert_id;


        $sql_po_update = "UPDATE `purchase_order` set  `status`='Generated',`performa_id` = ?,`cluster_id` = ?,`assigned_by` = ? ,`timestamp` = ?,`priority` = '0' where request_id = ?";
        $stmt_po_update = $conn -> prepare($sql_po_update);
        $stmt_po_update -> bind_param("iissi", $performa_id, $c_id, $_SESSION['username'], $date, $_POST['id']);
        $stmt_po_update -> execute();
        $scale="not set";
        $sql_po_id = "SELECT `purchase_order_id` FROM `purchase_order` where request_id = ?";
        $stmt_po_id = $conn -> prepare($sql_po_id);
        $stmt_po_id -> bind_param("i", $_POST['id']);
        $stmt_po_id -> execute();
        $result_po_id = $stmt_po_id -> get_result();
        $purchase_order_row = $result_po_id -> fetch_assoc();
        $purchase_order_id = $purchase_order_row['purchase_order_id'];
        $sql_price_information = "SELECT * FROM `price_information` where `purchase_order_id` = ?";
        $stmt_price_information = $conn -> prepare($sql_price_information);
        $stmt_price_information -> bind_param("i", $purchase_order_id);
        $stmt_price_information -> execute();
        $result_price_information = $stmt_price_information -> get_result();
        $_vendor = $result_price_information -> fetch_assoc();
        $price_id=$_vendor['id'];
        $vendor_id=$_vendor['providing_company'];
        $stmt_vendor_specific -> bind_param("i", $vendor_id);
        $stmt_vendor_specific -> execute();
        $result_vendor_specific = $stmt_vendor_specific -> get_result();
        $vendor = $result_vendor_specific -> fetch_assoc();
        $vendor_name = $vendor['vendor'];
        $sql_price_information="UPDATE `price_information` set `providing_company` = ?, `cluster_id` = ?, `quantity` = ?, `price` = ?,`vat` = 0, `total_price` = ?, `after_vat` = ?, `specification` = NULL, `selected` = 1 where `purchase_order_id` = ? ";
        $stmt_update_price_information = $conn -> prepare($sql_price_information);
        $stmt_update_price_information -> bind_param("siddddi", $vendor_name, $c_id, $qty ,$price ,$total ,$total ,$purchase_order_id);
        $stmt_update_price_information -> execute();
        $sql_selections = "SELECT * FROM `selections` WHERE `user` = ? AND `cluster_id` = ?";
        $stmt_selections = $conn -> prepare($sql_selections);
        $stmt_selections -> bind_param("si", $_SESSION['username'], $c_id);
        $stmt_selections -> execute();
        $result_selections = $stmt_selections -> get_result();
        if($result_selections -> num_rows > 0)
        {
            $sql_update_selections = "UPDATE `selections` SET selection = ? WHERE `user` = ? AND `cluster_id` = ?";
            $stmt_update_selections = $conn -> prepare($sql_update_selections);
            $stmt_update_selections -> bind_param("ssi", $selections, $_SESSION['username'], $c_id);
            $stmt_update_selections -> execute();
        }
        else
        {
            $sql_add_selections = "INSERT into `selections` (`user`, `cluster_id`, `selection`) VALUES (?,?,?)";
            $stmt_add_selections = $conn -> prepare($sql_add_selections);
            $stmt_add_selections -> bind_param("sis", $_SESSION['username'], $c_id, $price_id);
            $stmt_add_selections -> execute();
        }

        $stmt = $conn->prepare("INSERT INTO `stock`(`request_id`, `type`, `check_by`, `requested_quantity`, `in-stock`, `for_purchase`, `average_price`, `total_price`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt -> bind_param("issiiiii",$_POST['id'] ,$type, $_SESSION["username"], $qty_full,$zero, $qty,$zero,$zero);
        $stmt -> execute();
        $last_id = $conn->insert_id;
        $sql_update_request_stock = "UPDATE requests SET `status` = 'Committee Approval', `next_step`='HO Committee', `stock_info` = ? WHERE `request_id` = ?";
        $stmt_update_request_stock = $conn -> prepare($sql_update_request_stock);
        $stmt_update_request_stock -> bind_param("ii", $last_id, $_POST['id']);
        $stmt_update_request_stock -> execute();

        $sql_report_comparison = "UPDATE `report` set `compsheet_generated_date` = ? where `request_id` = ?";
        $stmt_report_comparison = $conn -> prepare($sql_report_comparison);
        $stmt_report_comparison -> bind_param("si", $date_cs, $_POST['id']);
        $stmt_report_comparison -> execute();
    }
    $_SESSION['success'] = "Price set successfully";
    header("location: ".$_SERVER['HTTP_REFERER']);
}

if(isset($_SESSION['username']))
{
    if(isset($_GET['vendor_update_id']))
    {
        $sql_vendor_status = "UPDATE `prefered_vendors` SET `status` = ? WHERE id = ?";
        $stmt_vendor_status = $conn -> prepare($sql_vendor_status);
        $stmt_vendor_status -> bind_param("si", $_GET['status'], $_GET['vendor_update_id']);
        $result = $stmt_vendor_status -> execute();
        if($result)
            echo " ";
        else 
            echo 0;
        // header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['vendor_limit'])){
        $offset=$_GET['vendor_limit'];
        $sql_vendors_loadmore = "SELECT * FROM prefered_vendors order by id desc limit $offset, 20";
        $stmt_vendors_loadmore = $conn -> prepare($sql_vendors_loadmore);
        $stmt_vendors_loadmore -> execute();
        $result_vendors_loadmore = $stmt_vendors_loadmore -> get_result();
        $str="";
        if($result_vendors_loadmore -> num_rows>0)
            while($row = $result_vendors_loadmore -> fetch_assoc())
            { 
                $phoneLinks="";
                $email="";
                $numbers=explode(',',$row['phone']);
              foreach($numbers as $number)
              $phoneLinks.="<a href='tel:+251$number' target='_blank'><i class='bi bi-telephone text-primary me-1'></i></a>"; 
              $numbers=explode(',',$row['phone']);
              if(strpos(explode(',',$row['email'])[0],'@')){
                $email="<a href='mailto:".explode(',',$row['email'])[0]."' target='_blank'><i  class='text-danger fas fa-envelope'></i></a>";
              }
              $checked=$row['status']==1?"checked":"";
              $printpage = "
              <span  class='float-end'>
                  <button type='submit' class='btn btn-outline-secondary border-0' data-bs-toggle='modal'  onclick='edit_vendor(this)' value='".$row['id']."' data-bs-target='#edit_vendor'>
                      <i class='text-primary fas fa-edit'></i>
                  </button>
              </span>
              <span class='form-check form-switch float-end'>
              <input class='form-check-input' type='checkbox' $checked  role='switch' name='".$row['id']."' onclick='update_status(this)' id='flexSwitchCheckDefault'>
            </span>
              ";
              $str.= "
                <div class='col-md-6 col-lg-3 my-4 fade-up-right'>
                    <div class='box'>
                    $printpage
                        <h3 class='text-capitalize'>
                        <span class='small text-secondary d-block mt-2'>".$row['vendor']."</span>
                        <span class='small text-secondary d-block mt-2'>".$row['business_type']."</span>                             
                        </h3>
                        <ul>
                            <li><b>contact person</b>: ".$row['contact']."</li>
                            <li><b>Position</b> : ".$row['position']."</li>
                            <li><b>Items Category</b> : ".$row['catagory']."</li>
                            <li><b>Address</b> : ".$row['address']."</li>
                            <li>$phoneLinks<a href='https://t.me/share/url?url=details' target='_blank'><i class='bi bi-telegram text-primary me-1'></i></a>
                         <a href='https://api.whatsapp.com/send?text=details' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>$email
                       </li>
                        </ul>                                
                    </div>
                </div>
                ";
              }
       
// </form>
// </div>
// </div>
 
        echo $str;
            }
if(isset($_POST['fileuploader_css'])){
    $file=$_FILES['vendor_csv']['name'];
    $filename=$_FILES['vendor_csv']['tmp_name'];
    $path_parts = pathinfo($file);
 if(strtolower($path_parts['extension'])=="csv"){
    $row = 0;
    $result=false;
    if($_FILES["vendor_csv"]["size"] > 0)
		 {
            $file = fopen($filename, "r");
            $x=0;
            while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
            {	
                if((++$x)!=1 && $emapData[3]!="")
                {
                    $sql_add_prefered_vendors = "INSERT INTO `prefered_vendors`(`catagory`, `vendor`, `business_type`,`contact`,`position`, `address`, `items`, `details`, `email`, `phone`) VALUES (? ,? ,? ,? ,? ,? ,? ,? ,? ,?)";
                    $stmt_add_prefered_vendors = $conn->prepare($sql_add_prefered_vendors);
                    $stmt_add_prefered_vendors->bind_param("ssssssssss", $emapData[8], $emapData[1], $emapData[7], $emapData[5], $emapData[6], $emapData[2], $emapData[0], $emapData[0], $emapData[4], $emapData[3]);
                    $result = $stmt_add_prefered_vendors->execute();
                }
            }
            $x-=3;
         }
        if($result)
            $_SESSION["done"]="$x Vandors added from excel sheet";
        else 
            $_SESSION['error']="Error occurred while adding records from excel sheet";
 }
 else{
    $_SESSION['error']="File format should always be CSV";
 }   
         header("location: ".$_SERVER['HTTP_REFERER']);
}
    if(isset($_POST['submit_feedback']))
    {
        // Director & Owner
        $feedback = str_replace("'","'",$_POST['feedback']);
        $sql_add_feedback="INSERT INTO `feedback` (`user`,`rating`,`feedback`) VALUES (? ,? ,?)";
        $stmt_add_feedback = $conn->prepare($sql_add_feedback);
        $stmt_add_feedback->bind_param("sds", $_SESSION['username'], $_POST['rating'], $feedback);
        $result = $stmt_add_feedback->execute();
        $_SESSION['success'] = "We Approciate your feedback";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_POST['submit_issue']))
    {
        $issue = str_replace("'","'",$_POST['issue']);
        $table = "requests";
        $system = isset($_POST['project-Name'])?",'".$_POST['project-Name']."'":"";
        $system_add = isset($_POST['project-Name'])?",`system`":"";
        if(!isset($_POST['project-Name']))
        {
            $id = isset($_POST['issue_for'])?$_POST['issue_for']:$_POST['submit_issue'];
            $table = explode("_",$id)[0];
            $id = explode("_",$id)[1];
        }
        else $id = 0;
        $stat = "Open";
        $closeDate = $close_timestamp = "";
        $sql_next_id = "SELECT MAX(id) + 1 AS next_id FROM issues";
        $stmt_next_id = $conn -> prepare($sql_next_id);
        $stmt_next_id -> execute();
        $result_next_id = $stmt_next_id -> get_result();
        $id_next = 0;
        if($result_next_id -> num_rows>0)
        {
            $row_next_id = $result_next_id -> fetch_assoc();
            $id_next = $row_next_id['next_id'];
        }
        $date=date("Y-m-d H:i:s");
        $file_name = array_filter($_FILES['supporting_documents']['name']);
        $total_count = count($_FILES['supporting_documents']['name']);
        $file_size=0;
        $error = "";
        $new_file = "";
        for( $j=0 ; $j < $total_count ; $j++ ) {
            $file_size = $file_size+$_FILES['supporting_documents']['size'][$j];
        }
        for( $i=0 ; $i < $total_count ; $i++ ) 
        {
            $file_tmp = $_FILES['supporting_documents']['tmp_name'][$i];
            if ($file_tmp != "")
            {
                $uniq = uniqid();
                $extention = ".".explode('.',$_FILES['supporting_documents']['name'][$i])[(sizeof(explode('.',$_FILES['supporting_documents']['name'][$i]))-1)];
                $newFilePath = "../../lpms_uploads/issue-".$id_next."-".$uniq.$extention;
                if(move_uploaded_file($file_tmp, $newFilePath))
                {
                    $new_file .= ($new_file=="")?"":":_:";
                    $new_file.="issue-".$id_next."-".$uniq.$extention;
                }
                else
                {
                    $error .= ($error=="")?"":"";
                    $error .= "'".$_FILES['supporting_documents']['name'][$i]."'";
                }
            }
        }
        if($new_file != "")
        {
            $close_timestamp .= ",`supporting_documents`";
            $closeDate = ",'".$new_file."'";
        }
        if(!isset($_POST['open']))
        {
            $stat = "Closed";
            $closeDate .= ",'".date('Y-m-d H:i:s')."'";
            $close_timestamp .= ",`close_timestamp`";
        }
        if(isset($_POST['reason']))
        {
            $thread = str_replace("'","'",$_POST['reason']);
            $close_timestamp .= ",`thread`";
            $closeDate .= ",'".$thread."'";
            $stat = "Closed";
        }
        $sql = "INSERT INTO `issues`(`title_id`, `user`, `type`, `issue`, `status`, `company`$system_add$close_timestamp) VALUES (? ,? ,? ,? ,? ,? $system$closeDate)";
        $stmt_add_issues = $conn -> prepare($sql);
        $stmt_add_issues -> bind_param("isssss", $id, $_SESSION['username'], $table, $issue, $stat, $_SESSION['company']);
        $result = $stmt_add_issues -> execute();
        $issue_id = (isset($thread))?intval($thread):$conn->insert_id;
        $mentions = [];
        $latest = $issue;
        while(strpos($latest,"@") !== false)
        {
          $latest = substr($latest,strpos($latest,"@")+1);
          $endpoint = (strpos($latest," ") === false)?strpos($latest,"<"):strpos($latest," ");
          $mention = substr($latest,0,$endpoint);
          if(!in_array($mention, $mentions))
          {
            array_push($mentions,$mention);
            $record_type = 'issues';
            $lowername = strtolower($mention);
            $record = "Issue-Mention:$lowername";
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $issue_id, $record);
            $stmt_add_record -> execute();
            notify_mention($lowername);
          }
        }
        unset($_SESSION['last_issue']);
        $_SESSION['success'] = "Issue Submitted";
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $cc ="Dagem.Adugna"; $bcc = ""; $tag = "";
        $subject_email = "Issue Raised By ".$_SESSION['username']." for ".(isset($_POST['project-Name'])?$_POST['project-Name']:"LPMS");
        $data_email = 
        "<strong>
            Please Check and fix the following issue raised:
            <br>
                $issue
            <br>
                Raised By ".$_SESSION['username']."
        </strong><br>";
        $reason = "closed";
        $send_to = "softwareengineers@hagbes.com,Software Team";
        $user=($_SESSION['username'].":-:".$_SESSION['position']);
        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
        $stmt_email_reason -> execute();
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_GET['pullOut']))
    {
        $requests = $_GET['pullOut'];
        $cluster = $_GET['cluster'];
        $purchaseOrders = $_GET['purchaseOrders'];
        $Date = date('Y-m-d H:i:s');

        $stmt_cluster -> bind_param("i", $cluster);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster -> get_result();
        $row = $result_cluster -> fetch_assoc();
        $price = $row['price'];

        $sql="INSERT INTO cluster (`type`, `status`, `price`, `Remarks`, `company`, `processing_company`, `procurement_company`, `finance_company`, `cheque_company`, `compiled_by`, `Checked_by`, `Finance_approved`, `cashier`, `cheque_signatories`, `cheque_percent`) SELECT `type`, `status`, `price`, `Remarks`, `company`, `processing_company`, `procurement_company`, `finance_company`, `cheque_company`, `compiled_by`, `Checked_by`, `Finance_approved`, `cashier`, `cheque_signatories`, `cheque_percent` FROM cluster where id = '$cluster'";
        $stmt_replicateCluster = $conn -> prepare($sql);
        $result = $stmt_replicateCluster -> execute();
        $newCluster = $conn->insert_id;

        $sql="INSERT INTO committee_approval (`committee_member`, `status`, `remark`, `cluster_id`, `timestamp`) SELECT `committee_member`, `status`, `remark`, $newCluster AS `cluster_id`, `timestamp` FROM committee_approval where cluster_id = '$cluster'";
        $stmt_replicateApproval = $conn -> prepare($sql);
        $result = $stmt_replicateApproval -> execute();
        
        $sql = "SELECT * FROM price_information WHERE `cluster_id` = ? and selected";
        $stmt_prices = $conn->prepare($sql);
        $stmt_prices -> bind_param("i", $cluster);
        $stmt_prices -> execute();
        $result_prices = $stmt_prices -> get_result();
        $newPrice = 0;
        if($result_prices->num_rows>0)
        {
            $sql = "SELECT SUM(after_vat) as total FROM price_information WHERE `cluster_id` = ? and selected and purchase_order_id in ($purchaseOrders) group by purchase_order_id";
            // echo $sql."<br>";
            $stmt_prices = $conn->prepare($sql);
            $stmt_prices -> bind_param("i", $cluster);
            $stmt_prices -> execute();
            $result_prices = $stmt_prices -> get_result();
            if($result_prices->num_rows>0)
                while($row_prices = $result_prices -> fetch_assoc())
                {
                    $newPrice += $row_prices['total'];
                }
        }
        else
        {
            $sql = "SELECT SUM(D.avgPrice) as total FROM (SELECT AVG(after_vat) as avgPrice from price_information WHERE purchase_order_id in ($purchaseOrders) and cluster_id = ? GROUP BY purchase_order_id) as D";
            $stmt_prices = $conn->prepare($sql);
            $stmt_prices -> bind_param("i", $cluster);
            $stmt_prices -> execute();
            $result_prices = $stmt_prices -> get_result();
            if($result_prices->num_rows>0)
            {
                while($row_prices = $result_prices -> fetch_assoc())
                {
                    $newPrice = $row_prices['total'];
                }
            }
        }
        $oldPrice = $price - $newPrice;
        $sql="UPDATE `cluster` SET `price`=? WHERE `id` = ?";
        $stmt_update_old_price = $conn -> prepare($sql);
        $stmt_update_old_price -> bind_param("di", $oldPrice,$cluster);
        $result = $stmt_update_old_price -> execute();

        $sql="UPDATE `cluster` SET `price`=? WHERE `id` = ?";
        $stmt_update_new_price = $conn -> prepare($sql);
        $stmt_update_new_price -> bind_param("di", $newPrice,$newCluster);
        $result = $stmt_update_new_price -> execute();

        $sql="UPDATE `price_information` SET `cluster_id` = ? WHERE purchase_order_id in ($purchaseOrders) and cluster_id = ?";
        $stmt_update_priceInfo = $conn -> prepare($sql);
        $stmt_update_priceInfo -> bind_param("ii", $newCluster, $cluster);
        $result = $stmt_update_priceInfo -> execute();

        $sql="UPDATE `purchase_order` SET `cluster_id` = ? WHERE purchase_order_id in ($purchaseOrders) and cluster_id = ?";
        $stmt_update_priceInfo = $conn -> prepare($sql);
        $stmt_update_priceInfo -> bind_param("ii", $newCluster, $cluster);
        $result = $stmt_update_priceInfo -> execute();
        echo '{"cluster_id":"'.$newCluster.'"}';
        // $_SESSION['success'] = "Request Separated";
        // header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_POST['close_issue']))
    {
        $closeDate = date('Y-m-d H:i:s');
        $sql="UPDATE `issues` SET `status`='Closed', closed_by = ?, close_timestamp = ? WHERE `id` = ? Or `thread` = ?";
        $stmt_update_issue = $conn -> prepare($sql);
        $stmt_update_issue -> bind_param("ssii", $_SESSION['username'], $closeDate, $_POST['close_issue'], $_POST['close_issue']);
        $result = $stmt_update_issue -> execute();
        $_SESSION['success'] = "Issue Closed";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_POST['edit_vendor']))
    {
        $name=$_POST['vendor'];
        $categories=implode(",",$_POST['category']);
        $items=implode(",",$_POST['item']);
        $contact=$_POST['contact'];
        $position=$_POST['position'];
        $address=$_POST['address'];
        $type=$_POST['business_type'];
        $detail=$_POST['Vendor_Details'];
        $phone=implode(",",$_POST['phones']);
        $email=implode(",",$_POST['emails']);
        $id=$_POST['vendor_id'];
        $sql="UPDATE `prefered_vendors` SET `id` = ?,`catagory` = ?,`vendor` = ?,`business_type` = ?,`contact` = ?,`position` = ?,`address` = ?,`items` = ?,`details` = ?,`email` = ?,`phone` = ? where id = ?";
        $stmt_update_prefered_vendors = $conn->prepare($sql);
        $stmt_update_prefered_vendors->bind_param("issssssssssi", $id, $categories, $name, $type, $contact, $position, $address, $items, $detail, $email, $phone, $id);
        $result = $stmt_update_prefered_vendors->execute();
        if($result)
        {
            $_SESSION["success"]="Vandor Updated";
        }
        else
        {
            $_SESSION['error']="Error Occured";
        }
         header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['close_req']) || isset($_GET['close_pur_req']) || isset($_GET['close_req_clus']))
    {
        $requests = [];
        if(isset($_GET['close_pur_req']))
        {
            $forbiden_stats = ['canceled','Rejected','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            $sql = "SELECT * FROM requests WHERE `purchase_requisition` = ?";
            $stmt_request_pr = $conn->prepare($sql);
            $stmt_request_pr -> bind_param("i", $_GET['close_pur_req']);
            $stmt_request_pr -> execute();
            $result_request_pr = $stmt_request_pr -> get_result();
            while($row = $result_request_pr -> fetch_assoc())
            {
                $change = true;
                foreach($forbiden_stats as $s)
                    if(strpos($row['status'],$s)!==false || $row['status'] == $s)
                        $change = false;
                if($change)
                    array_push($requests,$row['request_id']);
            }
        }
        else if(isset($_GET['close_req_clus']))
        {
            $forbiden_stats = ['canceled','Rejected','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            $sql = "SELECT *,R.status as `status`,R.request_id as `request_id` FROM purchase_order P inner join requests R on P.request_id = R.request_id WHERE `cluster_id` = ?";
            $stmt_cluster_fetch = $conn->prepare($sql);
            $stmt_cluster_fetch -> bind_param("i", $_GET['close_req_clus']);
            $stmt_cluster_fetch -> execute();
            $result_cluster_fetch = $stmt_cluster_fetch -> get_result();
            while($row = $result_cluster_fetch -> fetch_assoc())
            {
                $change = true;
                foreach($forbiden_stats as $s)
                    if(strpos($row['status'],$s)!==false || $row['status'] == $s)
                        $change = false;
                if($change)
                    array_push($requests,$row['request_id']);
            }
        }
        else
        {
            array_push($requests,$_GET['close_req']);
        }
        $data_email = "<strong>The following items : <br></strong><ul>";
        $i=0;
        foreach($requests as $request)
        {
            $i++;
            $close = "Closed Because - ".$_GET['reason'];
            $level = $_SESSION['department'];
            $stmt_remark -> bind_param("ssss", $request, $close,$_SESSION['username'],$level);
            $stmt_remark -> execute();
            $record_type = 'requests';
            $record = 'Canceled';
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $request, $record);
            $stmt_add_record -> execute();
    
            $sql_close_requests = "UPDATE requests SET `status`='canceled',`next_step`='canceled' WHERE `request_id`= ?";
            $stmt_close_requests = $conn->prepare($sql_close_requests);
            $stmt_close_requests -> bind_param("i", $request);
            $stmt_close_requests -> execute();
            
            $stmt_po_by_request -> bind_param("i", $request);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if($result_po_by_request->num_rows>0)
            {
                $row = $result_po_by_request->fetch_assoc();
                $cluster = $row['cluster_id'];
                $sql_close_purchase_order = "UPDATE purchase_order SET `status`='canceled' WHERE `request_id` = ?";
                $stmt_close_purchase_order = $conn->prepare($sql_close_purchase_order);
                $stmt_close_purchase_order -> bind_param("i", $request);
                $stmt_close_purchase_order -> execute();
                if(!is_null($cluster))
                {
                    $stmt_po_cluster_active -> bind_param("i", $cluster);
                    $stmt_po_cluster_active -> execute();
                    $result_po_by_request = $stmt_po_cluster_active -> get_result();
                    if($result_po_by_request->num_rows == 0)
                    {
                        $sql_close_cluster = "UPDATE cluster SET `status`='canceled' WHERE `id` = ?";
                        $stmt_close_cluster = $conn->prepare($sql_close_cluster);
                        $stmt_close_cluster -> bind_param("i", $cluster);
                        $stmt_close_cluster -> execute();
                    }
                }
            }
            $stmt_request -> bind_param("i", $request);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row = $result_request -> fetch_assoc();
    
            $na_t=str_replace(" ","",$row['request_type']);
            $reason_close = "%".$na_t."_".$request."_%";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $data_email .= "<li><strong> Item $i - </strong> $row[item] <br></li>";
        }
        $data_email .= "</ul><strong>That was requested on $row[date_requested] was canceled by procurement department ($_SESSION[username])</strong><br><br><br>";
        $sql2 = "SELECT * FROM `account` where `Username` = ? OR ((role = 'manager' OR `type` LIke '%manager%') AND company = ? AND department = ?) and `status` = 'active'";
        $stmt_usermngr_account = $conn->prepare($sql2);
        $stmt_usermngr_account -> bind_param("sss", $row['customer'], $row['company'], $row['department']);
        $stmt_usermngr_account -> execute();
        $result_usermngr_account = $stmt_usermngr_account -> get_result();
        if($result_usermngr_account -> num_rows>0)
        while($row2 = $result_usermngr_account -> fetch_assoc())
        {
            $send_to =$row2['email'].",".$row2['Username'];
            $cc =""; $bcc = "";
            $subject_email = "The following Item requests was canceled";
            $tag = $row2['Username'];
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $email_type = NULL;
            $sent_from='';
            $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag ,$com_lo, $sent_from, $email_type);
            $stmt_email -> execute();
        }
        $_SESSION["success"]="Request Closed";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['btntype']) || isset($_GET['batch_approve']) || isset($_GET['batch_reject']))
    {
        if(isset($_GET['batch_approve']) || isset($_GET['batch_reject']))
        {
            $all_req = (isset($_GET['batch_approve']))?explode(",",$_GET['batch_approve']):explode(",",$_GET['batch_reject']);
        }
        else
            $all_req = [explode("_",$_GET['btntype'])[2]];

        if(isset($_GET['btntype']))
            $app =(strpos($_GET['btntype'],"approve") !==false)?true:false;
        else
            $app =(isset($_GET['batch_approve']))?true:false;
        foreach($all_req as $req_id)
        {
            $date_app=date("Y-m-d H:i:s");
            $approved_by = $_SESSION['username'];

            if(isset($_GET['reason']) && $_GET['reason'] != "")
            {
                $level = "Manager";
                $stmt_remark -> bind_param("ssss", $req_id, $_GET['reason'],$_SESSION['username'],$level);
                $stmt_remark -> execute();
            }
            $stmt_request -> bind_param("i", $req_id);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows > 0)
                while($row_temp = $result_request -> fetch_assoc())
                {
                    $type = $row_temp['request_type'];
                    $na_t = str_replace(" ","",$type);
                    $requested_by = $row_temp['customer'];
                    $item = $row_temp['item'];
                    $depp = $row_temp['department'];
                    $compa = $row_temp['company'];
                    $req_quan = $row_temp['requested_quantity'];
                    $unit = $row_temp['unit'];
                    $specification = ($row_temp['spec_dep'] == 'IT' && is_null($row_temp['specification']));
                    $date_n_b = $row_temp['date_needed_by'];
                    $property_company=$row_temp['property_company'];
                }
            if($app)
            {
                if($_SESSION['role'] == 'GM' || ($_SESSION['role'] == 'Director' && $type=='agreement'))
                {
                    $tag = "";
                    $status = "Approved By GM";
                    $status=($type=='agreement')?'directors':$status;
                    $email_stat = "Approved";
                    $nxt=($type=='agreement')?"procurement":"Store";
                    $nxt= ($specification)?"IT Specification":$nxt;
                    $send_to = "";
                    if($type=='agreement')
                    {
                        if($_SESSION['role'] == 'Director' && $type=='agreement')
                            updaterequest($conn,$conn_fleet,$req_id,"one","","Agreement");
                        $data_for_email = "<strong>There is an agreement purchase request in $depp department in $compa waiting for yout approval please review in a timely manner<strong><br>";
                        $subject_email = "There is a purchase request in $depp department waiting for Director Approval";
                        $reason = "open_req_".$na_t."_".$req_id."_directors";
                        $sql_email = "SELECT * FROM `account` where `role` = 'Director' AND company = 'Hagbes HQ.' and `status`='active'";
                        $stmt_director = $conn->prepare($sql_email);
                        $stmt_director -> execute();
                        $result_director = $stmt_director -> get_result();
                        if($result_director -> num_rows>0)
                            while($row_email = $result_director -> fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                // $tag = $email_to;
                                $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                            }
                    }
                    else
                    {
                        if($specification)
                        {
                            $date_app=date("Y-m-d H:i:s");
                            $sql_rep = "UPDATE `report` SET `sent_for_spec` = ? WHERE `request_id` = ?";
                            $stmt_report_spec = $conn -> prepare($sql_rep);
                            $stmt_report_spec -> bind_param("si", $date_app, $req_id);
                            $result = $stmt_report_spec -> execute();

                            $stmt_company -> bind_param("s", $_SESSION['company']);
                            $stmt_company -> execute();
                            $result_company = $stmt_company -> get_result();
                            $row_comp = $result_company -> fetch_assoc();
                            $IT_company = ($row_comp['IT'])?$row_comp['Name']:"Hagbes HQ.";

                            $data_for_email = "<strong>There is a purchase request in $depp department waiting for IT Specification</strong><br>
                            <strong>Please enter a valid specification fitting the item </strong><br>";
                            $subject_email = "There is a purchase request In $depp department waiting for IT Specification";
                            $reason = "open_req_".$na_t."_".$req_id."_specification";
                            $sql_email = "SELECT * FROM `account` where `department` = 'IT' AND company = ? and (role = 'manager' OR `type` LIke '%manager%') and status='active'";
                            $stmt_email_accounts = $conn -> prepare($sql_email);
                            $stmt_email_accounts -> bind_param("s", $IT_company);
                        }
                        else
                        {
                            $data_for_email = "<strong>$item purchase request In $depp department waiting for store check please review in a timely manner<strong><br>";
                            $subject_email = "$item purchase request In $depp department waiting for store check";
                            $reason = "open_req_".$na_t."_".$req_id."_store";
                            $sql_email = "SELECT * FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = ? and status='active'";
                            $stmt_email_accounts = $conn -> prepare($sql_email);
                            $stmt_email_accounts -> bind_param("s", $property_company);
                        }
                        $stmt_email_accounts -> execute();
                        $result_email_accounts = $stmt_email_accounts -> get_result();
                        if($result_email_accounts->num_rows>0)
                            while($row_email = $result_email_accounts->fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                            }
                    }
                }
                else
                {
                    // $status = "Approved By ".((in_array($depp,$_SESSION['GMs']) || in_array("All",$_SESSION['GMs']))?"Dep.Manager":"GM");
                    $status = "Approved By Dep.Manager";
                    $email_stat = "Approved";
                    // $sql = "SELECT * FROM "
                    $nxt = (in_array($depp,$_SESSION['GMs']) || in_array("All",$_SESSION['GMs']))?"GM":"Store";
                    $nxt= ($specification && !in_array($depp,$_SESSION['GMs']) && !in_array("All",$_SESSION['GMs']))?"IT Specification":$nxt;

                    $send_to = "";
                    $tag = "";
                    if(in_array($depp,$_SESSION['GMs']) || in_array("All",$_SESSION['GMs']))
                    {
                        $like_department = "%$depp%";
                        $sql_email = "SELECT * FROM `account` where (`managing` LIKE ? OR `managing` LIKE '%All Departments%') and role = 'GM' AND company = ? and status='active'";
                        $stmt_email_gm = $conn -> prepare($sql_email);
                        $stmt_email_gm -> bind_param("ss", $like_department, $_SESSION['company']);
                        $stmt_email_gm -> execute();
                        $result_email_gm = $stmt_email_gm -> get_result();
                        if($result_email_gm->num_rows>0)
                            while($row_email = $result_email_gm->fetch_assoc())
                            {
                                $phone_number = $row_email['phone'];
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $tag = $email_to;
                                $send_to .=($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                                $sms_to = $email_to; $sms_from = $_SESSION['username'];
                                $msg = "A purchase order was requested in $depp and waiting $nxt approval please Visit lpms.hagbes.com";
                                // include "../common/sms.php";
                            }
                        $data_for_email = "<strong>There is a purchase request in $depp department waiting for $nxt approval please review in a timely manner<strong><br>";
                        $subject_email = "There is a purchase request in $depp department waiting for $nxt approval";
                        $reason = "open_req_".$na_t."_".$req_id."_$nxt"."_approve";
                    }
                    else
                    {
                        if($specification)
                        {
                            $date_app=date("Y-m-d H:i:s");
                            $sql_rep = "UPDATE `report` SET `sent_for_spec` = ? WHERE `request_id` = ?";
                            $stmt_report_spec = $conn -> prepare($sql_rep);
                            $stmt_report_spec -> bind_param("si", $date_app, $req_id);
                            $result = $stmt_report_spec -> execute();

                            $stmt_company -> bind_param("s", $_SESSION['company']);
                            $stmt_company -> execute();
                            $result_company = $stmt_company -> get_result();
                            $row_comp = $result_company -> fetch_assoc();
                            $IT_company = ($row_comp['IT'])?$row_comp['Name']:"Hagbes HQ.";

                            $data_for_email = "<strong>There is a purchase request in $depp department waiting for IT Specification</strong><br>
                            <strong>Please enter a valid specification fitting the item </strong><br>";
                            $subject_email = "There is a purchase request In $depp department waiting for IT Specification";
                            $reason = "open_req_".$na_t."_".$req_id."_specification";
                            $sql_email = "SELECT * FROM `account` where `department` = 'IT' AND company = ? and (role = 'manager' OR `type` LIke '%manager%') and status='active'";
                            $stmt_email_accounts = $conn -> prepare($sql_email);
                            $stmt_email_accounts -> bind_param("s", $IT_company);
                        }
                        else
                        {
                            $data_for_email = "<strong>$item purchase request In $depp department waiting for store check please review in a timely manner<strong><br>";
                            $subject_email = "$item purchase request In $depp department waiting for store check";
                            $reason = "open_req_".$na_t."_".$req_id."_store";
                            $sql_email = "SELECT * FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = ? and status='active'";
                            $stmt_email_accounts = $conn -> prepare($sql_email);
                            $stmt_email_accounts -> bind_param("s", $property_company);
                        }
                        $stmt_email_accounts -> execute();
                        $result_email_accounts = $stmt_email_accounts -> get_result();
                        if($result_email_accounts -> num_rows > 0)
                            while($row_email = $result_email_accounts -> fetch_assoc())
                            {
                                $email = $row_email['email'];
                                $email_to = $row_email['Username'];
                                $send_to .=($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                                $sms_to = $email_to; $sms_from = $_SESSION['username'];
                            }
                    }
                }
            }
            else
            {
                $email_to = $requested_by;
                $status = "Rejected By Dep.Manager";
                $email_stat = "Rejected";
                $nxt = "Manager Rejected";
                $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$requested_by."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email);
                $stmt2->fetch();
                $stmt2->close();
                $send_to = $email.",".$requested_by;
                
                $subject_email = "$item purchase request in ".$_SESSION['department']." Department";
                $data_for_email = "<strong>Your $type Purchase Request for $item Was $email_stat by $_SESSION[username] </strong><br>";
                $reason = "closed";
                $tag = $requested_by;
            }
            
            if($nxt!='procurement' && $nxt!='directors')
            {
                $sql2 = "UPDATE requests SET `status` = ?,`manager` = ?,`next_step` = ? WHERE `request_id` = ?";
                $stmt_status_and_manager = $conn -> prepare($sql2);
                $stmt_status_and_manager -> bind_param("sssi", $status, $approved_by, $nxt, $req_id);
                $stmt_status_and_manager -> execute();
                $sql_rep = "UPDATE `report` SET `manager_approval_date` = ? WHERE `request_id` = ?";
                $stmt_report_manager = $conn -> prepare($sql_rep);
                $stmt_report_manager -> bind_param("si", $date_app, $req_id);
                $stmt_report_manager -> execute();
            }
            if($_SESSION['role'] == 'Director' && $nxt!='procurement')
            {
                $sql_director = "UPDATE requests SET director = ? WHERE `request_id` = ?";
                $stmt_director = $conn -> prepare($sql_director);
                $stmt_director -> bind_param("si", $_SESSION['username'], $req_id);
                $stmt_director -> execute();

                $sql_rep = "UPDATE `report` SET `GM_approval_date`=? WHERE `request_id`=?";
                $stmt_report_gm = $conn -> prepare($sql_rep);
                $stmt_report_gm -> bind_param("si", $date_app, $req_id);
                $stmt_report_gm -> execute();
            } 
            if($_SESSION['role'] == 'Director' && $nxt=='procurement' )
            {
                $sql_directors = "UPDATE requests SET directors = ?,`status`='directors',`next_step` = ? WHERE `request_id` = ?";
                $stmt_directors = $conn -> prepare($sql_directors);
                $stmt_directors -> bind_param("ssi", $_SESSION['username'], $nxt, $req_id);
                $stmt_directors -> execute();
                $sql_rep = "UPDATE `report` SET `Directors_approval_date` = ? WHERE `request_id` = ?";
                $stmt_report_Directors = $conn -> prepare($sql_rep);
                $stmt_report_Directors -> bind_param("si", $date_app, $req_id);
                $stmt_report_Directors -> execute();
            } 
            if($_SESSION['role'] == 'GM')
            {
                $sql_GM = "UPDATE requests SET GM = ? WHERE `request_id` = ?";
                $stmt_GM = $conn -> prepare($sql_GM);
                $stmt_GM -> bind_param("si", $_SESSION['username'], $req_id);
                $stmt_GM -> execute();
                $sql_rep = "UPDATE `report` SET `GM_approval_date` = ? WHERE `request_id` = ?";
                $stmt_report_GM = $conn -> prepare($sql_rep);
                $stmt_report_GM -> bind_param("si", $date_app, $req_id);
                $stmt_report_GM -> execute();
            } 

            $users =str_replace("."," ",$requested_by);
            $data_email = "
            $data_for_email
            <ul>
            <li><strong> Catagory - </strong> $type <br></li>
            <li><strong> Item - </strong> $item <br></li>
            <li><strong> Requested By - </strong> $users <br></li>
            <li><strong> Quantity - </strong> $req_quan $unit <br></li>
            <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($date_n_b))." <br></li>
            </ul>
    
            <br><br><br>";
            
            $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
            $cc =""; $bcc = ""; //$email_to

            $reason_close = "open_req_".$na_t."_".$req_id."_requested";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();
            if($status == "Approved By Dep.Manager")
            {
                $email_id = $conn->insert_id;
                $page_to = 'Director/approval.php';
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
            if(($_SESSION['role'] != 'Director' || $type != 'agreement'))
            {
                if($_SESSION['role'] == 'GM')
                {
                    updaterequest($conn,$conn_fleet,$req_id,"one","","GM");
                }
                else
                    updaterequest($conn,$conn_fleet,$req_id,"one","","Dep");
            }
            // unset($_GET['btntype']);
    }
            $_SESSION["success"]=$email_stat;
    header("location: ".$_SERVER['HTTP_REFERER']);
}
/////////////////////////////////////////add_vendor///////////////////////////////////////////////////////////////
if(isset($_POST['add_vendor']))
{
    if(isset($_POST['item']))
    {
        $all_items = "";
        foreach($_POST['item'] as $item)
        {
            $all_items .= ($all_items == "")?$item:",".$item;
        }
    }
    
    if(isset($_POST['catagory']))
    {
        $all_catagory = "";
        foreach($_POST['catagory'] as $catagory)
        {
            $all_catagory .= ($all_catagory == "")?$catagory:",".$catagory;
        }
     }
  
        $email = (!isset($_POST['mails']) || $_POST['mails'] == '')?"No Email Provided":$_POST['mails'];
     
    $sql_add_prefered_vendors = "INSERT INTO `prefered_vendors`(`catagory`, `vendor`, `business_type`,`contact`,`position`, `address`, `items`, `details`, `email`, `phone`,`rank`) VALUES (? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?)";
    $stmt_add_prefered_vendors = $conn->prepare($sql_add_prefered_vendors);
    $stmt_add_prefered_vendors->bind_param("ssssssssssi", $all_catagory, $_POST['vendor'], $_POST['business_type'], $_POST['contact'], $_POST['position'], $_POST['address'], $all_items, $_POST['details'], $email, $_POST['phones'], $_POST['rank']);
    $result = $stmt_add_prefered_vendors->execute();
    if ($result)
    {
        $_SESSION['success'] = 'Vendor Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['add_agreement_vendor'])){

    $sql_add_agreement_vendors = "INSERT INTO `prefered_vendors` (`catagory`, `vendor`) VALUES ('agreement', ?)";
    $stmt_add_agreement_vendors = $conn->prepare($sql_add_agreement_vendors);
    $stmt_add_agreement_vendors->bind_param("s", $_POST['vendor1']);
    $result = $stmt_add_agreement_vendors->execute();
    if ($result) 
    {
        $_SESSION['success'] = 'Agreement Vendor Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if(isset($_GET["approve_item_dep"]) || isset($_GET["batch_approve_item_dep"]) || isset($_GET["reject_item_dep"]) || isset($_GET["batch_reject_item_dep"])) {
        if(isset($_GET["reason"])) {
            $reason = $_GET["reason"];
        }
        $date=date("Y-m-d H:i:s");
        if(isset($_GET["approve_item_dep"]) || isset($_GET["batch_approve_item_dep"])) {
            $requests = isset($_GET["approve_item_dep"])?[$_GET["approve_item_dep"]]:explode(",",$_GET["batch_approve_item_dep"]);
            $approvaltype = "Approved";
        }
        else {
            $requests = isset($_GET["reject_item_dep"])?[$_GET["reject_item_dep"]]:explode(",",$_GET["batch_reject_item_dep"]);
            $approvaltype = "Rejected";
        }
        foreach($requests as $request) {
            $reqId = explode(":-:",$request)[0];
            $approvalFor = explode(":-:",$request)[1];
            $stmt_request -> bind_param("i", $reqId);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows>0) {
                $row = $result_request->fetch_assoc();
                $type = $row['request_type'];
                $na_t=str_replace(" ","",$type);
                if($approvaltype == "Approved") {
                    if(in_array($approvalFor,["instock"])) {//$approvalFor == "instock" || $approvalFor == "both") {
                        $sql2 = "UPDATE stock SET `flag`='1' WHERE `request_id` = ?";
                        $stmt_stock_item_approval = $conn -> prepare($sql2);
                        $stmt_stock_item_approval -> bind_param("i", $row['request_id']);
                        $stmt_stock_item_approval -> execute();
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        if($row['for_purchase'] == 0)
                            updaterequest($conn,$conn_fleet,$row['request_id'],"one");
                        $record_type = "reqeusts";
                        $record = "Item In stock Approved";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        $sql2 = "UPDATE requests SET `reason_instock` = ? WHERE `request_id` = ?";
                        $stmt_reason_instock = $conn -> prepare($sql2);
                        $stmt_reason_instock -> bind_param("si", $reason, $row['request_id']);
                        $stmt_reason_instock -> execute();
                        $_SESSION["success"]="Item Approved";
                    }
                    if(in_array($approvalFor,["Purchase"])) {
                        echo $approvalFor;
                        $sql2 = "UPDATE requests SET `flag`='1', `reason_purchased` = ? WHERE `request_id` = ?";
                        $stmt_purchased_item_approval = $conn -> prepare($sql2);
                        $stmt_purchased_item_approval -> bind_param("si", $reason, $row['request_id']);
                        $stmt_purchased_item_approval -> execute();
                        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Dep_approve");
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        $record_type = "reqeusts";
                        $record = "Purchased Item Approved";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        $_SESSION["success"]="Item Approved";
                        $go_on = true;
                    }
                    if(in_array($approvalFor,["both"])) {
                        $sql2 = "UPDATE stock SET `flag`='1' WHERE `request_id` = ?";
                        $stmt_stock_item_approval = $conn -> prepare($sql2);
                        $stmt_stock_item_approval -> bind_param("i", $row['request_id']);
                        $stmt_stock_item_approval -> execute();
                        updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Dep_approve");
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        $record_type = "reqeusts";
                        $record = "Both in stock & Purchased Item Approved";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        $sql2 = "UPDATE requests SET `flag`='1', `reason_instock` = ?, `reason_purchased` = ? WHERE `request_id` = ?";
                        $stmt_reason_both = $conn -> prepare($sql2);
                        $stmt_reason_both -> bind_param("ssi", $reason, $reason, $row['request_id']);
                        $stmt_reason_both -> execute();
                        $_SESSION["success"]="Item Approved";
                    }
                }
                if($approvaltype == "Rejected") {
                    if(in_array($approvalFor,["Purchase"])) {
                        $status = "Recollect";
                        $record_type = "reqeusts";
                        $record = "Purchased Item Rejected";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        updaterequest($conn,$conn_fleet,$row['request_id'],"four","back");
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `request_id`=? AND `request_type`=?");
                        $stmt -> bind_param("ssis", $status, $date, $row['request_id'], $type);
                        $stmt -> execute();
                        $stmt = $conn->prepare("UPDATE requests SET `status`=?, `reason_purchased`=? WHERE `request_id`=? ");
                        $stmt -> bind_param("ssi", $status, $reason, $row['request_id']);
                        $stmt -> execute();
                        $_SESSION["success"]="Rejected Item";
                        $go_on = true;
                    }
                    if(in_array($approvalFor,["instock"])) {
                        $from_stock = true;
                        $status = "not approved";
                        $in = 0;
                        $out = $row['requested_quantity'];
                        $record_type = "reqeusts";
                        $record = "Item In stock Rejected";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        $stmt = $conn->prepare("UPDATE `stock` SET `status`=? ,`in-stock`=? ,`for_purchase`=? WHERE `request_id`=? AND `type` =?");
                        $stmt -> bind_param("siiis", $status, $in, $out, $row['request_id'], $type);
                        $stmt -> execute();
                        $sql2 = "UPDATE requests SET `reason_instock` = ? WHERE `request_id` = ?";
                        $stmt_reason_instock = $conn -> prepare($sql2);
                        $stmt_reason_instock -> bind_param("si", $reason, $row['request_id']);
                        $stmt_reason_instock -> execute();
                        $_SESSION["success"]="Rejected Item";
                        $go_on = true;
                    }
                    if(in_array($approvalFor,["both"])) {
                        $date=date("Y-m-d H:i:s");
                        $status = "Recollect";
                        updaterequest($conn,$conn_fleet,$row['request_id'],"four","back");
                        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `request_id`=? AND `request_type`=?");
                        $stmt -> bind_param("ssis", $status, $date, $row['request_id'], $type);
                        $stmt -> execute();
                        $record_type = "reqeusts";
                        $record = "Both in stock & Purchased Item Rejected";
                        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
                        $stmt_add_record -> execute();
                        $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
                        $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
                        $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
                        $stmt_report_dep_check_date -> execute();
                        $stmt = $conn->prepare("UPDATE requests SET `status`=?, `reason_instock`=?, `reason_purchased`=? WHERE `request_id`=? ");
                        $stmt -> bind_param("sssi", $status, $reason, $reason, $row['request_id']);
                        $stmt -> execute();
                        $status = "not approved";
                        $in = 0;
                        $out = $row['requested_quantity'];
                        $stmt = $conn->prepare("UPDATE `stock` SET `status`=? ,`in-stock`=? ,`for_purchase`=? WHERE `request_id`=? AND `type` =?");
                        $stmt -> bind_param("siiis", $status, $in, $out, $row['request_id'], $type);
                        $stmt -> execute();
                        $_SESSION["success"]="Rejected Item";
                        $go_on = true;
                    }
                }
                if(isset($_GET['reason']) && $_GET['reason'] != "")
                {
                    $level = "Department Stock Item Approval";
                    $stmt_remark -> bind_param("isss", $row['request_id'], $_GET['reason'], $_SESSION['username'], $level);
                    $stmt_remark -> execute();
                }
                $reason_close = "open_req_".$na_t."_".$reqId."_instock_approval";
                $stmt_email_close -> bind_param("s",$reason_close);
                $stmt_email_close -> execute();
                $item = $row['item'];
                $dep = $row['department'];
                $comp = $row['company'];
                $property_company = $row['property_company'];
                $date_requested = date("d-M-Y", strtotime($row['date_requested']));
                $km = (isset($row['current_km']))?$row['current_km']:"";
                $send_to = "";
                $sql_temp = "SELECT `email`,`Username` FROM `account` where `department`='property' AND `company` = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role='Store') AND `status` = 'active'";
                $stmt_email_stock = $conn->prepare($sql_temp);
                $stmt_email_stock -> bind_param("s", $property_company);
                $stmt_email_stock -> execute();
                $result_email_stock = $stmt_email_stock -> get_result();
                if($result_email_stock->num_rows>0)
                    while($row_temp = $result_email_stock->fetch_assoc()) 
                    {
                        // echo $send_to."<br>";
                        $send_to .= ($send_to=="")?$row_temp['email'].",".$row_temp['Username']:",".$row_temp['email'].",".$row_temp['Username'];
                    }
                $reason = (array_search($approvalFor,["instock"]))?"closed":"open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
                $detail = (array_search($approvalFor,["Purchase"]))?"Was Appoved By $dep Department Item is ready to be processed":
                ((isset($_GET["reject_item_".$na_t.$row['request_id']]))?"was rejected by $dep department purchase order was moved to purchase officer for recollection":
                "was rejected by $dep department purchase order will continue to purchasing proccess");
                $subject_email = "Requesting department feedback of $item";
                $data_email = "
                <strong>$item requested $detail<strong><br><br>
                <strong>Please act accordingly<strong><br><br><br>
                ";
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $cc =""; $bcc = "";
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $cust ,$com_lo ,$reason,$user);
                $stmt_email_reason -> execute();

                $email_id = $conn->insert_id;
                $page_to = 'property/storeclerk.php';
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    // $sql_collected_tasks = "SELECT *, R.status AS `rstatus`, S.status AS `sstatus` FROM requests AS R INNER JOIN `stock` AS S ON R.request_id = S.request_id WHERE ((R.flag = 0 AND R.status='Collected-not-comfirmed') OR (S.flag = 0 AND S.status = 'Approved')) AND `company` = ?";
    // $stmt_collected_tasks = $conn -> prepare($sql_collected_tasks);
    // $stmt_collected_tasks -> bind_param("s", $_SESSION['company']);
    // $stmt_collected_tasks -> execute();
    // $result_collected_tasks = $stmt_collected_tasks -> get_result();
    // if($result_collected_tasks->num_rows>0)
    // while($row = $result_collected_tasks->fetch_assoc())
    // {
    //     $type = $row['request_type'];
    //     $na_t=str_replace(" ","",$type);
    //     if(isset($_GET["reason"]))
    //     {
    //         $reason = $_GET["reason"];
    //     }
    //     $date=date("Y-m-d H:i:s");
    //     if(isset($_GET["approve_item_".$na_t.$row['request_id']]))
    //     {
    //         $sql2 = "UPDATE requests SET `flag`='1', `reason_purchased` = ? WHERE `request_id` = ?";
    //         $stmt_purchased_item_approval = $conn -> prepare($sql2);
    //         $stmt_purchased_item_approval -> bind_param("si", $reason, $row['request_id']);
    //         $stmt_purchased_item_approval -> execute();
    //         updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Dep_approve");
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         $record_type = "reqeusts";
    //         $record = "Purchased Item Approved";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         $_SESSION["success"]="Item Approved";
    //         $go_on = true;
    //     }
    //     if(isset($_GET["approve_item_instock_".$na_t.$row['request_id']]))
    //     {
    //         $from_stock = true;
    //         $sql2 = "UPDATE stock SET `flag`='1' WHERE `request_id` = ?";
    //         $stmt_stock_item_approval = $conn -> prepare($sql2);
    //         $stmt_stock_item_approval -> bind_param("i", $row['request_id']);
    //         $stmt_stock_item_approval -> execute();
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         if($row['for_purchase'] == 0)
    //             updaterequest($conn,$conn_fleet,$row['request_id'],"one");
    //         $record_type = "reqeusts";
    //         $record = "Item In stock Approved";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         $sql2 = "UPDATE requests SET `reason_instock` = ? WHERE `request_id` = ?";
    //         $stmt_reason_instock = $conn -> prepare($sql2);
    //         $stmt_reason_instock -> bind_param("si", $reason, $row['request_id']);
    //         $stmt_reason_instock -> execute();
    //         $_SESSION["success"]="Item Approved";
    //         $go_on = true;
    //     }
    //     if(isset($_GET["approve_item_both_".$na_t.$row['request_id']]))
    //     {
    //         $sql2 = "UPDATE stock SET `flag`='1' WHERE `request_id` = ?";
    //         $stmt_stock_item_approval = $conn -> prepare($sql2);
    //         $stmt_stock_item_approval -> bind_param("i", $row['request_id']);
    //         $stmt_stock_item_approval -> execute();
    //         updaterequest($conn,$conn_fleet,$row['request_id'],"four","","Dep_approve");
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         $record_type = "reqeusts";
    //         $record = "Both in stock & Purchased Item Approved";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         $sql2 = "UPDATE requests SET `flag`='1', `reason_instock` = ?, `reason_purchased` = ? WHERE `request_id` = ?";
    //         $stmt_reason_both = $conn -> prepare($sql2);
    //         $stmt_reason_both -> bind_param("ssi", $reason, $reason, $row['request_id']);
    //         $stmt_reason_both -> execute();
    //         $_SESSION["success"]="Item Approved";
    //         $go_on = true;
    //     }
    //     if(isset($_GET["reject_item_".$na_t.$row['request_id']]))
    //     {
    //         $status = "Recollect";
    //         $record_type = "reqeusts";
    //         $record = "Purchased Item Rejected";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         updaterequest($conn,$conn_fleet,$row['request_id'],"four","back");
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `request_id`=? AND `request_type`=?");
    //         $stmt -> bind_param("ssis", $status, $date, $row['request_id'], $type);
    //         $stmt -> execute();
    //         $stmt = $conn->prepare("UPDATE requests SET `status`=?, `reason_purchased`=? WHERE `request_id`=? ");
    //         $stmt -> bind_param("ssi", $status, $reason, $row['request_id']);
    //         $stmt -> execute();
    //         $_SESSION["success"]="Rejected Item";
    //         $go_on = true;
    //     }
    //     if(isset($_GET["reject_item_instock_".$na_t.$row['request_id']]))
    //     {
    //         $from_stock = true;
    //         $status = "not approved";
    //         $in = 0;
    //         $out = $row['requested_quantity'];
    //         $record_type = "reqeusts";
    //         $record = "Item In stock Rejected";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         $stmt = $conn->prepare("UPDATE `stock` SET `status`=? ,`in-stock`=? ,`for_purchase`=? WHERE `request_id`=? AND `type` =?");
    //         $stmt -> bind_param("siiis", $status, $in, $out, $row['request_id'], $type);
    //         $stmt -> execute();
    //         $sql2 = "UPDATE requests SET `reason_instock` = ? WHERE `request_id` = ?";
    //         $stmt_reason_instock = $conn -> prepare($sql2);
    //         $stmt_reason_instock -> bind_param("si", $reason, $row['request_id']);
    //         $stmt_reason_instock -> execute();
    //         $_SESSION["success"]="Rejected Item";
    //         $go_on = true;
    //     }
    //     if(isset($_GET["reject_item_both_".$na_t.$row['request_id']]))
    //     {
    //         $date=date("Y-m-d H:i:s");
    //         $status = "Recollect";
    //         updaterequest($conn,$conn_fleet,$row['request_id'],"four","back");
    //         $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `request_id`=? AND `request_type`=?");
    //         $stmt -> bind_param("ssis", $status, $date, $row['request_id'], $type);
    //         $stmt -> execute();
    //         $record_type = "reqeusts";
    //         $record = "Both in stock & Purchased Item Rejected";
    //         $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['request_id'], $record);
    //         $stmt_add_record -> execute();
    //         $sql_rep = "UPDATE `report` SET `dep_check_date` = ? WHERE `request_id` = ?";
    //         $stmt_report_dep_check_date = $conn -> prepare($sql_rep);
    //         $stmt_report_dep_check_date -> bind_param("si", $date, $row['request_id']);
    //         $stmt_report_dep_check_date -> execute();
    //         $stmt = $conn->prepare("UPDATE requests SET `status`=?, `reason_instock`=?, `reason_purchased`=? WHERE `request_id`=? ");
    //         $stmt -> bind_param("sssi", $status, $reason, $reason, $row['request_id']);
    //         $stmt -> execute();
    //         $status = "not approved";
    //         $in = 0;
    //         $out = $row['requested_quantity'];
    //         $stmt = $conn->prepare("UPDATE `stock` SET `status`=? ,`in-stock`=? ,`for_purchase`=? WHERE `request_id`=? AND `type` =?");
    //         $stmt -> bind_param("siiis", $status, $in, $out, $row['request_id'], $type);
    //         $stmt -> execute();
    //         $_SESSION["success"]="Rejected Item";
    //         $go_on = true;
    //     }
    //     if(isset($go_on))
    //     {
    //         if(isset($_GET['reason']) && $_GET['reason'] != "")
    //         {
    //             $level = "Department Stock Item Approval";
    //             $stmt_remark -> bind_param("isss", $row['request_id'], $_GET['reason'], $_SESSION['username'], $level);
    //             $stmt_remark -> execute();
    //         }
    //         $reason_close = "open_req_".$na_t."_".$all_details[2]."_instock_approval";
    //         $stmt_email_close -> bind_param("s",$reason_close);
    //         $stmt_email_close -> execute();
    //         $stmt_request -> bind_param("i", $row['request_id']);
    //         $stmt_request -> execute();
    //         $result_request = $stmt_request -> get_result();
    //         if($result_request -> num_rows>0)
    //             while($row_temp = $result_request->fetch_assoc()) 
    //             {
    //                 $item = $row_temp['item'];
    //                 $dep = $row_temp['department'];
    //                 $comp = $row_temp['company'];
    //                 $property_company = $row_temp['property_company'];
    //                 $date_requested = date("d-M-Y", strtotime($row_temp['date_requested']));
    //                 $km = (isset($row_temp['current_km']))?$row_temp['current_km']:"";
    //             }
                
    //         $send_to = "";
    //         $sql_temp = "SELECT `email`,`Username` FROM `account` where `department`='property' AND `company` = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role='Store') AND `status` = 'active'";
    //         $stmt_email_stock = $conn->prepare($sql_temp);
    //         $stmt_email_stock -> bind_param("s", $property_company);
    //         $stmt_email_stock -> execute();
    //         $result_email_stock = $stmt_email_stock -> get_result();
    //         if($result_email_stock->num_rows>0)
    //             while($row_temp = $result_email_stock->fetch_assoc()) 
    //             {
    //                 // echo $send_to."<br>";
    //                 $send_to .= ($send_to=="")?$row_temp['email'].",".$row_temp['Username']:",".$row_temp['email'].",".$row_temp['Username'];
    //             }
    //             $reason = (isset($from_stock) && $from_stock == true)?"closed":"open_req_".$na_t."_".$row['request_id']."_app/rej-dep";
    //         $detail = (isset($_GET["approve_item_".$na_t.$row['request_id']]))?"Was Appoved By $dep Department Item is ready to be processed":
    //         ((isset($_GET["reject_item_".$na_t.$row['request_id']]))?"was rejected by $dep department purchase order was moved to purchase officer for recollection":
    //         "was rejected by $dep department purchase order will continue to purchasing proccess");
    //         $subject_email = "Requesting department feedback of $item";
    //         $data_email = "
    //         <strong>$item requested $detail<strong><br><br>
    //         <strong>Please act accordingly<strong><br><br><br>
    //         ";
    //         $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
    //         $cc =""; $bcc = "";
    //         $user=($_SESSION['username'].":-:".$_SESSION['position']);
    //         $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $cust ,$com_lo ,$reason,$user);
    //         $stmt_email_reason -> execute();

    //         $email_id = $conn->insert_id;
    //         $page_to = 'property/storeclerk.php';
    //         $stmt_email_page -> bind_param("si",$page_to, $email_id);
    //         $stmt_email_page -> execute();
            
    //         header("location: ".$_SERVER['HTTP_REFERER']);
    //     }
    // }

    $date=date("Y-m-d H:i:s");
    if(isset($_GET["confirm_recieved"]))
    {
        $p_id = $_GET["confirm_recieved"];
        $stmt_po -> bind_param("i", $p_id);
        $stmt_po -> execute();
        $result_po = $stmt_po -> get_result();
        $row = $result_po -> fetch_assoc();
        $status = "All Complete";
        $recieved = "yes";
        $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?, `timestamp`=? WHERE `purchase_order_id`=?");
        $stmt -> bind_param("ssi", $status, $date, $pid);
        $stmt -> execute();
        
        $stmt = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=?");
        $stmt -> bind_param("si", $status, $row['cluster_id']);
        $stmt -> execute();
        
        $stmt = $conn->prepare("UPDATE requests SET `status`=?, `recieved`=? WHERE `request_id`=?");
        $stmt -> bind_param("ssi", $status, $recieved, $row['request_id']);
        $stmt -> execute();
        $stmt = $conn->prepare("UPDATE `report` SET `final_recieved_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $row['request_id'], $row['request_type']);
        $stmt -> execute();
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    $date=date("Y-m-d H:i:s");
    if(isset($_GET["confirm_in_stock"]))
    {
        $status = "All Complete";
        $recieved = "yes";
        $dataa = explode("_",$_GET["confirm_in_stock"]);
        $type = na_t_to_type($conn,$dataa[0]);
        if($dataa[2] == 0 )
        {
            $stmt = $conn->prepare("UPDATE requests SET `status`=?, `recieved`=? WHERE `request_id`=?");
            $stmt -> bind_param("ssi", $status, $recieved, $dataa[3]);
            $stmt -> execute();
            $stmt = $conn->prepare("UPDATE `report` SET `final_recieved_date`=? WHERE `request_id`=? AND `type`=?");
            $stmt -> bind_param("sis", $date, $dataa[3], $type);
            $stmt -> execute();
        }
        $stmt = $conn->prepare("UPDATE `stock` SET `status`=? WHERE `id`=?");
        $stmt -> bind_param("si", $status, $dataa[1]);
        $stmt -> execute();
        
        $stmt = $conn->prepare("UPDATE `report` SET `final_instock_recieved_date`=? WHERE `request_id`=? AND `type`=?");
        $stmt -> bind_param("sis", $date, $dataa[3], $type);
        $stmt -> execute();
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["process_cheque"]))
    {
        $c_id = intval(explode("::-::",$_GET["process_cheque"])[0]);
        $providing_company = explode("::-::",$_GET["process_cheque"])[1];
        $cpv_no = intval(explode("::-::",$_GET["process_cheque"])[2]);

        $stmt_cluster -> bind_param("i", $c_id);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster -> get_result();
        $row = $result_cluster -> fetch_assoc();

        $stmt_cheques_active_cpv -> bind_param("i", $cpv_no);
        $stmt_cheques_active_cpv -> execute();
        $result_cheques_active_cpv = $stmt_cheques_active_cpv -> get_result();
        $row_chq = $result_cheques_active_cpv -> fetch_assoc();
        $providing_company = $row_chq['providing_company'];
        $cheque_only = ($row_chq['prepared_percent']<100 && $row_chq['final']);

        $record_type = "cluster";
        $record = "Cheque Signed ($providing_company)";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['id'], $record);
        $stmt_add_record -> execute();
        
        $stmt_account -> bind_param("s", $_SESSION['username']);
        $stmt_account -> execute();
        $result_account = $stmt_account -> get_result();
        $row2 = $result_account -> fetch_assoc();
        $percent = intval(explode("_",$row2['cheque_percent'])[1]);
        $priority = explode("_",$row2['cheque_percent'])[0];
        $reason_close = "open_clust_".$row['id']."_cheque_signatory";
        $sql2 = "UPDATE emails SET `reason`='closed' WHERE `reason` = ? AND tag = ?";
        $stmt_email_close_specific = $conn->prepare($sql2);
        $stmt_email_close_specific -> bind_param("ss",$reason_close, $_SESSION['username']);
        $stmt_email_close_specific -> execute();
        if(is_null($row_chq['signatory']) || $row_chq['signatory'] =="")
        {
            $sql2 = "UPDATE `cheque_info` SET `signatory` = ?, `cheque_percent` = ? where `cpv_no` = ?";
            $stmt_update_signatory = $conn->prepare($sql2);
            $stmt_update_signatory -> bind_param("ssi",$_SESSION['username'], $row2['cheque_percent'], $cpv_no);
            $stmt_update_signatory -> execute();
            if($priority == "p" && $percent == 100)
                $full = true;
            else 
                $full = false;
            echo $conn->error;
        }
        else
        {
            $current_percent = floatval(explode("_",$row_chq['cheque_percent'])[1]);
            $current_priority = ($priority == "p")?$priority:explode("_",$row_chq['cheque_percent'])[0];
            $percent_agg = $percent + $current_percent;
            if($percent_agg >= 100)
            {
                $percent_agg = 100;
                if($current_priority == "p") $full = true;
                else $full = false;
            }
            else
            {
                $full = false;
            }
            $cheque_sig = (!is_null($row_chq['signatory']) && $row_chq['signatory'] != "")?$row_chq['signatory'].",".$_SESSION['username']:$_SESSION['username'];
            $cheque_percent = $current_priority."_".$percent_agg;
            $sql2 = "UPDATE cheque_info SET `signatory` = ?, `cheque_percent` = ? where `cpv_no` = ?";
            $stmt_update_signatory = $conn->prepare($sql2);
            $stmt_update_signatory -> bind_param("ssi",$cheque_sig, $cheque_percent, $cpv_no);
            $stmt_update_signatory -> execute();
        }
        if($full)
        {
            $date=date("Y-m-d H:i:s");
            $status =($row_chq['status'] == 'pending')?"Payment Processed":"All Payment Processed";
            if($row_chq['status'] != 'pending')
            {
                $stmt = $conn->prepare("UPDATE `cheque_info` SET `status`=? WHERE `providing_company`=? AND `cluster_id`=?");
                $stmt -> bind_param("ssi", $status, $row_chq['providing_company'], $row_chq['cluster_id']);
                $stmt -> execute();
            }
            else
            {
                $stmt = $conn->prepare("UPDATE `cheque_info` SET `status`=? WHERE `cpv_no`=?");
                $stmt -> bind_param("si", $status, $cpv_no);
                $stmt -> execute();
            }
            foreach(explode(":-:",$row_chq['purchase_order_ids']) As $po_id)
            {
                $stmt_po -> bind_param("i", $po_id);
                $stmt_po -> execute();
                $result_po = $stmt_po -> get_result();
                if($result_po -> num_rows>0)
                    while($row2 = $result_po -> fetch_assoc())
                    {
                        if(!$cheque_only)
                        {
                            updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","cheque_signed");
                            $officer_col = $row2['purchase_officer'];
                            $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=?,`collector`=? WHERE `purchase_order_id`=? ");
                            $stmt -> bind_param("ssi", $status, $row2['purchase_officer'], $po_id);
                            $stmt -> execute();
                            // $l=str_replace(" ","",$row2['request_type']);
                            $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
                            $stmt -> bind_param("si", $status, $row2['request_id']);
                            $stmt -> execute();
                            $sql2 = "UPDATE requests SET `next_step`='Collection' WHERE `request_id` = ?";
                            $stmt_next_step_Collection = $conn->prepare($sql2);
                            $stmt_next_step_Collection -> bind_param("i", $row2['request_id']);
                            $stmt_next_step_Collection -> execute();
                            $sql_rep = "UPDATE `report` SET `cheque_signed_date` = ?,`collector_assigned_date` = ? WHERE `request_id` = ?";
                            $stmt_report_signed_date = $conn -> prepare($sql_rep);
                            $stmt_report_signed_date -> bind_param("ssi", $date, $date, $row2['request_id']);
                            $stmt_report_signed_date -> execute();
                        }
                        $_SESSION["success"]=$status;
                        $procurement_company = $row2['procurement_company'];
                    }
            }

            $reason_close = "open_clust_".$c_id."_cheque_signatory";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();

            if(!$cheque_only)
            {
                $stmt = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=? ");
                $stmt -> bind_param("si", $status, $c_id);
                $stmt -> execute();
                $stmt_active_account -> bind_param("s", $officer_col);
                $stmt_active_account -> execute();
                $result_active_account = $stmt_active_account -> get_result();
                if($result_active_account -> num_rows>0)
                    while($row2 = $result_active_account -> fetch_assoc())
                    {
                        $phone_number = $row2['phone'];
                        $email = $row2['email'];
                        $sms_to = $officer_col; 
                        $sms_from = $_SESSION['username'];
                        $msg = "A cheque has been signed for purchase and is now ready to be collected Please visit lpms.hagbes.com";
                        include "../common/sms.php";
                        $subject_email = "A collection task was assigned to you";
                        $data_email = "
                        <strong>A Collection task was assigned to you for collection<strong><br>
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
        }
        $_SESSION["success"]="Cheque Signed";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    //////////////////////////////Spec Check///////////////////////////////////////////////
    if(isset($_GET['reject_spec']))
    {
        $requests = (isset($_GET['reject_spec']))?[$_GET['reject_spec']]:explode(",",$_GET['batch_reject_spec']);
        foreach($requests as $req)
        {
            if(isset($_GET['reason']) && $_GET['reason'] != "")
            {
                $level = "IT Manager";
                $stmt_remark -> bind_param("ssss", $req, $_GET['reason'],$_SESSION['username'],$level);
                $stmt_remark -> execute();
            }
            $sql_rejected_by_it = "UPDATE requests SET `status`='Rejected By IT',`next_step` = 'Rejected' WHERE `request_id`=?";
            $stmt_rejected_by_it = $conn -> prepare($sql_rejected_by_it);
            $stmt_rejected_by_it -> bind_param("i", $req);
            $stmt_rejected_by_it -> execute();
            $_SESSION['success'] = "Item Rejected";
            $stmt_request -> bind_param("i", $req);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row2 = $result_request -> fetch_assoc();
            $na_t=str_replace(" ","",$row2['request_type']);
            $reason_close = "open_req_".$na_t."_".$req."_specification";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['speccheck']))
    {
        $dep =str_replace(' ', '',$_GET['dep']); 
        $data = explode("_",$_GET['speccheck']);
        $type = na_t_to_type($conn,$data[0]);
        if($dep=='') $dep =null;
        $date_app=date("Y-m-d H:i:s");
        $sql_spec_dep = "UPDATE requests SET `spec_dep` = ? WHERE `request_id` = ?";
        $stmt_spec_dep = $conn -> prepare($sql_spec_dep);
        $stmt_spec_dep -> bind_param("si", $dep, $data[1]);
        $stmt_spec_dep -> execute();
        $sql_rep = "UPDATE `report` SET `sent_for_spec` = ? WHERE `request_id` = ?";
        $stmt_report_spec_dep = $conn -> prepare($sql_rep);
        $stmt_report_spec_dep -> bind_param("si", $date_app, $data[1]);
        $stmt_report_spec_dep -> execute();

        
        $stmt2 = $conn->prepare("SELECT `item`,`customer` FROM requests WHERE `request_id`='".$data[1]."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($item,$uname);
        $stmt2->fetch();
        $stmt2->close();

        $na_t=$data[0];
        $reason = "open_req_".$na_t."_".$data[1]."_specification";
        $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where (role = 'manager' OR `type` LIke '%manager%') AND department = '$dep'  and status='active'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($email,$email_to);
        $stmt2->fetch();
        $stmt2->close();
        $subject_email = "Item - $item is Awaiting Specification Details";
        $data_email = "
        <strong>An Item - $item purchase request was sent to your department<strong><br>
        <strong>Please enter a valid specification fitting the item </strong><br>
        ";
        $send_to = $email.",".$email_to;
        $cc =""; $bcc = ""; $tag = $email_to; 
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $user=($_SESSION['username'].":-:".$_SESSION['position']);
        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
        $stmt_email_reason -> execute();
        
        $email_id = $conn->insert_id;
        $page_to = 'requests/specs.php';
        $stmt_email_page -> bind_param("si",$page_to, $email_id);
        $stmt_email_page -> execute();
        
        // send_auto_email($subject_email,$data_email,$email.",".$uname);

        unset($_GET['dep']);
        unset($_GET['speccheck']);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////insert_spec//////////////////////////////////////////////////////////////
    
    // $list=array("ConsumerGoods","SpareandLubricant", "TyreandBattery","FixedAssets","StationaryandToiletaries","Miscellaneous");
    // foreach($list as $l)
    // {
        if(isset($_POST['spec_insert']))
        {
            $data = explode("_",$_POST['info']);
            $type = na_t_to_type($conn,$data[0]);
            // echo $dbs."</br>".$type."</br>".$data[1]."</br>".$_GET['spec'];
            $_spec = 'Default Spec';
            $date=date("Y-m-d H:i:s");
            $file_name = array_filter($_FILES['specs_pic']['name']);
            $total_count = count($_FILES['specs_pic']['name']);
            $file_size=0;
            $error = "";
            $new_file = "";
            for( $j=0 ; $j < $total_count ; $j++ ) {
                $file_size = $file_size+$_FILES['specs_pic']['size'][$j];
            }
            for( $i=0 ; $i < $total_count ; $i++ ) 
            {
                $file_tmp = $_FILES['specs_pic']['tmp_name'][$i];
                if ($file_tmp != "")
                {
                    $uniq = uniqid();
                    $extention = ".".explode('.',$_FILES['specs_pic']['name'][$i])[(sizeof(explode('.',$_FILES['specs_pic']['name'][$i]))-1)];
                    $newFilePath = "../../lpms_uploads/spec-".$data[0].$data[1]."_".$uniq.$extention;
                    if(move_uploaded_file($file_tmp, $newFilePath))
                    {
                        $new_file .= ($new_file=="")?"":":_:";
                        $new_file.="spec-".$data[0].$data[1]."_".$uniq.$extention;
                    }
                    else
                    {
                        $error .= ($error=="")?"":"";
                        $error .= "'".$_FILES['specs_pic']['name'][$i]."'";
                        echo $error;
                    }
                }
            }
            $spec = $_POST['spec'];// str_replace("'","'",$_POST['spec']);
            $request_id = intval($data[1]);
            $sql = "INSERT INTO `specification`(`request_id`, `type`, `details`, `pictures`, `date`, `given_by`, `department`) VALUES (? ,? ,? ,? ,? ,? ,?)";
            $stmt_add_specification = $conn -> prepare($sql);
            $stmt_add_specification -> bind_param("issssss", $request_id, $type, $spec, $new_file, $date ,$_SESSION['username'] ,$_SESSION['department']);
            $result = $stmt_add_specification -> execute();
            if (!$result) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            $last_id = $conn->insert_id;
            $sql = "UPDATE requests SET `status` = 'Specification Provided',`next_step` = 'Store',`specification` = ? WHERE `request_id` = ?";
            $stmt_update_spec_provided = $conn -> prepare($sql);
            $stmt_update_spec_provided -> bind_param("ii", $last_id ,$data[1]);
            $stmt_update_spec_provided -> execute();
            updaterequest($conn,$conn_fleet,$data[1],"one","","IT");
            $sql_rep = "UPDATE `report` SET `spec_recieved` = ? WHERE `request_id` = ?";
            $stmt_report = $conn -> prepare($sql_rep);
            $stmt_report -> bind_param("si", $date ,$data[1]);
            $stmt_report -> execute();
            $_SESSION['success'] = "Specification Provided";
            
            $na_t = $data[0];
            $reason_close = "open_req_".$na_t."_".$data[1]."_specification";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();

            
            $stmt_request -> bind_param("i", $data[1]);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows > 0)
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
                }
            $data_for_email = "<strong>$item purchase request In $depp department waiting for store check please review in a timely manner<strong><br>";
            $subject_email = "$item purchase request In $depp department waiting for store check";
            $reason = "open_req_".$na_t."_".$data[1]."_store";
            $sql_email = "SELECT * FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = ?  and status='active'";
            $stmt_email_Store = $conn->prepare($sql_email);
            $stmt_email_Store -> bind_param("s", $property_company);
            $stmt_email_Store -> execute();
            $result_email_Store = $stmt_email_Store -> get_result();
            if($result_email_Store->num_rows>0)
                while($row_email = $result_email_Store->fetch_assoc())
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

            header("location: ".$_SERVER['HTTP_REFERER']);
        }
    
    //////////////////////////////Manager Desc///////////////////////////////////////////////
    if(isset($_GET['request']))
    {
        $data = explode("_",$_GET['request']);
        $type = na_t_to_type($conn,$data[0]);
        $date_app=date("Y-m-d H:i:s");
        $sql2 = "UPDATE requests SET `manager_description` = ? WHERE `request_id` = ?";
        $stmt_manager_description = $conn -> prepare($sql2);
        $stmt_manager_description -> bind_param("si", $_GET['m_desc'] ,$data[1]);
        $stmt_manager_description -> execute();
        unset($_GET['request']);
        unset($_GET['m_desc']);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////for Consumer Goodss project list
if(isset($_GET["Request_CG"]))
{
    if($_GET["Request_CG"] == "General")
    {
        $_SESSION["project_id"]="0";
        $_SESSION["project_name"]=$_GET["Request_CG"];
    }
    else
    {
        $stmt_project_pms->bind_param("i", $_GET['Request_CG']);
        $stmt_project_pms->execute();
        $result_project_pms = $stmt_project_pms->get_result();
        if($result_project_pms -> num_rows>0)
            while($row = $result_project_pms -> fetch_assoc()) 
            {
                $_SESSION["project_id"]=$row['id'];
                $_SESSION["project_name"]=$row["project_name"];
            }
    }
    header("location:consumerGoodForm.php");
}


if(isset($_GET["request_spare"]))
{
    if($_GET["request_spare"] == 'None')
    {
        $_SESSION["request_for"]=$_GET["request_spare"];
        $_SESSION["job_name"]=$_GET["request_spare"];
        header("location:spareForm.php");
    }
    else 
    {
        $stmt_description->bind_param("i", $_GET['request_spare']);
        $stmt_description->execute();
        $result_description = $stmt_description->get_result();
        if($result_description -> num_rows>0)
        {
            while($row = $result_description -> fetch_assoc()) 
            {
                $_SESSION["request_for"]=$row['iden'];
                $_SESSION["job_name"]=$row["description"];
                header("location:spareForm.php");
            }
        }
    }
}

/////////////////raw request form///////////////
if(isset($_POST['submit_raw_request']))
{
    unset($_POST['submit_raw_request']);
    $stmt_unique = $conn->prepare("INSERT INTO `requests`
    (`request_for`, `request_type`, `customer`, `item`,
     `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
     `Remark`, `description`,
      `department`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    for($ii = 0;$ii<sizeof($_POST['item']);$ii++)
    {
        if($_POST['remark'][$ii]=='')
            $remark[$ii] = '#';
        else
            $remark[$ii] = $_POST['remark'][$ii];
        if($_POST['description'][$ii]=='')
            $desc[$ii] = '#';
        else
            $desc[$ii] = $_POST['description'][$ii];
        if($_SESSION["role"] == "GM")
            $status = 'Approved By GM';
        else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner" || strpos($_SESSION["a_type"],"manager") !== false)
            $status = 'Approved By Dep.Manager';
        else
            $status = 'waiting';
         

        $id_project=$_SESSION["project_id"];
        $uname=$_SESSION["username"];
        $type='Consumer Goods';
        if($id_project == '0')
        {
            $item[$ii]=$_POST['item'][$ii];
        }
        else
        {
            $sql_item = "SELECT * FROM item where `id` = ?";
            $stmt_item_fetch = $conn_pms -> prepare($sql_item);
            $stmt_item_fetch -> bind_param("i", $_POST['item'][$ii]);
            $stmt_item_fetch -> execute();
            $result_item_fetch = $stmt_item_fetch -> get_result();
            $row_item = $result_item_fetch -> fetch_assoc();
            $id_project .= "|".$row_item['id_t']."|".$_POST['item'][$ii];
            $item[$ii] = $row_item['item_name'];
        }
        $unit[$ii]=$_POST['unit'][$ii];
        $req_quan[$ii]=$_POST['req_quan'][$ii];
        $date_req=date("Y-m-d H:i:s");
        $date_n_b[$ii]=$_POST['date_n_b'][$ii];
        $department = $_SESSION['department'];
        $stmt_company -> bind_param("s", $_SESSION["company"]);
        $stmt_company -> execute();
        $result_company = $stmt_company -> get_result();
        if($result_company -> num_rows > 0)
        {
            while($r = $result_company -> fetch_assoc()) 
            {
                $comp_type = $r['type'];
                $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
            }
        }
        $has_gm = in_array($_SESSION['department'],$GMs) || in_array("All",$GMs);
        $property_company = $_SESSION['property_company'];
        if($comp_type == 'Branch')
        {
            // $property_company = $_SESSION['processing_company'];
            $procurement_company = $_SESSION['procurement_company'];
        }
        else
        {
            $procurement_company = $_SESSION['procurement_company'];
        }
        $stmt_unique -> bind_param("ssssdssssssssssss",$id_project,$type, $uname, $item[$ii], $req_quan[$ii], $unit[$ii], $date_req, $date_n_b[$ii], $remark[$ii], $desc[$ii], $department, $status, $_SESSION['company'], $_SESSION['processing_company'], $property_company, $procurement_company, $_SESSION['finance_company']);
        if($stmt_unique -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            $last_id[$ii] = $conn->insert_id;
            if($_SESSION["role"] == "GM")
            {
                $nxt = "Store";
                $sql2 = "UPDATE requests SET `manager` = ?, `GM` = ?,`next_step` = ?,phase_one = 3 WHERE `request_id` = ?";
                $stmt_GM_request = $conn -> prepare($sql2);
                $stmt_GM_request -> bind_param("sssi", $_SESSION['username'], $_SESSION['username'], $nxt, $last_id[$ii]);
                $stmt_GM_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")
            {
                $nxt = "Store";
                $set = ($_SESSION["role"] == "Director")?"director":"owner";
                $sql2 = "UPDATE requests SET `manager` = ?, `next_step` = ?, `$set` = ?, phase_one = 2 WHERE `request_id` = ?";
                $stmt_dirown_request = $conn -> prepare($sql2);
                $stmt_dirown_request -> bind_param("sssi", $_SESSION['username'], $nxt, $_SESSION['username'], $last_id[$ii]);
                $stmt_dirown_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if(strpos($_SESSION["a_type"],"manager") !== false)
            {
                $nxt = ($has_gm)?"GM":"Store";
                $sql2 = "UPDATE requests SET `manager` = ?,`next_step` = ?,phase_one = 2 WHERE `request_id` = ?";
                $stmt_manager = $conn -> prepare($sql2);
                $stmt_manager -> bind_param("ssi", $_SESSION['username'], $nxt, $last_id[$ii]);
                $stmt_manager -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`) VALUES (?, ?, ?, ?)");
                $stmt-> bind_param("isss",$last_id[$ii], $type, $date_req, $date_req);
                $stmt -> execute();
            }
            else
            {
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) VALUES (?, ?, ?)");
                $stmt-> bind_param("iss",$last_id[$ii], $type, $date_req);
                $stmt -> execute();
            }
        }
        else
        {
            echo $conn->error;
        }
    }
}
///////////////////////////////////////



///////////////// spare form///////////////
if(isset($_POST['submit_spare_request']))
{
    unset($_POST['submit_spare_request']);
    $stmt_spare = $conn->prepare("INSERT INTO `requests`
    (`request_for`, `request_type`, `customer`, `item`,
        `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `type`,
        `Remark`, `description`,`department`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`, `mode`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $i_copy = 0;

    for($ii = 0;$ii<sizeof($_POST['item']);$ii++)
    {
        if($i_copy <= $ii)
            $i_copy =$ii;

        if($_POST['remark'][$ii]=='')
            $remark[$ii] = '#';
        else
            $remark[$ii] = $_POST['remark'][$ii];
            
        if($_POST['description'][$ii]=='')
            $desc[$ii] = '#';
        else
            $desc[$ii] = $_POST['description'][$ii];
        if($_SESSION["role"] == "GM")
            $status = 'Approved By GM';
        else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner" || strpos($_SESSION["a_type"],"manager") !== false)
            $status = 'Approved By Dep.Manager';
        else
            $status = 'waiting';

        $id_job=$_SESSION["request_for"];
        $id_job.=(isset($_POST['job_num'][$ii]))?"|".$_POST['job_num'][$ii]:"";
        $uname=$_SESSION["username"];
        $type='Spare and Lubricant';
        $na_t = str_replace(" ","",$type);
        $item[$ii]=$_POST['item'][$ii];
        $unit[$ii]=$_POST['unit'][$ii];
        $req_quan[$ii]=$_POST['req_quan'][$ii];
        $date_req=date("Y-m-d H:i:s");
        $date_n_b[$ii]=$_POST['date_n_b'][$ii];
        $x =true;
        while($x)
        {
            if(isset($_POST['mode_'.$i_copy]))
            {
                $mode[$ii]=$_POST['mode_'.$i_copy];
                $typee[$ii]=$_POST['type_specific_'.$i_copy];
                $x =false;
            }
            $i_copy++;
        }
        $stmt_company -> bind_param("s", $_SESSION["company"]);
        $stmt_company -> execute();
        $result_company = $stmt_company -> get_result();
        if($result_company->num_rows > 0)
        {
            while($r = $result_company->fetch_assoc()) 
            {
                $comp_type = $r['type'];
                $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
            }
        }
        $has_gm = in_array($_SESSION['department'],$GMs) || in_array("All",$GMs);
        $property_company = $_SESSION['property_company'];
        $procurement_company = $_SESSION['procurement_company'];
        // if($mode[$ii] == 'Internal')
        // {
        //     $property_company = $_SESSION['processing_company'];
        //     $procurement_company = $_SESSION['processing_company'];
        // }
        // else
        // {
        //     $property_company = $_SESSION['property_company'];
        //     $procurement_company = $_SESSION['procurement_company'];
        // }
        $department = $_SESSION['department'];
        $stmt_spare -> bind_param("ssssdssssssssssssss",$id_job, $type, $uname, $item[$ii]
        , $req_quan[$ii], $unit[$ii], $date_req,
         $date_n_b[$ii], $typee[$ii], $remark[$ii]
        , $desc[$ii], $department, $status, $_SESSION['company'], $_SESSION['processing_company'], $property_company, $procurement_company, $_SESSION['finance_company'],$mode[$ii]);

        if($stmt_spare -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            $last_id[$ii] = $conn->insert_id;
            if($_SESSION["role"] == "GM")
            {
                $nxt = "Store";
                $sql2 = "UPDATE requests SET `manager` = ?, `GM` = ?,`next_step` = ?,phase_one = 3 WHERE `request_id` = ?";
                $stmt_GM_request = $conn -> prepare($sql2);
                $stmt_GM_request -> bind_param("sssi", $_SESSION['username'], $_SESSION['username'], $nxt, $last_id[$ii]);
                $stmt_GM_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")
            {
                $nxt = "Store";
                $set = ($_SESSION["role"] == "Director")?"director":"owner";
                $sql2 = "UPDATE requests SET `manager` = ?, `next_step` = ?, `$set` = ?, phase_one = 2 WHERE `request_id` = ?";
                $stmt_dirown_request = $conn -> prepare($sql2);
                $stmt_dirown_request -> bind_param("sssi", $_SESSION['username'], $nxt, $_SESSION['username'], $last_id[$ii]);
                $stmt_dirown_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if(strpos($_SESSION["a_type"],"manager") !== false)
            {
                $nxt = ($has_gm)?"GM":"Store";
                $sql2 = "UPDATE requests SET `manager` = ?,`next_step` = ?,phase_one = 2 WHERE `request_id` = ?";
                $stmt_manager = $conn -> prepare($sql2);
                $stmt_manager -> bind_param("ssi", $_SESSION['username'], $nxt, $last_id[$ii]);
                $stmt_manager -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`) VALUES (?, ?, ?, ?)");
                $stmt-> bind_param("isss",$last_id[$ii], $type, $date_req, $date_req);
                $stmt -> execute();
            }
            else
            {
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) VALUES (?, ?, ?)");
                $stmt-> bind_param("iss",$last_id[$ii], $type, $date_req);
                $stmt -> execute();
            }
            if(sizeof($_FILES['specs_pic'.$_POST['num'][$ii]]) != 0)
            {
                $date=date("Y-m-d H:i:s");
                $file_name = array_filter($_FILES['specs_pic'.$_POST['num'][$ii]]['name']);
                $total_count = count($_FILES['specs_pic'.$_POST['num'][$ii]]['name']);
                $file_size=0;
                $error = "";
                $new_file = "";
                for( $j=0 ; $j < $total_count ; $j++ ) {
                    $file_size = $file_size+$_FILES['specs_pic'.$_POST['num'][$ii]]['size'][$j];
                }
                for( $i=0 ; $i < $total_count ; $i++ ) 
                {
                    $file_tmp = $_FILES['specs_pic'.$_POST['num'][$ii]]['tmp_name'][$i];
                    if ($file_tmp != "")
                    {
                        $uniq = uniqid();
                        $extention = ".".explode('.',$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i])[(sizeof(explode('.',$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i]))-1)];
                        $newFilePath = "../../lpms_uploads/spec-".$na_t.$last_id[$ii]."_".$uniq.$extention;
                        if(move_uploaded_file($file_tmp, $newFilePath))
                        {
                            $new_file .= ($new_file=="")?"":":_:";
                            $new_file.="spec-".$na_t.$last_id[$ii]."_".$uniq.$extention;
                        }
                        else
                        {
                            $error .= ($error=="")?"":"";
                            $error .= "'".$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i]."'";
                        }
                    }
                }
                if($new_file != "")
                {
                    $spec = "";
                    $sql = "INSERT INTO `specification`(`request_id`, `type`, `details`, `pictures`, `date`, `given_by`, `department`) VALUES (? ,? ,? ,? ,? ,? ,?)";
                    $stmt_add_specification = $conn -> prepare($sql);
                    $stmt_add_specification -> bind_param("issssss", $last_id[$ii], $type, $spec, $new_file, $date ,$_SESSION['username'] ,$_SESSION['department']);
                    $result = $stmt_add_specification -> execute();
                    if($result)
                    {
                        $spec_id = $conn->insert_id;
                        $sql = "UPDATE requests SET `spec_dep` = ?, `specification` = ? WHERE `request_id` = ?";
                        $stmt_full_spec = $conn -> prepare($sql);
                        $stmt_full_spec -> bind_param("sii", $_SESSION['department'], $spec_id, $last_id[$ii]);
                        $stmt_full_spec -> execute();
                        $sql_rep = "UPDATE `report` SET `spec_recieved` = ? WHERE `request_id` = ?";
                        $stmt_report_spec = $conn -> prepare($sql_rep);
                        $stmt_report_spec -> bind_param("si", $date, $last_id[$ii]);
                        $stmt_report_spec -> execute();
                    }
                    else
                    {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                }
            }
        }
        else
        {
            echo $conn->error;
        }
    }
}
///////////////////////////////////////////


///////////////// tyre and battery form///////////////
if(isset($_POST['submit_tb_request']))
    {
        unset($_POST['submit_tb_request']);
        $re='yes';
        $sql = "SELECT MAX(`request_id`) As `max_i` FROM `requests` WHERE `request_for` = ? AND `recieved` = ? ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $_POST['plate'],$re);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($max_id);
        $stmt->fetch();
        $stmt->close();
        if(!isset($max_id))
        $max_id=Null;
        if($_POST['remark']=='')
            $remark = '#';
        else
            $remark = $_POST['remark'];
        if($_POST['description']=='')
            $desc = '#';
        else
            $desc = $_POST['description'];
        if($_SESSION["role"] == "GM")
            $status = 'Approved By GM';
        else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner" || strpos($_SESSION["a_type"],"manager") !== false)
            $status = 'Approved By Dep.Manager';
        else
            $status = 'waiting';
        $stmt_tb = $conn->prepare("INSERT INTO `requests`
        (`request_for`, `request_type`, `customer`, `item`,
            `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `to_replace`,
            `Remark`, `description`,`department`, `current_km`, `prev_req`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`, `mode`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $torep = $_POST['repser'][0];
        for($i=1;$i<count($_POST['repser']);$i++)
        {
            $torep.=','.$_POST['repser'][$i];
        }
        $plate=$_POST['plate'];
        $uname=$_SESSION["username"];
        $item=$_POST['item'];
        $type='Tyre and Battery';
        $unit = 'pcs';
        $req_quan=$_POST['req_quan'];
        $date_n_b=$_POST['date_n_b'];
        $current_km=$_POST['current_km'];
        $date_req=date("Y-m-d H:i:s");
        $mode=$_POST['mode'];
        $stmt_company -> bind_param("s", $_SESSION["company"]);
        $stmt_company -> execute();
        $result_company = $stmt_company -> get_result();
        if($result_company -> num_rows > 0)
        {
            while($r = $result_company -> fetch_assoc()) 
            {
                $comp_type = $r['type'];
                $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
            }
        }
        $has_gm = in_array($_SESSION['department'],$GMs) || in_array("All",$GMs);
        if($_POST['mode'] == 'Internal')
        {
            $sql = "SELECT company FROM `vehicle` WHERE `plateno` =? ";
            $stmt2 = $conn_fleet->prepare($sql);
            $stmt2->bind_param("s", $_POST['plate']);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($company_vehicle);
            $stmt2->fetch();
            $stmt2->close();
            $stmt_company -> bind_param("s", $company_vehicle);
            $stmt_company -> execute();
            $result_company = $stmt_company -> get_result();
            $r_comp = $result_company -> fetch_assoc();
            $finance_company = ($r_comp['finance'])?$r_comp['Name']:$r_comp['main'];
            $property_company = $_SESSION['processing_company'];
            $procurement_company = $_SESSION['processing_company'];
        }
        else
        {
                $finance_company = $_SESSION['finance_company'];
                $property_company = $_SESSION['property_company'];
                $procurement_company = $_SESSION['procurement_company'];
        }
        $department = $_SESSION['department'];
        $stmt_tb -> bind_param("ssssdsssssssiisssssss",$plate, $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $torep, $remark, $desc, $department, $current_km, $max_id, $status, $_SESSION['company'], $_SESSION['processing_company'], $property_company, $procurement_company, $finance_company, $mode);
        if($stmt_tb -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            $last_id = $conn->insert_id;
            if($_SESSION["role"] == "GM")
            {
                $nxt = "Store";
                $sql2 = "UPDATE requests SET `manager` = ?, `GM` = ?,`next_step` = ?,phase_one = 3 WHERE `request_id` = ?";
                $stmt_GM_request = $conn -> prepare($sql2);
                $stmt_GM_request -> bind_param("sssi", $_SESSION['username'], $_SESSION['username'], $nxt, $last_id);
                $stmt_GM_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id, $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")
            {
                $nxt = "Store";
                $set = ($_SESSION["role"] == "Director")?"director":"owner";
                $sql2 = "UPDATE requests SET `manager` = ?, `next_step` = ?, `$set` = ?, phase_one = 2 WHERE `request_id` = ?";
                $stmt_dirown_request = $conn -> prepare($sql2);
                $stmt_dirown_request -> bind_param("sssi", $_SESSION['username'], $nxt, $_SESSION['username'], $last_id);
                $stmt_dirown_request -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?)");
                $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                $stmt -> execute();
            }
            else if(strpos($_SESSION["a_type"],"manager") !== false)
            {
                $nxt = ($has_gm)?"GM":"Store";
                $sql2 = "UPDATE requests SET `manager` = ?,`next_step` = ?,phase_one = 2 WHERE `request_id` = ?";
                $stmt_manager = $conn -> prepare($sql2);
                $stmt_manager -> bind_param("ssi", $_SESSION['username'], $nxt, $last_id);
                $stmt_manager -> execute();
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`) VALUES (?, ?, ?, ?)");
                $stmt-> bind_param("isss",$last_id, $type, $date_req, $date_req);
                $stmt -> execute();
            }
            else
            {
                $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) VALUES (?, ?, ?)");
                $stmt-> bind_param("iss",$last_id, $type, $date_req);
                $stmt -> execute();
            }
            // header("location: ".$_SERVER['HTTP_REFERER']);
        }
        else
        {
            echo $conn->error;
        }
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if(isset($_POST['submit_request']))
{
    $type=$_POST['submit_request'];
    unset($_POST['submit_request']);
    $sql = "SELECT * FROM `catagory` where catagory = ?";
    $stmt_catagory = $conn->prepare($sql);
    $stmt_catagory -> bind_param("s", $type);
    $stmt_catagory -> execute();
    $result_catagory = $stmt_catagory -> get_result();
    if($result_catagory -> num_rows > 0)
        while($row = $result_catagory -> fetch_assoc()) 
        {
            $replacement = $row['replacements'];
        }
    if($type == 'Stationary and Toiletaries' || $type == 'Fixed Assets' || $type == 'Miscellaneous')
    {
        $property_company = $_SESSION['processing_company'];
        $procurement_company = $_SESSION['processing_company'];
    }
    else
    {
        $property_company = $_SESSION['property_company'];
        $procurement_company = $_SESSION['procurement_company'];
    }
    if($replacement)
    {
        $rep_count = 0;
        $radios = 0;
        $agg_quan = 0;
        $changed = false;
        $stmt_fixed = $conn->prepare("INSERT INTO `requests`
        (`request_type`, `customer`, `item`, `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `type`, `to_replace`,
        `Remark`, `description`, `spec_dep`, `department`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        for($ii = 0;$ii<sizeof($_POST['item']);$ii++)
        {
            $radios = (!$changed)?$ii:$radios+1;
            if($_POST['remark'][$ii]=='')
                $remark[$ii] = '#';
            else
                $remark[$ii] = $_POST['remark'][$ii];
            if($_POST['description'][$ii]=='')
                $desc[$ii] = '#';
            else
                $desc[$ii] = $_POST['description'][$ii];

            if($_SESSION["role"] == "GM")
                $status = 'Approved By GM';
            else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner" || strpos($_SESSION["a_type"],"manager") !== false)
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';

            while(!isset($_POST['IT_RELATED_'.($radios + 1)]))
                {
                    $changed = true;
                    $radios++;
                }
            // echo "$radios ".$_POST['reason_'.($radios + 1)].":<br>";
            if($_POST['reason_'.($radios + 1)]=='replace')
            {
                $agg_quan +=$_POST['req_quan'][$ii];
                $torep[$ii] = "";
                for($i=$rep_count;$i<$agg_quan;$i++)
                {
                    $torep[$ii].=($torep[$ii] == "")?$_POST['repser'][$i]:','.$_POST['repser'][$i];
                    $rep_count++;
                }
                // echo "|$radios $rep_count -- $torep[$ii]|<br>";
            }
            else
                {
                    $torep = null;
                    $agg_quan++;
                }
            $stmt_company -> bind_param("s", $_SESSION["company"]);
            $stmt_company -> execute();
            $result_company = $stmt_company -> get_result();
            if($result_company -> num_rows > 0)
            {
                while($r = $result_company -> fetch_assoc()) 
                {
                    $comp_type = $r['type'];
                    $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
                }
            }
            $has_gm = in_array($_SESSION['department'],$GMs) || in_array("All",$GMs);
            // foreach($_POST['repser'] as $rp) echo "<br>".sizeof($_POST['repser'])."__$rp<br>";
            $it_related[$ii] = ($_POST['IT_RELATED_'.($radios + 1)] == "yes")?"IT":null;
            $uname=$_SESSION["username"];
            $item[$ii]=$_POST['item'][$ii];
            $ty[$ii] = $_POST['reason_'.($radios + 1)];
            $unit[$ii]=$_POST['unit'][$ii];
            $req_quan[$ii]=$_POST['req_quan'][$ii];
            $date_n_b[$ii]=$_POST['date_n_b'][$ii];
            $date_req=date("Y-m-d H:i:s");

            $department = $_SESSION['department'];
            $stmt_fixed -> bind_param("sssdsssssssssssssss", $type, $uname, $item[$ii], $req_quan[$ii], $unit[$ii], $date_req, $date_n_b[$ii], $ty[$ii], $torep[$ii], $remark[$ii], $desc[$ii], $it_related[$ii], $department, $status, $_SESSION['company'], $_SESSION['processing_company'], $property_company, $procurement_company, $_SESSION['finance_company']);
            if($stmt_fixed -> execute())
            {
                $_SESSION["success"]="Requests Successfully Requested !!";
                $last_id[$ii] = $conn->insert_id;
                if($_SESSION["role"] == "GM")
                {
                    $nxt = ($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store";
                    $sql2 = "UPDATE requests SET `manager` = ?, `GM` = ?,`next_step` = ?,phase_one = 3 WHERE `request_id` = ?";
                    $stmt_GM_request = $conn -> prepare($sql2);
                    $stmt_GM_request -> bind_param("sssi", $_SESSION['username'], $_SESSION['username'], $nxt, $last_id[$ii]);
                    $stmt_GM_request -> execute();
                    if(isset($it_related[$ii]) && $it_related[$ii] == 'IT')
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `sent_for_spec`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt-> bind_param("isssss",$last_id[$ii], $type, $date_req, $date_req, $date_req, $date_req);
                    }
                    else
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?)");
                        $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                    }
                }
                else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")
                {
                    $nxt = ($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store";
                    $set = ($_SESSION["role"] == "Director")?"director":"owner";
                    $sql2 = "UPDATE requests SET `manager` = ?, `next_step` = ?, `$set` = ?, phase_one = 2 WHERE `request_id` = ?";
                    $stmt_dirown_request = $conn -> prepare($sql2);
                    $stmt_dirown_request -> bind_param("sssi", $_SESSION['username'], $nxt, $_SESSION['username'], $last_id[$ii]);
                    $stmt_dirown_request -> execute();
                    if(isset($it_related[$ii]) && $it_related[$ii] == 'IT')
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `sent_for_spec`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt-> bind_param("isssss",$last_id[$ii], $type, $date_req, $date_req, $date_req, $date_req);
                    }
                    else
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?)");
                        $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                    }
                }
                else if(strpos($_SESSION["a_type"],"manager") !== false)
                {
                    $nxt = ($has_gm)?'GM':(($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store");
                    $sql2 = "UPDATE requests SET `manager` = ?,`next_step` = ?,phase_one = 2 WHERE `request_id` = ?";
                    $stmt_manager = $conn -> prepare($sql2);
                    $stmt_manager -> bind_param("ssi", $_SESSION['username'], $nxt, $last_id);
                    $stmt_manager -> execute();

                    $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`) VALUES (?, ?, ?, ?)");
                    $stmt-> bind_param("isss",$last_id[$ii], $type, $date_req, $date_req);
                }
                else
                {
                    $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) VALUES (?, ?, ?)");
                    $stmt-> bind_param("iss",$last_id[$ii], $type, $date_req);
                }
                $stmt -> execute();
                // header("location: ".$_SERVER['HTTP_REFERER']);
            }
            else
            {
                echo $conn->error;
            }
        }
    }
    else
    {
        $stmt_unique = $conn->prepare("INSERT INTO `requests`
        (`request_type`, `customer`, `item`,
        `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
        `Remark`, `description`,
        `department`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        for($ii = 0;$ii<sizeof($_POST['item']);$ii++)
        {
            if($_POST['remark'][$ii]=='')
                $remark[$ii] = '#';
            else
                $remark[$ii] = $_POST['remark'][$ii];
            if($_POST['description'][$ii]=='')
                $desc[$ii] = '#';
            else
                $desc[$ii] = $_POST['description'][$ii];

            if($_SESSION["role"] == "GM")
                $status = 'Approved By GM';
            else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner" || strpos($_SESSION["a_type"],"manager") !== false)
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';

            $uname=$_SESSION["username"];
           
            $item[$ii]=$_POST['item'][$ii];
            $unit[$ii]=$_POST['unit'][$ii];
            $req_quan[$ii]=$_POST['req_quan'][$ii];
            $date_req=date("Y-m-d H:i:s");
            $date_n_b[$ii]=$_POST['date_n_b'][$ii];
            $department = $_SESSION['department'];
            $stmt_company -> bind_param("s", $_SESSION["company"]);
            $stmt_company -> execute();
            $result_company = $stmt_company -> get_result();
            if($result_company -> num_rows > 0)
            {
                while($r = $result_company -> fetch_assoc()) 
                {
                    $comp_type = $r['type'];
                    $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
                }
            }
            $has_gm = in_array($_SESSION['department'],$GMs) || in_array("All",$GMs);

            $stmt_unique -> bind_param("sssdssssssssssss",$type, $uname, $item[$ii], $req_quan[$ii], $unit[$ii], $date_req, $date_n_b[$ii], $remark[$ii], $desc[$ii], $department, $status, $_SESSION['company'], $_SESSION['processing_company'], $property_company, $procurement_company, $_SESSION['finance_company']);
            if($stmt_unique -> execute())
            {
                $_SESSION["success"]="Requests Successfully Requested !!";
                $last_id[$ii] = $conn->insert_id;
                if($type == 'agreement')
                {
                    $sql="INSERT INTO `purchase_order` (`status`,`request_type`,`request_id`, `scale`,`company`,`processing_company`, `property_company`, `procurement_company`,`finance_company`) VALUES ('waiting','agreement',?,'HO',?,?,?,?,?)";
                    $stmt_add_po = $conn -> prepare($sql);
                    $stmt_add_po -> bind_param("isssss", $last_id[$ii], $_SESSION['company'], $_SESSION['processing_company'], $procurement_company, $property_company, $_SESSION['finance_company']);
                    $stmt = $stmt_add_po -> execute();
                    $scale="not set";
                    $stmt2 = $conn->prepare("SELECT MAX(`purchase_order_id`) AS purchase_order_id FROM `purchase_order`");
                    $stmt2->execute();
                    $stmt2->store_result();
                    $stmt2->bind_result($purchase_order_id);
                    $stmt2->fetch();
                    $stmt2->close();
                    $sql = "INSERT INTO `price_information` (`purchase_order_id`, `providing_company`)VALUES (?,?)";
                    $stmt_add_pi = $conn -> prepare($sql);
                    $stmt_add_pi -> bind_param("is", $purchase_order_id, $_POST['vendor'][$ii]);
                    $stmt_ = $stmt_add_pi -> execute();
                }
                if(isset($_POST['num']) && sizeof($_FILES['specs_pic'.$_POST['num'][$ii]]) != 0)
                {
                    $date=date("Y-m-d H:i:s");
                    $file_name = array_filter($_FILES['specs_pic'.$_POST['num'][$ii]]['name']);
                    $total_count = count($_FILES['specs_pic'.$_POST['num'][$ii]]['name']);
                    $file_size=0;
                    $error = "";
                    $new_file = "";
                    for( $j=0 ; $j < $total_count ; $j++ ) {
                        $file_size = $file_size+$_FILES['specs_pic'.$_POST['num'][$ii]]['size'][$j];
                    }
                    for( $i=0 ; $i < $total_count ; $i++ ) 
                    {
                        $file_tmp = $_FILES['specs_pic'.$_POST['num'][$ii]]['tmp_name'][$i];
                        if ($file_tmp != "")
                        {
                            $uniq = uniqid();
                            $na_t=($type=='agreement'?$type:$na_t);
                            $extention = ".".explode('.',$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i])[(sizeof(explode('.',$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i]))-1)];
                            $newFilePath = "../../lpms_uploads/spec-".$na_t.$last_id[$ii]."_".$uniq.$extention;
                            if(move_uploaded_file($file_tmp, $newFilePath))
                            {
                                $new_file .= ($new_file=="")?"":":_:";
                                $new_file.="spec-".$na_t.$last_id[$ii]."_".$uniq.$extention;
                            }
                            else
                            {
                                $error .= ($error=="")?"":"";
                                $error .= "'".$_FILES['specs_pic'.$_POST['num'][$ii]]['name'][$i]."'";
                            }
                        }
                    }
                    if($new_file != "")
                    {
                        $spec = "";
                        $sql = "INSERT INTO `specification`(`request_id`, `type`, `details`, `pictures`, `date`, `given_by`, `department`) VALUES (? ,? ,? ,? ,? ,? ,?)";
                        $stmt_add_specification = $conn -> prepare($sql);
                        $stmt_add_specification -> bind_param("issssss", $last_id[$ii], $type, $spec, $new_file, $date ,$_SESSION['username'] ,$_SESSION['department']);
                        $result = $stmt_add_specification -> execute();
                        if ($result)
                        {
                            $spec_id = $conn->insert_id;
                            if($type == 'agreement')
                            {
                                $sql = "UPDATE requests SET `specification` = ? WHERE `request_id` = ?";
                                $stmt_full_spec = $conn -> prepare($sql);
                                $stmt_full_spec -> bind_param("ii", $spec_id, $last_id[$ii]);
                                $stmt_full_spec -> execute();
                            }
                            else
                            {
                                $sql = "UPDATE requests SET `spec_dep` = ?, `specification` = ? WHERE `request_id` = ?";
                                $stmt_full_spec = $conn -> prepare($sql);
                                $stmt_full_spec -> bind_param("sii", $_SESSION['department'], $spec_id, $last_id[$ii]);
                                $stmt_full_spec -> execute();
                            }
                            $sql_rep = "UPDATE `report` SET `spec_recieved` = ? WHERE `request_id` = ?";
                            $stmt_report_spec = $conn -> prepare($sql_rep);
                            $stmt_report_spec -> bind_param("si", $date, $last_id[$ii]);
                            $stmt_report_spec -> execute();
                        }
                        else
                        {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }
                    }
                }
                if($_SESSION["role"] == "GM")
                {
                    $nxt = ($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store";
                    $sql2 = "UPDATE requests SET `manager` = ?, `GM` = ?,`next_step` = ?,phase_one = 3 WHERE `request_id` = ?";
                    $stmt_GM_request = $conn -> prepare($sql2);
                    $stmt_GM_request -> bind_param("sssi", $_SESSION['username'], $_SESSION['username'], $nxt, $last_id[$ii]);
                    $stmt_GM_request -> execute();
                    if(isset($it_related[$ii]) && $it_related[$ii] == 'IT')
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `sent_for_spec`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt-> bind_param("isssss",$last_id[$ii], $type, $date_req, $date_req, $date_req, $date_req);
                    }
                    else
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `GM_approval_date`) VALUES (?, ?, ?, ?, ?)");
                        $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                    }
                }
                else if($_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")
                {
                    $nxt = ($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store";
                    $set = ($_SESSION["role"] == "Director")?"director":"owner";
                    $sql2 = "UPDATE requests SET `manager` = ?, `next_step` = ?, `$set` = ?, phase_one = 2 WHERE `request_id` = ?";
                    $stmt_dirown_request = $conn -> prepare($sql2);
                    $stmt_dirown_request -> bind_param("sssi", $_SESSION['username'], $nxt, $_SESSION['username'], $last_id[$ii]);
                    $stmt_dirown_request -> execute();
                    if(isset($it_related[$ii]) && $it_related[$ii] == 'IT')
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `sent_for_spec`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt-> bind_param("isssss",$last_id[$ii], $type, $date_req, $date_req, $date_req, $date_req);
                    }
                    else
                    {
                        $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`, `$_SESSION[role]_approval_date`) VALUES (?, ?, ?, ?, ?)");
                        $stmt-> bind_param("issss",$last_id[$ii], $type, $date_req, $date_req, $date_req);
                    }
                }
                else if(strpos($_SESSION["a_type"],"manager") !== false)
                {
                    $nxt = ($has_gm)?'GM':(($type == "Fixed Assets" && $it_related[$ii] = 'IT')?"IT Specification":"Store");
                    $sql2 = "UPDATE requests SET `manager` = ?,`next_step` = ?,phase_one = 2 WHERE `request_id` = ?";
                    $stmt_manager = $conn -> prepare($sql2);
                    $stmt_manager -> bind_param("ssi", $_SESSION['username'], $nxt, $last_id);
                    $stmt_manager -> execute();

                    $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`, `manager_approval_date`) VALUES (?, ?, ?, ?)");
                    $stmt-> bind_param("isss",$last_id[$ii], $type, $date_req, $date_req);
                }
                else
                {
                    $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) VALUES (?, ?, ?)");
                    $stmt-> bind_param("iss",$last_id[$ii], $type, $date_req);
                }
                $stmt -> execute();
            }
            else
            {
                echo $conn->error;
            }
        }
    }
}
///////////////////////////////////////
/////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////Write up report after insert//////////////////////////////

    if(isset($_SESSION["success"]))
    {
        if($_SESSION["success"]==="Requests Successfully Requested !!")
        {
            if(is_array($_POST['item']))
            {
                if($status == 'Approved By GM')
                    {
                        $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = '$_SESSION[company]'");
                        $data_email = "<strong>There is a purchase request by a director waiting for store check please review in a timely manner</strong><br>";
                        $subject_email = "There is a purchase request by a director waiting for store check";
                    }
                    else if($status == 'Approved By Dep.Manager')
                    {
                        $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where (`managing` LIKE '%$_SESSION[department]%' OR `managing` LIKE '%All Departments%') AND company = '$_SESSION[company]'");
                    }else
                    $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `department`='$_SESSION[department]' AND ((role = 'manager' OR `type` LIke '%manager%') OR `role`='Director') AND company = '$_SESSION[company]'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email,$man_name);
                $stmt2->fetch();
                $stmt2->close();
                $data_email = "";
                $cell_style="border:1px solid #dddddd;text-align: left; padding:8px";
                $table="<table style='font-family:arial,sans-serif; font-size:14px; border-collapse: collapse; width: 100%;'>
                <tr>
                <th style='$cell_style'>NO.</th>
                <th style='$cell_style'>Item</th>
                <th style='$cell_style'>Requested By</th>
                <th style='$cell_style'>Quantity</th>
                <th style='$cell_style'>Date Needed By</th>
                </tr>
                ";
                
                $x=1;
                $data_email .= "<strong>A $type purchase request was submitted to your department </strong><br><br>";
                for($ii = 0;$ii<sizeof($_POST['item']);$ii++)
                {
                    $na_t=str_replace(" ","",$type);
                   
                            $mult = (sizeof($_POST['item'])>1)?"Multiple":"$item[$ii]";
                        $subject_email = "$mult purchase request in ".$_SESSION['department']." Department";
                        $table.=" <tr>
                        <td style='$cell_style'>$x</td>
                        <td style='$cell_style' >".$item[$ii]."</td>
                        <td style='$cell_style' >".str_replace("."," ",$uname)."</td>
                        <td style='$cell_style' >".$req_quan[$ii]." ".$unit[$ii]."</td>
                        <td style='$cell_style' >".date("d-M-Y", strtotime($date_n_b[$ii]))."</td>
                      </tr>";
                      $x++;
                      if($status!='Approved By GM')
                        $reason =($status == 'waiting')?"open_req_".$na_t."_".$last_id[$ii]."_requested":"open_req_".$na_t."_".$last_id[$ii]."_director_approve";
                       else
                        $reason = "open_req_".$na_t."_".$last_id[$ii]."_store";
                       
                }
                
                $table.='</table>';
                $data_email.=$table;
                $data_email.="<br>Please review the request as soon as possible in a timely manner<br><br><br>";
                $send_to = $email.",".$man_name;
                $cc =""; $bcc = ""; $tag = $man_name;
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason-> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason-> execute();
                $email_id = $conn->insert_id;
                $page_to = 'requests/managerApproval.php';
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
            else
            {
                if($status == 'Approved By GM')
                    {
                        $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `department` = 'Property' AND `role` = 'Store' AND company = '$_SESSION[company]'");
                        $data_email = "<strong>There is a purchase request by a director waiting for store check please review in a timely manner</strong><br>";
                        $subject_email = "There is a Purchase Request By a Director waiting for Store Check";
                        $reason = "open_req_".$na_t."_".$req_id."_store";
                 
                    }
                else if($status == 'Approved By Dep.Manager')
                    $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `managing` LIKE '%$_SESSION[department]%' AND company = '$_SESSION[company]'");
                else
                    $stmt2 = $conn->prepare("SELECT `email`,`Username` FROM `account` where `department`='$_SESSION[department]' AND (role = 'manager' OR `type` LIke '%manager%') AND company = '$_SESSION[company]'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email, $man_name);
                $stmt2->fetch();
                $stmt2->close();
                
                $na_t=str_replace(" ","",$type);
                if($status != 'Approved By GM')
                {
                    $reason =($status == 'waiting')?"open_req_".$na_t."_".$last_id."_requested":"open_req_".$na_t."_".$last_id."_director_approve";

                    $subject_email = "$item purchase request in ".$_SESSION['department']." Department";
                    $users =str_replace("."," ",$uname);
                    $data_email = "
                    <strong>A purchase request was submitted to your department <strong><br>
                    <ul>
                        <li><strong> Catagory - </strong> $type <br></li>
                        <li><strong> Item - </strong> $item <br></li>
                        <li><strong> Requested By - </strong> $users <br></li>
                        <li><strong> Quantity - </strong> $req_quan $unit<br></li>
                        <li><strong> Date Needed By - </strong> ".date("d-M-Y", strtotime($date_n_b))." <br></li>
                    </ul>
                    please review the request as soon as possible in a timely manner<br><br><br>
                    Note : Request is also being checked in stock simultaniously
                    ";
                }
                else
                    $reason = "open_req_".$na_t."_".$last_id."_store";

                $send_to = $email.",".$man_name;
                $cc =""; $bcc = ""; $tag = $man_name;
                $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $user=($_SESSION['username'].":-:".$_SESSION['position']);
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                $stmt_email_reason -> execute();
                $email_id = $conn->insert_id;
                $page_to = 'requests/managerApproval.php';
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
            // if($status == 'Approved By Dep.Manager' && !(is_array($_POST['item'])))
            // {
            //     header("location: allphp.php?btntype="."approve_".$na_t."_".$last_id);
            // }
            // else
            include "../group_reqeusts.php";
            header("location: ".$_SERVER['HTTP_REFERER']);
                // send_auto_email($subject_email,$data_email,$email.",".$uname);
        }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////retry///////////////////////////////////////////////////////

$sql = "SELECT * FROM requests WHERE `status`='Rejected By Manager' OR `status`='Rejected'";
$stmt_rejected = $conn->prepare($sql);
$stmt_rejected -> execute();
$result_rejected = $stmt_rejected -> get_result();
if($result_rejected->num_rows>0)
    while($row = $result_rejected->fetch_assoc())
    {
        $na_t = na_t_to_type($conn,$row['request_type']);
        $name_r=$na_t."_redo_";
        if(isset($_GET[$name_r.$row['request_id']]))
        {
            $sql2 = "UPDATE requests SET `status`='waiting', `next_step`='Manager' WHERE `request_id` = ?";
            $stmt_fresh_start = $conn -> prepare($sql2);
            $stmt_fresh_start -> bind_param("i", $row['request_id']);
            $result = $stmt_fresh_start -> execute();
            if ($result)
            {
                $_SESSION["success"]="Reactivated";
                header("location: ".$_SERVER['HTTP_REFERER']);
            }
        }
    }

////////////////////////////////////////////////////////////edit///////////////////////////////////////////////////////
$sql = "SELECT * FROM requests WHERE `status`='Rejected By Manager' OR `status`='Rejected' OR `status`='waiting'";
$stmt_rejected = $conn->prepare($sql);
$stmt_rejected -> execute();
$result_rejected = $stmt_rejected -> get_result();
if($result_rejected -> num_rows > 0)
{
    while($row = $result_rejected -> fetch_assoc()) 
    {
        $na_t=str_replace(" ","",$row['request_type']);
        if(isset($_GET[$na_t.$row['request_id']]))
        {
            if($row['request_type']=="Consumer Goods")
            {
                $idd = explode("|",$row['request_for'])[0];
                if($row['request_for'] == '0')
                    $stmt2 = $conn->prepare("SELECT `Name` FROM `project` where `project_id`='".$row['request_for']."'");
                else
                {
                    $stmt2 = $conn_pms->prepare("SELECT `project_name` FROM `projects` where `id`='$idd'");
                }
            }
            else if($row['request_type']=="Spare and Lubricant")
                $stmt2 = $conn_ws->prepare("SELECT `description` FROM `description` where `iden`='".$row['request_for']."'");
            else if($row['request_type']=="Tyre and Battery") 
                $for=$row['request_for'];
            else
                $for = $row['department'];
            if($row['request_type']=="Consumer Goods" || $row['request_type']=="Spare and Lubricant")
            {
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($for);
                $stmt2->fetch();
                $stmt2->close();
            }
            $stmt_unique = $conn->prepare("INSERT INTO `requests_nolonger`(`type`, `request_id`, `requested_for`, `requested_by`, `item`, `date_requested`, `date_needed_by`, `quantity`, `company`, `Remark`, `changed_by`, `date`, `operation`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $date=date("Y-m-d H:i:s");
            $op="Edit";
            $stmt_unique -> bind_param("sisssssssssss",$row['request_type'] ,$row['request_id'], $for, $row['customer'], $row['item'], $row['date_requested'], $row['date_needed_by'], $row['requested_quantity'], $_SESSION['company'], $row['Remark'], $_SESSION['username'], $date, $op);
            $stmt_unique -> execute();

            $date = date("Y-m-d H:i:s");
            $status = 'waiting';
            $next = 'Manager';
            if($row['request_type']=="Tyre and Battery" || $row['request_type']=="Fixed Assets")
            {
                if(!isset($_GET['rep']))
                    $replace = null;
                else
                {
                    $replace ='';
                    foreach($_GET['rep'] as $to_rep)
                    {
                        $replace .= $to_rep . ",";
                    }
                    $replace = rtrim($replace,',');
                }
                $stmt = $conn->prepare("UPDATE requests SET `item`=?, `requested_quantity`=?, `date_needed_by`=?, `date_requested`=?, `status`=?, `Remark`=?, `description`=?, `to_replace`=?, `next_step`=? WHERE `request_id`=?");
                $stmt -> bind_param("sdsssssssi" ,$_GET['item'] ,$_GET['req_quan'] ,$_GET['date_n_b'] ,$date ,$status ,$_GET['remark'] ,$_GET['description'] ,$replace ,$next ,$row['request_id']);
                // echo $_GET['item'] ,"<br>",intval($_GET['req_quan']) ,"<br>",$_GET['date_n_b'] ,"<br>",$date ,"<br>",$status ,"<br>",$_GET['remark'] ,"<br>",$_GET['description'] ,"<br>",$replace ,"<br>",$nxt ,"<br>",$row['request_id'];
            }
            else
            {
                $stmt = $conn->prepare("UPDATE requests SET `item`=?, `requested_quantity`=?, `date_needed_by`=?, `date_requested`=?, `status`=?, `Remark`=?, `description`=?, `next_step`=? WHERE `request_id`=?");
                $stmt -> bind_param("sdssssssi" ,$_GET['item'] ,$_GET['req_quan'] ,$_GET['date_n_b'] ,$date ,$status ,$_GET['remark'] ,$_GET['description'] ,$next ,$row['request_id']);
            }
            $stmt -> execute();
            $_SESSION["success"]="Edited";
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////Delete////////////////////////////////////////////////////////////////////
$sql = "SELECT * FROM requests WHERE `status`='waiting'";
$stmt_waiting = $conn->prepare($sql);
$stmt_waiting -> execute();
$result_waiting = $stmt_waiting -> get_result();
if($result_waiting->num_rows>0)
    while($row = $result_waiting->fetch_assoc())
    {
        $ty = $row['request_type'];
        $na_t=str_replace(" ","",$ty);
        if(isset($_GET["Delete_".$na_t."_".$row['request_id']]))
        {
            if($row['request_type']=="Consumer Goods")
            {
                $idd = explode("|",$row['request_for'])[0];
                if($row['request_for'] == '0')
                    $stmt2 = $conn->prepare("SELECT `Name` FROM `project` where `project_id`='".$row['request_for']."'");
                else
                {
                    $stmt2 = $conn_pms->prepare("SELECT `project_name` FROM `projects` where `id`='$idd'");
                }
            }
            else if($row['request_type']=="Spare and Lubricant")
                $stmt2 = $conn_ws->prepare("SELECT `description` FROM `description` where `iden`='".$row['request_for']."'");
            else if($row['request_type']=="Tyre and Battery")
                $for=$row['request_for'];
            else
                $for = $row['department'];
            if($row['request_type']=="Consumer Goods" || $row['request_type']=="Spare and Lubricant")
            {
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($for);
                $stmt2->fetch();
                $stmt2->close();
            }
            $stmt_unique = $conn->prepare("INSERT INTO `requests_nolonger`(`type`, `request_id`, `requested_for`, `requested_by`, `item`, `date_requested`, `date_needed_by`, `quantity`, `company`, `Remark`, `changed_by`, `date`, `operation`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $date=date("Y-m-d H:i:s");
            $op="Delete";
            $stmt_unique -> bind_param("sisssssssssss",$row['request_type'] ,$row['request_id'], $for, $row['customer'], $row['item'], $row['date_requested'], $row['date_needed_by'], $row['requested_quantity'], $_SESSION['company'], $row['Remark'], $_SESSION['username'], $date, $op);
            $stmt_unique -> execute();
            $sqll = "DELETE from requests where `request_id` = ?";
            $stmt_delete_request = $conn -> prepare($sqll);
            $stmt_delete_request -> bind_param("i", $row['request_id']);
            $result = $stmt_delete_request -> execute();
            if ($result) {
                $_SESSION["success"]="Deleted";
                header("location: ".$_SERVER['HTTP_REFERER']);
            }
        }
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