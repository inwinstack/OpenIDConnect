<?php
namespace OCA\OpenIdConnect;

class GetInfo implements IGetInfoRequest {
    public static $teacherRole = array("校長",
                                       "教師",
                                       "職員",
                                       "縣市管理者",
                                       "學校管理者",
                                       "資訊組長");
    private $connection;
    private $setupParams = array();
    private $schoolId;
    private $groups = array();
    private $userGroup;
    private $displayName;
    private $errorMsg;
    private $guid;
    private $title = array();

    public function __construct($connection){
        $this->connection = $connection;
    }

    public function name() {
        return ISingleSignOnRequest::USERPROFILE;
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
        //$serverUrl = $this->connection->getServerUrl();

        $url = 'https://oidc.tanet.edu.tw/moeresource/api/v1/oidc/profile';
        
        curl_setopt($serverConnection, CURLOPT_URL, $url);
        curl_setopt($serverConnection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($serverConnection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($serverConnection, CURLOPT_ENCODING, 'UTF-8');
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

        $userInfo = $result;
        \OCP\Util::writeLog('Duncan', 'GetInfo Name:' . $userInfo["fullname"], \OCP\Util::ERROR);

        $this->displayName = $userInfo["fullname"];

        $this->guid = $userInfo["guid"];
        $this->schoolId = $userInfo["titles"][0]["schoolid"];
        $titleStr = $userInfo["titles"];
        foreach ($titleStr as $item) {
            foreach ($item["title"] as $title) {
                $this->title[] = $title;
            }
        }

        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getGroups() {
        return $this->groups;
    }

    public function getSchoolId() {
        return $this->schoolId;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Getter for user region
     *
     * @return string user region
     */
    public function getRegion() {
        return (int)substr($this->schoolId,0,2);
    }

    /**
     * Check user have permassion to use the service or not
     *
     * @return bool
     */
    public function hasPermission(){
        if ($this->userGroup == "T" || $this->userGroup == "S") {
            return true;
        }

        return true;
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
    
    /**
     * Get user role in this system
     *
     * @return string
     */
    public function getRole()
    {
        foreach ($this->title as $title) {
            if (in_array($title, self::$teacherRole)) {
                return \OC::$server->getSystemConfig()->getValue("sso_advance_user_group", NUll);
            }
        }
        return "stutent";
    }
    
}
