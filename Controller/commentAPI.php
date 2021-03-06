<?php
/**
 * User: imyxz
 * Date: 2017/5/26
 * Time: 14:55
 * Github: https://github.com/imyxz/
 */
class commentAPI extends SlimvcController{
    function newReply(){
        $return=array();
        try{
            if($this->helper("user_helper")->isLogin()==false)
                throw new Exception("还未登录");
            $user_info=$this->helper("user_helper")->getUserInfo();
            $json=$this->getRequestJson();
            $post_id=intval($json['post_id']);
            $content=$json['content'];
            $parent_id=$json['parent_id'];
            $post_info=$this->model("post_model")->getPostInfoByPostID($post_id);
            if(!$post_info)
                throw new Exception("文章不存在");
            if($parent_id>0) {
                $parent_comment_info = $this->model("comment_model")->getCommentInfo($parent_id);
                if (!$parent_comment_info || $parent_comment_info['post_id'] != $post_id)
                    throw new Exception("父评论不存在！");
            }
            if(!$comment_id=$this->model("comment_model")->insertComment($post_id,$user_info['user_id'],$parent_id,$content,0))
                throw new Exception("发表评论失败！");
            if($post_info['post_user_id']>0)
            {
                $this->model("message_model")->insertMessage($post_info['post_user_id'],
                    "@" . $user_info['user_nickname'] . " 在 " .$post_info['post_title'] . " 给您评论：" . substr($content,0,20) . "..."
                    ,$post_info['post_url'],0);

            }
            if($parent_id>0)
            {
                $parent_user_info=$this->model("user_model")->getUserInfo($parent_comment_info['comment_user_id']);

                $this->model("message_model")->insertMessage($parent_comment_info['comment_user_id'],
                    "@" . $parent_user_info['user_nickname'] . " 在 " .$post_info['post_title'] . " 给您的评论回复：" . substr($content,0,20) . "..."
                    ,$post_info['post_url'],0);

            }
            $return['comment_id']=$comment_id;
            $return['status']=1;

            $this->outputJson($return);


        }
        catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }
    }

    function getPostReplys()
    {
        $return=array();
        header('Cache-control: private, must-revalidate');
        try{
            if(!$this->helper("user_helper")->isLogin())
                $return['isLogin']=false;
            else
            {
                $return['isLogin']=true;
                $user_info=$this->helper("user_helper")->getUserInfo();
                $return['avatar']=$user_info['user_avatar'];
                $return['nickname']=$user_info['user_nickname'];
            }
            $site_post_id=intval($_GET['site_post_id']);
            $site_id=intval($_GET['site_id']);
            $title=substr(urldecode(@$_GET['title']),0,240);
            $image=substr(urldecode(@$_GET['image']),0,240);
            $url=substr(urldecode(@$_GET['url']),0,240);
            if(substr($url,0,4)!='http')//防止javascript注入
                $url='';
            if(substr($image,0,4)!='http')//防止javascript注入
                $image='';
            $site_info=$this->model("site_model")->getSiteInfoByID($site_id);
            if(!$site_info) throw new Exception("站点不存在");
            $post_info=$this->model("post_model")->getPostInfoBySiteInfo($site_id,$site_post_id);
            $post_id=0;
            $return['replys']=array();
            if($post_info)
            {
                $post_id=$post_info['post_id'];
                $comments=$this->model("comment_model")->getPostComments($post_id,0,1000);
                foreach($comments as $one)
                {
                    $return['replys'][$one['comment_id']]=array(
                        "comment_id"=>$one['comment_id'],
                        "parent_id"=>$one['comment_parent_id'],
                        "user_id"=>$one['comment_user_id'],
                        "user_nickname"=>$one['user_nickname'],
                        "user_avatar"=>$one['user_avatar'],
                        "content"=>$one['comment_content'],
                        "time"=>$one['comment_time'],
                        "time_stamp"=>$one['comment_unix_timestamp'],
                        "status"=>$one['comment_status']
                    );
                }

            }
            else
            {
                $post_id=$this->model("post_model")->newSitePost($site_id,$site_post_id,0,0);
                if($post_id<=0) throw new Exception("系统出错：无法插入新文章");
            }
            $this->model("post_model")->updatePostInfo($post_id,$title,$url,$image);
            $return['post_id']=$post_id;

            $return['status']=1;
            $this->outputJson($return);


        }
        catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }
    }
}