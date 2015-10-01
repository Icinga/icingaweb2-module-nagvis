<?php

use Icinga\Application\Icinga;
use Icinga\Authentication\Auth;

class CoreAuthModIcingaweb2 extends CoreAuthModule
{
    private $app;
    private $auth;
    private $user;

    private $iUserId = -1;
    private $sUsername = '';
    private $sPassword = '';
    private $sPasswordnew = '';
    private $sPasswordHash = '';

    public function __construct()
    {
        parent::$aFeatures = array(
            // General functions for authentication
            'passCredentials' => false,
            'getCredentials' => false,
            'isAuthenticated' => true,
            'getUser' => true,
            'getUserId' => true,

            // Changing passwords
            'passNewPassword' => false,
            'changePassword' => false,
            'passNewPassword' => false,

            // Managing users
            'createUser' => false,
        );

        $oldname = session_name();
        session_write_close();
        $old_path = ini_get('session.cookie_path');
        $old_id = session_id();
        $cacheLimiter = ini_get('session.cache_limiter');
        ini_set('session.use_cookies', false);
        ini_set('session.use_only_cookies', false);
        ini_set('session.cache_limiter', null);
        ini_set('session.cookie_path', '/');
        $icookie = 'Icingaweb2';
        if (isset($_COOKIE[$icookie])) {
            session_id($_COOKIE[$icookie]);
        }

        $this->app = Icinga::app();
        $this->auth = Auth::getInstance();
        if ($this->auth->isAuthenticated()) {
            $this->user = $this->auth->getUser();
        }
        ini_set('session.cookie_path', $old_path);
        session_id($old_id);
        session_name($oldname);
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.cache_limiter', $cacheLimiter);
    }

    public function getAllUsers()
    {
        die('getAllUsers');
        return array();
        // assoc -> userId, name
    }

    public function checkUserExists($name)
    {
        return true;
    }

    private function updatePassword()
    {
        return true;
    }

    private function addUser($user, $hash)
    {
        return true;
    }

    public function passCredentials($aData)
    {
        // die('pass ' . print_r($aData, 1));
        // eventually has user, password and passwordHash
    }

    public function passNewPassword($aData)
    {
        // die('new pass');
        // eventually has user, password and passwordHash
    }

    public function getCredentials()
    {
        return Array('user' => $this->getUser(),
                     'passwordHash' => null,
                     'userId' => $this->getUserId());
    }

    public function createUser($user, $password)
    {
        return false;
    }

    public function changePassword()
    {
        return false;
    }

    public function isAuthenticated($bTrustUsername = AUTH_NOT_TRUST_USERNAME)
    {
        return $this->user !== null;
    }

    public function getUser()
    {
        return $this->user->getUsername();
    }

    public function getUserId()
    {
        return $this->getUser();
    }
}
