<?php 

session_start();
if(isset($_SESSION['loc']))
{
    if($_SESSION["role"] != "manager" && $_SESSION["role"] != "Director") header("Location: ../");
    $string_inc = 'head.php';
    include $string_inc;
}
else
    header("Location: ../");
function divcreate($str)
{
    echo "
        <div class='pricing'>
            <div class='section-title text-center py-2  alert-primary rounded'>
                <h6 class='text-white'>POs waiting Approval</h4> 
            </div>
            <div class='row'>
                $str
            </div>
        </div>
    ";
}
?>
<script>
    set_title("LPMS | Prepare Cheque");
    sideactive("finance_approval");
    function batch_select(e)
    {
        let selections = "";
        let indicator = false;
        let all_batch = document.getElementsByClassName("ch_boxes");
        for(let i=0;i<all_batch.length;i++)
        {
            if(all_batch[i].checked) 
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.add("border-primary");
                indicator=true;
                selections += (selections =="")?all_batch[i].value:","+all_batch[i].value;
            }
            else
            {
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-2");
                all_batch[i].parentElement.parentElement.parentElement.parentElement.classList.remove("border-primary");
            }
        }
        document.getElementById("batch_approve").value = selections;
        if(indicator)
            document.getElementById('batch_div').classList.remove('d-none');
        else 
            document.getElementById('batch_div').classList.add('d-none');
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
        <h2>Process Payment for Requests</h2>
        <ol class="breadcrumb my-4">
            <li class="breadcrumb-item"><a href='index.php' style="text-decoration: none;">Home</a></li>
            <li class="breadcrumb-item active">Process Payment for Requests</li>
        </ol>
    </div>
    <?php include '../common/profile.php';?>
</div>
<div id='batch_div' class="position-fixed d-none my-4 p-4 shadow bg-light" style="top: 80%; left: 90%; z-index:1;">
    <form method="GET" action="allphp.php">
        <div class=''>
            <button type='button' onclick = 'prompt_confirmation(this)' class='btn btn-xl btn-outline-primary shadow mt-3' name='batch_approve' id='batch_approve'>Approve</button>
        </div>
    </form>
    <div class="mt-3 form-check">
        <input type="checkbox" class="form-check-input" id="checkboxAll" onclick='checkboxAll(this)'>
        <label class="form-check-label" for="checkboxAll">Select All</label>
    </div>
</div>
    <?php
        $str="";
        $status = "Reviewed";
        $stmt_cluster_by_finance->bind_param("ss", $status, $_SESSION['company']);
        $stmt_cluster_by_finance->execute();
        $result_cluster_by_finance = $stmt_cluster_by_finance->get_result();
        if($result_cluster_by_finance->num_rows>0)
        while($r_clus = $result_cluster_by_finance->fetch_assoc())
        {
            $stmt2 = $conn->prepare("SELECT `request_type`, count(*) AS num_req FROM `purchase_order` where `cluster_id`='".$r_clus['id']."'");
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->bind_result($r_type,$num_req);
            $stmt2->fetch();
            $stmt2->close();
            $printpage = "
                <form method='GET' action='../requests/print.php' class='float-end'>
                    <button type='submit' class='btn btn-outline-secondary border-0' name='print' value='".$r_clus['id'].":|:all'>
                        <i class='text-dark fas fa-print'></i>
                    </button>
                </form>";
            $str.= "
            <div class='col-md-6 col-lg-4 col-xl-3 my-4 focus'>
                <div class='box'>
                <h3>
                <span class='small text-secondary float-start'>
                <input value='".$r_clus['id']."' class='ch_boxes form-check-input d-block' type='checkbox' onclick='batch_select(this)'>
                </span>".$r_clus['type']."
                $printpage
                </h3>
                <form method='GET' action='allphp.php'>
                <ul>
                    <li class='text-start'><span class='fw-bold'>Items in Request : </span>".$num_req."</li>
                    <li class='text-start'><span class='fw-bold'>Total Price : </span>".number_format($r_clus['price'], 2, ".", ",")."</li>
                    <button type='button' name='".$r_clus['id']."' onclick='compsheet_loader(this)' class='btn btn-outline-primary btn-sm shadow' data-bs-toggle='modal' data-bs-target='#comp_sheet'>View Comparision Sheet
                    <i class='text-white fas fa-clipboard-list fa-fw'></i></button>
                    <li class='mt-3'>
                    <button class='btn btn-outline-success' type='button' onclick = 'prompt_confirmation(this)' value='".$r_clus['id']."' name='process'>Approve</button>
                    <button type='button' class='btn btn-outline-primary btn-sm shadow ' data-bs-toggle='modal' data-bs-target='#chat_modal' onclick='floating_chat_box(this)' name='cluster' value='".$r_clus['id']."' >Chat <i class='text-primary fa fa-comment'></i></button>
                    </li>
                </ul>
                </form>
                </div>
            </div>
                ";
        }
        if($str=='') 
            echo "<div class='py-5 pricing'>
                <div class='section-title text-center py-2  alert-primary rounded'>
                    <h3 class='mt-4'>There are no Purchase Orders Waiting for Payment</h3>
                </div>
            </div>";
        else
            divcreate($str);
    ?>
    
</div>
</div>
<?php include '../footer.php';?>