<?php
if(!strpos($_SERVER['PHP_SELF'],'rocurement'))
    echo "<script> var pos = '../';</script>";
else
    echo "<script> var pos = '';</script>";
$pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
function take_data($tbl_data,$rrow,$type)
{
    $na_t=str_replace(" ","",$type);
    $printpage = "<button type='button' title='item' class='btn btn-outline-secondary border-0 float-end' name='print_".$na_t."_".$rrow['request_id']."' onclick='print_page(this)'>
    <i class='text-dark fas fa-print'></i>
    </button>";
    $tbl_data.=($tbl_data != "")?"==":"";
    $item_container = "<button type='button'  title='".$rrow['recieved']."' name='specsfor_".$na_t."_".$rrow['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$rrow['item']."
    </button>".$printpage;
    $tbl_data.= $rrow['customer'].",".$item_container.",".$type.",".$rrow['department'].",".date("d-M-Y", strtotime($rrow['date_requested'])).","
    .date("d-M-Y", strtotime($rrow['date_needed_by'])).",".$rrow['status'];
    return $tbl_data;
}
 
function divcreate_requests_page($strr)
{
    return 
    "
        <form method='GET' action='allphp.php'>
            $strr
        </form> 
    ";
}
function createGraph($r,$c,$id)
{
    return "<script>
        var options$id = {
            annotations: {
                position: 'back'
            },
            dataLabels: {
                enabled:true
            },
            chart: {
                type: 'bar',
                height: 300
            },
            fill: {
                opacity:1
            },
            plotOptions: {
            },
            series: [{
                name: 'Requests',
                data: [$r]
            }],
            colors: '#435ebe',
            xaxis: {
                categories: [$c],
            },
        }
        var chartProfileVisit$id = new ApexCharts(document.querySelector('#$id'), options$id);
        chartProfileVisit$id.render();
    </script>";
}
function create_piechart($data,$id)
{
return
    "<script>
    document.addEventListener('DOMContentLoaded', () => {
        echarts.init(document.querySelector('#$id')).setOption({
        title: {
            text: 'Requests by Company',
            subtext: 'Raw Data',
            left: 'center'
        },
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [{
            name: 'Access From',
            type: 'pie',
            radius: '50%', // size of Piechart
            data: [
                $data
            ],
            emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
            }
        }]
        });
    });
    </script>";
}
function count_dep_req($comp = "")
{
    global $conn;
    $constraints_dep = "";
    $requests_dep = "";
    $sql2 = "SELECT * FROM department";
    $stmt_department = $conn->prepare($sql2); 
    $stmt_department->execute();
    $result = $stmt_department->get_result();
    if($result->num_rows>0)
    {
        while($row = $result->fetch_assoc())
        {
            $once = true;
            $dep_req[$row['Name']] = 0;
            $comp = ($comp == "")?$_SESSION['company']:$comp;
            $sql_requests_count = "SELECT count(*) as `c_r` FROM requests where department = ? AND company = ?";
            $stmt_requests_count = $conn->prepare($sql_requests_count);
            $stmt_requests_count -> bind_param("ss", $row['Name'], $comp);
            $stmt_requests_count -> execute();
            $result_requests_count = $stmt_requests_count->get_result();
            while($row2 = $result_requests_count->fetch_assoc())
            {
                if($row2['c_r']>0)
                {
                    if($once)
                        $constraints_dep .= ($constraints_dep == "")?"'".$row['Name']."'":",'".$row['Name']."'";
                    $dep_req[$row['Name']] = $dep_req[$row['Name']]+$row2['c_r'];
                    $once = false;
                }
            }
            if($dep_req[$row['Name']] != 0)
                $requests_dep .= ($requests_dep == "")?$dep_req[$row['Name']]:",".$dep_req[$row['Name']];
        }
    }
    return [$requests_dep,$constraints_dep];
}
?>
<div class='noPrint'>

    <div class="modal fade" id="pay_requ">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                    <div class="modal-header alert-primary">
                        <h4 class="w-100 text-center text-white">Cheque Information <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="pay_requ_body">
                    </div>
                    <!-- <div class="modal-footer border-0">
                        <form method='GET' action="<?php echo $pos?>../finance/allphp.php"> <button class='mx-auto btn btn-outline-success btn-sm mb-3' id='create_cheque_modal' type='button'  onclick='prompt_confirmation(this)'>Cheque Created</button></form>
                    </div> -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="item_details">
        <div class="modal-dialog modal-xl"><!-- modal-fullscreen-->
            <div class="modal-content">
                    <div class="modal-header alert-primary" id='view_full_detail'>
                        <h4 class="w-100 text-center text-white">Request Details<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="item_details_body">
                    </div>
                <div class="modal-footer noPrint">
                    <form method="GET" action="allphp.php">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <ul id='optional_btn'>
                    </form>
                </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="view_optionalModal">
        <div class="modal-dialog modal-xl"><!-- modal-fullscreen-->
            <div class="modal-content">
                <form method="POST" action="<?=$pos?>../requests/allphp.php" id='view_optional_detail'>

                </form>
                <div class="modal-footer noPrint">
                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    </script>
    <div class="modal fade" id="view_sms">
        <div class="modal-dialog modal-xl"><!-- modal-fullscreen-->
            <div class="modal-content">
                <form method="GET" action="allphp.php">
                    <div class="modal-header alert-primary" id='view_sms_detail'>
                        <h4 class="w-100 text-center text-white">Your SMS Messages<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="view_sms_body">

                    </div>
                </form>
                <div class="modal-footer noPrint">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="view_issue">
        <div class="modal-dialog modal-xl"><!-- modal-fullscreen-->
            <div class="modal-content">
                <form method="GET" action="allphp.php">
                    <div class="modal-header alert-primary" id='view_issue_detail'>
                        <h4 class="w-100 text-center text-white">Issues<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="view_issue_body">

                    </div>
                </form>
                <div class="modal-footer noPrint">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chat_modal">
        <div class="modal-dialog" id="chat_modal_dialog"><!-- modal-fullscreen-->
            <div class="modal-content">
                    <!-- <h4 class="w-100"><button type="button" class="btn btn-danger border-0 float-end me-2 mt-2" data-bs-dismiss="modal">X</button></h4> -->
                    <div class="modal-body" id="chat_modal_body">
                        <!-- <h4 class="text-center"><i class="text-danger fas fa-exclamation-triangle"></i> Under Maintainance</h4> -->
                    </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="status_info">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Request Status Detail</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
                    <div class="modal-body" id="status_body">
                        <?php 

$requests = array(
    'All Complete'=>"Purchase from request to handover",
    'Approved'=>"Purchase for which committee has approved",
    "All Payment Processed"=>"Purchase for which payment process is done and ready for collection",
    'Approved By Dep.Manager'=>"purchase Approved By Department Manager",
    'Approved By GM'=>"Purchase Approved By GM",
    'Approved By Property'=>"Purchase Approved By Property manager",
    'canceled'=>" The purchases that has been canceled",
    'Cheque Prepared'=>"Purchase for which check has been prepared",
    'Collected-not-comfirmed'=>"Item collected but not confirmed by department manager",
    'Committee Approval'=>"Purchase that has reached committee approval",
    'Finance Approved'=>" Purchase approved by financial manager",
    'Finance Approved Petty Cash'=>"Purchaser approved by finance for pettey cash",
    'Found In Stock'=>"Item that was found in stock",
    'Generating Quote'=>"Item for that peroforma is being collected",
    'In-Stock'=>"Item has been received by property department",
    'Payment Processed'=>"Payment has been processed and purchase ready for collection",
    'Petty Cash'=>"Payment is being processed by petty cash",
    'Petty Cash Approved'=>"Petty cash has been approved by financial manager",
    'Recollect'=>"Item has been sent for recollection",
    'Rejected By Dep.Manager'=>"Purchase that has been rejected by department manager",
    'Rejected By GM'=>"Purchase that has been rejected by GM",
    'Rejected By Director'=>"Purchase that has been rejected by director",
    'Rejected By Owner'=>"Purchase that has been rejected by owners",
    'Rejected By Property'=>"Purchase that has been rejected by property manager",
    'Reviewed'=>"Purchase that has been reviewed by finance department",
    'Sent to Finance'=>"Purchase sent to finance by Procurnment manager",
    'waiting'=>"Purchase waiting for Department manager approval"
  );
  $detail="";
  $x=0;
foreach($requests as $key=>$value){
    $detail.="<tr>
    <th scope='row'>".(++$x)."</th>
    <td><b>$key</b></td>
    <td>$value</td>
  </tr>";
}
// echo "<table border='2'><thead><td>Status</td><td>Description</td></thead><tbody>".$detail."</tbody></table>";
echo "<table class='table'>
<thead>
  <tr>
    <th scope='col'>N<u>o</u></th>
    <th scope='col'>Status</th>
    <th scope='col'>Description</th>
  </tr>
</thead>
<tbody>
$detail
</tbody>
</table>";
?>
                    </div>
                    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"  data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick='printStatus()'>print</button>
      </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="purchase_requisitions">
        <div class="modal-dialog modal-xl"><!-- modal-fullscreen-->
            <div class="modal-content">
                <form method="GET" action="allphp.php">
                    <div class="modal-header alert-primary" id='purchase_requisitions_view'>
                        <h4 class="w-100 text-center text-white">Purchase Requisition Form<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="purchase_requisitions_body">
                        ..
                    </div>
                </form>
                <div class="modal-footer noPrint">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<form method="GET" action="<?php echo $pos?>../Committee/allphp.php" id='form_committee'>
<input id='alt_form_committee' name='current_scale' class="d-none">
    <div class="modal fade" id="comp_sheet">
        <div class="modal-dialog modal-fullscreen"><!-- modal-fullscreen-->
            <div class="modal-content">
                    <div class="modal-header alert-primary">
                        <h4 class='my-2 w-100 text-center text-white'>Comparision Sheet<button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="comp_sheet_body" style = 'overflow:scroll'>
                        <!-- Company And Items Form -->
                    </div>
                    
                    <div class="modal-footer noPrint">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <div id='comp_sheet_footer_btn'>
                         
                    </div>
                    </div>
            </div>
        </div>
    </div>

    <div class='modal fade' id='reason_committee'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h3 id='top_text' class="w-100 text-center">Remark <span class='small text-secondary'>(optional)</span>
                    <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h3>
                </div>
                <div class='modal-body' id='reason_body'>
                    <textarea class='w-100' rows='2' name='reason'></textarea>
                    <button class='form-control btn btn-outline-success mt-3' id='btn_committee_reason'>Proceed</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approval_progress">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- <form method="GET" action="allphp.php"> -->
                    <!-- <div class="modal-header">
                        <h3 id='top_text'>Status</h3>
                        <button type="button" class="btn btn-danger border-0" data-bs-dismiss="modal"></button>
                    </div> -->
                    <div class="modal-body" id="approval_progress_body">
                        
                    </div>
                    <div class="modal-footer border-0">
                        <div id='app_progress_footer_btn'>
                                
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                <!-- </form>  -->
            </div>
        </div>
    </div>
</form> 

    <!-- <form method="GET" action="allphp.php">
        <div class='modal fade' id='comfirm_delete'>
            <div class='modal-dialog'>
                <div class='modal-content rounded-pill'>
                    <div class='modal-header flex-column border-0 mt-3'>
                        <div class="icon-box">
                            <i class="fa fa-trash-alt text-danger fa-5x"></i>
                        </div>                      
                    <h4 class="modal-title w-100 text-center my-3">Are you sure?</h4>   
                    </div>
                    <div class='modal-body text-center' id='comfirm_delete_body'>
                        <p class='px-5 text-secondary'>Do you really want to delete this Item? This process cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pb-5">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-lg" id="del_btn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </form> -->
    <div class="modal fade" id="edit_vendor">
    <div class="modal-dialog modal-lg shadow">
        <div class="modal-content rounded-5">
            <form method="POST" action="allphp.php">
                <div class="modal-header alert-primary">
                    <h4 class="modal-title text-light">Edit vendor</h4>
                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                </div>
                <div class="modal-body" id="editvendor">

                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="submit" class='btn btn-outline-primary mx-auto' name="edit_vendor">Update Vendor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profilemodal">
    <div class="modal-dialog modal-lg shadow">
        <form method="POST" action="<?php echo $pos?>../common/allphp.php">
            <div class="modal-content rounded-5">
                <div class="modal-header alert-primary">
                    <h4 class="modal-title text-light w-100 text-center">Edit Profile<button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button></h4>
                </div>
                <div class='modal-body'>
                    <div class='row w-100 mb-3'>
                        <h6 class='text-center'>
                            <i class='text-primary'>Company  -  </i><?=$_SESSION['company']?>, <i class='text-primary'>Department  -  </i><?=$_SESSION['department']?>
                        </h6>
                        <h6 class='text-center'>
                            <i class='text-primary'>Position  -  </i><?=$_SESSION['position']?>
                        </h6>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input id='username' name='username' type="text" class="form-control form-control-xl" value='<?php echo $_SESSION['username']?>' readonly>
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control rounded-4" id="firstName" name='firstName' value="<?=explode(".",$_SESSION['name'])[0]?>" required>
                            <label for="firstName">First Name</label>
                        </div>
                        <div class="col-sm-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control rounded-4" id="lastName" name='lastName' value="<?=(sizeof(explode(".",$_SESSION['name']))>1)?explode(".",$_SESSION['name'])[1]:explode(".",$_SESSION['name'])[0]?>" required>
                            <label for="lastName">Last Name</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">+251</span>
                        </div>
                        <input type="text" name="phone" id="update_phone" value="<?=str_replace("+251","",$_SESSION['phone_number'])?>" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9" required/>
                    </div>
                    <small class="text-secondary">Format : +251993819775</small>    
                    <div class="form-floating my-3">
                        <input type="email" class="form-control rounded-4" id="update_email" name='email' value="<?=$_SESSION['email']?>" required>
                        <label for="update_email">Email</label>
                    </div>
                </div>
                <div class="modal-footer">
                <!-- onclick="chanagepass(this)" -->
                    <button type='submit' class="btn btn-outline-success btn-sm shadow" name='change_profile'>Change Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="modal fade" id="passmodal">
    <div class="modal-dialog modal-lg shadow">
        <form method="POST" action="<?php echo $pos?>../common/allphp.php">
            <div class="modal-content rounded-5">
                <div class='modal-header flex-column border-0 mt-3'>
                    <h3 class="w-100 auth-title text-center">Change Password
                        <button type="button" id='ch_pass_close' class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h3>
                </div>
                <div class='modal-body'>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input id='username' name='username' type="text" class="form-control form-control-xl" value='<?php echo $_SESSION['username']?>' readonly>
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input id='oldpass' name='oldpass' type="password" class="form-control form-control-xl" placeholder="Old Password" required>
                        <div class="form-control-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                    </div>
                    <p class="small text-danger text-center" id='change_warnpass'></p>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input id='change_newpass' name='newpass' type="password" class="form-control form-control-xl" placeholder="New Password" required>
                        <div class="form-control-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input title="change" onkeyup="chanagepass(this)" onchange="chanagepass(this)" id='change_confpass' name='confpass' type="password" class="form-control form-control-xl" placeholder="Confirm Password" required>
                        <div class="form-control-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                <!-- onclick="chanagepass(this)" -->
                    <button type='button' title="change" onclick="chanagepass(this)" class="btn btn-outline-success btn-sm shadow" name='changepassword'>Change Password</button>
                </div>
            </div>
        </form>
    </div>
    </div>
    <form method="POST" action="<?php echo $pos?>../Procurement/senior/allphp.php" enctype="multipart/form-data">
        <div class="modal fade " id="view_performa" tabindex="-1" aria-labelledby="view_performaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg ">
                <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title" id="view_performaLabel">
                    <button type="button" class="btn btn-secondary" id='back_from_performa' data-bs-toggle="modal" data-bs-target="#item_details" ><i class='bi bi-arrow-left'></i></button>
                     View Proforma</h5>
                    <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body bg-primary bg-opacity-25" id='view_performa_body'>
                    
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name='insert_performa' id='insert_performa' class="btn btn-primary">Upload</button>
                </div> -->
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="documents">
        <div class="modal-dialog modal-xl shadow">
            <div class="modal-content rounded-5" id="documents_details">
                <!-- Display of files like Documents and SMS usage -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="company_select">
        <div class="modal-dialog modal-sm shadow">
            <div class="modal-content rounded-5">
                <form method="GET" action="allphp.php">
                    <div class="modal-header">
                        <h4 class="modal-title w-100 text-center">Send To
                            <!-- <button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button> -->
                        </h4>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mx-auto form-floating mb-3">
                            <select class="form-select" name="company_selector"  id="comp_selector" required>
                                <option value="">-- Select one --</option>
                                <?php
                                    $stmt_all_company->execute();
                                    $result = $stmt_all_company->get_result();
                                    if($result->num_rows>0)
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                                        }
                                    }
                                ?>
                            </select>
                            <label for="company">Selet Proper Company</label>
                        </div>
                        <button class="btn btn-success mx-auto" type="button" onclick="prompt_confirmation(this)" name="share_comp" id="share_comp">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="user_select">
        <div class="modal-dialog modal-sm shadow">
            <div class="modal-content rounded-5">
                    <div class="modal-header">
                        <h4 class="modal-title w-100 text-center">Privilage to Login With
                            <!-- <button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button> -->
                        </h4>
                    </div>
                    <div class="modal-body text-center">
                        <form method="POST" class="d-inline" action="<?=$pos?>../Admin/allphp.php">
                            <div class="mx-auto form-floating mb-3">
                                <select class="form-select" name="admin-login"  id="admin-login" required>
                                    <option value="">-- Select one --</option>
                                    <?php
                                        $sql_users = "SELECT Username FROM `account` order by Username";
                                        $stmt_users = $conn->prepare($sql_users);
                                        $stmt_users -> execute();
                                        $result_users = $stmt_users -> get_result();
                                        if($result_users -> num_rows>0)
                                        {
                                            while($row = $result_users -> fetch_assoc())
                                            {
                                                echo "<option value='".$row['Username']."'>".$row['Username']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="company">Selet User</label>
                            </div>
                            <div class="mx-auto form-floating mb-3">
                                <input type="password" class="form-control rounded-4" id="admin-password" name='admin-password'>
                                <label for="admin-password">Admin Password</label>
                            </div>
                            <?=(!isset($_SESSION['attempt-admin-pass']))?"":"<p class='text-sm text-danger mb-3'>Failed Attempts - ".$_SESSION['attempt-admin-pass']."</p>"?>
                            <button class="btn btn-success mx-auto" type="button" onclick="prompt_confirmation(this)" name="set-admin-login" id="set-admin-login">Login</button>
                        </form>
                        <form method="POST" class="d-inline" action="<?=$pos?>../Admin/allphp.php">
                            <button class="btn btn-primary mx-auto" type="button" onclick="prompt_confirmation(this)" name="reset-login" id="reset-login">Reset</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="EditModal">
        <div class="modal-dialog modal-lg shadow">
            <div class="modal-content rounded-5">
                <form method="GET" action="allphp.php">
                    <div class="modal-header alert-primary">
                        <h4 class="modal-title text-light w-100 text-center">Edit Purchase Order<button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button></h4>
                    </div>
                    <div class="modal-body" id="toedit">
                    
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit" id="edit_request">Edit Request<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCompModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="allphp.php"  enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title w-100 text-center" id='ttl'>Fill Comparision Sheet
                            <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h4>
                    </div>
                    <div class="modal-body" id="createCompModal_body">
                        <!-- Company And Items Form -->
                    </div>
                    <div class="modal-footer">
                        <div id="vat_holder">
                            <div class='form-group float-start d-flex d-none' id="vat_sample">
                                <label id='vat_lbl::0'></label>
                                <input type="hidden" class="vats" id='vat_for::0' name='vat_for[]'>
                                <div class='form-group'>
                                <select class='form-select' name='vat_item[]' id='vat_item::0' required>
                                    <?php
                                    $sql = "SELECT * FROM `tax`";
                                    $stmt_taxes = $conn->prepare($sql); 
                                    $stmt_taxes -> execute();
                                    $tax_res = $stmt_taxes -> get_result();
                                    while($tax_row = $tax_res -> fetch_assoc())
                                        echo "<option value='".$tax_row['value']."'>".$tax_row['tax_name']." - ".($tax_row['value']*100)."%</option>"; 
                                    ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class='form-group' id='remark'> 
                            <label for='remarks_creator' >Remarks and recommendations on Comparision Sheet</label><!-- <small class='text-secondary'> ( Optional )</small> -->
                            <textarea placeholder="Remarks and recommendations on Comparision Sheet" class='form-control border border-success outline-success' id='remarks_creator' style="width: 27rem; margin-left: -1px;margin-right: 21px;" onload="getRemark()" rows='2' cols='12' name='remarks_creator' required></textarea>
                        </div>
  
                        <div class="mb-3">
                            <label for="p_files" class="form-label">Insert Proforma</label>
                            <input id='p_files'type='file' id='performa_data' class='form-control multiple-files-filepond ms-0' name='performa[]' multiple required>
                        </div>
                        <button class="btn btn-primary" type="button" name="generate" id="generate"  onclick="final_send(this)">Generate</button> 
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<form method="POST" action="<?php echo $pos?>../Procurement/senior/allphp.php" enctype="multipart/form-data">
<div class="modal fade" id="modal_performa" tabindex="-1" aria-labelledby="modal_performaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center" id="modal_performaLabel">Upload Proforma
        <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal" aria-label="Close">X</button></h5>
      </div>
      <div class="modal-body">
            <input type='file' class='multiple-files-filepond' name='performa[]' multiple required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-toggle='modal' data-bs-target='#view_performa' >Back</button><!--  data-bs-dismiss="modal" -->
        <button type="submit" name='insert_performa' id='insert_performa' class="btn btn-primary">Upload</button>
      </div>
    </div>
  </div>
</div>
</form>
<div class="modal fade" id="give_reason">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" id='form_reasons'>
                <div class="modal-header">
                    <h3 id='top_text' class="w-100 text-center">Reason/ Remark <span class='small text-secondary' id='rem_optional'>(optional)</span>
                    <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal">X</button></h3>
                </div>
                <div class="modal-body" id="reason_body">
                    <!-- Company And Items Form -->
                    <textarea class='w-100' rows='2' id='reason_field' name='reason'></textarea>
                    <button class='form-control btn btn-outline-success mt-3' id='reason_btn'>Proceed</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<div class="modal fade" id="email_modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#vendor_select"><i class='bi bi-arrow-left'></i></button>
            </div>
            <div class="modal-body" id="email_modal_body">
                
            </div>
            <!-- <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
            </div> -->
        </div>
    </div>
</div>
    <div class="modal fade" id="warning">
        <div class="modal-dialog modal-xl shadow">
            <div class="modal-content rounded-5" id="warning_details">
                <div class="modal-header">
                    <h4 class="modal-title w-100 text-center"><i class="fas fa-exclamation text-warning"></i>Warning<button type='button' class='btn btn-danger border-0 float-end' data-bs-dismiss='modal'>X</button></h4>
                </div>
                <div class="modal-body">
                    <h4 class='text-danger text-center'> Please Be Aware the system will be momentarily under maintainance on 10:30 Local Time</h4>
                </div>
            </div>
        </div>
    </div>
    <button id='warning_btn' class='d-none' data-bs-toggle='modal' data-bs-target='#warning'></button>
<form action="<?php echo $pos?>../Procurement/junior/allphp.php" method='POST' enctype="multipart/form-data">
    <div class="modal fade" id="fleetModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header list-group-item-primary">
                    <h3 class="modal-title w-100 text-center" id="fleetModal_Label">Vehicle Requisition Form
                    <button type="button" class="btn btn-danger border-0 float-end" data-bs-dismiss="modal" aria-label="Close">X</button> </h3>
                </div>
                <div class="modal-body" id="fleetModal_body">
                    <div class='container mx-auto' id='fleetModal_form'>

                    </div>
                </div>
                <div class="modal-footer border-0 text-center">
                    <button class="btn btn-primary mx-auto" type="submit" name="submit_vehicle_request">Request Vehicle</button>
                </div>
            </div>
        </div>
    </div>
</form>

</div>
<script>
   
let spinners = "<div class='row'><div class='mx-auto spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div></div>";
function load_quill(type = "snow")
{
    if(type == "snow" || type == "All")
    {
        var snow = new Quill('#'+type, {
            theme: type
        });
    }
    if(type == "bubble")
    {
        var bubble = new Quill('#'+type, {
            theme: type
        });
    }
    if(type == "full" || type == "All")
    {
        new Quill("#full", { 
        bounds: "#full-container .editor", 
        modules: { 
            toolbar: [
                [{ font: [] }, { size: [] }], 
                ["bold", "italic", "underline", "strike"], 
                [
                    { color: [] }, 
                    { background: [] }
                ], 
                [
                    { script: "super" }, 
                    { script: "sub" }
                ], 
                [
                    { list: "ordered" }, 
                    { list: "bullet" }, 
                    { indent: "-1" }, 
                    { indent: "+1" }
                ], 
                ["direction", { align: [] }], 
                ["link", "image", "video"], 
                ["clean"]] 
            }, 
            theme: "snow" 
        })
    }
    // $('.account_Suggestable .ql-editor').on('input', function() {
    //     let inputed = $(this).text().slice(-1);
    //     if(inputed == "@") getSuggestion();
    // });
}
function removeItems(content,type = "class",index = "All")
{
    if(type == "class")
    {
        let items = document.getElementsByClassName(content);
        if(index == "All")
        {
            for(let i = 0;i<items.length;i++)
            {
                items[i].remove();
            }
        }
        else items[index].remove();
    }
    if(type == "id")
        document.getElementById(content).remove();
}
var quill_on = false;
function modal_optional(e,modal_for,others = "",newPage = "")
{
    let loadto = (newPage == "")?"view_optional_detail":newPage;
    let temp = "";
    if(others == 'loadmore')
    {
        temp = e.innerHTML;
        e.innerHTML = spinners;
    }
    else
    {
        document.getElementById(loadto).innerHTML=spinners;
    }
    let temp_val = (e.value == "" || others != "")?"a":e.value;
    let sent = (others != "")?"&"+others+"="+e.value:"";
    sent += (newPage != "")?"&newPage="+newPage:"";
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        if(others == 'loadmore')
        {
            if(modal_for =='Issue')
            {
                document.getElementById("Issues_holder").innerHTML+=this.responseText;
                document.getElementById("loadmore_"+modal_for).value = parseInt(e.value)+1;
            }
            else
            {
                document.getElementById(modal_for+"_tblbody").innerHTML+=this.responseText;
                document.getElementById("loadmore_"+modal_for).value = parseInt(e.value)+1;
            }
            e.innerHTML = temp;
        }
        else
        {
            let data_returned = this.responseText.split(":|-|:");
            document.getElementById(loadto).innerHTML = data_returned[0];
            if(document.getElementById("basic"))
            {
                window.raterJs({
                    element: document.querySelector("#basic"), 
                    starSize: 32,
                    rateCallback:function rateCallback(rating, done) {
                        this.setRating(rating); 
                        done(); 
                    }
                });
            }
            if(!quill_on)
            {
                load_quill();
                removeItems("ql-link");
                // quill_on = true;
            }
            // datatable();
        if(data_returned.length>1)
        {
            document.getElementById('snow').children[0].innerHTML = data_returned[1];
            let rate_value = parseInt(data_returned[2]);
        }
        }
        if(document.getElementById("loadmore_"+modal_for))
        {
            let loadmore_feedback = document.getElementById("loadmore_"+modal_for);
            if(parseInt(loadmore_feedback.value) >= parseInt(loadmore_feedback.getAttribute("data-max")))
                loadmore_feedback.classList.add("d-none"); 
        }
    }
    req.open("GET", pos+"../common/ajax_docs.php?"+e.name+"="+temp_val+sent);
    req.send();
}
function get_quill(content,type = "view")
{
    let opened = (type == "view")?"false":"true";
    let data = '<div class="ql-editor ql-blank" data-gramm="'+opened+'" contenteditable="'+opened+'">'+content+'</div>';
    return data;
}
function set_quill(e,data_from,data_to)
{
    let content = document.getElementById(data_from).children[0].children[0].innerHTML;
    let rating = (document.getElementById("feedback_rating"))?document.getElementById("feedback_rating").value:"empty";
    if(content.trim() != "" && content.trim() != "<br>" && rating != "")
    {
        let ss = document.getElementById(data_from).children[0].innerHTML;
        document.getElementById(data_to).value = ss;
        e.setAttribute('type','submit');
        e.click();
    }
    else
    {
        document.getElementById("warn_snow").innerHTML = "Please fill the entire form correctly";
    }
}
function adjust(e,close="",icon="")
{
    if(close == "")
    {
        if(e.innerHTML.includes("more"))
        {
            e.innerHTML = e.innerHTML.replace("more","less");
            if(icon == "collapse")
                e.children[1].innerHTML = document.getElementById("up-arrow").innerHTML;
            if(icon == "delete")
            {
                e.children[0].remove();
                e.setAttribute("onclick","this,\"collapse\"");
            }
        }
        else
        {
            e.innerHTML = e.innerHTML.replace("less","more");
            if(icon == "collapse")
                e.children[1].innerHTML = document.getElementById("up-arrow").innerHTML;
            if(icon == "delete")
            {
                e.children[0].remove();
                e.setAttribute("onclick","this,\"collapse\"");
            }
        }
    }
    else
    {
        if(e.innerHTML.includes("view more"))
        {
            e.innerHTML = e.innerHTML.replace("more","less");
            if(icon == "collapse")
                e.children[1].innerHTML = document.getElementById("up-arrow").innerHTML;
            if(icon == "delete")
            {
                e.children[0].remove();
                e.setAttribute("onclick","this,\"collapse\"");
            }
        }
        else
        {
            e.innerHTML = e.innerHTML.replace("less","more");
            if(icon == "collapse")
                e.children[1].innerHTML = document.getElementById("up-arrow").innerHTML;
            if(icon == "delete")
            {
                e.children[0].remove();
                e.setAttribute("onclick","this,\"collapse\"");
            }
        }
    }
}
function set_rating(e)
{
var feedback_rate = document.getElementById('feedback_rating');
    time = setTimeout(function(){
        let val = e.getAttribute('data-rating');
        if (val != null && feedback_rate.value != val)
        {
            feedback_rate.value = val;
        }
    }, 1);
}   
        function printStatus() {
            $("#status_info").modal("hide");
            const printContents = document.getElementById('status_body').innerHTML;
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location=''

        }
   
 setInterval(() =>{
    var cluster=(document.getElementById('cluster'))?document.getElementById('cluster').value:"";
    if(cluster && cluster.value!=""){
    document.getElementById('generate').value=cluster;
    document.getElementById('generate').name='edit_comp_sheet'
    const xhr = new XMLHttpRequest();
    xhr.onload = function(){//when the response is ready
        document.getElementById("remarks_creator").innerHTML=this.responseText;
       
        }
        xhr.open("GET","ajax_create_cs.php?remark_cluster="+cluster);
        xhr.send();
    }
},1000);

 
var temp_chat_data = "",msg_recieved="";
function load_chat(e)
{
    if(e == 'Userlist')
    {
///////////////////////////////////////////////////////////////////////////////////////////////////////
const searchBar = document.querySelector(".search input"),
        searchIcon = document.querySelector(".search button"),
        chatheader=document.querySelector(".users header"),
        usersList = document.querySelector(".users-list");
        searchBar.classList.toggle("show");
        searchIcon.onclick = ()=>{
        searchIcon.classList.toggle("active");
        searchBar.focus();
        if(searchBar.classList.contains("active")){
            searchBar.value = "";
            searchBar.classList.remove("active");
        }
        }
        searchBar.onkeyup = ()=>{
        let searchTerm = searchBar.value;
        if(searchTerm != ""){
            searchBar.classList.add("active");
        }else{
            searchBar.classList.remove("active");
            chat_box();
        }
        let xhr = new XMLHttpRequest();
        xhr.open("POST", pos+"../chat/search.php", true);
        xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                let data = xhr.response;
                usersList.innerHTML = data;
                }
            }
        }
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("searchTerm=" + searchTerm);
        }
        // setInterval(() =>{
        let xhr = new XMLHttpRequest();
        xhr.open("GET", pos+"../chat/userlist.php", true);
        xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                let data = xhr.response;
                if(temp_chat_data == "")
                    temp_chat_data = data;
                if(!searchBar.classList.contains("active")){
                    if(data != temp_chat_data)
                    {
                        usersList.innerHTML = data;
                        temp_chat_data = data;
                    }
                }
                }
            }
        }
        xhr.send();
        // }, 500);
    }
    else
    {
        const form = document.querySelector(".typing-area"),
        incoming_id = form.querySelector(".incoming_id").value,
        inputField = form.querySelector(".input-field"),
        sendBtn = form.querySelector("button"),
        chatheader=document.querySelector(".users header"),
        chatBox = document.querySelector(".chat-box");
        form.onsubmit = (e)=>{
            e.preventDefault();
        }
        inputField.focus();
        inputField.onkeyup = ()=>{
            if(inputField.value != ""){
                sendBtn.classList.add("active");
                let xhr=new XMLHttpRequest();

            }else{
                sendBtn.classList.remove("active");
            }
        }
    inputField.addEventListener('keydown', function (e) {
    // Get the code of pressed key
    const keyCode = e.which || e.keyCode;
    // 13 represents the Enter key
    if (keyCode === 13 && !e.shiftKey) {
        // Don't generate a new line
        e.preventDefault();
        let xhr = new XMLHttpRequest();
        xhr.open("POST", pos+"../chat/insert-chat.php", true);
            xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    inputField.value ='' ;
                    inputField.rows='2';
                    scrollToBottom();
                }
            }
            }
            let formData = new FormData(form);
            xhr.send(formData);
    }
});
        sendBtn.onclick = ()=>{
            let xhr = new XMLHttpRequest();
            xhr.open("POST", pos+"../chat/insert-chat.php", true);
            xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    inputField.value ='' ;
                    inputField.rows='2';
                    scrollToBottom();
                }
            }
            }
            let formData = new FormData(form);
            xhr.send(formData);
        }
        chatBox.onmouseenter = ()=>{
            chatBox.classList.add("active");
        }
        chatBox.onmouseleave = ()=>{
            chatBox.classList.remove("active");
        }
        setInterval(() =>{
            let xhr = new XMLHttpRequest();
            xhr.open("POST", pos+"../chat/get-chat.php", true);
            xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    let data = xhr.response;
                    if(msg_recieved == "") 
                    {
                        chatBox.innerHTML = data;
                        msg_recieved = data;
                        if(!chatBox.classList.contains("active")){
                            scrollToBottom();
                        }
                    }
                    if(msg_recieved != data)
                    {
                        chatBox.innerHTML = data;
                        msg_recieved = data;
                        if(!chatBox.classList.contains("active")){
                            scrollToBottom();
                        }
                    }
                    // chatBox.innerHTML = data;
                }
            }
            }
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("incoming_id="+incoming_id);
        }, 500);

        function scrollToBottom(){
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
$(function () {
  $('[data-bs-toggle="popover"]').popover()
});
    // let uniq = "<?=$_SESSION['unique_id']?>";
    // let strr = document.location+"";
    // let pos_chat = (strr.indexOf("rocurement")+1)?"../":"";
    // let xhr=new XMLHttpRequest();
    // xhr.onload = function(){
    //     if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
    //     document.getElementById("chat_modal_body").innerHTML=this.responseText;
    // }
    // xhr.open("GET", "../chat/chat.php?user_id="+uniq+"&pos="+pos_chat+"&data=0");
    // xhr.send();

    var eval_variable;
    function chat_person(e,data=0)
    {
        let strr = document.location+"";
        let pos_chat = (strr.indexOf("rocurement")+1)?"../":"";
        let uniq = e.name;
        const req = new XMLHttpRequest();
        req.onload = function(){
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("chat_modal_body").innerHTML=this.responseText;
        load_chat();
        }
        req.open("GET", pos+"../chat/chat.php?user_id="+uniq+"&pos="+pos_chat+"&data="+data);
        req.send();
    }
    function chat_box(data=0)
    {
        if(data)
        {
            floating_chat_box(document.getElementById("chat_modal_body"),data)
        }
        else
        {
            document.getElementById("chat_modal_body").innerHTML=spinners;
            let strr = document.location+"";
            let pos_chat = (strr.indexOf("rocurement")+1)?"../":"";
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            document.getElementById("chat_modal_body").innerHTML=this.responseText;
            load_chat('Userlist');
            }
            req.open("GET", pos+"../chat/all.php?pos="+pos_chat);
            req.send();
        }
    }
    function scroll(){
        document.getElementById('chbox').scrollTop =  document.getElementById('chbox').scrollHeight;
    }
    function getChat(id){
        let xhr = new XMLHttpRequest();
            xhr.open("POST", pos+"../chat/get-chat.php", true);
            var height= document.getElementById('chbox').scrollHeight;
            xhr.onload = ()=>{
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    let data = xhr.response;
                    document.getElementById('ch_bdy').innerHTML+=data;
                    scroll();
                }
            }
            }
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("req_id="+id);
    }
    const height=(document.getElementById('chbox'))?document.getElementById('chbox').scrollHeight:0;
    function send_msg(e){
 let ids=document.getElementById('ids_holder').value;
 let req_id=document.getElementById('req_holder').value
 let message=document.getElementById('text_area').value
  let xhr=new XMLHttpRequest();
  xhr.onload=function(){
    document.getElementById('text_area').value="";
    getChat(req_id)
  }
       xhr.open("GET", pos+"../chat/insert-chat.php?ids="+ids+'&message='+message+"&req_id="+req_id);
        xhr.send();
}
function get_user_detail(e){
        const req = new XMLHttpRequest();
        req.onload = function(){ 
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            Swal.fire({
            title: "User Detail",
            html:this.responseText,
        })
        }
        req.open("GET", pos+"../common/allphp.php?account_username="+e);
        req.send();
}
    function floating_chat_box(e,data=0) 
    {
        let id=0;
        if(data)
        id=data
        else
        id=e.value;
        let strr = document.location+"";
        let pos_chat = (strr.indexOf("rocurement")+1)?"../":"";
        const req = new XMLHttpRequest();
        req.onload = function(){ 
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("chat_modal_body").innerHTML=this.responseText;
        }
        req.open("GET", pos+"../chat/all.php?pos="+pos_chat+"&req_id="+id+'&type='+e.name);
        req.send();

    }
    var temp_cheque_data = "";
    function add_cheque(e)
    {
        let cpv = e.name;
        let withholding  = document.getElementById("withholding_"+cpv).innerHTML;
        let cheque_for  = document.getElementById("cheque_amt_"+cpv).innerHTML;
        let cheque_no  = document.getElementById("cheque_no_"+cpv).value;
        let bank  = document.getElementById("bank_for_"+cpv).value;
        let cluster_id  = document.getElementById("cluster_"+cpv).innerHTML;
        let po_s  = document.getElementById("pos_"+cpv).innerHTML;
        let providing_company  = "";
        let reqcomp  = document.getElementById("compfor_"+cpv).value;
        // let reqcomp  = document.getElementById("reqcomp_"+cpv).innerHTML;
        let cheque_percent  = document.getElementById("cheque_percent_"+cpv).value;
        // let before_vat  = document.getElementById("before_vat_"+cpv).value;
        let withheld  = document.getElementById("nowithholding_"+cpv).checked?"true":"false";
        temp_cheque_data = document.getElementById("cheque_no_form_"+cpv).innerHTML;
        document.getElementById("cheque_no_form_"+cpv).innerHTML=spinners;
        if(cheque_no == '' || bank == '')
        {
            document.getElementById("cheque_no_form_"+cpv).innerHTML = temp_cheque_data;
            document.getElementById('warn_cheque_'+cpv).classList.remove("d-none");
            document.getElementById('warn_cheque_'+cpv).innerHTML = "**Please Enter Cheque Number and Bank";
        }
        else
        {
            document.getElementById("cheque_no_form_"+cpv).innerHTML = temp_cheque_data;
            document.getElementById('warn_cheque_'+cpv).classList.add("d-none");
            document.getElementById('warn_cheque_'+cpv).innerHTML = "";
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            document.getElementById("cheque_no_form_"+cpv).innerHTML=this.responseText;
            document.getElementById("print_cpv_"+cpv).classList.remove("d-none");
            document.getElementById("print_cpv_"+cpv).click();
            pay_req(e);
            }
            req.open("GET", pos+"../finance/allphp.php?cpv_info="+withholding+"--"+cheque_for+"--"+cheque_no+"--"+bank+"--"+cluster_id+"--"+po_s+"--"+providing_company+"--"+reqcomp+"--"+cheque_percent+"--"+withheld);
            req.send();
        }
    }
    var search_hist = "";
    function load_sms(e,type = "all",special="",search="")
    {
        if(special != "search" || (special == "search" && search != search_hist))
        {
            if(special == "search")
                search_hist = search;
            let temp = "";
            if(special == "")
                document.getElementById("view_sms_body").innerHTML=spinners;
            else if(special == 'more')
            {
                temp = e.innerHTML;
                e.innerHTML = spinners.replaceAll("primary","white");
            }
            else if(special == 'inbox')
            {
                document.getElementById("view_sms_body").innerHTML=spinners;
            }
            else if(search != '')
            {
                document.getElementById("messages").innerHTML=spinners;
            }
            else
            {
                special = "";
                document.getElementById("view_sms_body").innerHTML=spinners;
            }
            if(search == "")
            {
                if(document.getElementById("search_count"))
                    document.getElementById("search_count").classList.add("d-none");
                if(document.getElementById("search_bar"))
                    document.getElementById("search_bar").classList.replace("col-10","col-12");
            }
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
                if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
                if(special == "")
                    document.getElementById("view_sms_body").innerHTML=this.responseText;
                else if(special == 'more')
                {
                    document.getElementById("sms_tblbody").innerHTML+=this.responseText;
                    document.getElementById("loadmore_sms").value = parseInt(e.value)+1;
                    e.innerHTML = temp;
                }
                else if(search != '')
                {
                    document.getElementById("search_count").classList.remove("d-none");
                    document.getElementById("search_bar").classList.replace("col-12","col-10");
                    document.getElementById("messages").innerHTML=this.responseText.split("--|--")[0];
                    document.getElementById("search_count").innerHTML=this.responseText.split("--|--")[1];
                }
                // datatable();
                let loadmore_sms = document.getElementById("loadmore_sms")
                if(parseInt(loadmore_sms.value) >= parseInt(loadmore_sms.name))
                    loadmore_sms.classList.add("d-none");  
            }
            let parameters = "?active="+type;
            if(search != '')
                parameters+="&search="+search;
            if(special == 'more')
                parameters+="&loadmore="+e.value;
            req.open("GET", pos+"../common/ajax_sms.php"+parameters);
            req.send();
        }
    }

    function pay_req(e,type = "")
    {
        document.getElementById("pay_requ_body").innerHTML=spinners;
        // document.getElementById("create_cheque_modal").name = e.name;
        // document.getElementById("create_cheque_modal").value = e.value;
        let str_type = (type == "view")?"&view=":((type == "advance")?"&advance=":((type == "advance_view")?"&advance=&view=":""));
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("pay_requ_body").innerHTML=this.responseText;
        }
        req.open("GET", pos+"../common/ajax_payreq.php?cluster_id="+e.value+str_type);
        req.send();
    }
    
    function view_documents(to,active = "")
    {
        let parameters = (active == "")?"":"&active="+active;
        document.getElementById("documents_details").innerHTML=spinners;
        parameters += "&tblcount="+(datatable());
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("documents_details").innerHTML=this.responseText;
        datatable_count = datatable();
        }
        req.open("GET", pos+"../common/ajax_docs.php?view_"+to+"="+parameters);
        req.send();
    }

    function comp_selector(e,comp)
    {
        document.getElementById('comp_selector').value=comp;
        document.getElementById('share_comp').value=e.value;
    }
    function view_progress_coll(e)
    {
        if(e.name != e.getAttribute('aria-expanded'))
        {
            e.name = (e.name == 'false')?'true':'false';
            document.getElementById("toogle_progress").classList.toggle("fa-plus-circle");
            document.getElementById("toogle_progress").classList.toggle("fa-minus-circle");
        }
        // e.children[0].classList.toggle("fa fa-plus-circle");
        // e.children[0].classList.toggle("fa fa-minus-circle");
        // let icon = (e.getAttribute('aria-expanded') == "true")?"<i class='fa fa-minus-circle'></i>":"<i class='fa fa-plus-circle'></i>";
        // e.innerHTML = icon+" View Progress";
    }
    function selections_committee(e)
    {
        let selections = document.getElementsByClassName('itemsss');
        let selected = false;
        for(let i=0;i<selections.length;i++)
        {
            if(selections[i].checked)
                selected = true
        }
        if(!selected)
            document.getElementById('warn_selection').innerHTML= "Atleast select one Selection or Reject"; 
        else
        {
            let btn = document.getElementById('committee_reason');
            btn.name = e.name;
            btn.value = e.value;
            btn.click();
            // e.type="submit";
            // e.click();
        }
    }
    function committee_reasons(e)
    {
        let btnn = document.getElementById('btn_committee_reason');
        btnn.name=e.name;
        btnn.value=e.value;
        if(e.innerHTML.includes("Reject"))
            btnn.classList.replace('btn-outline-success','btn-outline-danger');
        else
            btnn.classList.replace('btn-outline-danger','btn-outline-success');
    }
    function delete_item(e,it="")
    {
        Swal.fire({
            title: "Are you sure?",
            text: "You want to Delete the request!",
            icon: "warning",
            buttons: true,
            dangerMode: "true",
            buttons: ["Cancel", "Yes"]
        })
        .then((willDelete) => {
            if (willDelete) {   
                if(it=="")
                    window.location.href = "allphp.php?"+e.name+"="; 
                else
                {
                    let temp_data = e.name;
                    window.location.href = "allphp.php?value="+temp_data+"&delete_cs="; 
                }
            }
        });
    }
    
    function prompt_confirmation(e)
    {
        if(e.type!="submit")
        {
           Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            })
            .then((countinue_opp) => {
                if (countinue_opp.isConfirmed) {
                    e.type = "submit";
                    e.click();
                    e.setAttribute("disable","true");
                    // e.type = "button";
                    // window.location.href = "allphp.php?"+e.name+"=";
                }
            });
        }
    }
    function change_ch_view(e)
    {
        let t = e.id.split("_")[0];
        let o = (e.id.split("_")[0]=='list')?'tbl':'list';
        e.className = "btn nav-link active";
        document.getElementById(o+"_toggle").className = "btn nav-link";
        document.getElementById(o+"_cs_view").classList.add("d-none");
        document.getElementById(t+"_cs_view").classList.remove("d-none");
    }

    function purchase_requisition(e,p = "")
    {
        document.getElementById("purchase_requisitions_body").innerHTML=spinners;
        let pos_temp = pos;
        if(p=="yes") pos_temp = "";
        var req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            $(function () {
  $('[data-bs-toggle="popover"]').popover()
});
            document.getElementById('purchase_requisitions_body').innerHTML = this.responseText;
            if(e.value == 'yes' || e.title == 'yes')
                document.getElementById('purchase_requisitions_view').classList.replace('alert-primary','bg-success');
            else
                document.getElementById('purchase_requisitions_view').classList.replace('bg-success','alert-primary');
        }
        req.open("GET", pos_temp+"../common/purchase_requisition.php?purchase_requisition="+e.name);
        req.send();
    }
    function openmodal(e,p = "")
    {
        document.getElementById("item_details_body").innerHTML=spinners;   
        let pos_temp = pos;
        if(p=="yes") pos_temp = "";
        var req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            $(function () {
  $('[data-bs-toggle="popover"]').popover()
});
            document.getElementById('item_details_body').innerHTML = this.responseText;
            if(e.value == 'yes' || e.title == 'yes')
                document.getElementById('view_full_detail').classList.replace('alert-primary','bg-success');
            else
                document.getElementById('view_full_detail').classList.replace('bg-success','alert-primary');
        }
        req.open("GET", pos_temp+"../common/request_details.php?info="+e.name.replace('specsfor_','')+"&pos_temp="+pos_temp);
        req.send();
    }
    function mail(e)
    {
        document.getElementById("email_modal_body").innerHTML=spinners;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("email_modal_body").innerHTML=this.responseText;
        }
        req.open("GET", pos+"../common/ajax_email.php?data="+e.id);
        req.send();
    }
    function fleet_request(e,reason_for)
    {
        document.getElementById("fleetModal_form").innerHTML=spinners;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("fleetModal_form").innerHTML=this.responseText;
        }
        req.open("GET", pos+"../common/ajax_fleet_request.php?po=<?php echo $_SESSION['username']?>&reason="+reason_for);
        req.send();
    }
    function mail_vendor(e)
    {
        let name = document.getElementById('name'+e.name).value;
        let email = document.getElementById('email'+e.name).value;
        let subject = document.getElementById('subject'+e.name).value;
        let email_data = document.getElementById('email_data'+e.name).innerHTML;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");

            // vendors(e);
        }
        req.open("GET", pos+"../Procurement/GS/allphp.php?email="+email+"&name="+name+"&subject="+subject+"&email_data="+email_data+"&send_mail="+e.id);
        req.send();
    }
    function compsheet_loader(e,x="",ch = "",scale = "")
    {  
        let viewof_comparision = "";
        if(x!="") 
        {
            for(let i_agg = 0;i_agg< aggr.length;i_agg++)
            aggr[i_agg]=0;
        }
            let togo = (x=="")?pos+"../common/":pos+"../committee/";
        if(pos == "../" && x!="")
            togo = "../committee/";
        // else 
        if(e.title == "initial")
        {
            togo = pos+"../committee/";
            document.getElementById("form_committee").setAttribute("action",pos+"../Procurement/manager/allphp.php");
        }
        if(e.title == "view_comparision")
        {
            viewof_comparision = "&viewofcomparision=";
            togo = pos+"../committee/";
        }
        let scale_set = (scale!="")?"&current_scale="+scale:"";
        let sent_name = (ch != "")?"&name="+ch:"";
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
            $(function () {
            $('[data-bs-toggle="popover"]').popover()
            });
            document.getElementById("comp_sheet_body").innerHTML=this.responseText;
            // if(scale!="")
            // document.getElementById("alt_form_committee").value = scale;
            // document.getElementById("alt_form_committee").setAttribute("action","../Committee/allphp.php?current_scale="+scale);
        }
        req.open("GET", togo+"ajax_comp_sheet.php?data="+e.name+sent_name+scale_set+viewof_comparision);
        req.send();
    }
    function give_reason(e,t,rem = "",red = "")
    {
        if(rem != "") 
        {
            document.getElementById("rem_optional").classList.add("d-none");
            document.getElementById("reason_field").setAttribute("required",true);
        }
        else
        {
            document.getElementById("rem_optional").classList.remove("d-none");
            document.getElementById("reason_field").removeAttribute("required");
        }
        document.getElementById('reason_btn').name=e.name;
        document.getElementById('reason_btn').value=e.value;
        if(e.innerHTML.includes("Reject") || red != "")
            document.getElementById('reason_btn').classList.replace('btn-outline-success','btn-outline-danger')
        else
            document.getElementById('reason_btn').classList.replace('btn-outline-danger','btn-outline-success')
        document.getElementById('form_reasons').setAttribute("action",t+"/allphp.php");
    }
    
    function compsheet_creater(e,val)
    {
        
        j=1;
        i=1;
        bool_changed=false;
    
        document.getElementById('ttl').innerHTML =(val==1?'Edit':'Fill')+' Comparision Sheet For '+e.id;
        var element = e.parentElement.parentElement.children[0].innerHTML.split(":-:")[0];
        var idd = e.parentElement.parentElement.children[0].innerHTML.split(":-:")[1];
        var name = e.parentElement.parentElement.children[0].innerHTML.split(":-:")[2];
        var company = e.parentElement.parentElement.children[0].innerHTML.split(":-:")[3];
        var department = e.parentElement.parentElement.children[0].innerHTML.split(":-:")[4];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("createCompModal_body").innerHTML=this.responseText;
        document.getElementById('vat_holder').innerHTML = "";
        }
        req.open("GET", "ajax_create_cs.php?db="+element+"&idd="+idd+"&name="+name+"&company="+company+"&department="+department+"&type="+val+"&cluster="+e.name);
        req.send();
    }
   
    function Edit_loader(e)
    {
        var data = e.id.split("_");
        document.getElementById('edit_request').name=data[0]+data[1];
        var id= data[1];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("toedit").innerHTML=this.responseText;
        }
        req.open("GET", "Ajax_edit.php?id="+id+"&type="+data[0]);
        req.send();
    }

    function print_page(e,def = "",all = "")
    {
        if(all == "")
        {
            if(e.title=='item')
            {
                var req = new XMLHttpRequest();
                req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
                    document.getElementById('printonly').innerHTML=this.responseText;
                    document.getElementById('specs_show').click();
                    time = setTimeout(function(){
                        window.print();
                    }, 1000);
                }
                req.open("GET", pos+"../common/request_details.php?info="+e.name.replace('print_','')+"&pos_temp="+pos);
                req.send();
            }
            else if(e.title=='cluster')
            {
                let location = (def=="")?pos:"";
                var req = new XMLHttpRequest();
                req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
                    document.getElementById('printonly').innerHTML=this.responseText.replace('style = \'overflow:scroll\'',"");
                    window.print();
                }
                // req.open("GET", togo+"ajax_comp_sheet.php?data="+e.name);
                req.open("GET", location+"../common/ajax_comp_sheet.php?data="+e.name.replace('print_',''));
                req.send();
            }
        }
        else
        {
            let location = (def=="")?pos:"";
            var req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
                document.getElementById('printonly').innerHTML=this.responseText.replace('style = \'overflow:scroll\'',"");
                window.print();
            }
            req.open("GET", location+"../common/ajax_print_all.php?data="+e.name.replace('print_',''));
            req.send();

        }
    }
    function view_performa(e,loc,def = "")
    {
        let ppos = (def=="")?pos:((special_pos != "")?pos:"");
        var id = e.id;
        var pid = e.name;
        var type=e.title;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        if(this.responseText.includes('Go to Home Page')) window.location.assign(pos+"../index.php");
        document.getElementById("view_performa_body").innerHTML=this.responseText;
            document.getElementById("back_from_performa").classList.remove('d-none');
        document.getElementById("back_from_performa").setAttribute("data-bs-target","#"+loc); 
        }
        // req.open("GET", "ajax_performa.php?id="+id+"&pid="+pid);
        req.open("GET", ppos+"../common/ajax_performa.php?id="+id+"&pid="+pid+"&loc_pos="+ppos+"&type="+type);
        req.send();
    }
    var tempCs = "";
    function pulloutActive ()
    {
        var allRequests = document.getElementsByClassName("generalEdit");
        var open = false;
        for(var i=0; i<allRequests.length;i++)
        {
            if(allRequests[i].checked) 
            {
                open = true;
                break;
            }
        }
        if(open) document.getElementById("pullOut").classList.remove("d-none");
        else document.getElementById("pullOut").classList.add("d-none");
    }
    function pullOutRequest(e,cluster)
    {
        Swal.fire({
            title: "Are you sure? ",
            text: "you will Create A new Comparison for the selected Items",
            icon: "warning",
            showCancelButton: true,
            buttons: true,
            buttons: ["Cancel", "Yes"]
        })
        .then((countinue_opp) => {
            if (countinue_opp.isConfirmed) {
                var requests = [];
                var purchaseOrders = [];
                var allRequests = document.getElementsByClassName("generalEdit");
                // alert(allRequests.length)
                if(allRequests.length == 0)
                {
                    swal.fire('Failed!','Failed Creating Comparison From Items','error')
                    return;
                }
                for(var i=0; i<allRequests.length;i++)
                {
                    if(allRequests[i].checked) 
                    {
                        requests.push(allRequests[i].value.split(",")[0])
                        purchaseOrders.push(allRequests[i].value.split(",")[1])
                    }
                }
                tempCs = document.getElementById("comp_sheet_body").innerHTML;
                document.getElementById("comp_sheet_body").innerHTML = spinners;
                // alert(requests)
                // alert(purchaseOrders)
                const req = new XMLHttpRequest();
                req.onload = function(){//when the response is ready
                    // alert(this.responseText)
                    try {
                        if(JSON.parse(this.responseText))
                        {
                        // window.location.reload();
                        // alert(this.responseText) 
                        const data = JSON.parse(this.responseText);
                        // alert(data.cluster_id)
                        const button = document.createElement('button');
                        button.name = data.cluster_id;
                        compsheet_loader(button);
                        swal.fire({title:'Successful!',text:'New Comparision Created From Selected Items',icon:'success'})
                        }
                        else
                        swal.fire('Failed!','Failed Creating Comparison From Items','error')
                    }
                    catch (err) {
                        console.error(err)
                        swal.fire('Failed!','Failed Creating Comparison From Items','error')    
                    }
                    // compsheet_loader(document.getElementById("reloadComparison"))
                }
                req.open("GET", pos+"../requests/allphp.php?pullOut="+requests+"&purchaseOrders="+purchaseOrders+"&cluster="+cluster);
                req.send();
            }
        })
    }
   
    function chanagepass(e)
    {
        var npass= document.getElementById(e.title+'_newpass').value;
        var cpass= document.getElementById(e.title+'_confpass').value;
        if(npass != cpass)
            document.getElementById(e.title+"_warnpass").innerHTML="** Password Doesn't Match **";
        else
        {
            document.getElementById(e.title+"_warnpass").innerHTML="";
            if(e.type=='button')
            {
                e.type='submit';
                e.click();
                e.type='button';
            }
        }
    }
    function change_disp(e,t)
    {
        let o = (t=='gallery')?'list':'gallery';
        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
    }
    function checkboxAll(e)
    {
        let check_boxes = document.getElementsByClassName("ch_boxes");
        for(let i = 0; i < check_boxes.length; i++)
        {
            if(e.checked)
                check_boxes[i].checked = true;
            else
                check_boxes[i].checked = false;
            batch_select(this);
        }
    }
</script>

<?php 
    if($_SESSION['acc_status'] == 'waiting')
    {
        echo "
        <script>
            function cp()
            {
                document.getElementById('passmodal').setAttribute('data-bs-backdrop','static');
                document.getElementById('change_p').click();
                document.getElementById('ch_pass_close').remove();
            }
        </script>";
    }
    else
    {
        echo "<script> function cp(){} </script>";
    }
?>
