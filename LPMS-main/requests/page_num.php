<?php 
$dis = ($page_num == 1)?" disabled":"";
echo "<form method='GET' action='$_SERVER[PHP_SELF]'>
    <ul class='pagination justify-content-end' id='$amount'>
    <div class='dataTable-dropdown me-3'>
        <select class='dataTable-selector form-select form-select-sm' name='per_page' onchange='document.getElementById(\"active_page\").click();'>";
        for($i=5;$i<=25;$i=$i+5)
        {
            $act = ($i==$per_page)?" selected=''":"";
            echo "<option value='$i'$act>$i</option>";
        }
        echo "</select>
    </div>
        <li class='page-item$dis'><button type='submit' class='page-link me-2' name='page_num' value='".($page_num-1)."'>Previous</button></li>";
        for($i=1;$i<=$amount;$i++)
        {
            $act = ($i==$page_num)?" active":"";
            $act_id = ($i==1)?" id = 'active_page'":"";
            echo "<li class='page-item$act'><button type='submit'$act_id class='page-link' name='page_num' value='".($i)."'>$i</button></li>";
        }
        $dis = ($amount==$page_num)?" disabled":"";
        echo "<li class='page-item$dis'><button type='submit' class='page-link' name='page_num' value='".($page_num+1)."'>Next</button></li>
    </ul>
</form>";
?>