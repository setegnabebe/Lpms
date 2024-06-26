<!-- <div class="container"> -->
<div class="my-3 border p-3">
    <h3 class="text-center my-3">Manage Banks</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Bank</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="bank" placeholder="Bank Name" name='bank' required>
                    <label for="project">Bank Name</label>
                </div>
                <button class="btn btn-primary" type="submit" name="new_bank">Add Bank<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table10">
                <thead class="table-primary">
                    <tr>
                        <th>Bank</th>
                        <th>Date Inserted</th>
                        <th>Inserted By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM banks";
                    $stmt_bank = $conn->prepare($sql); 
                    $stmt_bank->execute();
                    $result = $stmt_bank->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            echo "
                            <tr id='row_".$row['id']."'>
                                <td class='text-capitalize' id='Name_".$row['id']."'>".$row['bank']."</td>
                                <td class='text-capitalize' id='date_".$row['id']."'>".date("d M Y",strtotime($row['added_date']))." ".date("h:i:s",strtotime($row['added_date']))."</td>
                                <td class='text-capitalize' id='Name_".$row['id']."'>".$row['added_by']."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div class="my-3 border p-3">
    <h3 class="text-center my-3">Manage Departments</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Department</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="dep" placeholder="Department Name" name='dep' required>
                    <label for="project">Department Name</label>
                </div>
                <button class="btn btn-primary" type="submit" name="new_department">Add Department<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table7">
                <thead class="table-primary">
                    <tr>
                        <th>Department</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM department";
                    $stmt_department = $conn->prepare($sql); 
                    $stmt_department->execute();
                    $result = $stmt_department->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            echo "
                            <tr id='row_".$row['Name']."'>
                                <td class='text-capitalize' id='Name_".$row['Name']."'>".$row['Name']."</td>
                                <td class='text-capitalize' id='date_".$row['Name']."'>".date("d M Y",strtotime($row['date_inserted']))." ".date("h:i:s",strtotime($row['date_inserted']))."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
                                        <!-- new -->
<div class="my-3 border p-3">
    <h3 class="text-center my-3">Manage Roles</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Roles</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="Role_add" placeholder="Role" name='role_add' required>
                    <label for="Role_add">Role</label>
                </div>
                <button class="btn btn-primary" type="submit" name="new_role">Add Role<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table8">
                <thead class="table-primary">
                    <tr>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM roles";
                    $stmt_role = $conn->prepare($sql); 
                    $stmt_role->execute();
                    $result = $stmt_role->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            echo "
                            <tr id='row_".$row['name']."'>
                                <td class='text-capitalize' id='RName_".$row['name']."'>".$row['name']."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div class="my-3 border p-3">
    <h3 class="text-center my-3">Manage Types</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Types</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="type_add" placeholder="Account Type" name='type_add' required>
                    <label for="type_add">Account Type</label>
                </div>
                <button class="btn btn-primary" type="submit" name="new_type">Add Account Type<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table9">
                <thead class="table-primary">
                    <tr>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM account_types";
                    $stmt_types = $conn->prepare($sql); 
                    $stmt_types->execute();
                    $result = $stmt_types->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            echo "
                            <tr id='row_".$row['name']."'>
                                <td class='text-capitalize' id='RName_".$row['name']."'>".$row['name']."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
                                        <!-- close -->
<div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Company</h3>
    <div data-aos="fade-right">
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <h4 class="modal-title text-light">Add Comapny</h4>
                <div class="form-floating mb-3">
                    <script>
                        function add_co(e)
                        {
                            let newOption = new Option(e.value,e.value);
                            document.getElementById("main_co").appendChild(newOption);
                        }
                    </script>
                    <input type="text" class="form-control rounded-4" id="Company" placeholder="Company Name" name='company' onchange="add_co(this)" required>
                    <label for="project">Company Name</label>
                </div>
                <div class="form-floating mb-3">
                    <select class="form-select" name="type"  id="type" required>
                            <option value="">-- Select one --</option>
                        <?php
                            $sql = "SELECT * FROM `comp` group by `type`";
                            $stmt_companyTypes = $conn_fleet->prepare($sql); 
                            $stmt_companyTypes->execute();
                            $result = $stmt_companyTypes->get_result();
                            if($result->num_rows>0)
                            {
                                while($row = $result->fetch_assoc())
                                {
                                    echo "<option value='".$row['type']."'>".$row['type']."</option>";
                                }
                            }
                        ?>
                        </select>
                    <label for="company">Type</label>
                </div>
                <div class="form-floating mb-3">
                    <select class="form-select" name="main_co"  id="main_co" required>
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
                    <label for="main">Main Company</label>
                </div>
                <div class="mb-3">
                    <input type='file' id='logo' class='form-control ms-0' name='logo'>
                    <label for="logo" class="form-label text-sm">Insert Logo</label>
                </div>
                <div class="row">
                    <div class='form-check mb-3 col'>
                        <input id='has_IT' name='has_IT' value='IT' class='form-check-input' type='checkbox'>
                        <label for="has_IT" class="form-label">IT</label>
                    </div>
                    <div class='form-check mb-3 col'>
                        <input id='has_property' name='has_property' value='property' class='form-check-input' type='checkbox'>
                        <label for="has_property" class="form-label">Property</label>
                    </div>
                    <div class='form-check mb-3 col'>
                        <input id='has_procurement' name='has_procurement' value='procurement' class='form-check-input' type='checkbox'>
                        <label for="has_procurement" class="form-label">Procurement</label>
                    </div>
                    <div class='form-check mb-3 col'>
                        <input id='has_purchasers' name='has_purchasers' value='purchasers' class='form-check-input' type='checkbox'>
                        <label for="has_purchasers" class="form-label">Purchasers</label>
                    </div>
                    <div class='form-check mb-3 col'>
                        <input id='has_finance' name='has_finance' value='finance' class='form-check-input' type='checkbox'>
                        <label for="has_finance" class="form-label">Finance</label>
                    </div>
                    <div class='form-check mb-3 col'>
                        <input id='has_ChequeSignatory' name='has_cheque' value='Cheque Signatory' class='form-check-input' type='checkbox'>
                        <label for="has_ChequeSignatory" class="form-label">Cheque Signatory</label>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" name="create_company">Add Company<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2"></h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table4">
                <thead class="table-primary">
                    <tr>
                        <th>Comapny</th>
                        <th>Type</th>
                        <th>Proccessing Company</th>
                        <th>Logo</th>
                        <th>IT</th>
                        <th>Property</th>
                        <th>Procurement</th>
                        <th>Purchasers</th>
                        <th>Finance</th>
                        <th>Cheque Signatory</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt_all_company->execute();
                    $result = $stmt_all_company->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch_IT =($row['IT'])?" checked":"";
                            $ch_prop =($row['property'])?" checked":"";
                            // $ch_swap =($row['store_swap'])?" checked":"";
                            $ch_proc =($row['procurement'])?" checked":"";
                            $ch_purch =($row['purchasers'])?" checked":"";
                            $ch_fin =($row['finance'])?" checked":"";
                            $ch_cheque =($row['cheque_signatory'])?" checked":"";
                            echo "
                            <tr id='row_".$row['Name']."'>
                                <td class='text-capitalize' id='Name_".$row['Name']."'>".$row['Name']."</td>
                                <td class='text-capitalize' id='Type_".$row['Name']."'>".$row['type']."</td>
                                <td class='text-capitalize' id='ProccessingCompany_".$row['Name']."'>
                                ";?>
                                    <select class="form-select form-select-sm" name="proccessing_<?php echo $row['Name']?>" onchange="change_pco(this)" id="proccessing_<?php echo $row['Name']?>" required>
                                        <?php
                                            $stmt_all_company->execute();
                                            $ress = $stmt_all_company->get_result();
                                            if($ress->num_rows>0)
                                            {
                                                while($rr = $ress->fetch_assoc())
                                                {
                                                    $selected = ($rr['Name'] == $row['main'])?" selected":"";
                                                    echo "<option value='".$rr['Name']."'$selected>".$rr['Name']."</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                               <?php echo "
                                </td>
                                <td class='text-capitalize' id='Name_".$row['Name']."'>
                                    ".$row['logo']."
                                    <button value='$row[logo]' name='$row[Name]' class='btn btn-outline-primary btn-sm mx-2' type='button' data-bs-toggle='modal' data-bs-target='#LogoModal' onclick='view_logo(this)'>View Logo</button>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='IT_".$row['Name']."' class='form-check-input' onchange='actions(this,\"IT\")' type='checkbox' role='switch' $ch_IT>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='property_".$row['Name']."' class='form-check-input' onchange='actions(this,\"property\")' type='checkbox' role='switch' $ch_prop>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='procurement_".$row['Name']."' class='form-check-input' onchange='actions(this,\"procurement\")' type='checkbox' role='switch' $ch_proc>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='purchasers_".$row['Name']."' class='form-check-input' onchange='actions(this,\"purchasers\")' type='checkbox' role='switch' $ch_purch>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='finance_".$row['Name']."' class='form-check-input' onchange='actions(this,\"finance\")' type='checkbox' role='switch' $ch_fin>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='chequesignatory_".$row['Name']."' class='form-check-input' onchange='actions(this,\"cheque_signatory\")' type='checkbox' role='switch' $ch_cheque>
                                    </div>
                                </td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
                
            </table>
        </form>
    </div>
</div>
<!---manage roles -------------------->
<div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Managerial Roles</h3>
    <div class='mx-auto' data-aos='fade-left'>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table2">
                <thead class="table-primary">
                    <tr>
                        <th>Username</th>
                        <th>Company</th>
                        <th>Procurnment Manager</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                  $sql = "SELECT * FROM `account` INNER JOIN comp on account.company=comp.Name WHERE ((account.department='GM' and account.type LIke '%Manager%') or account.role='Director') and account.company!='Hagbes HQ.';";
                  $stmt_additional = $conn->prepare($sql); 
                  $stmt_additional->execute();
                  $result = $stmt_additional->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch_dir =($row['additional_role'])==1?" checked":"";
                            echo "
                            <tr>     
                                <td class='text-capitalize' >".$row['Username']."</td>
                                <td class='text-capitalize'  >".$row['company']."</td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='Procurement:-:".$row['Username']."' class='form-check-input' onchange='change_role(this)' type='checkbox' role='switch' $ch_dir>
                                    </div>
                                </td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
                </tbody>
            </table>
        </form>
    </div>
</div>





<div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Projects</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Project</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="project" placeholder="Project Title" name='project' required>
                    <label for="project">Project Name</label>
                </div>
                <button class="btn btn-primary" type="submit" name="create_project">Add Project<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">Manage Projects</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table2">
                <thead class="table-primary">
                    <tr>
                        <th>Project</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM project";
                    $stmt_project_all = $conn->prepare($sql); 
                    $stmt_project_all->execute();
                    $result = $stmt_project_all->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch =($row['status'] == 'open')?" checked":"";
                            echo "
                            <tr id='row_".$row['Name']."'>
                                <td class='text-capitalize' id='Name_".$row['Name']."'>".$row['Name']."</td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <span class='text-capitalize' id='status_".$row['Name']."'>".$row['status']." </span><input id='for_".$row['Name']."' class='form-check-input' onchange='docs_change(this)' type='checkbox' role='switch' id='statuss'$ch>
                                    </div>
                                    <!--<label class='form-check-label' for='statuss'>".$row['status']."</label>-->
                                </td>
                                <!--<td class='text-center'>
                                    <button type='button' id='".$row['Name']."' class='btn btn-outline-primary' onclick='editaccount(this)' data-bs-toggle='modal' data-bs-target='#editaccountmodal'><i class='fa fa-edit'></i></button>
                                    <button type='button' class='btn btn-outline-danger'><i class='fa fa-trash'></i></button>
                                    <button class='btn btn-outline-primary'><i class='fa fa-edit'></i></button>
                                </td>-->
                                <td class='d-none text-danger' id = 'warn_".$row['Name']."'></td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Catagories</h3>
    <div data-aos="fade-right" class = 'mb-3'>
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <h4 class="modal-title text-light">Add Catagory</h4>
                <div class="row">
                    <div class="col form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="catagory" placeholder="catagory name" name='catagory' required>
                        <label for="catagory">Catagory Name</label>
                    </div>
                    <div class="col form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="display_name" placeholder="Display name" name='display_name' required>
                        <label for="display_name">Display Name</label>
                    </div>
                </div>
                <div class="form-floating">
                    <textarea class="form-control" placeholder="Describe the Catagory" name="catagory_description" id="catagory_description" style="height: 100px"></textarea>
                    <label for="catagory_description">Catagory Description</label>
                </div>
                <div class="form-floating d-none" id='privilege_div'>
                    <small>Privlage</small>
                    <select class="choices form-select multiple-remove" name="privilege[]" id="Privilege" multiple="multiple">
                        <optgroup label="Departments">
                            <?php
                                $stmt_all_company->execute();
                                $result_deps = $stmt_all_company->get_result();
                                if($result_deps->num_rows>0)
                                {
                                    while($row_deps = $result_deps->fetch_assoc())
                                    {
                                        $selected = (strpos($row_privilege['department'],$row_deps['Name']) !==false)?" selected":"";
                                        echo "<option value='".$row_deps['Name']."' $selected>".$row_deps['Name']."</option>";
                                    }
                                }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class='form-check mb-3'>
                    <input id='privilege_all' name='privilege_all' value='All' class='form-check-input' type='checkbox' onclick = 'all_privilege(this)' checked>
                    <label for="privilege_all" class="form-label">All Companies and Branchs Have privilege</label>
                </div>
                <div class="my-3">
                    <input type='file' id='catagory_img' class='form-control ms-0' name='catagory_img'>
                    <label for="catagory_img" class="form-label text-sm">Insert new catagory</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="path" placeholder="Path of file" name='path' value='requests/requestForm.php' required>
                    <label for="path">path</label>
                </div>
                <div class='form-check mb-3'>
                    <input id='replacement' name='replacement' value='1' class='form-check-input' type='checkbox'>
                    <label for="replacement" class="form-label">Items Have replacements</label>
                </div>
                <button class="btn btn-primary" type="submit" name="create_catagory">Add Catagory<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">Manage Catagories</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table6">
                <thead class="table-primary">
                    <tr>
                        <th>Catagory</th>
                        <th>Display Name</th>
                        <th>Description</th>
                        <th>image</th>
                        <th>Privilege (Companies)</th>
                        <th>Path</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM catagory";
                    $stmt_catagory = $conn->prepare($sql); 
                    $stmt_catagory->execute();
                    $result = $stmt_catagory->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            echo "
                            <tr id='row_".$row['catagory']."'>
                                <td class='text-capitalize' id='Name_".$row['catagory']."'>".$row['catagory']."</td>
                                <td class='text-capitalize' id='DName_".$row['catagory']."'>".$row['display_name']."</td>
                                <td class='text-capitalize' id='CDescr_".$row['catagory']."'>".$row['description']."</td>
                                <td class='text-capitalize' id='img_".$row['catagory']."'>".$row['image']."
                                <button value='$row[image]' name='$row[catagory]' class='btn btn-outline-primary btn-sm mx-2' type='button' data-bs-toggle='modal' data-bs-target='#CatagoryModal' onclick='view_catagory(this)'>View image</button>
                                </td>
                                <td class='text-capitalize' id='Privilege_".$row['catagory']."'>$row[privilege]
                                <button name='".$row['catagory']."' class='btn btn-outline-primary border-0' type='button' data-bs-toggle='modal' data-bs-target='#privilegemodal' onclick='privilegemodal(this)'><i class='fas fa-edit'></i></button>
                                </td>
                                <td class='text-capitalize' id='path_".$row['catagory']."'>".$row['path']."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Documentations</h3>
    <div data-aos="fade-right">
        <form method="POST" action="allphp.php"  enctype="multipart/form-data">
                <h4 class="modal-title text-light">Add Documents</h4>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="doc" placeholder="document Title" name='doc_name' required>
                    <label for="project">Name of Document</label>
                </div>
                <div class="mb-3">
                    <label for="doc_files" class="form-label">Insert Files</label>
                    <input id='doc_files' type='file' class='form-control multiple-files-filepond ms-0' name='docs' required>
                </div>
                <button class="btn btn-primary" type="submit" name="add_doc">Add Document<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">Manage Projects</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table11">
                <thead class="table-primary">
                    <tr>
                        <th>Document Name</th>
                        <th>Files</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM documentations";
                    $stmt_documents = $conn->prepare($sql); 
                    $stmt_documents->execute();
                    $result = $stmt_documents->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch =($row['status'] == 'open')?" checked":"";
                            echo "
                            <tr id='row_".$row['name']."'>
                                <td class='text-capitalize' id='Name_".$row['name']."'>".$row['name']."</td>
                                <td class='text-capitalize' id='file_".$row['name']."'>
                                    <a href='https://portal.hagbes.com/lpms_uploads/".$row['file']."' target='_blank' class='btn btn-outline-primary border-0' download >$row[file]</a>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <span class='text-capitalize' id='status_".$row['name'].":-:".$row['id']."'>".$row['status']." </span>
                                    <input id='for_".$row['name'].":-:".$row['id']."' class='form-check-input' onchange='docs_change(this,\"doc\")' type='checkbox' role='switch' id='statuss'$ch>
                                    </div>
                                </td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script>
    function privilegemodal(e)
    {
        document.getElementById("vieww_catagory").value = e.name;
        document.getElementById("vieww_dname").value = document.getElementById("DName_"+e.name).innerHTML;
        document.getElementById("update_catagory_description").value = document.getElementById("CDescr_"+e.name).innerHTML;
        document.getElementById("change_privilege").value = e.name;
    }
</script>
<form method="POST" action="allphp.php">
    <div class="modal fade" id="privilegemodal" tabindex="-1" role="dialog" aria-labelledby="privilegemodalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privilegemodalTitle">Catagory</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="vieww_catagory" placeholder="catagory name" name='vieww_catagory' readonly>
                        <label for="vieww_catagory">Catagory Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="vieww_dname" placeholder="catagory name" name='display_name'>
                        <label for="vieww_dname">Display Name</label>
                    </div>
                    <div class="form-floating d-none" id='edit_privilege_div'>
                        <small>Privlage</small>
                        <select class="choices form-select multiple-remove" name="privilege[]" id="edit_Privilege" multiple="multiple">
                            <optgroup label="company">
                                <?php
                                    $stmt_all_company->execute();
                                    $result_Comps = $stmt_all_company->get_result();
                                    if($result_Comps->num_rows>0)
                                    {
                                        while($row_deps = $result_Comps->fetch_assoc())
                                        {
                                            $selected = (strpos($row_privilege['company'],$row_deps['Name']) !==false)?" selected":"";
                                            echo "<option value='".$row_deps['Name']."' $selected>".$row_deps['Name']."</option>";
                                        }
                                    }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class='form-check mb-3'>
                        <input id="edit_privilege_all" name="privilege_all" value="All" class="form-check-input" type="checkbox" onclick='all_privilege(this,"edit_")' checked>
                        <label for="edit_privilege_all" class="form-label">All Companies and Branchs Have privilege</label>
                    </div>
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Describe the Catagory" name="catagory_description" id="update_catagory_description" style="height: 100px"></textarea>
                        <label for="catagory_description">Catagory Description</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" name='change_privilege' id='change_privilege'>Change</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- </div> -->
<form method="POST" action="allphp.php" enctype="multipart/form-data">
    <div class="modal fade" id="CatagoryModal" tabindex="-1" role="dialog" aria-labelledby="CatagoryModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CatagoryModalTitle">View Catagory Image</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <img class="d-block w-100" id='catagoryview'>
                    <div class=" d-none" id='change_catagory'>
                        <div class="my-3">
                            <input type='file' id='catagory' class='form-control ms-0' name='changed_catagory'>
                            <label for="catagory" class="form-label text-sm">Insert new Catagory Image</label>
                        </div>
                        <button class="btn btn-outline-primary" id='changed' name='change_catagory'>Change</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="catagory_change(this)">Change</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>
<form method="POST" action="allphp.php" enctype="multipart/form-data">
    <div class="modal fade" id="LogoModal" tabindex="-1" role="dialog" aria-labelledby="LogoModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="LogoModalTitle">View Logo</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <img class="d-block w-100" id='logoview'>
                    <div class=" d-none" id='change_logo'>
                        <div class="my-3">
                            <input type='file' id='logo' class='form-control ms-0' name='changed_logo'>
                            <label for="logo" class="form-label text-sm">Insert new Logo</label>
                        </div>
                        <button class="btn btn-outline-primary" id='changed_logoo' name='change_logo'>Change</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="logo_change(this)">Change</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function change_pco(e)
{
    let val = e.value;
    let co = e.name.split("_")[1];
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
        // document.getElementById().innerHTML = stat;
    }
    req.open("GET", "allphp.php?company="+co+"&proccessing="+val);
    req.send();
}
 
function actions(e,type)
{
    let stat = (e.checked)?1:0;
    var data = e.id.split("_")[1];
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
    }
    req.open("GET", "allphp.php?company="+data+"&val="+stat+"&action="+type);
    req.send();
}
function change_role(e)
{
    let data = (e.checked)?1:'';

    const req = new XMLHttpRequest();
    req.onload = function(){
    }
    req.open("GET", "allphp.php?additional_role="+data+"&user="+e.id.split(":-:")[1]);
    req.send();
}
function view_logo(e)
{
    document.getElementById("logoview").setAttribute("src","../img/"+e.value);
    document.getElementById("logoview").setAttribute("alt",e.value);
    document.getElementById("changed_logoo").value=e.name;
}
function logo_change(e)
{
    e.classList.add("d-none");
    document.getElementById("change_logo").classList.remove("d-none");
}
function docs_change(e,doc = "")
{
    let send_data = (doc == "")?"project_name":"document_name";
    let stat = (e.checked)?"open":"closed";
    var data = e.id.split("_")[1];
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
        document.getElementById("status_"+data).innerHTML = stat;
    }
    req.open("GET", "allphp.php?"+send_data+"="+data+"&stat="+stat);
    req.send();
}
function announcementChange(e,doc = "")
{
    let send_data = "changeAnnouncement";
    let stat = (e.checked)?"active":"closed";
    var data = e.id.split("_")[1];
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
        document.getElementById("announcement_"+data).innerHTML = stat;
    }
    req.open("GET", "allphp.php?"+send_data+"="+data+"&status="+stat);
    req.send();
}
function all_privilege(e,specific = "")
{
    if(e.checked)
    {
        document.getElementById(specific+'privilege_div').classList.add('d-none');
        document.getElementById(specific+'privilege_div').removeAttribute("required");
    }
    else
    {
        document.getElementById(specific+'privilege_div').classList.remove('d-none');
        document.getElementById(specific+'privilege_div').setAttribute("required","true");
    }
}
function view_catagory(e)
{
    document.getElementById("catagoryview").setAttribute("src","../img/"+e.value);
    document.getElementById("catagoryview").setAttribute("alt",e.value);
    document.getElementById("changed").value=e.name;
}
function catagory_change(e)
{
    e.classList.add("d-none");
    document.getElementById("change_catagory").classList.remove("d-none");
}
</script>