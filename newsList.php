<?php
/**
 * NewsList Handler
 *
 * PHP version 7
 *
 * @category  News
 * @package   News
 * @author    Mr.Hope <zhangbowang1998@gmail.com>
 * @copyright 2019 HopeStudio
 * @license   No License
 * @link      https://nenuyouth.com
 */

header("Content-Type: text/html; charset=utf-8");

// 是否强行升级
$forceUpdate = isset($_GET['force']) ? true : false;


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
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  curl_close($curl);
  return $response;
}

// 创建通知文件夹
chdir("/www/wwwroot/mp/news");

// 将从内网公告网址得到的字符串做处理，去掉其中的制表、换行符号
$listContent = preg_replace(
  '/[\n\r\t]*/',
  '',
  Http_get("http://mid.nenu.edu.cn:8080/xxhbwx/HttpReq?url=www.nenu.edu.cn/_s4/139/list1.psp&&appid=2")
);

// 设置通知列表匹配规则
$listRule = '/\',\'(.*?)\'.*?x;\'>(.*?)<\/d.*?\'>(.*?)</';

// 将匹配到的结果写入到listData数组中
preg_match_all($listRule, $listContent, $listData);

// 新建noticeList数组
$newsList = array();

// 输入代码标签
echo "<pre>";

// 对listData的每一项进行处理
for ($i = 0; $i < count($listData[0]); $i++) {

  // 从地址中获得url
  $url = ltrim($listData[1][$i], 'http://');
  // 从url中获取id
  $id = substr($url, -19, 10);

  // 如果相应id的json文件不存在或者启用了强制更新
  if (!file_exists($id . '.json') || $forceUpdate == true) {
    echo '开始匹配' . $id . "<br />";

    // 获得通知html
    $newsHtml = Http_get("http://mid.nenu.edu.cn:8080/xxhbwx/HttpReq?appid=2&url=$url");

    // 处理通知内容，去除br，无用换行和回车，以及html注释
    $newsContent = preg_replace(
      [
        "/<(br|em)\/>/",
        "/[\r\n\t]/",
        "/<!--.*?-->/",
        "/<st.*?g>(.*?)<\/.*？>/",
        "/<sp.*?>(.*?)<\/.*？>/",
        "/<em>(.*?)<\/em>/"
      ],
      ["#@", "", "", "$1", "$1", "$1"],
      $newsHtml
    );

    echo '通知内容是：' . $newsContent;

    // 按照特定规则匹配通知内容
    $newsRule = '/(.*)\${6}.*ent\">(.*)<d.*?eta\">(.*?)<\/d/';
    preg_match_all($newsRule, $newsContent, $newsData);

    // 输出通知信息
    var_dump($newsData);

    // 得到正文和页脚
    $text = preg_replace(
      [
        "/(<\/p><p.*?>)|(#@)/",
        "/<\/p>|<p(.*)?>/"
      ],
      ["\n", ""],
      $newsData[2][0]
    );
    $footer = preg_replace("/#@/", "\n", $newsData[3][0]);

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
          'url' => 'https://mp.nenuyouth.com/news/' . $id . '/' . $attchName,
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
      $news = array(
        "title" => $newsData[1][0],
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

      // 生成通知数据
      $news = array(
        "title" => $newsData[1][0],
        "content" => $text,
        "footer" => $footer
      );
    }
    var_dump($news);

    // 把通知序列化写入json
    $newsString = json_encode($news, 320);
    $newsResult = file_put_contents("$id.json", $newsString);
    if ($newsResult) {
      echo '通知' . $id . "生成成功!<br />";
    }
  } else {
    echo "已生成通知" . $id . "<br />";
  }

  // 生成通知列表
  $newsList[$i] = array(
    'id' => $id,
    'time' => $listData[3][$i],
    'title' => $listData[2][$i],
    'url' => $url
  );
}

echo '新闻列表处理完毕：';
var_dump($newsList);
$newsListString = json_encode($newsList, 320);
$newsListResult = file_put_contents('news.json', $newsListString);
if ($newsListResult) {
  echo 'Update success!';
};
echo "</pre>";
