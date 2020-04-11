<?php

/**
 * Login Handler
 *
 * PHP version 7
 *
 * @category  Login
 * @package   Login
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://mrhope.site
 */

header("Content-Type: text/json; charset=utf-8");

$AppSecretList = array(
  "wx33acb831ee1831a5" => 'e438e6c097c3ade497bca94d46729f48',
  "wx9ce37d9662499df3" => '16ecead5bfcf7cc85c2047dee1eb5b5f'
);

// 获得传递数据
$json = json_decode(file_get_contents('php://input'));

// 获得登录状态码
$code = $json->code;
$appID = $json->appID;
$secret = $AppSecretList[$appID];

/**
 * 网站数据请求函数
 *
 * @param string $url 网页地址
 *
 * @return string 网页的string内容
 */
function Http_get($url)
{
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_NOBODY, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_URL, $url);
  $response = curl_exec($curl);
  curl_close($curl);

  return $response;
}


$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appID . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code';

$response = Http_get($url);
echo $response;
$data = json_decode($response, true);
