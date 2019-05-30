<?php

use yii\helpers\Html;
use kartik\detail\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\dialog\Dialog;
use yii\web\JsExpression;
use yii\widgets\ListView;
use kartik\tabs\TabsX;
/* @var $this yii\web\View */
/* @var $model common\models\referral\Referral */

$this->title = empty($model->referral_code) ? $model->referral_id : $model->referral_code;
$this->params['breadcrumbs'][] = ['label' => 'Referrals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile("/css/modcss/progress.css", [], 'css-search-bar');

$statusreceived="progress-todo";
$statusshipped="progress-todo";
$statusaccepted="progress-todo";
$statusongoing="progress-todo";
$statuscompleted="progress-todo";
$statusuploaded="progress-todo";

foreach($logs as $log){
    if($log->referralstatus_id == 1){
       $statusreceived ="progress-done";
    }
    if($log->referralstatus_id == 2){
       $statusshipped ="progress-done";
    }
    if($log->referralstatus_id == 3){
       $statusaccepted ="progress-done";
    }
    if($log->referralstatus_id == 4){
       $statusongoing ="progress-done";
    }
    
    if($log->referralstatus_id == 4){
       $statuscompleted ="progress-done";
    }
    if($log->referralstatus_id == 5){
       $statusuploaded ="progress-done";
    }
}

$haveStatus = "";

if(!isset(\Yii::$app->session['config-item'])){
   \Yii::$app->session['config-item']=1; //Laboratories 
}
switch(\Yii::$app->session['config-item']){
    case 1: //Laboratories
        $LogActive=true;
        $ResultActive=false;
        $TrackActive=false;
        break;
    case 2: // Technical Managers
        $LogActive=false;
        $ResultActive=true;
        $TrackActive=false;
        break;
    case 3: //Discount
        $LogActive=false;
        $ResultActive=false;
        $TrackActive=true;
        break;
}
$Session= Yii::$app->session;

if(empty($model->referral_code)){
    $labelpanel = '<i class="glyphicon glyphicon-book"></i> Referral Code ' . $model->referral_code;
} else {
    $btnPrint = "<a href='/reports/preview?url=/lab/request/print-request?id=".$model->referral_id."' class='btn-sm btn-default' style='color:#000000;margin-left:15px;'><i class='fa fa-print'></i> Print</a>";
    $labelpanel = '<i class="glyphicon glyphicon-book"></i> Referral Code ' . $model->referral_code .' '.$btnPrint;
}

$rstlId = Yii::$app->user->identity->profile->rstl_id;

?>
<div class="referral-view">
    <div class="image-loader" style="display: none;"></div>
    <div class="container table-responsive">
        <?php
            echo DetailView::widget([
            'model'=>$model,
            'responsive'=>true,
            'hover'=>true,
            'mode'=>DetailView::MODE_VIEW,
            'panel'=>[
                'heading'=>'<i class="glyphicon glyphicon-book"></i> Referral Code ' . $model->referral_code .' '.$btnPrint,
                'type'=>DetailView::TYPE_PRIMARY,
            ],
            'buttons1' => '',
            'attributes'=>[
                [
                    'group'=>true,
                    'label'=>'Referral Details ',
                    'rowOptions'=>['class'=>'info']
                ],
                [
                    'columns' => [
                        [
                            'label'=>'Referral Code',
                            'displayOnly'=>true,
                            'valueColOptions'=>['style'=>'width:30%'],
                            'value'=> $model->referral_code,
                        ],
                        [
                            'label'=>'Customer / Agency',
                            'format'=>'raw',
                            'value'=> $model->customer_id > 0 && !empty($model->customer->customer_name) ? $model->customer->customer_name : "",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label'=>'Referral Date / Time',
                            'format'=>'raw',
                            'value'=> ($model->referral_date_time != "0000-00-00 00:00:00") ? Yii::$app->formatter->asDate($model->referral_date_time, 'php:F j, Y h:i a') : "<i class='text-danger font-weight-bold h5'>Pending referral request</i>",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                        [
                            'label'=>'Address',
                            'format'=>'raw',
                            'value'=> $model->customer_id > 0 && !empty($model->customer->customer_name) ? $model->customer->address : "",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                    
                ],
                [
                    'columns' => [
                       [
                            'label'=>'Sample Received Date',
                            'format'=>'raw',
                            'value'=> !empty($model->sample_received_date) ? Yii::$app->formatter->asDate($model->sample_received_date, 'php:F j, Y') : "<i class='text-danger font-weight-bold h5'>No sample received date</i>",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                        [
                            'label'=>'Tel no.',
                            'format'=>'raw',
                            'value'=> $model->customer_id > 0 && !empty($model->customer->customer_name) ? $model->customer->tel : "",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label'=>'Estimated Due Date',
                            'format'=>'raw',
                            'value'=> ($model->report_due != "0000-00-00 00:00:00") ? Yii::$app->formatter->asDate($model->report_due, 'php:F j, Y') : "<i class='text-danger font-weight-bold h5'>Pending referral request</i>",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                        [
                            'label'=>'Fax no.',
                            'format'=>'raw',
                            'value'=> $model->customer_id > 0 && !empty($model->customer->customer_name) ? $model->customer->fax : "",
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label'=>'Referred by',
                            'format'=>'raw',
                            'value'=> !empty($model->agencyreceiving) ? $model->agencyreceiving->name : null,
                            'displayOnly'=>true
                        ],
                        [
                            'label'=>'Referred to',
                            'format'=>'raw',
                            'value'=> !empty($model->agencytesting) ? $model->agencytesting->name : null,
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                ],
                [
                    'group'=>true,
                    'label'=>'Payment Details',
                    'rowOptions'=>['class'=>'info']
                ],
                [
                    'columns' => [
                        [
                            'label'=>'Deposite Slip',
                            'value'=>function($data) use ($depositslip,$model,$rstlId){
                                $link = '';
                                $link .= !empty($model->referral_code) && $model->receiving_agency_id  == $rstlId ? Html::button('<span class="glyphicon glyphicon-upload"></span> Upload', ['value'=>Url::to(['/referrals/attachment/upload_deposit','referral_id'=>$model->referral_id]), 'onclick'=>'upload(this.value,this.title)', 'class' => 'btn btn-primary btn-xs','title' => 'Upload Deposit Slip'])."<br>" : '';
                                if($depositslip > 0){
                                    foreach ($depositslip as $deposit) {
                                        $link .= Html::a('<span class="glyphicon glyphicon-save-file"></span> '.$deposit['filename'],'/referrals/attachment/download?referral_id='.$model->referral_id.'&file='.$deposit['attachment_id'], ['style'=>'font-size:12px;color:#000077;font-weight:bold;','title'=>'Download Deposit Slip','target'=>'_self'])."<br>";
                                    }
                                }
                                return $link;
                            },
                            'format'=>'raw',
                            'displayOnly'=>true,
                            'valueColOptions'=>['style'=>'width:30%;vertical-align: top;'],
                            'labelColOptions' => ['style' => 'width: 20%; text-align: right; vertical-align: top;'],
                        ],
                        [
                            'label'=>'Official Receipt',
                            'value'=>function($data) use ($officialreceipt,$model,$rstlId){
                                $link = '';
                                $link .= !empty($model->referral_code) && $model->testing_agency_id == $rstlId ? Html::button('<span class="glyphicon glyphicon-upload"></span> Upload', ['value'=>Url::to(['/referrals/attachment/upload_or','referral_id'=>$model->referral_id]), 'onclick'=>'upload(this.value,this.title)', 'class' => 'btn btn-primary btn-xs','title' => 'Upload Official Receipt'])."<br>" : '';
                                if($officialreceipt > 0){
                                    foreach ($officialreceipt as $or) {
                                        $link .= Html::a('<span class="glyphicon glyphicon-save-file"></span> '.$or['filename'],'/referrals/attachment/download?referral_id='.$model->referral_id.'&file='.$or['attachment_id'], ['style'=>'font-size:12px;color:#000077;font-weight:bold;','title'=>'Download Official Receipt','target'=>'_self'])."<br>";
                                    }
                                }
                                return $link;
                            },
                            'format'=>'raw',
                            'displayOnly'=>true,
                            'valueColOptions'=>['style'=>'width:30%;vertical-align: top;'],
                            'labelColOptions' => ['style' => 'width: 20%; text-align: right; vertical-align: top;'],
                        ],
                    ],
                ],              
                [
                    'group'=>true,
                    'label'=>'Transaction Details',
                    'rowOptions'=>['class'=>'info']
                ],
                [
                    'columns' => [
                        [ 
                            'label'=>'Recieved By',
                            'attribute' => 'cro_receiving',
                            'format'=>'raw',
                            //'value'=>$model->cro_receiving,
                            'displayOnly'=>true,
                            'valueColOptions'=>['style'=>'width:30%']
                        ],
                        [
                            'label'=>'Conforme',
                            'attribute' => 'conforme',
                            //'value'=> $model->conforme,
                            'format'=>'raw',
                            'valueColOptions'=>['style'=>'width:30%'], 
                            'displayOnly'=>true
                        ],
                    ],
                ],
            ],

        ]);
        ?>
    </div>
    <div class="container">
        <div class="table-responsive">
        <?php
            $gridColumns = [
                [
                    'attribute'=>'sample_code',
                    'enableSorting' => false,
                    'contentOptions' => [
                        'style'=>'max-width:70px; overflow: auto; white-space: normal; word-wrap: break-word;'
                    ],
                ],
                [
                    'attribute'=>'sample_name',
                    'enableSorting' => false,
                ],
                [
                    'attribute'=>'description',
                    'format' => 'raw',
                    'enableSorting' => false,
                    'value' => function($data) use ($model){
                        return ($model->lab_id == 2) ? "Sampling Date: <span style='color:#000077;'><b>".date("Y-m-d h:i A",strtotime($data->sampling_date))."</b></span>,&nbsp;".$data->description : $data->description;
                    },
                   'contentOptions' => [
                        'style'=>'max-width:180px; overflow: auto; white-space: normal; word-wrap: break-word;'
                    ],
                ],
            ];

            echo GridView::widget([
                'id' => 'sample-grid',
                'dataProvider'=> $sampleDataProvider,
                'pjax'=>true,
                'pjaxSettings' => [
                    'options' => [
                        'enablePushState' => false,
                    ]
                ],
                'responsive'=>true,
                'striped'=>true,
                'hover'=>true,
                'panel' => [
                    'heading'=>'<h3 class="panel-title">Samples</h3>',
                    'type'=>'primary',
                    'before'=>null,
                    'after'=>false,
                ],
                'columns' => $gridColumns,
                'toolbar' => [
                    'content'=> Html::a('<i class="glyphicon glyphicon-repeat"></i> Refresh Grid', [Url::to(['referral/view','id'=>$model->referral_id])], [
                                'class' => 'btn btn-default', 
                                'title' => 'Refresh Grid'
                            ]),
                ],
            ]);
        ?>
        </div>
    </div>
    <div class="container">
        <?php
            $analysisgridColumns = [
                [
                    'attribute'=>'sample.sample_name',
                    'header'=>'Sample',
                    'format' => 'raw',
                    'enableSorting' => false,
                    'value' => function($data) {
                        return !empty($data->sample) ? $data->sample->sample_name : null;
                    },
                    'contentOptions' => ['style' => 'width:10%; white-space: normal;'],
                   
                ],
                [
                    'attribute'=>'sample.sample_code',
                    'header'=>'Sample Code',
                    'value' => function($data) {
                        return !empty($data->sample) ? $data->sample->sample_code : null;
                    },
                    'format' => 'raw',
                    'enableSorting' => false,
                    'contentOptions' => ['style' => 'width:10%; white-space: normal;'],
                ],
                [
                    'attribute'=>'testname.test_name',
                    'format' => 'raw',
                    'header'=>'Test/ Calibration Requested',
                    'contentOptions' => ['style' => 'width: 15%;word-wrap: break-word;white-space:pre-line;'],
                    'enableSorting' => false,
                ],
                [
                    'attribute'=>'methodreference.method',
                    'format' => 'raw',
                    'header'=>'Test Method',
                    'enableSorting' => false,  
                    'contentOptions' => ['style' => 'width: 50%;word-wrap: break-word;white-space:pre-line;'],
                    'pageSummary' => '<span style="float:right";>SUBTOTAL<BR>DISCOUNT<BR><B>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TOTAL</B></span>',             
                ],
                [
                    'attribute'=>'analysis_fee',
                    'header'=>'Unit Price',
                    'enableSorting' => false,
                    'hAlign'=>'right',
                    'format' => 'raw',
                    'value'=>function($data){
                        return number_format($data['analysis_fee'],2);
                    },
                    'contentOptions' => [
                        'style'=>'max-width:80px; overflow: auto; white-space: normal; word-wrap: break-word;'
                    ],
                    'hAlign' => 'right', 
                    'vAlign' => 'left',
                    'width' => '7%',
                    'format' => 'raw',
                    'pageSummary'=> function () use ($subtotal,$discounted,$total,$countSample) {
                        if($countSample > 0){
                            return  '<div id="subtotal">₱'.number_format($subtotal, 2).'</div><div id="discount">₱'.number_format($discounted, 2).'</div><div id="total"><b>₱'.number_format($total, 2).'</b></div>';
                        } else {
                            return '';
                        }
                    },
                ],
            ];
            echo GridView::widget([
                'id' => 'analysis-grid',
                'responsive'=>true,
                'dataProvider'=> $analysisdataprovider,
                'pjax'=>true,
                'pjaxSettings' => [
                    'options' => [
                        'enablePushState' => false,
                    ]
                ],
                'responsive'=>true,
                'striped'=>true,
                'hover'=>true,
                'showPageSummary' => true,
                'hover'=>true,
                
                'panel' => [
                    'heading'=>'<h3 class="panel-title">Analysis</h3>',
                    'type'=>'primary',
                    'before'=> null,
                    'after'=> false,
                    //'footer'=>$actionButtonConfirm.$actionButtonSaveLocal,
                    'footer'=>null,
                ],
                'columns' => $analysisgridColumns,
                'toolbar' => [
                    'content'=> Html::a('<i class="glyphicon glyphicon-repeat"></i> Refresh Grid', [Url::to(['referral/view','id'=>$model->referral_id])], [
                                'class' => 'btn btn-default', 
                                'title' => 'Refresh Grid'
                            ]),
                ],
            ]);
        ?>
    </div>
    <div class="container" <?php echo $haveStatus; ?>>
        <ul class="progress-track">
                <li class="<?php echo $statusreceived; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span class="fa fa-dropbox fa-fw to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Received</span>
                </li>
                <li class="<?php echo $statusshipped; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span class="fa fa-truck fa-fw fa-flip-horizontal to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Shipped</span>
                </li>
                <li class="<?php echo $statusaccepted; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span style="margin-left:2px;" class="fa fa-cube fa-fw fa-lg to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Accepted</span>
                </li>
                <li class="<?php echo $statusongoing; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span class="fa fa-flask fa-fw to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Ongoing</span>
                </li>
                <li class="<?php echo $statuscompleted; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span class="fa fa-check fa-fw to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Completed</span>
                </li>
                <li class="<?php echo $statusuploaded; ?> progress-tooltip">
                        <div class="progress-icon-wrap">
                                <span class="fa fa-upload fa-fw to-fit-icon" aria-hidden="true"></span>
                        </div>
                        <span class="progress-tooltiptext">Uploaded</span>
                </li>
        </ul>
    </div>
    
      <div class="container">
         <div class="panel panel-primary">
        
        <div class="panel-body">
        <?php  
         $gridColumn="<div class='row'><div class='col-md-12'>". GridView::widget([
           'dataProvider' => $notificationDataProvider,
           // 'filterModel' => $searchModel,
            'id'=>'LaboratoryGrid',
            'tableOptions'=>['class'=>'table table-hover table-stripe table-hand'],
            'pjax'=>true,
            'pjaxSettings' => [
                    'options' => [
                        'enablePushState' => false,
                    ],
            ],
            'toolbar'=>[],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => '<i class="fa fa-columns"></i> List',
             ],
            'columns' => [
                [
                    'attribute' => '',
                    'label' => 'Date and Time',
                    'value' => function($model) {
                        return date("F j, Y h:i:s A", strtotime($model->notification_date));
                    }
                ],
                [
                    'attribute' => '',
                    'label' => 'Details',
                    'value'=>function($model){
                        switch($model->notification_type_id){
                            case 1:
                                return 'Notification sent to '.$model->recipient_id;
                            case 2:
                                return $model->sender_id.' confirmed the notification for Referral.';
                            case 3:
                                return 'Referral sent to '.$model->recipient_id;
                            default:

                        }
                    }
                ],
            ],
        ])."</div></div>";
         $gridColumn1="abcdefh123456";
         //$display=true;
         $gridColumn2=DetailView::widget([
                'model'=>$model,
                'responsive'=>true, 
             
                'hover'=>true,
                'mode'=>DetailView::MODE_VIEW,
                'panel'=>[
                    'type'=>DetailView::TYPE_PRIMARY,
                ],
                'buttons1' => '',
                'attributes' => [
                    [
                        'group'=>true,
                        'label'=>Html::button('<span class="glyphicon glyphicon-plus"></span> Add Referral Track', ['value'=>'/referrals/referral/addreceivedtrack', 'class' => 'btn btn-success','title' => Yii::t('app', "Add Referral Track"),'id'=>'btnreceivedtrack','onclick'=>'addreceivedtrack(this.value,this.title)']),
                        'rowOptions'=>['class'=>'info']
                    ],
                    [
                        'columns' => [
                            [
                                'label'=>'Transaction Number',
                                'format'=>'raw',
                                'value'=>'abcdsfsdf',//$model->transactionnum,
                                'valueColOptions'=>['style'=>'width:30%'], 
                                'displayOnly'=>true
                            ],
                            [
                                'label'=>'Customer Name',
                                'format'=>'raw',
                                'value'=>'131431412',//$model->customer ? $model->customer->customer_name : "",
                                'valueColOptions'=>['style'=>'width:30%'], 
                                'displayOnly'=>true
                            ],
                        ],

                    ], 
                
                ],
            ]);
                echo TabsX::widget([
                    'position' => TabsX::POS_ABOVE,
                    'align' => TabsX::ALIGN_LEFT,
                    'encodeLabels' => false,
                    'id' => 'tab_referral',
                    'items' => [
                        [
                            'label' => '<i class="fa fa-columns"></i> Logs',
                            'content' => $gridColumn,//$LogContent,
                            'active' => $LogActive,
                            'options' => ['id' => 'log'],
                           // 'visible' => Yii::$app->user->can('access-terminal-configurations')
                        ],
                        [
                            'label' => '<i class="fa fa-users"></i> Results',
                            'content' => $gridColumn1,//$ResultContent,
                            'active' => $ResultActive,
                            'options' => ['id' => 'result'],
                           // 'visible' => Yii::$app->user->can('access-terminal-configurations')
                        ],
                        [
                            'label' => '<i class="fa-level-down"></i> Referral Track',
                            'content' =>$gridColumn2,//$TrackContent ,
                            'active' => $TrackActive,
                            'options' => ['id' => 'referral_track'],
                           // 'visible' => Yii::$app->user->can('access-terminal-configurations')
                        ]
                    ],
                ]);
        ?>
        </div>
        </div>      
    </div>
</div>

</div>

<script type="text/javascript">
    function addreceivedtrack(url,title){
       LoadModal(title,url,'true','600px');
   }

   //upload slip
    function upload(url,title){
        $('.modal-title').html(title);
        $('#modal').modal('show')
            .find('#modalContent')
            .load(url);
    }
</script>   
   