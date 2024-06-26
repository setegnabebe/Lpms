<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Items Collected</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Recieve Items");
    sideactive("collected_recieved");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Items collected</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Items collected</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql = "SELECT * FROM `purchase_order` where `status` = 'Collected-not-comfirmed' OR `status` = 'Collected' OR `status` = 'Handed over to Store' ORDER BY `timestamp` DESC";
        $stmt_for_collection = $conn->prepare($sql);
        $stmt_for_collection -> execute();
        $result_for_collection = $stmt_for_collection -> get_result();
        if($result_for_collection -> num_rows > 0)
        while($row = $result_for_collection -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $stmt_request->bind_param("i", $row['request_id']);
            $stmt_request->execute();
            $result_request = $stmt_request->get_result();
            if($result_request -> num_rows > 0)
                while($row2 = $result_request -> fetch_assoc())
                {
                    if($row['request_type']=="Consumer Goods"){
                        $id=$row2['request_for'];
                        if($row2['request_for'] == 0)
                        {
                            $stmt_project->bind_param("i", $row2['request_for']);
                            $stmt_project->execute();
                            $result3 = $stmt_project->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else
                        {
                            $idd = explode("|",$row2['request_for'])[0];
                            $stmt_project_pms->bind_param("i", $idd);
                            $stmt_project_pms->execute();
                            $result3 = $stmt_project_pms->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                    }
                    else if($row['request_type']=="Spare and Lubricant"){
                        $id=$row2['request_for'];
                        $stmt_description->bind_param("i", $row2['request_for']);
                        $stmt_description->execute();
                        $result3 = $stmt_description->get_result();
                        $res=($result3->num_rows>0)?true:false;  
                    }
                    else if($row['request_type']=="Tyre and Battery")
                    {
                        $id=$row2['request_for'];
                        $name=$row2['request_for'];
                        $res=false;
                    }
                    else 
                    {
                        $id=$row2['request_id'];
                        $res=false;
                        $name=$row2['item'];
                    }
    
                    if($res)
                        while($row3 = $result3->fetch_assoc())
                        {
                            if($row['request_type']=="Consumer Goods")
                            {
                                $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                            }
                            else if($row['request_type']=="Spare and Lubricant")
                                $name=$row3['description'];
                        }
                        if($row['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                        $str.= "
                        <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'><h3 class='text-capitalize'>";
                        $str.=($res || $row['request_type']=="Tyre and Battery")?$name:"<button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                        $name</button>";
                        $str.= "<span class='small text-secondary d-block mt-2'>".$row['request_type']."</span></h3>
                        <ul>
                            <li class='d-none'>dbs</li>
                            <li class='d-none'>$id</li>
                            <li class='d-none'>$name</li>";
                            $str.=($res || $row['request_type']=="Tyre and Battery")?"
                            <li class='text-start'><span class='fw-bold'>Item : </span>
                            <button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            ".$row2['item']."</li></button>":"";
                            
                            $str.="
                            <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                            <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li>";
                                if($row['status'] == 'Collected-not-comfirmed')
                                    $str.= "
                                    <input type='submit' value='Confirm' name='confirm_collected_".$row['purchase_order_id']."'
                                    class='btn btn-sm btn-outline-primary'>";
                                else if($row['status'] == 'Collected')
                                $str.= "
                                <input type='submit' value='Handover to Store' name='handover_".$row['purchase_order_id']."'
                                class='btn btn-sm btn-outline-primary'>";
                                else
                                $str.= "<i class='text-primary'>Waiting for Comfirmaion</i>";

                                $str.= "
                                </li>
                            </ul>
                            </div>
                        </div>
                        ";
            }
            }
            if($str=='')
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Comparision Sheets Created</h3>
                        </div>
                    </div>";
            else 
                divcreate($str);
    ?>
    </form>
<!-- <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="allphp.php">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="mymodal_body"> -->
                    <!-- Company And Items Form -->
                <!-- </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div> -->

    
</div>
<?php include "../../footer.php"; ?>
