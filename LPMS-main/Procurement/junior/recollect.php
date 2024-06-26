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
                <h6 class='text-white'>POs waiting Recollection</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Recollect Items");
    sideactive("Recollection");
    var element,db;
    function loader_collect(e)
    {
        let temp = e.id.replace("view_","");
        let data = temp.split('_');
        element = data[0];
        db = data[1];
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("itemsview_body").innerHTML=this.responseText;
        }
        req.open("GET", "Ajax_show.php?cl_id="+element);
        req.send();
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
        <h2>Recollection of Items</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Recollection of Items</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
    <form method="GET" action="allphp.php">
    <?php
        $str="";
        $sql_recollection_tasks = "SELECT * FROM `purchase_order` where `collector` = ? AND status ='Recollect'";
        $stmt_recollection_tasks = $conn->prepare($sql_recollection_tasks);
        $stmt_recollection_tasks -> bind_param("s",$_SESSION['username']);
        $stmt_recollection_tasks -> execute();
        $result_recollection_tasks = $stmt_recollection_tasks -> get_result();
        if($result_recollection_tasks->num_rows>0)
        while($row = $result_recollection_tasks->fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $stmt_request -> bind_param("i", $row['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request -> get_result();
            if($result_request->num_rows>0)
            while($row2 = $result_request->fetch_assoc())
            {
            // $num_req = $result->num_rows;
                $str.= "
                <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3 class='text-capitalize'>".$row2['item']."</h3>
                <ul>
                    <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                    <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>
                    <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                    <li class='text-end'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                    View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>";
                    $str.= ($row2['status']!='Recollect')?"<li><i class='text-primary fw-bold'>Waiting for Comfirmation</i></li>":"";
                    $str.= ($row2['status']=='Recollect')?"
                    <li>
                    <button class='col-sm-9 mb-2 col-md-5 btn btn-outline-success btn-sm shadow' type='button' onclick = 'prompt_confirmation(this)' name='recollected' value='".$row['purchase_order_id']."'>Recollected</button>
                    <button class='col-sm-9 mb-2 col-md-5 btn btn-outline-danger btn-sm shadow' type='button' onclick = 'prompt_confirmation(this)' name='not_found' value='".$row['purchase_order_id']."'>Not Found</button>
                    </li>":"";
                            $str.= "</li>
                        </ul>
                    </div>
                </div>
                ";
            }
        }
        if($str !='')
            divcreate($str);
        else
            echo "<div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests to be Collected</h3>
                        </div>
                    </div>";
    ?>
<div class="modal fade" id="itemsview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemsview_body">
                    <!-- Company And Items Form -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id='close_modal_1'>Close</button>
                </div>
        </div>
    </div>
</div> 
</form>
    
</div>
<?php include '../../footer.php';?>
