<?php

namespace SocialiteProviders\PayPing;

use Illuminate\Http\Request;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'PAYPING';
    private $verifier;

    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [])
    {
        $this->verifier = $this->generateVerified();
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
                'https://oauth.payping.ir/connect/authorize', $state
            ) . '&code_challenge=' . $this->generateCodeChallenge($this->verifier) . '&code_challenge_method=S256';
    }


    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://oauth.payping.ir/connect/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://oauth.payping.ir/connect/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true)['account'];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['uuid'],
            'nickname' => $user['username'],
            'name' => $user['firstname'] . ' ' . $user['lastname'],
            'email' => $user['email'],
            'avatar' => $user['profilepicture'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
            'code_verifier'=> $this->verifier,
        ]);
    }


    private function generateVerified()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(32));
        $verifier = $this->base64UrlSafeEncode(pack('H*', $random));
        return $verifier;
    }

    /**
     * ساخت یک challenge code
     * @param $codeVerifier
     * @return string
     */
    private function generateCodeChallenge($codeVerifier)
    {
        return $this->base64UrlSafeEncode(pack('H*', hash('sha256', $codeVerifier)));
    }

    /**
     * escape رشته
     * @param $string
     * @return string
     */
    private function base64UrlSafeEncode($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}
