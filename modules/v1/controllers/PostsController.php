<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\FriendResource;
use app\modules\v1\models\GroupResource;
use app\modules\v1\models\PostResource;
use app\modules\v1\models\FileResource;
use app\modules\v1\models\FollowerResource;
use app\modules\v1\models\PrivacyResource;
use app\modules\v1\models\QuestionResource;
use app\modules\v1\models\VideoResource;
use Yii;
use yii\rest\IndexAction;
use yii\web\BadRequestHttpException;


class PostsController extends BaseController {

    public $modelClass = 'app\modules\v1\models\PostResource';
    public $excludedFields = ['id','author_id','post_user_id'];
    public $relationsWith = ['user','comments','attactments','tax','likes'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);

        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'prepareDataProvider' => function(IndexAction $action, $filter) {
                return $this->prepareDataProvider($action, null);
            }
        ];


        return $actions;
    }

    /**
     * @return PostResource
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreatepost()
    {

        /*
            TODO
            Check user existence before set "post_wall_id"
            Before attach post to group or other entity check their existance (ex. post_for = group id)
        */

        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        $acceptableFields = ['post_user_id','posttype', 'post_created_date','post_like_count', 'post_content',
            'post_community', 'post_poll','post_poll_title','post_poll_all_voted', 'post_comment_count', 'post_updated_date',
            'post_wall_id', 'post_group_id', 'author_id', 'post_tax_id', 'post_attachments', 'post_for'
        ];

        if($this->checkauthuser()){

            $newPost = new PostResource();
            $request = Yii::$app->getRequest();
            $data = $request->getBodyParams();


            if(!empty($data['posttype']) && !empty($data['questions'])){
                $newVote = new PostResource();
                $newVote->post_poll_title = $data['title'];
                $newVote->posttype = 'vote';
                $newVote->author_id = $this->checkauthuser();
                $newVote->post_wall_id = $this->checkauthuser();
                $newVote->post_user_id = $this->checkauthuser();
                $newVote->save();



                foreach ($data['questions'] as $key=>$value){
                    $queston = new QuestionResource();
                    $queston->author_id = $this->checkauthuser();
                    $queston->title = $value;
                    $queston->post_id = $newVote->id;
                    $queston->save();
                }

                $vote = PostResource::find()->where(['id'=>$newVote->id])
                    ->select('id,author_id,post_user_id,posttype,post_wall_id,post_poll_title,post_created_date')
                    ->with('questions')
                    ->asArray()
                    ->one();
                return $vote;
            }


            if(empty($data['post_wall_id'])){
                $data['post_wall_id'] = $this->checkauthuser();
            }

            foreach ($data as $key=>$value) {

                if($newPost->hasProperty($key) && in_array($key, $acceptableFields)){
                    $newPost->$key = $value;
                }
            }


            if(!empty($data['post_for'])){
                $group = GroupResource::findOne($data['post_for']);
                if(!empty($group) && $group->group_author == $this->checkauthuser()){
                    $newPost->post_for = $data['post_for'];
                    $newPost->posttype = 'group';
                }else{
                    throw new BadRequestHttpException("Group by id=".$data['post_for']." does not exist OR you have not access to add posts for this group");
                }
            }


            /*Checking permissions if user posts on another user wall*/
            if($data['post_wall_id'] != $this->checkauthuser()){
                $canPost = PrivacyResource::find()->where(['author_id'=>$data['post_wall_id']])->one();
                if(!empty($canPost)){
                    $ifCanPost = $canPost->can_post;
                    $seesRecords = $canPost->sees_other_records;
                    if($ifCanPost == 3){
                        throw new BadRequestHttpException("Access denied, Only owner can create posts to his/her wall", 402);
                    }elseif($ifCanPost == 2){
                        /*Checking if user is friend*/
                        $isFriend = FriendResource::find()->where(['user_id'=>$this->checkauthuser(), 'friend_id'=>$data['post_wall_id'], 'subscription'=>1])
                            ->orWhere(['user_id'=>$data['post_wall_id'], 'friend_id'=>$this->checkauthuser(), 'subscription'=>1])->one();
                        if(empty($isFriend)){
                            throw new BadRequestHttpException("Access denied, Only friends can create posts on user wall", 402);
                        }
                    }elseif($ifCanPost == 1 && $seesRecords == 3){
                        throw new BadRequestHttpException("Access denied. User wall is private, Nobody cannot see his/her wall", 402);
                    }
                }else{
                    throw new BadRequestHttpException("Wall id=".$data['post_wall_id']." not found");
                }
            }

            if(empty($data['questions'])) {
                $newPost->post_user_id = $this->checkauthuser();
                $newPost->author_id = $this->checkauthuser();
                $newPost->post_created_date = date( 'Y-m-d H:i:s' );
                $newPost->post_updated_date = date( 'Y-m-d H:i:s' );
                $newPost->post_like_count = 0;
                $newPost->post_comment_count = 0;

                $newPost->save();
            }

            /*Updating Files Model if request object consists attachment file ids*/

            /*TODO
             * Attache file(s) to post when it(s) not attached yet (check before attached)
             */
            if(!empty($data['post_attachments'])){
                $files = FileResource::find()->where(['in', 'id', $data['post_attachments']])->all();
                foreach ($files as $file){
                    $file->post_id = $newPost->id;
                    $file->update();
                }
            }

            if(!empty($data['post_videos'])){

                $videos = VideoResource::find()->where(['in', 'id', $data['post_videos']])->all();
                foreach ($videos as $video){
                    $video->post_id = $newPost->id;
                    $video->update();
                }
            }

            $lastPosts = PostResource::find()
                ->where(['id'=>$newPost->id])
                ->with('videos')->asArray()
                ->with('user')->asArray()
                ->with('comments')->asArray()
                ->with('attactments')->asArray()
                ->with('tax')->asArray()
                ->with('videos')->asArray()
                ->with('likes_dislikes')->asArray()
                ->orderBy('id DESC')
                ->one();

            return $lastPosts;
        }
    }

    public function actionView($id){
        if($this->checkauthuser()){
            $posts = PostResource::find()
                ->where(['id'=>$id])
                ->with('user')->asArray()
                ->with('comments')->asArray()
                ->with('attactments')->asArray()
                ->with('tax')->asArray()
                ->with('likes')->asArray()
                ->all();

            return $posts;
        }else{
            throw new UnauthorizedHttpException();
        }
    }


    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetpostsbywallid($id)
    {

        if($this->checkauthuser()){

            /*Checking Post privacy*/
            $privacy = PrivacyResource::find()->where(['author_id'=>$id])->one()->sees_other_records;

            /*Checking if user is friend*/
            $isFriend = FriendResource::find()->where(['user_id'=>$this->checkauthuser(), 'friend_id'=>$id, 'subscription'=>1])
                ->orWhere(['user_id'=>$id, 'friend_id'=>$this->checkauthuser(), 'subscription'=>1])->one();

            $allPosts = PostResource::find()
                ->where(['post_wall_id'=>$id])
                ->joinWith('user')->asArray()
                ->joinWith('comments')->asArray()
                ->joinWith('attactments')->asArray()
                ->joinWith('tax')->asArray()
                ->joinWith('videos')->asArray()
                ->joinWith('likes_dislikes')->asArray()
                ->orderBy('id DESC')
                ->all();

            $myPosts = PostResource::find()
                ->where(['post_wall_id'=>$id, 'post_user_id'=>$id])
                ->joinWith('user')->asArray()
                ->joinWith('comments')->asArray()
                ->joinWith('attactments')->asArray()
                ->joinWith('tax')->asArray()
                ->joinWith('videos')->asArray()
                ->joinWith('likes_dislikes')->asArray()
                ->orderBy('id DESC')
                ->all();


            if($privacy == 1){
                return $allPosts;
            }elseif($privacy == 2){
                if(!empty($isFriend) || $id == $this->checkauthuser()){
                    return $allPosts;
                }else{
                    return $myPosts;
                }
            }elseif($privacy == 3){
                if($id == $this->checkauthuser()){
                    return $allPosts;
                }else{
                    throw new BadRequestHttpException("Access denied, Posts can see only author", 402);
                }
            }
        }else{
            throw new UnauthorizedHttpException();
        }
    }

    public function actionDelete($id)
    {
        $post = PostResource::find()->where(['id'=>$id])->one();
        if($post->author_id == $this->checkauthuser() || $post->post_wall_id == $this->checkauthuser()){
            $post->delete();
            return ['status'=>'OK', 'message'=>'Post successfully deleted'];
        }else{
            return ['status'=>'False', 'message'=>'You don\'t have permission to delete this post'];
        }
    }

}

