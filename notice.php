<?php
/**
 * Notice Handler
 *
 * PHP version 7
 *
 * @category  Notice
 * @package   Notice
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://nenuyouth.com
 */

declare (strict_types = 1);

header("Content-Type: text/html; charset=utf-8");

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

// 从地址中获得url
$url = $_GET["url"];
// 从url中获取id
$id = substr($url, -19, 10);
// 移动到notice目录
chdir("/www/wwwroot/mp/notice");

echo '开始匹配' . $id . "\n";

// 获得通知html
$noticeHtml = Http_get("http://mid.nenu.edu.cn:8080/xxhbwx/HttpReq?appid=2&url=$url");

// 处理通知内容，去除br，无用换行和回车，以及html注释
$noticeContent = preg_replace(
  [
    "/<br\/>/",
    "/[\r\n\t]/",
    "/<!--.*?-->/",
    "/<st.*?g>(.*?)<\/.*？>/",
    "/<sp.*?>(.*?)<\/.*？>/",
    "/<em>(.*?)<\/em>/"
  ],
  ["#@", "", "", "$1", "$1", "$1"],
  $noticeHtml
);

echo '通知内容是：' . $noticeContent;

// 按照特定规则匹配通知内容
$noticeRule = '/(.*)\${6}.*ent\">(.*)<p s.*?ht;\">(.*?)<\/p/';
preg_match_all($noticeRule, $noticeContent, $noticeData);

// 输出通知信息
var_dump($noticeData);

// 得到正文和页脚
$text = preg_replace(
  [
    "/(<\/p><p.*?>)|(#@)/",
    "/<\/p>|<p(.*)?>/"
  ],
  ["\n", ""],
  $noticeData[2][0]
);
$footer = preg_replace("/#@/", "\n", $noticeData[3][0]);

// 使用特定规则对正文进行匹配寻找附件
preg_match_all('/附件：(?:[0-9]．)?/', $text, $temp, PREG_OFFSET_CAPTURE);
var_dump($temp);

// 如果找到附件
if ($temp[0]) {
  $position = $temp[0][0][1];
  var_dump($position);
  echo "找到附件！<br />";

  // 获得附件字符串
  $attachmentString = preg_replace("/\n/", "", substr($text, $position + 9));
  var_dump($attachmentString);

  // 匹配出每一个附件
  $mutiAttachmentRule = "/(?:[0-9]．)?.*?f=\"(.*?)\".*?\">(.*?)\.(.*?)<\/a>/";
  preg_match_all($mutiAttachmentRule, $attachmentString, $attachmentData);
  var_dump($attachmentData);
  $attachment = array();
  echo "附件匹配成功！<br />";

  // 生成附件的存放文件夹
  $dir = iconv("UTF-8", "GBK", $id);
  if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
    echo "创建文件夹" . $id . "成功。<br />";
  } else {
    echo "文件夹" . $id . "已存在。<br />";
  }

  // 获取附件并写入至附件文件夹
  for ($j = 0; $j < count($attachmentData[0]); $j++) {
    $attchUrl = 'http://www.nenu.edu.cn' . $attachmentData[1][$j];
    $attchFile = file_get_contents($attchUrl);
    $attchName = pathinfo($attchUrl)["basename"];
    file_put_contents("$id/" . $attchName, $attchFile);
    $attachment[$j] = array(
      'tag' => 'doc',
      'url' => 'https://mp.nenuyouth.com/notice/' . $id . '/' . $attchName,
      'docName' => $attachmentData[2][$j] . '.' . $attachmentData[3][$j]
    );
  }

  // 重新获得正文内容，并处理正文中的a标签
  $text = preg_replace(
    "/<a.*href=\"(.*?)\".*?>(.*)<\/a>/",
    "$2(网址为：$1)",
    substr($text, 0, $position)
  );

  // 生成通知数据
  $notice = array(
    "title" => $noticeData[1][0],
    "content" => $text,
    "attachment" => $attachment,
    "footer" => $footer
  );
} else {
  echo '没有附件！<br />';

  // 处理正文中的a标签
  $text = preg_replace(
    "/<a.*href=\"(.*?)\".*?>(.*)<\/a>/",
    "$2(网址为：$1)",
    $text
  );

  $notice = array(
    "title" => $noticeData[1][0],
    "content" => $text,
    "footer" => $footer
  );
}
echo $text;
$tableRule = '/(?:\<div.*?\>)?\<table.*?\>(?=.|\n)*?\<\/table>(?:\<\/div>)?/';
// $tableRule='/(?:\<div.*?\>)?((?:\<table.*?\>)(?=.|\n)*?(?:\<\/table>))(?:\<\/div>)?/';
preg_match_all($tableRule, $text, $tableData);
var_dump($tableData);
for ($l = 0; $l < count($tableData[0]); $l++) {
  $tableStartPosition = strpos($text, $tableData[0][$l]);
  $tableEndPosition = $tableStartPosition + strlen($tableData[0][$l]);
  $tableString = preg_replace("/\n/", "", $tableData[0][$l]);
  $tableRule2 = '/<table.*?\><tbody>(.*?)<\/tbody><\/table>/';
  preg_match_all($tableRule2, $tableString, $tableData2);
  var_dump($tableData2);
  $tableString2 = $tableData2[1][0];
  $tableRule3 = '/<tr.*?\>(.*?)<\/tr>/';
  preg_match_all($tableRule3, $tableString2, $tableData3);
  var_dump($tableData3);
}
var_dump($notice);
$noticeString = json_encode($notice, 320);
$noticeResult = file_put_contents("$id.json", $noticeString);

if ($noticeResult) {
  echo '通知' . $id . "生成成功!<br />";
}
