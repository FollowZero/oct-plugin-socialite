<?php namespace Plus\Socialite\Components;

use October\Rain\Auth\AuthException;
use Plus\Socialite\Models\FollowModel;
use Plus\Socialite\Models\Settings;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User as UserModel;
use URL;
use Auth;
use Route;
use Flash;
use Event;
use Request;
use Session;
use Validator;
use ValidationException;
use ApplicationException;

class Socialite extends ComponentBase
{

	public function componentDetails()
	{
		return [
			'name'        => '第三方登录',
			'description' => '第三方登录信息'
		];
	}

	/**
	 * Executed when this component is bound to a page or layout.
	 */
	public function onRun()
	{
	    //登录页和用户账号绑定页的第三方登录链接
		$social_links = $this->get_social_links();
		$this->page['social_links'] = $social_links;
		//是否可以注册绑定
        $can_bind_register=Settings::instance()->can_bind_register;
        $this->page['can_bind_register'] = $can_bind_register;
        //用户 登录|注册 绑定相关
        $follow_id=Session::get('social_follow_id');
        if($follow_id){
            $follow=FollowModel::where('id',$follow_id)->first();
            if(!$follow->user_id){
                $this->page['social_follow'] = $follow;
                $this->page['social_follow_id'] = $follow_id;
            }
        }
	}

    /**
     * 解除绑定
     */
	public function onUnbind(){
        try {
            $data=post();
            $provider=$data['provider'];
            $user=$this->user();
            if(!$user){
                throw new ApplicationException('登录失效.');
            }
            if(!$provider){
                throw new ApplicationException('参数错误');
            }
            $follow=FollowModel::where('user_id',$user->id)->where('provider',$provider)->first();
            if(!$follow){
                throw new ApplicationException('参数错误!');
            }
            $follow->user_id=0;
            $follow->save();
            Flash::success('操作成功');
            $social_links = $this->get_social_links();
            return [
                '#user_bind' => $this->renderPartial('socialite::user_bind',['social_links'=>$social_links])
            ];
        }
        catch (Exception $ex){
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }


    public function onBindLogin(){
        try {
            $settings = Settings::instance();
            $data = post();
            $rules = [];
            $rules['follow_id']='required';
            // zpxg login_userphone
            if($this->loginAttribute() == UserSettings::LOGIN_EMAIL){
                $rules['login']='required|email|between:6,255';
                $field='email';
            }
            if($this->loginAttribute() == UserSettings::LOGIN_USERNAME){
                $rules['login']='required|between:2,255';
                $field='username';
            }
//            if($this->loginAttribute() == UserSettings::LOGIN_USERPHONE){
//                $rules['login']='required|regex:/^[1][3-9][0-9]{9}$/';
//                $field='userphone';
//            }
            $rules['password'] = 'required|between:4,255';

            if (!array_key_exists('login', $data)) {
                $data['login'] = post('username', post('email'));
            }
            $data['login'] = trim($data['login']);

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }
            $follow_id=$data['follow_id'];
            $follow=FollowModel::where('id',$follow_id)->first();
            if(!$follow){
                throw new ApplicationException('参数错误');
            }
            if($follow->usre_id){
                throw new ApplicationException('参数错误！！');//已经绑定用户，致敬微信公众号开发，就返参数错误
            }

            //限制只有已注册会员才能绑定
            /*
             * Authenticate user
             */
            $credentials = [
                'login'    => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ];
            $user = Auth::authenticate($credentials);
            if ($user->isBanned()) {
                Auth::logout();
                throw new AuthException(/*Sorry, this user is currently not activated. Please contact us for further assistance.*/'rainlab.user::lang.account.banned');
            }
            //绑定
            $follow->user_id=$user->id;
            $follow->save();
            //清除social_follow_id
            Session::forget('social_follow_id');
            if(empty($settings->login_redirection_url)){
                return redirect(URL::to('/'));
            }else {
                return redirect($settings->login_redirection_url);
            }
        }
        catch (Exception $ex){
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }

    }



    /**
     * Register the user
     */
    public function onBindRegister()
    {
        try {
            $settings = Settings::instance();
            if (!$settings->can_bind_register) {
                throw new ApplicationException('暂不支持，请先注册再绑定');
            }
            /*
             * Validate input
             */
            $data = post();

            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            $rules = (new UserModel)->rules;

            // zpxg login_userphone
            if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
                unset($rules['email']);
            }
//            if ($this->loginAttribute() == UserSettings::LOGIN_USERPHONE) {
//                unset($rules['email']);
//                unset($rules['username']);
//            }
            if ($this->loginAttribute() !== UserSettings::LOGIN_USERNAME) {
                unset($rules['username']);
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Record IP address
             */
            if ($ipAddress = Request::ip()) {
                $data['created_ip_address'] = $data['last_ip_address'] = $ipAddress;
            }

            /*
             * Register user
             */
            Event::fire('rainlab.user.beforeRegister', [&$data]);

            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $adminActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_ADMIN;
            $user = Auth::register($data, $automaticActivation);

            Event::fire('rainlab.user.register', [$user, $data]);


            $follow_id=$data['follow_id'];
            $follow=FollowModel::where('id',$follow_id)->first();
            if(!$follow){
                throw new ApplicationException('参数错误');
            }
            if($follow->usre_id){
                throw new ApplicationException('参数错误！！');
            }
            //绑定
            $follow->user_id=$user->id;
            $follow->save();
            //清除social_follow_id
            Session::forget('social_follow_id');


            /*
             * Activation is by the user, send the email
             */
            if ($userActivation) {
                $this->sendActivationEmail($user);

                Flash::success(Lang::get(/*An activation email has been sent to your email address.*/'rainlab.user::lang.account.activation_email_sent'));
            }

            /*
             * Activation is by the admin, show message
             * For automatic email on account activation RainLab.Notify plugin is needed
             */
            if ($adminActivation) {
                Flash::success(Lang::get(/*You have successfully registered. Your account is not yet active and must be approved by an administrator.*/'rainlab.user::lang.account.activation_by_admin'));
            }

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }

            if(empty($settings->login_redirection_url)){
                return redirect(URL::to('/'));
            }else {
                return redirect($settings->login_redirection_url);
            }
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }



    /**
     * Returns the login model attribute.
     */
    public function loginAttribute()
    {
        return UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

    /**
     * Flag for allowing registration, pulled from UserSettings
     */
    public function canBindRegister()
    {
        return Settings::get('can_bind_register', false);
    }

    /**
     * 获取第三方登录链接
     * @return array
     */
    public function get_social_links(){
        $settings = Settings::instance();
        $providers = $settings->providers;
        $social_links = [];
        //用户绑定
        $user_had=[];
        $user=$this->user();
        if($user){
            $user_had=FollowModel::where('user_id',$user->id)->lists('provider');
        }
        foreach ( $providers as $provider_class => $provider_details ){
            if ( $provider_details['enabled']==1 ){
                $social_links[$provider_class]['url'] = url('socialite/authorize',[$provider_class]);
                $social_links[$provider_class]['title'] = $provider_details['title'];
                if(in_array($provider_class,$user_had)){
                    $social_links[$provider_class]['status']=1;
                }else{
                    $social_links[$provider_class]['status']=-1;
                }
                $cover_field='cover_'.$provider_class;
                $social_links[$provider_class]['cover_path']=$settings->$cover_field->path??'';
            }
        }
	    return $social_links;
    }

    /**
     * Returns the logged in user, if available
     */
    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }
}