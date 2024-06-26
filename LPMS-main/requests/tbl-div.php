
<script>
    function view_type(e,t)
    {
        let o = (t=='div')?'tbl':'div';
        // if(o=='div'){
        //     document.getElementById('search_text2').classList.add('d-none')
        //     if( document.getElementById('search_text').innerText.length)
        //     document.getElementById('search_text').classList.remove('d-none') 
        // }else{
        //     document.getElementById('search_text2').classList.remove('d-none') 
        //     document.getElementById('search_text').classList.add('d-none') 
        // }
       

        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
    }
</script>
<div class="row">
    <div class='text-center mx-auto mb-4 col-10' style="width: 200px;">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button type='button' class="btn nav-link active" id='tbl_toggle' onclick="view_type(this,'div')">
                    <i class='fas fa-table'></i>
                </button>
            </li>
            <li class="nav-item">
                <button type='button' class="btn nav-link" id='div_toggle' onclick="view_type(this,'tbl')">
                    <i class='fas fa-tablet-alt'></i>
                </button>
            </li>
        </ul>
    </div>
<?php 
if(isset($requests_tab)) {
    $gets = "";
    foreach($_GET as $att => $val)
    {
        $gets .= $att."=".$val."&";
    }
    ?>
<?php }?>
</div>