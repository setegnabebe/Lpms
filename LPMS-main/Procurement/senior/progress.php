<?php 
session_start();
if(isset($_SESSION['loc']))
{
    // $string_inc = 'head.php';
    // $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $string_inc = '../../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
    $stmt_proc_manager -> bind_param("s", $_SESSION['company']);
    $stmt_proc_manager -> execute();
    $result_proc_manager = $stmt_proc_manager -> get_result();
    $row_manager = $result_proc_manager -> fetch_assoc();
    $scale = "";
    if($row_manager["role"]=="manager" && $row_manager["department"]=='Procurement' && $row_manager["company"]=='Hagbes HQ.')
        $scale .= ($scale == "")?"(`scale` = 'procurement' OR `scale` = 'Owner')":" OR (`scale` = 'procurement' OR `scale` = 'Owner')";
    if(($row_manager["role"]=="manager" && $row_manager["department"]=='Procurement')||$row_manager['additional_role'] == 1)
        $scale .= ($scale == "")?"(`scale` = 'Branch' AND P.procurement_company = '".$row_manager['company']."')":" OR (`scale` = 'Branch' AND P.procurement_company = '".$row_manager['company']."')";
    if($row_manager["role"]=="Director" && ($row_manager["department"]=='GM' || in_array("All Departments",$row_manager["managing"])) && $row_manager["company"]!='Hagbes HQ.')// && in_array("All Departments",$row_manager["managing_department"])
        $scale .= ($scale == "")?"((`scale` = 'HO' OR `scale` = 'Owner') AND R.company = '".$row_manager['company']."')":" OR ((`scale` = 'HO' OR `scale` = 'Owner') AND R.company = '".$row_manager['company']."')";
    if(strpos($row_manager["type"],"Branch Committee") !== false)
        $scale .= ($scale == "")?"(`scale` = 'Branch' AND R.company = '".$row_manager['company']."')":" OR (`scale` = 'Branch' AND R.company = '".$row_manager['company']."')";
    
    if(strpos($row_manager["type"],"HO Committee") !== false)
        $scale .= ($scale == "")?"(`scale` = 'HO')":" OR (`scale` = 'HO')";
    
    if($row_manager["department"]=='Owner')
        $scale .= ($scale == "")?"(`scale` = 'Owner')":" OR (`scale` = 'Owner')";
    if($row_manager["department"]=='Owner')
        $scale .= ($scale == "")?"(customer = '".$row_manager['Username']."')":" OR (customer = '".$row_manager['Username']."')";
    else 
        $scale .= ($scale == "")?"(department = '".$row_manager['department']."' AND P.company = '".$row_manager['company']."')":" OR (department = '".$row_manager['department']."' AND P.company = '".$row_manager['company']."')";
    // if($scale == "") header("Location: index.php");
}
else
    header("Location: ../");
// include_once 'script.php';
// include "../connection/connect.php";
function divcreate($str)
{
    echo "
        <div class='container-fluid pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>POs waiting Approval</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
echo "<script>var special_pos = 'set';</script>";
?>
<script>
    set_title("LPMS | At Committee Approval");
    sideactive("view_committee");
    var element,db;
    function status_check(e)
    {
        var req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById('approval_progress_body').innerHTML = this.responseText;
        }
        req.open("GET", "../<?=$pos?>Committee/ajax_stat.php?c_id="+e.name);
        req.send();
    }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Purcahse Order Requests at Committee</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">View Purcahse Order Requestsat Committee</li>
        </ol>
    </div> 
    <?php include $pos.'../common/profile.php';?>
</div>
<form method="GET" action="allphp.php">
    <?php
        $str="";
        $edit = false;
        $spec_cond = ($row_manager['department'] == 'IT')?" or spec_dep='".$row_manager['department']."'":"";
        $sql_clus = "SELECT *,scale,C.type as c_type,R.request_type as request_type,C.id as id,R.company as company FROM `cluster` AS C Inner Join `purchase_order` AS P ON C.id=P.cluster_id  INNER JOIN requests AS R ON P.request_id = R.request_id where C.status='Generated' AND (($scale) $spec_cond) Group by cluster_id";
        $stmt_committee_fetch = $conn->prepare($sql_clus);
        $stmt_committee_fetch -> execute();
        $result_committee_fetch = $stmt_committee_fetch -> get_result();
        if($result_committee_fetch -> num_rows > 0)
        while($r_clus = $result_committee_fetch -> fetch_assoc())
        {
                $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($r_type,$num_req);
                $stmt2->fetch();
                $stmt2->close();
                
        if(strpos($row_manager["type"],"HO Committee") !== false || $row_manager["department"]=='Owner') 
            $company = "<li class='text-start'><span class='fw-bold'>Company: </span>".$r_clus['company']."</li>";
        else $company = "";
        $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>".$r_clus['c_type']." 
                <button type='button' title='cluster' class='btn btn-outline-secondary border-0 float-end' name='print_".$r_clus['id']."' onclick='print_page(this,\"special\")'>
                <i class='text-dark fas fa-print'></i>
                </button></h3>
                <ul>
                    <li class='text-start'><span class='fw-bold'>Items in Request : </span>".$num_req."</li>
                    <li class='text-start'><span class='fw-bold'>Scale : </span><span class='text-primary'>".$r_clus['scale']."</span></li>
                    <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($r_clus['price'], 2, ".", ",")."</li>
                    $company
                    <li class='mt-3' name='btns_po_comm'>
                    <span id='app_view_btn'><button type='button' name='".$r_clus['id']."' id='comp_sheet_view_btn' title='view_comparision' onclick='compsheet_loader(this,\"".$r_clus['id']."\")' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'> View </button></span>
                        <span id='app_status_btn'>
                            <button type='button' name='".$r_clus['id']."' id='comp_sheet_status_btn' onclick='status_check(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#approval_progress'>
                                Status
                            </button>
                        </span>
                    <!-- <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['id']."' >Chat <i class='text-primary fa fa-comment'></i></button> -->
                    </li>";
                $str.= "</ul>
                </div>
            </div>
            ";
        }
        
    if($str=='')
        echo "<div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No purchase Orders Waiting Approval</h3>
                    </div>
                </div>";
    else
        divcreate($str);
    ?>
    </form>
</div>
</div>

<?php include '../../footer.php';?>