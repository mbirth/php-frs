<?php

require_once __DIR__ . '/vendor' . '/autoload.php';

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates/partials'),
    'charset' => 'utf-8',
    'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
));

$data = array();

$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);

session_start();

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Delete session and redirect to self
    #$client->setAccessToken($_SESSION['access_token']);
    #$client->revokeToken();   // removed granted permissions from account
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . $client->getRedirectUri());
    exit(0);
}

if (isset($_GET['code']) && $_GET['code']) {
    // Validate OAuth2 result, set access token and redirect to self
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . $client->getRedirectUri());
    exit(0);
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    // Authenticated
    $client->setAccessToken($_SESSION['access_token']);

    $data['auth_needed'] = false;

    $oauth = new Google_Service_Oauth2($client);
    $userdata = $oauth->userinfo->get();
    $data['user']['name_first'] = $userdata->givenName;
    $data['userdata'] = print_r($userdata, true);


    // TODO: Check $userdata->verifiedEmail and deny if not verified.

    $tpl = $m->loadTemplate('loggedin_html');
} else {
    // Not authenticated
    $data['auth_needed'] = true;
    $data['auth_url'] = $client->createAuthUrl();
    $tpl = $m->loadTemplate('index_html');
}

$data['action'] = $_GET['action'];
echo $tpl->render($data);
