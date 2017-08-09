<?php

use kartik\grid\GridView;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\DashBoardSearch*/
/* @var $threeDayDataProvider \backend\models\search\DashBoardSearch*/
/* @var $monthDataProvider \backend\models\search\DashBoardSearch*/
?>
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered{
            margin-top: 0;
        }
    </style>
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <?php $form = \yii\widgets\ActiveForm::begin(
                    [
                        'method' => 'get',
                        'action' => '/payment/platform',
                    ]
                ); ?>

                <div class="col-md-12">
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'gid')->widget(kartik\select2\Select2::className(), [
                            'data' => \common\models\Game::gameDropDownData(),
                        ])?>
                    </div>
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'platform')->widget(\dosamigos\multiselect\MultiSelect::className(),
                            [
                                "options" => ['multiple' => "multiple"],
                                'data' => \common\models\Platform::platformDropDownData(),
                                "clientOptions" =>
                                    [
                                        'enableFiltering' => true,
                                        "selectAllText" => '全选',
                                        "includeSelectAllOption" => true,
                                        'numberDisplayed' => false,
                                        'maxHeight' => 0,
                                        'nonSelectedText' => '请选择平台',
                                    ],
                            ]) ?>
                    </div>
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'server_id')->widget(\dosamigos\multiselect\MultiSelect::className(),
                            [
                                "options" => ['multiple' => "multiple"],
                                'data' => \common\models\Platform::platformDropDownData(),
                                "clientOptions" =>
                                    [
                                        'enableFiltering' => true,
                                        "selectAllText" => '全选',
                                        "includeSelectAllOption" => true,
                                        'numberDisplayed' => false,
                                        'maxHeight' => 0,
                                        'nonSelectedText' => '请选择平台',
                                    ],
                            ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'time')->widget(\kartik\daterange\DateRangePicker::className(),[
                            'convertFormat'=>true,
                            'startAttribute' => 'from',
                            'endAttribute' => 'go',
                            'pluginOptions'=>[
                                'locale'=>['format' => 'Y-m-d'],
                            ]
                        ])->label('日期') ?>
                    </div>
                    <div class="col-md-1">
                        <?= \yii\helpers\Html::submitButton('搜索', ['class' => 'btn btn-success btn-flat', 'style' => 'margin-top: 25px;'])?>
                    </div>
                </div>
                <?php \yii\widgets\ActiveForm::end()?>
            </div>
        </div>
    </div>
    <div class="box box-default">
        <!--折线图-->
        <div class="box-header with-border">
            <h3 class="box-title">图表</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="per-hour-money-container"></div>
        </div>
    </div>

    <div class="box box-default">
        <!--折线图-->
        <div class="box-header with-border">
            <h3 class="box-title">图表</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="per-hour-man-container"></div>
        </div>
    </div>

<?php $columns = [
    ['class' => '\kartik\grid\SerialColumn'],
    [
        'label' => '平台',
        'value' => function($data){
            $game = \common\models\Platform::findOne($data['platform_id']);
            return $game->name ?? '';
        },
        'hAlign' => 'center',
        'pageSummary' => '汇总',
    ],
    [
        'label' => '新增用户',
        'hAlign' => 'center',
        'value' => function($data){
            return $data['new_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '活跃用户',
        'hAlign' => 'center',
        'value' => function($data){
            return $data['active_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '充值金额',
        'value' => function($data){
            return Yii::$app->formatter->asDecimal($data['pay_money_sum'], 2);
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '充值人数',
        'hAlign' => 'center',
        'value' => function($data){
            return $data['pay_man_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '付费渗透率(%)',
        'value' => function($data){
            if($data['active_sum'] > 0){
                return Yii::$app->formatter->asDecimal($data['pay_man_sum']/$data['active_sum'] * 100);
            }else{
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => 'ARPU(%)',
        'value' => function($data){
            if($data['pay_man_sum'] > 0){
                return Yii::$app->formatter->asDecimal($data['pay_money_sum']/$data['pay_man_sum'] * 100);
            }else{
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '新增充值人数',
        'value' => function($data){
            return $data['new_pay_man_sum'];
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'attribute' => '新增充值金额',
        'hAlign' => 'center',
        'value' => function($data){
            return Yii::$app->formatter->asDecimal($data['new_pay_money_sum'], 2);
        },
        'pageSummary' => true,
    ],
    [
        'label' => '新进充值占比(%)',
        'value' => function($data){
            if($data['pay_man_sum'] > 0){
                return Yii::$app->formatter->asDecimal($data['new_pay_money_sum']/$data['pay_money_sum']*100);
            }else{
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
];?>
<?= GridView::widget(
    [
        'autoXlFormat' => true,
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary default'],
        'export' => [
            'fontAwesome' => true,
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_BLANK,
        ],
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'toolbar' => [
            $columns,
        ],
        'id' => 'payment-game',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '游戏收入概要',
            'type' => 'default',
            'after' => false,
            'before' => false,
        ],
    ]
); ?>
<?php
//每时/充值走势图
$pay_charts = <<<EOL
        var param = {
            api: '/api/platform-payment-pie?',
            title: {
                text: '平台收入占比',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-hour-money-container',
            xAxis: 'dateTimeLabelFormats',
            param: {
                gid: '{$gidStr}',
                platform: '{$platformStr}',
                from: '{$searchModel->from}',
                to: '{$searchModel->to}',
            }
        };
        var chart = new Hcharts(param);
        chart.showPie();
EOL;
$this->registerJs($pay_charts);
//每时/充值人数走势图
$charts = <<<EOL
        var param = {
            api: '/api/platform-payment-spline?',
            title: {
                text: '平台收入趋势',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-hour-man-container',
            xAxis: 'dateTimeLabelFormats',
            param: {
                gid: '{$gidStr}',
                platform: '{$platformStr}',
                from: '{$searchModel->from}',
                to: '{$searchModel->to}',
            }
        };
        var chart = new Hcharts(param);
        chart.showSpline();
EOL;
$this->registerJs($charts);
?>