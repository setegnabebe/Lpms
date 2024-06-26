
<script>
    function change_type(e,t)
    {
        // let o = (t=='div')?'tbl':'div';
        const class_items = document.getElementsByClassName('types_admin');
        for(let inc = 0 ; inc < class_items.length; inc++)
        {
            class_items[inc].classList.remove("active");
            let div = document.getElementById(class_items[inc].id.replace("toggle","div"));
            if(!div.className.includes("d-none"))
                div.classList.add("d-none");
        }
        e.classList.add("active");
        document.getElementById(e.id.replace("toggle","div")).classList.remove('d-none');
        // e.className = "types_admin btn nav-link active";
        // document.getElementById(t+"_toggle").className = "btn nav-link";
        // document.getElementById(t+"_view").className = "d-none";
        // document.getElementById(o+"_view").removeAttribute('class');
        // if(o == 'tbl')
        //     document.getElementById("search_requests").classList.add("d-none");
        // else
        //     document.getElementById("search_requests").classList.remove("d-none");

    }
</script>
<div class='text-center mb-4' data-aos='fade-right'>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button type='button' class="types_admin btn nav-link active" id='Manage_account_toggle' onclick="change_type(this)">
                Manage Accounts
            </button>
        </li>
        <!-- <li class="nav-item">
            <button type='button' class="types_admin btn nav-link" id='Manage_project_toggle' onclick="change_type(this)">
                Manage Projects
            </button>
        </li> -->
        <li class="nav-item">
            <button type='button' class="types_admin btn nav-link" id='Edit_Comparission_sheet_toggle' onclick="change_type(this)">
                Manage Comparison Sheets
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="types_admin btn nav-link" id='amend_limit_toggle' onclick="change_type(this)">
                Amend Limit / Admin Settings
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="types_admin btn nav-link" id='manage_toggle' onclick="change_type(this)">
                Manage
            </button>
        </li>
        <li class="nav-item">
            <button type='button' class="types_admin btn nav-link" id='Manage_service_toggle' onclick="change_type(this)">
                Manage Service
            </button>
        </li>
    </ul>
</div>