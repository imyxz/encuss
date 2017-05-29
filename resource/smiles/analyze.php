<?php
/**
 * Created by PhpStorm.
 * User: i
 * Date: 2017/5/29
 * Time: 11:03
 */
$return=array();
for($x=0;$x<=3;$x++)
{
    $files=scandir("./$x/");
    $i=0;
    foreach($files as $one)
    {
        if(strpos($one,".gif")>0)
        {
            $return['bundle'][$x]['smiles'][$i++]=$one;
        }
    }
    $return['bundle'][$x]['basic_path']="resource/smiles/$x/";
}

echo json_encode($return);