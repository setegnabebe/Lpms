<?php 
    // session_start();
// $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
if(strpos($_SESSION['loc'],"Committee")===false) 
    include  "../".$_SESSION["loc"]."sidenav.php";
else
{
?>
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header p-1 ps-2 pt-3">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="../Committee/index.php">
                        <image src="<?php echo $pos.'../img/'.$_SESSION['logo']?>" style="min-height: 40px;min-width: 40px">
                        <!-- <img src="../assets/images/logo/Hagbeslogo.jpg" alt="Logo" srcset=""> -->
                         LPMS</a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div><hr style="height: 2px;" class="text-primary">
        <div class="sidebar-menu">
            <ul class="menu" id='side_list'>
            <div class="text-center my-auto"><span class='mx-auto spinner-border text-primary align-middle'></span></div>
                <?php include_once $pos."../common/sidenav.php";?>
            </ul>
        </div>
        <!-- <button class="sidebar-toggler btn x"><i data-feather="x"></i></button> -->
    </div>
</div>
<?php
}
?>