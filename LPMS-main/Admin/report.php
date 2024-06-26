<?php 
session_start();
if(isset($_SESSION['loc']) && ($_SESSION["role"]=="Admin" || $_SESSION["role"]=="Owner" || ($_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'Director') || ($_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'GM') || ($_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'Manager' && $_SESSION["department"] == 'Procurement')))
{
    $string_inc = '../'.$_SESSION["loc"].'head.php';
    include $string_inc;
}
else
    header("Location: ../");

 $cu= $_GET['id']; 
?>
<script>
    set_title("LPMS | Report");
    sideactive("Report");
</script>
<div id='main'>
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
            <header>
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            <h2>Reports</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    
    </div>
    <?php if(isset($_GET['Graph_company']) AND $cu=="purchase-office"){
        include("../bargraph.php");
    }else{
    if($cu=="purchase-office"){
    include("../purchase-office-performance.php");
    }else if($cu=="purchase-officer"){
    include("../purchase-officer-performance.php");
    }else if($cu=="senior-purchase-officer"){
    include("../senior-PO.php");
    }else if($cu=="requesting-department-performance"){
    include("../Requesting-department-performance.php");
    }else if($cu=="payment-processing"){
   include("../Payment-Processing.php");
    }}
    ?>
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip({
        placement: 'bottom'
    });
});
</script>
</div>
<?php include '../footer.php';?>