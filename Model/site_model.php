<?php
/**
 * User: imyxz
 * Date: 2017/5/27
 * Time: 13:11
 * Github: https://github.com/imyxz/
 */
class site_model extends SlimvcModel
{
    function getSiteInfoByID($site_id)
    {
        return $this->queryStmt("select * from site_info where site_id=? limit 1",
            "i",
            $site_id)->row();
    }
}