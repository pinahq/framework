<?php

namespace Pina\Layouts;

use Exception;
use Pina\App;
use Pina\Controls\Control;
use Pina\Html;
use Pina\Request;
use Pina\Controls\Meta;

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
        $body = Html::tag('body', $this->drawBody());
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
        return Html::zz('header.container section(a.logo[href=/](img[src=/logo.png]))');
    }


    /**
     * @return string
     * @throws Exception
     */
    protected function drawPageHeader()
    {
        $title = strval(Request::getPlace('page_header'));
        if (empty($title)) {
            return '';
        }
        $breadcrumb = Request::getPlace('breadcrumb');
        return Html::zz('nav.breadcrumbs(.container(%+header(h1%)))', $breadcrumb, $title);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function drawFooter()
    {
        return Html::zz('footer.container%', 'Made on PinaFramework (c) Alex Yashin');
    }

    protected function drawHead()
    {
        return Html::tag(
            'head',
            $this->drawTitle()
            . $this->drawMeta()
            . $this->drawIcon()
            . $this->drawCss()
            . $this->drawHeadCounters()
        );
    }

    protected function drawMeta()
    {
        return $this->drawCharset()
            . $this->drawCanonical()
            . $this->drawContentMeta()
            . $this->drawBrowserMeta();
    }

    protected function drawCharset()
    {
        return $this->drawMetaLine(['charset' => 'UTF-8']);
    }

    protected function drawCanonical()
    {
        $resource = Request::getPlace('canonical');
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

    protected function drawMetaLine($attributes)
    {
        if ((!empty($attributes['name']) || !empty($attributes['property'])) && empty($attributes['content'])) {
            return '';
        }
        return Html::tag('meta', '', $attributes);
    }


    protected function drawTitle()
    {
        $parts = [strip_tags(Request::getPlace('page_header')), $this->getCompanyTitle()];
        return Html::tag('title', implode(' - ', array_filter($parts)));
    }

    protected function getCompanyTitle()
    {
        return '';
    }

    protected function drawIcon()
    {
        return '';
    }

    protected function drawHeadCounters()
    {
        return '';
    }

    protected function drawCss()
    {
        return App::assets()->fetch('css');
    }

    protected function drawJs()
    {
        return App::assets()->fetch('js');
    }

}
