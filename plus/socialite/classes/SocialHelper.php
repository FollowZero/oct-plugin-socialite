<?php namespace Plus\Socialite\Classes;

/**
 * @author 郑州最帅的php程序员
 * @author_blog blog.fplus.top
 */


use Plus\Socialite\Models\Settings;

class SocialHelper {

    public static function getProfileConfigs($profileName) {
        $settings = Settings::instance();
        $is_extend=true;//是否扩展自定义提供者
        //已经支持的服务,本来支持飞书的，但v3的mapUserToObject方法，id获取写的是user_id,不是open_id.复制修改自定义一个
        $had_providers=['alipay','baidu','dingtalk','douban','douyin','facebook','github','google','linkedin','outlook','qcloud','qq','taobao','tapd','wechat','weibo','wework'];
        if(in_array($profileName,$had_providers)){
            $is_extend=false;
        }
        $config = [
            $profileName => [
                'client_id' => $settings->providers[$profileName]['client_id'],
                'client_secret' => $settings->providers[$profileName]['client_secret'],
                'redirect' => url('socialite/callback',[$profileName]),
            ],
            'is_extend' => $is_extend,
        ];
        if($profileName=='feishu'){
            $config[$profileName]['app_mode']='internal';
//            $config[$profileName]['app_ticket']=$settings->providers[$profileName]['app_ticket'];
        }
        if(in_array($profileName,['alipay','alipaydev'])){
            $config[$profileName]['rsa_private_key']=$settings->providers[$profileName]['client_secret'];
        }


        return $config;
    }
}