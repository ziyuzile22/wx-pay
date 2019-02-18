<?php
include './Base.php';

class WeiXinBack extends Base
{
    public function __construct()
    {
        parent::__construct();
        //接受微信服务器发送的数据
        $data = $this->getPost();
        //把xml转换为数组
        $arr = $this->XmlToArr($data);
        //记录数据到日志中
        //$this->logs('log.txt', $arr);
        //验证签名
        if ($this->checkSign($arr)) {
            //调用统一下单api
            $params = [
                'appid'            => self::APPID,
                'mch_id'           => self::MCHID,
                'nonce_str'        => md5(time()),
                'body'             => '扫码支付模式一',
                'out_trade_no'     =>  $arr['product_id'],  //订单号需要每次都不同，可以通过规则随机生成，支付过一个之后呢，再支付就不可以了，
                'total_fee'        => 2,  //分
                'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
                'notify_url'       => self::NOTIFY,
                'trade_type'       => 'NATIVE',
                'product_id'       => $arr['product_id'],
            ];
            // 调用统一下单的方法
            $arr = $this->unifiedorder($params);
            $return_params = [
                'return_code' => 'SUCCESS',
                'appid'       => self::APPID,
                'mch_id'      => self::MCHID,
                'nonce_str'   => md5(time()),
                'prepay_id'   => $arr['prepay_id'],
                'result_code' => 'SUCCESS',
            ];
            // 加上 sign 签名
            $return_params = $this->setSign($return_params);
            // 把数组转为 xml
            $return_xml = $this->ArrToXml($return_params);
            echo $return_xml;
            //获取到 prepay_id
            //返回 prepay_id 等数据
            $this->logs('log.txt', '1');
        } else {
            $this->logs('log.txt', '签名校验失败！');
        }
    }

}

new WeiXinBack();
