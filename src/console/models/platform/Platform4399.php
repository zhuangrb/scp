<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class Platform4399 extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $param_list = explode('|', $newParam['p'] ?? '');
        $pay_num = array_shift($param_list);
        $pay_to_user = array_shift($param_list);
        $pay_gold = array_shift($param_list);
        $time = array_shift($param_list);
        $flag = array_shift($param_list);
        $pay_rmb = array_shift($param_list);
        $channel = array_shift($param_list);

        $pay_data = array(
            'uid' => $pay_to_user ?: null,
            'platform' => '4399',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['serverid']  ?? 0),
            'time' => $time ?: null,
            'order_id' => $pay_num ?: null,
            'coins' => $pay_gold ?: 0,
            'money' => $pay_rmb ?: 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $back_url = 'http://my.4399.com/yxtlzj/';
        $login_type = 'web';
        if (isset($newParam['client']) && $newParam['client'] == 1) {
            $login_type = 'pc';
        }
        $login_data = array(
            'uid' => $newParam['username'] ?? null,
            'platform' => '4399',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['serverid']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'is_adult' => $newParam['cm'] ?? UserIsAdult::OTHER,
            'back_url' => urldecode($back_url),
            'type' => $login_type,
            'sign' => strtolower($newParam['flag'] ?? ''),
        );

        return $login_data;
    }
}
