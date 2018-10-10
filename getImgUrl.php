<?php

/**
 * Created by PhpStorm.
 * User: luosilent
 * Date: 2018/10/10
 * Time: 13:48
 */
class getImgUrl
{
    /**
     * 获取图片的详情url
     * @param $url
     * @return mixed
     */
    public function getUrl($url)
    {
        $res = $this->setRequest($url);
        $pattern = '/<li class=\"(.*)\"><a class=\"(.*)\" href=\"(.*)\">/isU';
        preg_match_all($pattern, $res, $matches);
        $imgUrl = array_unique($matches[3]);
        foreach ($imgUrl as $key => $value) {
            $imgUrl[$key] = $url . $value;
        }

        return $imgUrl;
    }

    /**
     * 获取图片的src
     * @param $url
     * @return mixed
     */
    public function getSrc($url)
    {
        $resUrl = $this->getUrl($url);
        $imgSrc = array();
        $i = 0;
        foreach ($resUrl as $value) {
            $i++;
            $res = $this->setRequest($value);
            $pattern = '/<img src=\"(.*)\" alt=\"(.*)\".*?>/isU';
            preg_match_all($pattern, $res, $matches);
//            $imgSrc['src'] = array_unique($matches[1]);//图片的url
//            $imgSrc['alt'] = array_unique($matches[2]);//图片的名称
            $res = $this -> crabImage($matches[1][0],$matches[2][0]);
            echo $res."下载完成--第 $i 张";
            echo "<br>";
            if ($i > 20){
                echo "一共抓取 $i 张";
                exit;
            }

        }

        return $imgSrc;
    }

    function crabImage($imgUrl, $fileName,$saveDir='./img/')
    {
        if (empty($imgUrl)) {
            return false;
        }
        //获取图片信息大小
        $imgSize = getImageSize($imgUrl);
        if (!in_array($imgSize['mime'], array('image/jpg', 'image/gif', 'image/png', 'image/jpeg'), true)) {
            return false;
        }
        //获取后缀名
        $_mime = explode('/', $imgSize['mime']);
        $_ext = '.' . end($_mime);
        if (empty($fileName)) {
            $fileName = uniqid(time(), true) . $_ext;
        }else{
            $fileName =$fileName . $_ext;
        }
        //开始下載
        ob_start();
        readfile($imgUrl);
        $imgInfo = ob_get_contents();
        ob_end_clean();
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        $fp = fopen($saveDir . $fileName, 'a');
        $imgLen = strlen($imgInfo);
        //计算图片源码大小
        $_inx = 1024;
        //每次写入1k
        $_time = ceil($imgLen / $_inx);
        for ($i = 0; $i < $_time; $i++) {
            fwrite($fp, substr($imgInfo, $i * $_inx, $_inx));
        }
        fclose($fp);

        return  $fileName;
    }


    /** curl抓取HTML
     * @param $url
     * @return mixed
     */
    public function setRequest($url)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'silent');
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);

        return $query;
    }

}

$test = new getImgUrl();
$get = $test -> getSrc("http://www.bee-ji.com");