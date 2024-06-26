<?php 
// session_start();
// if(!isset($_SESSION["username"]) || ($_SESSION["role"]!='Director' && $_SESSION["department"]!='Owner'))
// {
//     if(isset($_SESSION["username"]))
//         $loc = "Location: ../".$_SESSION["loc"];
//     else
//         $loc = "Location: ../";
//     header($loc);
// }

include '../common/head.php';
include '../'.$_SESSION["loc"].'sidenav.php';
// $qry="SELECT * FROM account where `Username`='".$_SESSION['username']."'";
// $res=$conn->query($qry);
// $row = $res->fetch_assoc();
// $_SESSION["managing_department"] = explode(",",$row['managing']);
?>