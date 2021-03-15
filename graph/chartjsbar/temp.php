<?php


$current_user = wp_get_current_user();
$list_param_name = ['weight', 'waist', 'pressure_top', 'pressure_bottom'];
if (
    isset($_POST['current_weight']) ||
    isset($_POST['current_waist']) ||
    isset($_POST['current_pressure_top']) ||
    isset($_POST['current_pressure_bottom'])
)
{

    update_user_meta( $current_user->ID, 'last_weight', $_POST['current_weight']);

    foreach ($list_param_name as $value) {

        $list_param = getUserParam($current_user->ID, $value);

        if(!empty($_POST['current_' . $value])){

            $updated_list_param = updateList($list_param, $value);

            $is_save = update_user_meta( $current_user->ID, $value, serialize($updated_list_param));

            if ($is_save)
                $list_param = $updated_list_param;

        }

        $params[$value] = $list_param;
    }

}else{

    foreach ($list_param_name as $value) {
        $list_param = getUserParam($current_user->ID, $value);
        $params[$value] = $list_param;
    }

}
//echo "<pre>" . print_r($params, 1) . "</pre>";
//die();

function getUserParam($userId, $paramName){
    $weight_string = get_user_meta( $userId, $paramName, true );

    if (!$weight_string)
        return array();

    $list_weight = unserialize($weight_string);
    if (is_array($list_weight))
        return $list_weight;

    return 'error';

}

function addElementToList($list, $element){
    $list_length = count($list);

    if(!$list_length){
        $list[] = $element;
        return $list;
    }

    if($list[$list_length-1]['x'] == date('Y-m-d')){
        $list[$list_length-1] = $element;
        return $list;
    }

    $list[] = $element;
    return $list;
}

function updateList($list, $param_name){
    if ($list == 'error')
        return;

    $element  = ['x' => date('Y-m-d'), 'y' => $_POST['current_' . $param_name]];
    return addElementToList($list, $element);
}


?>

<?php get_header(); ?>

    <!-- проверка прав -->
<?php if (is_user_logged_in()): ?>

    <main class="cabinet">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">

                    <?php get_sidebar('cabinet'); ?>


                </div>

                <div class="col-xl-9">
                    <section class="user-profile">

                        <h1 class="page-title">Ваши параметры</h1>


                        <div class="row">
                            <div class="col-xl-12">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Вес</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Талия</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Давление</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                        <canvas id="myWeight" width="" height=""></canvas>
                                    </div>
                                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <canvas id="myWaist" width="" height=""></canvas>
                                    </div>
                                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                        <canvas id="myPressure" width="" height=""></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12" style="margin-top: 40px;">
                                <form method="POST">
                                    <div class="form-group row">
                                        <label for="inputWeight3" class="col-sm-2 col-form-label">Вес</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="inputWeight3" name="current_weight" placeholder="кг">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputWaist3" class="col-sm-2 col-form-label">Обхват талии</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="inputWaist3" name="current_waist" placeholder="см">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputPressureTop3" class="col-sm-2 col-form-label">Давление</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="inputPressureTop3" placeholder="Верхнее" name="current_pressure_top">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputPressureBottom3" class="col-sm-2 col-form-label">Давление</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="inputPressureBottom3" placeholder="Нижнее" name="current_pressure_bottom">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <button type="submit" class="btn btn-primary">Сохранить</button>
                                        </div>
                                    </div>
                                </form>
                            </div>



                            <script>
                                var listWeight = JSON.parse(JSON.stringify(<?php echo json_encode($params['weight'], JSON_UNESCAPED_UNICODE);?>))
                                var listWaist = JSON.parse(JSON.stringify(<?php echo json_encode($params['waist'], JSON_UNESCAPED_UNICODE);?>))
                                var listPressureTop = JSON.parse(JSON.stringify(<?php echo json_encode($params['pressure_top'], JSON_UNESCAPED_UNICODE);?>))
                                var listPressureBottom = JSON.parse(JSON.stringify(<?php echo json_encode($params['pressure_bottom'], JSON_UNESCAPED_UNICODE);?>))
                                //console.log(listWeight);

                                function newDate(days) {
                                    return moment().add(days, 'd').toDate();
                                }

                                function newDateString(days) {
                                    return moment().add(days, 'd').format();
                                }

                                var color = Chart.helpers.color;
                                var config = {
                                    type: 'line',
                                    data: {
                                        datasets: [{
                                            label: 'Вес',
                                            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                                            borderColor: window.chartColors.red,
                                            fill: false,
                                            data: listWeight,
                                        },]
                                    },
                                    options: {
                                        responsive: true,
                                        title: {
                                            display: true,
                                            text: 'Динамика изменения веса'
                                        },
                                        scales: {
                                            xAxes: [{
                                                type: 'time',
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Дата'
                                                },
                                                time: {
                                                    unit: "day",
                                                    displayFormats: {
                                                        day: 'YYYY MM DD'
                                                    }
                                                },
                                                ticks: {
                                                    major: {
                                                        fontStyle: 'bold',
                                                        fontColor: '#FF0000'
                                                    }
                                                }
                                            }],
                                            yAxes: [{
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Вес (кг)'
                                                }
                                            }]
                                        }
                                    }
                                }

                                var configWaist = {
                                    type: 'line',
                                    data: {
                                        datasets: [{
                                            label: 'Обхват талии',
                                            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                                            borderColor: window.chartColors.red,
                                            fill: false,
                                            data: listWaist,
                                        },]
                                    },
                                    options: {
                                        responsive: true,
                                        title: {
                                            display: true,
                                            text: 'Динамика изменения обхвата талии'
                                        },
                                        scales: {
                                            xAxes: [{
                                                type: 'time',
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Дата'
                                                },
                                                time: {
                                                    unit: "day",
                                                    displayFormats: {
                                                        day: 'YYYY MM DD'
                                                    }
                                                },
                                                ticks: {
                                                    major: {
                                                        fontStyle: 'bold',
                                                        fontColor: '#FF0000'
                                                    }
                                                }
                                            }],
                                            yAxes: [{
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Обхват талии (см)'
                                                }
                                            }]
                                        }
                                    }
                                }

                                var configPressure = {
                                    type: 'line',
                                    data: {
                                        datasets: [{
                                            label: 'Верхнее давление',
                                            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                                            borderColor: window.chartColors.red,
                                            fill: false,
                                            data: listPressureTop,
                                        },{
                                            label: 'Нижнее давление',
                                            backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
                                            borderColor: window.chartColors.blue,
                                            fill: false,
                                            data: listPressureBottom,
                                        },]
                                    },
                                    options: {
                                        responsive: true,
                                        title: {
                                            display: true,
                                            text: 'История давления'
                                        },
                                        scales: {
                                            xAxes: [{
                                                type: 'time',
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Дата'
                                                },
                                                time: {
                                                    unit: "day",
                                                    displayFormats: {
                                                        day: 'YYYY MM DD'
                                                    }
                                                },
                                                ticks: {
                                                    major: {
                                                        fontStyle: 'bold',
                                                        fontColor: '#FF0000'
                                                    }
                                                }
                                            }],
                                            yAxes: [{
                                                display: true,
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Давление'
                                                }
                                            }]
                                        }
                                    }
                                }


                                window.onload = function() {
                                    var ctx = document.getElementById('myWeight').getContext('2d');
                                    window.myLine1 = new Chart(ctx, config);
                                    var ctx2 = document.getElementById('myWaist').getContext('2d');
                                    window.myLine2 = new Chart(ctx2, configWaist);
                                    var ctx3 = document.getElementById('myPressure').getContext('2d');
                                    window.myLine3 = new Chart(ctx3, configPressure);
                                }





                            </script>


                        </div>

                    </section>





                </div>

            </div>
        </div>
    </main>

<?php else: ?>
    <main class="cabinet">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <p>У вас нет прав для доступа к этой странице.</p>
                </div>
            </div>
        </div>
    </main>
<?php endif ?>

<?php get_footer();