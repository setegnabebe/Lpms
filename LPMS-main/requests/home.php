<?php $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":""); ?>
<script>
set_title("LPMS | Home");
sideactive("index");
</script>
<div id="main">
    <div class="row">
        <header class="col-sm-4 col-lg-7 col-xl-7">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a> 
            <?php // req_count($conn,$conn_fleet,'Consumer Goods', $_SERVER['PHP_SELF']); ?>
            <h5>Local Procurement Management System</h5>
            <h6 class='ms-4'>Request Categories</h6>
        </header>
        <?php include $pos.'../common/profile.php';?> 
    </div>
<div class="mt-5">
    <div class="row mx-auto">
    <?php 
        $temp='';
        if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner') $temp ="";
        else if(strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["department"] == 'Property' || $_SESSION["department"] == 'Procurement') $temp .="company,".$_SESSION['company'];
        else if($_SESSION["a_type"] == 'manager' && $_SESSION["department"] != 'Procurement'  && $_SESSION["department"] != 'Property') $temp .="department,".$_SESSION['department'].",company,".$_SESSION['company'];
        else if($_SESSION["department"] != 'Procurement' && $_SESSION["department"] != 'Property') $temp .="customer,".$_SESSION['username'].",company,".$_SESSION['company'];
    ?>
    <?php
        $query = "SELECT * from catagory order by catagory Asc";
        $stmt_catagories = $conn -> prepare($query);
        $stmt_catagories -> execute();
        $result_catagories = $stmt_catagories -> get_result();
        if($result_catagories -> num_rows>0)
            while($re = $result_catagories -> fetch_assoc())
            {
                if(in_array($_SESSION["company"],$privilege[$re['catagory']]) || in_array("All",$privilege[$re['catagory']]))
                {
                    $na_t=str_replace(" ","",$re['catagory']);
                    $name=explode(" ",$re['display_name']);
                    $type_str =(strpos($re['path'],"Form") !== False)?"?r_type=".$re['catagory']:"";
                    $second = "";
                    if(sizeof($name)>2)
                    {
                        $first_n = $name[0]." ".$name[1];
                        $i = 2;
                    }
                    else 
                    {
                        $first_n = $name[0];
                        $i = 1;
                    }
                    for(;$i<sizeof($name);$i++)
                        $second.=" ".$name[$i];
                    $temp_x = (($temp == "")?"":",")."request_type,".$re['catagory'];
                    $counter_type = badge_count_requests($conn,$conn_fleet,"$temp$temp_x");
            ?>

            <div class="col-xl-4 col-md-6 col-sm-12 mb-3">
                <div class="card mb-0">
                    <div class="card-content">
                        <img src="<?php echo $pos."../img/".$re['image']?>" style="max-width:100%" class="card-img-top img-fluid"
                            alt="singleminded">
                        <div class="card-body">
                            <h5 class="card-title text-center"><a href="<?php echo $pos."../".$re['path'].$type_str?>"><?=ucfirst($re['catagory'])?> (<?=$counter_type?>)</a></h5>
                            <p class="card-text">
                                <?=$re['description']?>
                            </p>
                        </div>
                    </div>
                    <a class='btn btn-outline-primary' href="<?php echo $pos."../".$re['path'].$type_str?>">Request <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                </div>
            </div>
                <!-- <div class="position-relative col-sm-12 col-md-6 col-lg-6 col-xl-3 mb-5" style="z-index:0" data-aos="fade-down-right">
                        <span class="position-absolute top-0 start-50 translate-middle rounded-pill badge alert-primary p-3" style="z-index:1">
                            <?php 
                                $temp_x = (($temp == "")?"":",")."request_type,".$re['catagory'];
                                echo badge_count_requests($conn,$conn_fleet,"$temp$temp_x")
                            ?> 
                            <span class="visually-hidden">Requests Collected</span>
                        </span>
                    <figure class="options"><img src="<?php echo $pos."../img/".$re['image']?>"/>
                    <figcaption>
                        <h2 class="rounded mx-2"><?php echo $first_n?> <span><?php echo $second?></span></h2>
                    </figcaption>
                    <a href="<?php echo $pos."../".$re['path'].$type_str?>"></a>
                    </figure>
                </div> -->
            <?php
                }
            }
    ?>
</div> 
</div>
</div>