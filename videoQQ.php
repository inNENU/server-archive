<?php

/**
 * Page Handler
 *
 * PHP version 7
 *
 * @category  Page
 * @package   Page
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://mrhope.site
 */

declare (strict_types = 1);

$vid=$_GET["vid"];
function http_get($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    // CURLOPT_RETURNTRANSFER  设置是否有返回值
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //执行完以后的返回值
    $response = curl_exec($curl);
    //释放curl
    curl_close($curl);
    return $response;
}
$data = http_get("http://vv.video.qq.com/getinfo?vids=$vid&platform=11&charge=0&otype=json");
var_dump($data);
echo $data;
$jsonString = '['.substr($data, 13, strlen($data)-14).']';
echo $jsonString;
$object = json_decode($jsonString, true);
var_dump($object);
$temp = $object[0]['vl']['vi'][0];
var_dump($temp);
$src=$temp['ul']['ui'][2]['url'].$temp['fn'].'?vkey='.$temp['ul']['ui'][0]['url'];
echo $src;
