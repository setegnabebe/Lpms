<?php
session_start();
include "../connection/connect.php";
include "../common/functions.php";
//\\\\\\\\\\\\\\\\\Confirmation/////////////////////////\\
if(isset($_SESSION['username']))
{
    $sql = "SELECT * FROM requests WHERE `flag` = 0 AND `status`='Collected-not-comfirmed' AND `company` = ? AND `department` = ?";
    $stmt_department = $conn->prepare($sql_department);
    $stmt_department->bind_param("ss", $_SESSION['company'], $_SESSION['department']);
    $stmt_department->execute();
    $result_department = $stmt_department->get_result();
    if($result_department->num_rows>0)
    while($row = $result_department->fetch_assoc())
    {
        $type = $row['request_type'];
        $na_t=str_replace(" ","",$type);
        if(isset($_GET["approve_item_".$na_t.$row['request_id']]))
        {
            $sql_request = "UPDATE requests SET `flag`='1' WHERE `request_id` = ?";
            $stmt_request_flag = $conn->prepare($sql_request);
            $stmt_request_flag -> bind_param("i",$row['request_id']);
            $stmt_request_flag -> execute();
            $_SESSION["success"]="Item Approved";
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
        if(isset($_GET["reject_item_".$na_t.$row['request_id']]))
        {
            $status = "Recollect";
            $stmt = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `request_id`=? AND `request_type`=?");
            $stmt -> bind_param("sis", $status, $row['request_id'], $type);
            $stmt -> execute();
            $stmt = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
            $stmt -> bind_param("si", $status, $row['request_id']);
            $stmt -> execute();
            $_SESSION["success"]="Rejected Item";
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
    }
    header("location: ".$_SERVER['HTTP_REFERER']);
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