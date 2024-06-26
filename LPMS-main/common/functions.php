<?php 
       function getDuration($date){
        $timeString='';
        $time=date("H:i",strtotime($date));
        $now = date("Y-m-d H:i");
        if(date("d",strtotime($date))==date("d",strtotime($now)))
            $timeString='Today, '.$time; // today 
          else if(abs((date("d",strtotime($date))-date("d",strtotime($now))))==1)
                $timeString='yesterday, '.$time;
            else 
                $timeString=date("Y-m-d , H:i",strtotime($date));;
            return $timeString;
    }
    function getNamedStatus($status, $row)
    {
        return ($status == 'Approved By Dep.Manager' && !is_null($row['manager'])) ? 'Approved By '.str_replace("."," ",$row['manager']).'' :
        (($status == 'Approved By GM' && !is_null($row['GM'])) ? 'Approved By '.str_replace("."," ",$row['GM']).'' :
        (($status == 'Approved By Director' && !is_null($row['director'])) ? 'Approved By '.str_replace("."," ",$row['director']).'' :
        (($status == 'Approved By Owner' && !is_null($row['owner'])) ? 'Approved By '.str_replace("."," ",$row['owner']).'' :
        (($status == 'Approved By Property' && !is_null($row['property'])) ? 'Approved By '.str_replace("."," ",$row['property']).'' :
        $status))));
    }
    function notify_mention($user)
    {
        // create notification
    }
    function addMark($num){
        if($num==0)
       return '<span style="font-size:12;margin-top:4px;margin-bottom:0px"><i class="bi bi-check2 text-info"></i></span>';
       else
       return '<span style="font-size:12;margin-top:4px;margin-bottom:0px"><i class="bi bi-check2-all text-info"></i></span>';
    }
    // $sql = "UPDATE `requests` SET `status`=?, `next_step`=? WHERE `request_id`=?";
    // $stmt_statusRequest = $conn->prepare($sql);
    // $stmt_statusRequest -> bind_param("ssi",$status ,$nxt_step ,$r['request_id']);
    // if (!($stmt_statusRequest -> execute())) echo "Error: " . $sql2 . "<br>" . $conn->error. "<br>" ;
function updaterequest($conn,$conn_fleet,$reqeust_id,$level,$back="",$place=""){
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $no_property = false;
    $sql = "SELECT * FROM `requests` WHERE `request_id` = ?";
    $stmt_request = $conn->prepare($sql);
    $stmt_request -> bind_param("i", $reqeust_id);
    $stmt_request -> execute();
    $result = $stmt_request -> get_result();
    if ($result -> num_rows > 0)
    {
        $row = $result -> fetch_assoc();
    }
    else return "Not Found";

    $sql = "SELECT * FROM `comp` where `Name` = ?";
    $stmt_company = $conn_fleet -> prepare($sql);
    $stmt_company -> bind_param("s", $row["company"]);
    $stmt_company -> execute();
    $result = $stmt_company -> get_result();
    if($result->num_rows > 0)
    {
        while($r = $result->fetch_assoc()) 
        {
            $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
        }
    }
    $has_gm = in_array($row['department'],$GMs) || in_array("All",$GMs);
    $spec_needed = ($row['spec_dep'] == 'IT' && $row['request_type'] != 'Spare and Lubricant');
    $type = $row['request_type'];
    if (($type == 'Spare and Lubricant' && ($row['mode'] == 'External' || $row['type'] == 'Lubricant')) || ($type == 'Tyre and Battery' && ($row['mode'] == 'External')) || $type == 'Miscellaneous' || $type == 'Consumer Goods') {
        $no_property = true;
    }
    $sql = "SELECT * FROM stock WHERE `id` = ?";
    $stmt_stock = $conn -> prepare($sql);
    $stmt_stock -> bind_param("i", $row["stock_info"]);
    $stmt_stock -> execute();
    $res = $stmt_stock -> get_result();
    if ($res->num_rows > 0) 
        while ($r = $res->fetch_assoc()) {
            $outofstock = $r['for_purchase'];
            if($r['in-stock'] > 0) $no_property = false;
        }
    $ph = ($level == 'one')?1:(($level == 'two')?2:(($level == 'three')?3:(($level == 'four')?4:1)));
    $total = getTotalPhase($conn,$conn_fleet,$reqeust_id,$ph);
    $sql = "SELECT phase_".$level." FROM `requests` where request_id = ?";
    $stmt_request_phase = $conn -> prepare($sql);
    $stmt_request_phase -> bind_param("i", $reqeust_id);
    $stmt_request_phase -> execute();
    $result = $stmt_request_phase -> get_result();
    if($result->num_rows>0)
    while($row = $result->fetch_assoc())
    {
        $phase_val = ($back == "")?$row['phase_'.$level]+1:$row['phase_'.$level]-1;
        if($ph == 1)
        {
            $phase_val = ($place == 'Dep')?2:
            (($place == 'GM')?3:
            (($place == 'Agreement')?3 + (($has_gm)?1:0):
            (($place == 'IT')?3 + (($has_gm)?1:0):
            (($place == 'Store')?3+(($has_gm)?1:0)+(($spec_needed)?1:0):
            (($place == 'Property')?4+(($has_gm)?1:0)+(($spec_needed)?1:0):
            (($place == 'Director')?4+(($has_gm)?1:0)+(($spec_needed)?1:0)+((!$no_property)?1:0):
            (($place == 'Owner')?$total:
            $phase_val)))))));
        }
        else if($ph == 2)
        {
            $phase_val = ($place == 'Assign')?1:
            (($place == 'Accept')?2:
            (($place == 'Complete')?3:
            (($place == 'Open_proforma')?4:
            (($place == 'Comparision_create')?5:
            (($place == 'Committee_sent')?6:
            $phase_val)))));
        }
        else if($ph == 3)
        {
            $phase_val = ($place == 'Approved')?1:
            (($place == 'sent_to_finance')?2:
            (($place == 'Review')?3:
            (($place == 'Finance Approved')?4:
            (($place == 'Cheque_prepare' || $place == 'Petty Cash')?5:
            (($place == 'give_petty_Cash' || $place == 'cheque_signed')?6:
            $phase_val)))));
        }
        else if($ph == 4)
        {
            $phase_val = 
            ($place == 'Collection')?1:
            (($place == 'Dep_approve')?2:
            (($place == 'Store_confirm')?3:
            (($place == 'Handover')?4:
            (($place == 'Settled')?5:
            $phase_val))));
        }
        $sql = "UPDATE `requests` SET `phase_".$level."` = ? WHERE `request_id` = ?";
        $stmt_update_phase = $conn -> prepare($sql);
        $stmt_update_phase -> bind_param("ii", $phase_val, $reqeust_id);
        $stmt_update_phase -> execute();
    }
    // return "$phase_val $place";
}
function getTotalPhase($conn,$conn_fleet,$reqeust_id,$level){
    
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $no_property = false;
    $sql = "SELECT * FROM `requests` WHERE `request_id` = ?";
    $stmt_request = $conn->prepare($sql);
    $stmt_request -> bind_param("i", $reqeust_id);
    $stmt_request -> execute();
    $result = $stmt_request -> get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
    else return "Not Found";

    $sql = "SELECT * FROM `comp` where `Name` = ?";
    $stmt_company = $conn_fleet -> prepare($sql);
    $stmt_company -> bind_param("s", $row["company"]);
    $stmt_company -> execute();
    $result = $stmt_company -> get_result();
    if($result->num_rows > 0)
    {
        while($r = $result->fetch_assoc()) 
        {
            $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
        }
    }
    $has_gm = in_array($row['department'],$GMs) || in_array("All",$GMs);
    $spec_needed = ($row['spec_dep'] == 'IT' && $row['request_type'] != 'Spare and Lubricant');
    $type = $row['request_type'];
    if (($type == 'Spare and Lubricant' && ($row['mode'] == 'External' || $row['type'] == 'Lubricant')) || ($type == 'Tyre and Battery' && ($row['mode'] == 'External')) || $type == 'Miscellaneous' || $type == 'Consumer Goods' || $type == 'agreement') {
        $no_property = true;
    }
    $sql = "SELECT * FROM stock WHERE `id` = ?";
    $stmt_stock = $conn -> prepare($sql);
    $stmt_stock -> bind_param("i", $row["stock_info"]);
    $stmt_stock -> execute();
    $res = $stmt_stock -> get_result();
    if ($res->num_rows > 0) 
        while ($r = $res->fetch_assoc()) {
            $outofstock = $r['for_purchase'];
            if($r['in-stock'] > 0) $no_property = false;
        }
    else
    {
        $outofstock = $row["requested_quantity"];
    }
    $initials = 0;
    if($level == 1)
    { 
        $initials = ($row['request_type'] == 'agreement')?2:3;
        if($has_gm)$initials++;
        if($spec_needed)$initials++;
        if(!$no_property)$initials++;
        if(isset($outofstock) && $outofstock == 0)$initials+=2;
        if($type == 'Fixed Assets' && ($outofstock != 0 || !isset($outofstock)))$initials++;
        if($type == 'agreement' && ($outofstock != 0 || !isset($outofstock)))$initials++;
        if($row["company"] == 'Hagbes HQ.' && $row["department"] != 'Owner' && ($outofstock != 0 || !isset($outofstock)))$initials++;
    }
    else if($level == 2)$initials = 6;
    else if($level == 3)$initials = 6;
    else if($level == 4)$initials = 5;
    return $initials;
}
function Gm_query($conn_fleet)
{
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $conditions = $conditions_not = $conditions_All = $conditions_neg = '';
    $sql_comp = "SELECT * FROM `comp`";
    $stmt_all_company = $conn_fleet -> prepare($sql_comp);
    $stmt_all_company -> execute();
    $result_comp = $stmt_all_company -> get_result();
    if($result_comp->num_rows>0)
        while($row_comp = $result_comp->fetch_assoc())
        {
            if(is_null($row_comp['With GM']) || $row_comp['With GM'] == "")
            {
                $conditions_All .= ($conditions_All == "")?"`company` = '$row_comp[Name]'":" OR `company` = '$row_comp[Name]'";
            }
            else
            {
                if(strpos($row_comp['With GM'],"All") === false)
                {
                    $deps_with_gm = explode(",", $row_comp["With GM"]);
                    $withGM = "";
                    foreach($deps_with_gm as $dep)
                    {
                        $withGM .= ($withGM == "")?"'".$dep."'":",'".$dep."'"; // trim($dep)
                    }
                    $conditions_neg .= ($conditions_neg == "")?"`company` = '$row_comp[Name]'":" OR `company` = '$row_comp[Name]'";
                    $deps = (strpos($row_comp['With GM'],"All") === false)?" and (`department` in (".$withGM."))":"";
                    $conditions .= ($conditions == "")?"(`company` = '$row_comp[Name]' $deps)":"OR (`company` = '$row_comp[Name]' $deps)";
                    $conditions_not .= ($conditions_not == "")?"(`company` = '$row_comp[Name]' and (`department` NOT IN (".$withGM.")))":" OR (`company` = '$row_comp[Name]' and (`department` NOT IN (".$row_comp['With GM'].")))";
                }
            }
        }
        $conditions_neg = ($conditions_neg != "" && $conditions_All != "")?"$conditions_All OR $conditions_neg":(($conditions_neg != "")?$conditions_neg:$conditions_All);
        $conditions = ($conditions_neg != "" && $conditions != "")?"!($conditions_neg) OR $conditions":(($conditions_neg != "")?"!($conditions_neg)":$conditions);
        $conditions = "(($conditions) AND `status` = 'Approved By GM')";
        $conditions_2 = $conditions_not.(($conditions_All != "" && $conditions_not != "")?" OR ":"").(($conditions_All != "")?"(".$conditions_All.")":"");
        $conditions_2 = ($conditions_2 != "")?"(($conditions_2) AND `status` = 'Approved By Dep.Manager')":"";
        $conditions .= (($conditions_2 != "" && $conditions != "")?" OR ":"").$conditions_2;
        return "(".$conditions.")";
}
function number_converter($e)
{
    $ones = array(
        "One"=>1,
        "Two"=>2,
        "Three"=>3,
        "Four"=>4,
        "Five"=>5,
        "Six"=>6,
        "Seven"=>7,
        "Eight"=>8,
        "Nine"=>9
    );
    $teens = array(
        "Eleven"=>11,
        "Twelve"=>12,
        "Thirteen"=>13,
        "Fourteen"=>14,
        "Fifteen"=>15,
        "Sixteen"=>16,
        "Seventeen"=>17,
        "Eighteen"=>18,
        "Nineteen"=>19
    );
    $tens = array(
        "Ten"=>10,
        "Twenty"=>20,
        "Thirty"=>30,
        "Fourty"=>40,
        "Fifty"=>50,
        "Sixty"=>60,
        "Seventy"=>70,
        "Eighty"=>80,
        "Ninety"=>90
    );
    $places = array(
        "Hundred"=>0,
        "Thousand"=>1,
        "Million"=>2,
        "Billion"=>3,
        "Trillion"=>4,
        "Quadrillion"=>5,
        "Quintrillion"=>6,
        "Sextillion"=>7
    );
    $number = number_format($e, 2, ".", ",");
    $commas = sizeof(explode(",",$number))-1;
    $whole_nums = explode(",",explode(".",$number)[0]);
    $decimal = explode(".",$number)[1];
    $string = "";
    for($ii = 0; $ii < sizeof($whole_nums);$ii++)
    {
        $hundredth = intval($whole_nums[$ii]/100)%10;
        if($hundredth != 0)
        {
            $string.= ($string == '')?array_search($hundredth,$ones)." ".array_search(0,$places):" ".array_search($hundredth,$ones)." ".array_search(0,$places);
        }
        if(array_search($whole_nums[$ii]%100,$teens) !== false)
        {
            $string.= ($string == '')?array_search($whole_nums[$ii]%100,$teens):" ".array_search($whole_nums[$ii]%100,$teens);
        }
        else if(array_search($whole_nums[$ii]%100,$tens) !== false)
        {
            $string.= ($string == '')?array_search($whole_nums[$ii]%100,$tens):" ".array_search($whole_nums[$ii]%100,$tens);
        }
        else
        {
            $tenth = (intval($whole_nums[$ii]/10)%10)*10;
            $string.= ($string == '')?array_search($tenth,$tens):" ".array_search($tenth,$tens);
            $one = $whole_nums[$ii]%10;
            $string.= ($string == '')?array_search($one,$ones):" ".array_search($one,$ones);
        }
        if($commas != 0)
        $string.= ($string == '')?array_search($commas,$places):" ".array_search($commas,$places);

        $commas--;
    }
    if($decimal != 0)
    {
        $string.=" And ";
        if(array_search($decimal%100,$teens) !== false)
        {
            $string.= ($string == '')?array_search($decimal%100,$teens):" ".array_search($decimal%100,$teens);
        }
        else if(array_search($decimal%100,$tens) !== false)
        {
            $string.= ($string == '')?array_search($decimal%100,$tens):" ".array_search($decimal%100,$tens);
        }
        else
        {
            $tenth = (intval($decimal/10)%10)*10;
            $string.= ($string == '')?array_search($tenth,$tens):" ".array_search($tenth,$tens);
            $one = $decimal%10;
            $string.= ($string == '')?array_search($one,$ones):" ".array_search($one,$ones);
        }   
        $string.=" Cents";
    }
    return $string;
}
if(isset($_GET['text_of_num']))
{
    echo number_converter($_GET['text_of_num']);
}
function na_t_to_type($conn,$na_t)
{
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $sql_category = "SELECT * from catagory";
    $stmt_categories = $conn -> prepare($sql_category);
    $stmt_categories -> execute();
    $ress = $stmt_categories -> get_result();
    if($ress -> num_rows>0)
        while($re = $ress -> fetch_assoc())
        {
            $type = $re["catagory"];
            $na_t_2=str_replace(" ","",$type);
            if($na_t == $na_t_2)
                return $type;
        }
        return "not Found";
}
 
function find_agg($conn,$str)
{
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $count = 0;
    $sql = "SELECT * FROM `requests` WHERE `request_for` = ? AND `status` NOT LIKE 'Reject%' AND `status` != `canceled`";
    $stmt_request_by_for = $conn -> prepare($sql);
    $stmt_request_by_for -> bind_param("s", $str);
    $stmt_request_by_for -> execute();
    $result = $stmt_request_by_for -> get_result();
    if($result->num_rows>0)
        while($row = $result->fetch_assoc())
        {
                $count += ($row['recieved'] == 'not')?$row['requested_quantity']:((!is_null($row['purchased_amount']))?$row['purchased_amount']:$row['requested_quantity']);
        }
        return $count;
}
function badge_count_requests($conn,$conn_fleet,$data,$counted ='*')
{
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $array_data = explode(',', $data);
    $count=0;
    $data_manup = $array_data;
    $sql = "SELECT $counted FROM requests";
    if(count($data_manup)>1)
    {
        for($i=0;$i<count($data_manup);$i+=2)
        {
            $operation = '=';
            if($i==0)
                $sql .= " WHERE ";

            else if(strpos($data_manup[$i],'||'))
            {
                $data_manup[$i] = str_replace(" ||","",$data_manup[$i]);
                $sql .= " OR ";
            }
            
            else
                $sql .= " AND ";

            if($i+2<count($data_manup))
            {
                if(strpos($data_manup[$i+2],'||') && !isset($open_OR))
                {

                    $open_OR = true;
                    $sql .= "( ";
                }
            }
            $op_list=array("<",">", "<=", ">=", "!=", "NOT LIKE", "LIKE");
            foreach($op_list as $op)
            {
                if(strpos($data_manup[$i+1],$op) !== false)
                {
                    $operation =$op;
                    $data_manup[$i+1] = str_replace($op,"",$data_manup[$i+1]);
                }
            }
            if($data_manup[$i+1]=='IS NOT NULL' || $data_manup[$i+1]=='IS NULL')
                $sql .= "`".$data_manup[$i]."` ".$data_manup[$i+1];
            // else if($operation != "=" && $operation != "!=")
            //     $sql .= $array_data[$i]." $operation ".$array_data[$i+1]."";
            else
            {
                if($data_manup[$i+1] == 'Special Condition')
                {
                    $sql .= Gm_query($conn_fleet);
                }
                else
                {
                    $data_check = (strpos($data_manup[$i+1],"`") !== FALSE)?str_replace("`","",$data_manup[$i+1]):"'".$data_manup[$i+1]."'";
                    $sql .= "`".$data_manup[$i]."` $operation $data_check";
                }
                
            }

            if(isset($open_OR))
            {
                if($i+2<count($array_data))
                {
                    if(strpos($array_data[$i+2],'||') == 0)
                    {
                        $sql .= " )";
                        unset($open_OR);
                    }
                }
                else
                {
                    $sql .= " )"; 
                    unset($open_OR);
                }
            }
        }
    }
    $stmt_count_requests = $conn -> prepare($sql);
    $stmt_count_requests -> execute();
    $result2 = $stmt_count_requests -> get_result();
    $count += $result2 -> num_rows;
    // if($result2 === false)
    // echo "<script>alert(\"".$conn->error."\")</script>";
    return $count;
}
function badge_count_custom($conn,$conn_fleet,$dbs,$data,$counted ='*',$group_by ='')
{
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    $array_data = explode(',', $data);
    $count=0;
        $sql = "SELECT $counted FROM $dbs";
        if(count($array_data)>1)
        {
            for($i=0;$i<count($array_data);$i+=2)
            {
                $operation = '=';
                if($i==0)
                    $sql .= " WHERE ";
                else if(strpos($array_data[$i],'||'))
                {
                    $array_data[$i] = str_replace(" ||","",$array_data[$i]);
                    $sql .= " OR ";
                }
                else
                    $sql .= " AND ";
                
                if($i+2<count($array_data))
                {
                    if(strpos($array_data[$i+2],'||') && !isset($open_OR))
                    {

                        $open_OR = true;
                        $sql .= "( ";
                    }
                }
                $op_list=array("<",">", "<=", ">=", "!=", "LIKE");
                foreach($op_list as $op)
                {
                    if(strpos($array_data[$i+1],$op) !== false)
                    {
                        $operation =$op;
                        $array_data[$i+1] = str_replace($op,"",$array_data[$i+1]);
                    }
                }
                if($array_data[$i+1]=='IS NOT NULL' || $array_data[$i+1]=='IS NULL' )
                    $sql .= $array_data[$i]." ".$array_data[$i+1];
                // else if($operation != "=" && $operation != "!=")
                //     $sql .= $array_data[$i]." $operation ".$array_data[$i+1];
                else
                    $sql .= $array_data[$i]." $operation '".$array_data[$i+1]."'";
                
                if(isset($open_OR))
                {
                    if($i+2<count($array_data))
                    {
                        if(strpos($array_data[$i+2],'||') == 0)
                        {
                            $sql .= " )";
                            unset($open_OR);
                        }
                    }
                    else
                    {
                        $sql .= " )"; 
                        unset($open_OR);
                    }
                }
            }
        }
    if($group_by != "")
    {
        $sql .= " Group By $group_by";
    }
    $stmt_count_custom = $conn -> prepare($sql);
    $stmt_count_custom -> execute();
    $result2 = $stmt_count_custom -> get_result();
    // if($result2 === false)
    // echo "<script>alert(\"".$conn->error."\")</script>";
    $count+=$result2->num_rows;
    return $count;
    // return $sql;
}
function table_create($headtbl,$str,$raw = true,$datatbl = true,$has_issue = false)
{
    $datatable = ($datatbl)?" id='table1'":"";
    if($raw)
    {
        $first_head = true;
        $first_body = true;
        $table_data = "
        <table class='table table-striped mt-3'$datatable style='overflow:scroll'>
        ";
        if($headtbl != "")
        {
            $all_head = explode(',',$headtbl);
            foreach($all_head as $th)
            {
                if($first_head)
                {
                    $table_data .= "
                        <thead class='table-primary'>
                        <tr>
                    ";
                    $first_head = false;
                }
                $table_data .= "
                    <th class='text-capitalize'>
                    $th
                    </th>
                ";
            }
            $table_data .= "
                </tr>
                </thead>
            ";
        }
        $all_rows = explode('==',$str);
        foreach($all_rows as $tr)
        {
            if($tr == "") continue;
            if($first_body)
            {
                $table_data .= "<tbody id = 'tbl_bdy'>";
                $first_body = false;
            }
            $table_data .= "<tr class='position-relative focus'>";// (($has_issue)?:"")
            $all_data = explode('::_::',$tr);
            foreach($all_data as $td)
            {
                $table_data .= "
                    <td class='text-capitalize ".((strpos($td,"issue.php") !== false)?"issue_holder":"")."'>
                        $td
                    </td>
                ";
            }
            $table_data .= "</tr>";
        }
        $table_data .= "
            </tbody>
        ";
        $table_data .= "
        </table>
        ";
    }
    else
    {
        $table_data = "
        <table class='table table-striped mt-3'$datatable>
        $headtbl
        $str
        </table>
        ";
    }
    return $table_data;
}
////////////////////// Prepares ////////////////////////////

###################### Remarks #######################################
$sql_remark = "INSERT INTO `remarks`(`request_id`, `remark`, `user`, `level`) VALUES (?,?,?,?)";
$stmt_remark = $conn->prepare($sql_remark);
######################################################################

###################### close email ###################################
$sql_email_close = "UPDATE emails SET `reason`='closed' WHERE `reason`=?";
$stmt_email_close = $conn->prepare($sql_email_close);
######################################################################

###################### close email by tag ############################
$sql_email_close_tag = "UPDATE emails SET `reason` = 'closed' WHERE `reason` = ? and tag = ?";
$stmt_email_close_tag = $conn->prepare($sql_email_close_tag);
######################################################################

###################### email location ################################
$sql_email_page = "UPDATE emails SET `to_page`=? WHERE `id`= ?";
$stmt_email_page = $conn->prepare($sql_email_page);
######################################################################

###################### Add Selection #################################
$sql_add_selections = "INSERT into `selections` (`user`, `cluster_id`, `selection`) VALUES (?,?,?)";
$stmt_add_selections = $conn->prepare($sql_add_selections);
######################################################################

###################### Add Record ####################################
$sql_add_record = "INSERT INTO `recorded_time`(`user`, `database_name`, `for_id`, `opperation`) VALUES (?,?,?,?)";
$stmt_add_record = $conn->prepare($sql_add_record);
######################################################################

###################### Select PO by Cluster ##########################
$sql_po_cluster = "SELECT * FROM `purchase_order` where `cluster_id`= ?";
$stmt_po_cluster = $conn->prepare($sql_po_cluster);
######################################################################

############################ Select POs ###############################
$sql_pos = "SELECT * FROM `purchase_order`";
$stmt_pos = $conn->prepare($sql_pos);
######################################################################

###################### Select PO by Cluster ##########################
$sql_po_cluster_active = "SELECT * FROM `purchase_order` where `cluster_id`= ? and `status` != 'canceled'";
$stmt_po_cluster_active = $conn->prepare($sql_po_cluster_active);
######################################################################

###################### Select Request ################################
$sql_request = "SELECT * FROM requests where `request_id`= ?";
$stmt_request = $conn->prepare($sql_request);
######################################################################

###################### Select cluster by finance #####################
$sql_cluster_by_finance = "SELECT * FROM `cluster` where `status` = ? AND finance_company = ?";
$stmt_cluster_by_finance = $conn->prepare($sql_cluster_by_finance);  
######################################################################

###################### Select PO #####################################
$sql_po = "SELECT * FROM purchase_order where `purchase_order_id`= ?";
$stmt_po = $conn->prepare($sql_po);
######################################################################

################# Select request with report #########################
$sql_request_with_report = "SELECT *,r.request_id as request_id FROM requests AS r INNER JOIN report AS rep on r.request_id = rep.request_id WHERE r.request_id = ?";
$stmt_request_with_report = $conn -> prepare($sql_request_with_report);
######################################################################

###################### Select PO by request_id #######################
$sql_po_by_request = "SELECT * FROM purchase_order where `request_id`= ?";
$stmt_po_by_request = $conn->prepare($sql_po_by_request);
######################################################################

###################### Select Performa ###############################
$sql_performa = "SELECT * FROM performa where `id`= ?";
$stmt_performa = $conn->prepare($sql_performa);
######################################################################

################ Select prefered_vendors specific ####################
$sql_vendor_specific = "SELECT * FROM `prefered_vendors` where id = ?";
$stmt_vendor_specific = $conn->prepare($sql_vendor_specific);
######################################################################

###################### Select Stock ##################################
$sql_stock = "SELECT * FROM `stock` where `id`= ?";
$stmt_stock = $conn->prepare($sql_stock);
######################################################################

###################### Select Account ################################
$sql_account = "SELECT * FROM `account` where  Username = ?";
$stmt_account = $conn->prepare($sql_account);
######################################################################

###################### Select email by reason ########################
$sql_email_reason = "SELECT * FROM `emails` where `reason` = ?";
$stmt_email_reason_get = $conn->prepare($sql_email_reason);
######################################################################

###################### Select Account active #########################
$sql_account_active = "SELECT * FROM `account` where  Username = ? and `status` = 'active'";
$stmt_account_active = $conn->prepare($sql_account_active);
######################################################################

###################### Select Active Account #########################
$sql_active_account = "SELECT * FROM `account` where  Username = ? and status = 'active'";
$stmt_active_account = $conn->prepare($sql_active_account);
######################################################################

###################### Select GM Account #############################
$sql_GM = "SELECT * FROM `account` where  (department = 'GM') AND company = ? and (role = 'GM') and status = 'active'";
$stmt_GM = $conn->prepare($sql_GM);
######################################################################

###################### Select Specific Approval ######################
$sql_specific_approval = "SELECT * FROM `committee_approval` Where `cluster_id` = ? AND `committee_member` = ? AND `status` = 'Approved'";
$stmt_specific_approval = $conn->prepare($sql_specific_approval);
######################################################################

###################### Select Specific Approval ######################
$sql_approvals = "SELECT * FROM `committee_approval` Where `cluster_id` = ? AND `status` = 'Approved'";
$stmt_approvals = $conn->prepare($sql_approvals);
######################################################################

###################### Select Rejection ##############################
$sql_rejected = "SELECT * FROM `committee_approval` Where `cluster_id` = ? AND `status` = 'Rejected'";
$stmt_rejected = $conn->prepare($sql_rejected);
######################################################################

###################### Select Selections #############################
$sql_selections = "SELECT * FROM `selections` Where `cluster_id` = ?";
$stmt_selections = $conn->prepare($sql_selections);
######################################################################

###################### Select Selections #############################
$sql_selections_specific = "SELECT * FROM `selections` Where `cluster_id` = ? and user = ?";
$stmt_selections_specific = $conn->prepare($sql_selections_specific);
######################################################################

###################### Select Cluster ################################
$sql_cluster = "SELECT * FROM `cluster` where `id` = ?";
$stmt_cluster = $conn->prepare($sql_cluster);
######################################################################

###################### Select limit_ho ###############################
$sql_limit = "SELECT * FROM `limit_ho` where company = ? ORDER BY id DESC limit 1";
$stmt_limit = $conn->prepare($sql_limit);
######################################################################

###################### Select Cheques ################################
$sql_cheques = "SELECT * FROM `cheque_info` where cluster_id = ?";
$stmt_cheques = $conn->prepare($sql_cheques);
######################################################################

###################### Select Cheques Active #########################
$sql_cheques_active = "SELECT * FROM `cheque_info` where cluster_id = ? and void = 0";
$stmt_cheques_active = $conn->prepare($sql_cheques_active);
######################################################################

###################### Select Cheques Active cpv_no ##################
$sql_cheques_active_cpv = "SELECT * FROM `cheque_info` where cpv_no = ? and void = 0";
$stmt_cheques_active_cpv = $conn->prepare($sql_cheques_active_cpv);
######################################################################

###################### Select Account based on Role ##################
$sql_account_role = "SELECT * FROM `account` Where `Username` = ? AND `role` = ?";
$stmt_account_role = $conn->prepare($sql_account_role);
######################################################################

###################### Select Account based on Role ##################
$sql_accounts_role_based = "SELECT * FROM `account` where department = ? AND company = ? AND `role` = ? AND `status` = 'active'";
$stmt_accounts_role_based = $conn->prepare($sql_accounts_role_based);
######################################################################

###################### Select HO procurement #########################
$sql_ho_proc_managers = "SELECT * FROM `account` where (department = 'Procurement' AND role='manager' AND company = 'Hagbes HQ.')  AND status = 'active'";
$stmt_ho_proc_managers = $conn->prepare($sql_ho_proc_managers);
######################################################################

###################### Select procurement manager ####################
$sql_proc_manager = "SELECT * FROM `account` where (((department = 'Procurement' AND (role = 'manager' OR `type` LIke '%manager%')) or `additional_role` = 1) and company = ?) AND status = 'active'";
$stmt_proc_manager = $conn->prepare($sql_proc_manager);
######################################################################

###################### Select PO by cluster active ###################
$sql_po_by_cluster_active = "SELECT * FROM `purchase_order` where cluster_id = ? and `status` != 'canceled'";
$stmt_po_by_cluster_active = $conn->prepare($sql_po_by_cluster_active);
######################################################################

############################## Update Status #########################
$stmt_status_update = $conn->prepare("UPDATE requests SET `status`=? WHERE `request_id`=? ");
######################################################################

############################## Update next step ######################
$stmt_next_step = $conn->prepare("UPDATE requests SET `next_step` = ? WHERE `request_id` = ?");
######################################################################

############################## Update next step ######################
$stmt_status_next = $conn->prepare("UPDATE requests SET `status`= ?, `next_step` = ? WHERE `request_id` = ?");
######################################################################

############################## Update PO Status ######################
$stmt_po_status = $conn->prepare("UPDATE `purchase_order` SET `status`=? WHERE `purchase_order_id`=?");
######################################################################

############################## Update cluster Status ######################
$stmt_cluster_status = $conn->prepare("UPDATE `cluster` SET `status`=? WHERE `id`=?");
######################################################################

############################## SELECT Price ##########################
$sql_prices = "SELECT * FROM `price_information` WHERE `cluster_id`= ?";
$stmt_prices = $conn->prepare($sql_prices);
######################################################################

########################### SELECT Price Selected ####################
$sql_prices_selected = "SELECT * FROM `price_information` WHERE `cluster_id`= ? And selected = '1'";
$stmt_prices_selected = $conn->prepare($sql_prices_selected);
######################################################################

############################## Select Specification ##################
$sql_specification = "SELECT * FROM `specification` WHERE `id` = ?";
$stmt_specification = $conn->prepare($sql_specification);
######################################################################

############################## Select all Companies ##################
$sql = "SELECT * FROM `comp`";
$stmt_all_company = $conn_fleet->prepare($sql); 
######################################################################

############################## Select Company ########################
$sql="SELECT * FROM `comp` where `Name`= ?";
$stmt_company = $conn_fleet->prepare($sql);
######################################################################

############################## Select Manager ########################
$sql = "SELECT * FROM account WHERE `department` = ? and `company` = ? and ((role = 'manager' OR `type` LIke '%manager%') OR `role`='GM' OR `role`='Director')";
$stmt_manager = $conn -> prepare($sql);
######################################################################

############################## Select Manager active ########################
$sql = "SELECT * FROM account WHERE `department` = ? and `company` = ? and ((role = 'manager' OR `type` LIke '%manager%') OR `role`='GM' OR `role`='Director') and `status` = 'active'";
$stmt_manager_active = $conn -> prepare($sql);
######################################################################

############################## Select Specification ##################
$sql_spec = "SELECT * FROM `specification` WHERE `id` = ?";
$stmt_spec = $conn->prepare($sql_spec);
######################################################################

############################## Select Project ########################
$sql_project = "SELECT * FROM `project` WHERE `project_id`= ?";
$stmt_project = $conn->prepare($sql_project);
######################################################################

############################## Select Project PMS ####################
$sql_project_pms = "SELECT * FROM `projects` WHERE `id`= ?";
$stmt_project_pms = $conn_pms -> prepare($sql_project_pms);
######################################################################

############################## Select Project PMS ####################
$sql_task = "SELECT * FROM `task` WHERE `id_p`= ?";
$stmt_task = $conn_pms -> prepare($sql_task);
######################################################################

############################## Select description WMS ################
$sql_description = "SELECT * FROM `description` WHERE `iden`= ?";
$stmt_description = $conn_ws -> prepare($sql_description);
######################################################################
?>