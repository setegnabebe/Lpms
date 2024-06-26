
<?php 
session_start();
if(isset($_SESSION['loc']))
{
    $string_inc = '../'.$_SESSION["loc"].'/head.php';
    include $string_inc;
}
else
    header("Location: ../");
if(!in_array($_SESSION["company"],$privilege["Tyre and Battery"]) && !in_array("All",$privilege["Tyre and Battery"]))
{
    header("Location: ../");
}
// include '../connection/connect_fms.php';
$remove_type_table = true;
?>
<script>
    set_title("LPMS | Tyre , battery and Inner Tube");
    sideactive("TyreandBattery_side");
    function view_type(e,t)
    {
        let o = (t=='div')?'tbl':'div';
        e.className = "btn nav-link active";
        document.getElementById(t+"_toggle").className = "btn nav-link";
        document.getElementById(t+"_view").className = "d-none";
        document.getElementById(o+"_view").removeAttribute('class');
        // if(o == 'tbl')
        //     document.getElementById("search_requests").classList.add("d-none");
        // else
        //     document.getElementById("search_requests").classList.remove("d-none");

    }
    function loader(deff = "")
    {
        var plate=document.getElementById("floatingplate").value;
        var item=document.getElementById("floatingitem").value;
        if(deff == "")
        {
            if(plate!="none")
            {
                const req = new XMLHttpRequest();
                req.onload = function(){//when the response is ready
                document.getElementById("history_t").innerHTML=this.responseText;
                document.getElementById('current_km').value=(isNaN(document.getElementById('c_km').innerHTML))?0:parseInt(document.getElementById('c_km').innerHTML);
                if(document.getElementById('current_km').value == 0 || document.getElementById('current_km').value == '')
                {
                    document.getElementById('current_km').removeAttribute("readonly");
                    document.getElementById('current_km').value = '';
                }
                else document.getElementById('current_km').setAttribute("readonly",true);
                }
                if(item!="")
                    req.open("GET", "Ajax_load.php?plate="+plate+"&item="+item);
                else
                    req.open("GET", "Ajax_load.php?plate="+plate);
                req.send();
            }
            else
            {
                document.getElementById("history_t").innerHTML = "";
            }
        }
        // var time = setTimeout(function(){
        //     document.getElementById('current_km').value=(isNaN(document.getElementById('c_km').innerHTML))?0:parseInt(document.getElementById('c_km').innerHTML);
        // }, 0100);
        if(item == '')
        {
            document.getElementById('floatingquantity').setAttribute('readonly',true);
            document.getElementById('floatingquantity').value = '';
        }
        else if(item == 'battery')
        {
            document.getElementById('floatingquantity').removeAttribute('readonly');
            // document.getElementById("floatingquantity").value =1;
            // document.getElementById("floatingquantity").setAttribute('readonly',true);
        }
        else
        {
            document.getElementById('floatingquantity').removeAttribute('readonly');
            // document.getElementById("floatingquantity").value ='';
        }
        cret(document.getElementById("floatingquantity"));
    }
    function pages(e)
    {
        var ul = e.parentElement.parentElement;
        var num_pages = parseInt(ul.id);
        var result;
        for(var i=1;i<=num_pages;i++)
        {
            if(ul.children[i].className.includes('active'))////////////page number buttons
                var current_page = ul.children[i].children[0];
            ul.children[i].classList.remove('active');
            document.getElementById("item "+ul.children[i].children[0].innerHTML).classList.add('d-none');
        }
        if(!isNaN(parseInt(e.innerHTML)))
        {
            result = e;
            e.parentElement.classList.add('active');
            document.getElementById("item "+e.innerHTML).classList.remove('d-none');
        }
        else
        {
            var itemm = (e.innerHTML=='Previous')?parseInt(current_page.innerHTML)-1:parseInt(current_page.innerHTML)+1;
            result = ul.children[itemm].children[0];
            ul.children[itemm].classList.add('active');
            document.getElementById("item "+itemm).classList.remove('d-none');
        }
        ul.children[0].classList.remove('disabled');
        ul.children[num_pages+1].classList.remove('disabled');
        if(result.innerHTML == 1)
            ul.children[0].classList.add('disabled');
        else if(result.innerHTML == num_pages)
            ul.children[num_pages+1].classList.add('disabled');
    }
    function cret(e)
    {
        if(e.value>20) {
            e.value=20;
        }
        var item=document.getElementById("floatingitem").value;
        var quan= parseInt(e.value);
        document.getElementById("replacement").innerHTML=empty.replace("xxxx",item);
        document.getElementById("replacement").innerHTML=(item == 'battery')?document.getElementById("replacement").innerHTML.replaceAll("xxxx",item):document.getElementById("replacement").innerHTML.replaceAll("xxxx",item + ' 1');
        var data = document.getElementById('rep');
        for(let i=1;i<quan;i++)
        {
            let tt= i+1;
            let ttdata = data.innerHTML.replaceAll("_1","_"+tt);
            document.getElementById("replacement").innerHTML+="<div class='mb-3 input-group position-relative'>"+ttdata.replaceAll(" 1"," "+tt)+"</div>";
        }
        for(let i=1;i<=quan;i++)
            document.getElementById("input_"+i).setAttribute("required",true);
        document.getElementById("replacement").classList.remove('d-none');
        if(isNaN(quan) || quan ==0)
        {
            document.getElementById("replacement").innerHTML=empty
            document.getElementById("replacement").classList.add('d-none');
        }
    }
    function checkitem(e)
    {
        if(e.value=='')
        {
            document.getElementById("badge"+e.id.replace("input","")).classList.add("d-none");
            return 0;
        }
        document.getElementById("badge"+e.id.replace("input","")).innerHTML = "<div class='spinner-border text-primary spinner-border-sm'></div>";
        document.getElementById("badge"+e.id.replace("input","")).classList.remove("d-none");
        var time = setTimeout(function(){
            let plate = document.getElementById("floatingplate").value;
            var item=document.getElementById("floatingitem").value;

            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            document.getElementById("badge"+e.id.replace("input","")).innerHTML=this.responseText;
            // document.getElementById("badge"+e.id.replace("input","")).classList.remove("d-none");
            }
            req.open("GET", "checkitem.php?serial="+e.value+"&item="+item+"&type=Tyre and Battery"+"&plate_no="+plate);
            req.send();

        }, 2000);

        var plate=document.getElementById("floatingplate").value;
        var item=document.getElementById("floatingitem").value;
        var total=document.getElementById("floatingquantity").value;
        var serials = "";
        for(let temp_i = 1;temp_i<=total;temp_i++)
        {
            let val = document.getElementById('input_'+temp_i).value;
            serials += (serials=="")?val:","+val;
        }
        const req2 = new XMLHttpRequest();
        req2.onload = function(){//when the response is ready
        document.getElementById("history_t").innerHTML=this.responseText;
        }
        req2.open("GET", "Ajax_load.php?plate="+plate+"&item="+item+"&serial="+serials);
        req2.send();
        
    }
    function round(e)
    {
        if(e.value=='')
        {
            document.getElementById("badge"+e.id.replace("input","")).classList.add("d-none");
            return 0;
        }
        document.getElementById("badge"+e.id.replace("input","")).classList.remove("d-none");
        document.getElementById("badge"+e.id.replace("input","")).innerHTML = "<div class='spinner-border text-primary'></div>";
    }
    function view_serial(e)
    {
        let all = document.getElementsByClassName('all_serials');
        for(let i_temp = 0; i_temp<all.length;i_temp++)
        {
            all[i_temp].classList.remove("active");
        }
        e.classList.add("active");
        
        document.getElementById("show_date").innerHTML = e.title.split('_')[0];
        document.getElementById("show_km").innerHTML = e.title.split('_')[1];
    }
</script>

<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7">
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Tyre,Battery and Inner Tube Request</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='../<?php echo $_SESSION["loc"]?>index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Tyre , Battery and Inner Tube Request</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
    <div class="container-fluid row">
            <?php req_count($conn,$conn_fleet,'Tyre and Battery'); ?>
            
        <div class='card' data-aos="fade-right"><!-- col-lg-4 col-md-12 -->
            <form method="POST" action="allphp.php">
                    <p class="text-center "><b>Remark : </b>All <span class='text-danger fs-5'>*</span> <span class='text-secondary'>are Required Fields</span></p>
                    <div class="card-header text-primary">
                        <h4 class="card-title">Add Purchase Order</h4>
                    </div>
                    <div class="card-body">
                    <div class="row" onkeyup="change()" onchange="change()">
                        <div class='ms-3 form-check mb-3 col-5'>
                            <input class='form-check-input' type='radio' id='External' name='mode' value='External' checked required>
                            <label class='form-check-label' for='External'>
                                External
                            </label>
                            <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                data-bs-content="Customer Vehicles"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>
                        </div>
                        <div class='ms-3 form-check mb-3 col-5'>
                            <input class='form-check-input' type='radio' id='Internal' name='mode' value='Internal' required>
                            <label class='form-check-label' for='Internal'>
                                Internal
                            </label>
                            <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                data-bs-content="Internal Vehicles Including All Sister Companies and Branches"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 form-floating mb-3" id='plate_numbers'>
                            <input onchange="loader()" name='plate' type="text" class="form-control rounded-4" id="floatingplate" placeholder="Plate Number">
                                <label for="floatingplate"><span class='text-danger'>*</span>Plate Numer</label>
                            </div>
                            <div class="col-sm-12 col-md-6 form-floating mb-3">
                                <select name='item' onchange="loader()" class="form-select inner" id="floatingitem">
                                    <option value="">-- Select Type --</option>
                                    <option value="battery">Battery</option>
                                    <option value="tyre">Tyres</option>
                                    <option value="inner tube">Inner Tube</option>
                                </select>
                                <label for="floatingitem"><span class='text-danger'>*</span>Requested Item</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-4 form-floating mb-3">
                                <input class="form-control rounded-4" type='number' step='any' id='current_km' name='current_km' readonly required>
                                <label for="current_km"><span class='text-danger'>*</span>Last Registed Kilometer</label>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-floating input-group mb-3">
                                    <!-- step="any"  -->
                                    <input onchange="loader('a')" onkeyup="loader('a')" name='req_quan' type="number" class="form-control rounded-4" id="floatingquantity" placeholder="Quantity" readonly min="0">
                                    <!-- onchange="loader()" -->
                                    <label for="floatingquantity"><span class='text-danger'>*</span>Quantity</label>
                                    <span class="input-group-text fw-bold"> Unit <br>Pcs</span>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 form-floating mb-3">
                                <input name='date_n_b' type="date" min="<?php echo $dateee?>" max="<?php echo $date_last?>" class="form-control rounded-4" id="floatingdaten">
                                <label for="floatingdaten"><span class='text-danger'>*</span>Date You Need It By</label>
                            </div>
                        </div>
                        <div class="d-none" id='replacement'>
                            <h6 class="text-center my-2 text-capitalize">xxxx To be Replaced</h6>
                            <div class="mb-3 input-group position-relative" id='rep'>
                                <span class='input-group-text text-capitalize' id='repser'><span class='text-danger'>*</span>xxxx Serial Number</span>
                                <input name='repser[]' type="text" class="form-control form-control-sm rounded-4" id='input_1' onchange="checkitem(this)" onkeyup="checkitem(this)">
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill d-none" id='badge_1'>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-sm-12 col-md-6" id='remark'>
                                <label for="remark"><span class='text-danger'>*</span>Remark <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                    data-bs-content="Reason For Purchase <br>Additional information Like Mechanic Name and Phone ..."> <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                <textarea class="form-control rounded-4" rows="1" name='remark' minlength="15" placeholder="Reason For Purchase Additional information Like Mechanic Name and Phone ..." required></textarea>
                            </div>
                            <div class="mb-3 col-sm-12 col-md-6" id='desc'>
                                <label for="desc"><span class='text-danger'>*</span>Description <span data-bs-html="true" class="btn-sm badge rounded-pill" style="cursor: pointer;" data-bs-trigger="focus" tabindex="0" data-bs-toggle="popover" title="" 
                                    data-bs-content="Details for Item including Model or Size Specification"> <i class="fa fa-info-circle text-primary" title="Details"></i></span>:</label>
                                <textarea class="form-control rounded-4" rows="1" name='description' minlength="15" placeholder="Details for Item including Model or Size Specification" required></textarea>
                            </div>
                        </div>
                        
                    <!-- <input class="" type='number' step='any' id='current_km' name='current_km' value="0"> -->
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit" name="submit_tb_request">Submit Request<i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
                    </div>
            </form>
        </div>
            <div class="border pt-4 shadow" data-aos="fade-left"><!-- col-md-12 col-lg-8  -->
                <div class="modal-header text-center">
                        <h2>Previous Data About vehicle</h2>
                </div>  
                <div class="modal-body px-2 row" id="history_t">
                            
                </div>
            </div>
    </div>
</div>
</div>
<script>
    var empty = document.getElementById("replacement").innerHTML;
    var temp_data ="";
    function change()
    {
        let Radio = document.getElementsByName("mode");
        var selected = Array. from(Radio). find(radio => radio. checked);
        if(selected.id == "Internal")
        {
            if(temp_data=="")
                temp_data = document.getElementById("plate_numbers").innerHTML;
            document.getElementById("plate_numbers").innerHTML = "<div class='mx-auto spinner-border text-primary'></div><span class='text-secondary ms-3'> Loading Vehicles </span>";
            const req = new XMLHttpRequest();
            req.onload = function(){//when the response is ready
            document.getElementById("plate_numbers").innerHTML=this.responseText;
            }
            req.open("GET", "Ajax_vehicles.php");
            req.send();
        }
        else
        {
            if(temp_data!="")
            document.getElementById("plate_numbers").innerHTML=temp_data;
        }
    }
</script>


<?php include '../footer.php';?>