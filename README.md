# Hybrid RBAC AuthManager for Yii2 Framework

The extension extends `yii\rbac\PhpManager` and allows to store user assignments in any storage instead of `app/rbac/assignments.php` file.

## Installation

Install with composer:

```bash
composer require elisdn/yii2-hybrid-authmanager
```

or add

```bash
"elisdn/yii2-hybrid-authmanager": "*"
```

to the require section of your `composer.json` file.

Set `authManager` component in your config:

```php
'components' => [
    ...
    'authManager' => [
        'class' => 'elisdn\hybrid\AuthManager',
        'modelClass' => 'app\modules\User',
    ],
],
```

and implement `AuthRoleModelInterface` in your model class.

## Usage samples

Storing single role in the `role` field of `user` table:

```php
namespace app\models;

use elisdn\hybrid\AuthRoleModelInterface;

class User extends ActiveRecord implements IdentityInterface, AuthRoleModelInterface
{
    ...

    public static function findAuthRoleIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findAuthIdsByRoleName($roleName)
    {
        return static::find()->where(['role' => $roleName])->select('id')->column();
    }

    public function getAuthRoleNames()
    {
        return (array)$this->role;
    }

    public function addAuthRoleName($roleName)
    {
        $this->updateAttributes(['role' => $this->role = $roleName]);
    }

    public function removeAuthRoleName($roleName)
    {
        $this->updateAttributes(['role' => $this->role = null]);
    }

    public function clearAuthRoleNames()
    {
        $this->updateAttributes(['role' => $this->role = null]);
    }

    ...
}
```

Storing multiple roles:

```php
class User extends ActiveRecord implements IdentityInterface, AuthRoleModelInterface
{
    ...

    public function getAuthRoleNames()
    {
        return $this->roles;
    }

    public function addAuthRoleName($roleName)
    {
        $this->updateAttributes(['roles' => $this->roles = array_merge($this->roles, [$roleName])]);
    }

    public function removeAuthRoleName($roleName)
    {
        $this->updateAttributes(['roles' => $this->roles = array_diff($this->roles, [$roleName])]);
    }

    public function clearAuthRoleNames()
    {
        $this->updateAttributes(['roles' => $this->roles = []]);
    }

    ...
}
```

Or with JSON serialization:

```php
class User extends ActiveRecord implements IdentityInterface, AuthRoleModelInterface
{
    ...

    public function getAuthRoleNames()
    {
        return (array)Json::decode($this->roles);
    }

    public function addAuthRoleName($roleName)
    {
        $roles = (array)Json::decode($this->roles);
        $this->roles[] = $roleName;
        $this->updateAttributes(['role' => $this->roles = Json::encode($this->roles)]);
    }

    public function removeAuthRoleName($roleName)
    {
        $roles = (array)Json::decode($this->roles);
        $this->roles = array_diff($this->roles, [$roleName])
        $this->updateAttributes(['role' => $this->roles = Json::encode($this->roles)]);
    }

    public function clearAuthRoleNames()
    {
        $this->updateAttributes(['role' => $this->roles = Json::encode([])]);
    }

    ...
}
```

### Handling role events (optional)

If you want to update your storage on system events just add your event handlers:

```php
class User extends ActiveRecord implements IdentityInterface, AuthRoleModelInterface
{
    ...

    public static function onRenameRole(RenameRoleEvent $event)
    {
        self::updateAll(['role' => $event->newRoleName], ['role' => $event->oldRoleName]);
    }

    public static function onRemoveRole(RemoveRoleEvent $event)
    {
        self::updateAll(['role' => null], ['role' => $event->roleName]);
    }

    public static function onRemoveAll(RemoveAllEvent $event)
    {
        self::updateAll(['role' => null]);
    }

    public static function onRemoveAllAssignments(RemoveAllAssignmentsEvent $event)
    {
        self::updateAll(['role' => null]);
    }
}
```

and register the handlers:

```php
'authManager' => [
    'class' => 'elisdn\hybrid\AuthManager',
    'modelClass' => 'app\models\User',
    'on renameRole' => ['app\models\User', 'onRenameRole'],
    'on removeRole' => ['app\models\User', 'onRemoveRole'],
    'on removeAll' => ['app\models\User', 'onRemoveAll'],
    'on removeAllAssignments' => ['app\models\User', 'onRemoveAllAssignments'],
],
```
