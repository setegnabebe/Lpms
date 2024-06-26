<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
    ?>
<?php include '../../requests/home.php';?>
<?php include '../../footer.php';?>