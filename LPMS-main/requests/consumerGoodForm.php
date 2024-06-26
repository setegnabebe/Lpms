<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
if(!isset($_SESSION["project_id"]) || !isset($_SESSION["project_name"]))
{
    header("location:projectList.php");
}
if(!in_array($_SESSION["company"],$privilege["Consumer Goods"]) && !in_array("All",$privilege["Consumer Goods"]))
{
    header("Location: ../");
}
$remove_type_table = true;
?>
<script>
sideactive("ConsumerGoods_side");
set_title("LPMS | Consumer Goods");
    var j = 0;
    function pages(e)
    {
       
        var ul = e.parentElement.parentElement;
        var num_pages = parseInt(ul.id);
        var result;
        for(var i=1;i<=num_pages;i++)
        {
            if(ul.children[i].className.includes('active'))
                var current_page = ul.children[i].children[0];
            ul.children[i].classList.remove('active');
            document.getElementById("item "+ul.children[i].children[0].innerHTML).classList.add('d-none');
        }
        if(!isNaN(parseInt(e.innerHTML)))
        {
            result = e;
            e.parentElement.classList.add('active');
            document.getElementById("item "+e.innerHTML).classList.remove('d-none');
        }
        else
        {
            var itemm = (e.innerHTML=='Previous')?parseInt(current_page.innerHTML)-1:parseInt(current_page.innerHTML)+1;
            result = ul.children[itemm].children[0];
            ul.children[itemm].classList.add('active');
            document.getElementById("item "+itemm).classList.remove('d-none');
        }
        ul.children[0].classList.remove('disabled');
        ul.children[num_pages+1].classList.remove('disabled');
        if(result.innerHTML == 1)
            ul.children[0].classList.add('disabled');
        else if(result.innerHTML == num_pages)
            ul.children[num_pages+1].classList.add('disabled');
    }
    function load_item(e)
    {
        let item_num = e.id.split("_")[1];
        let name_item = e.value+"_"+e.selectedIndex.name;
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
            document.getElementById("item_results_"+item_num).innerHTML = this.responseText;
        }
        req.open("GET", "item_details.php?item="+name_item+"&item_num="+item_num);
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
            <h2>Consumer Goods Request</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item"><a href='projectList.php' style="text-decoration: none;">Projects List</a></li>
                <li class="breadcrumb-item active">Consumer Goods Request Form</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>
    <div class="container-fluid row ">
            <?php req_count($conn,$conn_fleet,'Consumer Goods', ",request_for,%".$_SESSION["project_id"]."|%LIKE"); ?>
            <div class="card" data-aos="fade-right"><!-- col-sm-12 col-xl-4 -->
                <h3 class='text-center'>Project - <?php echo $_SESSION["project_name"] ?></h3>
                            <p class="text-center "><b>Remark : </b>All <span class='text-danger fs-5'>*</span> <span class='text-secondary'>are Required Fields</span></p>
                            <form method="POST" action="allphp.php">
                                <div class="modal-header text-primary">
                                    <h4 class="modal-title">Add Purchase Order</h4>
                                </div>
                                <div class="modal-body" id="mymodal_body">
                                    <div id='items'>
                                        <div id='item_1'>
                                            <?php if($_SESSION['project_id'] == 0)
                                            {?>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control rounded-4" id="floatingitem_1" placeholder="Item Name" name='item[]' required>
                                                <label for="floatingitem_1"><span class='text-danger'>*</span>Item Name</label>
                                            </div>
                                            <?php }
                                            else
                                            {
                                                ?>
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="item[]"  id="floatingitem_1" onchange = 'load_item(this)' required>
                                                    <option value="">-- Select Item --</option>
                                                    <?php
                                                        $sql_task = "SELECT * FROM task where `id_p` = ?";
                                                        $stmt_task = $conn_pms -> prepare($sql_task);
                                                        $stmt_task -> bind_param("i", $_SESSION['project_id']);
                                                        $stmt_task -> execute();
                                                        $result_task = $stmt_task -> get_result();
                                                        if($result_task->num_rows>0)
                                                        {
                                                            while($row_task = $result_task->fetch_assoc())
                                                            {
                                                                $date_today = date("Y-m-d");
                                                                $date_limit = date("Y-m-d",strtotime("+".$m_limit_consumer." Month", strtotime($date_today)));
                                                                $sql_item = "SELECT * FROM item where `id_t` = ? and date_needed_for <= ?";
                                                                $stmt_item_avail = $conn_pms -> prepare($sql_item);
                                                                $stmt_item_avail -> bind_param("is", $row_task['id'], $date_limit);
                                                                $stmt_item_avail -> execute();
                                                                $result_item_avail = $stmt_item_avail -> get_result();
                                                                if($result_item_avail -> num_rows>0)
                                                                {
                                                                    while($row_item = $result_item_avail -> fetch_assoc())
                                                                    {
                                                                        echo "<option value='".$row_item['id']."'>Task : $row_task[task_name] &nbsp &nbsp | &nbsp Item : $row_item[item_name] &nbsp &nbsp | &nbsp Date For : ".date("d-M-Y", strtotime($row_item['date_needed_for']))." &nbsp &nbsp | &nbsp BOQ : $row_item[total_quantity]</option>";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                    </select>
                                                    <label for="floatingitem_1"><span class='text-danger'>*</span>Item Name</label>
                                                </div>
                                            <?php }?>
                                            <div id='item_results_1'> 
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="form-floating input-group mb-3">
                                                            <!-- step="any"  -->
                                                            <input type="number" class="form-control rounded-4" min='0' id="floatingreq_1" placeholder="Required Quantity" name='req_quan[]' step='any' required>
                                                            <label for="floatingreq_1"><span class='text-danger'>*</span>Required Quantity</label>
                                                            <span class="input-group-text fw-bold"><span class='text-danger'>*</span>Unit</span>
                                                            <input type="text" class="form-control" id="unit_1" placeholder="Eg. Pcs" name ='unit[]' style="max-width: 20%;">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-6 form-floating mb-3">
                                                        <input type="date" class="form-control rounded-4" id="floatingdate_1" placeholder="Date Needed By" min="<?php echo $dateee?>" max="<?php echo $date_last?>" name='date_n_b[]' required>
                                                        <label for="floatingdate_1"><span class='text-danger'>*</span>Date Needed By</label>
                                                    </div>
                                                    <div class="mb-3 col-sm-12 col-md-6" id='remark_1'>
                                                        <label for="remarks_1"><span class='text-danger'>*</span>Remark <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                    data-bs-content="Reason For Purchase And Additional Information"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                                        <textarea id='remarks_1' class="form-control rounded-4" rows="1" name='remark[]' minlength="15" placeholder="Reason For Purchase And Additional Information" required></textarea>
                                                    </div>
                                                    <div class="mb-3 col-sm-12 col-md-6" id='desc_1'>
                                                        <label for="descri_1"><span class='text-danger'>*</span>Description <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                    data-bs-content="Details for Item including Model or Size Specification"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                                        <textarea id='descri_1' class="form-control rounded-4" rows="1" name='description[]' minlength="15" placeholder="Details for Item including Model or Size Specification" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-sm" type="button" name="1" onclick = "add_item(this)">Add Item <i class="fa fa-plus"></i></button>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit" name="submit_raw_request">Submit Request<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                                </div>
                            </form>
            </div>
    
    <div class='pt-4' data-aos='fade-left'><!-- col-sm-12 col-xl-8  -->
        <?php include 'tbl-div.php'?>
        <div class='pricing'> 
            <div class='section-title text-center py-2 mb-4  alert-primary rounded'>
            <?php $temp = (strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || strpos($_SESSION["a_type"],"HOCommittee") !== false )?"All Users":$_SESSION["username"];?>
                <h6 class='text-white'>Previous Requests By <?php echo $temp ?></h4>
            </div>
            <!-- style='background-color: #002266;'> -->
            <?php 
                $str = "<div>";
                $count = 0;
                $id_project=$_SESSION["project_id"];
                $type="Consumer Goods";
                $na_t=str_replace(" ","",$type);
                $tbl_data = "";
                $tbl_head = "#,Requested By,Item,Company,Department,Date Requested,Date Needed By,Status";
                $open = false;
                $project_sql = ($id_project != 0)?"AND `request_for` LIKE '%".$id_project."|%'":"AND `request_for` = '$id_project'";
                $sql = "SELECT * FROM requests WHERE request_type = 'Consumer Goods' $project_sql AND recieved='not' AND `status`!='Rejected By Manager' AND `status`!='Rejected By Dep.Manager' AND `status`!='Rejected'";
                $sql .= (strpos($_SESSION["a_type"],"manager") !== false && !isset($_SESSION["managing_department"]) && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND  department = '".$_SESSION['department']."'":"";
                if(isset($_SESSION["managing_department"]))
                {
                    if(!in_array("All Departments",$_SESSION["managing_department"]))
                    {
                        $temp_cond = "";
                        foreach($_SESSION["managing_department"] as $depp)
                            $temp_cond .=($temp_cond == "")?"department = '$depp'":"OR department = '$depp'";
                        $sql .= " AND ( $temp_cond )";
                    }
                }
                
                $sql .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":" AND company = '". $_SESSION['company']."'";
                $sql.=($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND `customer`='".$_SESSION["username"]."'":"";
                $sql.=($_SESSION['role']=='Director' || $_SESSION['role']=='Owner')?" OR `customer`='".$_SESSION["username"]."'":"";
                $sql .= " ORDER BY date_needed_by ASC, request_id DESC";
                $stmt_fetch_request = $conn -> prepare($sql);
                $stmt_fetch_request -> execute();
                $result_fetch_request = $stmt_fetch_request -> get_result();
                if($result_fetch_request -> num_rows>0)
                    while($row = $result_fetch_request -> fetch_assoc())
                    {
                        $dlt_btn2 = "";
                        $type=$row['request_type'];
                        $printpage = "
                        <form method='GET' action='print.php' class='float-end'>
                            <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row['request_id'].":|:$type'>
                            <i class='text-dark fas fa-print'></i>
                            </button>
                        </form>";
                        if(strtotime($row['date_needed_by'])<strtotime(date("Y-m-d")) && !($row['status']=="Rejected By Manager") || ($row['status']=="Rejected")) $over = 9;
                        else $over = 12;
                        if($row['status']=='waiting')
                        {
                            $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger btn-sm border-0' name='Delete_".$na_t."_".$row['request_id']."' onclick='delete_item(this)' ><i class='far fa-trash-alt'></i></button>";
                            $dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger btn-sm border-0 float-end' name='Delete_".$na_t."_".$row['request_id']."' onclick='delete_item(this)' ><i class='far fa-trash-alt'></i></button>";
                            if($over == 12)
                                $over = 10;
                        }
                        include 'tbl_code.php';
                        $count++;
                        if(($count-1)%3==0)
                        {
                            if($open) 
                            {
                                $str .= "</div>";
                            }
                            $page_num=(($count-1)/3)+1;
                            $dis=($page_num >1)?' d-none':'';
                            $str .= "<div class='row$dis' id='item $page_num'>";
                            $open=true;
                        }
                        $str .= "
                            <div class='col-sm-12 col-md-6 col-lg-4 my-4 focus'> 
                                <div class='box shadow'>
                                    <h3 class='text-capitalize row'>";
                                    $str .= ($over==9)?"<span class='text-danger col-1' style='font-size:20px;'><i class='fas fa-exclamation-circle'></i></span>":"";
                                    $str .= "<span class='col-$over'>Item  - 
                                    <button type='button' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                    ".$row['item']."</button></span>";
                                    $str .= ($row['status']=='waiting')?$dlt_btn:"";
                                    $str .= "</h3>
                                    <ul>";
                                    $uname = str_replace("."," ",$row['customer']);
                                    $str .= ($_SESSION['a_type']=='user')?"":"<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>";
                                    $str .= "
                                        <li class='text-start'><span class='fw-bold'> Quantity : </span>".$row['requested_quantity']."</li>
                                    <li class='text-start'><span class='fw-bold'> Date Requested : </span>".date("d-M-Y", strtotime($row['date_requested']))."</li>";
                                    $str .= ($over==9)?"<li class='text-danger text-start'>":"<li class='text-start'>";
                                    $str .= "<span class='fw-bold'> Date Needed By : </span>".date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                                    <li class='text-start'><span class='fw-bold'> Status : </span>".$row['status']."</li>
                                    </ul>
                                </div>
                            </div>";
                    }
                $str .= "</div>";////////////close the last page
                if(isset($page_num)){
                $str .= "<ul class='pagination justify-content-end' id='$page_num'>
                <li class='page-item disabled'><button type='button' class='page-link' onclick='pages(this)'>Previous</button></li>
                ";
                    $amount = ($count%3==0)?$count/3:($count/3)+1;
                    for($i=1;$i<=$amount;$i++)
                    {
                        $act = ($i==1)?' active':'';
                        $str .= "<li class='page-item$act'><button type='button' class='page-link' onclick='pages(this)'>$i</button></li>";
                    }
                    $dis = ($amount<2)?" disabled":"";
                
                $str .= "<li class='page-item$dis'><button type='button' class='page-link' onclick='pages(this)'>Next</button></li>
                </ul></div>";
                }
                else 
                    $str .= "<div class='box shadow'><h3 class='text-capitalize'> No Entries</h3></div></div>";
                $div_type = divcreate_requests_page($str);
                $tbl_format = table_create($tbl_head,$tbl_data,true);
                echo "<div id='tbl_view'>$tbl_format</div>";
                echo "<div class='d-none' id='div_view'>$div_type</div>";
            ?>
            </div>
            </div>
        </div>
</div>


<script>
    var item_str = document.getElementById("item_1").innerHTML;
    function add_item(e)
    {
        let num = parseInt(e.name);
        let next_num = num+1;
        let strrr = item_str.replaceAll("_1","_"+next_num);
        const div =  document.createElement('div');
        div.id = 'item_'+next_num;
        div.className='row';
        div.innerHTML = "<hr class='mt-2 mb-3'><div class='col-12 position-relative'>"+strrr+"<button class='btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle' type='button' onclick='remove(this)' id='remove"+next_num+"'>X</button>"+"</div>";
        document.getElementById('items').appendChild(div);
        e.name = next_num;
        ////////////////set Values//////////////
        const vals = ['remarks_','floatingdate_'];
        for(let i=0;i<vals.length;i++)
        {
            if(document.getElementById(vals[i]+next_num))
                document.getElementById(vals[i]+next_num).value=document.getElementById(vals[i]+'1').value;
        }
        //////////////////////////////////////
        $(function () {
            $('[data-bs-toggle="popover"]').popover()
        });
    }
    function remove(e)
    {
        e.parentElement.parentElement.remove();
    }
</script>
<?php include '../footer.php';?>