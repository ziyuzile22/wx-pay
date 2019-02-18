<?php
$abcd = 1;

class Base
{
    const KEY    = 'b9840976e63916e233d1d380549514a3';
    const APPID  = 'wxfc9a296ef77b9b95';
    const MCHID  = '1524706301';
    const SECRET = 'd556a3f26acbd70499024921c7404243';
    //统一下单 微信提供的接口地址
    const UOURL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    //支付成功通知url
    const NOTIFY = 'http://wap.fengea.net/pay/notify.php';

    public function __construct()
    {

    }

    //获取签名
    public function getSign($arr)
    {
        //去除空值
        array_filter($arr);
        //去除为 sign 的值
        if (isset($arr['sign'])) {
            unset($arr['sign']);
        }
        //排序
        ksort($arr);

        //组装字符
        $str = $this->arrToUrl($arr) . '&key=' . self::KEY;

        //使用md5加密
        $md5str = md5($str);

        //转换成大写
        return strtoupper($md5str);
    }

    //获取带签名的数组
    public function setSign($arr)
    {
        $arr['sign'] = $this->getSign($arr);

        return $arr;
    }

    //校验签名
    public function checkSign($arr)
    {
        //生成新签名
        $sign = $this->getSign($arr);
        //和数组中原始签名比较
        if ($sign == $arr['sign']) {
            return true;
        } else {
            return false;
        }
    }

    //数组转url字符串 不带 key
    public function arrToUrl($arr)
    {
        //预防 http_build_query 转义
        return urldecode(http_build_query($arr));
    }

    //记录日志到文件
    public function logs($file, $data)
    {
        $data = is_array($data) ? print_r($data, true) : $data;
        file_put_contents('./logs/' . $file, $data);
    }

    //获取微信服务器发送来的信息
    public function getPost()
    {
        return file_get_contents('php://input');
    }

    //Xml转数组
    public function XmlToArr($xml)
    {
        if ($xml == '') {
            return '';
        }

        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    //数组转为Xml
    public function ArrToXml($arr)
    {
        if (!is_array($arr) || count($arr) == 0) {
            return '';
        }

        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //post 字符串到接口
    public function postStr($url, $postfields)
    {
        $ch      = curl_init();
        $headers = [
            //"Content-Type:text/html;charset=UTF-8", "Connection: Keep-Alive"
        ];
        $params[CURLOPT_HTTPHEADER]     = $headers; //自定义header
        $params[CURLOPT_URL]            = $url; //请求url地址
        $params[CURLOPT_HEADER]         = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST]           = true;
        $params[CURLOPT_POSTFIELDS]     = $postfields;
        $params[CURLOPT_SSL_VERIFYPEER] = false; //禁用证书校验
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        return $content; //输出登录结果
    }

    //统一下单
    public function unifiedorder($params)
    {
        //获取到带签名的数组
        $params = $this->setSign($params);
        //将数组转为xml
        $xml = $this->ArrToXml($params);
        //把 xml 发送到微信提供的统一下单API地址, 返回的 data 也是 xml格式的数据
        $data = $this->postStr(self::UOURL, $xml);
        //把 $data 转换为 数组
        $arr = $this->XmlToArr($data);
        if ($arr['result_code'] == 'SUCCESS' && $arr['return_code'] == 'SUCCESS') {
            return $arr;
        } else {
            $this->logs('error.txt', $data);
            return false;
        }
    }

}
