<?php 
$pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
if(!isset($_SESSION['position']))$_SESSION['position'] = $pos;
include $pos."../connection/connect.php";
if(strpos($_SERVER['PHP_SELF'],'issue.php') === false && isset($_SESSION['last_issue'])) 
{
    unset($_SESSION['last_issue']);
}
if($_SESSION['department'] == 'Procurement' && !strpos($_SERVER['PHP_SELF'],'requests'))
    echo "<script> var pos = '../';</script>";
else
    echo "<script> var pos = '';</script>";
$sql_category = "SELECT * FROM `catagory`";
$stmt_category = $conn->prepare($sql_category);
$stmt_category -> execute();
$result_category = $stmt_category -> get_result();
if($result_category->num_rows>0)
    while($row_temp = $result_category->fetch_assoc())
    {
        $privilege[$row_temp["catagory"]] = explode(",", str_replace(' ', '', $row_temp["privilege"]));
    } 
$sql_feedback = "SELECT * FROM `feedback` where user = ?";
$stmt_feedback = $conn->prepare($sql_feedback);
$stmt_feedback -> bind_param("s", $_SESSION['username']);
$stmt_feedback -> execute();
$result_feedback = $stmt_feedback -> get_result();
$has_feedback = ($result_feedback->num_rows>0);
    
$logoutdate = date('Y-m-d H:i:s');
$sql_settings = "SELECT * from admin_settings order by id Desc Limit 1";
$stmt_settings = $conn->prepare($sql_settings);
$stmt_settings -> execute();
$result_settings = $stmt_settings -> get_result();
if($result_settings->num_rows>0)
    while($row = $result_settings->fetch_assoc())
    {
        $m_limit_consumer = $row['logout_time_min'];
        $timeout = $row['logout_time_min'];
        $surveyLimit = $row['surveyLimit'];
    }
$sql_logs = "SELECT * FROM `log` where `prev_id` = ?";
$stmt_logs = $conn->prepare($sql_logs);
$stmt_logs -> bind_param("i", $_SESSION['log_id']);
$stmt_logs -> execute();
$result_logs = $stmt_logs -> get_result();
if($result_logs->num_rows>0)
    while($row2 = $result_logs->fetch_assoc())
    {
      $datetime1 = new DateTime($row2["time"]);//start time
      $datetime2 = new DateTime($logoutdate);//end time
      $interval = $datetime1->diff($datetime2);
      
     
      // $interval->format('%Y years %m months %d days %H hours %i minutes %s seconds')."');</script>";
      $timediff = 0;
      $timediff += intval($interval->format('%i'));
      $timediff += intval($interval->format('%H'))*60;
      $timediff += intval($interval->format('%d'))*24*60;
      $timediff += intval($interval->format('%m'))*30*24*60;
      $timediff += intval($interval->format('%Y'))*365*24*60;
      if($timediff<$timeout)
      {
        $sql_update_log = "UPDATE `log` SET `time`=? WHERE `id`=?";
        $stmt_update_log = $conn->prepare($sql_update_log);
        $stmt_update_log -> bind_param("si", $logoutdate, $row2['id']);
        $stmt_update_log -> execute();
      }
      else 
      {
        $status = "Offline";
        $sql = "UPDATE Account SET user_status = ? WHERE unique_id = ?";
        $stmt_offline = $conn->prepare($sql);
        $stmt_offline -> bind_param("si", $status, $_SESSION['unique_id']);
        $stmt_offline -> execute();
        $conn->close();
        $conn_fleet->close();
        $conn_ws->close();
        session_destroy();
        header("Location:$pos../index.php");
      }
    }
else 
{
    $logoutdate = date('Y-m-d H:i:s');
    $op = "Logout";
    $sql_logout="INSERT INTO `log` (`operation`,`time`,`prev_id`,`user`)VALUES(?,?,?,?)";
    $stmt_logout = $conn->prepare($sql_logout);
    $stmt_logout -> bind_param("ssis", $op, $logoutdate, $_SESSION['log_id'], $_SESSION['username']);
    $stmt_logout -> execute();
}
?>
<!DOCTYPE html> 
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <?php 
            include $pos."../common/head_css.php";
        ?>

        <!-- <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet"> -->
        <style>
            @media screen
            {
                .noPrint{}
                .noScreen{display:none;}
            }
            @media print
            {
                .noPrint{display:none;}
                .noScreen{}
            }
            .fa-check-circle {
                animation-name: wiggle;
                animation-duration: 1s;
                animation-iteration-count: infinite;
                animation-timing-function: ease-in-out;
            }
            .focus:hover {
                transform: scale(1.04);
                transition: all 0.7s ease-in-out 0.1s;
            }
            .fa-exclamation-circle {
                animation-name: upd;
                animation-duration: 0.5s;
                animation-iteration-count: infinite;
                animation-timing-function: ease-in-out;
            }
            @keyframes upd {
                from { transform: translate(0,  0px); }
                65%  { transform: translate(0, 5px); }
                to   { transform: translate(0, -0px); } 
            } 
        </style>
    </head>
    <body onload="cp()">
        <?php 
include 'functions.php';
include 'details.php';
include $pos."../requests/requestcount.php";
include_once $pos.'../Committee/script.php';
?>
    <div id="app" class='noPrint'>
<?php #include 'sidenav.php'; ?>
<script>
    var sidetobe = "", special_pos= "";
function sideactive(e,from="")
{
    if(from == "")
        sidetobe = e;
    else if(e)
    {
        var ul = document.getElementById("side_list").children;
        for(let i=0;i<ul.length;i++)
        {
            if(ul[i].className.includes("mb-3 sidebar-item"))
                ul[i].classList.remove('active');
        }
        document.getElementById(e).classList.add('active');
        document.getElementById(document.getElementById(e).parentElement.id).classList.remove('d-none');
    }
}


function desc_man(e)
{
 
    if(document.getElementById('manager_remark').value=='')
    {
        document.getElementById('warnin').innerHTML="*Can't Be Empty Please Fill in the field";
        return 0;
    }
    var req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
        document.getElementById('close_man_rem').click();
        document.getElementById('man_rem_set').classList.remove('text-center');
        document.getElementById('man_rem_set').innerHTML="<i class='text-primary'>Manager Remark  -  </i>"+document.getElementById('manager_remark').value;
    }
    req.open("GET", "allphp.php?request="+e.id.replace('desc_man_','')+"&m_desc="+document.getElementById('manager_remark').value);
    req.send();
}
function query_search(e,from)
{
    if(e == '')
    {
        if(document.getElementById('view_more_btn'))
            document.getElementById('view_more_btn').classList.remove('d-none');
        if(temp!='')
            document.getElementById('searched').innerHTML=temp;
        else
            document.getElementById('searched').classList.add('d-none');
        var xx = document.getElementsByClassName('searched');
        for (let item of xx) {
            item.classList.remove('d-none');
        }
        return 0;
    }
    if(e != "" && from != "")
    {
        if(document.getElementById('view_more_btn')) 
            document.getElementById('view_more_btn').classList.add('d-none');
        var req = new XMLHttpRequest();
        var xx = document.getElementsByClassName('searched');
        for (let item of xx) {
            item.classList.add('d-none');
        }
        var tbl=document.getElementById('tbl_view');
        req.onload = function(){
            var data=this.responseText.split(":__:");
            document.getElementById('searched').innerHTML=data[0] ;
            document.getElementById('searched').classList.remove('d-none');
            tbl.innerHTML=data[1];
            document.getElementById('search_text').innerHTML=data[2];
            document.getElementById('search_text').classList.remove('d-none');
            document.getElementById('search_text2').innerHTML=data[2];
        }
        req.open("GET", pos+"../requests/search.php?param="+e+"&from="+from);
        req.send();
    }
}
// function (event) {
//     if (event.code === 'Enter') {
//         document.getElementById("").click();
//     }
// } 
function set_title(e)
{
    document.getElementById('title_page').innerHTML = e;
}
</script>
<?php
if(isset($surveyLimit) && !is_null($surveyLimit))
{
    $survey_deadline = $surveyLimit;
    $date1=date_create(date('Y-m-d'));
    $date2=date_create($survey_deadline);
    $diff=date_diff($date1,$date2);
    $_SESSION['survey'] = intval($diff->format("%R%a"));
    $survey = ($_SESSION['survey'] > 0);
    $survey_roles = ['Admin','Director','Owner'];
    $survey_condition = (in_array($_SESSION['role'],$survey_roles) && $_SESSION['company'] == 'Hagbes HQ.');
}
else
{
    $_SESSION['survey'] = 0;
    $survey = true;
}
?>