<?php

use kartik\grid\GridView;
use kartik\widgets\DateTimePicker;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\ServerPaymentSearch */
/* @var $platformStr string */
/* @var $serverStr string */
/* @var $from string */
/* @var $to string */
?>
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered {
            margin-top: 0;
        }
    </style>
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <?php $form = \yii\widgets\ActiveForm::begin(
                    [
                        'method' => 'get',
                        'action' => '/payment-analysis/index',
                    ]
                ); ?>

                <div class="col-md-12">
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'game_id')->widget(
                            kartik\select2\Select2::className(),
                            [
                                'data' => \common\models\Game::gameDropDownData(),
                            ]
                        )->label('游戏:') ?>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <?php if ($searchModel->platform_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->platform_id); ?>"
                                       id="selected_platform_id"/>
                            <?php endif; ?>
                            <?= $form->field($searchModel, 'platform_id')->widget(
                                \dosamigos\multiselect\MultiSelect::className(),
                                [
                                    'data' => \common\models\Platform::platformDropDownData(),
                                    "options" => ['multiple' => "multiple", "width" => '200px'],
                                    "clientOptions" =>
                                        [
                                            "includeSelectAllOption" => true,
                                            'enableFiltering' => true,
                                            'numberDisplayed' => 2,
                                            'selectAllText' => '全选',
                                            'filterPlaceholder' => '请选择...',
                                            'nonSelectedText' => '未选择',
                                            'buttonWidth' => '100px',
                                        ],
                                ]
                            )->label('平台:') ?>
                        </div>
                    </div>
                    <div class="col-md-1" id="s_l" style="display: none">
                        <div class="form-group">
                            <?php if ($searchModel->server_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->server_id); ?>"
                                       id="selected_server_id"/>
                            <?php endif; ?>
                            <?= $form->field($searchModel, 'server_id')->widget(
                                \dosamigos\multiselect\MultiSelect::className(),
                                [
                                    'data' => \common\models\Server::ServerDataDropData(
                                        $searchModel->game_id,
                                        $searchModel->platform_id
                                    ),
                                    "options" => ['multiple' => "multiple", "width" => '200px',],
                                    "clientOptions" =>
                                        [
                                            "includeSelectAllOption" => true,
                                            'enableFiltering' => true,
                                            'numberDisplayed' => 2,
                                            'selectAllText' => '全选',
                                            'filterPlaceholder' => '请选择...',
                                            'nonSelectedText' => '未选择',
                                            'buttonWidth' => '100px',
                                        ],
                                ]
                            )->label('区服:') ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'from')->widget(
                            DateTimePicker::className(),
                            [
                                'options' => ['placeholder' => '开始日期'],
                                'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
                                'convertFormat' => true,
                                'pluginOptions' => [
                                    'format' => 'yyyy-MM-dd HH:i',
                                    'todayHighlight' => true,
                                    'autoclose' => true,
                                ],
                            ]
                        )->label('开始') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'to')->widget(
                            DateTimePicker::className(),
                            [
                                'options' => ['placeholder' => '结束日期'],
                                'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
                                'convertFormat' => true,
                                'pluginOptions' => [
                                    'format' => 'yyyy-MM-dd HH:i',
                                    'todayHighlight' => true,
                                    'autoclose' => true,
                                ],
                            ]
                        )->label('结束') ?>
                    </div>
                    <div class="col-md-1">
                        <?= \yii\helpers\Html::submitButton(
                            '搜索',
                            ['class' => 'btn btn-success btn-flat', 'style' => 'margin-top: 25px;']
                        ) ?>
                    </div>
                </div>
                <?php \yii\widgets\ActiveForm::end() ?>
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
            <div id="per-day-server-bar-container" style="height: 800px"></div>
        </div>
    </div>


<?php $columns = [
    ['class' => '\kartik\grid\SerialColumn', 'pageSummary' => '汇总'],
    [
        'label' => '时间',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['date'];
        },
    ],
    [
        'label' => '新增用户',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['new_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '活跃用户',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['active_sum'] + $data['new_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '充值金额',
        'value' => function ($data) {
            return round($data['pay_money_sum'], 2);
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '充值人数',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['pay_man_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '付费渗透率(%)',
        'value' => function ($data) {
            if (($data['active_sum'] + $data['new_sum']) > 0) {
                return round($data['pay_man_sum'] / ($data['active_sum'] + $data['new_sum']) * 100, 2);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => 'ARPU',
        'value' => function ($data) {
            if ($data['pay_man_sum'] > 0) {
                return round($data['pay_money_sum'] / $data['pay_man_sum'], 2);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '新增充值人数',
        'value' => function ($data) {
            return $data['new_pay_man_sum'];
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'attribute' => '新增充值金额',
        'hAlign' => 'center',
        'value' => function ($data) {
            return round($data['new_pay_money_sum'], 2);
        },
        'pageSummary' => true,
    ],
    [
        'label' => '新进充值占比(%)',
        'value' => function ($data) {
            if ($data['pay_man_sum'] > 0) {
                return round($data['new_pay_money_sum'] / $data['pay_money_sum'] * 100, 2);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
]; ?>
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
$charts = <<<EOL
        var param = {
            api: '/api/payment-analysis-area-spline?',
            title: {
                text: '收入趋势',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-day-server-bar-container',
            param: {
                gid: '{$searchModel->game_id}',
                platform: '{$platformStr}',
                server:'{$serverStr}',
                from: '{$from}',
                to: '{$to}',
            }
        };
        var chart = new Hcharts(param);
        chart.showAreaSpline();
EOL;
$this->registerJs($charts);
?>
<?php
$this->registerJsFile(
    '/js/linkage_multi.js',
    [
        'depends' => [
            'backend\assets\MultiSelectFilterAsset',
        ],
    ]
);
$script = <<<EOL
    var Component = new IMultiSelect({
        original: '#paymentanalysissearch-game_id',
        aim: '#server-payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
$script = <<<EOL
    var Component = new IMultiSelect({
        original: '#paymentanalysissearch-platform_id',
        aim: '#paymentanalysissearch-server_id',
        append: '#s_l', 
        append_show_max_length: 2,
        selected_values_id: '#selected_server_id',
        url:'/api/get-server-by-platform',
        depend:'#paymentanalysissearch-game_id',
    });
    Component.start();
EOL;

$this->registerJs($script);
?>