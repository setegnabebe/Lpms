<?php 
// session_start();
if(!isset($_SESSION["username"]) || ($_SESSION["department"]!='Procurement' && !(isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) && $_SESSION["department"]!='Owner'))
{
    if(isset($_SESSION["username"])){
        
        $loc = "Location: ../../".$_SESSION["loc"];
    }
    else
        $loc = "Location: ../../";
    header($loc);
}
$pos = (strpos($_SERVER['PHP_SELF'],'requests'))?"":"../";
include $pos.'../common/head.php';
include $pos.'../'.$_SESSION["loc"].'sidenav.php';
?>