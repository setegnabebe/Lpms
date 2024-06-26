<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($conn))
{
    include "connection/connect.php";
}
if(isset($conn))
{
    $sql_latest_purchase_requisition = "SELECT * FROM `requests` Where purchase_requisition IS NOT NULL order by purchase_requisition DESC LIMIT 1";
    $stmt_latest_purchase_requisition = $conn -> prepare($sql_latest_purchase_requisition);
    $stmt_latest_purchase_requisition -> execute();
    $result_latest_purchase_requisition = $stmt_latest_purchase_requisition -> get_result();
    if($result_latest_purchase_requisition -> num_rows>0)
    {
        $row = $result_latest_purchase_requisition -> fetch_assoc();
        $purchase_requisition = $row['purchase_requisition'];
    }
    else
        $purchase_requisition = 0;
    $sql_null_purchase_requisition = "SELECT * FROM `requests` as R INNER JOIN report as Rep on R.request_id = Rep.request_id Where purchase_requisition IS NULL order by R.request_id ASC";
    $stmt_null_purchase_requisition = $conn -> prepare($sql_null_purchase_requisition);
    $stmt_null_purchase_requisition -> execute();
    $result_null_purchase_requisition = $stmt_null_purchase_requisition -> get_result();
    if($result_null_purchase_requisition -> num_rows > 0)
        while($row = $result_null_purchase_requisition -> fetch_assoc())
        {
            $purchase_requisition++;
            $sql_on_req_date = "SELECT *,R.request_id as request_id FROM `requests` as R INNER JOIN report as Rep on R.request_id = Rep.request_id Where request_date = ? and customer = ?";
            $stmt_on_req_date = $conn -> prepare($sql_on_req_date);
            $stmt_on_req_date -> bind_param("ss", $row['request_date'], $row['customer']);
            $stmt_on_req_date -> execute();
            $result_on_req_date = $stmt_on_req_date -> get_result();
            if($result_on_req_date -> num_rows > 0)
                while($row2 = $result_on_req_date -> fetch_assoc())
                {
                    $sql_update_purchase_requisition = "UPDATE requests SET `purchase_requisition` = ? WHERE `request_id` = ?";
                    $stmt_update_purchase_requisition = $conn -> prepare($sql_update_purchase_requisition);
                    $stmt_update_purchase_requisition -> bind_param("ii", $purchase_requisition, $row2['request_id']);
                    $stmt_update_purchase_requisition -> execute();
                    // echo "Requestition number - ".$purchase_requisition."<br>";
                }
            $stmt_null_purchase_requisition -> execute();
            $result_null_purchase_requisition = $stmt_null_purchase_requisition -> get_result();
        }
}
?>