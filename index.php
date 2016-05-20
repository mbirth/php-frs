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
$client->setRedirectUri('https://raspi.mbirth.de/dev/FRS/');
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);

$data['auth_url'] = $client->createAuthUrl();

$tpl = $m->loadTemplate('index_html');
echo $tpl->render($data);
