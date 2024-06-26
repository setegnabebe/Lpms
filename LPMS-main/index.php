<?php 

    session_start();
    include "connection/connect.php";
    if(isset($_SESSION["username"])){
        $pagee = (isset($_GET['url']))?$_GET['url']:$_SESSION['loc'];
        if(isset($_GET['url']))
        {
            if(strpos($pagee,"rocurement"))
            {
                if($_SESSION["role"]=='Purchase officer')
                    $_SESSION["loc"] = "procurement/junior/";
                else if($_SESSION["role"]=='GS' || $_SESSION["role"]=='user')
                    $_SESSION["loc"] = "procurement/GS/";
                else if($_SESSION["role"]=='manager')
                    $_SESSION["loc"] = "procurement/manager/";
                else if($_SESSION["role"]=='Senior Purchase officer')
                    $_SESSION["loc"] = "procurement/senior/";
            }
        }
        header("Location: ".$pagee);
    }
    else if(isset($_GET['url']))
    {
        $_SESSION['page'] = $_GET['url'];
    }
    include "auto_request.php";
?>
<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="img/Hagbeslogo.jpg">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet"> -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/pages/auth.css">
    <!-- <script src="assets/jquery.min.js"></script> -->
    <link href="assets/css/aos.css" rel="stylesheet">
    <script src="assets/js/aos.js"></script>
    <!-- <script src="https://kit.fontawesome.com/8f3384bc9f.js" crossorigin="anonymous"></script> -->
</head>

<body style=" overflow: hidden;">
<div id="auth">
    <!-- <h1 class="h1 text-center"></h1> -->
    <div class="row h-100 ">
        <div class="col-xl-6 col-12 shadow">
            <div id="auth-left" class="mx-5 shadow mt-5"  data-aos="fade-up-right">
                <h1 class="h1 text-center" title="Local procurement Managment System">LPMS | Log in</h1>
                    <?php 
                        if(isset($_SESSION["fail"])){
                            echo $_SESSION["fail"];
                            unset($_SESSION["fail"]);
                        }
                    ?>
                        <hr class="m-auto my-5">
                    <form method="post" action="allphp.php">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl rounded-pill" placeholder="Username" name="username" required>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl rounded-pill" placeholder="Password" name="password" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block btn-lg shadow-lg rounded-pill">Log in</button>
                      <a href="http://portal.hagbes.com" class="btn btn-info text-white w-100 rounded-pill mt-2"><i class="bi bi-arrow-left"></i>Portal home</a>
                    </form>
                <div class="text-center mt-5 text-lg fs-4">
                    <p class='text-gray-600'>
                        Forgot your account? <a href="forgot_password.php" class="font-bold">Click Here</a>.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-6 d-none d-xl-block shadow"  data-aos="fade-down-left">
     <!-- data-aos-easing="linear" data-aos-duration="2500"> -->
            <div>
                <img src="img/procurement.jpg" class="h-100 w-100">
            </div>
        </div>
    </div>
</div>

</body>
</html>
<script src="https://www.google.com/recaptcha/api.js?render=6Le1FKsZAAAAAG5iOWZwMB4KQXLFtUbonm55oxfC"></script>
<script>
    function onClick(e) {e.preventDefault();grecaptcha.ready(function() {grecaptcha.execute('6Le1FKsZAAAAAG5iOWZwMB4KQXLFtUbonm55oxfC', {action: 'submit'}).then(function(token) {console.log(token);});});}
</script>
<script>
    AOS.init({
    duration: 1500,
    })
</script>
<?php 
if(isset($_SESSION["success"]))
{
    echo "<script> swal('Successful!','Password Was Successfully changed','success')</script>";
    unset($_SESSION["success"]);
}

?>