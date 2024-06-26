<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str,$title = "")
{
    echo "
    <div class='pricing'>
        <div class='section-title text-center py-2  alert-primary rounded'>
            <h6 class='text-white'>All Purchase Order Assigned by Supervisor $title</h4> 
        </div>
        <div class='row'>
            $str
        </div>
    </div>
    ";
}
function selector()
{
    include "../../connection/connect.php";
    $sql_po_pending = "SELECT * FROM `purchase_order` WHERE `status`='pending'";
    $stmt_po_pending = $conn -> prepare($sql_po_pending);
    $stmt_po_pending -> execute();
    $result_po_pending = $stmt_po_pending -> get_result();
    if($result_po_pending -> num_rows > 0)
        while($row = $result_po_pending -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            echo "
                <script>document.getElementById('".$na_t."_".$row['request_id']."').value='".$row["purchase_officer"]."'</script>
            ";
        }
}
function selector_col()
{
    include "../../connection/connect.php";
    $sql_po_request_processed = "SELECT *,P.status as `status`,P.request_id as `request_id` FROM `purchase_order` AS P INNER JOIN requests AS R on P.request_id = R.request_id where P.status='Payment Processed' AND collector IS NOT NULL AND P.procurement_company = ?";
    $stmt_po_request_processed = $conn -> prepare($sql_po_request_processed);
    $stmt_po_request_processed -> bind_param("s", $_SESSION['company']);
    $stmt_po_request_processed -> execute();
    $result_po_request_processed = $stmt_po_request_processed -> get_result();
    if($result_po_request_processed -> num_rows > 0)
        while($row = $result_po_request_processed -> fetch_assoc())
        {
            echo "
                <script>document.getElementById('".$row['purchase_order_id']."').value='".$row["collector"]."'</script>
            ";
        }
}
?>
<script>
    set_title("LPMS | Assigned Orders");
    sideactive("assigned");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Accept Quota Collection</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Accept Quota Collection</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid px-4">
    <?php
    $str="";
    $sql_assigned_officers = "SELECT * FROM `purchase_order` WHERE status='pending' AND `procurement_company` = ? ORDER BY timestamp DESC";
    $stmt_assigned_officers = $conn->prepare($sql_assigned_officers);
    $stmt_assigned_officers -> bind_param("s", $_SESSION['company']);
    $stmt_assigned_officers->execute();
    $result_assigned_officers = $stmt_assigned_officers -> get_result();
    if($result_assigned_officers -> num_rows>0)
        while($row = $result_assigned_officers -> fetch_assoc())
        {
            $type=$row['request_type'];
            $na_t=str_replace(" ","",$type);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows>0)
                while($row2 = $result_request -> fetch_assoc())
                {
                    if($row['request_type']=="Consumer Goods"){
                        if($row2['request_for'] == 0)
                        {
                            $stmt_project->bind_param("i", $row2['request_for']);
                            $stmt_project->execute();
                            $result3 = $stmt_project->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else
                        {
                            $id = explode("|",$row2['request_for'])[0];
                            $stmt_project_pms->bind_param("i", $id);
                            $stmt_project_pms->execute();
                            $result3 = $stmt_project_pms->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                    }
                    else if($row['request_type']=="Spare and Lubricant"){
                        $stmt_description->bind_param("i", $row2['request_for']);
                        $stmt_description->execute();
                        $result3 = $stmt_description->get_result();
                        $res=($result3->num_rows>0)?true:false;  
                    }
                    else if($row['request_type']=="Tyre and Battery")
                    {
                        $name="Plate Number - ".$row2['request_for'];
                        $res=false;
                    }
                    else 
                    {
                        $res=false;
                        $name="Item - ".$row2['item'];
                    }

                    if($res)
                        while($row3 = $result3->fetch_assoc())
                        {
                            if($type=="Consumer Goods")
                            {
                                $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                            }
                            else if($row['request_type']=="Spare and Lubricant")
                                $name="Job - ".$row3['description'];
                        }
                        if($row['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                        if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                        else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                        else $prio="";
                        $uname =str_replace("."," ",$row2['customer']);
                        $str.= "
                        <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                            <div class='box'>
                                <h3 class='text-capitalize row'>
                                    <span class='col-10'>
                                    ".$name."
                                        <span class='small text-secondary d-block mt-2'>$type</span>
                                    </span>
                                <span class='small text-secondary d-block col-2'>$prio</span>
                                </h3>
                                <form method='GET' action='allphp.php'>
                            <ul>
                                <li class='text-start'><span class='fw-bold'><button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn2(this)' >
                                <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li class='text-start' title='".$row['timestamp']."'><span class='fw-bold'>Date Assigned : </span>".date("d-M-Y", strtotime($row['timestamp']))."</li>
                                <li class='row'>
                                <span id='btns_Rassign_collector2'>
                                    <div class='input-group mb-3'>
                                        <select class='form-select form-select-sm' name='officer' id='".$na_t."_".$row2['request_id']."' required>
                                            <option value=''>--Select Purchase Officer--</option>
                                                ";
                                                $qq = "(company = '".$_SESSION['company']."'";
                                                $qq .= ")";
                                                $sql_purchase_officers = "SELECT * FROM account WHERE `role`='Purchase officer' AND `status` = 'active' AND $qq";
                                                $stmt_purchase_officers = $conn->prepare($sql_purchase_officers);
                                                $stmt_purchase_officers->execute();
                                                $result_purchase_officers = $stmt_purchase_officers->get_result();
                                                if($result_purchase_officers->num_rows>0)
                                                {
                                                    while($row_purchase_officers = $result_purchase_officers->fetch_assoc())
                                                    {
                                                        $officer=$row_purchase_officers['Username'];
                                                        $str.= "<option value='$officer'>$officer (Company : $row_purchase_officers[company])</option>";
                                                    }
                                                }
                                $str.=" </select>
                                        <button type='button' onclick = 'prompt_confirmation(this)' value='".$row2['request_id']."' class='btn btn-sm btn-outline-primary alert-primary' name='Reassign_officer'>
                                            Reassign ?
                                        </button>
                                        <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-primary text-primary fa fa-comment'></i></button>

                                    </div>
                                    </span>
                                </li>
                            </ul>
                            </form>
                            </div>
                        </div>
                        ";
                    }
                }
        if($str !='')
            divcreate($str,"For Proforma Gathering");
        else
            $empty = true;
        selector();
        ?>
</form>


    <?php
    $str="";
    $sql_assigned_collectors = "SELECT *,P.status as `status`,P.request_id as `request_id` FROM `purchase_order` AS P INNER JOIN requests AS R on P.request_id = R.request_id where P.status='Payment Processed' AND collector IS NOT NULL AND P.procurement_company = ?";
    $stmt_assigned_collectors = $conn->prepare($sql_assigned_collectors);
    $stmt_assigned_collectors -> bind_param("s", $_SESSION['company']);
    $stmt_assigned_collectors->execute();
    $result_assigned_collectors = $stmt_assigned_collectors -> get_result();
    if($result_assigned_collectors->num_rows>0)
        while($row = $result_assigned_collectors->fetch_assoc())
        {
            $type = $row['request_type'];
            if($row['request_type']=="Consumer Goods"){
                if($row['request_for'] == 0)
                {
                    $stmt_project->bind_param("i", $row['request_for']);
                    $stmt_project->execute();
                    $result3 = $stmt_project->get_result();
                    $res=($result3->num_rows>0)?true:false;
                }
                else
                {
                    $id = explode("|",$row['request_for'])[0];
                    $stmt_project_pms->bind_param("i", $id);
                    $stmt_project_pms->execute();
                    $result3 = $stmt_project_pms->get_result();
                    $res=($result3 -> num_rows > 0)?true:false;
                }
            }
            else if($row['request_type']=="Spare and Lubricant"){
                $stmt_description->bind_param("i", $row['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;  
            }
            else if($row['request_type']=="Tyre and Battery")
            {
                $name="Plate Number - ".$row['request_for'];
                $res=false;
            }
            else 
            {
                $res=false;
                $name="Item - ".$row['item'];
            }

            if($res)
                while($row3 = $result3->fetch_assoc())
                {
                    if($type=="Consumer Goods")
                    {
                        $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                    }
                    else if($row['request_type']=="Spare and Lubricant")
                        $name="Job - ".$row3['description'];
                }
            if($row['request_type']=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
            if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
            else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
            else $prio="";
            $r_type = $row['request_type'];
            $na_t=str_replace(" ","",$r_type);
            $uname =str_replace("."," ",$row['customer']);
            $str.= "
                <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <form method='GET' action='allphp.php'>
                    <div class='box'>
                    <h3>                                   
                        $name
                    </h3>
                    <ul>
                        <li class='text-start'><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                        <span class='fw-bold'>Item : </span>".$row['item']."</button></li>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row['date_needed_by']."</li>
                        <li class='row'>
                        <span id='btns_Rassign_collector'>
                            <div class='input-group mb-3'>
                                <select class='form-select form-select-sm' name='$row[purchase_order_id]' id='$row[purchase_order_id]' required>
                                    <option value=''>--Select Purchase Officer--</option>";
                                    $qq = "(company = '".$_SESSION['company']."'";
                                    $qq .= ")";
                                    $sql_purchase_officers = "SELECT * FROM account WHERE `role`='Purchase officer' AND `status` = 'active' AND $qq";
                                    $stmt_purchase_officers = $conn->prepare($sql_purchase_officers);
                                    $stmt_purchase_officers->execute();
                                    $result_purchase_officers = $stmt_purchase_officers->get_result();
                                    if($result_purchase_officers->num_rows>0)
                                    {
                                        while($row_purchase_officers = $result_purchase_officers->fetch_assoc())
                                        {
                                            $officer=$row_purchase_officers['Username'];
                                            $str.= "<option value='$officer'>$officer (Company : $row_purchase_officers[company])</option>";
                                        }
                                    }
                        $str.=" </select>
                                <button type='button' onclick = 'prompt_confirmation(this)' value='$row[purchase_order_id]' class='btn btn-sm btn-outline-primary alert-primary' name='Reassign_collector'>
                                    Reassign ?
                                </button>
                                <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-primary text-primary fa fa-comment'></i></button>
                            </div>
                            </span>
                        </li>
                    </ul>
                    </div>
                    </form>
                </div>
                ";
        }
        if($str !='')
            divcreate($str,"For Collection");
        else if(isset($empty))
            echo "<div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests Have been Assigned</h3>
                        </div>
                    </div>";
        selector_col();
        ?>
</div>
</div>
<?php include '../../footer.php';?>
<script>
    function add_btn(e){
document.getElementById('optional_btn').innerHTML= "<form method='GET' action='allphp.php'>"+document.getElementById('btns_Rassign_collector').innerHTML+"</form>";
openmodal(e);
}
function add_btn2(e){
document.getElementById('optional_btn').innerHTML= "<form method='GET' action='allphp.php'>"+document.getElementById('btns_Rassign_collector2').innerHTML+"</form>";
openmodal(e);
}
</script>
