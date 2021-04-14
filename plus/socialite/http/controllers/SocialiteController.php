<?php

namespace Plus\Socialite\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Overtrue\Socialite\SocialiteManager;
use Plus\Socialite\Classes\AuthHelper;
use Plus\Socialite\Classes\SocialHelper;
use Auth;
use Plus\Socialite\Models\Settings;
use Session;

class SocialiteController extends Controller
{

    public function authorize($profile = 'default')
    {

        $config=SocialHelper::getProfileConfigs($profile);
        $socialite = new SocialiteManager($config);

        if($config['is_extend']){
            $nameCustomProvider='Plus\Socialite\Classes\Providers\\'.ucfirst($profile).'Provider';
            $socialite->extend($profile, function(array $config)use($nameCustomProvider){
                return new $nameCustomProvider($config);
            });
        }

        $url  = $socialite->create($profile)->redirect();
        return redirect($url);
    }

    public function callback($profile = 'default')
    {

        $config=SocialHelper::getProfileConfigs($profile);

        $socialite = new SocialiteManager($config);

        if($config['is_extend']){
            $nameCustomProvider='Plus\Socialite\Classes\Providers\\'.ucfirst($profile).'Provider';
            $socialite->extend($profile, function(array $config)use($nameCustomProvider){
                return new $nameCustomProvider($config);
            });
        }
//        dd($config);
        $code = request()->query('code')??request()->query('auth_code');
        $user  = $socialite->create($profile)->userFromCode($code);
//        Log::info('aa',$user->toArray());
        $authHelper=new AuthHelper();
        return $authHelper->initUser($user,$profile);

    }

    public function test()
    {
        Session::put('key', 'default');
        $data = Session::all();
//        $data = Session::get('key');
        dd($data);
        $settings = Settings::instance();
        return redirect($settings->bind_url);
    }



}
