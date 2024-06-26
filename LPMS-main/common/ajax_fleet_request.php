<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    ?>
    <div class="container">
        <div class='divider'>
            <div class='divider-text fw-bold'>Trip Start</div>
        </div>
        <div class="row">
            <div class="form-group mb-3 col-6">
                <label for="date_for"><span class='text-danger'>*</span>Date Request For</label>
                <input type="date" name="date_for" id="date_for" class="form-control rounded-4" min="<?php echo date("Y-m-d");?>" value="<?php echo date("Y-m-d");?>" required>
            </div>
            <div class="form-group mb-3 col-6">
                <label for="time_for"><span class='text-danger'>*</span>Time Request From</label>
                <input type="time" name="time_for" id="time_for" class="form-control rounded-4" min="08:00" max="17:00" value="<?php echo date("H:i");?>" required>
            </div>
        </div>
        <div class='divider'>
            <div class='divider-text fw-bold'>Trip End</div>
        </div>
        <div class="row">
            <div class="form-group mb-3 col-6">
                <label for="date_for"><span class='text-danger'>*</span>Estimate Return Date</label>
                <input type="date" name="date_to" id="date_for" class="form-control rounded-4" min="<?php echo date("Y-m-d");?>" value="<?php echo date("Y-m-d");?>" required>
            </div>
            <div class="form-group mb-3 col-6">
                <label for="time_for"><span class='text-danger'>*</span>Estimate Return Time</label>
                <input type="time" name="time_to" id="time_for" class="form-control rounded-4" min="08:00" max="17:00" value="" required>
            </div>
        </div>
        <div class='divider'>
            <div class='divider-text fw-bold'>Details</div>
        </div>
        <div class='form-group mb-3'>
            <label for='purpose'><span class='text-danger'>*</span>Purpose of Trip</label>
            <textarea class='form-control' id='purpose' rows='2' name='purpose' required></textarea>
        </div>
        <div class='form-group mb-3' id='requests_accepted'>
            <div class="row mx-auto" id='items'>
                <label for='items' class="my-2"><span class='text-danger'>*</span>Vehicle Request for</label>
            <!-- <div class='form-floating form-group mb-3 input-group'>
                <select class='form-select' name='requests' id='' onchange='t_p(this)' onkeyup='t_p(this)' required> -->
                <?php 
                    if($_GET['reason'] == "performa")
                        $sql = "SELECT * FROM purchase_order WHERE `purchase_officer`= ? AND status = 'Accepted'";
                    else
                        $sql = "SELECT * FROM purchase_order WHERE `collector`= ?"; //  AND status = ''
                    $stmt_po_status = $conn->prepare($sql);
                    $stmt_po_status->bind_param("s", $_GET['po']);
                    $stmt_po_status->execute();
                    $result_po = $stmt_po_status->get_result();
                    if($result_po->num_rows>0)
                        while($row = $result_po->fetch_assoc())
                        {
                            $stmt_request->bind_param("i", $row['request_id']);
                            $stmt_request->execute();
                            $result_request = $stmt_request->get_result();
                            $row2 = $result_request->fetch_assoc();
                            $uname =str_replace("."," ",$row2['customer']);
                            echo "
                            <div class='form-check mb-3 col ms-2'>
                                <input id='request_$row[purchase_order_id]' name='requested_items[]' value='$row[purchase_order_id]' class='form-check-input' type='checkbox'>
                                <label for='request_$row[purchase_order_id]' class='form-label'>
                                Item - $row2[item]<br>
                                Quantity - $row2[requested_quantity] $row2[unit]<br>
                                Requested By - $uname
                                </label>
                            </div>";
                        }
                ?>
                <!-- </select> -->
            <!-- </div> -->
            </div>
        </div>
        <div class='form-group mb-3 mx-auto'>
            <label for='destination'><span class='text-danger'>*</span>Destination</label>
            <input type='text' class='form-control' id='destination' name='destination' required>
        </div>
        <div class="row">
            <div class="form-group mb-3 col-3 mx-auto">
                <label for="travelers_no"><span class='text-danger'>*</span>No of Travelers</label>
                <input type="number" class="form-control rounded-4" min='1' max='10' name='travelers_no' value='1' id='travelers_no' required>
            </div>
            <div class='form-group mb-3 col-8 mx-auto'>
                <label for='travelers_name'><span class='text-danger'>*</span>Name Of Travelers</label>
                <input type='text' class='form-control' id='travelers_name'  name='travelers_name' value='<?php echo $_SESSION['username']?>' required>
            </div>
        </div>
    </div>  
<?php 
}
else
{
    echo $go_home;
}
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>