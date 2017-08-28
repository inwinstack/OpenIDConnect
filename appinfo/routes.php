<?php
/**
 * ownCloud - testmiddleware
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Dino Peng <dino.p@inwinstack.com>
 * @copyright Dino Peng 2016
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\TestMiddleWare\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */


\OCP\API::register('post',
        '/apps/openidconnect/getredircthost',
        function($urlParameters) {
            $accessToken = $_POST['access_token'];
            \OCP\Util::writeLog('Duncan','=======$accessToken====='.$accessToken, \OCP\Util::ERROR);
            $ch = curl_init();
            
            $url = \OCA\OpenIdConnect\GetInfo::EDUINFOURL;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' .$accessToken
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            $result = curl_exec($ch);
            if (!curl_errno($ch)) {
                switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                    case 200:
                        break;
                    default:
                        \OCP\Util::writeLog('OpenIdConnect','=Get schoolid failed. Status code = '.$http_code, \OCP\Util::ERROR);
                }
            }
            else{
                $result = '';
            }
            curl_close($ch);
            if ($result == ''){
                return new \OC_OCS_Result(array('result'=>false));
            }
            $result= json_decode($result,true);
            if (array_key_exists('schoolid',$result)){
                $schoolId = $result['schoolid'];
                $region = (int)substr($schoolId,0,2);
                $config = \OC::$server->getSystemConfig();
                $regions = $config->getValue("sso_regions");
                $redirectHost = $config->getValue("sso_owncloud_url")[$regions[$region]];
                return new \OC_OCS_Result(array('result'=>$redirectHost));
            }
            
            return new \OC_OCS_Result(array('result'=>false));
        },
        'openidconnect',\OCP\API::GUEST_AUTH);

\OCP\API::register(
        'post',
        '/apps/openidconnect/decryptidtoken',
        function($urlParameters) {
            $idToken = $_POST['id_token'];
            $jwksUri = $_POST['jwks_uri'];
 
            $openIdObj = new \OCA\OpenIdConnect\OpenIdUtil();
            
            
            $jd = $openIdObj->getModnExp($jwksUri);
            if( !$jd) {
                $data = array('result'=> false);
                return new \OC_OCS_Result($data);
            }
            $e= $jd['keys'][0]['e'];
            $n= $jd['keys'][0]['n'];
            
            $rsa = \OCA\OpenIdConnect\OpenIdUtil::getRSA();
            $nx = \OCA\OpenIdConnect\OpenIdUtil::getNx($openIdObj->urlsafeB64Decode($n), 256);
            $ex = \OCA\OpenIdConnect\OpenIdUtil::getEx($openIdObj->urlsafeB64Decode($e), 256);

            
            $rsa->loadKey(
                        array(
                                'e' => $ex,
                                'n' => $nx
                        )
                    );
            
            $pubkey= $rsa->getPublicKey();
            
            $JWS = \OCA\OpenIdConnect\OpenIdUtil::getJWS(array(),'SecLib');
            
            $jws = $JWS->load($idToken);
                
            /*
             (
             [sub] => xxx-xxx-xxx
             [aud] => aaaaaaaaaa
             [iss] => https://oidc.tanet.edu.tw
             [preferred_username] => test
             [exp] => 1500606930
             [iat] => 1500603330
             [nonce] => xxxxxxxx
             [open2_id] => Array
             (
             [0] => https://openid.ntpc.edu.tw/user/test
             )
            
             )
             */
            $p=$jws->getPayload();;  //取得payload
            
            if ($jws->verify($pubkey, 'RS256')) {
                $data = array('result'=> $p);
                return new \OC_OCS_Result($data);
            
            }else{
                $data = array('result'=> false);
                return new \OC_OCS_Result($data);
            }

        },
        'openidconnect',
        \OC_API::GUEST_AUTH);

return [
    'routes' => [
        [
			'name'         => 'collaboration_api#preflighted_cors', // Valid for all API end points
			'url'          => '/api/{path}',
			'verb'         => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
	   ['name' => 'collaboration_api#getFileList', 'url' => '/api/filelist', 'verb' => 'GET'],       
	   ['name' => 'collaboration_api#shareLinks', 'url' => '/api/share', 'verb' => 'POST'],
	   ['name' => 'collaboration_api#unshare', 'url' => '/api/unshare', 'verb' => 'POST'],
	   ['name' => 'collaboration_api#upload', 'url' => '/api/upload', 'verb' => 'POST'],
	   ['name' => 'collaboration_api#download', 'url' => '/api/download', 'verb' => 'GET'],
    ]
];


