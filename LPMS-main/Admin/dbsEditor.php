<?php 
session_start();
if(isset($_SESSION['loc']))//
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
?>
<script>
    set_title("LPMS | Db. Editor");
    sideactive("db_editor");
</script>
<div id="main">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
            <header>
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            <h2>Dbs Editor</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Dbs Editor</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>
<script>
    var temp_data = "";
    var focus_id = '';
    function begin(e)
    {
        e.setAttribute("contenteditable","plaintext-only");
        e.focus();
        temp_data = e.innerHTML;
    }
    function changed(e)
    {
        e.removeAttribute("contenteditable");
        Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            })
            .then((result) => {
                if (result.isConfirmed) {
                    let upd = e.innerHTML.replaceAll("&",":/:");
                    let conn_var = document.getElementById('conns').value;
                    let dbs = document.getElementById("conns").options[document.getElementById('conns').selectedIndex].innerHTML;
                    const req = new XMLHttpRequest();
                    req.onload = function(){//when the response is ready
                        if(this.responseText == 'success')
                            Swal.fire('Successful!','Update Successful','success');
                        else
                        {
                            e.innerHTML = temp_data;
                            temp_data = "";
                            Swal.fire('Failed!',this.responseText,'error');
                        }
                    }
                    req.open("GET", "ajax_update.php?update_table="+e.id+"&value="+upd+"&conn_var="+conn_var+"&dbs="+dbs);
                    req.send();
                }
                else
                {
                    e.innerHTML = temp_data;
                    temp_data = "";
                }
            });
    }
    function dbs_change(e)
    {
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById("db_tables").innerHTML = this.responseText;
        }
        req.open("GET", "ajax_update.php?show_table="+e.value);
        req.send();
    }
</script>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div>
<select class='form-select text-primary mb-3' name='conns' id='conns' onchange="dbs_change(this)">
    <option name = 'conn' value = 'conn'>LPMS</option>
    <option value = 'conn_fleet'>FMS</option>
    <option value = 'conn_ais'>AIS</option>
    <option value = 'conn_pms'>PMS</option>
    <option value = 'conn_ws'>WS</option>
    <option value = 'conn_mrf'>MRF</option>
    <!-- <option value = 'conn_fleet'>FMS</option>
    <option value = 'conn_ais'>AIS</option>
    <option value = 'conn_pms'>PMS</option>
    <option value = 'conn_ws'>WS</option> -->
    <option name = 'conn_sms' value = 'conn_sms'>SMS</option>
    <?php ?>
</select>
</div>
    <div id ='db_tables' class="mx-auto">
    <h3 class="text-center my-3">Manage Database Tables</h3>
        <table class="table table-striped mt-3" id="table1">
            <thead class="table-primary">
                <tr>
                    <th class='text-center'>Tabels</th>
                    <th>Opperation</th>
                </tr>
            </thead>
            <tbody>
        <?php

if(isset($_POST['conns']))
{
    $conn_to_use = ${$_POST['conns']};
}
else 
    $conn_to_use = $conn;
        $sql = "SHOW TABLES";
        $stmt_show = $conn_to_use->prepare($sql); 
        $stmt_show -> execute();
        $result = $stmt_show -> get_result();
        if($result->num_rows>0)
            while($row = $result->fetch_assoc())
            {
                foreach($row as $r)
                if($r != "account")
                echo "<tr><td class='text-center'>$r</td>
                <td><button class='btn btn-primary btn-sm' onclick='terms(this)' type='button' data-bs-toggle='modal' data-bs-target='#dbs_editor_terms' name='tbl' value='$r' type='submit'>Open</button></td></tr>";
            }
        ?>
            </tbody>
        </table>
    </div>
<div class='modal fade' id='dbs_editor_terms'>
    <div class='modal-dialog modal-xl'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h3 id='top_text' class="w-100 text-center">Conditions <span class='small text-secondary'>(optional)</span>
                <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h3>
            </div>
            <div class='modal-body' id='reason_body'>
                <h5 class='text-center' id='query'></h4>
                <div class="row">
                    <div class="row col-12">
                        <div class='text-center mx-auto mb-4 col-10'>
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <button type='button' onclick="query_type(this)" class="btn nav-link active" data-bs-toggle="tab" id='select-tab' data-bs-target="#select_q" role="tab" aria-controls="select_q" aria-selected="true">
                                        Select Query
                                    </button>
                                </li>
                                <!-- <li class="nav-item">
                                    <button type='button' onclick="query_type(this)" class="btn nav-link" data-bs-toggle="tab" id='update-tab' data-bs-target="#update_q" role="tab" aria-controls="update_q" aria-selected="false">
                                        Update Query
                                    </button>
                                </li> -->
                            </ul>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-6'>
                        <div class="tab-content pt-2">
                            <div class="tab-pane fade show active" id="select_q" role="tabpanel" aria-labelledby="select-tab">
                                <div class="form-floating mb-3">
                                    <input onchange="terms_update(this)" onkeyup="terms_update(this)" type="text" class="form-control" name='specific_sql' placeholder="a" id='cond_sql' onBlur="focus_id = this.id">
                                    <label for="cond_sql">Condtions <i class="text-secondary text-primary">Sample : Attribute_1 = 'value_1'</i></label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input onchange="terms_update(this)" onkeyup="terms_update(this)"  type="number" class="form-control" name='limit_sql' placeholder="a" id='limit_sql' onBlur="focus_id = this.id">
                                    <label for="limit">Limit<span class="text-sm text-secondary">(optional) </span></label>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="update_q" role="tabpanel" aria-labelledby="update-tab">
                                <div class="form-floating mb-3">
                                    <input onchange="terms_update(this,'update')" onkeyup="terms_update(this,'update')" type="text" class="form-control" name='set_value' placeholder="a" id='set_sql_upd' onBlur="focus_id = this.id">
                                    <label for="cond_sql">Set Values <i class="text-secondary text-primary">Sample : Attribute_1 = 'value_1',Attribute_1 = 'value_1'</i></label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input onchange="terms_update(this,'update')" onkeyup="terms_update(this,'update')" type="text" class="form-control" name='update_sql' placeholder="a" id='cond_sql_upd' onBlur="focus_id = this.id">
                                    <label for="cond_sql">Condtions <i class="text-secondary text-primary">Sample : Attribute_1 = 'value_1'</i></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='col-sm-12 col-md-6' id='ajax_keys'>
                        
                    </div>
                </div>
                <div class='w-100'>
                    <button class='btn btn-primary mx-auto'  id='tbl_modal' name='tbl' type='submit'>Run</button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<?php
if(isset($_POST['conns']))
{
    $conn_to_use = ${$_POST['conns']};
    $tbl = $_POST['tbl'];
    //////////////////////////////////select//////////////////////////////////////////
    if(isset($_POST['specific_sql']) && $_POST['specific_sql']!='')
    {
        $specific_sql = $_POST['specific_sql'];
    }
    if(isset($_POST['limit_sql']) && $_POST['limit_sql']!='')
    {
        $limit_sql = $_POST['limit_sql'];
    }
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////Update//////////////////////////////////////////
    if(isset($_POST['update_sql']) && $_POST['update_sql']!='')
    {
        $specific_sql = $_POST['update_sql'];
    }
    if(isset($_POST['set_value']) && $_POST['set_value']!='')
    {
        $sql_pkey = "show columns from $tbl where `Key` = 'PRI'";
        $stmt_pkey = $conn -> prepare($sql_pkey);
        $stmt_pkey -> execute();
        $result_pkey = $stmt_pkey -> get_result();
        if($result_pkey->num_rows>0)
        {
            while($row_pkey = $result_pkey->fetch_assoc())
                $P_KEY = $row_pkey['Field'];
        }
        $cond = (!isset($specific_sql) || $specific_sql == "")?'1':$specific_sql;
        $select = "SELECT * from $tbl where $cond";
        if(!isset($P_KEY) || $P_KEY == '') 
            $select = 'LIMIT 1';
        $stmt_fetch = $conn_to_use -> prepare($select);
        $stmt_fetch -> execute();
        $result_fetch = $stmt_fetch -> get_result();
        $update_query = "UPDATE $tbl SET $_POST[set_value] WHERE $cond";
        $stmt_update_tbl = $conn_to_use -> prepare($update_query);
        if($stmt_update_tbl -> execute() === TRUE)
        {
            $_SESSION['success'] = $update_query." Successfully Executed";
            while($row = $result_fetch -> fetch_assoc())
            {
                $select = "SELECT * from $tbl where $cond";
                if(isset($P_KEY) && $P_KEY != '')
                {
                    $pri_val_pair = "$P_KEY-$row[$P_KEY]";
                    $select .= " AND $P_KEY = $row[$P_KEY]";
                }
                else
                {
                    $select = 'LIMIT 1';
                    $pri_val_pair = $cond;
                }
                $stmt_updated = $conn_to_use -> prepare($select);
                $stmt_updated -> execute();
                $result_updated = $stmt_updated -> get_result();

                $row_upd = $result_updated->fetch_assoc();
                $updated_data=array_diff_assoc($row,$row_upd);
                $before_data=array_diff_assoc($row_upd,$row);
                foreach($before_data as $att=>$val)
                {
                    $dbs = (sizeof(explode("_",$_POST['conns']))>1)?explode("_",$_POST['conns'])[1]:$_POST['conns'];
                    if($dbs == 'conn') $dbs = 'LPMS';
                    $updated_val = $before_data[$att]."->".$updated_data[$att];
                    $sql_dbs_edits = "INSERT INTO `dbs_edits`(`user`, `dbs`, `tbl`, `pri-value`, `att`, `value`) VALUES (?,?,?,?,?,?)";
                    $stmt_dbs_edits = $conn->prepare($sql_dbs_edits);
                    $stmt_dbs_edits -> bind_param("ssssss",$_SESSION['username'], $dbs, $tbl, $pri_val_pair, $att, $updated_val);
                    $stmt_dbs_edits -> execute();
                }
                    
            }
        }
        else
        {
            $_SESSION['failed_attempt'] = $update_query." Failed (".$conn_to_use->error.")";
        }
    }
    //////////////////////////////////////////////////////////////////////////////////
}
else
{
    $conn_to_use = $conn;
    $tbl = "account_types";
}
?>
<h3 class="text-center my-3">Manage Data of `<?= $tbl?>` Table</h3>
<table class="table table-striped mt-3" id="table2">
    <thead class="table-success">
        <tr>
<?php
$attributes = [];
$sql_columns = "SELECT * from $tbl where 1 Limit 1";
$stmt_columns = $conn_to_use -> prepare($sql_columns);
$stmt_columns -> execute();
$result_columns = $stmt_columns -> get_result();
if($result_columns -> num_rows>0)
    while($row = $result_columns -> fetch_assoc())
    {
        foreach($row as $att => $r_val)
        {
            if($att != "password" && $att != "creation_date")
            {
                array_push($attributes,$att);
                echo "<th>$att</th>";
            }
        }
    }
?>
</tr>
    </thead>
    <tbody>
<?php
$P_KEYS =[];
$forbiden = ['log','dbs_edits'];
$sql_PriKeys = "show columns from $tbl where `Key` = 'PRI'";
$stmt_PriKeys = $conn_to_use -> prepare($sql_PriKeys);
$stmt_PriKeys -> execute();
$result_PriKeys = $stmt_PriKeys -> get_result();
if($result_PriKeys -> num_rows > 0 && !in_array($tbl,$forbiden))
{
    while($row = $result_PriKeys->fetch_assoc())
        array_push($P_KEYS,$row['Field']);
    // $P_KEY = $row['Field'];
    $editable = " onClick='begin(this)' onFocusout='changed(this)'";
}
else
{
    $editable = "";
    $P_KEY = "";
}
$sql_tbl_data = "Select * from $tbl";
$sql_tbl_data .= (isset($specific_sql))?" Where ".$specific_sql:"";
$sql_tbl_data .= (isset($limit_sql))?" Limit ".$limit_sql:"";
$stmt_tbl_data = $conn_to_use -> prepare($sql_tbl_data);
$stmt_tbl_data -> execute();
$result_tbl_data = $stmt_tbl_data -> get_result();
if($result_tbl_data -> num_rows>0)
    while($row = $result_tbl_data -> fetch_assoc())
    {
        echo "<tr>";
            foreach($attributes as $att)
            {
                $P_KEYSS = "";
                if(isset($P_KEYS))
                foreach($P_KEYS as $P_KEY)
                {
                    $P_KEYSS .= ($P_KEYSS == "")?$row["$P_KEY"]:"::/::".$row["$P_KEY"];
                }
                // if($att != "password" && $att != "creation_date")
                echo "<td class='editable_stuff' id='".$tbl."::-::".$P_KEYSS."::-::".$att."' $editable>$row[$att]</td>";
            }
        echo "</tr>";
    }
?>
    </tbody>
</table>
<script>
    <?PHP 
    if(isset($_POST['conns']))
    {?>
        if(document.getElementsByName('<?php echo $_POST['conns']?>').length == 0)
        {
            let name_conn = ("<?php echo $_POST['conns']?>".split("_").length>1)?"<?php echo $_POST['conns']?>".split("_")[1]:"<?php echo $_POST['conns']?>";
            var option = document.createElement("option");
            option.text = name_conn;
            option.name = "<?php echo $_POST['conns']?>";
            option.value = "<?php echo $_POST['conns']?>";
            document.getElementById('conns').add(option);
        }
        document.getElementById('conns').value = '<?php echo $_POST['conns']?>';
        <?php
    }
    ?>
    var temp_q;
    function addattr(e)
    {
        let input = document.getElementById(focus_id);
        let position_insert = input.selectionStart;
        if(position_insert == 0)
        {
            input.value = "`"+e.innerHTML+"`"+input.value;
        }
        else
        {
            let new_str = input.value.slice(0,position_insert)+"`"+e.innerHTML+"`"+input.value.slice(position_insert);
            input.value = new_str;
        }
        input.focus();
        let pos_caret = position_insert + e.innerHTML.length+2;
        input.setSelectionRange(pos_caret, pos_caret);
    }
    function terms(e)
    {
        document.getElementById("query").innerHTML = "Select * from "+e.value;
        document.getElementById("tbl_modal").value = e.value;
        let con = document.getElementById("conns").value;
        temp_q = document.getElementById("query").innerHTML;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("cond_sql").value="";
        document.getElementById("limit_sql").value="";
            document.getElementById("ajax_keys").innerHTML=this.responseText;
        }
        req.open("GET", "ajax_update.php?keys="+e.value+"&conn="+con);
        req.send();
    }
    function terms_update(e,type='')
    {
        if(type != '')
        {
            let cond = document.getElementById("cond_sql_upd");
            let update = document.getElementById("set_sql_upd");
            let tbl_name = document.getElementById("tbl_modal").value;
            let new_temp = 'Update '+tbl_name+' set ';
            if(update.value != "") 
            {
                new_temp += update.value;
            }
            let conditions = "";
            if(cond.value != "") 
            {
                conditions += " Where "+cond.value;
            }
            else
                conditions = " Where 1";
            document.getElementById("query").innerHTML = new_temp+conditions;
        }
        else
        {
            let conditions = "";
            let cond = document.getElementById("cond_sql");
            let limit_sql = document.getElementById("limit_sql");
            if(cond.value != "") 
            {
                conditions += " Where "+cond.value;
            }
            if(limit_sql.value != "") 
            {
                conditions += " LIMIT "+limit_sql.value;
            }
            document.getElementById("query").innerHTML = temp_q+conditions;
        }
    }
    function query_type(e)
    {
        focus_id = '';
        let btn = document.getElementById('tbl_modal');
        if(e.id == 'select-tab')
        {
            btn.removeAttribute('onclick');
            btn.type = 'submit';
            document.getElementById('set_sql_upd').value = "";
            document.getElementById('cond_sql_upd').value = "";
        }
        else
        {
            btn.setAttribute('onclick','prompt_confirmation(this)');
            btn.type = 'button';
            document.getElementById('specific_sql').value = "";
            document.getElementById('limit_sql').value = "";
        }
    }
</script>
</div>
</div>
<?php include '../footer.php';?>
