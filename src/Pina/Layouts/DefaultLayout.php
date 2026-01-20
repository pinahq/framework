<?php

namespace Pina\Layouts;

use Exception;
use Pina\App;
use Pina\Config;
use Pina\Controls\Control;
use Pina\Controls\IconMeta;
use Pina\Controls\Meta;
use Pina\CSRF;
use Pina\Html;
use Pina\Input;
use Pina\Menu\MainMenu;
use Pina\Menu\RouterSiblingMenu;
use Pina\Menu\SectionMenuComposer;

class DefaultLayout extends Control
{

    /**
     * @return string
     * @throws Exception
     */
    protected function draw()
    {
        $this->loadResources();

        //вначале генерируем тег body, чтобы все контролы внутри body смогли зарегистировать css перед генерацией head
        $body = Html::tag('body', $this->drawBody(), $this->makeAttributes());
        $head = Html::tag('head', $this->drawHead());
        return '<!DOCTYPE html>' . "\n" . Html::tag('html', $head . $body . $this->drawJs());
    }

    protected function loadResources()
    {

    }

    /**
     * @return string
     * @throws Exception
     */
    protected function drawBody()
    {
        return $this->drawHeader()
            . $this->drawSectionMenu()
            . $this->drawPageHeader()
            . $this->drawInnerBefore()
            . $this->drawInner()
            . $this->drawInnerAfter()
            . $this->drawFooter();
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function drawHeader()
    {
        return Html::nest('header/.container', $this->drawMainMenu());
    }

    protected function drawMainMenu()
    {
        return clone App::load(MainMenu::class);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function drawPageHeader()
    {
        $title = App::place('page_header')->make();
        if (empty($title)) {
            return '';
        }
        $breadcrumb = App::place('breadcrumb')->make();
        return Html::zz(
            '.page-header(.container(%+h1%+%))',
            $breadcrumb ? Html::nest('nav.breadcrumbs', $breadcrumb) : '',
            $title,
            App::load(RouterSiblingMenu::class)
        );
    }

    protected function drawSectionMenu()
    {
        /** @var SectionMenuComposer $composer */
        $composer = App::load(SectionMenuComposer::class);
        $menu = $composer->resolve(Input::getResource());
        $menu->addClass('bar');
        $r = strval($menu);
        return $r ? Html::nest('.section-header/.container', $r) : '';
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function drawFooter()
    {
        $domain = Config::get('app', 'host');
        return Html::zz(
            'footer(.container(a[href=/]%+%))',
            $domain,
            $this->drawCopyright()
        );
    }

    protected function drawCopyright()
    {
        return Html::nest('span.copyright', '© 2007-' . date('Y') . ' Alex Yashin');
    }

    protected function drawHead()
    {
        return $this->drawMeta()
            . $this->drawIcon()
            . $this->drawCss()
            . $this->drawHeadCounters();
    }

    protected function drawMeta()
    {
        return $this->drawCharset()
            . $this->drawCanonical()
            . $this->drawContentMeta()
            . $this->drawBrowserMeta()
            . $this->drawCSRFMeta();
    }

    protected function drawCharset()
    {
        return $this->drawMetaLine(['charset' => 'UTF-8']);
    }

    protected function drawCanonical()
    {
        $resource = App::place('canonical')->make();
        if (empty($resource)) {
            return '';
        }

        return Html::tag('link', '', ['rel' => 'canonical', 'href' => App::link($resource)]);
    }

    protected function drawContentMeta()
    {
        return App::load(Meta::class);
    }

    protected function drawBrowserMeta()
    {
        return
            $this->drawMetaLine(['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'])
            . $this->drawMetaLine(
                [
                    'name' => "viewport",
                    'content' => "width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"
                ]
            );
    }

    protected function drawCSRFMeta()
    {
        return $this->drawMetaLine(['name' => 'csrf-token', 'content' => CSRF::token()]);
    }

    protected function drawMetaLine($attributes)
    {
        if ((!empty($attributes['name']) || !empty($attributes['property'])) && empty($attributes['content'])) {
            return '';
        }
        return Html::tag('meta', '', $attributes);
    }

    protected function drawIcon()
    {
        return App::load(IconMeta::class);
    }

    protected function drawHeadCounters()
    {
        return '';
    }

    protected function drawCss()
    {
        App::assets()->addStyleContent(App::place('styles'));
        return App::assets()->fetch('css');
    }

    protected function drawJs()
    {
        App::assets()->addScriptContent(App::place('scripts'));
        return App::assets()->fetch('js');
    }

}