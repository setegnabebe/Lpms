<?php
$stmt_password = $conn->prepare("INSERT INTO `forgot_password_links`(`Requested_by`, `Sent_to`, `Link`, `date`) VALUES (?,?,?,?)");
$stmt_password -> bind_param("ssss",$row_acc['Username'],$row_acc['email'],$uniq,$date_n_password);
$stmt_password -> execute();

$stmt = $conn->prepare("UPDATE `company` SET `logo`=? WHERE `Name`=?");
$stmt -> bind_param("ss",$temp_name ,$_POST['change_logo']);
$stmt -> execute();

$stmt = $conn->prepare($sql);
$stmt -> bind_param("ssss",$_POST['display_name'] ,$dep ,$description ,$_POST['change_privilege']);
$stmt -> execute();

$sql_acc = "SELECT * FROM account where `Username` = ?";
$stmt_acc = $conn->prepare($sql_acc); 
$stmt_acc->bind_param("s", $_POST['uname']);
$stmt_acc->execute();
$result_acc = $stmt_acc->get_result();
while($row_acc = $result_acc->fetch_assoc())
{
        
}

$qu="SELECT * from transfer where reqdate=?";
$stmt78 = $conn->prepare($qu); 
$stmt78->bind_param("s", $date);
$stmt78->execute();
$res78 = $stmt78->get_result();
if(mysqli_num_rows($res78)>0)
        while($row=mysqli_fetch_array($res78)){
        }
$stmt_companies = $conn->prepare($sql_companies);
$stmt_companies -> bind_param("i", $row['id']);
$stmt_companies -> execute();
$result_companies = $stmt_companies->get_result();
?>