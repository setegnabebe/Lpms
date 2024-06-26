<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Under Maintainance</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/pages/error.css">
    <link rel="icon" href="img/Hagbeslogo.jpg">
</head>

<body>
    <div id="error">
<div class="error-page container">
    <div class="col-sm-6 col-md-2 offset-md-2 mx-auto">
        <img class="img-error" src="img/undermainance.jpg" alt="Not Found">
    </div>
    <div class="col-md-8 col-12 offset-md-2">
        <div class="text-center">
            <h1 class="error-title">LPMS is Under Maintainance</h1>
            <p class='fs-5 text-gray-600'>This wont take long <br>Please be patient while we maintain the system <br><br>
            <!-- <p class='fs-5 text-gray-600'>Until the Web site is operational <br>Please Continue the Purchase Proccess as Previous done in Paper<br><br> -->
            If You Have any questions please contact :<br>
            <span class="text-primary">Tegenu Matewos : </span> - Tegenu.Matewos@hagbes.com - +251912789049<br>
            <span class="text-primary">Dagem Adugna : </span> - Dagem.Adugna@hagbes.com - +251911474028<br>
            <span class="text-primary">Gashu Wendawke : </span> - Gashu.Wendawke@hagbes.com - +251928549312<br>
            </p>
            <!-- <a href="index.html" class="btn btn-lg btn-outline-primary mt-3">Go Home</a> -->
        </div>
    </div>
    <?php
    include_once "common/email.php";
     send_auto_email("LPMS","test","TEST Email","Dagem.Adugna@hagbes.com,Dagem.Adugna","Hagbes HQ.,Hagbeslogo.jpg","","Dagem.Adugna",""); 
    ?>
    
</div>

</div>
</body>

</html>
                    <!-- $ipAddress = $_SERVER['REMOTE_ADDR']; -->

