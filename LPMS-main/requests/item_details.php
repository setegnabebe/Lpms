<?php
    session_start();
    include '../connection/connect.php';
    $item_id = $_GET['item'];
    $item_num = $_GET['item_num'];
    $sql_item = "SELECT * FROM item where `id` = ?";
    $stmt_item = $conn_pms -> prepare($sql_item);
    $stmt_item -> bind_param("i", $item_id);
    $stmt_item -> execute();
    $result_item = $stmt_item -> get_result();
    $row_item = $result_item->fetch_assoc();
    
    $count_prev = 0;
    $project_combo = $_SESSION['project_id']."|".$row_item['id_t']."|".$row_item['id'];
    $sql_in_project = "SELECT * FROM `requests` WHERE `request_for` = ? AND `status` NOT LIKE 'Reject%' AND `status` != 'canceled'";
    $stmt_in_project = $conn -> prepare($sql_in_project);
    $stmt_in_project -> bind_param("s", $project_combo);
    $stmt_in_project -> execute();
    $result_in_project = $stmt_in_project -> get_result();
    if($result_in_project -> num_rows > 0)
        while($row = $result_in_project -> fetch_assoc())
        {
                $count_prev += ($row['recieved'] == 'not')?$row['requested_quantity']:((!is_null($row['purchased_amount']))?$row['purchased_amount']:$row['requested_quantity']);
        }
    $remaining_boq = $row_item['total_quantity'] - $count_prev;
    $date_cg_project = (date("Y-m-d",strtotime($row_item['date_needed_for']))<$dateee)?$dateee:date("Y-m-d",strtotime($row_item['date_needed_for']));
?>

<div class="form-floating input-group mb-3">
    <input title='Maximum BOQ is <?php echo $row_item['total_quantity']?>' type="number" step="any" class="form-control rounded-4" max = '<?php echo $remaining_boq?>' min='0' id="floatingreq_<?php echo $item_num?>" placeholder="Required Quantity" name='req_quan[]' required>
    <label for="floatingreq_<?php echo $item_num?>">Required Quantity <span class="text-secondary text-sm">( Remaining BOQ is <?php echo $remaining_boq?>)</span></label>
    <span class="input-group-text fw-bold">Unit</span>
    <input type="text" class="form-control" id="unit_<?php echo $item_num?>" placeholder="Eg. Pcs" name ='unit[]' style="max-width: 20%;" value = '<?php echo $row_item['unit']?>' readonly>
</div>
<div class="form-floating mb-3">
    <input type="date" class="form-control rounded-4" id="floatingdate_<?php echo $item_num?>" value = '<?php echo $date_cg_project?>' placeholder="Date Needed By" max="<?php echo $date_last?>" name='date_n_b[]' required>
    <label for="floatingdate_<?php echo $item_num?>">Date Needed By</label>
</div>
<div class="mb-3" id='remark_<?php echo $item_num?>'>
    <textarea class="form-control rounded-4" rows="3" name='remark[]' placeholder="Reason For Purchase" readonly><?php echo $row_item['remark']?></textarea>
</div>
<div class="mb-3" id='desc_<?php echo $item_num?>'>
    <textarea class="form-control rounded-4" rows="5" name='description[]' placeholder="Details for Item" readonly><?php echo $row_item['description']?></textarea>
</div>