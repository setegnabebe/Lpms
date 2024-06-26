<?php
$getcomapny=$_GET['Graph_company'];
if(isset($getcomapny)){
  
   
$sql_bargraph_types = "SELECT DISTINCT request_type from requests  where company = ? and `status`='All Complete' ORDER BY
`requests`.`request_type` ASC ;";
$sql_bargraph_query="SELECT
request_type,
requests.request_id,
company,
AVG(
    CASE WHEN manager_approval_date IS NOT NULL AND request_date IS NOT NULL THEN timetocalculate(
        manager_approval_date,
        request_date
    ) ELSE 0
END
) AS manager,
AVG(
CASE WHEN Director_approval_date IS NOT NULL AND manager_approval_date IS NOT NULL THEN timetocalculate(
    Director_approval_date,
    manager_approval_date
) ELSE 0
END
) AS director,
AVG(
CASE WHEN Owner_approval_date IS NULL AND stock_check_date IS NOT NULL AND Director_approval_date IS NOT NULL THEN timetocalculate(
    stock_check_date,
    Director_approval_date
) WHEN Owner_approval_date IS NOT NULL AND stock_check_date IS NOT NULL THEN timetocalculate(
    stock_check_date,
    Owner_approval_date
) ELSE 0
END
) AS stock,
AVG(
CASE WHEN property_approval_date IS NOT NULL AND stock_check_date IS NOT NULL THEN timetocalculate(
    property_approval_date,
    stock_check_date
) ELSE 0
END
) AS property,
AVG(
CASE WHEN officer_assigned_date IS NOT NULL AND property_approval_date IS NOT NULL THEN timetocalculate(
    officer_assigned_date,
    property_approval_date
) ELSE 0
END
) AS AssignTime,
AVG(
CASE WHEN officer_assigned_date IS NOT NULL AND officer_acceptance_date IS NOT NULL THEN timetocalculate(
    officer_acceptance_date,
    officer_assigned_date
) ELSE 0
END
) AS AcceptanceTime,
AVG(
CASE WHEN performa_generated_date IS NOT NULL AND officer_acceptance_date IS NOT NULL THEN timetocalculate(
    performa_generated_date,
    officer_acceptance_date
) ELSE 0
END
) AS PerformaCollect,
AVG(
CASE WHEN performa_generated_date IS NOT NULL AND performa_confirm_date IS NOT NULL THEN timetocalculate(
    performa_confirm_date,
    performa_generated_date
) ELSE 0
END
) AS PerformaConfirm,
AVG(
CASE WHEN compsheet_generated_date IS NOT NULL AND performa_confirm_date IS NOT NULL THEN timetocalculate(
    compsheet_generated_date,
    performa_confirm_date
) ELSE 0
END
) AS comparision,
AVG(
CASE WHEN committee_approval_date IS NOT NULL AND sent_to_committee_date IS NOT NULL THEN timetocalculate(
    committee_approval_date,
    sent_to_committee_date
) ELSE 0
END
) AS committe,
AVG(
CASE WHEN committee_approval_date IS NOT NULL AND sent_to_finance_date IS NOT NULL THEN timetocalculate(
    sent_to_finance_date,
    committee_approval_date
) ELSE 0
END
) AS finalize,
AVG(
CASE WHEN sent_to_finance_date IS NOT NULL AND Disbursement_review_date IS NOT NULL THEN timetocalculate(
    Disbursement_review_date,
    sent_to_finance_date
) ELSE 0
END
) AS Review,
AVG(
CASE WHEN finance_approval_date IS NOT NULL AND Disbursement_review_date IS NOT NULL THEN timetocalculate(
    finance_approval_date,
    Disbursement_review_date
) ELSE 0
END
) AS FinanceApprove,
AVG(
CASE WHEN finance_approval_date IS NOT NULL AND cheque_prepared_date IS NOT NULL THEN timetocalculate(
    cheque_prepared_date,
    finance_approval_date
) ELSE 0
END
) AS PrepareCheque,
AVG(
CASE WHEN cheque_signed_date IS NOT NULL AND cheque_prepared_date IS NOT NULL THEN timetocalculate(
    cheque_signed_date,
    cheque_prepared_date
) ELSE 0
END
) AS SignCheque,
AVG(
CASE WHEN cheque_signed_date IS NOT NULL AND collection_date IS NOT NULL THEN timetocalculate(
  collection_date,
  cheque_signed_date
) ELSE 0
END
) AS CollectionTime,
AVG(
CASE WHEN collection_date IS NOT NULL AND dep_check_date IS NOT NULL THEN timetocalculate(dep_check_date, collection_date) ELSE 0
END
) AS DepartmentCheck,
AVG(
CASE WHEN recollection_date IS NOT NULL AND dep_check_date IS NOT NULL THEN timetocalculate(
    recollection_date,
    dep_check_date
) ELSE 0
END
) AS RecollectionTime,
AVG(
CASE WHEN handover_comfirmed IS NOT NULL AND dep_check_date IS NOT NULL THEN timetocalculate(
    handover_comfirmed,
    dep_check_date
) ELSE 0
END
) AS StoreConfirm,
AVG(
CASE WHEN handover_comfirmed IS NOT NULL AND final_recieved_date IS NOT NULL THEN timetocalculate(
    final_recieved_date,
    handover_comfirmed
)
 WHEN handover_comfirmed IS NULL AND final_recieved_date IS NOT NULL THEN timetocalculate(
    final_recieved_date,
    property_approval_date
)



 ELSE 0
END
) AS FinalHandover
FROM
`report`
INNER JOIN requests ON report.request_id = requests.request_id
WHERE
STATUS
= 'All Complete' AND final_recieved_date is not null and   company = ?
GROUP BY
request_type
ORDER BY
`requests`.`request_type` ASC;";
$stmt_bargraph_query = $conn -> prepare($sql_bargraph_query);
$stmt_bargraph_query -> bind_param("s", $getcomapny);
$stmt_bargraph_query -> execute();
$result_bargraph_query = $stmt_bargraph_query -> get_result();
if($result_bargraph_query -> num_rows > 0)
  while($row2=$result_bargraph_query -> fetch_assoc())
  {
    $manager[]=  round($row2['manager'],2);
    $director[]=  round($row2['director'],2);
    $stock[]=  round($row2['stock'],2);
    $property[]= round($row2['property'],2);
    $AssignTime[]= round($row2['AssignTime'],2);
    $AcceptanceTime[]= round($row2['AcceptanceTime'],2);
    $PerformaCollect[]= round($row2['PerformaCollect'],2);
    $PerformaConfirm[]= round($row2['PerformaConfirm'],2);

    $comparision[]=  round($row2['comparision'],2);
    $committe[]=  round($row2['committe'],2);
    $finalize[]=  round($row2['finalize'],2);
    $Review[]=  round($row2['Review'],2);
    $FinanceApprove[]=  round($row2['FinanceApprove'],2);
    $PrepareCheque[]=  round($row2['PrepareCheque'],2);
    $SignCheque[]=  round($row2['SignCheque'],2);
    $CollectionTime[]=  round($row2['CollectionTime'],2);
    $DepartmentCheck[]=  round($row2['DepartmentCheck'],2);
    $RecollectionTime[]=  round($row2['RecollectionTime'],2);
    $StoreConfirm[]=  round($row2['StoreConfirm'],2);
    $FinalHandover[]=  round($row2['FinalHandover'],2);
  }

$stmt_bargraph_types = $conn -> prepare($sql_bargraph_types);
$stmt_bargraph_types -> bind_param("s", $getcomapny);
$stmt_bargraph_types -> execute();
$result_bargraph_types = $stmt_bargraph_types -> get_result();
if($result_bargraph_types -> num_rows>0)
  while($row = $result_bargraph_types -> fetch_assoc())
  {
    $requesttype[]=  $row['request_type'];
  }
else   echo "0";
            


?>
<div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title ms-auto">Request Approvals Interval based on two Consecutive Approval Roles for <?php echo $getcomapny ?> Company</h5>
           
<div id="verticalBarChart" style="min-height: 800px;" class="echart"></div>


<script>
  document.addEventListener("DOMContentLoaded", () => {
    echarts.init(document.querySelector("#verticalBarChart")).setOption({
      title: {
        text: ''
      },
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },

      
      legend: {},
      grid: {
        left:'0%',
        right:'0%',
        bottom:'0%',
        containLabel: true
      },

      xAxis: {
        type: 'value',
        beginAtZero: true
      },
 
      yAxis: {

        type: 'category',
        data: <?php echo json_encode($requesttype) ;  ?>

      },
      series: [{
          name: 'Manager',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($manager); ?>,
          label: {
            show:true
      },

          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Director',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($director); ?>,
          label: {
         show:true
      },
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Stock',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($stock); ?>,
          label: {
           show:true,

      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
         {
          name: 'Property',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($property); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
        {
          name: 'Assign Time',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($AssignTime); ?>,
          label: {
             show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
        {
          name: 'Acceptance Time',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($AcceptanceTime); ?>,
          label: {
             show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
        {
          name: 'Performa Collect',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($PerformaCollect); ?>,
          label: {
             show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
        {
          name: 'Performa Confirm',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($PerformaConfirm); ?>,
          label: {
             show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }, 
         {
          name: 'Comparision',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($comparision); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Committe',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($committe); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Finalize',
          type: 'bar', 
          stack: 'total',
          data:<?php echo json_encode($finalize); ?> ,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Review ',
          type: 'bar',
          stack: 'total',
           data: <?php echo json_encode($Review); ?>,
        label: {
        show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Finance Approve',
          type: 'bar', 
          stack: 'total',
          data: <?php echo json_encode($FinanceApprove); ?>,
        label: {
        show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Prepare Cheque',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($PrepareCheque); ?>,
        label: {
        show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Sign Cheque',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($SignCheque); ?>,
        label: {
        show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Collection Time',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($CollectionTime); ?>,
        label: {
        show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        
        
        {
          name: 'Department Check',
          type: 'bar',
         stack: 'total',
          data: <?php echo json_encode($DepartmentCheck); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Recollection Time',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($RecollectionTime); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Store Confirm',
          type: 'bar',
          stack: 'total',
          data: <?php echo json_encode($StoreConfirm); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        },
        {
          name: 'Final Handover',
          type: 'bar',
         stack: 'total',
          data: <?php echo json_encode($FinalHandover); ?>,
          label: {
            show:true
      },
      
          emphasis: {
        focus: 'series'
      }
        }
     
      ]
    });
  });
</script>
<!-- End Vertical Bar Chart -->

</div>

<text class='text-center'><strong class='text-center'>Remark:</strong>The number describes avarage time taken in hour </text>
</div>
</div>
<?php

}else {
  echo 'no one';
}?>