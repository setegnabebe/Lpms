<?php
      $chtbtn="";
      $mgr=$_SESSION['role']=='manager';
      $proc_dep=$_SESSION['department']=="Procurement";
      $prop_dep=$_SESSION['department']=="property";
      $store=$_SESSION['role']=="Store";
      $fin_dep=$_SESSION['department']=="Finance";
      $dep=$_SESSION['department']==$row['department'];
      $dir=$_SESSION['role']=='Director';
  
  if(($_SESSION['username']==$row['customer']||$mgr)&&$dep&&$row['status']=='waiting'&&$row['company']==$_SESSION['company']||
  (($mgr&&$dep)||$dir)&&$row['status']=='Approved By Dep.Manager'&&$row['company']==$_SESSION['company']||
  (($dir||($store||$prop_dep&&$mgr)&&$row['next_step']!="Owner"||$_SESSION['role']=='Owner'&&$row['request_type']=='Fixed Assets')&&$row['next_step']!='Performa' || $row['next_step']=='Performa'&&$mgr&&($prop_dep|| $proc_dep))&&$row['status']=='Approved By GM'&&$row['company']==$_SESSION['company']||
  (($_SESSION['role']=='Owner' || ($store ||$prop_dep &&$mgr)&&$row['company']==$_SESSION['company'])&&$row['request_type']=='Fixed Assets'&&$row['next_step']!='Property'|| $row['next_step']=='Property')&&$row['status']=='Approved By Owner'||
  $row['status']=="Approved By Property" && $mgr&&($prop_dep|| $proc_dep)&&$row['company']==$_SESSION['company']||
  ($proc_dep && $mgr || ($_SESSION['role']=="Purchase officer" && $row['next_step']!="Comparision Sheet Generation"||$row['next_step']=="Comparision Sheet Generation"&&$_SESSION['role']=="Senior Purchase officer"))&&$row['status']=="Generating Quote"||
  ($proc_dep&&$mgr||$_SESSION['role']=="Purchase officer" ||$dep&&$mgr )&&($row['status']=="Payment Processed"|| $row['status']=="Collected-not-comfirmed")||
  (($dep||$prop_dep)&&$mgr ||$store)&&$row['status']=="Collected-not-comfirmed"||
  ($prop_dep&&$mgr ||$_SESSION['role'])&&$row['status']=="In-Stock")
  $chtbtn="<button type='button' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row['purchase_requisition']."' >Chat <i class='text-primary fa fa-comment'></i></button>";
 
 
  if(($row['status']=="Generated"||$row['status']=="Approved")&&(isset($row_po['cluster_id']))&&$_SESSION['a_type']!="user"||
  (($proc_dep || $fin_dep)&& $mgr|| $_SESSION['role']=="Disbursement")&& $row['status']=="Sent to Finance"|| 
  $row['status']=="Reviewed"&&$fin_dep&&$mgr||
    $_SESSION['role']=="cashier" ||$fin_dep && $mgr&&$row['status']=="Finance Approved"||
  ((strpos($_SESSION["a_type"],"ChequeSignatory") !== false && isset($_SESSION['company_signatory'])|| $_SESSION['role']=="cashier" ||$fin_dep&& $mgr)&&$row['status']=="Cheque Prepared"))
  $chtbtn="<button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".(isset($row_po['cluster_id'])?$row_po['cluster_id']:"")."' >Chat <i class='text-primary fa fa-comment'></i></button>"; 
?>