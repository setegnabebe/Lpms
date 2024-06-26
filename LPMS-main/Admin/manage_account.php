
<div class='' data-aos="fade-down-left">
    <div class="alert-outine-primary text-center my-2">
        <h3>Create Account</h3>
    </div>
    <form method="POST" action="allphp.php">
        <div class="form-floating mb-3">
            <input type="text" class="form-control rounded-4" id="uname" name='uname' required>
            <label for="uname">Username</label>
        </div>
        <div class="form-floating mb-3">
            <input type="email" class="form-control rounded-4" id="email" name='email' required>
            <label for="email">Email</label>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">+251</span>
            </div>
        <input type="text" name="tel" id="tel" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9"required/>
        </div>
        <small class="text-secondary">Format : +251993819775</small>    
        <!-- <div class="form-floating mb-3">
        <input type="text" name="tel" id="tel" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9"required/>
            <label for="tel">Phone No:</label>
            <small class="text-secondary">Format : +251993819775</small>
        </div> -->
        <div class='row '>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <select class="form-select" name="department"  id="department" required>
                        <option value="">-- Select one --</option>
                    <?php
                        $sql = "SELECT * FROM department";
                        $stmt_department = $conn->prepare($sql); 
                        $stmt_department->execute();
                        $result = $stmt_department->get_result();
                        if($result->num_rows>0)
                        {
                            while($row = $result->fetch_assoc())
                            {
                                echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                            }
                        }
                    ?>
                    </select>
                <label for="department">Department</label>
            </div>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <select class="form-select" name="company"  id="company" required>
                        <option value="">-- Select one --</option>
                    <?php
                        $stmt_all_company->execute();
                        $result = $stmt_all_company->get_result();
                        if($result->num_rows>0)
                        {
                            while($row = $result->fetch_assoc())
                            {
                                echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                            }
                        }
                    ?>
                    </select>
                <label for="company">Company</label>
            </div>
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <select class="form-select" name="role"  id="role" required>
                    <option value="">-- Select one --</option>
                    <?php
                        $sql = "SELECT * FROM `roles`";
                        $stmt_role = $conn->prepare($sql); 
                        $stmt_role->execute();
                        $result = $stmt_role->get_result();
                        if($result->num_rows>0)
                        {
                            while($row = $result->fetch_assoc())
                            {
                                echo "<option value='".$row['name']."'>".$row['name']."</option>";
                            }
                        }
                    ?>
                </select>
                <label for="role">Role</label>
            </div>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3" id='type_create'>
                <small>Account Type</small>
                <select class="choices form-select multiple-remove" name="type[]"  id="type"  multiple="multiple" onchange="signatory(this)" required>
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
                                    echo "<option value='".$row['name']."'>".$row['name']."</option>";
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                <select class="form-select" name="percent"  id="percent">
                    <option value="">Not A Cheque Signatory</option>
                    <option value="p_100">Has Full privilege To Sign</option>
                    <option value="p_50">Cosigner With privilege</option>
                    <option value="not_50">Cosigner Without privilege</option>
                </select>
                <!-- <input type="text" class="form-control rounded-4" id="percent" name='percent'> -->
                <label for="percent">Cheque percent</label>
            </div>
            <div class="col-sm-12 col-md-4 mx-auto form-floating mb-3">
                <small>Managing Departments</small>
                <select class="choices form-select multiple-remove" name="man_deps[]"  id="man_deps"  multiple="multiple">
                    <optgroup label="Departments">
                        <option value='All Departments'>All Departments</option>
                        <?php
                            $stmt_department->execute();
                            $result = $stmt_department->get_result();
                            if($result->num_rows>0)
                            {
                                while($row = $result->fetch_assoc())
                                {
                                    echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            
            </div>
            <div class="col-sm-12 col-md-2 float-end form-floating mb-3">
                <small>Additional Role</small>
                <div class='form-check form-switch'>
                <input name='additional_role' value='1' class='form-check-input'  type='checkbox' role='switch'>
                </div>
            </div>
        
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control rounded-4" id="create_newpass" name='password' required>
            <label for="create_newpass">Password</label>
        </div>
        <div class="form-floating mb-3">
            <input title='create' onkeyup="chanagepass(this)" onchange='chanagepass(this)' type="password" class="form-control rounded-4" id="create_confpass" name='con_password' required>
            <label for="create_confpass">Comfirm Password</label>
        </div>
        <p class="small text-danger text-center" id='create_warnpass'></p>
        <div class="text-center alert">
            <button type='button' title='create' onclick='chanagepass(this)' class='btn btn-outline-primary mx-auto' name="create_account">Create Account</button>
        </div>
    </form>
</div>

<!-- Edit accounts  -->
<div class='py-3' data-aos="fade-up-right">
<script>
    function view_type(e,t)
    {
        let o = (t=='div')?'tbl':'div';
        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
    }
</script>
<div class='text-center mx-auto mb-4' style="width: 200px;">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button type='button' class="btn nav-link active" id='div_toggle' onclick="view_type(this,'tbl')">
                <i class='fas fa-tablet-alt'></i>
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="btn nav-link" id='tbl_toggle' onclick="view_type(this,'div')">
                <i class='fas fa-table'></i>
            </button>
        </li>
    </ul>
</div>
<h3 class="text-center my-2">Existing Accounts</h3>
<div id='div_view'>
    <form method="POST" action="allphp.php" class="border shadow">
    <div class='pricing'>
        <div class='px-4'>
                <?php 
                $count = 1;
                $open = false;
                $tbl = "";
                $sql = "SELECT * FROM account order by Username ASC";
                $stmt_account = $conn->prepare($sql); 
                $stmt_account->execute();
                $result = $stmt_account->get_result();
                $amount = ceil($result->num_rows/10);
                if($result->num_rows>0)
                    while($row = $result->fetch_assoc())
                    {
                        $page_num = ceil($count/10);
                        if(($count-1) % 10 == 0)
                        {
                            $open = true;
                            $active = ($page_num == 1)?"":" d-none";
                            echo "<div id='page_$page_num' class='pages row $active'>";
                        }
                        $ch =($row['status'] == 'active' || $row['status'] == 'waiting')?" checked":"";
                        $role = ($row['role'] == 'Director' && $row['company'] != "Hagbes HQ.")?$row['role']." (Branch Manager)":$row['role'];
                        switch ($row['cheque_percent'])
                        {
                            case "p_100":
                                $cheque = "Has Full privilege To Sign";
                                break;
                            case "p_50":
                                $cheque = "Cosigner With privilege";
                                break;
                            case "not_50":
                                $cheque = "Cosigner Without privilege";
                                break;
                            default:
                                $cheque = "";
                                break;
                        }
                        $tbl .= "<tr id='row_".$row['Username']."'>
                                    <td id='Username:-:".$row['Username']."'>".$row['Username']."</td>
                                    <td id='phone:-:".$row['Username']."'>".$row['phone']."</td>
                                    <td id='email:-:".$row['Username']."'>".$row['email']."</td>
                                    <td id='department:-:".$row['Username']."'>".$row['department']."</td>
                                    <td id='managing:-:".$row['Username']."'>".$row['managing']."</td>
                                    <td id='cheque_percent:-:".$row['Username']."' title='$row[cheque_percent]'>".$cheque."</td>
                                    <td id='company:-:".$row['Username']."'>".$row['company']."</td>
                                    <td id='type:-:".$row['Username']."'>".$row['type']."</td>
                                    <td id='role:-:".$row['Username']."' title='$row[role]'>".$role."</td>
                                    <td id='additional:-:".$row['Username']."' title='$row[additional_role]'>".$row['additional_role']."</td>
                                    <td>
                                        <div class='form-check form-switch'>
                                            <span class='text-capitalize' id='tblstatus:-:".$row['Username']."'>".$row['status']." </span>
                                            <input name='for:-:".$row['Username']."' class='form-check-input' onchange='status_change(this)' type='checkbox' role='switch' id='statuss'$ch>
                                        </div>
                                        <!--<label class='form-check-label' for='statuss'>".$row['status']."</label>-->
                                    </td>
                                    <td class='text-center'>
                                        <button type='button' name='".$row['Username']."' class='btn btn-outline-primary' onclick='editaccount(this)' data-bs-toggle='modal' data-bs-target='#editaccountmodal'><i class='fa fa-edit'></i></button>
                                        <!--<button type='button' class='btn btn-outline-danger'><i class='fa fa-trash'></i></button>
                                        <button class='btn btn-outline-primary'><i class='fa fa-edit'></i></button>-->
                                    </td>
                                </tr>";
                        echo "<div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                                <div class='card'>
                                    <div class='section-title text-center rounded border-bottom pt-3 mb-2'>
                                    <div class='row'>
                                        <h6 class='card-title text-dark col-8'>".$row['Username']."
                                            <button type='button' name='".$row['Username']."' class='btn btn-outline-primary btn-sm' onclick='editaccount(this)' data-bs-toggle='modal' data-bs-target='#editaccountmodal'>
                                                <i class='fa fa-edit'></i>
                                            </button>
                                        </h6>
                                        <div class='col-4'>
                                            <div class='form-check form-check-sm form-switch '>
                                                <span class='text-capitalize' id='status:-:".$row['Username']."'>".$row['status']." </span>
                                                <input name='for:-:".$row['Username']."' class='form-check-input' onchange='status_change(this)' type='checkbox' role='switch' id='statuss'$ch>
                                            </div>
                                        </div>
                                    </div>
                                    <span class='text-center text-capitalize'>Role - ".$role."</span>
                                    </div>
                                    <ul>
                                        <li><span class='text-primary'>Account Type - </span>".$row['type']."</li>
                                        <li><span class='text-primary'>Email - </span>".$row['email']."</li>
                                        <li><span class='text-primary'>Phone - </span>".$row['phone']."</li>
                                        <li><span class='text-primary'>Department - </span>".$row['department']."</li>
                                        <li><span class='text-primary'>Company - </span>".$row['company']."</li>".
                                        (($row['managing'] != "" && !is_null($row['managing']))?
                                        "<li><span class='text-primary'>Managing Departments - </span>".$row['managing']."</li>":"").
                                        ((strpos($row['type'],"Cheque Signatory") !== False)?
                                        "<li><span class='text-primary'>Cheque privilege - </span>$cheque</li>":"")."
                                    </ul>
                                </div>
                            </div>";
                            if($count % 10 == 0)
                            {
                                echo "</div>";
                                $open = false;
                            }
                            $count++;
                    }
                    if($open)
                    {
                        echo "</div>";
                        $open = false;
                    }
                ?>
                <script>
                    function page_change(e)
                    {
                        let page_nums = e.parentElement.parentElement.children;
                        let pages = document.getElementsByClassName("pages");
                        let total_pages = parseInt(e.parentElement.parentElement.id);
                        if(e.innerHTML == "Previous" || e.innerHTML == "Next")
                        {
                            let current_page;
                            for(let i=0;i<pages.length;i++)
                            {
                                if(page_nums[i+1].className.includes("active"))
                                    current_page = parseInt(page_nums[i+1].children[0].innerHTML);
                                page_nums[i+1].classList.remove("active");
                                pages[i].classList.add("d-none");
                            }
                            var new_page = (e.innerHTML == "Previous")?current_page-1:current_page+1;
                            document.getElementById("page_"+new_page).classList.remove("d-none");
                            page_nums[new_page].classList.add("active");
                        }
                        else
                        {
                            for(let i=0;i<pages.length;i++)
                            {
                                page_nums[i+1].classList.remove("active");
                                pages[i].classList.add("d-none");
                            }
                            var new_page = parseInt(e.innerHTML);
                            e.parentElement.classList.add("active");
                            document.getElementById("page_"+e.innerHTML).classList.remove("d-none");
                        }
                        if(total_pages == new_page)
                            page_nums[(page_nums.length)-1].classList.add("disabled");
                        else
                            page_nums[(page_nums.length)-1].classList.remove("disabled");

                        if(new_page == 1)
                            page_nums[0].classList.add("disabled");
                        else
                            page_nums[0].classList.remove("disabled");
                    }
                </script>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination" id="<?php echo $amount;?>">
                            <li class="page-item disabled"><button class='page-link btn' type='button' onclick='page_change(this)'>Previous</button></li>
                            <?php for($i=1;$i<=$amount;$i++)
                            {
                                $active = ($i == 1)?" active":"";
                                echo "
                                <li class='page-item$active'>
                                    <button class='page-link btn' type='button' onclick='page_change(this)'>$i</button>
                                </li>";
                            }
                            ?>
                            <li class="page-item"><button class='page-link btn' type='button' onclick='page_change(this)'>Next</button></li>
                        </ul>
                    </nav>
                </div>
        </div>
    </div>
    </form>
</div>
<div id='tbl_view' class="d-none">
    <form method="POST" action="allphp.php" class="mx-auto border shadow">
        <table class="table table-striped mt-3" id="table1">
            <thead class="table-primary">
                <tr>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>managing(director)</th>
                    <th>percent(signatory)</th>
                    <th>Company</th>
                    <th>Account Type</th>
                    <th>Role of User</th>
                    <th> Additional Role </th>
                    <th>In-Use</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $tbl;?>
            </tbody>
        </table>
    </form>
</div>
</div>
<div class="d-none">
    <h3 class="text-center my-2">Existing Accounts</h3>
    <form method="POST" action="allphp.php" class="mx-auto border shadow">
        <table class="table table-striped mt-3">
            <thead class="table-primary">
                <tr>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>managing(director)</th>
                    <th>percent(signatory)</th>
                    <th>Company</th>
                    <th>Account Type</th>
                    <th>Role of User </th>
                    <th> Additional Role </th>
                    <th>In-Use</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $tbl;?>
            </tbody>
        </table>
    </form>
</div>
<div class="modal fade" id="editaccountmodal">
    <div class="modal-dialog modal-lg shadow">
        <div class="modal-content rounded-5">
            <form method="POST" action="allphp.php">
                <div class="modal-header alert-primary">
                    <h4 class="modal-title text-light">Edit Account</h4>
                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                </div>
                <div class="modal-body" id="editaccount">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control rounded-4" id="edit_Username" name='uname' readonly>
                            <label for="edit_Username">Username</label>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">+251</span>
                            </div>
                        <input type="text" name="tel" id="edit_phone" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9"required/>
                        </div>
                        <small class="text-secondary">Format : +251993819775</small>    
                        <!-- <div class="form-floating mb-3">
                            <input type="email" class="form-control rounded-4" id="edit_email" name='email' required>
                            <label for="edit_email">Email</label>
                        </div> -->
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control rounded-4" id="edit_email" name='email' required>
                            <label for="edit_email">Email</label>
                        </div>
                        <div class='row '>
                            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                                <select class="form-select" name="department"  id="edit_department" required>
                                        <option value="">-- Select one --</option>
                                    <?php
                                        $sql = "SELECT * FROM department";
                                        $stmt_department = $conn->prepare($sql); 
                                        $stmt_department->execute();
                                        $result = $stmt_department->get_result();
                                        if($result->num_rows>0)
                                        {
                                            while($row = $result->fetch_assoc())
                                            {
                                                echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
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
                                        $stmt_all_company->execute();
                                        $result = $stmt_all_company->get_result();
                                        if($result->num_rows>0)
                                        {
                                            while($row = $result->fetch_assoc())
                                            {
                                                echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                                            }
                                        }
                                    ?>
                                    </select>
                                <label for="edit_company">Company</label>
                            </div>
                        </div>
                        <div id='acc_types_current' class="mb-2">
                            <small class='d-block mb-2'>Current Account Types :</small>
                        </div>
                        <input type="text" id='to_remove_acc_type' name='to_remove_acc_type' class="d-none">
                        <div class='row'>
                            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                                <select class="form-select" name="role"  id="edit_role" required>
                                    <option value="">-- Select one --</option>
                                    <?php
                                        $sql = "SELECT * FROM `roles`";
                                        $stmt_role = $conn->prepare($sql); 
                                        $stmt_role->execute();
                                        $result = $stmt_role->get_result();
                                        if($result->num_rows>0)
                                        {
                                            while($row = $result->fetch_assoc())
                                            {
                                                echo "<option value='".$row['name']."'>".$row['name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="edit_role">Role</label>
                            </div>
                            <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                                <small>Account Type</small>
                                <select class="choices form-select multiple-remove" name="type[]" id="edit_acc_type" multiple="multiple">
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
                            <div id='managing_deps' class="mb-2">
                                <small class='d-block mb-2'>Selected Managing Departments :</small>
                            </div>
                            <input type="text" id='to_remove_dep' name='to_remove_dep' class="d-none">
                            <div class='row'>
                                <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                                    <select class="form-select" name="percent"  id="edit_cheque_percent">
                                        <option value="">Not A Cheque Signatory</option>
                                        <option value="p_100">Has Full privilege To Sign</option>
                                        <option value="p_50">Cosigner With privilege</option>
                                        <option value="not_50">Cosigner Without privilege</option>
                                    </select>
                                    <label for="percent">Cheque percent</label>
                                </div>
                                <div class="col-sm-12 col-md-6 mx-auto form-floating mb-3">
                                    <small>Managing Departments</small>
                                    <select class="choices form-select multiple-remove" name="man_deps[]"  id="man_deps"  multiple="multiple">
                                        <optgroup label="Departments">
                                        <option value='All Departments'>All Departments</option>
                                            <?php
                                                $stmt_department->execute();
                                                $result = $stmt_department->get_result();
                                                if($result->num_rows>0)
                                                {
                                                    while($row = $result->fetch_assoc())
                                                    {
                                                        echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
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
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="submit" class='btn btn-outline-primary mx-auto' name="edit_account">Edit Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function editaccount(e)
    {
        var row = document.getElementById("row_"+e.name);
        var td = row.children;
        for(let i=0;i<td.length;i++)
        {
            let ele = document.getElementById("edit_"+td[i].id.split(":-:")[0]);
            if(ele)
            { 
                if(td[i].id.split("_")[0].split(":-:")[0] == 'phone')
                {
                    let ph = td[i].innerHTML.split("251")[1];
                    ele.value = ph;
                }
                else if(td[i].id.split(":-:")[0] == 'role' || td[i].id.split(":-:")[0] == 'cheque_percent')
                {
                    ele.value = td[i].title;
                }
                else
                    ele.value = td[i].innerHTML;
            }
            else
            {
                if(td[i].id.split(":-:")[0] == 'managing')
                {
                    let pre_m_deps = document.getElementById("pre_m_deps");
                    if(pre_m_deps) pre_m_deps.remove();
                    if(td[i].innerHTML != "")
                    {
                        const div = document.createElement('div');
                        div.id = "pre_m_deps";
                        let all_m_deps = td[i].innerHTML.split(",");
                        for(let j = 0; j<all_m_deps.length;j++)
                        {
                            div.innerHTML += "<div class='btn btn-sm btn-primary mx-1 mb-2'>"+all_m_deps[j]+" <button class='btn btn-sm btn-primary' type='button' onclick='remove_mdep(this)'>X</button></div>";
                        }
                        document.getElementById('managing_deps').appendChild(div);
                        document.getElementById('managing_deps').classList.remove("d-none");
                    }
                    else
                        document.getElementById('managing_deps').classList.add("d-none");
                }
                else if(td[i].id.split(":-:")[0] == 'type')
                {
                    let pre_types = document.getElementById("pre_types");
                    if(pre_types) pre_types.remove();
                    if(td[i].innerHTML != "")
                    {
                        const div = document.createElement('div');
                        div.id = "pre_types";
                        let all_atypes = td[i].innerHTML.split(",");
                        for(let j = 0; j<all_atypes.length;j++)
                        {
                            div.innerHTML += "<div class='btn btn-sm btn-primary mx-1 mb-2'>"+all_atypes[j]+" <button class='btn btn-sm btn-primary' type='button' onclick='remove_acc_types(this)'>X</button></div>";
                        }
                        document.getElementById('acc_types_current').appendChild(div);
                        document.getElementById('acc_types_current').classList.remove("d-none");
                    }
                    else
                        document.getElementById('acc_types_current').classList.add("d-none");
                }
            }
        }
    }
    function remove_acc_types(e)
    {
        let to_remove = e.parentElement.innerHTML.split(" <button")[0];
        let val_local = document.getElementById("to_remove_acc_type").value;
        document.getElementById("to_remove_acc_type").value+= (val_local == "")?to_remove:","+to_remove;
        e.parentElement.remove();
    }
    function remove_mdep(e)
    {
        let to_remove = e.parentElement.innerHTML.split(" <button")[0];
        let val_local = document.getElementById("to_remove_dep").value;
        document.getElementById("to_remove_dep").value+= (val_local == "")?to_remove:","+to_remove;
        e.parentElement.remove();
    }
    function status_change(e)
    {
        let stat = (e.checked)?"active":"inactive";
        var data = e.name.split(":-:")[1];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById("status:-:"+data).innerHTML = stat;
            document.getElementById("tblstatus:-:"+data).innerHTML = stat;
        }
        req.open("GET", "allphp.php?uname="+data+"&stat="+stat);
        req.send();
    }
    function signatory(e)
    {
        let signatory_check = false;
        for(var option of e.options)
        {
            if(option.selected && option.value == "Cheque Signatory")
                signatory_check = true;
        }
        if(signatory_check)
            document.getElementById('percent').setAttribute("required",true);
        else
            document.getElementById('percent').removeAttribute("required");
    }
</script>