
<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
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
            <div class='row' id='".$n."_body'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    sideactive("store");
    set_title("LPMS | Store");
    // function stockreview(e)
    // {
    //     var na_type = e.name.replace('stock','');
    //     na_type = na_type.split("_")[0];
    //     var amount = document.getElementById(e.name.replace('stock','amount')).value;
    //     var full = document.getElementById(e.name.replace('stock','full')).value;
    //     var price = 0;
    //     // var req = new XMLHttpRequest();
    //     // req.onload = function(){//when the response is ready
    //     //     document.getElementById(e.name.replace('stock','main')).remove();
    //     //         location.reload();
    //         // if(document.getElementById(na_type+"_body").childNodes.length == 0)
 
    //         // document.getElementById('wwarn').innerHTML=this.responseText;
    //     // }
    //     // req.open("GET", "allphp.php?stock="+e.name.replace('stock','')+"&amt="+amount+"&full="+full+"&av_price="+price);
    //     // req.send();
    //     document.getElementById(e.name.replace('stock','all')).value = e.name.replace('stock','')+":-:"+amount+":-:"+full+":-:"+price;
    //     prompt_confirmation(e);
    // }
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
    function instock(e)
    {
        if(e.id.includes('full'))
        {
            let val =parseInt(e.value);
            let id = e.id.replace('full','amount');
            let am = document.getElementById(id);
            if(e.checked)
            {
                am.value = val;
                am.setAttribute('readonly',true);
            }
            else
            {
                am.value = 0;
                am.removeAttribute('readonly');
            }
        }
        else if(e.id.includes('Av_price'))
        {
            if(e.value != '')
            {
                document.getElementById('warning_stock').innerHTML = '';
            }
        }
        else
        {
            let id = e.id.replace('amount','full');
            let am = document.getElementById(id);
            let val = parseInt(am.value);
            if(parseInt(e.value) == val || parseInt(e.value) > e.max)
            {
                e.value = e.max;
                am.click();
            }
            else if(e.value=='' || e.value < e.min)
                e.value= 0;
        }
    }
    function batch_select(e)
    {
        let selections = "";
        let to_replace = "";
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
        document.getElementById("batch_instock").value = selections;
        document.getElementById("batch_outofstock").value = selections;
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
        <h2>Requested Items</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Requested Items</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div class="container-fluid">
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-success shadow mt-3' name='batch_instock' id='batch_instock'>Full instock</button>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-outline-danger shadow mt-3' name='batch_outofstock' id='batch_outofstock'>Out of Stock</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <div class='nav bg-light btn-group'>
    <button type='button' class='btn btn-white border-primary  mx-2 rounded-pill' onclick="show(this)">All Requests</button>
    <?php
    $emp =true;
    
    $conditions = Gm_query($conn_fleet);
    $cond = "(($conditions AND (spec_dep IS NULL OR specification IS NOT NULL)) OR `status` = 'Specification Provided')";
    $sql = "SELECT request_type FROM requests WHERE `stock_info` IS NULL and request_type != 'Agreement' AND $cond AND `property_company` = ? Group by request_type";
    $stmt_stock_types = $conn->prepare($sql);
    $stmt_stock_types -> bind_param("s", $_SESSION['company']);
    $stmt_stock_types -> execute();
    $result_stock_types = $stmt_stock_types -> get_result();
    if($result_stock_types -> num_rows > 0)
        while($r = $result_stock_types -> fetch_assoc())
        {
            $type = $r['request_type'];
            $query = "SELECT count(*) AS num FROM requests WHERE `stock_info` IS NULL and request_type != 'Agreement' AND $cond AND `property_company` = ? AND request_type = ?";
            $stmt_stock_types_counter = $conn->prepare($query);
            $stmt_stock_types_counter -> bind_param("ss", $_SESSION['company'], $r['request_type']);
            $stmt_stock_types_counter -> execute();
            $result_stock_types_counter = $stmt_stock_types_counter -> get_result();
            if($result_stock_types_counter -> num_rows>0)
                while($r2 = $result_stock_types_counter -> fetch_assoc())
                {
                    if($r2['num']>0)
                    {
                        $emp=false;
                        echo "<button type='button' class='btn btn-primary border-primary mx-2 rounded-pill' onclick='show(this)'>".$type."</button>";
                    }
                }
        } 
    ?>
    </div>
    
    <div class="row m-auto" id='reload'>
        <?php
        $sql = "SELECT request_type FROM requests WHERE `stock_info` IS NULL and request_type != 'Agreement' AND $cond AND `property_company` = ? Group by request_type";
        $stmt_stock_types = $conn->prepare($sql);
        $stmt_stock_types -> bind_param("s", $_SESSION['company']);
        $stmt_stock_types -> execute();
        $result_stock_types = $stmt_stock_types -> get_result();
        if($result_stock_types -> num_rows>0)
            while($rs = $result_stock_types -> fetch_assoc())
            {
                $type = $rs['request_type'];
                $na_t=str_replace(" ","",$type);
                // ((spec_dep IS NOT NULL AND specification  IS NOT NULL) OR spec_dep IS NULL)
                $sql = "SELECT * FROM requests WHERE `stock_info` IS NULL and request_type != 'Agreement' AND $cond AND `property_company` = ? AND request_type = ? ORDER BY date_needed_by ASC";
                $stmt_requests_custom = $conn->prepare($sql);
                $stmt_requests_custom -> bind_param("ss", $_SESSION['company'], $rs['request_type']);
                $stmt_requests_custom -> execute();
                $result_requests_custom = $stmt_requests_custom -> get_result();
                $str="";
                if($result_requests_custom->num_rows>0)
                    while($row = $result_requests_custom->fetch_assoc())
                    {
                        if($type=="Consumer Goods"){
                            $id=$row['request_for'];
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
                        else if($type=="Spare and Lubricant"){
                            $stmt_description->bind_param("i", $row['request_for']);
                            $stmt_description->execute();
                            $result3 = $stmt_description->get_result();
                            $res=($result3->num_rows>0)?true:false;
                        }
                        else if($type=="Tyre and Battery")
                        {
                            $res=false;
                            $name=$row['request_for'];
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
                        /////////////////////////////Count on Agreso//////////////////
                        $item = str_replace("'","'",trim($row['item']));
                        $sql_agr = "SELECT * FROM store WHERE `product_descr` = ?";
                        $stmt_from_agresso = $conn->prepare($sql_agr);
                        $stmt_from_agresso -> bind_param("s", $item);
                        $stmt_from_agresso -> execute();
                        $result_from_agresso = $stmt_from_agresso -> get_result();
                        if($result_from_agresso -> num_rows < 21)
                            if(sizeof(explode(" ",$item))>1)
                            {
                                $q = "(`product_descr` LIKE '%$item%' AND `product_descr` != '$item')";
                                foreach(explode(" ",$item) as $i)
                                {
                                    if(strlen($i)>2)
                                    {
                                        $i_proccessed = trim($i);
                                        $q .= ($q == "")?"`product_descr` LIKE '%$i_proccessed%'":" OR `product_descr` LIKE '%$i_proccessed%'";
                                    }
                                }
                                if($q != "")
                                {
                                    $sql_agr = "SELECT * FROM store WHERE $q";
                                    $stmt_from_agresso_wide = $conn->prepare($sql_agr);
                                    $stmt_from_agresso_wide -> execute();
                                    $result_from_agresso_wide = $stmt_from_agresso_wide -> get_result();
                                }
                            }
                        if(isset($result_from_agresso_wide))
                            $found = $result_from_agresso -> num_rows + $result_from_agresso_wide -> num_rows;
                        else
                            $found = $result_from_agresso -> num_rows;
                        /////////////////////////////////////////////////////////////
                        if($type=="Spare and Lubricant" && strpos($row['request_for'],"None|")!==false) $name = (explode("|",$row['request_for'])[1] == 0)?$row['item']:"Job - ".explode("|",$row['request_for'])[1];
                        $str.="
                        <div class='col-sm-12 col-md-6 col-lg-4 col-xl-3 my-4' id='main".$na_t."_".$row['request_id']."'>
                            <form method='GET' action='allphp.php'>
                            <div class='box shadow'>
                            <h3 class='text-capitalize'>
                            ".((is_null($row['spec_dep']) && is_null($row['specification'])) || (!is_null($row['spec_dep']) && !is_null($row['specification']))
                            ?"<span class='small text-secondary float-start'>
                                <input value='".$row['request_id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                            </span>":
                            "")."
                            ".$name."</h3>
                            <ul>";
                            $str.=($res || $type=="Tyre and Battery" || $type=="Spare and Lubricant")?"<li class='text-start'><span class='fw-bold'>Requested Item : </span><button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                ".$row['item']."</button></li>":"";
                            $str.="<li class='text-start'><span class='fw-bold'>Amount : </span>".$row['requested_quantity']." - ".$row['unit']."</li>
                                <li class='text-start text-primary'><span class='fw-bold'>Company : </span>".$row['company']."</li>
                                <li class='text-start'><span class='fw-bold'>Status : </span>".$row['status']."</li>
                                <li class='text-center'>
                                <button type='button' data-bs-toggle='modal' data-bs-target='#instore_check_modal' onclick='check_instore(\"$row[item]\")' class='btn btn-outline-warning text-dark btn-sm shadow'>Check Store ( $found ) Found</button>
                                <button type='button'  title='".$row['description']."' value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                View Details <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                                </li>";
                                $str.=
                               
                                (!is_null($row['spec_dep']) && is_null($row['specification']))?
                                "<li class='text-start'><span class='fw-bold'>Awaiting Specification From : </span><i class='text-primary'>".$row['spec_dep']." Department</i></li>":"";
                                if((is_null($row['spec_dep']) && is_null($row['specification'])) || (!is_null($row['spec_dep']) && !is_null($row['specification'])))
                            $str.="
                                <li>
                                    <button type='button' class='btn btn-outline-success btn-sm shadow mb-3' data-bs-toggle='collapse' data-bs-target='#in".$na_t."_".$row['request_id']."' role='button' aria-expanded='false' aria-controls='in".$na_t."_".$row['request_id']."'>In-stock <i class='text-white text-white far fa-thumbs-up fa-fw'></i></button> 
                                    <button type='button' class='btn btn-outline-danger btn-sm shadow mb-3' onclick='prompt_confirmation(this)' name='out_of_stock' value='".$row['request_id']."' id='out_of_stock".$na_t."_".$row['request_id']."'>Out-of-Stock <i class='text-white text-white far fa-thumbs-down fa-fw'></i></button>
                                    <button type='button' class='btn btn-outline-primary btn-sm shadow mb-3' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>

                                    </li>
                                <li class='collapse row' id='in".$na_t."_".$row['request_id']."'>
                                    <span class='text-center fw-bold mb-3'> Amount </span>
                                    <div class=' col-10'>
                                        <div class='input-group mb-1'>
                                            <div class='input-group-text'>
                                                <input class='form-check-input mt-0' type='checkbox' value='".$row['requested_quantity']."' id='full".$na_t."_".$row['request_id']."' aria-label='Fully In-Stoke' onclick='instock(this)'> 
                                                <label for='full".$na_t."_".$row['request_id']."'> Fully</label>
                                            </div>
                                            <input type='number' value='0' min='0' step='any' max='".$row['requested_quantity']."' class='form-control' aria-label='Amount in Stock' name='stock_amount' id='amount".$na_t."_".$row['request_id']."' onchange='instock(this)' required> <!--  -->
                                        </div>
                                            <input type='number' class='form-control form-control-sm d-none' id='Av_price".$na_t."_".$row['request_id']."'>
                                    </div>
                                    <!-- <input type = 'text' class='d-none' id='all".$na_t."_".$row['request_id']."' name='all_stock_data'> -->
                                    <button type='button' class='btn btn-outline-success btn-sm shadow col-2' onclick='prompt_confirmation(this)' name='in_stock' value='".$row['request_id']."' id='in_stock".$na_t."_".$row['request_id']."'><i class='fas fa-arrow-right'></i></button> 
                                    <span id='warning_stock' class='text-center fw-bold mb-3 text-danger'></span>
                                </li>";
                        $str.="
                            </ul>
                            </div>
                            </form>
                        </div>";
                        
                    }
                    if($str!='')
                        divcreate($str,$type);
            }
            if($emp)
                echo "
                    <div class='py-5 pricing'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Requests at this Time</h3>
                        </div>
                    </div>";
        ?>
    </div>
</div>
</div>
<script>
    function check_instore(e)
    {
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("instore_check_body").innerHTML=this.responseText;
        }
        req.open("GET", "ajax_store.php?item="+e);
        req.send();
    }
    function readmore_store(e)
    {
        let val = e.value.split("_")[0];
        let page_num = parseInt(val)+1;
        e.value = page_num+"_"+e.value.split("_")[1];
        if(page_num == parseInt(e.value.split("_")[1]))
            e.classList.add('d-none');
        const req = new XMLHttpRequest();
        req.onload = function(){//when the response is ready
        document.getElementById("add_here").innerHTML+=this.responseText;
        }
        req.open("GET", "ajax_store.php?page_num="+page_num);
        req.send();
    }
</script>
<div class="modal fade" id="instore_check_modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="GET" id='instore_check_form'>
                <div class="modal-header text-center">
                    <h3 id='top_text mx-auto'>Items In Store <span class='small text-secondary'>( Agreso )</span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="instore_check_body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<?php include '../footer.php';?>