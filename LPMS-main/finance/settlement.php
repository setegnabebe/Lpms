<?php 

session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Director") header("Location: ../");
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
                <h6 class='text-white'>POs waiting Settlement</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Settlement");
    sideactive("Settlement");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Settlements Requested</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Settlements Requested</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql = "SELECT * FROM `purchase_order` where `settlement`='requested' AND finance_company = ?";
        $stmt_po_settlement = $conn -> prepare($sql);
        $stmt_po_settlement -> bind_param("s", $_SESSION['company']);
        $stmt_po_settlement -> execute();
        $result_po_settlement = $stmt_po_settlement -> get_result();
        if($result_po_settlement->num_rows>0)
        while($row = $result_po_settlement->fetch_assoc())
        {
            $type=$row['request_type'];
            $na_t=str_replace(" ","",$type);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request->num_rows>0)
                while($row2 = $result_request->fetch_assoc())
                {
                    if($type=="Consumer Goods")
                    {
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
                            if($row['request_type']=="Consumer Goods")
                            {
                                $name = ($row2['request_for'] == 0)?$row3['Name']:$row3['project_name'];
                            }
                            else if($row['request_type']=="Spare and Lubricant")
                                $name = "Job - ".$row3['description'];
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
                                    <span>
                                    ".$name."<button type='button' title='item' class='btn btn-outline-secondary border-0 float-end' name='print_".$na_t."_".$row['request_id']."' onclick='print_page(this)'>
                                    <i class='text-dark fas fa-print'></i>
                                    </button>
                                    <span class='small text-secondary d-block mt-2'>$type</span></h3>
                                    <ul>
                                    <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                    <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                    <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                    <li><button type='button' onclick = 'prompt_confirmation(this)' value='".$row['purchase_order_id']."' class='btn btn-outline-primary btn-sm shadow ms-2' name='complete_settlement'>Complete Settlement</button></li>
                                </ul>
                            </div>
                        </div>
                        ";
                    }
        }
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Payment</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>
    </form>
</div>
</div>
<?php include '../footer.php';?>