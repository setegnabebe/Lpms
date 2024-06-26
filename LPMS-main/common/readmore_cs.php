<?php 
session_start();
    include "../connection/connect.php";
    $per_page=(isset($_GET['per_page']))?$_GET['per_page']:40;
    $page_num=(isset($_GET['page_num']))?$_GET['page_num']:1;
    $offset=($page_num-1)*$per_page;
    $sql_clus = $_SESSION['query_cs']." LIMIT $per_page OFFSET $offset";
    $stmt_cluster_readmore = $conn->prepare($sql_clus);
    $stmt_cluster_readmore -> execute();
    $result_cluster_readmore = $stmt_cluster_readmore -> get_result();
    if($result_cluster_readmore -> num_rows > 0)
    while($r_clus = $result_cluster_readmore -> fetch_assoc())
    {
        $stmt2 = $conn->prepare("SELECT count(DISTINCT `providing_company`) AS companies FROM `price_information` where `cluster_id`='".$r_clus['id']."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($co_count);
        $stmt2->fetch();
        $stmt2->close();
        $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
        $stmt2->execute();
        $stmt2->store_result();
        $stmt2->bind_result($r_type,$num_req);
        $stmt2->fetch(); 
        $stmt2->close();
        echo "
            <div class='col-md-6 col-lg-3 my-4'>
                <div class='box'>
                    <h3 class='text-capitalize'>".$r_clus['type']."
                    <button type='button' title='cluster' class='btn btn-outline-secondary border-0 float-end' name='print_".$r_clus['id']."' onclick='print_page(this)'>
                    <i class='text-dark fas fa-print'></i>
                    </button>
                    <span class='small text-secondary d-block mt-2'>$r_type</span></h3>
                    <ul>
                        
                        <li>Number of Items Requested : ".$num_req."</li>
                        <li>Number Of Companies : ".$co_count."</li>
                        <li>Total Price : ".number_format($r_clus['price'], 2, ".", ",")."</li>
                        <li>Status : ".$r_clus['status']."</li>
                    </ul>
                        <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                </div>
            </div>
            ";
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