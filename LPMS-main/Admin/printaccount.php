<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
?>
<script>
    set_title("LPMS | Accounts");
    sideactive("accounts");
</script>
<div id='main'>
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
            <header>
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            <h2>Accounts</h2>
            <ol class="breadcrumb my-4">
                <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Accounts</li>
            </ol>
        </div>
        <?php include '../common/profile.php';?>
    </div>
    <h4 class="text-center mt-4">Accounts</h4>
    <div class='w-100 px-4 mt-3'>
        <table class='table'>
            
        <thead class="table-primary">
                    <tr>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Department</th>
                        <!-- <th>managing(director)</th> -->
                        <th>Company</th>
                        <th>Account Type</th>
                        <th>Role of User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM account";
                    $stmt_account = $conn->prepare($sql); 
                    $stmt_account->execute();
                    $result = $stmt_account->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch =($row['status'] == 'active' || $row['status'] == 'waiting')?" checked":"";
                            echo "
                            <tr id='row_".$row['Username']."'>
                                <td id='Username_".$row['Username']."'>".$row['Username']."</td>
                                <td id='phone_".$row['Username']."'>".$row['phone']."</td>
                                <td id='email_".$row['Username']."'>".$row['email']."</td>
                                <td id='department_".$row['Username']."'>".$row['department']."</td>
                                <!-- <td id='managing_".$row['Username']."'>".$row['managing']."</td> -->
                                <td id='company_".$row['Username']."'>".$row['company']."</td>
                                <td id='type_".$row['Username']."'>".$row['type']."</td>
                                <td id='role_".$row['Username']."'>".$row['role']."</td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
        </table>
    </div>
</div>
<?php include '../footer.php';?>