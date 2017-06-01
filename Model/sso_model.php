<?php
/**
 * User: imyxz
 * Date: 2017/5/31
 * Time: 21:53
 * Github: https://github.com/imyxz/
 */
class sso_model extends SlimvcModel
{
    function generateToken($site_id,$login_user_id,$expire_time)
    {
        $token_key=$this->generateRandString(32);
        if(!$this->queryStmt("insert into sso_info set token_key=?,site_id=?,login_user_id=?,expire_time=DATE_ADD(now(),INTERVAL ? MINUTE ),is_visited=false",
            "siii",
            $token_key,
            $site_id,
            $login_user_id,
            $expire_time))
            return false;

        return array("token_id"=>$this->InsertId,
                     "token_key"=>$token_key);
    }
    function getTokenInfo($token_id)
    {
        return $this->queryStmt("select * from sso_info where token_id=? and expire_time>=now() and is_visited=false limit 1",
            "i",
            $token_id)->row();
    }
    function setTokenVisited($token_id)
    {
        return $this->queryStmt("update sso_info set is_visited=true where token_id=? limit 1",
            "i",
            $token_id);
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