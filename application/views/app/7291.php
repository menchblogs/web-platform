<?php

$client_id = get_domain_setting(14881);
$client_secret = get_domain_setting(14882);
$server_name = get_server('SERVER_NAME');

use Auth0\SDK\Auth0;

if($client_id && $client_secret && $server_name){

    //This page is loaded after member successfully authenticates via Auth0
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    require 'vendor/autoload.php';

    $auth0 = new Auth0\SDK\Auth0([
        'domain' => 'mench.auth0.com',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => 'https://'.$server_name.'/-14564',
        'scope' => 'openid profile email',
    ]);

    $auth0->logout();
    session_delete();


    echo '<div class="center-info">';
    echo '<div class="text-center platform-large">'.get_domain('m__cover').'</div>';
    echo '<p style="margin-top:13px; text-align: center;">'.view_shuffle_message(12694).'</p>';
    echo '</div>';

    /*



    js_redirect('/', 1597);
    */


    header('Location: ' . sprintf('http://%s/v2/logout?client_id=%s&returnTo=%s', 'mench.auth0.com', $client_id, 'https://'.$server_name));


} else {

    echo 'Going to home...';

    js_redirect('/', 13);

}


?>