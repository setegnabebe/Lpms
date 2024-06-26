
<script src="<?php echo $pos?>../assets/bootstrap/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- <script src="<?php //echo $pos?>../assets/js/extensions/sweetalert2.js"></script> -->
<!-- <script src="<?php //echo $pos?>../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script> -->
<!-- <script src="<?php //echo $pos?>../assets/sweetalert.css"></script> -->
<!-- <script src="<?php //echo $pos?>../assets/sweetalert.min.js"></script> -->

<script src="<?php echo $pos?>../assets/fontAwesome.min.js" crossorigin="anonymous"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script> -->
<script src="<?php echo $pos?>../assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<!-- <script src="<?php echo $pos?>../assets/js/bootstrap.bundle.min.js"></script> -->
<!-- <script src="<?php echo $pos?>../assets/vendors/toastify/toastify.js"></script> -->
<!-- <script src="<?php echo $pos?>../assets/js/extensions/toastify.js"></script> -->
<script src="<?php echo $pos?>../assets/vendors/quill/quill.min.js"></script>
<!-- <script src="<?php echo $pos?>../assets/js/pages/form-editor.js"></script> -->
<script src="<?php echo $pos?>../assets/vendors/apexcharts/apexcharts.js"></script>
<!-- <script src="<?php //echo $pos?>../assets/js/pages/dashboard.js"></script> -->
<!-- <script src="<?php echo $pos?>../assets/js/extensions/sweetalert2.js"></script> -->
<!-- <script src="<?php echo $pos?>../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script> -->
<script src="<?php echo $pos?>../assets/sweetalert.min.js"></script> -->
<script src="<?php echo $pos?>../assets/vendors/choices.js/choices.min.js"></script>
<script src="<?php echo $pos?>../assets/js/pages/form-element-select.js"></script>
<script src="<?php echo $pos?>../assets/vendors/simple-datatables/simple-datatables.js"></script>
<script src="<?php echo $pos?>../assets/vendors/chart.js/chart.min.js"></script>
<script src="<?php echo $pos?>../assets/vendors/echarts/echarts.min.js"></script>
<script src="<?php echo $pos?>../assets/vendors/rater-js/rater-js.js"></script>
<script src="<?php echo $pos?>../assets/js/extensions/rater-js.js"></script>
<script src="<?php echo $pos?>../assets/js/mazer.js"></script>
<script  src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script  src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script  src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://fastly.jsdelivr.net/npm/echarts@5.4.2/dist/echarts.min.js"></script>

<script>
  $(function () {
    $('#compose-textarea').summernote()
  })
</script> 
<script src="<?php echo $pos?>../assets/mailbox/summernote-bs4.min.js"></script>
<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    "showDropdowns": true,
    "linkedCalendars": false,
    "showCustomRangeLabel": false,
  }, function(start, end, label) {
    var btn=document.getElementById('date');
    btn.value=start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD');
    btn.click();
  });
});
</script>
<script type="text/javascript">
  jQuery(document).ready(function($) {
// Javascript to enable link to tab
var url = document.location.toString();
if (url.match('#')) {
    $('.nav-pills a[href="#' + url.split('#')[1] + '"]').tab('show');
} 
// Change hash for page-reload
$('.nav-pills a').on('shown.bs.tab',function (e){
    window.location.hash = e.target.hash;
  });

if (url.match('#')) {
    $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
} 
// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
  });
});
</script>


