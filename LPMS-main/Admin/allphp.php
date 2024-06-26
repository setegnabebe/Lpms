<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
if(isset($_SESSION['username']))
{
if(isset($_POST['create_account']))
{
    $date_created = date("Y-m-d H:i:s");
    $account_type = $_POST['type'][0];
    for($i = 1 ; $i < sizeof($_POST['type']);$i++)
    {
        $account_type .= ",".$_POST['type'][$i];
    }
    $man_deps = "";
    if(isset($_POST['man_deps']))
    for($i = 0 ; $i < sizeof($_POST['man_deps']);$i++)
    {
        $man_deps .= ($man_deps =="")?$_POST['man_deps'][$i]:",".$_POST['man_deps'][$i];
    }
    $phone = "+251".$_POST['tel'];
    $add=(isset($_POST['additional_role']) && $_POST['additional_role']==1)?1:'';
    $status = "waiting";
    $password = md5($_POST['password']);
    $stmt_account = $conn->prepare("INSERT INTO `account`(`Username`, `phone`, `email`, `password`, `company`, `department`, `type`, `role`,`additional_role`, `managing`,`status`, `cheque_percent`, `creation_date`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt_account -> bind_param("sssssssssssss",$_POST['uname'],$phone,$_POST['email'],$password,$_POST['company'],$_POST['department'],$account_type,$_POST['role'] ,$add,$man_deps, $status,$_POST['percent'] ,$date_created);
    if ($stmt_account -> execute()) 
    {
        $cc =""; $bcc = ""; $tag = str_replace("."," ",$_POST['uname']);
        $_SESSION['success'] = 'Account Successfully Created';
        $subject_email = "Congratulations an account was created for you";
        $data_email = "
        An account was successfully created for you on LPMS (Local Procurement Management System)<br>
        <strong>Please visit the website either by
        <hr>
        <p style='text-align:center;'><strong>Going to portal.hagbes.com and then clicking On LPMS </strong><br></p>
        <p style='text-align:center;'>OR<br></p>
        <p style='text-align:center;'><strong>Going to lpms.hagbes.com directly</strong><br></p>
        <p><strong>Your Username is <span style='color:blue;'>$_POST[uname]</span></strong><br></p>
        <p><strong>Password is <span style='color:blue;'>123</span>(Be sure to change it when you log in)</strong><br></p>
        ";
        $send_to = $_POST['email'].",".$tag;
        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
        $email_type = NULL;
        $sent_from='';
        $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
        $stmt_email -> execute();
    } 
    else
    {
        echo $conn->error;
        $_SESSION['failed_attempt'] = $conn->error;
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['test-sms']))
{
    $content = "Test -> Content:".$_POST['test-content'];
    $phone = "+251".$_POST['tel'];
    $query4s="INSERT INTO outbox (DestinationNumber,TextDecoded,CreatorID) VALUES (?,?,?)";
    $stmt_add_sms = $conn_sms->prepare($query4s);
    $stmt_add_sms -> bind_param("sss", $phone, $content, $_SESSION['username']);
    $stmt_add_sms -> execute();
    $_SESSION['success'] = 'SMS Sent';
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['test-email']))
{
    $cc =""; $bcc = ""; $tag = "";
    $subject_email = "This is a Test Email";
    $data_email = $_POST['test-content'];
    $send_to = $_POST['email'].",".(explode("@",$_POST['email'])[0]);
    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
    $email_type = NULL;
    $sent_from=$_SESSION['username'];
    $stmt_email -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $sent_from, $email_type);
    $stmt_email -> execute();
    $_SESSION['success'] = 'Test Email Sent';
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['adjust_min']))
{
    $stmtStockUpdate = $conn->prepare("UPDATE `store` set `minimum_stock_level` = ? WHERE `minimum_stock_level` = ?");
    $stmtStockUpdate -> bind_param("ii",$_POST['min_stock'],$_POST['adjust_min']);
    if ($stmtStockUpdate -> execute()) 
    {
        $_SESSION['success'] = 'Minimum Stock Level Updated';
    }
    else
    {
        $_SESSION['failed_attempt'] = 'Update Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['adjust_logout']) || isset($_POST['adjust_m_limit']) || isset($_POST['adjust_surveyExpire']))
{
    $sql_settings = "SELECT * from admin_settings order by id Desc Limit 1";
    $stmt_settings = $conn -> prepare($sql_settings);
    $stmt_settings -> execute();
    $result_settings = $stmt_columns -> get_result();
    if($result_settings->num_rows>0)
        $row = $result_settings->fetch_assoc();
    $logout_time_min = (isset($_POST['adjust_logout']))?$_POST['logout_time']:((isset($row))?$row['logout_time_min']:"30");
    $month_limit_consumer_good = (isset($_POST['adjust_m_limit']))?$_POST['m_limit']:((isset($row))?$row['month_limit_consumer_good']:"2");
    $auto_days = (isset($_POST['adjust_m_limit']))?$_POST['auto_days']:((isset($row))?$row['pms_auto_request']:"21");
    $surveyLimit = (isset($_POST['adjust_surveyExpire']))?"'".$_POST['surveyLimit']."'":((isset($row))?"'".$row['surveyLimit']."'":"current_date");
    $surveyLimit = (isset($_POST['adjust_surveyExpire']) && isset($_POST['no_expire']))?"NULL":$surveyLimit;
    
    $stmt_logout = $conn->prepare("INSERT into `admin_settings` (`logout_time_min`,`month_limit_consumer_good`,`pms_auto_request`,`surveyLimit`) VALUES (?,?,?,?)");
    $stmt_logout -> bind_param("iiis",$logout_time_min,$month_limit_consumer_good,$auto_days,$surveyLimit);
    if ($stmt_logout -> execute()) 
    {
        $_SESSION['success'] = (isset($_POST['adjust_logout']))?'Auto Logout Time Updated':((isset($_POST['adjust_m_limit']))?'Consumer Good Month Limit Updated':'Survey Limit Updated');
    }
    else
    {
        $_SESSION['failed_attempt'] = 'Update Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['edit_account']))
{
    $sql_acc = "SELECT * FROM account where `Username` = ?";
    $stmt_acc = $conn->prepare($sql_acc); 
    $stmt_acc->bind_param("s", $_POST['uname']);
    $stmt_acc->execute();
    $result_acc = $stmt_acc->get_result();
    $row_acc = $result_acc->fetch_assoc();
    ///////////stoping point
    if($_POST['password'] == '')
        $pass = $row_acc['password'];
    else 
        $pass = md5($_POST['password']);
        
    $pre_deps = explode(",",$row_acc['managing']);
    $man_deps = "";
    $remove_deps = explode(",",$_POST['to_remove_dep']);
    for($i = 0 ; $i < sizeof($pre_deps);$i++)
    {
        if(!in_array($pre_deps[$i],$remove_deps))
            $man_deps .= ($man_deps =="")?$pre_deps[$i]:",".$pre_deps[$i];
    }
    $left_deps = explode(",",$man_deps);
    if(isset($_POST['man_deps']))
        for($i = 0 ; $i < sizeof($_POST['man_deps']);$i++)
        {
            if(!in_array($_POST['man_deps'][$i],$left_deps))
                $man_deps .= ($man_deps =="")?$_POST['man_deps'][$i]:",".$_POST['man_deps'][$i];
        }
    
    $pre_acc_types = explode(",",$row_acc['type']);
    $account_type = "";
    $remove_acc_type = explode(",",$_POST['to_remove_acc_type']);
    for($i = 0 ; $i < sizeof($pre_acc_types);$i++)
    {
        if(!in_array($pre_acc_types[$i],$remove_acc_type))
            $account_type .= ($account_type =="")?$pre_acc_types[$i]:",".$pre_acc_types[$i];
    }
    $left_acc_types = explode(",",$account_type);
    if(isset($_POST['type']))
        for($i = 0 ; $i < sizeof($_POST['type']);$i++)
        {
            if(!in_array($_POST['type'][$i],$left_acc_types))
                $account_type .= ($account_type =="")?$_POST['type'][$i]:",".$_POST['type'][$i];
        }

    if($_POST['password'] != '')
    {
        $date_done=date("Y-m-d H:i:s");
        $uniq = "Updated by Admin (".$_SESSION['username'].")";
        $date_n_password = $date_done.":-:".$row_acc['password'];
        $stmt_password = $conn->prepare("INSERT INTO `forgot_password_links`(`Requested_by`, `Sent_to`, `Link`, `date`) VALUES (?,?,?,?)");
        $stmt_password -> bind_param("ssss",$row_acc['Username'],$row_acc['email'],$uniq,$date_n_password);
        $stmt_password -> execute();
    }
    $date_created = date("Y-m-d H:i:s");
    $phone = "+251". $_POST['tel'];
    $stmt = $conn->prepare("UPDATE `account` SET `Username`=?, `phone`=?, `email`=?, `password`=?, `company`=?, `department`=?, `type`=?, `role`=?, `managing`=?, `cheque_percent`=? WHERE `Username`=?");
    $stmt -> bind_param("sssssssssss", $_POST['uname'], $phone, $_POST['email'], $pass,$_POST['company'],$_POST['department'],$account_type,$_POST['role'],$man_deps,$_POST['percent'],$_POST['uname']);
    $stmt -> execute();

    $_SESSION['success'] = "Account Successfully Edited";
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['uname']))
{
    $stmt = $conn->prepare("UPDATE `account` SET `status`=? WHERE `Username`=?");
    $stmt -> bind_param("ss",$_GET['stat'] ,$_GET['uname']);
    $stmt -> execute();
    unset($_GET['uname']);
}
if(isset($_GET['create_project']))
{
    $sql = "INSERT INTO `project` (`Name`) VALUES (?)";
    $stmt_project_add = $conn->prepare($sql);
    $stmt_project_add -> bind_param("s",$_GET['project']);
    if ($stmt_project_add -> execute()) 
    {
        $_SESSION['success'] = 'Project Successfully Created';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['addAnnouncement']))
{
    $sql = "INSERT INTO `announcement` (`announcement`, `announcedBy`) VALUES (?,?)";
    $stmt_announcement_add = $conn_mrf->prepare($sql);
    $stmt_announcement_add -> bind_param("ss", $_GET['announcement'], $_SESSION['username']);
    if ($stmt_announcement_add -> execute()) 
    {
        $_SESSION['success'] = 'Announcement Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['changeAnnouncement']))
{
    $stmt = $conn_mrf->prepare("UPDATE `announcement` SET `status`=? WHERE `id`=?");
    $stmt -> bind_param("ss",$_GET['status'] ,$_GET['changeAnnouncement']);
    $stmt -> execute();
}
if(isset($_POST['change_logo']))
{
    $name = str_replace(" ","",$_POST['change_logo']);
    $file_name = $_FILES['changed_logo']['name'];
    $file_size=0;
    $error = "";
    $file_size = $file_size+$_FILES['changed_logo']['size'];
    $file_tmp = $_FILES['changed_logo']['tmp_name'];
    if($file_tmp != "")
    {
        $temp_name = $name."logo.".explode(".",$file_name)[1];
        $newFilePath = "../img/".$temp_name;
        if(move_uploaded_file($file_tmp, $newFilePath))
        {
            $new_file =$temp_name;
            $stmt = $conn->prepare("UPDATE `company` SET `logo`=? WHERE `Name`=?");
            $stmt -> bind_param("ss",$temp_name ,$_POST['change_logo']);
            $stmt -> execute();
            $_SESSION['success'] = "Logo changed Successfully";
        }
        else
        {
            $error .= ($error=="")?"":"";
            $error .= "'".$_FILES['changed_logo']['name']."'";
            echo $error;
        }
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['change_privilege']))
{
    if(isset($_POST['privilege_all']))
        $dep = "All";
    else
    {
        $dep = "";
        for($i = 0 ; $i < sizeof($_POST['privilege']);$i++)
        {
            $dep .= ($dep == "")?$_POST['privilege'][$i]:",".$_POST['privilege'][$i];
        }
    }
    $description = str_replace("'","'",$_POST['catagory_description']);
    $sql = "UPDATE `catagory` set `display_name` = ?,`privilege` = ?,`description` = ? WHERE `catagory` = '?'";
    $stmt_catagoryEdit = $conn->prepare($sql);
    $stmt_catagoryEdit -> bind_param("ssss",$_POST['display_name'] ,$dep ,$description ,$_POST['change_privilege']);
    $stmt_catagoryEdit -> execute();
    $_SESSION['success'] = 'Privilege & Display Name Successfully Updated';
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['create_catagory']))
{
    $inde = (isset($_POST['replacement']))?$_POST['replacement']:0;
    $name = str_replace(" ","",$_POST['catagory']);
    $file_name = $_FILES['catagory_img']['name'];
    $file_size=0;
    $error = "";
    $file_size = $file_size+$_FILES['catagory_img']['size'];
    $file_tmp = $_FILES['catagory_img']['tmp_name'];
    $description = str_replace("'","'",$_POST['catagory_description']);
    if($file_tmp != "")
    {
        $temp_name = $name.".".explode(".",$file_name)[1];
        $newFilePath = "../img/".$temp_name;
        if(move_uploaded_file($file_tmp, $newFilePath))
        {
            $new_file =$temp_name;
            if(isset($_POST['privilege_all']))
                $dep = "All";
            else
            {
                $dep = "";
                for($i = 0 ; $i < sizeof($_POST['privilege']);$i++)
                {
                    $dep .= ($dep == "")?$_POST['privilege'][$i]:",".$_POST['privilege'][$i];
                }
            }
            $sql = "INSERT INTO `catagory`(`catagory`,`display_name`,`description`, `image`, `path`, `replacements`, `privilege`) VALUES (?,?,?,?,?,?,?)";
            $stmt_catagory = $conn->prepare($sql);
            $stmt_catagory -> bind_param("sssssss",$_POST['catagory'] ,$_POST['display_name'] ,$description ,$temp_name ,$_POST['path'] ,$inde ,$dep);
            if ($stmt_catagory -> execute()) 
            {
                $_SESSION['success'] = 'Catagory Successfully Added';
            } 
            else
            {
                $_SESSION['failed_attempt'] = 'Failed';
            }
        }
        else
        {
            $error .= ($error=="")?"":"";
            $error .= "'".$_FILES['catagory_img']['name']."'";
            $_SESSION['success'] = 'Image Not Compatable';
        }
    } 
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['change_catagory']))
{
    $name = $_POST['change_catagory'];
    $file_name = $_FILES['changed_catagory']['name'];
    $file_size=0;
    $error = "";
    $file_size = $file_size+$_FILES['changed_catagory']['size'];
    $file_tmp = $_FILES['changed_catagory']['tmp_name'];
    if($file_tmp != "")
    {
        $temp_name = $name.".".explode(".",$file_name)[1];
        $newFilePath = "../img/".$temp_name;
        if(move_uploaded_file($file_tmp, $newFilePath))
        {
            $new_file =$temp_name;
            $stmt_catIcon = $conn->prepare("UPDATE `catagory` SET `image`=? WHERE `catagory`=?");
            $stmt_catIcon -> bind_param("ss",$temp_name ,$_POST['change_catagory']);
            $stmt_catIcon -> execute();
            $_SESSION['success'] = "Logo changed Successfully";
        }
        else
        {
            $error .= ($error=="")?"":"";
            $error .= "'".$_FILES['changed_logo']['name']."'";
            echo $error;
        }
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_POST['create_company']))
{
    $IT = (isset($_POST['has_IT']))?1:0;
    $property = (isset($_POST['has_property']))?1:0;
    $procurement = (isset($_POST['has_procurement']))?1:0;
    $purchasers = (isset($_POST['has_purchasers']))?1:0;
    $finance = (isset($_POST['has_finance']))?1:0;
    $cheque_signatory = (isset($_POST['has_cheque']))?1:0;
    $name = str_replace(" ","",$_POST['company']);
    $file_name = $_FILES['logo']['name'];
    $file_size=0;
    $error = "";
    $file_size = $file_size+$_FILES['logo']['size'];
    $file_tmp = $_FILES['logo']['tmp_name'];
    if($file_tmp != "")
    {
        $temp_name = $name."logo.".explode(".",$file_name)[1];
        $newFilePath = "../img/".$temp_name;
        if(move_uploaded_file($file_tmp, $newFilePath))
        {
            $new_file =$temp_name;
        }
        else
        {
            $error .= ($error=="")?"":"";
            $error .= "'".$_FILES['logo']['name']."'";
            echo $error;
        }
    }
    else
    {
        $temp_name = "Hagbeslogo.jpg";
    }
    $sql = "INSERT INTO `comp`(`Name`, `type`, `main`, `logo`, `IT`, `property`, `procurement`, `purchasers`, `finance`, `cheque_signatory`) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmt_add_company = $conn_fleet->prepare($sql);
    $stmt_add_company -> bind_param("ssssiiiiii",$_POST['company'] ,$_POST['type'] ,$_POST['main_co'] ,$temp_name ,$IT ,$property ,$procurement ,$purchasers ,$finance ,$cheque_signatory);
    if ($stmt_add_company -> execute()) 
    {
        $_SESSION['success'] = 'Company Successfully Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['new_department']))
{
    $sql = "INSERT INTO `department` (`Name`) VALUES (?)";
    $stmt_department = $conn->prepare($sql);
    $stmt_department -> bind_param("s",$_GET['dep']);
    if ($stmt_department -> execute()) 
    {
        $_SESSION['success'] = 'Department Successfully Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['new_bank']))
{
    $sql = "INSERT INTO `banks` (`bank`,`added_by`) VALUES (?,?)";
    $stmt_bank = $conn->prepare($sql);
    $stmt_bank -> bind_param("ss",$_GET['bank'], $_SESSION['username']);
    if ($stmt_bank -> execute()) 
    {
        $_SESSION['success'] = 'Bank Successfully Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['new_type']))
{
    $sql = "INSERT INTO `account_types` (`name`) VALUES (?)";
    $stmt_types = $conn->prepare($sql);
    $stmt_types -> bind_param("s",$_GET['type_add']);
    if ($stmt_types -> execute()) 
    {
        $_SESSION['success'] = 'Account Type Successfully Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['new_role']))
{
    $sql = "INSERT INTO `roles` (`name`) VALUES (?)";
    $stmt_roles = $conn->prepare($sql);
    $stmt_roles -> bind_param("s",$_GET['role_add']);
    if ($stmt_roles -> execute()) 
    {
        $_SESSION['success'] = 'Role Successfully Added';
    } 
    else
    {
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['amend_limit']))
{
    if($_GET['min_app_radio'] == "percentage")
        $min_app = $_GET['min_app'];
    else
    {
        $amou = $_GET['Amount_app'];
        $tot = $_GET['Total_app'];
        $min_app = ($amou / $tot)*100;
    }
    $sql = "INSERT INTO `limit_ho` (`company`,`cheque_limit`,`amount_limit`,`amount_limit_top`,`Vat`,`minimum_approval`,`petty_cash`,`perdiem_pettycash`) VALUES 
    (?,?,?,?,?,?,?,?)";
    $stmt_limits = $conn->prepare($sql);
    $stmt_limits -> bind_param("siiiiiii",$_GET['company'], $_GET['cheque_limit'], $_GET['limit'], $_GET['limit_top'], $_GET['Vat'], $min_app, $_GET['cash_limit'],$_GET['perdiem_cash_limit'] );
    if ($stmt_limits -> execute()) 
    {
        $_SESSION['success'] = 'Limit Set';
    } 
    else
    {
        echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['add_tax']))
{
$user=$_SESSION['username'];

    $sql = "INSERT INTO `tax` (`tax_name`,`value`,`createdBy`) VALUES (?,?,?)";
    $stmt_tax = $conn->prepare($sql);
    $stmt_tax -> bind_param("sis",$_GET['tax_name'], $_GET['value'], $user);
    if ($stmt_tax -> execute()) 
    {
        $_SESSION['success'] = 'Tax information has been added';
    } 
    else
    {
        echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
        $_SESSION['success'] = 'Failed';
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['tax_id']) && $_GET['tax_id']){
    echo 1;
}
if(isset($_POST["add_doc"]))
{
    $file_size=0;
    $file_size = $_FILES['docs']['size'];
    $file_tmp = $_FILES['docs']['tmp_name'];
    if ($file_tmp != "")
    {
        $uniq = uniqid();
        $extention = ".".explode('.',$_FILES['docs']['name'])[(sizeof(explode('.',$_FILES['docs']['name']))-1)];
        $newFilePath = "../../lpms_uploads/Document-".$uniq.$extention;
        if(move_uploaded_file($file_tmp, $newFilePath))
        {
            $file_name = "Document-".$uniq.$extention;
        }
        else
        {
            $error = "'".$_FILES['docs']['name']."'";
        }
    }
    if($error == "")
    {
        $sql = "INSERT INTO `documentations`(`name`, `file`,`uploaded_by`) VALUES (?,?,?)";
        $stmt_documents = $conn->prepare($sql);
        $stmt_documents -> bind_param("sss",$_POST['doc_name'], $file_name, $_SESSION['username']);
        $stmt_documents -> execute();
        $_SESSION["success"]="Document Uploaded Successfully";
    }
    else
    {
        $_SESSION["success"] = "File not Uploaded";
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_GET['project_name']) || isset($_GET['document_name']))
{
    if(isset($_GET['project_name']))
    {
        $name = $_GET['project_name'];
        $stmt = $conn->prepare("UPDATE `project` SET `status`=? WHERE `Name`=?");
        $stmt -> bind_param("ss",$_GET['stat'] ,$name);
        $stmt -> execute();
        unset($_GET['project_name']);
    }
    else
    {
        $name = explode(":-:",$_GET['document_name'])[1];
        $stmt = $conn->prepare("UPDATE `documentations` SET `status`=? WHERE `id`=?");
        $stmt -> bind_param("si",$_GET['stat'] ,$name);
        $stmt -> execute();
        unset($_GET['document_name']);
    }

    // echo "UPDATE `documentation` SET `status`='".$_GET['stat']."' WHERE `id`='$name'";
}
if(isset($_GET['action']))
{
    $stmt = $conn_fleet->prepare("UPDATE `comp` SET `$_GET[action]`=? WHERE `Name`=?");
    $stmt -> bind_param("ss",$_GET['val'] ,$_GET['company']);
    $stmt -> execute();
    unset($_GET['action']);
}
if(isset($_GET['additional_role']))
{
    $stmt = $conn->prepare("UPDATE `account` SET `additional_role`=? WHERE `Username`=?");
    $stmt -> bind_param("ss",$_GET['additional_role'],$_GET['user']);
    $stmt -> execute();
    unset($_GET['additional_role']);
}
if(isset($_GET['proccessing']))
{
    $stmt = $conn_fleet->prepare("UPDATE `comp` SET `main`=? WHERE `Name`=?");
    $stmt -> bind_param("ss",$_GET['proccessing'] ,$_GET['company']);
    $stmt -> execute();
    unset($_GET['proccessing']);
}
if(isset($_GET["delete_cs"]))
{
    ///////////////////////////////////Delete Code for CS
    $sql_clusterID = "SELECT * FROM `cluster` WHERE id = ?";
    $stmt_clus = $conn->prepare($sql_clusterID); 
    $stmt_clus->bind_param("i", $_GET['value']);
    $stmt_clus->execute();
    $result_clus = $stmt_clus->get_result();
    if($result_clus->num_rows>0)
    while($r_clus = $result_clus->fetch_assoc())
    {
        $sql_POClusterID = "SELECT * FROM `purchase_order` WHERE cluster_id = ?";
        $stmt_po = $conn->prepare($sql_POClusterID); 
        $stmt_po->bind_param("i", $_GET['value']);
        $stmt_po->execute();
        $result = $stmt_po->get_result();
        if($result->num_rows>0)
        while($r = $result->fetch_assoc())
        {
            $stmt2 = $conn->prepare("SELECT `compsheet_generated_date` FROM `report` where `request_id`='".$r['request_id']."' AND `type`='".$r['request_type']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($date_created);
            $stmt2->fetch();
            $stmt2->close();
        }
        $sql = "INSERT INTO `cluster_deleted` VALUES (?,?,?,?,?,?,?)";
        $stmt_clusterDeleted = $conn->prepare($sql);
        $stmt_clusterDeleted -> bind_param("ississs",$r_clus['id'] ,$r_clus['type'] ,$r_clus['status'] ,$r_clus['price'] ,$r_clus['company'] ,$r_clus['compiled_by'] ,$date_created);
        if ($stmt_clusterDeleted -> execute()) 
        {
            /////////////////////////////
            $stmt_po->bind_param("i", $_GET['value']);
            $stmt_po->execute();
            $result = $stmt_po->get_result();
            if($result->num_rows>0)
            while($r = $result->fetch_assoc())
            {
                $status = "Generating Quote";$nxt_step = "Procurement";$null = "NULL";
                $sql = "UPDATE `requests` SET `status`=?, `next_step`=? WHERE `request_id`=?";
                $stmt_statusRequest = $conn->prepare($sql);
                $stmt_statusRequest -> bind_param("ssi",$status ,$nxt_step ,$r['request_id']);
                if (!($stmt_statusRequest -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
                $sql = "UPDATE `report` SET `compsheet_generated_date`=? where `request_id`=?";
                $stmt_reportComp = $conn->prepare($sql);
                $stmt_reportComp -> bind_param("si",$null ,$r['request_id']);
                if (!($stmt_reportComp -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            }
            $scale = "not set";$status = "Performa Comfirmed";$cl_id = "NULL";
            $sql = "UPDATE `purchase_order` SET `scale`=?, `status`=?, `cluster_id`=? WHERE `cluster_id`=?";
            $stmt_clusterCustom = $conn->prepare($sql);
            $stmt_clusterCustom -> bind_param("ssii",$scale ,$status ,$cl_id ,$r_clus['id']);
            if (!($stmt_clusterCustom -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            
            $sql_PiClusterID = "SELECT * FROM `price_information` WHERE cluster_id = ?";
            $stmt_pi = $conn->prepare($sql_PiClusterID); 
            $stmt_pi->bind_param("i", $_GET['value']);
            $stmt_pi->execute();
            $result = $stmt_pi->get_result();
            if($result->num_rows>0)
            while($r = $result->fetch_assoc())
            {
                $sql = "INSERT INTO `price_information_deleted` VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt -> bind_param("iiiiiiiii",$r['id'] ,$r['cluster_id'] ,$r['purchase_order_id'] ,$r['purchase_order_id'] ,$r['quantity'] ,$r['price'] ,$r['total_price'] ,$r['after_vat'] ,$r['selected']);
                $stmt -> execute();
            }
            $sql = "DELETE FROM `price_information` WHERE cluster_id=?";
            $stmt_deletePI = $conn->prepare($sql);
            $stmt_deletePI -> bind_param("i",$_GET['value']);
            if (!($stmt_deletePI -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            $sql = "DELETE FROM `selections` WHERE cluster_id=?";
            $stmt_deleteselection = $conn->prepare($sql);
            $stmt_deleteselection -> bind_param("i",$_GET['value']);
            if (!($stmt_deleteselection -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            $sql = "DELETE FROM `cluster` WHERE id=?";
            $stmt_deletecluster = $conn->prepare($sql);
            $stmt_deletecluster -> bind_param("i",$_GET['value']);
            if (!($stmt_deletecluster -> execute())) echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            $_SESSION['success'] = 'Deleted Successfully';
        } 
        else
        {
            $_SESSION['success'] = 'Failed';
            echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
        }
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
}
    if((isset($_POST['admin-login']) || isset($_POST['reset-login'])) && ($_SESSION["role"]=="Admin" || isset($_SESSION['admin-access'])))
    {
        if(isset($_POST['admin-login']))
        {
            $sql="SELECT `admin-password` FROM `admin_settings` ORDER BY id DESC Limit 1";
            $stmt_admin_password = $conn->prepare($sql);
            $stmt_admin_password->execute();
            $result_admin_password = $stmt_admin_password->get_result();
            if($result_admin_password->num_rows > 0)
            {
                while($row_admin_password = $result_admin_password->fetch_assoc()) 
                    $pass = md5($_POST['admin-password']) == $row_admin_password['admin-password'];
            }
            else $pass = true;
            $record_type = "Secret Login";
            $operation = "Logged in as - ".$_POST['admin-login'];
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_SESSION['unique_id'], $operation);
            $stmt_add_record -> execute();
        }
        else 
        {
            $pass = true;
            $record_type = "Secret Logout";
            $operation = "Logged Out From - ".$_POST['reset-login'];
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $_SESSION['unique_id'], $operation);
            $stmt_add_record -> execute();
        }
        if($pass)
        {
            $un = (isset($_POST['admin-login']))?$_POST['admin-login']:$_SESSION['admin-access'];
            $qry="SELECT * FROM account where `Username`=?";
            $stmt_login = $conn->prepare($qry);
            $stmt_login->bind_param("s", $un);
            $stmt_login->execute();
            $result_login = $stmt_login->get_result();
            if($result_login->num_rows > 0)
            {
                while($row = $result_login->fetch_assoc()) 
                {
                    if(isset($_POST['admin-login']))
                    {
                        $_SESSION['admin-access'] = $_SESSION['username'];
                        $_SESSION['logged-in-as'] = $_POST['admin-login'];
                        unset($_SESSION['attempt-admin-pass']);
                    }
                    else 
                    {
                        unset($_SESSION['admin-access']);
                        unset($_SESSION['logged-in-as']);
                    }
                    $sql="SELECT * FROM `comp` where `Name`= ?";
                    $stmt_company = $conn_fleet->prepare($sql);
                    $stmt_company->bind_param("s",$row["company"]);
                    $stmt_company->execute();
                    $result_company = $stmt_company->get_result();
                    if($result_company->num_rows > 0)
                    {
                        while($r = $result_company->fetch_assoc()) 
                        {
                            $_SESSION['logo'] = $r['logo'];
                            $_SESSION['processing_company'] = $r['main'];
                            $_SESSION['property_company'] = ($r['property'])?$row["company"]:$r['main'];
                            $_SESSION['procurement_company'] = ($r['procurement'])?$row["company"]:$r['main'];
                            $_SESSION['finance_company'] = ($r['finance'])?$row["company"]:$r['main'];
                            $_SESSION['cheque_company'] = ($r['cheque_signatory'])?$row["company"]:$r['main'];
                            $_SESSION['company_signatory'] = ($r['cheque_signatory']);
                            $_SESSION['GMs'] = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
                            $_SESSION['perdiem'] = ($r['perdiem']);
                        }
                    }
                    if(isset($row['managing']) && !is_null($row['managing']) && $row['managing'] != "")
                    $_SESSION["managing_department"] = explode(",",$row['managing']);
                    else unset($_SESSION["managing_department"]);
                    $_SESSION["additional_role"]=$row["additional_role"];
                    $dep_list=array("Procurement","Disbursement","Property","Finance");
                    $_SESSION["dep_list"]=$dep_list;
                    $_SESSION["company"]=$row["company"];
                    $_SESSION["department"]=$row["department"];
                    $_SESSION["a_type"]=str_replace(' ', '', $row["type"]);
                    $_SESSION["role"] = $row["role"];
                    $_SESSION["position"] = $row["position"];
                    if($row["role"] == "Director" || $row["role"] == "Admin" || $row["role"] == "GM")
                    {
                        $_SESSION['loc'] = $_SESSION["role"]."/";
                    }
                    else if(in_array($_SESSION["department"], $dep_list))
                    {
                        $_SESSION['loc'] = $_SESSION["department"]."/";
                    }
                    else if(strpos($_SESSION["a_type"],"Committee") || $_SESSION["role"]=='Owner')
                    {
                        $_SESSION['loc'] = "Committee/";
                    }
                    else
                    {
                        $_SESSION['loc'] = $row["type"]."/";
                    }
                }
            }
        }
        else 
        {
            $_SESSION['attempt-admin-pass'] = (isset($_SESSION['attempt-admin-pass']))?$_SESSION['attempt-admin-pass']+1:1;
        }
        header("location: ../".$_SESSION['loc']);
    }

}
else
    header("location: ".$_SERVER['HTTP_REFERER']);
$conn->close();
$conn_fleet->close();
$conn_ws->close();
?>