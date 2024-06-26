<?php
include "connection/connect.php";
// $qry="SELECT * FROM `account` WHERE `name` is null";    
// $res=$conn->query($qry);
// while($row = $res->fetch_assoc()) 
// {
//     $sql2 = "UPDATE account SET `name`='".$row['Username']."' WHERE `Username`='".$row['Username']."'";
//     $conn->query($sql2);
// }
// 3848 - 3853
// 3862 - 3867
// 3876 - 3881
// 3890 - 3895
// 3904 - 3909
// 3918 - 3923
// 3932 - 3937
$new_units = [582.60,521.74,826.68,1043.48,1831.30,956.52,1000.00];
$ind = 0;
for($start = 3848; $start <= 3932; $start+=14)
{
    $end = $start + 5;
    //
    $qry="SELECT * FROM `price_information` WHERE id >= $start AND id <= $end";   
    $res=$conn->query($qry);
    if($res->num_rows>0)
        while($row = $res->fetch_assoc()) 
        {
            if($new_units[$ind] != $row['price'])
            {
                $new_unit = $new_units[$ind];
                $new_total = $new_unit * $row['quantity'];
                $new_av = $new_total * (1 + $row['vat']);
                $sql2 = "UPDATE price_information SET `price` = '$new_unit',`total_price` = '$new_total',`after_vat` = '$new_av',`selected` = 0 WHERE `id`='".$row['id']."'";
                echo $sql2."<br>";
                $conn->query($sql2);
                $user = (isset($_SESSION['username']))?$_SESSION['username']:"Dagem.Adugna";
                $update_attr = "id-".$row['id'];
                $unit = $row['price']."->".$new_unit;
                $total = $row['total_price']."->".$new_total;
                $av = $row['after_vat']."->".$new_av;
                
                $sql = "INSERT INTO `dbs_edits`(`user`, `dbs`, `tbl`, `pri-value`, `att`, `value`) VALUES ('$user','LPMS','price_information',
                '$update_attr','price','$unit')";
                echo $sql."<br>";
                $conn->query($sql);
    
                $sql = "INSERT INTO `dbs_edits`(`user`, `dbs`, `tbl`, `pri-value`, `att`, `value`) VALUES ('$user','LPMS','price_information',
                '$update_attr','total_price','$total')";
                echo $sql."<br>";
                $conn->query($sql);
    
                $sql = "INSERT INTO `dbs_edits`(`user`, `dbs`, `tbl`, `pri-value`, `att`, `value`) VALUES ('$user','LPMS','price_information',
                '$update_attr','after_vat','$av')";
                echo $sql."<br>";
                $conn->query($sql);
            }
        }
        $ind++;
        echo "<br>";
}
?>
<?php
    $conn->close();
    $conn_pms->close();
    $conn_fleet->close();
    $conn_ws->close();
    $conn_ais->close();
    $conn_sms->close();
    $conn_mrf->close();
?>