<?php

namespace elisdn\hybrid;
/**
 * Mock for the filemtime() function for rbac classes. Avoid random test fails.
 * @return int
 */
function filemtime($file)
{
    return \elisdn\hybrid\tests\AuthManagerTest::$filemtime ?: \filemtime($file);
}

/**
 * Mock for the time() function for rbac classes. Avoid random test fails.
 * @return int
 */
function time()
{
    return \elisdn\hybrid\tests\AuthManagerTest::$time ?: \time();
}

namespace elisdn\hybrid\tests;

use Yii;
/**
 * @group rbac
 * @property ExposedAuthManager $auth
 */
class AuthManagerTest extends ManagerTestCase
{
    public static $filemtime;
    public static $time;

    protected function getItemFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-items.php';
    }

    protected function getAssignmentFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-assignments.php';
    }

    protected function getRuleFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-rules.php';
    }

    protected function removeDataFiles()
    {
        @unlink($this->getItemFile());
        @unlink($this->getAssignmentFile());
        @unlink($this->getRuleFile());
    }

    /**
     * @inheritdoc
     */
    protected function createManager()
    {
        return new ExposedAuthManager([
            'modelClass' => 'elisdn\hybrid\tests\User',
            'on renameRole' => ['elisdn\hybrid\tests\User', 'onRenameRole'],
            'on removeRole' => ['elisdn\hybrid\tests\User', 'onRemoveRole'],
            'on removeAll' => ['elisdn\hybrid\tests\User', 'onRemoveAll'],
            'on removeAllAssignments' => ['elisdn\hybrid\tests\User', 'onRemoveAllAssignments'],
            'itemFile' => $this->getItemFile(),
            'assignmentFile' => $this->getAssignmentFile(),
            'ruleFile' => $this->getRuleFile(),
        ]);
    }

    protected function setUp()
    {
        static::$filemtime = null;
        static::$time = null;
        parent::setUp();
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('PhpManager is not compatible with HHVM.');
        }
        $this->mockApplication();
        $this->removeDataFiles();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        $this->removeDataFiles();
        static::$filemtime = null;
        static::$time = null;
        parent::tearDown();
    }

    public function testSaveLoad()
    {
        static::$time = static::$filemtime = \time();
        $this->prepareData();
        $items = $this->auth->items;
        $children = $this->auth->children;
        $assignments = $this->auth->assignments;
        $rules = $this->auth->rules;
        $this->auth->save();
        $this->auth = $this->createManager();
        $this->auth->load();
        $this->assertEquals($items, $this->auth->items);
        $this->assertEquals($children, $this->auth->children);
        $this->assertEquals($assignments, $this->auth->assignments);
        $this->assertEquals($rules, $this->auth->rules);
    }

    public function testUpdateItemName()
    {
        $this->prepareData();
        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->name = 'UPDATED-NAME';
        $this->assertTrue($this->auth->update($name, $permission), 'You should be able to update name.');
    }

    public function testUpdateDescription()
    {
        $this->prepareData();
        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->description = 'UPDATED-DESCRIPTION';
        $this->assertTrue($this->auth->update($name, $permission), 'You should be able to save w/o changing name.');
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     */
    public function testOverwriteName()
    {
        $this->prepareData();
        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->name = 'createPost';
        $this->auth->update($name, $permission);
    }

    public function testSaveAssignments()
    {
        $this->auth->removeAll();
        $role = $this->auth->createRole('Admin');
        $this->auth->add($role);
        $this->auth->assign($role, 13);
        $this->assertContains('Admin', User::findAuthRoleIdentity(13)->getAuthRoleNames());
        $role->name = 'NewAdmin';
        $this->auth->update('Admin', $role);
        $this->assertContains('NewAdmin', User::findAuthRoleIdentity(13)->getAuthRoleNames());
        $this->auth->remove($role);
        $this->assertNotContains('NewAdmin', User::findAuthRoleIdentity(13)->getAuthRoleNames());
    }
}