<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include "../common/functions.php";
    $loc_pos = $_SESSION["position"];
    $id=$_GET['id'];
    $pid=$_GET['pid'];
    $loc_pos=$_GET['loc_pos'];
    $count = 0;
    $sql = "SELECT * FROM `performa` WHERE `id`= ?";
    $stmt_proforma = $conn->prepare($sql);
    $stmt_proforma->bind_param("i", $id);
    $stmt_proforma->execute();
    $result_proforma = $stmt_proforma->get_result();
    $row = $result_proforma->fetch_assoc();
    $allfiles = explode(":_:",$row['files']);
    ?>
    <style>
        .carousel-control-next,
        .carousel-control-prev /*, .carousel-indicators */ {
            filter: invert(100%);
        }
    </style>
    <div class='text-center mx-auto mb-4' style="width: 100px;">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button type='button' class="btn nav-link active" id='list_toggle' onclick="change_disp(this,'gallery')">
                    <i class='fas fa-list'></i>
                </button>
            </li>
            <li class="nav-item">
                <button type='button' class="btn nav-link" id='gallery_toggle' onclick="change_disp(this,'list')">
                    <i class='fas fa-tablet-alt'></i>
                </button>
            </li>
        </ul>
    </div>
    <?php
    $indic = "<div class='carousel-indicators'>";
    $counter_indic = 0;
    $dataa = "<div class='carousel-inner'>";
    echo "
    <div id='list_view'>
        <ul class= 'list-group list-group-flush text-start'>
        <li class='list-group-item list-group-item-primary mb-4 text-start'>
            <ul class= 'list-group list-group-flush text-start'>";
        foreach($allfiles as $file)
        {
            $count++;
            echo "
            <li class='list-group-item list-group-item-light text-strong'>
            File $count - <i class='text-primary'>$file </i>
            <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='btn btn-outline-primary border-0 float-end' download > <i class='fa fa-download'></i></a>
            </li>";
        }
        echo "</ul></ul></li>";
        echo "
        <!--<div class='text-center'>
        <button onclick='load_performa(this)' name='".$pid."' type='button' class='btn btn-sm btn-outline-primary m-auto' data-bs-toggle='modal' data-bs-target='#modal_performa'>
            Upload New Proforma
        </button>
        </div>-->
    </div>
    <div id='gallery_view' class='d-none'>
        <div id='Galleryperforma' class='carousel slide carousel-fade' data-bs-ride='carousel'>
            ";
            $counter=0;
        foreach($allfiles as $file)
        {
            if(!strpos(strtolower($file),".pdf")){
                $counter++;
            $temp = ($counter_indic==0)?" class='active' aria-current='true' ":"";
            $temp2 = ($counter_indic==0)?" active":"";
            $indic .=
            "<button type='button' data-bs-target='#Galleryperforma' data-bs-slide-to='$counter_indic' aria-label='Slide ".($counter_indic+1)."' $temp></button>";
            $dataa .= "
            <div class='carousel-item$temp2'>
                <img class='d-block w-100' src='https://portal.hagbes.com/lpms_uploads/".$file."'>
            </div>";
            $counter_indic++;
            }
        }
        $indic .= "</div>";
        $dataa .= "</div>";
        if($counter){
        echo 
        "$indic $dataa<a class='carousel-control-prev' href='#Galleryperforma' role='button' type='button' data-bs-slide='prev'>
            <span class='carousel-control-prev-icon' aria-hidden='true'></span>
        </a>
        <a class='carousel-control-next' href='#Galleryperforma' role='button' data-bs-slide='next'>
            <span class='carousel-control-next-icon' aria-hidden='true'></span>
        </a>
        </div>
        ";
    echo "
    </div>
    ";
        }else{
        echo " <div class='py-2'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h3 class='mt-4 text-white'>Preview not supported</h3>
            </div>
        </div>";
        }
}
else
{
    echo $go_home;
}
$conn->close();
$conn_pms->close();
$conn_fleet->close();
$conn_ws->close();
$conn_ais->close();
$conn_sms->close();
$conn_mrf->close();
?>