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
                <h6 class='text-white'>Signed Cheques</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Sign Cheque");
    sideactive("cheque_history");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Cheque Signed History</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Cheque Signed History</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql_signed_cheques = "SELECT * FROM `cheque_info` where signatory LIKE ?";
        $like_user = "%".$_SESSION['username']."%";
        $stmt_signed_cheques = $conn -> prepare($sql_signed_cheques);
        $stmt_signed_cheques -> bind_param("s", $like_user);
        $stmt_signed_cheques -> execute();
        $result_signed_cheques = $stmt_signed_cheques -> get_result();
        if($result_signed_cheques -> num_rows>0)
        while($r_clus = $result_signed_cheques -> fetch_assoc())
        {
            $po_id = explode(":-:",$r_clus['purchase_order_ids'])[0];
            $stmt_po -> bind_param("i", $po_id);
            $stmt_po -> execute();
            $result_po = $stmt_po -> get_result();
            if($result_po->num_rows > 0)
            {
                $row_req = $result_po->fetch_assoc();
                $stmt_request -> bind_param("i", $row_req['request_id']);
                $stmt_request -> execute();
                $result_request = $stmt_request -> get_result();
                $row_req = $result_request -> fetch_assoc();
            }

            if(!is_null($r_clus['signatory']) && strpos($r_clus['signatory'],$_SESSION['username'])!==FALSE)
                $signed = true;
            else
                $signed = false;
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>".(($r_clus['void'])?"<span class='badge bg-danger text-sm float-start'>Void</span>":"").
                $r_clus['providing_company']."
                <button type='button' title='cluster' class='btn btn-outline-secondary border-0 float-end' name='print_".$r_clus['cluster_id']."' onclick='print_page(this)'>
                <i class='text-dark fas fa-print'></i>
                </button>
                </h3>
                <ul>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Department : </span>".(isset($row_req['department'])?$row_req['department']:"<span class='text-danger'>Missing Value</span>")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Requesting Company : </span>".(isset($row_req['company'])?$row_req['company']:"<span class='text-danger'>Missing Value</span>")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Finance Company : </span>".(isset($row_req['finance_company'])?$row_req['finance_company']:"<span class='text-danger'>Missing Value</span>")."</li>
                    <li class='text-start text-primary'><span class='fw-bold text-dark'>Total Price : </span>".number_format($r_clus['cheque_amount'], 2, ".", ",")."</li>
                    <button type='button' name='".$r_clus['cluster_id']."' onclick='add_btn(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                    <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                    <button class='mx-auto btn btn-outline-success btn-sm' type='button' onclick = 'pay_req(this,\"view\")' value='CPV-".$r_clus['cpv_no']."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>View CPV</button>";
                    $str.= "
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