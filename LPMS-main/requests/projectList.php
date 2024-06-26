<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
if(!in_array($_SESSION["company"],$privilege["Consumer Goods"]) && !in_array("All",$privilege["Consumer Goods"]))
{
    header("Location: ../");
}
unset($_SESSION['project_name']);
?>
<script>
sideactive("ConsumerGoods_side");
set_title("LPMS | Consumer Goods");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>All Currently Active Projects</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Projects List</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
        <div class="container-fluid row" data-aos="zoom-out">
            <!-- <div class="col-4"> </div> -->
            <?php req_count($conn,$conn_fleet,"Consumer Goods"); ?>
            <div class = 'container shadow'>
            <div class="row m-auto container card-body">
                <form method="GET" action="allphp.php">
                    <table class="table table-hover w-75 m-auto" id='table1'>
                        <thead class='table-primary'>
                            <tr>
                                <!-- <th scope="col">Project project_id</th> -->
                                <th scope="col">Name</th>
                                <th scope="col">Status</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $common_string = "";
                                $common_string .=(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["role"]=="Owner")?"":",customer,".$_SESSION["username"];
                                $common_string .=(strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"]!="Procurement" && $_SESSION["department"]!="Property")?",department,".$_SESSION['department']:"";
                                $common_string .=(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":",company,". $_SESSION['company'];
                                $sql = "SELECT * FROM project ORDER BY status ASC";
                                $stmt_projects = $conn -> prepare($sql);
                                $stmt_projects -> execute();
                                $result_projects = $stmt_projects -> get_result();
                                if($result_projects->num_rows>0)
                                    while($row = $result_projects->fetch_assoc())
                                    {
                                        if($row['status'] == "open")
                                        {
                                            $icon = "<i class='fas fa-check-circle text-success'></i>";
                                            $disable = "";
                                        }
                                        else
                                        {
                                            $icon = "<i class='far fa-window-close text-danger'></i>";
                                            $disable = " disabled";
                                        }
                                        echo "<tr><td class='text-capitalize'>".$row['Name']."</td>
                                        <td class='text-capitalize'>".$row['status']." $icon</td>
                                        <td class='position-relative'>
                                        <button type='submit' value='General' name='Request_CG' class='btn btn-outline-primary'>Request</button>
                                        <span class='position-absolute top-0 start-100 translate-middle rounded-pill badge bg-success'>".
                                        badge_count_requests($conn,$conn_fleet,"request_type,Consumer Goods,request_for,".$row['project_id']."".$common_string)."
                                            <span class='visually-hidden'>Requests</span>
                                        </span>
                                        </td></tr>";
                                    }
                                else
                                {
                                    echo "<script>document.getElementById('tbl1').style.visibility='hidden';</script>";
                                    echo "<h1 class='d1 text-center'>No Current Projects</h1>";
                                }
                            ?>
                        </tbody>
                    </table>
                    <table class="table table-hover w-75 m-auto" id='table2'>
                        <thead class='table-primary'>
                            <tr>
                                <!-- <th scope="col">Project project_id</th> -->
                                <th scope="col">Name</th>
                                <th scope="col">Type</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql = "SELECT * FROM projects WHERE `status` = '2' ORDER BY creation_date DESC";
                                $stmt_project_pms_active = $conn_pms -> prepare($sql);
                                $stmt_project_pms_active -> execute();
                                $result_project_pms_active = $stmt_project_pms_active -> get_result();
                                if($result_project_pms_active -> num_rows>0)
                                    while($row = $result_project_pms_active -> fetch_assoc())
                                    {
                                        echo "<tr><td class='text-capitalize'>".$row['project_name']."</td>
                                        <td class='text-capitalize'>".$row['project_type']."</td>
                                        <td class='position-relative'>
                                        <button type='submit' value='".$row['id']."' name='Request_CG' class='btn btn-outline-primary'>Request</button>
                                        <span class='position-absolute top-0 start-100 translate-middle rounded-pill badge bg-success'>".
                                        badge_count_requests($conn,$conn_fleet,"request_type,Consumer Goods,request_for,%".$row['id']."|%LIKE".$common_string)."
                                            <span class='visually-hidden'>Requests</span>
                                        </span>
                                        </td></tr>";
                                    }
                                else
                                {
                                    echo "<script>document.getElementById('tbl1').style.visibility='hidden';</script>";
                                    echo "<h1 class='d1 text-center'>No Current Projects</h1>";
                                }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>
            </div>
            
        </div>
</div>
</div>
<?php include '../footer.php';?>