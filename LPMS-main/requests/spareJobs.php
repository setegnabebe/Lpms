<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
if(!in_array($_SESSION["company"],$privilege["Spare and Lubricant"]) && !in_array("All",$privilege["Spare and Lubricant"]))
{
    header("Location: ../");
}
// include '../connection/connect_ws.php';
unset($_SESSION['job_name']);
?>
<script>
    set_title("LPMS | Spare & Lubricants");
sideactive("SpareandLubricant_side");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>All Currently Active Jobs</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Jobs List</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
        <div class="container-fluid row" data-aos="zoom-out">
        <?php req_count($conn,$conn_fleet,'Spare and Lubricant'); 
        $common_string = "";
        $common_string .=(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["role"]=="Owner")?"":",customer,".$_SESSION["username"];
        $common_string .=(strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"]!="Procurement" && $_SESSION["department"]!="Property")?",department,".$_SESSION['department']:"";
        $common_string .=(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":",company,". $_SESSION['company'];
    ?>
            <div class='container shadow'>
                <div class="row m-auto card-body">
                <form method='GET' action='allphp.php'>
                    <table class="table table-hover w-75 m-auto" id='table1'>
                        <thead class='table-primary'>
                            <tr>
                                <!-- <th scope="col">Job Number</th> -->
                                <th scope="col">Description</th>
                                <th scope="col">Customer</th>
                                <th scope="col" colspan=""></th>
                            </tr>
                        </thead>
                        <tbody> 
                            <tr>
                                <!-- <th scope="col">Job Number</th> -->
                                <td class='col text-center' colspan="2">General Request</td>
                                <td class="position-relative"> 
                                    <button type='submit' name='request_spare' value='None' class='btn btn-outline-primary'>
                                        Request
                                    </button>
                                    <span class='position-absolute top-0 start-100 translate-middle rounded-pill badge bg-success'>
                                    <?php echo badge_count_requests($conn,$conn_fleet,"request_type,Spare and Lubricant,request_for,None".$common_string);?>
                                        <span class='visually-hidden'>Requests</span>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-hover w-75 m-auto" id='table2'>
                        <thead class='table-primary'>
                            <tr>
                                <!-- <th scope="col">Job Number</th> -->
                                <th scope="col">Description</th>
                                <th scope="col">Customer</th>
                                <th scope="col" colspan=""></th>
                            </tr>
                        </thead>
                        <tbody> 
                            <?php
                                // $common_string = "";
                                // $common_string .=(strpos($_SESSION["a_type"],"manager") !== false || $_SESSION["department"]=="Procurement" || $_SESSION["department"]=="Property" || strpos($_SESSION["a_type"],"HOCommittee") !== false || strpos($_SESSION["a_type"],"BranchCommittee") !== false || $_SESSION["role"]=="Owner")?"":",customer,".$_SESSION["username"];
                                // $common_string .=(strpos($_SESSION["a_type"],"manager") !== false && $_SESSION["department"]!="Procurement" && $_SESSION["department"]!="Property")?",department,".$_SESSION['department']:"";
                                // $common_string .=(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["role"]=="Owner")?"":",company,". $_SESSION['company'];
                                $sql = "SELECT * FROM `description`";
                                $stmt_descriptions = $conn_ws -> prepare($sql);
                                // $stmt_descriptions -> bind_param("s", $row['Name']);
                                $stmt_descriptions -> execute();
                                $result_descriptions = $stmt_descriptions -> get_result();
                                if($result_descriptions -> num_rows > 0)
                                    while($row = $result_descriptions -> fetch_assoc())
                                    {
                                        ///////////////////////////have to customise on only the user sees his jobs
                                    $sql2 = "SELECT * FROM customerinfo where id = ?";
                                    $stmt_customerinfo = $conn_ws -> prepare($sql2);
                                    $stmt_customerinfo -> bind_param("i", $row['id']);
                                    $stmt_customerinfo -> execute();
                                    $result_customerinfo = $stmt_customerinfo -> get_result();
                                    if($result_customerinfo->num_rows>0)
                                        while($row2 = $result_customerinfo->fetch_assoc())
                                        {
                                            // <th scope='row' >".$row['iden']."</th>
                                            ;?>
                                            <?php
                                            echo "<tr>
                                            <td class='text-capitalize'>".$row['description']."</td>
                                            <td class='text-capitalize'>".$row2["cn"]."</td><td class='position-relative'>";
                                            
                                            echo ($row['status']=='approve')?"
                                            <button type='submit' name='request_spare' value='".$row['iden']."' class='btn btn-outline-primary'>
                                                Request
                                            </button>
                                            ":"<i class='text-primary'>Job Not Active</i>";
                                            echo "
                                                <span class='position-absolute top-0 start-100 translate-middle rounded-pill badge bg-success'>".
                                                badge_count_requests($conn,$conn_fleet,"request_type,Spare and Lubricant,request_for,".$row['iden'].$common_string)."
                                                    <span class='visually-hidden'>Requests</span>
                                                </span>
                                            </td>
                                            </tr>";  
                                            // <input type='submit' name='request_spare".$row['iden']."' value='Request' class='btn btn-outline-primary'>
                                        }
                                    }
                                else
                                {
                                    echo "<script>document.getElementById('tbl2').style.visibility='hidden';</script>";
                                    echo "<h1 class='d1 text-center'>No Current Jobs</h1>";
                                }
                            ?>
                        </tbody>
                    </table>
                    </form>
                </div>
            </div>
        </div>
</div>
    <?php include '../footer.php';?>
