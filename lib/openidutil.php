<?php

namespace OCA\OpenIdConnect;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Namshi\JOSE\JWS;
use Namshi\JOSE\SimpleJWS;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Namshi\JOSE\Base64\Encoder;

set_include_path ( get_include_path () . PATH_SEPARATOR . \OC_App::getAppPath ( 'openidconnect' ));

require_once 'vendor/autoload.php';


class OpenIdUtil {
    /**
     *
     */
    public function getEndPoint($rtn_array=false){
        $options = array(
                'http' => array(
                        'header'  => '',
                        'method'  => 'GET',
                        'content' => ''
                ));
        $context = stream_context_create($options);
        $result = file_get_contents(WELL_KNOWN_URL, false, $context);
        $u= json_decode($result, $rtn_array);
        return $u; //object
    }
    
    public function getAccessToken($token_ep='' ,$code='', $redir_uri='' ,$rtn_array=false){
        $hash = base64_encode( CLIENT_ID . ":" . CLIENT_SECRET);
        $data = array('grant_type' => 'authorization_code', 'code'=> $code,
                'redirect_uri' => $redir_uri);
        $header= array( "Content-type: application/x-www-form-urlencoded",
                "Authorization: Basic $hash" ) ;
        $options = array(
                'http' => array(
                        'header'  => $header,
                        'method'  => 'POST',
                        'content' => http_build_query($data)
                ));
        $context = stream_context_create($options);
        $result = file_get_contents($token_ep, false, $context);
        $j= json_decode($result, $rtn_array);
        return $j;
    }
    public function getModnExp($jwks_uri){
        $options = array(
                'http' => array(
                        'header'  => '',
                        'method'  => 'GET',
                        'content' => ''
                ));
        $context = stream_context_create($options);
        $result = file_get_contents($jwks_uri, false, $context);
        $u= json_decode($result, true);
        return $u; //object
    }
    
    public function getUserinfo($token_ep='' ,$accesstoken='',$rtn_array=false){
        $header= array( "Authorization: Bearer $accesstoken" );
        $options = array(
                'http' => array(
                        'header'  => $header,
                        'method'  => 'GET',
                        'content' => ''
                ));
        $context = stream_context_create($options);
        $result = file_get_contents($token_ep, false, $context);
        $u= json_decode($result,$rtn_array);
        return $u;
    }
    public function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    
    public function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    
    public static function getRSA(){
        return new RSA();
    }
    
    public static function getNx($nKey,$rsaBit){
        return new BigInteger($nKey, 256);
    }
    
    public static function getEx($eKey,$rsaBit){
        return new BigInteger($eKey, 256);
    }
    
    public static function getJWS($jwsArray,$mode){
        return new JWS($jwsArray,$mode);
    }
}

