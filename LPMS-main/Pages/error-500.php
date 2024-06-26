<?php include "head.php"?>
<script>
    document.getElementsByTagName("title")[0].innerHTML = "System Error";
</script>
<div class="error-page container">
    <div class="col-md-8 col-12 offset-md-2">
        <img class="img-error" src="../assets/images/samples/error-500.png" alt="Not Found">
        <div class="text-center">
            <h1 class="error-title">System Error</h1>
            <p class="fs-5 text-gray-600">The website is currently unaivailable. Try again later or contact the
                developer.</p>
            <a href="index.html" class="btn btn-lg btn-outline-primary mt-3">Go Home</a>
        </div>
    </div>
</div>
<?php include "footer.php"?>
