<?php namespace Plus\Socialite;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Plus\Socialite\Components\Socialite'       => 'socialite',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => '第三方登录',
                'description' => '第三方授权登录配置',
                'category' => 'Socialite',
                'icon' => 'oc-icon-user-secret',
                'class' => 'Plus\Socialite\Models\Settings',
                'order' => 500,
            ]
        ];
    }
}
