<script>
    function report_view_type(e)
    {
        const report_types = new Array('table', 'bar', 'pie');
        let length = report_types.length;
        for(let i = 0;i<length;i++)
        {
            if(e.id.includes(report_types[i]))
            {
                if(!e.className.includes('active'))
                {
                    e.classList.add('active');
                    document.getElementById(e.id.replace("toggle","div")).classList.remove('d-none');
                }
                else
                    break;
            }
            else
            {
                document.getElementById("report_"+report_types[i]+"_toggle").classList.remove('active');
                document.getElementById("report_"+report_types[i]+"_div").classList.add('d-none');
            }
        }
        // if(e.id.includes("pie"))
        //     $('#'+e.id.replace("toggle","div")).load(window.location.href + " #"+e.id.replace("toggle","div") );
    }
    // var auto_refresh = setInterval( function() { $('#loading').load(window.location.href + " #loading" ).fadeIn("slow"); }, 1000);
</script>
<?php
if(strpos($_SESSION["a_type"],"HOCommittee") === false && $_SESSION["department"] != 'Owner')
{
    $pie_off = " d-none";
    $pie_active = "";
    $tbl_onn = " active";
}
else
{
    $pie_off = "";
    $pie_active = " active";
    $tbl_onn = "";
}
?>
<div class='text-center mx-auto mb-4' style="width: 400px;">
    <ul class="nav nav-tabs">
        <li class="nav-item<?php echo $pie_off?>">
            <button type='button' class="btn nav-link<?php echo $pie_active?>" id='report_pie_toggle' onclick="report_view_type(this)">
                <i class='fas fa-chart-pie'></i>
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="btn nav-link<?php echo $tbl_onn?>" id='report_table_toggle' onclick="report_view_type(this)">
                <i class='fas fa-table'></i>
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="btn nav-link" id='report_bar_toggle' onclick="report_view_type(this)">
                <i class='fas fa-chart-bar'></i>
            </button>
        </li>
    </ul>
</div>