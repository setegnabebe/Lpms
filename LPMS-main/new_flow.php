<?php
session_start();
function Gm_query($conn_fleet)
{
    $conditions = $conditions_not = $conditions_All = $conditions_neg = '';
    $sql_comp = "SELECT * FROM `comp`";
    $stmt_all_company = $conn_fleet->prepare($sql_comp); 
    $stmt_all_company -> execute();
    $result_comp = $stmt_all_company -> get_result();
    if($result_comp->num_rows > 0)
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
                    $conditions_neg .= ($conditions_neg == "")?"`company` = '$row_comp[Name]'":" OR `company` = '$row_comp[Name]'";
                    $deps = (strpos($row_comp['With GM'],"All") === false)?" and (`department` in ('".$row_comp['With GM']."'))":"";
                    $conditions .= ($conditions == "")?"(`company` = '$row_comp[Name]' $deps)":"OR (`company` = '$row_comp[Name]' $deps)";
                    $conditions_not .= ($conditions_not == "")?"(`company` = '$row_comp[Name]' and (`department` NOT IN ('".$row_comp['With GM']."')))":" OR (`company` = '$row_comp[Name]' and (`department` NOT IN (".$row_comp['With GM'].")))";
                }
            }
        }
        $conditions_neg = ($conditions_neg != "" && $conditions_All != "")?"$conditions_All OR $conditions_neg":(($conditions_neg != "")?$conditions_neg:$conditions_All);
        $conditions = ($conditions_neg != "" && $conditions != "")?"!($conditions_neg) OR $conditions":(($conditions_neg != "")?"!($conditions_neg)":$conditions);
        // $conditions = "(($conditions) AND `status` = 'Approved By GM')";
        // $conditions_2 = $conditions_not.(($conditions_All != "" && $conditions_not != "")?" OR ":"")."(".$conditions_All.")";
        // $conditions_2 = ($conditions_2 != "")?"(($conditions_2) AND `status` = 'Approved By Dep.Manager')":"";
        // $conditions .= (($conditions_2 != "" && $conditions != "")?" OR ":"").$conditions_2;
        return "(".$conditions.")";
}
$servername = "localhost";
$username = "root";
$password = "";//4hR3XnqZaTcg3hf.
$conn = new mysqli($servername, $username, $password,"project_lpms");
$conn_fleet = new mysqli($servername, $username, $password,"fleet");


$needsProperty = "((request_type = 'Spare and Lubricant' AND type = 'Spare' AND `mode` = 'Internal') OR 
(request_type = 'Tyre and Battery' AND `mode` = 'Internal') OR 
(request_type != 'Consumer Goods' AND request_type != 'Tyre and Battery' AND request_type != 'Spare and Lubricant' AND request_type != 'Miscellaneous'))";

$sql = "UPDATE `requests` SET `department` = 'GM' where company != 'Hagbes HQ.' and `department` = 'Director'";
$stmt_set_dep_gm = $conn -> prepare($sql); 
$stmt_set_dep_gm -> execute();
$sql = "UPDATE `account` SET `department` = 'GM' where company != 'Hagbes HQ.' AND `department` = 'Director'";
$stmt_set_account_dep_gm = $conn -> prepare($sql); 
$stmt_set_account_dep_gm -> execute();
$sql = "UPDATE `account` SET `role` = 'GM' where company != 'Hagbes HQ.' AND `role` = 'Director'";
$stmt_set_account_role_gm = $conn -> prepare($sql); 
$stmt_set_account_role_gm -> execute();
// Update all GM and GM_approval_date //
$sql = "UPDATE `requests` SET GM = director where `director` IS NOT NULL";
$stmt_update_gm_director = $conn -> prepare($sql); 
$stmt_update_gm_director -> execute();
$sql = "UPDATE `report` SET GM_approval_date = Director_approval_date where `Director_approval_date` IS NOT NULL";
$stmt_update_gm_date_director = $conn -> prepare($sql); 
$stmt_update_gm_date_director -> execute();
////////////////////////////////////////

 //////////// Spec Provided //////////// (MIGHT PULL BACK OWNER APPROVAL)
 $sql = "UPDATE `requests` SET `status` = 'Specification Provided' where request_type = 'Fixed Assets' AND (`status` = 'Approved By Director' OR `status` = 'Approved By Owner') AND spec_dep = 'IT' AND specification IS NOT NULL AND stock_info IS NULL";
 $stmt_Specification_Provided = $conn -> prepare($sql); 
 $stmt_Specification_Provided -> execute();
 ////////////////////////////////////////

 //////////////////////////////////////
$sql = "UPDATE `requests` SET `status` = 'Store Checked' where (`status` = 'Approved By Director' OR `status` = 'Approved By Owner') AND stock_info IS NOT NULL"; //  AND $needsProperty
$stmt_Store_Checked = $conn -> prepare($sql); 
$stmt_Store_Checked -> execute();
 //////////////////////////////////////
 
 
//////////////////////////////////////////
 $sql = "UPDATE `requests` SET `status` = 'Approved By GM' where `status` = 'Approved By Director' and stock_info IS NULL AND ".Gm_query($conn_fleet);
 $stmt_Approved_By_GM = $conn -> prepare($sql); 
 $stmt_Approved_By_GM -> execute();
 //////////////////////////////////////////
 
//////////////////////////////////////////
$sql = "UPDATE `requests` SET `status` = 'Approved By Dep.Manager' where `status` = 'Approved By Director' and stock_info IS NULL AND !".Gm_query($conn_fleet);
$stmt_Approved_By_DepManager = $conn -> prepare($sql); 
$stmt_Approved_By_DepManager -> execute();
//////////////////////////////////////////

// $sql = "UPDATE `requests` SET `phase_one` = '2' where `status` = 'Approved By Dep.Manager'";
// $conn->query($sql);

// $sql = "UPDATE `requests` SET GM = director where `status` = 'Approved By Director' AND stock_info IS NOT NULL AND !$needsProperty and company = 'Hagbes HQ.'";
// $conn->query($sql);
        




################# End of Phase 1 ####################
 //////////////////////////////////////
$sql = "UPDATE `requests` SET `status` = 'Approved By Director' where `status` = 'Approved By Property' AND company = 'Hagbes HQ.' and request_type != 'Fixed Assets'";
$stmt_Approved_By_Director = $conn -> prepare($sql); 
$stmt_Approved_By_Director -> execute();
 //////////////////////////////////////
$sql = "UPDATE `requests` SET `status` = 'Approved By Owner' where `status` = 'Approved By Property' AND request_type = 'Fixed Assets'";
$stmt_Approved_By_Owner = $conn -> prepare($sql); 
$stmt_Approved_By_Owner -> execute();
 //////////////////////////////////////



 $sql = "UPDATE `requests` SET `phase_one` = '2' where `status` = 'Approved By Dep.Manager' OR `status` = 'Rejected By Dep.Manager'";
 $stmt_phase_one_2 = $conn -> prepare($sql); 
 $stmt_phase_one_2 -> execute();
 $sql = "UPDATE `requests` SET `phase_one` = '3' where `status` = 'Approved By GM' OR `status` = 'Rejected By GM'";
 $stmt_phase_one_3 = $conn -> prepare($sql); 
 $stmt_phase_one_3 -> execute();

// $sql = "SELECT *,R.request_type as request_type FROM `requests` as R INNER JOIN `stock` as S ON R.request_id = S.request_id where R.status != 'waiting'";
// $outofstock = $row['for_purchase'];
// $instock = $row['in-stock'];
// if($instock > 0) $no_property = false;
// if(isset($outofstock) && $outofstock == 0)$initials+=2;

$sql = "SELECT * FROM `requests` where `status` != 'waiting'";
$stmt_active = $conn -> prepare($sql); 
$stmt_active -> execute();
$result_active = $stmt_active -> get_result();
if($result_active -> num_rows>0)
    while($row = $result_active -> fetch_assoc())
    {
        unset($passed3);
        $type = $row['request_type'];
        $sql = "SELECT * FROM `comp` where `Name` = ?";
        $stmt_specific_company = $conn_fleet->prepare($sql);
        $stmt_specific_company -> bind_param("s", $row["company"]);
        $stmt_specific_company -> execute();
        $result_specific_company = $stmt_specific_company -> get_result();
        if($result_specific_company -> num_rows > 0)
        {
            while($r = $result_specific_company -> fetch_assoc()) 
            {
                $GMs = (!is_null($r['With GM']))?explode(",",$r['With GM']):[];
            }
        }
        $has_gm = in_array($row['department'],$GMs) || in_array("All",$GMs);

        $no_property = false;
        if (($type == 'Spare and Lubricant' && ($row['mode'] == 'External' || $row['type'] == 'Lubricant')) || ($type == 'Tyre and Battery' && ($row['mode'] == 'External')) || $type == 'Miscellaneous' || $type == 'Consumer Goods') {
            $no_property = true;
        }
        $spec_needed = ($row['spec_dep'] == 'IT' && $row['request_type'] != 'Spare and Lubricant');


        $initials = 3;
        if($has_gm)$initials++;
        if($spec_needed)$initials++;
        if(!$no_property)$initials++;
        if($type == 'Fixed Assets')$initials++;
        if($type == 'agreement')$initials++;
        if($row["company"] == 'Hagbes HQ.' && $row["department"] != 'Owner')$initials++;
        if(!is_null($row['stock_info']))
        {
            $sql = "SELECT * FROM stock WHERE `id` = ?"; // and `type`='".$type."'
            $stmt_stock = $conn -> prepare($sql);
            $stmt_stock -> bind_param("i", $row['stock_info']);
            $stmt_stock -> execute();
            $result_stock = $stmt_stock -> get_result();
            if($result_stock -> num_rows > 0)
                while ($r = $result_stock -> fetch_assoc())
                {
                    $status_stock = $r['status'];
                    $instock = $r['in-stock'];
                    if($instock > 0) $no_property = false;
                    $outofstock = $r['for_purchase'];
                    if($outofstock == 0)
                    {
                        if($row['status'] == 'Found In Stock')
                        {
                            if($r['status'] == 'not approved')
                            {
                                $sql = "UPDATE `requests` SET `phase_one` = '4',next_step = 'Property' where request_id = ?";
                                $stmt_phase_one_4 = $conn -> prepare($sql);
                                $stmt_phase_one_4 -> bind_param("i", $row['request_id']);
                                $stmt_phase_one_4 -> execute();
                            }
                            else if($r['status'] == 'Approved')
                            {
                                $sql = "UPDATE `requests` SET `phase_one` = '5',next_step = 'Department Approval' where request_id = ?";
                                $stmt_phase_one_5 = $conn -> prepare($sql);
                                $stmt_phase_one_5 -> bind_param("i", $row['request_id']);
                                $stmt_phase_one_5 -> execute();
                            }
                            else if($r['flag'] == 1)
                            {
                                $sql = "UPDATE `requests` SET `phase_one` = '6',next_step = 'Store' where request_id = ?";
                                $stmt_phase_one_6 = $conn -> prepare($sql);
                                $stmt_phase_one_6 -> bind_param("i", $row['request_id']);
                                $stmt_phase_one_6 -> execute();
                            }
                        }
                        else if($row['status'] == 'All Complete' || $r['status'] == 'All Complete')
                        {
                            $sql = "UPDATE `requests` SET `phase_one` = '7' where request_id = ?";
                            $stmt_phase_one_7 = $conn -> prepare($sql);
                            $stmt_phase_one_7 -> bind_param("i", $row['request_id']);
                            $stmt_phase_one_7 -> execute();
                        }
                    }
                }
        }
        $sql = "SELECT * FROM purchase_order WHERE `request_id` = ?";
        $stmt_po_request = $conn -> prepare($sql);
        $stmt_po_request -> bind_param("i", $row['request_id']);
        $stmt_po_request -> execute();
        $result_po_request = $stmt_po_request -> get_result();
        if ($result_po_request -> num_rows == 0)
        {
            if($row['status'] == 'Approved By Dep.Manager')
            {
                if($has_gm)
                {
                    $next = "next_step = 'GM'";
                }
                else if($spec_needed)
                {
                    $next = "next_step = 'IT Specification'";
                }
                else
                {
                    $next = "next_step = 'Store'";
                }
                $sql = "UPDATE `requests` SET $next where `request_id` = ?";
                $stmt_nxt_step = $conn -> prepare($sql);
                $stmt_nxt_step -> bind_param("i", $row['request_id']);
                $stmt_nxt_step -> execute();
            }
            else if($row['status'] == 'Approved By GM')
            {
                if($spec_needed)
                {
                    $next = "next_step = 'IT Specification'";
                }
                else
                {
                    $next = "next_step = 'Store'";
                }
                $sql = "UPDATE `requests` SET $next where `request_id` = ?";
                $stmt_nxt_step = $conn -> prepare($sql);
                $stmt_nxt_step -> bind_param("i", $row['request_id']);
                $stmt_nxt_step -> execute();
            }
            else if($row['status'] == 'Approved By Director')
            {
                if($type == 'Fixed Assets')
                {
                    $place = $initials-1;
                    $next = ",next_step = 'Owner'";
                }
                else
                {
                    $place = $initials;
                    $next = ",next_step = 'Performa'";
                }
                $sql = "UPDATE `requests` SET `phase_one` = ?$next where `request_id` = ?";
                $stmt_phase_one_nxt_step = $conn -> prepare($sql);
                $stmt_phase_one_nxt_step -> bind_param("ii", $place ,$row['request_id']);
                $stmt_phase_one_nxt_step -> execute();
            }
            else if($row['status'] == 'Specification Provided')
            {
                $x = 3 + (($has_gm)?1:0);
                $next = ",next_step = 'Store'";
                $sql = "UPDATE `requests` SET `phase_one` = ?$next where `request_id` = ?";
                $stmt_phase_one_nxt_step = $conn -> prepare($sql);
                $stmt_phase_one_nxt_step -> bind_param("ii", $x ,$row['request_id']);
                $stmt_phase_one_nxt_step -> execute();
            }
            else if($row['status'] == 'Store Checked')
            {
                $x = 3 + (($has_gm)?1:0) + (($spec_needed)?1:0);
                $next = ",next_step = 'Performa'";
                if(!($no_property))
                {
                    $next = ",next_step = 'Property'";
                }
                else
                {
                    if($row['company'] == 'Hagbes HQ.')
                        $next = ",next_step = 'Director'";
                    else if($type == 'Fixed Assets')
                        $next = ",next_step = 'Owner'";
                }
                $sql = "UPDATE `requests` SET `phase_one` = ?$next where `request_id` = ?";
                $stmt_phase_one_nxt_step = $conn -> prepare($sql);
                $stmt_phase_one_nxt_step -> bind_param("ii", $x ,$row['request_id']);
                $stmt_phase_one_nxt_step -> execute();
            }
            else if($row['status'] == 'Approved By Property')
            {
                $next = ",next_step = 'Performa'";
                if($row['company'] == 'Hagbes HQ.')
                    $next = ",next_step = 'Director'";
                else if($type == 'Fixed Assets')
                    $next = ",next_step = 'Owner'";
                $x = 4 + (($has_gm)?1:0) + (($spec_needed)?1:0);
                $sql = "UPDATE `requests` SET `phase_one` = ?$next where `request_id` = ?";
                $stmt_phase_one_nxt_step = $conn -> prepare($sql);
                $stmt_phase_one_nxt_step -> bind_param("ii", $x ,$row['request_id']);
                $stmt_phase_one_nxt_step -> execute();
            }
            else if($row['status'] == 'Approved By Owner')
            {
                $next = ",next_step = 'Performa'";
                $sql = "UPDATE `requests` SET `phase_one` = ?$next where `request_id` = ?";
                $stmt_phase_one_nxt_step = $conn -> prepare($sql);
                $stmt_phase_one_nxt_step -> bind_param("ii", $initials ,$row['request_id']);
                $stmt_phase_one_nxt_step -> execute();
            }
            ///////////////////################### Completed Phase 1 #####################//////////////////////////
        }

        $initials_2 = 6;
        if ($result_po_request -> num_rows > 0)
        while ($r = $result_po_request -> fetch_assoc()) {

            $sql = "UPDATE `requests` SET `phase_one` = ? where `request_id` = ?";
            $stmt_phase_one = $conn -> prepare($sql);
            $stmt_phase_one -> bind_param("ii", $initials, $row['request_id']);
            $stmt_phase_one -> execute();
            if(is_null($r['cluster_id']))
            {
                $num = 1;
                if($r['status'] == 'pending') $num = 1;
                else if($r['status'] == 'Accepted') $num = 2;
                else if($r['status'] == 'Complete') $num = 3;
                else if($r['status'] == 'Performa Comfirmed') $num = 4;
                $sql = "UPDATE `requests` SET `phase_two` = ? where `request_id` = ?";
                $stmt_phase_two = $conn -> prepare($sql);
                $stmt_phase_two -> bind_param("ii", $num, $row['request_id']);
                $stmt_phase_two -> execute();
                ///////////////////################### Completed Phase 2 #####################//////////////////////////
            }
            else
            {
                $sql = "SELECT * FROM cluster WHERE `id` = ? AND status != 'Pending'";
                $stmt_active_cluster = $conn -> prepare($sql);
                $stmt_active_cluster -> bind_param("i", $r['cluster_id']);
                $stmt_active_cluster -> execute();
                $result_active_cluster = $stmt_active_cluster -> get_result();
                if ($result_active_cluster -> num_rows > 0)
                while ($r_clus = $result_active_cluster -> fetch_assoc())
                {
                    $sql = "UPDATE `requests` SET `phase_two` = ? where `request_id` = ?";
                    $stmt_phase_two = $conn -> prepare($sql);
                    $stmt_phase_two -> bind_param("ii", $initials_2, $row['request_id']);
                    $stmt_phase_two -> execute();
                    $initials_3 = 6;
                    if($r_clus['status'] == 'Approved')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = '1' where `request_id` = ?";
                        $stmt_phase_three_1 = $conn -> prepare($sql);
                        $stmt_phase_three_1 -> bind_param("i", $row['request_id']);
                        $stmt_phase_three_1 -> execute();
                    }
                    else if($row['status'] == 'Sent to Finance')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = '2' where `request_id` = ?";
                        $stmt_phase_three_2 = $conn -> prepare($sql);
                        $stmt_phase_three_2 -> bind_param("i", $row['request_id']);
                        $stmt_phase_three_2 -> execute();
                    }
                    else if($row['status'] == 'Reviewed')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = '3' where `request_id` = ?";
                        $stmt_phase_three_3 = $conn -> prepare($sql);
                        $stmt_phase_three_3 -> bind_param("i", $row['request_id']);
                        $stmt_phase_three_3 -> execute();
                    }
                    else if($row['status'] == 'Finance Approved' || $row['status'] == 'Finance Approved Petty Cash')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = '4' where `request_id` = ?";
                        $stmt_phase_three_4 = $conn -> prepare($sql);
                        $stmt_phase_three_4 -> bind_param("i", $row['request_id']);
                        $stmt_phase_three_4 -> execute();
                    }
                    else if($row['status'] == 'Cheque Prepared' || $row['status'] == 'Petty Cash Approved')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = '5' where `request_id` = ?";
                        $stmt_phase_three_5 = $conn -> prepare($sql);
                        $stmt_phase_three_5 -> bind_param("i", $row['request_id']);
                        $stmt_phase_three_5 -> execute();
                    }
                    else if($row['status'] == 'Payment Processed')
                    {
                        $sql = "UPDATE `requests` SET `phase_three` = ? where `request_id` = ?";
                        $stmt_phase_three = $conn -> prepare($sql);
                        $stmt_phase_three -> bind_param("ii", $initials_3, $row['request_id']);
                        $stmt_phase_three -> execute();
                    }
                    else if($r_clus['status'] != 'Generated')
                    {
                        $passed3 = true;
                        ///////////////////################### Completed Phase 3 #####################//////////////////////////
                    }
                }
                else
                {
                    $sql = "UPDATE `requests` SET `phase_two` = '5' where `request_id` = ?";
                    $stmt_phase_two_5 = $conn -> prepare($sql);
                    $stmt_phase_two_5 -> bind_param("i", $row['request_id']);
                    $stmt_phase_two_5 -> execute();
                }
            }
        }
        if(isset($passed3))
        {
            $initials_4 = 5;
            $sql = "UPDATE `requests` SET `phase_three` = ? where `request_id` = ?";
            $stmt_phase_three = $conn -> prepare($sql);
            $stmt_phase_three -> bind_param("ii", $initials_3, $row['request_id']);
            $stmt_phase_three -> execute();
            if($row['status'] == 'Collected-not-comfirmed' && $row['flag'] == 0) // || $row['status'] == 'Collected'
            {
                $sql = "UPDATE `requests` SET `phase_four` = '1' where `request_id` = ?";
                $stmt_phase_four_1 = $conn -> prepare($sql);
                $stmt_phase_four_1 -> bind_param("i", $row['request_id']);
                $stmt_phase_four_1 -> execute();
            }
            else if($row['status'] == 'Collected-not-comfirmed' && $row['flag'] == 1) // || $row['status'] == 'Collected'
            {
                $sql = "UPDATE `requests` SET `phase_four` = '2' where `request_id` = ?";
                $stmt_phase_four_2 = $conn -> prepare($sql);
                $stmt_phase_four_2 -> bind_param("i", $row['request_id']);
                $stmt_phase_four_2 -> execute();
            }
            else if($row['status'] == 'In-stock') // || $row['status'] == 'Collected'
            {
                $sql = "UPDATE `requests` SET `phase_four` = '3' where `request_id` = ?";
                $stmt_phase_four_3 = $conn -> prepare($sql);
                $stmt_phase_four_3 -> bind_param("i", $row['request_id']);
                $stmt_phase_four_3 -> execute();
            }
            else if($row['status'] == 'All Complete') // || $row['status'] == 'Collected'
            {
                $sql = "UPDATE `requests` SET `phase_four` = '4' where `request_id` = ?";
                $stmt_phase_four_4 = $conn -> prepare($sql);
                $stmt_phase_four_4 -> bind_param("i", $row['request_id']);
                $stmt_phase_four_4 -> execute();
                ///////////////////################### Completed Phase 3 #####################//////////////////////////
            }
            else if(isset($r['settlement']) && !is_null($r['settlement']))
            {
                $sql = "UPDATE `requests` SET `phase_four` = '5' where `request_id` = ?";
                $stmt_phase_four_5 = $conn -> prepare($sql);
                $stmt_phase_four_5 -> bind_param("i", $row['request_id']);
                $stmt_phase_four_5 -> execute();
            }
        }

    }