<?php

require_once("OAuth.php");
require_once("TrivialOAuthDataStore.php");

function getLastOAuthBodyBaseString() {
    global $LastOAuthBodyBaseString;
    return $LastOAuthBodyBaseString;
}

function handleOAuthBodyPOST($oauth_consumer_key, $oauth_consumer_secret) {
    $request_headers = local_providerapi\OAuthUtil::get_headers();
    // print_r($request_headers);

    // Must reject application/x-www-form-urlencoded
    if ($request_headers['Content-type'] == 'application/x-www-form-urlencoded') {
        throw new Exception("OAuth request body signing must not use application/x-www-form-urlencoded");
    }

    if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
        $header_parameters = local_providerapi\OAuthUtil::split_header($request_headers['Authorization']);

        // echo("HEADER PARMS=\n");
        // print_r($header_parameters);
        $oauth_body_hash = $header_parameters['oauth_body_hash'];
        // echo("OBH=".$oauth_body_hash."\n");
    }

    if (!isset($oauth_body_hash)) {
        throw new Exception("OAuth request body signing requires oauth_body_hash body");
    }

    // Verify the message signature
    $store = new local_providerapi\TrivialOAuthDataStore();
    $store->add_consumer($oauth_consumer_key, $oauth_consumer_secret);

    $server = new local_providerapi\OAuthServer($store);

    $method = new local_providerapi\OAuthSignatureMethod_HMAC_SHA1();
    $server->add_signature_method($method);
    $request = local_providerapi\OAuthRequest::from_request();

    global $LastOAuthBodyBaseString;
    $LastOAuthBodyBaseString = $request->get_signature_base_string();
    // echo($LastOAuthBodyBaseString."\n");

    try {
        $server->verify_request($request);
    } catch (Exception $e) {
        $message = $e->getMessage();
        throw new Exception("OAuth signature failed: " . $message);
    }

    $postdata = file_get_contents('php://input');
    // echo($postdata);

    $hash = base64_encode(sha1($postdata, true));

    if ($hash != $oauth_body_hash) {
        throw new Exception("OAuth oauth_body_hash mismatch");
    }

    return $postdata;
}

function sendOAuthBodyPOST($method, $endpoint, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $body) {
    global $CFG;

    require_once($CFG->dirroot . '/lib/filelib.php');

    $hash = base64_encode(sha1($body, true));

    $parms = array('oauth_body_hash' => $hash);

    $test_token = '';
    $hmac_method = new local_providerapi\OAuthSignatureMethod_HMAC_SHA1();
    $test_consumer = new local_providerapi\OAuthConsumer($oauth_consumer_key, $oauth_consumer_secret, null);

    $acc_req = local_providerapi\OAuthRequest::from_consumer_and_token($test_consumer, $test_token, $method, $endpoint, $parms);
    $acc_req->sign_request($hmac_method, $test_consumer, $test_token);

    // Pass this back up "out of band" for debugging
    global $LastOAuthBodyBaseString;
    $LastOAuthBodyBaseString = $acc_req->get_signature_base_string();
    // echo($LastOAuthBodyBaseString."\m");

    $headers = array();
    $headers[] = $acc_req->to_header();
    $headers[] = "Content-type: " . $content_type;

    $curl = new curl();
    $curl->setHeader($headers);
    $response = $curl->post($endpoint, $body);

    return $response;
}

function sendOAuthParamsPOST($method, $endpoint, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $params) {

    if (is_array($params)) {
        $body = http_build_query($params, '', '&');
    } else {
        $body = $params;
    }

    $hash = base64_encode(sha1($body, true));

    $parms = $params;
    $parms['oauth_body_hash'] = $hash;

    $test_token = '';
    $hmac_method = new local_providerapi\OAuthSignatureMethod_HMAC_SHA1();
    $test_consumer = new local_providerapi\OAuthConsumer($oauth_consumer_key, $oauth_consumer_secret, null);

    $acc_req = local_providerapi\OAuthRequest::from_consumer_and_token($test_consumer, $test_token, $method, $endpoint, $parms);
    $acc_req->sign_request($hmac_method, $test_consumer, $test_token);

    // Pass this back up "out of band" for debugging
    global $LastOAuthBodyBaseString;
    $LastOAuthBodyBaseString = $acc_req->get_signature_base_string();
    // echo($LastOAuthBodyBaseString."\m");

    $header = $acc_req->to_header();
    $header = $header . "\r\nContent-type: " . $content_type . "\r\n";

    $params = array('http' => array(
            'method' => 'POST',
            'content' => $body,
            'header' => $header
    ));
    $ctx = stream_context_create($params);
    $fp = @fopen($endpoint, 'rb', false, $ctx);
    if (!$fp) {
        throw new \Exception("Problem with $endpoint, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) {
        throw new \Exception("Problem reading data from $endpoint, $php_errormsg");
    }
    return $response;
}

?>
