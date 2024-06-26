<?php 
    session_start();
if(isset($_SESSION['loc']))
{
    // $string_inc = 'head.php';
    // include $string_inc;
    $string_inc = '../'.$_SESSION["loc"].'head.php';
    include $string_inc;
}
else
    header("Location: ../");
?>
<script>
    set_title("LPMS | View Setting");
    sideactive("settings_view");
</script>
<div id="main">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
            <header>
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            <h2>Settings </h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>

    
    <div id="" class="container-fluid ">
        <?php ?>
        <div class="my-3 border p-3">
        <h3 class="text-center my-3">Amend Limit between Main / Branch Committee</h3>
           <div class="mx-auto aos-init aos-animate" data-aos="fade-left">
           <!-- <h3 class="text-center my-2">View Previous Limits</h3> -->
        <!-- <h3 class="text-center my-2">View Previous Limits</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped" id="table1">
                <thead class="table-primary">
                    <tr>
                        <th>Company</th>
                        <th>Limit for Branch</th>
                        <th>Limit For Procurement Dep.</th>
                        <th>Vat</th>
                        <th>Minimum Approval Percentage</th>
                        <th>Petty Cash Limit</th>
                        <th>Perdiem Petty Cash Limit</th>
                        <th>Date Set</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $once =true;
                    $sql = "SELECT * FROM `limit_ho` ORDER BY id DESC";
                    $stmt_limit = $conn->prepare($sql); 
                    $stmt_limit->execute();
                    $result = $stmt_limit->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $vat = $row['Vat']*100;
                            $once=false;
                            echo "
                            <tr class='alert-secondary'>
                                <td class='text-capitalize'>".$row['company']."</td>
                                <td class='text-capitalize'>".$row['amount_limit']."</td>
                                <td class='text-capitalize'>".$row['amount_limit_top']."</td>
                                <td class='text-capitalize'>".$vat."%</td>
                                <td class='text-capitalize'>".$row['minimum_approval']."%</td>
                                <td class='text-capitalize'>".$row['petty_cash']."</td>
                                <td class='text-capitalize'>".$row['perdiem_pettycash']."</td> 
                                <td class='text-capitalize'>".$row['date']."</td>
                                <td class='d-none text-danger' id = 'warn_".$row['id']."'></td>
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
    <div id="manage_div" class="container-fluid ">
        <?php ?>
        <div class="my-3 border p-3">
<h3 class="text-center my-3">Manage Company</h3>
    <div data-aos="fade-right">
       
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2"></h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table3">
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
                    $stmt_all_company -> execute();
                    $result = $stmt_all_company -> get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch_IT =($row['IT'])?" checked ":"";
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
                                ".$row['main'];
                                ?>
                               <?php echo "
                                </td>
                                <td class='text-capitalize' id='Name_".$row['Name']."'>
                                    ".$row['logo']."
                                    <button value='$row[logo]' name='$row[Name]' class='btn btn-outline-primary btn-sm mx-2' type='button' data-bs-toggle='modal' data-bs-target='#LogoModal' onclick='view_logo(this)'>View Logo</button>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='IT_".$row['Name']."' class='form-check-input' onchange='actions(this,\"IT\")' type='checkbox' role='switch' Disabled $ch_IT>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='property_".$row['Name']."' class='form-check-input' onchange='actions(this,\"property\")' type='checkbox' role='switch' Disabled $ch_prop>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='procurement_".$row['Name']."' class='form-check-input' onchange='actions(this,\"procurement\")' type='checkbox' role='switch' Disabled $ch_proc>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='purchasers_".$row['Name']."' class='form-check-input' onchange='actions(this,\"purchasers\")' type='checkbox' role='switch' Disabled $ch_purch>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='finance_".$row['Name']."' class='form-check-input' onchange='actions(this,\"finance\")' type='checkbox' role='switch' Disabled $ch_fin>
                                    </div>
                                </td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <input id='chequesignatory_".$row['Name']."' class='form-check-input' onchange='actions(this,\"cheque_signatory\")' type='checkbox' role='switch' Disabled $ch_cheque>
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
                    </div>
<h3 class="text-center my-2">Existing Accounts</h3>
    <form method="POST" action="allphp.php" class="mx-auto border shadow">
        <table class="table table-striped mt-3" id="table2">
            <thead class="table-primary">
                <tr>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>managing(director)</th>
                    <th>Company</th>
                    <th>Account Type</th>
                    <th>Role of User</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $count = 1;
                $open = false;
                $tbl = "";
                $sql = "SELECT * FROM account order by Username ASC";
                $stmt_account = $conn->prepare($sql); 
                $stmt_account -> execute();
                $result = $stmt_account -> get_result();
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
                        $text=$row['status'] == 'active'?"text-success":($row['status'] == 'waiting'?"text-warning":"text-danger");
                        $role = ($row['role'] == 'Director' && $row['company'] != "Hagbes HQ.")?$row['role']." (Branch Manager".($row['additional_role']?' ,procurnment manager)':''):$row['role'];
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
                        echo "<tr id='row_".$row['Username']."'>
                        <td id='Username:-:".$row['Username']."'>".$row['Username']."</td>
                        <td id='phone:-:".$row['Username']."'>".$row['phone']."</td>
                        <td id='email:-:".$row['Username']."'>".$row['email']."</td>
                        <td id='department:-:".$row['Username']."'>".$row['department']."</td>
                        <td id='managing:-:".$row['Username']."'>".$row['managing']."</td>
                        <td id='company:-:".$row['Username']."'>".$row['company']."</td>
                        <td id='type:-:".$row['Username']."'>".str_replace("Cheque Signatory","Cheque Signatory($cheque)",$row['type'])."</td>
                        <td id='role:-:".$row['Username']."' title='$row[role]'>".$role."</td>
                        <td>
                            <div class='form-check form-switch'>
                                <span class='text-capitalize $text'>".$row['status']." </span>
                            </div>
                            
                        </td>
                    </tr>";
                      
                    }?>
            </tbody>
        </table>
    </form>

                <h3 class="modal-title text-center my-3" id='form_top'>View Taxes</h3>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">View Previous Limits</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table6">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Tax Name</th>
                        <th>Tax Value</th>
                        <th>Created By</th>
                        <th>Date Created</th>
                        <!-- <th>Operation</th>  -->
                    </tr>
                </thead>
                <tbody>
                    <?php 
                   $x=0;
                    $sql = "SELECT * FROM `tax` ORDER BY id ASC";
                    $stmt_tax = $conn->prepare($sql); 
                    $stmt_tax -> execute();
                    $result = $stmt_tax -> get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $vat = $row['value']*100;
                            $id=$row['id'];
                            echo "
                            <tr>
                                <td class='text-capitalize'>".(++$x)."</td>
                                <td class='text-capitalize'>".$row['tax_name']."</td>
                                <td class='text-capitalize'>".$vat."%</td>
                                <td class='text-capitalize'>".$row['createdBy']."</td>
                                <td class='text-capitalize'>".$row['dateCreated']."</td>
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
                    <!-- <button type="button" class="btn btn-primary" onclick="logo_change(this)">Change</button> -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

 
               
<script>
function view_logo(e)
{
    document.getElementById("logoview").setAttribute("src","../img/"+e.value);
    document.getElementById("logoview").setAttribute("alt",e.value);
    document.getElementById("changed_logoo").value=e.name;
}
const datatablesSimple = document.getElementById('tbl_acc');
if (datatablesSimple) {
    new simpleDatatables.DataTable(datatablesSimple);
}
</script>
<?php include '../footer.php';?>


