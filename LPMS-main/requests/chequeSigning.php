<?php 

session_start();
if(isset($_SESSION['loc']) && strpos($_SESSION["a_type"],"ChequeSignatory") !== false && isset($_SESSION['company_signatory']) && $_SESSION['company_signatory'])
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
                <h6 class='text-white'>POs waiting Cheques to be Signed</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Cheque History");
    sideactive("payment");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Process Payment for Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Process Payment for Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql_cond = ($_SESSION['company'] != "Hagbes HQ.")?" AND cheque_company = ?":"";
        $sql_cheques = "SELECT * FROM `cheque_info` where (`status`='pending' Or `status` = 'pending payment processed') $sql_cond AND void != 1";
        if($_SESSION['company'] != "Hagbes HQ.") $sql_cheques .= " Order By cpv_no DESC";
        $stmt_cheques = $conn -> prepare($sql_cheques);
        if($_SESSION['company'] != "Hagbes HQ.")
        $stmt_cheques -> bind_param("s", $_SESSION['company']);
        $stmt_cheques -> execute();
        $result_cheques = $stmt_cheques -> get_result();
        if($result_cheques -> num_rows > 0)
        while($r_clus = $result_cheques -> fetch_assoc())
        {
            $stmt_po_cluster -> bind_param("i", $r_clus['cluster_id']);
            $stmt_po_cluster -> execute();
            $result_po_cluster = $stmt_po_cluster -> get_result();
            $row_req = $result_po_cluster -> fetch_assoc();

            $stmt_request -> bind_param("i", $row_req['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            $row_req = $result_request->fetch_assoc();

            if(!is_null($r_clus['signatory']) && strpos($r_clus['signatory'],$_SESSION['username'])!==FALSE)
                $signed = true;
            else
                $signed = false;
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>".$r_clus['providing_company']."
                <button type='button' title='cluster' class='btn btn-outline-secondary border-0 float-end' name='print_".$r_clus['cluster_id']."' onclick='print_page(this)'>
                <i class='text-dark fas fa-print'></i>
                </button>
                </h3>
                <ul>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Department : </span>".(isset($row_req['department'])?$row_req['department']:"")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Requesting Company : </span>".(isset($row_req['company'])?$row_req['company']:"")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Finance Company : </span>".(isset($row_req['finance_company'])?$row_req['finance_company']:"")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Total Price : </span>".number_format($r_clus['cheque_amount'], 2, ".", ",")."</li>
                    <button type='button' name='".$r_clus['cluster_id']."' onclick='add_btn(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                    <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                    <button class='mx-auto btn btn-outline-success btn-sm' type='button' onclick = 'pay_req(this,\"view\")' value='CPV-".$r_clus['cpv_no']."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>View CPV</button>
                    <li class='mt-3' id='app_cheque_btn".$r_clus['cluster_id']."'>";
                    $str.= ($signed)?"<i class='fw-bold text-primary text-center'>Sign Cheque</i>":"<button class='btn btn-outline-success' type='button' onclick = 'prompt_confirmation(this)' name='process_cheque' value='".$r_clus['cluster_id']."::-::ABC::-::".$r_clus['cpv_no']."'>Sign Cheque</button>
                    <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['cluster_id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                    ";
                    $str.= "
                    </li>
                </ul>
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
    </form>
</div>
</div>
<?php include '../footer.php';?>
<script>
function add_btn(e){
 document.getElementById('comp_sheet_footer_btn').innerHTML=document.getElementById('app_cheque_btn'+e.name).innerHTML;
 compsheet_loader(e)
    }
</script>