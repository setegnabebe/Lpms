<?php 
session_start();
include "../connection/connect.php";
include "../common/functions.php";

$pos = $_GET['pos'];
$sql="SELECT * FROM catagory where catagory='agreement'";
$stmt_agreement = $conn -> prepare($sql); 
$stmt_agreement -> execute();
$res_cat = $stmt_agreement -> get_result();
$is_agreement = $res_cat->num_rows;
 
?>
<div class='d-none' id='down-arrow'>
    <i class='fas fa-angle-down'></i>
</div>
<div class='d-none' id='up-arrow'>
    <i class='fas fa-angle-up'></i>
</div>
<?php
if($_SESSION["role"]=="Admin")
{
?>
    <li
        class="mb-3 sidebar-item  " id='panel'>
        <a href="../admin/panel.php" class='sidebar-link'>
            <i class="bi bi-person-lines-fill"></i>
            <span>Panel</span>
        </a>
    </li>
<hr>
    <li
        class="mb-3 sidebar-item  " id='accounts'>
        <a href="../admin/printaccount.php" class='sidebar-link'>
            <i class="bi bi-person-lines-fill"></i>
            <span>Accounts</span>
        </a>
    </li>
<hr>
    <li
        class="mb-3 sidebar-item  " id='db_editor'>
        <a href="../admin/dbsEditor.php" class='sidebar-link'>
            <i class="fa fa-edit"></i>
            <span>Dbs Editor</span>
        </a>
    </li>
<hr>
<?php
        
}

$temp_requests="";
if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner' || $_SESSION["role"]=="Admin" || (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement')||$_SESSION['additional_role'] == 1) 
    $temp_requests ="";
else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') 
    $temp_requests .="company,".$_SESSION['company'];
else if($_SESSION["role"] == 'Director')
{
    if(!in_array("All Departments",$_SESSION["managing_department"]))
    {
        foreach($_SESSION["managing_department"] as $depp)
            $temp_requests .=($temp_requests == "")?"department,$depp":", ||department,$depp";
    }
    $temp_requests .=($temp_requests == "")?"customer,".$_SESSION['username']:" ||customer,".$_SESSION['username'];
}
else if(strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') 
    $temp_requests .="department,".$_SESSION['department'].",company,".$_SESSION['company'];
else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') 
    $temp_requests .="customer,".$_SESSION['username'];
// if($_SESSION["department"] != 'HOCommittee' && $_SESSION["department"] != 'Owner') $temp ="";
?>
<li
        class="mb-3 sidebar-item  " id='POs'><!-- position-relative  -->
        <?php 
            // $val = badge_count_requests($conn,$conn_fleet,"$temp_requests");
            // $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
            // $color_badge = "bg-success";
        ?> 
        <!-- <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
            <?php //echo $val?> 
            <span class="visually-hidden">Requests</span>
        </span> -->
        <a href="<?php echo $pos?>../requests/requests.php" class='sidebar-link'>
            <i class="bi bi-list-check"></i>
            <span>
                Purchase Orders</span>
        </a>
    </li>
<hr>
<?php 

// include_once $pos."../Committee/sidenav.php";
if(strpos($_SESSION["a_type"],"manager") !== false || strpos($_SESSION["a_type"],"Committee") !== false || $_SESSION["role"]=='Owner' || ($_SESSION["company"]=="Hagbes HQ." && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement'))
{
    $total_count = 0;
    $val = [];

    $val[0] = 0;
    if(($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement' && $_SESSION["company"]=='Hagbes HQ.')
    {
        $temp ="P.scale,procurement";
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
        $temp ="P.scale,owner,P.procurement_company,".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement')
    {
        $temp ="P.scale,Branch, ||P.scale,HO,P.procurement_company,".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(strpos($_SESSION["a_type"],"BranchCommittee") !== false)
    {
        $temp ="scale,Branch,P.company,".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(strpos($_SESSION["a_type"],"HOCommittee") !== false)
    {
        $temp ="scale,HO";
        if(($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement')
            $temp .=",P.procurement_company,!=".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(($_SESSION["role"]=="Director" || $_SESSION["role"]=="GM") && ($_SESSION["department"]=='GM' || in_array("All Departments",$_SESSION["managing_department"])) && $_SESSION["company"]!='Hagbes HQ.')
    {
        $temp ="scale,HO, ||scale,Owner,P.company,".$_SESSION["company"].",R.department, !=".$_SESSION["department"];
        $val[0] += badge_count_custom($conn,$conn_fleet,"requests AS R INNER JOIN purchase_order AS P on R.request_id = P.request_id  Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if($_SESSION["role"]=='Owner')
    {
        $temp ="scale,Owner";
        $scale = "`scale` = 'Owner'";
        $id_sidenave = "Owner";
        $val[0] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
        $join_sql = "requests AS R INNER JOIN purchase_order AS P ON R.request_id = P.request_id INNER JOIN cluster AS C ON P.cluster_id = C.id";
        $condition = "C.status,Generated,R.customer,".$_SESSION['username'].",R.company,".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition,"DISTINCT cluster_id");   
    }
    else if(($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) || $_SESSION["role"]=='Director')
    {
        $join_sql = "requests AS R INNER JOIN purchase_order AS P ON R.request_id = P.request_id INNER JOIN cluster AS C ON P.cluster_id = C.id";
        $condition = "C.status,Generated,R.department,".$_SESSION['department'].",R.company,".$_SESSION['company'];
        if(strpos($_SESSION["a_type"],"BranchCommittee") !== false)
            $condition .= ",scale,!=Branch,P.company,".$_SESSION['company'];
        if(strpos($_SESSION["a_type"],"HO") !== false)
            $condition .= ",scale,!=HO,P.company,".$_SESSION['company'];
        if($_SESSION["department"]=='Procurement')
            $condition .= ",P.procurement_company,!=".$_SESSION['company'];
        $val[0] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition,"DISTINCT cluster_id");
    }
    // FOR IT
    if($_SESSION['department'] == "IT")
    {
        $temp ="spec_dep,".$_SESSION['department'].",department,".$_SESSION['department']."!=";
        $val[0] += badge_count_custom($conn,$conn_fleet,"requests AS R Inner Join purchase_order AS P on P.request_id = R.request_id Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
        $temp ="spec_dep,".$_SESSION['department'].",department,".$_SESSION['department'].",R.company,".$_SESSION['company']."!=";
        $val[0] += badge_count_custom($conn,$conn_fleet,"requests AS R Inner Join purchase_order AS P on P.request_id = R.request_id Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    $total_count += $val[0];

    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
?>
<!-- <li class=" sidebar-item row" onclick="drop_down(this,'com_app')">
<span class='position-relative btn small text-primary sidebar-link col-10'>Committee Approval
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge_all?>">
        <?php //echo $total_count?> 
        <span class="visually-hidden">Committee Tab</span>
    </span>
</span>
<span id='com_app_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
<div class="drops d-none" id="com_app"> -->
<?php 
$color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
?> 
<li
class="position-relative mb-3 sidebar-item  " id='approval_committee'>
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val[0]?> 
        <span class="visually-hidden">Committee Approval</span>
    </span>
<a href="<?php echo $pos?>../Committee/Approval.php" class='sidebar-link'>
    <i class="bi bi-file-earmark-medical-fill"></i>
    <span>POs for Committee</span>
</a>
</li>
<!-- </div> -->
<hr>
    <?php
}
if($_SESSION["role"]=='Owner')
{
?>
<li class="position-relative mb-3 sidebar-item  " id='fixed_approval'>
        <?php 
            $val = badge_count_requests($conn,$conn_fleet,"status,Approved By Director,request_type,Fixed Assets");
            $val += badge_count_requests($conn,$conn_fleet,"status,Approved by Property,company,!=Hagbes HQ., ||customer,`director`,request_type,Fixed Assets");
            $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val?> 
            <span class="visually-hidden">Requests Waiting Approval</span>
        </span>
    <a href="../Committee/ownerApproval.php" class='sidebar-link'>
        <i class="fa fa-spinner fa-pulse"></i>
        <span>Fixed Asset POs</span>
    </a>
</li>
<hr>
    <?php
}
if(strpos($_SESSION["a_type"],"ChequeSignatory") !== false && isset($_SESSION['company_signatory']) && $_SESSION['company_signatory'])
{?>
    <li class="position-relative mb-3 sidebar-item  " id='payment'>
        <?php 
            $sql_cond = ($_SESSION['company'] != "Hagbes HQ.")?",cheque_company,".$_SESSION['company']:"";
            $val = badge_count_custom($conn,$conn_fleet,"`cheque_info`","status,pending, ||status,pending payment processed".$sql_cond);
            $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val?> 
            <span class="visually-hidden">Cheque Signing</span>
        </span>
        <a href="<?php echo $pos?>../requests/chequeSigning.php" class='sidebar-link'>
            <i class="fas fa-signature"></i>
            <span>Cheque Signing</span>
        </a>
    </li>
    <li class="mb-3 sidebar-item  " id='cheque_history'>
        <a href="<?php echo $pos?>../requests/chequeHistory.php" class='sidebar-link'>
            <i class="fas fa-signature"></i>
            <span>Cheque History</span>
        </a>
    </li>
    <hr>
     <li
            class="position-relative mb-3 sidebar-item" id='perdiemcheque'>
            <?php
        if($_SESSION["role"] != "Disbursement"){  
            if($_SESSION['company'] == 'Hagbes HQ.')            
            $req =  "SELECT * FROM perdiem where  (company in (SELECT `Name` from comp where cheque_signatory = 0) or company in (SELECT `Name` from comp where perdiem = 0) or company = '".$_SESSION['company']."') and (`status` = 'Cheque reviewed'"; 
            else
            $req =  "SELECT * FROM perdiem where  company = '".$_SESSION['company']."' and (`status` = 'Cheque reviewed'";   
            //if(($_SESSION["department"] == "Disbursement" and ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || strpos($_SESSION["a_type"],"Petty Cash Approver") !== false) 
           // $req .= " OR (`id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Petty' AND (cheque_percent != 'p_100' OR cheque_percent is NULL)))";   
            $req .= " ) ";            
        }else{          
            $req =  "SELECT * FROM perdiem where (`status` = 'Cheque reviewed') and (company = '".$_SESSION['company']."'"; 
            if($_SESSION['company'] == 'Hagbes HQ.')  
            $req .= " or company in (SELECT `Name` from comp where perdiem = 0)";  
            $req .= " )";
        }
            $stmt_perdiem_count = $conn_fleet -> prepare($req); 
            $stmt_perdiem_count -> execute();
            $res = $stmt_perdiem_count -> get_result();
            $preq = $res -> num_rows;   
            $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $colorbadge?>">
                <?php echo isset($preq)?$preq:0 ?> 
                <span class="visually-hidden">Approve Petty Cash</span>
            </span>
            <a href="../requests/perdiem.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Sign perdiem cheque
                </span>
            </a>
        </li>
        <hr>
    <?php 
       }
       if($_SESSION['perdiem'] == true AND  ($_SESSION["company"] != "Hagbes HQ." AND ($_SESSION["department"]=="GM" or $_SESSION["department"]=="Dirctor" ))){ 
        ?>
        <li
              class="position-relative mb-3 sidebar-item  " id='perdiem'>
              <?php 
               $req = "SELECT * FROM perdiem where `status` = 'Settlement cheque checked' AND `next-id` IS NULL AND company = ? GROUP BY job_id";
               $stmt_perdiem_settlement_count = $conn_fleet -> prepare($req); 
               $stmt_perdiem_settlement_count -> bind_param("s", $_SESSION['company']);
               $stmt_perdiem_settlement_count -> execute();
               $res = $stmt_perdiem_settlement_count -> get_result();
             if ($res->num_rows > 0)
                 $preq = $res->num_rows;
             else
                 $preq = 0;
  
                 $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
              ?> 
              <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo  $colorbadge?>">
                  <?php echo $preq?> 
                  <span class="visually-hidden">Approve Perdiem Settlement</span>
              </span>
              <a href="../finance/perdiem.php" class='sidebar-link'>
                  <i class="far fa-file-alt"></i>
                  <span>Approve Perdiem Settlement
                  </span>
              </a>
          </li>
        <?php } ?>  
        <hr>
<?php
if($_SESSION["role"]=='Owner')
{
?>
    <!-- <li
    class="position-relative mb-3 sidebar-item  " id='item_check'>
            <?php 
            $val = badge_count_requests($conn,$conn_fleet,"flag,0,status,Collected-not-comfirmed,department,".$_SESSION['department']);
            $join_sql = "requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id";
        // `flag` = 0 AND (R.status='Collected-not-comfirmed' OR S.status = 'Approved') AND `company` = '".$_SESSION['company']."' AND `department` = '".$_SESSION['department']."'";
            $condition = "S.flag,0,S.status,Approved,department,".$_SESSION['department'].",R.company,".$_SESSION['company'];
            $val += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
                $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
            ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val?> 
            <span class="visually-hidden">Waiting For Approval</span>
        </span>
    <a href="<?php echo $pos?>../requests/itemCheck.php" class='sidebar-link'>
        <i class="fas fa-store"></i>
        <span>In-Stock POs</span>
    </a>
    </li>
<hr> -->
<?php
}

if($_SESSION["additional_role"]==1 || $_SESSION["department"]=='Procurement' && (strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["role"]=='Senior Purchase officer'))
{?>
    <li
        class="mb-3 sidebar-item  " id='viewcsheet'><!-- position-relative  -->
        <?php 
            // $val = badge_count_custom($conn,$conn_fleet,"cluster","procurement_company,".$_SESSION['company']);
            // $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
            // $color_badge = "bg-success";
        ?> 
        <!-- <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
            <?php //echo $val?> 
            <span class="visually-hidden">Comparision Sheets</span>
        </span> -->
        <a href="../<?php echo $pos?>Procurement/senior/comparisionSheets.php" class='sidebar-link'>
            <i class='fas fa-eye'></i>
            <span>Comparision Sheets</span>
        </a>
    </li>
<hr>
<?php  
}
?>
<?php
//////////////////////////////////////////////////////Director///////////////////////////////////////////////////////////////////
if(isset($_SESSION["managing_department"]) || $_SESSION['role'] == 'Director' || $_SESSION['role'] == 'GM')
{?>
<?php 
 
    $cond2 = "";//"department,".$_SESSION['department'].
    if(!in_array("All Departments",$_SESSION["managing_department"]))
        foreach($_SESSION["managing_department"] as $d) 
        {
            $cond2 .= ($cond2 == "")?"department,".$d:", ||department,".$d;
            $like_filed = "%$d%";
            $query = "SELECT Username FROM account WHERE managing LIKE ? and role = 'GM'";
            $stmt_managing = $conn -> prepare($query); 
            $stmt_managing -> bind_param("s", $like_filed);
            $stmt_managing -> execute();
            $res = $stmt_managing -> get_result();
            if($res->num_rows>0)
                while($r = $res->fetch_assoc())
                {
                    $cond2 .= ($cond2 == "")?"customer,$r[Username]":", ||customer,$r[Username]";
                }
        }
    $cond2 .= ($cond2 == "")?"company,".$_SESSION['company']:",company,".$_SESSION['company'];
    if($_SESSION["role"] == "Owner")
    {
        $cond2 = "status,Approved By Dep.Manager, ||status,waiting,customer,".$_SESSION['username'];    
        $val = badge_count_requests($conn,$conn_fleet,$cond2);
    }
    else if($_SESSION["role"] == "Director")
    {
        $val = 0;
        $cond = "stock_info,IS NOT NULL,status,Approved By Property,customer, !=`director`, ||director,IS NULL,request_type, !=Fixed Assets,".$cond2;
        $val += badge_count_requests($conn,$conn_fleet,$cond);
        
        $cond = "request_type,Tyre and Battery,status,Store Checked, ||status,Approved By Property,customer, !=`director`, ||director,IS NULL,mode,External,stock_info,IS NOT NULL,".$cond2;
        $val += badge_count_requests($conn,$conn_fleet,$cond);

        $cond = "request_type,Spare and Lubricant,status,Store Checked, ||status,Approved By Property,customer, !=`director`, ||director,IS NULL,mode,External,stock_info,IS NOT NULL,".$cond2;
        $val += badge_count_requests($conn,$conn_fleet,$cond);

        $cond = "request_type,Spare and Lubricant,status,Store Checked, ||status,Approved By Property,customer, !=`director`, ||director,IS NULL,mode,Internal,type,Lubricant,stock_info,IS NOT NULL,".$cond2;
        $val += badge_count_requests($conn,$conn_fleet,$cond);

        $cond = "request_type,Miscellaneous, ||request_type,Consumer Goods,customer, !=`director`, ||director,IS NULL,status,Store Checked, ||status,Approved By Property,stock_info,IS NOT NULL,".$cond2;
        $val += badge_count_requests($conn,$conn_fleet,$cond);
    }
    else if($_SESSION["role"] == "GM")
    {
        $cond2 = "status,Approved By Dep.Manager,".$cond2;    
        $val = badge_count_requests($conn,$conn_fleet,$cond2);
    }
    $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
?> 
<!-- <li class=" sidebar-item row" onclick="drop_down(this,'Director_side')">
<span class='position-relative btn small text-primary sidebar-link col-10'>
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
        <?php //echo $val?> 
        <span class="visually-hidden">Requests Waiting Approval</span>
    </span>
    Director
</span>
<span id='Director_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i>
</li>
    <div class="drops d-none" id="Director_side"> -->
    <li class="position-relative mb-3 sidebar-item  " id='director_approval'>
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val?> 
                <span class="visually-hidden">Requests Waiting Approval</span>
            </span>
        <a href=<?php echo $pos.'../'.$_SESSION['role'].'/approval.php'?> class='sidebar-link'>
            <i class="fa fa-spinner fa-pulse"></i>
            <span><?=$_SESSION['role']?> Level POs</span>
        </a>
    </li>
    <!-- </div> -->
<hr>
<?php
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////Disburisment///////////////////////////////////////////////////////////////

/////////////////////Agreement /////////////////////////


if($_SESSION['role'] == 'Director' && $_SESSION['company']=='Hagbes HQ.'&&$is_agreement)
{?>
<?php 
  $cond2 ="next_step,directors";
    $val = badge_count_requests($conn,$conn_fleet,$cond2);
    $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
?> 
<!-- <li class=" sidebar-item row" onclick="drop_down(this,'Director_side')">
<span class='position-relative btn small text-primary sidebar-link col-10'>
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
        <?php //echo $val?> 
        <span class="visually-hidden">Requests Waiting Approval</span>
    </span>
    Director
</span>
<span id='Director_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i>
</li>
    <div class="drops d-none" id="Director_side"> -->
    <li class="position-relative mb-3 sidebar-item  " id='agreement_approval'>
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val?> 
                <span class="visually-hidden">Requests Waiting Approval</span>
            </span>
        <a href=<?php echo $pos.'../requests/agreementApproval.php'?> class='sidebar-link'>
        <i class="fa fa-solid fa-handshake"></i>
            <span>Agreement Purchase</span>
        </a>
    </li>
    <!-- </div> -->
<hr>
<?php
}













///////////////////////////////////////
if($_SESSION['department'] == 'Disbursement' || strpos($_SESSION["a_type"],"PettyCashApprover") !== false)// || (isset($_SESSION["managing_department"]) && in_array("Disbursement",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    
    $total_count = 0;
    $val = [];
    $val[0] = badge_count_custom($conn,$conn_fleet,"`price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id","P.status,Finance Approved Petty Cash,selected,1,finance_company,".$_SESSION['company'],"DISTINCT providing_company,P.cluster_id");
    $val[0] += badge_count_custom($conn,$conn_fleet,"`purchase_order`","status,Petty Cash,finance_company,".$_SESSION['company']); // cluster_id,IS NULL,
    $total_count += $val[0];
    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    ?>
    <!-- <li class=" sidebar-item row" onclick="drop_down(this,'Disbursement_side')">
    <span class='position-relative btn small text-primary sidebar-link col-10'>Disbursement
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge_all?>">
            <?php //echo $total_count?> 
            <span class="visually-hidden">Disbursement Tab</span>
        </span>
    </span>
    <span id='Disbursement_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
        <div class="drops d-none" id="Disbursement_side"> -->
<?php
if(strpos($_SESSION["a_type"],"PettyCashApprover") !== false)
{
?>
        <li
            class="position-relative mb-3 sidebar-item  " id='petty_cash'>
            <?php 
                $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[0]?> 
                <span class="visually-hidden">Approve Petty Cash</span>
            </span>
            <a href="../Disbursement/pettycash.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Approve Petty Cash
                </span>
            </a>
        </li>
        <!-- <li
            class="position-relative mb-3 sidebar-item  " id='petty_cash'>
            <?php 
                $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[0]?> 
                <span class="visually-hidden">Approve Perdiem Petty Cash</span>
            </span>
            <a href="../Disbursement/perdiem_pettycash.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Approve Perdiem Petty Cash
                </span>
            </a>
        </li> -->
        <!-- </div> -->
<hr>
<?php
}
// if($_SESSION['department'] == 'Disbursement')
// {
?>
<!-- <li
            class="position-relative mb-3 sidebar-item  " id='perdiem_disb'>
            <?php
            // $req = "SELECT * from perdiem where `status` = 'cheque prepared'";
            //   $res = $conn_fleet->query($req);
            // if ($res->num_rows > 0)
            //     $preq = $res->num_rows;
            // else
            //     $preq = 0;

            //     $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $colorbadge?>">
                <?php //echo $preq?> 
                <span class="visually-hidden">Approve Petty Cash</span>
            </span>
            <a href="../Disbursement/perdiem.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Approve perdiem request
                </span>
            </a>
        </li>
<hr> -->
<?php
// }
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////Property///////////////////////////////////////////////////////////////////
// foreach($_SESSION["managing_department"] as $d)
if($_SESSION['department'] == 'Property' || $_SESSION['role'] == 'Store' || (isset($_SESSION["managing_department"]) && in_array("Property",$_SESSION["managing_department"])))// || (isset($_SESSION["managing_department"]) && in_array("Property",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    $total_count = 0;
    $val = [];
    $val[0] = badge_count_requests($conn,$conn_fleet,"stock_info,IS NULL,spec_dep,IS NULL,status,Special Condition,request_type,agreement!=,property_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"status,Specification Provided,property_company,".$_SESSION['company']);
    // $val[0] += badge_count_requests($conn,$conn_fleet,"stock_info,IS NULL,status,Approved By Owner,property_company,".$_SESSION['company']);
    $total_count += $val[0];
    $val[1] = badge_count_requests($conn,$conn_fleet,"status,In-stock, ||status,Collected-not-comfirmed, ||status,Collected,property_company,".$_SESSION['company']);
    $join_sql = "requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id";
    $condition = "S.status,Complete-uncomfirmed, ||S.status,Approved,property_company,".$_SESSION['company'];
    $val[1] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
    $total_count += $val[1];
    if(strpos($_SESSION["a_type"],"manager") !== false)
    {
        $val[2] = 0;
        $join_sql = "requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id";
        $condition = "`in-stock`,0>,S.status,not approved,property_company,".$_SESSION['company'];
        $val[2] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
        $condition = "R.status,Store Checked,property_company,".$_SESSION['company'];
        $val[2] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
        $condition = "`in-stock`,0>,S.status,not approved,R.status,Store Checked,property_company,".$_SESSION['company'];
        $val[2] -= badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
        $val[2] -= badge_count_requests($conn,$conn_fleet,"request_type,Tyre and Battery,status,Store Checked,mode,External,stock_info,IS NOT NULL,property_company,".$_SESSION['company']);
        $val[2] -= badge_count_requests($conn,$conn_fleet,"request_type,Spare and Lubricant,status,Store Checked,mode,External,stock_info,IS NOT NULL,property_company,".$_SESSION['company']);
        $val[2] -= badge_count_requests($conn,$conn_fleet,"request_type,Spare and Lubricant,status,Store Checked,mode,Internal,type,Lubricant,stock_info,IS NOT NULL,property_company,".$_SESSION['company']);
        $val[2] -= badge_count_requests($conn,$conn_fleet,"request_type,Miscellaneous,status,Store Checked,stock_info,IS NOT NULL,property_company,".$_SESSION['company']);
        $val[2] -= badge_count_requests($conn,$conn_fleet,"request_type,Consumer Goods,status,Store Checked,stock_info,IS NOT NULL,property_company,".$_SESSION['company']);
    $total_count += $val[2];
}
    $val[3] = badge_count_requests($conn,$conn_fleet,"to_replace,IS NOT NULL,replaced_items,IS NULL,recieved,yes,property_company,".$_SESSION['company']);
    $total_count += $val[3];

    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    $indent = ""; $hrl = "<hr>";
    ?>
    <?php if(isset($_SESSION["managing_department"]) && in_array("Property",$_SESSION["managing_department"])) {
    $indent = "ms-3"; $hrl = "";?>
<li class=" sidebar-item row" onclick="drop_down(this,'property_side')">
<span class='position-relative btn small text-primary sidebar-link col-10'>Property
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge_all?>">
        <?php echo $total_count?> 
        <span class="visually-hidden">Property Tab</span>
    </span>
</span>
<span id='property_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
    <div class="drops d-none" id="property_side">
<?php }?>
<li
    class="position-relative mb-3 sidebar-item <?=$indent?> " id='store'>
    <?php  
        $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
    ?> 
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val[0]?> 
        <span class="visually-hidden">Items To check In stock</span>
    </span>
    <a href="<?=$pos?>../Property/storeclerk.php" class='sidebar-link'>
        <i class="fas fa-clipboard-check"></i>
        <span>Store Check</span>
    </a>
</li>
<?=$hrl?> 
<li
    class="position-relative mb-3 sidebar-item <?=$indent?> " id='purchased'>
    <?php 
        $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
    ?> 
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val[1]?> 
        <span class="visually-hidden">Collected Items</span>
    </span>
    <a href="<?=$pos?>../Property/purchased.php" class='sidebar-link'>
        <i class="bi bi-stack"></i>
        <span>Completed POs</span>
    </a>
</li>
<?=$hrl?>
<?php 
if(strpos($_SESSION["a_type"],"manager") !== false || (isset($_SESSION["managing_department"]) && in_array("Property",$_SESSION["managing_department"])))// || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner"
{?>
<li
    class="position-relative mb-3 sidebar-item <?=$indent?> " id='property_approval'>
    <?php 
        // $val = badge_count_requests($conn,$conn_fleet,"status,Found In Stock,company,".$_SESSION['company']);
        $color_badge = ($val[2] == 0)?"bg-secondary":(($val[2] < 5)?"bg-success":(($val[2] < 10)?"bg-warning":"bg-danger"));
    ?> 
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val[2]?> 
        <span class="visually-hidden">Property Level Approval</span>
    </span>
    <a href="<?=$pos?>../Property/propertyApproval.php" class='sidebar-link'>
        <i class="fa fa-spinner fa-pulse"></i>
        <span>
        Prop. Level Approval</span>
    </a>
</li>
<?=$hrl?>
<?php }?>
<li
    class="position-relative mb-3 sidebar-item <?=$indent?> " id='replaced_items'>
    <?php  
        $color_badge = ($val[3] == 0)?"bg-secondary":(($val[3] < 5)?"bg-success":(($val[3] < 10)?"bg-warning":"bg-danger"));
    ?> 
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val[3]?> 
        <span class="visually-hidden">Replacements to Collect</span>
    </span>
    <a href="<?=$pos?>../Property/replacementsCollected.php" class='sidebar-link'>
        <i class="fas fa-clipboard-check"></i>
        <span>Replacements to Collect</span>
    </a>
</li>
<?php if(isset($_SESSION["managing_department"]) && in_array("Property",$_SESSION["managing_department"])) {?>
</div>
<?php 
}
?>
<hr> 
<?php
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner")//$_SESSION["role"] == "Owner" || 
{
    $cond2 = "company,".$_SESSION['company'].",department,".$_SESSION['department']; //($_SESSION["role"] == "Owner")?"customer,".$_SESSION['username']:
    $text_app = ($_SESSION["department"] == "IT")?"Dep. Level POs / Specification" : "Dep. Level POs";
    
    $total_count = 0;
    $val = [];
    $val[0] = 0;
    if($_SESSION["department"] == "IT")
    {
        $cond = "spec_dep,".$_SESSION['department'];
        if($_SESSION['company'] == 'Hagbes HQ.')
        {
            $sql_comp = "SELECT * FROM `comp` where `IT` = '1' AND `Name` != 'Hagbes HQ.'";
            $stmt_IT_other = $conn_fleet -> prepare($sql_comp); 
            $stmt_IT_other -> execute();
            $result_IT_other = $stmt_IT_other -> get_result();
            if($result_IT_other -> num_rows>0)
            while($row_comp = $result_IT_other -> fetch_assoc())
                $cond .= ",company,".$row_comp['Name']."!=";
        }
        else
        {
            $cond .= ",company,".$_SESSION['company'];
        }

        $val[0] = badge_count_requests($conn,$conn_fleet,$cond.",specification,IS NULL,status,Special Condition");
        $total_count += $val[0];
    }
    
    $all_deps = "";
    if(isset($_SESSION["managing_department"]))
    {
        if(!in_array("All Departments",$_SESSION["managing_department"]))
        {
            foreach($_SESSION["managing_department"] as $deps)
            $all_deps .= ", ||department,$deps";
        }
    }
    $val[1] = badge_count_requests($conn,$conn_fleet,"status,waiting,".$cond2.$all_deps);
    $total_count += $val[1];

    $val[2]=0;
    // if(strpos($_SESSION["a_type"],"Committee")===false && strpos($_SESSION["a_type"],"Owner")===false)
    $join_sql = "requests AS R INNER JOIN purchase_order AS P ON R.request_id = P.request_id INNER JOIN cluster AS C ON P.cluster_id = C.id";
    $condition = "C.status,Generated,R.department,".$_SESSION['department'].",R.company,".$_SESSION['company'];
    $val[2] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition,"DISTINCT cluster_id");
    $total_count += $val[2];
    
$departments = "";
if(($_SESSION["role"]=="Director" || $_SESSION['role'] == 'GM') && ($_SESSION["department"]=='GM' || in_array("All Departments",$_SESSION["managing_department"])) && $_SESSION["company"]!='Hagbes HQ.')
{
    $sql_account = "SELECT * FROM `account` WHERE department not in (SELECT department from account where (role = 'manager' OR `type` LIke '%manager%') and company = ? group by department) and company = ?";
    $stmt_no_manager = $conn->prepare($sql_account);
    $stmt_no_manager -> bind_param("ss", $_SESSION["company"], $_SESSION["company"]);
    $stmt_no_manager -> execute();
    $result_no_manager = $stmt_no_manager->get_result();
    if($result_no_manager -> num_rows > 0)
        while($row_account = $result_no_manager -> fetch_assoc())
        {
            $departments .= ($departments == "")?"department,".$row_account['department']:", ||department,".$row_account['department'];
        }
}
if($departments == "")
    $departments = "department,".$_SESSION['department'];

    $val[3] = badge_count_requests($conn,$conn_fleet,"flag,0,status,Collected-not-comfirmed,$departments,company,".$_SESSION['company']);
    $join_sql = "requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id";
// `flag` = 0 AND (R.status='Collected-not-comfirmed' OR S.status = 'Approved') AND `company` = '".$_SESSION['company']."' AND `department` = '".$_SESSION['department']."'";
    $condition = "S.flag,0,S.status,Approved,$departments,R.company,".$_SESSION['company'];
    $val[3] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
    $total_count += $val[3];

    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
    ?>
    <!-- <li class=" sidebar-item row" onclick="drop_down(this,'a&s')">
    <span class='position-relative btn small text-primary sidebar-link col-10'><?php //echo $text_app?>
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge_all?>">
            <?php //echo $total_count?> 
            <span class="visually-hidden"><?php //echo $text_app?> Tab</span>
        </span>
    </span>
    <span id='a&s_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></span></li>
    <div class="drops d-none" id="a&s"> -->
        <?php if($_SESSION["department"] == "IT")// || ()
        {
            $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
            ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='spec'>
                <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                    <?php echo $val[0]?> 
                    <span class="visually-hidden">Requests Sent for Specification</span>
                </span>
            <a href="<?php echo $pos?>../requests/specs.php" class='sidebar-link'>
                <i class="bi bi-file-post-fill"></i>
                <span>Set Specifications</span>
            </a>
        </li>
        <hr>
        <?php }?>
        <?php //if($_SESSION["role"] != "Director")// || ($_SESSION["role"] == "Director") 
        //{?>
        <li
            class="position-relative mb-3 sidebar-item  " id='approval'>
                <?php 
                    $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
                ?> 
                <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                    <?php echo $val[1]?> 
                    <span class="visually-hidden">Requests Waiting Approval</span>
                </span>
            <a href="<?php echo $pos?>../requests/managerApproval.php" class='sidebar-link'>
                <i class="fa fa-spinner fa-pulse"></i>
                <span>Dep. Level POs</span>
            </a>
        </li>
        <hr>
        
        <!-- <li class="position-relative mb-3 sidebar-item  " id='approval_alt'>
                <?php 
                    //$color_badge = ($val[2] == 0)?"bg-secondary":(($val[2] < 5)?"bg-success":(($val[2] < 10)?"bg-warning":"bg-danger"));
                ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
                <?php //echo $val[2]?> 
                <span class="visually-hidden">Waiting For Approval</span>
            </span>
            <a href="<?php //echo $pos?>../requests/alternateApproval.php" class='sidebar-link'>
                <i class="bi bi-file-earmark-medical-fill"></i>
                <span>Department Committee Approval</span>
            </a>
        </li> 
        <hr> -->
        <li
        class="position-relative mb-3 sidebar-item  " id='item_check'>
                <?php 
                    $color_badge = ($val[3] == 0)?"bg-secondary":(($val[3] < 5)?"bg-success":(($val[3] < 10)?"bg-warning":"bg-danger"));
                ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[3]?> 
                <span class="visually-hidden">Waiting For Approval</span>
            </span>
        <a href="<?php echo $pos?>../requests/itemCheck.php" class='sidebar-link'>
            <i class="fas fa-store"></i>
            <span>In-Stock POs</span>
        </a>
        </li>
    <!-- </div> -->
<hr>
<?php }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if(($_SESSION['role'] == 'Director' && $_SESSION['company'] == 'Hagbes HQ.') || $_SESSION['role'] == 'Owner' ||($_SESSION['department']=="IT"&&$_SESSION['role']=="Admin"))
{
    ?>
    <li
        class="mb-3 sidebar-item  " id='Vendors'>
        <a href="../<?php echo $pos?>requests/preferedVendors.php" class='sidebar-link'>
            <i class="fas fa-hands-helping"></i>
            <span>
                Prefered Vendors
            </span>
        </a>
    </li>
    <hr>
    <?php
}

////////////////////////////////////////////////////////////Finance///////////////////////////////////////////////////////////////



if($_SESSION['department'] == 'Finance' || $_SESSION['role'] == 'Cashier' || $_SESSION['role'] == 'Disbursement' || (isset($_SESSION["managing_department"]) && in_array("Finance",$_SESSION["managing_department"])))// || (isset($_SESSION["managing_department"]) && in_array("Finance",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    
    $total_count = 0;
    $val = [];

    if(strpos($_SESSION["a_type"],"manager") !== false)// || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner"
    {
        $val[0] = badge_count_custom($conn,$conn_fleet,"cluster","status,Reviewed,finance_company,".$_SESSION['company']);
        $total_count += $val[0];

        $val[1] = badge_count_custom($conn,$conn_fleet,"purchase_order","settlement,requested,finance_company,".$_SESSION['company']);
        $total_count += $val[1];

    }
    $val[2] = badge_count_custom($conn,$conn_fleet,"`price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id","P.status,Cheque Prepared, ||P.status,Finance Approved,P.finance_company,".$_SESSION['company'].",selected,1","*","providing_company,P.cluster_id");
    $val[2] += badge_count_custom($conn,$conn_fleet,"`cheque_info` AS c_i Left JOIN cluster AS C on c_i.cluster_id=c.id","prepared_percent,100<,c_i.status,All Payment Processed!=,final,0,C.finance_company,".$_SESSION['company']);
    $total_count += $val[2];

    $val[3] = badge_count_custom($conn,$conn_fleet,"cluster","status,Sent to Finance,finance_company,".$_SESSION['company']);
    $total_count += $val[3];

    $val[4] = badge_count_custom($conn,$conn_fleet,"`price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id","P.status,Petty Cash Approved,P.finance_company,".$_SESSION['company'].",selected,1","*","providing_company,P.cluster_id");
    $val[4] += badge_count_custom($conn,$conn_fleet,"`purchase_order`","status,Petty Cash Approved,cluster_id,IS NULL,finance_company,".$_SESSION['company']);
    $total_count += $val[4];

    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
    ?>
    <!-- <li class=" sidebar-item row" onclick="drop_down(this,'Finance_side')">
    <span class='position-relative btn small text-primary sidebar-link col-10'>Finance
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge_all?>">
            <?php //echo $total_count?> 
            <span class="visually-hidden">Finance Tab</span>
        </span>
    </span><span id='Finance_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
    <div class="drops d-none" id="Finance_side"> -->
    <?php 
        if(strpos($_SESSION["a_type"],"manager") !== false)// || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner"
        {
        ?>
            <li
                class="position-relative mb-3 sidebar-item  " id='finance_approval'>
                <?php 
                    $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
                ?> 
                <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                    <?php echo $val[0]?> 
                    <span class="visually-hidden">Review POs</span>
                </span>
                <a href="../finance/financeApproval.php" class='sidebar-link'>
                    <i class="far fa-file-alt"></i>
                    <span>Approve POs</span>
                </a>
            </li>
        <hr>
            <li
                class="position-relative mb-3 sidebar-item  " id='Settlement'>
                <?php 
                    $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
                ?> 
                <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                    <?php echo $val[1]?> 
                    <span class="visually-hidden">Settlement</span>
                </span>
                <a href="../finance/settlement.php" class='sidebar-link'>
                    <i class="far fa-folder"></i>
                    <span>Settlements</span>
                </a>
            </li>
        <hr>
        <?php } 
        
        if(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION['role'] == 'Cashier' || $_SESSION['role'] == 'Disbursement')// || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner"
        {
        ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='cashier'>
            <?php 
                $color_badge = ($val[2] == 0)?"bg-secondary":(($val[2] < 5)?"bg-success":(($val[2] < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[2]?> 
                <span class="visually-hidden">waiting Cheque</span>
            </span>
            <a href="../finance/cashier.php" class='sidebar-link'>
                <i class="fas fa-file-signature"></i>
                <span>Prepare Cheques</span>
            </a>
        </li>
        <hr>
        <li
            class="position-relative mb-3 sidebar-item  " id='pettycash'>
            <?php 
                $color_badge = ($val[4] == 0)?"bg-secondary":(($val[4] < 5)?"bg-success":(($val[4] < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[4]?> 
                <span class="visually-hidden">waiting Cheque</span>
            </span>
            <a href="../finance/pettyCash.php" class='sidebar-link'>
                <i class="fas fa-money-bill-wave"></i>
                <span>Petty Cash</span>
            </a>
        </li>
        <hr>
        <li
            class="mb-3 sidebar-item  " id='CPV'>
            <a href="../finance/cpv.php" class='sidebar-link'>
                <i class="fas fa-file-signature"></i>
                <span>View CPV</span>
            </a>
        </li>
        <hr>
        <?php if(($_SESSION["role"] == "Cashier" AND strpos($_SESSION["a_type"],"Perdiem") === false) or ($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance")){ ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='perdiem'>
            <?php 
            if($_SESSION["role"] == "Cashier"){
                if($_SESSION['company'] == 'Hagbes HQ.')
                $req =  "SELECT * FROM perdiem where ((`status` = 'Senior accountant approved' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Cheque')) OR `status` = 'Payment approved' OR `status` = 'Settlement cheque approved') and  (company in (SELECT `Name` from comp where perdiem = 0) or company = '".$_SESSION['company']."')"; 
                else
                $req =  "SELECT * FROM perdiem where ((`status` = 'Senior accountant approved' AND `id` IN (SELECT perdime_id FROM traveladvance where payment_option = 'Cheque')) OR `status` = 'Payment approved' OR `status` = 'Settlement cheque approved') and company = '".$_SESSION['company']."' ";      
            }else if($_SESSION["role"] == "Disbursement" AND $_SESSION["department"] == "Finance"){
                if($_SESSION['company'] == 'Hagbes HQ.')
                $req =  "SELECT * FROM perdiem where (`status` = 'Cheque prepared' or `status` = 'Settlement cheque approved') and (company = '".$_SESSION['company']."' or company in (SELECT `Name` from comp where perdiem = 0))";
                else
                $req =  "SELECT * FROM perdiem where (`status` = 'Cheque prepared' or `status` = 'Settlement cheque approved') and company = '".$_SESSION['company']."'";
            }
            $stmt_perdiem_count_2 = $conn_fleet -> prepare($req);
            $stmt_perdiem_count_2 -> execute();
            $res = $stmt_perdiem_count_2->get_result();
            $preq = $res->num_rows;
            $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
                  ?> 
              <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $colorbadge?>">
                <?php echo $preq?> 
                <span class="visually-hidden">Perdiem</span>
            </span>
            <a href="../finance/perdiem.php" class='sidebar-link'>
                <i class="fas fa-file-signature"></i>
                <span>Perdiem</span>
            </a>
        </li>
        <hr>
        <?php } 
        }
        if(strpos($_SESSION["a_type"],"Perdiem") !== false and $_SESSION["company"] == "Hagbes HQ.")
        {
        ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='perdiem'>
            <?php 
            if($_SESSION['company'] == 'Hagbes HQ.')
                $req = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?)   GROUP BY job_id";
            else
                $req = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND  company = ?  GROUP BY job_id";
            
            $stmt_perdiem_settlement_review_count = $conn_fleet->prepare($req);
            $stmt_perdiem_settlement_review_count -> bind_param("s", $_SESSION["company"]);
            $stmt_perdiem_settlement_review_count -> execute();
            $res = $stmt_perdiem_settlement_review_count -> get_result();
            if ($res->num_rows > 0)
                $preq = $res->num_rows;
            else
                $preq = 0;

               $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo  $colorbadge?>">
                <?php echo $preq?> 
                <span class="visually-hidden">Prepare settlement</span>
            </span>
            <a href="../requests/prepare_settlement.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Prepare settlement
                </span>
            </a>
            
        </li> 
        <hr>
        <?php } else if($_SESSION["department"]=="Finance" and $_SESSION['role'] == "manager"){
            ?>
            <li
                class="position-relative mb-3 sidebar-item  " id='review'>
                <?php 
                if($_SESSION['company'] == 'Hagbes HQ.')
                    $req = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?)   GROUP BY job_id";
                else
                    $req = "SELECT * FROM perdiem where (`status` = 'Settlement reviewed' OR `status` = 'Payment processed') AND `next-id` IS NULL AND  company = ?  GROUP BY job_id";
                
                $stmt_perdiem_settlement_review_count = $conn_fleet->prepare($req);
                $stmt_perdiem_settlement_review_count -> bind_param("s", $_SESSION["company"]);
                $stmt_perdiem_settlement_review_count -> execute();
                $res = $stmt_perdiem_settlement_review_count -> get_result();
                if ($res->num_rows > 0)
                    $preq = $res->num_rows;
                else
                    $preq = 0;
    
                   $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
                ?>

                <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo  $colorbadge?>">
                <?php echo $preq?> 
                <span class="visually-hidden">Prepare settlement cheque</span>
            </span>
            <a href="../finance/perdiem.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Approve settlement cheque
                </span>
            </a>
            </li> 
        <hr>
<?php
        }
     if($_SESSION["company"] == "Hagbes HQ." and $_SESSION["department"]=="Finance" and ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false))
        {
          ?>
          <li
              class="position-relative mb-3 sidebar-item  " id='perdiem'>
              <?php 
               $req = "SELECT * FROM perdiem where `status` = 'Settlement cheque checked' AND `next-id` IS NULL AND (company in (SELECT `Name` from comp where perdiem = 0) or company = ?) GROUP BY job_id";
               $stmt_perdiem_settlement_cheque_count = $conn_fleet->prepare($req);
               $stmt_perdiem_settlement_cheque_count -> bind_param("s", $_SESSION['company']);
               $stmt_perdiem_settlement_cheque_count -> execute();
               $res = $stmt_perdiem_settlement_cheque_count->get_result();
             if ($res->num_rows > 0)
                 $preq = $res->num_rows;
             else
                 $preq = 0;
  
                 $colorbadge = ($preq == 0)?"bg-secondary":(($preq < 5)?"bg-success":(($preq < 10)?"bg-warning":"bg-danger"));
              ?> 
              <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo  $colorbadge?>">
                  <?php echo $preq?> 
                  <span class="visually-hidden">Approve Perdiem Settlement</span>
              </span>
              <a href="../finance/perdiem.php" class='sidebar-link'>
                  <i class="far fa-file-alt"></i>
                  <span>Approve Perdiem Settlement
                  </span>
              </a>
          </li>
          <hr>
          <?php 
          }       
        if(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION['role'] == 'Disbursement')// || $_SESSION["role"] == "Director" || $_SESSION["role"] == "Owner"
        {
        ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='review'>
            <?php 
                $color_badge = ($val[3] == 0)?"bg-secondary":(($val[3] < 5)?"bg-success":(($val[3] < 10)?"bg-warning":"bg-danger"));
            ?> 
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[3]?> 
                <span class="visually-hidden">Review POs</span>
            </span>
            <a href="../finance/review.php" class='sidebar-link'>
                <i class="far fa-file-alt"></i>
                <span>Review POs
                </span>
            </a>
        </li>
        <hr>
        <?php }
    } 


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////Purchase officer///////////////////////////////////////////////////////////////

if(($_SESSION['department'] == "Procurement" && $_SESSION["role"]=='Purchase officer'))//  || (isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    $total_count = 0;
    $val = [];
    $val[0] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,pending,purchase_officer,".$_SESSION['username']);
    $total_count += $val[0];
    
    $val[1] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Accepted, ||status,Complete,purchase_officer,".$_SESSION['username']);
    $total_count += $val[1];
    $val[2] = badge_count_custom($conn,$conn_fleet,"`price_information` AS p_i Inner join `purchase_order` AS P ON p_i.purchase_order_id=P.purchase_order_id AND p_i.cluster_id=P.cluster_id"
    ,"P.status,Payment Processed, ||P.status,Collected-not-comfirmed,collector,".$_SESSION['username'].",P.procurement_company,$_SESSION[company],selected,1","*","providing_company,P.cluster_id");
    $val[2] += badge_count_custom($conn,$conn_fleet,"`purchase_order`"
    ,"status,Payment Processed, ||status,Collected-not-comfirmed,collector,".$_SESSION['username'].",procurement_company,$_SESSION[company],cluster_id,IS NULL");
    $total_count += $val[2];
// P.status = 'Payment Processed' OR P.status = 'Collected-not-comfirmed') AND collector = '$_SESSION[username]' AND P.procurement_company='$_SESSION[company]'
    $val[3] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Recollect,collector,".$_SESSION['username']);
    $total_count += $val[3];

    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
    ?>
    <!-- <li class=" sidebar-item row" onclick="drop_down(this,'PO_side')">
    <span class='position-relative btn small text-primary sidebar-link col-10'>Purchase officer
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge_all?>">
            <?php //echo $total_count?> 
            <span class="visually-hidden">PO Tab</span>
        </span>
    </span>
    <span id='PO_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
    <div class="drops d-none" id="PO_side"> -->
    <li
        class="position-relative mb-3 sidebar-item  " id='assigned'>
        <?php 
            $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[0]?> 
            <span class="visually-hidden">Requests Assigned</span>
        </span>
        <a href="../<?php echo $pos.$_SESSION['loc']?>newJobs.php" class='sidebar-link'>
            <i class="far fa-clipboard"></i>
            <span>Assigned POs</span>
        </a>
    </li>
<hr>
    <li
        class="position-relative mb-3 sidebar-item  " id='accepted'>
        <?php 
            $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[1]?> 
            <span class="visually-hidden">Requests Accepted</span>
        </span>
        <a href="../<?php echo $pos.$_SESSION['loc']?>acceptedJob.php" class='sidebar-link'>
            <i class="fas fa-clipboard-check"></i>
            <span>Accepted POs</span>
        </a>
    </li>
<hr>
    <li
        class="position-relative mb-3 sidebar-item  " id='collection'>
        <?php 
            $color_badge = ($val[2] == 0)?"bg-secondary":(($val[2] < 5)?"bg-success":(($val[2] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php 
// badge_count_custom($conn,$conn_fleet,"purchase_order","status,Payment Processed, ||status,Partly Collected, ||status,Collected-not-comfirmed,collector,".$_SESSION['username'].",company,".$_SESSION['company'],"cluster_id")
                echo $val[2]
            ?> 
            <span class="visually-hidden">Requests waiting to be Collected</span>
        </span>
        <a href="../<?php echo $pos.$_SESSION['loc']?>collectionJob.php" class='sidebar-link'>
            <i class="bi bi-stack"></i>
            <span>
            Item Collection</span>
        </a>
    </li>
<hr>
    <li
        class="position-relative mb-3 sidebar-item  " id='Recollection'>
        <?php 
            $color_badge = ($val[3] == 0)?"bg-secondary":(($val[3] < 5)?"bg-success":(($val[3] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php 
                echo $val[3]
            ?> 
            <span class="visually-hidden">Requests waiting to be Recollected</span>
        </span>
        <a href="../<?php echo $pos.$_SESSION['loc']?>recollect.php" class='sidebar-link'>
            <i class="fas fa-redo-alt"></i>
            <span>
            Item Recollection</span>
        </a>
    </li>
    <hr>
<?php
}

if(($_SESSION["department"] == 'Property'|| $_SESSION['role']=="Admin" || $_SESSION['department'] == "Procurement"))//  || (isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='hist_jacket'>
            <a href="../<?php echo $pos?>requests/historyJacket.php" class='sidebar-link'>
                <i class="fas fa-history"></i>
                <span>History Jacket</span>
            </a>
        </li>
    <hr>
<?php
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////manager///////////////////////////////////////////////////////////////


$indent = "";
if(($_SESSION['department'] == "Procurement" && $_SESSION["role"]=='manager') || ($_SESSION['department'] == "Procurement" && ($_SESSION["role"]=='user' || $_SESSION["role"]=='Senior Purchase officer')) || ($_SESSION['additional_role']==1) || (isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])))//  || (isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    $indent = "";
    $total_count = 0;
    $val = [];
    // (
    //     (request_type != 'Fixed assets' AND `status`='Approved By Director') OR 
    //     (request_type = 'Fixed assets' AND `status`='Approved By Owner') OR 
    //     (company != 'Hagbes HQ.' AND (
    //     (request_type = 'Spare and Lubricant' AND ((type = 'Spare' AND `mode` = 'Internal' AND `status`='Approved By Property') OR 
    //     ((`mode` = 'External' OR (`mode` = 'Internal' AND `type` = 'Lubricant')) AND `status`='Store Checked'))) OR 
    //     (request_type = 'Tyre and Battery' AND ((`mode` = 'Internal' AND `status`='Approved By Property') OR 
    //     (`mode` = 'External' AND `status`='Store Checked'))) OR 
    //     ((request_type = 'Miscellaneous' OR request_type = 'Consumer Goods') AND `status`='Store Checked')))
    // )
    $val[0] = badge_count_requests($conn,$conn_fleet,"status,Approved By Director,request_type,!=Fixed Assets,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"status,Approved By Property,request_type,Fixed Assets,department,Owner,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"status,Approved By Owner,procurement_company,".$_SESSION['company']);
    // fix columon equation
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Tyre and Battery,status,Store Checked, ||status,Approved By Property,mode,External,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Spare and Lubricant,status,Store Checked, ||status,Approved By Property,mode,External,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Spare and Lubricant,status,Store Checked, ||status,Approved By Property,mode,Internal,type,Lubricant,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Miscellaneous, ||request_type,Consumer Goods,status,Store Checked, ||status,Approved By Property,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Stationary and Toiletaries,status,Approved By Property,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Tyre and Battery,status,Approved By Property,mode,Internal,procurement_company,".$_SESSION['company']);
    $val[0] += badge_count_requests($conn,$conn_fleet,"company,!=Hagbes HQ., ||department,Owner, ||customer,`director`,request_type,Spare and Lubricant,status,Approved By Property,mode,Internal,type,Spare,procurement_company,".$_SESSION['company']);
    $total_count += $val[0];
    
    $val[1] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Payment Processed,collector,IS NULL,procurement_company,".$_SESSION['company']);
    $total_count += $val[1];

    $val[2] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,pending,procurement_company,".$_SESSION['company']);
    $val[2] += badge_count_custom($conn,$conn_fleet,"purchase_order","status,Payment Processed,collector,IS NOT NULL,procurement_company,".$_SESSION['company']);
    $total_count += $val[2];
    $val[7] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Performa Comfirmed,procurement_company,".$_SESSION['company']);
    $total_count += $val[7];
    
if($_SESSION["role"]!='user' && $_SESSION["role"]!='Senior Purchase officer')
{
    $indent = "ms-3";
    $val[3] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Recollection Failed,procurement_company,".$_SESSION['company']);
    $total_count += $val[3];
    
    $val[4] = badge_count_custom($conn,$conn_fleet,"purchase_order","status,Complete,procurement_company,".$_SESSION['company']);
    $total_count += $val[4];

    $val[5] = badge_count_custom($conn,$conn_fleet,"cluster","status,Pending, ||status,updated,procurement_company,".$_SESSION['company']);
    $total_count += $val[5];

    $val[6] = badge_count_custom($conn,$conn_fleet,"cluster","status,Approved,procurement_company,".$_SESSION['company']);
    $total_count += $val[6];

    $cond2 ="next_step,procurement,company,".$_SESSION['procurement_company']."";
    $val[8] = badge_count_requests($conn,$conn_fleet,$cond2);
    $total_count += $val[8];
}
    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
if($_SESSION["role"]!='user' && $_SESSION["role"]!='Senior Purchase officer')
{?>
    <li class=" sidebar-item row" onclick="drop_down(this,'manager_side')">
    <span class='position-relative btn small text-primary sidebar-link col-10'>Procurement Manager
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge_all?>">
            <?php echo $total_count?> 
            <span class="visually-hidden">Procurement Manager Tab</span>
        </span>
    </span>
    <span id='manager_side_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></li>
    <div class="drops d-none" id="manager_side">
<?php } ?>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='assign'>
        <?php 
            $color_badge = ($val[0] == 0)?"bg-secondary":(($val[0] < 5)?"bg-success":(($val[0] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[0]?> 
            <span class="visually-hidden">Requests waiting to be Assigned</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/GS/assignOfficer.php" class='sidebar-link'>
            <i class="bi bi-journal-text"></i>
            <span>POs For Proforma</span>
        </a>
    </li>
    <?php if($_SESSION["role"]=='user' || $_SESSION["role"]=='Senior Purchase officer') echo "<hr>";?>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='collection'>
        <?php 
            $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[1]?> 
            <span class="visually-hidden">Requests waiting to be Collected</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/GS/assignCollector.php" class='sidebar-link'>
            <i class="bi bi-journal-text"></i>
            <span>
            Cheque Signed POs</span>
        </a>
    </li>
    <?php if($_SESSION["role"]=='user' || $_SESSION["role"]=='Senior Purchase officer') echo "<hr>";?>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='assigned'>
        <?php 
            $color_badge = ($val[2] == 0)?"bg-secondary":(($val[2] < 5)?"bg-success":(($val[2] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[2]?> 
            <span class="visually-hidden">Requests Assigned</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/GS/viewAssigned.php" class='sidebar-link'>
            <i class="bi bi-eye-fill"></i>
            <span>Assigned POs</span>
        </a>
    </li>
    <?php if($_SESSION["role"]=='user' || $_SESSION["role"]=='Senior Purchase officer') echo "<hr>";?>
    <?php
if($_SESSION["role"]!='user' && $_SESSION["role"]!='Senior Purchase officer'){
    ?>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='Failed_Collection'>
        <?php 
            $color_badge = ($val[3] == 0)?"bg-secondary":(($val[3] < 5)?"bg-success":(($val[3] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[3]?> 
            <span class="visually-hidden">Failed Collection</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/manager/recollection.php" class='sidebar-link'>
            <i class="bi bi-journal-text"></i>
            <span>Failed Collections</span>
        </a>
    </li>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='performa'>
        <?php 
            $color_badge = ($val[4] == 0)?"bg-secondary":(($val[4] < 5)?"bg-success":(($val[4] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[4]?> 
            <span class="visually-hidden">Requests with Proforma</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/manager/openProforma.php" class='sidebar-link'>
            <i class="fab fa-get-pocket"></i>
            <span>
            Proforma Received 
            </span>
        </a>
    </li>
    
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='check_comp_sheet'>
        <?php 
            $color_badge = ($val[5] == 0)?"bg-secondary":(($val[5] < 5)?"bg-success":(($val[5] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[5]?> 
            <span class="visually-hidden">Comparisions / Proforma Inserted</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/manager/check_comp_sheet.php" class='sidebar-link'>
            <i class="fas fa-envelope-open-text"></i>
            <span>New Comparisions Sheets</span>
        </a>
    </li>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='additional'>
        <?php 
            $color_badge = ($val[6] == 0)?"bg-secondary":(($val[6] < 5)?"bg-success":(($val[6] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[6]?> 
            <span class="visually-hidden">Committee Approved Requests</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/manager/sentToFinance.php" class='sidebar-link'>
            <i class="fas fa-check-double"></i>
            <span>Committee Approved Requests
            </span>
        </a>
    </li>
    <?php }?>
    <li
        class="position-relative mb-3 sidebar-item <?=$indent?> " id='csheet'>
        <?php 
            $color_badge = ($val[7] == 0)?"bg-secondary":(($val[7] < 5)?"bg-success":(($val[7] < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
            <?php echo $val[7]?> 
            <span class="visually-hidden">Requests Waiting Comparision Sheets</span>
        </span>
        <a href="../<?php echo $pos?>Procurement/senior/createComparision.php" class='sidebar-link'>
            <i class="fas fa-plus-circle"></i>
            <span>
            Create Comparision Sheet</span>
        </a>
    </li>
    <?php if($_SESSION["role"]=='user' || $_SESSION["role"]=='Senior Purchase officer') echo "<hr>";?> 
    <?php 
if($_SESSION["role"]!='user' && $_SESSION["role"]!='Senior Purchase officer'){
    $color_badge = ($val[8] == 0)?"bg-secondary":(($val[8] < 5)?"bg-success":(($val[8] < 10)?"bg-warning":"bg-danger"));
    if($is_agreement){
?> 
    <li class="position-relative mb-3 sidebar-item  " id='agreement_approval1'>
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[8]?> 
                <span class="visually-hidden">Requests Waiting Approval</span>
            </span>
        <a href=<?php echo $pos.'../requests/agreementApprovalCs.php'?> class='sidebar-link'>
        <i class="fa fa-solid fa-handshake"></i>
            <span>Agreement Purchase</span>
        </a>
    </li>
<hr>
<?php
    }
    ?>
    </div>
    <?php
}
}
////////////////////////////////////////////////////////////Senior Purchase officer///////////////////////////////////////////////////////////////


if(($_SESSION['department'] == "Procurement" && $_SESSION["role"]=='Senior Purchase officer'))//  || (isset($_SESSION["managing_department"]) && in_array("Procurement",$_SESSION["managing_department"])) || $_SESSION['department'] == 'Owner')
{
    $total_count = 0;
    $val = [];
    if(!isset($row_manager))
    {
        $sql = "SELECT * from account where ((`role` = 'Manager' and department = 'Procurement') OR additional_role) and company = ? and `status` = 'active'";
        $stmt_procumrement_account = $conn->prepare($sql);
        $stmt_procumrement_account -> bind_param("s", $_SESSION['company']);
        $stmt_procumrement_account -> execute();
        $result = $stmt_procumrement_account->get_result();
        $row_manager = $result->fetch_assoc();
    }

    $val[1] = 0;
    if($row_manager["role"]=="manager" && $row_manager["department"]=='Procurement' && $row_manager["company"]=='Hagbes HQ.')
    {
        $temp ="P.scale,procurement, ||P.scale,owner,P.procurement_company,".$row_manager['company'];
        $val[1] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if($row_manager["role"]=="manager" && $row_manager["department"]=='Procurement')
    {
        $temp ="P.scale,Branch,P.procurement_company,".$row_manager['company'];
        $val[1] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(strpos($row_manager["type"],"Branch Committee") !== false)
    {
        $temp ="scale,Branch,P.company,".$row_manager['company'];
        $val[1] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if(strpos($row_manager["type"],"HO Committee") !== false)
    {
        $temp ="scale,HO";
        $val[1] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if($row_manager["role"]=="Director" && ($row_manager["department"]=='GM' || in_array("All Departments",$row_manager["managing_department"])) && $row_manager["company"]!='Hagbes HQ.')
    {
        $temp ="scale,HO, ||scale,Owner,P.company,".$row_manager["company"].",R.department, !=".$row_manager["department"];
        $val[1] += badge_count_custom($conn,$conn_fleet,"requests AS R INNER JOIN purchase_order AS P on R.request_id = P.request_id  Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    }
    if($row_manager["department"]=='Owner')
    {
        $temp ="scale,Owner";
        $scale = "`scale` = 'Owner'";
        $id_sidenave = "Owner";
        $val[1] += badge_count_custom($conn,$conn_fleet,"purchase_order AS P Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
        $join_sql = "requests AS R INNER JOIN purchase_order AS P ON R.request_id = P.request_id INNER JOIN cluster AS C ON P.cluster_id = C.id";
        $condition = "C.status,Generated,R.customer,".$row_manager['username'].",R.company,".$row_manager['company'];
        $val[1] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition,"DISTINCT cluster_id");   
    }
    else if($row_manager['role'] == "manager" || $row_manager["role"]=='Director')
    {
        $temp =",spec_dep,!=".$row_manager['department'];
        $join_sql = "requests AS R INNER JOIN purchase_order AS P ON R.request_id = P.request_id INNER JOIN cluster AS C ON P.cluster_id = C.id";
        $condition = "C.status,Generated,R.department,".$row_manager['department'].",R.company,".$row_manager['company'];
        if(strpos($row_manager["type"],"Branch Committee") !== false)
            $condition .= ",scale,!=Branch,P.company,".$row_manager['company'];
        if(strpos($row_manager["type"],"HO") !== false)
            $condition .= ",scale,!=HO,P.company,".$row_manager['company'];
        $val[1] += badge_count_custom($conn,$conn_fleet,$join_sql,$condition.$temp,"DISTINCT cluster_id");
    }
    // FOR IT
    $temp ="spec_dep,".$row_manager['department'];
    $val[1] += badge_count_custom($conn,$conn_fleet,"requests AS R Inner Join purchase_order AS P on P.request_id = R.request_id Inner Join cluster AS C on P.cluster_id = C.id","C.status,Generated,performa_id,IS NOT NULL,$temp","DISTINCT cluster_id");
    $total_count += $val[1];
    $color_badge_all = ($total_count == 0)?"bg-secondary":(($total_count < 5)?"bg-success":(($total_count < 10)?"bg-warning":"bg-danger"));
    
    ?>
    <?php
        $color_badge = ($val[1] == 0)?"bg-secondary":(($val[1] < 5)?"bg-success":(($val[1] < 10)?"bg-warning":"bg-danger"));
    ?>
        <li
            class="position-relative mb-3 sidebar-item  " id='view_committee'>
            <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
                <?php echo $val[1]?> 
                <span class="visually-hidden">Requests in Committee approval</span>
            </span>
            <a href="../<?php echo $pos?>Procurement/senior/progress.php" class='sidebar-link'>
                <i class="fas fa-history"></i>
                <span>View requests at Committee</span>
            </a>
        </li>
        <hr>
<?php
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
    <?php 
// $sql = "SELECT * FROM `privilege`";
// $result_temp = $conn->query($sql);
// if($result_temp->num_rows>0)
//     while($row_temp = $result_temp->fetch_assoc())
//     {
//         $privilage[$row_temp["type"]] = explode(",", str_replace(' ', '', $row_temp["company"]));
//     } 
    ?>
<!-- <li class=" sidebar-item row" onclick="drop_down(this,'req')"><span class='btn small text-primary sidebar-link col-10'>Requests </span><span id='req_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></span></li>
<div class="drops d-none" id="req">
    <?php
        // $query = "SELECT * from catagory";
        // $ress = $conn->query($query);
        // if($ress->num_rows>0)
        //     while($re = $ress->fetch_assoc())
        //     {
        //         if(in_array($_SESSION["company"],$privilage[$re['catagory']]) || in_array("All",$privilage[$re['catagory']]))
        //         {
        //             $na_t=str_replace(" ","",$re['catagory']);
        //             $type_str =(strpos($re['path'],"form"))?"?r_type=".$re['catagory']:"";
        //             ?>
        //             <li
        //                 class="position-relative mb-3 sidebar-item  " id='<?php //echo $na_t?>_side'>
        //                     <?php 
        //                         $temp_x = (($temp_requests == "")?"":",")."request_type,".$re['catagory'];
        //                         $val = badge_count_requests($conn,$conn_fleet,"$temp_requests$temp_x");
        //                         $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
        //                     ?> 
        //                     <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
        //                         <?php //echo $val?> 
        //                         <span class="visually-hidden"><?php //echo $re['catagory']?></span>
        //                     </span>
        //                 <a href="<?php //echo $pos?>../<?php //echo $re['path'].$type_str?>" class='sidebar-link'>
        //                 <i class="bi bi-bricks"></i>
        //                     <span><?php //echo $re['catagory']?></span>
        //                 </a>
        //             </li>
        //         <?php
        //         }
        //     }
    ?>
</div>
<hr> -->
<!-- <li class=" sidebar-item row" onclick="drop_down(this,'other')"><span class='btn small text-primary sidebar-link col-10'>Other </span><span id='other_icon' class='col-1 sidebar-link ' style = 'cursor: pointer;'><i class='fas fa-angle-down'></i></span></li>
<div class="drops d-none" id="other" >
    <li
        class="position-relative mb-3 sidebar-item  " id='recieve'>
        <?php 
            // $val = badge_count_requests($conn,$conn_fleet,"status,Complete-uncomfirmed,customer,".$_SESSION['username'].",company,".$_SESSION['company']);
            // $join_sql = "requests AS R INNER JOIN `stock` AS S ON R.stock_info = S.id";
            // $condition = "S.status,Complete-uncomfirmed,customer,".$_SESSION['username'].",department,".$_SESSION['department'].",company,".$_SESSION['company'];
            // $val += badge_count_custom($conn,$conn_fleet,$join_sql,$condition);
            // $color_badge = ($val == 0)?"bg-secondary":(($val < 5)?"bg-success":(($val < 10)?"bg-warning":"bg-danger"));
        ?> 
        <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php //echo $color_badge?>">
            <?php //echo $val?> 
            <span class="visually-hidden">Requests Collected</span>
        </span>
        <a href="<?php //echo $pos?>../requests/recieve.php" class='sidebar-link'>
            <i class="bi bi-file-earmark-check"></i>
            <span>Recieve Requested Item</span>
        </a>
    </li>
    <li
        class="mb-3 sidebar-item  " id='report'>
        <a href="<?php //echo $pos?>../requests/report.php" class='sidebar-link'>
            <i class="bi bi-file-text-fill"></i>
            <span>
                View Report</span>
        </a>
    </li>
</div> -->
<?php 
if($_SESSION["role"]=="Admin" || $_SESSION["role"]=="Owner" || ($_SESSION["department"]=="Procurement and Adminstration" && $_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'Director') || ($_SESSION["department"]=="Procurement" && $_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'Manager') || ($_SESSION["company"]=="Hagbes HQ." && $_SESSION["role"] == 'GM'))
{?>
<li class="mb-3 sidebar-item nav-item " id='Report'>
        <a class="sidebar-link" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#" aria-expanded="true">
          <i class="bi bi-bar-chart"></i><span>Reports </span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav" style="list-style-type:none">
          <li class='py-1'>
            <a href="<?php echo $pos?>../admin/report.php?id=purchase-office"  class="sidebar-link" >
              <span>Purchase Office</span>
            </a>
          </li>
          <li class='py-1'>
            <a href="<?php echo $pos?>../admin/report.php?id=purchase-officer" class="sidebar-link">
              <span>Purchaser Performance</span>
            </a>
          </li>
          <li class='py-1'>
            <a href="<?php echo $pos?>../admin/report.php?id=senior-purchase-officer" class="sidebar-link">
              <span>Senior POs</span>
            </a>
          </li>
          <li class='py-1'>
            <a href="<?php echo $pos?>../admin/report.php?id=requesting-department-performance" class="sidebar-link">
              <span>Requesting Departnment Performance</span>
            </a>
          </li>
          <li class='py-1'>
            <a href="<?php echo $pos?>../admin/report.php?id=payment-processing" class="sidebar-link">
              <span>Payment Processing</span>
            </a>
          </li>
        </ul>
      </li>
      <hr>
      <li class="mb-3 sidebar-item nav-item " id='settings_view'>
    <a class="sidebar-link" data-bs-target="#charts-nav" href="<?php echo $pos?>../admin/setting.php" class="sidebar-link">
          <i class="bi bi-gear"></i><span>View Setting</span>
        </a>
      </li>
      <hr>

<?php 
} ?>
<?php
    $lowername = strtolower($_SESSION['username']);
    $val_mentions = badge_count_custom($conn,$conn_fleet,"recorded_time as R inner join issues as I on R.for_id = I.id and R.database_name = 'issues'","opperation,Issue-Mention:$lowername,I.status,Open");
    $color_badge = ($val_mentions == 0)?"d-none":(($val_mentions < 5)?"bg-success":(($val_mentions < 10)?"bg-warning":"bg-danger"));
?>
<li class="position-relative mb-3 sidebar-item nav-item" id='issues_page'>
    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge <?php echo $color_badge?>">
        <?php echo $val_mentions?> 
        <span class="visually-hidden">Mentions in Issue</span>
    </span>
    <a class="sidebar-link" href="<?php echo $pos?>../requests/issue.php" class="sidebar-link">
        <i class="fa fa-exclamation"></i><span>Issues</span>
    </a>
</li>
<hr>
<?php if(strpos($_SESSION["a_type"],'Admin') !== false || $_SESSION['role'] == 'Director' || $_SESSION['role'] == 'Owner' || ($_SESSION['role'] == 'manager' && $_SESSION['department'] == 'IT')){?>
<li
    class="mb-3 sidebar-item">
    <button class='btn sidebar-link' onclick='view_documents("smsusage")' data-bs-toggle='modal' data-bs-target='#documents'>
        <i class="fas fa-circle-notch"></i>
        <span>All SMS Usage</span>
    </button>
</li>
<hr>
<?php }?>

<li
    class="mb-3 sidebar-item">
    <button class='btn sidebar-link text-danger' onclick='view_documents("documents_details")' data-bs-toggle='modal' data-bs-target='#documents'>
        <i class="far fa-file-pdf  text-danger"></i>
        <span>Documentations</span>
    </button>
</li>
<?php
// $conn->close();
// $conn_pms->close();
// $conn_fleet->close();
// $conn_ws->close();
// $conn_ais->close();
// $conn_sms->close();
// $conn_mrf->close();
?>