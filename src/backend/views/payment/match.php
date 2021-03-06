<?php

use kartik\grid\GridView;
use kartik\file\FileInput;
use kartik\form\ActiveForm;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\PaymentSearch */
/* @var $gidStr String */
/* @var $platformStr String */
?>
<style>
    .select2-container .select2-selection--single .select2-selection__rendered {
        margin-top: 0;
    }
</style>
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col-md-12">
                <?= FileInput::widget(
                    [
                        'name' => 'file',
                        'options' => [
                            'multiple' => false,
                        ],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showCaption' => false,
                            'uploadUrl' => \yii\helpers\Url::to(['/payment/upload']),
                            'initialPreviewAsData' => true,
                            'maxFileCount' => 10,
                        ],
                    ]
                ); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <?php $form = \yii\widgets\ActiveForm::begin(
                [
                    'method' => 'get',
                    'action' => '/payment/match',
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
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'platform_id')->widget(
                        kartik\select2\Select2::className(),
                        [
                            'data' => (\common\models\Platform::platformDropDownData()),
                        ]
                    )->label('平台:') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'time')->widget(
                        \kartik\daterange\DateRangePicker::className(),
                        [
                            'convertFormat' => true,
                            'startAttribute' => 'from',
                            'endAttribute' => 'to',
                            'pluginOptions' => [
                                'locale' => ['format' => 'Y-m-d'],
                            ],
                        ]
                    )->label('日期:') ?>
                </div>
                <div class="col-md-1">
                    <?= \yii\helpers\Html::Button(
                        '比对',
                        ['class' => 'btn btn-success btn-flat', 'style' => 'margin-top: 25px;', 'id' => '_match']
                    ) ?>
                </div>
            </div>
            <?php \yii\widgets\ActiveForm::end() ?>
        </div>
    </div>
</div>


<?php $columns = [
    ['class' => '\kartik\grid\SerialColumn', 'pageSummary' => '汇总',],
    [
        'label' => '订单',
        'attribute' => 'order_id',
        'value' => function ($data) {

            return "\t".$data['order_id'] ?? '';
        },
//        'hAlign' => 'center',
        'format' => 'raw',
    ],
    [
        'label' => '平台',
        'value' => function ($data) {
            $platform = \common\models\Platform::findOne($data['platform_id']);

            return $platform->abbreviation ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '游戏',
        'value' => function ($data) {
            $game = \common\models\Game::findOne($data['game_id']);

            return $game->name ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '区服',
        'value' => function ($data) {
            $server = \common\models\Server::findOne($data['server_id']);

            return $server->server ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '用户',
        'value' => function ($data) {
            $user = \common\models\User::findOne($data['user_id']);

            return $user->uid ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '元宝',
        'value' => function ($data) {

            return $data['coins'] ?? 0;
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '金额',
        'value' => function ($data) {
            return $data['money'] ?? 0;
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '下单时间',
        'attribute' => 'time',
        'value' => function ($data) {
            return $data['time'] ?? 0;
        },
        'hAlign' => 'center',
    ],
]; ?>
<?php

$fullExport = \kartik\export\ExportMenu::widget(
    [
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'fontAwesome' => true,
        'target' => \kartik\export\ExportMenu::TARGET_BLANK,
        'pjaxContainerId' => 'payment-list-grid',
        'asDropdown' => true,
        'showColumnSelector' => false,
        'dropdownOptions' => [
            'label' => '导出数据',
            'class' => 'btn btn-default',
            'itemsBefore' => [
                '<li class="dropdown-header">导出全部数据</li>',
            ],
        ],
    ]
);
?>
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
            $fullExport,
        ],
        'id' => 'payment-list',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '订单详情',
            'type' => 'default',
            'after' => false,
        ],
    ]
); ?>
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
        original: '#paymentsearch-game_id',
        aim: '#payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
    $('#_match').on('click', function(){
        $.getJSON('/payment/match', $('#w2').serialize(), function(data){
             console.log(data)
        })
    })
EOL;
$this->registerJs($script);
?>

