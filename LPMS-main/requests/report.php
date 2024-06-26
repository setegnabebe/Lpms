<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
include "report_data.php"; 
?>
<script> 
    set_title("LPMS | Report"); 
    sideactive("report");
</script>
<div id="main">
    <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Performance Report</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Performance Report</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<?php
    $str ='';
    
    $sql = "SELECT * FROM requests WHERE request_id IS NOT NULL";
    if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner') $sql .="";
    else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') $sql .=" AND company = '". $_SESSION['company']."'";
    else if($_SESSION["a_type"] == 'manager' && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') $sql .=" AND  department = '".$_SESSION['department']."' AND company = '". $_SESSION['company']."'";
    else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') $sql .=" AND `customer`='".$_SESSION["username"]."' AND company = '". $_SESSION['company']."'";
    $sql.=" ORDER BY request_id ASC";
    $stmt_requests_complex = $conn -> prepare($sql);
    // $stmt_requests_complex -> bind_param("s", $row['Name']);
    $stmt_requests_complex -> execute();
    $result_requests_complex = $stmt_requests_complex -> get_result();
    if($result_requests_complex -> num_rows>0)
        while($row = $result_requests_complex -> fetch_assoc())
        {
            $type = $row['request_type'];
            $na_t=str_replace(" ","",$type);
            if($type=="Consumer Goods"){
                if($row['request_for'] == 0)
                {
                    $stmt_project->bind_param("i", $row['request_for']);
                    $stmt_project->execute();
                    $result3 = $stmt_project->get_result();
                    $res=($result3->num_rows>0)?true:false;
                }
                else
                {
                    $idd = explode("|",$row['request_for'])[0];
                    $stmt_project_pms->bind_param("i", $idd);
                    $stmt_project_pms->execute();
                    $result3 = $stmt_project_pms->get_result();
                    $res=($result3->num_rows>0)?true:false;
                }
            }
            else if($type =="Spare and Lubricant"){
                $stmt_description->bind_param("i", $row['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;
            }
            else if($type =="Tyre and Battery")
            {
                $name=$row['request_for'];
                $res=false;
            }
            else 
            {
                $res=false;
                $name=" - ";
            }
            if($res)
                while($row3 = $result3->fetch_assoc())
                {
                    if($type=="Consumer Goods")
                    {
                        $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                    }
                    else if($type =="Spare and Lubricant")
                        $name=$row3['description'];
                }
                if($type =="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
            $stmt_po_by_request -> bind_param("i", $row['request_id']);
            $stmt_po_by_request -> execute();
            $result_po_by_request = $stmt_po_by_request -> get_result();
            if($result_po_by_request -> num_rows>0)
                while($row2 = $result_po_by_request -> fetch_assoc())
                {
                    $officer = $row2['purchase_officer'];
                    $sql3 ="SELECT * FROM `price_information` WHERE cluster_id = ? AND purchase_order_id = ?";
                    $stmt_price_information = $conn -> prepare($sql3);
                    $stmt_price_information -> bind_param("ii", $row2['cluster_id'], $row2['purchase_order_id']);
                    $stmt_price_information -> execute();
                    $result_price_information = $stmt_price_information -> get_result();
                    if($result_price_information -> num_rows>0)
                        while($row3 = $result_price_information -> fetch_assoc())
                        {
                            $price = $row3['after_vat'];
                        }
                    else {
                        $price = " - ";
                    }
                }
            else {
                $officer = " - ";
                $price = " - ";
            }
            $sql3 ="SELECT * FROM `report` WHERE request_id = ?";
            $stmt_report = $conn -> prepare($sql3);
            $stmt_report -> bind_param("i", $row['request_id']);
            $stmt_report -> execute();
            $result_report = $stmt_report -> get_result();
            if($result_report -> num_rows > 0)
                while($row3 = $result_report -> fetch_assoc())
                {
                    $date_r = ($row['date_requested']!='')?date("d-M-Y", strtotime($row['date_requested'])):" - ";
                    $date_ma = ($row3['manager_approval_date']!='')?date("d-M-Y", strtotime($row3['manager_approval_date'])):" - ";
                    $date_comp = ($row3['compsheet_generated_date']!='')?date("d-M-Y", strtotime($row3['compsheet_generated_date'])):" - ";
                    $date_ca = ($row3['committee_approval_date']!='')?date("d-M-Y", strtotime($row3['committee_approval_date'])):" - ";
                    $date_cheq = ($row3['cheque_signed_date']!='')?date("d-M-Y", strtotime($row3['cheque_signed_date'])):" - ";
                    $date_hand = ($row3['handover_comfirmed']!='')?date("d-M-Y", strtotime($row3['handover_comfirmed'])):" - ";
                    $date_final = ($row3['final_recieved_date']!='')?date("d-M-Y", strtotime($row3['final_recieved_date'])):" - ";
                    $str.="
                    <tr> 
                    <th>$type</th>
                    <td><button type='button' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                    ".$row['item']."</button></td>
                    <td>".$row['department']."</td>
                    <td>".$name."</td>
                    <td title='".$row['unit']."'>".$row['requested_quantity']."</td>
                    <td>".$price."</td>
                    <td>".$officer."</td>
                    <td title='".$row['date_requested']."'>".$date_r."</td>
                    <td title='".$row3['manager_approval_date']."'>".$date_ma."</td>
                    <td title='".$row3['compsheet_generated_date']."'>".$date_comp."</td>
                    <td title='".$row3['committee_approval_date']."'>".$date_ca."</td>
                    <td title='".$row3['cheque_signed_date']."'>".$date_cheq."</td>
                    <td title='".$row3['handover_comfirmed']."'>".$date_hand."</td>
                    <td title='".$row3['final_recieved_date']."'>".$date_final."</td>
                    </tr>
                    ";
            }
        }
?>
<?php 
    include "report_type_selection.php";
    if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner')
    {
?>
<div id='report_pie_div'>
    <?php //include "report_pie_select.php" ?>
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pie Chart</h5>
                <div id="requests_chart" style="min-height: 400px;" class="echart"></div>
            </div>
        </div>
    </div>
</div>
<?php echo create_piechart($piechart_data,"requests_chart"); 
$tbl_on = "class='d-none'";
    }
else
    $tbl_on = "";
?>
<div style='overflow:scroll' id='report_table_div' <?php echo $tbl_on?>>
<table class="table table-striped" id="table1">
    <thead class='table-primary'>
        <tr>
            <th>Request Type</th>
            <th>Item</th>
            <th>Department</th>
            <th>Job/Project/Plate_No</th>
            <th>Amount</th>
            <th>Price(Birr)</th>
            <th>Purchase Officer</th>
            <th>Date Requested</th>
            <th>Date Approved By GM</th>
            <th>Date Comparision sheet Completed</th>
            <th>Date Approved By Committee</th>
            <th>Date Chaque Recieved</th>
            <th>Date Item Delievered To Store</th>
            <th>Date Taken From Request To GRV</th>
        </tr>
    </thead>
    <tbody>
        <?php echo $str ?>
    </tbody>
</table>
</div>
<div id='report_bar_div' class='d-none'>
    <?php include "report_graph_select.php" ?>
    <div class='container' id='Company_view'>
        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Request at each Company</h4>
                    </div>
                    <div class="card-body">
                        <div id="comp_bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='container d-none' id='Department_view'>
        <div class="row mt-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Request at each Department</h4>
                    </div>
                    <div class="card-body">
                        <div id="dep_bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php include "../footer.php"; ?>
<div>
<!-- Insering Graphs -->
    <?php echo createGraph($requests,$constraints,"comp_bar");?>
    <!-- <script>eval(eval_variable)</script> -->
    <?php 
        if($all)
        {
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
                        if(isset($_SESSION["Graph_company"])) 
                        {
                            if($row['Name'] == $_SESSION["Graph_company"]) 
                            {
                                echo "<div id='graph_dep'>".createGraph($requests_dep[$row['Name']],$constraints_dep[$row['Name']],"dep_bar")."</div>";
                                break;
                            }
                        }
                        else
                        {
                            echo "<div id='graph_dep'>".createGraph($requests_dep[$row['Name']],$constraints_dep[$row['Name']],"dep_bar")."</div>";
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            echo createGraph($requests_dep,$constraints_dep,"dep_bar");
        }
    ?>
<!-- ////////////////////////// -->
</div>
<?php 
    if(isset($_SESSION["Graph_company"])) 
    {
        echo "
        <script>
        const myTimeout = setTimeout(xx, 1);
            function xx()
            {
                document.getElementById('report_bar_toggle').click();
                document.getElementById('Department_toggle').click();
            }
        </script>
        ";
        unset($_SESSION["Graph_company"]);
    }
?>