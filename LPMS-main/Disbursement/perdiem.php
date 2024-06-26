<?php 

session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Cashier" && $_SESSION["role"] != "Director" && $_SESSION["role"] != "Disbursement") header("Location: ../");
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Perdiem requests</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | perdiem");
    sideactive("perdiem_disb");
</script>
 <div id="main">
   <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Perdiem and travel advance</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Prepare check</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<?php
if(isset($_POST['travelapproval'])){
  $approvedby = $_SESSION["username"];
  $actiontaken = $_POST['checkstatus'];
    $checkid = $_POST['checkid'];
  if($actiontaken == 'Approved')
    $status = 'Cheque approved';
  else if($actiontaken == 'Rejected') 
    $status = 'Cheque rejected';

    $checkapproval = "UPDATE perdiem SET travel_approved_by = ?, `status` = ? where id = ?";
    $stmt_travel_perdiem = $conn_fleet -> prepare($checkapproval);
    $stmt_travel_perdiem -> bind_param("ssi",$approvedby ,$status ,$checkid);
    $query2 = $stmt_travel_perdiem -> execute();

    $check = "UPDATE traveladvance SET travel_approvalstatus = ? WHERE perdime_id = ?";
    $stmt_travel_status_perdiem = $conn_fleet -> prepare($check);
    $stmt_travel_status_perdiem -> bind_param("si",$actiontaken ,$checkid);
    $query2 = $stmt_travel_status_perdiem -> execute();
    if($query OR $query2){
      $_SESSION['success'] = 'Perdiem request approved successfully';
    }
}
?>
        <?php
        $str="";
        $sql_clus =  "SELECT * FROM perdiem where `status` = 'Cheque prepared'";
        $stmt_perdiem = $conn_fleet->prepare($sql_clus);
        $stmt_perdiem->execute();
        $result_perdiem = $stmt_perdiem->get_result(); 
        if($result_perdiem->num_rows>0)
        while($row = $result_perdiem->fetch_assoc())
        {
            $id = $row['id'];
            $jobid = $row['job_id'];
            $request_date = $row['dateofrequest'];
            $company = $row['company'];
            $role = $row['role'];
            $department =  $row['fromdepartment'];
            $reason = $row['reasonfortrip'];
            $subject = $row['subject'];
            $customer = $row['customer_name'];
            $departuredate = $row['departure_date'];
            $returndate = $row['return_date'];
            $departureplace = $row['departure_place'];
            $destination = $row['destination'];
            $driver = $row['driver'];
            $travellers = $row['travellers'];
            $preparedby = $row['prepared_by'];
            $str.= '
            <div class="col-md-6 col-lg-4 col-xl-4 my-4 focus">
            <div class="box">
            <h3>
            Job Id - '.$row['job_id'].'
            </h3>
                <p class="text-start mb-2"><span>From : </span><i><u>'.$role.'</u>, <u>'.$company.'</u>, <u>'.$row['fromdepartment'].'</u></i></p>
                <p class="text-start mb-2"><span>Subject : </span><i>'.$subject.'</i></p>

                <p class="text-start mb-2"><span>Departure date : </span><i>'.$departuredate.'</i></p>
                <p class="text-start mb-2"><span>Return date : </span><i>'.$returndate.'</i></p>

                <p class="text-start mb-2"><span>Departure place : </span><i>'.$departureplace.'</i></p>
                <p class="text-start mb-2"><span>Destination : </span><i>'.$destination.'</i></p>

                <p class="text-start mb-2"><span>Customer Name : </span><i>'.$customer.'</i></p>
                <p class="text-start mb-2"><span>Reason for travel : </span><i>'.$reason.'</i></p> 

                <p class="text-start mb-2"><span>Travellers : </span><i>'.$travellers.'</i></p>  
                <p class="text-start mb-2"><span>Driver : </span><i>'.$driver.'</i></p>                            
                <p class="text-start mb-2"><span>Prepared By : </span><i>'.$preparedby.'</i></p>                             

              <button class="mx-auto btn btn-outline-primary btn-sm mb-3" type="submit"  name="detail" value="'.$id.'" data-bs-toggle="modal" data-bs-target="#fullscreenModal">View Detail</button>   
                </div>
            </div> 
                ';
        } ?>
        <form method="POST" action="perdiem.php">
        <?php 
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no perdiem requests</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>   
    <button data-bs-toggle="modal" id='modal_open' type="button" data-bs-target="#fullscreenModal" class="btn btn-outline-primary float-end btn-sm me-5 box shadow d-none">View Detail</button>
     </form>

     <div class="modal fade" id="fullscreenModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Perdiem request and travel advance</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

          <form method="POST" action="perdiem.php" class="row">
            <div class="modal-body">                    
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title mb-3 text-center">Perdiem request</h5>
             <div class="row">
              <!-- Vertical Form -->          
              <?php 
                  if(isset($_POST['detail'])){
                    $i = $_POST['detail'];
                    $totalkm = 0;
                  $detail = "SELECT * FROM perdiem where id = ?";
                  $stmt_perdiem_id = $conn_fleet->prepare($detail);  
                  $stmt_perdiem_id->bind_param("i", $i);
                  $stmt_perdiem_id->execute();
                  $result_perdiem_id = $stmt_perdiem_id->get_result(); 
                  if($result_perdiem_id->num_rows > 0);
                    while($detailrow = $result_perdiem_id->fetch_assoc()){
                      $Customersite = ($detailrow['customersite_km'] * $detailrow['tripperday'] * $detailrow['daysof_stay']);
                      $distanckm = $detailrow['round_distance_km'];
                      $totalkm = $Customersite + $distanckm;
                        ?>
                <input type="hidden" name='checkid' value="<?php echo $detailrow['id'] ?>">        
                <div class="col-sm-6 mb-2">
                  <label for="jobid" class="form-label me-3"><b>Job Id:</b></label>               
                  <span id="jobid"><?php echo $detailrow['job_id']   ?></span>                            
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputEmail4" class="form-label me-3"><b>Date of request:</b></label>
                  <span id="requestdate"><?php echo $detailrow['dateofrequest']   ?></span> 
                </div>
                <div class="col-sm-6 mb-3">
                  <label for="inputPassword4" class="form-label me-3"><b>From:</b></label>
                  <span  id="from"><?php echo $detailrow['role']   ?>,<?php echo $detailrow['company']   ?>,<?php echo $detailrow['fromdepartment']   ?></span>
                </div>
                <div class="col-sm-10 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Subject:</b></label>
                  <span id="subject"><?php echo $detailrow['subject']   ?></span>
                </div>
                <div class="col-sm-10 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Customer Name:</b></label>
                  <span id="customername"><?php echo $detailrow['customer_name']   ?></span>
                </div>
                <div class="col-sm-12 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Reason For Travel:</b></label>
                 <textarea class="form-control"   rows = "4" id="reasonfortravel" readonly><?php echo $detailrow['reasonfortrip']  ?></textarea>
                </div>
                <div class="col-sm-6 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Travellers:</b></label>
                  <span id="travellers"><?php echo $detailrow['travellers']   ?></span>
                </div>
                <div class="col-sm-6 mb-3">
                  <label for="inputAddress" class="form-label me-3"><b>Driver:</b></label>
                  <span id="driver"><?php echo $detailrow['driver']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Departure Date:</b></label>
                  <span id="departuredate"><?php echo $detailrow['departure_date']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Return Date:</b></label>
                  <span id="returndate"><?php echo $detailrow['return_date']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Days of stay:</b></label>
                  <span id="daysofstay"><?php echo $detailrow['daysof_stay']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Departure place:</b></label>
                  <span id="Departureplace"><?php echo $detailrow['departure_place']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Destination:</b></label>
                  <span id="Destination"><?php echo $detailrow['destination']   ?></span>
                </div>
                <div class="col-sm-4 mb-2">
                  <label for="inputAddress" class="form-label me-3"><b>Distance:</b></label>
                  <span id="Distance"><?php echo $detailrow['round_distance_km']   ?></span>
                </div>
                 
                <div class="col-sm-12 mb-2">
                <fieldset class="row border rounded-3 p-3">
                  <legend class="col-form-label float-none w-auto">Total km</legend>
                  <p class='text-start mb-2'>Trip type: <span> </span></p> 
                   
                  <div class="row mb-3">
                <label class="col-sm-2 col-form-label">@Customer site:</label>
                   <div class="col-sm-3">
                   <div class="input-group mb-3"> 
                    <input value="<?php echo $detailrow['customersite_km']   ?>"  type="text" aria-describedby="basic-addon4" class="form-control two" readonly>  
                      <span class="input-group-text" id="basic-addon4">Km</span>             
                    </div>
                  </div>
                <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                    <input value="<?php echo $detailrow['tripperday']   ?>"   type="text" aria-describedby="basic-addon4" class="form-control two" readonly>  
                      <span class="input-group-text" id="basic-addon4">Trip perday</span>             
                  </div>
                </div>
                <div class="col-sm-3">
                <div class="input-group mb-3"> 
                    <input value="<?php echo $detailrow['daysof_stay']   ?>"  type="text" aria-describedby="basic-addon4" class="form-control two" readonly>  
                      <span class="input-group-text" id="basic-addon4">Days</span>             
                </div>
              </div>
            </div>  
            <div class="col-sm-4">
            <div class="input-group mb-3"> 
              <label for="inputAddress" class="form-label  me-3">Total Km:</label>
                <input value="<?php echo  $totalkm   ?>"  type="text" aria-describedby="basic-addon4" class="form-control two" readonly>
                 <span class="input-group-text" id="basic-addon4">Km</span> 
            </div>
            </div>
                </fieldset>
             
              <div class="col-sm-10 mt-2">
                  <label for="inputAddress" class="col-form-label"><b>Prepared By:</b></label>
                  <span id="customername"><?php echo $detailrow['prepared_by']   ?></span>
                </div> 
            
                 <?php 
                $traveladvance = "SELECT * from traveladvance where perdime_id = ?";
                $stmt_traveladvance_id = $conn_fleet->prepare($traveladvance);  
                $stmt_traveladvance_id->bind_param("i", $i);
                $stmt_traveladvance_id->execute();
                $result_traveladvance_id = $stmt_traveladvance_id->get_result();
                   if($result_traveladvance_id->num_rows > 0);
                    while($travelrow = $result_traveladvance_id->fetch_assoc()){
                      $idd = $travelrow['id'];
                      $reason = $travelrow['reason'];
                      $rate = $travelrow['rate'];
                      $days = $travelrow['days'];
                      $totalcost = $travelrow['birr'];          
                  ?>            
                <div style = "border-color: #719ECE;box-shadow: 0 0 10px" class="card col-lg-12 mt-3 mb-2 mx-auto">
                  <div class="card-body">
              <h5 class="card-title mt-3 mb-3 text-center">Travel Advance</h5>
              <div class="row mb-2">              
                <div class="col-sm-6 mb-2">
                  <label for="nameofemployee" class="form-label me-3"><b>Name of Employee:</b></label>               
                 <span id="nameofemployee"><?php echo $travelrow['name_of_employee']   ?></span>                         
              </div>
               
              <div class="col-sm-6 mb-2">               
                <label for="inputEmail" class="col-form-label"><b>Role in this trip:</b></label>
                  <span id="roleinthistrip"><?php echo $travelrow['roleonthistrip']   ?></span>   
                 </div> 
              </div> 
                 
                 <div class="col-sm-12 mb-2">
                <fieldset class="row border rounded-3 p-3">
                  <legend class="col-form-label float-none w-auto">Amount Required</legend>
                                 
                    <?php $split1 = explode('::',$reason);
                          $split2 = explode('::',$rate);
                          $split3 = explode('::',$days);
                          $split4 = explode('::',$totalcost);
                        
                        for($j = 0;$j < count($split1); $j++){
                    ?>
              <div class="row mb-2"> 
                <div class="col-md-3">
                  <div class="form-floating mb-3">
                    <input value="<?php echo $split1[$j] ?>"  type="text"  class="form-control" id="reason" aria-label="State" readonly>                 
                    <label for="reason">Reason</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="input-group mb-3"> 
                  <input value="<?php echo $split2[$j] ?>"  type="text"  aria-describedby="basic-add" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add">Rate</span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="input-group mb-3"> 
                  <input value="<?php echo $split3[$j] ?>"  type="text" aria-describedby="basic-add1" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add1">Days</span>             
                  </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-floating input-group mb-3"> 
                    <input value="<?php echo $split4[$j] ?>" type="text" aria-describedby="basic-add2" class="form-control" readonly>  
                    <span class="input-group-text" id="basic-add2">Total cost</span> 
                    <label for="floatingSelect">In Birr</label>            
                  </div>
                  </div>
                 </div>
                 <?php } ?>

                </fieldset>
                </div> 
                <div class="row mt-3"> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cheque Number:</b></label>               
                       <input type="text" value="<?php echo  $travelrow['cheque_number']  ?>"  class="form-control" readonly>                           
                        </div> 
                        <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Cpv Number:</b></label>               
                       <input type="text" value="<?php echo  $travelrow['cpv_number']  ?>"  class="form-control" readonly>                           
                        </div> 
                   <div class="col-sm-4 mb-2">
                      <label for="jobid" class="form-label me-3"><b>Bank:</b></label>               
                      <input type="text" value="<?php echo  $travelrow['bank']  ?>"  class="form-control" readonly>  
                        </div>              
                   </div>
                
              </div>
           </div>                       
                <?php  
                   } ?>
                   
                  <div class="text-center mt-3">
                  <button type='button' class="btn btn-outline-success">
                      <input class="form-check-input" type="radio" name="checkstatus" id="aRadios" value="Approved" required>
                          <label class="form-check-label" for="aRadios">
                              Approve
                              <i class="fas fa-check-circle"></i></label>
                            </button>
                  
                  <button type='button' class="btn btn-outline-danger">
                      <input class="form-check-input" type="radio" name="checkstatus" id="aRadios" value="Rejected" required>
                          <label class="form-check-label" for="aRadios">
                              Reject
                              <i class="bi bi-x-circle"></i></label>
                            </button><!-- continue from here  -->
                </div>
                 <?php }
               }   
                 ?>
            </div>
          </div>
        </div>
      </div> 
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" name = 'travelapproval' class="btn btn-primary">Take Action</button>
              </div>         
             </div>
            </form><!-- Vertical Form -->    
          </div>
        </div><!-- End Full Screen Modal-->
        </div>

        <?php include '../footer.php';?>
<script>
<?php 
if(isset($_POST['detail']))
{?>
  document.getElementById('modal_open').click();
  <?php
  unset($_POST['detail']);
}?>
</script>