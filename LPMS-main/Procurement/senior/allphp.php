<?php
session_start();
    include "../../connection/connect.php";
    include "../../common/functions.php";
if(isset($_GET['plate']))
{
    $sql = "SELECT driver , company FROM `vehicle` WHERE `plateno` =? ";
    $stmt2 = $conn_fleet->prepare($sql);
    $stmt2->bind_param("s", $_GET['plate']);
    $stmt2->execute();
    $stmt2->store_result();
    $stmt2->bind_result($driver, $company);
    $stmt2->fetch();
    $stmt2->close();
    echo $driver."_".$company;
}
if(isset($_POST['fileuploader_btn']))
{
    $file=$_FILES['history_csv']['name'];
    $filename=$_FILES['history_csv']['tmp_name'];
    $path_parts = pathinfo($file);
    $datePosting = date("Y-m-d H:i:s");
    if(strtolower($path_parts['extension'])=="csv")
    {
        $row = 0;
        $result=false;
        if($_FILES["history_csv"]["size"] > 0)
        {
            $file = fopen($filename, "r");
            $x=0;
            while (($data = fgetcsv($file, 10000, ",")) !== FALSE)
            {	
                if((++$x)!=1 && $data[0]!="")
                {
                    $date=explode(" ",$data[4])[0];
                    $time=explode(" ",$data[4])[1];
                    $date_data=explode("/",$date);
                    $date_time=$date_data[2].'-'.$date_data[0].'-'.$date_data[1].' '.$time;
                    $sql = "INSERT INTO `history_jacket` (`vehicle`, `item`, `quantity`, `serial`, `date_purchased`, `kilometer`, `description`, `km_diff`, `time_diff`, `inserted_by`) VALUES (?,?,?,?,?,?,?,?,?,?)";
                    $stmt_record_history_jacket = $conn->prepare($sql);
                    $stmt_record_history_jacket -> bind_param("ssissisiss", $data[0], $data[1], $data[2], $data[3], $date_time, $data[5], $data[6], $data[7], $data[8], $_SESSION['username']);
                    $stmt_record_history_jacket -> execute();
                }
            }
            // echo $sql;
        }
        if($result)
            $_SESSION["success"]="$x history jacket date added from excel sheet";
        else 
            $_SESSION['error']="Error occurred while adding records from excel sheet";
    }
    else
    {
        $_SESSION['error']="File format should always be CSV";
    }   
    header("location: ".$_SERVER['HTTP_REFERER']);
}
// echo isset($_POST['save_history']);
if(isset($_POST['save_history']))
{
    $sql="UPDATE `history_jacket` SET `item` = ?,`quantity` = ?,`kilometer` = ?,`description` = ?,`km_diff` = ?,`time_diff` = ? WHERE id = ?";
    $stmt_update_history_jacket = $conn->prepare($sql);
    $stmt_update_history_jacket -> bind_param("siisssi", $_POST['item'], $_POST['qty'], $_POST['km'], $_POST['desc'], $_POST['kmdiff'], $_POST['timediff'], $_POST['id']);
    if($stmt_update_history_jacket -> execute())
        $_SESSION['success']="History jacket updated successfully";
    else
    $_SESSION['error']="History jacket didnot updated successfully";

    header("location: ".$_SERVER['HTTP_REFERER']);
}
if(isset($_SESSION['username']))
{
    if(isset($_GET['remove_p']))
    {
        $id = explode("_",$_GET['remove_p'])[1];
        $remove = explode("_",$_GET['remove_p'])[0];
        $stmt_performa -> bind_param("i", $id);
        $stmt_performa -> execute();
        $result_performa = $stmt_performa -> get_result();
        $row_performa = $result_performa->fetch_assoc();
        $allfiles = explode(":_:",$row_performa['files']);
        $new_performa = "";
        $count = 0;
        foreach($allfiles as $file)
        {
            $count++;
            if($count != $remove)
                $new_performa.=($new_performa == "")?$file:":_:".$file;
            else
                $remove_file = $file;
        }
        $path = "../../../lpms_uploads/";
        unlink($path.$remove_file);
        $sql2 = "UPDATE performa SET files = ? WHERE `id`=?";
        $stmt_update_performa = $conn->prepare($sql2);
        $stmt_update_performa -> bind_param("si", $new_performa, $id);
        if($stmt_update_performa -> execute())
            echo "success";
        else
            echo "fail";
        unset($_GET['remove_p']);
    }
    if(isset($_GET['create_History']))
    {
        $sql = "INSERT INTO `history_jacket` (`vehicle`, `item`, `quantity`, `serial`, `date_purchased`, `kilometer`, `description`, `km_diff`, `time_diff`, `inserted_by`) VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt_record_history_jacket = $conn->prepare($sql);
        $stmt_record_history_jacket -> bind_param("ssissisiss", $_GET['plate'], $_GET['item'], $_GET['quantity'], $_GET['serial'], $_GET['date'], $_GET['kilometer'], $_GET['desc_item'], $_GET['km_diff'], $_GET['time_diff'], $_SESSION['username']);
        if ($stmt_record_history_jacket -> execute()) 
        {
            $_SESSION['success'] = 'Added To History';
        } 
        else
        {
            echo "Error: " . $sql . "<br>" . $conn->error. "<br>" ;
            $_SESSION['success'] = 'Failed';
        }
        header("location: ".$_SERVER['HTTP_REFERER']);
    }

    if(isset($_POST["insert_performa"]))
    {
        $file_name = array_filter($_FILES['performa']['name']);
        $total_count = count($_FILES['performa']['name']);
        $file_size=0;
        $id = $_POST["insert_performa"];
        $stmt_po -> bind_param("i", $id);
        $stmt_po -> execute();
        $result_po = $stmt_po -> get_result();
        $row = $result_po -> fetch_assoc();
        $c_id = $row['cluster_id'];
        $performa_id = $row['performa_id'];
        $stmt_performa -> bind_param("i", $performa_id);
        $stmt_performa -> execute();
        $result_performa = $stmt_performa -> get_result();
        $row = $result_performa -> fetch_assoc();
        $new_file = $row['files'];
        $error = "";
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
                $newFilePath = "../../../lpms_uploads/Performa-Batch-".$c_id."-".$uniq.$extention;
                if(move_uploaded_file($file_tmp, $newFilePath))
                {
                    $new_file .= ($new_file=="")?"":":_:";
                    $new_file.="Performa-Batch-".$c_id."-".$uniq.$extention;
                }
                else
                {
                    $error .= ($error=="")?"":"";
                    $error .= "'".$_FILES['performa']['name'][$i]."'";
                }
            }
        }
        $error .= ($error=="")?"":" not Uploaded Successfully";
        $error_count = count(explode(", ",$error));
        $_SESSION["success"]=($error == "")?"Uploaded Successfully":$error_count." files not Uploaded";
        $sql2 = "UPDATE performa SET files = ? WHERE `id`=?";
        $stmt_update_performa = $conn->prepare($sql2);
        $stmt_update_performa -> bind_param("si", $new_file, $performa_id);
        $stmt_update_performa -> execute();
        header("location: ".$_SERVER['HTTP_REFERER']);
    }
    if(isset($_GET['cluster']))
    {
        if($_GET['option']==1)
            $status = "edit";
        else
        {
            $po_sql = "SELECT `status` from purchase_order where cluster_id = ? limit 1";
            $stmt_status_po = $conn->prepare($po_sql);
            $stmt_status_po -> bind_param("i", $_GET['cluster']);
            $stmt_status_po -> execute();
            $result_status_po = $stmt_status_po -> get_result();
            $status_row = $result_status_po -> fetch_assoc();
            $status = $status_row['status'];
        }
        $stmt_cluster_status -> bind_param("si", $status, $_GET['cluster']);
        if($stmt_cluster_status -> execute())
        {
            echo 0;
        }
        else
        {
            echo 1;
        }
    }
        ////////////////////////////////////////////////////for Senior
        if(isset($_POST['generate']))
        {
            $stmt_limit -> bind_param("i", $_SESSION['company']);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
            if ($result_limit -> num_rows == 0)
            {
                $compaines = "Others";
                $stmt_limit -> bind_param("i", $compaines);
                $stmt_limit -> execute();
                $result_limit = $stmt_limit->get_result();
            }
            if($result_limit -> num_rows>0)
            {
                $r_new = $result_limit -> fetch_assoc();
                $Vat = $r_new['Vat'];
                $limit = $r_new['amount_limit'];
                $limit_top = $r_new['amount_limit_top'];
            }
            else 
            {
                // $Vat = 0.15;
                $limit = 30000;
                $limit_top = 60000;
            }
            $remarks_creator = $_POST['remarks_creator'];
            /////////////////////////////////////////////////////////create Cluster
            $stmt = $conn->prepare("INSERT INTO `cluster` (`type`, `Remarks` , `company`, `compiled_by`) VALUES (?,?,?,?)");
            $stmt -> bind_param("ssss",$_POST['name'],$remarks_creator,$_SESSION['company'],$_SESSION['username']);
            if($stmt -> execute())
            {
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
                        $newFilePath = "../../../lpms_uploads/Performa-Batch-".$c_id."-".$uniq.$extention;
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
                $sql = "INSERT INTO `performa` (`files`) VALUES (?)";
                $stmt_record_performa = $conn->prepare($sql);
                $stmt_record_performa -> bind_param("s", $new_file);
                $stmt_record_performa -> execute();
                // echo $conn -> error;
                $performa_id = $conn->insert_id;
                
                //////////////////////////////////////////////////////////
                $i=0;//////number of total item counter
                $quantity=0;/////////////////

                //////////////////////////////////////////////////////////////////////insert price info
                for($j=0;$j<count($_POST['company']);$j++)
                {
                    // echo $_POST['company'][$j]."<br>";
                    $quantity =$quantity+intval($_POST['item_c'][$j]);
                    while($i<$quantity)
                    {
                        // echo $quantity."<br>";
                        $selected = false;
                        // if(is_array($_POST[$_POST['item'][$i]]))
                        // {
                        //     $selected = false;
                        //     foreach($_POST[$_POST['item'][$i]] as $com)
                        //     {
                        //         if($com-1 == $j)
                        //             $selected = true;
                        //     }
                        // }
                        // else
                        // {
                        //     $selected = false;
                        //     if($j==$_POST[$_POST['item'][$i]]-1)////because company number starts from 1 in the other page
                        //     {
                        //         $selected = true;
                        //     }
                        // }

                        $vats = [];
                        foreach($_POST['vat_item'] as $inde=>$vv)
                        {
                            $vats[$_POST['vat_for'][$inde]] = $vv;
                        }
                        
                        $company_name = $_POST['company'][$j];
                        $after_vat = floatval($_POST['t_price'][$i])*$vats[$_POST['item'][$i]]+ floatval($_POST['t_price'][$i]);
                        $stmt_unique = $conn->prepare("INSERT INTO `price_information` (`purchase_order_id`, `cluster_id`, `providing_company`, `quantity`, `price`,`vat`, `total_price`, `after_vat`, `specification`, `selected`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
                        $stmt_unique -> bind_param("iisiddddsi", $_POST['item'][$i], $c_id, $company_name, $_POST['avquan'][$i], $_POST['price'][$i],$vats[$_POST['item'][$i]],$_POST['t_price'][$i],$after_vat, $_POST['spec'][$i], $selected);
                        $stmt_unique -> execute();

                        $status = "Generated";
                        $stmt = $conn->prepare("UPDATE `purchase_order` SET `performa_id`=? , `status`=? , `cluster_id`=?  WHERE `purchase_order_id`=? ");
                        $stmt -> bind_param("isii", $performa_id, $status, $c_id, $_POST['item'][$i]);
                        $stmt -> execute();

                        $i++;
                    }
                }
                ////////////////////////////////////////////////////////////////////////////////////////
                $date=date("Y-m-d H:i:s");
                $avg_price = 0;
                $sql = "SELECT DISTINCT(purchase_order_id) FROM `price_information` WHERE `cluster_id` = ?";
                $stmt_price_poi = $conn->prepare($sql);
                $stmt_price_poi -> bind_param("i", $c_id);
                $stmt_price_poi -> execute();
                $result_price_poi = $stmt_price_poi -> get_result();
                if($result_price_poi -> num_rows>0)
                while($r = $result_price_poi -> fetch_assoc())
                {
                    $stmt_po -> bind_param("i", $r['purchase_order_id']);
                    $stmt_po -> execute();
                    $result_po = $stmt_po -> get_result();
                    $r_po = $result_po -> fetch_assoc();
                    $stmt_request -> bind_param("i", $r_request['request_id']);
                    $stmt_request -> execute();
                    $result_request = $stmt_request -> get_result();
                    $r_po = $result_request -> fetch_assoc();
                    updaterequest($conn,$conn_fleet,$r_po['request_id'],"two","","Comparision_create");
                    $quantity_item = $r_po['requested_quantity'];
                    $stmt2 = $conn->prepare("SELECT quantity,AVG(`price`),vat AS total FROM `price_information` WHERE `cluster_id`=? AND purchase_order_id = ?");
                    $stmt2 -> bind_param("ii",$c_id,$r['purchase_order_id']);
                    $stmt2->execute();
                    $stmt2->store_result(); 
                    $stmt2->bind_result($quantity_item,$total,$Vat);
                    $stmt2->fetch();
                    $stmt2->close();
                    $total = ($total * $quantity_item) * (1 + $Vat);
                    $avg_price+=$total;
                }
                // $total = ($Vat * $avg_price) + $avg_price;
                // $total = round($total ,2);
                $total = $avg_price;
                $stmt2 = $conn->prepare("SELECT `request_type` FROM `purchase_order` WHERE `cluster_id`=?");
                $stmt2 -> bind_param("i",$c_id);
                $stmt2->execute();
                $stmt2->store_result(); 
                $stmt2->bind_result($type);
                $stmt2->fetch();
                $stmt2->close();
                $sql_item="SELECT * FROM `purchase_order` inner join requests on requests.request_id=purchase_order.request_id WHERE `cluster_id` = ?";
                $stmt_po_req_cluster = $conn -> prepare($sql_item);
                $stmt_po_req_cluster -> bind_param("i", $c_id);
                $stmt_po_req_cluster -> execute();
                $item_result = $stmt_po_req_cluster -> get_result();
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
                if($item_result->num_rows>0)
                while($item_row = $item_result->fetch_assoc())
                {
                    $comp=$item_row['company'];
                    $table.="
                    <tr>
                        <td style='$cell_style'>$x</td>
                        <td style='$cell_style' >".$item_row['item']."</td>
                        <td style='$cell_style' >".$item_row['requested_quantity']."</td>
                        <td style='$cell_style' >".$item_row['unit']."</td>
                    </tr>";
                    $x++;
                }
                $table.='</table>';
                
                 
                // if($type == 'Fixed Assets')
                //     $scale = 'Owner';
                // else
                //     $scale =($total>=$limit || $_SESSION['company'] =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                // if($_SESSION['company'] =='Hagbes HQ.')
                //     $scale =($total>=$limit_top)?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                // $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `scale`=? , `timestamp`=? WHERE `cluster_id`=?");
                // $stmt_unique -> bind_param("ssi", $scale, $date, $c_id);
                // $stmt_unique -> execute();

                $stmt_unique = $conn->prepare("UPDATE `cluster` SET `price`=? WHERE `id`=?");
                $stmt_unique -> bind_param("si", $total, $c_id);
                $stmt_unique -> execute();
        //////////////////////////////////////////////////////Report/////////////////////////////////////////////////////
                $stmt_po_cluster -> bind_param("i", $c_id);
                $stmt_po_cluster -> execute();
                $result_po_cluster = $stmt_po_cluster -> get_result();
                if($result_po_cluster -> num_rows > 0)
                    while($r = $result_po_cluster -> fetch_assoc())
                    {
                        $stmt_request -> bind_param("i", $r['request_id']);
                        $stmt_request -> execute();
                        $result_request = $stmt_request -> get_result();
                        $r_dbs = $result_request -> fetch_assoc();
                        $req_dep = $r_dbs['department'];
                        
                        $sql2 = "UPDATE cluster SET `company`=?, processing_company=?, procurement_company=?, finance_company=? WHERE `id`=?";
                        $stmt_record_performa = $conn->prepare($sql2);
                        $stmt_record_performa -> bind_param("ssssi", $r_dbs['company'], $r_dbs['processing_company'], $r_dbs['procurement_company'], $r_dbs['finance_company'], $c_id);
                        $stmt_record_performa -> execute();
                        
                        if($type == 'Fixed Assets')
                            $scale = 'Owner';
                        else
                            $scale =($r_dbs['company'] =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                            // $scale =($total>=$limit || $r_dbs['company'] =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                            
                        $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `scale`=?  WHERE `cluster_id`=?");
                        $stmt_unique -> bind_param("si", $scale, $c_id);
                        $stmt_unique -> execute();
                        $status = 'Committee Approval';
                        $sql_rep = "UPDATE `report` SET `compsheet_generated_date` = ? WHERE `request_id` = ?";
                        $stmt_record_performa = $conn->prepare($sql_rep);
                        $stmt_record_performa -> bind_param("si", $date, $r['request_id']);
                        $stmt_record_performa -> execute();
                        // $l=str_replace(" ","",$r['request_type']);
                        $nxt_step = "$scale Committee";
                        $stmt_status_next -> bind_param("ssi", $nxt_step, $status, $r['request_id']);
                        $stmt_status_next -> execute();

                    $na_t=str_replace(" ","",$r['request_type']);
                    $reason_close = "open_req_".$na_t."_".$r["request_id"]."_performa_opened";
                    $stmt_email_close -> bind_param("s",$reason_close);
                    $stmt_email_close -> execute();
                    }
                
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $_SESSION["success"]="Comparision Sheet Successfully Created";
                $send_to = "";
                $stmt_proc_manager -> bind_param("s", $_SESSION['company']);
                $stmt_proc_manager -> execute();
                $result_proc_manager = $stmt_proc_manager -> get_result();
                if($result_proc_manager -> num_rows>0)
                    while($r = $result_proc_manager -> fetch_assoc())
                    {
                        $tag = $r['Username'];
                        $send_to.=($send_to=="")?$r['email'].",".$r['Username']:",".$r['email'].",".$r['Username'];
                    }
                    
                    $reason = "open_clust_".$c_id."_review_cs";
                    $subject_email = "Please review Comparison sheet and proforma and send for committee approval";
                    $data_email = "
                    <strong>Comparision sheet was created for requests shown in the table below at $comp</strong><br>
                    Please review Comparison sheet and proforma and send for committee approval<br> 
                    ";
                 
                    $data_email.=$table; ;
                    $cc =""; $bcc = ""; 
                    $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                    $user=($_SESSION['username'].":-:".$_SESSION['position']);
                    $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                    $stmt_email_reason -> execute();

                    $email_id = $conn->insert_id;
                    $page_to = "Procurement/manager/check_comp_sheet.php";
                    $stmt_email_page -> bind_param("si",$page_to, $email_id);
                    $stmt_email_page -> execute();
                header("location: ".$_SERVER['HTTP_REFERER']);
            }
            else 
                echo $conn->error;
        }
                ////////////////////////////////////////////////////for Senior
                if(isset($_POST['edit_comp_sheet']))
                {
                    $c_id=$_POST['edit_comp_sheet'];
                    $sql_item="SELECT * FROM `purchase_order` inner join requests on requests.request_id=purchase_order.request_id WHERE `cluster_id` = ?";
                    $stmt_items = $conn->prepare($sql_item);
                    $stmt_items -> bind_param("i", $c_id);
                    $stmt_items -> execute();
                    $result_items = $stmt_items -> get_result();
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
                    if($result_items->num_rows>0)
                    while($item_row = $result_items->fetch_assoc())
                    {
                        $comp=$item_row['company'];
                        $table.="
                        <tr>
                            <td style='$cell_style'>$x</td>
                            <td style='$cell_style' >".$item_row['item']."</td>
                            <td style='$cell_style' >".$item_row['requested_quantity']."</td>
                            <td style='$cell_style' >".$item_row['unit']."</td>
                        </tr>";
                        $x++;
                    }
                    $table.='</table>';
                    $stmt_limit -> bind_param("s", $comp);
                    $stmt_limit -> execute();
                    $result_limit = $stmt_limit->get_result();
                    if ($result_limit -> num_rows == 0)
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
                        $limit = 30000;
                        $limit_top = 60000;
                        $Vat = 0.15;
                    }
                    $remarks_creator = $_POST['remarks_creator'];
                    if($c_id)
                    {
                        $file_name = array_filter($_FILES['performa']['name']);
                        $total_count = count($_FILES['performa']['name']);
                        $file_size=0;
 
                        $error = "";
                        $new_file = "";
                        for( $j=0 ; $j < $total_count ; $j++ )
                        {
                            $file_size = $file_size+$_FILES['performa']['size'][$j];
                        }
                        for( $i=0 ; $i < $total_count ; $i++ ) 
                        {
                            $file_tmp = $_FILES['performa']['tmp_name'][$i];
                            if ($file_tmp != "")
                            {
                                $uniq = uniqid();
                                $extention = ".".explode('.',$_FILES['performa']['name'][$i])[(sizeof(explode('.',$_FILES['performa']['name'][$i]))-1)];
                                $newFilePath = "../../../lpms_uploads/Performa-Batch-".$c_id."-".$uniq.$extention;  
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
                        $performa="SELECT `files`,id FROM  `performa` WHERE `id` = (SELECT performa_id from purchase_order where cluster_id = ? LIMIT 1);";
                        $stmt_performa_found = $conn->prepare($performa);
                        $stmt_performa_found -> bind_param("i", $c_id);
                        $stmt_performa_found -> execute();
                        $result_performa_found = $stmt_performa_found -> get_result();
                        if($result_performa_found -> num_rows>0)
                           $row_data=$result_performa_found -> fetch_assoc();
                        $performa_id= $row_data['id'];
                        $new_file=$row_data['files'].":_:".$new_file;
                        $sql_performa = "UPDATE `performa` set `files` = ? where id = ?";
                        $stmt_performa_update = $conn->prepare($sql_performa);
                        $stmt_performa_update -> bind_param("si", $new_file, $performa_id);
                        $stmt_performa_update -> execute();
                          //////////////////////////////////////////////////////////
                        $i=0;//////number of total item counter
                        $quantity=0;/////////////////
        
                         //////////////////////////////////////////////////////////////////////insert price info
                        for($j=0;$j<count($_POST['company']);$j++)
                        {
                             // echo $_POST['company'][$j]."<br>";
                            $quantity =$quantity+intval($_POST['item_c'][$j]);
                            while($i<$quantity)
                            {
                                
                                $selected = false;
                                $vats = [];
                                foreach($_POST['vat_item'] as $inde=>$vv)
                                {
                                    $vats[$_POST['vat_for'][$inde]] = $vv;
                                }
                                $company_name = $_POST['company'][$j];
                                $after_vat = floatval($_POST['t_price'][$i])*$vats[$_POST['item'][$i]]+ floatval($_POST['t_price'][$i]);
                                $stmt_unique = $conn->prepare("INSERT INTO `price_information` (`purchase_order_id`, `cluster_id`, `providing_company`, `quantity`, `price`,`vat`, `total_price`, `after_vat`, `specification`, `selected`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
                                $stmt_unique -> bind_param("iisiddddsi", $_POST['item'][$i], $c_id, $company_name, $_POST['avquan'][$i], $_POST['price'][$i],$vats[$_POST['item'][$i]],$_POST['t_price'][$i],$after_vat, $_POST['spec'][$i], $selected);
                                $stmt_unique -> execute();
                                $i++;
                            }
                        }
                    $status = "updated";
                    $sql = "UPDATE cluster SET `status` = ?, Remarks = ? where id = ?";
                    $stmt_update_cluster = $conn->prepare($sql);
                    $stmt_update_cluster -> bind_param("ssi", $status, $_POST['remarks_creator'], $c_id);
                    $stmt_update_cluster -> execute();

                    $status = "Reactivated";
                    $sql="UPDATE committee_approval SET `status` = ? where `cluster_id` = ?";
                    $stmt_update_committee_approval = $conn->prepare($sql);
                    $stmt_update_committee_approval -> bind_param("si", $status, $c_id);
                    $stmt_update_committee_approval -> execute();

                    $record_type = "cluster";
                    $record = "Cluster Updated";
                    $stmt_add_record -> bind_param("ssis", $_SESSION['username'], $record_type, $c_id, $record);
                    $stmt_add_record -> execute();

                //         ////////////////////////////////////////////////////////////////////////////////////////
                    $date=date("Y-m-d H:i:s");
                    $avg_price = 0;
                    $sql = "SELECT DISTINCT(purchase_order_id) FROM `price_information` WHERE `cluster_id` = ?";
                    $stmt_price_information = $conn->prepare($sql);
                    $stmt_price_information -> bind_param("i", $c_id);
                    $stmt_price_information -> execute();
                    $result_price_information = $stmt_price_information -> get_result();
                    if($result_price_information -> num_rows > 0)
                    while($r = $result_price_information -> fetch_assoc())
                    {
                        $stmt_po -> bind_param("i", $r['purchase_order_id']);
                        $stmt_po -> execute();
                        $result_po = $stmt_po -> get_result();
                        $r_po = $result_po -> fetch_assoc();
                        
                        $stmt_request -> bind_param("i", $r_po['request_id']);
                        $stmt_request -> execute();
                        $result_request = $stmt_request -> get_result();
                        $row = $result_request->fetch_assoc();
                        $quantity_item = $row['requested_quantity'];
                        $stmt2 = $conn->prepare("SELECT AVG(`price`) AS total FROM `price_information` WHERE `cluster_id`=? AND purchase_order_id = ?");
                        $stmt2 -> bind_param("ii",$c_id,$r['purchase_order_id']);
                        $stmt2->execute();
                        $stmt2->store_result(); 
                        $stmt2->bind_result($total);
                        $stmt2->fetch();
                        $stmt2->close();
                        $total = $total * $quantity_item;
                        $avg_price+=$total;
                    }
                    $total = ($Vat * $avg_price) + $avg_price;
                    $total = round($total ,2);
                    $stmt2 = $conn->prepare("SELECT `request_type` FROM `purchase_order` WHERE `cluster_id`=?");
                    $stmt2 -> bind_param("i",$c_id);
                    $stmt2->execute();
                    $stmt2->store_result(); 
                    $stmt2->bind_result($type);
                    $stmt2->fetch();
                    $stmt2->close();
                    $stmt_unique = $conn->prepare("UPDATE `cluster` SET `price`=? WHERE `id`=?");
                    $stmt_unique -> bind_param("si", $total, $c_id);
                    $stmt_unique -> execute();
                //  //////////////////////////////////////////////////////Report/////////////////////////////////////////////////////
                    $stmt_po_cluster -> bind_param("i", $c_id);
                    $stmt_po_cluster -> execute();
                    $result_po_cluster = $stmt_po_cluster -> get_result();
                    if($result_po_cluster -> num_rows > 0)
                        while($r = $result_po_cluster -> fetch_assoc())
                        {
                            $stmt_request -> bind_param("i", $r['request_id']);
                            $stmt_request -> execute();
                            $result_request = $stmt_request -> get_result();
                            $r_dbs = $result_request -> fetch_assoc();
                            $req_dep = $r_dbs['department'];
                            
                            $sql2 = "UPDATE cluster SET `company` = ?, processing_company = ?, procurement_company = ?, finance_company = ? WHERE `id` = ?";
                            $stmt_price_information = $conn->prepare($sql2);
                            $stmt_price_information -> bind_param("ssssi", $r_dbs['company'], $r_dbs['processing_company'], $r_dbs['procurement_company'], $r_dbs['finance_company'], $c_id);
                            $stmt_price_information -> execute();
                            
                            if($type == 'Fixed Assets')
                                $scale = 'Owner';
                            else
                                    $scale =($r_dbs['company'] =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                                    // $scale =($total>=$limit || $r_dbs['company'] =='Hagbes HQ.')?(($total>=$limit_top)?'HO':'procurement'):'Branch';
                                
                            $stmt_unique = $conn->prepare("UPDATE `purchase_order` SET `scale`=?  WHERE `cluster_id`=?");
                            $stmt_unique -> bind_param("si", $scale, $c_id);
                            $stmt_unique -> execute();
                            $status = 'Committee Approval';
                            $sql_rep = "UPDATE `report` SET `compsheet_generated_date` = ? WHERE `request_id` = ?";
                            $stmt_report = $conn->prepare($sql_rep);
                            $stmt_report -> bind_param("si", $date, $r['request_id']);
                            $stmt_report -> execute();
                            // $l=str_replace(" ","",$r['request_type']);
                            $nxt_step = "$scale Committee";
                            $stmt_status_next -> bind_param("ssi", $nxt_step, $status, $r['request_id']);
                            $stmt_status_next -> execute();
                            $na_t=str_replace(" ","",$r['request_type']);
                            $reason_close = "open_req_".$na_t."_".$r["request_id"]."_performa_opened";
                            $stmt_email_close -> bind_param("s",$reason_close);
                            $stmt_email_close -> execute();
                        }
               
        
                         $_SESSION["success"]="Comparision Sheet Successfully Updated";
                         $send_to = "";
                         $stmt_proc_manager -> bind_param("s", $_SESSION['company']);
                         $stmt_proc_manager -> execute();
                         $result_proc_manager = $stmt_proc_manager -> get_result();
                         if($result_proc_manager -> num_rows > 0)
                             while($r = $result_proc_manager -> fetch_assoc())
                             {
                                 $tag = $r['Username'];
                                 $send_to.=($send_to=="")?$r['email'].",".$r['Username']:",".$r['email'].",".$r['Username'];
                             }
                            
                             $reason = "open_clust_".$c_id."_review_cs";
                             $subject_email = "Please review Comparison sheet and proforma and send for committee approval";
                             $data_email = "
                             <strong>Comparision sheet was created for requests listed below at $comp</strong><br>
                             Please review Comparison sheet and proforma and send for committee approval<br><br>
                             ";
                             $data_email.=$table;
                            //  echo $data_email;
                             $cc =""; $bcc = ""; 
                             $com_lo = $_SESSION['company'].",".$_SESSION['logo'];
                             $user=($_SESSION['username'].":-:".$_SESSION['position']);
                             $stmt_email_reason -> bind_param("ssssssssss",$project_name, $send_to, $cc, $bcc, $subject_email, $data_email, $tag, $com_lo, $reason,$user);
                             $stmt_email_reason -> execute();
        
                             $email_id = $conn->insert_id;
                             $page_to = "Procurement/manager/check_comp_sheet.php";
                             $stmt_email_page -> bind_param("si",$page_to, $email_id);
                             $stmt_email_page -> execute();
                
                         header("location: ".$_SERVER['HTTP_REFERER']);
                    }
                    else 
                        echo $conn->error;
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