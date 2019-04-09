<?php
header("Content-Type: text/html; charset=utf-8");
$forceUpdate=isset($_GET['force'])?true:false;
function http_get($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
chdir("/www/wwwroot/mp/notice");
$listContent = preg_replace('/[\n\r\t]*/', '', http_get("http://mid.nenu.edu.cn:8080/xxhbwx/HttpReq?url=www.nenu.edu.cn/_s4/tzggwmhzqw/list1.psp&appid=2"));
$listRule='/\',\'(.*?)\'.*?x;\'>(.*?)<\/d.*?\'>(.*?)</';
preg_match_all($listRule, $listContent, $listData);
$noticeList=array();
echo "<pre>";
for ($i=0;$i<count($listData[0]);$i++) {
    $url=ltrim($listData[1][$i], 'http://');
    $id=substr($url, -19, 10);
    if (!file_exists($id.'.json')||$forceUpdate==true) {
        echo '开始匹配'.$id."<br />";
        $noticeHtml= http_get("http://mid.nenu.edu.cn:8080/xxhbwx/HttpReq?appid=2&url=$url");
        $noticeContent = preg_replace(["/<br\/>/","/[\r\n\t]/","/<!--.*?-->/"], ["#@","",""], $noticeHtml);
        $noticeRule='/(.*)\${6}.*ent\">(.*)<p s.*?ht;\">(.*?)<\/p/';
        preg_match_all($noticeRule, $noticeContent, $noticeData);
        $text = preg_replace(["/(<\/p><p.*?>)|(#@)/","/<\/p>|<p(.*)?>/"], ["\n",""], $noticeData[2][0]);
        $footer = preg_replace("/#@/", "\n", $noticeData[3][0]);
        preg_match_all('/附件：(?:[0-9]．)?/', $text, $temp, PREG_OFFSET_CAPTURE);
        var_dump($temp);
        if ($temp[0]) {
            $position=$temp[0][0][1];
            var_dump($position);
            echo "找到附件！<br />";
            $attachmentString = preg_replace("/\n/", "", substr($text, $position+9));
            var_dump($attachmentString);
            $mutiAttachmentRule="/(?:[0-9]．)?.*?f=\"(.*?)\".*?\">(.*?)\.(.*?)<\/a>/";
            preg_match_all($mutiAttachmentRule, $attachmentString, $attachmentData);
            var_dump($attachmentData);
            $attachment=array();
            echo "附件匹配成功！<br />";
            $dir = iconv("UTF-8", "GBK", $id);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
                echo "创建文件夹".$id."成功。<br />";
            } else {
                echo "文件夹".$id."已存在。<br />";
            }
            for ($j=0;$j<count($attachmentData[0]);$j++) {
                $attchUrl='http://www.nenu.edu.cn'.$attachmentData[1][$j];
                $attchFile=file_get_contents($attchUrl);
                $attchName=pathinfo($attchUrl)["basename"];
                file_put_contents("$id/".$attchName, $attchFile);
                $attachment[$j]=array(
                    'tag'=>'doc',
                    'url'=>'https://mrhope.top/mpServer/notice/'.$id.'/'.$attchName,
                    'docName'=>$attachmentData[2][$j].'.'.$attachmentData[3][$j]
                );
            }
            $text = substr($text, 0, $position);
            $notice = array("title" => $noticeData[1][0], "content" => $text, "attachment" => $attachment, "footer" => $footer);
        } else {
            echo '没有附件！<br />';
            $notice = array("title" => $noticeData[1][0], "content" => $text, "footer" => $footer);
        }
        var_dump($notice);
        $noticeString = json_encode($notice, 320);
        $noticeResult = file_put_contents("$id.json", $noticeString);
        if ($noticeResult) {
            echo '通知'.$id."生成成功!<br />";
        }
    } else {
        echo "已生成通知".$id."<br />";
    }
    $noticeList[$i]=array(
        'id'=>$id,
        'time'=>$listData[3][$i],
        'title'=>$listData[2][$i],
        'url'=>$url
    );
}
echo '通知列表处理完毕：';
var_dump($noticeList);
$noticeListString = json_encode($noticeList, 320);
$noticeListResult=file_put_contents('notice.json', $noticeListString);
if ($noticeListResult) {
    echo 'Update success!';
};
echo "</pre>";
