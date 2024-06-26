<?php
session_start();
$go_home = "<p class='d-none'> Go to Home Page</p>";
if(isset($_SESSION['username']))
{
    include '../connection/connect.php';
    include "../common/functions.php";
    $str='';
    $subject_email = "";
    $data_email = "";
    $request_id = "";
    $na_t = "";
    $vendor_id=explode("_",$_GET['data'])[0];
    $sql2 = "SELECT * FROM `prefered_vendors` where id = ?";
    $stmt_vendors = $conn->prepare($sql2);
    $stmt_vendors->bind_param("i", $vendor_id);
    $stmt_vendors->execute();
    $result_vendors = $stmt_vendors->get_result();
    if($result_vendors->num_rows>0)
    $row2 = $result_vendors->fetch_assoc();
    $subject_email = "Request for Proforma";
    $data_email = "
    Dear $row2[vendor],
        Please send us at $_SESSION[company] s detailed Proforma";
    $button = "data-bs-dismiss='modal'";
    if(explode("_",$_GET['data'])[1] != "unspecific")
    {
        $spec = "";
        $request_id=explode("_",$_GET['data'])[1];
        $na_t=explode("_",$_GET['data'])[2];
        $type = na_t_to_type($conn,$na_t);
        $stmt_request->bind_param("i", $request_id);
        $stmt_request->execute();
        $result_request = $stmt_request->get_result();
        $row = $result_request->fetch_assoc();
        $subject_email .= " for item - ".$row['item']; 
        $data_email .= " For $row[item]";
        $button = "data-bs-toggle='modal' data-bs-target='#vendor_select'";
        if(!is_null($row['specification']))
        {
            $stmt_spec->bind_param("i", $row['specification']);
            $stmt_spec->execute();
            $result_spec = $stmt_spec->get_result();
            $row_spec = $result_spec->fetch_assoc();
            $spec_dets = $row_spec['details'];
            $spec_dets = str_replace('<div class="ql-editor" data-gramm="false" contenteditable="false">',"",$spec_dets);
            $spec_dets = str_replace('</div>',"",$spec_dets);
            $tags = ["<p>","</p>","<h>","<div>","</div>"];
            foreach($tags as $tag)
            {
                if($tag == "<h>")
                {
                    for($ii = 1;$ii<=6;$ii++)
                    {
                        $spec_dets = str_replace("<h".$ii.">","",$spec_dets);
                        $spec_dets = str_replace("</h".$ii.">","",$spec_dets);
                    }
                }
                else
                {
                    $spec_dets = str_replace($tag,"",$spec_dets);
                }
            }
            $data_email .= "\n Specification \n$spec_dets";
            if(!is_null($row_spec["pictures"]) && $row_spec["pictures"] != "")
            {
                $spec .= "
                <div class='row gallery noPrint'>
                <h6 class='text-center'>Pictures/PDF</h6>";

                $allfiles = explode(":_:",$row_spec['pictures']);
                foreach($allfiles as $file)
                {
                    if(strpos($file,"pdf"))
                        $spec .= "
                            <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                <a href='https://portal.hagbes.com/lpms_uploads/".$file."' target='_blank' class='text-dark btn btn-outline-primary border-0 float-end' download >PDF Download <i class='fa fa-download'></i></a>
                            </div>";
                    else
                        $spec .= "
                            <div class='col-6 col-sm-6 col-lg-3 mt-2 mt-md-0 mb-md-0 mb-2'>
                                <a href='https://portal.hagbes.com/lpms_uploads/".$file."'>
                                    <img class='w-100 active' src='https://portal.hagbes.com/lpms_uploads/".$file."' alt = 'Specifcation pictures'>
                                </a>
                            </div>";
                }
                $spec .= "</div>";
            }
        }
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
                $spec
                <div class='float-end'>
                    <button class='btn btn-primary me-1 mb-1 px-2' $button type='button' onclick='mail_vendor(this)'
                    name='".$na_t."_".$request_id."' id='$row2[id]_".$request_id."_$na_t'>Send</button>
                </div>
            </div>
        </div>";
        $conn->close();
        $conn_pms->close();
        $conn_fleet->close();
        $conn_ws->close();
        $conn_ais->close();
        $conn_sms->close();
        $conn_mrf->close();
}
else
{
    echo $go_home;
}
?>