<?php

namespace Frs;

class SessionManager
{
    private $client;
    private $googleAuthValid;

    public function __construct()
    {
        $this->googleAuthValid = false;
        $this->client = new \Google_Client();
        $this->client->setAuthConfigFile('client_secret.json');
        $this->client->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);

        session_start();
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authAndRedirect($authCode)
    {
        // Validate OAuth2 result, set access token and redirect to self
        $this->client->authenticate($authCode);
        $_SESSION['access_token'] = $this->client->getAccessToken();
        $this->redirectAndExit();
    }

    public function logoutAndRedirect()
    {
        // Delete session and redirect to self
        #$this->client->setAccessToken($_SESSION['access_token']);
        #$this->client->revokeToken();   // removed granted permissions from account
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirectAndExit();
    }

    private function redirectAndExit()
    {
        header('Location: ' . $this->client->getRedirectUri());
        exit(0);
    }

    public function storeFormData($form_type)
    {
        $skey = 'form_' . $form_type;
        $_SESSION[$skey] = $_POST;
    }

    public function hasSessionToken()
    {
        return (isset($_SESSION['access_token']) && $_SESSION['access_token']);
    }

    public function verifySession()
    {
        $this->client->setAccessToken($_SESSION['access_token']);
        if ($this->client->isAccessTokenExpired()) {
            throw new \Exception('Token expired. <a href="' . $this->getAuthUrl() . '">Request new one</a>.');
        }

        $this->googleAuthValid = true;
    }

    public function getUserinfo()
    {
        if (!$this->googleAuthValid) {
            return array();
        }
        $oauth = new \Google_Service_Oauth2($this->client);
        $userdata = $oauth->userinfo->get();

        $result = array(
            'name_first' => $userdata->givenName,
            'name_last'  => $userdata->familyName,
            'name'       => $userdata->name,
            'picture'    => $userdata->picture,
            'email'      => $userdata->email,
            'gender'     => $userdata->gender,
            'verifiedEmail' => $userdata->verifiedEmail,
        );
        return $result;
    }
}
