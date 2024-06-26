<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Items Requested</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Replaced Items");
    sideactive("replaced_items");
    function rep_collect(e)
    {
        // document.getElementById('stat_btn').name=e.name;
        document.getElementById('replacement_btn').value=e.name;
        if(e.innerHTML.includes("Not"))
            document.getElementById('replacement_btn').classList.replace('btn-outline-success','btn-outline-danger')
        else
            document.getElementById('replacement_btn').classList.replace('btn-outline-danger','btn-outline-success')
    }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Property Approval</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Property Approval</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<!-- <div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick='rep_collect_batch(this)' data-bs-toggle='modal' data-bs-target='#rep_collect' class='btn btn-outline-success shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button>
            <button type='button' onclick='rep_collect_batch(this)' data-bs-toggle='modal' data-bs-target='#rep_collect' class='btn btn-outline-danger shadow mt-3' name='batch_reject' id='batch_reject'>Reject</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div> -->
    <form method="GET" action="allphp.php">
    <?php
        $str="";
            $sql = "SELECT * FROM requests WHERE to_replace IS NOT NULL AND replaced_items IS NULL AND recieved = 'yes' AND property_company = ?";
            $stmt_replacements = $conn->prepare($sql);
            $stmt_replacements -> bind_param("s", $_SESSION['company']);
            $stmt_replacements -> execute();
            $result_replacements = $stmt_replacements -> get_result();
            if($result_replacements->num_rows>0)
            while($row = $result_replacements->fetch_assoc())
            {
                $type = $row["request_type"];
                $na_t=str_replace(" ","",$type);
                if($row['request_type']=="Consumer Goods")
                {
                    $id=$row['request_for'];
                    if($row['request_for'] == 0)
                    {
                        $stmt_project->bind_param("i", $row['request_for']);
                        $stmt_project->execute();
                        $result3 = $stmt_project->get_result();
                        $res=($result3->num_rows>0)?true:false;
                    }
                    else
                    {
                        $idd = explode("|",$row['request_for'])[0];
                        $stmt_project_pms->bind_param("i", $idd);
                        $stmt_project_pms->execute();
                        $result3 = $stmt_project_pms->get_result();
                        $res=($result3->num_rows>0)?true:false;
                    }
                }
                else if($type=="Spare and Lubricant")
                {
                    $id=$row['request_for'];
                    $stmt_description->bind_param("i", $row['request_for']);
                    $stmt_description->execute();
                    $result3 = $stmt_description->get_result();
                    $res=($result3->num_rows>0)?true:false;  
                }
                else if($type=="Tyre and Battery")
                {
                    $id=$row['request_for'];
                    $name=$row['request_for'];
                    $res=false;
                }
                else 
                {
                    $id=$row['request_id'];
                    $res=false;
                    $name=$row['item'];
                }
                if($res)
                    while($row3 = $result3->fetch_assoc())
                    {
                        if($row['request_type']=="Consumer Goods")
                        {
                            $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                        }
                        else if($type=="Spare and Lubricant")
                            $name=$row3['description'];
                    }
                    $quan_replaced = sizeof(explode(",",$row['to_replace']));
                    if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'>
                        <h3 class='text-capitalize'>".$name."</h3>
                        <ul>
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Item : </span>".$row['item']."</li>
                            <li class='text-start'><span class='fw-bold'>Quantity Replaced : </span>".$quan_replaced."</li>
                            <li class='text-end'><button type='button' title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                            <li class='mt-3'>
                                <button type='button' onclick='rep_collect(this)' data-bs-toggle='modal' data-bs-target='#rep_collect' class='btn btn-outline-success btn-sm shadow' name='$row[request_id]_Collected'>Item Received</button> 
                                <button type='button' onclick='rep_collect(this)' data-bs-toggle='modal' data-bs-target='#rep_collect' class='btn btn-outline-danger btn-sm shadow' name='$row[request_id]_Rejected'>Not Found</button>
                            </li>
                        </ul>
                        </div>
                    </div>
                    ";
            }
        // }
        if($str=='')
            echo "
                <div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>No Replacements to Collect</h3>
                    </div>
                </div>";
        else 
            divcreate($str);
    ?>
    </form>
</div>
<div class="modal fade" id="rep_collect">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="allphp.php">
                <div class="modal-header text-center">
                    <h3 id='top_text' class="">Remark</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reason_body">
                    <textarea class='col-9 ms-4' rows='4' name='reason' required></textarea>
                    <button class='mx-auto d-block btn btn-outline-success mt-3' name='replacement' id='replacement_btn'>Proceed</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<?php include "../footer.php"; ?>