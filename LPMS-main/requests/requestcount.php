<?php 
    function req_count($conn,$conn_fleet,$req_type, $special = "",$req_stat = "")
    {
        if($req_stat != "")
        {
            $all_active =""; $comp_active =""; $pend_active =""; $rej_active ="";
            if($req_stat=='All')
                $all_active ="list-group-item-warning";
            else if($req_stat=='Completed')
                $comp_active ="list-group-item-warning";
            else if($req_stat=='Rejected')
                $rej_active ="list-group-item-warning";
            else if($req_stat=='Pending')
                $pend_active ="list-group-item-warning";
        }
        else
        {
            $all_active =""; $comp_active =""; $pend_active =""; $rej_active ="";
        }
        $na_t=str_replace(" ","",$req_type);
        $type = $req_type;
        $completed = 0; $rejected = 0; $pending = 0;

    ////////////////////////////////////////Common Filters////////////////////////////////////////////////////
        $common_string = "";
        $common_string .=(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || $_SESSION["department"]=="Finance" || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin")?"":",customer,".$_SESSION["username"];
        $common_string .=(strpos($_SESSION["a_type"],"manager") !== false && !isset($_SESSION["managing_department"]) && $_SESSION["department"]!="Procurement" && $_SESSION["department"]!="Property" && $_SESSION["department"]!="Finance")?",department,".$_SESSION['department']:"";
        $common_string .=(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || $_SESSION["department"]=="Finance")?"":",company,". $_SESSION['company'];
        $common_string .=($_SESSION["department"]=='Procurement' && $_SESSION['company'] == 'Hagbes HQ.' &&  ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false))?"":(($_SESSION["department"]=='Procurement')?",procurement_company,". $_SESSION['company'].", ||company,". $_SESSION['company']:"");
        $common_string .=($_SESSION["department"]=='Finance')?",finance_company,". $_SESSION['company'].", ||company,". $_SESSION['company']:"";
        $common_string .=($_SESSION["department"]=='Property')?",property_company,". $_SESSION['company'].", ||company,". $_SESSION['company']:"";
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    if(isset($_SESSION["managing_department"]) && strpos($_SESSION["a_type"],"Committee") === false)
    {
        if(!in_array("All Departments",$_SESSION["managing_department"]))
        {
            foreach($_SESSION["managing_department"] as $depp)
                $common_string .=($common_string == "")?",department,$depp":", ||department,$depp";
        }
    }
    $coma = ($common_string == "")?"":",";
    if($type != 'All')
        $All= badge_count_requests($conn,$conn_fleet,substr($common_string,1).$coma."request_type,".$type);
    else
        $All= badge_count_requests($conn,$conn_fleet,substr($common_string,1));
        $string_data = "";
        $string_data .="recieved,not!=";
        $string_data .=$common_string.$special;
        if($type != 'All')
            $completed = badge_count_requests($conn,$conn_fleet,$string_data.",request_type,".$type);
        else
            $completed = badge_count_requests($conn,$conn_fleet,$string_data);
            // echo "<script>alert('$string_data')</script>";
        $string_data = "";
        $string_data .="status,Reject%LIKE, ||status,canceled";
        $string_data .=$common_string.$special;

        if($type != 'All')
            $rejected= badge_count_requests($conn,$conn_fleet,$string_data.",request_type,".$type);
        else
            $rejected= badge_count_requests($conn,$conn_fleet,$string_data);

        $string_data = "";
        $string_data .="recieved,not,status,Reject%NOT LIKE,status,canceled!=";
        $string_data .=$common_string.$special;
        if($type != 'All')
            $pending= badge_count_requests($conn,$conn_fleet,$string_data.",request_type,".$type);
        else
            $pending= badge_count_requests($conn,$conn_fleet,$string_data);
    // }
    $na_t = ($type =='All')?"All":$na_t;
    ?>
<div class="row shadow mb-4" data-aos="zoom-in"     id="req_count_body">
<select class='form-control btn-primary' style='cursor:pointer;' name='request'>
<option   value = '<?php echo $na_t?>_All'  id = '<?php echo $na_t?>_All' >Total POs : <?php echo $All?></option>
<option value = '<?php echo $na_t?>_Completed' id = '<?php echo $na_t?>_Completed' ><h6 class=""><i class='fas fa-check-circle text-success'></i> Completed POs</h6>: <?php echo $completed?></option>
<option value = '<?php echo $na_t?>_Rejected' id = '<?php echo $na_t?>_Rejected'> <h6 class=""><i class='far fa-window-close text-danger'></i> Rejected POs</h6>: <?php echo $rejected?></option>
<option  value = '<?php echo $na_t?>_Pending' id = '<?php echo $na_t?>_Pending'> <h6 class=""><i class='fas fa-exchange-alt text-info'></i> Active POs</h6>:<?php echo $pending?></option>
</select>
</div>
<?php
if(isset($_GET["request"]))
{
    ?>
    <script>
        document.getElementById("<?=$_GET["request"]?>").selected = true;
    </script>
    <?php
}
}
?>
 