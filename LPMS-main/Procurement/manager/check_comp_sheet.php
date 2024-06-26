<?php 

session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>POs To Be Sent to Committee</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Check Comparision Sheet");
    sideactive("check_comp_sheet");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Check Comparision Sheet And Proforma</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Check Comparision Sheet And Proforma</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
    <?php
        $str="";
        $sql_clus = "SELECT * FROM `cluster` where (`status`='Pending' or `status`='updated')  AND `procurement_company` = ?";
        $stmt_pending_clusters = $conn->prepare($sql_clus);
        $stmt_pending_clusters -> bind_param("s", $_SESSION['company']);
        $stmt_pending_clusters -> execute();
        $result_pending_clusters = $stmt_pending_clusters -> get_result();
        if($result_pending_clusters -> num_rows>0)
        while($r_clus = $result_pending_clusters -> fetch_assoc())
        {
            $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($r_type,$num_req);
            $stmt2->fetch();
            $stmt2->close();
            $printpage = "
                <form method='GET' action='../../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['id'].":|:cluster'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>".$r_clus['type']."
                $printpage
                </h3>
                <form method='GET' action='allphp.php'>
                <ul>
                    <li class='text-start'><span class='fw-bold'>Items in Request : </span>".$num_req."</li>
                    <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($r_clus['price'], 2, ".", ",")."</li>
                    <span id='app_status_btn_btn'> <button type='button' name='".$r_clus['id']."' id='comp_sheet_view_btn_app' onclick='add_btn(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                    <i class='text-white fas fa-clipboard-list fa-fw'></i></button></span>
                   
                    <li class='mt-3' id='app_status_btns'>
                    <span id='app_status_btn_btn2'><button type='button' title='initial' name='".$r_clus['id']."' id='proc'  onclick='add_btn(this)' class='mb-2 btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>
                    View and Send
                    </button></span>
                    <span id='app_status_btn_btn3'><button type='button' onclick='prompt_confirmation(this)' class='btn btn-outline-secondary btn-sm mb-2' name='redo_compSheet' value = '".$r_clus['id']."'>Revert for Edit</button></span>
                    </li>
                    
                </ul>
                </form>
                </div>
            </div>
                ";
        }
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Payment</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>
</div>
</div>
<?php include '../../footer.php';?>
<script>
function add_btn(e){
    if(e.id=='comp_sheet_view_btn_app'){
 document.getElementById('comp_sheet_footer_btn').innerHTML="<form method='GET' action='allphp.php'>"+document.getElementById('app_status_btns').innerHTML+"</form>";
 compsheet_loader(e)
    }else if(e.id=='proc'){
 document.getElementById('comp_sheet_footer_btn').innerHTML="<form method='GET' action='allphp.php'>"+document.getElementById('app_status_btn_btn3').innerHTML+"</form>";
 compsheet_loader(e,e.name) 
    }
}
    </script>