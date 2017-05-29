<?php
/**
 * User: imyxz
 * Date: 2017/5/24
 * Time: 14:16
 * Github: https://github.com/imyxz/
 */
$Config=array();
$Config['DebugSql']=false;
$Config['Session']=false;
$Config['CharSet'] = 'utf-8';


$Config['Host'] = 'localhost';
$Config['User'] = 'root';
$Config['Password'] = '';
$Config['DBname'] = 'encuss';

$_SERVER['REQUEST_URI']=str_replace("encuss/","",$_SERVER['REQUEST_URI']);