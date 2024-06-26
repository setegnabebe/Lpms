<?php
session_start();
    include "../connection/connect.php";
    include "../common/functions.php";
    ///////////######################################Login######################################/////////////////
    
    //########################################################################################################////
    ///////////######################################changepassword######################################/////////////////
    if(isset($_SESSION['username']))
    {
        if(isset($_GET['account_username'])){
        $stmt_account -> bind_param("s", $_GET['account_username']);
        $stmt_account -> execute();
        $result_account = $stmt_account -> get_result();
        $row = $result_account->fetch_assoc();
        echo "<table style='margin-left:auto;margin-right:auto;'><tr><td>Username: </td><td>".str_replace("."," ",$row['Username'])."</td></tr><tr><td>Department:  </td><td>".$row['department'].'</td></tr><tr><td>Position:</td><td>'.$row['position'].'</td></tr>';
        }
        if(isset($_POST['changepassword']))
        {
            $uname = $_POST['username'];
            $oldp = md5($_POST['oldpass']);
            $newp = md5($_POST['newpass']);
            $qry="SELECT * FROM `account` where `Username` = ? AND `password` = ?";
            $stmt_login = $conn->prepare($qry);
            $stmt_login->bind_param("ss", $uname, $oldp);
            $stmt_login->execute();
            $result_login = $stmt_login->get_result();
            if($result_login->num_rows == 0)
            {
                $_SESSION['passfail'] = "** Incorrect Password **";
                header("location: ".$_SERVER['HTTP_REFERER']);
            }
            else
            {
                $st_ac = "active";
                $sql2 = "UPDATE `account` SET `password` = ?,`status` = ?  WHERE `Username` = ? AND `password` = ?";
                $stmt_update_password = $conn->prepare($sql2);
                $stmt_update_password->bind_param("ssss", $newp, $st_ac, $uname, $oldp);
                $stmt_update_password->execute();
                $_SESSION['acc_status'] = $st_ac;
                $_SESSION['success'] = "Password Successfuly Changed!!";
                header("location: ".$_SERVER['HTTP_REFERER']);
            }
        }
        if(isset($_POST['change_profile']))
        {
            $uname = $_POST['username'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $name = $_POST['firstName'].".".$_POST['lastName'];

            $operation = "Profile Updated";
            $operation .= ($phone != $_SESSION['phone_number'])?" Phone ".$_SESSION['phone_number']." -> $phone":"";
            $operation .= ($email != $_SESSION['email'])?" Email ".$_SESSION['email']." -> $email":"";
            $operation .= ($name != $_SESSION['name'])?" Name ".$_SESSION['name']." -> $name":"";

            $_SESSION['email'] = $email;
            $_SESSION['phone_number'] = $phone;
            $sql2 = "UPDATE `account` SET `name` = ?,`phone` = ?,`email` = ?,`update_date`=CURRENT_TIMESTAMP, `updated_by` = ? WHERE `Username` = ?";
            $stmt_update_profile = $conn->prepare($sql2);
            $stmt_update_profile->bind_param("sssss", $name, $phone, $email, $uname, $uname);
            $stmt_update_profile->execute();
            $_SESSION['name'] = $name;
            $tbl = 'account';
            $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $tbl, $_SESSION['unique_id'], $operation);
            $stmt_add_record -> execute();
            
            $_SESSION['success'] = "Profile Successfuly Updated!!";
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
//######################################################################################################################//
}
else
    header("location: ".$_SERVER['HTTP_REFERER']);
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>