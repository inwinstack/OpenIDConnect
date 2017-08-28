<?php
namespace OCA\OpenIdConnect;

use Exception;

class Util {
    public static function changeMailAcountUser($userID,$authInfo){
        $manager = \OC::$server->getUserManager();
        $userToken = $authInfo['access_token'];
        $manager->emit('\OC\User', 'preLogin', array($userID, $userToken));
        $user = $manager->get($userID);
        \OC::$server->getUserSession()->setUser($user);
        \OC::$server->getUserSession()->setLoginName($user);
        \OC_Util::setupFS($userID);
        \OC::$server->getUserFolder($userID);
        $manager->emit('\OC\User', 'postLogin', array($user, $userToken));
        self::wirteAuthInfoToSession($authInfo);
        return true;
    }
    
    public static function login($userInfo, $authInfo, $userProfile) {
        $userID = $userInfo->getUserId();
        $userToken = $userInfo->getToken();
        $manager = \OC::$server->getUserManager();

        $manager->emit('\OC\User', 'preLogin', array($userID, $userToken));

        $user = $manager->get($userID);
        \OC::$server->getUserSession()->setUser($user);
        \OC::$server->getUserSession()->setLoginName($user);
        \OC_Util::setupFS($userID);
        \OC::$server->getUserFolder($userID);

        if (class_exists('\\OCA\\OpenIdConnect\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo, $userProfile);
        }

        $manager->emit('\OC\User', 'postLogin', array($user, $userToken));

        self::wirteAuthInfoToSession($authInfo);

        return true;
    }

    public static function firstLogin($userInfo, $authInfo, $userProfile) {
        //sub
        $userID = $userInfo->getUserId();

        $password = RequestManager::getRequest(ISingleSignOnRequest::USERPASSWORDGENERATOR) ? RequestManager::send(ISingleSignOnRequest::USERPASSWORDGENERATOR) : $userID;

        $user = \OC_User::createUser($userID, $password);

        if (class_exists('\\OCA\\OpenIdConnect\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo, $userProfile);
        }
        
        self::saveOpenIDInfoToDB($userInfo,$isOpenIDUser=true);
        
        self::wirteAuthInfoToSession($authInfo);
        return \OC_User::login($userID, $password);
    }
    
    public static function saveOpenIDInfoToDB($userInfo,$isOpenIDUser=false){
        $userID = $userInfo->getUserId();
        $preferredUserName = $userInfo->getPreferredName();
        $email = $userInfo->getEmail();
        
        
        
        if ($isOpenIDUser){
            $sql = "INSERT INTO *PREFIX*openid_connect_user_info
                (`sub`, `preferred_username`,`openid_connect_user`)
                VALUES (?, ?, ?)";
            $prepare = \OC_DB::prepare($sql);
            $result = $prepare->execute(array($userID,$preferredUserName,$isOpenIDUser));
        }
        else{
            $sql = "INSERT INTO *PREFIX*openid_connect_user_info
                (`sub`, `preferred_username`,`match_oc_account`,`openid_connect_user`)
                VALUES (?, ?, ?, ?)";
            $prepare = \OC_DB::prepare($sql);
            $result = $prepare->execute(array($userID,$preferredUserName,$email,$isOpenIDUser));
        }
        if($result){
            return true;
        }
        return false;
    }
    
    public static function getOpenIDInfoInDBBySub($sub){
        $sql = "SELECT * FROM `*PREFIX*openid_connect_user_info` WHERE `sub` = ?";
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array($sub));
        if ($result->rowCount() > 0){
            while ($row = $result->fetchRow()) {
                return array('sub' => $row['sub'],
                        'preferred_username' => $row['preferred_username'],
                        'old_preferred_username' => $row['old_preferred_username'],
                        'match_oc_account' => $row['match_oc_account']
                );
            }
        }
        return array();
    }
    
    public static function checkPreferredNameChanged($userInfo,$isOpenIDUser=false){
        $userID = $userInfo->getUserId();
        $preferredUserName = $userInfo->getPreferredName();
        $mail = $userInfo->getEmail();

        /*
        $sql = "UPDATE `*PREFIX*openid_connect_user_info`
                SET old_preferred_username = preferred_username,
                    preferred_username = ?
                WHERE preferred_username != ?
                AND sub = ?";
        */
        
        $sql = "SELECT * FROM `*PREFIX*openid_connect_user_info` WHERE `sub` = ? 
                AND `preferred_username` = ?";
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array($userID,$preferredUserName));
        if (!$result->rowCount() > 0){
            self::updatePreferredName($userID, $preferredUserName);
        }

    }
    
    public static function updatePreferredName($userID,$preferredUserName){
        $sql = "
                UPDATE `*PREFIX*openid_connect_user_info`
                SET
                `old_preferred_username` =
                CASE
	               WHEN `sub` = ? THEN `preferred_username`
	               ELSE `old_preferred_username`
                END,
                `preferred_username`=
                CASE
	               WHEN (`sub` != ? AND `preferred_username` = ?) THEN 'false'
	               WHEN (`sub` = ? AND `preferred_username` != ?) THEN ?
	               ELSE `preferred_username`
                END
                WHERE `sub` = ? OR `preferred_username` = ?
                ";
        
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array($userID,$userID,$preferredUserName,$userID,$preferredUserName,
                $preferredUserName,$userID,$preferredUserName));
    }
    
    public static function webDavLogin($userID, $password) {
        $config = \OC::$server->getSystemConfig();

        RequestManager::init($config->getValue("sso_portal_url1"), $config->getValue("sso_requests1"));

        $authInfo = WebDavAuthInfo::get($userID, $password);

        $userInfo = RequestManager::getRequest(ISingleSignOnRequest::INFO);

        $userInfo->setup(array("action" => "webDavLogin"));

        if(!$userInfo->send($authInfo)) {
            return ;
        }

        if($config->getValue("sso_multiple_region1")) {
            self::redirectRegion($userInfo, $config->getValue("sso_regions1"), $config->getValue("sso_owncloud_url1"));
        }
        
        if(!\OC_User::userExists($userInfo->getUserId())) {
            return self::firstLogin($userInfo, $authInfo);
        }

        if($authInfo){
            return self::login($userInfo, $authInfo);
        }

        return false;
    }

    public static function redirect($url) {
        if(!$url) {
            \OC_Util::redirectToDefaultPage();
        }
        else {
            header("location: " . $url);
            exit();
        }
    }

    /**
     * Check user region and redirect to correct region.
     *
     * @return void
     */
    public static function redirectRegion($userInfo, $regions, $serverUrls) {
        $region = $userInfo->getRegion();
        $request = \OC::$server->getRequest();

        if($request->getServerHost() === $serverUrls[$regions[$region]]) {
            return ;
        }

        $redirectUrl = RedirectRegion::getRegionUrl($region);

        self::redirect($redirectUrl);
    }

    /**
     * Write auth info to session
     *
     * @param array $authInfo
     * @return void
     */
    public static function wirteAuthInfoToSession($authInfo)
    {
        foreach ($authInfo as $key => $value) {
            \OC::$server->getSession()->set("sso_" . $key, $value);
        }
    }
    
    
}


