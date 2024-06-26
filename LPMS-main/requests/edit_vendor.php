<?php
    session_start();
    include "../connection/connect.php";
function hightlight($key,$text){
    return (str_ireplace($key,"<span class='text-warning'>$key</span>",$text));
}
if(isset($_GET['type'])||isset($_GET['keyword'])){
    $filter="";
    $type=$_GET['type'];
    $key=$_GET['keyword'];
    if($key)
    $filter.=" and (vendor LIKE '%$key%' or catagory LIKE '%$key%'or `vendor` LIKE '%$key%' or `business_type` LIKE '%$key%' or `contact` LIKE '%$key%' or `position` LIKE '%$key%' or `address` LIKE '%$key%' or `items` LIKE '%$key%' or `details` LIKE '%$key%' or `email` LIKE '%$key%' or `phone` LIKE '%$key%')";
    if($type && $type!="all")
    {
        $filter.=" and catagory LIKE '%$type%'";
    }
    $str="";
    $sql = "SELECT * FROM prefered_vendors where 1 $filter order by id desc";
    $stmt_filtered_vendors = $conn -> prepare($sql);
    $stmt_filtered_vendors -> execute();
    $result_filtered_vendors = $stmt_filtered_vendors -> get_result();
    if($result_filtered_vendors -> num_rows>0)
        while($row = $result_filtered_vendors -> fetch_assoc())
        { 
            $phoneLinks="";
            $email="";
            $numbers=explode(',',$row['phone']);
          foreach($numbers as $number)
          $phoneLinks.="<a href='tel:+251$number' target='_blank'><i class='bi bi-telephone text-primary me-1'></i></a>"; 
          $numbers=explode(',',$row['phone']);
          if(strpos(explode(',',$row['email'])[0],'@')){
            $email="<a href='mailto:".explode(',',$row['email'])[0]."' target='_blank'><i  class='text-danger fas fa-envelope'></i></a>";
          }
          $printpage = "
          <span  class='float-end'>
              <button type='submit' class='btn btn-outline-secondary border-0' data-bs-toggle='modal'  onclick='edit_vendor(this)' value='".$row['id']."' data-bs-target='#edit_vendor'>
                  <i class='text-primary fas fa-edit'></i>
              </button>
          </span>";
          // <i id='$row[id]_unspecific' data-bs-toggle='modal' data-bs-target='#email_modal' type='button' onclick='mail(this)' class='text-danger fas fa-envelope'></i>
          $str.= "
            <div class='col-md-6 col-lg-3 my-4 fade-up-right'>
                <div class='box'>
                $printpage
                    <h3 class='text-capitalize'>
                    <span class='small text-secondary d-block mt-2'>".hightlight($key,$row['vendor'])."</span>
                    <span class='small text-secondary d-block mt-2'>".hightlight($key,$row['business_type'])."</span>                             
                    </h3>
                    <ul>
                        <li><b>contact person</b>: ".hightlight($key,$row['contact'])."</li>
                        <li><b>Position</b> : ".hightlight($key,$row['position'])."</li>
                        <li><b>Items Category</b> : ".hightlight($key,$row['catagory'])."</li>
                        <li><b>Address</b> : ".hightlight($key,$row['address'])."</li>
                        <li>$phoneLinks<a href='https://t.me/share/url?url=details' target='_blank'><i class='bi bi-telegram text-primary me-1'></i></a>
                     <a href='https://api.whatsapp.com/send?text=details' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>$email
                   </li>
                    </ul>                                
                </div>
            </div>
            ";
          }
          if($str=="")
          echo "<div class='py-5 pricing border'>
          <div class='section-title text-center py-2  alert-primary rounded'>
              <h3 class='mt-4'>No Vendors Found</h3>
          </div>
      </div>";
      else
          echo $str;
}
if(isset($_GET['vendor_id'])){
    $vendor_id=$_GET['vendor_id'];
$sql = "SELECT * FROM `prefered_vendors` where id = ?";
$stmt_specific_vendor = $conn -> prepare($sql);
$stmt_specific_vendor -> bind_param("i", $vendor_id);
$stmt_specific_vendor -> execute();
$result_specific_vendor = $stmt_specific_vendor -> get_result();
if($result_specific_vendor -> num_rows)
    $row = $result_specific_vendor -> fetch_assoc();

// print_r($row); 
$category_list=explode(",",$row['catagory']);
$item_list=explode(",",$row['items']);
$category_sql="SELECT * FROM `catagory`";
$stmt_catagories = $conn -> prepare($category_sql);
$stmt_catagories -> execute();
$result_catagories = $stmt_catagories -> get_result();
$cat_row = $result_catagories -> fetch_assoc();
echo "<div class='row'>
<div class='form-floating mb-3 mt-3'>
<input type='text' class='form-control rounded-4 d-none' id='vendor_id' name='vendor_id' value='".$row['id']."' required>
<input type='text' class='form-control rounded-4' id='vendor' name='vendor' value='".$row['vendor']."' required>
<label for=vendor><span class='text-danger'>*</span>Vendor Name</label>
</div>
<div class=form-floating mb-3>
            <div>Catagories<span class='text-danger'>*</span></div>";
                    
$stmt_catagories -> execute();
$result_catagories = $stmt_catagories -> get_result();
echo "<div class='row'>";
  if ($result_catagories -> num_rows > 0) { 
  while($row1 = $result_catagories -> fetch_assoc()){
    $status = $row1["catagory"];

  echo "<div class='col-lg-4'>
  <div class='form-check'>
  <input class='form-check-input' type='checkbox' name='category[]'  id='".$row1['catagory']."'".( in_array( $row1['catagory'],$category_list)?"checked":"")." onchange='onchange(this)' value='".$row1['catagory']."' style='cursor: pointer;' >
  <label class='form-check-label' for='".$row1['catagory']."'>$status
  </label>
  </div> </div>";
   }}
  echo "</div>
   </div>
   </div>
   <small><span class='text-danger'>*</span>Items being provided</small>
        <div class='row' id='items_update'>";
            
            $x=0;
            foreach($item_list as $item){
       echo "<div class='mb-3 col-10 row' id='".(++$x)."'>
        <div class='col-sm-11'>  
        <input type='text' class='form-control rounded-4' id='item".($x)."' value='$item' name='item[]' required> 
                </div>
                <div class='col-lg-1'>
                <span class='float-right'>".($x!=1?"<button type='button' class='bg-danger btn'  name='".($x)."'  onclick='removeItems(this)'><i class='fa fa-times text-white rounded-4 outline-primary' aria-hidden='true'></i></span></button>":"")."
                </div>
            </div>";
            }
      echo "</div>
        <button type='button' onclick='add_items(this)' class='btn btn-sm btn-outline-primary mx-auto mb-3' name='".count($item_list)."'>Add Item</button>
        <div class='row'>
        <div class='form-floating mb-3 col-lg-6'>
            <input type='text' class='form-control rounded-4' id='contact' value='".$row['contact']."' name='contact' required>
            <label for='address'><span class='text-danger'>*</span>Contact Person</label>
        </div>
        <div class='form-floating mb-3 col-lg-6'>
            <input type='text' class='form-control rounded-4' id='position' value='".$row['position']."' name='position' required>
            <label for='address'><span class='text-danger'>*</span>Position</label>
        </div>
        </div>
        <div class='row'>
        <div class='form-floating mb-3 col-lg-6'>
            <input type='text' class='form-control rounded-4' id='Address' value='".$row['address']."' name='address' required>
            <label for='address'><span class='text-danger'>*</span>Address</label>
        </div>
        <div class='form-floating mb-3 col-lg-6'>
            <input type='text' class='form-control rounded-4' id='business_type' value='".$row['business_type']."'  name='business_type' required>
            <label for='business_type'><span class='text-danger'>*</span>Business Type</label>
        </div>
        </div>
        <div class='form-group mb-3'>
            <label for='Vendor_Details'><span class='text-danger'>*</span>Vendor Details</label>
            <textarea class='form-control' id='Vendor_Details' name='Vendor_Details'  rows='3'>".$row['details']."</textarea>
        </div>   
        <div class='row'>
                <div class='col-lg-6'>
                <div class='input-group-prepend row outline border-2'>
          <small><span class='text-danger'>*</span>Phone Numbers</small>
        <div class='row' id='items_update_phone'>";
            
            $x=0;
            $phone_list=explode(",",$row['phone']);
            foreach($phone_list as $item){
       echo "<div class='mb-3  row' id='phone".(++$x)."'>
        <div class='col-sm-11'>  
        <input type='text' class='form-control rounded-4' id='phone".($x)."' value='$item' name='phones[]' required> 
                </div>
                <div class='col-lg-1'>
                <span class='float-right'>".($x!=1?"<button type='button' class='bg-danger btn'  name='".($x)."'  onclick='removeItems_phone(this)'><i class='fa fa-times text-white rounded-4 outline-primary' aria-hidden='true'></i></span></button>":"")."
                </div>
            </div>";
            }
            
       echo "</div>
        </div>
        </div>
        <div class='col-lg-6'>
        <div class='input-group-prepend row outline border-2'>
          <small><span class='text-danger'>*</span>Email Addresses</small>
        <div class='row' id='items_update_email'>";
             
            $email_list=explode(",",$row['email']);
            $x=0;
            foreach($email_list as $item){
       echo "<div class='mb-3  row' id='email".(++$x)."'>
        <div class='col-sm-11'>  
        <input type='text' class='form-control rounded-4' id='item".($x)."' value='$item' name='emails[]' required> 
                </div>
                <div class='col-lg-1'>
                <span class='float-right'>".($x!=1?"<button type='button' class='bg-danger btn'  name='".($x)."'  onclick='removeItems_email(this)'><i class='fa fa-times text-white rounded-4 outline-primary' aria-hidden='true'></i></span></button>":"")."
                </div>
            </div>";
            }
       echo "</div>
        </div>
        </div>
        </div>
        <div class='row'>
            <div class='col-lg-6'>
            <button type='button'  onclick='add_items_phone(this)' class='btn btn-sm btn-outline-primary mx-auto mb-3' name='".count($phone_list)."'>Add Item</button>
        </div>
        <div class='col-lg-6'>
            <button type='button' onclick='add_items_email(this)' class='btn btn-sm btn-outline-primary mx-auto mb-3' name='".count($email_list)."'>Add Item</button>
        </div>
        </div>";
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
    
       
        