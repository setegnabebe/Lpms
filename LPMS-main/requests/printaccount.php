<?php 
session_start();
// $servername = "localhost";
// $username = "root";
// $password = "4hR3XnqZaTcg3hf.";

// $conn = new mysqli($servername, $username, $password,"project_lpms");
if(isset($_SESSION["username"]))
{
    if($_SESSION["role"] != 'Owner' && $_SESSION["role"] != 'Admin'){
        header("Location: index.php");
    }
    else
    {
        $string_inc = '../'.$_SESSION["loc"].'/head.php';
        include $string_inc;
    }
}
else 
    header("Location: index.php");
?>
<script>
    set_title("LPMS | Accounts");
    sideactive("Accounts");
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>View Accounts</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">View Accounts</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <h4 class="text-center mt-4">Accounts</h4>
<div class='w-100 px-4 mt-3'>
    <table class='table' id='table1'>
    <thead class="table-primary">
                <tr>
                    <th>No.</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>managing(director)</th>
                    <th>Company</th>
                    <th>Account Type</th>
                    <th>Role of User</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i=0;
                $sql = "SELECT * FROM account";
                $stmt_accounts = $conn -> prepare($sql);
                $stmt_accounts -> execute();
                $result_accounts = $stmt_accounts -> get_result();
                if($result_accounts -> num_rows>0)
                    while($row = $result_accounts -> fetch_assoc())
                    {
                        $i++;
                        $ch =($row['status'] == 'active' || $row['status'] == 'waiting')?" checked":"";
                        echo "
                        <tr id='row_".$row['Username']."'>
                            <td id='num_".$row['Username']."'>$i</td>
                            <td id='Username_".$row['Username']."'>".$row['Username']."</td>
                            <td id='phone_".$row['Username']."'>".$row['phone']."</td>
                            <td id='email_".$row['Username']."'>".$row['email']."</td>
                            <td id='department_".$row['Username']."'>".$row['department']."</td>
                            <td id='managing_".$row['Username']."'>".$row['managing']."</td>
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
<?php include "../footer.php"; ?>