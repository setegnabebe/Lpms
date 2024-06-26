<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include "../../common/functions.php";
    if(isset($_GET['history_id'])){
        $sql = "SELECT * FROM history_jacket where id = ?";
        $stmt_hjacket = $conn->prepare($sql);
        $stmt_hjacket -> bind_param("i", $_GET['history_id']);
        $stmt_hjacket -> execute();
        $result_hjacket = $stmt_hjacket -> get_result();
        if($result_hjacket -> num_rows>0)
            $row = $result_hjacket -> fetch_assoc();
        $sql_driver = "SELECT driver FROM `vehicle` WHERE `plateno` = ?";
        $stmt_get_driver = $conn_fleet->prepare($sql);
        $stmt_get_driver -> bind_param("s", $row['vehicle']);
        $stmt_get_driver -> execute();
        $result_get_driver = $stmt_get_driver -> get_result();
        if($result_get_driver -> num_rows > 0)
            while($row_driver = $result_get_driver -> fetch_assoc())
            {
                $driver = $row_driver['driver'];
            }
        $plate=$row['vehicle'];
        $serial=$row['serial'];
        $item=$row['item'];
        $qty=$row['quantity'];
        $desc=$row['description'];
        $date=$row['date_purchased'];
        $km=$row['kilometer'];
        $km_diff=$row['km_diff'];
        $time_diff=$row['time_diff'];
        $bs=$item=="battery"?"selected":"";
        $ts=$item=="tyre"?"selected":"";
        $id=$_GET['history_id'];
        echo "
        <input type='hidden' name='id' value='$id'/>
        <div class='row'>
        <div class='col-md-6'>Plate No: $plate</div>
        <div class='col-md-6'>Driver: $driver</div>
        </div>
        <hr>
        <div>Serial Number: $serial</div>
        <hr>
        <div class='row'>
        <div class='mb-3 col-md-6'>
        <label for='Item' class='col-form-label'>Item:</label>
        <select id='Item' name='item' value='".ucfirst($item)."' class='form-control'>
        <option value='Tyre' $ts>Tyre</option>
        <option value='Battery' $bs>Battery</option>
        </select>
        </div>
        <div class='mb-3 col-md-6'>
        <label for='quantity' class='col-form-label'>Quantity:</label>
        <input type='number' class='form-control' name='qty' value='$qty' id='quantity'>
    </div>
        </div>
        <div class='mb-3'>
        <label for='message-text' class='col-form-label'>Description:</label>
        <textarea class='form-control' id='message-text' name='desc'>$desc</textarea>
    </div>
    <div>Date purchased: $date</div>
    <div class='row'>
        <div class='mb-3 col-md-6'>
        <label for='Kilo-meter' class='col-form-label'>Kilometer:</label>
        <input type='text' class='form-control' id='Kilo-meter' name='km' value='$km'>
        </div>
        <div class='mb-3 col-md-6'>
        <label for='kilometer-diff' class='col-form-label'>Kilometer difference:</label>
        <input type='text' class='form-control' id='kilometer-diff' name='kmdiff' value='$km_diff'>
    </div>
        </div>
        <div class='mb-3'>
        <label for='Time-diff' class='col-form-label'>Time difference:</label>
        <input type='text' class='form-control' id='Time-diff' name='timediff' value='$time_diff'>
    </div>";
    }
    else if(isset($_GET['offset_num'])){
        $offset=$_GET['offset_num'];
        $str="";
        $filtered = ($_SESSION['company'] != 'Hagbes HQ.' || !(($_SESSION["department"]=='Procurement' && ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false)) || $_SESSION['additional_role'] == 1));
        $sql_clus2 = "SELECT * FROM `cluster` ".(($filtered)?"WHERE `procurement_company` = ?":"")." ORDER BY id DESC";
        $stmt_clusters_by_proc = $conn->prepare($sql_clus2);
        if($filtered)
        $stmt_clusters_by_proc -> bind_param("s", $_SESSION['company']);
        $stmt_clusters_by_proc -> execute();
        $result_clusters_by_proc = $stmt_clusters_by_proc -> get_result();
        $length = $result_clusters_by_proc->num_rows;
        $sql_clus = "SELECT * FROM `cluster` ".(($filtered)?"WHERE `procurement_company` = ?":"")." ORDER BY id DESC limit $offset, 40";
        $stmt_clusters_by_proc = $conn->prepare($sql_clus);
        if($filtered)
        $stmt_clusters_by_proc -> bind_param("s", $_SESSION['company']);
        $stmt_clusters_by_proc -> execute();
        $result_clusters_by_proc = $stmt_clusters_by_proc -> get_result();
        $data_count=0;
        if($result_clusters_by_proc -> num_rows>0)
        while($r_clus = $result_clusters_by_proc -> fetch_assoc())
        {
            $sql_po = "SELECT * FROM `purchase_order` Where cluster_id = ? GROUP BY cluster_id";
            $stmt_po_by_cluster_group = $conn->prepare($sql_po);
            $stmt_po_by_cluster_group -> bind_param("i", $r_clus['id']);
            $stmt_po_by_cluster_group -> execute();
            $result_po_by_cluster_group = $stmt_po_by_cluster_group -> get_result();
            if($result_po_by_cluster_group->num_rows>0)
            {
                $r_po = $result_po_by_cluster_group->fetch_assoc();
                $performa_id = $r_po['performa_id'];
                $pid = $r_po['purchase_order_id'];
                $cid=$r_po['cluster_id'];
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
        
        // $sql_r = $sql2 = "SELECT *,r.request_id as request_id FROM requests AS r INNER JOIN report AS rep on r.request_id = rep.request_id WHERE r.request_id = '".$id."'";
        $stmt_request_with_report -> bind_param("i", $id);
        $stmt_request_with_report -> execute();
        $result_request_with_report = $stmt_request_with_report -> get_result();
        if($result_request_with_report -> num_rows>0)
        while($row2 = $result_request_with_report -> fetch_assoc()) {
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
                $stmt_description->bind_param("i", $row2['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;  
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
                            <li>Status : ".$r_clus['status']."</li>
                            <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn mb-8 btn-outline-primary btn-sm shadow my-4' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                            <i class='text-white fas fa-clipboard-list fa-fw'></i></button>";
                            $data_count++;
                            $str.= ($r_clus['status']=='Pending' ||$r_clus['status']=='Generated' ||$r_clus['status']=='updated')?"
                            <li id='switch1-".$r_clus['id']."'>
                                <button class='btn btn-sm btn-outline-warning' onClick='edit_performa(this)' type='button' data-bs-toggle='modal' data-bs-target='#view_performa' value='$pid' name='$performa_id'>Edit proforma</button>
                            </li>":"";
                                $str.= (($r_clus['status']=='Generated'||$r_clus['status']=='Pending') && ( (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement') || $_SESSION["additional_role"]==1))?"
                            <li id='switch2-".$r_clus['id']."'>
                                <button class=' btn btn-sm btn-outline-info'   onClick='open_for_edit(this)' title='1'  type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Open for Edit</button>
                            </li>":"";
                                $str.= "
                            <li class='".(($r_clus['status']=='edit')?"":"d-none")."' id='edit-".$r_clus['id']."'>
                                <button class='mt-3 btn btn-sm btn-outline-info mt-3' onClick='compsheet_creater(this,1)'  type='button' data-bs-toggle='modal' id='$name' data-bs-target='#createCompModal' value='$pid' name='$cid'>Edit Comparision Sheet</button>
                                <button class='mt-3 btn btn-sm btn-outline-danger mt-3' onClick='open_for_edit(this)' title='2'  type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Undo</button>
                            </li>";
                    $str.= "</ul>
                        </form>
                    </div>
                </div>
                ";
            }
            echo $str;
        
    }else  if(isset($_GET['keyword'])||isset($_GET['type'])||isset($_GET['status'])||isset($_GET['company'])) {
        function isFound($k){
            $exclude=["request_id","purchase_order_id","id","timestamp","performa_id"];
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
        $filterstatus1=" and C.status='$status' ";
        if($status=='all'){
                $filterstatus1="";
        }
        $filtertype="";
        if($type!="")
        $filtertype=" and  r.request_type='$type' ";
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
        $col_Sql3="show columns from `requests`";
        $stmt_columns3 = $conn->prepare($col_Sql3);
        $stmt_columns3 -> execute();
        $result_columns3 = $stmt_columns3 -> get_result();
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
        if($result_columns3->num_rows>0)
        while($data_res3=$result_columns3->fetch_assoc()){
        $like.="r.".$data_res3['Field']." LIKE '%$keyword%' or ";
        }
        $like.=" 0 )";
        }
        if($status!=""&&$status!="all")
        $filter_status=" C.status='$status' and ";
        else
        $filter_status="";

        $F_cond = (strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner" || $_SESSION["role"]=="Admin" || ( ($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement' && $_SESSION["company"] == "Hagbes HQ."))?" where 1 $filterstatus1  $like  ":"Where  $filter_status c.procurement_company = '". $_SESSION['company']."'  $like ";
        $sql_clus = "SELECT *,C.type as type ,C.company as company ,C.status as `status` FROM `cluster` as C left join purchase_order as p on P.cluster_id = C.id left join requests as r on r.request_id=p.request_id $F_cond $filtercomp  $filtertype group by id ORDER BY id DESC";
        // $sql_clus = "SELECT * FROM `cluster` as C $F_cond $filtercomp  $filtertype group by id ORDER BY id DESC";
        $stmt_clusters = $conn->prepare($sql_clus);
        $stmt_clusters -> execute();
        $result_clusters = $stmt_clusters -> get_result();
        $total_num = $result_clusters -> num_rows;
        $offset=(isset($_GET['offset']))?$_GET['offset']:0;
        $_SESSION['query_cs'] = $sql_clus;
        $stmt_clusters = $conn->prepare($sql_clus);
        $stmt_clusters -> execute();
        $result_clusters = $stmt_clusters -> get_result();
        $total_length = $result_clusters -> num_rows;
        $sql_clus .= " LIMIT $offset, 40 ";
        $stmt_clusters = $conn->prepare($sql_clus);
        $stmt_clusters -> execute();
        $result_clusters = $stmt_clusters -> get_result();
        if($result_clusters -> num_rows > 0)
        while($r_clus = $result_clusters -> fetch_assoc())
        {
            $found ="";
            foreach($r_clus as $key => $data){
                    if(!isFound($key)&&$keyword!=""&&$key!=""&& !is_null($data) && !is_null($keyword) && stripos($data, $keyword) !== false){
                    if($key=="cluster_id")
                    $key="Inspection Number";
            $found .="<li class='text-primary'>".ucfirst(str_replace("_"," ",$key))." : ".(str_ireplace($keyword,"<span class='text-decoration-underline fw-bolder'>$keyword</span>",$data))."</li>";
                    }
                }
            $sql_po = "SELECT * FROM `purchase_order` Where cluster_id = ? GROUP BY cluster_id";
            $stmt_po_by_cluster_group = $conn->prepare($sql_po);
            $stmt_po_by_cluster_group -> bind_param("i", $r_clus['id']);
            $stmt_po_by_cluster_group -> execute();
            $result_po_by_cluster_group = $stmt_po_by_cluster_group -> get_result();
            // if($result_po_by_cluster_group->num_rows==0)
            // {
            //     $sql_po_recollection_failed = "SELECT * FROM `purchase_order_recollection_failed` where `cluster_id` = ?";
            //     $stmt_po_recollection_failed = $conn -> prepare($sql_po_recollection_failed);
            //     $stmt_po_recollection_failed -> bind_param("i", $r_clus["id"]);
            //     $stmt_po_recollection_failed -> execute();
            //     $result_po_by_cluster_group = $stmt_po_recollection_failed -> get_result();
            // }
            if($result_po_by_cluster_group->num_rows>0)
            {
                $r_po = $result_po_by_cluster_group->fetch_assoc();
                $performa_id = $r_po['performa_id'];
                $pid = $r_po['purchase_order_id'];
                $cid=$r_po['cluster_id'];
            }
            $stmt2 = $conn->prepare("SELECT count(DISTINCT `providing_company`) AS companies FROM `price_information` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($co_count);
            $stmt2->fetch();
            $stmt2->close();
            $filtertype = "";
            if($type != "")
            $filtertype = " and  request_type='$type' ";
            if($type=='all'){
                $filtertype = "";
            }
            $company=$_GET['company'];
            $filtercomp = "";
            if($company != "")
            $filtercomp = " and  company='$company' ";
            if($company=='all'){
                $filtercomp = "";
            } 
            $po_sql="SELECT `request_type`, count(*) AS num_req,request_id as id ,company FROM `purchase_order` where `cluster_id` = ? $filtercomp  $filtertype";
            $stmt_pos_filter = $conn->prepare($po_sql);
            $stmt_pos_filter -> bind_param("i", $r_clus['id']);
            $stmt_pos_filter -> execute();
            $result_pos_filter = $stmt_pos_filter -> get_result();
            if($result_pos_filter -> num_rows>0)
            while($po_data = $result_pos_filter -> fetch_assoc()){
            $r_type=$po_data['request_type'];
            $num_req=$po_data['num_req'];
            $id=$po_data['id'];
            $company=$po_data['company'];
            $cond="";
            $stmt2 = $conn->prepare("SELECT item as name,department as dep FROM `requests` where `request_id`='".$id."' $cond");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($name,$dep);
            $stmt2->fetch(); 
            $stmt2->close();
        // $sql_r= $sql2 = "SELECT *,r.request_id as request_id FROM requests AS r INNER JOIN report AS rep on r.request_id =rep.request_id WHERE r.request_id='".$id ."' $cond";
        $sql_request_with_report_custom = "SELECT *,r.request_id as request_id FROM requests AS r INNER JOIN report AS rep on r.request_id =rep.request_id WHERE r.request_id = ? $cond";
        $stmt_request_with_report_custom = $conn -> prepare($sql_request_with_report_custom);
        $stmt_request_with_report_custom -> bind_param("i", $id);
        $stmt_request_with_report_custom -> execute();
        $result_request_with_report_custom = $stmt_request_with_report_custom -> get_result();
        if($result_request_with_report_custom -> num_rows>0)
        while($row2 = $result_request_with_report_custom->fetch_assoc()) {
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
                $stmt_description->bind_param("i", $row2['request_for']);
                $stmt_description->execute();
                $result3 = $stmt_description->get_result();
                $res=($result3->num_rows>0)?true:false;  
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
    if($num_req!=0){
            $printpage = "
                <form method='GET' action='../../requests/print.php' class='float-end'>
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
                        <form method='GET' action='allphp.php'>
                        <ul class='text-start'>
                        <li class='d-none'>$r_type:-:$id:-:$name:-:$company:-:$dep</li>
                            <li>Number of Items Requested : ".$num_req."</li>
                            <li>Number Of Companies : ".$co_count."</li>
                            <li>Total Price : ".number_format($r_clus['price'], 2, ".", ",")."</li>
                            <li>Status : ".$r_clus['status']."</li>
                            $found
                            <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow my-4' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                            <i class='text-white fas fa-clipboard-list fa-fw'></i></button>";
                            $str.= ($r_clus['status']=='Pending' ||$r_clus['status']=='Generated' ||$r_clus['status']=='updated')?"
                            <li id='switch1-".$r_clus['id']."'>
                                <button class='btn btn-sm btn-outline-warning' onClick='edit_performa(this)' type='button' data-bs-toggle='modal' data-bs-target='#view_performa' value='$pid' name='$performa_id'>Edit proforma</button>
                            </li>":"";
                                $str.= (($r_clus['status']=='Generated'||$r_clus['status']=='Pending') && ( (($_SESSION['role'] == "manager" || strpos($_SESSION["a_type"],"manager") !== false) && $_SESSION["department"]=='Procurement') || $_SESSION["additional_role"]==1))?"
                            <li id='switch2-".$r_clus['id']."'>
                                <button class=' btn btn-sm btn-outline-info'   onClick='open_for_edit(this)' title='1'  type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Open for Edit</button>
                            </li>":"";
                                $str.= "
                            <li class='".(($r_clus['status']=='edit')?"":"d-none")."' id='edit-".$r_clus['id']."'>
                                <button class='mt-3 btn btn-sm btn-outline-info mt-3' onClick='compsheet_creater(this,1)'  type='button' data-bs-toggle='modal' id='$name' data-bs-target='#createCompModal' value='$pid' name='$cid'>Edit Comparision Sheet</button>
                                <button class='mt-3 btn btn-sm btn-outline-danger mt-3' onClick='open_for_edit(this)' title='2'  type='button'  id='".$r_clus['id']."' value='$pid' name='$cid' >Undo</button>
                            </li>";
                    $str.= "</ul>
                        </form>
                    </div>
                </div>
                ";
        }
    }
    }
    $viewmore="";
    $diff=$total_length-$offset;
    if($diff>40){
            $viewmore="<button type='button' class='btn btn-primary' name='$offset' value='$total_length' onclick='read_more(this)'>
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
            echo $str.":___:".$viewmore;
    }else{
    $id=$_GET['id'];
    $pid=$_GET['pid'];
    $count = 0;
    $stmt_performa -> bind_param("i", $id);
    $stmt_performa -> execute();
    $result_performa = $stmt_performa -> get_result();
    // $result_performa->num_rows>0;
    $row = $result_performa -> fetch_assoc();
    $allfiles = explode(":_:",$row['files']);
    ?>
    <style>
        .carousel-control-next,
    .carousel-control-prev /*, .carousel-indicators */ {
        filter: invert(100%);
    }
    </style>
    <div class='text-center mx-auto mb-4' style="width: 100px;">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button type='button' class="btn nav-link" id='gallery_toggle' onclick="change_disp(this,'list')">
                    <i class='fas fa-tablet-alt'></i>
                </button>
            </li>
            <li class="nav-item">
                <button type='button' class="btn nav-link" id='list_toggle' onclick="change_disp(this,'gallery')">
                    <i class='fas fa-list'></i>
                </button>
            </li>
        </ul>
    </div>
    <?php
    $indic = "<div class='carousel-indicators'>";
    $counter_indic = 0;
    $dataa = "<div class='carousel-inner'>";
    echo "
    <div id='gallery_view' class='d-none'>
        <div id='Galleryperforma' class='carousel slide carousel-fade' data-bs-ride='carousel'>
            ";
        foreach($allfiles as $file)
        {
            $temp = ($counter_indic==0)?" class='active' aria-current='true' ":"";
            $temp2 = ($counter_indic==0)?" active":"";
            $indic .=
            "<button id='indic_$file' type='button' data-bs-target='#Galleryperforma' data-bs-slide-to='$counter_indic' aria-label='Slide ".($counter_indic+1)."' $temp></button>";
            $dataa .= "
                <div id='divv_$file' class='carousel-item$temp2'>
                    <img class='d-block w-100' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
                </div>";
            $counter_indic++;
        }
        $indic .= "</div>";
        $dataa .= "</div>";
        echo 
        "$indic $dataa<a class='carousel-control-prev' href='#Galleryperforma' role='button' type='button' data-bs-slide='prev'>
            <span class='carousel-control-prev-icon' aria-hidden='true'></span>
        </a>
        <a class='carousel-control-next' href='#Galleryperforma' role='button' data-bs-slide='next'>
            <span class='carousel-control-next-icon' aria-hidden='true'></span>
        </a>
        </div>
        ";
    echo "
    </div>
    <div id='list_view'>
        <ul class= 'list-group list-group-flush text-start'>
        <li class='list-group-item list-group-item-primary mb-4 text-start'>
            <ul class= 'list-group list-group-flush text-start'>";
        foreach($allfiles as $file)
        {
            $count++;
            echo "
            <li class='list-group-item list-group-item-light text-strong' id='list_$file'>
                File $count - <i class='text-primary'>$file </i>
                <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='btn btn-sm btn-outline-primary border-0 float-end' download> <i class='fa fa-download'></i></a>
                <button type='button' title='".$pid."' name='$file' value='$count"."_".$id."' class='btn btn-sm btn-outline-danger border-0 float-end' onclick='remove_performa(this)'><i class='fa fa-trash'></i></button>
            </li>";
        }
        echo "</ul></li></ul>";
        echo "
    </div>";
    echo (isset($_GET['edit']))?"
    <div class='text-center py-1 my-2 bg-light'>
        <button onclick='load_performa(this)' name='".$pid."' type='button' class='btn btn-sm btn-outline-primary m-auto' data-bs-toggle='modal' data-bs-target='#modal_performa'>
            Add Images to Perfoma
        </button>
    </div>
    ":"";
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