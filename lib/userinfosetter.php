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

        $data = [
            'region' => $userProfile->getRegion(),
            'schoolCode' => $userProfile->getSchoolId()
        ];
        $config->setUserValue($userID, "settings", "regionData", json_encode($data));

        if ($config->getUserValue($userID, "setting", "role") != NULL && $config->getUserValue($userID, "files", "quota") == "30 GB") {
            return;
        }

        \OC_User::setDisplayName($userID, $userInfo->getDisplayName());
        $config->setUserValue($userID, "settings", "email", $userInfo->getEmail());
        //$config->setUserValue($userID, "files", "quota", "30 GB");

        if ($userProfile->getRole() === $advanceGroup) {
            $config->setUserValue($userID, "settings", "role", $userProfile->getRole());

            // used to notify advance groups like teacher have 30GB quota
            // if($config->getUserValue($userID, "teacher_notification", "notification", NULL) === NULL) {
            //     $config->setUserValue($userID, "teacher_notification", "notification", "1");
            // }
            $config->setUserValue($userID, "files", "quota", "20 GB");
            $group = \OC::$server->getGroupManager()->get($advanceGroup);
            if(!$group) {
                $group = \OC::$server->getGroupManager()->createGroup($advanceGroup);
            }
            $group->addUser($user);
        }
    }
    
}
