<?php 
if(strpos($_SERVER['PHP_SELF'],'Procurement') || strpos($_SERVER['PHP_SELF'],'procurement'))
    echo "<script> var pos = '../';</script>";
else
    echo "<script> var pos = '';</script>";
?>
<script>
    const open_icon = [];
    var inc_icon = 0;
    function drop_down(e,div)
    {
        let x = document.getElementById(div);
        if(x.className.includes('d-none'))
        {
            let divs = document.getElementsByClassName('drops');
            for(let i=0;i<divs.length;i++)
            {
                divs[i].classList.add('d-none');
            }
            for(let i=0;i<open_icon.length;i++)
            {
                open_icon[i].innerHTML = document.getElementById("down-arrow").innerHTML;
            }
            x.classList.remove('d-none');
            let icon = document.getElementById(div+"_icon");
            open_icon[inc_icon] = icon;
            inc_icon++;
            icon.innerHTML = document.getElementById("up-arrow").innerHTML;
        }
        else
        {
            x.classList.add('d-none');
            let icon = document.getElementById(div+"_icon");
            icon.innerHTML = document.getElementById("down-arrow").innerHTML;
        }
    }
// $(".flip").click(function(){
// return false;
// });
    let req_side = new XMLHttpRequest();
    req_side.onload = function(){//when the response is ready
        document.getElementById('side_list').innerHTML=this.responseText;
        sideactive(sidetobe,"open");
    }
    req_side.open("GET", pos+"../common/ajax_side.php?pos="+pos);
    req_side.send();
</script>