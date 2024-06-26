<?php
// if(isset($_POST['send-message']) && isset($_POST['Serverpassword']))
// {
//     $SMSaccount = "SMS.Sender";
//     $pwd=md5($_POST['Serverpassword']);
//     $qry="SELECT * FROM account where `Username` = '$SMSaccount' AND `password` = ?";
//     $stmt_account = $conn->prepare($qry);
//     $stmt_account->bind_param("s", $pwd);
//     $stmt_account->execute();
//     $result_account = $stmt_account->get_result();
//     if($result_account->num_rows > 0)
//     {
//     }
// }
date_default_timezone_set('Africa/Addis_Ababa');
$servername = "localhost";
$username = "root";
$password = "";
$conn_sms = new mysqli($servername, $username, $password,"sms");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = 'PASSWORD';
    $receivedApiKey = isset($_SERVER['HTTP_API_KEY']) ? $_SERVER['HTTP_API_KEY'] : '';
    
    if ($receivedApiKey !== $apiKey) {
        http_response_code(401); 
        echo "Invalid API key";
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    foreach($data as $key=>$value)
    {
        ${$key} = $value;
    }
    $query4s="INSERT INTO outbox (DestinationNumber,TextDecoded,CreatorID) VALUES (?,?,?)";
    $stmt_add_sms = $conn_sms->prepare($query4s);
    $stmt_add_sms -> bind_param("sss", $phone_number, $message, $account);
    $stmt_add_sms -> execute();
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(405);
    echo "Invalid request method";
}