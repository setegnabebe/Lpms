<?php
session_start();
$pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
$first =(isset($_SESSION["username"]))?"../":"";
include_once $pos.$first."common/email.php";
$servername = "localhost";
$username = "root";
$password = "";//4hR3XnqZaTcg3hf.

$conn = new mysqli($servername, $username, $password,"project_lpms");

$conn_ws = new mysqli($servername, $username, $password,"ws");

$conn_fleet = new mysqli($servername, $username, $password,"fleet");

$db_list=array("`raw_material_request`","`spare_lub_request`", "`tb_request`", "`fixed_assets_request`","`stationary_request`", "`misc_request`");
foreach($db_list as $dbs)
{
    
$sql_po_cluster = "SELECT * FROM `purchase_order` where `cluster_id`= ?";
$stmt_po_cluster = $conn->prepare($sql_po_cluster);
$stmt_po_cluster -> bind_param("i", $row['id']);
$stmt_po_cluster -> execute();
$result_po_cluster = $stmt_po_cluster -> get_result();
if($result_po_cluster -> num_rows > 0)
    while($row2 = $result_po_cluster -> fetch_assoc())
    {
    /////////////////raw request form///////////////
    if(isset($_GET['submit_raw_request']))
    {
        $stmt_unique = $conn->prepare("INSERT INTO `requests`
        (`request_for`, `request_type`, `customer`, `item`,
        `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
        `Remark`, `description`,
        `department`, `status`, `company`, `processing_company`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($_GET['remark'][$ii]=='')
            $remark[$ii] = '#';
        else
            $remark[$ii] = $_GET['remark'][$ii];
        if($_GET['description'][$ii]=='')
            $desc[$ii] = '#';
        else
            $desc[$ii] = $_GET['description'][$ii];
            if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';
        $id_project=$_SESSION["project_id"];
        $uname=$_SESSION["username"];
        $type='Raw Material';
        $item[$ii]=$_GET['item'][$ii];
        $unit[$ii]=$_GET['unit'][$ii];
        $req_quan[$ii]=$_GET['req_quan'][$ii];
        $date_req=date("Y-m-d H:i:s");
        $date_n_b[$ii]=$_GET['date_n_b'][$ii];
        $stmt_unique -> bind_param("ssssisssssssss",$id_project,$type, $uname, $item[$ii], $req_quan[$ii], $unit[$ii], $date_req, $date_n_b[$ii], $remark[$ii], $desc[$ii], $_SESSION['department'], $status, $_SESSION['company'], $_SESSION['processing_company']);
        if($stmt_unique -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            $last_id[$ii] = $conn->insert_id;
            $stmt = $conn->prepare("INSERT INTO `report`(`request_id`, `type`, `request_date`) 
            VALUES (?, ?, ?)");
            $stmt-> bind_param("iss",$last_id[$ii], $type, $date_req);
            $stmt -> execute();
        }
        else
        {
            echo $conn->error;
        }
    }
    ///////////////////////////////////////



    ///////////////// spare form///////////////
    if(isset($_GET['submit_spare_request']))
    {
        if($_GET['remark']=='')
            $remark = '#';
        else
            $remark = $_GET['remark'];
        if($_GET['description']=='')
            $desc = '#';
        else
            $desc = $_GET['description'];
            if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';
        $stmt_spare = $conn->prepare("INSERT INTO `requests`
        (`request_for`, `request_type`, `customer`, `item`,
            `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `type`,
            `Remark`, `description`,`department`, `status`, `company`, `processing_company`, `mode`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $id_job=$_SESSION["request_for"];
        $uname=$_SESSION["username"];
        $type='Spare and Lubricant';
        $item=$_GET['item'];
        $unit=$_GET['unit'];
        $req_quan=$_GET['req_quan'];
        $date_req=date("Y-m-d H:i:s");
        $date_n_b=$_GET['date_n_b'];
        $mode=$_GET['mode'];
        $typee=$_GET['type_specific'];
        $stmt_spare -> bind_param("ssssisssssssssss",$id_job, $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $typee, $remark, $desc, $_SESSION['department'], $status, $_SESSION['company'], $_SESSION['company'],$mode);

        if($stmt_spare -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            // header("location: ".$_SERVER['HTTP_REFERER']);
        }
        else
        {
            echo $conn->error;
        }
    }
    ///////////////////////////////////////////


    ///////////////// tyre and battery form///////////////
    if(isset($_GET['submit_tb_request']))
        {
            $re='yes';
            $sql = "SELECT MAX(`request_id`) As `max_i` FROM `tb_request` WHERE `request_for` = ? AND `recieved` = ? ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $_GET['plate'],$re);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($max_id);
            $stmt->fetch();
            $stmt->close();
            if(!isset($max_id))
            $max_id=Null;
            if($_GET['remark']=='')
                $remark = '#';
            else
                $remark = $_GET['remark'];
            if($_GET['description']=='')
                $desc = '#';
            else
                $desc = $_GET['description'];
                if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
                    $status = 'Approved By Dep.Manager';
                else
                    $status = 'waiting';
            $stmt_tb = $conn->prepare("INSERT INTO `requests`
            (`request_for`, `request_type`, `customer`, `item`,
                `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `to_replace`,
                `Remark`, `description`,`department`, `current_km`, `prev_req`, `status`, `company`, `processing_company`, `mode`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $torep = $_GET['repser'][0];
            for($i=1;$i<count($_GET['repser']);$i++)
            {
                $torep.=','.$_GET['repser'][$i];
            }
            $plate=$_GET['plate'];
            $uname=$_SESSION["username"];
            $item=$_GET['item'];
            $type='Tyre and Battery';
            $unit = 'pcs';
            $req_quan=$_GET['req_quan'];
            $date_n_b=$_GET['date_n_b'];
            $current_km=$_GET['current_km'];
            $date_req=date("Y-m-d H:i:s");
            $mode=$_GET['mode'];
            $stmt_tb -> bind_param("ssssisssssssiissss",$plate, $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $torep, $remark, $desc, $_SESSION['department'], $current_km, $max_id, $status, $_SESSION['company'], $_SESSION['processing_company'], $mode);
            if($stmt_tb -> execute())
            {
                $_SESSION["success"]="Requests Successfully Requested !!";
                // header("location: ".$_SERVER['HTTP_REFERER']);
            }
            else
            {
                echo $conn->error;
            }
        }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////// FIXED form///////////////
    if(isset($_GET['submit_fixed_request']))
    {
        if($_GET['remark']=='')
            $remark = '#';
        else
            $remark = $_GET['remark'];
        if($_GET['description']=='')
            $desc = '#';
        else
            $desc = $_GET['description'];
        if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
            $status = 'Approved By Dep.Manager';
        else
            $status = 'waiting';
        $stmt_fixed = $conn->prepare("INSERT INTO `requests`
        (`request_type`, `customer`, `item`, `requested_quantity`, `unit`, `date_requested`, `date_needed_by`, `type`, `to_replace`,
        `Remark`, `description`, `spec_dep`, `department`, `status`, `company`, `processing_company`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($_GET['reason']=='replace')
        {
            $torep = $_GET['repser'][0];
            for($i=1;$i<count($_GET['repser']);$i++)
            {
                $torep.=','.$_GET['repser'][$i];
            }
        }
        else
            $torep = null;
        $it_related = ($_GET['IT_RELATED'] == "yes")?"IT":null;
        $uname=$_SESSION["username"];
        $item=$_GET['item'];
        $type='Fixed Assetss';
        $ty = $_GET['reason'];
        $unit = 'pcs';
        $req_quan=$_GET['req_quan'];
        $date_n_b=$_GET['date_n_b'];
        $date_req=date("Y-m-d H:i:s");

        $stmt_fixed -> bind_param("sssissssssssssss", $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $ty, $torep, $remark, $desc, $it_related, $_SESSION['department'], $status, $_SESSION['company'], $_SESSION['processing_company']);
        if($stmt_fixed -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            // header("location: ".$_SERVER['HTTP_REFERER']);
        }
        else
        {
            echo $conn->error;
        }
    }
    ////////////////////////////////////////////////////////////


    /////////////////////////////////// STATIONARY form///////////////
    if(isset($_GET['submit_stationary_request']))
    {
        if($_GET['remark']=='')
            $remark = '#';
        else
            $remark = $_GET['remark'];
        if($_GET['description']=='')
            $desc = '#';
        else
            $desc = $_GET['description'];
            if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';
        $stmt_stationary = $conn->prepare("INSERT INTO `requests`
        (`request_type`, `customer`, `item`, `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
        `Remark`, `description`, `department`, `status`, `company`, `processing_company`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $unit=$_GET['unit'];
        $uname=$_SESSION["username"];
        $item=$_GET['item'];
        $type='Stationary and Toiletaries';
        $req_quan=$_GET['req_quan'];
        $date_n_b=$_GET['date_n_b'];
        $date_req=date("Y-m-d H:i:s");
        $stmt_stationary -> bind_param("sssisssssssss", $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $remark, $desc, $_SESSION['department'], $status, $_SESSION['company'], $_SESSION['processing_company']);
        if($stmt_stationary -> execute())
            $_SESSION["success"]="Requests Successfully Requested !!";
        else
        {
            echo $conn->error;
        }
        // header("location: ".$_SERVER['HTTP_REFERER']);
    }
    /////////////////////////////////////////////////////////////////


    //////////////////////////////// Misc form/////////////////////////////
    if(isset($_GET['submit_misc_request']))
    {
        if($_GET['remark']=='')
            $remark = '#';
        else
            $remark = $_GET['remark'];
        if($_GET['description']=='')
            $desc = '#';
        else
            $desc = $_GET['description'];
            if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Owner"))
                $status = 'Approved By Dep.Manager';
            else
                $status = 'waiting';
        $stmt_misc = $conn->prepare("INSERT INTO `requests`
        (`request_type`, `customer`, `item`, `requested_quantity`, `unit`, `date_requested`, `date_needed_by`,
        `Remark`, `description`, `department`, `status`, `company`, `processing_company`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $uname=$_SESSION["username"];
        $unit=$_GET['unit'];
        $item=$_GET['item'];
        $type='Miscellaneous';
        $req_quan=$_GET['req_quan'];
        $date_n_b=$_GET['date_n_b'];
        $date_req=date("Y-m-d H:i:s");
        $stmt_misc -> bind_param("sssisssssssss", $type, $uname, $item, $req_quan, $unit, $date_req, $date_n_b, $remark, $desc, $_SESSION['department'], $status, $_SESSION['company'], $_SESSION['company']);
        
        if($stmt_misc -> execute())
        {
            $_SESSION["success"]="Requests Successfully Requested !!";
            // header("location: ".$_SERVER['HTTP_REFERER']);
        }
        else
        {
            echo $conn->error;
        }
    }
}
}
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>