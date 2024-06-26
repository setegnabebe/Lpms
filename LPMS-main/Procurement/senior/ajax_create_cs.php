<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include "../../common/functions.php";

    if(isset($_GET['remark_cluster'])){
        if($_GET['remark_cluster']!="")
        {
            $cluster=$_GET['remark_cluster'];
            $sql = 'SELECT Remarks from cluster where id = ?';
            $stmt_get_remark = $conn->prepare($sql);
            $stmt_get_remark -> bind_param("i", $cluster);
            $stmt_get_remark -> execute();
            $result_get_remark = $stmt_get_remark -> get_result();
            if($result_get_remark -> num_rows>0)
            {
                $row = $result_get_remark -> fetch_assoc();
                echo $row['Remarks'];
            }
        }
    }
    else{
        $type=$_GET['db'];
    $idd=$_GET['idd'];
    $company=$_GET['company'];
    $department=$_GET['department'];
    $form_type=$_GET['type'];
    $cluster=$_GET['cluster'];
    echo " 
        <ul class='nav nav-tabs mb-3' id='company_tab'>
            <li class='nav-item'><button class='nav-link active bg-warning text-black fw-bold' type ='button' onclick = 'activeco(this)' id='Company_1_tab'>Company 1</button></li>
            <li class='nav-item' id='add_company'><button type='button' onclick='add_company()' class='btn'><i class='fa fa-plus'></i></button></li>
            <li class='nav-item d-none' id='remove_company'><button type='button' onclick='remove_company()' class='btn my-auto'><i class='fa fa-minus'></i></button></li>
            <input class = 'd-none' type='text' name='name' value='".$_GET['name']."' readonly>
        </ul>
        <div id='company1'>
        <input class = 'd-none' type='number' step='any' id='item_count1' name='item_c[]' value='1' readonly>
        <input class = 'd-none' type='number' step='any' id='cluster' value=".$cluster.">
            <div class='form-floating mb-3'>
                <input type='text' class='form-control rounded-4' id='1floatingco' name='company[]' onchange='company_name(this)' onkeyup='company_name(this)' required>
                <label for='1floatingco'>Company Name :</label>
            </div>
            <p style='text-align:right;'><button class='btn btn-success btn-sm mb-2' type='button' onclick='adder(this)' id='add1'><i class='fa fa-plus'></i> Add Item</button></p>
            <div class='row bg-light py-3 mt-3 mx-auto' id='item 0'>
                <div class='col-sm-12 col-md-12'>
                    <div class='row'>
                        <div class='col-md-6 col-lg-3 pt-3'>
                            <div class='form-floating mb-3 input-group'>
                                <select class='form-select' name='item[]' id='1floatingitem0' onchange='t_p(this)' onkeyup='t_p(this)' required>
                                    <option value='none'>-- Select one --</option>";
                                if($form_type==1){
                                    $sqltemp = "SELECT *,p.request_id as request_id FROM `purchase_order` AS P inner join requests AS R on p.request_id=R.request_id where `cluster_id` = '".$cluster."'";
                                }
                                else{
                                    $sqltemp = "SELECT * FROM `requests` WHERE `status` = 'Generating Quote' AND next_step = 'Comparision Sheet Generation' AND `request_type` = '$type' AND company = '$company' AND department = '$department'";
                                }
                                    if($type=='Consumer Goods')
                                    {
                                        $re_for = ($type=='Consumer Goods' && $_GET['idd'] != 0)?explode("|",$_GET['idd'])[0]."|":$_GET['idd'];
                                        $sqltemp.=($type=='Consumer Goods' && $_GET['idd'] != 0)?" AND request_for LIKE '$re_for%'":" AND request_for = '$re_for'";
                                    }
                                    if($form_type==1){
                                        $sqltemp .=" ORDER BY P.request_id DESC";
                                    }
                                    else
                                        $sqltemp .=" ORDER BY request_id DESC";
                                    $stmt_items_fetch = $conn->prepare($sqltemp);
                                    // $stmt_items_fetch -> bind_param("i", $cluster);
                                    $stmt_items_fetch -> execute();
                                    $result_items_fetch = $stmt_items_fetch -> get_result();
                                    if($result_items_fetch->num_rows > 0)
                                        while($rowtemp = $result_items_fetch->fetch_assoc())
                                        {
                                            $stmt_po_by_request -> bind_param("i", $rowtemp['request_id']);
                                            $stmt_po_by_request -> execute();
                                            $result_po_by_request = $stmt_po_by_request -> get_result();
                                            if($result_po_by_request -> num_rows>0)
                                                while($row = $result_po_by_request -> fetch_assoc())
                                                {
                                                    $stmt_stock -> bind_param("i", $rowtemp['stock_info']);
                                                    $stmt_stock -> execute();
                                                    $result_stock = $stmt_stock -> get_result();
                                                    if($result_stock -> num_rows>0)
                                                        while($rows = $result_stock -> fetch_assoc())
                                                        {
                                                            echo "<option title='Description :- ".$rowtemp['description'].", Remark :- ".$rowtemp['Remark']."' id='".$rowtemp['unit']."' value='".$row['purchase_order_id']."' class='".$rows['for_purchase']."'>".$rowtemp['item']."</option>";
                                                        }
                                                }
                                        }
                                
                        echo "
                                </select>
                                <label for='1floatingitem0'>Items Requested</label>
                                <span class='input-group-text' id='1quan0'></span>
                            </div>
                        </div>
                        <div class='col-md-6 col-lg-3 pt-3'>
                            <div class='form-floating mb-3'>
                                <input type='number' step='any' class='form-control rounded-4' id='1floatingquan0' name='avquan[]' onchange='t_p(this)' onkeyup='t_p(this)' required>
                                <label for='1floatingquan0'>Available Quantity :</label><span id='1warning0'></span>
                            </div>
                        </div>
                        <div class='col-md-6 col-lg-3 pt-3'>
                            <div class='form-floating mb-3'>
                                <input type='number' step='any' min='1' class='form-control rounded-4' id='1floatingprice0' name='price[]' onchange='t_p(this)' onkeyup='t_p(this)' required>
                                <label for='1floatingprice0'>Unit Price :</label>
                            </div>
                        </div>
                        <div class='col-md-6 col-lg-3 pt-3'>
                            <div class='form-floating mb-3'>
                                <input type='number' step='any' class='form-control rounded-4' id='1floatingtp0' name='t_price[]' readonly>
                                <label for='1floatingtp0'>Total Price :</label>
                            </div>
                        </div>
                        <div class='form-check d-none ms-4 pb-2'>
                            <input class='form-check-input' type='radio' id='1itemselected0' onclick = 'green(this)' disabled>
                            <label class='form-check-label' for='1itemselected0'>
                                Recommended
                            </label>
                        </div>
                        <div class='form-group'>
                            <label for='1spec0'>Item Specification <small class='text-secondary'> ( Optional )</small></label>
                            <textarea class='form-control' id='1spec0' rows='2' name='spec[]'></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
    }
}
else
{
  echo $go_home;
}
?>
<?php
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>