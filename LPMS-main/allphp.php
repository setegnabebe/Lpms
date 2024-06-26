<?php
    session_start();
    include "connection/connect.php";//currently from lpms account table
    if(isset($_POST['login'])){
        // $_SESSION['username']=$_POST['username'];
        // header("Location: user/");
        $un=$_POST['username'];
        $pwd=md5($_POST['password']);
        
        $qry="SELECT * FROM account where `Username`=? AND `password`=?";
        $stmt_login = $conn->prepare($qry);
        $stmt_login->bind_param("ss", $un, $pwd);
        $stmt_login->execute();
        $result_login = $stmt_login->get_result();
        if($result_login->num_rows > 0)
        {
            while($row = $result_login->fetch_assoc()) 
            {
                if($row["status"] == "active" || $row['status'] == 'waiting')
                {
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
                            $_SESSION['processing_company'] = $r['main'];//($r['independence'] == 'not' || $r['type'] == 'Branch')?$r['main']:$row["company"]
                            $_SESSION['property_company'] = ($r['property'])?$row["company"]:$r['main'];
                            $_SESSION['procurement_company'] = ($r['procurement'])?$row["company"]:$r['main'];
                            $_SESSION['finance_company'] = ($r['finance'])?$row["company"]:$r['main'];
                            $_SESSION['cheque_company'] = ($r['cheque_signatory'])?$row["company"]:$r['main'];
                            $_SESSION['company_signatory'] = ($r['cheque_signatory']);
                            $_SESSION['GMs'] = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
                            $_SESSION['perdiem'] = ($r['perdiem']);
                            // if(isset($r["included"]) && !is_null($r["included"]))
                            //     $_SESSION["included_company"]=explode(",",$r["included"]);
                        }
                    }
                    if(isset($row['managing']) && !is_null($row['managing']) && $row['managing'] != "")
                        // $_SESSION["managing_department"] = explode(",",str_replace(' ', '', $row['managing']));
                        $_SESSION["managing_department"] = explode(",",$row['managing']);

                    $status = "Online";
                    $sql_update_online = "UPDATE Account SET user_status = ? WHERE unique_id = ? and status='active'";
                    $stmt_update_online = $conn->prepare($sql_update_online);
                    $stmt_update_online -> bind_param("si",$status ,$row['unique_id']);
                    $stmt_update_online -> execute();
                    $_SESSION['user_status'] = $status;
                    $_SESSION['unique_id'] = $row['unique_id'];
                    $_SESSION["acc_status"] = $row["status"];
                    $_SESSION["username"] = $row["Username"];
                    $_SESSION["name"] = (is_null($row["name"]) || $row["name"] == "")?$row["Username"]:$row["name"];
                    $_SESSION["additional_role"]=$row["additional_role"];
                    $dep_list=array("Procurement","Disbursement","Property","Finance");
                    $_SESSION["dep_list"]=$dep_list;
                    $_SESSION["company"]=$row["company"];
                    $_SESSION["department"]=$row["department"];
                    $_SESSION["a_type"]=str_replace(' ', '', $row["type"]);
                    $_SESSION["role"] = $row["role"];
                    $_SESSION["email"] = $row["email"];
                    $_SESSION["position"] = $row["position"];
                    $_SESSION["phone_number"] = $row["phone"];
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
                   
                    // $loc = (in_array($_SESSION["department"], $dep_list))?
                    // ((strpos($_SESSION["a_type"],"Committee") || $_SESSION["role"]=='Owner')?"Location: Committee/":"Location: ".$_SESSION["department"]."/"):
                    // "Location: ".$row["type"]."/";
                    // echo "
                    // Status : ".$_SESSION["acc_status"]."<br>
                    // username : ".$_SESSION["username"]."<br>
                    // company : ".$_SESSION["company"]."<br>
                    // department : ".$_SESSION["department"]."<br>
                    // a_type : ".$_SESSION["a_type"]."<br>
                    // role : ".$_SESSION["role"]."<br>
                    // Session loc : ".$_SESSION["loc"]."<br>
                    // ";
                    // $loc = (in_array($_SESSION["department"], $dep_list))?"Location: ".$_SESSION["department"]."/":"Location: requests/";
                $_SESSION["ip_address"] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                $logdate = date('Y-m-d H:i:s');
                $sql="INSERT INTO `log` (`operation`,`time`,`user`,`ipaddress`)VALUES('Login',?,?,?)";
                $stmt_log = $conn->prepare($sql);
                $stmt_log->bind_param("sss",$logdate, $_SESSION['username'], $_SESSION['ip_address']);
                $stmt_log->execute();
                
                $_SESSION['log_id']=$conn->insert_id;
                $pagee = (isset($_SESSION['page']))?$_SESSION['page']:$_SESSION['loc'];
                if(isset($_SESSION['page']))
                {
                    if(strpos($pagee,"rocurement"))
                    {
                        if($_SESSION["role"]=='Purchase officer')
                            $_SESSION["loc"] = "procurement/junior/";
                        else if($_SESSION["role"]=='GS' || $_SESSION["role"]=='user')
                            $_SESSION["loc"] = "procurement/GS/";
                        else if($_SESSION["role"]=='manager')
                            $_SESSION["loc"] = "procurement/manager/";
                        else if($_SESSION["role"]=='Senior Purchase officer')
                            $_SESSION["loc"] = "procurement/senior/";
                    }
                }
                unset($_SESSION['page']);
                    header("Location: ".$pagee);
                    // echo $_SESSION['loc'];
                }
                else{
                    $_SESSION['fail']="<h5 class='text-danger text-center mt-2'>*Account was Disabled*</h5>";
                    header("Location: index.php");
                }
            }
        }

        else{
            $_SESSION['fail']="<h5 class='text-danger text-center mt-2'>*Invalid Username or Password*</h5>";
            header("Location: index.php");
        }
    }
    if(isset($_POST['forgot']))
    {
        $un=$_POST['username'];
        $email=$_POST['email'];
        $qry="SELECT * FROM account where `Username`=? AND `email`=? and status='active'";
        $stmt_password = $conn->prepare($qry);
        $stmt_password->bind_param("ss", $un, $email);
        $stmt_password->execute();
        $result_password = $stmt_password->get_result();
        if($result_password->num_rows > 0)
        {
            while($row = $result_password->fetch_assoc()) 
            {
                $date_done=date("Y-m-d H:i:s");
                $uniq = uniqid();
                $uniq .= uniqid("",$uniq);
                $uniq .= uniqid("",$uniq);
                $date_n_password = $date_done.":-:".$row['password'];
                $sql = "INSERT INTO `forgot_password_links`(`Requested_by`, `Sent_to`, `Link`, `date`) VALUES (?,?,?,?)";
                $stmt_forgot_password = $conn -> prepare($sql);
                $stmt_forgot_password -> bind_param("ssss",$un ,$email, $uniq, $date_n_password);
                $stmt_forgot_password -> execute();
                // $link = "http://localhost/LPMS/forgot_password.php?id=".$uniq;
                $uname =str_replace("."," ",$un);
                $link = "https://lpms.hagbes.com/forgot_password.php?id=".$uniq;
                $subject_email = "Password reset email";
                $data_email = 
                "<strong>Password reset was requested by $uname</strong><br><br><br>
                <a style='display: block;
                width: 115px;
                height: 25px;
                background: #4E9CAF;
                padding: 10px;
                text-align: center;
                border-radius: 5px;
                color: white;
                font-weight: bold;
                line-height: 25px;' href='$link'>Reset</a><br><br><br>
                Link is only valid for 10 days<br>
                If the request wasn't submitted by you ignore this message<br><br><br>";
                // $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                $com_lo = "Hagbes HQ.,Hagbeslogo.jpg";
                $cc =""; $bcc = ""; $tag = "";
                $to = $email.",".$un;
                $reason = "closed";
                $sent_from="";
                $stmt_email_reason -> bind_param("ssssssssss",$project_name, $to, $cc, $bcc, $subject_email, $data_email ,$tag,$com_lo,$reason,$sent_from);
                $stmt_email_reason -> execute();
                // $phone_number = $row['phone']; $sms_to = $row['Username']; $sms_from = "LPMS System";
                // $msg = "Password Reset Requested click on $link";
                // include "common/sms.php";
                // send_auto_email($subject_email,$data_email,$email.",".$un,"Dagem.Adugna@hagbes.com");
                header("Location: pages/successfulReset.php");
            }
        }
        else
        {
            $_SESSION['fail']="<h5 class='text-danger text-center mt-2'>*No Account With this Email and Username*</h5>";
            header("location: ".$_SERVER['HTTP_REFERER']);
        }

    }
    if(isset($_POST['set_password']))
    {
        $un=$_POST['username'];
        $email=$_POST['email'];
        $password = md5($_POST['password']);
        $uniq = $_SESSION['forgot_link'];
        $qry="SELECT * FROM account where `Username`=? AND `email`=? and `status`='active'";
        $stmt_account = $conn->prepare($qry);
        $stmt_account->bind_param("ss", $un, $email);
        $stmt_account->execute();
        $result_account = $stmt_account->get_result();
        if($result_account->num_rows > 0)
        {
            $sql2 = "UPDATE account SET `password`=? WHERE `Username`=? AND `email`=?";
            $stmt_password = $conn->prepare($sql2);
            $stmt_password -> bind_param("sss", $password, $un, $email);
            $stmt_password -> execute();
            $sql2 = "UPDATE forgot_password_links SET `status`='used' WHERE `Link`=?";
            $stmt_link_used = $conn->prepare($sql2);
            $stmt_link_used -> bind_param("s", $uniq);
            $stmt_link_used -> execute();
            $_SESSION["success"] = true;
        }
        unset($_SESSION['forgot_link']);
        $_SESSION['success'] = true;
        header("Location: index.php");
    }
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