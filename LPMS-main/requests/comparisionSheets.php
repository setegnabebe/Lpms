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
    echo "
        <div class='pricing'>
            <div class='row' id='cs_found'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    sideactive("POs");
    set_title("LPMS | View Comparision Sheet");
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
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item"><a href='../requests/requests.php' style="text-decoration: none;">Purchase Orders</a></li>
            <li class="breadcrumb-item active">View Comparision Sheets</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
<div class='row'>
   <?php 
   if($_SESSION['company']=='Hagbes HQ.'){
   echo '<form method="GET"  class="col-sm-6 col-md-3" data-aos="fade-right">'.
        '<select class="form-select text-primary mb-3" id="req_company" onchange="search_comp_sheet(this)">'.
        '<option value="all" class="text-center"> Filter by Company </option>'.
        '<option value="all"> ALL</option>'.
        $status_sql="SELECT DISTINCT (purchase_order.company) FROM `cluster` inner join purchase_order WHERE purchase_order.`procurement_company` = ? order by purchase_order.company;";
        $stmt_get_cluster_company = $conn -> prepare($status_sql);
        $stmt_get_cluster_company -> bind_param("s", $_SESSION['company']);
        $stmt_get_cluster_company -> execute();
        $result_get_cluster_company = $stmt_get_cluster_company -> get_result();
        if($result_get_cluster_company -> num_rows > 0)
            while($status_row = $result_get_cluster_company -> fetch_assoc())
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
        $status_sql="SELECT DISTINCT(`status`) FROM `cluster` WHERE `procurement_company` = ? ORDER BY `cluster`.`status` ASC";
        $stmt_get_cluster_status = $conn -> prepare($status_sql);
        $stmt_get_cluster_status -> bind_param("s", $_SESSION['company']);
        $stmt_get_cluster_status -> execute();
        $result_get_cluster_status = $stmt_get_cluster_status -> get_result();
        if($result_get_cluster_status->num_rows>0)
            while($status_row = $result_get_cluster_status->fetch_assoc())
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
                $q = "SELECT * from catagory";
                $stmt_catagory = $conn -> prepare($q);
                $stmt_catagory -> execute();
                $result_catagory = $stmt_catagory -> get_result();
                if($result_catagory -> num_rows>0)
                    while($ree = $result_catagory -> fetch_assoc())
                    {
                        $na_t_1=str_replace(" ","",$ree['catagory']);
                        $category=$ree['catagory'];
                        echo "<option title='$na_t_1"."_All' id='$na_t_1"."_All' value='$category'>$ree[display_name]</option>";
                    }?>
        </select>
        <button class='d-none' id='changed'></button>
    </form>
    <div  class='<?php echo $_SESSION['company']=='Hagbes HQ.'?'col-sm-6 col-md-3':'col-sm-6 col-md-4'?>' data-aos="fade-right">
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
        $F_cond = (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || ( ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement' && $_SESSION["company"] == "Hagbes HQ."))?"":"Where company = '". $_SESSION['company']."'";
        $sql_clus = "SELECT * FROM `cluster` $F_cond ORDER BY id DESC";
        $stmt_cluster_fetch = $conn -> prepare($sql_clus);
        // $stmt_cluster_fetch -> bind_param("s", $_SESSION['company']);
        $stmt_cluster_fetch -> execute();
        $result_cluster_fetch = $stmt_cluster_fetch -> get_result();
        $total_num = $result_cluster_fetch->num_rows;
        $per_page=(isset($_GET['per_page']))?$_GET['per_page']:40;
        $page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
        $offset=($page_num-1)*$per_page;
        $amount = ceil($total_num/$per_page);
        $_SESSION['query_cs'] = $sql_clus;
        $sql_clus .= " LIMIT $per_page";// OFFSET $offset";
        $stmt_cluster_fetch_limited = $conn -> prepare($sql_clus);
        // $stmt_cluster_fetch_limited -> bind_param("s", $_SESSION['company']);
        $stmt_cluster_fetch_limited -> execute();
        $result_cluster_fetch_limited = $stmt_cluster_fetch_limited -> get_result();
        if($result_cluster_fetch_limited->num_rows>0)
        while($r_clus = $result_cluster_fetch_limited->fetch_assoc())
        {
            $avail = true;
            $btn_close = "";
            $forbiden_stats = ['canceled','Rejected','Recollection Failed','Changed','closed','Rejected','All Payment Processed','Payment Processed','Collected-not-comfirmed','Collected','In-stock','All Complete'];
            foreach($forbiden_stats as $s)
                if(strpos($r_clus['status'],$s)!==false || $r_clus['status'] == $s) $avail = false;
            if((($_SESSION['company'] == $r_clus['procurement_company'] && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail)
            {
                $btn_close = "
                <form method='GET' action='allphp.php' class='float-end mt-3'>
                    <button class='btn btn-outline-danger btn-sm' name='close_req_clus' value='$r_clus[id]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\",\"Red\")'>Close Requests</button>
                </form>";
            }
            $stmt2 = $conn->prepare("SELECT count(DISTINCT `providing_company`) AS companies FROM `price_information` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($co_count);
            $stmt2->fetch();
            $stmt2->close();

            $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($r_type,$num_req);
            $stmt2->fetch(); 
            $stmt2->close();
            $printpage = "
                <form method='GET' action='../requests/print.php' class='float-end'>
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
                        <ul>
                            <li>Number of Items Requested : ".$num_req."</li>
                            <li>Number Of Companies : ".$co_count."</li>
                            <li>Total Price : ".number_format($r_clus['price'], 2, ".", ",")."</li>
                            <li>Status : ".$r_clus['status']."</li>
                        </ul>
                            <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                            <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                            ";
                            if($btn_close != "") $str .= $btn_close;
                    $str.= "
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
            else 
            {
                divcreate($str);
                echo ($amount<=1)?"":"
                <div id='load_more' class='container-fluid text-center'>
                    <button type='button' class='btn btn-primary' name='1' value='$amount' onclick='readmore(this)'>
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
            req.onload = function(){
                var data=this.responseText.split(":___:"); 
                if(offset)
            document.getElementById("cs_found").innerHTML+=data[0];
            else
            document.getElementById("cs_found").innerHTML=data[0];

            document.getElementById("load_more").innerHTML=data[1]?data[1]:"";

            document.getElementById("search_text").innerHTML="search result for "+comp_res+" "+status_res+" "+type_res+" "+keyword_res;
            }
            req.open("GET", "ajax_load.php?offset="+offset+"&keyword="+keyword+"&status="+status+"&type="+type+"&company="+company);
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
    function readmore(e)
    {
        let temp_name = e.innerHTML;
        let page_num = parseInt(e.name)+1;
        e.name = page_num;
        if(page_num == e.value)
            e.classList.add('d-none');
        e.innerHTML = "<i class='fa fa-spinner fa-pulse'></i> Loading";
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("cs_found").innerHTML+=this.responseText;
        e.innerHTML = temp_name;
        }
        req.open("GET", "../common/readmore_cs.php?page_num="+page_num);
        req.send();
    }
    </script>
</div>
<?php include "../footer.php"; ?>
