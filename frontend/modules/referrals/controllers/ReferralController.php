<?php

namespace frontend\modules\referrals\controllers;

use Yii;
use common\models\referral\Referral;
use common\models\referral\ReferralSearch;
use common\models\referral\Sample;
use common\models\referral\Analysis;
use common\models\referral\Notification;
use common\models\referral\Customer;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\ReferralComponent;
use common\components\ReferralFunctions;
use linslin\yii2\curl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use common\models\referral\Statuslogs;
use common\models\referral\Referraltrackreceiving;
use common\models\referral\Referraltracktesting;
/**
 * ReferralController implements the CRUD actions for Referral model.
 */
class ReferralController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Referral models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReferralSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Referral model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $rstlId = (int) Yii::$app->user->identity->profile->rstl_id;

        if($rstlId > 0)
        {
            $function = new ReferralFunctions();
            $refcomponent = new ReferralComponent();
            
            $checknotified = $function->checkNotified($id,$rstlId);
            $checkOwner = $function->checkOwner($id,$rstlId);

            if($checknotified > 0 || $checkOwner > 0)
            {
                $model = $this->findModel($id);
                $samples = $model->samples;
                $analyses = Analysis::find()->joinWith('sample',false)->where('referral_id =:referralId',[':referralId'=>$id])->all();
                $notification= Notification::find()->where('referral_id =:referralId',[':referralId'=>$id])->orderBy(['notification_type_id' => SORT_ASC])->all();
                $statuslogs= Statuslogs::find()->where('referral_id =:referralId',[':referralId'=>$id])->all();
                
                //$customer = Customer::findOne($model->customer_id);

                //set third parameter to 1 for attachment type deposit slip
                $deposit = json_decode($refcomponent->getAttachment($id,Yii::$app->user->identity->profile->rstl_id,1),true);
                //set third parameter to 2 for attachment type or
                $or = json_decode($refcomponent->getAttachment($id,Yii::$app->user->identity->profile->rstl_id,2),true);

                $sampleDataProvider = new ArrayDataProvider([
                    'allModels' => $samples,
                    'pagination'=> [
                        'pageSize' => 10,
                    ],
                ]);

                $analysisDataprovider = new ArrayDataProvider([
                    'allModels' => $analyses,
                    'pagination'=>false,
                    /*'pagination'=> [
                        'pageSize' => 10,
                    ],*/

                ]);
                
                $notificationDataProvider = new ArrayDataProvider([
                    'allModels' => $notification,
                    'pagination'=> [
                        'pageSize' => 10,
                    ],
                ]);
               
                $query = new Query;
                $subtotal = $query->from('tbl_analysis')
                   ->join('INNER JOIN', 'tbl_sample', 'tbl_analysis.sample_id = tbl_sample.sample_id')
                   ->where('referral_id =:referralId',[':referralId'=>$id])
                   ->sum('analysis_fee');

                /*$subtotal = Analysis::find()->joinWith('sample',false)
                    ->where('referral_id =:referralId',[':referralId'=>$id])
                    ->sum('analysis_fee');*/

                $rate = $model->discount_rate;
                $discounted = $subtotal * ($rate/100);
                $total = $subtotal - $discounted;
                
                $countreceiving=Referraltrackreceiving::find()->where('referral_id =:referralId',[':referralId'=>$id])->count();
                $counttesting=Referraltracktesting::find()->where('referral_id =:referralId',[':referralId'=>$id])->count();
               
                return $this->render('view', [
                    'model' => $model,
                    //'request' => $request,
                    //'customer' => $customer,
                    'sampleDataProvider' => $sampleDataProvider,
                    'analysisdataprovider'=> $analysisDataprovider,
                    'subtotal' => $subtotal,
                    'discounted' => $discounted,
                    'total' => $total,
                    'countSample' => count($samples),
                    //'notification' => $noticeDetails,
                    'depositslip' => $deposit,
                    'officialreceipt' => $or,
                    'notificationDataProvider' => $notificationDataProvider,
                    'logs'=>$statuslogs,
                    'modelRefTracktesting'=>$this->findModeltestingtrack($id),
                    'modelRefTrackreceiving'=>$this->findModelreceivedtrack($id),
                    'counttesting'=> $counttesting,
                    'countreceiving'=>$countreceiving
                ]);
            } else {
                Yii::$app->session->setFlash('error', "Your agency doesn't appear notified!");
                return $this->redirect(['/referrals/notification']);
            }
        } else {
            Yii::$app->session->setFlash('error', "Invalid request!");
            return $this->redirect(['/referrals/notification']);
        }

        /*return $this->render('view', [
            'model' => $this->findModel($id),
        ]);*/
    }

    //view notification
    public function actionViewnotice($id)
    {
        $rstlId = (int) Yii::$app->user->identity->profile->rstl_id;
        $noticeId = (int) Yii::$app->request->get('notice_id');

        if($rstlId > 0 && $noticeId > 0)
        {
            $function = new ReferralFunctions();
            $refcomponent = new ReferralComponent();

            $checknotified = $function->checkNotified($id,$rstlId);
            $checkOwner = $function->checkOwner($id,$rstlId);

            $noticeDetails = Notification::find()
                ->where('notification_id =:notificationId AND recipient_id =:recipientId', [':notificationId'=>$noticeId,':recipientId'=>$rstlId])
                ->one();

            if(($checknotified > 0 && count($noticeDetails) > 0) || $checkOwner > 0)
            {
                $model = $this->findModel($id);

                $samples = $model->samples;
                $analyses = Analysis::find()->joinWith('sample',false)->where('referral_id =:referralId',[':referralId'=>$id])->all();

                //set third parameter to 1 for attachment type deposit slip
                $deposit = json_decode($refcomponent->getAttachment($id,Yii::$app->user->identity->profile->rstl_id,1),true);
                //set third parameter to 2 for attachment type or
                $or = json_decode($refcomponent->getAttachment($id,Yii::$app->user->identity->profile->rstl_id,2),true);

                $sampleDataProvider = new ArrayDataProvider([
                    'allModels' => $samples,
                    'pagination'=> [
                        'pageSize' => 10,
                    ],
                ]);

                $analysisDataprovider = new ArrayDataProvider([
                    'allModels' => $analyses,
                    //'pagination'=>false,
                    'pagination'=> [
                        'pageSize' => 10,
                    ],

                ]);

                $query = new Query;
                $subtotal = $query->from('tbl_analysis')
                   ->join('INNER JOIN', 'tbl_sample', 'tbl_analysis.sample_id = tbl_sample.sample_id')
                   ->where('referral_id =:referralId',[':referralId'=>$id])
                   ->sum('analysis_fee');

                $rate = $model->discount_rate;
                $discounted = $subtotal * ($rate/100);
                $total = $subtotal - $discounted;

                return $this->render('viewnotice', [
                    'model' => $model,
                    'sampleDataProvider' => $sampleDataProvider,
                    'analysisdataprovider'=> $analysisDataprovider,
                    'subtotal' => $subtotal,
                    'discounted' => $discounted,
                    'total' => $total,
                    'countSample' => count($samples),
                    'notification' => $noticeDetails,
                    'depositslip' => $deposit,
                    'officialreceipt' => $or,
                ]);
            } else {
                Yii::$app->session->setFlash('error', "Your agency doesn't appear notified!");
                return $this->redirect(['/referrals/notification']);
            }
        } else {
            Yii::$app->session->setFlash('error', "Invalid request!");
            return $this->redirect(['/referrals/notification']);
        }
    }

    //confirm notification
    public function actionConfirm()
    {
        if (Yii::$app->request->post()) {

            if(!empty(Yii::$app->request->post('estimated_due_date')))
            {
                $connection= Yii::$app->labdb;
                $connection->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
                $transaction = $connection->beginTransaction();

                $mi = !empty(Yii::$app->user->identity->profile->middleinitial) ? " ".substr(Yii::$app->user->identity->profile->middleinitial, 0, 1).". " : " ";

                $rstlId = (int) Yii::$app->user->identity->profile->rstl_id;
                $noticeId = (int) Yii::$app->request->get('notice_id');
                $referralId = (int) Yii::$app->request->get('referral_id');
                $sentby = (int) Yii::$app->request->get('sender_id'); //will become the recipient

                $senderName = Yii::$app->user->identity->profile->firstname.$mi.Yii::$app->user->identity->profile->lastname;

                if($noticeId > 0 && $referralId > 0 && $sentby > 0){

                        $connection= Yii::$app->db;
                        $connection->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
                        $transaction = $connection->beginTransaction();
                        
                        $notification = new Notification();
                        $notification->referral_id = $referralId;
                        $notification->notification_type_id = 2;
                        $notification->sender_id = (int) Yii::$app->user->identity->profile->rstl_id;
                        $notification->recipient_id = $sentby; //sender will become the recipient
                        $notification->sender_user_id = (int) Yii::$app->user->identity->profile->user_id;
                        $notification->sender_name = $senderName;
                        $notification->remarks = date('Y-m-d',strtotime(Yii::$app->request->post('estimated_due_date')));
                        $notification->notification_date = date('Y-m-d H:i:s');
                        if($notification->save()){
                            $noticeSent = Notification::find()->where(['notification_id'=>$noticeId])->one();
                            $noticeSent->responded = 1;
                            if($noticeSent->save()){
                                //$transaction->commit();
                                $success = 1;
                            } else {
                                $transaction->rollBack();
                                $success = 0;
                            }
                        } else {
                            $transaction->rollBack();
                            $success = 0;
                        }

                    if($success == 1){
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Confirmation sent");
                        return $this->redirect(['/referrals/notification']);
                    } else {
                        $transaction->rollBack();
                        return "<div class='alert alert-danger'><span class='glyphicon glyphicon-exclamation-sign' style='font-size:18px;'></span>&nbsp;Server Error: Confirmation fail!</div>";
                    }
                } else {
                    $transaction->rollBack();
                    return "<div class='alert alert-danger'><span class='glyphicon glyphicon-exclamation-sign' style='font-size:18px;'></span>&nbsp;Invalid request!</div>";
                }
            } else {
                Yii::$app->session->setFlash('error', "Estimated Due Date should not be empty!");
                return $this->redirect(['/referrals/referral/view','id'=>Yii::$app->request->get('referral_id'),'notice_id'=>Yii::$app->request->get('notice_id')]);
            }
        } else {
            return $this->renderAjax('_confirm');
        }
    }

    /**
     * Creates a new Referral model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Referral();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->referral_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Referral model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->referral_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Referral model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Referral model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Referral the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Referral::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    protected function findModelreceivedtrack($referral_id)
    {
        $received = Referraltrackreceiving::find()->where('referral_id =:referralId',[':referralId'=>$referral_id])->one();
        if ($received !== null) {
            return $received;
        }
        else{
            $model= new Referraltrackreceiving();
            return $model;
        }
    }  
    protected function findModeltestingtrack($referral_id)
    {
        $tracking = Referraltracktesting::find()->where('referral_id =:referralId',[':referralId'=>$referral_id])->one();
        if ($tracking !== null) {
            return $tracking;
        }
        else{
            $newModel = new Referraltracktesting();
            return $newModel;
        }
       // throw new NotFoundHttpException('hayiop');
    }
    
}
