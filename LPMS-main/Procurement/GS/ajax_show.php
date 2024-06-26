<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include '../../common/functions.php';
    function divcreate($str,$n)
    {
        echo "
        <div class='card'>
        <div class='card-header'>
            <h4 class='text-center'>Details For $n Request</h4>
        </div>
        <div class='card-body'>
            $str
        </div>
        <button type='button' name='".$_GET['cl_id']."' onclick='compsheet_loader(this)' class='form-control form-control-small btn-outline-primary shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet<i class='text-white fas fa-clipboard-list fa-fw'></i></button></li>
        </div>
        ";
    }
    if(isset($_GET['limit_offset'])||($_GET['type']&&!isset($_GET['company']))){
        $limit=$_GET['limit_offset'];
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
        $b_count = 0;
        $sql_request_type = "SELECT request_type FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? Group By request_type";
        $stmt_request_type = $conn->prepare($sql_request_type);
        $stmt_request_type->bind_param("s", $_SESSION['company']);
        $stmt_request_type->execute();
        $result_request_type = $stmt_request_type->get_result();
        if($result_request_type->num_rows>0)
            while($rs = $result_request_type->fetch_assoc())
            {
                $type = $_GET['type']=='All Requests'?"":" and request_type='".$_GET['type']."'";
                $sql_request_type_count = "SELECT count(*) AS num, stock_info, requested_quantity FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? $type ";
                $stmt_request_type_count = $conn->prepare($sql_request_type_count);
                $stmt_request_type_count->bind_param("s", $_SESSION['company']);
                $stmt_request_type_count->execute();
                $result_request_type_count = $stmt_request_type_count->get_result();
                if($result_request_type_count->num_rows>0)
                    while($r = $result_request_type_count->fetch_assoc())
                    {
                        if($r['num']>0)
                        {
                            $stmt_stock->bind_param("i", $r['stock_info']);
                            $stmt_stock->execute();
                            $result_stock = $stmt_stock->get_result();
                            if($result_stock->num_rows>0)
                                while($rr = $result_stock->fetch_assoc())
                                {
                                    $instock = $rr['in-stock'];
                                    $forpurchase = $rr['for_purchase'];
                                }
                            if($instock == $r['requested_quantity']) 
                            continue;
                            
                            $b_count++;
                        }
                    }
            }

        $sql_request_custom = "SELECT * FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ? $type ORDER BY date_needed_by ASC, request_id DESC";
        $stmt_request_custom = $conn->prepare($sql_request_custom);
        $stmt_request_custom->bind_param("s", $_SESSION['company']);
        $stmt_request_custom->execute();
        $result_request_custom = $stmt_request_custom->get_result();
        $length=$result_request_custom->num_rows;
    
        $sql_request_custom2 = "SELECT * FROM requests Inner Join report ON requests.request_id = report.request_id WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ? $type 
        ORDER BY (
            CASE 
                When `Owner_approval_date` IS NOT NULL THEN Owner_approval_date 
                When `Directors_approval_date` IS NOT NULL THEN Directors_approval_date 
                When `property_approval_date` IS NOT NULL THEN property_approval_date 
                ELSE stock_check_date    
            END) DESC
        limit $limit";
        $stmt_request_custom2 = $conn->prepare($sql_request_custom2);
        $stmt_request_custom2->bind_param("s", $_SESSION['company']);
        $stmt_request_custom2->execute();
        $result_request_custom2 = $stmt_request_custom2->get_result();
        $str="";
        if($result_request_custom2->num_rows>0)
            while($row2 = $result_request_custom2->fetch_assoc())
            {
                        $type = $row2["request_type"]; 
                $na_t=str_replace(" ","",$type);
                $stmt_stock->bind_param("i", $row2['stock_info']);
                $stmt_stock->execute();
                $result_stock = $stmt_stock->get_result();
                if($result_stock -> num_rows>0)
                    while($r = $result_stock->fetch_assoc())
                    {
                        $instock = $r['in-stock'];
                        $forpurchase = $r['for_purchase'];
                    }
                if($instock == $row2['requested_quantity']) continue;
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
                    Proforma Request for :-
                    Item - $row2[item], 
                    Quantity - $row2[requested_quantity] $row2[unit],
                    Date Delivery before - ".date("d-M-Y", strtotime($row2['date_needed_by']));
                    if(isset($row2['specification']) && !is_null($row2['specification']))
                    {
                        $stmt_specification->bind_param("i", $row2['specification']);
                        $stmt_specification->execute();
                        $result_specification = $stmt_specification->get_result();
                        if($result_specification->num_rows>0)
                        while($row_spec = $result_specification->fetch_assoc())
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
                                <input value='".$na_t."_".$row2['request_id']."' class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                                ".$name."
                                $printpage
                                <span class='small text-secondary d-block mt-2'>$type <span class='text-primary'>(".$row2['company'].")</span></span>
                                <div class='mt-2'>
                                    Share <i class='bi bi-share-fill text-dark me-1'></i> : 
                                    <a href='https://t.me/share/url?url=$details' target='_blank'><i class='bi bi-telegram text-primary me-1'></i></a>
                                    <a href='https://api.whatsapp.com/send?text=$details' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>
                                </div>
                            </h3>
                            <form method='GET' action='allphp.php'>
                            <ul>
                                <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                
                                <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>
                                <li class='text-start'><span class='fw-bold'>Priority : </span><span id='p_".$na_t."_".$row2['request_id']."'>Unassigned</span></li>
                                <li class='row'>
                                <div class='input-group mb-3'>
                                    <select class='form-select form-select-sm' name='".$na_t."_".$row2['request_id']."' id='".$na_t."_".$row2['request_id']."'>
                                        <option value=''>--Select Purchase Officer--</option>";
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
                                                $str.= "<option value='".$officer."'>$officer (Company : $row[company])</option>";
                                            }
                                        }
                            $str.=" </select>
                                    <input type='button' value='Assign'  data-bs-toggle='modal' data-bs-target='#priority_modal' onclick='priority(this)' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>
                                    <!--<input type='submit' value='Assign' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>-->
                                </div>
                                <div class='divider fw-bold'>
                                    <div class='divider-text'>
                                        Or
                                    </div>
                                </div>
                            <div class='mb-2'>
                            <a class='btn btn-success btn-sm mx-auto' href='./mailingList.php?request_id=".$row2['request_id']."' title='$pos' id='send_".$na_t."_".$row2['request_id']."'
                            name='".$na_t."_".$row2['request_id']."'>Email Vendors <i class='fa fa-envelope text-white ms-1'></i></a>
                            </div>
                            </ul>
                            </form>
                        </div>
                    </div>
                ";
                                    }
                                    echo $str;
                                    
    }else if(isset($_GET['type'])&&isset($_GET['company'])){

        $date_filter='';
        if($_GET['start']!=''&&$_GET['end']==''){
            $date_filter=" and datediff('".$_GET['start']."',date_requested)<0";
        }
        if($_GET['end']!=''&&$_GET['start']==''){
            $date_filter=" and datediff('".$_GET['end']."',date_requested)>0";
        }
        if($_GET['end']!=''&&$_GET['start']!=''){
            $date_filter=" and datediff('".$_GET['start']."',date_requested)<0 and datediff('".$_GET['end']."',date_requested)>0";
        }
        if(($_GET['end']==''||$_GET['end']=='undefined')&&($_GET['start']==''||$_GET['start']=='undefined')){
            $date_filter="";
        }
            $company_filter="";
            $type_filter="";
            $keyword_filter="";
            if($_GET['keyword']!=''&&$_GET['keyword']!='undefined')
            $keyword_filter= " and item like '%".$_GET['keyword']."%'";
            if(isset($_GET['company'])&&$_GET['company']!=''&&$_GET['company']!='all')
            $company_filter= " and company='".$_GET['company']."'";
            if(isset($_GET['type'])&&$_GET['type']!=''&&$_GET['type']!='all')
            $type_filter= " and request_type='".$_GET['type']."'";
            $cond=$company_filter.$type_filter.$date_filter.$keyword_filter;
            $stmt_company -> bind_param("s", $_SESSION['company']);
            $stmt_company -> execute();
            $result_company = $stmt_company -> get_result();
            if($result_company -> num_rows > 0){
                while($row=$result_company -> fetch_assoc()){
                    $stor_swap=$row['store_swap'];
                }
            }
            $condition = "(
            (request_type != 'Fixed assets' AND `status`='Approved By Director') OR 
            (request_type = 'Fixed assets' AND `status`='Approved By Owner') OR 
            (department = 'Owner' AND `status`='Approved By Property') OR 
            ((company != 'Hagbes HQ.' OR department = 'Owner' OR (director IS NOT NULL AND customer = director)) AND (
            (request_type = 'Stationary and Toiletaries' AND `status`='Approved By Property') OR 
            (request_type = 'Spare and Lubricant' AND ((type = 'Spare' AND `mode` = 'Internal' AND `status`='Approved By Property') OR 
            ((`mode` = 'External' OR (`mode` = 'Internal' AND `type` = 'Lubricant')) AND (`status`='Store Checked' OR `status`='Approved By Property')))) OR 
            (request_type = 'Tyre and Battery' AND ((`mode` = 'Internal' AND `status`='Approved By Property') OR 
            (`mode` = 'External' AND (`status`='Store Checked' OR `status`='Approved By Property')))) OR 
            ((request_type = 'Miscellaneous' OR request_type = 'Consumer Goods') AND (`status`='Store Checked' OR `status`='Approved By Property'))))
            )";
            $b_count = 0;
            $sql_types_custom = "SELECT request_type FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? Group By request_type";
            $stmt_types_custom = $conn->prepare($sql_types_custom);
            $stmt_types_custom->bind_param("s", $_SESSION['company']);
            $stmt_types_custom->execute();
            $result_types_custom = $stmt_types_custom->get_result();
            if($result_types_custom->num_rows>0)
                while($rs = $result_types_custom->fetch_assoc())
                {
                    $type = $_GET['type']=='All Requests'?"":" and request_type='".$_GET['type']."'";
                    $sql_query_custom = "SELECT count(*) AS num, stock_info, requested_quantity FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND `procurement_company` = ? $type ";
                    $stmt_query_custom = $conn->prepare($sql_query_custom);
                    $stmt_query_custom->bind_param("s", $_SESSION['company']);
                    $stmt_query_custom->execute();
                    $result_query_custom = $stmt_query_custom->get_result();
                    if($result_query_custom->num_rows>0)
                        while($r = $result_query_custom->fetch_assoc())
                        {
                            if($r['num']>0)
                            {
                                $stmt_stock->bind_param("i", $r['stock_info']);
                                $stmt_stock->execute();
                                $result_stock = $stmt_stock->get_result();
                                if($result_stock->num_rows>0)
                                    while($rr = $result_stock->fetch_assoc())
                                    {
                                        $instock = $rr['in-stock'];
                                        $forpurchase = $rr['for_purchase'];
                                    }
                                if($instock == $r['requested_quantity']) 
                                continue;
                                
                                $b_count++;
                            }
                        }
                }
        $sql_query_custom_count = "SELECT * FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ? $cond ORDER BY date_needed_by ASC, request_id DESC";
        $stmt_query_custom_count = $conn -> prepare($sql_query_custom_count);
        $stmt_query_custom_count -> bind_param("s", $_SESSION['company']);
        $stmt_query_custom_count -> execute();
        $result_query_custom_count = $stmt_query_custom_count -> get_result();
        $length=$result_query_custom_count -> num_rows;
    
        $sql_query_custom2 = "SELECT * FROM requests WHERE $condition AND `stock_info` IS NOT NULL AND  `procurement_company` = ? $cond ORDER BY date_needed_by ASC, request_id DESC ";
        $stmt_query_custom2 = $conn -> prepare($sql_query_custom2);
        $stmt_query_custom2 -> bind_param("s", $_SESSION['company']);
        $stmt_query_custom2 -> execute();
        $result_query_custom2 = $stmt_query_custom2 -> get_result();
        
        $str="";
        if($result_query_custom2 -> num_rows>0)
            while($row2 = $result_query_custom2 -> fetch_assoc())
            {
                        $type = $row2["request_type"]; 
                $na_t=str_replace(" ","",$type);
                $stmt_stock->bind_param("i", $row2['stock_info']);
                $stmt_stock->execute();
                $result_stock = $stmt_stock->get_result();
                if($result_stock->num_rows>0)
                    while($r = $result_stock->fetch_assoc())
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
                    Proforma Request for :-
                    Item - $row2[item], 
                    Quantity - $row2[requested_quantity] $row2[unit],
                    Date Delivery before - ".date("d-M-Y", strtotime($row2['date_needed_by']));
                    if(isset($row2['specification']) && !is_null($row2['specification']))
                    {
                        $stmt_specification->bind_param("i", $row2['specification']);
                        $stmt_specification->execute();
                        $result_specification = $stmt_specification->get_result();
                        if($result_specification->num_rows>0)
                        while($row_spec = $result_specification->fetch_assoc())
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
                                <input value='".$na_t."_".$row2['request_id']."' class='ch_boxes form-check-input float-start' type='checkbox' onclick='batch_select(this)'>
                                ".$name."
                                $printpage
                                <span class='small text-secondary d-block mt-2'>$type <span class='text-primary'>(".$row2['company'].")</span></span>
                                <div class='mt-2'>
                                    Share <i class='bi bi-share-fill text-dark me-1'></i> : 
                                    <a href='https://t.me/share/url?url=$details' target='_blank'><i class='bi bi-telegram text-primary me-1'></i></a>
                                    <a href='https://api.whatsapp.com/send?text=$details' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>
                                </div>
                            </h3>
                            <form method='GET' action='allphp.php'>
                            <ul>
                                <li class='text-start'><button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row2['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                <span class='fw-bold'>Item : </span>".$row2['item']."</button></li>
                                
                                <li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>
                                <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                <li class='text-start'><span class='fw-bold'>Date Needed By : </span>".$row2['date_needed_by']."</li>
                                <li class='text-start'><span class='fw-bold'>Priority : </span><span id='p_".$na_t."_".$row2['request_id']."'>Unassigned</span></li>
                                <li class='row'>
                                <div class='input-group mb-3'>
                                    <select class='form-select form-select-sm' name='".$na_t."_".$row2['request_id']."' id='".$na_t."_".$row2['request_id']."'>
                                        <option value=''>--Select Purchase Officer--</option>";
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
                                                $str.= "<option value='".$officer."'>$officer (Company : $row[company])</option>";
                                            }
                                        }
                            $str.=" </select>
                                    <input type='button' value='Assign'  data-bs-toggle='modal' data-bs-target='#priority_modal' onclick='priority(this)' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>
                                    <!--<input type='submit' value='Assign' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_".$na_t."_".$row2['request_id']."'>-->
                                </div>
                                <div class='divider fw-bold'>
                                    <div class='divider-text'>
                                        Or
                                    </div>
                                </div>
                            <div class='mb-2'>
                            <a class='btn btn-success btn-sm mx-auto' href='./mailingList.php?request_id=".$row2['request_id']."' title='$pos' id='send_".$na_t."_".$row2['request_id']."'
                            name='".$na_t."_".$row2['request_id']."'>Email Vendors <i class='fa fa-envelope text-white ms-1'></i></a>
                            </div>
                            </ul>
                            </form>
                        </div>
                    </div>
                ";
                                    }
                                    echo $str;  

    }
    else{
    $str='';
    $i=1;

    $stmt_cluster -> bind_param("i", $_GET['cl_id']);
    $stmt_cluster -> execute();
    $result_cluster = $stmt_cluster -> get_result();
    if($result_cluster->num_rows>0)
    while($r_clus = $result_cluster->fetch_assoc())
        $name= $r_clus['type'];
        $stmt_po_cluster -> bind_param("i", $_GET['cl_id']);
        $stmt_po_cluster -> execute();
        $result_po_cluster = $stmt_po_cluster -> get_result();
        if($result_po_cluster->num_rows>0)
        while($row = $result_po_cluster->fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            $sql_price_specific_selected = "SELECT * FROM `price_information` where cluster_id = ? AND `purchase_order_id`=? AND selected";
            $stmt_price_specific_selected = $conn -> prepare($sql_price_specific_selected);
            $stmt_price_specific_selected -> bind_param("ii", $row['cluster_id'], $row['purchase_order_id']);
            $stmt_price_specific_selected -> execute();
            $result_price_specific_selected = $stmt_price_specific_selected -> get_result();
            if($result_price_specific_selected -> num_rows>0)
                while($row_price = $result_price_specific_selected -> fetch_assoc())
                {
                    // echo $result_price->num_rows;
                    $u_price = $row_price['price'];
                    $total_price = $row_price['total_price'];

                    $stmt_cluster -> bind_param("i", $row['cluster_id']);
                    $stmt_cluster -> execute();
                    $result_cluster = $stmt_cluster -> get_result();
                    $clus_row=$result_cluster->fetch_assoc();
                    $sql_limits = "SELECT * FROM `limit_ho` where company= ? ORDER BY id DESC limit 1";
                    $stmt_limits = $conn->prepare($sql_limits);
                    $stmt_limits->bind_param("s", $clus_row['company']);
                    $stmt_limits->execute();
                    $result_limits = $stmt_limits->get_result();
                    if ($result_limits->num_rows ==0)
                    {
                        $comps = "Others";
                        $stmt_limits->bind_param("s", $comps);
                        $stmt_limits->execute();
                        $result_limits = $stmt_limits->get_result();
                    }
                    if($result_limits->num_rows>0)
                    {
                        $r_new = $result_limits->fetch_assoc();
                        $Vat = $r_new['Vat'];
                    }
                    else $Vat = 0.15;
                    $price_VAT = round((($row_price['total_price']*$Vat)+$row_price['total_price']),3);
                }
                $str .= "<ul class= 'list-group list-group-flush'>";
                $stmt_request->bind_param("i", $row['request_id']);
                $stmt_request->execute();
                $result_request = $stmt_request->get_result();
                if($result_request->num_rows>0)
                    while($row2 = $result_request->fetch_assoc())
                    {
                        // echo $result2->num_rows;
                        $stmt_account_active->bind_param("s", $row2['customer']);
                        $stmt_account_active->execute();
                        $result_account_active = $stmt_account_active->get_result();
                        if($result_account_active->num_rows>0)
                            while($row_dep = $result_account_active->fetch_assoc())
                            {
                                $dep = $row_dep['department'];
                            }
                            
                        $form_req = date("d-M-Y", strtotime($row2['date_requested']));
                        $form_need = date("d-M-Y", strtotime($row2['date_needed_by']));
                        $str .="
                                <div class='row'>
                                <li data-bs-toggle='collapse' data-bs-target='#content$i' role='button' aria-expanded='false' aria-controls='content$i' class='col-11 list-group-item list-group-item-success mb-3 text-capitalize'>
                                    <span class='text-primary text-capitalize'>Item $i - </span>".$row2['item'];
                                    $str .=(!is_null($row['collector']))?" <span class='fw-bold'><i class='fa fa-check-circle text-primary'></i> Officer Assigned</span>":"";
                                    $str .="
                                </li>
                            <li class='col-1 list-group-item border-0 mb-3 text-capitalize'>
                                <button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)'>
                                <i class='fas fa-external-link-alt'></i></button></li>
                                <div class='row m-auto w-100'>
                                <li class='list-group-item list-group-item-primary mb-4 m-auto collapse' id='content$i'>
                                    <ul class= 'list-group list-group-flush'>
                                        <li class='list-group-item list-group-item-light'><i class='text-primary'>Quantity  -  </i>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                        <div class='row m-auto w-100'>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Unit Price  -  </i>".number_format($u_price, 2, ".", ",")." Birr</li>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Total Price  -  </i>".number_format($total_price, 2, ".", ",")." Birr</li>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-4'><i class='text-primary'>Price With VAT -  </i>".number_format($price_VAT, 2, ".", ",")." Birr</li>
                                        </div>";
                                        if(is_null($row['collector']))
                                        {
                                            $str .="<li class='row container'>
                                                    <div class='input-group my-3 mx-auto'>
                                                        <select class='form-select form-select-sm' name='Collector_".$na_t."_".$row2['request_id']."' id='".$na_t."_".$row2['request_id']."'>
                                                            <option value=''>--Select Purchase Officer--</option>
                                                                ";
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
                                                                        $str.= "<option value='".$officer."'>$officer (Company : $row[company])</option>";
                                                                    }
                                                                }
                                                $str.=" </select>
                                                        <button value='".$na_t."_".$row2['request_id']."' class='btn btn-sm btn-outline-primary alert-primary' name='Assign_Collector_i'>
                                                            Assign
                                                        </button>
                                                    </div>
                                                    </li>";
                                        }
                                        else
                                            $str .="<li class='list-group-item list-group-item-light'><i class='text-primary'>Collecting Purchase Officer -  </i>".$row['collector']."</li>";

                                    $str .="
                                    </ul>
                                </li>
                                </div>";
                    }
                    $str .= "</ul>";
                    $i++;
            }
    divcreate($str,$name);
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