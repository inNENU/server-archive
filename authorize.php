<?php

/**
 * Authorize Handler
 *
 * PHP version 7
 *
 * @category  Authorize
 * @package   Authorize
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://mrhope.site
 */

header("Content-Type: text/html; charset=utf-8");

$AppSecretList = array(
  "wx33acb831ee1831a5" => 'e438e6c097c3ade497bca94d46729f48',
  "wx9ce37d9662499df3" => '16ecead5bfcf7cc85c2047dee1eb5b5f'
);

// 获得传递数据
$json = json_decode(file_get_contents('php://input'));

// // 获取用户名密码
// $username = $json->username;
// $password = $json->password;

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
  // curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_HEADER, true);  //输出header信息
  curl_setopt($curl, CURLOPT_NOBODY, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_URL, $url);
  $response = curl_exec($curl);


  $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
  // 根据头大小去获取头信息内容
  $header = substr($response, 0, $headerSize);
  curl_close($curl);

  return array('header' => $header, 'body' => $response);
}


$url = 'https://ids.nenu.edu.cn/amserver/UI/Login';

$response = Http_get($url);

var_dump($response);
