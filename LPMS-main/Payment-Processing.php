<div class="card">
            <div class="card-body">
              <h5 class="card-title">Payment Processing</h5>

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab5" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" href='#bordered-home5' id="home-tab5" data-bs-toggle="tab" data-bs-target="#bordered-home5" type="button" role="tab" aria-controls="home" aria-selected="true" title="Average payment processing time per each purchasing department." > Avarage Payment Proccesing  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href='#bordered-profile5' id="profile-tab5" data-bs-toggle="tab" data-bs-target="#bordered-profile5" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1" title="Average processing time per each cashier to handover cheques.">Cashier to Handover Cheques</a>
                </li>
               
              </ul>
              <div class="tab-content pt-2" id="borderedTabContent5">
                <div class="tab-pane fade active show" id="bordered-home5" role="tabpanel" aria-labelledby="home-tab5">
                <div class="row pd-3">
                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        
                         </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Item Category</th>
                                    <th scope="col">Total Request</th>
                                    <th scope="col">Average Time</th>
                             
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                          <?php
                          $sql = "SELECT finance_company,request_type,avg(timetocalculate(cheque_prepared_date,finance_approval_date)) as avgdate,
                          count(*) as total
                          FROM cheque_info Right JOIN
                          purchase_order ON SUBSTRING_INDEX(purchase_order_ids,':-:',1)=purchase_order_id INNER JOIN report 
                          ON purchase_order.request_id=report.request_id INNER JOIN account ON account.Username=cheque_info.created_by 
                          WHERE cheque_prepared_date IS NOT NULL AND finance_approval_date IS NOT NULL AND finance_approval_date < cheque_prepared_date GROUP BY
                          finance_company,request_type ORDER BY finance_company";
                          $stmt_report_Average_payment = $conn->prepare($sql);
                          $stmt_report_Average_payment -> execute();
                          $result_report_Average_payment = $stmt_report_Average_payment -> get_result();
                          $x=0;
                          // echo $conn->error;
                          if($result_report_Average_payment -> num_rows > 0)
                            while($row=$result_report_Average_payment -> fetch_assoc())
                            {
                              echo "<tr>
                                <td>".(++$x)."</td>
                                <td>".$row['finance_company']."</td>
                                <td>".$row['request_type']."</td>
                                <td>".$row['total']."</td>
                                <td>".number_format($row['avgdate'], 2, ".", ",")."&nbsp;&nbsp;Hr.</td>
                     
                              </tr>";
                            }
                  
                          ?> 
                        </tbody>
                              
                       </table>
                   </div>
                </div>

                </div>
                <div class="tab-pane fade" id="bordered-profile5" role="tabpanel" aria-labelledby="profile-tab5">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Cashier</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Average Processing Time </th>
                                  
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                            <?php
                            $sql="SELECT finance_company,account.department, cheque_info.created_by as Cashiers,avg(timetocalculate(cheque_prepared_date,finance_approval_date)) as avgdate
                            FROM cheque_info Right JOIN
                            purchase_order ON SUBSTRING_INDEX(purchase_order_ids,':-:',1)=purchase_order_id INNER JOIN report 
                            ON purchase_order.request_id=report.request_id INNER JOIN account ON account.Username=cheque_info.created_by 
                            WHERE cheque_prepared_date IS NOT NULL AND finance_approval_date IS NOT NULL AND finance_approval_date < cheque_prepared_date GROUP BY cheque_info.created_by, finance_company
                            ORDER BY `avgdate` ASC";
                            $stmt_report_Processing_payment = $conn->prepare($sql);
                            $stmt_report_Processing_payment -> execute();
                            $result_report_Processing_payment = $stmt_report_Processing_payment -> get_result();
                            $x=0;
                            if($result_report_Processing_payment -> num_rows > 0)
                              while($row = $result_report_Processing_payment -> fetch_assoc())
                              {
                                echo "<tr>
                                  <td>".(++$x)."</td> 
                                  <td>".str_replace('.',' ',$row['Cashiers'])."</td>
                                  <td>".$row['finance_company']."</td>
                                  <td>".number_format($row['avgdate'], 2, ".", ",")." &nbsp;&nbsp;Hr.</td>
                                </tr>";
                              }
                            ?>

                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
              
              </div><!-- End Bordered Tabs -->

            </div>
          </div>


















