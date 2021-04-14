<?php namespace Plus\Socialite\Models;

use Model;

/**
 * Model
 */
class Settings extends Model
{


    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'socialite_settings';
    public $settingsFields = 'fields.yaml';

    public $attachOne = [
        'cover_feishu' => 'System\Models\File',
        'cover_github' => 'System\Models\File',
        'cover_alipaydev' => 'System\Models\File',
        'cover_xiaomi' => 'System\Models\File',
        'cover_gitee' => 'System\Models\File',
        'cover_gitlab' => 'System\Models\File',
        'cover_wechat' => 'System\Models\File',
    ];
}
