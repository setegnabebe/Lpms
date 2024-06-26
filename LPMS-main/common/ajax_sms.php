<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    $thead = $tbody = "";
    $all = $success = $fail = $inbox = '';
    $current_page = (isset($_GET['loadmore']))?$_GET['loadmore']:1;
    ${$_GET['active']} = "active";
    $sql = "SELECT * FROM sentitems WHERE DestinationNumber = ?";
    $stmt_sms_sent = $conn_sms -> prepare($sql);
    $stmt_sms_sent -> bind_param("s", $_SESSION['phone_number']);
    $stmt_sms_sent -> execute();
    $result_sms_sent = $stmt_sms_sent -> get_result();
    $all_sms = $result_sms_sent -> num_rows;

    $stmt_sms_successful = $conn_sms -> prepare($sql." AND `Status` != 'SendingError'");
    $stmt_sms_successful -> bind_param("s", $_SESSION['phone_number']);
    $stmt_sms_successful -> execute();
    $result_sms_successful = $stmt_sms_successful -> get_result();
    $success_sms = $result_sms_successful->num_rows;

    $stmt_sms_failed = $conn_sms -> prepare($sql." AND `Status` = 'SendingError'");
    $stmt_sms_failed -> bind_param("s", $_SESSION['phone_number']);
    $stmt_sms_failed -> execute();
    $result_sms_failed = $stmt_sms_failed -> get_result();
    $fail_sms = $result_sms_failed->num_rows;
    
    $stmt_sms_inbox = $conn_sms -> prepare("SELECT * FROM inbox WHERE TextDecoded != ''");
    $stmt_sms_inbox -> execute();
    $result_sms_inbox = $stmt_sms_inbox -> get_result();
    $inbox_sms = $result_sms_inbox -> num_rows;

    if($_GET['active'] == 'success') {
        $sql .= " AND `Status` != 'SendingError'";
    }
    else if($_GET['active'] == 'fail') {
        $sql .= " AND `Status` = 'SendingError'";
    }
    else if($_GET['active'] == 'inbox') {
        $sql = "SELECT * FROM inbox WHERE TextDecoded != ''";
    }
    if(isset($_GET['search']) && $_GET['search'] != "")
    {
        $sql .= " AND `TextDecoded` LIKE ?";
        $search_term = "%".$_GET['search']."%";
        if($_GET['active'] == 'inbox')
        {
            $sql .= " order by UpdatedInDB desc";
            $stmt_sms_active = $conn_sms -> prepare($sql);
            $stmt_sms_active -> bind_param("s", $search_term);
        }
        else
        {
            $sql .= " order by SendingDateTime desc";
            $stmt_sms_active = $conn_sms -> prepare($sql);
            $stmt_sms_active -> bind_param("ss", $_SESSION['phone_number'], $search_term);
        }
        $stmt_sms_active -> execute();
        $result_sms_active = $stmt_sms_active -> get_result();
        $search_result = $result_sms_active->num_rows;
    }
    else
    {
        if($_GET['active'] == 'inbox')
        {
            $sql .= " order by UpdatedInDB desc";
            $stmt_sms_active = $conn_sms -> prepare($sql);
            $stmt_sms_active -> execute();
            $result_sms_active = $stmt_sms_active -> get_result();
        }
        else
        {
            $sql .= " order by SendingDateTime desc";
            $stmt_sms_active = $conn_sms -> prepare($sql);
            $stmt_sms_active -> bind_param("s", $_SESSION['phone_number']);
            $stmt_sms_active -> execute();
            $result_sms_active = $stmt_sms_active -> get_result();
        }
    }
    $total_pages = ceil(($result_sms_active->num_rows)/40);
    $sql .= " LIMIT 40";
    $count = 0;
    if(isset($_GET['loadmore']))
    {
        $load_from = $_GET['loadmore']*40;
        $sql .= " OFFSET $load_from";
        $count += $load_from;
    }
    $stmt_sms_active = $conn_sms -> prepare($sql);
    if(isset($_GET['search']) && $_GET['search'] != "")
    {
        if($_GET['active'] == 'inbox')
        {
            $stmt_sms_active -> bind_param("s", $search_term);
        }
        else
        {
            $stmt_sms_active -> bind_param("ss", $_SESSION['phone_number'], $search_term);
        }
        $stmt_sms_active -> execute();
        $result_sms_active = $stmt_sms_active -> get_result();
    }
    else
    {
        if($_GET['active'] == 'inbox')
        {
            $stmt_sms_active -> execute();
            $result_sms_active = $stmt_sms_active -> get_result();
        }
        else
        {
            $stmt_sms_active -> bind_param("s", $_SESSION['phone_number']);
            $stmt_sms_active -> execute();
            $result_sms_active = $stmt_sms_active -> get_result();
        }
    }
    $thead='<tr>
                <th>#</th>
                <th style="width:10%">Date</th>
                <th>Message</th>
            </tr>';
    if($result_sms_active->num_rows>0)
    {
        while($row = $result_sms_active->fetch_assoc())
        {
            $count++;
            if($_GET['active'] == 'inbox')
            {
                $name = "";
                $all_data = $row['TextDecoded'];
                $from = "<div class='mt-1 fw-bold'>From : ".$row['SenderNumber']."</div>";
                $status = "successful";
                $status_color = "success";
            }
            else
            {

                if(strpos($row['TextDecoded'],"Dear") !== false)
                {
                    $data = explode(",",$row['TextDecoded'],2);
                    $name = (isset($data[1]))?"<div class='fw-bold mb-1'>$data[0]</div>":"";
                    $all_data = (isset($data[1]))?$data[1]:$row['TextDecoded'];
                }
                else
                {
                    $name = "";
                    $all_data = $row['TextDecoded'];
                }
        
                $all_data = (strpos($all_data,$row['CreatorID']) == strlen($all_data)-strlen($row['CreatorID']))?substr($all_data, 0, strlen($all_data)-strlen($row['CreatorID'])):$all_data;
                $status_color = ($row['Status'] == 'SendingError')?"danger":"success";
                $status = ($row['Status'] == 'SendingError')?"Failed":"successful";
                $from = (str_replace("."," ",$_SESSION['username']) != $row['CreatorID'] && $_SESSION['username'] != $row['CreatorID'])?"<div class='mt-1 fw-bold'>From : ".str_replace("."," ",$row['CreatorID'])."</div>":"";
            }
            $tbody .= "
            <tr>
                <td>$count</td>
                <td>".(($_GET['active'] == 'inbox')?date('d-M-Y H:i', strtotime($row['UpdatedInDB'])):date('d-M-Y H:i', strtotime($row['SendingDateTime'])))."</td>
                <td data-bs-toggle='tooltip' data-bs-title='Default tooltip'>
                    <span class='visually-hidden'>$status</span>
                    <li class='list-group-item list-group-item-$status_color d-flex justify-content-between align-items-start'>
                        <div class='ms-2 me-auto'>
                        $name
                        $all_data
                        $from
                        </div>
                    </li>
                </td>
            </tr>";
        }
    }
    if($tbody == "") $tbody = "<tr><th colspan='3' class='text-center'>No messages found</th></tr>"; 
    if(isset($_GET['loadmore']))
    {
        echo $tbody;
    }
    else if(isset($_GET['search']) && $_GET['search'] != "")
    { ?>
        <table class='table' id=''><!-- tableDynamic1 -->
            <thead class='bg-light'><?= $thead?></thead>
            <tbody id='sms_tblbody'><?= $tbody?></tbody>
        </table>
        <?=($current_page<$total_pages)?"<div class='text-center' id='loadmore_btn'><button class='btn btn-primary' id='loadmore_sms' type='button' name='$total_pages' value='".($current_page)."' onclick='load_sms(this,\"".$_GET['active']."\",\"more\",\"".$_GET['search']."\")'>Load More</button></div>":""?>
        --|--<?=$search_result?>
    <?php }
    else
    { ?>
    <div class="container-fluid">
        <div class="float-end">
            <div class="d-inline me-1">
                <span class="alert-danger px-2 py-0">&nbsp;</span> - Not received
            </div>
            <div class="d-inline">
                <span class="alert-success px-2 py-0">&nbsp;</span> - Received
            </div>
        </div>
        <div class="col-md-12 col-lg-6 col-xl-7 mx-auto text-center">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?=$all?> position-relative" href="#" onclick='load_sms(this)'>All <span class="badge bg-info ms-1"><?=$all_sms?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?=$success?> position-relative" href="#" onclick='load_sms(this,"success")'>Your Inbox <span class="badge bg-success ms-1"><?=$success_sms?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?=$fail?> position-relative" href="#" onclick='load_sms(this,"fail")'>Failed <span class="badge bg-danger ms-1"><?=$fail_sms?></span></a>
                </li>
                <?php 
                if(strpos($_SESSION["a_type"],'Admin') !== false || $_SESSION['role'] == 'Director' || $_SESSION['role'] == 'Owner' || ($_SESSION['role'] == 'manager' && $_SESSION['department'] == 'IT'))//
                { ?>
                    <li class="nav-item">
                        <a class="nav-link <?=$inbox?> position-relative" href="#" onclick='load_sms(this,"inbox")'>SIM Inbox <span class="badge bg-info ms-1"><?=$inbox_sms?></span></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="float-end my-3 me-2 row">
            <div id="search_bar" class="col-12 me-0"><input class="form-control" placeholder="Search..." type="text" onkeyup='load_sms(this,"<?= $_GET["active"]?>","search",this.value)' onchange='load_sms(this,"<?= $_GET["active"]?>","search",this.value)'></div>
            <span id="search_count" class="d-none badge alert-primary pt-2 col-2 fs-6"></span>
        </div>
        <div id='messages'>
            <table class='table' id=''><!-- tableDynamic1 -->
                <thead class='bg-light'><?= $thead?></thead>
                <tbody id='sms_tblbody'><?= $tbody?></tbody>
            </table>
            <?=($current_page<$total_pages)?"<div class='text-center' id='loadmore_btn'><button class='btn btn-primary' id='loadmore_sms' name='$total_pages' type='button' value='".($current_page)."' onclick='load_sms(this,\"".$_GET['active']."\",\"more\")'>Load More</button></div>":""?>
        </div>
    </div>
    <?php }
}
else
{
    echo $go_home;
}
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>