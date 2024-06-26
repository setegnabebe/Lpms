<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
        include '../connection/connect.php';
        include "../common/functions.php";
        include '../common/details.php';
        if(isset($_GET['item']))
        {
                $item = trim($_GET['item']);
                $amount =0;
                $sql = "SELECT * FROM store WHERE `product_descr` = '".$item."' ORDER BY `lastupdate_onerp` DESC";//GROUP BY `product_partno`,`warehouse` 
                $sql1 = "SELECT * FROM store WHERE `product_descr` = ? ORDER BY `lastupdate_onerp` DESC LIMIT 21";//GROUP BY `product_partno`,`warehouse` 
                $stmt_search_agresso = $conn->prepare($sql);
                // $stmt_search_agresso -> bind_param("i", $item);
                $stmt_search_agresso -> execute();
                $result_search_agresso = $stmt_search_agresso -> get_result();
                if($result_search_agresso -> num_rows < 21)
                        if(sizeof(explode(" ",$item))>1)
                        {
                                $q = "(`product_descr` LIKE '%$item%' AND `product_descr` != '$item')";
                                foreach(explode(" ",$item) as $i)
                                {
                                        // $i_proccessed = (strlen($i)>2)?trim($i):trim($i)." ";
                                        if(strlen($i)>2)
                                        {
                                                $i_proccessed = trim($i);
                                                $q .= ($q == "")?"`product_descr` LIKE '%$i_proccessed%'":" OR `product_descr` LIKE '%$i_proccessed%'";
                                        }
                                }
                                if($q != "")
                                {
                                        $limit = 21 - $result_search_agresso -> num_rows;
                                        $sql = "SELECT * FROM store WHERE $q ORDER BY `lastupdate_onerp` DESC";//GROUP BY `product_partno`,`warehouse` 
                                        $sql2 = "SELECT * FROM store WHERE $q ORDER BY `lastupdate_onerp` DESC LIMIT $limit";//GROUP BY `product_partno`,`warehouse` 
                                        $stmt_search_agresso_desc = $conn->prepare($sql);
                                        $stmt_search_agresso_desc -> execute();
                                        $result_search_agresso_desc = $stmt_search_agresso_desc -> get_result();
                                }
                        }
                if(isset($result_search_agresso_desc))
                        $found = $result->num_rows + $result_search_agresso_desc->num_rows;
                else
                        $found = $result_search_agresso -> num_rows;
                $amount =ceil($found/21);
                if($found == 0)
                        echo "<div class='py-5 pricing'>
                                <div class='section-title text-center py-2  alert-primary rounded'>
                                        <h3 class='mt-4'>There are No Items that Match the Item in Agreso Store</h3>
                                </div>
                                <!--<h3 class='mt-4 text-center'><button class='btn btn-primary'>View All items In store</button></h3>-->
                        </div>";
                else
                {
                        echo "
                        <ul class= 'row' id='add_here'>
                        <h4 class='text-capitalize mb-2 text-center text-primary'> ".$_SESSION['company']."</h4>
                        <h5 class='text-capitalize mb-2 text-center'>Item - ".$item." (found matches - ".$found.")</h5>
                                ";
                        if($result_search_agresso -> num_rows > 0)
                        {
                                $stmt_search_agresso_limited = $conn->prepare($sql1);
                                $stmt_search_agresso_limited -> bind_param("i", $item);
                                $stmt_search_agresso_limited -> execute();
                                $result_search_agresso = $stmt_search_agresso_limited -> get_result();
                                while($row = $result_search_agresso->fetch_assoc())
                                {
                                        $stock_level = intval($row['stock_level']);
                                        $minimum_stock_level = intval($row['minimum_stock_level']);
                                        $color = (($stock_level == 0)?"danger":(($stock_level <= $minimum_stock_level)?"warning":"success"));
                                        echo "
                                                <div class='col-4 mx-auto my-2'>
                                                <li class='list-group-item list-group-item-$color'>
                                                        <div class='mx-auto'>
                                                                <span class='text-primary text-capitalize'>Product Part NO</span> - $row[product_partno]<br>
                                                                <span class='text-primary text-capitalize'>Description</span> - $row[product_descr]<br>
                                                                <span class='text-primary text-capitalize'>Warehouse</span> - $row[warehouse]<br>
                                                                <span class='text-primary text-capitalize'>Stock Level</span> - $stock_level<br>
                                                                <span class='text-primary text-capitalize'>Minimum Stock Level</span> - $minimum_stock_level<br>
                                                        </div>
                                                </li>
                                                </div>";
                                }
                        }
                        else if(isset($result_search_agresso_desc))
                        {
                                $stmt_search_agresso_desc = $conn->prepare($sql2);
                                $stmt_search_agresso_desc -> execute();
                                $result_search_agresso_desc = $stmt_search_agresso_desc -> get_result();
                                while($row = $result_search_agresso_desc -> fetch_assoc())
                                {
                                        $stock_level = intval($row['stock_level']);
                                        $minimum_stock_level = intval($row['minimum_stock_level']);
                                        $color = (($stock_level == 0)?"danger":(($stock_level <= $minimum_stock_level)?"warning":"success"));
                                        echo "<div class='col-4 mx-auto my-2'>
                                                        <li class='list-group-item list-group-item-$color'>
                                                                <div class='mx-auto'>
                                                                        <span class='text-primary text-capitalize'>Product Part NO</span> - $row[product_partno]<br>
                                                                        <span class='text-primary text-capitalize'>Description</span> - $row[product_descr]<br>
                                                                        <span class='text-primary text-capitalize'>Warehouse</span> - $row[warehouse]<br>
                                                                        <span class='text-primary text-capitalize'>Stock Level</span> - $stock_level<br>
                                                                        <span class='text-primary text-capitalize'>Minimum Stock Level</span> - $minimum_stock_level<br>
                                                                </div>
                                                        </li>
                                                </div>";
                                }
                        }
                        echo "
                        </ul>";
                        if($amount > 1)
                        {
                                echo "
                                <div id='load_more_store' class='container-fluid text-center'>
                                        <button type='button' class='btn btn-primary' value='1_$amount' onclick='readmore_store(this)'>
                                                View More
                                        </button>
                                </div>";
                                $_SESSION['sql_store'] = $sql;
                        }
                        echo "
                        <p class='text-center border-top mt-3 pt-2'><b>Remark</b><br>
                                <span class='text-success'>Green :</span><span class='text-secondary'> Means Item is Found In store</span><br>
                                <span class='text-warning'>Orange :</span><span class='text-secondary'> Means Item is Running Out</span><br>
                                <span class='text-danger'>Red :</span><span class='text-secondary'> Means Item is out of stock</span>
                        </p>";
                }
        }
        else if(isset($_GET['page_num']))
        {
                $per_page=21;
                $page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
                $offset=($page_num-1)*$per_page;
                $sql = $_SESSION['sql_store']." LIMIT $per_page OFFSET $offset";
                $stmt_search_agresso = $conn->prepare($sql);
                // $stmt_search_agresso -> bind_param("i", $item);
                $stmt_search_agresso -> execute();
                $result_search_agresso = $stmt_search_agresso -> get_result();
                while($row = $result_search_agresso->fetch_assoc())
                {
                        $stock_level = intval($row['stock_level']);
                        $minimum_stock_level = intval($row['minimum_stock_level']);
                        $color = (($stock_level == 0)?"danger":(($stock_level <= $minimum_stock_level)?"warning":"success"));
                        echo "<div class='col-4 mx-auto my-2'>
                                        <li class='list-group-item list-group-item-$color'>
                                                <div class='mx-auto'>
                                                        <span class='text-primary text-capitalize'>Product Part NO</span> - $row[product_partno]<br>
                                                        <span class='text-primary text-capitalize'>Description</span> - $row[product_descr]<br>
                                                        <span class='text-primary text-capitalize'>Warehouse</span> - $row[warehouse]<br>
                                                        <span class='text-primary text-capitalize'>Stock Level</span> - $stock_level<br>
                                                        <span class='text-primary text-capitalize'>Minimum Stock Level</span> - $minimum_stock_level<br>
                                                </div>
                                        </li>
                                </div>";
                }
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