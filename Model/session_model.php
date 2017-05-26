<?php
class session_model extends SlimvcModel
{

    function newSession($session_pass,$minutes)
    {
        if(!$this->queryStmt("insert into session_info set session_pass=?,session_status=0,session_info='',session_start_time=now(),session_last_time=now(),session_end_time=date_add(now(),?,MINUTE)",
            "si",
            $session_pass,
            $minutes))
            return false;
        return $this->InsertId;
    }

    function getSessionInfo($session_id)
    {
        return $this-$this->queryStmt("select * from session_info where session_id=? and session_status=0 and session_end_time<=now() limit 1",
            "i",
            $session_id)->row();
    }
    function delRemember($session_id)
    {
        return $this->queryStmt("update session_info set session_status=1 where session_id=? limit 1",
            "i",
            $session_id);
    }
    function updateSessionInfo($session_id,$session_info,$minutes=0)
    {
        if($minutes>0)
            return $this->queryStmt("update session_info set session_info=?,session_end_time=add_time(now(),?,MINUTE ) where session_id=? limit 1",
                "sii",
                $session_info,$minutes,$session_id);
        else
            return $this->queryStmt("update session_info set session_info=? where session_id=? limit 1",
                "sii",
                $session_info,$session_id);

    }
    function renewSessionTime($session_id,$minutes)
    {
        return $this->queryStmt("update session_info set session_end_time=add_time(now(),?,MINUTE ) where session_id=? limit 1",
            "ii",
            $minutes,
            $session_id);
    }



}

?>