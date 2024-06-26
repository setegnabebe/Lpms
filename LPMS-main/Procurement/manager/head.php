<?php 
// session_start();
$pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
if(!isset($_SESSION["username"]) || 
($_SESSION["department"]!='Procurement' && !(isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) && $_SESSION["additional_role"]!=1 && $_SESSION["department"]!='Owner'))
{
    if(isset($_SESSION["username"])){
        
        $loc = "Location: $pos../".$_SESSION["loc"];
    }
    else
        $loc = "Location: $pos../";
    header($loc);
}
// $pos = (strpos($_SERVER['PHP_SELF'],'requests'))?"":"../";
include $pos.'../common/head.php';
include $pos.'../'.$_SESSION["loc"].'sidenav.php';
// include_once $pos.'../Committee/script.php';
?>