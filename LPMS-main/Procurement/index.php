<?php 
session_start();
if(!isset($_SESSION["username"]) || $_SESSION["department"]!='Procurement'){
    if(isset($_SESSION["username"]))
        $loc = (in_array($_SESSION["department"], $_SESSION["dep_list"]))?"Location: ../".$_SESSION["department"]."/":"Location: ../".$_SESSION["role"]."/";
    else
        $loc = "Location: ../";
    header($loc);
}
else
{
    if($_SESSION["role"]=='Purchase officer')
    {
        $_SESSION["loc"] = "procurement/junior/";
        $loc =  "Location: junior/";
    }
    else if($_SESSION["role"]=='GS' || $_SESSION["role"]=='user')
    {
        $_SESSION["loc"] = "procurement/GS/";
        $loc =  "Location: GS/";
    }
    else if($_SESSION["role"]=='manager')
    {
        $_SESSION["loc"] = "procurement/manager/";
        $loc =  "Location: manager/";
    }
    else if($_SESSION["role"]=='Senior Purchase officer')
    {
        $_SESSION["loc"] = "procurement/senior/";
        $loc =  "Location: senior/";
    }
    header($loc);
}
?>