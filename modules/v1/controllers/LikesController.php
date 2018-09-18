<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\LikeResource;
use app\modules\v1\models\PostResource;
use Yii;
use yii\web\BadRequestHttpException;


class LikesController extends BaseController {

    public $modelClass = 'app\modules\v1\models\LikeResource';
    public $excludedFields = ['id','user_id','post_id'];

    /**
     * @return LikeResource
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreatelike()
    {
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        $acceptableFields = ['post_id'];

        if($this->checkauthuser()){
            $like = LikeResource::find()->where(['user_id'=>$this->checkauthuser(), 'post_id'=>$data['post_id']])->one();
            $post = PostResource::findOne($data['post_id']);

            if(empty($like) && !empty($post)){
                $newLike = new LikeResource();
                $request = Yii::$app->getRequest();
                $data = $request->getBodyParams();

                foreach ($data as $key=>$value) {if($newLike->hasProperty($key) && in_array($key, $acceptableFields)){ $newLike->$key = $value; }}

                $newLike->user_id       = $this->checkauthuser();
                $newLike->author_id     = $this->checkauthuser();
                $newLike->created_date  = date('Y-m-d H:i:s');
                $newLike->status  = $data['action'];

                $newLike->save();
                $count = $post->post_like_count;
                $discount = $post->post_dislike_count;
                if(!empty($data['action']) && $data['action']=='like'){
                    $post->post_like_count = $count +1;
                    $post->update();
                    return ['status'=>'OK', 'message'=>'liked'];
                }
                if(!empty($data['action']) && $data['action']=='dislike'){
                    $post->post_dislike_count = $discount +1;
                    $post->update();
                    return ['status'=>'OK', 'message'=>'disliked'];
                }

            }else{
                throw new BadRequestHttpException("Resourse not found");
            }
        }
    }
}

