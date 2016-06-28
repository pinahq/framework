<?php

use Pina\Access;
use Pina\App;

class AccessTest extends PHPUnit_Framework_TestCase
{

    public function testPermit()
    {
        App::env('test');

        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertTrue(Access::isPrivate('accounts/5/items', 'show'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        Access::addGroup('enabled');
        Access::addGroup('provider');
        Access::addGroup('owner');
        Access::addCondition('self', array('user_id' => 2));
        $this->assertTrue(Access::isPermitted('accounts/5/items', 'show'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        Access::addGroup('enabled');
        Access::addGroup('provider');
        $this->assertFalse(Access::isPermitted('accounts/5/items', 'show'));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;provider,manager');
        $this->assertFalse(Access::isPermitted('accounts/5/items', 'show', array()));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self');
        Access::addCondition('self', array('account_id' => 5));
        $this->assertTrue(Access::isPermitted('accounts/5/items', 'show', array()));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self,provider');
        Access::addGroup('provider');
        Access::addCondition('self', array('account_id' => 5));
        $this->assertTrue(Access::isPermitted('accounts/5/items', 'show', array()));

        Access::reset();
        Access::permit('accounts/:account_id/items', 'provider,owner;self,provider');
        Access::addGroup('provider');
        Access::addCondition('self', array('account_id' => 4));
        $this->assertFalse(Access::isPermitted('accounts/5/items', 'show', array()));

        Access::reset();
        Access::permit('accounts/:account_id/users', 'provider;buyer');
        Access::permit('accounts/:account_id/users/:user_id/lists', 'buyer');
        Access::addGroup('provider');
        $this->assertTrue(Access::isPermitted('accounts/5/users', 'show', array()));
        $this->assertFalse(Access::isPermitted('accounts/5/users/5/lists', 'show', array()));
    }

}
