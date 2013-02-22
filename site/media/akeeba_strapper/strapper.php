<?php
/**
 * Akeeba Strapper
 * A handy distribution of namespaced jQuery, jQuery UI and Twitter
 * Bootstrapper for use with Akeeba components.
 */
 
defined('_JEXEC') or die();

if(!defined('FOF_INCLUDED')) {
	include_once JPATH_SITE.'/libraries/fof/include.php';
}
 
class AkeebaStrapper {
 	/** @var bool True when jQuery is already included */
 	public static $_includedJQuery = false;
 	
 	/** @var bool True when jQuery UI is already included */
 	public static $_includedJQueryUI = false;
 	
 	/** @var bool True when Bootstrap is already included */
 	public static $_includedBootstrap = false;
 	
 	/** @var array List of URLs to Javascript files */
 	public static $scriptURLs = array();
 	
 	/** @var array List of script definitions to include in the head */
 	public static $scriptDefs = array();
 	
 	/** @var array List of URLs to CSS files */
 	public static $cssURLs = array();
 	
 	/** @var array List of CSS definitions to include in the head */
 	public static $cssDefs = array();
 	
 	/** @var string The jQuery UI theme to use, default is 'smoothness' */
 	protected static $jqUItheme = 'smoothness';
 	
	/** @var string A query tag to append to CSS and JS files for versioning purposes */
	public static $tag = null;
	
	/** @var bool Should I preload my Javascript/CSS files on Joomla! 2.5 or earlier? */
	public static $preloadOnOldJoomla = true;
	
	/**
	 * Is this something running under the CLI mode?
	 * @staticvar bool|null $isCli
	 * @return null 
	 */
	public static function isCli()
	{
		static $isCli = null;
		if(is_null($isCli)) {
			try {
				if(is_null(JFactory::$application)) {
					$isCLI = true;
				} else {
					$isCLI = version_compare(JVERSION, '1.6.0', 'ge') ? (JFactory::getApplication() instanceof JException) : false;
				}
			} catch(Exception $e) {
				$isCLI = true;
			}		
		}
		return $isCli;
	}
	
 	/**
 	 * Loads our namespaced jQuery, accessible through akeeba.jQuery
 	 */
 	public static function jQuery()
 	{
		if(self::isCli()) return;
		
 		self::$_includedJQuery = true;
 		
		if(version_compare(JVERSION, '3.0', 'lt')) {
			// Joomla! 2.5 and earlier, load our own library
			self::$scriptURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/akeebajq.js');
		} else {
			if(AkeebaStrapper::isCli()) return;
			// Joomla! 3.0 and later, use Joomla! code to load the library
			JHtml::_('jquery.framework');
			$script = <<<ENDSCRIPT
if(typeof(akeeba) == 'undefined') {
	var akeeba = {};
}
if(typeof(akeeba.jQuery) == 'undefined') {
	akeeba.jQuery = jQuery.noConflict();
}
ENDSCRIPT;
			JFactory::getDocument()->addScriptDeclaration($script);
		}
 	}
 	
 	/**
 	 * Sets the jQuery UI theme to use. It must be the name of a subdirectory of
 	 * media/akeeba_strapper/css or templates/<yourtemplate>/media/akeeba_strapper/css
 	 *
 	 * @param $theme string The name of the subdirectory holding the theme
 	 */
 	public static function setjQueryUItheme($theme)
 	{
		if(self::isCli()) return;
		
 		self::$jqUItheme = $theme;
 	}
 	
	/**
	 * Loads our namespaced jQuery UI and its stylesheet
	 */
 	public static function jQueryUI()
 	{
		if(self::isCli()) return;
		
 		if(!self::$_includedJQuery) {
 			self::jQuery();
 		}
 	
 		self::$_includedJQueryUI = true;
 		$theme = self::$jqUItheme;
 		
		$url = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/akeebajqui.js');
		if(version_compare(JVERSION, '3.0', 'lt')) {
			// Joomla! 2.5, use our magic loader
			self::$scriptURLs[] = $url;
		} else {
			// Joomla! 3.0 and later, use Joomla!'s loader
			if(AkeebaStrapper::isCli()) return;
			JFactory::getDocument()->addScript($url);
		}
 		self::$cssURLs[] = FOFTemplateUtils::parsePath("media://akeeba_strapper/css/$theme/theme.css");
 	}
 	
 	/**
 	 * Loads our namespaced Twitter Bootstrap. You have to wrap the output you want style
 	 * with an element having the class akeeba-bootstrap added to it.
 	 */
 	public static function bootstrap()
 	{
		if(self::isCli()) return;
		
 		if(!self::$_includedJQuery) {
 			self::jQuery();
 		}
 	
		if(version_compare(JVERSION, '3.0', 'lt')) {
			self::$scriptURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/bootstrap.min.js');
			self::$cssURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/css/bootstrap.min.css');
		} else {
			self::$cssURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/css/bootstrap.j3.css');
		}
		
		self::$cssURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/css/strapper.css');
 	}
 	
 	/**
 	 * Adds an arbitraty Javascript file.
 	 *
 	 * @param $path string The path to the file, in the format media://path/to/file
 	 */
 	public static function addJSfile($path)
 	{
 		self::$scriptURLs[] = FOFTemplateUtils::parsePath($path);
 	}
 	
 	/**
 	 * Add inline Javascript
 	 *
 	 * @param $script string Raw inline Javascript
 	 */
	public static function addJSdef($script)
	{
		self::$scriptDefs[] = $script;
	}
	
	/**
	 * Adds an arbitraty CSS file.
	 *
	 * @param $path string The path to the file, in the format media://path/to/file
	 */
 	public static function addCSSfile($path)
 	{
 		self::$cssURLs[] = FOFTemplateUtils::parsePath($path);
 	}
 	
	/**
	 * Add inline CSS
	 *
	 * @param $style string Raw inline CSS
	 */
 	public static function addCSSdef($style)
 	{
 		self::$cssDefs[] = $style;
 	}
 }
 
 /**
  * This is a workaround which ensures that Akeeba's namespaced JavaScript and CSS will be loaded
  * wihtout being tampered with by any system pluign. Moreover, since we are loading first, we can
  * be pretty sure that namespacing *will* work and we won't cause any incompatibilities with third
  * party extensions loading different versions of these GUI libraries.
  *
  * This code works by registering a system plugin hook :) It will grab the HTML and drop its own
  * JS and CSS definitions in the head of the script, before anything else has the chance to run.
  *
  * Peace.
  */
 function AkeebaStrapperLoader()
 {	 
 	// If there are no script defs, just go to sleep
 	if(
		empty(AkeebaStrapper::$scriptURLs) &&
		empty(AkeebaStrapper::$scriptDefs) &&
		empty(AkeebaStrapper::$cssDefs) &&
		empty(AkeebaStrapper::$cssURLs)
	) {
		return;
	}
	
	// Get the query tag
	$tag = AkeebaStrapper::$tag;
	if(empty($tag)) {
		$tag = '';
	} else {
		$tag = '?'.ltrim($tag,'?');
	}
 
 	$myscripts = '';
	
	if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
		$buffer = JResponse::getBody();
	}
	
 	if(!empty(AkeebaStrapper::$scriptURLs)) foreach(AkeebaStrapper::$scriptURLs as $url)
 	{
		if(AkeebaStrapper::$preloadOnOldJoomla && (basename($url) == 'bootstrap.min.js')) {
			// Special case: check that nobody else is using bootstrap[.min].js on the page.
			$scriptRegex="/<script [^>]+(\/>|><\/script>)/i";
			$jsRegex="/([^\"\'=]+\.(js)(\?[^\"\']*){0,1})[\"\']/i";
			preg_match_all($scriptRegex, $buffer, $matches);
			$scripts=@implode('',$matches[0]);
			preg_match_all($jsRegex,$scripts,$matches);
			$skip = false;
			foreach( $matches[1] as $scripturl ) {
				$scripturl = basename($scripturl);
				if(in_array($scripturl, array('bootstrap.min.js','bootstrap.js'))) {
					$skip = true;
				}
			}
			if($skip) continue;
		}
		if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
			$myscripts .= '<script type="text/javascript" src="'.$url.$tag.'"></script>'."\n";
		} else {
			JFactory::getDocument()->addScript($url.$tag);
		}
 	}
 	
 	if(!empty(AkeebaStrapper::$scriptDefs))
 	{
		if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
			$myscripts .= '<script type="text/javascript" language="javascript">'."\n";
		} else {
			$myscripts = '';
		}
 		foreach(AkeebaStrapper::$scriptDefs as $def)
 		{
 			$myscripts .= $def."\n";
 		}
		if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
			$myscripts .= '</script>'."\n";
		} else {
			JFactory::getDocument()->addScriptDeclaration($myscripts);
		}
 	}
	
 	if(!empty(AkeebaStrapper::$cssURLs)) foreach(AkeebaStrapper::$cssURLs as $url)
 	{
		if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
			$myscripts .= '<link type="text/css" rel="stylesheet" href="'.$url.$tag.'" />'."\n";
		} else {
			JFactory::getDocument()->addStyleSheet($url.$tag);
		}
 	}
 	
 	if(!empty(AkeebaStrapper::$cssDefs))
 	{
 		$myscripts .= '<style type="text/css">'."\n";
 		foreach(AkeebaStrapper::$cssDefs as $def)
 		{
			if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
				$myscripts .= $def."\n";
			} else {
				JFactory::getDocument()->addScriptDeclaration($def."\n");
			}
 		}
 		$myscripts .= '</style>'."\n";
 	}
 	
	if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
		$pos = strpos($buffer, "<head>");
		if($pos > 0)
		{
			$buffer = substr($buffer, 0, $pos + 6).$myscripts.substr($buffer, $pos + 6);
			JResponse::setBody($buffer);
		}
	}
 }
 
// Add our pseudo-plugin to the application event queue
if(!AkeebaStrapper::isCli()) {
	$app = JFactory::getApplication();
	if(version_compare(JVERSION, '3.0', 'lt') && AkeebaStrapper::$preloadOnOldJoomla) {
		$app->registerEvent('onAfterRender', 'AkeebaStrapperLoader');
	} else {
		$app->registerEvent('onBeforeRender', 'AkeebaStrapperLoader');
	}
}