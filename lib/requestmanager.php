<?php
namespace OCA\OpenIdConnect;

use Exception;

class RequestManager {
    private static $serverConnection;
    private static $requests = array();

    public static function init($serverUrl, $requests) {
        if (!class_exists('\\OCA\\OpenIdConnect\\APIServerConnection')) {
            throw new Exception("The class \\OCA\\OpenIdConnect\\APIServerConnection did't exist.");
        }

        self::$serverConnection = new \OCA\OpenIdConnect\APIServerConnection($serverUrl);

        foreach($requests as $request) {
            if(!class_exists($request)) {
                throw new Exception("The class " . $request . " did't exist.");
            }
        }

        foreach($requests as $request) {
            $request = new $request(self::$serverConnection);
            if($request instanceof ISingleSignOnRequest) {
                self::$requests[$request->name()] = $request;
            }
        }
    }

    public static function send($requestName, $data = array()) {
        if(array_key_exists($requestName, self::$requests)) {
            return self::$requests[$requestName]->send($data);
        }
        return false;
    }

    public static function getRequest($requestName) {
        if(array_key_exists($requestName, self::$requests)) {
            return self::$requests[$requestName];
        }
        return false;
    }
}
