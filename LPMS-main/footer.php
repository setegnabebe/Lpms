
<div class="position-fixed" style="bottom: 7%; right:3%;">
<button class="btn btn-lg btn-info float-end position-relative" data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='chat_box()'>Chat<i class="fa fa-comment"></i>
<span class="position-absolute top-0  badge rounded-pill bg-danger text-sm d-none" id="chat_badge"> </span></button>
</div>
<script>
  
//  setInterval(() =>{
//     var xmlhttp = new XMLHttpRequest();
//     var badge=document.getElementById("chat_badge");
//     xmlhttp.onreadystatechange = function() {
//       if (this.readyState == 4 && this.status == 200) {
//         var number=this.responseText;
//         if((number>0))
//         {
//             badge.classList.remove('d-none');
//             badge.innerHTML = this.responseText;
//         }
//         else{
//             badge.classList.add('d-none');
//         }
//       }
      
//     };
//     xmlhttp.open("GET",pos+"../chat/chatCounter.php",true);
//     xmlhttp.send();
//  }, 500);
 
        </script>
        <style type="text/css">
  #toTop{
  position: fixed;
  bottom: 1%;
  right: 14%;
  cursor: pointer;
  display: none;
  z-index: 9999;
}
</style>
<script type="text/javascript">
    function setIssueBtn()
    {
        $('.focus').mouseenter(function(){
            if($(this).children(".issue_btn").length > 0)
            {
                $(this).children(".issue_btn").removeClass("d-none");
            }
            if($(this).children(".issue_holder").length > 0)
            {
                $(this).children(".issue_holder").children(".issue_btn").removeClass("d-none");
            }
        });
        $('.focus').mouseleave(function(){
            if($(this).children(".issue_btn").length > 0)
            {
                $(this).children(".issue_btn").addClass("d-none");
            }
            if($(this).children(".issue_holder").length > 0)
            {
                $(this).children(".issue_holder").children(".issue_btn").addClass("d-none");
            }
        });
    }
  $(document).ready(function(){
      $('body').append('<div id="toTop" title="To Top"><i class="fa fa-arrow-circle-up" style="font-size:36px;"></i></div>');
      $(window).scroll(function () {
      if ($(this).scrollTop() > 400) {
        $('#toTop').fadeIn();
      } else {
        $('#toTop').fadeOut();
      }
    }); 
    $('#toTop').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 2000);
        return false;
    });
    setIssueBtn();
});
</script>
<?php 
    $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":"");
    if(isset($_SESSION['passfail']))
    {
        ?>
        <script>
            document.getElementById('change_p').click();
            document.getElementById("warnpass").innerHTML = "<?php echo $_SESSION['passfail']?>";
            document.getElementById("badge").innerHTML =12;
        </script>
        <?php
        unset($_SESSION['passfail']);
    }
    else
    { ?>
        <script> if(document.getElementById("warnpass"))document.getElementById("warnpass").innerHTML = ""; </script>
<?php }
?>
</div>
<div class='noScreen' id='printonly'></div>
    <footer class="py-3 mt-3 bg-light bg-opacity-75 noPrint ">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-dark">Copyright &copy; local procurement 2022</div>
                <div>
                    <a href="#">Privacy Policy</a>
                    &middot; 
                    <a href="#">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </footer>
</body>
<?php include $pos."../common/footer_script.php"?>
<?php
    if(!$has_feedback && !isset($_SESSION['passfail']) && !isset($_SESSION['shown']) && ($survey))
    {
        $_SESSION['shown'] = true;
        ?>
        <script>
            document.getElementById('feedback_btn').click();
        </script>
        <?php
    }
?>
<script src="https://www.google.com/recaptcha/api.js?render=6Le1FKsZAAAAAG5iOWZwMB4KQXLFtUbonm55oxfC"></script>
<script>
    function onClick(e) {e.preventDefault();grecaptcha.ready(function() {grecaptcha.execute('6Le1FKsZAAAAAG5iOWZwMB4KQXLFtUbonm55oxfC', {action: 'submit'}).then(function(token) {console.log(token);});});}
</script>
<!-- <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script> -->
<script>//81036
<?php // if(!isset($_SESSION['warned'])){$_SESSION['warned']=true;?>
   // document.getElementById('warning_btn').click();
<?php // }?>
<?php if(strpos($_SERVER['PHP_SELF'],'dbsEditor') === false)
{?>
if (window.history.replaceState) {
  window.history.replaceState( null, null, window.location.href );
}
<?php }?>
$(function () {
    $('[data-bs-toggle="popover"]').popover()
});
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip()
});
    AOS.init({
    duration: 1500,
    })
   
    var dataTable_holder = [];
    function datatable()
    {
        dataTable_holder = [];
        for(let inc_tbl = 1;1;inc_tbl++)
        {
            let table1 = document.querySelector('#table'+inc_tbl);
            if(table1)
                dataTable_holder[inc_tbl] = new simpleDatatables.DataTable(table1);
            else
                break;
        }
        let counter = (dataTable_holder.length)?dataTable_holder.length:1;
        return counter;
    }
    var datatable_count = datatable();
</script>
</html>
<?php 
if(isset($_SESSION["success"]))
{
    $msg = ($_SESSION["success"]===true)?"Operation Successful!":$_SESSION["success"];
    echo "<script> swal.fire({title:'Successful!',text:'".$msg."',icon:'success'})</script>";
    unset($_SESSION["success"]);
}
if(isset($_SESSION["failed_attempt"]))
{
    $msg = ($_SESSION["failed_attempt"]===true)?"Operation Failed!":$_SESSION["failed_attempt"];
    echo "<script> swal.fire('Failed!','".$msg."','error')</script>";
    unset($_SESSION["failed_attempt"]);
}
if(isset($_SESSION['fleet_request']))
{
    ?><script>document.getElementById('fleet_request_btn').click();</script><?php 
    unset($_SESSION['fleet_request']);
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