<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
    $type = $_GET['r_type'];
    $na_t=str_replace(" ","",$_GET['r_type']);
if(!in_array($_SESSION["company"],$privilege[$type]) && !in_array("All",$privilege[$type]))
{
    header("Location: ../");
}

echo "
<script>
    var r_type = \"$type\";
    var na_t = \"$na_t\";
</script>";
$remove_type_table = true;
?>
<script>
    set_title("LPMS | "+r_type);
    sideactive(na_t+"_side");
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
    function checkitem(e,item_num,type)
    {
        if(e.value=='')
        {
            document.getElementById("badge"+e.id.replace("input","")).classList.add("d-none");
            return 0;
        }
        document.getElementById("badge"+e.id.replace("input","")).innerHTML = "<div class='spinner-border text-primary spinner-border-sm'></div>";
        document.getElementById("badge"+e.id.replace("input","")).classList.remove("d-none");
        var time = setTimeout(function(){
            var item=document.getElementById("floatingitem"+item_num).value;
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            document.getElementById("badge"+e.id.replace("input","")).innerHTML=this.responseText;
            // document.getElementById("badge"+e.id.replace("input","")).classList.remove("d-none");
            }
            req.open("GET", "checkitem.php?serial="+e.value+"&item="+item+"&type="+type);
            req.send();
        }, 2000);
    }
    function setLimit(e){
        const index=e.id.charAt(parseInt(e.id.length)-1)-1;
        document.getElementsByName("remark[]")[index].minLength=e.value.length;
        document.getElementsByName("description[]")[index].minLength=e.value.length;
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
            <h2><?php echo ucwords($type);?> Request</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active"><?php echo ucwords($type);?> Request Form</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>
    <div class="container-fluid row ">
            <?php req_count($conn,$conn_fleet,"$type"); ?>
            <div class="card" data-aos="fade-right"><!-- col-sm-12 col-xl-4 -->
                            <p class="text-center "><b>Remark : </b>All <span class='text-danger fs-5'>*</span> <span class='text-secondary'>are Required Fields</span></p>
                            <form method="POST" action="allphp.php" enctype="multipart/form-data">
                                <div class="card-header text-primary">
                                    <h4 class="card-title">Add Purchase Order</h4>
                                </div>
                                <div class="card-body" id="mymodal_body">
                                    <div id='items'>
                                        <div id='item_1'>
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4 form-floating mb-3">
                                                    <input type="text" class="form-control rounded-4" onchange="cret(document.getElementById('floatingreq_1'),'_1')" id="floatingitem_1" placeholder="Item Name" name='item[]' required>
                                                    <label for="floatingitem_1"><span class='text-danger'>*</span>Item Name</label>
                                                </div>
                                                <div class="col-sm-12 col-md-4">
                                                    <div class="form-floating input-group mb-3">
                                                        <!-- step="any"  -->
                                                        <input type="number" class="form-control rounded-4" onchange="cret(document.getElementById('floatingreq_1'),'_1')" min='0' id="floatingreq_1" placeholder="Required Quantity" name='req_quan[]' step='any' required>
                                                        <label for="floatingreq_1"><span class='text-danger'>*</span>Quantity</label>
                                                        <span class="input-group-text fw-bold"><span class='text-danger'>*</span>Unit</span>
                                                        <input type="text" class="form-control" id="unit_1" placeholder="Eg. Pcs" name ='unit[]' style="max-width: 25%;">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-4 form-floating mb-3">
                                                    <input type="date" class="form-control rounded-4" id="floatingdate_1" placeholder="Date Needed By" min="<?php echo $dateee?>" max="<?php echo $date_last?>" name='date_n_b[]' required>
                                                    <label for="floatingdate_1"><span class='text-danger'>*</span>Date Needed By</label>
                                                </div>
                                            </div>
                                            <?php 
                                            $sql_catagory = "SELECT * FROM `catagory` where `catagory` = ?";
                                            $stmt_category_specific = $conn -> prepare($sql_catagory);
                                            $stmt_category_specific -> bind_param("s", $type);
                                            $stmt_category_specific -> execute();
                                            $result_category_specific = $stmt_category_specific -> get_result();
                                            $row_catagory = $result_category_specific->fetch_assoc();
                                            $split = "";
                                            if($row_catagory['replacements'])
                                            {
                                                if($type == 'Fixed Assets')
                                                { $split = "col-md-6";?>
                                                    <div class="row">
                                                <?php }?>
                                                <div class="row col-sm-12 <?=$split?>" id='mode_1' onkeyup="cret(document.getElementById('floatingreq_1'),'_1')" onchange="cret(document.getElementById('floatingreq_1'),'_1')">
                                                    <label for="mode_1" class="fw-bold"><span class='text-danger'>*</span>Replacement or Brand New</label>    
                                                    <div class='ms-3 form-check mb-3 col-5'>
                                                        <input class='form-check-input' type='radio' id='toreplace_1' name='reason_1' value='replace' required>
                                                        <label class='form-check-label' for='toreplace_1'>
                                                            Replacement
                                                        </label>
                                                    </div>
                                                    <div class='ms-3 form-check mb-3 col-5'>
                                                        <input class='form-check-input' type='radio' id='new_1' name='reason_1' value='new' required>
                                                        <label class='form-check-label' for='new_1'>
                                                            Brand New
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php }?>
                                            <?php 
                                            if($row_catagory['replacements'])
                                            {?>
                                                <div class="d-none" id='replacement_1'>
                                                    <h6 class="text-center my-2 text-capitalize">xxxx To be Replaced</h6>
                                                    <div class="mb-3 input-group position-relative" id='rep_1'>
                                                        <span class='input-group-text text-capitalize' id='repser_1'><span class='text-danger'>*</span>xxxx Serial Number</span>
                                                        <input name='repser[]' type="text" class="form-control form-control-sm rounded-4" id='input::1_1' onchange="checkitem(this,'_1','<?php $type?>')" onkeyup="checkitem(this,'_1','<?php $type?>')">
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill d-none" id='badge::1_1'>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php }?>
                                            <?php 
                                            if($type == 'Fixed Assets')
                                            {?>
                                            <div class="row col-sm-12 <?=$split?> border-2 border-start" id='itype_1'>
                                                <label for="itype_1" class="fw-bold"><span class='text-danger'>*</span>Item Type</label>
                                                <div class='ms-3 form-check mb-3 col-5'>
                                                    <input class='form-check-input' type='radio' id='itrelated_1' name='IT_RELATED_1' value='yes' required>
                                                    <label class='form-check-label' for='itrelated_1'>
                                                        IT Related <a href="https://portal.hagbes.com/lpms_uploads/Document-64942ed8aeb92.pdf"  target="_blank"><i class="fas fa-sharp fa-solid fa-file-pdf me-8 fs-5 text-danger"></i></a>
                                                    </label>
                                                </div>
                                                <div class='ms-3 form-check mb-3 col-5'>
                                                    <input class='form-check-input' type='radio' id='non-it_1' name='IT_RELATED_1' value='no' required>
                                                    <label class='form-check-label' for='non-it_1'>
                                                        Non - IT
                                                    </label>
                                                </div>
                                            </div>
                                            <?php 
                                            if($row_catagory['replacements'])
                                            {?>
                                                </div>
                                            <?php }
                                            }
                                            if($type=='agreement'){
                                            ?>
                                            <input type='hidden' value='_1' name='num[]'>
                                            <div class="row">
                                                <div class="col-sm-12 col-md-6 mb-3">
                                            <label> <span class="text-secondary">Upload Bincard</span><span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                                        data-bs-content="picture of stock ballance"> <i class="fa fa-info-circle text-primary" title="Details"></i></span> :</label>
                                                <input type='file' id='specs_pic' class='form-control multiple-files-filepond ms-0' name='specs_pic_1[]' multiple required>
                                            </div>
                                        
                                            <?php
                                            $sql='SELECT * FROM `prefered_vendors` WHERE `catagory`="agreement"';
                                            $stmt_agreement_vendors = $conn -> prepare($sql);
                                            // $stmt_agreement_vendors -> bind_param("s", $type);
                                            $stmt_agreement_vendors -> execute();
                                            $result_agreement_vendors = $stmt_agreement_vendors -> get_result();
                                           
                                             echo '<div class="col-sm-12 col-md-6 form-floating mb-3">
                                             <select class="form-control" id="vendor_name_" name="vendor[]" required>
                                             <option value="">Select vendor</option>';
                                             while($row_agreement_vendors = $result_agreement_vendors -> fetch_assoc())
                                             echo "<option value='$row_agreement_vendors[id]'>$row_agreement_vendors[vendor]</option>";

                                             echo '</select><label for="vendor_name_"><span class="text-danger">*</span>Vendor Name</label>
                                         </div>
                                         </div>
                                         ';
                                            }
                                            ?>
                                            <!-- <div class="row text-center">
                                                <div class=" col-6">
                                                    <a class="btn btn-sm alert-primary mb-3" type="button" data-bs-toggle="collapse" role="button" href="#remark_1" aria-expanded="false" aria-controls="remark_1">Remark / Reason</a>
                                                </div>
                                                <div class="col-6">
                                                    <button class="btn btn-sm alert-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#desc_1" aria-expanded="false" aria-controls="desc_1">Add Description</button>
                                                </div>
                                            </div> -->
                                            <div class="row">
                                                <div class="mb-3 col-sm-12 col-md-6" id='remark_1'>
                                                    <label for="remark_1" class="fw-bold"><span class='text-danger'>*</span>Remark <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                    data-bs-content="Reason For Purchase and Additional Information"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                                    <textarea id='remarks_1' class="form-control rounded-4" rows="1" name='remark[]' minlength="15" placeholder="Reason For Purchase and Additional Information"  required></textarea>
                                                </div>
                                                <div class="mb-3 col-sm-12 col-md-6" id='desc_1'>
                                                    <label for="desc_1" class="fw-bold"><span class='text-danger'>*</span>Description <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover"  title="" 
                                    data-bs-content="Details for Item with Detailed Specification" > <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                                    <textarea class="form-control rounded-4" rows="1" name='description[]' minlength="15" placeholder="Details for Item with Detailed Specification" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-sm" type="button" name="1" onclick = "add_item(this)">Add Item <i class="fa fa-plus"></i></button>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit" name="submit_request" value="<?php echo $type?>">Submit Request<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                                </div>
                            </form> 
            </div>
    

<div class=' pt-4' data-aos='fade-left'><!-- col-sm-12 col-xl-8 -->
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
                $tbl_data = "";
                $tbl_head = "#,Requested By,Item,Company,Department,Date Requested,Date Needed By,Status";
                $open = false;
                $sql = "SELECT * FROM requests WHERE request_type = '$type' AND recieved='not' AND `status`!='Rejected By Manager' AND `status`!='Rejected By Dep.Manager' AND `status`!='Rejected'";
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
                // $sql .= (strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND  department = '".$_SESSION['department']."'":"";
                $sql .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":" AND company = '". $_SESSION['company']."'";
                $sql.=($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND `customer`='".$_SESSION["username"]."'":"";
                $sql.=($_SESSION['role']=='Director' || $_SESSION['role']=='Owner')?" OR `customer`='".$_SESSION["username"]."'":"";
                $sql .= " ORDER BY date_needed_by ASC, request_id DESC";
                // if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner') $sql .="";
                // else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') $sql .=" AND company = '". $_SESSION['company']."'";
                // else if($_SESSION["a_type"] == 'manager' && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') $sql .=" AND  department = '".$_SESSION['department']."' AND company = '". $_SESSION['company']."'";
                // else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') $sql .=" AND `customer`='".$_SESSION["username"]."' AND company = '". $_SESSION['company']."'";
                // $sql.=" ORDER BY date_needed_by ASC, request_id DESC";
                // $sql .= (strpos($_SESSION["a_type"],"manager") !== false)?" ORDER BY date_needed_by ASC, request_id DESC":"AND customer='".$_SESSION["username"]."' ORDER BY date_needed_by ASC, request_id DESC";
                $stmt_fetch_requests = $conn -> prepare($sql);
                // $stmt_fetch_requests -> bind_param("s", $row['Name']);
                $stmt_fetch_requests -> execute();
                $result_fetch_requests = $stmt_fetch_requests -> get_result();
                if($result_fetch_requests -> num_rows>0)
                    while($row = $result_fetch_requests -> fetch_assoc())
                    {
                        $type=$row['request_type'];
                        $printpage = "
                        <form method='GET' action='print.php' class='float-end'>
                            <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row['request_id'].":|:$type'>
                            <i class='text-dark fas fa-print'></i>
                            </button>
                        </form>";
                        $dlt_btn2 = "";
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
    var empty = document.getElementById("replacement_1").innerHTML;
    function cret(e,item_num)
    {
        var item=document.getElementById("floatingitem"+item_num).value;
        var radio=document.getElementsByName("reason"+item_num);
        var reason;
        radio.forEach(element => {
            if(element.checked)
            {
                reason = element.value;
            }
        });
        var quan= parseInt(e.value);
        if(item!='' && reason == 'replace')
        {
            document.getElementById("replacement"+item_num).innerHTML=empty.replace("xxxx",item).replaceAll("_1",item_num);
            document.getElementById("replacement"+item_num).innerHTML=document.getElementById("replacement"+item_num).innerHTML.replaceAll("xxxx",item + ' 1');
            var data = document.getElementById('rep'+item_num);

            for(let i=1;i<quan;i++)
            {
                let tt= i+1;
                let ttdata = data.innerHTML.replaceAll("::1","::"+tt);
                document.getElementById("replacement"+item_num).innerHTML+="<div class='mb-3 input-group position-relative'>"+ttdata.replaceAll(" 1"," "+tt)+"</div>";
            }
            for(let i=1;i<=quan;i++)
                document.getElementById("input::"+i+item_num).setAttribute("required",true);
            document.getElementById("replacement"+item_num).classList.remove('d-none');
        }
        if(reason == 'new' || isNaN(quan) || quan ==0 || item=='')
        {
            document.getElementById("replacement"+item_num).innerHTML=empty.replaceAll("_1",item_num);
            document.getElementById("replacement"+item_num).classList.add('d-none');
        }
    }
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