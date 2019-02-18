<?php
include './Base.php';

class Notify extends Base
{
    public function __construct()
    {
        parent::__construct();
        //获取数据 服务器提交过来的通知数据 xml
        $xml = $this->getPost();
        //将 xml 格式的数据转换为 数组
        $arr = $this->XmlToArr($xml);

        $this->logs('log.txt', $arr);
        
        //验证签名
        if ($this->checkSign($arr)) {
            //验证订单金额
            if ($this->checkPrice($arr)) {
                //更新订单状态,生产环境中，是更新数据表，这里记录到日志
                $this->logs('log.txt', '2');
                // 定义一个数组转为xml告诉微信服务器，已经支付成功
                $params = [
                    'return_code' => 'SUCCESS',
                    'return_msg'  => 'OK',
                ];
                echo $this->ArrToXml($params);
            }
            //根据商户订单号 $arr['out_trade_no'] 在商户系统内查询订单金额 并和微信返回来的 订单金额$arr['total_fee']做比较
            //更新订单状态
        }
    }

    //验证订单金额
    //根据商户订单号 $arr['out_trade_no'] 在商户系统内查询订单金额 并和微信返回来的 订单金额$arr['total_fee']做比较
    public function checkPrice($arr)
    {
        if ($arr['return_code'] == 'SUCCESS' && $arr['result_code'] == 'SUCCESS') {
            if ($arr['total_fee'] == 2) {
                //这里直接写了2分钱，是因为前面用的是测试的2分钱
                //生产环境需要根据订单号在数据库中查询金额
                return true;
            } else {
                $this->logs('log.txt', '订单金额不匹配!微信支付系统提交过来的金额为：' . $arr['total_fee']);
            }
        } else {
            $this->logs('log.txt', '通知状态有误！');
            return false;
        }
    }

}

new Notify();
