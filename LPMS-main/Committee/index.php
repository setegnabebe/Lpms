<?php 
session_start();
if(isset($_SESSION['loc']))
{
    // $string_inc = 'head.php';
    // $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");?>
<?php include '../requests/home.php';?>
<?php include '../footer.php';?>