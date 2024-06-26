
<?php include "head.php"?>
<script>
    document.getElementsByTagName("title")[0].innerHTML = "Successfully Sent";
</script>
<div class="error-page container">
    <div>
        <div class="w-25 mx-auto">
            <img class="img-error" src="../assets/images/samples/password.png" alt="Not Found">
        </div>
        <div class="text-center">
            <h1 class="error-title">Successful</h1>
            <p class='fs-5 text-gray-600'>Email Sent to Reset Password.</p>
            <a href="../index.php" class="btn btn-lg btn-outline-primary mt-3">Go Home</a>
        </div>
    </div>
</div>
<?php include "footer.php"?>
