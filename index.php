<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>-->
<script src="js/chart.min.js"></script>

<!--<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.1"></script>-->
<script src="js/utils.js"></script>
<!--<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>-->
<!--<script src="js/chartjs-plugin-zoom.min.js"></script>-->

<?php
require 'db_connect.php';



$tokenSymbol = 'BREW';
$toAddress = '0x1fd9af4999de0d61c2a6cbd3d4892b675a082999';

$brewByTimeSold = $db->query('SELECT SUM(value) as value, timestamp, tokenDecimal FROM brew_busd WHERE tokenSymbol = ? AND toAddress = ? GROUP BY year(timeStamp), month(timeStamp), day(timeStamp), hour(timeStamp) ORDER BY timestamp ASC', $tokenSymbol, $toAddress);

$brewByTimeBought  = $db->query('SELECT SUM(value) as value, timestamp, tokenDecimal FROM brew_busd WHERE tokenSymbol = ? AND fromAddress = ? GROUP BY year(timeStamp), month(timeStamp), day(timeStamp), hour(timeStamp) ORDER BY timestamp ASC', $tokenSymbol, $toAddress);

$listSold = dbResultToArray($brewByTimeSold, 'sold');
$listBought = dbResultToArray($brewByTimeBought, 'bought');

echo '<pre>' . print_r($brewByTimeSold->getRowCount(), 1) . '</pre>';
echo '<pre>' . print_r($listSold['time'], 1) . '</pre>';
//echo '<pre>' . print_r(, 1) . '/<pre>';

//echo '<pre>' . print_r(dbTestToArray($brewByTimeSold, 'sold'), 1) . '</pre>';
//echo '<pre>' . print_r(dbTestToArray($brewByTimeBought, 'bought'), 1) . '</pre>';

$listDelta = array();
for ($i=0; $i<count($listSold['sold']); $i++){
    $listDelta[] = $listBought['bought'][$i] - $listSold['sold'][$i];
}
echo '<pre>' . print_r($listDelta, 1) . '/<pre>';
//die();



function dbTestToArray($result, $prefix){
    $list = array();
    foreach ($result as $row) {
        $list[] = [
            'time_'.$prefix => $row->timestamp->format("Y-m-d H:00"),
            $prefix => $row->value,//convertValue($row->value, $row->tokenDecimal),
        ];
    }
    return $list;
}


function dbResultToArray($result, $prefix){
    $list = array();
    foreach ($result as $row) {
        $list['time'][] = $row->timestamp->format("Y-m-d H:00");
        $list[$prefix][] = convertValue($row->value, $row->tokenDecimal);
    }
    return $list;
}


function convertValue($value, $decimal){
    $divisor = pow(10, $decimal);
    return $value/$divisor;
}

//echo '<pre>' . print_r($result, 1) . '/<pre>';
?>
<div style="width: 75%">
    <canvas id="canvas"></canvas>
</div>

<div style="width: 75%">
    <canvas id="canvas2"></canvas>
</div>

<script>
    var listTime = JSON.parse(JSON.stringify(<?php echo json_encode($listSold['time'], JSON_UNESCAPED_UNICODE);?>))
    var listSold = JSON.parse(JSON.stringify(<?php echo json_encode($listSold['sold'], JSON_UNESCAPED_UNICODE);?>))
    var listBought = JSON.parse(JSON.stringify(<?php echo json_encode($listBought['bought'], JSON_UNESCAPED_UNICODE);?>))
    var barChartData = {
        labels: listTime,
        datasets: [{
            label: 'Dataset 1',
            backgroundColor: window.chartColors.red,
            data: listSold,
        }, {
            label: 'Dataset 2',
            backgroundColor: window.chartColors.green,
            data: listBought,
        }]

    };

    var listDelta = JSON.parse(JSON.stringify(<?php echo json_encode($listDelta, JSON_UNESCAPED_UNICODE);?>))
    var barChartData2 = {
        labels: listTime,
        datasets: [{
            label: 'Dataset 1',
            backgroundColor: window.chartColors.red,
            data: listDelta,
        },]

    };


    window.onload = function() {
        var ctx = document.getElementById('canvas').getContext('2d');
        window.myBar = new Chart(ctx, {
            type: 'bar',
            data: barChartData,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Chart.js Bar Chart - Stacked'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        /*ticks: {
                            beginAtZero:true
                        },*/
                        stacked: true,

                    }
                }
            }
        });


        var ctx2 = document.getElementById('canvas2').getContext('2d');
        window.myBar2 = new Chart(ctx2, {
            type: 'bar',
            data: barChartData2,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Chart.js Bar Chart - Stacked'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        /*ticks: {
                            beginAtZero:true
                        },*/
                        stacked: true,

                    }
                }
            }
        });
    };



</script>


