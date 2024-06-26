<!-- <div class="container"> -->
<div class="my-4 border p-3">
<div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h3 class="modal-title text-center my-3">Amend Limit between Main / Branch Committee</h3>
                <div class="form-floating mb-3">
                    <select class="form-select" name="company"  id="company" required>
                        <?php
                            $company = $_SESSION['company'];
                            $sql = "SELECT * FROM `limit_ho` ORDER BY id DESC limit 1";
                            $stmt_limit_1 = $conn->prepare($sql); 
                            $stmt_limit_1 -> execute();
                            $res = $stmt_limit_1 -> get_result();
                            if($res -> num_rows>0)
                            {
                                $r_new = $res->fetch_assoc();
                                $company = $r_new['company'];
                                $cheque_limit = $r_new['cheque_limit'];
                                $limit = $r_new['amount_limit'];
                                $limit_top = $r_new['amount_limit_top'];
                                $Vat = $r_new['Vat'];
                                $min_app = $r_new['minimum_approval'];
                                $cash_limit = $r_new['petty_cash'];
                                $perdiem_cash_limit = $r_new['perdiem_pettycash'];
                            }
                            else $limit = $cheque_limit = $limit_top = $min_app = $cash_limit = $perdiem_cash_limit = "";
                            echo "<option value='".$company."'>".$company."</option>";
                            $sql = "SELECT * FROM `comp`";
                            $stmt_all_company = $conn_fleet->prepare($sql); 
                            $stmt_all_company -> execute();
                            $result = $stmt_all_company -> get_result();
                            if($result -> num_rows>0)
                            {
                                while($row = $result->fetch_assoc())
                                {
                                    if($row['Name'] != $company)
                                        echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                                }
                            }
                        ?>
                        </select>
                    <label for="edit_company">Company</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control rounded-4" id="cheque_limit" placeholder="Amount" name='cheque_limit' value="<?php echo $cheque_limit?>">
                    <label for="limit">Cheque Limit Amount</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control rounded-4" id="limit" placeholder="Amount" name='limit' value="<?php echo $limit?>" required>
                    <label for="limit">Limit Amount</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control rounded-4" id="limit" placeholder="Amount-Top" name='limit_top' value="<?php echo $limit_top?>" required>
                    <label for="limit">Limit Amount To Top</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" step='any' class="form-control rounded-4" id="Vat" placeholder="Vat" name='Vat' value="<?php echo $Vat?>" required>
                    <label for="Vat">Vat</label>
                </div>
                <script>
                    function change_format(e)
                    {
                        let idd = e.id+"_field";
                        document.getElementById(idd).classList.remove("d-none");
                        var array_field = ["percentage_field","out_of_field"];
                        for(let i=0;i<array_field.length;i++)
                        {
                            if(array_field[i] == idd) continue
                            else document.getElementById(array_field[i]).classList.add("d-none");
                        }
                    }
                </script>
                <div class="row"> <!-- onkeyup="change_format()" onchange="change_format()"> -->
                    <div class='ms-3 form-check mb-3 col-5'>
                        <input class='form-check-input' type='radio' id='percentage' name='min_app_radio' value='percentage' onkeyup="change_format(this)" onchange="change_format(this)" checked>
                        <label class='form-check-label' for='percentage'>
                            Insert In Percentage Format
                        </label>
                    </div>
                    <div class='ms-3 form-check mb-3 col-5'>
                        <input class='form-check-input' type='radio' id='out_of' name='min_app_radio' value='out_of' onkeyup="change_format(this)" onchange="change_format(this)">
                        <label class='form-check-label' for='out_of'>
                            Insert In * out of * format
                        </label>
                    </div>
                </div>
                <div class="form-floating mb-3" id="percentage_field">
                    <input type="number" step='any' class="form-control rounded-4" id="min_app" placeholder="Minimum Approval Percentage" name='min_app' value="<?php echo $min_app?>" required>
                    <label for="min_app">Minimum Approval Percentage</label>
                </div>
                <div class="row d-none" id="out_of_field">
                    <div class="input-group my-3">
                        <input type="number" class="form-control rounded-4" id="Amount_app" placeholder="Amount_app" name='Amount_app'>
                        <span class="input-group-text">Out OF</span>
                        <input type="number" class="form-control rounded-4" id="Total_app" placeholder="Total_app" name='Total_app'>
                    </div>
                </div>
                <div class="form-floating mb-3" id="percentage_field">
                    <input type="number" step='any' class="form-control rounded-4" id="cash_limit" placeholder="Petty Cash Limit" name='cash_limit' value="<?php echo $cash_limit?>" required>
                    <label for="cash_limit">Petty Cash Limit</label>
                </div>
                <div class="form-floating mb-3" id="percentage_field">
                    <input type="number" step='any' class="form-control rounded-4" id="perdiem_cash_limit" placeholder="Perdiem Petty Cash Limit" name='perdiem_cash_limit' value="<?php echo $perdiem_cash_limit?>" required>
                    <label for="perdiem_cash_limit">Perdiem Petty Cash Limit</label>
                </div>
                <button class="btn btn-primary" type="button" onclick="prompt_confirmation(this)" name="amend_limit">Add Limit<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">View Previous Limits</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table5">
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
                    $stmt_limitAll = $conn->prepare($sql); 
                    $stmt_limitAll -> execute();
                    $result = $stmt_limitAll -> get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $vat = $row['Vat']*100;
                            $once=false;
                            echo "
                            <tr>
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

<div class="my-4 border p-3">
<div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h3 class="modal-title text-center my-3" id='form_top'>Manage Taxes</h3>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control rounded-4" id="tax_name" placeholder="Amount" name='tax_name'  required>
                    <label for="limit">Tax Name</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control rounded-4" id="limit" step='any' placeholder="Amount-Top" name='value' required>
                    <label for="limit">Tax Value</label>
                </div>
                <button class="btn btn-primary" type="submit"  name="add_tax">Add Vat<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                <a href='#form_top' class='d-none' id='btn_top'></a>
        </form>
    </div>
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
                        <th>Operation</th>
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
                                <td><a type='button'><i class='fa fa-edit me-3 text-success' onclick='edit($id)'></i><a/><a type='button'><i class='fa fa-trash me-3 text-danger' name='$id' onclick='delete_vat($id)'></i><a/></td>
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
function edit(e){
   alert(e)
   const btn=document.getElementById('btn_top')
   btn.type='button';
   btn.click();
}
function delete_vat(e){
           Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            })
            .then((countinue_opp) => {
                if (countinue_opp.isConfirmed) {
let xhr=new XMLHttpRequest();
xhr.onload=function(){
if(this.responseText==1){

    Swal.fire('Successful!',"Tax information deleted successfully",'success');
}
else{
    Swal.fire('Successful!',"Tax information is not deleted successfully",'error');
}
}
xhr.open("GET","allphp.php?tax_id="+e);
xhr.send();
                }
            });
}
</script>
<?php
$level = "";
$sql_stock_level = "SELECT `minimum_stock_level`, count(minimum_stock_level) AS count_maximum FROM store group by minimum_stock_level order by count_maximum DESC Limit 1";
$stmt_stock_level = $conn -> prepare($sql_stock_level);
$stmt_stock_level -> execute();
$result_stock_level = $stmt_stock_level -> get_result();
if($result_stock_level -> num_rows>0)
    while($row = $result_stock_level -> fetch_assoc())
    {
        $level = $row['minimum_stock_level'];
    }
?>
<div class="my-4 border p-3">
<h3 class="text-center my-3">Minimum Stock level for all</h3>
    <p class="text-center text-danger border border-3 border-primary"> Minimum Stock (current) : <?php echo $level?></p>
    <div data-aos="fade-right" class = 'mb-3'>
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <div class="form-floating mb-3">
                    <input type="number" value='<?php echo $level?>' class="form-control rounded-4" id="min_stock" name='min_stock' required>
                    <label for="min_stock">Minimum Stock Level</label>
                </div>
                <button class="btn btn-primary" type="submit" value='<?php echo $level?>' name="adjust_min">Adjust<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
</div>
<?php
$sql = "SELECT * from admin_settings order by id Desc Limit 1";
$stmt_setting = $conn->prepare($sql); 
$stmt_setting -> execute();
$result = $stmt_setting -> get_result();
if($result->num_rows>0)
    while($row = $result->fetch_assoc())
    {
        $timeout = $row['logout_time_min'];
        $m_limit = $row['month_limit_consumer_good'];
        $auto_days = $row['pms_auto_request'];
        $surveyLimit = $row['surveyLimit'];
        $no_limit = (is_null($surveyLimit));
    }
?>
<div class="my-4 border p-3">
<h3 class="text-center my-3">Auto Logout Time</h3>
    <p class="text-center text-danger border border-3 border-primary"> Logout Time (current) : <?php echo $timeout?> Min</p>
    <div data-aos="fade-right" class = 'mb-3'>
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <div class="form-floating mb-3">
                    <input type="number" value='<?php echo $timeout?>' class="form-control rounded-4" id="logout_time" name='logout_time' required>
                    <label for="min_stock">Auto Logout Time in Minutes</label>
                </div>
                <button class="btn btn-primary" type="submit" name="adjust_logout">Adjust<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
</div>
<div class="my-4 border p-3">
<h3 class="text-center my-3">Max Date for Consumer Goods Request(PMS)</h3>
    <p class="text-center text-danger border border-3 border-primary">
         Current Limit (Month) : <?php echo $m_limit?> Month<br>
         Auto Request in : <?php echo $auto_days?> Days
    </p>
    <div data-aos="fade-right" class = 'mb-3'>
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <div class="form-floating mb-3">
                    <input type="number" value='<?php echo $m_limit?>' class="form-control rounded-4" id="m_limit" name='m_limit' required>
                    <label for="m_limit">Set Month Limit</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" value='<?php echo $auto_days?>' class="form-control rounded-4" id="auto_days" name='auto_days' required>
                    <label for="auto_days">Set days for Auto Request</label>
                </div>
                <button class="btn btn-primary" type="submit" name="adjust_m_limit">Adjust Month Limit<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
</div>
<div class="my-4 border p-3">
    <h3 class="text-center my-3">Survey Expire Date</h3>
    <p class="text-center text-danger border border-3 border-primary"> Expire Time (current) : <?php echo $surveyLimit?></p>
    <div data-aos="fade-right" class = 'mb-3'>
        <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <div class="form-floating mb-3">
                    <input type="date" value='<?php echo $surveyLimit?>' class="form-control rounded-4" id="surveyLimit" name='surveyLimit'  <?=$no_limit?"readonly":""?> required>
                    <label for="surveyLimit">Survey Expire Date</label>
                </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" value="true" name="no_expire" id="no_expire" onclick="on_off(this,'surveyLimit')" <?=$no_limit?"checked":""?>>
                        <label class="form-check-label" for="no_expire">No Expire Date</label>
                    </div>
                <button class="btn btn-primary" type="submit" name="adjust_surveyExpire">Adjust<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
</div>
<script>
    function on_off(e,input,type='readonly')
    {
        if(e.checked) document.getElementById(input).setAttribute(type,true);
        else document.getElementById(input).removeAttribute(type);
    }
</script>
<!-- </div> -->