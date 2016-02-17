<?php

namespace elisdn\hybrid\tests;

use elisdn\hybrid\AuthManager;

/**
 * Exposes protected properties and methods to inspect from outside
 */
class ExposedAuthManager extends AuthManager
{
    /**
     * @var \yii\rbac\Item[]
     */
    public $items = []; // itemName => item
    /**
     * @var array
     */
    public $children = []; // itemName, childName => child
    /**
     * @var \yii\rbac\Assignment[]
     */
    public $assignments = []; // userId, itemName => assignment
    /**
     * @var \yii\rbac\Rule[]
     */
    public $rules = []; // ruleName => rule

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        return parent::updateItem($name, $item);
    }

    public function load()
    {
        parent::load();
    }

    public function save()
    {
        parent::save();
    }
}