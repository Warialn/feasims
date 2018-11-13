<?php

namespace Home\Model;


use Common\Model\Model;

class Policy extends Model
{
    public static function iplib_col()
    {
    	$c = [
            '名称'         => 'name',
            '起始IP地址'   => 'begin_ip',
            '终止IP地址'   => 'end_ip',
            'IP地址段类型' => 'ip_type',
            '描述'         => 'discription',
    	];

    	return $c;
    }
    public static function famous_col()
    {
        $c = [
            '网站名称' => 'popularweb_name',
            '域名/URL'     => 'popularweb_url',
            '监测标识' => 'monitor_flag',
        ];

        return $c;
    }
    public static function domain_col()
    {
        $c = [
            '域名'     => 'host',
            '用户名'   => 'username',
            '密码'     => 'password',
            '备注'     => 'remark',
            '监测标识' => 'monitor_flag',
        ];

        return $c;
    }
     public static function col2value($key,$value)
    {
        $c = [
            '机房' => function($v){
                return self::col2local_roomname($v);
            }
        ];
        $c = [
            '密码' => function($v){
                return md5($v);
            }
        ];
        return isset($c[$key]) ? $c[$key]($value) : $value;
    }
}
