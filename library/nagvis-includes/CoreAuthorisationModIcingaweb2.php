<?php

use Icinga\Application\Icinga;
use Icinga\Authentication\Manager;

class CoreAuthorisationModIcingaweb2 extends CoreAuthorisationModule
{
    public $rolesConfigurable = false;

    private $auth;

    public function __construct()
    {
        $this->auth = Manager::getInstance();
    }

    public function parsePermissions($sUsername = null)
    {
        if ($sUsername !== null) {
            die('parsePermissions() with username is not supported');
        }

        $perms = array(
            'General'  => array('*' => array('*' => true)),
            //'Overview' => array('view' => array('*' => true)),
            'Map'      => array('view' => array('*' => true)),
            'Search'   => array('view' => array('*' => true)),
            'Rotation' => array('view' => array('*' => true))
        );

        // Never allowed:
        // ChangePassword - change
        // Auth - logout
        // UserMgmt - manage
        // RoleMgmt - manage
        // Action - perform - *

        if ($this->auth->hasPermission('nagvis/edit')) {
            $perms['ManageShapes']      = array('manage' => array('*' => true));
            $perms['ManageBackgrounds'] = array('manage' => array('*' => true));
            $perms['Overview']['edit']  = array('*' => true);
            $perms['Map']['add']    = array('*' => true);
            $perms['Map']['edit']   = array('*' => true);
            $perms['Map']['manage'] = array('*' => true);
        }

        if ($this->auth->hasPermission('nagvis/admin')) {
            $perms['MainCfg'] = array('edit' => array('*' => true));
        }

        return $perms;
    }

    public function getUserRoles($userId)
    {
        // $userId is now the username
        return array();
        die("getUserRoles($userId)");
        return array(0 => array('name' => 'Administrators'));
        // [ { roleId, name }, ... ]
    }

    public function getAllRoles()
    {
        // die('getAllRoles');
        // User menu -> Manage Users
        return array();
        // [ { roleId, name }, ... ]
    }


    // I want to get rid of those :(

    public function isPermitted($sModule, $sAction, $sObj = null)
    {
        die("isPermitted($sModule, $sAction, $sObj) - should never be called");
        return false;
    }

    public function deletePermission($mod, $name)
    {
        return false;
        // $mod -> Map, Rotation
    }

    public function createPermission($mod, $name)
    {
        return false;
    }

    public function roleUsedBy($roleId)
    {
        die("roleUsedBy($roleId)");
        return array();
        // [ name , ... ]
    }

    public function deleteRole($roleId)
    {
        return false;
    }

    public function deleteUser($userId)
    {
        return false;
    }

    public function updateUserRoles($userId, $roles)
    {
        return false;
        // roles = [roleId, ...]
    }


    public function getRoleId($sRole)
    {
        die("getRoleId($sRole)");
        return 0;
    }

    public function getAllPerms()
    {
        die('getAllPerms');
        return array();
        // [ { permId, mod, act, obj }, ... ]
    }

    public function getRolePerms($roleId) {
        die("getRolePerms($roleId)");
        return array();
        // [ permId => true, ... ]
    }

    public function updateRolePerms($roleId, $perms)
    {
        // $perms = [ permId, ... ]
        return false;
    }

    public function checkRoleExists($name)
    {
        die("checkRoleExists($name)");
        return false;
    }

    public function createRole($name)
    {
        die("createRole($name)");
        return true;
    }


    private function checkUserExistsById($id)
    {
        die('checkUserExistsById');
        return false;
    }

    public function getUserId($sUsername)
    {
        die('getUserId');
        return 0;
    }
}
