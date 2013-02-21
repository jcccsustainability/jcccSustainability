<?php
/**
 * ------------------------------------------------------------------------
 * JA T3v2 System Plugin
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.cache.cache');

/**
 * T3Cache class
 *
 * @package JAT3.Core
 */
class T3Cache extends JObject
{
    const T3_CACHE_GROUP  = 't3_pages';
    const T3_CACHE_ASSETS = 't3_assets';

    var $cache        = null;
    var $_devmode     = false;

    var $started = array();
    var $buffer = array();
    var $_options = null;

    /**
     * Constructor
     *
     * @param bool $devmode    Indicate development mode or not
     */
    public function __construct ($devmode = true)
    {
        $this->_devmode = $devmode;
        $conf = JFactory::getConfig();
        $options = array(
            'defaultgroup' => self::T3_CACHE_GROUP,
            'caching'      => true,
            'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache'),
            'lifetime'      => (int)$conf->get('cachetime') * 60,
        );

        //JFactory::getCache();
        //$this->cache = new JCache($options);
        $this->cache = JCache::getInstance('', $options);
    }

    /**
     * Get instance of T3Cache
     *
     * @param bool $devmode    Developed mode
     *
     * @return T3Cache
     */
    public static function getT3Cache ($devmode = true)
    {
        static $t3cache = null;
        if ($t3cache == null) {
            $t3cache = new T3Cache($devmode);
        }
        return $t3cache;
    }

    /**
     * Store cached data by key & group
     *
     * @param string  $data     Cached data
     * @param string  $key      Cached key
     * @param string  $group    Cached group
     *
     * @return bool  True if cache stored
     */
    public function store($data, $key, $group = null)
    {
        // Not store when devmode = true
        if ($this->_devmode) return false;

        $cache = $this->cache;

        return $cache->store($data, $key, $group);
    }

    /**
     * Get cached data by key & group
     *
     * @param string $key      Cached key
     * @param string $group    Cached group
     *
     * @return mixed  False if failure or cached data string
     */
    public function get($key, $group = null)
    {
        // Nothing was store when devmode = true
        if ($this->_devmode) return false;

        $cache = $this->cache;

        return $cache->get($key, $group);
    }

    /**
     * Store cached object by key & T3_CACHE_ASSETS
     *
     * @param object $object    Cached object (don't contain resource type)
     * @param string $key       Cached key
     *
     * @return bool  True if cache stored
     */
    public function storeObject($object, $key)
    {
        // Not store object when devmode = true
        if ($this->_devmode) return false;

        try {
            $data  = serialize($object);
        } catch(Exception $e){
            return false;
        }
        
        $cache = $this->cache;
        return $cache->store($data, $key, T3Cache::T3_CACHE_ASSETS);
    }

    /**
     * Get cached object by key & T3_CACHE_ASSETS
     *
     * @param string $key   Cached key
     *
     * @return mixed   False if failure or cached object
     */
    public function getObject($key)
    {
        // No object was store when devmode = true
        if ($this->_devmode) return false;

        $cache = $this->cache;
        $data  = $cache->get($key, T3Cache::T3_CACHE_ASSETS);
        $object = unserialize($data);
        return $object;
    }

    /**
     * Get data file by key & T3_CACHE_ASSESTS
     *
     * @param string $data    Cached data file
     * @param string $key     Cached key
     *
     * @return bool  True if cache stored
     */
    public function storeFile($data, $key)
    {
        // No file was store when devmode = true
        if ($this->_devmode) return false;

        $cache = $this->cache;

        return $cache->store($data, $key, T3Cache::T3_CACHE_ASSETS);
    }

    /**
     * Get data file by key & T3_CACHE_ASSESTS
     *
     * @param string $key   Cached key
     *
     * @return mixed  False if failure or cached data file
     */
    public function getFile($key)
    {
        // No file was store when devmode = true
        if ($this->_devmode) return false;

        $cache = $this->cache;
        $data  = $cache->get($key, T3Cache::T3_CACHE_ASSETS);

        return $data;
    }

    /**
     * Set caching
     *
     * @param bool $enabled    Enabled caching
     *
     * @return void
     */
    public function setCaching($enabled)
    {
        $this->cache->setCaching($enabled);
    }

    /**
     * Clean cache
     *
     * @param int $t3assets    Level of cleaning
     *
     * @return void
     */
    public static function clean($t3assets = 0)
    {
        $cache = T3Cache::getT3Cache();
        $cache->_clean($t3assets);
    }

    /**
     * Clean T3 cache
     * If $t3assets > 0,  deleted all cached content in defaultgroup
     * If $t3assets > 1,  deleted all cached content in assets group
     * If $t3assets > 2, deleted all cached content in css/js optimize folder
     *
     * @param int $t3assets    Level cache
     *
     * @return void
     */
    private function _clean($t3assets = 0)
    {
        $cache = $this->cache;
        // Clear cache in default group folder
        if ($t3assets > 0) {
            $cache->clean();
        }

        // Clear cache in assets folder
        if ($t3assets > 1) {
            $cache->clean(self::T3_CACHE_ASSETS);
        }

        if ($t3assets > 2) {
            //clean t3-assets folder, the cache for js/css
            $templates = T3Common::get_active_templates();
            //T3Common::log(var_export($templates, true));
            foreach ($templates as $template) {
                $file = T3Path::path("templates/$template").DS.'params.ini';
                if (is_file($file)) {
                    $content = file_get_contents($file);
                    // $params = new JParameter($content);
					
					// Strict standards: Declaration of JParameter::loadSetupFile() should be compatible with that of JRegistry::loadSetupFile() 
					$params =  $content;
					if (!($content instanceof JRegistry))
					{
						$params =  new JRegistry($content);
					}			
					
                    $cache_path = $params->get('optimize_folder', 't3-assets');
                    $path = T3Path::path($cache_path);
                    //T3Common::log($path);
                    if (is_dir($path)) {
                        @JFolder::delete($path);
                    }
                }
            }
        }
    }

    /**
     * Get page key from URI, browser (version), params (cookie params)
     *
     * @return mixed  NULL if devmode/noncache or string key code
     */
    public static function getPageKey ()
    {
        static $key = null;
        if ($key) return $key;

        // No cache in devmode
        $t3cache = T3Cache::getT3Cache();
        if ($t3cache->_devmode) return null;

        // No cache when disable T3 cache
        $config = T3Common::get_template_based_params();
        if ($config->get('cache', 0) == 0) return null;

        // TODO: need to move in cache page code at the end of onAfterRender
        $mainframe = JFactory::getApplication();
        $messages = $mainframe->getMessageQueue();
        // Ignore cache when there're some message
        if (is_array($messages) && count($messages)) {
            return null;
        }

        // If user log-in, ignore cache
        $user = JFactory::getUser();
        if (!$user->get('guest') || $_SERVER['REQUEST_METHOD'] != 'GET') {
            return null;
        }

        // If ie6, ignore cache
        $isIE6 = T3Template::isIE6();
        if ($isIE6) {
            return null;
        }

        // Don't cache when offline
        $cfg = JFactory::getConfig();
        if ($cfg->get('offline')) {
            return null;
        }

        $uri = JRequest::getURI();

        //$browser = T3Common::getBrowserSortName() . "-" . T3Common::getBrowserMajorVersion();
        $mobile  = T3Common::mobile_device_detect();
        $params  = T3Parameter::getInstance();
        $cparams = '';
        foreach ($params->_params_cookie as $k => $v) {
            $cparams .= $k . "=" . $v . '&';
        }

        //$key = "page - URI: $uri; Browser: $browser; Params: $cparams";
        $key = "page - URI: $uri; Mobile: $mobile; Params: $cparams";

        //T3Common::log($key . '  ' . T3Common::getBrowserSortName() . "-" . T3Common::getBrowserMajorVersion());

        return $key;
    }

    /**
     * Get preload key from template information
     *
     * @param string  $template    String template information
     *
     * @return mixed  NULL if devmode or keycode string
     */
    public static function getPreloadKey ($template)
    {
        $t3cache = T3Cache::getT3Cache();
        if ($t3cache->_devmode) return null; //no cache in devmode*/
        $string = 'template-' . $template;

        return $string;
    }

    /**
     * Get profile key from active profile & default profile
     *
     * @return mixed  NULL if devmode or keycode string
     */
    public static function getProfileKey ()
    {
        $t3cache = T3Cache::getT3Cache();
        if ($t3cache->_devmode) return null; //no cache in devmode

        $profile = T3Common::get_active_profile().'-'.T3Common::get_default_profile();
        $string  = 'profile-'.$profile;

        return $string;
    }

    /**
     * Get theme key from active layout & active themes
     *
     * @return mixed   NULL if devmode or keycode string
     */
    public static function getThemeKey ()
    {
        $t3cache = T3Cache::getT3Cache();
        if ($t3cache->_devmode) return null; //no cache in devmode

        $themes = T3Common::get_active_themes();
        $layout = T3Common::get_active_layout();
        $string = 'theme-infos-'.$layout;
        if (is_array($themes)) $string .= serialize($themes);

        return $string;
    }
}
