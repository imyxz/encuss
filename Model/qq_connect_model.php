<?php
/**
 * User: imyxz
 * Date: 2017/5/30
 * Time: 12:55
 * Github: https://github.com/imyxz/
 */
class qq_connect_model extends SlimvcModel
{
    function newQQConnect($qq_open_id,$user_id,$access_token,$refresh_token,$expire_time,$nickname,$user_info)
    {
        return $this->queryStmt("insert into qq_connect set qq_open_id=?,user_id=?,qq_access_token=?,qq_refresh_token=?,qq_expire_time=DATE_ADD(now(),INTERVAL ? SECOND ) ,qq_nickname=?,qq_user_info=?",
            "sississ",
            $qq_open_id,
            $user_id,
            $access_token,
            $refresh_token,
            $expire_time,
            $nickname,
            $user_info);
    }
    function getInfoByOpenID($qq_open_id)
    {
        return $this->queryStmt("select * from qq_connect where qq_open_id=? limit 1",
            "s",
            $qq_open_id)->row();
    }
    function getInfoByUserID($user_id)
    {
        return $this->queryStmt("select * from qq_connect where user_id=? limit 1",
            "i",
            $user_id)->row();
    }
    function updateAllInfoByUserID($user_id,$open_id,$access_token,$refresh_token,$expire_time,$nickname,$user_info)
    {
        return $this->queryStmt("update qq_connect set qq_open_id=?,qq_access_token=?,qq_refresh_token=?,qq_expire_time=DATE_ADD(now(),INTERVAL ? SECOND ) ,qq_nickname=?,qq_user_info=? where user_id=?",
            "sssissi",
            $open_id,
            $access_token,
            $refresh_token,
            $expire_time,
            $nickname,
            $user_info,
            $user_id);
    }
    function updateAllInfoByOpenID($openid,$user_id,$access_token,$refresh_token,$expire_time,$nickname,$user_info)
    {
        return $this->queryStmt("update qq_connect set user_id=?,qq_access_token=?,qq_refresh_token=?,qq_expire_time=DATE_ADD(now(),INTERVAL ? SECOND ),qq_nickname=?,qq_user_info=? where qq_open_id=? limit 1",
            "ississs",
            $user_id,
            $access_token,
            $refresh_token,
            $expire_time,
            $nickname,
            $user_info,
            $openid);
    }

}