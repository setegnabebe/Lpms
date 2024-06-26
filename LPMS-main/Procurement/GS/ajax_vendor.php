<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include "../../common/functions.php";
    include "../../common/details.php";
    function divcreate($str)
    {
        echo "
        <div class='card'>
        <div class='card-body'>
            $str
        </div>
        </div>
        ";
    }
    ?>
    <?php
    $str='';
    $request_id=explode("_",$_GET['request_Details'])[1];
    $na_t=explode("_",$_GET['request_Details'])[0];
    $sent_vendors = [];
    if(isset($_SESSION['emailed_vendors']))
    {
        $sent_vendors = explode(",",$_SESSION['emailed_vendors']);
    }
    $type = na_t_to_type($conn,$na_t);
    $like_type = "%$type%";
    $sql_vendor_specific = "SELECT * FROM `prefered_vendors` where catagory LIKE ?";
    $stmt_vendor_specific = $conn->prepare($sql_vendor_specific);
    $stmt_vendor_specific->bind_param("s", $like_type);
    $stmt_vendor_specific->execute();
    $result_vendor_specific = $stmt_vendor_specific->get_result();
    if($result_vendor_specific->num_rows == 0)
    {
        $none =  "<div class='py-5 pricing'>
                    <div class='section-title text-center py-2  alert-primary rounded'>
                        <h3 class='mt-4'>There are No Vendors Registered</h3>
                    </div>
                </div>";
    }
    else
        $none = "";
    $stmt_request->bind_param("i", $request_id);
    $stmt_request->execute();
    $result_request = $stmt_request->get_result();
    if($result_request->num_rows>0)
    {
        while($row = $result_request->fetch_assoc())
        {
            $str.="<ul class= 'list-group list-group-flush'>
            <h4 class='text-capitalize mb-2 text-center text-primary'> ".$_SESSION['company']."</h4>
            <h5 class='text-capitalize mb-2 text-center'>Item - ".$row['item']."</h5>
            <span class='text-capitalize text-secondary mb-2 text-center'><i class='text-primary'>Catagory </i>- $type";
            
            while($row2 = $result_vendor_specific->fetch_assoc())
            {
                $sent = "";
                $check_string = $row2['id']."_".$row['request_id']."_".$na_t;
                $reason = "Vendor_$row2[vendor]_$row[request_id]";
                $stmt_email_reason_get -> bind_param("s", $reason);
                $stmt_email_reason_get -> execute();
                $result_email_reason = $stmt_email_reason_get -> get_result();
                if($result_email_reason->num_rows>0)
                {
                    $sent = " <i class='text-success'>Email Sent!</i>";
                }
                // if(in_array($check_string,$sent_vendors))
                // {
                //     $sent = " <i class='text-success'>Email Sent!</i>";
                // }
                
    //             $details = "<li class='list-group-item list-group-item-primary mb-3'><b class='py-2'>Details</b></li>
    //             <div class='row m-auto w-100 text-center'>";
    //             $details_list = explode(":",$row2['details']);
    //             for($i=0; $i<sizeof($details_list); $i=$i+2)
    //             {
    // $details.=" <span class='col-sm-12 col-md-5 mx-auto my-1'>
    //                 <b class='text-primary'>".$details_list[$i]." : </b>".$details_list[$i+1]."
    //             </span>";
    //             }
    //             $details.="</div>";

                $items_list = "<li class='list-group-item list-group-item-primary mb-3'><b class='py-2'>Provided Items</b></li>
                <div class='row m-auto w-100 text-center'>";
                $items = explode(",",$row2['items']);
                foreach($items as $item)
                {
                    $items_list.="<li class='list-group-item list-group-item-light col-sm-4 col-md-3 mx-2 my-1'><i class='text-primary'></i>$item</li>";
                }
                $items_list.="</div>";
                // if verified menamen      //$str .=(!is_null($row['collector']))?" <span class='fw-bold'><i class='fa fa-check-circle text-primary'></i> Officer Assigned</span>":"";
                $str .="
                        <div class='my-3'>
                            <li class='list-group-item list-group-item-secondary mb-3 row'>
                                <div id='vendor_title_$row2[id]' data-bs-toggle='collapse' data-bs-target='#vendor".$row2['id']."' role='button' aria-expanded='false' aria-controls='vendor".$row2['id']."' >
                                    <span class='text-primary text-capitalize'>Vendor - </span>".$row2['vendor']."
                                    $sent
                                    <button class='btn btn-danger float-end' id='".$row2['id']."_".$row['request_id']."_$na_t' data-bs-toggle='modal' data-bs-target='#email_modal' type='button' onclick='mail(this)'>
                                        <i class='fas fa-envelope '></i>
                                    </button>
                                </div>
                            </li>
                            <div class='row m-auto w-75 collapse' id='vendor".$row2['id']."'><!--  -->
                                <li class='list-group-item list-group-item-primary mb-4 m-auto' id='contact".$row2['id']."'>
                                    <ul class= 'list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-primary mb-3'><b class='py-2'>Contact Info</b></li>
                                        <div class='row m-auto w-100 text-center'>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-5 mx-auto'><i class='text-primary'>Email : </i>".$row2['email']."</li>
                                            <li class='list-group-item list-group-item-light col-sm-12 col-md-5 mx-auto'><i class='text-primary'>Phone : </i>".$row2['phone']."</li>
                                        </div>
                                    </ul>
                                </li>
                                <li class='list-group-item list-group-item-primary mb-4 m-auto' id='details".$row2['id']."'>
                                    <ul class= 'list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-primary mb-3'><b class='py-2'>Details</b></li>
                                        <div class='row m-auto w-100 text-center'>
                                            <b class='col-10 mx-auto'>".$row2['details']."</b>
                                        </div>
                                    </ul>
                                </li>
                                <li class='list-group-item list-group-item-primary mb-4 m-auto' id='items".$row2['id']."'>
                                    <ul class= 'list-group list-group-flush'>
                                        $items_list
                                    </ul>
                                </li>
                            </div>
                        </div>
                        </ul>";
            }
            divcreate($str.$none);
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