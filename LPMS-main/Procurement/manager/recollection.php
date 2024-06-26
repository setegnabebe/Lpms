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
                <h6 class='text-white'>Requests That weren't collected</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Failed Collection");
    sideactive("Failed_Collection");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Recollection of Items</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Recollection of Items</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql = "SELECT * FROM `purchase_order` where status='Recollection Failed' AND `procurement_company` = ?";
        $stmt_for_recollection = $conn->prepare($sql);
        $stmt_for_recollection -> bind_param("s", $_SESSION['company']);
        $stmt_for_recollection -> execute();
        $result_for_recollection = $stmt_for_recollection -> get_result();
        if($result_for_recollection -> num_rows>0)
        while($row = $result_for_recollection -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows > 0)
            while($row2 = $result_request -> fetch_assoc())
            { 
            // $num_req = $result->num_rows;
                $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3 class='text-capitalize'>".$row2['item']."</h3>
                        <ul>
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                            <li class='text-end'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>";
                            $str.="
                            <li>
                            <div class='input-group'>
                                <select class='form-select form-select-sm' name='".$na_t."_".$row['purchase_order_id']."'>
                                    <option value=''>--Select Stage--</option>";
                                    $sql = "SELECT * FROM `request_stages`";
                                    $stmt_request_stages = $conn->prepare($sql);
                                    $stmt_request_stages -> execute();
                                    $result_request_stages = $stmt_request_stages -> get_result();
                                    if($result_request_stages -> num_rows > 0)
                                        while($rr = $result_request_stages -> fetch_assoc())
                                        {
                                            $str.= "<option value='$rr[stage]'>$rr[stage]</option>";
                                        }
                        $str.=" </select>
                                <button type='button' onclick='prompt_confirmation(this)' name='goto' value='$row[purchase_order_id]' class='btn btn-sm btn-outline-primary alert-primary'>
                                    Go back to
                                </button>
                            </div></li>
                            <div class='divider fw-bold'>
                                <div class='divider-text'>
                                    Or
                                </div>
                            </div>
                            <div class='mb-2'>
                                <button class='btn btn-danger btn-sm mx-auto' type='button' onclick='prompt_confirmation(this)' name='close_request' value='$row[purchase_order_id]'>Close Request</button>
                            </div>";
                            $str.= "
                        </ul>
                        </div>
                    </div>
                ";
            }
        }
        if($str !='')
            divcreate($str);
        else
            echo "<div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests to be Collected</h3>
                        </div>
                    </div>";
    ?>
</form>
    
</div>
<?php include '../../footer.php';?>
