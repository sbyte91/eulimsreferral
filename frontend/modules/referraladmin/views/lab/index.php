<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\referraladmin\LabSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Labs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lab-index">
<div class="panel panel-default col-xs-12">
        <div class="panel-heading"><i class="fa fa-adn"></i> </div>
        <div class="panel-body">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Lab', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'lab_id',
            'labname',
            'labcode',
            'active',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
        </div>
</div>
</div>