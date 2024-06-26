<?php 

session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION['loc'].'head.php';
    include $string_inc;
}
else
    header("Location: ../");
    function divcreate($str)
{
    echo "
        <div class='pricing  ' data-aos='fade-up-right'>
            <div class='row ' id='cs_found'>
                $str
            </div>
        </div>
    ";
}
$required=isset($_GET['agreement'])?"":"required";
$required2=isset($_GET['agreement'])?"required":"";
?>
<script>
    set_title("LPMS | Vendors");
    sideactive("Vendors");
    function add_item(e)
    {
        let item_number = parseInt(e.name)+1;
        e.name = item_number;
        const div =  document.createElement('div');
        div.id=item_number;
        div.innerHTML = item.replace("item","item"+item_number);
        document.getElementById('items').appendChild(div);
    }
    function removeItem(e){
        const element=e.parentElement.parentElement.parentElement.parentElement.id;
           document.getElementById(element).remove();
    }
    function add_items(e)
    {
        let item_number = parseInt(e.name)+1;
        e.name = item_number;
        const div =  document.createElement('div');
        div.id=item_number;
        div.innerHTML = items.replace("item_element","items"+item_number);
        document.getElementById('items_update').appendChild(div);
    }
    function removeItems(e){
        const element=e.parentElement.parentElement.parentElement.id;
           document.getElementById(element).remove();
    }

///phone

    function add_items_phone(e)
    {
        let item_number = parseInt(e.name)+1;
        e.name = item_number;
        const div =  document.createElement('div');
        div.id=item_number;
        div.innerHTML = phoneItem.replace("phone_id","phone"+item_number);
        document.getElementById('items_update_phone').appendChild(div);
    }
    function removeItems_phone(e){
        const element=e.parentElement.parentElement.parentElement.id;
           document.getElementById(element).remove();
    }
    //email
    function add_items_email(e)
    {
        let item_number = parseInt(e.name)+1;
        e.name = item_number;
        const div =  document.createElement('div');
        div.id=item_number;
        div.innerHTML = emailItem.replace("email_id","email"+item_number);
        document.getElementById('items_update_email').appendChild(div);
    }
    function removeItems_email(e){
        const element=e.parentElement.parentElement.parentElement.id;
           document.getElementById(element).remove();
    }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Prefered Vendors</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Prefered Vendors</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<script>
  </script>
<div class="card" data-aos="fade-right"> 
<div class="form-check">
<?php 
if(isset($_GET['agreement']))
echo "<a class='btn btn-primary' href='preferedVendors.php'>Switch to Vendor form</a>";
else
echo "<a class='btn btn-primary' href='?agreement='>Switch to Agreement form</a>";
?>
</div>
                            <p class="text-center "><b>Remark : </b>All <span class='text-danger fs-5'>*</span> <span class='text-secondary'>are Required Fields</span> 
                          <?php echo isset($_GET['agreement'])?'':'
                            <span class="float-end  mx-8 fs-5 "><a href="../assets/Tyre and Battery.csv" download ><i class="bi bi-download"></i> Excel Sample file</a></span></p>
                                <div class="modal-header text-primary">';
                                ?>
                                    <h4 class="modal-title text-center">Add new <?php echo isset($_GET['agreement'])?'agreement ':"" ?>vendor</h4>
                                  <?php 
                                    echo isset($_GET['agreement'])?'':'<form  class="float-left" action="allphp.php" method="POST" enctype="multipart/form-data">
                                      <label for="fileupload" class="text-primary">Import vendor Excel</label>
                                    <input type="file" id="fileupload" class="float-left" name="vendor_csv" accept=".csv" <?php echo $required?>
                                    <button  type="submit" name="fileuploader_css" class="float-left btn btn-primary">Import from CSV/Excel</button>
                                    </form>
                                </div>';
                                ?>
                                <form method="POST" action="allphp.php">
                                <div class="modal-body" id="mymodal_body">
                                  <div class='<?php echo (isset($_GET['agreement'])?"d-none":"")?>'>
                                <div class="row">
                                <div class="form-floating mb-3 col-lg-6 mt-3">
            <input type="text" class="form-control rounded-4" id="vendor" name='vendor' <?php echo $required?> >
            <label for="vendor"><span class='text-danger'>*</span>Vendor Name</label>
        </div>
            <div class="form-floating mb-3 col-lg-6">
            <small><span class='text-danger'>*</span>Catagories</small>
            <select class="choices form-control multiple-remove" name="catagory[]"  id="catagory"  multiple="multiple" <?php echo $required?> >
                <optgroup label="Catagories">
                    <?php
                        $sql_catagory = "SELECT * FROM `catagory`";
                        $stmt_catagory = $conn->prepare($sql_catagory);
                        // $stmt_catagory -> bind_param("s", $property_company);
                        $stmt_catagory -> execute();
                        $result_catagory = $stmt_catagory -> get_result();
                        if($result_catagory -> num_rows>0)
                        {
                            while($row = $result_catagory -> fetch_assoc())
                            {
                                echo "<option value='".$row['catagory']."'>".$row['catagory']."</option>";
                            }
                        }
                    ?>
                </optgroup>
            </select>
           </div> 
                      </div>
           <small><span class='text-danger'>*</span>Items being provided</small>
        <div class="row" id='items'>
            <div class="mb-3 col-10 row" id="1">
            <div class='col-sm-11'>  
            <input type="text" class="form-control rounded-4" id="item1" name='item[]' <?php echo $required?> > 
                    </div>
                    <div class='col-lg-1'>
                                     </div>
                </div>
        </div>
  
        <button type='button' onclick='add_item(this)' class='btn btn-sm btn-outline-primary mx-auto mb-3' name="1">Add Item</button>
        <div class="row">
        <div class="form-floating mb-3 col-lg-6">
            <input type="text" class="form-control rounded-4" id="contact" name='contact' <?php echo $required?> >
            <label for="address"><span class='text-danger'>*</span>Contact Person</label>
        </div>
        <div class="form-floating mb-3 col-lg-6">
            <input type="text" class="form-control rounded-4" id="position" name='position' <?php echo $required?> >
            <label for="address"><span class='text-danger'>*</span>Position</label>
        </div>
        </div>
        <div class="row">
        <div class="form-floating mb-3 col-lg-4">
            <input type="text" class="form-control rounded-4" id="Address" name='address' <?php echo $required?> >
            <label for="address"><span class='text-danger'>*</span>Address</label>
        </div>
        <div class="form-floating mb-3 col-lg-4">
            <input type="text" class="form-control rounded-4" id="business_type" name='business_type' <?php echo $required?> >
            <label for="business_type"><span class='text-danger'>*</span>Business Type</label>
        </div>
        <div class="form-floating mb-3 col-lg-4">
           <select class='form-control' <?php echo $required?>  name='rank'>
                <option value="">Select one</option>
                <option value="1">1-First priority</option>
                <option value="2">2-second priority</option>
                <option value="3">3-third priority</option>
                <option value="4">4-fourth priority</option>
                <option value="5">5-fifth priority</option>
          </select>
            <label for="business_type"><span class='text-danger'>*</span>Rank</label>
        </div>
        </div>
        <div class="form-group mb-3">
            <label for="Vendor_Details"><span class='text-danger'>*</span>Vendor Details</label>
            <textarea class="form-control" name='details' id="Vendor_Details" rows="3"></textarea>
        </div>                    
<input type='text' class="d-none" id="phones" name='phones' value=" "/>
<input type='text' class="d-none" id="mails" name='mails' value=" "/>
           <div class="row w-100" id='mode_1'> 
          <div class='form-check mb-3 col-6'>
          <div class="input-group-prepend row outline border-2">
            <label for="lable_phone">Phone number</label>
            <div class="input-group input-area" id="divKeywords" style="border-width: 0 0 2px 0;">
              <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">+251</span>
              </div>
              <input type="text" style="font-size:1.2em;"  class="rounded-4 outline-primary form-control" id="txtInput" maxlength="9" placeholder="925605984" />
            </div>
          </div>
          </div>
          <div class='form-check mb-3 col-5'>
          <div class="input-group-prepend row">
            <label for="lable_email">Email Adress</label>
          <div class="input-area2" id="divKeywords2" style="border-width: 0 0 2px 0;">
  <input type="text" style="font-size:1.2em;"  class="rounded-4 outline-primary form-control" id="txtInput2" placeholder="username@mail.com" />
</div>   
        </div>
                                </div>
                      </div>
                      </div>
                      <div class='<?php echo (!isset($_GET['agreement'])?"d-none":"")?>'>
                                <div class="row">
                                <div class="form-floating mb-3 mt-3">
            <input type="text" class="form-control rounded-4" id="vendor" name='vendor1' <?php echo $required2?> >
            <label for="vendor"><span class='text-danger'>*</span>Vendor Name</label>
        </div>
          </div>
                      </div>
                                <div class="modal-footer">
                                <button type='submit'  class="btn btn-primary" name='<?php echo isset($_GET['agreement'])?"add_agreement_vendor":"add_vendor"?>'>Add <?php echo isset($_GET['agreement'])?" agreement ":""?>Vendor<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                                </div>
                                </div>
                            </form>
            </div>

<div class='conainter-fluid'>
<div class=' py-3' > 
    <h3 class="text-center my-2" data-aos="fade-up-right">Vendors</h3>
    <div class='row mx-auto border mt-5 w-8/12' style="width:85%;" data-aos="fade-up-right">
      <div class="col-sm-3"></div>
     <form method="GET" action='' class='col-sm-2 col-md-3 float-right my-auto' data-aos="fade-right">
    <select class='form-select text-primary mb-3' id='req_type' onchange="filter(this)">
                <?php
                    echo "<option id='All_All' value='all'>All</option>";
                $sql_catagory = "SELECT * from catagory";
                $stmt_catagory = $conn->prepare($sql_catagory);
                // $stmt_catagory -> bind_param("s", $property_company);
                $stmt_catagory -> execute();
                $result_catagory = $stmt_catagory -> get_result();
                if($result_catagory->num_rows>0)
                    while($ree = $result_catagory->fetch_assoc())
                    {
                        $na_t_1=$ree['catagory'];
                        echo "<option id='$na_t_1' value='$na_t_1'>$ree[display_name]</option>";
                    }?>
        </select>
        <button class='d-none' id='changed'></button>
                </form>
    <form method="GET" action='' class='col-sm-6 col-md-4  mt-4' data-aos="fade-right">
    <div class="input-group mb-3" id="search_requests">
            <input type="text" class="form-control" placeholder="Search"
                aria-label="Search" id='req_keyword' aria-describedby="button-addon2">
                <button class="btn btn-outline-success" type="button" id="search_btn" onclick="filter(this)"><i
                    class="bi bi-search"></i></button>
        </div>
    </form>
</div>
                <?php 
                $category=isset($_GET['agreement'])?" where catagory='agreement'":"";
                $str="";
                $sql_len = "SELECT * FROM prefered_vendors $category order by id desc";
                $stmt_prefered_vendors = $conn->prepare($sql_len);
                $stmt_prefered_vendors -> execute();
                $result_prefered_vendors = $stmt_prefered_vendors -> get_result();
                $len = $result_prefered_vendors -> num_rows;

                $sql = "SELECT * FROM prefered_vendors $category order by id desc limit 20";
                $stmt_prefered_vendors_limited = $conn->prepare($sql);
                $stmt_prefered_vendors_limited -> execute();
                $result_prefered_vendors_limited = $stmt_prefered_vendors_limited -> get_result();
                if($result_prefered_vendors_limited -> num_rows > 0)
                    while($row = $result_prefered_vendors_limited -> fetch_assoc())
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
                      $checked=$row['status']==1?"checked":"";
                      $status_val=$row['status']==1?0:1;
                      $printpage = "
                      <span  class='float-end'>
                          <button type='submit' class='btn btn-outline-secondary border-0' data-bs-toggle='modal'  onclick='edit_vendor(this)' value='".$row['id']."' data-bs-target='#edit_vendor'>
                              <i class='text-primary fas fa-edit'></i>
                          </button>
                      </span>
                      <span class='form-check form-switch float-end'>
                      <input class='form-check-input' type='checkbox' $checked role='switch' name='".$row['id']."' value='$status_val'  onclick='update_status(this)' id='flexSwitchCheckDefault'>
                    </span>
                      ";

                      // <i id='$row[id]_unspecific' data-bs-toggle='modal' data-bs-target='#email_modal' type='button' onclick='mail(this)' class='text-danger fas fa-envelope'></i>
                      $str.= "
                        <div class='col-md-6 col-lg-3 my-4 fade-up-right'>
                            <div class='box'>
                            $printpage
                                <h3 class='text-capitalize '>
                                <span class='small text-secondary d-block mt-2'>".$row['vendor']."</span>";

                                $str.= ($row['catagory']!='agreement' && !is_null($row['rank']))?"<span class='small text-secondary d-block mt-2'>".$row['business_type']."(".str_repeat('<span class="fa fa-star text-warning"></span>',$row['rank']).str_repeat('<span class="fa fa-star"></span>',5-$row['rank']).")</span>":"";                           
                                $str.="</h3>
                                 <ul class='text-start'>";
                              $str.=(($row['catagory']!='agreement')?"<li><b>contact person</b>: ".$row['contact']."</li><li><b>Position</b> : ".$row['position']."</li>":"");

                                    $str.="<li><b>Items Category</b> : ".$row['catagory']."</li>
                                    <li><b>Address</b> : ".$row['address']."</li>
                                    <li>$phoneLinks<a href='https://t.me/share/url?url=details' target='_blank'><i class='bi bi-telegram text-primary me-1'></i></a>
                                 <a href='https://api.whatsapp.com/send?text=details' target='_blank'><i class='bi bi-whatsapp text-success me-1'></i></a>$email
                               </li>
                                </ul>                                
                            </div>
                        </div>
                        ";
                      }
                ?>
    </form>
</div>
</div>
<?php    if($str=='')
                echo "
                    <div class='py-5 pricing border'>
                        <div class='section-title text-center py-2  alert-primary rounded'>
                            <h3 class='mt-4'>No Vendors Added</h3>
                        </div>
                    </div>";
            else 
                divcreate($str);
                echo $len>20?"<div id='load_more' class='container-fluid text-center'>
                <button type='button' class='btn btn-primary' name='0' value='$len' onclick='readmore(this)'>
                    View More
                </button>
            </div>":"";
               ?>
        <div class="row d-none" id='item'>
          <div class="mb-3 col-10 row" id="1">
            <div class='col-sm-11'>  
              <input type="text" class="form-control rounded-4" id="item1" name='item[]' required> 
            </div>
            <div class='col-lg-1'>
              <span class="float-right">
                <button type='button' class="bg-danger btn"  name="1"  onclick='removeItem(this)'><i class="fa fa-times text-white rounded-4 outline-primary" aria-hidden="true"></i></button>
              </span>
            </div>
          </div>
        </div>  

        <div class="row d-none" id='item_element'>
          <div class="mb-3 col-10 row" id="1">
            <div class='col-sm-11'>  
              <input type="text" class="form-control rounded-4" id="item1" name='item[]' required> 
            </div>
            <div class='col-lg-1'>
              <span class="float-right">
                <button type='button' class="bg-danger btn"  name="1"  onclick='removeItem(this)'><i class="fa fa-times text-white rounded-4 outline-primary" aria-hidden="true"></i></button>
              </span>
            </div>
          </div>
        </div>  
        <div class="row d-none" id='item_element_phone'>
          <div class='mb-3  row' id='phone_id'>
            <div class='input-group col-sm-11'>  
              <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">+251</span>
              </div>
              <input type='text' class='form-control rounded-4' id='phone'  name='phones[]' required> 
            </div>
            <div class='col-lg-1'>
              <span class='float-right'>
                <button type='button' class='bg-danger btn'  name='1'  onclick='removeItems_phone(this)'><i class='fa fa-times text-white rounded-4 outline-primary' aria-hidden='true'></i></button>
              </span>
            </div>
          </div>
        </div>
        <div class="row d-none" id='item_element_email'>
          <div class='mb-3  row' id='email_id'>
            <div class='col-sm-11'>  
              <input type='text' class='form-control rounded-4' id='email'    name='emails[]' required> 
            </div>
            <div class='col-lg-1'>
              <span class='float-right'>
                <button type='button' class='bg-danger btn'  name='1'  onclick='removeItems_email(this)'><i class='fa fa-times text-white rounded-4 outline-primary' aria-hidden='true'></i></button>
              </span>
            </div>
          </div>
        </div>

<script>
  function readmore(e){
     e.name=parseInt(e.name)+20;
    
     if((e.value-e.name)<20)
     e.classList.add('d-none');
     else
     e.classList.remove('d-none');
     let xhr=new XMLHttpRequest();
     xhr.onload=function(){
      document.getElementById("cs_found").innerHTML+=this.responseText;
     }
     xhr.open("GET","allphp.php?vendor_limit="+e.name);
     xhr.send();
    
  }
 
           
function update_status(e){
  var y=e.checked;
   e.checked=(!e.checked);
   Swal.fire({
         title: 'Do you want to update Vendor?',
         text: "you wish to countinue",
             icon: "warning",
             showCancelButton: true,
               buttons: true,
              buttons: ["Cancel", "Update"]
            })
    .then((result) => {
                if (result.isConfirmed) {
let xhr=new XMLHttpRequest();
xhr.onload=function(){
  if(this.responseText){
  Swal.fire({
  position: 'center',
  icon: 'success',
  title: 'Vendor Updated'+this.responseText,
  showConfirmButton: false,
  timer: 1500
})
e.checked=y;
  }
else
Swal.fire({
  position: 'center',
  icon: 'error',
  title: 'Vendor Update failed',
  showConfirmButton: false,
  timer: 1500
})
}
xhr.open("GET","allphp.php?vendor_update_id="+e.name+"&status="+e.value);
xhr.send();
  }  
})
}
function filter(e){
  let keyword=document.getElementById("req_keyword").value;
  let type=document.getElementById("req_type").value;
 let xhr=new XMLHttpRequest();
 xhr.onload=function(){
document.getElementById("cs_found").innerHTML=xhr.responseText;
 }
 xhr.open("GET", "edit_vendor.php?type="+type+"&keyword="+keyword);
 xhr.send();
}
   function edit_vendor(e){
    let xhr=new XMLHttpRequest();
    xhr.onload=function(){
      document.getElementById("editvendor").innerHTML=xhr.responseText;
    }
    xhr.open("GET", "edit_vendor.php?vendor_id="+e.value);
    xhr.send();
   }
    var item = document.getElementById("item").innerHTML;
    var items = document.getElementById("item_element").innerHTML; 
    var phoneItem = document.getElementById("item_element_phone").innerHTML;
    var emailItem = document.getElementById("item_element_email").innerHTML;
    var phoneInput = document.getElementById("phones");
    var emailInput = document.getElementById("mails");
    var allKeywords = []
  
function deleteWord(element){
  var index = allKeywords.indexOf($(element).parent('.keyword').text());
  if(index !== -1){                                  
    allKeywords.splice(index, 1);
  }
  $(element).parent('.keyword').remove();
  var data=allKeywords.toString();
    phoneInput.value=data;
}

//Add a keyword
function addWord(word){
  if(word === undefined || word === ''){
    return;
  }
  allKeywords.push(word);
  
  $('#divKeywords > input[type=text]').before($('<p class="keyword" name="" style="background: #76a9fc;border-radius: 5px;display: inline-block;margin: 0 5px 0 0;padding: 3px;">' + word + '<a class="delete" style="cursor: pointer;margin:0 3px;" onclick="deleteWord(this)"><i class="fa fa-times" aria-hidden="true"></i></a></p>'));
  $('#txtInput').val('');
  $('#txtInput').focus();
}
function addWordFromTextBox(){
  var val = $('#txtInput').val();
  if(val !== undefined && val !== ''){
    addWord(val);
  }
}
function checkLetter(){
  var val = $('#txtInput').val()
  if(val.length > 0){
    var letter = val.slice(-1);
    if(letter === ',' || letter === ';'|| letter==" "){
      var word = val.slice(0,-1);
      if(word.length > 0){
        addWord(word);
        var phone="";
        var data=allKeywords.toString();
         phoneInput.value=data;
      }
    }
  }
}

$('#txtInput').blur(addWordFromTextBox);
$('#txtInput').keyup(checkLetter);
$('#divKeywords').click(function(){ $('#txtInput').focus(); });

var EmailList = []
 
 function deleteWord2(element){
   var index = EmailList.indexOf($(element).parent('.keyword2').text());
   if(index !== -1){                                  
     EmailList.splice(index, 1);
   }
   $(element).parent('.keyword2').remove();
   var data=EmailList.toString();
   emailInput.value=data;
 }
 
 //Add a keyword
 function addWord2(word){
   if(word === undefined || word === ''){
     return;
   }
   
   EmailList.push(word);
   
   $('#divKeywords2 > input[type=text]').before($('<p class="keyword2" name="emails[]" style="background: #76a9fc;border-radius: 5px;display: inline-block;margin: 0 5px 0 0;padding: 3px;">' + word + '<a class="delete2" style="cursor: pointer;margin:0 3px;" onclick="deleteWord2(this)"><i class="fa fa-times" aria-hidden="true"></i></a></p>'));
   $('#txtInput2').val('');
   $('#txtInput2').focus();
 }
 
  
 function addWordFromTextBox2(){
   var val = $('#txtInput2').val();
   if(val !== undefined && val !== ''){
     addWord2(val);
   }
 }
 
 function checkLetter2(){
   var val = $('#txtInput2').val()
   if(val.length > 0){
     var letter = val.slice(-1);
     if(letter === ',' || letter === ';'){
       var word = val.slice(0,-1);
       if(word.length > 0){
         addWord2(word);
         var data=EmailList.toString();
         emailInput.value=data;
       }
     }
   }
 }
 $('#txtInput2').blur(addWordFromTextBox2);
 $('#txtInput2').keyup(checkLetter2);
 $('#divKeywords2').click(function(){ $('#txtInput2').focus(); });
</script>
<?php include '../footer.php';?>
<?php
if(isset($_SESSION['error'])){
  echo "<script>Swal.fire({position: 'center',icon: 'error',title:'".$_SESSION['error']."',showConfirmButton: false,timer: 1500})</script>";
  unset($_SESSION['error']);
  }
  if(isset($_SESSION['done'])){
    echo "<script>Swal.fire({position: 'center',icon: 'success',title:'".$_SESSION['done']."',showConfirmButton: false,timer: 1500})</script>";
    unset($_SESSION['done']);
    }
?>