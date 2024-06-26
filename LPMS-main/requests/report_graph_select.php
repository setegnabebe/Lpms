<script>
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
</script>
<div class='text-center mx-auto mb-4' style="width: 400px;">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button type='button' class="btn nav-link active" id='Company_toggle' onclick="report_type(this,'Department')">
                Company
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="btn nav-link" id='Department_toggle' onclick="report_type(this,'Company')">
                Department
            </button>
        </li>
    </ul>
</div>
<?php 
if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner') $none = "";
else $none = " class='d-none'";
if(strpos($_SESSION["a_type"],"HOCommittee") !== false || $_SESSION["department"] == 'Owner')
$first_exec = true;
{?>
<script>
    function change_comp()
    {
        document.getElementById("set_graph").click();
    }
</script>
<form method="GET" action="report_php.php" <?php echo $none?>>
    <div class="mb-4 form-floating mb-3 d-none"  id='all_deps'>
        <select class="form-select" name="department"  id="department" onchange="change_comp()">
    <?php 
        // $o = " active";
        if(isset($_SESSION["Graph_company"])) 
        {
            $temp_company = $_SESSION["Graph_company"];
        }
        $sql2 = "SELECT * FROM `comp`";
        $stmt_companies = $conn_fleet -> prepare($sql2);
        $stmt_companies -> execute();
        $result_companies = $stmt_companies -> get_result();
        if($result_companies -> num_rows>0)
        {
            while($row = $result_companies -> fetch_assoc())
            {
                if(isset($_SESSION["Graph_company"]) && $first_exec)
                {
                    echo "<option value='".$_SESSION["Graph_company"]."'>".$_SESSION["Graph_company"]."</option>";
                    $first_exec = false;
                }
                $once = true;
                $company_req[$row['Name']] = 0;
                $sql = "SELECT count(*) as `c_r` FROM requests where company = ?";
                $stmt_count_requests = $conn -> prepare($sql);
                $stmt_count_requests -> bind_param("s", $row['Name']);
                $stmt_count_requests -> execute();
                $result_count_requests = $stmt_count_requests -> get_result();
                while($row2 = $result_count_requests->fetch_assoc())
                {
                    if($row2['c_r']>0)
                    {
                        $company_req[$row['Name']] = $company_req[$row['Name']]+$row2['c_r'];
                        $once = false;
                    }
                }
                if($company_req[$row['Name']] != 0)
                {
                    if(isset($temp_company)) 
                        if($row['Name'] == $temp_company) 
                            continue;
                        
                    echo "<option value='".$row['Name']."'>".$row['Name']."</option>";
                }
            }
        }
    ?>
    </select>
<label for="department">Department</label>
<button class="d-none" name="graph_department" id="set_graph">set_graph</button>
</div>
    <!-- </div> -->
</form>
<?php }?>