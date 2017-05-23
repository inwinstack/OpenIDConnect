<?php
namespace OCA\OpenIdConnect;
include ('/isinglesignonrequest.php');

interface IUserInfoRequest extends ISingleSignOnRequest {
    /**
     * setup userinfo
     * @param array params
     * @return void
     * @author Julius
     **/
    public function setup($params);

    /**
     * Getter for UserId
     *
     * @return string
     * @author Julius
     */
    public function getUserId();

    /**
     * Getter for Email
     *
     * @return string
     * @author Julius
     */
    public function getEmail();

    /**
     * Getter for display name
     *
     * @return string
     * @author Julius
     */
    public function getDisplayName();

    /**
     * Get user auth token
     *
     * @return void
     */
    public function getToken();
    
    /**
     * Check has error message or not
     *
     * @return true|false
     * @author Julius
     **/
    public function hasErrorMsg();
}
