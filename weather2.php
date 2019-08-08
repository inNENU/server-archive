<?php

/**
 * Page Handler
 *
 * PHP version 7
 *
 * @category  weather
 * @package   weather
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://nenuyouth.com
 */

declare(strict_types=1);

header("content-type:application/json;charset=utf-8");

/**
 * 获取指定文件并转为字符串
 *
 * @param string $url url地址
 *
 * @return string 获取到的字符串数据
 */
function Http_get(string $url): string
{
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  curl_close($curl);
  return $response;
}

// 获得通知html
echo Http_get("https://wis.qq.com/weather/common?source=pc&weather_type=observe%7Cforecast_1h%7Cforecast_24h%7Cindex%7Calarm%7Climit%7Ctips%7Crise&province=%E5%90%89%E6%9E%97&city=%E9%95%BF%E6%98%A5&county=%E5%8D%97%E5%85%B3");
