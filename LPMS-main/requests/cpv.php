<?php 

session_start();
if(isset($_SESSION['loc']) && strpos($_SESSION["a_type"],"ChequeSignatory") !== false)
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
                <h6 class='text-white'>Previous CPVs</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | CPVs");
    sideactive("CPV");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View CPVs</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">View CPVs</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    
    <?php
        $str="";
        $like_username = "%$_SESSION[username]%";
        // $sql_clus = "SELECT *,C.id As cid FROM `cluster` AS C inner Join cheque_info AS CPV ON C.id = CPV.cluster_id Group by CPV.cluster_id,purchase_order_ids,providing_company";
        $sql_clus = "SELECT *,C.id As cid FROM `cluster` AS C inner Join cheque_info AS CPV ON C.id = CPV.cluster_id AND signatory LIKE ? AND void != 1 ORDER BY cpv_no DESC";
        $stmt_fetch_signed_cheques = $conn -> prepare($sql_clus);
        $stmt_fetch_signed_cheques -> bind_param("s", $like_username);
        $stmt_fetch_signed_cheques -> execute();
        $result_fetch_signed_cheques = $stmt_fetch_signed_cheques -> get_result();
        if($result_fetch_signed_cheques -> num_rows > 0)
        while($r_clus = $result_fetch_signed_cheques -> fetch_assoc())
        {
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>
                    Company - ".$r_clus['providing_company']."
                </h3>
                <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start'><span class='fw-bold'>CPV No : </span><span class='text-primary'>".$r_clus['cpv_no']."</span></li>
                        <li class='text-start'><span class='fw-bold'>For Company : </span><span class='text-primary'>".$r_clus['company']."</span></li>
                        <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($r_clus['cheque_amount'], 2, ".", ",")."</li>
                        <button type='button' name='".$r_clus['cid']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                        <li class='mt-3'>
                            <button class='mx-auto btn btn-outline-success btn-sm mb-3' type='button' onclick = 'pay_req(this,\"view\")' value='".$r_clus['cid']."::-::".$r_clus['purchase_order_ids']."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>
                                View CPV
                            </button>
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
                    <h3 class='mt-4'>There are no CPVs</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>
    
</div>
</div>
<?php include '../footer.php';?>