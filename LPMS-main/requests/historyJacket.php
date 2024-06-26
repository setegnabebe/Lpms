<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = "../".$_SESSION['loc'].'head.php';
    include $string_inc;
    $SPO=$_SESSION["role"]=='Senior Purchase officer';
}
else
    header("Location: ../");
?>
<script>
    set_title("LPMS | History Jacket");
    sideactive("hist_jacket");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>History Jackets</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">History Jackets</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<!-- <strong class="btn btn-warning" onClick="printExternal('collected.php')"><i class="fa fa-file-pdf"></i> pdf</strong> -->
<script>
    function load_driver(e)
    {
        if(e.value == "") 
            document.getElementById("Driver").value = "";
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            let x = this.responseText.split("_")
            document.getElementById("Driver").value = x[0];
            document.getElementById("Company").value = x[1];
        }
        req.open("GET", "../procurement/senior/allphp.php?plate="+e.value);
        req.send();
    }
</script>
<div class="my-3">
<h3 class="text-center my-3" data-aos="fade-right"><?php echo ($SPO?"Manage History Jacket":"View History Jacket")?></h3>
<?php 
    if($SPO){
    ?>
    <div data-aos="fade-right"><!-- -->

        <span class="float-end  mx-8 fs-5 "><a href='<?php echo '../assets/history jacket.csv'?>' download ><i class="bi bi-download"></i> Excel Sample file</a></span></p>
        <form  class="float-end" action="../procurement/senior/allphp.php" method="post" enctype="multipart/form-data">
                                      <label for="fileupload" class="text-primary">Import vendor Excel</label>
                                    <input type="file" id="fileupload" class="float-left" name="history_csv" accept=".csv" required/>
                                    <button  type="submit" name="fileuploader_btn" class="float-left btn btn-primary">Import from CSV/Excel</button>
            </form>
            <form method="GET" action="../procurement/senior/allphp.php">
            <h4 class="modal-title text-light">Add History</h4>
            <div class="form-floating">
                <select class="form-select mb-3" onchange="load_driver(this)" name="plate"  id="floatingplate">
                <option value="none">-- Select one Vehicle --</option>
                <?php
                    $sql = "SELECT * FROM vehicle order by plateno ASC";
                    $stmt_vehicles = $conn_fleet -> prepare($sql);
                    $stmt_vehicles -> execute();
                    $result_vehicles = $stmt_vehicles -> get_result();
                    if($result_vehicles -> num_rows > 0)
                    {
                        while($row = $result_vehicles -> fetch_assoc())
                        {
                            $p_num=$row['plateno'];
                            echo "<option value='$p_num'>$p_num</option>";
                        }
                    }
                ?>
                </select>
                <label for="floatingplate">Plate Number</label>
            </div>
            <div class="row mb-3">
                <div class="form-floating col-5 mx-auto">
                    <input type="text" class="form-control rounded-4" id="Driver" placeholder="Driver name" readonly>
                    <label for="Driver">Driver</label>
                </div>
                <div class="form-floating col-5 mx-auto">
                    <input type="text" class="form-control rounded-4" id="Company" placeholder="Company" readonly>
                    <label for="Company">Company</label>
                </div>
            </div>
            <div class="row mb-3">
                <div class="form-floating col-6">
                    <select name='item' class="form-select mb-3" id="floatingitem" required>
                        <option value="">-- Select item --</option>
                        <option value="battery">Battery</option>
                        <option value="tyre">Tyres</option>
                        <option value="inner tube">Inner Tube</option>
                    </select>
                    <label for="floatingitem">Item</label>
                </div>
                <div class="form-floating col-6">
                    <input type="number" class="form-control rounded-4" id="quantity" placeholder="quantity" name='quantity' required>
                    <label for="quantity">Quantity</label>
                </div>
            </div>
            <div class="form-floating mb-3">
                <input type="text" class="form-control rounded-4" id="Serial" placeholder="Serial" name='serial' required>
                <label for="Serial">Serial Number</label>
            </div>
            <div class="form-floating mb-3">
                <input type="date" class="form-control rounded-4" id="Date" max="<?php echo $dateee?>" placeholder="Date Purchased" name='date' required>
                <label for="Date">Date Purchased</label>
            </div>
            <div class="form-floating mb-3">
                <input type="number" min='0' class="form-control rounded-4" id="Kilometer" placeholder="Kilometer" name='kilometer' required>
                <label for="Kilometer">Kilometer</label>
            </div>
            <div class='form-group'>
                <label for='Details'>Item Description</label>
                <textarea class='form-control' id='Details' rows='3' name='desc_item' required></textarea>
            </div>
            <div class="row mb-3">
                <div class="form-floating col-6">
                    <input type="number" min='0' class="form-control rounded-4" id="km_diff" placeholder="Kilometer Difference" name='km_diff' required>
                    <label for="km_diff">Kilometer Difference</label>
                </div>
                <div class="form-floating col-6">
                    <input type="text" class="form-control rounded-4" id="time_diff" placeholder="Time Difference" name='time_diff' required>
                    <label for="time_diff">Time Difference</label>
                </div>
            </div>
            <button class="btn btn-primary" type="submit" name="create_History">Add History<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <?php
}
?>
    <div class='mx-auto mt-2' data-aos='fade-left'><!--  -->
        <!-- <h3 class="text-center my-2">Manage Projects</h3> -->
        <form method="POST" action="../procurement/senior/allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table1">
                <thead class="table-primary">
                    <tr>
                        <th>Plate Number</th>
                        <th>Current Driver</th>
                        <th>Item Description</th>
                        <th>Qunatity</th>
                        <th>Serial Number</th>
                        <th>Description</th>
                        <th>Date Purchased</th>
                        <th>Kilometer</th>
                        <th>Kilometer Difference</th>
                        <th>Time Difference</th>
                       <?php echo ($SPO?'<th>Operations</th>':"")?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM history_jacket";
                    $stmt_history_jackets = $conn -> prepare($sql);
                    $stmt_history_jackets -> execute();
                    $result_history_jackets = $stmt_history_jackets -> get_result();
                    if($result_history_jackets -> num_rows>0)
                        while($row = $result_history_jackets -> fetch_assoc())
                        {
                            $sql_driver = "SELECT driver FROM `vehicle` WHERE `plateno` = ?";
                            $stmt_driver = $conn_fleet -> prepare($sql_driver);
                            $stmt_driver -> bind_param("s", $row['vehicle']);
                            $stmt_driver -> execute();
                            $result_driver = $stmt_driver -> get_result();
                            if($result_driver->num_rows>0)
                                while($row_driver = $result_driver->fetch_assoc())
                                {
                                    $driver = $row_driver['driver'];
                                }
                            echo "
                            <tr id='row_".$row['id']."'>
                                <td class='text-capitalize' id='Plate_".$row['id']."'>".$row['vehicle']."</td>
                                <td class='text-capitalize' id='Driver_".$row['id']."'>$driver</td>
                                <td class='text-capitalize' id='Item_".$row['id']."'>".$row['item']."</td>
                                <td class='text-capitalize' id='Quantity_".$row['id']."'>".$row['quantity']."</td>
                                <td class='text-capitalize' id='Serial_".$row['id']."'>".$row['serial']."</td>
                                <td class='text-capitalize' id='description_".$row['id']."'>".$row['description']."</td>
                                <td class='text-capitalize' id='DatePurchased_".$row['id']."'>".$row['date_purchased']."</td>
                                <td class='text-capitalize' id='Kilometer_".$row['id']."'>".$row['kilometer']."</td>
                                <td class='text-capitalize' id='Kmdif_".$row['id']."'>".$row['km_diff']."</td>
                                <td class='text-capitalize' id='timediff_".$row['id']."'>".$row['time_diff']."</td>";
                            echo ($SPO?"<td><a type='button' class='btn' data-bs-toggle='modal' onclick='open_edit(this)' data-bs-target='#history_jacket' title='1' id='".$row['id']."'><i class='fas fa-edit me-2'></a></i><i class='fas fa-trash'></i>":"")."
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>  
</div>
<div class="modal fade" id="history_jacket" tabindex="-1" aria-labelledby="history_jacket" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="history_jacket">Edit History Jacket</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../procurement/senior/allphp.php" method="post">
      <div class="modal-body" id='history_bdy'> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="save_history">Save</button>
      </div>
      <form>
    </div>
  </div>
</div>
<script>
function open_edit(e){
let xhr=new XMLHttpRequest();
// alert("ajax_performa.php?history_id="+e.id) 
xhr.onload=function(){
document.getElementById('history_bdy').innerHTML=this.responseText;
}
xhr.open("GET","../procurement/senior/ajax_performa.php?history_id="+e.id)
xhr.send();
}
</script>
<?php include "../footer.php"; ?>
