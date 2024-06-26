<?php

session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    ?>
    <!-- <h4 class="text-center my-2">Purchase Requisition Form</h4> -->
    <?php
    $purchase_requisition = $_GET['purchase_requisition'];
    $sql = "SELECT * FROM requests WHERE purchase_requisition = ?";
    $stmt_request_pr = $conn->prepare($sql);
    $stmt_request_pr -> bind_param("i", $purchase_requisition);
    $stmt_request_pr -> execute();
    $result_request_pr = $stmt_request_pr -> get_result();
    if($result_request_pr->num_rows > 0) {
        $row = $result_request_pr->fetch_assoc();
        $stmt_request_pr -> execute();
        $result_request_pr = $stmt_request_pr -> get_result();?>
        <div class="float-end px-5 py-3">
            <p class="me-3 text-dark mb-1"><span class = "fw-bold">Catagory - </span><?php echo $row['request_type']?></p>
            <p class="me-3 text-dark mb-1"><span class = "fw-bold">Request Date - </span><?php echo date("d-M-Y H:i",strtotime($row['date_requested']))?></p>
            <p class="me-3 text-dark mb-1"><span class = "fw-bold">Requested by - </span><?php echo str_replace("."," ",$row['customer'])?></p>
        </div>
        <table class="table border ">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Item</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Date needed by</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $i=0;
    $btn_close = "";
        while($row = $result_request_pr -> fetch_assoc()) {
            $avail = true;
            $forbiden_stats = ['canceled','Rejected','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            foreach($forbiden_stats as $s)
                if(strpos($row['status'],$s)!==false || $row['status'] == $s) $avail = false;
                if((((($_SESSION['company'] == $row['procurement_company'] || $_SESSION['company'] == 'Hagbes HQ.') && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail) || $btn_close != "")
                {
                    $btn_close = "
                    <form method='GET' action='allphp.php' class='float-end'>
                        <button class='btn btn-outline-danger btn-sm' name='close_pur_req' value='$row[purchase_requisition]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\",\"Red\")'>Close Request</button>
                    </form>";
                }
            $i++;
            $status = $row['status'];
            $type=$row['request_type'];
            $na_t=str_replace(" ","",$type);
            $stmt_po_by_request -> bind_param("i", $row['request_id']);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if($result_po_by_request -> num_rows > 0) {
                $row2 = $result_po_by_request -> fetch_assoc();
                $status = ($row['status'] == "Generating Quote")?$row2['status']:$status;
            }
            ?>
            <tr>
                <td class="text-capitalize text-center"><?=$i?></td>
                <td class="text-capitalize text-center"><?="<button type='button'  value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']."</button>"?></td>
                <td class="text-capitalize text-center"><?=$row['requested_quantity']." ".$row['unit']?></td>
                <td class="text-capitalize text-center"><?=date("d-M-Y",strtotime($row['date_needed_by']))?></td>
                <td class="text-capitalize text-center"><?=getNamedStatus($status,$row)?></td>
            </tr>
    <?php
        }
        if($btn_close != "")
        { ?>
            <tr>
                <td class="text-capitalize text-center" colspan="5"><?=$btn_close?></td>
            </tr>
        <?php }
        ?>
            </tbody>
        </table>
        <?php
    }
}
else
{
    echo $go_home;
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