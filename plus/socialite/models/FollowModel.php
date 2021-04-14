<?php namespace Plus\Socialite\Models;

use Model;

/**
 * Model
 */
class FollowModel extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'plus_socialite_follow';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Options array
     */
    protected $jsonable = [
        'raw',
        'token_response'
    ];

}
