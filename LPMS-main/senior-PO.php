<div class="card">
            <div class="card-body">
              <h5 class="card-title">Senior Purchase Officer Performance</h5>

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab3" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" href='#bordered-home3' id="home-tab3" data-bs-toggle="tab" data-bs-target="#bordered-home3" type="button" role="tab" aria-controls="home" aria-selected="true" title="Average time taken for comparison per each purchasing department." >Average Time Taken for Comparison</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href='#bordered-profile3' id="profile-tab3" data-bs-toggle="tab" data-bs-target="#bordered-profile3" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1" title=" Standard time to create comparison after POs have been received">Standard Time to Create Comparison</a>
                </li>
               
              </ul>
              <div class="tab-content pt-2" id="borderedTabContent3">
                <div class="tab-pane fade active show" id="bordered-home3" role="tabpanel" aria-labelledby="home-tab3">
                <div class="row pd-3">
                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        
                         </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Handled CS</th>
                                    <th scope="col">Total CS</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Average in Hr</th>
                                 
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                              
                          <?php 
                            $sql="select l.name as name,l.handled_cs as handled_cs,r.total as total,l.procurement_company as procurement_company,m.AVG_date as AVG_date from ((SELECT compiled_by as Name, COUNT(*)  as handled_cs , procurement_company from cluster GROUP BY compiled_by) as l INNER JOIN (
                              select COUNT(*)  as total,procurement_company from cluster GROUP By procurement_company) as r
                                        ) 
                                        INNER join (
                                           SELECT cluster.compiled_by as name, AVG(timetocalculate(compsheet_generated_date, report.performa_confirm_date)) as AVG_date FROM `report` INNER JOIN purchase_order on report.request_id=purchase_order.request_id INNER join cluster on cluster.id=purchase_order.cluster_id GROUP by cluster.compiled_by
                                            ) as m 
                                        WHERE l.procurement_company=r.procurement_company and l.Name=m.name;";
                                        $stmt_report_spo_Performance = $conn->prepare($sql);
                                        $stmt_report_spo_Performance -> execute();
                                        $result_report_spo_Performance = $stmt_report_spo_Performance -> get_result();
                                        $x=0;
                                        if($result_report_spo_Performance -> num_rows>0)
                                          while($row=$result_report_spo_Performance -> fetch_assoc())
                                          {
                                            echo "
                                              <tr> 
                                                <td>".++$x."</td>
                                                <td>".$row['name']."</td>
                                                <td>".$row['handled_cs']."</td>
                                                <td>".$row['total']."</td>
                                                <td>".$row['procurement_company']."</td>
                                                <td>".number_format($row['AVG_date'], 2, ".", ",")."</td>
                                              </tr>";
                                          }
                            ?>
                        </tbody>
                              
                       </table>
                   </div>
                </div>

                </div>
                <div class="tab-pane fade" id="bordered-profile3" role="tabpanel" aria-labelledby="profile-tab3">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Handled CS</th>
                                    <th scope="col">Total CS</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Average in Hr</th>
                    
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                               <td colspan="6" class="text-center py-2">Coming Soon</td>
                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
              
              </div><!-- End Bordered Tabs -->

            </div>
          </div>


















