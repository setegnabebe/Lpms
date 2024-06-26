<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
        include '../connection/connect.php';
        include "../common/functions.php";
        include '../common/details.php';
        if(isset($_GET['keyword'])||isset($_GET['type'])||isset($_GET['status'])||isset($_GET['company'])) {
        function isFound($k){
                $exclude=["request_id","purchase_order_id","timestamp","cluster_id","id","performa_id"];
                foreach($exclude as $x){
                        if($k==$x)
                        return true;
                }
                return false;
        }
                $str="";
                $keyword =ltrim($_GET['keyword']);
                $type = $_GET['type'];
                $status=$_GET['status'];
                $company=$_GET['company'];
                $filter="";
                $filterstatus1=" and C.status='$status'";
                if($status=='all'){
                        $filterstatus1="";
                }
                $filtertype="";
                if($type!="")
                $filtertype=" and request_type='$type' ";
                if($type=='all'){
                $filtertype="";
                }
                $company=$_GET['company'];
                $filtercomp="";
                if($company!="")
                $filtercomp=" and  C.company='$company' ";
                if($company=='all'){
                $filtercomp="";
                } 
        
                $col_Sql1="show columns from `cluster`";
                $stmt_columns1 = $conn->prepare($col_Sql1);
                $stmt_columns1 -> execute();
                $result_columns1 = $stmt_columns1 -> get_result();
                $col_Sql2="show columns from `purchase_order`";
                $stmt_columns2 = $conn->prepare($col_Sql2);
                $stmt_columns2 -> execute();
                $result_columns2 = $stmt_columns2 -> get_result();
                $like="";
                if($keyword){
                $like=" and  (";

                if($result_columns1->num_rows>0)
                while($data_res1=$result_columns1->fetch_assoc()){
                $like.="C.".$data_res1['Field']." LIKE '%$keyword%' or ";
                }
                if($result_columns2->num_rows>0)
                while($data_res2=$result_columns2->fetch_assoc()){
                $like.="p.".$data_res2['Field']." LIKE '%$keyword%' or ";
                }
                $like.=" 0 )";
                }
                $F_cond = (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || ( ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement' && $_SESSION["company"] == "Hagbes HQ."))?" where 1 $filterstatus1  $like  ":"Where 1 $filterstatus1 and C.company = '". $_SESSION['company']."' $like ";
                $sql_clus = "SELECT *,C.company as company,C.status as `status` FROM `cluster` as C inner join purchase_order as p on P.cluster_id = C.id $F_cond $filtercomp  $filtertype group by id ORDER BY id DESC";
                $stmt_filtered = $conn->prepare($sql_clus);
                $stmt_filtered -> execute();
                $result_filtered = $stmt_filtered -> get_result();
                $total_num = $result_filtered -> num_rows;
                $offset=(isset($_GET['offset']))?$_GET['offset']:0;
                $_SESSION['query_cs'] = $sql_clus;
                $stmt_filtered_first = $conn->prepare($sql_clus);
                $stmt_filtered_first -> execute();
                $result_filtered_first = $stmt_filtered_first -> get_result();
                $total_length = $result_filtered_first -> num_rows;
                $sql_clus .= " LIMIT $offset, 40 ";
                $stmt_filtered_limited = $conn->prepare($sql_clus);
                $stmt_filtered_limited -> execute();
                $result_filtered_limited = $stmt_filtered_limited -> get_result();
                if($result_filtered_limited -> num_rows > 0)
                while($r_clus = $result_filtered_limited -> fetch_assoc())
                {
                        $found ="";
                        foreach($r_clus as $key => $data){
                                if(!isFound($key)&&$keyword!=""&&$key!=""&& !is_null($data) && !is_null($keyword) && stripos($data, $keyword) !== false)
                        $found .="<li class='text-primary'>".ucfirst(str_replace("_"," ",$key))." : ".(str_ireplace($keyword,"<span class='text-decoration-underline fw-bolder'>$keyword</span>",$data))."</li>";
                        }
                $stmt2 = $conn->prepare("SELECT count(DISTINCT `providing_company`) AS companies FROM `price_information` where `cluster_id`='".$r_clus['id']."'");
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($co_count);
                $stmt2->fetch();
                $stmt2->close();
                $po_sql="SELECT `request_type`, count(*) AS num_req, company FROM `purchase_order` where `cluster_id` = ?";
                $stmt_pos_types = $conn->prepare($po_sql);
                $stmt_pos_types -> bind_param("i", $r_clus['id']);
                $stmt_pos_types -> execute();
                $result_pos_types = $stmt_pos_types -> get_result();
                if($result_pos_types -> num_rows > 0)
                while($po_data = $result_pos_types -> fetch_assoc()){
                        
                $r_type=$po_data['request_type'];
                $num_req=$po_data['num_req'];
                $cond="";
                if($num_req!=0){
                        $avail = true;
                        $btn_close = "";
                        $forbiden_stats = ['canceled','Rejected','Recollection Failed','Changed','closed','Rejected','All Payment Processed','Payment Processed','Collected-not-comfirmed','Collected','In-stock','All Complete'];
                        foreach($forbiden_stats as $s)
                                if(strpos($r_clus['status'],$s)!==false || $r_clus['status'] == $s) $avail = false;
                        if(((($_SESSION['company'] == $r_clus['procurement_company'] || $_SESSION['company'] == 'Hagbes HQ.') && (($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1)) || $_SESSION["role"]=="Admin") && $avail)
                        {
                                $btn_close = "
                                <form method='GET' action='allphp.php' class='float-end mt-3'>
                                <button class='btn btn-outline-danger btn-sm' name='close_req_clus' value='$r_clus[id]' type='button' data-bs-toggle='modal' data-bs-target='#give_reason' onclick='give_reason(this,\"../requests\",\"remove\",\"Red\")'>Close Request</button>
                                </form>";
                        }
                $printpage = "
                        <form method='GET' action='../requests/print.php' class='float-end'>
                        <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['id'].":|:cluster'>
                                <i class='text-dark fas fa-print'></i>
                        </button>
                        </form>";
                $str.= "
                        <div class='col-md-6 col-lg-3 my-4'>
                        <div class='box'>
                                <h3 class='text-capitalize'>".str_ireplace($keyword,"<span class='bg-warning'>$keyword</span>",$r_clus['type'])."
                                $printpage
                                <span class='small text-secondary d-block mt-2'>$r_type</span></h3>
                                <ul>
                                <li>Number of Items Requested : ".$num_req."</li>
                                <li>Number Of Companies : ".$co_count."</li>
                                <li>Total Price : ".number_format($r_clus['price'], 2, ".", ",")."</li>
                                <li>Status : ".$r_clus['status']."</li>
                                $found
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
        }
        }
        $viewmore="";
        $diff=$total_length-$offset;
        if($diff>40){
                $viewmore="<button type='button' class='btn btn-primary' name='0' value='$total_length' onclick='read_more(this)'>
                View More
        </button>";
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
        echo $str.":___:".$viewmore;
        }  
        }
        else{
                if(isset($_GET['serial']))
                {
                        $serial = explode(",",$_GET['serial']);
                }
                else $serial = [];
                $first = true;
                $date_last_checked = "";
                $sql = "SELECT * FROM `requests` WHERE `request_for` = ? AND request_type = 'Tyre and Battery' AND `recieved`= 'yes'";
                $stmt_tyre_item = $conn->prepare($sql);
                $stmt_tyre_item -> bind_param("s", $_GET['plate']);
                if(isset($_GET['item']))
                {
                        $sql .= " AND `item` = ?";
                        $stmt_tyre_item = $conn->prepare($sql);
                        $stmt_tyre_item -> bind_param("ss", $_GET['plate'], $_GET['item']);
                }
                $stmt_tyre_item -> execute();
                $result_tyre_item = $stmt_tyre_item -> get_result();
                if($result_tyre_item -> num_rows>0)
                while($row = $result_tyre_item -> fetch_assoc())
                {
                        if(sizeof($serial)>0)
                        {
                                foreach($serial as $s)
                                {
                                        $sql2 = "SELECT * FROM `purchase history` WHERE `request_id` = ? AND `type`= 'Tyre and Battery' AND `serial` = ?";
                                        $stmt_get_history = $conn->prepare($sql2);
                                        $stmt_get_history -> bind_param("is", $row['request_id'], $s);
                                        $stmt_get_history -> execute();
                                        $result_get_history = $stmt_get_history -> get_result();
                                        if($result_get_history -> num_rows>0)
                                        while($row2 = $result_get_history -> fetch_assoc())
                                        {
                                                if($date_last_checked == "")
                                                {
                                                        $maxdate[$s] = $row2['date'];
                                                        $maxkm[$s] = $row2['kilometer'];
                                                        $date_last_checked = $row2['date'];
                                                }
                                                else if(strtotime($date_last_checked) <= strtotime($row2['date']))
                                                {
                                                        $maxdate[$s] = $row2['date'];
                                                        $maxkm[$s] = $row2['kilometer'];
                                                        $date_last_checked = $row2['date'];
                                                }
                                        }
                                        $date_last_checked = "";
                                }
                        }
                        else
                        {
                                $sql2 = "SELECT * FROM `purchase history` WHERE `request_id` = ? AND `type`= 'Tyre and Battery'";
                                $stmt_all_history = $conn->prepare($sql2);
                                $stmt_all_history -> bind_param("i", $row['request_id']);
                                $stmt_all_history -> execute();
                                $result_all_history = $stmt_all_history -> get_result();
                                if($result_all_history -> num_rows > 0)
                                while($row2 = $result_all_history -> fetch_assoc())
                                {
                                        if($date_last_checked == "")
                                        {
                                                $maxdate = $row2['date'];
                                                $maxkm = $row2['kilometer'];
                                                $date_last_checked = $row2['date'];
                                        }
                                        else if(strtotime($date_last_checked) <= strtotime($row2['date']))
                                        {
                                                $maxdate = $row2['date'];
                                                $maxkm = $row2['kilometer'];
                                                $date_last_checked = $row2['date'];
                                        }
                                }
                        }
                }


                $sql = "SELECT driver , company ,gps FROM `vehicle` WHERE `plateno` =? ";

                $stmt2 = $conn_fleet->prepare($sql);
                $stmt2->bind_param("s", $_GET['plate']);
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($driver, $company, $gps);
                $stmt2->fetch();
                $stmt2->close();

                $sql = "SELECT kmatend AS current_km FROM `actualreport` where `platenumber` = ? Order by registerdate Desc Limit 1";

                $stmt2 = $conn_fleet->prepare($sql);
                $stmt2->bind_param("s", $_GET['plate']);
                $stmt2->execute();
                $stmt2->store_result();
                $stmt2->bind_result($curr_km);
                $stmt2->fetch();
                $stmt2->close();
                if(sizeof($serial)>0)
                {
                        foreach($serial as $s)
                        {
                                $maxdate[$s] =(isset($maxdate[$s]))?date("d-M-Y", strtotime($maxdate[$s])):"None";
                                $maxkm[$s] =(isset($maxkm[$s]))?$maxkm[$s]:"None";
                        }
                }
                else
                {
                        $maxdate =(isset($maxdate))?date("d-M-Y", strtotime($maxdate)):"None";
                        $maxkm =(isset($maxkm))?$maxkm:"None";
                }
                $curr_km =(isset($curr_km))?$curr_km:"None";
                echo "
                <div class='col-lg-4 col-md-12'>
                <div class='card-header text-center'>
                        <i class='fas fa-table me-1'></i>
                        <h3>Information</h3>
                </div>
                <div class='row m-auto card-body'>
                <i class='text-start text-warning alert-primary'>Plate Number</i><span class='text-end alert-primary'>".$_GET['plate']."</span><hr>
                <i class='text-start text-warning alert-primary'>Driver</i><span class='text-end alert-primary'>". $driver ."</span><hr>
                <i class='text-start text-warning alert-primary'>Company</i><span class='text-end alert-primary'>". $company ."</span><hr>";
                if(sizeof($serial)>0)
                {
                        $first_serial = "";
                        echo "
                        <div class='text-center mx-auto mb-4' style='width: 200px;'>
                        <ul class='nav nav-tabs'>";
                        foreach($serial as $s)
                        {
                                if($first)
                                {
                                        $first_serial = $s;
                                        $active = " active";
                                        $first = false;
                                }
                                else $active = "";
                                echo 
                                "
                                <li class='nav-item'>
                                <button type='button' title='".$maxdate[$s]."_".$maxkm[$s]."' class='all_serials btn nav-link$active' id='".$s."_toggle' onclick='view_serial(this)'>
                                        $s
                                </button>
                                </li>
                                ";
                        }
                        echo "
                        </ul>
                        </div>
                        <i class='text-start text-warning alert-primary'> Date of Previous Purchase</i><span class='text-end alert-primary' id='show_date'>". $maxdate[$first_serial] ." </span><hr>
                        <i class='text-start text-warning alert-primary'> KM at Previous Purchase</i><span class='text-end alert-primary' id='show_km'>". $maxkm[$first_serial] ." </span><hr>
                        ";
                }
                else
                echo "
                <i class='text-start text-warning alert-primary'> Date of Previous Purchase</i><span class='text-end alert-primary' id='show_date'>". $maxdate ." </span><hr>
                <i class='text-start text-warning alert-primary'> KM at Previous Purchase</i><span class='text-end alert-primary' id='show_km'>". $maxkm ." </span><hr>
                ";
                echo "<i class='text-start text-warning alert-primary'> Current Kilo meter</i><span class='text-end alert-primary' id='c_km'>". $curr_km ." </span><hr>
                </div>
                </div>
                ";
                $count = 0;
                $sql = "SELECT * FROM `requests` where `request_for` = ? AND `status`!='Rejected By Manager' AND `status`!='Rejected By Dep.Manager' AND `status`!='Rejected'";
                $str = "";
                $type="Tyre and Battery";
                $na_t=str_replace(" ","",$type);
                $tbl_data = "";
                $tbl_head = "#,Requested By,Item,Plate No,Company,Department,Date Requested,Date Needed By,Status";
                $sql .= (strpos($_SESSION["a_type"],"manager") !== false && !isset($_SESSION["managing_department"]) && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND  department = '".$_SESSION['department']."'":"";
                $temp_cond = "";
                if(isset($_SESSION["managing_department"]))
                {
                        if(!in_array("All Departments",$_SESSION["managing_department"]))
                        {
                                foreach($_SESSION["managing_department"] as $depp)
                                $temp_cond .=($temp_cond == "")?"department = '$depp'":" OR department = '$depp'";
                        }
                }
                // $sql .= (strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND  department = '".$_SESSION['department']."'":"";
                if($temp_cond != "") $temp_cond = "($temp_cond)";
                $temp_cond .= (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":(($temp_cond == "")?"`company` = '". $_SESSION['company']."'":" AND `company` = '". $_SESSION['company']."'");
                $temp_cond.=($_SESSION['a_type']=='user' && $_SESSION["department"]!='Procurement' && $_SESSION["department"]!='Property')?" AND `customer`='".$_SESSION["username"]."'":"";
                $temp_cond.=($_SESSION['role']=='Director' || $_SESSION['role']=='Owner')?" OR `customer`='".$_SESSION["username"]."'":"";
                $sql .= " AND ( $temp_cond )";
                $sql .= " ORDER BY date_needed_by ASC, request_id DESC";
                // if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner') $sql .="";
                // else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') $sql .=" AND company = '". $_SESSION['company']."'";
                // else if($_SESSION["a_type"] == 'manager' && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') $sql .=" AND  department = '".$_SESSION['department']."' AND company = '". $_SESSION['company']."'";
                // else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') $sql .=" AND `customer`='".$_SESSION["username"]."' AND company = '". $_SESSION['company']."'";
                // $sql.=" ORDER BY date_needed_by ASC, request_id DESC";

                // $sql.=($_SESSION['a_type']=='user')?" AND `customer`='".$_SESSION["username"]."' ORDER BY date_needed_by ASC, request_id DESC":" ORDER BY date_needed_by ASC, request_id DESC";
                $stmt_get_data = $conn->prepare($sql);
                $stmt_get_data -> bind_param("s", $_GET['plate']);
                $stmt_get_data -> execute();
                $result_get_data = $stmt_get_data -> get_result();
                if($result_get_data -> num_rows>0)
                {
                        echo "<div class='col-lg-8 col-md-12'>";
                                include 'tbl-div.php';
                        echo "<div class='pricing'>
                                <form method='GET' action='allphp.php'>
                                <div class='section-title text-center py-2 alert-primary'>
                                        <h6 class='text-white'>Previous Requests</h4> 
                                </div>
                        ";
                        while($row = $result_get_data -> fetch_assoc())
                        {
                                $dlt_btn2 = "";
                                $type=$row['request_type'];
                                $printpage = "
                                <form method='GET' action='print.php' class='float-end'>
                                    <button type='submit' class='btn btn-outline-secondary border-0 ' name='print' value='".$row['request_id'].":|:$type'>
                                    <i class='text-dark fas fa-print'></i>
                                    </button>
                                </form>";
                                if(strtotime($row['date_needed_by'])<strtotime(date("Y-m-d")) && !($row['status']=="Rejected By Manager") || ($row['status']=="Rejected")) $over = 9;
                                        else $over = 12;
                                if($row['status']=='waiting')
                                {
                                        $dlt_btn = "<button type='button' class='col-2 btn btn-outline-danger btn-sm border-0' name='Delete_TyreandBattery_".$row['request_id']."' onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";        
                                        $dlt_btn2 = "<button type='button' class='col-2 btn btn-outline-danger btn-sm border-0 float-end' name='Delete_TyreandBattery_".$row['request_id']."' onclick='delete_item(this)'><i class='far fa-trash-alt'></i></button>";        
                                        if($over == 12)
                                                $over = 10;
                                }
                                include 'tbl_code.php';
                                $count++;
                                $p_num = intval(($count%2==0)?$count/2:($count/2)+1);
                                $dis = ($count<3)?'':' d-none';
                                if(($count-1)%2==0)
                                        $str .=  "<div class='row$dis' id='item $p_num'>";
                                $str .=  "
                                <div class='col-md-6 col-lg-6 col-xl-6 my-4'>
                                        <div class='box shadow'>
                                        <h3 class='text-capitalize row'>";
                                        $str .=  ($over==9)?"<span class='text-danger col-1' style='font-size:20px;'><i class='fas fa-exclamation-circle'></i></span>":"";
                                        $str .=  "<span class='col-$over'><button type='button' title='".$row['description']."' value='".$row['recieved']."' name='specsfor_TyreandBattery_".$row['request_id']."' class='text-capitalize btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                        ".$row['item']."</button></span>";
                                        $str .=  ($row['status']=='waiting')?$dlt_btn:"";
                                        $uname =str_replace("."," ",$row['customer']);
                                        $str .=  "</h3>
                                        <ul>
                                        </li>";
                                                $str .=  ($_SESSION['a_type']=='user')?"":"<li class='text-start'><span class='fw-bold'>Requested By : </span>$uname</li>";
                                                $str .=  "
                                                <li class='text-start'><span class='fw-bold'> Quantity : </span>".$row['requested_quantity']."</li>
                                        <li class='text-start'><span class='fw-bold'> Date Requested : </span>".date("d-M-Y", strtotime($row['date_requested']))."</li>";
                                        $str .=  ($over==9)?"<li class='text-danger text-start'>":"<li class='text-start'>";
                                        $str .=  "<span class='fw-bold'> Date Needed By : </span>".date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                                        <li class='text-start'><span class='fw-bold'> Status : </span>".$row['status']."</li>
                                        </div>
                                </div>";
                                if($count%2==0)
                                        $str .=  "</div>";
                        }
                        if($count%2!=0) $str .=  "</div>";

                        $page_c = intval(($count%2==0)?$count/2:($count/2)+1);
                        $str .=  "
                        <ul class='pagination justify-content-end' id='$page_c'>
                                <li class='page-item disabled'><button type='button' class='page-link' onclick='pages(this)'>Previous</button></li>";
                                for($i=1;$i<=$page_c;$i++)
                                {
                                        $act = ($i==1)?' active':'';
                                        $str .=  "<li class='page-item$act'><button type='button' class='page-link' onclick='pages(this)'>$i</button></li>";
                                        $dis = ($count<=2)?" disabled":"";
                                }
                                $str .=  "<li class='page-item$dis'><button type='button' class='page-link' onclick='pages(this)'>Next</button></li>
                        </ul>
                        </div>
                        </form>
                        </div>
                        ";
                        // <script>pages('yess')</script>
                }
                else {
                        $str .=  "
                        <div class='col-lg-8 col-md-12'>
                        <div class='card-header text-center'>
                        <h2>No requests</h2>
                        </div>
                        </div>";
                }
                $div_type = divcreate_requests_page($str);
                $tbl_format = table_create($tbl_head,$tbl_data,true);
                // $dis = ($page_num == 1)?" disabled":"";
                // echo "<form method='GET' action='$_SERVER[PHP_SELF]'>
                //     <ul class='pagination justify-content-end' id='$amount'>
                //     <div class='dataTable-dropdown me-3'>
                //         <select class='dataTable-selector form-select form-select-sm' name='per_page' onchange='document.getElementById(\"active_page\").click();'>";
                //         for($i=5;$i<=25;$i=$i+5)
                //         {
                //             $act = ($i==$per_page)?" selected=''":"";
                //             echo "<option value='$i'$act>$i</option>";
                //         }
                //         echo "</select>
                //     </div>
                //         <li class='page-item$dis'><button type='submit' class='page-link me-2' name='page_num' value='".($page_num-1)."'>Previous</button></li>";
                //         for($i=1;$i<=$amount;$i++)
                //         {
                //             $act = ($i==$page_num)?" active":"";
                //             $act_id = ($i==1)?" id = 'active_page'":"";
                //             echo "<li class='page-item$act'><button type='submit'$act_id class='page-link' name='page_num' value='".($i)."'>$i</button></li>";
                //         }
                //         $dis = ($amount==$page_num)?" disabled":"";
                //         echo "<li class='page-item$dis'><button type='submit' class='page-link' name='page_num' value='".($page_num+1)."'>Next</button></li>
                //     </ul>";
                
                // if(isset($_GET['serial']))
                //         echo "<input name='serial' value='$_GET[serial]' class='d-none'>";
                // if(isset($_GET['item']))
                //         echo "<input name='item' value='$_GET[item]' class='d-none'>";
                // if(isset($_GET['plate']))
                //         echo "<input name='plate' value='$_GET[plate]' class='d-none'>";
                // echo "</form>";
                echo "<div id='tbl_view' style = 'overflow:scroll'>$tbl_format</div>";
                echo "<div class='d-none' id='div_view'>$div_type</div>";
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