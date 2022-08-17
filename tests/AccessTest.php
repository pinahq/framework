<?php

use PHPUnit\Framework\TestCase;
use Pina\Access;
use Pina\App;

class AccessTest extends TestCase
{

    public function testPermit()
    {
        App::init('test', __DIR__ . '/config');

        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertTrue(Access::isPrivate('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        Access::addGroup('enabled');
        Access::addGroup('provider');
        Access::addGroup('owner');
        Access::addCondition('self', array('user_id' => 2));
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        $groups = Access::getGroups();
        $this->assertEquals(['enabled', 'provider', 'owner'], $groups);
        $this->assertTrue(Access::hasGroup('enabled'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        Access::addGroup('enabled');
        Access::addGroup('provider');
        $this->assertFalse(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertFalse(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self');
        Access::addCondition('self', array('account_id' => 5));
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self,provider');
        Access::addGroup('provider');
        Access::addCondition('self', array('account_id' => 5));
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self,provider');
        Access::addGroup('provider');
        Access::addCondition('self', array('account_id' => 4));
        $this->assertFalse(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/users', 'provider;buyer');
        Access::permit('accounts/:account_id/users/:user_id/lists', 'buyer');
        Access::addGroup('provider');
        $this->assertTrue(Access::isPermitted('accounts/5/users'));
        $this->assertFalse(Access::isPermitted('accounts/5/users/5/lists'));

        Access::reset();
        Access::permit('users/:user_id', 'self');
        Access::addCondition('self', array('user_id' => 1));
        $this->assertTrue(Access::isPermitted('users/1'));
        $this->assertFalse(Access::isPermitted('users/5'));
        //TODO:
        //$this->assertTrue(Access::isPermitted('users/create'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'root');
        Access::permit('accounts/:id/items', 'admin');
        Access::addGroup('admin');
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'root');
        Access::permit('accounts/:id/items', 'admin');
        Access::addGroup('root');
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'root;admin');
        Access::addGroup('admin');
        $this->assertTrue(Access::isPermitted('accounts/5/items'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'root;admin');
        Access::addGroup('admin');
        $this->assertFalse(Access::isHandlerPermitted('users'));
    }

}
