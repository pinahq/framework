<?php

namespace Pina;

class Site
{
    private static $config = false;

    private static $id = 0;
    private static $accountId = 0;
    private static $domain = "";
    private static $enabled = 'Y';
    private static $key = "";
    private static $template = "";

    public static function id()
    {
        return self::$id;
    }

    public static function accountId($siteId = false)
    {
        if (empty($siteId) || $siteId == self::$id) {
            return self::$accountId;
        }
        
        return SQL::table('cody_site')->whereBy('site_id', $siteId)->value('account_id');
    }

    public static function enabled()
    {
        return self::$enabled;
    }

    public static function key()
    {
        return self::$key;
    }

    public static function domain()
    {
        return self::$domain;
    }

    public static function template()
    {
        return self::$template;
    }

    public static function baseUrl($id = false)
    {
        if ($id === false) {
            $id = self::$id;
        }

        $id = intval($id);
        if (empty($id)) {
            if (empty(self::$config)) {
                self::$config = Config::load('site');
            }
            
            return "http://".self::$config['default']['domain'].'/';
        }

        $domain = self::$domain;
        if ($id != self::$id) {
            $domain = SQL::table('cody_site')->whereBy('site_id', $id)->value('site_domain');
        }

        return "http://".$domain."/";
    }

    public static function initById($siteId)
    {
        $siteId = intval($siteId);
        
        if (empty(self::$config)) {
            self::$config = Config::load('site');
        }

        if ($siteId == 0) {
            
            self::$id = 0;
            self::$accountId = 0;
            self::$domain = self::$config['default']['domain'];
            self::$key = self::$config['default']['key'];
            self::$template = self::$config['default']['template'];
            self::$enabled = self::$config['default']['enabled'];
            return true;
        }
        
        if (empty(self::$config['table'])) {
            return false;
        }
        
        $table = new self::$config['table'];
        $site = $table->whereBy('site_id', $siteId)
            ->select('site_id,account_id,site_domain,site_key,site_template,site_enabled')
            ->first();
        
        if (empty($site)) {
            return false;
        }

        list(self::$id, self::$accountId, self::$domain, self::$key, self::$template, self::$enabled) = $site;

        return true;
    }

    public static function init($host = '')
    {
        if (empty($host)) {
            return false;
        }

        $host = str_replace("www.", "", $host);
        
        if (empty(self::$config)) {
            self::$config = Config::load('site');
        }

        if (strcasecmp($host, self::$config['default']['domain']) == 0 
            || strcasecmp("www.".$host, self::$config['default']['domain']) == 0
        ) {
            self::$id = 0;
            self::$accountId = 0;
            self::$domain = self::$config['default']['domain'];
            self::$key = self::$config['default']['key'];
            self::$template = self::$config['default']['template'];
            self::$enabled = self::$config['default']['enabled'];
            return true;
        }
        
        if (!empty(self::$config['table'])) {

            $table = new self::$config['table'];
            $site = $table
                ->whereBy('site_domain', array($host, 'www.'.$host))
                ->select('site_id,account_id,site_domain,site_key,site_template,site_enabled')
                ->first();

            if (!empty($site)) {
                list(self::$id, self::$accountId, self::$domain, self::$key, 
                    self::$template, self::$enabled)
                    = array_values($site);
                return true;
            }
        }

        if (!empty(self::$config['sites']) && is_array(self::$config['sites'])) {
            foreach (self::$config['sites'] as $siteId => $site) {
                if (strcasecmp($host, $site['domain']) == 0 
                    || strcasecmp("www.".$host, $site['domain']) == 0
                ) {
                    self::$id = $siteId;
                    self::$accountId = $site['account_id'];
                    self::$domain = $site['domain'];
                    self::$key = $site['key'];
                    self::$template = $site['template'];
                    self::$enabled = $site['enabled'];
                    return true;
                }
            }
        }

        return false;
    }

}
