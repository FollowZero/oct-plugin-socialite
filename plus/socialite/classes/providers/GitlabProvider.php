<?php
namespace Plus\Socialite\Classes\Providers;

use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\User;

class GitlabProvider extends Base
{
    public const NAME = 'gitlab';
    protected string $baseUrl = 'https://gitlab.com';
    protected array $scopes = ['read_user '];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/token';
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * @param  string  $token
     *
     * @return array
     * @throws \Overtrue\Socialite\Exceptions\InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = $this->baseUrl . '/api/v4/user';

        $response = $this->getHttpClient()->get(
            $userUrl,
            [
                'query'=>[
                    'access_token' => $token,
                ]
            ]
        );
        $user = json_decode($response->getBody(), true);
        return $user;
    }

    /**
     * @param array $user
     *
     * @return \Overtrue\Socialite\User
     */
    protected function mapUserToObject(array $user): User
    {
        return new User([
            'id' => $user['id'] ?? null,
            'nickname' => $user['username'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['avatar_url'] ?? null,
        ]);
    }




}
