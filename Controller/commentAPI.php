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
            if(!$comment_id=$this->model("comment_model")->insertComment($post_id,$user_info['user_id'],$parent_id,$content,0))
                throw new Exception("发表评论失败！");
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
            $site_info=$this->model("site_model")->getSiteInfoByID($site_id);
            if(!$site_info) throw new Exception("站点不存在");
            $post_info=$this->model("post_model")->getPostInfoBySiteInfo($site_id,$site_post_id);
            $post_id=0;
            $return['replys']=array();
            if($post_info)
            {
                $post_id=$post_info['post_id'];
                $comments=$this->model("comment_model")->getPostComments($post_id,0,100);
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
                        "status"=>$one['comment_status']
                    );
                }

            }
            else
            {
                $post_id=$this->model("post_model")->newSitePost($site_id,$site_post_id,0,0);
                if($post_id<=0) throw new Exception("系统出错：无法插入新文章");
            }
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