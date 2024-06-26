<?php $pos = (strpos($_SERVER['PHP_SELF'],'Procurement'))?"../":((strpos($_SERVER['PHP_SELF'],'procurement'))?"../":""); ?>
<!-- <div class="" data-aos="fade-left" style="text-align: right;z-index: 999"> -->
<?php

?>
<nav class="container-fluid col-sm-12 col-md-6 col-lg-5 col-xl-5 navbar-expand navbar-light ">
    <div class=" navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <!-- <li class="nav-item dropdown me-1">
                <a class="nav-link active dropdown-toggle text-gray-600" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class='bi bi-envelope bi-sub fs-4'></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li>
                        <h6 class="dropdown-header">Mail</h6>
                    </li>
                    <li><a class="dropdown-item" href="#">No new mail</a></li>
                </ul>
            </li> -->
            <?php if($survey || $survey_condition)
            {?>
            <li class="nav-item dropdown">
                <button class="btn btn-info btn-sm" type="button" id="feedback_btn" name="feedback" onclick="modal_optional(this,'feedback')" 
                    aria-expanded="false" data-bs-toggle='modal' data-bs-target='#view_optionalModal'><!--  position-relative --><!--  onclick='load_sms(this)' -->
                    <?=($has_feedback)?"View ":""?>Survey
                </button>
            </li>
            <?php }?>
            <?php if(($_SESSION["role"]=="Admin" || isset($_SESSION['admin-access'])) && (!isset($_SESSION['attempt-admin-pass']) || $_SESSION['attempt-admin-pass']<3))
            {?>
            <li class="ms-3 nav-item dropdown">
                <button class="btn btn-outline-danger btn-sm" type="button" id="user_select_btn" name="user_select_btn"
                    aria-expanded="false" data-bs-toggle='modal' data-bs-target='#user_select'><!--  position-relative --><!--  onclick='load_sms(this)' -->
                    Secret Login
                </button>
            </li>
            <?php }?>
            <?php
                // if(strpos($_SESSION["a_type"],"Committee")===false && strpos($_SESSION["a_type"],"Owner")===false)
                $issue_val = badge_count_custom($conn,$conn_fleet,"issues","status,Open,thread,IS NULL");
            ?>
            <li class="nav-item dropdown ms-3 d-none">
                <button class="position-relative btn btn-outline-danger btn-sm" type="button" id="Issue_btn"
                        name="view_issues" onclick="modal_optional(this,'issue')" 
                        aria-expanded="false" data-bs-toggle='modal'
                        data-bs-target='#view_optionalModal'>
                    Issues 
                    <?php if($issue_val > 0){?>
                    <span class="position-absolute top-0 start-100 translate-middle rounded-pill badge bg-primary">
                        <?=$issue_val?>
                        <span class="visually-hidden">Issues</span>
                    </span>
                    <?php }?>
                </button>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link active dropdown-toggle text-gray-600" href="#" 
                    aria-expanded="false" onclick='load_sms(this)' data-bs-toggle='modal' data-bs-target='#view_sms'><!--  position-relative -->
                    <i class='bi bi-bell bi-sub fs-4'></i>
                </a>
            </li>
        </ul>
        <div class="dropdown ">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" onmouseover="this.click()">
            <?php echo $_SESSION['username']?>
            </button>
                <?=isset($_SESSION['logged-in-as'])?'
                <span class="badge bg-secondary mt-1 d-block fw-bold text-sm text-center " title="login-as">As <span title="login-as">'.$_SESSION['logged-in-as'].'</span></span>':''?>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li>
                    <span class="dropdown-item fw-bold my-0 py-0"  title="Company-Department"><i class="fas fa-map-marker-alt me-2"></i> <span title="Company-Department"><?php echo $_SESSION['company']?></span> | <span title="Account Type"><?php echo $_SESSION['department']?></span></span>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <span class="dropdown-item fw-bold my-0 py-0"  title="Role"><i class="fa fa-user me-2"></i> <span title="Role"><?php echo $_SESSION['role']?></span> | <span title="Account Type"><?php echo $_SESSION['a_type']?></span></span>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li><a href="#" class="dropdown-item fw-bold my-0 py-0"  title="Update Profile" data-bs-toggle="modal" data-bs-target="#profileModal" id='change_profile'><i class="far fa-user-circle me-2"></i> Update Profile</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a href="#" class="dropdown-item fw-bold my-0 py-0"  title="Change Password" data-bs-toggle="modal" data-bs-target="#passmodal" id='change_p'><i class="fa fa-lock me-2"></i> Change Password</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item fw-bold my-0 py-0" href="<?php echo $pos?>../logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- </div> -->