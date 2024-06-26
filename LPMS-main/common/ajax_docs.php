<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    function get_request($tbl_issue,$id_issue,$type_return = "",$system = "")
    {
        include '../connection/connect.php';
        $result_str = $itemname = "";
        if($tbl_issue == "requests")
        {
            $sql_request = "SELECT * FROM requests where `request_id`= ?";
            $stmt_request = $conn->prepare($sql_request);
            $stmt_request->bind_param("i", $id_issue);
            $stmt_request->execute();
            $result_request = $stmt_request->get_result();
            if($result_request->num_rows>0)
                while($row = $result_request->fetch_assoc())
                {
                    $type=$row['request_type'];
                    $na_t=str_replace(" ","",$type);
                    $itemname = "<button type='button'  value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']."</button>";
                    $result_str= "<div class='row my-2 mx-auto'>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Item  - $itemname </i></li>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Quantity  -  </i>".$row['requested_quantity']."</li>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Catagory  -  </i>".$row['request_type']."</li>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Requested by  -  </i>".str_replace('.',' ',$row['customer'])."</li>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Request Date  -  </i>".date('d-M-Y H:i',strtotime($row['date_requested']))."</li>
                        <li class='list-group-item list-group-item-light border col-sm-12 col-md-6'><i class='text-primary'>Date Needed by  -  </i>".date('d-M-Y',strtotime($row['date_needed_by']))."</li>";
                        $sql_po_by_request = "SELECT * FROM purchase_order where `request_id`= ?";
                        $stmt_po_by_request = $conn->prepare($sql_po_by_request);
                        $stmt_po_by_request->bind_param("i", $id_issue);
                        $stmt_po_by_request->execute();
                        $result_po_by_request = $stmt_po_by_request->get_result();
                        if($result_po_by_request->num_rows>0)
                        {
                            $row_po_by_request = $result_po_by_request->fetch_assoc();
                            if($row_po_by_request['cluster_id'] != "" && !is_null($row_po_by_request['cluster_id']))
                                $result_str .="
                            <div class='list-group-item list-group-item-light border text-center'>
                            <button type='button' name='".$row_po_by_request['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet</button>
                            </div>
                            ";
                        }
                        $result_str .="
                        </div>";
                        if(isset($_SESSION['last_issue']))
                            $result_str .= "<input type='hidden' name='issue_for' value='".$_SESSION['last_issue']."'>";
                }
            else if($system != "" && !is_null($system)) 
            {
                $result_str = $itemname = "<h5 class=''>For - $system</h5><input type='hidden' name='project-Name' value='".$system."'>";
            }
            else
            {
                $result_str = $itemname = "<h4 class='text-center text-danger'>Request Not Found</h4>";
            }
        }
        return ($type_return == "")?$result_str:$itemname;
    }

    if(isset($_GET['view_documents_details']))
    {
        ?>
        <div class="modal-header">
            <h4 class="modal-title w-100 text-center">Read Documentations<button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button></h4>
        </div>
        <div class="modal-body">
        <?php
        $count = 0;
        $sql = "SELECT * FROM `documentations` WHERE `status`='open'";
        $stmt_docs = $conn->prepare($sql);
        $stmt_docs->execute();
        $result_docs = $stmt_docs->get_result();
        if($result_docs->num_rows==0)
        {
        echo "
        <div class='py-5 pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h3 class='mt-4'>No Documents Published</h3>
            </div>
        </div>";
        }
        else
        {
            echo "
            <div>
                <ul class= 'list-group list-group-flush text-start'>
                    <li class='list-group-item list-group-item-primary mb-4 text-start'>
                    <ul class= 'list-group list-group-flush text-start'>";
                        while($row = $result_docs->fetch_assoc())
                        {
                            $name = str_replace("."," ",$row['uploaded_by']);
                            $file = $row['name'];
                            $count++;
                            echo "
                            <li class='list-group-item list-group-item-light text-strong'>
                            File $count - 
                            <a href='https://portal.hagbes.com/lpms_uploads/".$row['file']."' target='_blank' class='btn btn-outline-primary border-0' download >
                            <i>$file </i> </a>
                            <a href='https://portal.hagbes.com/lpms_uploads/".$row['file']."' target='_blank' class='btn btn-outline-primary border-0 float-end' download ><i class='fas fa-external-link-alt'></i></a>
                            <span class='text-secondary d-block'> Uploaded By : ".$name."</span>
                            <span class='text-secondary d-block'> Date Uploaded : ".date("d-M-Y", strtotime($row['date']))."</span>
                            </li>";
                        }
                        echo "
                        </ul>
                    </li>
                </ul>
            </div>
                ";
        }
        ?>
        </div>
        <?php
    }
    //////////////////////////////////////// SMS Ussage ////////////////////////////////////////
    if(isset($_GET['view_smsusage']))
    {
        $thead = $tbody = "";
        // $current_page = (isset($_GET['loadmore']))?$_GET['loadmore']:1;
        $sql = "SELECT YEAR(`UpdatedInDB`) as Year,MONTHNAME(`UpdatedInDB`) as Month, count(*) as totalsmssent,`Status`,`UpdatedInDB` FROM `sentitems` WHERE Status='SendingOKNoReport' GROUP BY MONTH(`UpdatedInDB`), YEAR(`UpdatedInDB`) ORDER BY timestamp(`UpdatedInDB`) desc;";
        $sent = "active";
        $failed = "";
        if(isset($_GET['active']))
        {
            if($_GET['active'] == 'sent') {
                $failed = "";
                $sent = "active";
                $sql = "SELECT YEAR(`UpdatedInDB`) as Year,MONTHNAME(`UpdatedInDB`) as Month, count(*) as totalsmssent,`Status`,`UpdatedInDB` FROM `sentitems` WHERE Status='SendingOKNoReport' GROUP BY MONTH(`UpdatedInDB`), YEAR(`UpdatedInDB`) ORDER BY timestamp(`UpdatedInDB`) desc;";
            }
            else if($_GET['active'] == 'failed') {
                $sent = "";
                $failed = "active";
                $sql = "SELECT YEAR(`UpdatedInDB`) as Year,MONTHNAME(`UpdatedInDB`) as Month, count(*) as totalsmssent,`Status`,`UpdatedInDB` FROM `sentitems` WHERE Status!='SendingOKNoReport' GROUP BY MONTH(`UpdatedInDB`), YEAR(`UpdatedInDB`) ORDER BY timestamp(`UpdatedInDB`) desc;";
            }
        }
        // $sql .= " GROUP BY title_id,type";
    $thead='<tr class="text-center">
    <th>Year</th>
    <th>Month</th>
    <th>Total SMS</th>
    </tr>';
    // <th>Status</th>

        $stmt_sms_count = $conn_sms->prepare($sql);
        $stmt_sms_count->execute();
        $result_sms_count = $stmt_sms_count->get_result();
        if($result_sms_count->num_rows>0)
        {
            while($row = $result_sms_count->fetch_assoc())
            {
                $thismonth = (date('y-m',strtotime($row['UpdatedInDB'])) == date('y-m'))?" (Current Month)":"";
                $tbody .= "
                <tr>
                    <td>$row[Year]</td>
                    <td>$row[Month] $thismonth</td>
                    <td>$row[totalsmssent]</td>
                </tr>";
            }
        }
        ?>
        <div class="modal-header alert-primary">
            <h4 class="modal-title w-100 text-center text-white">SMS Usage History (+251-993-81-9775)<button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button></h4>

        </div>
        <div class="modal-body">
        <div class="container-fluid">
            <a class="btn btn-outline-primary float-end position-relative" href="#" onclick='load_sms(this,"inbox")' data-bs-toggle='modal' data-bs-target='#view_sms'>SIM Inbox <i class='fas fa-external-link-alt'></i></a><!--  <span class="badge bg-info ms-1"><?=$inbox_sms?></span> -->
            <div class="col-sm-6 col-md-6 col-xl-5 mx-auto mb-4">
                <ul class="nav nav-pills">
                    <!-- <li class="nav-item">
                        <a class="nav-link <?=$all?> position-relative" href="#" onclick='load_sms(this)'>All <span class="badge bg-info ms-1"><?=$all_sms?></span></a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link <?=$sent?> position-relative" href="#" onclick='view_documents("smsusage","sent")'>Sent Messages</span></a><!--  <span class="badge bg-success ms-1"><?php //echo $success_sms?> -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?=$failed?> position-relative" href="#" onclick='view_documents("smsusage","failed")'>Failed Messages</a><!--  <span class="badge bg-danger ms-1"><?php //echo $fail_sms?></span> -->
                    </li>
                </ul>
            </div>
            <div id='messages'>
                <table class='table' id='table<?=$_GET['tblcount']?>'><!-- tableDynamic1 -->
                    <thead class='bg-light'><?= $thead?></thead>
                    <tbody id='sms_tblbody'><?= $tbody?></tbody>
                </table>
            </div>
        </div>
        </div>
        <?php
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////

    if(isset($_GET['feedback']))
    {
        $sql = "SELECT * FROM feedback WHERE user = ? ORDER BY timestamp DESC LIMIT 1";
        $stmt_feedback = $conn->prepare($sql);
        $stmt_feedback->bind_param("s", $_SESSION['username']);
        $stmt_feedback->execute();
        $result_feedback = $stmt_feedback->get_result();
        if($result_feedback->num_rows > 0) $row = $result_feedback->fetch_assoc();
        $edit = ($result_feedback->num_rows > 0)?"Edit <i class='fa fa-edit'> </i>":"Submit";
        ?>
        <div class="modal-header alert-primary">
            <h4 class="w-100 text-center text-white">Survey Form<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
        </div>
        <div class="modal-body" id="view_feedback_body">
            <h5 class="text-secondary text-center fw-bold">
            <button type="button" name="view_feedback" class="btn btn-sm btn-outline-primary float-end"  onclick="modal_optional(this,'feedback')">View Feedbacks</button>
            <?php if($_SESSION['survey'] > 0){?>
            <span class="text-danger text-center mt-2 d-block"><?=$_SESSION['survey']?> Day<?=($_SESSION['survey']>1)?"s":""?> Left</span>
            <?php }?>
                Please take a moment to complete a short survey about our system. <br>
                Your feedback is important in improving our services. <br>
                Thank you!
            </h5>
            <h6 id="warn_snow" class="text-danger text-center"></h6>
            <div class="my-3">
                <div id="snow" style="min-height: 100px;"></div>
                <textarea class="form-control d-none" name="feedback" id="feedback" rows="3" required></textarea>
            </div>
            <div class="text-center mb-4">
                <h6 class="d-block mb-2">Your Rating <?= ($result_feedback->num_rows > 0)?"<span class='text-secondary'>(Previous $row[rating] <i class='fas fa-star text-warning'></i>)</span>":""?></h6>
                <div onclick="set_rating(this)" class="" id='basic'></div>
                <input type="hidden" id="feedback_rating" name="rating">
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-success" name="submit_feedback" onclick="set_quill(this,'snow','feedback')"><?=$edit?></button>
            </div>
        </div>
        <?php
        if($result_feedback->num_rows > 0)
        {
            echo ":|-|:".$row['feedback'];
        }
    }
    if(isset($_GET['view_feedback']))
    {
        $thead = $tbody = "";
        $roles = ['Admin','Director','Owner'];
        $current_page = (isset($_GET['loadmore']))?$_GET['loadmore']:1;
        $condition = (in_array($_SESSION['role'],$roles) && $_SESSION['company'] == 'Hagbes HQ.')?"":" WHERE user = '".$_SESSION['username']."'";
        if((in_array($_SESSION['role'],$roles) && $_SESSION['company'] == 'Hagbes HQ.'))
        {
            $average = 0;
            $avg_count = 0; $avg_sum = 0;
            $sql = "SELECT user FROM feedback group by user";
            $stmt_feedback_user = $conn->prepare($sql);
            $stmt_feedback_user->execute();
            $result_feedback_user = $stmt_feedback_user->get_result();
            if($result_feedback_user->num_rows > 0)
            {
                while($row = $result_feedback_user->fetch_assoc())
                {
                    $avg_count++;
                    $sql_avg = "SELECT AVG(rating) as average FROM feedback where user = ?";
                    $stmt_avg_rating = $conn -> prepare($sql_avg);
                    $stmt_avg_rating -> bind_param("s", $row['user']);
                    $stmt_avg_rating -> execute();
                    $result_avg_rating = $stmt_avg_rating -> get_result();
                    $row_avg = $result_avg_rating -> fetch_assoc();
                    $avg_sum += $row_avg['average'];
                }
                $average = $avg_sum/$avg_count;
            }
        }
        else
        {
            $sql_avg = "SELECT AVG(rating) as average FROM feedback $condition";
            $stmt_avg_rating_special = $conn->prepare($sql_avg);
            $stmt_avg_rating_special -> execute();
            $result_avg_rating_special  = $stmt_avg_rating_special -> get_result();
            $row = $result_avg_rating_special -> fetch_assoc();
            $average = $row['average'];
        }
        $sql = "SELECT * FROM feedback $condition";
        if(isset($_GET['search']) && $_GET['search'] != "")
        {
            $sql .= " AND `user` LIKE '%".$_GET['search']."%'";
            $stmt_avg_rating_search = $conn->prepare($sql);
            // $stmt_avg_rating_search -> bind_param("s", $search_like);
            $stmt_avg_rating_search -> execute();
            $result  = $stmt_avg_rating_search -> get_result();
            $search_result = $result->num_rows;
        }
        $ratings = [];
        $sql .= " Order by timestamp DESC,id DESC";
        $stmt_avg_ratings = $conn->prepare($sql);
        $stmt_avg_ratings -> execute();
        $result  = $stmt_avg_ratings -> get_result();
        $total_pages = ceil(($result->num_rows)/40);
        $total = $result->num_rows;
        $sql .= " LIMIT 40";
        $count = 0;
        if(isset($_GET['loadmore']))
        {
            $load_from = $_GET['loadmore']*40;
            $sql .= " OFFSET $load_from";
            $count += $load_from;
        }
        $stmt_avg_ratings = $conn->prepare($sql);
        $stmt_avg_ratings -> execute();
        $result  = $stmt_avg_ratings -> get_result();
        $thead='<tr>
                    <th>#</th>
                    <th class="text-center">Feedback</th>
                </tr>';
        if($result->num_rows>0)
        {
            while($row = $result->fetch_assoc())
            {
                $temp_rate = (isset($ratings[$row['rating']]))?$ratings[$row['rating']]+1:1;
                $ratings[$row['rating']] = $temp_rate;
                $count++;
                $rating = "<span class='ms-2'>".$row['rating']." <i class='fas fa-star text-warning'></i></span>";
                $all_data = $row['feedback'];
                $tbody .= "
                <tr>
                    <td style='width:5%'>$count</td>
                    <td title='".date('d-M-Y H:i', strtotime($row['timestamp']))."'>
                        <h6 class='font-bold d-inline float-end text-secondary mb-1'>"." ".date('d-M-Y', strtotime($row['timestamp']))."</h6>
                        <div class='d-flex align-items-center mb-2'>
                            <div class='avatar avatar-sm'>
                                <img src='../assets/images/faces/1.jpg' alt='' >
                            </div>
                            <div class='ms-3 name pt-2'>
                                <h6 class='font-bold'>".str_replace("."," ",$row['user'])." $rating</h6><!---->
                            </div> 
                        </div>
                        <li class='list-group-item d-flex justify-content-between align-items-start border-0'>
                            <div class='ms-2 me-auto'>
                            $all_data
                            </div>
                        </li>
                    </td>
                </tr>";
            }
        }
        if($tbody == "") $tbody = "<tr><th colspan='2' class='text-center'>No Feedbacks Given</th></tr>"; 
        if(isset($_GET['loadmore'])) echo $tbody;
        else
        {
        ?>
        <div class="modal-header alert-primary">
            <button type="button" class="btn btn-outline-primary" name="feedback" onclick="modal_optional(this,'feedback')"><i class='bi bi-arrow-left'></i></button>
            <h4 class="w-100 text-center text-white">Feedback Form<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
        </div>
        <div class="modal-body" id="view_feedback_body">
            <div class="row">
                <div class="col-2 border-end text-center">
                    <h5 class="">Average Rating</h5>
                    <p class="fs-2"><?=number_format($average, 2, ".", ",")?> <i class='fas fa-star text-warning'></i></p>
                    <h6 class=""><?=$total?> reviews</h6>
                </div>
                <div class="col-10">
                    <?php 
                    for($i=5;$i>0;$i--)
                    {
                        $new_condition = ($condition=="")?" Where rating = '$i'":$condition." AND rating = '$i'";
                        $sql = "SELECT * FROM feedback $new_condition";
                        $stmt_feedbacks = $conn->prepare($sql);
                        $stmt_feedbacks -> execute();
                        $result  = $stmt_feedbacks -> get_result();
                        $people = $result->num_rows;
                        $percent = ($total == 0)?$total:($people / $total)*100;
                        ?>
                        <div class="row my-1" title="<?=$people." of ".$total?>">
                            <span class="col-1 text-end"><?=$i?> <i class='fas fa-star text-warning'></i></span>
                            <div class="col-11">
                                <div class="progress" role="progressbar" aria-valuenow="<?=$percent?>" aria-valuemin="0" aria-valuemax="100"><!--  style="height: 5px" -->
                                    <div class="progress-bar" style="width: <?=$percent?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div id='feedbacks' class="mt-3">
                <table class='table' id=''><!-- tableDynamic1 -->
                    <thead class='bg-light'><?= $thead?></thead>
                    <tbody id='feedback_tblbody'><?= $tbody?></tbody>
                </table>
                <?=($current_page<$total_pages)?"<div class='text-center' id='loadmore_btn'><button class='btn btn-primary' id='loadmore_feedback' name='view_feedback' data-max='$total_pages' type='button' value='".($current_page)."' onclick='modal_optional(this,\"feedback\",\"loadmore\")'>Load More</button></div>":""?>
            </div>
        </div>
        <?php
        }
    }
    if(isset($_GET['issue']) || isset($_GET['reply']))
    {
        $with_id = "";
        if(isset($_GET['reply']))
        {
            $sql = "SELECT * FROM issues WHERE id = ?";
            $stmt_issues = $conn->prepare($sql);
            $stmt_issues->bind_param("i", $_GET['reply']);
            $stmt_issues->execute();
            $result_issues = $stmt_issues->get_result();
            if($result_issues->num_rows>0)
                while($row = $result_issues->fetch_assoc())
                {
                    $tbl_issue = $row['type'];
                    $id_issue = $row['title_id'];
                    $system_issue = $row['system'];
                    $with_id = " and id =".$row['id'];
                    if(is_null($system_issue))
                    $_SESSION['last_issue'] = $tbl_issue."_".$row['title_id']."_".$row['id'];
                    else
                    $_SESSION['last_issue'] = $tbl_issue."_".$row['title_id']."_".$row['id']."_".$system_issue;
                }
            $result_str = get_request($tbl_issue,$id_issue,"",$system_issue);
        }
        else if($_GET['issue'] == 'Other Issue')
        {
            $result_str = "
            <div class='my-3'>
                <select class='form-select text-primary' name='project-Name' id='project-Name' required>
                    <option value = ''>- Select System -</option>
                    <option value = 'Local Procurement Management System'>Local Procurement Management System</option>
                    <option value = 'Leave Management System'>Leave Management System</option>
                    <option value = 'Perdiem'>Perdiem</option>
                    <option value = '>Fleet Management System'>Fleet Management System</option>
                    <option value = 'Asset Inspection System'>Asset Inspection System</option>
                    <option value = 'Project Management System'>Project Management System</option>
                    <option value = 'Workshop Management System'>Workshop Management System</option>
                    <option value = 'Maintaince Requisition Form System'>Maintaince Requisition Form System</option>
                </select>
            </div>
            ";
        }
        else
        {
            $_SESSION['last_issue'] = $_GET['issue'];
            $tbl_issue = explode("_",$_GET['issue'])[0];
            $id_issue = explode("_",$_GET['issue'])[1];
            if(sizeof(explode("_",$_GET['issue']))>2) $with_id = " and id =".explode("_",$_GET['issue'])[2];
            $system = (sizeof(explode("_",$_GET['issue'])) > 3)?explode("_",$_GET['issue'])[3]:"";
            $result_str = get_request($tbl_issue,$id_issue,"",$system);
        }
        $prev_issues = "";
        $toNew = (isset($_GET["newPage"]))?",'','".$_GET["newPage"]."'":"";
        $reply = (isset($_GET['reply']) || sizeof(explode("_",$_GET['issue']))>2);
        $reply_for = ($reply)?"<h5 class='text-center'>Reply to the above open issue</h5>":"";
        $sql = "SELECT * FROM issues WHERE title_id = ? and `type` = ? and `status` = 'Open' and thread IS NULL $with_id";
        $stmt_issues_open = $conn -> prepare($sql);
        $stmt_issues_open -> bind_param("is", $id_issue, $tbl_issue);
        $stmt_issues_open -> execute();
        $result = $stmt_issues_open -> get_result();
        if($result->num_rows>0)
        {
            while($row = $result->fetch_assoc())
            {
                $radio_open = ($reply)?"
                <div class='mb-3 form-check'>
                    <input type='radio' class='form-check-input d-none' name='reason' value='".$row['id']."' onclick='adjust(this,\"open_status\")' checked>
                </div>":"";
                $color = ($row['status'] == "Closed")?"danger":"success";
                $issue_status = "<span class='badge bg-$color ms-2'>".$row['status']."</span>";// <div class='mt-1 d-inline'></div>
                $all_data = $row['issue']; // text-primary
                $prev_issues .= "
                <div class='".(($reply)?"":"col-sm-12 col-md-5")." mx-auto mb-2'>
                    <div class='bg-white rounded p-3 ".(($reply)?"border border-4 border-primary":"")."'>
                    <div class='container bg-light py-2'>
                        <h6 class='font-bold text-sm d-inline float-end text-secondary mb-1'><span title='".date('d-M-Y H:i', strtotime($row['timestamp']))."'>".date('d-M-Y', strtotime($row['timestamp']))."</span></h6>
                        <div class='d-flex align-items-center'>
                            $radio_open
                            <div class='avatar avatar-sm'>
                                <img src='../assets/images/faces/1.jpg' alt=''>
                            </div>
                            <div class='ms-3 name pt-2'>
                                <h6 class='font-bold'>".str_replace("."," ",$row['user'])." $issue_status</h6>
                            </div> 
                        </div>
                        <div class='d-flex justify-content-between align-items-start mb-1'>
                            <div class='ms-2 me-auto'>
                            $all_data
                            </div>
                            ".(($row['status'] != "Closed" && !$reply)?"
                                <button value='".$row['id']."' onclick=\"modal_optional(this,'issue'$toNew)\" type='button' name='reply' class='btn btn-sm ms-1'>
                                    <i class='fas fa-reply text-primary'></i>
                                </button>":"")."
                            ".(($row['status'] != "Closed" && ($_SESSION['username'] == $row['user'] || $_SESSION['role'] == 'Admin'))?
                            "<button class='btn btn-outline-danger btn-sm' type='button' onclick='prompt_confirmation(this)' name='close_issue' value='".$row['id']."'>Close</button>":"")."
                        </div>";
                        if (!is_null($row['supporting_documents']) && $row['supporting_documents'] != '') {
                            $prev_issues .= "
                            <div class='row gallery noPrint'>
                            <h6 class='text-center'>Pictures/PDF</h6>";

                            $allfiles = explode(':_:', $row['supporting_documents']);
                            foreach ($allfiles as $file) {
                                if (strpos($file, 'pdf')) {
                                    $prev_issues .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                        </div>";
                                } else {
                                    $prev_issues .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' >
                                                <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                            </a>
                                        </div>";
                                }
                            }
                            $prev_issues .= '</div>';
                        }
                        $prev_issues .= '</div>';
                $sql_thread = "SELECT * FROM issues WHERE thread = ? order by timestamp DESC,id DESC";
                $stmt_issues_in_thread = $conn -> prepare($sql_thread);
                $stmt_issues_in_thread -> bind_param("i", $row['id']);
                $stmt_issues_in_thread -> execute();
                $result_thread = $stmt_issues_in_thread -> get_result();
                if($result_thread->num_rows>0)
                {
                    $id_collapse = $collapser = "";
                    if($result_thread->num_rows > 1)
                    {
                        $id_collapse = "<div class='collapse' id='prev_$row[id]'>";
                        $collapser = " data-bs-toggle='collapse' data-bs-target='#prev_$row[id]' aria-expanded='false' aria-controls='prev_$row[id]'";
                    }
                    $prev_issues .= "<div class='divider my-0 fw-bold'><div class='divider-text'>Responses</div></div>";
                    $count_thread = 0;
                    while($row_thread = $result_thread->fetch_assoc())
                    {
                        $count_thread++;
                        if($count_thread==2)
                        {
                            $prev_issues .= $id_collapse;
                        }
                        $color = ($row_thread['status'] == "Closed")?"danger":"success";
                        $issue_status = "<span class='badge bg-$color ms-2'>".$row_thread['status']."</span>";// <div class='mt-1 d-inline'></div>
                        $all_data = $row_thread['issue']; // text-primary
                        $prev_issues .= "
                        <div class='container bg-light py-2'>
                            <h6 class='font-bold text-sm d-inline float-end text-secondary mb-1'><span title='".date('d-M-Y H:i', strtotime($row_thread['timestamp']))."'>".date('d-M-Y', strtotime($row_thread['timestamp']))."</span></h6>
                            <div class='d-flex align-items-center'>
                                <div class='avatar avatar-sm'>
                                    <img src='../assets/images/faces/1.jpg' alt=''>
                                </div>
                                <div class='ms-3 name pt-2'>
                                    <h6 class='font-bold'>".str_replace("."," ",$row_thread['user'])."</h6>
                                </div> 
                            </div>
                            <div class='d-flex justify-content-between align-items-start mb-1'>
                                <div class='ms-2 me-auto'>
                                $all_data
                                </div>
                                <!-- used to have a close btn -->
                            </div>";
                        if (!is_null($row_thread['supporting_documents']) && $row_thread['supporting_documents'] != '') {
                            $prev_issues .= "
                            <div class='row gallery noPrint'>
                            <h6 class='text-center'>Pictures/PDF</h6>";
                        
                            $allfiles = explode(':_:', $row_thread['supporting_documents']);
                            foreach ($allfiles as $file) {
                                if (strpos($file, 'pdf')) {
                                    $prev_issues .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                        </div>";
                                } else {
                                    $prev_issues .= "
                                        <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' >
                                                <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                            </a>
                                        </div>";
                                }
                            }
                            $prev_issues .= '</div>';
                        }
                        $prev_issues .= '</div>';
                    }
                    $prev_issues .= ($count_thread>1)?"</div><div class='divider my-0 fw-bold' $collapser><div class='divider-text'><div class='badge alert-primary' onclick='adjust(this)'><span>View more </span><i class='fas fa-angle-down'></i></div></div></div>":"";
                }
                $prev_issues .= "
                </div>
                </div>";
            }
            $prev_issues = "
            <div class='container row mx-auto'>
            <h4 class='text-center'>Active Issues</h5>
                $prev_issues
                $reply_for
            </div>";
        }
        if(!isset($_GET['newPage']))
        {
        ?>
            <div class="modal-header alert-primary">
                <h4 class="w-100 text-center text-white">Issue Form<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
            </div>
            <div class="modal-body" id="view_issue_body">
        <?php
        }
        $toNew = (isset($_GET["newPage"]))?",'','".$_GET["newPage"]."'":"";
        ?>
        <div class='text-end'>
            <button type="button" name="view_issues" class="btn btn-outline-primary"  onclick="modal_optional(this,'issue'<?=$toNew?>)">
                View all Issues
            </button>
        </div>
            <!-- Fill Issue Form -->
            <?php 
            echo $result_str;
            echo $prev_issues;
            if(!$reply)
            {
            ?>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="open_status" name="open" value="true" checked>
                    <label class="form-check-label" for="open_status">Open Issue</label><span data-bs-html='true' class='btn ms-0 badge rounded-pill' data-bs-toggle='popover' title='Hints' data-bs-content='- Please fill in Issues you found about specific requests<br> - Issues will be pending until it is closed<br> - Issues opened by you can only be closed by you or System Administrators'><i class='fa fa-info-circle text-primary' title='Details'></i></span>
                </div>
            <?php 
            }
            ?>
            <h6 id="warn_snow" class="text-danger text-center"></h6>
            <div class="my-3">
                <div id="snow" class='bg-white account_Suggestable' style="min-height: 100px; max-height: 200px;" onchange="suggestData(this)"></div>
                <textarea class="form-control d-none" name="issue" id="issue" rows="3" ></textarea>
            </div>
            <div class="my-3">
                <label for="supporting_documents" class="form-label text-pr">Insert Supporting Documents <span class="text-sm">(optional)</span></label>
                <input class="form-control" type="file" name='supporting_documents[]' id='supporting_documents' multiple>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-success" name="submit_issue" value="0" onclick="set_quill(this,'snow','issue')">Submit</button>
            </div>
            <?php
    if(!isset($_GET['newPage']))
    {
    ?>
        </div>
    <?php
    }
    ?>
    <?php
    }
    if(isset($_GET['view_issues']))
    {
        // unset($_SESSION['last_issue']); // incase
        $thead = $tbody = "";
        $roles = ['Admin','Director','Owner'];
        $departments = ['Procurement','Property','Gm'];
        $current_page = (isset($_GET['loadmore']))?$_GET['loadmore']:1;
        $condition = ((in_array($_SESSION['role'],$roles) || in_array($_SESSION['department'],$departments)) && $_SESSION['company'] == 'Hagbes HQ.')?" WHERE 1":((in_array($_SESSION['role'],$roles) || in_array($_SESSION['department'],$departments))?" WHERE company = '".$_SESSION['company']."'":" WHERE user = '".$_SESSION['username']."'");
        $sql = "SELECT * FROM issues $condition AND thread IS NULL";
        if(isset($_GET['search']) && $_GET['search'] != "")
        {
            $sql .= " AND `user` LIKE '%".$_GET['search']."%'";
            $stmt_issues_search = $conn -> prepare($sql);
            $stmt_issues_search -> execute();
            $result = $stmt_issues_search -> get_result();
            $search_result = $result->num_rows;
        }
        $all = "active";
        $open = $closed = "";
        if(isset($_GET['active']))
        {
            if($_GET['active'] == 'Open') {
                $all = "";
                $open = "active";
                $sql .= " AND `status` != 'Closed'";
            }
            else if($_GET['active'] == 'Closed') {
                $all = "";
                $closed = "active";
                $sql .= " AND `status` = 'Closed'";
            }
        }
        // $sql .= " GROUP BY title_id,type";
        $sql .= " Order by timestamp DESC,id DESC";
        $stmt_issues_chosen = $conn -> prepare($sql);
        $stmt_issues_chosen -> execute();
        $result = $stmt_issues_chosen -> get_result();
        $total_pages = ceil(($result->num_rows)/40);
        $total = $result->num_rows;
        $sql .= " LIMIT 40";
        $count = 0;
        if(isset($_GET['loadmore']))
        {
            $load_from = $_GET['loadmore']*40;
            $sql .= " OFFSET $load_from";
            $count += $load_from;
        }
        $stmt_issues_limited = $conn -> prepare($sql);
        $stmt_issues_limited -> execute();
        $result = $stmt_issues_limited -> get_result();
        // $thead='<tr>
        //             <th>#</th>
        //             <th class="text-center">Issue</th>
        //         </tr>';
        if($result->num_rows>0)
        {
            while($row = $result->fetch_assoc())
            {
                $count++;
                $toNew = (isset($_GET["newPage"]))?",'','".$_GET["newPage"]."'":"";
                $color = ($row['status'] == "Closed")?"danger":"success";
                $issue_status = "<span class='badge bg-$color ms-2'>".$row['status']."</span>";// <div class='mt-1 d-inline'></div>
                $issue_close = "";
                if($row['status'] == "Closed")
                {
                    $issue_close = " - <span title='".date('d-M-Y H:i', strtotime($row['close_timestamp']))."' class=''>".date('d-M-Y', strtotime($row['close_timestamp']))."</span>";
                }
                $tbl = $row['type'];
                $for_id = $row['title_id'];
                $system_issue = $row['system'];
                $result_str = get_request($tbl,$for_id,"",$system_issue);
            $all_data = $row['issue']; // text-primary
            $lowername = strtolower($_SESSION['username']);
            $mentioned = strpos($row['issue'],"@".$lowername) !== false;
            if($mentioned)
            {
                $opperation = "Issue-Mention:".$lowername;
                $sql_mention = "SELECT * FROM recorded_time WHERE opperation = ? and for_id = ?";
                $stmt_mention = $conn -> prepare($sql_mention);
                $stmt_mention -> bind_param("si", $opperation, $row['id']);
                $stmt_mention -> execute();
                $result_mention = $stmt_mention -> get_result();
                if($result_mention->num_rows > 0)
                {
                    $row_mention = $result_mention->fetch_assoc();
                    $opperation = "Issue-Mention:".$lowername."-Seen";
                    $sql_update_mention = "UPDATE recorded_time SET `opperation` = ? WHERE `id` = ?";
                    $stmt_update_mention = $conn->prepare($sql_update_mention);
                    $stmt_update_mention -> bind_param("si", $opperation, $row_mention['id']);
                    $stmt_update_mention -> execute();
                }
                else $mentioned = false;
            }
            $short_issue = strip_tags($all_data); 
            $color = ($row['status'] == "Closed")?"danger":"success";
            $issue_status = "<span class='badge bg-$color ms-2'>".$row['status']."</span>";
            $tbody .= "
            <tr>
            <td data-bs-toggle='collapse' data-bs-target='#details_$row[id]' aria-expanded='false' aria-controls='details_$row[id]' onclick='adjust(this,\"collapse\",\"collapse\")'>
            <i class='fas fa-angle-down'></i><span class='visually-hidden'>view more</span> $count</td>
            <td>$row[user]</td>
            <td>".get_request($tbl,$for_id,"item",$system_issue)."</td>
            <td class='text-truncate' style='max-width: 250px;cursor: pointer;' data-bs-toggle='collapse' data-bs-target='#details_$row[id]' aria-expanded='false' aria-controls='details_$row[id]' onclick='adjust(this,\"collapse\"".(($mentioned)?",\"delete\")'><span><i class='fas fa-dot-circle text-success me-1' title='Mentioned'></i></span>":")'>")."$short_issue</td>
            <td>$row[timestamp]</td>
            <td>$issue_status</td>
            <td>
            ".(($row['status'] != "Closed")?"
                <button value='".$row['id']."' onclick=\"modal_optional(this,'issue'$toNew)\" type='button' name='reply' class='btn btn-sm ms-1'>
                    <i class='fas fa-reply text-primary'></i>
                </button>":"")."
            ".(($row['status'] != "Closed" && ($_SESSION['username'] == $row['user'] || $_SESSION['role'] == 'Admin'))?"<button class='btn btn-outline-danger btn-sm' type='button' onclick='prompt_confirmation(this)' name='close_issue' value='".$row['id']."'>Close</button>":"")."
        </td>
            </tr>
            <tr class='collapse' id='details_$row[id]'>
            <td colspan='7'>
            <div class='col-sm-12 mx-auto py-1 row list-group-item-primary rounded my-2'>
                $result_str";
            // $count++;
            $color = ($row['status'] == "Closed")?"danger":"success";
            $issue_status = "<span class='badge bg-$color ms-2'>".$row['status']."</span>";// <div class='mt-1 d-inline'></div>
            $issue_close = "";
            if($row['status'] == "Closed" && !is_null($row['close_timestamp']))
            {
                $issue_close = " - <span title='".date('d-M-Y H:i', strtotime($row['close_timestamp']))."' class=''>".date('d-M-Y', strtotime($row['close_timestamp']))."</span>";
            }
            $tbl = $row['type'];
            $for_id = $row['title_id'];
        $all_data = $row['issue']; // text-primary
        $tbody .= "
        <div class='mx-auto mb-2 '><!-- col-sm-12 col-md-5  -->
            <div class='bg-white rounded p-3'>
                <div class='container bg-light py-2'>
                <h6 class='font-bold text-sm d-inline float-end text-secondary mb-1'><span title='".date('d-M-Y H:i', strtotime($row['timestamp']))."'>".date('d-M-Y', strtotime($row['timestamp']))."</span></h6>
                <div class='d-flex align-items-center'>
                    <div class='avatar avatar-sm'>
                        <img src='../assets/images/faces/1.jpg' alt=''>
                    </div>
                    <div class='ms-3 name pt-2'>
                        <h6 class='font-bold'>".str_replace("."," ",$row['user'])." $issue_status</h6>
                    </div> 
                </div>
                <div class='d-flex justify-content-between align-items-start mb-1'>
                    <div class='ms-2 me-auto'>
                    $all_data
                    </div>
                    ".(($row['status'] != "Closed")?"
                        <button value='".$row['id']."' onclick=\"modal_optional(this,'issue'$toNew)\" type='button' name='reply' class='btn btn-sm ms-1'>
                            <i class='fas fa-reply text-primary'></i>
                        </button>":"")."
                    ".(($row['status'] != "Closed" && ($_SESSION['username'] == $row['user'] || $_SESSION['role'] == 'Admin'))?"<button class='btn btn-outline-danger btn-sm' type='button' onclick='prompt_confirmation(this)' name='close_issue' value='".$row['id']."'>Close</button>":"")."
                </div>";
                if (!is_null($row['supporting_documents']) && $row['supporting_documents'] != '') {
                    $tbody .= "
                    <div class='row gallery noPrint'>
                    <h6 class='text-center'>Pictures/PDF</h6>";
                    $allfiles = explode(':_:', $row['supporting_documents']);
                    foreach ($allfiles as $file) {
                        if (strpos($file, 'pdf')) {
                            $tbody .= "
                                <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                    <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                </div>";
                        } else {
                            $tbody .= "
                                <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                    <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' >
                                        <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                    </a>
                                </div>";
                        }
                    }
                    $tbody .= '</div>';
                }
                $tbody .= '</div>';
        $sql_thread = "SELECT * FROM issues WHERE thread = ? order by timestamp DESC,id DESC";
        $stmt_issues_thread = $conn -> prepare($sql_thread);
        $stmt_issues_thread -> bind_param("i", $row['id']);
        $stmt_issues_thread -> execute();
        $result_thread = $stmt_issues_thread -> get_result();
        if($result_thread->num_rows>0)
        {
            $id_collapse = $collapser = "";
            if($result_thread->num_rows > 1)
            {
                $id_collapse = "<div class='collapse' id='prev_$row[id]'>";
                $collapser = " data-bs-toggle='collapse' data-bs-target='#prev_$row[id]' aria-expanded='false' aria-controls='prev_$row[id]'";
            }
            $tbody .= "<div class='divider my-0 fw-bold'><div class='divider-text'>Responses</div></div>";
            $count_thread = 0;
            while($row_thread = $result_thread->fetch_assoc())
            {
                $count_thread++;
                if($count_thread==2)
                {
                    $tbody .= $id_collapse;
                }
                $color = ($row_thread['status'] == "Closed")?"danger":"success";
                $issue_status = "<span class='badge bg-$color ms-2'>".$row_thread['status']."</span>";// <div class='mt-1 d-inline'></div>
                $all_data = $row_thread['issue']; // text-primary
                $tbody .= "
                <div class='container bg-light py-2'>
                    <h6 class='font-bold text-sm d-inline float-end text-secondary mb-1'><span title='".date('d-M-Y H:i', strtotime($row_thread['timestamp']))."'>".date('d-M-Y', strtotime($row_thread['timestamp']))."</span></h6>
                    <div class='d-flex align-items-center'>
                        <div class='avatar avatar-sm'>
                            <img src='../assets/images/faces/1.jpg' alt=''>
                        </div>
                        <div class='ms-3 name pt-2'>
                            <h6 class='font-bold'>".str_replace("."," ",$row_thread['user'])."</h6>
                        </div> 
                    </div>
                    <div class='d-flex justify-content-between align-items-start mb-1'>
                        <div class='ms-2 me-auto'>
                        $all_data
                        </div>
                        <!-- used to have a close button -->
                    </div>";
                    if (!is_null($row_thread['supporting_documents']) && $row_thread['supporting_documents'] != '') {
                        $tbody .= "
                        <div class='row gallery noPrint'>
                        <h6 class='text-center'>Pictures/PDF</h6>";
    
                        $allfiles = explode(':_:', $row_thread['supporting_documents']);
                        foreach ($allfiles as $file) {
                            if (strpos($file, 'pdf')) {
                                $tbody .= "
                                    <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                        <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                                    </div>";
                            } else {
                                $tbody .= "
                                    <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                        <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' >
                                            <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                        </a>
                                    </div>";
                            }
                        }
                        $tbody .= '</div>';
                    }
                    $tbody .= '</div>';
            }
            $tbody .= ($count_thread>1)?"</div><div class='divider my-0 fw-bold' $collapser><div class='divider-text'><div class='badge alert-primary' onclick='adjust(this)'><span>View more </span><i class='fas fa-angle-down'></i></div></div></div>":"";
        }
        $tbody .= "
        </div>
        </div>";
    // }
    // }
    $tbody .= "
    </div>
    </td>
    </tr>";
            }
        }
        if($tbody == "") $tbody = "<tr><td colspan='7'><div class='alert-primary mt-3 px-0 py-2'><div class='text-center alert-secondary fw-bold p-3'>No Issues Given</div></div></td></tr>"; 
        if(isset($_GET['loadmore']) || (isset($_GET['search']) && $_GET['search'] != "")) echo $tbody;
        else
        {
    if(!isset($_GET['newPage']))
    {
    ?>
    <div class="modal-header alert-primary">
        <?php if(isset($_SESSION['last_issue'])){?>
        <button type="button" value='<?=$_SESSION['last_issue']?>' class="btn btn-outline-primary" name="issue" onclick="modal_optional(this,'issue')"><i class='bi bi-arrow-left'></i> To</button>
        <?php }?>
        <h4 class="w-100 text-center text-white">Issue Form<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
    </div>
    <div class="modal-body" id="view_Issue_body">
    <?php
    }
    $toNew = (isset($_GET["newPage"]))?",'','".$_GET["newPage"]."'":"";
    $toNew2 = (isset($_GET["newPage"]))?',"'.$_GET["newPage"].'"':"";
    $toNew3 = (isset($_GET["newPage"]))?',\"'.$_GET["newPage"].'\"':"";
    ?>
    <?php if(isset($_SESSION['last_issue']) && isset($_GET['newPage'])){?>
    <button type="button" value='<?=$_SESSION['last_issue']?>' class="btn btn-outline-primary" name="issue" onclick="modal_optional(this,'issue'<?=$toNew?>)"><i class='fas fa-arrow-left'></i> To request</button>
    <?php 
        // unset($_SESSION['last_issue']); // incase
    }?>
<button type="button" value='Other Issue' class="btn btn-primary btn-sm float-end" name="issue" onclick="modal_optional(this,'issue'<?=$toNew?>)">Raise Issue for Other Systems</button>
                <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 mx-auto">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <button class="nav-link <?=$all?> position-relative" name="view_issues" type="button" onclick="modal_optional(this,'issue'<?=$toNew?>)">All <span class="badge bg-info ms-1"></span></button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link <?=$open?> position-relative" name="view_issues" type="button" value="Open" onclick='modal_optional(this,"issue","active"<?=$toNew2?>)'>Open <span class="badge bg-success ms-1"></span></button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link <?=$closed?> position-relative" name="view_issues" type="button" value="Closed" onclick='modal_optional(this,"issue","active"<?=$toNew2?>)'>Closed <span class="badge bg-danger ms-1"></span></button>
                    </li>
                </ul>
            </div>
            <div id='Issues' class="row mx-auto mt-3">
                <table class="table">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Issue By</th>
                            <th>Item</th>
                            <th>Details</th>
                            <th>Open date</th>
                            <th>Status</th>
                            <th>Operation</th>
                        </tr>
                    </thead>
                    <tbody id='Issues_holder'>
                    <?= $tbody?>
                    </tbody>
                </table>
                <!-- <div id='Issues_holder' class="row">
                    <?php //echo $tbody;?>
                </div> -->
                <?=($current_page<$total_pages)?"<div class='text-center' id='loadmore_btn'><button class='btn btn-primary' id='loadmore_Issue' name='view_issues' data-max='$total_pages' type='button' value='".($current_page)."' onclick='modal_optional(this,\"Issue\",\"loadmore\"$toNew2)'>Load More</button></div>":""?>
            </div>
            <?php
            if(!isset($_GET['newPage']))
            {
            ?>
                </div>
            <?php
            }
        }
    }
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