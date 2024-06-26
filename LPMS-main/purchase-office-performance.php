   <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Purchase Office Performance</h5>

            <!-- Bordered Tabs -->
            <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active"  href="#bordered-home" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-home" type="button" role="tab" aria-controls="home" aria-selected="false" tabindex="-1" title="Average time taken to purchase items per category">Average Time Taken</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" href='#bordered-profile' id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-profile" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1" title="Ratio of items bought from vendor list vs. non vendor list">Vendor List vs. Non Vendor List</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" href='#bordered-contact' id="contact-tab" data-bs-toggle="tab" data-bs-target="#bordered-contact" type="button" role="tab" aria-controls="contact" aria-selected="true" title="Ratio of “on time” delivery compared to requested date">Ratio of on Time Delivery</a>
              </li>
            </ul>
            
            <div class="tab-content pt-2" id="borderedTabContent">
              <div class="tab-pane fade active show" id="bordered-home" role="tabpanel" aria-labelledby="home-tab">
              <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' enctype="multipart/form-data">
          
              <div class="row pb-3">
              <div class="col-lg-4">
                  <select  name="companyList" class="form-select" id='company' aria-label="Default select example" onchange='filter(this)'>
                    <option value="all">Company/Branch</option>
                      <?php
                      $query="SELECT DISTINCT company from requests inner Join report on requests.request_id = report.request_id where report.officer_assigned_date IS NOT NULL and report.final_recieved_date IS NOT NULL";
                      $stmt_report_Performance = $conn->prepare($query);
                      $stmt_report_Performance -> execute();
                      $result_report_Performance = $stmt_report_Performance -> get_result();
                      while($row = $result_report_Performance -> fetch_assoc())
                      {
                        $name=$row['company'];
                        ?>
                          <option value="<?php echo $name?>"><?php echo $name?></option>
                <?php } ?>
                    </select> 
                  </div> 
                  <div class="col-lg-4">
                  <select  name="categoryList" id='category' class="form-select" aria-label="Default select example" onchange='filter(this)'>
                    <option value="all">Item Category</option>
                    <?php
                      $sql = "SELECT * FROM catagory";
                      $stmt_catagory = $conn->prepare($sql); 
                      $stmt_catagory -> execute();
                      $result = $stmt_catagory -> get_result();
                      if($result -> num_rows>0)
                        while($row1 = $result->fetch_assoc())
                        {
                          $name1 = $row1['catagory'];
                          ?> 
                          <option value="<?php echo $name1?>"><?php echo $name1?></option>
                  <?php } ?>
                    </select> 
                  </div> 
                  
              </div>
            </form>
              <?php
              if(isset($_POST['view'])){
                  $companyList=$_POST['companyList'];
                  $categoryList=$_POST['categoryList'];
                if($companyList=="" AND $categoryList=="")
                  {
                  $qu11="SELECT * from catagory";
                }else if($companyList!="" AND $categoryList==""){
                  $qu11="SELECT * from catagory";
                }else if($companyList=="" AND $categoryList!=""){
                  $qu11="SELECT * from catagory";
                }else if($companyList!="" AND $categoryList!=""){
                  $qu11="SELECT * from catagory";
                }}else{
                  $qu11="SELECT * from catagory";
                }
              
                ?>
              <table class='table table-striped'>
              <thead class='tbl_header_style'>
                <tr>
                      <th>#</th>
                    <th>Company </th>
                    <th>Item Catagory </th>
                    
                    <th data-toggle="tooltip" data-placement="bottom" title="Total Requests is total purchased requests per each catagory " >Total Requests</th>
                    <th data-toggle="tooltip" data-placement="bottom" title="Avarage Time Taken is avarage time from request date to final recived date based on each company and each request type !" data-content="Some content inside the popover">Average Time Taken
                    </th> 
                    <th>Detail</th> 
                </tr>
                <?php 
                  $stmt_catagory_custom = $conn -> prepare($qu11); 
                  $stmt_catagory_custom -> execute();
                  $result_catagory_custom = $stmt_catagory_custom -> get_result();
                  if($result_catagory_custom -> num_rows == 0)
                  {?>
                    <tr><td colspan="3" style="text-align:center;"><b>No related data with your search query!</b></td></tr>
            <?php }
                  else
                  {
                    while($row11 = $result_catagory_custom->fetch_assoc())
                    {?>
                      <tr><?php 
                    }
                  }?>
                </tr>
            </thead>      
            <tbody id='tbl_body'>
              <?php
              $sql_comp="SELECT DISTINCT company from requests inner Join report on requests.request_id = report.request_id WHERE report.request_date IS NOT NULL and report.final_recieved_date IS NOT NULL";
              $stmt_details = $conn -> prepare($sql_comp); 
              $stmt_details -> execute();
              $result_details = $stmt_details -> get_result();
                $x=0;
                if(mysqli_num_rows($result_details)>0)
                while($r = $result_details -> fetch_assoc()){
                $c = $r['company'];
                $ac = 'All Complete';
                $sql_details_avg = "SELECT requests.company as comp,requests.request_type as type,count(*) as total ,AVG((timetocalculate(report.final_recieved_date ,report.request_date))/8) as avg_days from requests INNER JOIN report on report.request_id=requests.request_id WHERE requests.company=? and status=? and report.request_date IS NOT NULL and report.final_recieved_date  IS NOT NULL GROUP BY requests.request_type;";
                $stmt_details_avg = $conn -> prepare($sql_details_avg); 
                $stmt_details_avg -> bind_param("ss", $c, $ac);
                $stmt_details_avg -> execute();
                $result_details_avg = $stmt_details_avg -> get_result(); 
                if($result_details_avg -> num_rows>0)
                  while($row = $result_details_avg -> fetch_assoc())
                  {
                    echo "<tr>
                    <td>".(++$x)."</td>
                    <td>".$row['comp']."</td>
                    <td>".$row['type']."</td>
                    <td>".$row['total']."</td>
                    <td>".number_format($row['avg_days'], 2, ".", ",")." days (".number_format($row['avg_days']*8, 2, ".", ",")." Hr)</td>";
                    ?> 
                    <td><a href="report.php?id=<?php echo $cu;?>&Graph_company=<?php echo $row['comp'];?>" class='btn btn-success'>Details</a></td> 
                    <?php "</tr>";
                  }
              }
                ?>
              </tbody>              
              </table>
            </div>
              <div class="tab-pane fade" id="bordered-profile" role="tabpanel" aria-labelledby="profile-tab">
              <div class="row">
              <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' enctype="multipart/form-data">
          
          <div class="row pb-3">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
              <select  name="companyList" class="form-select" aria-label="Default select example" onchange="filter1(this)">
                <option value="all">Company/Branch</option>
                  <?php
                    $sql_comp_from_request = "SELECT DISTINCT company from requests";
                    $stmt_comp_from_request = $conn -> prepare($sql_comp_from_request);
                    $stmt_comp_from_request -> execute();
                    $result_comp_from_request = $stmt_comp_from_request -> get_result(); 
                    if($result_comp_from_request -> num_rows > 0)
                      while($row = $result_comp_from_request -> fetch_assoc())
                      {
                        $name=$row['company'];
                        ?>
                          <option value="<?php echo $name?>"><?php echo $name?></option>
                        <?php
                      }
                  ?>
                </select> 
            </div>
            
          </div>
        </form> 
          <?php
          if(isset($_POST['view'])){
              $companyList=$_POST['companyList'];
              $categoryList=$_POST['categoryList'];
            if($companyList=="" AND $categoryList=="")
              {
              $qu11="SELECT * from catagory";
            }else if($companyList!="" AND $categoryList==""){
              $qu11="SELECT * from catagory";
            }else if($companyList=="" AND $categoryList!=""){
              $qu11="SELECT * from catagory";
            }else if($companyList!="" AND $categoryList!=""){
              $qu11="SELECT * from catagory";
            }}else{
              $qu11="SELECT * from catagory";
            }
            ?>
          <table class='table table-striped'>
          <thead class='tbl_header_style'>
            <tr>
                <th>#</th>
                <th>Company</th> 
                <th>From Vendor </th>
                <th>From Non-vendor</th> 
                <th data-toggle="tooltip" data-placement="bottom" title="Total completed Requests is total purchased requests per each company ">Total Requests </th>
              
            </tr>
            <?php 
            $stmt_catagory_custom = $conn -> prepare($qu11); 
            $stmt_catagory_custom -> execute();
            $result_catagory_custom = $stmt_catagory_custom -> get_result();
            if($result_catagory_custom -> num_rows == 0)
            {?>
              <tr><td colspan="3" style="text-align:center;"><b>No related data with your search query!</b></td></tr><?php 
            }
            else
            {
              while($row11 = $result_catagory_custom->fetch_assoc())
              {?>
              <tr><?php 
              }
            }?>
            </tr>
        </thead>                    
          <tbody id="tbl_body1">
        <?php
        $sql="SELECT company,SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, COUNT(vendor) AS vandor, count(*) as total from requests INNER JOIN report on requests.request_id=report.request_id  where report.final_recieved_date is not null   GROUP BY company ORDER BY company ASC;";
        $stmt_details_2 = $conn -> prepare($sql); 
        $stmt_details_2 -> execute();
        $result_details_2 = $stmt_details_2 -> get_result();
          $x=0;
          if($result_details_2 -> num_rows>0)
            while($row = $result_details_2 -> fetch_assoc())
            {
              echo "<tr>
                <td>".(++$x)."</td>
                <td>".$row['company']."</td> 
                <td>".$row['vandor']."</td>
                <td>".$row['non_vandors']."</td>
                <td>".$row['total']."</td>
            
              </tr>";
            }
        ?>
          </tbody>  
          </table>
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
              <div class="card">
          <div class="card-body">
            <h5 class="card-title">  </h5>

            <!-- Stacked Bar Chart -->
            <canvas id="stakedBarChart" style="max-height: 400px; display: block; box-sizing: border-box; height: 400px; width: 829px;" width="829" height="400"></canvas>
            <?php
                $sql="SELECT company,SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, COUNT(vendor) AS vandor, count(*) as total from requests INNER JOIN report where report.final_recieved_date is not null GROUP BY company ORDER BY company ASC;";
                $stmt_details_3 = $conn -> prepare($sql); 
                $stmt_details_3 -> execute();
                $result_details_3 = $stmt_details_3 -> get_result();
                $x=0;
                if($result_details_3 -> num_rows > 0)
                  while($row = $result_details_3 -> fetch_assoc())
                  {
                    $companyList[]=  $row['company'];
                    $nonvenderList[]= $row['non_vandors'];
                    $venderList[]= $row['vandor'];
                    $totalNumber[]= $row['total'];
                  }
            ?>
            <script>
              document.addEventListener("DOMContentLoaded", () => {
                new Chart(document.querySelector('#stakedBarChart'), {
                  type: 'bar',
                  data: {
                    
                    labels: <?php echo json_encode($companyList); ?>,
                    datasets: [{
                        label: 'Vender',
                        data: <?php echo json_encode($venderList); ?>,
                        backgroundColor: '#21B531',
                      },
                      {
                        label: 'Non Vender',
                        data:  <?php echo json_encode( $nonvenderList); ?>,
                        backgroundColor: '#EE4436',
                      },
                    
                    ]
                  },
                  options: {
                    plugins: {
                      title: {
                        display: true,
                        text: ''
                      },
                    },
                    responsive: true,
                    scales: {
                      x: {
                        stacked: false,
                      },
                      y: {
                        stacked: false
                      }
                    }
                  }
                });
              });
            </script>
            <!-- End Stacked Bar Chart -->

          </div>
           <text class='text-center'><strong>Remark:</strong> Vender vs Non vendor list of purchased requests</text>
        </div>
           
      </div>
              </div>
              </div>
              <div class="tab-pane fade" id="bordered-contact" role="tabpanel" aria-labelledby="contact-tab">
                
          <div class="row pb-3">
          <div class="col-lg-8">
              <select  name="com" class="form-select" aria-label="Default select example" onchange="filter3(this)">
                <option value="all">Company/Branch</option>
                  <?php
                  $query="SELECT DISTINCT company from requests inner Join report on requests.request_id = report.request_id where report.officer_assigned_date IS NOT NULL and report.final_recieved_date IS NOT NULL";
                  $stmt_details_4 = $conn -> prepare($query); 
                  $stmt_details_4 -> execute();
                  $result_details_4 = $stmt_details_4 -> get_result();
                    while($row=mysqli_fetch_array($result_details_4))
                    {
                      $name=$row['company'];
                      ?>
                <option value="<?php echo $name?>"><?php echo $name?></option>
                  <?php
                  }
                  ?>
                </select> 
            </div> 
          
          </div>
          <table class='table table-striped'>
          <thead class='tbl_header_style'>
            <tr >
                <th>#</th>
                <th>Company</th> 
                <th>On Time </th>
                <th>Not OnTime</th>
                <th data-toggle="tooltip" data-placement="bottom" title="Total Completed Requests is total purchased requests per each company ">Total Requests</th> 
              <th>OnTime(%)</th> 
                
            </tr>
        </thead> 
        <tbody id="tbl_body2">
        <?php
            $sql="SELECT sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) > 8 then 1 else 0 end) as notontime,
            sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) <= 8 then 1 else 0 end) as ontime, 
            sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) > 8 then 1 else 0 end)+sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end) as total_request,100*sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end)/(sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) > 1 then 1 else 0 end)+sum(case when timetocalculate( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end)) as ontime_percent, requests.company 
              from requests INNER JOIN report on requests.request_id=report.request_id where report.final_recieved_date is not null GROUP BY company;";
          $stmt_details_5 = $conn -> prepare($query); 
          $stmt_details_5 -> execute();
          $result_details_5 = $stmt_details_5 -> get_result();
          $x=0;
          if($result_details_5 -> num_rows>0)
            while($row = $result_details_5 -> fetch_assoc())
            {
              echo "<tr>
                <td>".(++$x)."</td>
                <td>".$row['company']."</td>
                <td>".$row['ontime']."</td>
                <td>".$row['notontime']."</td>
                <td>".$row['total_request']."</td>
                <td>".number_format($row['ontime_percent'], 2, ".", ",")."</td>
              </tr>";
            }
      ?> 
            </tbody> 
          </table>
              </div>
            </div><!-- End Bordered Tabs -->

          </div>
        </div>

      </div>
    </div>
  </section>
  <script>
function filter(e){

const company=document.getElementById('company').value;
const category=document.getElementById('category').value;
const xhr=new XMLHttpRequest();
xhr.onload=function(){
  document.getElementById("tbl_body").innerHTML=this.responseText;
}
xhr.open("GET","../admin/report_ajax.php?category="+category+"&company="+company);

xhr.send();
}

function filter3(e) {
const xhr=new XMLHttpRequest();
xhr.onload=function(){
  document.getElementById("tbl_body2").innerHTML=this.responseText;
}
xhr.open("GET","report_ajax.php?com="+e.value);

xhr.send();

}
function filter1(e) {
const xhr=new XMLHttpRequest();
xhr.onload=function(){
  document.getElementById("tbl_body1").innerHTML=this.responseText;
}
xhr.open("GET","report_ajax.php?comvender="+e.value);
xhr.send();
}

</script>
