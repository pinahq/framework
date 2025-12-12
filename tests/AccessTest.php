<?php

use PHPUnit\Framework\TestCase;
use Pina\Access;
use Pina\App;

class AccessTest extends TestCase
{

    public function testPermit()
    {
        App::init('test', __DIR__ . '/config');

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertTrue($access->isPrivate('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $access->addGroup('enabled');
        $access->addGroup('provider');
        $access->addGroup('owner');
        $access->addCondition('self', array('user_id' => 2));
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $groups = $access->getGroups();
        $this->assertEquals(['enabled', 'provider', 'owner'], $groups);
        $this->assertTrue($access->hasGroup('enabled'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $access->addGroup('enabled');
        $access->addGroup('provider');
        $this->assertFalse($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertFalse($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;self');
        $access->addCondition('self', array('account_id' => 5));
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;self,provider');
        $access->addGroup('provider');
        $access->addCondition('self', array('account_id' => 5));
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'provider,owner;self,provider');
        $access->addGroup('provider');
        $access->addCondition('self', array('account_id' => 4));
        $this->assertFalse($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/users', 'provider;buyer');
        $access->permit('accounts/:account_id/users/:user_id/lists', 'buyer');
        $access->addGroup('provider');
        $this->assertTrue($access->isPermitted('accounts/5/users'));
        $this->assertFalse($access->isPermitted('accounts/5/users/5/lists'));

        $access = new Access();
        $access->permit('users/:user_id', 'self');
        $access->addCondition('self', array('user_id' => 1));
        $this->assertTrue($access->isPermitted('users/1'));
        $this->assertFalse($access->isPermitted('users/5'));
        //TODO:
        //$this->assertTrue($access->isPermitted('users/create'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'root');
        $access->permit('accounts/:id/items', 'admin');
        $access->addGroup('admin');
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'root');
        $access->permit('accounts/:id/items', 'admin');
        $access->addGroup('root');
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'root;admin');
        $access->addGroup('admin');
        $this->assertTrue($access->isPermitted('accounts/5/items'));

        $access = new Access();
        $access->permit('accounts/:account_id/items', 'root;admin');
        $access->addGroup('admin');
        $this->assertFalse($access->isHandlerPermitted('users'));

        $access = new Access();
        $access->permit('auth', 'public');
        $access->permit('auth', 'root;registered');
        $access->addGroup('public');
        $this->assertTrue($access->isPermitted('auth'));
    }

}
