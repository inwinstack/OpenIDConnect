<?php
/**
 * @author Duncan Chiang <duncan.c@inwinstack.com>
 * 
 * 
 * ownCloud - openidconnect
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 * 
 * @copyright Copyright (c) 2017, inwinSTACK, Inc.
 * @license AGPL-3.0
 */

namespace OCA\OpenIdConnect;

class AuthInfo implements IAuthInfo
{
    /**
     * requeir keys for auth info
     *
     * @var array
     */
    public static $requireKeys = array("access_token","preferred_username");

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
    public static function get()
    {
        $request = \OC::$server->getRequest();
        $session = \OC::$server->getSession();
        foreach (self::$requireKeys as $key) {
            if($request->offsetGet($key)) {
                self::$info[$key] = $request->offsetGet($key);
            }
            else if($request->getHeader($key)) {
                self::$info[$key] = $request->getHeader($key);
            }
            else if($session->get("sso_" . $key)) {
                self::$info[$key] = $session->get("sso_" . $key);
            }
        }

        self::$info["userIp"] = $request->getRemoteAddress();
        foreach (self::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                return null;
            }
        }

        return self::$info;
    }
    
}

