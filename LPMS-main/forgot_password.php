<?php 
    session_start();
include "connection/connect.php";
    if(isset($_SESSION["username"])){
        header("Location: ".$_SESSION['loc']);
    }
    if(isset($_GET["id"]))
    {
        $sql_forgot_password = "SELECT * FROM forgot_password_links where `Link` = ?";
        $stmt_forgot_password = $conn -> prepare($sql_forgot_password);
        $stmt_forgot_password -> bind_param("s", $_GET["id"]);
        $stmt_forgot_password -> execute();
        $result_forgot_password = $stmt_forgot_password -> get_result();
        if($result_forgot_password -> num_rows > 0)
        {
            while($row = $result_forgot_password -> fetch_assoc()) 
            {
                if($row['status'] == "new")
                {
                    $date_fromdb = explode(":-:",$row['date'])[0];
                    $date1 = date_create($date_fromdb);
                    $date2 = date_create(date("Y-m-d H:i:s"));
                    $diff=date_diff($date1,$date2);
                    $date_past = intval($diff->format("%a"));
                    if($date_past < 11)
                    {
                        $_SESSION['forgot_link'] = $_GET["id"];
                        $un = $row['Requested_by'];
                        $email = $row['Sent_to'];
                    }
                    else
                    {
                        $_SESSION["fail"] = "<h5 class='text-danger text-center mt-2'>*Link Has Expired Please Request another*</h5>";
                    }
                }
                else if($row['status'] == "expired")
                {
                    $_SESSION["fail"] = "<h5 class='text-danger text-center mt-2'>*Link Has Expired Please Request another*</h5>";
                }
                else
                {
                    $_SESSION["fail"] = "<h5 class='text-danger text-center mt-2'>*Link Was previously Used Please Request another*</h5>";
                }
            }
        }
    }
?>
<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet"> -->
    <link rel="icon" href="img/Hagbeslogo.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/pages/auth.css">
    <!-- <script src="assets/jquery.min.js"></script> -->
    <link href="assets/css/aos.css" rel="stylesheet">
    <script src="assets/js/aos.js"></script>
    <!-- <script src="https://kit.fontawesome.com/8f3384bc9f.js" crossorigin="anonymous"></script> -->
    <script>
    function chanagepass(e)
    {
        var npass= document.getElementById('new_pass').value;
        var cpass= document.getElementById('conf_pass').value;
        if(npass != cpass)
            document.getElementById("warn").innerHTML="** Password Doesn't Match **";
        else
        {
            document.getElementById("warn").innerHTML="";
            e.type='submit';
            e.click();
        }
    }
</script>
</head>

<body>
<div id="auth">
    <!-- <h1 class="h1 text-center"></h1> -->
    <div class="row h-100 ">
        <div class="col-xl-6 col-12 shadow">
            <div id="auth-left" class="mx-5 shadow mt-5"  data-aos="fade-down">
                <?php if(isset($un))
                {?>
                    <h1 class="h1 text-center" title="Reset Password">Change Password</h1>
                    <hr class="m-auto my-5">
                    <form action="allphp.php" method="POST">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl rounded-pill" placeholder="Username" name="username" value = "<?php echo $un?>" readonly>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input name="email" type="email" class="form-control form-control-xl rounded-pill" placeholder="Email" value = "<?php echo $email?>" readonly>
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                        <span class="text-danger" id="warn"></span>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl rounded-pill" id='new_pass' placeholder="New Password" name="password" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl rounded-pill" id='conf_pass' placeholder="Comfirm Password" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button type="button" onclick="chanagepass(this)" class="btn btn-primary btn-block btn-lg shadow-lg mt-5" name="set_password">Set Password</button>
                    </form>
                <?php }
                else
                {?>
                    <h1 class="h1 text-center" title="Reset Password">Forgot Password</h1>
                    <p class="auth-subtitle mb-5 text-center">Enter your Email and Username</p>
                    <hr class="m-auto my-5">
                        <?php 
                            if(isset($_SESSION["fail"]))
                            {
                                echo $_SESSION["fail"];
                                unset($_SESSION["fail"]);
                            }
                        ?>
                    <form action="allphp.php" method="POST">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl rounded-pill" placeholder="Username" name="username" required>
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input name="email" type="email" class="form-control form-control-xl rounded-pill" placeholder="Email" required>
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5" name="forgot">Send</button>
                    </form>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>Remember your account? <a href="index.php" class="font-bold">Log in</a>.
                        </p>
                    </div>
                    <?php }?>
            </div>
        </div>
        <div class="col-xl-6 d-none d-xl-block shadow"  data-aos="fade-up"><!-- data-aos-easing="linear" data-aos-duration="2500"> -->
            <div>
                <img src="img/procurement.jpg" class="h-100 w-100">
            </div>
        </div>
    </div>
</div>
</body>

</html>
<script>
    AOS.init({
    duration: 1500,
    })
</script>
