
<?php 
if(strpos($_SESSION["a_type"],"HOCommittee") === false && $_SESSION["department"] != 'Owner')
{
    [$requests_dep,$constraints_dep] = count_dep_req();
    $all =false;
}
else
    $all =true;
$constraints = "";
$piechart_data = "";
$requests = "";
$sql2 = "SELECT * FROM `comp`";
$stmt_companies = $conn_fleet -> prepare($sql2);
$stmt_companies -> execute();
$result_companies = $stmt_companies -> get_result();
if($result_companies -> num_rows>0)
{
    while($row = $result_companies -> fetch_assoc())
    {
        $once = true;
        $company_req[$row['Name']] = 0;
        $sql = "SELECT count(*) as `c_r` FROM requests where company = ?";
        $stmt_count_requests = $conn -> prepare($sql);
        $stmt_count_requests -> bind_param("s", $row['Name']);
        $stmt_count_requests -> execute();
        $result_count_requests = $stmt_count_requests -> get_result();
        while($row2 = $result_count_requests -> fetch_assoc())
        {
            if($row2['c_r']>0)
            {
                if($once)
                    $constraints .= ($constraints == "")?"'".$row['Name']."'":",'".$row['Name']."'";
                $company_req[$row['Name']] = $company_req[$row['Name']]+$row2['c_r'];
                $once = false;
            }
        }
        if($company_req[$row['Name']] != 0)
        {
            $piechart_data .= "{
                value: ".$company_req[$row['Name']].",
                name: '".$row['Name']."'
            },";
            $requests .= ($requests == "")?$company_req[$row['Name']]:",".$company_req[$row['Name']];
            if($all)
                [$requests_dep[$row['Name']],$constraints_dep[$row['Name']]] = count_dep_req($row['Name']);
            else
                [$requests_dep,$constraints_dep] = count_dep_req();
        }
    }
}
$piechart_data = rtrim($piechart_data,',');
?>
<?php
?>