<?php
    session_start();
    include '../connection/connect.php';
    include '../common/functions.php';
    if(isset($_GET['set_price'])){
    $stmt_request -> bind_param("i", $_GET['set_price']);
    $stmt_request -> execute();
    $result_request = $stmt_request -> get_result();
    $row = $result_request->fetch_assoc();

    $sql_vendor_popi = 'SELECT prefered_vendors.vendor as vendor from  purchase_order INNER JOIN price_information on price_information.purchase_order_id=purchase_order.purchase_order_id INNER JOIN prefered_vendors on prefered_vendors.id=price_information.providing_company WHERE purchase_order.request_id = ?';
    $stmt_vendor_popi = $conn -> prepare($sql_vendor_popi);
    $stmt_vendor_popi -> bind_param("i", $_GET['set_price']);
    $stmt_vendor_popi -> execute();
    $result_vendor_popi = $stmt_vendor_popi -> get_result();
    $row2=$result_vendor_popi->fetch_assoc();

      echo "<div class='form-group'>
      <label for='recipient-name' class='col-form-label'>Item:</label>
      <input type='text' class='form-control' id='recipient-name' name='item' value='$row[item]' readonly>
      <input type='hidden' value='$row[company]' name='company'/>
      <input type='hidden' value='$row[request_id]' name='id'/>
    </div>
    <div class='row'>
    <div class='form-group col-md-6'>
      <label for='recipient-name' class='col-form-label'>Requested Quantity:</label>
      <input type='text' class='form-control' id='recipient-name' value='$row[requested_quantity]' readonly>
    </div>
    <div class='form-group col-md-6'>
      <label for='recipient-name' class='col-form-label'>Provided Quantity <span class='text-danger'>*</span>:</label>
      <input type='number' step='any' class='form-control' id='recipient-name' name='quantity' max='$row[requested_quantity]' required>
    </div>
    <div>
    <label for='recipient-name' class='col-form-label'>Set Unit Price(<span class='text-primary'><i>after vat</i></span>) <span class='text-danger'>*</span>:</label>
<div class='input-group mb-3'>
<input type='hidden' name='providing_company' value='$row2[vendor]'/>
  <span class='input-group-text'>$row2[vendor]</span>
  <input type='number' class='form-control'  aria-describedby='basic-addon1' step='any' min=0.01 name='price' required>
  <span class='input-group-text'>Birr</span>
</div>
<div class='form-group'>
<label for='message-text' class='col-form-label'>Remark <span class='text-danger'>*</span>:</label>
<textarea class='form-control' id='message-text' name='remark' required></textarea>
</div>
<div class='mb-3'>
<label for='p_files' class='form-label'>Attach Receipt (Proforma) <span class='text-danger'>*</span></label>
<input id='p_files'type='file' id='performa_data' class='form-control multiple-files-filepond ms-0' name='performa[]' multiple required>
</div>
";
        }
        else{
    $serial =$_GET['serial'];
    $item =$_GET['item'];
    $found = false;
    ////////////////////////////check if an item with this serial number exists////////////////////////////////
    if($_GET['type'] == "Tyre and Battery")
    {
        $sql_history_jacket = "SELECT * from `history_jacket` where `serial` = ? and item = ? AND vehicle = ?";
        $stmt_history_jacket = $conn -> prepare($sql_history_jacket);
        $stmt_history_jacket -> bind_param("sss", $serial, $item, $_GET['plate_no']);
        $stmt_history_jacket -> execute();
        $result_history_jacket = $stmt_history_jacket -> get_result();
        if($result_history_jacket -> num_rows > 0)
            echo "<i class='fas fa-check-circle text-success'></i>";
        else
            echo "<i class='fas fa-exclamation-circle text-danger'></i>";
    }
    else
    {
        $sql_purchase_history = "SELECT * from `purchase history` where `Serial` = ? and item = ? AND `type` = ?";
        $stmt_purchase_history = $conn -> prepare($sql_purchase_history);
        $stmt_purchase_history -> bind_param("sss", $serial, $item, $_GET['type']);
        $stmt_purchase_history -> execute();
        $result_purchase_history = $stmt_purchase_history -> get_result();
        if($result_purchase_history -> num_rows>0)
            echo "<i class='fas fa-check-circle text-success'></i>";
        else
            echo "<i class='fas fa-exclamation-circle text-danger'></i>";
    }
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