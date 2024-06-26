<?php 
session_start();
include '../connection/connect.php';
if(isset($_GET['fcompany'])){
// echo $_GET['fcompany'];
// echo $_GET['department'];
// echo $_GET['type'];
}
// ajax for avarage time taken for purchase office performance
if( isset($_GET['company']) || isset($_GET['category']) ){
  $str="";
  if( $_GET['company']!='all'&&$_GET['category']=='all'){
    
$sql=  "SELECT requests.company as comp,requests.request_type as type,count(*) as total ,AVG((timetocalculate(report.final_recieved_date ,report.request_date))/8) as avg_days from requests INNER JOIN report on report.request_id=requests.request_id WHERE requests.company=? and status='All Complete' and report.request_date IS NOT NULL and report.final_recieved_date  IS NOT NULL GROUP BY requests.request_type;";
$stmt_report1 = $conn->prepare($sql); 
$stmt_report1 -> bind_param("s", $_GET['company']);
$stmt_report1 -> execute();
$result = $stmt_report1 -> get_result();
$x=0;
if($result->num_rows>0){
while($row=$result->fetch_assoc()){
  $str.="<tr>
<td>".(++$x)."</td>
 <td>".$row['comp']."</td>
 <td>".$row['type']."</td>
 <td>".$row['total']."</td>
 <td>".number_format($row['avg_days'], 2, ".", ",")." days</td>
 <td><button class='btn btn-success' > Detail</button></td>  
</tr>";
}
}
else
   $str.= "<tr >
   <td colspan='6'><div class='alert-primary mt-3 px-0 py-2'><div class='text-center alert-secondary fw-bold p-3'>No Result Found</div></div></td>
   
   
   
   </tr>";
  }

else 
if( $_GET['category']!='all'&&$_GET['company']=='all'){
$sql="SELECT requests.company as comp,requests.request_type as type,count(*) as total ,AVG((timetocalculate(report.final_recieved_date ,report.request_date))/8) as avg_days from requests INNER JOIN report on report.request_id=requests.request_id WHERE requests.request_type=? and status='All Complete' and report.request_date IS NOT NULL and report.final_recieved_date  IS NOT NULL GROUP BY requests.company;";   
$stmt_report2 = $conn->prepare($sql); 
$stmt_report2 -> bind_param("s", $_GET['category']);
$stmt_report2 -> execute();
$result = $stmt_report2 -> get_result();
 $x=0;
 if($result->num_rows>0){
 while($row=$result->fetch_assoc()){
   $str.="<tr>
 <td>".(++$x)."</td>
  <td>".$row['comp']."</td>
  <td>".$row['type']."</td>
  <td>".$row['total']."</td>
  <td>".number_format($row['avg_days'], 2, ".", ",")." days</td>
  <td><button class='btn btn-success' >Detail</button></td>  
 </tr>";
 }
 }
 else
    $str.= "<tr >
    <td colspan='6'><div class='alert-primary mt-3 px-0 py-2'><div class='text-center alert-secondary fw-bold p-3'>No Result Found</div></div></td>
    
    
    
    </tr>";
   }
 

   else  if( $_GET['company']!='all'&&$_GET['category']!='all'){
    $sql="SELECT requests.company as comp,requests.request_type as type,count(*) as total ,AVG((timetocalculate(report.final_recieved_date ,report.request_date))/8) as avg_days from requests INNER JOIN report on report.request_id=requests.request_id WHERE requests.request_type=? and requests.company=? and status='All Complete' and report.request_date IS NOT NULL and report.final_recieved_date  IS NOT NULL GROUP BY requests.request_type;";
    $stmt_report3 = $conn->prepare($sql); 
    $stmt_report3 -> bind_param("s", $_GET['category']);
    $stmt_report3 -> execute();
    $result = $stmt_report3 -> get_result();
 $x=0;
 if($result->num_rows>0){ 
 while($row=$result->fetch_assoc()){

      $str.="<tr>
       <td>".(++$x)."</td>
        <td>".$row['comp']."</td>
        <td>".$row['type']."</td>
        <td>".$row['total']."</td>
        <td>".number_format($row['avg_days'], 2, ".", ",")." days </td>
        <td><button class='btn btn-success' > Detail</button></td>  
       </tr>";
 }
 }
 else
 $str.= "<tr >
 <td colspan='6'><div class='alert-primary mt-3 px-0 py-2'><div class='text-center alert-secondary fw-bold p-3'>No Result Found</div></div></td>
 
 
 
 </tr>";
    }
   else{
      $sql_comp="SELECT DISTINCT company from requests inner Join report on requests.request_id = report.request_id where report.officer_assigned_date IS NOT NULL and report.final_recieved_date IS NOT NULL";
      $stmt_report4 = $conn->prepare($sql); 
      $stmt_report4 -> execute();
      $com_res = $stmt_report4 -> get_result();
      $x=0;
      if($com_res->num_rows>0)
      while($r=$com_res->fetch_assoc()){
        $sql="SELECT requests.company as comp,requests.request_type as type,count(*) as total ,AVG((timetocalculate(report.final_recieved_date ,report.request_date))/8) as avg_days from requests INNER JOIN report on report.request_id=requests.request_id WHERE requests.company=? and status='All Complete' and report.request_date IS NOT NULL and report.final_recieved_date  IS NOT NULL GROUP BY requests.request_type;";
        $stmt_report5 = $conn->prepare($sql); 
        $stmt_report5 -> bind_param("s", $r['company']);
        $stmt_report5 -> execute();
        $result = $stmt_report5 -> get_result();
     if($result->num_rows>0){
   while($row=$result->fetch_assoc()){
      $str.="<tr>
       <td>".(++$x)."</td>
        <td>".$row['comp']."</td>
        <td>".$row['type']."</td>
        <td>".$row['total']."</td>
        <td>".number_format($row['avg_days'], 2, ".", ",")."</td>
        <td><button class='btn btn-success' > Detail</button></td>  
       </tr>";
     }
     }
     else
     $str.= "<tr >
     <td colspan='6'><div class='alert-primary mt-3 px-0 py-2'><div class='text-center alert-secondary fw-bold p-3'>No Result Found</div></div></td>
  
     
     
     </tr>";
    
    }
  
  }
    
   echo $str;
}
// ----------------------------------------------------------------
//ajax for vender vs non vender list filter

if(isset($_GET['com'])){
  $str="";
if($_GET['com']=='all'){

 
  $sql="select sum(case when datediff( report.final_recieved_date,date_needed_by ) > 1 then 1 else 0 end) as notontime,
  sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end) as ontime, 
  sum(case when datediff( report.final_recieved_date,date_needed_by ) > 1 then 1 else 0 end)+sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end) as total_request,100*sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end)/(sum(case when datediff( report.final_recieved_date,date_needed_by ) > 1 then 1 else 0 end)+sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 1 then 1 else 0 end)) as ontime_percent, requests.company 
    from requests INNER JOIN report on requests.request_id=report.request_id where report.final_recieved_date is not null  GROUP BY company;";
    $stmt_report6 = $conn->prepare($sql); 
    $stmt_report6 -> execute();
    $result = $stmt_report6 -> get_result();

 $x=0;
 if($result->num_rows>0){
 
   while($row=$result->fetch_assoc())
   {
   
    $str.= "<tr>
       <td>".(++$x)."</td>
       <td>".$row['company']."</td>
       <td>".$row['ontime']."</td>
       <td>".$row['notontime']."</td>
       <td>".$row['total_request']."</td>
       <td>".round($row['ontime_percent'],3)."</td>
 
     </tr>";
  }
}
else
   $str.="no results found ";
}
else{
 
 
    $sql="select sum(case when datediff( report.final_recieved_date,date_needed_by ) > 0 then 1 else 0 end) as notontime,
    sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 0 then 1 else 0 end) as ontime, 
    sum(case when datediff( report.final_recieved_date,date_needed_by ) > 0 then 1 else 0 end)+sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 0 then 1 else 0 end) as total_request,100*sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 0 then 1 else 0 end)/(sum(case when datediff( report.final_recieved_date,date_needed_by ) > 0 then 1 else 0 end)+sum(case when datediff( report.final_recieved_date,date_needed_by ) <= 0 then 1 else 0 end)) as ontime_percent, requests.company 
      from requests INNER JOIN report on requests.request_id=report.request_id where report.final_recieved_date is not null and requests.company=? GROUP BY company;";
      $stmt_report7 = $conn->prepare($sql); 
      $stmt_report7 -> bind_param("s", $_GET['com']);
      $stmt_report7 -> execute();
      $result = $stmt_report7 -> get_result();

   $x=0;
   if($result->num_rows>0){
   
     while($row=$result->fetch_assoc())
     {
     
      $str.=  "<tr>
         <td>".(++$x)."</td>
         <td>".$row['company']."</td>
         <td>".$row['ontime']."</td>
         <td>".$row['notontime']."</td>
         <td>".$row['total_request']."</td>
         <td>".round($row['ontime_percent'],3)."</td>
   
       </tr>";
    }
  }
  else
   $str.="no results found ";
  }
   echo $str;
  }
//----------------------------------------------------------------
//ajax for ratio of ontime delivery filter
  if(isset($_GET['comvender'])){
    $str="";
  if($_GET['comvender']=='all'){
 $sql="SELECT company,SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, COUNT(vendor) AS vandor, count(*) as total from requests GROUP BY company ORDER BY company ASC;";
 $stmt_report8 = $conn->prepare($sql); 
 $stmt_report8 -> execute();
 $result = $stmt_report8 -> get_result();
  
   $x=0;
   if($result->num_rows>0){
   
     while($row=$result->fetch_assoc())
     {
     
      $str.= "<tr>
      <td>".(++$x)."</td>
      <td>".$row['company']."</td>
      <td>".$row['non_vandors']."</td>
      <td>".$row['vandor']."</td>
      <td>".$row['total']."</td>
  
    </tr>";
    }
  }
  else
     $str.="no results found ";
  }
  else{
    $sql="SELECT company,SUM(CASE WHEN vendor is null THEN 1 ELSE 0 END) AS non_vandors, COUNT(vendor) AS vandor, count(*) as total from requests where company=? GROUP BY company ORDER BY company ASC;";
    $stmt_report9 = $conn->prepare($sql); 
    $stmt_report9 -> bind_param("s", $_GET['comvender']);
    $stmt_report9 -> execute();
    $result = $stmt_report9 -> get_result();
  
     $x=0;
     if($result->num_rows>0){
     
       while($row=$result->fetch_assoc())
       {
        $companyList=$row['company'];
        $nonvenderList=$row['non_vandors'];
        $venderList=$row['vandor'];
        $totalNumber=$row['total'];
        $str.=  "<tr>
        <td>".(++$x)."</td>
        <td>".$row['company']."</td>
        <td>".$row['non_vandors']."</td>
        <td>".$row['vandor']."</td>
        <td>".$row['total']."</td>
    
      </tr>";
      }
    }
    else
     $str.="no results found ";
    }
     echo $str;
    }
    // ----------------------------------------------------------------
    //ajax for detail of Requesting department performance product type purchased
    if(isset($_GET['fcompany'])){

 
      $x=0;
     
      
      $sql="SELECT * from requests INNER join purchase_order on requests.request_id=purchase_order.request_id 
       where requests.company =? and requests.department=? and requests.request_type=? and requests.status = 'All Complete'  ";
      $stmt_report10 = $conn->prepare($sql); 
      $stmt_report10 -> bind_param("sss", $_GET['fcompany'] ,$_GET['department'] ,$_GET['type']);
      $stmt_report10 -> execute();
      $result = $stmt_report10 -> get_result();
    
   
      if($result->num_rows>0)
   

       echo " <h6 class='text-center'> Company:".$_GET['fcompany'] ."&nbsp;&nbsp;&nbsp;Department :".$_GET['department'] ."&nbsp;&nbsp;&nbsp;Request Type:".$_GET['type']." </h6>";
      while($row=$result->fetch_assoc())
      {
        $sql_po = "SELECT * FROM `purchase_order` where `request_id` = ?";
        $stmt_report11 = $conn->prepare($sql_po); 
        $stmt_report11 -> bind_param("i", $row['request_id']);
        $stmt_report11 -> execute();
        $result_po = $stmt_report11 -> get_result();
        if($result_po->num_rows>0)
        {
            $row_po = $result_po->fetch_assoc();
            $view_cs = (!is_null($row_po['cluster_id']))?"<button type='button' name='".$row_po['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>Comparision Sheet</button>":"";
        }
        else $view_cs = "";
        $type=$row['request_type'];
        $na_t=str_replace(" ","",$type);
        echo "
        <div class='d-inline-block box shadow' style = 'margin-left:20px;margin-top:20px;' >
            <div class='card-columns-fluid'>
                <div class='card ' style = 'width: 22rem;  '>
                
                        <div class='card-body'>
                        <h6 class='card-title'><b>Item :</b> <button type='button'  value='".$row['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow text-capitalize' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >".$row['item']."</button>     </h6>
                        <hr/>
                        <ul class='list-unstyled'>
                        <li class='text-start'><span class='fw-bold'>Requested By : </span>".$row['customer']."</li>
                        <li class='text-start'><span class='fw-bold'>Quantity : </span>".$row['requested_quantity']." ".$row['unit']."</li>
                        <li class='text-start'><span class='fw-bold'>Date Requested : </span>". date("d-M-Y", strtotime($row['date_requested']))."</li>
                        <li class='text-start'><span class='fw-bold'>Date Needed By : </span>". date("d-M-Y", strtotime($row['date_needed_by']))."</li>
                        <li class='text-start' id='stat".$row['request_id']."'><span class='fw-bold'>Status :  </span>".$row['status']."</li>

            </ul>
                      
                    </div>
                     $view_cs
                </div>
            </div>
        </div>

 
</div> 
       
     
       ";
      
     
      }
      
   
   
     }
   //----------------------------------------------------------------
      
  
  
  
  ?>

