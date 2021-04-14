<?php namespace Plus\Socialite\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Route;
use Illuminate\Support\Facades\Auth;

class Follow extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
    }

    public function test(){
        dd(Auth::getUser());
    }
}
