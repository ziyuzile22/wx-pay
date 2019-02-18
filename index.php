<?php

include './Base.php';
include './phpqrcode/phpqrcode.php';

class WeiXinPay extends Base
{
    public function check()
    {
        // appid=323213243&body=xxxxxxxxxx&mch_id=ewr3434&sign=54A9196FDE33A95E408E900D2834D468
        $params = [
            'appid'  => '323213243',
            'mch_id' => 'ewr3434',
            'body'   => 'xxxxxxxxxx',
            'sign'   => '54A9196FDE33A95E408E900D2834D468',
        ];
        if ($this->checkSign($params)) {
            echo '签名校验通过！';
        } else {
            echo '签名校验失败！';
        }
    }

    public function getQRurl($oid)
    {
        $params = [
            'appid'      => self::APPID,
            'mch_id'     => self::MCHID,
            'nonce_str'  => md5(time()),
            'product_id' => $oid,
            'time_stamp' => time(),
        ];
        $data = $this->setSign($params);
        return 'weixin://wxpay/bizpayurl?' . $this->arrToUrl($data);
    }

}

$obj = new WeiXinPay();
if(isset($_GET['pid'])){
    QRcode::png($obj->getQRurl($_GET['pid']));
    $obj->logs('log.txt', '0');
}
// echo $obj->getQRurl('1234');
