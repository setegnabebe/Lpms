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
        <div class='pricing' >
            <div class='row' id='comp_sheet_data'>
                $str
            </div>
        </div>
    ";
}
$filtered = ($_SESSION['company'] != 'Hagbes HQ.' || !(($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1));
?>
<script>
    set_title("LPMS | View Comparision Sheets");
    sideactive("viewcsheet");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Comparision Sheets</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">View Comparision Sheets</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<div class="container-fluid">
<div class='row'>
   <?php 
   if($_SESSION['company']=='Hagbes HQ.'){
   echo '<form method="GET"  class="col-sm-6 col-md-3" data-aos="fade-right">'.
        '<select class="form-select text-primary mb-3" id="req_company" onchange="search_comp_sheet(this)">'.
        '<option value="all" class="text-center"> Filter by Company </option>'.
        '<option value="all"> ALL</option>'.
        $companies_sql="SELECT DISTINCT (purchase_order.company) FROM `cluster` inner join purchase_order ".(($filtered)?"WHERE purchase_order.`procurement_company` = ?":"");
        $stmt_companies_list = $conn->prepare($companies_sql);
        if($filtered)
        $stmt_companies_list -> bind_param("s", $_SESSION['company']);
        $stmt_companies_list -> execute();
        $result_companies_list = $stmt_companies_list -> get_result();
        if($result_companies_list -> num_rows>0)
            while($status_row = $result_companies_list -> fetch_assoc())
            {
                echo "<option>".$status_row['company']."</option>";
            }
       echo "</select></form>";
    }
    ?>
    <form method="GET" action='' class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
        <select class='form-select text-primary mb-3' id='req_status' onchange="search_comp_sheet(this)">
        <option value="all" class='text-center'> Filter by status </option>
        <option value='all'> ALL</option>
        <?php
        $status_sql="SELECT DISTINCT(status) FROM `cluster` ".(($filtered)?"WHERE `procurement_company` = ?":"")." ORDER BY `cluster`.`status` ASC";
        $stmt_status_list = $conn->prepare($status_sql);
        if($filtered)
        $stmt_status_list -> bind_param("s", $_SESSION['company']);
        $stmt_status_list -> execute();
        $result_status_list = $stmt_status_list -> get_result();
        if($result_status_list -> num_rows>0)
        while($status_row = $result_status_list -> fetch_assoc())
        {
            echo "<option>".$status_row['status']."</option>";
        }
        ?>
        </select>
        <button class='d-none' id='changed'></button>
    </form>
    <form method="GET" action='' class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
        <select class='form-select text-primary mb-3' id='req_type' onchange="search_comp_sheet(this)">
               <option value="" class='text-center'> Filter by type </option>
                <?php
                $catagory_sql = "SELECT * from catagory";
                $stmt_catagory_list = $conn->prepare($catagory_sql);
                $stmt_catagory_list -> execute();
                $result_catagory_list = $stmt_catagory_list -> get_result();
                if($result_catagory_list -> num_rows>0)
                    while($row = $result_catagory_list -> fetch_assoc())
                    {
                        $na_t_1 = str_replace(" ","",$row['catagory']);
                        $category = $row['catagory'];
                        echo "<option title='$na_t_1"."_All' id='$na_t_1"."_All' value='$category'>$row[display_name]</option>";
                    }?>
        </select>
        <button class='d-none' id='changed'></button>
    </form>
    <div class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
    <div class="input-group mb-3" id="search_requests">
            <input type="text" class="form-control" placeholder="Search"
                aria-label="Search" id='req_keyword' aria-describedby="button-addon2" onkeydown='search(event)'>
                <button class="btn btn-outline-success" type="button" id="search_btn" onclick="search_comp_sheet(this)"><i
                    class="bi bi-search"></i></button>
        </div>
        </div>
</div>
<div class="row" id="searched">
<h4 class='mt-4 text-center' id='search_text'> </h4>
<div>

<?php if(isset($requests_tab)) {
    $gets = "";
    foreach($_GET as $att => $val)
    {
        $gets .= $att."=".$val."&";
    }
    ?>
    <div class='float-end col-2'><a class='btn btn-sm btn-success' href="<?php echo $pos?>../requests/requests.php?<?php echo $gets?>user="><i class='fas fa-eye'></i> My POs</a></div>
    <div class='float-end col-2'><a class='btn btn-sm btn-primary' href="<?php echo $pos?>../requests/comparisionSheets.php"><i class='fas fa-eye'></i> Comparision Sheets</a></div><?php }?>
</div>

    <?php
        $str="";
        $sql_clus = "SELECT * FROM `cluster` ".(($filtered)?"WHERE `procurement_company` = ?":"")."  ORDER BY id DESC";
        $stmt_cluster_procurement = $conn->prepare($sql_clus);
        if($filtered)
        $stmt_cluster_procurement -> bind_param("s", $_SESSION['company']);
        $stmt_cluster_procurement -> execute();
        $result_cluster_procurement = $stmt_cluster_procurement -> get_result();
        $length = $result_cluster_procurement -> num_rows;
        $sql_clus = "SELECT * FROM `cluster` ".(($filtered)?"WHERE `procurement_company` = ?":"")."  ORDER BY id DESC limit 40";//WHERE proccessing_company = '$_SESSION['company']
        $stmt_cluster_procurement = $conn->prepare($sql_clus);
        if($filtered)
        $stmt_cluster_procurement -> bind_param("s", $_SESSION['company']);
        $stmt_cluster_procurement -> execute();
        $result_cluster_procurement = $stmt_cluster_procurement -> get_result();
        $data_count=0;
        if($result_cluster_procurement -> num_rows>0)
        while($r_clus = $result_cluster_procurement -> fetch_assoc())
        {
            $sql_po = "SELECT * FROM `purchase_order` Where cluster_id = ? GROUP BY cluster_id";//WHERE proccessing_company = '$_SESSION['company']
            $stmt_po_cluster_group = $conn->prepare($sql_po);
            $stmt_po_cluster_group -> bind_param("i", $r_clus['id']);
            $stmt_po_cluster_group -> execute();
            $result_po_cluster_group = $stmt_po_cluster_group -> get_result();
            if($result_po_cluster_group -> num_rows > 0)
            {
                $r_po = $result_po_cluster_group -> fetch_assoc();
                $performa_id = $r_po['performa_id'];
                $pid = $r_po['purchase_order_id'];
                $cid = $r_po['cluster_id'];
            }

            $avail = true;
            $btn_close = "";
            $forbiden_stats = ['canceled','Rejected','Recollection Failed','Changed','closed','Rejected','All Payment Processed','Payment Processed','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            foreach($forbiden_stats as $s)
                if(strpos($r_clus['status'],$s)!==false || $r_clus['status'] == $s) $avail = false;
            if((($_SESSION['company'] == $r_clus['procurement_company'] && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail)
            {
                $btn_close = "
                <form method='GET' action='allphp.php' class='float-end mt-3'>
                    <button class='btn btn-outline-danger btn-sm' name='close_req_clus' value='$r_clus[id]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\",\"Red\")'>Close Request</button>
                </form>";
            }
            $stmt2 = $conn->prepare("SELECT count(DISTINCT `providing_company`) AS companies FROM `price_information` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($co_count);
            $stmt2->fetch();
            $stmt2->close();

            $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req,request_id as id ,company FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($r_type,$num_req,$id,$company);
            $stmt2->fetch(); 
            $stmt2->close();

            $stmt2 = $conn->prepare("SELECT item as name,department as dep FROM `requests` where `request_id`='".$id."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($name,$dep);
            $stmt2->fetch(); 
            $stmt2->close();
            // $sql_r = $sql2 = "SELECT *,r.request_id as request_id FROM requests AS r INNER JOIN report AS rep on r.request_id =rep.request_id WHERE r.request_id = '".$id ."'";
            $stmt_request_with_report -> bind_param("i", $id);
            $stmt_request_with_report -> execute();
            $result_request_with_report = $stmt_request_with_report -> get_result();
            if($result_request_with_report -> num_rows > 0)
            while($row2 = $result_request_with_report -> fetch_assoc())
            {
                if($r_type=="Consumer Goods")
                {
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
                else if($r_type=="Spare and Lubricant")
                {
                    $id=$row2['request_for'];
                    $stmt_description -> bind_param("i", $row2['request_for']);
                    $stmt_description -> execute();
                    $result3 = $stmt_description -> get_result();
                    $res = ($result3 -> num_rows > 0)?true:false;  
                }
                else if($r_type=="Tyre and Battery")
                {
                    $id=$row2['request_for'];
                    $name=$row2['request_for'];
                    $res=false;
                }
                else 
                {
                    $id=$row2['request_id'];
                    $name=$row2['item'];
                    $res=false;
                }
                if($res)
                while($row3 = $result3->fetch_assoc())
                {
                    if($r_type=="Consumer Goods")
                    {
                        $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                    }
                    else if($r_type=="Spare and Lubricant")
                        $name=$row3['description'];
                }
          }
            $printpage = "
                <form method='GET' action='../../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['id'].":|:cluster'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
                <div class='col-md-6 col-lg-3 my-4'>
                    <div class='box'>
                        <h3 class='text-capitalize'>".$r_clus['type']."
                        $printpage
                        <span class='small text-secondary d-block mt-2'>$r_type</span></h3>
                        <form method='GET' action='allphp.php'>
                        <ul class='text-start'>
                           <li class='d-none'>$r_type:-:$id:-:$name:-:$company:-:$dep</li>
                            <li>Number of Items Requested : ".$num_req."</li>
                            <li>Number Of Companies : ".$co_count."</li>
                            <li>Total Price : ".((!is_null($r_clus['price']))?number_format($r_clus['price'], 2, ".", ","):$r_clus['price'])."</li>
                            <li>Status : ".$r_clus['status']."</li>";
                            $data_count++;
                            if(isset($pid)) {
                                $str.= "
                                <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn mb-8 btn-outline-primary btn-sm shadow my-4' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                                <i class='text-white fas fa-clipboard-list fa-fw'></i></button>";
                                $str.= ($r_clus['status']=='Pending' ||$r_clus['status']=='Generated' ||$r_clus['status']=='updated')?"
                                    <li id='switch1-".$r_clus['id']."'>
                                        <button class='btn btn-sm btn-outline-warning' onClick='edit_performa(this)' type='button' data-bs-toggle='modal' data-bs-target='#view_performa' value='$pid' name='$performa_id'>Edit proforma</button>
                                    </li>":"";
                                $str.= (($r_clus['status']=='Generated'||$r_clus['status']=='Pending') && ( (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement') || $_SESSION["additional_role"]==1))?"
                                    <li id='switch2-".$r_clus['id']."'>
                                        <button class=' btn btn-sm btn-outline-info' onClick='open_for_edit(this)' title='1' type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Open for Edit</button>
                                    </li>":"";
                                if($r_clus['status']=='edit')
                                $str.= "
                                    <li class='".(($r_clus['status']=='edit')?"":"d-none")."' id='edit-".$r_clus['id']."'>
                                        <button class='mt-3 btn btn-sm btn-outline-info mt-3' onClick='compsheet_creater(this,1)'  type='button' data-bs-toggle='modal' id='$name' data-bs-target='#createCompModal' value='$pid' name='$cid'>Edit Comparision Sheet</button>
                                        <button class='mt-3 btn btn-sm btn-outline-danger mt-3' onClick='open_for_edit(this)' title='2'  type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Undo</button>
                                    </li>";
                            }
                    $str.= "</ul>
                        </form>
                    </div>
                </div>
                ";
        }
            if($str=='')
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Comparision Sheets Created</h3>
                        </div>
                    </div>";
            else {
                divcreate($str);
                echo ($length<40)?"":"
                <div id='load_more' class='container-fluid text-center'>
                    <button type='button' class='btn btn-primary' name='0' value='$length' onclick='readmore(this)'>
                        View More
                    </button>
                </div>";
            }
       
    ?>
    <script>
        function search_comp_sheet(e,offset=0){ 
            var comp_res="",status_res="",search_res="",type_res="", keyword_res="";
            var comp=document.getElementById("req_company");
           
            const status=document.getElementById("req_status").value;
            const type=document.getElementById("req_type").value;
            const keyword=document.getElementById("req_keyword").value;
     
            if(comp){
              company=comp.value;
              if(company!="all")
              comp_res="Company ='"+company+"' "
            }
            else
            company="";
            if(status!=""&&status!="all"){
                status_res=" Status ='"+status+"'  " 
            }
            if(type!=""&&type!="all"){
                type_res=" type='"+type+"'  " 
            }
            if(keyword!="")
            keyword_res="Keyword ='"+keyword+"' ";
            if((status==""||status=="all")&&(type==""||type=="all")&&(company==""||company=="all")&&(keyword==""))
             document.getElementById("search_text").classList.add("d-none");
            else{
                 document.getElementById("search_text").classList.remove("d-none");
             }
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            var data=this.responseText.split(":___:"); 
            if(offset)
            document.getElementById("comp_sheet_data").innerHTML+=data[0];
            else
            document.getElementById("comp_sheet_data").innerHTML=data[0];

            document.getElementById("load_more").innerHTML=data[1]?data[1]:"";
            document.getElementById("search_text").innerHTML="search result for "+comp_res+" "+status_res+" "+type_res+" "+keyword_res;
            }
            req.open("GET", "ajax_performa.php?offset="+offset+"&keyword="+keyword+"&status="+status+"&type="+type+"&company="+company);
            req.send();
        }
        function search(event){
       if(event.keyCode==13){
        event.preventDefault()
        search_comp_sheet(event)
       }
    }
        function read_more(e)
    {
        let page_num = parseInt(e.name)+40;
        e.name = page_num;
        search_comp_sheet(e,e.name);
    }
        function readmore(e){
        let temp_name = e.innerHTML;
        let page_num = parseInt(e.name)+40;
        e.name = page_num;
        if((e.value-page_num)<40)
            e.classList.add('d-none');
        e.innerHTML = "<i class='fa fa-spinner fa-pulse'></i> Loading";
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("comp_sheet_data").innerHTML+=this.responseText;
        e.innerHTML = temp_name;
        }
        req.open("GET", "ajax_performa.php?offset_num="+page_num);
        req.send();
        }

    function view_type(e,t)
    {
        let o = (t=='div')?'tbl':'div';
        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
        if(o == 'tbl')
            document.getElementById("search_requests").classList.add("d-none");
        else
            document.getElementById("search_requests").classList.remove("d-none");

    }


            function open_for_edit(e){
                Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            }).then((result) => {
                if (result.isConfirmed) {
                        const req = new XMLHttpRequest();
                        req.onload=function(){
                            if(this.responseText==0){
                                Swal.fire('Successful!',"Comparision Sheet "+(e.title==1?"Opend":"closed")+" Successfully",'success');
                                document.getElementById("edit-"+e.name).classList.remove("d-none");
                                document.getElementById("switch1-"+e.name).remove();
                                document.getElementById("switch2-"+e.name).remove();
                            }else{
                                Swal.fire('Fail!',"Operation was not successful",'error');
                            }
                        }
                        req.open("GET", "allphp.php?cluster="+e.name+"&option="+e.title);
                        req.send();
                    }
                });
        }
        function load_performa(e)
        {
            document.getElementById('insert_performa').value = e.name;
        }
    
        function edit_performa(e)
        {
            var id = e.name;
            var pid = e.value;
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            document.getElementById("view_performa_body").innerHTML=this.responseText;
            document.getElementById("back_from_performa").classList.add('d-none');
            }
            req.open("GET", "ajax_performa.php?id="+id+"&pid="+pid+"&edit=");
            req.send();
        }
        function remove_performa(e)
        {
            Swal.fire({
                title: "Are you sure? ",
                text: "you wish to countinue",
                icon: "warning",
                showCancelButton: true,
                buttons: true,
                buttons: ["Cancel", "Yes"]
            }).then((result) => {
                if (result.isConfirmed) {
                        const req = new XMLHttpRequest();
                        req.onload = function(){//when the response is ready
                            if(this.responseText == "success")
                            {
                                var pid = e.title;
                                var id = e.value.split("_")[1];
                                const req = new XMLHttpRequest();
                                req.onload = function(){//when the response is ready
                                document.getElementById("view_performa_body").innerHTML=this.responseText;
                                document.getElementById("back_from_performa").classList.add('d-none');
                                // document.getElementById("back_from_performa").setAttribute("data-bs-target","#"+loc);
                                }
                                // req.open("GET", "ajax_performa.php?id="+id+"&pid="+pid);
                                req.open("GET", "ajax_performa.php?id="+id+"&pid="+pid);
                                req.send();
                            }
                            else
                                console.log(this.responseText);
                        }
                        req.open("GET", "allphp.php?remove_p="+e.value);
                        req.send();
                    }
                });
        }
 
///////////////////////////////////////////////////Global Declaration//////////////////////////////////////////////////////////
var i=1;
var j=1;
var bool_changed=false;
var options_changed=false;
// const options_changed = [];
var holder,item_holder,options;
var stat = [];
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////Final Fix before Submiting the Form/////////////////////////////////////////////
function final_send(e)
{
    stat.forEach(indiv => {
        if(indiv)
        {
            let all_check = document.getElementsByName(stat.indexOf(indiv));
            all_check.forEach(element => {
                element.name +="[]";
            });
        }
    });
    // if(document.getElementById('performa_data').value != "")
    // {
        e.type='submit';
        e.click();
    // }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////Hold Dataa for Replication/////////////////////////////////////////////
function hold()
{
    if(!bool_changed)
    {
        bool_changed=true;
        holder= document.getElementById('company1').innerHTML;
        item_holder=document.getElementById('item 0').innerHTML;
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////ADD ITEM///////////////////////////////////////////////////////////////
function adder(e)
{
    hold();
    var comp_num = e.id;
    comp_num = comp_num.replace('add','');
    var val = parseInt(document.getElementById('item_count'+comp_num).value);
    var item_code=item_holder.replace("col-md-12","col-md-11");
    item_code=item_code.replace(id='1floatingitem0',comp_num+"floatingitem"+i);
    item_code=item_code.replace(id='1floatingprice0',comp_num+"floatingprice"+i);
    item_code=item_code.replace(id='1floatingtp0',comp_num+"floatingtp"+i);
    // item_code=item_code.replace(id='1spec0',comp_num+"spec"+i);
    item_code=item_code.replace(id='1itemselected0',comp_num+"itemselected"+i);
    item_code=item_code.replace("1itemselected0", comp_num+"itemselected"+i);
    item_code=item_code.replace("1floatingquan0", comp_num+"floatingquan"+i);
    item_code=item_code.replace("1floatingquan0", comp_num+"floatingquan"+i);
    item_code=item_code.replace("1spec0", comp_num+"spec"+i);
    item_code=item_code.replace("1spec0", comp_num+"spec"+i);
    item_code=item_code.replace("1warning0", comp_num+"warning"+i);
    item_code=item_code.replace("1quan0", comp_num+"quan"+i);

    
    const div =  document.createElement('div');
    div.id = 'item '+i;
    div.className='row bg-light py-3 mt-3 mx-auto';
    div.innerHTML = item_code + "<div class='col-sm-1'><button class='btn btn-danger btn-sm mb-2' type='button' onclick='remove(this)' id='remove"+comp_num+"'>X</button></div>";
    i++;
    document.getElementById('company'+comp_num).appendChild(div);
    document.getElementById('item_count'+comp_num).value=(isNaN(val))?1:val+1;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////Delete Item////////////////////////////////////////////////////////////////
function remove(e) 
{
    var comp_num = e.id;
    comp_num = comp_num.replace('remove','');
    var val = parseInt(document.getElementById('item_count'+comp_num).value);
    document.getElementById('item_count'+comp_num).value=(isNaN(val))?1:val-1;
    e.parentElement.parentElement.remove();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////Change Company to Company//////////////////////////////////////////////////////////
function activeco(e)
{
    let alldiv = document.getElementById('createCompModal_body').children;
    for(var k=1 ; k<alldiv.length;k++)
    {
        alldiv[k].className='d-none';
        document.getElementById('Company_'+k+'_tab').className = "nav-link text-dark fw-bold";
    }
    var id_comp = e.id.replace("Company_","").replace("_tab","");
    document.getElementById('company'+id_comp).removeAttribute("class");
    document.getElementById('Company_'+id_comp+'_tab').className = "nav-link active bg-warning text-black fw-bold";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////Set Company Name///////////////////////////////////////////////////////////////////////////////
function company_name(e)
{
    let c_num = e.id.replace('floatingco','');
    document.getElementById('Company_'+c_num+'_tab').innerHTML=(e.value=='')?"Company "+c_num:document.getElementById('Company_'+c_num+'_tab').innerHTML=e.value.charAt(0).toUpperCase() + e.value.slice(1);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////ADD COMPANY//////////////////////////////////////////////////////////////////////////
function add_company()
{
////////////////////###########add Tab for company###########///////////////////////////////////
    j++;
    const li =  document.createElement('li');
    const btn = document.createElement('button');
    const txt = document.createTextNode('Company '+j);
    li.className="nav-item";
    btn.id='Company_'+j+'_tab';
    btn.appendChild(txt);
    btn.type='button';
    btn.setAttribute('onclick','activeco(this)')
    btn.className="nav-link text-dark";
    li.appendChild(btn);
    document.getElementById('company_tab').insertBefore(li,document.getElementById('add_company'));
    document.getElementById('remove_company').className="nav-item";

////////////////////###########add new company Div###########///////////////////////////////////

    const div =  document.createElement('div');
    div.id='company'+j;
    div.className="d-none";
    div.innerHTML=(bool_changed)?holder:document.getElementById('company1').innerHTML;
    div.innerHTML = div.innerHTML.replace("add1","add"+j);
    div.innerHTML = div.innerHTML.replace("item_count1","item_count"+j);
    div.innerHTML = div.innerHTML.replaceAll("1floatingco",j+"floatingco");
    div.innerHTML = div.innerHTML.replace("1floatingitem0",j+"floatingitem0");
    div.innerHTML = div.innerHTML.replace("1floatingprice0",j+"floatingprice0");
    div.innerHTML = div.innerHTML.replace("1floatingtp0",j+"floatingtp0");
    div.innerHTML = div.innerHTML.replace("1spec0",j+"spec0");
    div.innerHTML = div.innerHTML.replace("1spec0",j+"spec0");
    div.innerHTML = div.innerHTML.replace("1itemselected0",j+"itemselected0");
    div.innerHTML = div.innerHTML.replace("1itemselected0", j+"itemselected0");
    div.innerHTML = div.innerHTML.replace("1floatingquan0", j+"floatingquan0");
    div.innerHTML = div.innerHTML.replace("1floatingquan0", j+"floatingquan0");
    div.innerHTML = div.innerHTML.replace("1warning0", j+"warning0");
    div.innerHTML = div.innerHTML.replace("1quan0", j+"quan0");
    document.getElementById('createCompModal_body').appendChild(div);
    activeco(document.getElementById('Company_'+j+'_tab'));
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////Delete Company////////////////////////////////////////////////////////////////
function remove_company()
{
    if(j>1)
    {
        document.getElementById('Company_'+j+'_tab').remove();
        document.getElementById('company'+j).remove();
        j--;
        activeco(document.getElementById('Company_'+j+'_tab'));
    }
    if (j==1)
        document.getElementById('remove_company').className="nav-item d-none";
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////When Item Selected/////////////////////////////////////////////////////////////////////
    

    
    
    function green(e)
    {
        //####################################declarations######################################//

        var num = e.id.split("itemselected");//////////////get Company and Item Number
        var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);////////SElect Box for the Item
        let t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);////////////Total Quantity
        let req = parseFloat(document.getElementById(num[0]+'floatingquan'+num[1]).value);////////Amount on Current Item
        var all_com = document.getElementsByName(e.name);//all company that offeres that item
        let agg=0;///////////To check Aggrigate
        var tempp = e.parentElement.parentElement.parentElement;///////////////////////////////Current Item Box

        //#####################################################################################//

        //////////////////////////////////////////////////////////////////////If Being Selected//////////////////////////////////////////////////////////////
        if(e.checked)
        {
            //////////////////////////////////////////Partly Supplied////////////////////////////////////////////////////////////////
            if(t_req > req)
            {
                stat[e.name]=true;///////////Hold Partly Submitted Items 
                ////////////////////Change into Checkbox////////////////////
                all_com.forEach(com => {
                    com.type='checkbox';
                    com.removeAttribute("required");
                });
                ////////////////////////////////////////////////////////////

                /////////////////////Calculate Aggrigate////////////////////
                all_com.forEach(com => {
                    if(com.checked)
                    {
                        let t_num = com.id.split("itemselected");
                        agg+=parseFloat(document.getElementById(t_num[0]+'floatingquan'+t_num[1]).value);
                    }
                });
                /////////////////////////////////////////////////////////////

                /////////////////////OVER Aggrigate/////////////////////////
                if(agg>t_req)
                {
                    all_com.forEach(com => {
                        if(com.checked)
                        {
                            let t_num = com.id.split("itemselected");
                            let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                            warn.innerHTML='*Aggrigate Amount is Greater Than Requested';
                            warn.classList.add('text-danger');
                        }
                        else
                        {
                            let t_num = com.id.split("itemselected");
                            let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                            warn.innerHTML='';
                        }
                    });
                }
                /////////////////////////////////////////////////////////////
                /////////////////////Not Over///////////////////////////////
                else 
                {
                    //////////////////Fully Supplied in aggrigate/////////////////////
                    // if(agg==t_req)
                    // {
                    //     all_com.forEach(com => {
                    //         com.parentElement.parentElement.parentElement.classList.remove('alert-warning');
                    //         com.parentElement.parentElement.parentElement.classList.add('alert-success');
                    //     });
                    // }
                    //////////////////////////////////////////////////////////////
                    all_com.forEach(com => {
                        let t_num = com.id.split("itemselected");
                        let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                        warn.innerHTML='';
                    });
                }
                //////////////////////////////////////////////////////////////

                ////////////////////////////Remove Colors/////////////////////
                all_com.forEach(com => {
                    com.parentElement.parentElement.parentElement.classList.remove('alert-success');
                    com.parentElement.parentElement.parentElement.classList.remove('alert-danger');
                    com.parentElement.parentElement.parentElement.classList.remove('bg-light');
                    // btn_r.parentElement.parentElement.parentElement.classList.add("alert-danger");
                });
                ///////////////////////////////////////////////////////////////

                ////////////////////////Main Coloring ////////////////////////
                if(!tempp.className.includes('alert-warning'))
                    tempp.classList.add("alert-warning");
                else
                {
                    tempp.classList.add("bg-light");
                    tempp.classList.remove('alert-warning');
                }
                ////////////////////////////////////////////////////////////
            }

            else
            {
            /////////////////////////////////////////////////Fully Supplied/////////////////////////////////////////
                if(stat[e.name])//////////////if it was Partially before/////////
                    stat[e.name]=false;
                    
                    all_com.forEach(com => {
                        com.type='radio';
                        // com.setAttribute("required",true);
                        com.parentElement.parentElement.parentElement.classList.remove('alert-warning');
                        com.parentElement.parentElement.parentElement.classList.remove("alert-danger");
                        com.parentElement.parentElement.parentElement.classList.remove('bg-light');
                });
                if(tempp.className.includes('alert-success'))
                {
                    e.checked = false;
                    tempp.classList.remove('alert-success');
                    tempp.classList.add("bg-light");
                }
                else
                {
                    all_com.forEach(com => {
                        com.parentElement.parentElement.parentElement.classList.add("alert-danger");
                        com.parentElement.parentElement.parentElement.classList.remove('alert-success');
                    });
                    tempp.classList.remove('alert-danger');
                    tempp.classList.remove('bg-light');
                    tempp.classList.add("alert-success");
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else
        {/////////////////////////not selected check
            let r_buttons = document.getElementsByName(e.name);
            var tempp = e.parentElement.parentElement.parentElement;
            r_buttons.forEach(btn_r => {
                if(btn_r.parentElement.parentElement.parentElement.className.includes('alert-success'))
                {
                    tempp.classList.remove('bg-light');
                    tempp.classList.add('alert-danger');
                }
                else
                {
                    tempp.classList.remove('alert-danger');
                    tempp.classList.remove('alert-warning');
                    tempp.classList.remove('alert-success');
                    tempp.classList.add('bg-light');
                }
            });
            let agg=0;
            r_buttons.forEach(btn_r => {
                    if(btn_r.checked)
                    {
                        let t_num = btn_r.id.split("itemselected");
                        agg+=parseFloat(document.getElementById(t_num[0]+'floatingquan'+t_num[1]).value);
                    }
                });
                if(agg<=t_req)
                {
                    r_buttons.forEach(btn_r => {
                        let t_num = btn_r.id.split("itemselected");
                        let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                        warn.innerHTML='';
                    });
                }
        }
    }
    function t_p(e)
    {
        if(e.id.includes("floatingprice"))
        {
            var num = e.id.split("floatingprice");
            var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);
            t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);
            req = document.getElementById(num[0]+'floatingquan'+num[1]).value;
        }
        else if(e.id.includes("floatingquan"))
        {
            var num = e.id.split("floatingquan");
            var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);
            t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);
            req = e.value;
            hold(); 
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            obj_selector.setAttribute("name",document.getElementById(num[0]+'floatingitem'+num[1]).value);
            obj_selector.setAttribute("value",num[0]);
            if(e.value=='')
            {
                obj_selector.setAttribute("type",'radio');
                obj_selector.setAttribute("disabled",true);
                obj_selector.checked = false;
            }
        }
        else
        {
            var num = e.id.split("floatingitem");
            t_req = parseFloat(e.options[e.selectedIndex].className);
            hold(); 
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            obj_selector.setAttribute("name",document.getElementById(num[0]+'floatingitem'+num[1]).value);
            obj_selector.setAttribute("value",num[0]);
            if(e.value=='none')
            {
                obj_selector.setAttribute("type",'radio');
                obj_selector.setAttribute("disabled",true);
                obj_selector.checked = false;
            }
            else
            {
                /////////////////////////////////////////////////////////////////////////
                add_vat_item(e);
                /////////////////////////////////////////////////////////////////////////
                if(stat[document.getElementById(num[0]+'floatingitem'+num[1]).value])
                {
                    obj_selector.removeAttribute("required");
                    obj_selector.type='checkbox';
                }
            }
            if(!isNaN(t_req))
            {
                e.title =  e.options[e.selectedIndex].title;
                let unit = e.options[e.selectedIndex].id;
                document.getElementById(num[0]+'quan'+num[1]).innerHTML = 'Quantity - '+t_req+' '+unit;
            }
            else
                document.getElementById(num[0]+'quan'+num[1]).innerHTML = '';
            green(document.getElementById(num[0]+'itemselected'+num[1]));
            // var val = parseInt(document.getElementById('item_count'+num[0]).value);
            // save_options(document.getElementById(num[0]+'floatingitem'+num[1]));
            // for(let items = 0; items < val; items++)
            // {
            //     if(e.selectedIndex == 0) document.getElementById(num[0]+'floatingitem'+items).innerHTML = options;
            //     if(num[1] != items)
            //     {
            //         document.getElementById(num[0]+'floatingitem'+items).remove(e.selectedIndex);
            //     }
            // }
        }
        if(document.getElementById(num[0]+'floatingitem'+num[1]).value!='none' && document.getElementById(num[0]+'floatingquan'+num[1]).value!='')
        {
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            //if(obj_selector.type=='radio') obj_selector.setAttribute("required",true);
                obj_selector.removeAttribute("disabled");
        }
        var warn = document.getElementById(num[0]+'warning'+num[1]);
        if(document.getElementById(num[0]+'floatingquan'+num[1]).value!="" && document.getElementById(num[0]+'floatingitem'+num[1]).value!="none")
        {
            if(t_req <document.getElementById(num[0]+'floatingquan'+num[1]).value)
            {
                warn.innerHTML='* Amount is Greater Than Requested';
                warn.classList.add('text-danger');
            }
            else
            {
                warn.innerHTML='';
            }
        }
        else
        {
            warn.innerHTML='';
        }
            document.getElementById(num[0]+'floatingtp'+num[1]).value=req * document.getElementById(num[0]+'floatingprice'+num[1]).value;
        // if(document.getElementById(num[0]+'floatingprice'+num[1]).value=="" || document.getElementById(num[0]+'floatingitem'+num[1]).value=="none")
        //     document.getElementById(num[0]+'floatingtp'+num[1]).value="";
    }
    function save_options(e)
    {
        if(!options_changed)
        {
            options_changed=true;
            options = e.innerHTML;
        }
    }
    var count_vat = 0;
    function add_vat_item(e)
    {
        ////////////////////////
        let vat_found = false;
        let all_vats = document.getElementsByClassName('vats');
        for(var i=0; i<all_vats.length;i++)
        {
            if(all_vats[i].value == e.value) vat_found = true;
        }
        if(!vat_found)
        {
            count_vat++;
            let add_html = vat_template.replaceAll('::0',"::"+count_vat);
            const div =  document.createElement('div');
            div.id = 'vats_'+count_vat;
            div.innerHTML = add_html;
            document.getElementById('vat_holder').appendChild(div);
            document.getElementById('vat_lbl::'+count_vat).innerHTML = e.options[e.selectedIndex].innerHTML;
            document.getElementById('vat_for::'+count_vat).value = e.value;
        }
    }
 
    </script>
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
<script>
    var vat_template = document.getElementById('vat_sample').innerHTML;
    document.getElementById('vat_sample').remove();
</script>