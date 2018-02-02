<?php

namespace OCA\OpenIdConnect;

/**
 * Class UserInfoSetter
 * @author Dauba
 */
class UserInfoSetter
{
    /**
     * Set ownCloud user info
     *
     * @return void
     */
    public static function setInfo($user, $userInfo, $userProfile)
    {
        $config = \OC::$server->getConfig();
        $userID = $userInfo->getUserId();
        $advanceGroup = \OC::$server->getSystemConfig()->getValue("sso_advance_user_group", NULL);

        $regionData = \OC::$server->getConfig()->getUserValue($userID, "settings", "regionData",false);
        if (!$regionData ||
            $regionDataDecoded['region'] !== $userInfo->getRegion() ||
            $regionDataDecoded['schoolCode'] !== $userInfo->getSchoolId()
            ){
                $data = ['region' => $userInfo->getRegion(),
                    'schoolCode' => $userInfo->getSchoolId(),
                ];
                $config->setUserValue($userID, "settings", "regionData", json_encode($data));
        }

        $savedRole = $config->getUserValue($userID, "settings", "role",NULL);
        if ($savedRole !== $userInfo->getRole()) {
            $config->setUserValue($userID, "settings", "role", $userInfo->getRole());
        }
        
        $savedEmail = $config->getUserValue($userID, "settings", "email",NULL);
        if ($savedEmail !== $userInfo->getEmail()) {
            $config->setUserValue($userID, "settings", "email", $userInfo->getEmail());
        }

        \OC_User::setDisplayName($userID, $userInfo->getDisplayName());

        if ($userProfile->getRole() === $advanceGroup) {

            // used to notify advance groups like teacher have 30GB quota
            // if($config->getUserValue($userID, "teacher_notification", "notification", NULL) === NULL) {
            //     $config->setUserValue($userID, "teacher_notification", "notification", "1");
            // }
            $group = \OC::$server->getGroupManager()->get($advanceGroup);
            if(!$group) {
                $group = \OC::$server->getGroupManager()->createGroup($advanceGroup);
            }
            $group->addUser($user);
        }
    }
    
}
