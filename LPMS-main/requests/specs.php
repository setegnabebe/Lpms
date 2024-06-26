
<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    if($str=='') $str = "<div class='py-5 pricing'>
    <div class='section-title text-center py-2  alert-primary rounded'>
        <h3 class='mt-4'>No specification Requests at this Time</h3>
    </div>
</div>";
    echo "
        <div class='py-5 pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Specifications</h4>
                <small class='text-center'>Insert Specifications for the following request</small> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Set Specifications");
    sideactive("spec");
    function setspecmodal(e)
    {
        // document.getElementById('specmodal').innerHTML=hold;
        document.getElementById('warning').innerHTML='';
        document.getElementById("t_of_m").innerHTML="Enter Specifications for "+e.title;
        document.getElementById('done').value=e.id;
        // document.getElementById('snow').innerHTML+='<h2>'+e.title+'</h2>';
    }
    function set_spec(e)
    {
        if(document.getElementById('snow').innerHTML.includes('ql-blank'))
        {
            document.getElementById('warning').innerHTML='*This Field can not be Empty';
            return 0;
        }
        let ss = document.getElementById('snow').innerHTML.substring(0,document.getElementById('snow').innerHTML.indexOf("</div"))
        ss=ss.replaceAll('true','false')+'</div>';
        document.getElementById('view').value = ss;
        e.setAttribute('type','submit');
        e.click();
    
    }
    function batch_select(e)
    {
        let selections = "";
        let indicator = false;
        let all_batch = document.getElementsByClassName("ch_boxes");
        for(let i=0;i<all_batch.length;i++)
        {
            if(all_batch[i].checked) 
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-primary");
                indicator=true;
                selections += (selections =="")?all_batch[i].value:","+all_batch[i].value;
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        
        // document.getElementById("batch_approve").value = selections;
        document.getElementById("batch_reject").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
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
        <h2>Set Specifications for Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Set Specifications for Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow list-group-item-light" style="top: 80%; left: 90%; z-index:1; z-index:1;">
    <form method="GET" action="../requests/allphp.php">
        <div class=''>
            <!-- <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../requests")' class='btn btn-outline-success shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button> -->
            <button type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,"../requests","remove")' class='btn btn-sm btn-outline-Danger fw-bold shadow' name='batch_reject' id='batch_reject'>Reject Request</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <form method="GET" action="allphp.php">
        <div class="row m-auto">
            <?php 
            $conditions = Gm_query($conn_fleet);
            $str='';
            $sql = "SELECT * FROM requests where `spec_dep` = ? AND specification IS NULL AND ($conditions)";
            if($_SESSION['company'] == 'Hagbes HQ.')
            {
                $sql_comp = "SELECT * FROM `comp` where `IT` = '1' AND `Name` != 'Hagbes HQ.'";
                $stmt_company_with_it = $conn_fleet -> prepare($sql_comp);
                $stmt_company_with_it -> execute();
                $result_company_with_it = $stmt_company_with_it -> get_result();
                if($result_company_with_it -> num_rows>0)
                    while($row_comp = $result_company_with_it -> fetch_assoc())
                        $sql .= " AND company != '".$row_comp['Name']."'";
            }
            else
            {
                $sql .= " AND company = '".$_SESSION['company']."'";
            }
            $stmt_requests_fetch = $conn -> prepare($sql);
            $stmt_requests_fetch -> bind_param("s", $_SESSION['department']);
            $stmt_requests_fetch -> execute();
            $result_requests_fetch = $stmt_requests_fetch -> get_result();
            if($result_requests_fetch -> num_rows>0)
                while($row = $result_requests_fetch -> fetch_assoc())
                {
                    $type = $row["request_type"];
                    $na_t=str_replace(" ","",$type);
                    $uname = str_replace("."," ",$row['customer']);
                    $str.="
                        <div class='col-md-4 col-lg-3 my-4'>
                            <div class='box shadow'>
                                <h3 class='row'>
                                    <span class='text-capitalize'><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    ".$row['item']."</button>
                                    <span class='small text-secondary float-start'>
                                        <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                                    </span></span>
                                </h3>
                                <ul>
                                    <!-- <li class='text-start'><span class='fw-bold'>Item : <button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    ".$row['item']."</button></span></li> -->
                                    <li class='text-start'><span class='fw-bold'>Catagory : </span>$type</li>
                                    <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                    <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                                    <!--<li class='text-start'><span class='fw-bold'>Date Requested : </span>".$row['date_requested']."</li>
                                    <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row['date_needed_by']."</li>-->
                                    <li>
                                        <input type='button' title='".$row['item']."' value='Write up Specification' class='btn btn-sm btn-outline-primary fw-bold' 
                                        data-bs-toggle='modal' data-bs-target='#specmodal' onclick='setspecmodal(this)' id='".$na_t."_".$row['request_id']."'>
                                        <button class='btn btn-sm btn-outline-Danger ms-1 fw-bold' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\")' type='button' name='reject_spec' value='$row[request_id]'>Reject Request</button>
                                    </li>
                                ";
                                $str .="
                                </ul>
                            </div>
                        </div>";
                }
            divcreate($str); 
            ?>
        </div>
    </form>
<!-- <form method="GET" action="allphp.php"></form> -->
<div class="modal fade" id="specmodal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="allphp.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title" id='t_of_m'></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="specmodal_body">
                    <span id='warning' class='text-danger'></span>
                    <div id="snow" style="min-height: 100px;">
                        
                    </div>
                    <!-- <form method="GET" action="allphp.php"> -->
                        <!-- <div class="card">
                            <div class="card-header">
                                <h5 class="card-title text-center">Insert Image <span class="text-secondary fs-6">(Optional)</span></h5>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <input type="file" class="image-exif-filepond">
                                </div>
                            </div>
                            <button class='btn btn-danger' type="submit" name='xxx'> XXX </button>
                        </div> -->
                    <!-- </form> -->
                    <textarea id='view' name='spec' class="d-none"></textarea>
                    <input type="text" class="d-none" id='done' name='info'>
                        <div class="mb-3">
                            <label for="specs_pic" class="form-label">Insert Picture of Specs</label>
                            <input type='file' id='specs_pic' class='form-control multiple-files-filepond ms-0' name='specs_pic[]' multiple>
                        </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-primary" type="button" name='spec_insert' onclick="set_spec(this)">Finished</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</div>
<?php include '../footer.php';?>
<script>load_quill()</script>