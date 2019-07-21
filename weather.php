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

header("Content-Type: text/json; charset=utf-8");

echo file_get_contents("https://wxapi.hotapp.cn/proxy/?appkey=hotapp477295126&url=http://wthrcdn.etouch.cn/weather_mini?city=长春");
