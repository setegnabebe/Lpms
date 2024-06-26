
<script>
    const aggr = [];
    function clicked(e,row)
    {
        if (typeof aggr[row] === 'undefined') {
            aggr[row] = 0;
        }
        var chil = e.parentElement.children;
        let req_id = e.title.split("_")[0];
        let quan = parseInt(document.getElementById('purchasequan_'+req_id).innerHTML);
        let provided = parseInt(document.getElementById('prov_'+e.title).innerHTML);
        let color_add = (quan == provided)?"bg-success":"bg-warning";
        if(aggr[row] + provided > quan && color_add == "bg-warning" && !e.className.includes('bg-warning')) return 0;
        if(e.className.includes('bg-success'))
        {
            aggr[row] = 0;
            for(var i = 0; i<chil.length;i++)
            {
                chil[i].classList.remove('bg-success');
                chil[i].classList.remove('bg-warning');
                chil[i].classList.remove('bg-danger');
                chil[i].classList.remove('bg-opacity-75'); 
                chil[i].classList.replace('text-dark','text-success');
            }
            document.getElementById(e.title).checked = false;
        }
        else if(e.className.includes('bg-warning'))
        {
            aggr[row] -= provided;
            for(var i = 0; i<chil.length;i++)
            {
                if(chil[i].title == e.title || aggr[row]==0){
                chil[i].classList.remove('bg-success');
                chil[i].classList.remove('bg-warning');
                chil[i].classList.remove('bg-danger');
                chil[i].classList.remove('bg-opacity-75'); 
                chil[i].classList.replace('text-dark','text-success');
                }
            }
            document.getElementById(e.title).checked = false;
        }
        else
        {
            var once = true;
            for(var i = 0; i<chil.length;i++)
            {
                if(chil[i].title == e.title)
                {
                    if(color_add == 'bg-warning' && aggr[row] + provided <= quan && once)
                    {
                        aggr[row] += provided;
                        once = false;
                    }
                    else if(color_add == 'bg-success')
                    {
                        aggr[row] = provided;
                    }
                    chil[i].classList.remove('bg-danger');
                    chil[i].classList.add(color_add);
                    chil[i].classList.add('bg-opacity-75'); 
                    chil[i].classList.remove('text-success');
                    chil[i].classList.add('text-dark');
                    
                }
                else if(chil[i].className.includes('has'))
                {
                    let provided_all = parseInt(document.getElementById('prov_'+chil[i].title).innerHTML);
                    if(color_add == 'bg-success')
                    {
                        document.getElementById(chil[i].title).checked = false;
                    }
                    if(color_add == "bg-success" || provided_all == quan)// || (aggr[row] + provided_all > quan &&  chil[i].classList.includes('bg-warning')))// || aggr[row] > quan )
                    {
                        chil[i].classList.remove('bg-success');
                        chil[i].classList.remove('bg-warning');
                        chil[i].classList.add('bg-danger');
                        chil[i].classList.add('bg-opacity-75'); 
                        chil[i].classList.remove('text-success');
                        chil[i].classList.add('text-dark');
                    }
                }
            }
            document.getElementById(e.title).click();
        }
    }
    function proceed()
    {
        document.getElementById('Approved').click();
    }
</script>