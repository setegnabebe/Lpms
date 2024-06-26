<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include '../common/functions.php';
    if(strpos($_GET['cluster_id'],"CPV-") !== false)
    {
        $Currentcpv_no =  explode("-", $_GET['cluster_id'])[1];
        $cpv_no = $Currentcpv_no;
    }
    else
    {
        $cluster_id = explode("::-::", $_GET['cluster_id'])[0];
        $poss = explode("::-::", $_GET['cluster_id'])[1];
    }
    $count = 0;
    $sql_cheque = "SELECT * FROM `cheque_info` ORDER By cpv_no DESC LIMIT 1";
    $stmt_latest_cpv = $conn->prepare($sql_cheque);
    $stmt_latest_cpv -> execute();
    $result_cheque  = $stmt_latest_cpv -> get_result();
    if(!isset($Currentcpv_no))
    {
        if($result_cheque -> num_rows > 0)
        {
            $row_cheque = $result_cheque -> fetch_assoc();
            $cpv_no = $row_cheque['cpv_no'];
            echo "<p class='text-end text-primary'>Last CPV Number : $cpv_no</p>";
        }
        else
        {
            $cpv_no = 0;
            echo "<p class='text-end'>No Previous CPVs</p>";
        }
        $cpv_no++;
    }
if(isset($Currentcpv_no))
{
    $sql_cheque = "SELECT * FROM `cheque_info` where cpv_no = ?";
    if(isset($_GET['advance'])) $sql_cheque .=" AND final = 1";
    $stmt_cheques = $conn->prepare($sql_cheque);
    $stmt_cheques->bind_param("i", $Currentcpv_no);
    $stmt_cheques->execute();
    $result_cheque = $stmt_cheques -> get_result();
}
else
{
    $sql_cheque = "SELECT * FROM `cheque_info` where cluster_id = ? AND purchase_order_ids = ? AND void != 1";
    if(isset($_GET['advance'])) $sql_cheque .=" AND final = 1";
    $stmt_cheques = $conn->prepare($sql_cheque);
    $stmt_cheques -> bind_param("is", $cluster_id, $poss);
    $stmt_cheques -> execute();
    $result_cheque = $stmt_cheques -> get_result();
}
    $cpvs_count = $result_cheque->num_rows;
    if($cpvs_count>0)
    {
        $row_cheque = $result_cheque->fetch_assoc();
        $cluster_id = $row_cheque['cluster_id'];
        $poss = $row_cheque['purchase_order_ids'];
        $req_comp = $row_cheque['company'];
        $providing_company = $row_cheque['providing_company'];
        if(!isset($_GET['view']) || (!$row_cheque['void'] && $row_cheque['status'] == 'pending' && is_null($row_cheque['cheque_amount'])))
        {
            $create_cheque = "
                    <form method='GET' action='../finance/allphp.php' class='col-lg-3'> 
                        <button class='btn btn-success mb-3' value='$cluster_id::-::$poss::-::$row_cheque[cpv_no]' name='prepare' type='button' onclick='prompt_confirmation(this)'>
                            Cheque Created
                        </button>
                    </form>
                ";   
            $size_btn = "6";
        }
        else
        {
            $create_cheque = "";
            $size_btn = "6";
        }
        $adv = (isset($_GET['advance']))?"<input type='hidden' name='advance' value='set'>":"";
        // if(!isset($Currentcpv_no))
        $voider = ($row_cheque['void'])?"":"
        <form method='GET' action='../finance/allphp.php' class='col-lg-3'> 
            <button class='btn btn-danger mb-3' value='$row_cheque[cpv_no]' name='void' type='button' onclick='prompt_confirmation(this)'>
                Void Cheque
            </button>
        </form>";
        echo "
        <div class='row mt-4 border-0 text-center'>
            <form action='../finance/print.php' method='GET' class='col-lg-$size_btn'>
                $adv
                <button class='btn btn-Primary mb-3' name='print_cpv' value='$cluster_id,$row_cheque[purchase_order_ids]'>Print CPV</button>
            </form>
            $voider
            $create_cheque
        </div><hr>";   
    }
        $count++;
        $items = "";
        $price_before_vat = 0;
        $price_after_vat = 0;
        $s_po = explode(":-:",$poss)[0];
        $pos = "";
        $sql_price = "SELECT * FROM `price_information` where cluster_id = ? and purchase_order_id = ? and selected";
        $stmt_price_selected = $conn->prepare($sql_price);
        $stmt_price_selected -> bind_param("ii", $cluster_id, $s_po);
        $stmt_price_selected -> execute();
        $result_price_selected = $stmt_price_selected -> get_result();
        $row_price = $result_price_selected->fetch_assoc();
        $providing_company = $row_price['providing_company'];
        $providing_company_q = str_replace("'","'",$providing_company);
        $sql_price_bycompany = "SELECT * FROM `price_information` where cluster_id = ? and providing_company = ? and selected";
        $stmt_price_bycompany = $conn->prepare($sql_price_bycompany);
        $stmt_price_bycompany -> bind_param("is", $cluster_id, $providing_company_q);
        $stmt_price_bycompany -> execute();
        $result_price_bycompany = $stmt_price_bycompany -> get_result();
        while($row_price = $result_price_bycompany->fetch_assoc())
        {
            // $providing_company = $row_price['providing_company'];
            $pos .= ($pos == "")?$row_price['purchase_order_id']:":-:".$row_price['purchase_order_id'];
            $stmt_po -> bind_param("i", $row_price['purchase_order_id']);
            $stmt_po -> execute();
            $result_po = $stmt_po -> get_result();
            $row_po = $result_po->fetch_assoc();
            if($row_po['status'] != "canceled")
            {
                if(!isset($req_comp ))
                    $req_comp = $row_po['company'];
                if($row_po['request_type'] == 'Tyre and Battery')
                {
                    $req_comp = $row_po['finance_company'];
                }
                $stmt_request -> bind_param("i", $row_po['request_id']);
                $stmt_request -> execute();
                $result_request = $stmt_request -> get_result();
                $row_req = $result_request->fetch_assoc();
                
                $items .= ($items=="")?$row_req['item']:" AND ".$row_req['item'];
    
                $price_before_vat += $row_price['total_price'];
                $price_after_vat += $row_price['after_vat'];
            }
        }
        if($price_before_vat <= 10000){
            $withholding = 0;  
        }else{
            $withholding = 0.02 * $price_before_vat;
        } 

        $cheque_for = $price_after_vat - $withholding;
        $date = date("d/m/Y");
        $prepared_by = $_SESSION['username'];
        $sql_cheque = "SELECT * FROM `cheque_info` where cluster_id = ? AND purchase_order_ids = ?";
        $sql_cheque .= (isset($Currentcpv_no))?" AND cpv_no = ".$Currentcpv_no:" AND void != 1";
        if(isset($_GET['advance'])) 
        {
            $fisrt_cpv = $sql_cheque;
            $sql_cheque .= " AND final = 1";
        }
        $stmt_cheques = $conn->prepare($sql_cheque);
        $stmt_cheques -> bind_param("is", $cluster_id, $pos);
        $stmt_cheques -> execute();
        $result_cheque = $stmt_cheques -> get_result();
        if($result_cheque -> num_rows > 0)
        {
            $row_cheque = $result_cheque->fetch_assoc();
            $date = date("d/m/Y",strtotime($row_cheque['creation_date']));
            $found_cpv_no = $row_cheque['cpv_no'];  
            $cheque_no = $row_cheque['cheque_no'];  
            $percent = $row_cheque['prepared_percent'];
            $cheque_for = $row_cheque['cheque_amount'];
            $withholding = $row_cheque['withholding'];
            $bank = $row_cheque['bank']; 
            $prepared_by = $row_cheque['created_by'];
            $found = true; 
        }
        else
        {
            $found = false; 
        }
        if(isset($_GET['advance']))
        {
            $stmt_cheques = $conn->prepare($fisrt_cpv);
            $stmt_cheques -> bind_param("is", $cluster_id, $pos);
            $stmt_cheques -> execute();
            $result_cheque = $stmt_cheques -> get_result();
            $row_cheque = $result_cheque -> fetch_assoc();
            $cheque_for = ($found)?$cheque_for:$cheque_for-$row_cheque['cheque_amount'];
            $new_percent = 100 - floatVal($row_cheque['prepared_percent']);
        }
        $advance = (isset($percent) && $percent != 100)?"( Advance Payment )":"";
        // $hide = ($count != 1)?" d-none":"";  id='payment_req_$count'
        $visible = ($withholding == 0)?"":"d-none";
        echo "
            <ul class= 'list-group list-group-flush fs-5' id='cpv_form_$cpv_no'>
                <h4 class='text-capitalize mt-4 text-center text-primary' id='reqcomp_$cpv_no'>$req_comp</h4>
                <h4 class='text-capitalize my-4 text-center'>Cheque, C.P.O & Tansfer Payment Requisition Form</h5>
                <h5 class='text-end mb-4'>Date : $date</h5>
                <li class='list-group-item list-group-item-light mt-4 ms-4 border-0'><b>Pay For : </b><span> $providing_company</span></li>
                <li class='list-group-item list-group-item-light ms-4 border-0'><b>In Figure : </b><span id='Figure_for_$cpv_no'>".number_format($cheque_for, 2, ".", ",")."</span></li>
                <li class='list-group-item list-group-item-light ms-4 border-0'><b>In Words : </b><span id='text_for_$cpv_no'>".number_converter($cheque_for)."</span></li>
                <li class='list-group-item list-group-item-light ms-4 border-0'><b>Purpose : </b><span>for Purchase of $items as per the attached document</span></li>
                <li class='list-group-item list-group-item-danger text-sm ms-4 border-0 text-center $visible' id='visible_$cpv_no'><b>No Withholding</b></li>
                <li class='list-group-item list-group-item-light mt-4 fw-bold border-top'>";
                if($withholding != 0)
                    echo "<p class='my-4 withholder'>Cash Withholding :<span class='text-dark' id='withhold_$cpv_no'>  2% Of (".number_format($price_before_vat, 2, ".", ",").")  = ".number_format($withholding, 2, ".", ",")."</span></p>";             
                if(isset($percent) && $percent != 100 && !isset($_GET['advance']) && $found)
                    echo "<p class='my-4'>Cheque Prepared For :<span class='text-dark' id='ch_for_$cpv_no'>  $percent % Of (".number_format($price_after_vat, 2, ".", ",").") = ".number_format($cheque_for, 2, ".", ",")."</span></p>";  
                else
                    echo "<p class='my-4'>Cheque Prepared For :<span class='text-dark' id='ch_for_$cpv_no'>  ".number_format($price_after_vat, 2, ".", ",")."<span class='withholder'> - ".number_format($withholding, 2, ".", ",").((isset($_GET['advance']))?" - ".number_format($row_cheque['cheque_amount'], 2, ".", ",")." ( Advance )":"")." = ".number_format($cheque_for, 2, ".", ",")."</span></span></p>";
                echo "</li>
                <li class='d-none'><span id='pafter_vat_$cpv_no'>$price_after_vat</span><span id='pbefore_vat_$cpv_no'>$price_before_vat</span><span id='cheque_amt_$cpv_no'>$cheque_for</span><span id='withholding_$cpv_no'>$withholding</span><span id='cluster_$cpv_no'>$cluster_id</span><span id='pos_$cpv_no'>$pos</span><span id='providing_company_$cpv_no'>$providing_company</span></li>
                <li class='list-group-item list-group-item-light border-0 text-start ms-4 mt-4'><b>Prepare By  : <i class='text-primary'>$prepared_by</i></b></li>
                <div id='cheque_no_form_$cpv_no'>";
                if($found)
                {
                    echo "<li class='list-group-item list-group-item-light border-0 text-start ms-4 mt-4'><b>CPV  : <i class='text-primary'>$found_cpv_no</i></b></li>";
                    echo "<li class='list-group-item list-group-item-light border-0 text-start ms-4 mt-4'><b>Cheque Number  : <i class='text-primary'>$cheque_no</i> $bank</b></li>";
                }
                else
                {
                    echo "
                    <li class='list-group-item-light border-0 text-start ms-4 mt-4 row'>
                    <p class='text-danger d-none' id='warn_cheque_$cpv_no'></p>
                    <div class='form-floating mb-3 col-lg-5'>
                        <input type='text' class='form-control mb-2' name='cheque_no' id='cheque_no_$cpv_no'>
                        <label for='cheque_no_$cpv_no'>Cheque Number</label>
                    </div>
                    <div class='mb-3 col-lg-5'>
                            <select class='form-select' name='bank[]'  id='bank_for_$cpv_no'>";
                            $sql_banks = 'SELECT * FROM `banks`';
                            $stmt_banks = $conn->prepare($sql_banks);
                            $stmt_banks -> execute();
                            $result_banks = $stmt_banks -> get_result();
                            if($result_banks->num_rows>0)
                            {
                                echo '<option class="text-center" value="">--Select Bank--</option>';
                                while($row = $result_banks->fetch_assoc())
                                {
                                        echo '<option value="'.$row['id'].'">'.$row['bank'].'</option>';
                                }
                            }
                            else
                            {
                                echo '<option value="">No Banks on the System</option>';
                            }
                    echo "
                            </select>
                    </div>
                    <div class='col-lg-2'>
                        <button class='btn btn-success mx-3' type='button' value='$cluster_id::-::$pos' name='$cpv_no' onclick = 'add_cheque(this)'><i class='fa fa-check'></i></button>
                    </div>
                    <div class='form-floating mb-3 col-lg-6'>
                        <input ".((isset($_GET['advance']))?"value = '$new_percent' readonly ":"")."type='number' step='any' class='form-control form-control-sm mb-2' name='cheque_percent' onkeyup = 'set_percent(\"$cpv_no\")' onchange = 'set_percent(\"$cpv_no\")' Value = '100' id='cheque_percent_$cpv_no'>
                        <label for='cheque_percent_$cpv_no'>Cheque Percent</label>
                    </div>
                    <div class='form-floating mb-3 col-lg-6'>
                        <select class='form-select form-select-sm' name='compfor_$cpv_no'  id='compfor_$cpv_no' required>
                            <option value='$req_comp'>$req_comp</option>";
                                $stmt_all_company -> execute();
                                $result_all_company = $stmt_all_company -> get_result();
                                if($result_all_company->num_rows>0)
                                {
                                    while($row = $result_all_company->fetch_assoc())
                                    {
                                        if($req_comp != $row['Name'])
                                            echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                                    }
                                }
                        echo "
                        </select>
                        <label for='compfor_$cpv_no'>Company for</label>
                    </div>
                        <div class='form-check mx-2 ".(($withholding == 0)?"d-none":"")."'>
                            <input id='nowithholding_$cpv_no' name='nowithholding_$cpv_no' value='true' class='form-check-input form-check-input-sm' onchange='withholding(this)' type='checkbox' checked>
                            <label for='nowithholding_$cpv_no' class='form-label'>Withholding</label>
                        </div>
                    </li>";
                }
                echo "
                </div>
            </ul>
            <hr>";
        // ."
        // <div class='form-check col-lg-4 mx-2 d-none'>
        //     <input name='before_vat' value='before_vat' class='form-check-input form-check-input-sm' onkeyup = 'set_percent(\"$cpv_no\")' onchange = 'set_percent(\"$cpv_no\")' type='checkbox' id='before_vat_$cpv_no' checked>
        //     <label for='before_vat_$cpv_no' class='form-label'>Before Vat</label>
        // </div>"
        // $hide = ($found)?"":"d-none";
        // echo "<a class='btn btn-primary $hide' id='print_cpv_$cpv_no' target='_blank' href='../finance/print.php?print_cpv=$cluster_id,$pos'>Print CPV</a>";
        $cpv_no++;
}
else
{
    echo $go_home;
}
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>