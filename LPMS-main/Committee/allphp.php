<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
if(isset($_SESSION['username']))
{
    if(isset($_GET['btntype']) || isset($_GET['batch_owner_approve']) || isset($_GET['batch_owner_reject']))
    {
        if(isset($_GET['btntype']))
        {
            $requests=[explode("_",$_GET['btntype'])[2]];
        }
        else
        {
            $requests = (isset($_GET['batch_owner_approve']))?explode(',',$_GET['batch_owner_approve']):explode(',',$_GET['batch_owner_reject']);
        }
        foreach($requests as $req_id)
        {
            $date_app=date("Y-m-d H:i:s");
            $approved_by = $_SESSION['username'];
            $for_spec = false;
            $stmt_request -> bind_param("i", $req_id);
            $stmt_request -> execute();
            $result_request = $stmt_request->get_result();
            if($result_request->num_rows>0)
            while($row_temp = $result_request->fetch_assoc())
            {
                $type = $row_temp['request_type'];
                $na_t=str_replace(" ","",$type);
                $requested_by = $row_temp['customer'];
                $item = $row_temp['item'];
                $depp = $row_temp['department'];
                $req_quan = $row_temp['requested_quantity'];
                $unit = $row_temp['unit'];
                $date_n_b = $row_temp['date_needed_by'];
                $property_company = $row_temp['property_company'];
                $company=$row_temp['company'];
                $proc_comp=$row_temp['procurement_company'];
                if(!is_null($row_temp['spec_dep']) && is_null($row_temp['specification']))
                {
                    $for_spec = true;
                }
            }
    
            if((isset($_GET['btntype']) && strpos($_GET['btntype'],"approve") !==false) || isset($_GET['batch_owner_approve']))
            {
                $status = "Approved By Owner" ;
                $email_stat = "Approved";
                $nxt ="Performa";
                $send_to = "";
                    $reason = "open_req_".$na_t."_".$req_id."_GS_assign";
                    $sql_email = "SELECT * FROM `account` where ((`department` = 'Procurement' AND (role = 'manager' OR `type` LIke '%manager%')) or `additional_role` = 1) AND company = ? and status='active'";
                //    echo $sql_email;
                    $stmt_email = $conn->prepare($sql_email);
                    $stmt_email -> bind_param("s", $proc_comp);
                    $stmt_email -> execute();
                    $result_email = $stmt_email->get_result();
                    if($result_email->num_rows>0)
                        while($row_email = $result_email->fetch_assoc())
                        {
                            $email = $row_email['email'];
                            $email_to = $row_email['Username'];
                            $send_to .= ($send_to == "")?$email.",".$email_to:",".$email.",".$email_to;
                        }
                        $subject_email = "An item was sent for purchase";
                        $data_for_email='Please assign a purchase officer for Proforma gathering';
            }
            else
            {
                $email_to = $requested_by;
                $status = "Rejected By Owner";
                $email_stat = "Rejected";
                $nxt = "Owner Rejected";
                $stmt2 = $conn->prepare("SELECT `email` FROM `account` where `Username`='".$requested_by."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($email);
                $stmt2->fetch();
                $stmt2->close();
                $send_to = $email.",".$requested_by;
                
                $subject_email = "$item purchase request In ".$_SESSION['department']." department";
                $data_for_email = "<strong>Your $type purchase request for $item was $email_stat by $_SESSION[username] </strong><br>";
                $reason = "closed";
            }
            if(isset($_GET['reason']) && $_GET['reason'] != "")
            {
                $level = "Owner";
                $stmt_remark -> bind_param("ssss",$req_id,$_GET['reason'],$_SESSION['username'],$level);
                $stmt_remark -> execute();
            }
            $sql_req = "UPDATE requests SET `status`=?,`owner`=?,`next_step`=? WHERE `request_id`=?";
            $stmt_req_owner = $conn->prepare($sql_req);
            $stmt_req_owner -> bind_param("sssi",$status ,$_SESSION['username'] ,$nxt ,$req_id);
            $stmt_req_owner -> execute();
            $sql_rep = "UPDATE `report` SET `Owner_approval_date`=? WHERE `request_id`=?";
            $stmt_rep = $conn->prepare($sql_rep);
            $stmt_rep -> bind_param("si",$date_app ,$req_id);
            $stmt_rep -> execute();
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
            $cc =""; $bcc = ""; $tag = $email_to;
    
            $reason_close = "open_req_".$na_t."_".$req_id."_owner";
            $stmt_email_close -> bind_param("s",$reason_close);
            $stmt_email_close -> execute();
            $user=($_SESSION['username'].":-:".$_SESSION['position']);
            $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
            $stmt_email_reason -> execute();
            if($status == "Approved By Gm")
            {
                $email_id = $conn->insert_id;
                $page_to = "Procurement/GS/assignOfficer.php";
                $stmt_email_page -> bind_param("si",$page_to, $email_id);
                $stmt_email_page -> execute();
            }
            $_SESSION["success"]=$email_stat;
            updaterequest($conn,$conn_fleet,$req_id,"one","","Owner");
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    
    if(isset($_GET["newselection"]))
    {
        $managers = [];
        $c_id = $_GET["newselection"];
        $stmt_cluster -> bind_param("i", $c_id);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster->get_result();
        $row = $result_cluster->fetch_assoc();
        $procurement_company = $row['procurement_company'];
        $comp_count = 0;
        $sql_appover = "SELECT username FROM `committee_approval` inner Join account on account.username=committee_approval.committee_member Where `cluster_id` = ? AND committee_approval.`status` = 'Approved'";
        $stmt_appover = $conn->prepare($sql_appover);
        $stmt_appover -> bind_param("i", $row['id']);
        $stmt_appover -> execute();
        $result_appover = $stmt_appover->get_result();
        if($result_appover->num_rows>0)
        $users="";
        while($comms = $result_appover->fetch_assoc())
        {
         $users.=str_replace("."," ",$comms['username']).",";
        }
        $users=rtrim($users,",");
        $sql_companies = "SELECT DISTINCT `providing_company` FROM `price_information` where `cluster_id`= ?";
        $stmt_companies = $conn->prepare($sql_companies);
        $stmt_companies -> bind_param("i", $row['id']);
        $stmt_companies -> execute();
        $result_companies = $stmt_companies->get_result();
        if($result_companies->num_rows>0)

        while($r = $result_companies->fetch_assoc())
        {
            // echo $r['providing_company']."<br>";
            $companies[$comp_count] = $r['providing_company'];
            $comp_count++;
        }
        $sql_po = "SELECT * FROM `purchase_order` where `cluster_id`= ?";
        $stmt_po = $conn->prepare($sql_po);
        $stmt_po -> bind_param("i", $row['id']);
        $stmt_po -> execute();
        $result_po = $stmt_po->get_result();
        if($result_po->num_rows>0)
            while($row2 = $result_po->fetch_assoc())
            {
                $scale_current = $row2['scale'];
                if($row2['scale'] == "procurement" || $row2['scale'] == "Owner")
                    $cur_sca = $row2['scale'];
                else
                    $cur_sca = $row2['scale']." Committee";
                    
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
                    $stmt_cluster -> bind_param("i", $row['id']);
                    $stmt_cluster -> execute();
                    $result_cluster = $stmt_cluster->get_result();
                    $clus_row=$result_cluster->fetch_assoc();
                    $sql_limit = "SELECT * FROM `limit_ho` where company=? ORDER BY id DESC limit 1";
                    $stmt_limit = $conn->prepare($sql_limit);
                    $stmt_limit -> bind_param("s", $clus_row['company']);
                    $stmt_limit -> execute();
                    $result_limit = $stmt_limit->get_result();
                    if ($result_limit->num_rows ==0)
                    {
                        $comps = "Others";
                        $stmt_limit -> bind_param("s", $comps);
                        $stmt_limit -> execute();
                        $result_limit = $stmt_limit->get_result();
                    }
                    if($result_limit->num_rows>0)
                    {
                        $r_new = $result_limit->fetch_assoc();
                        $Vat = $r_new['Vat'];
                    }
                    else $Vat = 0.15;
                    // $total = ($Vat * $total) + $total;
                    $stmt = $conn->prepare("UPDATE `cluster` SET `price`=? WHERE `id`=?");
                    $stmt -> bind_param("si", $total, $row['id']);
                    $stmt -> execute();
                }
            }
            
        $selections = "";
        $sql_selected_prices = "SELECT * FROM `price_information` WHERE `cluster_id`=? And selected = '1'";
        $stmt_selected_prices = $conn->prepare($sql_selected_prices);
        $stmt_selected_prices -> bind_param("i", $row['id']);
        $stmt_selected_prices -> execute();
        $result_selected_prices = $stmt_selected_prices->get_result();
        if($result_selected_prices->num_rows>0)
            while($r = $result_selected_prices->fetch_assoc())
            {
                $selections .= ($selections=="")?$r["id"]:",".$r["id"];
            }
        // echo $selections;
        $sql_user_selections = "SELECT * FROM `selections` WHERE `user` = ? AND `cluster_id`= ?";
        $stmt_user_selections = $conn->prepare($sql_user_selections);
        $stmt_user_selections -> bind_param("si",$_SESSION['username'] ,$row['id']);
        $stmt_user_selections -> execute();
        $result_user_selections = $stmt_user_selections->get_result();
        if($result_user_selections->num_rows>0)
        {
            $sql_update_selections = "UPDATE `selections` SET selection = ? WHERE `user` = ? AND `cluster_id`= ?";
            $stmt_update_selections = $conn->prepare($sql_update_selections);
            $stmt_update_selections -> bind_param("ssi", $selections, $_SESSION['username'], $row['id']);
            $stmt_update_selections -> execute();
        }
        else
        {
            $stmt_add_selections -> bind_param("sis", $_SESSION['username'], $row['id'], $selections);
            $stmt_add_selections -> execute();
        }

        $record_type = "cluster";
        $record = "Committee Approved";
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $row['id'], $record);
        $stmt_add_record -> execute();
    
        $status = "Approved";
        if(isset($_GET['reason']))
        $reason = ($_GET['reason']=='')?"#":$_GET['reason'];
        else $reason = "#";
        $date=date("Y-m-d H:i:s");
        $sql_prev_approved = "SELECT id FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
        $stmt_prev_approved = $conn->prepare($sql_prev_approved);
        $stmt_prev_approved -> bind_param("si",$_SESSION['username'] ,$row['id']);
        $stmt_prev_approved -> execute();
        $result_prev_approved = $stmt_prev_approved->get_result();
        if($result_prev_approved->num_rows>0)
            while($row_temp = $result_prev_approved->fetch_assoc())
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
        // if(isset($cur_sca))
        // {
            // Branch Committee
        //     echo $cur_sca."<br><br><br>";
        // if(strpos($cur_sca,'Committee')===false && $cur_sca!='procurement')
        //     $eval = "(`department` = 'Owner' OR (`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = '$procurement_company'))";
        // else if($cur_sca=='procurement')
        //     $eval = "`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = 'Hagbes HQ.'";
        // else 
        //     $eval = "type LIKE '%".$cur_sca."%' AND company = '$req_com'";
        $reason = "open_clust_$row[id]_committee_levl";
        $stmt_email_close_tag -> bind_param("ss", $reason, $_SESSION['username']);
        $stmt_email_close_tag -> execute();
        // }
        // else
        // {
        //     if(strpos($_GET['current_scale'],'Committee')===false && $_GET['current_scale']!='procurement') $eval = "`department` = 'Owner'";
        //     else $eval = "type LIKE '%".$_GET['current_scale']."%'";
        // }
        $stmt_po_cluster -> bind_param("i", $row['id']);
        $stmt_po_cluster -> execute();
        $result_po_cluster = $stmt_po_cluster->get_result();
        if($result_po_cluster->num_rows>0)
            while($row2 = $result_po_cluster->fetch_assoc())
            {
                // $dbs=type_to_dbs($row2['request_type']);
                $stmt_request -> bind_param("i", $row2['request_id']);
                $stmt_request -> execute();
                $result_request = $stmt_request->get_result();
                if($result_request->num_rows>0)
                    while($row_dbs = $result_request->fetch_assoc())
                    {
                        $request_by = $row_dbs["customer"];
                        $GM_app = $row_dbs["GM"];
                        $Owner_app = $row_dbs["owner"];
                        $director_app = $row_dbs["director"];
                        $req_dep = $row_dbs["department"];
                        $req_com = $row_dbs["company"];
                        $req_type = $row_dbs["request_type"];
                        $spec_dep = $row_dbs["spec_dep"];
                        $req_proc_com = $row_dbs["procurement_company"];
                        $stmt_account -> bind_param("s", $request_by);
                        $stmt_account -> execute();
                        $result_account = $stmt_account->get_result();
                        if($result_account->num_rows>0)
                            while($row_man = $result_account->fetch_assoc())
                                $role = $row_man['role'];
                    }
            }
        if(strpos($cur_sca,'Committee')===false && $cur_sca!='procurement')
            $eval = "(`role` = 'Owner' OR (((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%'))  or `additional_role` = 1)  AND company = '$procurement_company'))";
        else if($cur_sca=='procurement')
            $eval = "`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = 'Hagbes HQ.'";
        else if(strpos($cur_sca,'HO')!==false)
            $eval = "type LIKE '%".$cur_sca."%'";
        else 
            $eval = "(type LIKE '%".$cur_sca."%' AND company = '$req_com') OR (((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%'))  or `additional_role` = 1) AND company = '$req_proc_com')";
        $sql_committee_members = "SELECT count(*) as com_count FROM `account` Where ($eval) and status = 'active'";
        $sql_committee_members .= ($cur_sca == "Owner")?" Group by role,department":"";
        $stmt_committee_members = $conn->prepare($sql_committee_members);
        $stmt_committee_members -> execute();
        $result_committee_members = $stmt_committee_members->get_result();
        if($result_committee_members->num_rows>0)
            while($row_temp = $result_committee_members->fetch_assoc())
                $com_count = $row_temp['com_count'];
        
        $man_dep = ((strpos($cur_sca,"HO")!==false || $cur_sca == 'Owner') && $req_com != 'Hagbes HQ.' && $req_dep != 'GM' && $req_dep != 'Director')?"(department = '$req_dep' OR department = 'GM' OR department = 'Director')":"department = '$req_dep'";
        // $sql_man = "SELECT * FROM `account` where  department = '$req_dep' AND company = '$req_com' and (role = 'manager' OR `type` LIke '%manager%') and status = 'active' and type NOT LIKE '%Committee%' and type NOT LIKE '%Owner%'";
        $sql_manager_director = "SELECT * FROM `account` where  department = ? AND company = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role = 'Director' OR role = 'GM' OR role = 'Owner') and status = 'active' and type NOT LIKE '%".$cur_sca."%'";
        $stmt_manager_director = $conn->prepare($sql_manager_director);
        $stmt_manager_director -> bind_param("ss", $req_dep, $req_com);
        $stmt_manager_director -> execute();
        $result_manager_director = $stmt_manager_director -> get_result();
        if($result_manager_director -> num_rows>0)
            // while($row_man = $result_man->fetch_assoc())
                $com_count++;
        if(($cur_sca == 'Owner') && $req_com != 'Hagbes HQ.' && $req_dep != 'GM' && $req_dep != 'Director') // strpos($cur_sca,"HO")!==false || 
            $com_count++;
        if($req_dep == "Procurement" && $req_com == "Hagbes HQ." && $cur_sca=='procurement') $com_count--;
        if($req_dep == "Procurement" && $req_com != "Hagbes HQ." && $procurement_company != "Hagbes HQ." && strpos($cur_sca,'Branch')!==false) $com_count--;
            $sql_management = "SELECT * FROM `account` where  department = ? AND company = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role = 'Director' OR role = 'GM') and status = 'active'";
            $stmt_management = $conn->prepare($sql_management);
            $stmt_management -> bind_param("ss", $req_dep, $req_com);
            $stmt_management -> execute();
            $result_management = $stmt_management->get_result();
            if($result_management->num_rows>0)
                while($row_man = $result_management->fetch_assoc())
                {
                    array_push($managers,$row_man['Username']);
                    $manager = $row_man['Username'];
                }
                if($req_com != 'Hagbes HQ.')
                {
                    $stmt_GM -> bind_param("s", $req_com);
                    $stmt_GM -> execute();
                    $result_GM = $stmt_GM->get_result();
                    if($result_GM->num_rows>0)
                        while($row_man = $result_GM->fetch_assoc())
                        {
                            $GM = $row_man['Username'];
                        }
            }
            if($req_dep == 'Owner' || $role == 'Director' || $role == 'GM' || isset($manager_included)) 
            {
                $manager=($req_dep == 'Owner')?$Owner_app:(($role == 'GM')?$GM_app:$director_app);
            }
        if(strpos($cur_sca,"HO")!==false)
        {
            $sql_ho_count = "SELECT count(*) as com_count FROM `committee_approval` C INNER JOIN account A on C.committee_member = A.Username Where `cluster_id` = ? AND C.status = 'Approved' and `type` LIKE '%HO Committee%'";
            $stmt_ho_count = $conn->prepare($sql_ho_count);
            $stmt_ho_count -> bind_param("i", $row['id']);
            $stmt_ho_count -> execute();
            $result_count = $stmt_ho_count->get_result();
        }
        else
        {
            $sql_approval_count = "SELECT count(*) as com_count FROM `committee_approval` Where `cluster_id` = ? AND `status` = 'Approved'";
            $stmt_approval_count = $conn->prepare($sql_approval_count);
            $stmt_approval_count -> bind_param("i", $row['id']);
            $stmt_approval_count -> execute();
            $result_count = $stmt_approval_count->get_result();
        }
        if($result_count->num_rows>0)
            while($row_temp = $result_count->fetch_assoc())
                $approved = $row_temp['com_count'];
        if($result_count->num_rows>0 && strpos($cur_sca,"HO")!==false)
        {
            $sql_approval_count = "SELECT count(*) as com_count FROM `committee_approval` Where `cluster_id` = ? AND `status` = 'Approved'";
            $stmt_approval_count = $conn->prepare($sql_approval_count);
            $stmt_approval_count -> bind_param("i", $row['id']);
            $stmt_approval_count -> execute();
            $result_count = $stmt_approval_count->get_result();
            if($result_count->num_rows>0)
            {
                $row_temp = $result_count->fetch_assoc();
                $all_approved = $row_temp['com_count'];
            }
            if($all_approved != $approved) $approved = $all_approved;
        }
        if(!isset($go_on))
        {
            $gm_approved = true;
            if((strpos($cur_sca,"HO")!==false || $cur_sca == 'Owner') && isset($GM))
            {
                $stmt_specific_approval -> bind_param("is", $row['id'], $GM);
                $stmt_specific_approval -> execute();
                $result_specific_approval = $stmt_specific_approval->get_result();
                $gm_approved = ($result_specific_approval->num_rows>0);
            }
            $Proc_Admin_approved = true;
            if(strpos($cur_sca,"HO")!==false)
            {
                $sql_Procurement_and_admin = "SELECT * FROM `account` WHERE department='Procurement and Adminstration' and role='director' and status='active'";
                $stmt_Procurement_and_admin = $conn->prepare($sql_Procurement_and_admin);
                $stmt_Procurement_and_admin -> execute();
                $result_Procurement_and_admin = $stmt_Procurement_and_admin->get_result();
                if($result_Procurement_and_admin->num_rows>0)
                    while($row_pad = $result_Procurement_and_admin->fetch_assoc())
                    {
                        $pad = $row_pad['Username'];
                    }
                $stmt_specific_approval -> bind_param("is", $row['id'], $pad);
                $stmt_specific_approval -> execute();
                $result_specific_approval = $stmt_specific_approval->get_result();
             
                $Proc_Admin_approved = ($result_specific_approval->num_rows>0);
            }

            if($req_com == 'Hagbes HQ.' || (isset($manager) && $manager !=''))
            {
                $man_cond = "";
                if(sizeof($managers)>1)
                {
                    foreach($managers as $mans)
                    {
                        $man_cond .= ($man_cond == "")?"(`committee_member` = '$mans'":" OR `committee_member` = '$mans'";
                    }
                    $man_cond .= ")";
                }
                else
                {
                    $man_cond = "`committee_member` = '$manager'";
                }
                $sql = "SELECT * FROM `committee_approval` Where `cluster_id` = ? AND $man_cond AND `status` = 'Approved'";
            }
            else
                $sql = "SELECT * FROM `committee_approval` Where `cluster_id` = ? AND `committee_member` = '$GM' AND `status` = 'Approved'";
            $stmt_manager_committee = $conn->prepare($sql);
            $stmt_manager_committee -> bind_param("i", $row['id']);
            $stmt_manager_committee -> execute();
            $result_manager = $stmt_manager_committee->get_result();
            $spec_approved = true;
            if(!is_null($spec_dep) && $spec_dep == 'IT')
            {
                $sql_spec_manager = "SELECT * FROM `account` where  (department = ? and role='manager' and company='Hagbes HQ.') and status = 'active'";
                $stmt_spec_manager = $conn->prepare($sql_spec_manager);
                $stmt_spec_manager -> bind_param("s", $spec_dep);
                $stmt_spec_manager -> execute();
                $result_spec_manager = $stmt_spec_manager->get_result();
                if($result_spec_manager->num_rows>0)
                    while($row_spec = $result_spec_manager->fetch_assoc())
                    {
                        $stmt_specific_approval -> bind_param("is", $row['id'], $row_spec['Username']);
                        $stmt_specific_approval -> execute();
                        $result_specific_approval = $stmt_specific_approval->get_result();
                        $spec_approved = ($result_specific_approval->num_rows>0);
                    }
            }
            if($result_manager->num_rows>0 && $gm_approved && $spec_approved &&$Proc_Admin_approved)
                $go_on = True;
            else
                $go_on = false;
        }
     
        $stmt_rejected -> bind_param("i", $row['id']);
        $stmt_rejected -> execute();
        $result_rejected = $stmt_rejected->get_result();
        if($result_rejected->num_rows>0)
            $rejected = 1;
        else
            $rejected = 0;
        $all_selectsss = "";
        $selections_match = 1;
        $stmt_selections -> bind_param("i", $row['id']);
        $stmt_selections -> execute();
        $result_selections = $stmt_selections->get_result();
        if($result_selections->num_rows>0)
        while($row_temp = $result_selections->fetch_assoc())
        {
            if($row['compiled_by'] != $row_temp['user'])
                if($all_selectsss=="")$all_selectsss = $row_temp["selection"];
                else
                {
                        if($all_selectsss != $row_temp["selection"])
                        {
                            $selections_match = 0;
                        }
                }
        }
        $stmt_cluster -> bind_param("i", $row['id']);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster->get_result();
        $clus_row=$result_cluster->fetch_assoc();
        
        $stmt_limit -> bind_param("s", $clus_row['company']);
        $stmt_limit -> execute();
        $result_limit = $stmt_limit->get_result();
        if ($result_limit->num_rows ==0)
        {
            $companies = "Others";
            $stmt_limit -> bind_param("s", $companies);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
        }
        if($result_limit->num_rows>0)
        {
            $r_limit = $result_limit->fetch_assoc();
            $minimum_approval = $r_limit['minimum_approval'];
            $Vat = $r_limit['Vat'];
            $limit = $r_limit['amount_limit'];
            $limit_top = $r_limit['amount_limit_top'];
        }
        else
        {
            $minimum_approval = 100;
            $Vat = 0.15;
            $limit = 30000;
            $limit_top = 60000;
        }
        $current_appproval = ($approved / $com_count) * 100;
        if($cur_sca == "Owner")
        {
            $regular = false;
            $stmt_approvals -> bind_param("i", $row['id']);
            $stmt_approvals -> execute();
            $result_approvals = $stmt_approvals->get_result();
            if($result_approvals->num_rows>0)
            {
                while($row_temp = $result_approvals->fetch_assoc())
                {
                    $role = "Owner";
                    $stmt_account_role -> bind_param("ss", $row_temp['committee_member'], $role);
                    $stmt_account_role -> execute();
                    $result_account_role = $stmt_account_role->get_result();
                    if($result_account_role->num_rows>0)
                        $regular = true;
                }
            }
        }
        else
        {
            $regular = $current_appproval >= $minimum_approval;
        }
        if(!$selections_match)
        {
            $stmt_approvals -> bind_param("i", $row['id']);
            $stmt_approvals -> execute();
            $result_approvals = $stmt_approvals->get_result();
            $sql_item="SELECT requests.unit as unit, requests.requested_quantity as qty , requests.company as comp, requests.item as item FROM `purchase_order` INNER JOIN requests on requests.request_id=purchase_order.request_id where `cluster_id` = ?";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item -> bind_param("i", $row['id']);
            $stmt_item -> execute();
            $items_result = $stmt_item->get_result();
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
            if($items_result->num_rows>0)
            while($item_row=$items_result->fetch_assoc()){
                $comp=$item_row['comp'];
                $table.=" <tr>
                    <td style='$cell_style'>$x</td>
                    <td style='$cell_style' >".$item_row['item']."</td>
                    <td style='$cell_style' >".$item_row['qty']."</td>
                    <td style='$cell_style' >".$item_row['unit']."</td>
                </tr>";
                $x++;
            }
           $table.='</table>';
            if($result_approvals->num_rows>0)
            {
                while($row_temp = $result_approvals->fetch_assoc())
                {
                    $stmt_active_account -> bind_param("s", $row_temp['committee_member']);
                    $stmt_active_account -> execute();
                    $result_active_account = $stmt_active_account->get_result();
                    if($result_active_account->num_rows>0)
                    while($row_temp2 = $result_active_account->fetch_assoc())
                    {
                        $email = $row_temp2['email'];
                        $out = $row_temp2['Username'];
                        $subject_email = "Different items were selected for purchase";
                        $data_email = "
                        <strong>At approval of Comparison sheets different items were selected for purchase at <b>$comp</b>. please resolve for the request to continue. Detail of requests are shown below<br>$table</strong><br><br>";
                        $send_to = $email.",".$out;
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = $out;
                        $reason = "open_clust_$row[id]_selection_mismatch";
                        $user=($_SESSION['username'].":-:".$_SESSION['position']);
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        $email_id = $conn->insert_id;
                    }
                }
            }
        }
        else
        {
            $reason = "open_clust_$row[id]_selection_mismatch";
            $stmt_email_close -> bind_param("s", $reason);
            $stmt_email_close -> execute();
        }
        // echo "current_appproval : $approved / $com_count = $current_appproval<br>";
        // echo "minimum_approval : $minimum_approval<br>";
        // echo "go_on : $go_on<br>";
        // echo "rejected : $rejected<br>";
        // echo "selections_match : $selections_match<br>";

        if($regular && $go_on && !$rejected && $selections_match)
        {
            // echo "SADASDASAS<br>";
            $reason = "open_clust_$row[id]_committee_levl";
            $stmt_email_close -> bind_param("s", $reason);
            $stmt_email_close -> execute();
            $sql_item="SELECT requests.unit as unit, requests.requested_quantity as qty , requests.company as comp, requests.item as item FROM `purchase_order` INNER JOIN requests on requests.request_id=purchase_order.request_id where `cluster_id` = ?";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item -> bind_param("i", $row['id']);
            $stmt_item -> execute();
            $items_result = $stmt_item->get_result();
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
            if($items_result->num_rows>0)
            while($item_row=$items_result->fetch_assoc()){
             $comp=$item_row['comp'];
            $table.=" <tr>
             <td style='$cell_style'>$x</td>
             <td style='$cell_style' >".$item_row['item']."</td>
             <td style='$cell_style' >".$item_row['qty']."</td>
             <td style='$cell_style' >".$item_row['unit']."</td>
           </tr>";
           $x++;
            }
            $table.='</table>';
            if($scale_current == 'Branch')
                $scale =($total>=$limit || $req_com =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
            else
                $scale = $scale_current;
            
            $sql_committee_account = "SELECT * FROM `account` as A inner join committee_approval as CA on A.Username = CA.committee_member where `cluster_id` = ? AND CA.status = 'Approved' AND (department = 'Procurement' AND role='manager' AND company = 'Hagbes HQ.')  AND A.status = 'active'";
            $stmt_committee_account = $conn->prepare($sql_committee_account);
            $stmt_committee_account -> bind_param("i", $row['id']);
            $stmt_committee_account -> execute();
            $res = $stmt_committee_account->get_result();
            if($scale != $scale_current && (($res->num_rows==0 && $scale == 'procurement') || $scale == 'HO'))
            {
                $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `scale`=?  WHERE `cluster_id`=?");
                $stmt_unique -> bind_param("si", $scale, $row['id']);
                $stmt_unique -> execute();
                $stmt_ho_proc_managers -> execute();
                $result_ho_proc_managers = $stmt_ho_proc_managers->get_result();
                if($result_ho_proc_managers->num_rows>0)
                    while($r = $result_ho_proc_managers->fetch_assoc())
                    {
                        $send_to = $r['email'].",".$r['Username'];
                        $reason = "open_clust_".$row['id']."_committee_levl";
                        $subject_email = "Committee approval for requests";
                        $data_email = "
                        <strong>Requests listed in the table below are ready for committee approval <strong><br>
                        Please review the request as soon as possible1<br><br><br>
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
                        include "../common/sms.php";    
                    }
            }
            else
            {
                $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `cluster_id`=? ");
                $stmt -> bind_param("si", $status, $row['id']);
                $stmt -> execute();
                $stmt = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=? ");
                $stmt -> bind_param("si", $status, $row['id']);
                $stmt -> execute();

                $stmt_po_by_cluster_active -> bind_param("i", $row['id']);
                $stmt_po_by_cluster_active -> execute();
                $result_po_by_cluster_active = $stmt_po_by_cluster_active->get_result();
                if($result_po_by_cluster_active->num_rows>0)
                    while($row2 = $result_po_by_cluster_active->fetch_assoc())
                    {
                        updaterequest($conn,$conn_fleet,$row2['request_id'],"three","","Approved");
                        // $l=str_replace(" ","",$row2['request_type']);
                        // $dbs=type_to_dbs($row2['request_type']);
                        $stmt_status_update -> bind_param("si", $status, $row2['request_id']);
                        $stmt_status_update -> execute();
                        // $nxt_stp = "Waiting";
                        if($status == "Approved")
                            $nxt_stp = "Finance";
                        else
                            $nxt_stp = "Rejected";
                        $stmt_next_step -> bind_param("si", $nxt_stp, $row2['request_id']);
                        $stmt_next_step -> execute();
                        $_SESSION["success"]="$status";
                        $stmt_report_com = $conn->prepare("UPDATE `report` SET `committee_approval_date` = ? WHERE `request_id` = ?");
                        $stmt_report_com -> bind_param("si", $date, $row2['request_id']);
                        $stmt_report_com -> execute();
                    }
                $stmt_proc_manager -> bind_param("s", $req_proc_com);
                $stmt_proc_manager -> execute();
                $result_proc_manager = $stmt_proc_manager->get_result();
                if($result_proc_manager->num_rows>0)
                    while($row2 = $result_proc_manager->fetch_assoc())
                    {
                        $email = $row2['email'];
                        $out = $row2['Username'];
                        $subject_email = "Purchase orders have passed committee aprroval";
                        $data_email = "
                        <strong>Purchase orders have passed committee aprroval please review and sent to finance for further processing</strong><br><br><br>
                        ";
                        $send_to = $email.",".$out;
                        $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                        $cc =""; $bcc = ""; $tag = $out;
                        $reason = "open_clust_$row[id]_committee_approved";
                        $user=($users.":-:(committee members)" );
                        $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                        $stmt_email_reason -> execute();
                        $email_id = $conn->insert_id;
                        $page_to = "Procurement/manager/sentToFinance.php";
                        $stmt_email_page -> bind_param("si",$page_to, $email_id);
                        $stmt_email_page -> execute();
                    }
            }
            if($scale != $scale_current)
                $_SESSION["success"]="Items sent To Head Office Procurement Department";
        }
        if(!isset($_SESSION["success"]))
            $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["reactivate"]))
    {
        $c_id = $_GET["reactivate"];
        $stmt_cluster -> bind_param("i", $c_id);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster->get_result();
        $row = $result_cluster->fetch_assoc();
        $reactivate = 'Reactivated';
        $record_type = 'cluster';
        $record = 'Committee Reactivated';
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $c_id, $record);
        $stmt_add_record -> execute();
    
        // $remark = '#';
        $stmt = $conn->prepare("UPDATE `committee_approval` SET `status`=? WHERE `committee_member`=? AND `cluster_id` = ?");
        $stmt -> bind_param("ssi", $reactivate, $_SESSION['username'], $row['id']);
        $stmt -> execute();
        $_SESSION["success"]="$reactivate";
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET["reject"]))
    {
        $c_id = $_GET["reject"];
        $stmt_cluster -> bind_param("i", $c_id);
        $stmt_cluster -> execute();
        $result_cluster = $stmt_cluster->get_result();
        $row = $result_cluster->fetch_assoc();

        $record_type = 'cluster';
        $record = 'Committee Rejected';
        $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $c_id, $record);
        $stmt_add_record -> execute();
    
        $status = "Rejected";
        $reason = (!isset($_GET['reason']) || $_GET['reason']=='')?"#":$_GET['reason'];
        $date=date("Y-m-d H:i:s");
        $sql_prev_approved = "SELECT id FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
        $stmt_prev_approved = $conn->prepare($sql_prev_approved);
        $stmt_prev_approved -> bind_param("si",$_SESSION['username'] ,$row['id']);
        $stmt_prev_approved -> execute();
        $result_prev_approved = $stmt_prev_approved->get_result();
        if($result_prev_approved->num_rows>0)
            while($row_temp = $result_prev_approved->fetch_assoc())
            {
                $stmt = $conn->prepare("UPDATE `committee_approval` SET `status`=?, `remark`=?, `timestamp`=? WHERE `id`=?");
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
        $_SESSION["success"]=true;
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
}
else
    header("location: ".$_SERVER['HTTP_REFERER']);
$conn->close();
$conn_fleet->close();
$conn_ws->close();
?>