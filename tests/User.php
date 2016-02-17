<?php

namespace elisdn\hybrid\tests;

use elisdn\hybrid\AuthRoleModelInterface;
use elisdn\hybrid\events\RemoveRoleEvent;
use elisdn\hybrid\events\RenameRoleEvent;
use Yii;

class User implements AuthRoleModelInterface
{
    /**
     * @var self[]
     */
    public static $users;

    public $id;
    public $roles = [];

    private function __construct($id)
    {
        $this->id = $id;
    }

    public static function reset()
    {
        return self::$users = [];
    }

    public static function findAuthRoleIdentity($id)
    {
        if (!isset(self::$users[$id])) {
            self::$users[$id] = new self($id);
        }
        return self::$users[$id];
    }

    public static function onRemoveAll()
    {
        self::$users = [];
    }

    public static function onRemoveAllAssignments()
    {
        self::$users = [];
    }

    public static function onRenameRole(RenameRoleEvent $event)
    {
        foreach (self::$users as $user) {
            if (isset($user->roles[$event->oldRoleName])) {
                unset($user->roles[$event->oldRoleName]);
                $user->roles[$event->newRoleName] = $event->newRoleName;
            }
        }
    }

    public static function onRemoveRole(RemoveRoleEvent $event)
    {
        foreach (self::$users as $user) {
            if (isset($user->roles[$event->roleName])) {
                unset($user->roles[$event->roleName]);
            }
        }
    }

    public static function findAuthIdsByRoleName($roleName)
    {
        $ids = [];
        foreach (self::$users as $user) {
            if (in_array($roleName, $user->roles)) {
                $ids[] = $user->id;
            }
        }
        return $ids;
    }

    public function getAuthRoleNames()
    {
        return array_values($this->roles);
    }

    public function addAuthRoleName($roleName)
    {
        $this->roles[$roleName] = $roleName;
    }

    public function removeAuthRoleName($roleName)
    {
        unset($this->roles[$roleName]);
    }

    public function clearAuthRoleNames()
    {
        $this->roles = [];
    }
}
