
<!-- <script>
    function report_type(e,t,y="")
    {
        // if(e.classList.includes('active')) break;
        var temp = (y=='pie')?"_pie":"";
        let o = (t=='Department')?'Company':'Department';
        e.classList.add("active");
        document.getElementById(t+temp+"_toggle").classList.remove("active");
        document.getElementById(t+temp+"_view").classList.add("d-none");
        document.getElementById(o+temp+"_view").classList.remove('d-none');
        if(t=='Department')
            document.getElementById("all_deps"+temp).classList.add('d-none');
        else
            document.getElementById("all_deps"+temp).classList.remove('d-none');
    }
</script> -->
<!-- <div class='text-center mx-auto mb-4' style="width: 400px;">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button type='button' class="btn nav-link active" id='Company_pie_toggle' onclick="report_type(this,'Department','pie')">
                Company
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="btn nav-link" id='Department_pie_toggle' onclick="report_type(this,'Company','pie')">
                Department
            </button>
        </li>
    </ul>
</div> -->
<!-- <div class='mb-4 d-none' id='all_deps_pie'>
    <ul class="nav nav-tabs"> -->
<?php 
// $o = " active";
//     $sql2 = "SELECT * FROM `comp`";
//     $result = $conn_fleet->query($sql2);
//     if($result->num_rows>0)
//     {
//         while($row = $result->fetch_assoc())
//         {
//             $once = true;
//             $company_req[$row['Name']] = 0;
//             $db_list=array("");
//             foreach($db_list as $dbs)
//             {
//                 $sql = "SELECT count(*) as `c_r` FROM $dbs where company = '".$row['Name']."'";
//                 $result2 = $conn->query($sql);
//                     while($row2 = $result2->fetch_assoc())
//                     {
//                         if($row2['c_r']>0)
//                         {
//                             $company_req[$row['Name']] = $company_req[$row['Name']]+$row2['c_r'];
//                             $once = false;
//                         }
//                     }
//             }
//             if($company_req[$row['Name']] != 0)
//             {
//                 echo "
//                 <li class='nav-item'>
//                     <button type='button' class='btn nav-link$o' id='Company_pie_toggle' onclick='report_type(this,\'Department\')'>
//                         ".$row['Name']."
//                     </button>
//                 </li>";
//                 $o = "";
//             }
//         }
//     }
?>
    <!-- </ul>
</div> -->