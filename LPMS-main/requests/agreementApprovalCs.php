
<?php 
    session_start();
if(isset($_SESSION['loc']))
{ 
    if($_SESSION["department"]!='Owner' &&$_SESSION["role"]!='manager' &&$_SESSION["role"]!='GM' && $_SESSION["role"]!='Director')
        header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
// include '../connection/connect_ws.php'; 
function divcreate($str,$n)
{
    if($str=='') return 0; 
    echo "
        <div class='py-5 pricing' id='$n'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>All Requests for $n</h6> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Approval");
    sideactive("agreement_approval1");
    function show(e)
    {
        var arraycont = e.parentElement.children;
        if(e.innerHTML=='All Requests')
        {
            for($i=0;$i<arraycont.length;$i++)
            {
                arraycont[$i].classList.replace('btn-white','btn-primary');
                arraycont[$i].classList.remove('shadow');
                if($i!=0)
                    document.getElementById(arraycont[$i].innerHTML).classList.remove('d-none');
            }
        }
        else
        {
            for($i=0;$i<arraycont.length;$i++)
            {
                arraycont[$i].classList.replace('btn-white','btn-primary');
                if($i!=0)
                    document.getElementById(arraycont[$i].innerHTML).classList.add('d-none');
            }
            document.getElementById(e.innerHTML).classList.remove('d-none');
        }
        e.classList.replace('btn-primary','btn-white');
        e.classList.add('shadow','btn-white');
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
        document.getElementById("batch_approve").value = selections;
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
        <h2>Approve Purcahse Order Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Approve Purcahse Order Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<?php  
$emp=true;
            $query = "SELECT * FROM requests WHERE `next_step`='directors' and procurement_company=?";
            $stmt_agreement_fetch = $conn->prepare($query);
            $stmt_agreement_fetch -> bind_param("s", $_SESSION['company']);
            $stmt_agreement_fetch -> execute();
            $result_agreement_fetch = $stmt_agreement_fetch -> get_result();
            if($result_agreement_fetch -> num_rows>0)
                while($r = $result_agreement_fetch -> fetch_assoc())
                {
                    $query = "SELECT count(*) AS num FROM requests WHERE `next_step`='procurement' and `request_type`='agreement' and procurement_company = ?";
                    $stmt_agreement_count = $conn->prepare($query);
                    $stmt_agreement_count -> bind_param("s", $_SESSION['company']);
                    $stmt_agreement_count -> execute();
                    $result_agreement_count = $stmt_agreement_count -> get_result();
                    if($result_agreement_count -> num_rows>0)
                        while($r2 = $result_agreement_count -> fetch_assoc())
                        {
                            if($r2['num']>0)
                            {
                                $emp=false;
                            }
                        }
                }
            ?>
            <form method="GET" action="allphp.php">
            <div class="row m-auto" id='reload'>
                <?php
                $sql = "SELECT * FROM requests WHERE `next_step`='procurement' and procurement_company = ? group by request_type";
                $stmt_agreement_procurement = $conn->prepare($sql);
                $stmt_agreement_procurement -> bind_param("s", $_SESSION['company']);
                $stmt_agreement_procurement -> execute();
                $result_agreement_procurement = $stmt_agreement_procurement -> get_result();
                $str="";
                $emp=true;
                if($result_agreement_procurement->num_rows>0)
                    while($r = $result_agreement_procurement->fetch_assoc())
                    {
                        $sql = "SELECT * FROM requests WHERE `next_step`='procurement'  AND `request_type` = ? and procurement_company = ? ORDER BY date_needed_by ASC";
                        $stmt_agreement_procurement_filter = $conn->prepare($sql);
                        $stmt_agreement_procurement_filter -> bind_param("ss", $r['request_type'], $_SESSION['company']);
                        $stmt_agreement_procurement_filter -> execute();
                        $result_agreement_procurement_filter = $stmt_agreement_procurement_filter -> get_result();
                        $str="";
                        $emp=true;
                        if($result_agreement_procurement_filter -> num_rows>0)
                            while($row = $result_agreement_procurement_filter -> fetch_assoc())
                            {
                                $type = $row['request_type'];
                                $na_t=str_replace(" ","",$type);
                                if($type=="Consumer Goods"){
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
                                    $stmt_description->bind_param("i", $row['request_for']);
                                    $stmt_description->execute();
                                    $result3 = $stmt_description->get_result();
                                    $res=($result3->num_rows>0)?true:false;
                                }
                                else if($type=="Tyre and Battery")
                                {
                                    $name=$row['request_for'];
                                    $res=false;
                                }
                                else 
                                {
                                    $res=false;
                                    $name=$row['item'];
                                }
                                if($res)
                                    while($row3 = $result3->fetch_assoc())
                                    {
                                        if($type=="Consumer Goods")
                                        {
                                            $name = "Project - ".(($row['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                                        }
                                        else if($type=="Spare and Lubricant")
                                            {
                                                $name=$row3['description'];
                                            }
                                    }
                                $emp=false;
                                if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                                $spec_dep = "<select class='form-select bg-light' id='spec_".$na_t."_".$row['request_id']."'>";
                                $sql = "SELECT * FROM `department` WHERE `Name` = 'IT'";
                                $stmt_department_it = $conn->prepare($sql);
                                $stmt_department_it -> execute();
                                $result_department_it = $stmt_department_it -> get_result();
                                if($result_department_it -> num_rows > 0)
                                    while($r= $result_department_it -> fetch_assoc())
                                    {
                                        $spec_dep .= "<option value='".$r['Name']."'>".$r['Name']."</option>";
                                    }
                                // $spec_dep .= ($_SESSION["role"] == "Owner")?"<option value='".$_SESSION["username"]."'>".$_SESSION["username"]."</option>":"";
                                $spec_dep .= "</select>";
                                $uname = str_replace("."," ",$row['customer']);
                                $str.="
                                <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4'>
                                    <div class='box shadow'>
                                        <h3 class='row'>
                                            <button id='undo_".$na_t.$row['request_id']."' name='undo_".$na_t.$row['request_id']."' type='button' onclick='update(this)' class='btn col-2 d-none'><i class='fas fa-undo'></i></button> 
                                            <span class='text-capitalize col-12'>".$name."
                                            <span class='small text-secondary float-start'>
                                                <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                                            </span></span>
                                        </h3>
                                        <ul>
                                            <li class='text-start'><span class='fw-bold'>Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                                            ".$row['item']."</button></li>
                                            <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                            <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>";
                                            if($_SESSION['role'] == "Director" || $_SESSION['role'] == "Owner")
                                                $str.="<li class='text-start'><span class='fw-bold'>Department : </span>".$row['department']."</li>";
                                            $str.="
                                                <div class='mb-3 collapse mx-5 text-start' id='remark_view".$row['request_id']."'>
                                                    <p style='word-wrap: break-word'>Remark : ".$row['Remark']."</p>
                                                </div>
                                            <li name='btns_mgr_app' id='btns".$na_t."_".$row['request_id']."'>
                                                <button type='button' class='btn btn-outline-success btn-sm shadow' data-bs-toggle='modal' data-bs-target='#price_modal' onclick='set_price(this)'  id='".$row['request_id']."'>Set pricing</button> 
                                                <button type='button' class='btn btn-outline-danger btn-sm shadow' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\")' name='btntype' value='reject_".$na_t."_".$row['request_id']."' id='reject_".$na_t.$row['request_id']."'>Reject <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                                </li>
                                            <li id='loading_".$na_t.$row['request_id']."' class='d-none'> <div class='spinner-border text-primary'></div> </li>
                                            ";
                                        $str .="
                                        </ul>
                                    </div>
                                </div>";
                            }
            
                        divcreate($str,ucwords($type));
                    }




            if($emp)
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests Waiting Approval at this Time</h3>
                        </div>
                    </div>";
        ?>
    </div>
    </form>
</div>
</div>
<div class="modal fade" id="price_modal" tabindex="-1" role="dialog" aria-labelledby="price_modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="price_modalLabel">Create Agreement Comparsion Sheet</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action='allphp.php' method='post' enctype="multipart/form-data">
      <div class="modal-body" id='price_bdy'>
   
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" name='submit_price' class="btn btn-success">Proceed</button>
      </div>
    </form>
    </div>
  </div>
</div>
<?php include '../footer.php';?>
<script>
    function set_price(e)
    {
    let xhr=new XMLHttpRequest();
    xhr.onload=function(){
document.getElementById('price_bdy').innerHTML=this.responseText;
    }
    xhr.open("GET","checkitem.php?set_price="+e.id);
    xhr.send();
    }
function add_btn(e){
 document.getElementById('optional_btn').innerHTML=document.getElementsByName('btns_mgr_app')[0].innerHTML;
openmodal(e);
}

</script>
 