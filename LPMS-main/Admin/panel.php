<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
?>
<script>
    set_title("LPMS | Admin Panel");
    sideactive("panel");
    function onlyNumberKey(event) {
        var regex = new RegExp("^[0-9-+-]");
        var key = String.fromCharCode(event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
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
            <h2>Admin Panel</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Panel</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>
    <?php include 'tabs.php';?>
    <!-- Create/Edit Project -->
    <div id="Manage_account_div">
        <?php include 'manage_account.php';?>
    </div>
    <!-- Edit Comparission sheet -->
    <div id="Edit_Comparission_sheet_div" class=" d-none">
        <?php include 'Edit_Comparission_sheet.php';?>
    </div>
    <div id="amend_limit_div" class=" d-none">
        <?php 
            include 'amend_limit.php';
        ?>
    </div>
    <div id="manage_div" class=" d-none">
        <?php 
            include 'manage.php';
        ?>
    </div>
    <div id="Manage_service_div" class=" d-none">
        <?php 
            include 'manage_service.php';
        ?>
    </div>
</div>
</div>
<?php include '../footer.php';?>