<?php

/**
 * Page Handler
 *
 * PHP version 7
 *
 * @category  Res
 * @package   Res
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://mrhope.site
 */

declare(strict_types=1);

header("content-type:application/json;charset=utf-8");

chdir("../");

$name = $_GET['res'];

$filename = $name . "Version.json";

$handle = @fopen($filename, "r");
if ($handle) {
  $contents = fread($handle, filesize($filename));
  fclose($handle);
  echo $contents;
} else {
  echo 'error';
}
