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

$tpl_done = false;

// route pages that work with and without login
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'faq':
            $tpl = $m->loadTemplate('faq_html');
            $tpl_done = true;
            break;
    }
}

if (!$tpl_done && isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    // Authenticated
    try {
        $created = $_SESSION['access_token']['created'];
        $expires = $_SESSION['access_token']['expires_in'];
        $expire_stamp = intval($created) + intval($expires);
        $data['session_created'] = $created;
        $data['session_expires'] = $expires;
        $data['session_time_left'] = ($expire_stamp) - time();
        $client->setAccessToken($_SESSION['access_token']);
    } catch (Exception $e) {
        print_r($e);
        $_SESSION['access_token'] = $client->refreshToken(null);
        print_r($_SESSION['access_token']);
    }

    $data['auth_needed'] = false;

    try {
        $oauth = new Google_Service_Oauth2($client);
        $userdata = $oauth->userinfo->get();
    } catch (Exception $e) {
        print_r($e);
        die();
    }
    $data['user'] = array(
        'name_first' => $userdata->givenName,
        'name_last'  => $userdata->familyName,
        'name'       => $userdata->name,
        'picture'    => $userdata->picture,
        'email'      => $userdata->email,
        'gender'     => $userdata->gender,
    );

    // Check $userdata->verifiedEmail and deny if not verified.
    if (!$userdata->verifiedEmail) {
        $tpl = $m->loadTemplate('notverified_html');
        $tpl_done = true;
    }

    switch ($_GET['action']) {
        case 'faq':
            $tpl = $m->loadTemplate('faq_html');
            break;
        default:
            if (!$tpl_done) {
                $tpl = $m->loadTemplate('loggedin_html');
                $tpl_done = true;
            }
            break;
    }
} elseif (!$tpl_done) {
    // Not authenticated
    $data['auth_needed'] = true;
    $data['auth_url'] = $client->createAuthUrl();
    $tpl = $m->loadTemplate('index_html');
}

$data['action'] = $_GET['action'];
echo $tpl->render($data);
