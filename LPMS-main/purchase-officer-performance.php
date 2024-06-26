<div class="card">
            <div class="card-body">
              <h5 class="card-title">Purchase Officer Performance</h5>

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab2" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" href="#bordered-home2" id="home-tab2" data-bs-toggle="tab" data-bs-target="#bordered-home2" type="button" role="tab" aria-controls="home" aria-selected="true" >Purchaser Performance for Goods Collection  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href="#bordered-profile2" id="profile-tab2" data-bs-toggle="tab" data-bs-target="#bordered-profile2" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Purchaser Performance for Proforma Collection</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link " href="#bordered-contact2" id="contact-tab2" data-bs-toggle="tab" data-bs-target="#bordered-contact2" type="button" role="tab" aria-controls="contact" aria-selected="false" tabindex="-1">Acceptance vs. Rejection from Store/User Department</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link " href="#bordered-ondutyoffduty" id="ondutyoffduty-tab2" data-bs-toggle="tab" data-bs-target="#bordered-ondutyoffduty" type="button" role="tab" aria-controls="ondutyoffduty" aria-selected="false" tabindex="-1">Onduty vs Offduty</a>
                </li>
              </ul>
              <div class="tab-content pt-2" id="borderedTabContent2">
                <div class="tab-pane fade active show" id="bordered-home2" role="tabpanel" aria-labelledby="home-tab2">
                <div class="row pd-3">
                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        
                         </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 " style='overflow:scroll'>
                        <table  class="table table-striped ">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Purchaser</th>
                                    <th scope="col">Company</th>
                                    <th data-toggle="tooltip" data-placement="bottom" title="Handeled Request From NonVender " >NonVender</th>
                                    <th data-toggle="tooltip" data-placement="bottom" title="Handeled Request From Vender ">Vender</th>
                                    <th data-toggle="tooltip" data-placement="bottom" title="Handeled Request From Vender Plus From NoneVender per each Purchaser ">Total Handled Request</th>
                                    <th data-toggle="tooltip" data-placement="bottom" title="All Requests per Each Company">Total Request</th>

                                <th data-toggle="tooltip" data-placement="bottom" title="Performance From NonVendor ">Nonvender Performance (%)</th>
                                <th data-toggle="tooltip" data-placement="bottom" title="Performance From Vendor" >Vendor Performance (%)</th>
                                <th data-toggle="tooltip" data-placement="bottom" title="Total Performance(Performance From Vendor plus Performance From NonVendor)" >Total Performance (%)</th>
                                <th data-toggle="tooltip" data-placement="bottom" title="Total Hours taken to to collect" >Time to Collect (Hr)</th>
                                <th data-toggle="tooltip" data-placement="bottom" title="Number of Timely Collected goods " >Timely Collected</th>
                                <th data-toggle="tooltip" data-placement="bottom" title="Number of Not Timely Collected goods" >Not Timely</th>
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                          <?php

          $sql="SELECT purchase_officer,l.procurement_company,non_vandors,vandor,handled_request,total,(l.vandor/r.total)*100 as venderperformance,
          (l.non_vandors/r.total)*100 as non_vender_performance,
          (l.handled_request/r.total)*100 as toalPerformance,Time_to_Collect,notontime,ontime FROM ((SELECT purchase_officer,purchase_order.procurement_company,
          SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, 
          COUNT(vendor) AS vandor,count(purchase_officer) as handled_request,
          sum(case when timetocalculate( report.collection_date,collector_assigned_date )*8 > 6 then 1 else 0 end) as notontime,
          sum(case when timetocalculate( report.collection_date,collector_assigned_date )*8 <= 6 then 1 else 0 end) as ontime,
          avg(timetocalculate(report.collection_date ,report.collector_assigned_date  )) as Time_to_Collect
          FROM `purchase_order` inner join requests on purchase_order.request_id=requests.request_id
          INNER join report on requests.request_id=report.request_id WHERE report.collection_date  is not null and report.collector_assigned_date is not null
          group by purchase_officer  
          ORDER BY `purchase_order`.`procurement_company` ASC) AS l  INNER JOIN 
          (SELECT procurement_company, COUNT(*) AS total FROM purchase_order INNER JOIN report on report.request_id=purchase_order.request_id WHERE report.collection_date IS NOT Null and report.collector_assigned_date IS NOT null
          GROUP BY procurement_company) AS r) WHERE l.procurement_company=r.procurement_company;";
          $stmt_report_Performance = $conn->prepare($sql);
          $stmt_report_Performance -> execute();
          $result_report_Performance = $stmt_report_Performance -> get_result();
          $x=0;
          if($result_report_Performance -> num_rows>0)
            while($row = $result_report_Performance->fetch_assoc())
            {
              echo "<tr>
                <td>".(++$x)."</td>
                <td>".str_replace('.',' ',$row['purchase_officer'])."</td>
                <td>".$row['procurement_company']."</td>
                <td>".$row['non_vandors']."</td>
                <td>".$row['vandor']."</td>
                <td>".$row['handled_request']."</td>
                <td>".$row['total']."</td>
                <td>".number_format($row['non_vender_performance'], 2, ".", ",")."</td>
                <td>".number_format($row['venderperformance'], 2, ".", ",")."</td>
                <td>".number_format($row['toalPerformance'], 2, ".", ",")."</td>
                <td>".number_format($row['Time_to_Collect'], 2, ".", ","). "&nbsp;&nbsp;hr</td>
                <td>".$row['ontime']."</td>
                <td>".$row['notontime']."</td>
              </tr>";
            }
      ?>
                         </tbody>

                       </table>
                   </div>
                </div>

                </div>
                <div class="tab-pane fade" id="bordered-profile2" role="tabpanel" aria-labelledby="profile-tab2">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                            <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Purchaser</th>
                                    <th scope="col">Company</th>
                                    <th scope="col"> From NonVender</th>
                                    <th scope="col">Handeled Request From Vender</th>
                                    <th scope="col">Total Handled Request</th>
                                    <th scope="col">Total Request</th>

                                <th scope="col">Nonvender Performance (%)</th>
                                <th scope="col">Vendor Performance (%)</th>
                                <th scope="col">Total Performance (%)</th>
                                <th scope="col">Time to Collect (Hr)</th>
                                <th scope="col">Timely Collected</th>
                                <th scope="col">Not Timely</th>
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                            <?php 
                            $sql = "SELECT purchase_officer,l.procurement_company,non_vandors,vandor,handled_request,total,(l.vandor/r.total)*100 as venderperformance,
                            (l.non_vandors/r.total)*100 as non_vender_performance,
                            (l.handled_request/r.total)*100 as toalPerformance,Time_to_Collect,notontime,ontime FROM ((SELECT purchase_officer,purchase_order.procurement_company,
                            SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, 
                            COUNT(vendor) AS vandor,count(purchase_officer) as handled_request,
                            sum(case when timetocalculate( report.performa_generated_date,officer_assigned_date )*8 > 6 then 1 else 0 end) as notontime,
                            sum(case when timetocalculate( report.performa_generated_date,officer_assigned_date )*8 <= 6 then 1 else 0 end) as ontime,
                            avg(timetocalculate(report.performa_generated_date ,report.officer_assigned_date  )) as Time_to_Collect
                            FROM `purchase_order` inner join requests on purchase_order.request_id=requests.request_id
                            INNER join report on requests.request_id=report.request_id WHERE report.performa_generated_date  is not null and report.officer_assigned_date is not null
                            group by purchase_officer  
                            ORDER BY `purchase_order`.`procurement_company` ASC) AS l  INNER JOIN 
                            (SELECT procurement_company, COUNT(*) AS total FROM purchase_order INNER JOIN report on report.request_id=purchase_order.request_id WHERE report.performa_generated_date IS NOT Null and report.officer_assigned_date IS NOT null
                            GROUP BY procurement_company) AS r) WHERE l.procurement_company=r.procurement_company;";
                            $stmt_report_Performance_2 = $conn->prepare($sql);
                            $stmt_report_Performance_2 -> execute();
                            $result_report_Performance_2 = $stmt_report_Performance_2 -> get_result();
                            $x=0;
                            if($result_report_Performance_2 -> num_rows>0)
                              while($row = $result_report_Performance_2 -> fetch_assoc())
                              {
                              echo "
                              <tr>
                                <td>".(++$x)."</td>
                                <td>".$row['purchase_officer']."</td>
                                <td>".$row['procurement_company']."</td>
                                <td>".$row['non_vandors']."</td>
                                <td>".$row['vandor']."</td>
                                <td>".$row['handled_request']."</td>
                                <td>".$row['total']."</td>
                                <td>".number_format($row['non_vender_performance'], 2, ".", ",")."</td>
                                <td>".number_format($row['venderperformance'], 2, ".", ",")."</td>
                                <td>".number_format($row['toalPerformance'], 2, ".", ",")."</td>
                                <td>".number_format($row['Time_to_Collect'], 2, ".", ","). "&nbsp;&nbsp;hr</td>
                                <td>".$row['ontime']."</td>
                                <td>".$row['notontime']."</td>
                              </tr>";
                            }
                            ?>
                         

                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
                <div class="tab-pane fade " id="bordered-contact2" role="tabpanel" aria-labelledby="contact-tab2">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                                <tr>
                                    <th scope="col">#</th>      
                                    <th scope="col">Company</th>
                                    <th scope="col">Accepted</th>
                                    <th scope="col">Rejected</th>
                                    <th scope="col">Total Request</th>
                                    <th scope="col">Accepted (%)</th>
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                          <?php
                            $c1 = 'requests';
                            $ac1 = 'All Complete';
                            $sql= "SELECT company,sum(CASE WHEN opperation='Purchased Item Approved' THEN 1 else 0 END) AS Accepted, COUNT(CASE WHEN opperation ='Purchased Item Rejected' THEN 1 END) AS Rejected,COUNT(*) AS Total,
                            100*(sum(CASE WHEN opperation='Purchased Item Approved' THEN 1 else 0 END))/COUNT(*) as AcceptedPercent FROM `recorded_time` Inner JOIN 
                            requests on recorded_time.for_id=requests.request_id   WHERE `database_name` =?  and status=? group by company;";
                            $stmt2 = $conn -> prepare($sql); 
                            $stmt2 -> bind_param("ss", $c1,$ac1);
                            $stmt2 -> execute();
                            $result = $stmt2 -> get_result(); 
                            $x=0;
                            if($result -> num_rows>0)
                              while($row = $result -> fetch_assoc())
                              {
                                echo"
                                <tr>
                                  <td>".(++$x)."</td>
                                  <td>".($row['company'])."</td>
                                  <td>".($row['Accepted'])."</td>
                                  <td>".($row['Rejected'])."</td>
                                  <td>".($row['Total'])."</td>
                                  <td>".number_format($row['AcceptedPercent'], 2, ".", ",")."</td>
                                </tr>
                                ";
                              }
                            ?>

                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
                <div class="tab-pane fade" id="bordered-ondutyoffduty" role="tabpanel" aria-labelledby="ondutyoffduty-tab2">
                <div class="row pd-3">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <table  class="table table-striped">
                            <thead class="tbl_header_style">
                            <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Purchaser</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">On Duty in Hr.</th>
                                    <th scope="col">Off Duty in Hr.</th>
                                    <th scope="col">Total Duty in Hr.</th>
                               
                                </tr>
                            </thead>
                          </thead>
                          <tbody>
                          //  <?php 
                            // $c2 = 'temp_account';
                            // $sql="select purchase_officer,company,GetGoodsCollectionTime(purchase_officer) as dutyhour ,timetocalculate(CURRENT_DATE,'2022-12-15 11:21:46')-GetGoodsCollectionTime(purchase_officer) as offdutyhour  ,timetocalculate(CURRENT_DATE,'2022-12-15 11:21:46') as totaldutyhour  from purchase_order INNER join report on
                            // purchase_order.request_id=report.request_id WHERE collection_date is not null and collector_assigned_date is not null 
                            // and officer_assigned_date is not null and performa_generated_date is not null and purchase_officer!=?
                            // GROUP by purchase_officer;";
                            // $stmt1 = $conn -> prepare($sql);
                            // $stmt1 -> bind_param("s", $c2);
                            // $stmt1 -> execute();
                            // $result = $stmt1 -> get_result();
                            // $x=0;
                            // if($result -> num_rows>0)
                            //   while($row = $result -> fetch_assoc())
                            //     {
                            //       echo "
                            //       <tr>
                            //         <td>".(++$x)."</td>
                            //         <td>".str_replace('.',' ',($row['purchase_officer']))."</td>
                            //         <td>".($row['company'])."</td>
                            //         <td>".number_format($row['dutyhour'], 2, ".", ",")."</td>
                            //         <td>".number_format($row['offdutyhour'], 2, ".", ",")."</td>
                            //         <td>".number_format($row['totaldutyhour'], 2, ".", ",")."</td>
                            //       </tr>";
                            //     }
                            // ?>
                            
                           

                         </tbody>

                       </table>
                   </div>
                </div>
                </div>
              </div><!-- End Bordered Tabs -->

            </div>
          </div>


















