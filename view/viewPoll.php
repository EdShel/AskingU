<?php

require_once "classes/Poll.php";
require_once "classes/Variant.php";
require_once "classes/ErrorHandler.php";
require_once "classes/VariantComponent.php";

if (!isset($model)) {
    ErrorHandler::AddError("View requires a model!");
}

?>


<div class="container up-indent">
    <div class="row mb-3">
        <h2 class="col-8">
            Просмотр опроса
        </h2>
        <div class="col-4 text-right">
            <?php

            if ($model->IsPollOfCurrentUser) {
                $formId = "delForm" . $model->Id;
                echo <<<HTML
<form action="/deletePoll" method="post" id="$formId">
    <input type="hidden" name="pollId" value="{$model->Id}">
    <a class="btn btn-secondary" href="#"
    onclick="document.getElementById('{$formId}').submit();">
        <span class="fas fa-trash-alt"></span>
        Удалить опрос</a>
</form>
HTML;
            }

            ?>
        </div>
    </div>

    <div class="row row-eq-height mb-4">
        <div class="col-lg-4 col-md-6 col-sm-12 col-12">
            <p class="poll-view-question">
                <?php echo $model->Question; ?>
            </p>
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12 col-12">
            <ul class="list-group list-group-flush">
                <?php

                foreach ($model->Variants as $i => $variant) {
                    echo '<li class="list-group-item">';
                    echo new VariantComponent($variant);
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div>
        <p>
            <a class="btn btn-info" data-toggle="collapse"
               href="#collapseStats" role="button"
               aria-expanded="true" aria-controls="collapseStats">
                <span class="fas fa-chevron-circle-down"></span>
                Скрыть/отобразить статистику
            </a>
        </p>
        <div class="collapse show" id="collapseStats">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
                    <canvas id="myChart" width="20" height="20"></canvas>
                    <?php
                    $dataLabels = array();
                    $dataValues = array();
                    foreach ($model->Variants as $i => $variant) {
                        $dataLabels[] = $variant->Value;
                        $dataValues[] = $variant->VotesCount;
                    }
                    $dataLabels = json_encode($dataLabels);
                    $dataValues = json_encode($dataValues);


                    ?>
                    <script>
                        var ctx = document.getElementById("myChart").getContext('2d');
                        var dataValues = <?php echo $dataValues ?>;
                        var dataLabels = <?php echo $dataLabels ?>;
                        var statsChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: dataLabels,
                                datasets: [{
                                    label: 'Ответившие пользователи',
                                    data: dataValues,
                                    backgroundColor: '#007bff',
                                }]
                            },
                            options: {
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        barPercentage: 0.5,
                                        ticks: {
                                            max: 8,
                                        }
                                    }],
                                    yAxes: [{
                                        ticks: {
                                            fixedStepSize: 1,
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                    </script>
                </div>
                <div class="col-lg-8 col-md-6 col-sm-12 col-12">
                    <form action="viewStat" method="post" id="filterForm">

                        <input type="hidden" name="pollId"
                               value="<?php echo $model->Id; ?>">

                        <!-- Gender filtering -->

                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="sGender">
                                    <span class="fas fa-venus-mars"></span>
                                    Фильтрация по полу
                                </label>

                            </div>
                            <div class="col-sm-8">
                                <select id="sGender" name="gender" class="form-control">
                                    <option value="any" selected>Любой</option>
                                    <option value="0">Мужской</option>
                                    <option value="1">Женский</option>
                                </select>
                            </div>
                        </div>


                        <!-- Age filtering -->

                        <?php

                        // Generate 100 years

                        $year = date("Y");
                        $yearHTML = "<option selected value='-1'>Не важно</option>";
                        for ($i = $year; $i >= $year - 100; --$i) {
                            $yearHTML .= "<option value='$i'>$i</option>";
                        }
                        ?>

                        <div class="row">
                            <div class="col-sm-4">
                                <label>
                                    <span class="fas fa-user-friends"></span>
                                    Фильтрация по году рождения
                                </label>
                            </div>
                            <div class="col-sm-1">
                                <label for="tYearFrom">От</label>
                            </div>
                            <div class="col-sm-3">
                                <select id="tYearFrom" class="form-control" name="yearFrom">
                                    <?php echo $yearHTML ?>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <label for="tYearTo">До</label>
                            </div>
                            <div class="col-sm-3">
                                <select id="tYearTo" class="form-control" name="yearTo">
                                    <?php echo $yearHTML ?>
                                </select>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                Максимально голосов
                            </div>
                            <div class="col-sm-8">
                                <span id="max"></span>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                Минимально голосов
                            </div>
                            <div class="col-sm-8">
                                <span id="min"></span>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                Медиана
                            </div>
                            <div class="col-sm-8">
                                <span id="med"></span>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                В среднем голосов
                            </div>
                            <div class="col-sm-8">
                                <span id="avg"></span>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                Среднеквадратическое отклонение
                            </div>
                            <div class="col-sm-8">
                                <span id="stdev"></span>
                            </div>
                        </div>
                        <hr>

                        <div class="text-right">
                            <input type="submit" class="btn btn-primary"
                                   value="Обновить статистику">
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function (e) {
        $('#filterForm').submit(UpdateStat);
        $('#filterForm').submit();
    });


</script>