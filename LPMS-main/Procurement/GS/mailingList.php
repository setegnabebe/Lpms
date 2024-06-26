<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str,$n)
{
    if($str=='') return 0;
    echo "
        <div class='py-5 pricing'>
            <div class='row po_list'  value='0' id='assign_po'>
                $str
            </div>
        </div>
        ";
        if($n>39)
       echo "<div id='load_more' class='container-fluid text-center'>
        <button type='button' id='view_more_btn' class='btn btn-primary' name='1' value='40' onclick='load_more(this)'>
            View More
        </button>
    </div>
    ";
}
function format($date1){
  $str="";
 $number=abs(strtotime($date1)-strtotime("now"));
 if($number<60)
 $str="$number seconds ago";
 else if($number>=60 && $number<3600)
 $str= floor(($number/60))." minutes ago";
 else if($number>=3600 && $number<86400)
 $str=floor(($number/3600))." hours ago";
 else if($number>=86400 && $number<691200)
 $str=floor(($number/86400))." Days ago";
 else if($number>=691200 && $number<2592000)
 $str=floor(($number/691200))." Weeks ago";
 else if($number>=2592000 && $number<31104000)
 $str=floor(($number/2592000))." months ago";
 else
 $str=floor(($number/31104000))."Years ago";
 return $str;
}
function selector()
{
    include "../../connection/connect.php";
    $sql_pos = "SELECT * FROM `purchase_order`";
    $stmt_pos = $conn->prepare($sql_pos);
    $stmt_pos -> execute();
    $result_pos = $stmt_pos -> get_result();
    if($result_pos -> num_rows > 0)
        while($row = $result_pos -> fetch_assoc())
        {
            $na_t=str_replace(" ","",$row['request_type']);
            echo "
                <script>document.getElementById('".$na_t."_".$row['request_id']."').value='".$row["purchase_officer"]."'</script>
            ";
        }
}
?>
<script>
    set_title("LPMS | Mail Vendor");
    sideactive("Mail List");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Email Vendor</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Email Vendor</a></li>
            <li class="breadcrumb-item active">Sent Emails</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
</div>
 <?php 
include $pos.'../common/head_mail.php';
?>     
 <div class="wrapper">
  <div class="content-wrapper">
    <section class="content">
      <div class="row">
        <div class="col-md-3">
          <a href="compose.html" id='compose' class="btn btn-primary btn-block mb-3">Compose</a>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Vendors List</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
            <div class="card-body p-0">
              <ul class=" nav-pills flex-column bg-light text-white">
                     <?php
                          $ids=explode(",",$_GET['request_id']);
                          $cond="";
                          $cond2="";
                          foreach($ids as $id)
                          $cond.=" request_id = $id or ";
                            $sql_request_type_request = "SELECT DISTINCT(request_type) from requests where $cond 0";
                            $stmt_request_type_request = $conn->prepare($sql_request_type_request);
                            $stmt_request_type_request -> execute();
                            $result_request_type_request = $stmt_request_type_request -> get_result();
                            if($result_request_type_request -> num_rows>0)
                            while($row = $result_request_type_request -> fetch_assoc())
                            $cond2.="catagory LIKE '%".$row['request_type']."%' or ";
                         $sql_vendor_with_condition = "SELECT * FROM `prefered_vendors` where $cond2 0 limit 10";
                         $stmt_vendor_with_condition = $conn->prepare($sql_vendor_with_condition);
                         $stmt_vendor_with_condition -> execute();
                         $result_vendor_with_condition = $stmt_vendor_with_condition -> get_result();
                         if($result_vendor_with_condition -> num_rows > 0) {
                           while($row = $result_vendor_with_condition -> fetch_assoc()) {
                              echo "<span class='nav-item fs-5'> <input class='form-check-input' type='checkbox' onchange='mark_vendors(this)' value='".$row['id']."'><i class='bi bi-box-arrow-up-right float-end mr-4 text-center align-middle' title='view detail'></i><a href='#' class='nav-link'>".$row['vendor']." </a></span>";
                           }
                         }
                     ?>
              </ul>
            </div>
            </div>
          </div>
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">Sent Emails</h3>

              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" placeholder="Search Mail">
                  <div class="input-group-append">
                    <div class="btn btn-primary">
                      <i class="fas fa-search"></i>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-tools -->
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <div class="mailbox-controls">
                <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="far fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-reply"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-share"></i>
                  </button>
                </div>
                <button type="button" class="btn btn-default btn-sm">
                  <i class="fas fa-sync-alt"></i>
                </button>
                <div class="float-right">
                <span id='left1'>1</span>-<span id='right1'>200</span>/<span id='total'>200</span>
                  <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id='prev_btn' disabled onclick='prev(this)'>
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id='nxt_btn' onclick='next(this)'>
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped">
                  <tbody id='tbody'>
                    <?php 
                      $sql_emails_by_emailType_all = "SELECT * FROM emails WHERE email_type IS NOT NULL";
                      $stmt_emails_by_emailType_all = $conn->prepare($sql_emails_by_emailType_all);
                      $stmt_emails_by_emailType_all -> execute();
                      $result_emails_by_emailType_all = $stmt_emails_by_emailType_all -> get_result();
                      $length = $result_emails_by_emailType_all->num_rows;
                      $sql_emails_limit="SELECT * FROM emails WHERE email_type IS NOT NULL order by id desc LIMIT 20 ";
                      $stmt_emails_limit = $conn->prepare($sql_emails_limit);
                      $stmt_emails_limit -> execute();
                      $result_emails_limit = $stmt_emails_limit -> get_result();
                      $length2 = $result_emails_limit -> num_rows;
                      if($result_emails_limit -> num_rows>0)
                        while($row=$result_emails_limit -> fetch_assoc()){
                            echo ' <tr>
                            <td>
                              <div class="icheck-primary">
                                <input type="checkbox" value="'.$row['id'].'" id="check'.$row['id'].'">
                                <label for="check'.$row['id'].'"></label>
                              </div>
                            </td>
                            <td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td>
                            <td class="mailbox-name"><a href="./read-mail.php?id='.$row['id'].'">'.(explode('_',explode(':-:',$row['reason'])[0])[1]).'</a></td>
                            <td class="mailbox-subject"><b>'.$row['subject'].'</b> -'.explode('<br>',$row['data'])[0].'...
                            </td>
                            <td class="mailbox-attachment"></td>
                            <td class="mailbox-date">'.format($row['time']).'</td>
                          </tr>';
                      }
                    
                    echo "<input type='hidden' id='length' value='$length' name='$length2'/>";
                    ?>
                  </tbody>
                </table>
                
              </div>
            </div>
            
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
 
</div>
<script>

    var length_holder=document.getElementById("length");
    const left1=document.getElementById("left1");
    const right1=document.getElementById("right1");
    const total=document.getElementById("total");
    total.innerHTML=length_holder.value;
    right1.innerHTML=length_holder.name;
 
    function next(e){
    let xhr=new XMLHttpRequest();
    xhr.onload=function(){
      document.getElementById("tbody").innerHTML=this.responseText;
       length_holder.name=parseInt(length_holder.name)+20;
      if(parseInt(length_holder.name)>parseInt(length_holder.value)){
        length_holder.name=length_holder.value;
        e.setAttribute('disabled', '')
        right1.setAttribute('disabled', '')
      }else{
        right1.removeAttribute('disabled');
  
      }
      if(parseInt(length_holder.name)-20>0){
        document.getElementById('prev_btn').removeAttribute('disabled');
      }else{
        document.getElementById('prev_btn').setAttribute('disabled', '');
      }
      left1.innerHTML=right1.innerHTML;
    right1.innerHTML=length_holder.name;
    }
    xhr.open("GET","ajax_email.php?limit="+document.getElementById("right1").innerHTML);
    xhr.send();
  }
  function prev(e){
    let xhr=new XMLHttpRequest();
    xhr.onload=function(){
      document.getElementById("tbody").innerHTML=this.responseText;

       length_holder.name=parseInt(length_holder.name)-20>0?parseInt(length_holder.name)-20:parseInt(length_holder.name);
      if(parseInt(length_holder.name)-20<0){
        length_holder.name=length_holder.name;
        e.setAttribute('disabled', '');
      }else{
        e.removeAttribute('disabled');
      }
      if(parseInt(length_holder.name)+20<total){
        document.getElementById('nxt_btn').removeAttribute('disabled');
      }else{
        document.getElementById('nxt_btn').setAttribute('disabled', '');
      }
      right1.innerHTML=left1.innerHTML;
      left1.innerHTML=length_holder.name;
    }
    xhr.open("GET","ajax_email.php?limit="+document.getElementById("right1").innerHTML);
    xhr.send();
  }
     var vendor_list=[];
     if(vendor_list.length==0)
     document.getElementById("compose").href='';
  function mark_vendors(e){
    
    if(e.checked) {
    vendor_list.push(e.value);
    } else {
    vendor_list=vendor_list.filter(function(letter){
    return letter!=e.value;
})
    }
    document.getElementById("compose").href='compose.php?request_id=<?php echo $_GET['request_id']?>&venders='+vendor_list;
  }
  $(function () {
    //Enable check and uncheck all functionality
    $('.checkbox-toggle').click(function () {
      var clicks = $(this).data('clicks')
      if (clicks) {
        //Uncheck all checkboxes
        $('.mailbox-messages input[type=\'checkbox\']').prop('checked', false)
        $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square')
      } else {
        //Check all checkboxes
        $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
        $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square')
      }
      $(this).data('clicks', !clicks)
    })

    //Handle starring for font awesome
    $('.mailbox-star').click(function (e) {
      e.preventDefault()
      //detect type
      var $this = $(this).find('a > i')
      var fa    = $this.hasClass('fa')

      //Switch states
      if (fa) {
        $this.toggleClass('fa-star')
        $this.toggleClass('fa-star-o')
      }
    })
  })
</script>

<script src="<?php echo $pos.'../mailbox/jquery.min.js'?>"></script>
  <script src="<?php echo $pos.'../mailbox/bootstrap.bundle.min.js'?>"></script> 
 <script src="<?php echo $pos.'../mailbox/adminlte.min.js'?>"></script>
<script src="<?php echo $pos.'../mailbox/demo.js' ?>"></script>  
<script>
function vendors(e)
{
    document.getElementById("use_vendors").value = e.name;
    const req = new XMLHttpRequest();
    req.onload = function(){//when the response is ready
    document.getElementById("vendor_select_body").innerHTML=this.responseText;
    }
    req.open("GET", "Ajax_vendor.php?request_Details="+e.name);
    req.send();
}
</script>
<?php include "../../footer.php"; ?>
