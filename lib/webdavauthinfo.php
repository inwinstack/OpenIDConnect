<?php

namespace OCA\OpenIdConnect;

/**
 * Class WebDavAuthInfo
 * @author Dauba
 */
class WebDavAuthInfo implements IWebDavAuthInfo
{
    
    /**
    * requeir keys for auth info
    *
    * @var array
    **/
    private static $requireKeys = array("token1", "userIp");

    /**
     * auth info
     *
     * @var array
     */
    private static $info = array();
    
    /**
     * Getter for Info
     *
     * @return array
     */
    public static function get($userID, $password)
    {
        self::$info["userIp"] = $request->getRemoteAddress();
        self::$info["token1"] = RequestManager::send(IOpenIdConnectRequest::GETTOKEN, array("userId" => $userID, "password" => $password, "userIp" => self::$info["userIp"]));

        foreach (AuthInfo::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                return null;
            }
        }
        return self::$info;
    }
    
}
