<?php
    session_start();
    if(isset($_GET["graph_department"]))
    $_SESSION["Graph_company"] = $_GET["department"];
    header("location: ".$_SERVER['HTTP_REFERER']);
?>