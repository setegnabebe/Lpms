<?php
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // $date_today = date("Y-m-d");
    // $sql = "SELECT * from auto_request where `date` = '$date_today'";
    // $result = $conn->query($sql);
    // if($result->num_rows == 0)
    // {
    //     $request_ids = "";
    //     $sql = "SELECT * from admin_settings order by id Desc Limit 1";
    //     $result = $conn->query($sql);
    //     if($result->num_rows == 0)
    //     {
    //         $row = $result->fetch_assoc();
    //         $auto_time = intval($row['logout_time_min']);
    //     }
    //     else
    //         $auto_time = 21;
        
    //     $date_limit = date("Y-m-d",strtotime("+".$auto_time." Days", strtotime($date_today)));
    //     $sql_project = "SELECT * FROM projects";// WHERE `status` = '3'
    //     $result_project = $conn_pms->query($sql_project);
    //     if($result_project->num_rows>0)
    //         while($row_project = $result_project->fetch_assoc())
    //         {
    //             $sql_task = "SELECT * FROM task where `id_p` = '$row_project[id]'";
    //             $result_task = $conn_pms->query($sql_task);
    //             if($result_task->num_rows>0)
    //                 while($row_task = $result_task->fetch_assoc())
    //                 {
    //                     $sql_item = "SELECT * FROM item where `id_t` = '$row_task[id]' and `date_needed_for` = '$date_limit'";
    //                     $result_item = $conn_pms->query($sql_item);
    //                     if($result_item->num_rows>0)
    //                         while($row_item = $result_item->fetch_assoc())
    //                         {
    //                             $request_for = $row_project['id']."|".$row_task['id']."|".$row_item['id'];
    //                             $remaining_boq = $row_item['total_quantity'];
    //                             $sql = "SELECT * FROM `requests` WHERE `request_for` = '$request_for' AND `status` NOT LIKE 'Reject%' AND `status` != 'canceled'";
    //                             $result = $conn->query($sql);
    //                             if($result->num_rows == 0)
    //                             {
    //                                 $sql_user = "SELECT * FROM `account` WHERE `username` = '$row_project[created_by]'";
    //                                 $result_user = $conn_pms->query($sql_user);
    //                                 if($result_user->num_rows > 0)
    //                                     $row_user = $result_user->fetch_assoc();
    //                                 else 
    //                                 {
    //                                     $row_user['company'] = 'Hagbes HQ.';
    //                                     $row_user['department'] = 'Director';
    //                                 }
    //                                 $sql_comp = "SELECT * FROM `comp` WHERE `Name` = '$row_user[company]'";
    //                                 $result_comp = $conn_fleet->query($sql_comp);
    //                                 if($result_comp->num_rows > 0)
    //                                     {
    //                                         $row_comp = $result_comp->fetch_assoc();
    //                                         $property_company = ($row_comp['property'])?$row_user["company"]:$row_comp['main'];
    //                                         $procurement_company = ($row_comp['procurement'])?$row_user["company"]:$row_comp['main'];
    //                                         $finance_company = ($row_comp['finance'])?$row_user["company"]:$row_comp['main'];
    //                                         $cheque_company = ($row_comp['cheque_signatory'])?$row_user["company"]:$row_comp['main'];
    //                                         $processing_company = $row_comp['main'];
    //                                     }
    //                                 else
    //                                 {
    //                                     $processing_company =  $property_company = $procurement_company = $finance_company = $cheque_company =$row_user['company'];
    //                                 }
    //                                 $stmt_unique = $conn->prepare("INSERT INTO `requests`
    //                                 (`request_for`, `request_type`, `customer`, `item`,
    //                                 `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
    //                                 `Remark`, `description`,
    //                                 `department`, `status`, `company`, `processing_company`, `property_company`, `procurement_company`, `finance_company`) 
    //                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //                                     $status = 'waiting';
    //                                     $uname="LPMS System";
    //                                     $date_req=date("Y-m-d H:i:s");
    //                                     $type='Consumer Goods';
    //                                     $stmt_unique -> bind_param("ssssissssssssssss",$request_for,$type, $uname, $row_item['item_name'], $row_item['total_quantity'], $row_item['unit'], $date_req, $row_item['date_needed_for']
    //                                     , $row_item['remark'], $row_item['description'], $row_user['department'], $status, $row_user['company'], $processing_company, $property_company, $procurement_company, $finance_company);
    //                                     if($stmt_unique -> execute())
    //                                     {
    //                                         $last_id = $conn->insert_id;
    //                                         $request_ids .= ($request_ids == "")?$last_id:",".$last_id;
    //                                         $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) 
    //                                         VALUES (?, ?, ?)");
    //                                         $stmt-> bind_param("iss",$last_id, $type, $date_req);
    //                                         $stmt -> execute();
    //                                     }
    //                             }
    //                         }
    //                 }
    //         }
    //         $sql2 = "INSERT INTO `auto_request`(`date`, `requests`) VALUES ('$date_today','$request_ids')";
    //         $conn->query($sql2);
    // }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>