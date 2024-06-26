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
    if(strpos($_SESSION["a_type"],'Admin') === false)
        header("Location: ../".$_SESSION["loc"]."/");
}
include '../common/head.php';
include '../'.$_SESSION["loc"].'sidenav.php';
?>