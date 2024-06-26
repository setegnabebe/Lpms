<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include "../common/functions.php";
    ?>
    <select class="form-select" onchange="loader()" name="plate"  id="floatingplate">
    <option value="none">-- Select Vehicle --</option>
    <?php
    if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner' || $_SESSION["role"]=="Admin" || ((($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement') ||$_SESSION['additional_role']==1)) 
        $temp_requests ="";
    else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') 
        $temp_requests ="company = '".$_SESSION['company']."'";
    else if($_SESSION["role"] == 'Director')
    {
        foreach($_SESSION["managing_department"] as $depp)
            $temp_requests .=($temp_requests == "")?"department ='$depp'":", OR department = '$depp'";
        $temp_requests .=($temp_requests == "")?"customer ='$_SESSION[username]'":", OR customer = '$_SESSION[username]'";
    }
    else if(strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') 
        $temp_requests .="department = '".$_SESSION['department']."' AND company = '".$_SESSION['company']."'";
    else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') 
        $temp_requests .="customer ='$_SESSION[username]'";

        $sql_vehicle = "SELECT * FROM vehicle Order by plateno ASC";
        $stmt_vehicle = $conn_fleet->prepare($sql_vehicle);  
        $stmt_vehicle -> execute();
        $result_vehicle = $stmt_vehicle -> get_result();
        if($result_vehicle -> num_rows > 0)
        {
            while($row = $result_vehicle -> fetch_assoc())
            {
                $p_num=$row['plateno'];
                $sql_counted_plate_no = "SELECT count(*) As counted FROM requests where `request_type` = 'Tyre and Battery' AND (`item` = 'tyre' or `item` = 'inner tube') and `request_for` = ?";
                $stmt_counted_plate_no = $conn -> prepare($sql_counted_plate_no);  
                $stmt_counted_plate_no -> bind_param("s", $p_num);
                $stmt_counted_plate_no -> execute();
                $result_counted_plate_no = $stmt_counted_plate_no -> get_result();
                $row_has = $result_counted_plate_no -> fetch_assoc();
                echo "<option value='".$p_num."'>$p_num &nbsp &nbsp | &nbsp Requests - $row_has[counted]</option>";
            }
        }
    ?>
    </select>
    <label for="floatingplate"><span class='text-danger'>*</span>Plate Numer</label>
    <?php
}
else
{
  echo $go_home;
}?>
<?php
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>