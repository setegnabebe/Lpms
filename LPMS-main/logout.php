<?php
    session_start();
   include "connection/connect.php";
   $logoutdate = date('Y-m-d H:i:s');
   if(!isset($_SESSION['log_id'])) $_SESSION['log_id'] = 0;
   $sql_admin_settings = "SELECT * from admin_settings order by id Desc Limit 1";
   $stmt_admin_settings = $conn -> prepare($sql_admin_settings);
   $stmt_admin_settings -> execute();
   $result_admin_settings = $stmt_admin_settings -> get_result();
   if($result_admin_settings -> num_rows>0)
       while($row = $result_admin_settings -> fetch_assoc())
       {
           $timeout = $row['logout_time_min'];
       }
   $sql_log_login = "SELECT * FROM `log` where `prev_id` = ?";
   $stmt_log_login = $conn -> prepare($sql_log_login);
   $stmt_log_login -> bind_param("i", $_SESSION['log_id']);
   $stmt_log_login -> execute();
   $result_log_login = $stmt_log_login -> get_result();
   if($result_log_login -> num_rows>0)
       while($row2 = $result_log_login -> fetch_assoc())
       {
         $datetime1 = new DateTime($row2["time"]);//start time
         $datetime2 = new DateTime($logoutdate);//end time
         $interval = $datetime1->diff($datetime2);
         $timediff = 0;
         $timediff += intval($interval->format('%i'));
         $timediff += intval($interval->format('%H'))*60;
         $timediff += intval($interval->format('%d'))*24*60;
         $timediff += intval($interval->format('%m'))*30*24*60;
         $timediff += intval($interval->format('%Y'))*365*24*60;
         if($timediff<$timeout)
         {
           $sql_log_logout = "UPDATE `log` SET `time` = ? WHERE `id` = ?";
           $stmt_log_logout = $conn -> prepare($sql_log_logout);
           $stmt_log_logout -> bind_param("si", $logoutdate, $row2['id']);
           $stmt_log_logout -> execute();
         }
       }
   else 
   {
      $sql_add_log_logout = "INSERT INTO `log` (`operation`,`time`,`prev_id`,`user`)VALUES('Logout',?,?,?)";
      $stmt_add_log_logout = $conn -> prepare($sql_add_log_logout);
      $stmt_add_log_logout -> bind_param("sis", $logoutdate, $_SESSION['log_id'], $_SESSION['username']);
      $stmt_add_log_logout -> execute();
   }
   
   $status = "Offline";
   $sql_update_offline = "UPDATE Account SET user_status = ? WHERE unique_id = ?";
   $stmt_update_offline = $conn -> prepare($sql_update_offline);
   $stmt_update_offline -> bind_param("si", $status, $_SESSION['unique_id']);
   $stmt_update_offline -> execute();
   $conn->close();
    $conn_fleet->close();
    $conn_ws->close();
   session_destroy();
   header("Location:index.php");
?>