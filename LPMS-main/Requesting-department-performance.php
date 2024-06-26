<div class="card">
            <div class="card-body">
              <h5 class="card-title">Requesting Department Performance</h5>

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab4" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" href='#bordered-home4' id="home-tab4" data-bs-toggle="tab" data-bs-target="#bordered-home4" type="button" role="tab" aria-controls="home" aria-selected="true" title="Average Planning = date item needed by - requested date (in days and hours)" >Average Planning </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href='#bordered-profile4' id="profile-tab4" data-bs-toggle="tab" data-bs-target="#bordered-profile4" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1"  title='Product types purchased, frequency of purchase, quantities, amount in birr, purchase history'> Product Types Purchased</a>
                </li>
               
              </ul>
              <div class="tab-content pt-2" id="borderedTabContent4">
                <div class="tab-pane fade active show" id="bordered-home4" role="tabpanel" aria-labelledby="home-tab4">
                <div class="row pd-3">
                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        
                         </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Total Purchased</th>
                                    <th scope="col">Avarage Efficiency </th>
                                
                                 
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                           <?php
                          
                          $sql="SELECT department, company, COUNT(*) as total,  AVG(( timetocalculate( final_recieved_date,date_needed_by))) AS Efficency FROM requests
                          INNER JOIN report ON requests.request_id = report.request_id WHERE date_needed_by IS NOT NULL AND final_recieved_date IS NOT NULL 
                          GROUP BY company,department ORDER BY `requests`.`company` ASC;";
                          $stmt_report_dep_Performance = $conn->prepare($sql);
                          $stmt_report_dep_Performance -> execute();
                          $result_report_dep_Performance = $stmt_report_dep_Performance -> get_result();
                          $x=0;
                          if($result_report_dep_Performance -> num_rows>0)
                            while($row = $result_report_dep_Performance -> fetch_assoc())
                            {
                                $in_day = $row['Efficency']/8;
                                echo "
                                  <tr>
                                      <td >".++$x."</td>
                                      <td>".$row['department']."</td>
                                      <td >".$row['company']."</td>
                                      <td >".$row['total']."</td>
                                      <td >".number_format($in_day, 2, ".", ",")." Days. (".number_format($row['Efficency'], 2, ".", ",")." Hr.)</td>
                                  </tr>";
                            }
                           ?>
                        </tbody>
                              
                       </table>
                   </div>
                </div>

                </div>
                <div class="tab-pane fade" id="bordered-profile4" role="tabpanel" aria-labelledby="profile-tab4">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Frequency of Purchase</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Birr (in ETB)</th>
                                    <th scope="col">History</th>
                    
                                </tr>
                                
                            </thead>
                          </thead>
                          <tbody>
                          <?php
                            $total_frequency = 0;
                            $total_quantity=0;
                            $total_birr=0;
                            $ac3 = 'All Complete';
                          $sql="SELECT  requests.company as company,requests.department as department,requests.request_type as type
                          ,count(requests.request_type ) as frequency,sum(quantity) as quantity, sum(after_vat) as birr from requests
                          INNER join purchase_order on requests.request_id=purchase_order.request_id INNER JOIN price_information on 
                          purchase_order.purchase_order_id=price_information.purchase_order_id and purchase_order.cluster_id=price_information.cluster_id
                          WHERE requests.STATUS=? and selected GROUP BY  requests.company ,requests.department,requests.request_type;";
                          $stmt3 = $conn->prepare($sql); 
                          $stmt3->bind_param("s", $ac3);
                          $stmt3->execute();
                          $result = $stmt3->get_result(); 
                          $x=0;
                          if($result->num_rows>0)
                            while($row=$result->fetch_assoc())
                            {
                              $total_frequency += $row['frequency'];
                              $total_quantity += $row['quantity'];
                              $total_birr += $row['birr'];
                              echo"
                              <tr>
                                <td >".++$x."</td>
                                <td>".$row['company']."</td>
                                <td>".$row['department']."</td>
                                <td>".$row['type']."</td>
                                <td>".$row['frequency']."</td>
                                <td>".$row['quantity']."</td>
                                <td>".number_format($row['birr'], 2, ".", ",")."</td>
                                <td><button class='btn btn-success' data-bs-toggle='modal' data-bs-target='#detail_modal' name='".$row['company'].":__:".$row['department'].":__:".$row['type']."' onclick='details(this)'> Detail</button></td>
                              </tr>";
                            } 
                            echo "
                              <tr class='bg-secondary' >  
                                  <td colspan='4' class='text-center text-white'> Total </td>
                                  <td class='text-white'>".$total_frequency."</td>
                                  <td class='text-white' >".$total_quantity."</td>
                                  <td colspan='4' class='text-white' >".$total_birr."</td>
                              </tr>";
                           ?>
        
                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
              
              </div><!-- End Bordered Tabs -->

            </div>
          </div>
           
          <div class="modal fade" id="detail_modal" tabindex="-1" role="document" aria-labelledby="detail_modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header " >
        <h2 class="modal-title" > Report Detail For </h2>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="mreportbody">
         
  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
 
 
          <script>
function details(e){
  var xhr= new XMLHttpRequest();
  var data=e.name.split(':__:' );
  xhr.onload=function(){
    document.getElementById('mreportbody').innerHTML=this.responseText;
  }
  xhr.open("GET","report_ajax.php?fcompany="+data[0]+"&department="+data[1]+"&type="+data[2]);

  xhr.send();
}
</script>


















