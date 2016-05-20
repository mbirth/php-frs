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

if (isset($_GET['code']) && $_GET['code']) {
    // OAuth2 result
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
    $data['userdata'] = print_r($userdata, true);
} else {
    // Not authenticated
    $data['auth_needed'] = true;
    $data['auth_url'] = $client->createAuthUrl();
}

$tpl = $m->loadTemplate('index_html');
echo $tpl->render($data);
