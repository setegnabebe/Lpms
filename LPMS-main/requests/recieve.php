<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str,$title)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>$title</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Recive Items");
    sideactive("recieve");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Recieve Items At Property</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Recieve Items At Property</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql = "SELECT * FROM `purchase_order` where `status` = 'Complete-uncomfirmed' ORDER BY `timestamp` DESC";
        $stmt_purchase_unconfirmened = $conn -> prepare($sql);
        $stmt_purchase_unconfirmened -> execute();
        $result_purchase_unconfirmened = $stmt_purchase_unconfirmened -> get_result();
        if($result_purchase_unconfirmened -> num_rows > 0)
        while($row = $result_purchase_unconfirmened -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            // $sql2 = "SELECT * FROM requests WHERE `request_id`='".$row['request_id']."' AND `company` = '".$_SESSION['company']."'";
            $stmt_request -> bind_param("i", $row["request_id"]);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request -> num_rows>0)
                while($row2 = $result_request -> fetch_assoc())
                {
                    if($_SESSION['username']!=$row2['customer']) break;
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
                            <div class='box'>
                            <h3 class='text-capitalize'>".$name."</h3>
                            <ul>
                                <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                                <li class='text-start'><span class='fw-bold'>Requsted Item : </span>".$row2['item']."</li>
                                <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li class='text-end'><button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                                <li>
                                    <button type='submit' value='".$row['purchase_order_id']."' name='confirm_recieved' class='btn btn-sm btn-outline-primary'>
                                    Confirm Recieved
                                    </button>
                                </li>
                            </ul>
                            </div>
                        </div>
                        ";
            }
            }
            if($str=='') $Fisrt_empty = true;
            else 
                divcreate($str,"Items Purchased");
            $str="";
            $sql = "SELECT * FROM `stock` where `status` = 'Complete-uncomfirmed' ORDER BY `id` DESC";
            $stmt_stock_unconfirmened = $conn -> prepare($sql);
            $stmt_stock_unconfirmened -> execute();
            $result_stock_unconfirmened = $stmt_stock_unconfirmened -> get_result();
            if($result_stock_unconfirmened->num_rows>0)
            while($row = $result_stock_unconfirmened->fetch_assoc())
            {
                $na_t=str_replace(" ","",$row['type']);
                // $sql2 = "SELECT * FROM requests WHERE `request_id`='".$row['request_id']."' AND `company` = '".$_SESSION['company']."'";
                $stmt_request -> bind_param("i", $row["request_id"]);
                $stmt_request -> execute();
                $result_request = $stmt_request -> get_result();
                if($result_request -> num_rows>0)
                    while($row2 = $result_request -> fetch_assoc())
                    {
                        if($_SESSION['username']!=$row2['customer']) break;
                        if($row['type']=="Consumer Goods"){
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
                        else if($row['type']=="Spare and Lubricant"){
                            $id=$row2['request_for'];
                            $stmt_description->bind_param("i", $row2['request_for']);
                            $stmt_description->execute();
                            $result3 = $stmt_description->get_result();
                            $res=($result3->num_rows>0)?true:false;  
                        }
                        else if($row['type']=="Tyre and Battery")
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
                                if($row['type']=="Consumer Goods")
                                    $name=$row3['Name'];
                                else if($row['type']=="Spare and Lubricant")
                                    $name=$row3['description'];
                            }
                            if($row['type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                            $str.= "
                            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                                <div class='box'>
                                <h3 class='text-capitalize'>".$name."</h3>
                                <ul>
                                    <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                                    <li class='text-start'><span class='fw-bold'>Requsted Item : </span>".$row2['item']."</li>
                                    <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                    <li class='text-end'><button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                        View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
                                    <li>
                                    <button class='btn btn-sm btn-outline-primary' type='submit' value='".$na_t."_".$row['id']."_".$row['for_purchase']."_".$row['request_id']."' name='confirm_in_stock'>Confirm Recieved</button>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            ";
                }
                }
                if($str=='') $second_empty = true;
                else 
                    divcreate($str,"Item in stock");
                if(isset($Fisrt_empty) && isset($second_empty))
                    echo "
                        <div class='py-5 pricing'>
                            <div class='section-title text-center py-2  alert-primary rounded'>
                                <h3 class='mt-4'>No Items Ready to be Recieved</h3>
                            </div>
                        </div>";
    ?>
    </form>
</div>
<?php include "../footer.php"; ?>