<?php 
// session_start();
if(!isset($_SESSION["username"]) || (strpos($_SESSION["a_type"],"BranchCommittee") === false && strpos($_SESSION["a_type"],"HOCommittee") === false && $_SESSION["department"]!='Owner'))
{
    if(isset($_SESSION["username"]))
        $loc = "Location: ../".$_SESSION["loc"];
    else
        $loc = "Location: ../";
    header($loc);
}
include '../common/head.php';
include '../'.$_SESSION["loc"].'sidenav.php';
// include_once 'script.php';
?>
