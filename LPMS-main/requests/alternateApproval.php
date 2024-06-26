<?php 

session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
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
    set_title("LPMS | Approval");
    sideactive("approval_alt");
    var element,db;
    function reason(e,scale)
    {
        document.getElementById("form").setAttribute("action","../Committee/allphp.php?current_scale="+scale);
        document.getElementById('stat_btn').name=e.name;
        if(e.innerHTML.includes("Reject"))
            document.getElementById('stat_btn').classList.replace('btn-outline-success','btn-outline-danger')
        else
            document.getElementById('stat_btn').classList.replace('btn-outline-danger','btn-outline-success')
    }
    function status_check(e,sc)
    {
        var req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById('approval_progress_body').innerHTML = this.responseText;
        }
        req.open("GET", "../Committee/ajax_stat.php?c_id="+e.name+"&scale="+sc);
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
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Purcahse Order Requests</li>
        </ol>
    </div> 
    <?php include '../common/profile.php';?>
</div>
<!-- <form method="GET" action="" id="form"> -->
    <?php
        $str="";
        $edit = false;
        $sql_clus = "SELECT * FROM `cluster` where `status`='Generated'";
        $stmt_cluster_generated = $conn->prepare($sql);
        $stmt_cluster_generated -> execute();
        $result_cluster_generated = $stmt_cluster_generated -> get_result();
        if($result_cluster_generated -> num_rows > 0)
        while($r_clus = $result_cluster_generated -> fetch_assoc())
        {
            $sql_request_in_cluster = "SELECT DISTINCT `cluster_id`,scale,R.request_type as request_type FROM `purchase_order` AS P INNER JOIN requests AS R ON P.request_id = R.request_id WHERE `cluster_id` = ? AND department = ?";
            $stmt_request_in_cluster = $conn -> prepare($sql_request_in_cluster);
            $stmt_request_in_cluster -> bind_param("is", $r_clus['id'], $_SESSION['department']);
            $stmt_request_in_cluster -> execute();
            $result_request_in_cluster = $stmt_request_in_cluster -> get_result();
            if($result_request_in_cluster -> num_rows>0)
            while($r_po = $result_request_in_cluster -> fetch_assoc())
            {
                $type = $r_po['request_type'];
                if(isset($r_po['scale']))
                    $scale = $r_po['scale'];
                unset($status);
                $sql_approvals = "SELECT * FROM `committee_approval` Where `committee_member` = ? AND `cluster_id` = ?";
                $stmt_approvals = $conn -> prepare($sql_approvals);
                $stmt_approvals -> bind_param("si", $_SESSION['username'], $r_clus['id']);
                $stmt_approvals -> execute();
                $result_approvals = $stmt_approvals -> get_result();
                if($result_approvals -> num_rows>0)
                    while($row_temp = $result_approvals -> fetch_assoc())
                    {
                        $status = $row_temp["status"];
                    }
                $stmt2 = $conn->prepare("SELECT `request_type`, count(*),`scale` AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($r_type,$num_req,$scale);
                $stmt2->fetch();
                $stmt2->close();
                
        // if(strpos($_SESSION["a_type"],"HOCommittee") !== false) 
            $company = "<li class='text-start'><span class='fw-bold'>Company: </span>".$r_clus['company']."</li>";
        // else $company = "";
                $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3>".$r_clus['type']."
                        <button type='button' title='cluster' class='btn btn-outline-secondary border-0 float-end' name='print_".$r_clus['id']."' onclick='print_page(this)'>
                        <i class='text-dark fas fa-print'></i>
                        </button></h3>
                        <ul>
                        <li class='text-start'><span class='fw-bold'>Items in Request : </span>".$num_req."</li>
                        <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($r_clus['price'], 2, ".", ",")."</li>
                        $company
                        <li class='mt-3'>
                            <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this,this.name,\"\",\"$scale\")' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>
                            View
                            </button>
                            <button type='button' name='".$r_clus['id']."' onclick='status_check(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#approval_progress'>Status
                            </button>
                        </li>";
                        if(isset($status) && $status!='Reactivated')
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
    <!-- </form> -->
<div class="modal fade" id="reason">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="../Committee/allphp.php">
                <div class="modal-header">
                    <h3 id='top_text'>Remark <span class='small text-secondary'>(optional)</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reason_body">
                    <!-- Company And Items Form -->
                    <textarea class='w-100' rows='2' name='reason'></textarea>
                    <button class='form-control btn btn-outline-success mt-3' id='stat_btn'>Proceed</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<div class="modal fade" id="approval_progress">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="GET" action="../Committee/allphp.php">
                <!-- <div class="modal-header">
                    <h3 id='top_text'>Status</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div> -->
                <div class="modal-body" id="approval_progress_body">
                    
                </div>
                <div class="modal-footer border-0">
                        <div id='app_progress_footer_btn'>
                                
                        </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </form> 
        </div>
    </div>
</div>
</div>
</div>

<?php include '../footer.php';?>