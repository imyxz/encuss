<?php
/**
 * User: imyxz
 * Date: 2017/5/31
 * Time: 22:05
 * Github: https://github.com/imyxz/
 */
class api_access_model extends SlimvcModel
{
    function generateToken($privilege,$site_id,$user_id,$expire_time)
    {
        $token_key=$this->generateRandString(32);
        if(!$this->queryStmt("insert into api_access_info set access_key=?,access_privilege=?,site_id=?,access_user_id=?,expire_time=DATE_ADD(now(),INTERVAL ? MINUTE )",
            "ssiii",
            $token_key,
            $privilege,
            $site_id,
            $user_id,
            $expire_time))
            return false;
        return array("token_id"=>$this->InsertId,
            "token_key"=>$token_key);
    }
    function getTokenInfo($token_id)
    {
        return $this->queryStmt("select * from api_access_info where access_id=? and expire_time>=now() limit 1",
            "i",
            $token_id)->row();
    }
    protected function generateRandString($length)
    {
        $str="1234567890QWERTYUIOPLKJHGFDSAZXCVBNM";
        $ret="";
        for($i=0;$i<$length;$i++)
        {
            $ret = $ret . substr($str,rand(0,strlen($str)-1),1);
        }
        return $ret;
    }
}