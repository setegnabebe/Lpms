<?php 
session_start();
if(isset($_SESSION['loc']))
{
    // $string_inc = 'head.php';
    // $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
    $scale = "";
    if(($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement' && $_SESSION["company"]=='Hagbes HQ.')
        $scale .= ($scale == "")?"(`scale` = 'procurement' OR `scale` = 'Owner')":" OR (`scale` = 'procurement' OR `scale` = 'Owner')";
    if((($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement')||$_SESSION['additional_role'] == 1)
        $scale .= ($scale == "")?"((`scale` = 'Branch' OR `scale` = 'HO') AND P.procurement_company = '".$_SESSION['company']."')":" OR ((`scale` = 'Branch' OR `scale` = 'HO') AND P.procurement_company = '".$_SESSION['company']."')";
    if(($_SESSION["role"]=="Director" || $_SESSION["role"]=="GM") && ($_SESSION["department"]=='GM' || in_array("All Departments",$_SESSION["managing_department"])) && $_SESSION["company"]!='Hagbes HQ.')// && in_array("All Departments",$_SESSION["managing_department"])
        $scale .= ($scale == "")?"((`scale` = 'HO' OR `scale` = 'Owner') AND R.company = '".$_SESSION['company']."')":" OR ((`scale` = 'HO' OR `scale` = 'Owner') AND R.company = '".$_SESSION['company']."')";
    if(strpos($_SESSION["a_type"],"BranchCommittee") !== false)
        $scale .= ($scale == "")?"(`scale` = 'Branch' AND R.company = '".$_SESSION['company']."')":" OR (`scale` = 'Branch' AND R.company = '".$_SESSION['company']."')";
    
    if(strpos($_SESSION["a_type"],"HOCommittee") !== false)
        $scale .= ($scale == "")?"(`scale` = 'HO')":" OR (`scale` = 'HO')";
    
    if($_SESSION["role"]=='Owner')
        $scale .= ($scale == "")?"(`scale` = 'Owner')":" OR (`scale` = 'Owner')";
    if($_SESSION["role"]=='Owner')
        $scale .= ($scale == "")?"(customer = '".$_SESSION['username']."')":" OR (customer = '".$_SESSION['username']."')";
    else 
        $scale .= ($scale == "")?"(department = '".$_SESSION['department']."' AND P.company = '".$_SESSION['company']."')":" OR (department = '".$_SESSION['department']."' AND P.company = '".$_SESSION['company']."')";
    // if($scale == "") header("Location: index.php");
}
else
    header("Location: ../");
// include_once 'script.php';
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
?>
<script>
    set_title("LPMS | Committee Approval");
    sideactive("approval_committee");
    var element,db;
    function status_check(e)
    {
        var req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById('approval_progress_body').innerHTML = this.responseText;
        }
        req.open("GET", "ajax_stat.php?c_id="+e.name);
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
        <h2>Approve Purcahse Order Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Purcahse Order Requests</li>
        </ol>
    </div> 
    <?php include '../common/profile.php';?>
</div>
<form method="GET" action="allphp.php">
    <?php
        $str="";
        // echo $scale;
        $edit = false;
        $spec_cond = ($_SESSION['department'] == 'IT')?" or spec_dep='".$_SESSION['department']."'":"";
        $sql_clus = "SELECT *,scale,C.type as c_type,R.request_type as request_type,C.id as id,R.company as company FROM `cluster` AS C Inner Join `purchase_order` AS P ON C.id=P.cluster_id  INNER JOIN requests AS R ON P.request_id = R.request_id where (C.status='Generated') AND (($scale) $spec_cond) Group by cluster_id"; //  || C.status='updated'
        $stmt_cluster_committee = $conn->prepare($sql_clus);
        $stmt_cluster_committee->execute();
        $result_cluster_committee = $stmt_cluster_committee->get_result();
        // echo $sql_clus."<br>";
        // echo $conn->error."<br>";
        if($result_cluster_committee->num_rows>0)
        while($r_clus = $result_cluster_committee->fetch_assoc())
        {
                unset($status);
                $sql_committee_approval = "SELECT * FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
                $stmt_cluster_committee_specific = $conn->prepare($sql_committee_approval);
                $stmt_cluster_committee_specific -> bind_param("si", $_SESSION['username'], $r_clus['id']);
                $stmt_cluster_committee_specific -> execute();
                $result_cluster_committee_specific = $stmt_cluster_committee_specific->get_result();
                if($result_cluster_committee_specific->num_rows>0)
                    while($row_temp = $result_cluster_committee_specific->fetch_assoc())
                    {
                        $status = $row_temp["status"];
                    }
                $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($r_type,$num_req);
                $stmt2->fetch();
                $stmt2->close();
                
        if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=='Owner') 
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
                            <span id='app_view_btn_".$r_clus['id']."'>  
                            <button type='button' name='".$r_clus['id']."' id='comp_sheet_view_btn'  onclick='add_btn(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>
                                View
                            </button>
                            </span>
                            <span id='app_status_btn_".$r_clus['id']."'>
                                <button type='button' name='".$r_clus['id']."' id='comp_sheet_status_btn' onclick='add_btn(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#approval_progress'>
                                    Status
                                </button>
                            </span>
                            <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                            </li>";
                            if(isset($status) && ($status!='Reactivated' || $status!="updated"))
                                $str.= "<li class='mt-3 fw-bold'>
                                            You Have <i class='text-primary'>$status</i> It
                                        </li>";
                            else
                                $str.= "
                                    <li class='mt-3 fw-bold'>
                                        <i class='text-primary'>Pending</i>
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

<?php include '../footer.php';?>
<script>
function add_btn(e){
    if(e.id=='comp_sheet_view_btn'){
 document.getElementById('comp_sheet_footer_btn').innerHTML=document.getElementById('app_status_btn_'+e.name).innerHTML;
 compsheet_loader(e,e.name)
    }else if(e.id=='comp_sheet_status_btn'){
 document.getElementById('app_progress_footer_btn').innerHTML=document.getElementById('app_view_btn_'+e.name).innerHTML;
 status_check(e)  
    }
}
</script>
