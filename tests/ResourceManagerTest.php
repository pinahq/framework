<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\Url;
use Pina\Route;
use Pina\ResourceManager;

class ResourceManagerTest extends TestCase
{

    public function test()
    {
        $manager = new ResourceManager;
        $manager->append('js', "<script>alert('123');</script>");
        $manager->append('js', "<script>alert('234');</script>");
        $this->assertEquals("<script>alert('123');</script>\r\n<script>alert('234');</script>", $manager->fetch('js'));

        $manager = new ResourceManager;
        $manager->append('js', "<script>alert('123');</script>");
        $manager->mode('layout');
        $manager->append('js', "<script>alert('234');</script>");
        $this->assertEquals("<script>alert('234');</script>\r\n<script>alert('123');</script>", $manager->fetch('js'));
    }

}
