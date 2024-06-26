<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
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
            <li class="breadcrumb-item active">Compose new Email</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';
    include  '../../common/head_mail.php';
    ?>
</div>
  
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">

          </div>
        </div>
      </div> 
    </section>
    <form class="content" action='allphp.php' method='post' >
      <div class="container-fluid">
      <div class="col-md-3">
          </div>
        <div class="row">
          <div class="col-md-1" style='width:5%;'> </div>
          <?php 
          $ids=explode(',',$_GET['venders']);
          $mail_list='';
          foreach($ids as $id)
          {
            $stmt_vendor_specific -> bind_param("i", $id);
            $stmt_vendor_specific -> execute();
            $result_vendor_specific = $stmt_vendor_specific -> get_result();
            if($result_vendor_specific -> num_rows>0)
              $result=$result_vendor_specific -> fetch_assoc();
              $mail_list.= explode(',',$result['email'])[0]." | ";
          }
          $req_list=explode(',',$_GET['request_id']);
          $str="";
          foreach($req_list as $id){
         $stmt_request->bind_param("i", $id);
         $stmt_request->execute();
         $result_request = $stmt_request->get_result();
         if($result_request->num_rows>0)
        $row = $result_request->fetch_assoc();
         $str.='<ul>
         <li>Item Name :-     <b>'.$row['item'].'</b></li>
         <li>Quantity:-        <b>'.$row['requested_quantity'].' '.($row['unit']==''?'pcs':$row['unit']).'</b></li>
         <li>Date Delivery before:-     <b>'.date("d-M-Y", strtotime($row['date_needed_by'])).'</b></li>
         <li>Given Specification:-     <b>'.($row['specification']==''?'No specification provided':$row['specification']).'</b></li>
         <li>Given Specification -  <b>'.$row['description'].'</b></li>
       </ul>';
          }
          ?> <input type="hidden" name="emails" value="<?php echo $_GET['venders'] ?>" />
          <input type="hidden" id="req_id" value="<?php echo $_GET['request_id'] ?>" />
          <div class="col-md-11" style='width:95%;'>
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">Compose New Message</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <input class="form-control" placeholder="To:" value='TO: <?php echo rtrim($mail_list,' | ') ?>'>
                </div>
                <div class="form-group">
                  <input class="form-control" placeholder="Subject:" name='subject' value="Email for performa collection">
                </div>
                <div class="form-group">
                    <textarea id="compose-textarea" name='email_body' class="form-control ml-4" style="height: 300px" readonly>
                    <p class="mt-4 ml-4  bold"> Dear , [Contact Person]</p>
                      <p class="ml-4 w-75">I hope this email finds you well. I am writing to request your proforma for goods that my company requires. 
                        We have been impressed with the quality of your products and believe that they would be an excellent fit for our needs. Specifically, we are interested in purchasing list of goods that are listed below in detail.
                        If possible, we would like to receive proforma for goods by by indicated delivery date to ensure that we can meet our company needs.</p>       
                       Proforma Request for :- 
                    <?php echo $str?>
                    <p class="ml-4 w-75">Please let us know if this is feasible in timely manner
                     Thank you for your attention to this matter. We look forward to working with you and utilizing your high-quality products.</p>
                    <p>[attachment]<p>
                      <b><p><i>Kind Regards</i></p>
                      <p><?=str_replace(".", " ",$_SESSION['username'])?>, <?= $_SESSION['position']?></p>
                      <p><i>Hagbes Pvt.Ltd. , www.hagbes.com</i></p></b>
                    </textarea>
                </div>
              </div>
              <div class="card-footer">
                <div class="float-start">
                  <!-- <button type="button" class="btn btn-default ml-4"><i class="fas fa-pencil-alt"></i>Draft</button> -->
                  <button type="submit" name='email_vendor'  value='<?php echo $_GET['request_id'] ?>'  class="btn btn-primary"><i class="far fa-envelope"></i>Send</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> 
    </form>
    
    <form action='allphp.php' method='GET' >
            <input type="hidden" name="vendors" value="<?php echo $_GET['venders'] ?>" />
              <button class='btn btn-success btn-sm relative' style='margin-left:9%;font-size:1.2em;margin-top:-1%;' type='submit' name='use_vendor' value='<?php echo $_GET['request_id'] ?>'>Continues With Vendors<i class='fa fa-envelope text-white ms-1'></i></button>
            </form>
  </div>

<script>
  $(function () {
    $('#compose-textarea').summernote()
  })
</script>  
 <script src="<?php echo $pos.'../assets/mailbox/adminlte.min.js'?>"></script>
<!-- <script src="<?php echo $pos.'../assets/mailbox/summernote-bs4.min.js' ?>"></script>   -->
<?php include "../../footer.php"; ?>
