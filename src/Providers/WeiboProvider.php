<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Weibo provider for Overtrue/socialite.
 *
 * @author overtrue <i@overtrue.me>
 */

namespace Overtrue\Socialite\Providers;

use Overtrue\Socialite\AccessTokenInterface;
use Overtrue\Socialite\ProviderInterface;
use Overtrue\Socialite\User;

/**
 * Class WeiboProvider.
 */
class WeiboProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base url of Weibo API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weibo.com';

    /**
     * The API version for the request.
     *
     * @var string
     */
    protected $version = '2';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

    /**
     * The uid of user authorized.
     *
     * @var int
     */
    protected $uid;

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'/'.$this->version.'/oauth2/access_token';
    }

    /**
     * Get the Post fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param \Overtrue\Socialite\AccessTokenInterface $token
     *
     * @return array
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/'.$this->version.'/users/show.json', [
            'query' => [
                'uid'          => $token['uid'],
                'access_token' => $token->getToken(),
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return \Overtrue\Socialite\User
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id'       => $user['id'],
            'nickname' => $user['name'],
            'name'     => $user['name'],
            'email'    => isset($user['email']) ? $user['email'] : null,
            'avatar'   => isset($user['avatar_large']) ? $user['avatar_large'] : null,
        ]);
    }
}
