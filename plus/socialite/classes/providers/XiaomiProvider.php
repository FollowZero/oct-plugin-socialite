<?php
namespace Plus\Socialite\Classes\Providers;



use Overtrue\Socialite\Contracts\ProviderInterface;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\User;

class XiaomiProvider extends Base
{
    public const NAME = 'xiaomi';
    protected string $baseUrl = 'https://account.xiaomi.com';
    protected string $openUrl = 'https://open.account.xiaomi.com';
    protected array $scopes = [1,3,4,6];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth2/token';
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

    //有些平台用code获取token这一步是get提交的
    /**
     * @param  string  $code
     * @return array
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);
        //小米返回的数据带有'&&&START&&&'直接去掉
        $res=str_replace('&&&START&&&','',$response->getBody()->getContents());
        return $this->normalizeAccessTokenResponse($res);
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
        $userUrl = $this->openUrl . '/user/profile';

        $response = $this->getHttpClient()->get(
            $userUrl,
            [
                'query' => [
                    'token' => $token,
                    'clientId' => $this->getClientId(),
                ],
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
        //小米获取raw多了一层 data
        return new User(
            [
                'id' => $user['data']['unionId'] ?? null,
                'name' => $user['data']['miliaoNick'] ?? null,
                'avatar' => $user['data']['miliaoIcon'] ?? null,
                'email' => $user['data']['email'] ?? null,
            ]
        );
    }




}
