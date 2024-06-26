<?php
session_start();
include '../connection/connect.php';
include "../common/functions.php";
include "../common/head_css.php";
$name = $_GET['uname'];
$sql_acc = "SELECT * FROM account where `Username` = ?";
$stmt_acc = $conn->prepare($sql_acc); 
$stmt_acc->bind_param("s", $name);
$stmt_acc->execute();
$result_acc = $stmt_acc->get_result();
$row_acc = $result_acc->fetch_assoc();
$phone = explode("+251",$row_acc['phone'])[1];
?>
<!--  value='<?php echo $row_acc['']?>' -->
    <div class="form-floating mb-3">
        <input type="text" class="form-control rounded-4" value='<?php echo $row_acc['Username']?>' id="edit_Username" name='uname' readonly>
        <label for="edit_Username">Username</label>
    </div>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1">+251</span>
        </div>
    <input type="text" value='<?php echo $phone?>' name="tel" id="edit_tel" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9"required/>
    </div>
    <small class="text-secondary">Format : +251993819775</small>    
    <div class="form-floating mb-3">
        <input type="email" value='<?php echo $row_acc['email']?>' class="form-control rounded-4" id="edit_email" name='email' required>
        <label for="edit_email">Email</label>
    </div>
    <div class='row '>
        <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
            <select class="form-select" name="department"  id="edit_department" required>
                    <option value="">-- Select one --</option>
                <?php
                    $sql = "SELECT * FROM department";
                    $stmt_dep = $conn->prepare($sql);
                    $stmt_dep->execute();
                    $result = $stmt_dep->get_result();
                    if($result->num_rows>0)
                    {
                        while($row = $result->fetch_assoc())
                        {
                            $selected = ($row_acc["department"] == $row['Name'])?" selected":"";
                            echo "<option value='".$row['Name']."'$selected>".$row['Name']."</option>";
                        }
                    }
                ?>
                </select>
            <label for="edit_department">Department</label>
        </div>
        <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
            <select class="form-select" name="company"  id="edit_company" required>
                    <option value="">-- Select one --</option>
                <?php
                    $sql = "SELECT * FROM `comp`";
                    $stmt_comp = $conn_fleet->prepare($sql);
                    $stmt_comp->execute();
                    $result = $stmt_comp->get_result();
                    if($result->num_rows>0)
                    {
                        while($row = $result->fetch_assoc())
                        {
                            $selected = ($row_acc["company"] == $row['Name'])?" selected":"";
                            echo "<option value='".$row['Name']."'$selected>".$row['Name']."</option>";
                        }
                    }
                ?>
                </select>
            <label for="edit_company">Company</label>
        </div>
    </div>
    <div class='row'>
        <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
            <select class="form-select" name="role"  id="edit_role" required>
                <option value="">-- Select one --</option>
                <?php
                    $sql = "SELECT DISTINCT `role` FROM account";
                    $stmt_role = $conn->prepare($sql);
                    $stmt_role->execute();
                    $result = $stmt_role->get_result();
                    if($result->num_rows>0)
                    {
                        while($row = $result->fetch_assoc())
                        {
                            $selected = ($row_acc["role"] == $row['role'])?" selected":"";
                            echo "<option value='".$row['role']."'$selected>".$row['role']."</option>";
                        }
                    }
                ?>
            </select>
            <label for="edit_role">Role</label>
        </div>
        <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
            <small>Account Type</small>
            <select class="choices form-select multiple-remove" name="type[]" id="edit_type" multiple="multiple" required>
                <optgroup label="Account Types">
                    <?php
                        $sql = "SELECT * FROM `account_types`";
                        $stmt_types = $conn->prepare($sql);
                        $stmt_types->execute();
                        $result = $stmt_types->get_result();
                        if($result->num_rows>0)
                        {
                            while($row = $result->fetch_assoc())
                            {
                                // $selected = (strpos($row_acc['type'],$row['name']) !== False)?" selected":"";
                                if(strpos($row_acc['type'],$row['name']) === False)
                                    echo "<option value='".$row['name']."'>".$row['name']."</option>";
                            }
                        }
                    ?>
                </optgroup>
            </select>
            <!-- <select class="form-select" name="type"  id="type" required>
                    <option value="none">-- Select one --</option>
                </select>-->
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <input type="text" value='<?php echo $row_acc['cheque_percent']?>' class="form-control rounded-4" id="percent" name='percent'>
                <label for="percent">Cheque percent</label>
            </div>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <small>Managing Departments</small>
                <select class="choices form-select multiple-remove" name="man_deps[]"  id="man_deps"  multiple="multiple">
                    <optgroup label="Departments">
                        <option value='All Departments'>All Departments</option>
                        <?php
                            $result = $stmt_dep->get_result();
                            if($result->num_rows>0)
                            {
                                while($row = $result->fetch_assoc())
                                {
                                    $selected = (strpos($row_acc['managing'],$row['Name']) !== False)?" selected":"";
                                    echo "<option value='".$row['Name']."'$selected>".$row['Name']."</option>";
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control rounded-4" id="edit_password" name='password'>
            <label for="edit_password">Password</label>
        </div>
    </div>
    <?php 
include "../common/footer_script.php";
?>