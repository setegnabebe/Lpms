<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    $na_t = $_GET['type'];
    $type = na_t_to_type($conn,$na_t);

    $sql = "SELECT `item`, `date_needed_by`, `requested_quantity`,`Remark`,`description` FROM requests WHERE `request_id` =?";
    if($_GET['type'] == 'fixed' || $_GET['type'] == 'tb')
        $sql = "SELECT `item`, `date_needed_by`, `requested_quantity`,`Remark`,`description`,`to_replace` FROM requests WHERE `request_id` =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($_GET['type'] == 'fixed' || $_GET['type'] == 'tb')
        $stmt->bind_result($item, $date_n_b, $req_quan, $remark, $description, $replacement);
    else
        $stmt->bind_result($item, $date_n_b, $req_quan, $remark, $description); 
    $stmt->fetch();
    $stmt->close();
    echo "
    <input name='db' value='requests' class='d-none'>
    <div class='form-floating mb-3'>
        <input type='text' class='form-control rounded-4' id='floatingitem' name='item' value='$item'>
        <label for='floatingitem'>Item Name</label>
    </div>
    <div class='form-floating mb-3'>
        <input type='text' class='form-control rounded-4' id='floatingreq' name='req_quan' value='$req_quan'>
        <label for='floatingreq'>Required Quantity</label>
    </div>
    <div class='form-floating mb-3'>
        <input type='date' class='form-control rounded-4' id='floatingdate' name='date_n_b' value='$date_n_b'>
        <label for='floatingdate'>Date Needed By</label>
    </div>";

    if($_GET['type'] == 'fixed' || $_GET['type'] == 'tb')
    {
        $rep = explode(",",$replacement);
        $i = 0;
        echo "<div class='row'>";
        foreach($rep as $rep_val)
        {
            $i++;
            echo "
            <div class='form-floating mb-3 col-sm-12 col-md-6'>
                <input type='text' class='form-control rounded-4' id='floatingrep_$i' name='rep[]' value='$rep_val'>
                <label for='floatingrep_$i'>To be Replaced number $i</label>
            </div>
            ";
        }
        echo "</div>";
    }
    echo "
    <div class='row'>
        <div class=' col-6'>
            <button class='btn btn-sm alert-primary mb-3' type='button' data-bs-toggle='collapse' data-bs-target='#remark' aria-expanded='false' aria-controls='remark'>Edit / Add Remark</button>
        </div>
        <div class=' col-6'>
            <button class='btn btn-sm alert-primary mb-3' type='button' data-bs-toggle='collapse' data-bs-target='#description' aria-expanded='false' aria-controls='description'>Edit / Add Description</button>
        </div>
    </div>
    <div class='mb-3 collapse' id='remark'>
        <textarea class='form-control rounded-4' rows='3' id='floatingremark' name='remark' placeholder='Reason For Purchase'>$remark</textarea>
    </div>
    <div class='mb-3 collapse' id='description'>
        <textarea class='form-control rounded-4' rows='3' id='floatingremark' name='description' placeholder='Description of Item'>$description</textarea>
    </div>
    ";
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