<?php
    $sms_to=str_replace("."," ",$sms_to);
    $msg="Dear ".$sms_to.",\n".$msg."\n\n".$sms_from;
    //$detailmessage=substr($msg,0,300); this one is for limiting the maximum character limit of message
    if($phone_number != "")
    {
        $query4s="INSERT INTO outbox (DestinationNumber,TextDecoded,CreatorID) VALUES (?,?,?)";
        $stmt_add_sms = $conn_sms->prepare($query4s);
        $stmt_add_sms -> bind_param("sss", $phone_number, $msg, $_SESSION['username']);
        $stmt_add_sms -> execute();
    }
?>