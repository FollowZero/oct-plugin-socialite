<?php namespace Plus\Socialite\Classes;

/**
 * @author 郑州最帅的php程序员
 * @author_blog blog.fplus.top
 */

use Auth;
use Event;
use Plus\Socialite\Models\FollowModel;
use Plus\Socialite\Models\Settings;
use URL;
use Log;
use Session;
use ApplicationException;

class AuthHelper {

    public function initUser($social_user,$profile){
        $settings = Settings::instance();
        $raw=$social_user->getRaw();
        //v2这里有一个唯一标识id $social_user->getId(); v3暂时没发现，先用raw的open_id
        $uuid=$social_user->getId();
        if(!$uuid){
            throw new ApplicationException('参数错误.未获取第三方唯一用户id');
        }
        $provider=strtolower($profile);
        $follow=FollowModel::where('uuid',$uuid)->where('provider',$provider)->first();
        if(!$follow){
            $follow=new FollowModel();
            $follow->uuid=$uuid;
            $follow->provider=$provider;
            $follow->nickname=$social_user->getNickname();
            $follow->name=$social_user->getName();
            $follow->email=$social_user->getEmail();
            $follow->avatar=$social_user->getAvatar();
            $follow->raw=$raw;
            $follow->token_response=$social_user->getTokenResponse();
            $follow->save();
        }
        $follow_user_id=$follow->user_id??0;
        //判断是否绑定
        //判断是否登录
        $user=$this->user();
        if($follow_user_id>0){//已绑定
            if($user){//已登录
                //判断登录和绑定的用户是否一致
                if($follow_user_id!=$user->id){
                    throw new ApplicationException('参数错误.');
                }else{
                    return redirect(URL::to('/'));
                }
            }else{//未登录-去登录
                Auth::loginUsingId($follow_user_id, true);
                if(empty($settings->login_redirection_url)){
                    return redirect(URL::to('/'));
                }else {
                    return redirect($settings->login_redirection_url);
                }
            }
        }else{//未绑定
            if($user){//已登录-此处的场景是个人中心的社交账号绑定
                $follow->user_id=$user->id;
                $follow->save();
                if(empty($settings->user_bind_url)){
                    return redirect(URL::to('/'));
                }else {
                    return redirect($settings->user_bind_url);
                }
            }else{//未登录-此场景是首次登录绑定
                Session::put('social_follow_id', $follow->id);
                if(empty($settings->auth_bind_url)){
                    throw new ApplicationException('参数错误.未设定绑定页面');
                }else {
                    return redirect($settings->auth_bind_url);
                }
            }
        }

    }

    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

}