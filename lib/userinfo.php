<?php
namespace OCA\OpenIdConnect;

class UserInfo implements IUserInfoRequest {
    public static $teacherRole = array("校長",
                                       "教師",
                                       "職員",
                                       "縣市管理者",
                                       "學校管理者");
    private $connection;
    private $setupParams = array();
    private $userId;
    private $email;
    private $groups = array();
    private $userGroup;
    private $displayName;
    private $errorMsg;
    private $sid;
    private $title = array();

    public function __construct($connection){
        $this->connection = $connection;
    }

    public function name() {
        return ISingleSignOnRequest::USERINFO;
    }

    /**
     * setup userinfo
     *
     * @param array $param
     * @return void
     */
    public function setup($params)
    {
        foreach ($params as $key => $value) {
            $this->setupParams[$key] = $value;
        }
    }

    public function send($data = null) {
        $serverConnection = $this->connection->getConnection();
        $serverUrl = $this->connection->getServerUrl();

        $url = $serverUrl . 'userinfo';

        \OCP\Util::writeLog('Duncan', $url, \OCP\Util::ERROR);
        
        curl_setopt($serverConnection, CURLOPT_URL, $url);
        curl_setopt($serverConnection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($serverConnection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($serverConnection, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' .$data['access_token']
         ));

        $result = curl_exec($serverConnection);

        // only for DEMO
        $limit = 0;
        $pageError = "<html><head><title>Error</title></head><body>Internal Server Error</body></html>";
        while((curl_errno($serverConnection) == 28 || $result == $pageError) && $limit < 4){
            $limit++;
            $result = curl_exec($serverConnection);
        }
        //
        
        $result = json_decode($result, true);


        // $statusCode = (int)$result["retcode"];

	    // \OCP\Util::writeLog('Duncan', $result, \OCP\Util::ERROR);
	
        // // need to fit to real error data
        // if ($statusCode != 0) {
        //     if($this->setupParams["action"] == "webDavLogin") {
        //         switch ($statusCode) {
        //             case 1:
        //                 $errorMsg = "Missing parameter 'password'";
        //                 break;
        //             case 2:
        //                 $errorMsg = "Missing parameter 'userid'";
        //                 break;
        //             case 3:
        //                 $errorMsg = "Userid not exsit";
        //                 break;
        //             case 4:
        //                 $errorMsg = "Verification failed";
        //                 break;
        //         }
        //     }
        //     else {
        //         switch ($statusCode) {
        //             case 1:
        //                 $errorMsg = "Missing parameter 'key'";
        //                 break;
        //             case 2:
        //                 $errorMsg = "Error format of parameter 'key'";
        //                 break;
        //             case 3:
        //                 $errorMsg = "Missing parameter 'userid'";
        //                 break;
        //             case 4:
        //                 $errorMsg = "Userid not exsit";
        //                 break;
        //             case 5:
        //                 $errorMsg = "Verification failed";
        //                 break;
        //         }
        //     }
        //     $this->errorMsg = $errorMsg;
        //     return false;
        // }

        $userInfo = $result;
        \OCP\Util::writeLog('Duncan', 'UserInfo Email:' . $userInfo["email"], \OCP\Util::ERROR);

        $this->userId = $result["email"];
        $this->email = $result["email"];
        $this->displayName = $result["name"];
        $this->token = $data['access_token'];

        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getGroups() {
        return $this->groups;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Get user auth token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }


    /**
     * Check has error massage or not
     *
     * @return true|false
     */
    public function hasErrorMsg()
    {
        return $this->errorMsg ? true : false;
    }
}
