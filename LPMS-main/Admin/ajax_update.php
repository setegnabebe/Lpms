<?php
session_start();
include "../connection/connect.php";
if(isset($_GET['update_table']))
{
    $P_KEYS = [];
    $tbl = explode("::-::",$_GET['update_table'])[0];
    $pri_value = explode("::-::",$_GET['update_table'])[1];
    $all_val_pri = explode("::/::",$pri_value);
    $update_att = explode("::-::",$_GET['update_table'])[2];
    $sql_keys = "show columns from $tbl where `Key` = 'PRI'";
    $stmt_keys = ${$_GET['conn_var']} -> prepare($sql_keys);
    $stmt_keys -> execute();
    $result_keys = $stmt_keys -> get_result();
    if($result_keys->num_rows>0)
    {
        while($row = $result_keys->fetch_assoc())
        {
            array_push($P_KEYS,$row['Field']);
        }
    }
    $val = trim($_GET['value']," \n\r\t\v\x00");
    $val = str_replace(":/:","&",$val);
    if($update_att == "phone")
        $val = "+".$val;
    
    $P_KEYSS = "";
    foreach($P_KEYS as $ind => $P_KEY)
    {
        $P_KEYSS .= ($P_KEYSS == "")?"`$P_KEY` = '$all_val_pri[$ind]'":"AND `$P_KEY` = '$all_val_pri[$ind]'";
    }
    $sql_table_data = "SELECT * from $tbl";
    $sql_table_data .= " WHERE $P_KEYSS";
    $stmt_table_data = ${$_GET['conn_var']} -> prepare($sql_table_data);
    $stmt_table_data -> execute();
    $result_table_data = $stmt_table_data -> get_result();
    $row = $result_table_data -> fetch_assoc();
    $pri_val_pair = "";
    foreach($P_KEYS as $ind => $P_KEY)
    {
        $pri_val_pair .= ($pri_val_pair == "")?"$P_KEY-$all_val_pri[$ind]":", $P_KEY-$all_val_pri[$ind]";
    }
    $updated_val = $row["$update_att"]."->".$val;
    $val = ($val != 'NULL')?"'$val'":$val;
    $sql_table_update = "UPDATE `$tbl` set `$update_att` = $val WHERE $P_KEYSS";
    $stmt_table_update = ${$_GET['conn_var']} -> prepare($sql_table_update);
    $stmt_table_update -> execute();
    if($stmt_table_update)
    {
        $sql_dbs_edits = "INSERT INTO `dbs_edits`(`user`, `dbs`, `tbl`, `pri-value`, `att`, `value`) VALUES (?,?,?,?,?,?)";
        $stmt_dbs_edits = $conn->prepare($sql_dbs_edits);
        $stmt_dbs_edits -> bind_param("ssssss",$_SESSION['username'] ,$_GET['dbs'] ,$tbl ,$pri_val_pair ,$update_att ,$updated_val);
        $stmt_dbs_edits -> execute();
        echo "success";
    }
    else
        echo ${$_GET['conn_var']}->error;
}
if(isset($_GET['show_table']))
{
    ?>
    <table class="table table-striped mt-3" id="table1">
        <thead class="table-primary">
            <tr>
                <th>Tabels</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $sql_tables = "SHOW TABLES";
    $stmt_tables = ${$_GET['show_table']} -> prepare($sql_tables);
    $stmt_tables -> execute();
    $result_tables = $stmt_tables -> get_result();
    if($result_tables -> num_rows>0)
        while($row = $result_tables -> fetch_assoc())
        {
            foreach($row as $r)
            echo "<tr><td>$r <button class='btn btn-primary btn-sm float-end' onclick='terms(this)' type='button' data-bs-toggle='modal' data-bs-target='#dbs_editor_terms' name='tbl' value='$r'>Open</button></td></tr>";
        }
    ?>
        </tbody>
    </table>
<?php
}
if(isset($_GET['keys']))
{
    $P_KEYS = [];
    $sql_columns = "show columns from $_GET[keys] where `Key` = 'PRI'";
    $stmt_columns = ${$_GET['conn']} -> prepare($sql_columns);
    $stmt_columns -> execute();
    $result_columns = $stmt_columns -> get_result();
    if($result_columns->num_rows>0)
    {
        while($row = $result_columns->fetch_assoc())
            array_push($P_KEYS,$row['Field']);
    }
    $sql_columns = "SELECT * from $_GET[keys] where 1 Limit 1";
    $stmt_columns = ${$_GET['conn']} -> prepare($sql_columns);
    $stmt_columns -> execute();
    $result_columns = $stmt_columns -> get_result();
    if($result_columns -> num_rows>0)
        while($row = $result_columns -> fetch_assoc())
        {
            echo "<div class='row'>";
            foreach($row as $att => $r_val)
            {
                $col = (in_array($att,$P_KEYS))?"outline-success":"primary";
                echo "<button type='button' class='btn btn-$col col mx-3 mb-2' onclick='addattr(this)'>$att</button>";
            }
            echo "</div>";
        }
}
?>