
<?php 
session_start();

if(isset($_SESSION['loc']))
{
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../../");
function divcreate($str)
{
    echo "
    <div class='pricing'>
        <div class='section-title text-center py-2  alert-primary rounded'>
            <h6 class='text-white'>All Requests Pending Comparison sheet</h6> 
        </div>
        <div class='row'>
            $str
        </div>
    </div>
    ";
}
?>

<script>
set_title("LPMS | Create Comparision Sheets");
sideactive("csheet");
///////////////////////////////////////////////////Global Declaration//////////////////////////////////////////////////////////
var i=1;
var j=1;
var bool_changed=false;
var options_changed=false;
// const options_changed = [];
var holder,item_holder,options;
var stat = [];
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////Final Fix before Submiting the Form/////////////////////////////////////////////
function final_send(e)
{
    stat.forEach(indiv => {
        if(indiv)
        {
            let all_check = document.getElementsByName(stat.indexOf(indiv));
            all_check.forEach(element => {
                element.name +="[]";
            });
        }
    });
    // if(document.getElementById('performa_data').value != "")
    // {
        e.type='submit';
        e.click();
    // }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////Hold Dataa for Replication/////////////////////////////////////////////
function hold()
{
    if(!bool_changed)
    {
        bool_changed=true;
        holder= document.getElementById('company1').innerHTML;
        item_holder=document.getElementById('item 0').innerHTML;
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////ADD ITEM///////////////////////////////////////////////////////////////
function adder(e)
{
    hold();
    var comp_num = e.id;
    comp_num = comp_num.replace('add','');
    var val = parseInt(document.getElementById('item_count'+comp_num).value);
    var item_code=item_holder.replace("col-md-12","col-md-11");
    item_code=item_code.replace(id='1floatingitem0',comp_num+"floatingitem"+i);
    item_code=item_code.replace(id='1floatingprice0',comp_num+"floatingprice"+i);
    item_code=item_code.replace(id='1floatingtp0',comp_num+"floatingtp"+i);
    // item_code=item_code.replace(id='1spec0',comp_num+"spec"+i);
    item_code=item_code.replace(id='1itemselected0',comp_num+"itemselected"+i);
    item_code=item_code.replace("1itemselected0", comp_num+"itemselected"+i);
    item_code=item_code.replace("1floatingquan0", comp_num+"floatingquan"+i);
    item_code=item_code.replace("1floatingquan0", comp_num+"floatingquan"+i);
    item_code=item_code.replace("1spec0", comp_num+"spec"+i);
    item_code=item_code.replace("1spec0", comp_num+"spec"+i);
    item_code=item_code.replace("1warning0", comp_num+"warning"+i);
    item_code=item_code.replace("1quan0", comp_num+"quan"+i);

    
    const div =  document.createElement('div');
    div.id = 'item '+i;
    div.className='row bg-light py-3 mt-3 mx-auto';
    div.innerHTML = item_code + "<div class='col-sm-1'><button class='btn btn-danger btn-sm mb-2' type='button' onclick='remove(this)' id='remove"+comp_num+"'>X</button></div>";
    i++;
    document.getElementById('company'+comp_num).appendChild(div);
    document.getElementById('item_count'+comp_num).value=(isNaN(val))?1:val+1;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////Delete Item////////////////////////////////////////////////////////////////
function remove(e) 
{
    var comp_num = e.id;
    comp_num = comp_num.replace('remove','');
    var val = parseInt(document.getElementById('item_count'+comp_num).value);
    document.getElementById('item_count'+comp_num).value=(isNaN(val))?1:val-1;
    e.parentElement.parentElement.remove();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////Change Company to Company//////////////////////////////////////////////////////////
function activeco(e)
{
    let alldiv = document.getElementById('createCompModal_body').children;
    for(var k=1 ; k<alldiv.length;k++)
    {
        alldiv[k].className='d-none';
        document.getElementById('Company_'+k+'_tab').className = "nav-link text-dark fw-bold";
    }
    var id_comp = e.id.replace("Company_","").replace("_tab","");
    document.getElementById('company'+id_comp).removeAttribute("class");
    document.getElementById('Company_'+id_comp+'_tab').className = "nav-link active bg-warning text-black fw-bold";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////Set Company Name///////////////////////////////////////////////////////////////////////////////
function company_name(e)
{
    let c_num = e.id.replace('floatingco','');
    document.getElementById('Company_'+c_num+'_tab').innerHTML=(e.value=='')?"Company "+c_num:document.getElementById('Company_'+c_num+'_tab').innerHTML=e.value.charAt(0).toUpperCase() + e.value.slice(1);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////ADD COMPANY//////////////////////////////////////////////////////////////////////////
function add_company()
{
////////////////////###########add Tab for company###########///////////////////////////////////
    j++;
    const li =  document.createElement('li');
    const btn = document.createElement('button');
    const txt = document.createTextNode('Company '+j);
    li.className="nav-item";
    btn.id='Company_'+j+'_tab';
    btn.appendChild(txt);
    btn.type='button';
    btn.setAttribute('onclick','activeco(this)')
    btn.className="nav-link text-dark";
    li.appendChild(btn);
    document.getElementById('company_tab').insertBefore(li,document.getElementById('add_company'));
    document.getElementById('remove_company').className="nav-item";

////////////////////###########add new company Div###########///////////////////////////////////

    const div =  document.createElement('div');
    div.id='company'+j;
    div.className="d-none";
    div.innerHTML=(bool_changed)?holder:document.getElementById('company1').innerHTML;
    div.innerHTML = div.innerHTML.replace("add1","add"+j);
    div.innerHTML = div.innerHTML.replace("item_count1","item_count"+j);
    div.innerHTML = div.innerHTML.replaceAll("1floatingco",j+"floatingco");
    div.innerHTML = div.innerHTML.replace("1floatingitem0",j+"floatingitem0");
    div.innerHTML = div.innerHTML.replace("1floatingprice0",j+"floatingprice0");
    div.innerHTML = div.innerHTML.replace("1floatingtp0",j+"floatingtp0");
    div.innerHTML = div.innerHTML.replace("1spec0",j+"spec0");
    div.innerHTML = div.innerHTML.replace("1spec0",j+"spec0");
    div.innerHTML = div.innerHTML.replace("1itemselected0",j+"itemselected0");
    div.innerHTML = div.innerHTML.replace("1itemselected0", j+"itemselected0");
    div.innerHTML = div.innerHTML.replace("1floatingquan0", j+"floatingquan0");
    div.innerHTML = div.innerHTML.replace("1floatingquan0", j+"floatingquan0");
    div.innerHTML = div.innerHTML.replace("1warning0", j+"warning0");
    div.innerHTML = div.innerHTML.replace("1quan0", j+"quan0");
    document.getElementById('createCompModal_body').appendChild(div);
    activeco(document.getElementById('Company_'+j+'_tab'));
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////Delete Company////////////////////////////////////////////////////////////////
function remove_company()
{
    if(j>1)
    {
        document.getElementById('Company_'+j+'_tab').remove();
        document.getElementById('company'+j).remove();
        j--;
        activeco(document.getElementById('Company_'+j+'_tab'));
    }
    if (j==1)
        document.getElementById('remove_company').className="nav-item d-none";
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////When Item Selected/////////////////////////////////////////////////////////////////////
    

    
    
    function green(e)
    {
        //####################################declarations######################################//

        var num = e.id.split("itemselected");//////////////get Company and Item Number
        var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);////////SElect Box for the Item
        let t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);////////////Total Quantity
        let req = parseFloat(document.getElementById(num[0]+'floatingquan'+num[1]).value);////////Amount on Current Item
        var all_com = document.getElementsByName(e.name);//all company that offeres that item
        let agg=0;///////////To check Aggrigate
        var tempp = e.parentElement.parentElement.parentElement;///////////////////////////////Current Item Box

        //#####################################################################################//

        //////////////////////////////////////////////////////////////////////If Being Selected//////////////////////////////////////////////////////////////
        if(e.checked)
        {
            //////////////////////////////////////////Partly Supplied////////////////////////////////////////////////////////////////
            if(t_req > req)
            {
                stat[e.name]=true;///////////Hold Partly Submitted Items 
                ////////////////////Change into Checkbox////////////////////
                all_com.forEach(com => {
                    com.type='checkbox';
                    com.removeAttribute("required");
                });
                ////////////////////////////////////////////////////////////

                /////////////////////Calculate Aggrigate////////////////////
                all_com.forEach(com => {
                    if(com.checked)
                    {
                        let t_num = com.id.split("itemselected");
                        agg+=parseFloat(document.getElementById(t_num[0]+'floatingquan'+t_num[1]).value);
                    }
                });
                /////////////////////////////////////////////////////////////

                /////////////////////OVER Aggrigate/////////////////////////
                if(agg>t_req)
                {
                    all_com.forEach(com => {
                        if(com.checked)
                        {
                            let t_num = com.id.split("itemselected");
                            let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                            warn.innerHTML='*Aggrigate Amount is Greater Than Requested';
                            warn.classList.add('text-danger');
                        }
                        else
                        {
                            let t_num = com.id.split("itemselected");
                            let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                            warn.innerHTML='';
                        }
                    });
                }
                /////////////////////////////////////////////////////////////
                /////////////////////Not Over///////////////////////////////
                else 
                {
                    //////////////////Fully Supplied in aggrigate/////////////////////
                    // if(agg==t_req)
                    // {
                    //     all_com.forEach(com => {
                    //         com.parentElement.parentElement.parentElement.classList.remove('alert-warning');
                    //         com.parentElement.parentElement.parentElement.classList.add('alert-success');
                    //     });
                    // }
                    //////////////////////////////////////////////////////////////
                    all_com.forEach(com => {
                        let t_num = com.id.split("itemselected");
                        let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                        warn.innerHTML='';
                    });
                }
                //////////////////////////////////////////////////////////////

                ////////////////////////////Remove Colors/////////////////////
                all_com.forEach(com => {
                    com.parentElement.parentElement.parentElement.classList.remove('alert-success');
                    com.parentElement.parentElement.parentElement.classList.remove('alert-danger');
                    com.parentElement.parentElement.parentElement.classList.remove('bg-light');
                    // btn_r.parentElement.parentElement.parentElement.classList.add("alert-danger");
                });
                ///////////////////////////////////////////////////////////////

                ////////////////////////Main Coloring ////////////////////////
                if(!tempp.className.includes('alert-warning'))
                    tempp.classList.add("alert-warning");
                else
                {
                    tempp.classList.add("bg-light");
                    tempp.classList.remove('alert-warning');
                }
                ////////////////////////////////////////////////////////////
            }

            else
            {
            /////////////////////////////////////////////////Fully Supplied/////////////////////////////////////////
                if(stat[e.name])//////////////if it was Partially before/////////
                    stat[e.name]=false;
                    
                    all_com.forEach(com => {
                        com.type='radio';
                        // com.setAttribute("required",true);
                        com.parentElement.parentElement.parentElement.classList.remove('alert-warning');
                        com.parentElement.parentElement.parentElement.classList.remove("alert-danger");
                        com.parentElement.parentElement.parentElement.classList.remove('bg-light');
                });
                if(tempp.className.includes('alert-success'))
                {
                    e.checked = false;
                    tempp.classList.remove('alert-success');
                    tempp.classList.add("bg-light");
                }
                else
                {
                    all_com.forEach(com => {
                        com.parentElement.parentElement.parentElement.classList.add("alert-danger");
                        com.parentElement.parentElement.parentElement.classList.remove('alert-success');
                    });
                    tempp.classList.remove('alert-danger');
                    tempp.classList.remove('bg-light');
                    tempp.classList.add("alert-success");
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else
        {/////////////////////////not selected check
            let r_buttons = document.getElementsByName(e.name);
            var tempp = e.parentElement.parentElement.parentElement;
            r_buttons.forEach(btn_r => {
                if(btn_r.parentElement.parentElement.parentElement.className.includes('alert-success'))
                {
                    tempp.classList.remove('bg-light');
                    tempp.classList.add('alert-danger');
                }
                else
                {
                    tempp.classList.remove('alert-danger');
                    tempp.classList.remove('alert-warning');
                    tempp.classList.remove('alert-success');
                    tempp.classList.add('bg-light');
                }
            });
            let agg=0;
            r_buttons.forEach(btn_r => {
                    if(btn_r.checked)
                    {
                        let t_num = btn_r.id.split("itemselected");
                        agg+=parseFloat(document.getElementById(t_num[0]+'floatingquan'+t_num[1]).value);
                    }
                });
                if(agg<=t_req)
                {
                    r_buttons.forEach(btn_r => {
                        let t_num = btn_r.id.split("itemselected");
                        let warn = document.getElementById(t_num[0]+'warning'+t_num[1]);
                        warn.innerHTML='';
                    });
                }
        }
    }
    function t_p(e)
    {
        if(e.id.includes("floatingprice"))
        {
            var num = e.id.split("floatingprice");
            var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);
            t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);
            req = document.getElementById(num[0]+'floatingquan'+num[1]).value;
        }
        else if(e.id.includes("floatingquan"))
        {
            var num = e.id.split("floatingquan");
            var slctitem = document.getElementById(num[0]+'floatingitem'+num[1]);
            t_req = parseFloat(slctitem.options[slctitem.selectedIndex].className);
            req = e.value;
            hold(); 
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            obj_selector.setAttribute("name",document.getElementById(num[0]+'floatingitem'+num[1]).value);
            obj_selector.setAttribute("value",num[0]);
            if(e.value=='')
            {
                obj_selector.setAttribute("type",'radio');
                obj_selector.setAttribute("disabled",true);
                obj_selector.checked = false;
            }
        }
        else
        {
            var num = e.id.split("floatingitem");
            t_req = parseFloat(e.options[e.selectedIndex].className);
            hold(); 
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            obj_selector.setAttribute("name",document.getElementById(num[0]+'floatingitem'+num[1]).value);
            obj_selector.setAttribute("value",num[0]);
            if(e.value=='none')
            {
                obj_selector.setAttribute("type",'radio');
                obj_selector.setAttribute("disabled",true);
                obj_selector.checked = false;
            }
            else
            {
                /////////////////////////////////////////////////////////////////////////
                add_vat_item(e);
                /////////////////////////////////////////////////////////////////////////
                if(stat[document.getElementById(num[0]+'floatingitem'+num[1]).value])
                {
                    obj_selector.removeAttribute("required");
                    obj_selector.type='checkbox';
                }
            }
            if(!isNaN(t_req))
            {
                e.title =  e.options[e.selectedIndex].title;
                let unit = e.options[e.selectedIndex].id;
                document.getElementById(num[0]+'quan'+num[1]).innerHTML = 'Quantity - '+t_req+' '+unit;
            }
            else
                document.getElementById(num[0]+'quan'+num[1]).innerHTML = '';
            green(document.getElementById(num[0]+'itemselected'+num[1]));
            // var val = parseInt(document.getElementById('item_count'+num[0]).value);
            // save_options(document.getElementById(num[0]+'floatingitem'+num[1]));
            // for(let items = 0; items < val; items++)
            // {
            //     if(e.selectedIndex == 0) document.getElementById(num[0]+'floatingitem'+items).innerHTML = options;
            //     if(num[1] != items)
            //     {
            //         document.getElementById(num[0]+'floatingitem'+items).remove(e.selectedIndex);
            //     }
            // }
        }
        if(document.getElementById(num[0]+'floatingitem'+num[1]).value!='none' && document.getElementById(num[0]+'floatingquan'+num[1]).value!='')
        {
            var obj_selector = document.getElementById(num[0]+'itemselected'+num[1]);
            //if(obj_selector.type=='radio') obj_selector.setAttribute("required",true);
                obj_selector.removeAttribute("disabled");
        }
        var warn = document.getElementById(num[0]+'warning'+num[1]);
        if(document.getElementById(num[0]+'floatingquan'+num[1]).value!="" && document.getElementById(num[0]+'floatingitem'+num[1]).value!="none")
        {
            if(t_req <document.getElementById(num[0]+'floatingquan'+num[1]).value)
            {
                warn.innerHTML='* Amount is Greater Than Requested';
                warn.classList.add('text-danger');
            }
            else
            {
                warn.innerHTML='';
            }
        }
        else
        {
            warn.innerHTML='';
        }
            document.getElementById(num[0]+'floatingtp'+num[1]).value=req * document.getElementById(num[0]+'floatingprice'+num[1]).value;
        // if(document.getElementById(num[0]+'floatingprice'+num[1]).value=="" || document.getElementById(num[0]+'floatingitem'+num[1]).value=="none")
        //     document.getElementById(num[0]+'floatingtp'+num[1]).value="";
    }
    function save_options(e)
    {
        if(!options_changed)
        {
            options_changed=true;
            options = e.innerHTML;
        }
    }
    var count_vat = 0;
    function add_vat_item(e)
    {
        ////////////////////////
        let vat_found = false;
        let all_vats = document.getElementsByClassName('vats');
        for(var i=0; i<all_vats.length;i++)
        {
            if(all_vats[i].value == e.value) vat_found = true;
        }
        if(!vat_found)
        {
            count_vat++;
            let add_html = vat_template.replaceAll('::0',"::"+count_vat);
            const div =  document.createElement('div');
            div.id = 'vats_'+count_vat;
            div.innerHTML = add_html;
            document.getElementById('vat_holder').appendChild(div);
            document.getElementById('vat_lbl::'+count_vat).innerHTML = e.options[e.selectedIndex].innerHTML;
            document.getElementById('vat_for::'+count_vat).value = e.value;
        }
    }

    // function loader(e)
    // {
    //     j=1;
    //     i=1;
    //     bool_changed=false;
    //     document.getElementById('ttl').innerHTML = 'Fill Comparision Sheet For '+e.id;
    //     var element = e.parentElement.parentElement.children[0].innerHTML;
    //     var idd = e.parentElement.parentElement.children[1].innerHTML;
    //     var name = e.parentElement.parentElement.children[2].innerHTML;
    //     const req = new XMLHttpRequest();
    //     req.onload = function(){//when the response is ready
    //     document.getElementById("createCompModal_body").innerHTML=this.responseText;
    //     }
    //     req.open("GET", "Ajax.php?db="+element+"&idd="+idd+"&name="+name);
    //     req.send();
    // }
</script>
<div id="main">
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-7 col-xl-7"> 
        <header>
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>
        <h2>Create Comparision Sheets</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Create Comparision Sheets</li>
        </ol>
    </div>
    <?php include '../../common/profile.php';?>
</div>
<script>
    function load_performa(e)
    {
        document.getElementById('insert_performa').value = e.name;
    }
</script>
<div class="container-fluid px-4">
<?php
$sql = "SELECT * FROM `purchase_order` WHERE (`status`='Performa Comfirmed' OR (`status`='Generated' AND `performa_id` IS NULL)) AND `procurement_company` = ? ORDER BY timestamp DESC";
$stmt_for_comparision = $conn -> prepare($sql);
$stmt_for_comparision -> bind_param("s", $_SESSION['company']);
$stmt_for_comparision -> execute();
$result_for_comparision = $stmt_for_comparision -> get_result();
$str="";
if($result_for_comparision -> num_rows>0)
    while($row = $result_for_comparision -> fetch_assoc())
    {
        $type=$row['request_type'];
        $na_t=str_replace(" ","",$type);
        $stmt_request_with_report -> bind_param("i", $row['request_id']);
        $stmt_request_with_report -> execute();
        $result_request_with_report = $stmt_request_with_report -> get_result();
        if($result_request_with_report -> num_rows>0)
            while($row2 = $result_request_with_report -> fetch_assoc())
            {
                if($row['request_type']=="Consumer Goods")
                {
                    $id=$row2['request_for'];
                    if($row2['request_for'] == 0)
                    {
                        $stmt_project->bind_param("i", $row2['request_for']);
                        $stmt_project->execute();
                        $result3 = $stmt_project->get_result();
                        $res=($result3->num_rows>0)?true:false;
                    }
                    else
                    {
                        $idd = explode("|",$row2['request_for'])[0];
                        $stmt_project_pms->bind_param("i", $idd);
                        $stmt_project_pms->execute();
                        $result3 = $stmt_project_pms->get_result();
                        $res=($result3->num_rows>0)?true:false;
                    }
                }
                else if($row['request_type']=="Spare and Lubricant")
                {
                    $id=$row2['request_for'];
                    $stmt_description->bind_param("i", $row2['request_for']);
                    $stmt_description->execute();
                    $result3 = $stmt_description->get_result();
                    $res=($result3->num_rows>0)?true:false;  
                }
                else if($row['request_type']=="Tyre and Battery")
                {
                    $id=$row2['request_for'];
                    $name=$row2['request_for'];
                    $res=false;
                }
                else 
                {
                    $id=$row2['request_id'];
                    $res=false;
                    $name=$row2['item'];
                }

                if($res)
                    while($row3 = $result3->fetch_assoc())
                    {
                        if($row['request_type']=="Consumer Goods")
                        {
                            $name = "Project - ".(($row2['request_for'] == 0)?$row3['Name']:$row3['project_name']);
                        }
                        else if($row['request_type']=="Spare and Lubricant")
                            $name=$row3['description'];
                    }
                    if($row['request_type']=="Spare and Lubricant" && strpos($row2['request_for'],"None|")!==false) $name = (explode("|",$row2['request_for'])[1] == 0)?$row2['item']:"Job - ".explode("|",$row2['request_for'])[1];
                    if($row['priority']>3) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else if($row['priority']>0) $prio = "<i class='text-warning fas fa-star'></i>".$row['priority']."/5";
                    else $prio="";
                    $printpage = "
                        <form method='GET' action='../../requests/print.php' class='float-end'>
                            <button type='submit' class='btn btn-outline-secondary border-0' id='print_$row[purchase_order_id]' name='print' value='".$row['request_id'].":|:$type'>
                                <i class='text-dark fas fa-print'></i>
                            </button>
                        </form>";
                    $str.= "
                    <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                        <div class='box'><h3 class='text-capitalize'>
                            <span class='small text-secondary float-start'>$prio</span>";
                            $str.=($res || $row['request_type']=="Tyre and Battery")?$name:"<button type='button'  title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                            $name</button>";
                            $str.= "
                            $printpage
                            <span class='small text-secondary d-block mt-2'>$type</span>
                            </h3>
                            <form method='GET' action='allphp.php'>
                            <ul>
                                <li class='d-none'>$type:-:$id:-:$name:-:$row2[company]:-:$row2[department]</li>";
                                $str.=($res || $row['request_type']=="Tyre and Battery")?"
                                <li class='text-start'><span class='fw-bold'>Item : </span>
                                <button type='button' title='".$row2['description']."' value='".$row2['recieved']."' name='specsfor_".$na_t."_".$row['request_id']."' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#item_details' onclick='openmodal(this)' >
                                ".$row2['item']."</li></button>":"";
                                $str.="
                                <li class='text-start'><span class='fw-bold'>Department : </span>".$row2['department']."</li>
                                <li class='text-start'><span class='fw-bold'>Requsted Quantity : </span>".$row2['requested_quantity']." ".$row2['unit']."</li>
                                ".((is_null($row2['performa_confirm_date']))?"":"
                                <li class='text-start'><span class='fw-bold'>Proforma Received Date : </span>".date("d-M-Y", strtotime($row2['performa_confirm_date']))."</li>")."
                                ".((is_null($row2['date_requested']))?"":"
                                <li class='text-start'><span class='fw-bold'>Date Requested : </span>".date("d-M-Y", strtotime($row2['date_requested']))."</li>")."
                                ";
                                    $str.= ($row['status'] != "Generated")?"<li>
                                    <input type='button' value='Create Comparision Sheet' 
                                    class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#createCompModal' onclick='compsheet_creater(this,2)' id='$name'>
                                    <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='req_id' value='".$row2['purchase_requisition']."' >Chat <i class='text-white text-white fa fa-comment'></i></button>
                                    </li>":
                                    "<li>
                                        <button type='button' name='".$row['cluster_id']."' onclick='compsheet_loader(this)' class='btn btn-outline-warning btn-sm' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                                        <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                                    </li>";
                            $str.= "
                            </ul>
                            </form>
                        </div>
                    </div>
                    ";
            }
    }
        
    if($str=='')
    echo "
        <div class='py-5 pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h3 class='mt-4'>No Requests Awaiting Comparision sheets</h3>
            </div>
        </div>";
else 
    divcreate($str);
    ?>
    
</div>
</div>
<?php include "../../footer.php"; ?>
<script>
    var vat_template = document.getElementById('vat_sample').innerHTML;
    document.getElementById('vat_sample').remove();
</script>
