<?php
class index_model extends SlimvcModel{
    function showTable($table)
    {
        return $this->queryStmt("select * from $table where 1=?","i",1)->all();
    }
}