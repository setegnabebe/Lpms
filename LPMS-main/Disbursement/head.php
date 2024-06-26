<?php 
// session_start();
if(!isset($_SESSION["username"]) || ($_SESSION["department"]!='Disbursement' && !(isset($_SESSION["managing_department"]) && in_array("Disbursement",$_SESSION["managing_department"])) && $_SESSION["department"]!='Owner'))
{
    if(isset($_SESSION["username"]))
        $loc = "Location: ../".$_SESSION["loc"];
    else
        $loc = "Location: ../";
    header($loc);
}
include '../common/head.php';
include '../'.$_SESSION["loc"].'sidenav.php';
?>