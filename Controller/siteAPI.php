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
}