<?php
/**
 * User: imyxz
 * Date: 2017/6/1
 * Time: 0:15
 * Github: https://github.com/imyxz/
 */
class siteAPI extends SlimvcController
{
    function get_user_info()
    {
        try{
            $json=$this->getRequestJson();
            $token_id=intval($json['token_id']);
            $token_key=$json['token_key'];
            $site_id=$json['site_id'];
            $token_info=$this->model("api_access_model")->getTokenInfo($token_id);
            if(!$token_info || $token_info['access_key']!=$token_key || $site_id!=$token_info['site_id'])
                throw new Exception("Token错误！");
            $privilege=json_decode($token_info['access_privilege'],true);
            if(!in_array("get_user_info",$privilege))
                throw new Exception("没有此接口权限");
            $user_id=$token_info['access_user_id'];
            $user_info=$this->model("user_model")->getUserInfo($user_id);
            $return['nickname']=$user_info['user_nickname'];
            $return['user_id']=$user_id;
            $return['avatar']=$user_info['user_avatar'];
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
    function importDuoshuoUser()
    {
        try{
            $json=json_decode(file_get_contents("php://input"),true,10000, JSON_BIGINT_AS_STRING);
            $exist=0;
            $add=0;
            $error=0;
            foreach($json as $key=>&$one)
            {
                $user_name="_ds_" . $key;
                $nickname=$one['user_name'];
                $avatar=$one['avatar'];
                if($this->model("user_model")->isUserExist($user_name))
                {
                    $exist++;
                    continue;
                }
                $user_id=$this->helper("user_helper")->newUser($user_name,"DUOSHUO","NULL",$nickname,"0.0.0.0");
                if($user_id<=0)
                {
                    $error++;
                    continue;
                }
                $this->helper("user_helper")->updateUserAvatar($user_id,$avatar);
                $add++;
            }
            $return['exist']=$exist;
            $return['add']=$add;
            $return['error']=$error;
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
    function importDuoshuoComment()
    {
        try{
            $json=json_decode(file_get_contents("php://input"),true,10000, JSON_BIGINT_AS_STRING);
            $exist=0;
            $add=0;
            $error=0;
            $site_id=intval($_GET['site_id']);
            $site_info=$this->model("site_model")->getSiteInfoByID($site_id);
            $comments=array();
            $users=array();
            if(!$site_info)
                throw new Exception("站点不存在！");

            foreach($json as $key=>&$one)
            {
                $site_post_id=intval($key);
                $post_info=$this->model("post_model")->getPostInfoBySiteInfo($site_id,$site_post_id);
                $post_id=0;
                if(!$post_info)
                    $post_id=$this->model("post_model")->newSitePost($site_id,$site_post_id,0,0);
                else
                    $post_id=$post_info['post_id'];
                if($post_id<=0)
                    throw new Exception("插入文章失败！");
                foreach($one['comments'] as &$two)
                {
                    $parent_id=0;
                    $user_name="_ds_" . $two['author_id'];
                    $content=$two['contents'];
                    if(isset($users[$user_name]))
                        $user_id=$users[$user_name];
                    else
                    {
                        $user_id=$this->model("user_model")->getUserId($user_name);
                        $users[$user_name]=$user_id;
                    }
                    if(isset($comments[$two['parent_id']]))
                        $parent_id=$comments[$two['parent_id']];
                    if($user_id<=0)
                    {
                        $error++;
                        continue;
                    }
                    $comment_id=$this->model("comment_model")->insertComment($post_id,$user_id,$parent_id,$content,0);
                    if($comment_id<=0)
                    {
                        $error++;
                        continue;
                    }
                    $this->model("comment_model")->updateCommentTime($comment_id,intval($two['time']));
                    $comments[$two['comment_id']]=$comment_id;
                    $add++;
                }
            }
            $return['exist']=$exist;
            $return['add']=$add;
            $return['error']=$error;
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