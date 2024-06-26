<?php
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';
//Define name spaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function send_auto_email($project, $subject, $data,$recip,$company_logo,$cc_sent="",$username_tag="",$to_page="",$sent_from="",$file = "")
{
    //Create instance of phpmailer
    $mail = new PHPMailer();
    //Set mailer to use smtp
    $mail->isSMTP();
    //define smtp host
    $mail->Host = "smtp.gmail.com";
    //enable smtp authentication
    $mail->SMTPAuth ="true";
    //set type of encryption(ssl/tls)
    $mail->SMTPSecure = "tls";
    //set port to connect smtp
    $mail->Port = "587";
    // $mail->SMTPDebug = 2;
    if($project == 'LPMS')
    {
      $web = "https://lpms.hagbes.com";
      $pic_loc = $web."/img";
      $mail->Username = "lpmshagbes@gmail.com";
      $mail->Password = "zeqqvxpatsvwpmxm";
      $mail->IsHTML(true);
      $mail->setFrom("lpmshagbes@gmail.com","LPMS | Local Procurement Management System");
      $reason_email = "Purchase Requests";
    }
    else if($project == 'FMS')
    {
      $web = "https://fms.hagbes.com";
      $pic_loc = $web."/images";
      $mail->Username = "assethagbes@gmail.com";
      $mail->Password = "mgvhwsqjhjrlmjkv";
      $mail->IsHTML(true);
      $mail->setFrom("assethagbes@gmail.com","FMS | Fleet Management System");
      $reason_email = "Fleet Management System";
    }
    else if($project == 'LMS')
    {
      $web = "https://hr.hagbes.com";
      $pic_loc = $web."/images";
      $mail->Username = "assethagbes@gmail.com";
      $mail->Password = "mgvhwsqjhjrlmjkv";
      $mail->IsHTML(true);
      $mail->setFrom("assethagbes@gmail.com","LMS | Leave Management System");
      $reason_email = "Leave Requests";
    }
    else
    {
      $web = "https://lpms.hagbes.com";
      $pic_loc = $web."/img";
      $mail->Username = "lpmshagbes@gmail.com";
      $mail->Password = "zeqqvxpatsvwpmxm";
      $mail->IsHTML(true);
      $mail->setFrom("lpmshagbes@gmail.com","LPMS | Local Procurement Management System");
      $reason_email = "Purchase Requests";
    }
    $page = ($to_page == "")?"":"?url=".$to_page;
    //set email subject
    $date=date("Y-m-d");
    $mail->Subject = $subject;
    //set sender email
    if(!is_null($company_logo) && $company_logo != "")
    {
      $com_lo = explode(",",$company_logo);
      $company = $com_lo[0];
      $logo = $com_lo[1];
    }
    else
    {
      $company = "Hagbes HQ.";
      $logo = "Hagbeslogo.jpg";
    }
    if($sent_from == "")
    {
      $footer = "<p style='font-size:10px;'><hr><i>Kind Regards,<br>$project System</i></p>";
    }
    else
    {
      $sender = explode(":-:",$sent_from)[0];
      $position = explode(":-:",$sent_from)[1];
      $footer = "<p style='font-size:10px;'><hr><i>Kind Regards,<br><b>Name: </b>".str_replace("."," ",$sender).", ".$position.",<br><b>Company: </b>".$company."</i></p>";
    }
    // $footer = "<p>Software Developer Team</p>";
    //email body
    // $mail->Body = "Dear Sir/Madam,<br><br>This vehicle for date <strong>'$date'</strong> has status:<ul><li> not ok</li><li>not available or</li> <li>damaged part or body.</li></ul><br><br>For more visit <a href='http://ais.hagbes.com'>HERE</a><br><br>With Ragards,<br>AIS Software Development Team";

    // $mail->Body = "
    // <html>
    //     <head>
    //         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    //         <title></title>
    //     </head>
    //     <body>
    //         <strong>Dear Sir/Madam,</strong><br><br>
    //         $data
    //         <br><br>
    //         For more visit <a href='http://lpms.hagbes.com'>HERE</a>
    //         <br><br>With Ragards,<br>
    //         Hagbes Software Development Team
    //     </body>
    // </html>";
  //   <tr>
  //   <td align='left' bgcolor='#ffffff' style='padding: 24px;font-family: Source Sans Pro, Helvetica, Arial, sans-serif;font-size: 16px;line-height: 24px;'>
  //     <p style='margin: 0'>
  //      Dear Sir/Madam,<br>                  
  //     </p>
  //   </td>
  // </tr>
  // <h3>This is from Local Procurement Managment System | ".$_SESSION['company']."</h3>
  $img = $logo;
  // if($logo == "ultimate_logo.gif") $img=str_replace("gif","png",$img);
  $tttt = ($username_tag == "")? "Sir / Madam":$username_tag;
  if($username_tag != "") // && $project == 'LPMS'
    $tttt = str_replace("."," ",$tttt);
    $mail->Body = "
    <div style='background-color: #eef0f2'>
        <table border='0' cellpadding='0' cellspacing='0' width='100%'>
          <tr>
            <td align='center'>
              <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px'>
                <tr>
                  <td align='center' valign='top' style='padding: 36px 24px'>
                    <a href='$web' target='_blank' style='display: inline-block'>
                      <img src='$pic_loc/$img' alt='Logo' border='0' width='50'/>
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align='center'>
              <table border='0' cellpadding='0' cellspacing='0'  width='100%' style='max-width: 600px'>
                <tr>
                  <td align='left' bgcolor='#ffffff' style=' padding: 36px 24px 0; font-family: Source Sans Pro, Helvetica, Arial, sans-serif;border-top: 3px solid #0564a6;'>
                    <h1 style='margin: 0;font-size: 32px;font-weight: 700;letter-spacing: -1px;line-height: 48px;'>
                      Dear $tttt,
                    </h1>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align='center'>
              <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px'>
                <tr>
                  <td align='left' bgcolor='#ffffff' style=' padding: 24px;font-family: Source Sans Pro, Helvetica, Arial, sans-serif;font-size: 16px;line-height: 24px;'>
                    <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                      <tr >
                        <td align='left' bgcolor='#EEF0F2' width='75%' style='padding: 12px;font-family: Source Sans Pro, Helvetica, Arial,sans-serif;font-size: 16px;line-height: 24px;'>
                            $data
                            $footer
                        </td> 
                      </tr>
                     <tr>
                      <td align='left' bgcolor='#ffffff' style=' padding: 24px;font-family: Source Sans Pro, Helvetica, Arial, sans-serif;font-size: 16px;line-height: 24px;'>
                    <a href='$web$page'><button style='  background-color: #54c057;border: none;color: white;padding: 20px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;margin: 4px 0px;cursor: pointer;border-radius: 12px;' > Goto Website!</button></a>
                    </td>
                     </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align='center' valign='top' width='100%'>
              <table align='center' bgcolor='#ffffff' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px'>
                <tr>
                  <td align='center' valign='top' style='font-size: 0; border-bottom: 3px solid #d4dadf'>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align='center' style='padding: 24px'>
              <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px'>
                <tr>
                  <td align='center' style=' padding: 12px 24px; font-family: Source Sans Pro, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px;color: #666;' ><hr>
                    <p style='margin: 0'>
                      You received this email as a notification because you registered in our website to update you with $reason_email. If you didn't registered in our website!, You can safely delete this email.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>";
    //add recipient
    $recipients = explode(",",$recip);
    for($i=0;$i<sizeof($recipients);$i=$i+2)
    {
        $mail->addAddress($recipients[$i], $recipients[$i+1]);
    }
    // if($project == 'LPMS')
    //   $mail->AddCC("Dagem.Adugna@hagbes.com");
    // else 
    if($project == 'FMS')
      $mail->AddCC("Mahlet.Nigussie@hagbes.com");
    if(!is_null($file) && $file != "")
    {
      $files = explode("||-||",$file);
      for($i=0;$i<sizeof($files);$i++)
      {
        $mail->AddAttachment($files[$i]);
      }
    }
    $cc = explode(",",$cc_sent);
    for($i=0;$i<sizeof($cc);$i++)
    {
        $mail->AddCC($cc[$i]);
    }

    // $recipients = array(
    //     $recip_email => $recip_name,
    // //'vighen.behesnilian@hagbes.com' => 'Vighen Behesnilian',
    // //'desalegn.whawariat@hagbes.com' => 'Desalegn W/hawariat',
    // // ..
    // );
    // foreach($recipients as $email => $name)
    // {
    // $mail->addAddress($email, $name);
    // }
    //Add CC
    //$mail->AddCC("gashu.wendawke@hagbes.com");
    // $mail->AddCC("Dagem.Adugna@hagbes.com");
    //finally send email
    if($mail->Send()){
        return "Email Sent";
    }else{
      return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    $mail->smtpClose();
}
?>