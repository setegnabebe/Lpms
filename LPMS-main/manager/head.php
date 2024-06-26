<?php 
// session_start();
if(!isset($_SESSION["username"]) || in_array($_SESSION["department"], $_SESSION["dep_list"]))
{
    if(isset($_SESSION["username"]))
        $loc = "Location: ../".$_SESSION["loc"];
    else
        $loc = "Location: ../";
    header($loc);
}
else
{
    if($_SESSION["a_type"]!='manager')
        header("Location: ../".$_SESSION["a_type"]."/");
}
include '../common/head.php';
include '../'.$_SESSION["loc"].'sidenav.php';
// include_once '../Committee/script.php';
?>