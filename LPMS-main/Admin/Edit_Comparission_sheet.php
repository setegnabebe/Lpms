    <div class='mx-auto' data-aos='fade-left'>
    <?php 
    // $dbsss = ["cluster","cluster_deleted"];
    $dbsss = array(""=>"3", "_deleted"=>"4");
    // foreach($dbsss as $d => $v) 
    // {
        $d="";$v=3;
        ?>
        <h3 class="text-center my-2"><?php echo ($d=="")?"Manage Comparison Sheets":"Deleted Comparison Sheets";?></h3>
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table<?php echo $v?>">
                <thead class="table-primary">
                    <tr>
                        <th>Type</th>
                        <th>For</th>
                        <th>Items</th>
                        <th>Companies</th>
                        <th>Made By</th>
                        <th>Total Price</th>
                        <th>Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                     $sql_clus = "SELECT * FROM `cluster$d` ORDER BY id DESC";
                     $stmt_cluster = $conn->prepare($sql_clus); 
                     $stmt_cluster -> execute();
                     $result_clus = $stmt_cluster -> get_result();
                     if($result_clus->num_rows>0)
                     while($r_clus = $result_clus->fetch_assoc())
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
                        $ch =($r_clus['status'] == 'open')?" checked":"";
                            echo "
                            <tr id='row_".$r_clus['id']."'>
                            <td class='text-capitalize'>".$r_type."</td>
                                <td class='text-capitalize' id='forr_".$r_clus['type']."'>".$r_clus['type']."</td>
                                <td class='text-capitalize'>".$num_req."</td>
                                <td class='text-capitalize'>".$co_count."</td>
                                <td class='text-capitalize'>".$r_clus['compiled_by']."</td>
                                <td class='text-capitalize'>".((is_null($r_clus['price']))?" - ":number_format($r_clus['price'], 2, ".", ","))."</td>
                                <td class='text-center'>
                                    <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'><i class='fas fa-external-link-alt'></i></button>
                                    <!--<button type='button' id='".$r_clus['id']."' class='btn btn-sm btn-outline-primary' onclick='editaccount(this)' data-bs-toggle='modal' data-bs-target='#editaccountmodal'><i class='fa fa-edit'></i></button>-->
                                    <button type='button' class='btn btn-sm btn-outline-danger' name='".$r_clus['id']."' onclick='delete_item(this,this)'><i class='fa fa-trash'></i></button>
                                    <!--<button type='button' class='col-2 btn btn-outline-danger border-0' name='".$r_clus['id']."' onclick='delete_item(this,'cs')'><i class='far fa-trash-alt'></i></button>-->
                                </td>
                                <td class='d-none text-danger' id = 'warn_".$r_clus['id']."'></td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    <?php //}?>
    </div>