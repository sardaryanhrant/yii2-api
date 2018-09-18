<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\BlockUserResource;
use app\modules\v1\models\FileResource;
use app\modules\v1\models\FollowerResource;
use app\modules\v1\models\GroupResource;
use app\modules\v1\models\PostResource;
use app\modules\v1\models\UserResource;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class FollowersController extends BaseController {

    public $modelClass = 'app\modules\v1\models\FollowerResource';
    public $excludedFields = ['id','author_id'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex(){
        if($this->checkauthuser()) {
            $chats = FollowerResource::find()
                ->where(['or', ['follow_to'=>$this->checkauthuser()]])
                ->all();
            return $chats;
        }else{
            throw new UnauthorizedHttpException();
        }
    }

    public function actionCreate(){
        $newFollower = new FollowerResource();

        $acceptableFields = ['follow_to','to'];

        $request = \Yii::$app->getRequest();
        $data = $request->getBodyParams();

        if($data['to'] == 'user'){ $model = new UserResource();
        }elseif($data['to'] == 'group'){
            $model = new GroupResource();
        }
        if($data['to'] == 'user' && $data['follow_to'] == $this->checkauthuser()) {
            throw new BadRequestHttpException( "You cannot follow yourself :) " );
        }


        if($data['to'] == 'group') {
            /*Checking if group exist*/
            if ($this->userExist( $data['follow_to'], new GroupResource() )) {
                /*Befor following group check if user is not blocked by group*/
                $ifUserIsBlockedByGroup = FollowerResource::find()->where( ['author_id' => $this->checkauthuser(), 'follow_to' => $data['follow_to'], 'to' => 'group'] )->one();
                if (empty( $ifUserIsBlockedByGroup )) {
                    foreach ($data as $key => $value) {
                        if ($newFollower->hasProperty( $key ) && in_array( $key, $acceptableFields )) {
                            $newFollower->$key = $value;
                        }
                    }
                    $newFollower->author_id = $this->checkauthuser();
                    $newFollower->user_id = $this->checkauthuser();
                    $newFollower->save();
                    return $newFollower;
                }
            } else {
                throw new NotFoundHttpException( 'Group does not exist by id=' . $data['follow_to'] );
            }
        }

        if ($this->userExist( $data['follow_to'], $model )) {
            foreach ($data as $key => $value) {
                if ($newFollower->hasProperty( $key ) && in_array( $key, $acceptableFields )) {$newFollower->$key = $value;}
            }

            $newFollower->author_id = $this->checkauthuser();
            $newFollower->user_id = $this->checkauthuser();
            $newFollower->save();
            return $newFollower;
        } else {
            throw new BadRequestHttpException( "Resourse not found.");
        }
    }

    public function actionDelete($id){
        if($this->checkauthuser()) {
            $request = \Yii::$app->getRequest();
            $data = $request->getBodyParams();

            $follow = FollowerResource::find()->where( ['follow_to' => $id, 'to' => $data['to'], 'user_id' => $this->checkauthuser()] )->one();
            if(!empty($follow )){
                $follow->delete();
            }else{
                throw new BadRequestHttpException("Resource not found");
            }

        }
    }



    /**
     * @return array
     */
    public function actionNews()
    {

        if($this->checkauthuser()){
            $friendList = FollowerResource::find()
                ->where(['user_id'=>$this->checkauthuser()])
                ->andWhere(['to'=>'user'])
                ->all();
            $frendIds = array();
            $groupIds = array();
            foreach ($friendList as $value) { $frendIds[] = $value['follow_to'];}

            $groupList = FollowerResource::find()
                ->where(['user_id'=>$this->checkauthuser()])
                ->andWhere(['to'=>'group'])
                ->all();

            foreach ($groupList as $value) { $groupIds[] = $value['follow_to'];}

            $news = array();

            $friendPosts = UserResource::find()->where(['in', 'id', $frendIds])
                ->select('id,user_name,user_last_name,user_photo,user_gender')
                ->with('posts')->asArray()
                ->all();

            $groupPosts = GroupResource::find()->where(['in', 'id', $groupIds])
                ->select('id,group_name,group_background,group_created_date')
                ->with('groupposts')->asArray()
                ->all();

            foreach ($friendPosts as $post){
                if(!empty($post['posts'])){
                    foreach ($post['posts'] as $p){
                        $p['user']['user_name'] = $post['user_name'];
                        $p['user']['user_last_name'] = $post['user_last_name'];
                        $p['user']['user_photo'] = $post['user_photo'];
                        $p['user']['user_gender'] = $post['user_gender'];
                        $news[] = $p;
                    }
                }
            }

            foreach ($groupPosts as $post){
                if(!empty($post['groupposts'])) {
                    foreach ($post['groupposts'] as $p){
                        $p['group']['group_name'] = $post['group_name'];
                        $p['group']['group_id'] = $post['id'];
                        $p['group']['group_background'] = $post['group_background'];
                        $news[] = $p;
                    }
                }
            }

            $interests = UserResource::findOne($this->checkauthuser());
            if(!empty($interests->user_interests)) {
                $interestsArr = explode(',', $interests->user_interests['interests']);
                $googleQueryString = '';
                foreach ($interestsArr as $q){
                    $googleQueryString .= str_replace(' ', '', $q).'+';
                }

                $googleQueryString = substr( $googleQueryString, 0, -1 );
                $googleNews = json_decode( file_get_contents( "https://newsapi.org/v2/everything?q=" . $googleQueryString . "&sortBy=publishedAt&apiKey=9085260422a840588b5f8b30044f4edd" ) );

                foreach ($googleNews->articles as $gnews) {
                    $article = array();
                    $article['id'] = $gnews->source->id;
                    $article['id'] = $gnews->source->id;
                    $article['posttype'] = 'googlenews';
                    $article['post_content'] = $gnews->description;
                    $article['comments'] = [];
                    $article['post_comment_count'] = 0;
                    $article['post_dislike_count'] = 0;
                    $article['post_like_count'] = 0;
                    $article['post_wall_id'] = null;
                    $article['post_created_date'] = $gnews->publishedAt;
                    $article['attachments'][] = ['path' => $gnews->urlToImage];
                    $article['user'] = ['user_name' => $gnews->author, 'user_last_name' => '', 'user_photo' => ''];
                    $news[] = $article;
                    $article = array();
                }
            }

            usort($news, function ($item1, $item2) {
                return $item2['post_created_date'] <=> $item1['post_created_date'];
            });

            return  $news;
        }
    }

}

