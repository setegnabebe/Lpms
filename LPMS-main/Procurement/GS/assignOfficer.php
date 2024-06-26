<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str,$n)
{
    if($str=='') return 0;
    echo "
        <div class='py-1 pricing'>
            <div class='row po_list'  value='0' id='assign_po'>
                $str
            </div>
        </div>
        ";
        if($n>39)
       echo "<div id='load_more' class='container-fluid text-center'>
        <button type='button' id='view_more_btn' class='btn btn-primary' name='1' value='40' onclick='load_more(this)'>
            View More
        </button>
    </div>
    ";
}
function selector()
{
    include "../../connection/connect.php";
    $sql_pos = "SELECT * FROM `purchase_order`";
    $stmt_pos = $conn->prepare($sql_pos);
    $stmt_pos -> execute();
    $result_pos = $stmt_pos -> get_result();
    if($result_pos -> num_rows>0)
        while($row = $result_pos -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            echo "
                <script>document.getElementById('".$na_t."_".$row['request_id']."').value='".$row["purchase_officer"]."'</script>
            ";
        }
}
?>
<script>
    set_title("LPMS | Assign For Proforma");
    sideactive("assign");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Assign A Purchase Officer</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Assign A Purchase Officer</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid">
<!-- <div class='nav bg-light py-3 row'> -->
    <div class='nav bg-light btn-group'  id='type_selection' title='All Requests'></div>
    <!-- <button type='button' class='btn btn-white border-primary  mx-2 rounded-pill' onclick="show(this)" id='total_b'>All Requests</button> -->
    <?php
        $condition = "(
        (request_type != 'Fixed assets' AND `status`='Approved By Director') OR 
        (request_type = 'Fixed assets' AND `status`='Approved By Owner') OR 
        (department = 'Owner' AND `status`='Approved By Property') OR 
        ((company != 'Hagbes HQ.' OR department = 'Owner' OR (director IS NOT NULL AND customer = director)) AND (
        (request_type = 'Stationary and Toiletaries' AND `status`='Approved By Property') OR 
        (request_type = 'Spare and Lubricant' AND ((requests.type = 'Spare' AND `mode` = 'Internal' AND `status`='Approved By Property') OR 
        ((`mode` = 'External' OR (`mode` = 'Internal' AND requests.type = 'Lubricant')) AND (`status`='Store Checked' OR `status`='Approved By Property')))) OR 
        (request_type = 'Tyre and Battery' AND ((`mode` = 'Internal' AND `status`='Approved By Property') OR 
        (`mode` = 'External' AND (`status`='Store Checked' OR `status`='Approved By Property')))) OR 
        ((request_type = 'Miscellaneous' OR request_type = 'Consumer Goods') AND (`status`='Store Checked' OR `status`='Approved By Property'))))
        )";
        // ((request_type != 'Miscellaneous' AND request_type != 'Consumer Goods' AND request_type != 'Spare and Lubricant' AND request_type != 'Tyre and Battery') AND $statuses) OR 
        $b_count = 0;
        $sql_assignOfficer_request_type = "SELECT request_type FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? Group By request_type";
        $stmt_assignOfficer_request_type = $conn->prepare($sql_assignOfficer_request_type);
        $stmt_assignOfficer_request_type -> bind_param("s", $_SESSION['company']);
        $stmt_assignOfficer_request_type->execute();
        $result_assignOfficer_request_type = $stmt_assignOfficer_request_type->get_result();
        if($result_assignOfficer_request_type->num_rows>0)
            while($rs = $result_assignOfficer_request_type->fetch_assoc())
            {
                $type = $rs['request_type'];
                $sql_assignOfficer_count = "SELECT count(*) AS num, stock_info, requested_quantity FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? AND  request_type = ?";
                $stmt_assignOfficer_count = $conn->prepare($sql_assignOfficer_count);
                $stmt_assignOfficer_count -> bind_param("ss", $_SESSION['company'], $type);
                $stmt_assignOfficer_count->execute();
                $result_assignOfficer_count = $stmt_assignOfficer_count->get_result();
                if($result_assignOfficer_count->num_rows>0)
                    while($r = $result_assignOfficer_count->fetch_assoc())
                    {
                        if($r['num']>0)
                        {
                            $stmt_stock -> bind_param("i", $r['stock_info']);
                            $stmt_stock -> execute();
                            $result_stock = $stmt_stock -> get_result();
                            if($result_stock -> num_rows>0)
                                while($rr = $result_stock -> fetch_assoc())
                                {
                                    $instock = $rr['in-stock'];
                                    $forpurchase = $rr['for_purchase'];
                                }
                            if($instock == $r['requested_quantity']) continue;
                            // echo "<button type='button' class='btn btn-primary border-primary mx-2 rounded-pill' onclick='show(this)'>".$type."</button>";
                            $b_count++;
                        }
                    }
            }
            // if($b_count==1)
            //     echo "<script>document.getElementById('total_b').classList.add('d-none');</script>";
        
        ?>
    <!-- </div> -->
         <!-- <div class='section-title text-center pt-2 alert-primary rounded mt-4 p-2 '>
     <h6 class='text-white' id='search_text'>All Requests for All Type</h6> 
  </div> -->
  <div class='row mt-4'>
   <?php 
   if($_SESSION['company']=='Hagbes HQ.'){
   echo '<form method="GET"  class="col-sm-6 col-md-3" data-aos="fade-right">'.
        '<select class="form-select text-primary mb-3" id="req_company" onchange="filter(this)">'.
        '<option value="all" class="text-center"> Filter by Company </option>'.
        '<option value="all"> ALL</option>'.
        $sql_companies_po="SELECT DISTINCT (purchase_order.company) FROM `cluster` inner join purchase_order WHERE purchase_order.`procurement_company` = ?";
        $stmt_companies_po = $conn->prepare($sql_companies_po);
        $stmt_companies_po -> bind_param("s", $_SESSION['company']);
        $stmt_companies_po->execute();
        $result_companies_po = $stmt_companies_po->get_result();
        if($result_companies_po->num_rows>0)
        while($status_row = $result_companies_po->fetch_assoc())
        {
        echo "<option>".$status_row['company']."</option>";
        }
       echo "</select></form>";
    }
    ?>
    <form method="GET" action='' class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
    <!-- <form action='allphp.php' class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-4':'col-sm-6 col-md-3'?>' style='width:15%;' data-aos="fade-right"> -->
         <input type="text" class='form-control text-primary' name="daterange" value="04/26/2023 - 04/26/2023 " />
        <button   class='d-none' id='date' type="button"   onclick='filter(this)' value=''></button>
        <button class='d-none' id='changed_start'></button>
    </form>
    <form class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
        <select class='form-select text-primary mb-3' id='req_type' onchange="filter(this)">
               <option value="" class='text-center'> Filter by type </option>
                <?php
                $sql_categories = "SELECT * from catagory";
                $stmt_categories = $conn->prepare($sql_categories);
                $stmt_categories->execute();
                $result_categories = $stmt_categories->get_result();
                if($result_categories->num_rows>0)
                    while($ree = $result_categories->fetch_assoc())
                    {
                        $na_t_1=str_replace(" ","",$ree['catagory']);
                        $category=$ree['catagory'];
                        echo "<option title='$na_t_1"."_All' id='$na_t_1"."_All' value='$category'>$ree[display_name]</option>";
                    }?>
        </select>
        <button class='d-none' id='changed'></button>
    </form>
    <form method="GET" action='' class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
    <div class="input-group mb-3" id="search_requests">
            <input type="text" class="form-control" placeholder="Search"
                aria-label="Search" id='req_keyword' aria-describedby="button-addon2">
                <button class="btn btn-outline-success" type="button" id="search_btn" onclick="filter(this)"><i
                    class="bi bi-search"></i></button>
        </div>
    </form>
</div>
<div class="row" id="searched">
<h4 class='mt-4 text-center d-none' id='search_text'> </h4>
<div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow shadow-warning alert-primary" style="top: 80%; left: 85%; z-index: 999;">
    <div class='mb-3'>
        <select class='form-select form-select-sm' name='batch_selection' id='batch_selection'>
            <option value=''>--Select Purchase Officer--</option>
            <?php
                $qq = "(company = '".$_SESSION['company']."'";
                $qq .= ")";
                $sql_purchase_officers = "SELECT * FROM account WHERE `role`='Purchase officer' AND `status` = 'active' AND $qq";
                $stmt_purchase_officers = $conn->prepare($sql_purchase_officers);
                $stmt_purchase_officers->execute();
                $result_purchase_officers = $stmt_purchase_officers->get_result();
                if($result_purchase_officers->num_rows>0)
                {
                    while($row = $result_purchase_officers->fetch_assoc())
                    {
                        $officer=$row['Username'];
                        echo "<option value='$officer'>$officer (Company : $row[company])</option>";
                    }
                }
            ?>
        </select>
        <input type='button' value='Assign'  data-bs-toggle='modal' data-bs-target='#priority_modal' onclick='priority(this,"batch")' class='btn btn-sm btn-outline-warning mt-3' name='Assign_batch'>
        <a  type='button' id='email_batch' class='btn btn-success btn-sm mx-auto mt-2' href='<?php "./mailingList.php?request_id="?>'>Email Vendors <i class='fa fa-envelope text-white ms-1'></i></a>
    </div>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
        <?php
                $sql_upper_limit = "SELECT * FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ?";
                $stmt_upper_limit = $conn->prepare($sql_upper_limit);
                $stmt_upper_limit -> bind_param("s", $_SESSION['company']);
                $stmt_upper_limit->execute();
                $result_upper_limit = $stmt_upper_limit->get_result();
                $upper_limit = $result_upper_limit->num_rows;
                $sql_assignOfficer = "SELECT * FROM requests Inner Join report ON requests.request_id = report.request_id WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ? 
                ORDER BY (
                    CASE 
                        When `Owner_approval_date` IS NOT NULL THEN Owner_approval_date 
                        When `Directors_approval_date` IS NOT NULL THEN Directors_approval_date 
                        When `property_approval_date` IS NOT NULL THEN property_approval_date 
                        ELSE stock_check_date    
                    END) DESC limit 40";
                $str="";
                $stmt_assignOfficer = $conn->prepare($sql_assignOfficer);
                $stmt_assignOfficer -> bind_param("s", $_SESSION['company']);
                $stmt_assignOfficer -> execute();
                $result_assignOfficer = $stmt_assignOfficer -> get_result();
                $length = $result_assignOfficer -> num_rows;
                if($result_assignOfficer->num_rows>0)
                    while($row2 = $result_assignOfficer->fetch_assoc())
                    {
                        $type = $row2["request_type"]; //request_type = '$type' AND
                        $na_t = str_replace(" ","",$type);
                        $stmt_stock -> bind_param("i", $row2['stock_info']);
                        $stmt_stock -> execute();
                        $result_stock = $stmt_stock -> get_result();
                        if($result_stock -> num_rows>0)
                            while($r = $result_stock -> fetch_assoc())
                            {
                                $instock = $r['in-stock'];
                                $forpurchase = $r['for_purchase'];
                            }
                        if($forpurchase == 0) continue;
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
                        else if($type=="Spare and Lubricant"){
                            $stmt_description->bind_param("i", $row2['request_for']);
                            $stmt_description->execute();
                            $result3 = $stmt_description->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else if($type=="Tyre and Battery")
                        {
                            $name=$row2['request_for'];
                            $res=false;
                        }
                        else 
                        {
                            $res=false;
                            $name=$row2['item'];
                        }
                        if($res)
                            while($row3 = $result3->fetch_assoc())
                            {
                                if($type=="Consumer Goods")
                                {
                                    $name = ($row2['request_for'] == 0)?$row3['Name']:$row3['project_name'];
                                }
                                else if($type=="Spare and Lubricant")
                                    $name=$row3['description'];
                            }
                            ?>
                            <?php
                            $details = "
                            I hope this message finds you well. I am writing to request your proforma for goods that my company requires. 
                            We have been impressed with the quality of your products and believe that they would be an excellent fit for our needs. Specifically, 
                            we are interested in purchasing list of goods that are listed below in detail. If possible, we would like to receive proforma for goods  by indicated  date to ensure that we can meet our company needs.
                             Proforma Request for:- 
                              Item - $row2[item],  
                               Quantity - $row2[requested_quantity] $row2[unit], 
                               Date Delivery before - ".date("d-M-Y", strtotime($row2['date_needed_by']));
                            if(isset($row2['specification']) && !is_null($row2['specification']))
                            {
                                $stmt_specification -> bind_param("i", $row2['specification']);
                                $stmt_specification -> execute();
                                $result_specification = $stmt_specification -> get_result();
                                if($result_specification -> num_rows>0)
                                    while($row_spec = $result_specification -> fetch_assoc())
                                    {
                                        $specc = str_replace("<div class=\"ql-editor\" data-gramm=\"false\" contenteditable=\"false\">","",$row_spec["details"]);
                                        $tagss = ["div","p","h1","h2","h3","h4","h5","h6"];
                                        foreach($tagss as $tag)
                                        {
                                            $specc = str_replace("<".$tag.">","",$specc);
                                            $specc = str_replace("</".$tag.">","",$specc);
                                        }
                                    }
                                $details .= "
                                Specification - ".$specc;
                            }
                            if($row2['description'] != "#" && $row2['description'] != "")
                            $details .= "
                            Given Specification - ".$row2['description'];

                            if($type=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                            
                        $printpage = "
                        <form method='GET' action='../../requests/print.php' class='float-end'>
                            <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row2['request_id'].":|:$type'>
                            <i class='text-dark fas fa-print'></i>
                            </button>
                        </form>";
                        $uname =str_replace("."," ",$row2['customer']);
                    
                            $str.= "
                            <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4'>
                                <div class='box'>
                                    <h3 class='text-capitalize'>
                                        <input value='".$na_t."_".$row2['request_id']."' name=".$row2['request_id']." class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                                        ".$name."
                                        $printpage 
                                        <span class='small text-secondary d-block mt-2'>$type <span class='text-primary'>(".$row2['company'].")</span></span>
                                        <div class=''> Share <i class='bi bi-share-fill text-dark me-1'></i> : 
                                        <a href='https://api.whatsapp.com/send?text=$details'  class='mx-auto' id='send_".$na_t."_".$row2['request_id']."'
                                        name='".$na_t."_".$row2['request_id']."' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>
                                        <a href='https://t.me/share/url?url=$details' class='mx-auto'  data-bs-target='#vendor_select' target='_blank'    id='send_".$na_t."_".$row2['request_id']."'
                                        name='".$na_t."_".$row2['request_id']."'><i class='bi bi-telegram text-primary'></i></a>
                                        </div>
                                    </h3>
                                    <form method='GET' action='allphp.php'>
                                    <ul>
                                        <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='add_btn(this)' >
                                        <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                        
                                        <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>
                                        <li class='text-start'><span class='fw-bold'>Priority : </span><span id='p_".$na_t."_".$row2['request_id']."'>Unassigned</span></li>
                                        <li class='row'>
                                        <div name='btns_assign_app'>
                                        <div class='input-group mb-3'>
                                            <select class='form-select form-select-sm' name='".$na_t."_".$row2['request_id']."' id='".$na_t."_".$row2['request_id']."'>
                                                <option value=''>--Select Purchase Officer--</option>";
                                                $stmt_purchase_officers -> execute();
                                                $result_purchase_officers = $stmt_purchase_officers->get_result();
                                                if($result_purchase_officers -> num_rows>0)
                                                {
                                                    while($row = $result_purchase_officers -> fetch_assoc())
                                                    {
                                                        $officer=$row['Username'];
                                                        $str.= "<option value='".$officer."'>$officer (Company : $row[company])</option>";
                                                    }
                                                }
                                    $str.=" </select>
                                            <input type='button' value='Assign'  data-bs-toggle='modal' data-bs-target='#priority_modal' onclick='priority(this)' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>
                                            <!--<input type='submit' value='Assign' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>-->
                                        </div>
                                        </div>
                                        <div class='divider fw-bold'>
                                            <div class='divider-text'>
                                                Or 
                                            </div>
                                        </div>
                                    <div class='mb-2' name='btns_assign_app2'>
                                        <a class='btn btn-success btn-sm mx-auto' href='./mailingList.php?request_id=".$row2['request_id']."' title='$pos' id='send_".$na_t."_".$row2['request_id']."'
                                        name='".$na_t."_".$row2['request_id']."'>Email Vendors <i class='fa fa-envelope text-white ms-1'></i></a>
                                        <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-primary text-primary fa fa-comment'></i></button>
                                        </div>
                                    </ul>
                                    </form>
                                </div>
                            </div>";
                            }
                        divcreate($str,$length);
            // }
            if($b_count==0)
            {
                echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are No Purchase Orders Pending</h3>
                </div>
            </div>";
                echo "<script>document.getElementById('total_b').classList.add('d-none');</script>";
            }
            selector();
        ?>
</div>
</div>
<script>
function add_btn(e){
 document.getElementById('optional_btn').innerHTML=document.getElementsByName('btns_assign_app')[0].innerHTML+"<div class='mb-2' ><b>OR</b> </div > "+document.getElementsByName('btns_assign_app2')[0].innerHTML
openmodal(e);
}
function filter(e){
    var comp_res="",type_res="", date_res="", keyword_res="";
    var comp=document.getElementById("req_company");
    const type=document.getElementById("req_type").value;
    const div=document.getElementById('type_selection').title = document.getElementById("req_type").value;
    const date=document.getElementById('date').value;
    const keyword=document.getElementById('req_keyword').value;
    if(keyword)
        keyword_res=" keyword = <span class='text-warning'>"+keyword+"</span>";
    date_res=date;
    if(comp){
        company=comp.value;
        if(company!="all")
            comp_res="Company ='"+company+"' ";
    }
    else
        company="";
    if(type!=""&&type!="all"){
        type_res=" type ='"+type+"'  ";
    }
    if((type==""||type=="all")&&(company==""||company=="all")&&(date==""||date==undefined)||(keyword==""))
        document.getElementById("search_text").classList.add("d-none");
    else
        document.getElementById("search_text").classList.remove("d-none");

    document.getElementById('view_more_btn').classList.add("d-none");
    const req = new XMLHttpRequest();
    req.onload = function(){
        document.getElementById("assign_po").innerHTML=req.responseText;
    //      document.getElementById("view_more_btn").classList.add('d-none');
        document.getElementById("search_text").innerHTML="search result for "+comp_res+" "+type_res+" "+date_res+" "+keyword_res;
        document.getElementById("search_text").classList.remove('d-none');
            
    }
    req.open("GET", "ajax_show.php?type="+type+"&company="+company+"&keyword="+keyword+'&start='+date.split(" to ")[0]+'&end='+date.split(" to ")[1]);   
    req.send();
}
    function show(e)
    {
        var arraycont = e.parentElement.children;
        for(var i = 0; i < arraycont.length; i++){
         arraycont[i].classList.add('btn-primary');
         }
        e.classList.replace('btn-primary','btn-white');
        const div=document.getElementById('type_selection')
        document.getElementById('search_text').innerHTML="All Requests for "+e.innerHTML;
        document.getElementById('search_text').classList.remove('d-none');
        div.title=e.innerHTML;
        const more=document.getElementById('view_more_btn');
        more.value=40;
      const req = new XMLHttpRequest();
    req.onload = function(){
     document.getElementById("assign_po").innerHTML=this.responseText;
    }
    req.open("GET", "Ajax_show.php?limit_offset="+more.value+"&type="+e.innerHTML);
    req.send();
    }

    function load_more(e){
  
        const div=document.getElementById('type_selection')
        e.value=parseInt(e.value)+40;
        // if(parseInt(e.value)>parseInt(document.getElementById('length_store').title)){
        //    e.classList.add('d-none');
        // }
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
     document.getElementById("assign_po").innerHTML=this.responseText;
    
    }
    req.open("GET", "Ajax_show.php?limit_offset="+e.value+"&type="+div.title);
    req.send();
    }
function vendors(e)
{
    document.getElementById("use_vendors").value = e.name;
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
    document.getElementById("vendor_select_body").innerHTML=this.responseText;
    }
    req.open("GET", "Ajax_vendor.php?request_Details="+e.name);
    req.send();
}
// function mail(e)
// {
//     const req = new XMLHttpRequest();
//     req.onload = function(){//when the response is ready
//     document.getElementById("email_modal_body").innerHTML=this.responseText;
//     }
//     req.open("GET", "ajax_email.php?data="+e.id);
//     req.send();
// }
</script>
<div class="modal fade" id="vendor_select">
    <form method="GET" action="allphp.php">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body" id="vendor_select_body">
                    
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success btn-sm" name='use_vendors' id='use_vendors' data-bs-dismiss="modal">Continue With Vendors</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade modal-borderless" id="priority_modal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content rounded-5">
            <form method="GET" action="allphp.php">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Priority <small class="text-secondary">(optional)</small></h4>
                    <button type='button' id='close_priority_modal' class='btn-close' data-bs-dismiss='modal'></button>
                </div>
                <div class="modal-body" id="priority">
                    <div class='d-none' id='basic'></div>
                    <p id='warn' class='text-center text-danger d-none'></p>
                    <input type='text' class='d-none' id='input_PO' required>
                    <input name='selections' id='selections' type="text" class="form-control d-none">
                    <div id='step'></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm" type="button" onclick="assign(this)" id="assign_btn">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function batch_select(e)
    {
       
        let selections = "";
        let indicator = false;
        let requestids="";
        let all_batch = document.getElementsByClassName("ch_boxes");
        for(let i=0;i<all_batch.length;i++)
        {
            if(all_batch[i].checked) 
            {
                all_batch[i].parentElement.parentElement.classList.add("border");
                all_batch[i].parentElement.parentElement.classList.add("border-2");
                all_batch[i].parentElement.parentElement.classList.add("border-primary");
                indicator=true;
                selections += (selections =="")?all_batch[i].value:","+all_batch[i].value;
                requestids+=(requestids =="")?all_batch[i].name:","+all_batch[i].name;
                
            }
            else
            {
                all_batch[i].parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("selections").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
            document.getElementById("email_batch").href='./mailingList.php?request_id='+requestids;
    }
    var backup_data,first_time =true;
    function assign(e)
    {
        // let id = e.name.replace("priority","p");
        // document.getElementById(id).innerHTML = document.getElementById("step").title;
        // document.getElementById(e.name).value = document.getElementById("step").title.split("/")[0];
        let temp_val = e.name+"_"+document.getElementById("step").getAttribute("data-rating");
        e.value = temp_val;
        // e.value = document.getElementById("step").title.split("/")[0];
 
        // e.value = document.getElementById("step").title.split("/")[0];
        if(document.getElementById('input_PO').value != "")
        {
            if(e.name != "Assign_batch")
                e.name = e.id;
            e.removeAttribute("onclick");
            prompt_confirmation(e);
            // e.type = "submit";
            // e.click();
        }
        else
        {
            document.getElementById("warn").innerHTML = "Please Go Back and Select Purchase officer";
        }
        // document.getElementById(e.name.replace("priority","p_set")).innerHTML = "<i class='ms-3 text-success fas fa-check-circle'></i>";
        // document.getElementById("close_priority_modal").click();
    }
    function priority(e,x="")
    {
        if(x=="")
        {
            let common_id = e.name.split('_')[1]+"_"+e.name.split('_')[2];
            let value = document.getElementById(common_id);
            document.getElementById('input_PO').name = common_id;
            document.getElementById('input_PO').value = value.value;
     
        }
        else
        {
            let value = document.getElementById("batch_selection");
            document.getElementById('input_PO').name = "officer";
            document.getElementById('input_PO').value = value.value;
 
        }
        // if(value.value == "")
        // {
        //     document.getElementById("warn").innerHTML = "No Purchase Officer Selected";
        //     document.getElementById("warn").classList.remove('d-none');
        // }
        // else
        // {
            document.getElementById("warn").innerHTML = "";
            document.getElementById("warn").classList.add('d-none');
            if(first_time)
            {
                backup_data = document.getElementById("step").innerHTML;
                first_time = false;
            }
            else
            {
                document.getElementById("step").innerHTML = backup_data;
            }
            document.getElementById("assign_btn").name = e.name;
            // document.getElementById("assign_btn").value = document.getElementById("selections").value;
        // }
    }
if (window.history.replaceState) {
  window.history.replaceState( null, null, window.location.href );
}
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    "showDropdowns": true,
    "linkedCalendars": false,
    "showCustomRangeLabel": false,
  }, function(start, end, label) {
    var btn=document.getElementById('date');
    btn.value=start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD');
    btn.click();
  });
});
</script>
<?php include "../../footer.php"; ?>
