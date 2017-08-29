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
    private $preferredName ;
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
        
        $result = json_decode($result, true);

        $userInfo = $result;

        $this->userId = $result["sub"];
        $this->preferredName = $data['preferred_username'];
        $this->email = $data['preferred_username'].'@mail.edu.tw';
        $this->displayName = $result["name"];
        $this->token = $data['access_token'];

        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }
    
    public function getPreferredName() {
        return $this->preferredName;
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


