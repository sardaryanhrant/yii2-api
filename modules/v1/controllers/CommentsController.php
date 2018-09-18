<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\CommentResource;
use app\modules\v1\models\FriendResource;
use app\modules\v1\models\PostResource;
use app\modules\v1\models\PrivacyResource;
use yii\web\BadRequestHttpException;

class CommentsController extends BaseController
{
    public $modelClass = 'app\modules\v1\models\CommentResource';
    public $excludedFields = ['id','author_id', 'comment_post_id', 'comment_user_id'];

    public function actionCreatecomment()
    {
        if($this->checkauthuser()){
            $request = \Yii::$app->getRequest();
            $data = $request->getBodyParams();
            $newComment = new CommentResource();

            $acceptableFields = ['comment_post_id', 'comment_content', 'comment_for'];

            /*If comments are going to posts
             * Checking post owner
             */
            $postOwner = PostResource::findOne($data['comment_post_id'])->post_user_id;
            if($postOwner != $this->checkauthuser() ){
                /*Checking privacy for comment*/
                $commentPrivacy = PrivacyResource::find()->where(['author_id'=>$postOwner])->one()->can_comment;
                if($commentPrivacy == 3){
                    throw new BadRequestHttpException("Access denied, Only owner can create comments on his/her wall", 402);
                }elseif($commentPrivacy == 2){
                    /*Checking if user is friend*/
                    $isFriend = FriendResource::find()->where(['user_id'=>$this->checkauthuser(), 'friend_id'=>$postOwner, 'subscription'=>1])
                        ->orWhere(['user_id'=>$postOwner, 'friend_id'=>$this->checkauthuser(), 'subscription'=>1])->one();
                    if(empty($isFriend)){
                        throw new BadRequestHttpException("Access denied, Only owner friends can create comments on his/her wall", 402);
                    }
                }
            }

            foreach ($data as $key=>$value) {
                if($newComment->hasProperty($key) && in_array($key, $acceptableFields)){
                    $newComment->$key = $value;
                }
            }

            $newComment->comment_user_id = $this->checkauthuser();
            $newComment->comment_created_date = date('Y-m-d H:i:s');
            $newComment->comment_updated_date = date('Y-m-d H:i:s');
            $newComment->author_id = $this->checkauthuser();
            $newComment->save();
            return $newComment;
        }       
        
    }



}
