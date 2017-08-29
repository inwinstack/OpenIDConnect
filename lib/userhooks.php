<?php

namespace OCA\OpenIdConnect;

use OCP\Util;
use OCP\User;

class UserHooks {
    private $userManager;
    private $UserFolder;

    public function __construct($userManager, $userFolder){
        $this->userManager = $userManager;
        $this->UserFolder = $userFolder;
    }


    public function register() {
        $deleteOpenIDInfoDB = function($user) {

        $sql = "DELETE FROM  `*PREFIX*openid_connect_user_info`
                WHERE  `sub` = ? OR `match_oc_account` = ?";
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array($user->getUID(),$user->getUID()));
        };
        $this->userManager->listen('\OC\User', 'postDelete', $deleteOpenIDInfoDB);
   }
}


