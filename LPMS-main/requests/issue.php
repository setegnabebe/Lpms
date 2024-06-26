<?php 

session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>Previous CPVs</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Issues");
    sideactive("issues_page");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Issues</h2>
            <!-- <span data-bs-html='true' class='btn ms-0 badge rounded-pill' data-bs-toggle='popover' title='Hints' data-bs-content='- Please fill in Issues you found about specific requests<br> - Issues will be pending until it is closed<br> - Issues opened by you can only be closed by you or System Administrators'><i class='fa fa-info-circle text-primary' title='Details'></i></span> -->
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Issues</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <?php
    $issue = (isset($_POST['issue']))?$_POST['issue']:((isset($_SESSION['last_issue']))?$_SESSION['last_issue']:"");
    $btn_content = ($issue != "")?" name='issue' value='".$issue."'":"name='view_issues' value='Open'";
    ?>
    <div class="">
        <button class="form-control d-none" id='loader_issue' <?=$btn_content?>>LOAD</button>
        <!-- <h5 class='text-center'>Issues<span data-bs-html='true' class='btn btn-sm badge rounded-pill text-sm' data-bs-toggle='popover' title='Hints' data-bs-content='- Please fill in Issues you found about specific requests<br> - Issues will be pending until it is closed<br> - Issues opened by you can only be closed by you or System Administrators'><i class='fa fa-info-circle text-primary' title='Details'></i></span></h5> -->
    <h5 class='container'>
        This <b>Issue page</b> is a platform where users can freely raise concerns on specific requests without holding back the request. <br>
        To create a new issue go to Purchase order page and Click on Raise Issue Button on the request.<br>
        To Mention the person you want to respond, just write their username after an @ symbole (Eg. @firstname.lastname)
    </h5>
        <form method="POST" action="../requests/allphp.php" enctype="multipart/form-data">
            <div id='issue_content' class=' p-2'></div>
        </form>
    </div>
    <script>
        modal_optional(document.getElementById("loader_issue"),"issue","<?=$issue != ""?"":"active"?>","issue_content");
    </script>
</div>
</div>
<?php include '../footer.php';?>