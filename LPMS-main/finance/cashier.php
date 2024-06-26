<?php 
session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement") header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    // $string_inc = 'head.php';
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
    set_title("LPMS | Cashier");
    sideactive("cashier");
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
        document.getElementById("batch_prepare").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
    }
    function withholding(e)
    {
        if(e.checked)
        {
            document.getElementById("visible_"+e.id.split("_")[1]).classList.add("d-none");
            // document.getElementById("withholdingadd_"+e.id.split("_")[1]).classList.remove("d-none");
        }
        else
        {
            document.getElementById("visible_"+e.id.split("_")[1]).classList.remove("d-none");
            // document.getElementById("withholdingadd_"+e.id.split("_")[1]).classList.add("d-none");
        }
    }

    let ch_for_temp = "",Figure_for_temp = "",text_for_temp = "",withholding_temp = "";
    function set_percent(cpv)
    {
        let percent = parseFloat(document.getElementById("cheque_percent_"+cpv).value);
        // let vat = (document.getElementById("before_vat_"+cpv))?!(document.getElementById("before_vat_"+cpv).innerHTML):0;
        if(percent != 100 && document.getElementById("cheque_percent_"+cpv).value != '')
        {
            ////////////backups//////////////////////
            if(ch_for_temp == "") ch_for_temp = document.getElementById("ch_for_"+cpv).innerHTML;
            if(Figure_for_temp == "") Figure_for_temp = document.getElementById("Figure_for_"+cpv).innerHTML;
            if(withholding_temp == "") withholding_temp = document.getElementById("withholding_"+cpv).innerHTML;
            if(text_for_temp == "") text_for_temp = document.getElementById("text_for_"+cpv).innerHTML;
            ///////////////////////////////////////
            let price_before_vat = document.getElementById('pafter_vat_'+cpv).innerHTML;
            let new_price = (percent/100)*price_before_vat;
            ////////////interface//////////////////////
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
                document.getElementById('text_for_'+cpv).innerHTML = this.responseText;
            }
            req.open("GET", pos+"../common/functions.php?text_of_num="+new_price);
            req.send();
            if(document.getElementById("withhold_"+cpv)) document.getElementById("withhold_"+cpv).parentElement.classList.add('d-none');
            document.getElementById("ch_for_"+cpv).innerHTML = percent+" % Of ("+price_before_vat+") = "+new_price;
            document.getElementById('Figure_for_'+cpv).innerHTML = new_price;
            ////////////////////////////////////////////
            ///////////////backend//////////////////////
            document.getElementById("cheque_amt_"+cpv).innerHTML = new_price;
            document.getElementById("withholding_"+cpv).innerHTML = 0;
            ////////////////////////////////////////////
        }
        else
        {
            ////////////interface//////////////////////
            if(document.getElementById("withhold_"+cpv)) document.getElementById("withhold_"+cpv).parentElement.classList.remove('d-none');
            document.getElementById("ch_for_"+cpv).innerHTML = ch_for_temp;
            document.getElementById("Figure_for_"+cpv).innerHTML = Figure_for_temp;
            document.getElementById('text_for_'+cpv).innerHTML = text_for_temp;
            ////////////////////////////////////////////
            ///////////////backend//////////////////////
            document.getElementById("cheque_amt_"+cpv).innerHTML = Figure_for_temp;
            document.getElementById("withholding_"+cpv).innerHTML = withholding_temp;
            ////////////////////////////////////////////
            ///////////////reset backup//////////////////////
            ch_for_temp = "";Figure_for_temp = "";text_for_temp = "";withholding_temp = "";
            ////////////////////////////////////////////
        }
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
        <h2>Prepare Cheque</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Prepare Cheque</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_prepare' id='batch_prepare'>Prepare Cheque</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    
    <?php
        $str="";
        $sql_cheque_ready = "SELECT *,SUM(P_i.after_vat) AS price,P.cluster_id AS cluster_id,P.status AS `status`,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where (P.status = 'Finance Approved' OR P.status = 'Cheque Prepared') AND selected AND finance_company = ? Group by providing_company,P.cluster_id order by P.timestamp desc";
        $stmt_cheque_ready = $conn->prepare($sql_cheque_ready);  
        $stmt_cheque_ready -> bind_param("s", $_SESSION['company']);
        $stmt_cheque_ready -> execute();
        $result_cheque_ready = $stmt_cheque_ready -> get_result();
        if($result_cheque_ready->num_rows>0)
        while($r_clus = $result_cheque_ready->fetch_assoc())
        {
            $pos = "";
            $providing_company = (strpos($r_clus['providing_company'],"'") !== false && strpos($r_clus['providing_company'],"\'") === false)?str_replace("'","'",$r_clus['providing_company']):$r_clus['providing_company'];
            $sql_pos_pi = "SELECT *,P.purchase_order_id AS `purchase_order_id` from `price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id Where P.cluster_id = ? AND providing_company = ? AND selected order by id";
            $stmt_pos_pi = $conn->prepare($sql_pos_pi);  
            $stmt_pos_pi -> bind_param("is", $r_clus['cluster_id'], $providing_company);
            $stmt_pos_pi -> execute();
            $result_pos_pi = $stmt_pos_pi -> get_result();
            while($r_pos = $result_pos_pi->fetch_assoc())
            $pos .= ($pos == "")?$r_pos['purchase_order_id']:":-:".$r_pos['purchase_order_id'];

            $stmt_cluster -> bind_param("i", $r_clus['cluster_id']);
            $stmt_cluster -> execute();
            $result_cluster = $stmt_cluster->get_result();
            $clus_row=$result_cluster->fetch_assoc();

            $stmt_limit -> bind_param("s", $clus_row['company']);
            $stmt_limit -> execute();
            $result_limit = $stmt_limit->get_result();
            if ($result_limit->num_rows ==0)
            {
                $other = "Others";
                $stmt_limit -> bind_param("s", $other);
                $stmt_limit -> execute();
                $result_limit = $stmt_limit->get_result();
            }
            $row_limit = $result_limit->fetch_assoc();
            $price = $r_clus['price'];
            $stmt_request -> bind_param("i", $r_clus['request_id']);
            $stmt_request -> execute();
            $result_request = $stmt_request->get_result();
            $row_req = $result_request->fetch_assoc();
            $printpage = "
                <form method='GET' action='../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['cluster_id'].":|:all'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>";
                $str .= ($r_clus['status'] == "Finance Approved")?"
                <span class='small text-secondary float-start'>
                <button type='button' value='".$r_clus['cluster_id']."::-::".$pos."' class='btn btn-sm' data-bs-toggle='modal' data-bs-target='#company_select' onclick='comp_selector(this,\"".(isset($row_req['company'])?$row_req['company']:"")."\")'><i class='fa fa-share'></i></button>
                </span>":"";
                $str .= $r_clus['providing_company'].(($r_clus['status'] == 'Petty Cash Approved')?" <i class='text-primary fw-bold text-sm'>Petty Cash</i>":"")."
                $printpage
                </h3>
                <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Department : </span>".(isset($row_req['department'])?$row_req['department']:"")."</li>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Company : </span>".(isset($row_req['company'])?$row_req['company']:"")."</li>
                        <li class='text-start text-primary'><span class='fw-bold text-dark'>Total Price : </span>".number_format($price, 2, ".", ",")."</li>
                        <button type='button' name='".$r_clus['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                        <li class='mt-3'>".
                            (($r_clus['status'] == "Finance Approved")?
                            "<button class='mx-auto btn btn-outline-success btn-sm mb-3' type='button' onclick = 'pay_req(this)' value='".$r_clus['cluster_id']."::-::".$pos."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>Payment Requisition</button>
                            <button type='button' class='btn btn-outline-primary btn-sm shadow mb-3' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['cluster_id']."' >Chat <i class='text-primary fa fa-comment'></i></button>

                            <!--<button class='mx-auto btn btn-outline-success btn-sm' type='button' onclick = 'prompt_confirmation(this)' value='".$r_clus['cluster_id']."' name='prepare'>Cheque Prepared</button>-->":
                            (($r_clus['status'] == 'Petty Cash Approved')?
                            "<button class='btn btn-sm  btn-outline-success' type='button' onclick = 'prompt_confirmation(this)' value='".$r_clus['cluster_id']."::-::".$pos."' name='petty_cash'>Petty Cash Ready</button>":
                            "<i class='text-primary fw-bold'>Waiting for Signiture</i>
                            <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['cluster_id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
        
                            <button class='mx-auto btn btn-outline-success btn-sm mb-3' type='button' onclick = 'pay_req(this,\"view\")' value='".$r_clus['cluster_id']."::-::".$pos."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>
                                View CPV
                            </button>"))
                        ."</li>
                    </ul>
                </form>
                </div>
            </div>
                ";
        }
        $title = "POs waiting Cheques";
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Payment</h3>
                </div>
            </div>";
        else
            divcreate($str,$title);
        $str="";
        $sql_advances = "SELECT *,c_i.status as `status`,c_i.company as `company`,c_i.cheque_company as `cheque_company` from `cheque_info` AS c_i INNER JOIN cluster AS c on c_i.cluster_id = c.id Where prepared_percent < 100 AND c_i.status != 'All Payment Processed' AND final = 0  AND void != 1 AND finance_company = ?";
        $stmt_advances = $conn->prepare($sql_advances);  
        $stmt_advances -> bind_param("s", $_SESSION['company']);
        $stmt_advances -> execute();
        $result_advances = $stmt_advances -> get_result();
        if($result_advances -> num_rows>0)
        while($r_adv = $result_advances -> fetch_assoc())
        {
            $sql_previous_cheque = "SELECT * from `cheque_info`  Where providing_company = ? AND cluster_id = ? AND cpv_no != ?  AND void != 1";
            $stmt_previous_cheque = $conn->prepare($sql_previous_cheque);  
            $stmt_previous_cheque -> bind_param("sii", $r_adv['providing_company'], $r_adv['cluster_id'], $r_adv['cpv_no']);
            $stmt_previous_cheque -> execute();
            $result_previous_cheque = $stmt_previous_cheque -> get_result();
            if($result_previous_cheque->num_rows>0)
                $r_new = $result_previous_cheque->fetch_assoc();
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>
                ".$r_adv['providing_company']."
                </h3>
                <form method='GET' action='allphp.php'>
                    <ul>
                        <li class='text-start'><span class='fw-bold'>For Company : </span><span class='text-primary'>".$r_adv['company']."</span></li>
                        <li class='text-start'><span class='fw-bold'>Advance Paid : </span>".number_format($r_adv['cheque_amount'], 2, ".", ",")."</li>
                        <button type='button' name='".$r_adv['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                        <li class='mt-3'>".
                            ((!isset($r_new) || $r_new['status'] != "pending payment processed")?
                            "<button class='mx-auto btn btn-outline-success btn-sm mb-3' type='button' onclick = 'pay_req(this,\"advance\")' value='".$r_adv['cluster_id']."::-::".$r_adv['purchase_order_ids']."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>Payment Requisition</button>
                            <button type='button' class='btn btn-outline-primary btn-sm shadow mb-3' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_adv['id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                            ":"<i class='text-primary fw-bold'>Waiting for Signiture</i>
                            <button class='mx-auto btn btn-outline-success btn-sm mb-3' type='button' onclick = 'pay_req(this,\"advance_view\")' value='".$r_adv['cluster_id']."::-::".$r_adv['purchase_order_ids']."' name='prepare' data-bs-toggle='modal' data-bs-target='#pay_requ'>
                                View CPV
                            </button>")
                        ."</li>
                    </ul>
                </form>
                </div>
            </div>
                ";
                unset($r_new);
        }
        $title = "Advance Payed POs";
        if($str!='')
            divcreate($str,$title);
    ?>
    
</div>
</div>
<?php include '../footer.php';?>