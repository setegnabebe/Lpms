<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../../connection/connect.php';
    include "../../common/functions.php";
    function format($date1){
        $str="";
    $number=abs(strtotime($date1)-strtotime("now"));
    if($number<60)
    $str="$number seconds ago";
    else if($number>=60 && $number<3600)
    $str= floor(($number/60))." minutes ago";
    else if($number>=3600 && $number<86400)
    $str=floor(($number/3600))." hours ago";
    else if($number>=86400 && $number<691200)
    $str=floor(($number/86400))." Days ago";
    else if($number>=691200 && $number<2592000)
    $str=floor(($number/691200))." Weeks ago";
    else if($number>=2592000 && $number<31104000)
    $str=floor(($number/2592000))." months ago";
    else
    $str=floor(($number/31104000))."Years ago";
    return $str;
    }
    if(isset($_GET['limit'])){
        $sql_emails="SELECT * FROM emails WHERE email_type IS NOT NULL";
        $stmt_emails = $conn->prepare($sql_emails);
        $stmt_emails->execute();
        $result_emails = $stmt_emails->get_result();
        $length=$result_emails->num_rows;
        $sql_emails_active = "SELECT * FROM emails WHERE email_type IS NOT NULL order by id desc LIMIT ".$_GET['limit'].", 20 ";
        $stmt_emails_active = $conn -> prepare($sql_emails_active);
        $stmt_emails_active -> execute();
        $result_emails_active = $stmt_emails_active -> get_result();
        $length2=$result_emails_active->num_rows;
        $str="";
        if($result_emails_active->num_rows>0)
        while($row=$result_emails_active->fetch_assoc()){
            $str.='<tr>
            <td>
                <div class="icheck-primary">
                <input type="checkbox" value="'.$row['id'].'" id="check'.$row['id'].'">
                <label for="check'.$row['id'].'"></label>
                </div>
            </td>
            <td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td>
            <td class="mailbox-name"><a href="./read-mail.php?id='.$row['id'].'">'.(explode('_',explode(':-:',$row['reason'])[0])[1]).'</a></td>
            <td class="mailbox-subject"><b>'.$row['subject'].'</b> -'.explode('<br>',$row['data'])[0].'...
            </td>
            <td class="mailbox-attachment"></td>
            <td class="mailbox-date">'.format($row['time']).'</td>
            </tr>';
        }
        echo $str;
    }else{
    $str='';
    $subject_email = "";
    $data_email = "";
    $request_id = "";
    $na_t = "";
    $vendor_id=explode("_",$_GET['data'])[0];
    $stmt_vendor_specific->bind_param("i", $vendor_id);
    $stmt_vendor_specific->execute();
    $result_vendor_specific = $stmt_vendor_specific->get_result();
    if($result_vendor_specific->num_rows>0)
    $row2 = $result_vendor_specific->fetch_assoc();
    $subject_email = "Request for proforma";
    $data_email = "
    Dear $row2[vendor],
        Please Send Us at $_SESSION[company] A detailed proforma";
    if(explode("_",$_GET['data'])[1] != "unspecific")
    {
        $request_id=explode("_",$_GET['data'])[1];
        $na_t=explode("_",$_GET['data'])[2];
        $type = na_t_to_type($conn,$na_t);
        $stmt_request -> bind_param("i", $request_id);
        $stmt_request -> execute();
        $result_request = $stmt_request->get_result();
        $row = $result_request->fetch_assoc();
        $subject_email .= " for item - ".$row['item']; 
        $data_email .= "For $row[item]";
    }
    echo "
        <div class='card'>
            <div class='card-header'>
                <h3 class='text-center'>Email Vendors</h3>
            </div>
            <div class='card-body'>
                <div class='form-group'>
                    <label for='email'>Vendor</label>
                    <input type='text' class='form-control' name='name' id='name".$na_t."_".$request_id."' placeholder='Enter email' value='".$row2['vendor']."' readonly>
                </div>
                <div class='form-group'>
                    <label for='email'>Vendor Email</label>
                    <input type='text' class='form-control' name='email' id='email".$na_t."_".$request_id."' placeholder='Enter email' value='".$row2['email']."' readonly>
                </div>
                <div class='form-group mb-3'>
                    <label for='basicInput'>Subject</label>
                    <input type='text' class='form-control' name='subject' id='subject".$na_t."_".$request_id."' placeholder='Enter Subject' value='$subject_email'>
                </div>
                <div class='form-group mb-3'>
                    <label for='email_data' class='form-label'>Email Data</label>
                    <textarea class='form-control' name='email_data' id='email_data".$na_t."_".$request_id."' rows='6'>$data_email</textarea>
                </div>
                <div class='float-end'>
                    <button class='btn btn-primary me-1 mb-1 px-2' data-bs-toggle='modal' data-bs-target='#vendor_select' type='button' onclick='mail_vendor(this)'
                    name='".$na_t."_".$request_id."' id='$row2[id]_".$request_id."_$na_t'>Send</button>
                </div>
            </div>
        </div>";
    }
}
else
{
    echo $go_home;
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