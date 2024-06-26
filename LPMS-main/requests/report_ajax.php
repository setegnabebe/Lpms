<?php
    session_start();
    include "../connection/connect.php";
    include "../common/details.php";
    include "report_data.php";
    // echo $_GET['comp']."<br>";
    // echo $requests_dep[$_GET['comp']]."<br>";
    // echo $constraints_dep[$_GET['comp']]."<br>";
    echo createGraph($requests_dep[$_GET['comp']],$constraints_dep[$_GET['comp']],"dep_bar");
?>