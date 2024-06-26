<?php
session_start();
include '../connection/connect.php';
include '../common/functions.php';
// $scale_recieved = (isset($_SESSION['current_scale']))?$_SESSION['current_scale']:$_GET['scale'];
$str = '';
$sql_scale = "SELECT DISTINCT `scale` FROM `purchase_order` where `cluster_id`= ?";
$stmt_scale = $conn->prepare($sql_scale);
$stmt_scale->bind_param("i", $_GET['c_id']);
$stmt_scale->execute();
$result_scale = $stmt_scale->get_result();
if($result_scale->num_rows>0)
while($row = $result_scale->fetch_assoc())
{
    $scale = $row['scale'];
}

$all_selectsss = "";
$selections_match = 1;
$sql_cluster = "SELECT * FROM `cluster` Where `id` = ?";
$stmt_cluster = $conn->prepare($sql_cluster);
$stmt_cluster->bind_param("i", $_GET['c_id']);
$stmt_cluster->execute();
$result_cluster = $stmt_cluster->get_result();
if($result_cluster->num_rows>0)
$row_temp = $result_cluster->fetch_assoc();
$compiled_by = $row_temp['compiled_by'];
$procurement_company = $row_temp['procurement_company'];
$req_company = $row_temp['company'];
$sql_selections = "SELECT * FROM `selections` Where `cluster_id` = ?";
$stmt_selections = $conn->prepare($sql_selections);
$stmt_selections->bind_param("i", $_GET['c_id']);
$stmt_selections->execute();
$result_selections = $stmt_selections->get_result();
if($result_selections->num_rows>0)
while($row_temp = $result_selections->fetch_assoc())
{
    if($compiled_by != $row_temp['user'])
        if($all_selectsss=="")$all_selectsss = $row_temp["selection"];
        else
        {
                if($all_selectsss != $row_temp["selection"])
                {
                    $set[$row_temp['user']] = (isset($set[$row_temp['user']]))?$set[$row_temp['user']]++:0;
                    $selections_match = 0;
                }
        }
}


// $scale_recieved = (isset($_SESSION['current_scale']))?$_SESSION['current_scale']:$_GET['scale'];
$ssscale = $scale;
if($scale != 'Owner' && $scale != 'procurement') 
$scale .= ' Committee';
$str .="<ul class= 'list-group list-group-flush'>
<h5 class='text-capitalize my-5 text-center'>Approval Status Of All ".$scale."</h5>";
if($scale == 'Owner')
 $eval = "(`role` = 'Owner' OR (`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = '$procurement_company'))";
else if($scale == 'procurement') 
$eval = "`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = 'Hagbes HQ.'";
else if(strpos($scale,"HO")!==false) 
$eval = "((`type` LIKE '%".$scale."%' AND company = 'Hagbes HQ.')".(($procurement_company != "Hagbes HQ.")?" Or (`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = '$procurement_company')":"").")";
else 
$eval = "((`type` LIKE '%".$scale."%' AND company = '$req_company') OR ((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%')) or `additional_role`=1 ) AND company = '$procurement_company')";
$spec="";
$sql_po = "SELECT * FROM `purchase_order` where `cluster_id`= ?";
$stmt_po = $conn->prepare($sql_po);
$stmt_po->bind_param("i", $_GET['c_id']);
$stmt_po->execute();
$result_po = $stmt_po->get_result();
if($result_po->num_rows>0)
while($row = $result_po->fetch_assoc())
{
    $req_id = $row["request_id"];
}
$stmt_request -> bind_param("i", $req_id);
$stmt_request -> execute();
$result_request = $stmt_request -> get_result();
if($result_request -> num_rows>0)
while($row = $result_request -> fetch_assoc())
{
    $req_by = $row['customer'];
    $req_com = $row['company'];
    $dep = $row['department'];
    $comp = $row['company'];
    $true = 1;
    $sql_comp  = "SELECT * FROM comp where `Name`=? and IT = ?";
    $stmt_comp = $conn_fleet->prepare($sql_comp);
    $stmt_comp->bind_param("si", $req_com, $true);
    $stmt_comp->execute();
    $result_comp = $stmt_comp->get_result();
    $it_comp = ($result_comp ->num_rows>0)? $req_com : "Hagbes HQ.";
    if($row['spec_dep'] == 'IT' && !($row['spec_dep']==$row['department'] && $row['company'] == 'Hagbes HQ.'))
        $spec=" OR (department='".$row['spec_dep']."' and company = '$it_comp' and (role = 'manager' OR `type` LIke '%manager%'))";
    $sql_account = "SELECT * FROM `account` where  Username = ?";
    $stmt_account = $conn->prepare($sql_account);
    $stmt_account->bind_param("s", $req_by);
    $stmt_account->execute();
    $result_account = $stmt_account->get_result();
    if($result_account->num_rows>0)
        while($row_man = $result_account->fetch_assoc())
            $role = $row_man['role'];
}
$req_dep_man = [];
$man_dep = ((strpos($scale,"HO")!==false || $scale == 'Owner') && $req_company != 'Hagbes HQ.' && $dep != 'GM' && $dep != 'Director')?"(department = '$dep' OR department = 'GM' OR department = 'Director')":"department = '$dep'";
if($comp != 'Hagbes HQ.' && $scale == 'procurement')
{
    $str .="<h6 class='text-center'>Procurment Committee</h6>";
    $sql_approval = "SELECT * FROM `committee_approval` where `cluster_id` = ?";
    $stmt_approval = $conn->prepare($sql_approval);
    $stmt_approval->bind_param("i", $_GET['c_id']);
    $stmt_approval->execute();
    $result_approval = $stmt_approval->get_result();
    if($result_approval->num_rows>0)
    while($row2 = $result_approval->fetch_assoc())
    {
        array_push($req_dep_man,$row2['committee_member']);
        if($row2['status'] == 'Reactivated')
        {
            $icon = "<i class='ms-3 text-primary fas fas fa-clock'></i>";
            $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row2['committee_member']."  -  </i>Waiting $icon</li>";
            break;
        }
        if($row2['status'] == 'Approved') $icon = "<i class='ms-3 text-success fas fa-check-circle'></i>";
        else
        {
            $icon = "<i class='ms-3 text-danger fas fa-times-circle'></i>";
        }
        $icon .= ($row2['committee_member'] == $_SESSION['username'])?"<button name='reactivate' value='".$_GET['c_id']."' class='btn btn-sm btn-outline-warning'>Reactivate</button>":"";
        
        $str .="<li class='list-group-item list-group-item-light text-center'>
                    <i class='text-primary'>".$row2['committee_member']."  -  </i>".$row2['status']." $icon";
        $str .="<ul class= 'list-group list-group-flush'>
            <li class='list-group-item list-group-item-light text-center ms-4 border-0 text-secondary'><i>Date  -  </i>".date("d-M-Y h:i:s", strtotime($row2['timestamp']))."</li>";
        if($row2['remark']!='#')
            $str .="<li class='list-group-item list-group-item-light text-center ms-4 text-secondary'><i>Reason  -  </i>".$row2['remark']."</li>";
        $str .= "</ul>";
    }
    else {
        $icon = "<i class='ms-3 text-primary fas fa-clock'></i>";
        $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row2['committee_member']."  -  </i>Waiting $icon</li>";
    }
}
if($dep == "Owner" || $role == "Director")
    $sql = "SELECT * FROM `account` where  Username = '$req_by' AND status = 'active'";
else
    $sql = "SELECT * FROM `account` where $man_dep AND company = '$comp' and ((role = 'manager' OR `type` LIke '%manager%') OR role = 'Director' OR role = 'GM') and status = 'active' and type NOT LIKE '%$ssscale%' and type NOT LIKE '%Owner%'";
$stmt_manager_director = $conn->prepare($sql);
$stmt_manager_director -> execute();
$result_manager_director = $stmt_manager_director->get_result();
$new_sql = "";
$same = false;
if($result_manager_director->num_rows>0)
while($row = $result_manager_director->fetch_assoc()) 
{
    array_push($req_dep_man,$row['Username']);
    $sql_approval = "SELECT * FROM `committee_approval` where `committee_member` = ? && `cluster_id` = ?";
    $stmt_approval = $conn->prepare($sql_approval);
    $stmt_approval -> bind_param("si", $row['Username'], $_GET['c_id']);
    $stmt_approval -> execute();
    $result_approval = $stmt_approval->get_result();
    if($result_approval->num_rows>0)
    while($row2 = $result_approval->fetch_assoc())
    {
        if($row2['status'] == 'Reactivated')
        {
            $icon = "<i class='ms-3 text-primary fas fas fa-clock'></i>";
            $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row['Username']." <small class='text-secondary'>(Requesting Department)</small>  -  </i>Waiting $icon</li>";
            break;
        }
        if($row2['status'] == 'Approved') $icon = "<i class='ms-3 text-success fas fa-check-circle'></i>";
        else
        {
            $icon = "<i class='ms-3 text-danger fas fa-times-circle'></i>";
            // $icon .= ($row2['committee_member'] == $_SESSION['username'])?"<button name='reactivate".$_GET['c_id']."' class='btn btn-sm btn-outline-warning'>Reactivate</button>":"";
        }
        $icon .= ($row2['committee_member'] == $_SESSION['username'])?"<button name='reactivate' value = '".$_GET['c_id']."' class='btn btn-sm btn-outline-warning'>Reactivate</button>":"";
        $str .="<li class='list-group-item list-group-item-light text-center border-top'><i class='text-primary'>".$row['Username']." <small class='text-secondary'>(Requesting Department)</small>  -  </i>".$row2['status']." $icon";
        $str .="<ul class= 'list-group list-group-flush'>
            <li class='list-group-item list-group-item-light text-center ms-4 border-0 text-secondary'><i>Date  -  </i>".date("d-M-Y h:i:s", strtotime($row2['timestamp']))."</li>";
        if($row2['remark']!='#')
            $str .="<li class='list-group-item list-group-item-light text-center ms-4 text-secondary'><i>Reason  -  </i>".$row2['remark']."</li>";
        $str .= "</ul>";
    }
    else {
        $icon = "<i class='ms-3 text-primary fas fa-clock'></i>";
        $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row['Username']." <small class='text-secondary'>(Requesting Department)</small>  -  </i>Waiting $icon</li></ul>";
    }
}
$sql = "SELECT * FROM `account` where ($eval $spec) AND status = 'active'";
if(isset($req_dep_man))
{
    foreach ($req_dep_man as $value_dep) {
        $sql .= " and Username != '$value_dep'";
    }
}
$stmt_committee_members = $conn->prepare($sql);
$stmt_committee_members -> execute();
$result_committee_members = $stmt_committee_members->get_result();
if($result_committee_members->num_rows>0)
while($row = $result_committee_members->fetch_assoc())
{
    $sql_approval = "SELECT * FROM `committee_approval` where `committee_member` = ? && `cluster_id` = ?";
    $stmt_approval = $conn->prepare($sql_approval);
    $stmt_approval -> bind_param("si", $row['Username'], $_GET['c_id']);
    $stmt_approval -> execute();
    $result_approval = $stmt_approval->get_result();
    if($result_approval->num_rows>0)
    while($row2 = $result_approval->fetch_assoc())
    {
        if($row2['status'] == 'Reactivated')
        {
            $icon = "<i class='ms-3 text-primary fas fas fa-clock'></i>";
            $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row['Username']."  -  </i>Waiting $icon</li>";
            break;
        }
        if($row2['status'] == 'Approved') $icon = "<i class='ms-3 text-success fas fa-check-circle'></i>";
        else
        {
            $icon = "<i class='ms-3 text-danger fas fa-times-circle'></i>";
        }
        $icon .= ($row2['committee_member'] == $_SESSION['username'])?"<button name='reactivate' value='".$_GET['c_id']."' class='btn btn-sm btn-outline-warning'>Reactivate</button>":"";
        
        $str .="<li class='list-group-item list-group-item-light text-center'>
                    <i class='text-primary'>".$row['Username']."  -  </i>".$row2['status']." $icon";
        $str .="<ul class= 'list-group list-group-flush'>
            <li class='list-group-item list-group-item-light text-center ms-4 border-0 text-secondary'><i>Date  -  </i>".date("d-M-Y h:i:s", strtotime($row2['timestamp']))."</li>";
        if($row2['remark']!='#')
            $str .="<li class='list-group-item list-group-item-light text-center ms-4 text-secondary'><i>Reason  -  </i>".$row2['remark']."</li>";
        $str .= "</ul>";
    }
    else {
        $icon = "<i class='ms-3 text-primary fas fa-clock'></i>";
        $str .="<li class='list-group-item list-group-item-light text-center'><i class='text-primary'>".$row['Username']."  -  </i>Waiting $icon</li>";
    }
}
            $sql_cluster = "SELECT * FROM `cluster` where `id`= ?";
            $stmt_cluster = $conn->prepare($sql_cluster);
            $stmt_cluster -> bind_param("i", $_GET['c_id']);
            $stmt_cluster -> execute();
            $result_cluster = $stmt_cluster->get_result();
            $clus_row=$result_cluster->fetch_assoc();
            $sql_limit = "SELECT * FROM `limit_ho` where company=? ORDER BY id DESC limit 1";
            $stmt_limit = $conn->prepare($sql_limit);
            $stmt_limit -> bind_param("i", $clus_row['company']);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
        if ($result_limit->num_rows ==0)
        {
            $compaines = "Others";
            $stmt_limit -> bind_param("i", $compaines);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
        }
        if($result_limit->num_rows>0)
        {
            $r_limit = $result_limit->fetch_assoc();
            $minimum_approval = $r_limit['minimum_approval'];
        }

if(strpos($scale,'Committee')===false && $scale!='procurement')
    $eval = "(`role` = 'Owner' OR (((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') ) or `additional_role` = 1 ) AND company = '$procurement_company'))";
else if($scale=='procurement')
    $eval = "`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%') AND company = 'Hagbes HQ.'";
else if(strpos($scale,'HO')!==false)
    $eval = "type LIKE '%".$scale."%'";
else 
    $eval = "(type LIKE '%".$scale."%' AND company = '$req_com') OR (((`department` = 'procurement' AND (role = 'manager' OR `type` LIke '%manager%')) or `additional_role` = 1 ) AND company = '$procurement_company' )";
$sql_com_count = "SELECT count(*) as com_count FROM `account` Where ($eval $spec) and status = 'active'";
$stmt_com_count = $conn->prepare($sql_com_count);
$stmt_com_count -> execute();
$result_com_count = $stmt_com_count -> get_result();
if($result_com_count->num_rows>0)
    while($row_temp = $result_com_count->fetch_assoc())
        $com_count = $row_temp['com_count'];

// $sql_man = "SELECT * FROM `account` where  department = '$dep' AND company = '$req_com' and (role = 'manager' OR `type` LIke '%manager%') and status = 'active' and type NOT LIKE '%Committee%' and type NOT LIKE '%Owner%'";
$sql_man = "SELECT * FROM `account` where  $man_dep AND company = ? and ((role = 'manager' OR `type` LIke '%manager%') OR role = 'Director' OR role = 'GM' OR role = 'Owner') and status = 'active' and type NOT LIKE '%".$scale."%'";
$stmt_accounts = $conn->prepare($sql_man);
$stmt_accounts -> bind_param("s", $req_com);
$stmt_accounts -> execute();
$result_accounts = $stmt_accounts -> get_result();
if($result_accounts -> num_rows>0)
{
    while($row_man = $result_accounts -> fetch_assoc())
        $com_count++;
}
// if(strpos($scale,"HO")!==false && $req_company != 'Hagbes HQ.' && $dep != 'GM')
// $com_count++;
if($dep == "Procurement" && $comp == "Hagbes HQ." && $scale=='procurement') 
$com_count--;
if($dep == "Procurement" && $comp != "Hagbes HQ." && $procurement_company != "Hagbes HQ." && strpos($scale,'Branch')!==false) 
$com_count--;
// if($spec)
// $com_count--;

$sql_com_count = "SELECT count(*) as com_count FROM `committee_approval` Where `cluster_id` = ? AND `status` = 'Approved'";
$stmt_com_count = $conn->prepare($sql_com_count);
$stmt_com_count -> bind_param("i", $_GET['c_id']);
$stmt_com_count -> execute();
$result_com_count = $stmt_com_count->get_result();
if($result_com_count->num_rows>0)
    while($row_temp = $result_com_count->fetch_assoc())
        $approved = $row_temp['com_count'];
$current_appproval = intval(($approved / $com_count) * 100);

$str .= "
<div class='divider fw-bold mt-4'>
    <div class='divider-text'>
        Minimum of $minimum_approval% Approval needed 
    </div>
</div>
<div class='divider fw-bold'>
    <div class='divider-text'>
        Current Approval at $current_appproval% ( $approved Out of $com_count )
    </div>
</div>";
echo $str;

if(!$selections_match)
{
    echo "<p class='text-danger text-center small'> <strong>Request On Hold <br>Because Different Items being selected for purchase</strong></p>";
}
?>