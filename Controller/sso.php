<?php
/**
 * User: imyxz
 * Date: 2017/5/31
 * Time: 9:46
 * Github: https://github.com/imyxz/
 */
class sso extends SlimvcController
{
    function getAccessToken()
    {
        try{
            $json=$this->getRequestJson();
            $token_id=intval($json['token_id']);
            $token_key=$json['token_key'];
            $api_secret=$json['api_secret'];
            $site_id=$json['site_id'];
            $token_info=$this->model("sso_model")->getTokenInfo($token_id);
            $site_info=$this->model("site_model")->getSiteInfoByID($site_id);

            if(!$site_info || $site_info['site_api_secret']!=$api_secret)
                throw new Exception("api secret 错误！");
            if(!$token_info || $token_info['token_key']!=$token_key || $site_id!=$token_info['site_id'])
                throw new Exception("Token错误！");

            $privilege=array("get_user_info");

            $access_token=$this->model("api_access_model")->generateToken(json_encode($privilege),$site_id,$token_info['login_user_id'],60*24*30);
            if(!$access_token)  throw new Exception("系统内部错误！请再试！");
            $this->model("sso_model")->setTokenVisited($token_id);
            $return['token_id']=$access_token['token_id'];
            $return['token_key']=$access_token['token_key'];
            $return['expire']=60*24*30*3600;
            $return['privilege']=$privilege;
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
