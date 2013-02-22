<?php
/**
 *
 * @license GNU General Public License version 3 ;
 */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

class plgSystemeasyheader17v23 extends JPlugin

{function __construct(&$subject, $config)
	{parent::__construct($subject, $config);
 		$this->loadLanguage();}

    function onAfterDispatch()
    {
// collect parameters
//scripts
        $ehscript1 = $this->params->get('ehscript1');
        $ehscript2 = $this->params->get('ehscript2');
        $ehscript3 = $this->params->get('ehscript3');
        $ehscript4 = $this->params->get('ehscript4');
        $ehscript5 = $this->params->get('ehscript5');
//styles
        $ehstyle1 = $this->params->get('ehstyle1');
        $ehstyle2 = $this->params->get('ehstyle2');
        $ehstyle3 = $this->params->get('ehstyle3');
        $ehstyle4 = $this->params->get('ehstyle4');
        $ehstyle5 = $this->params->get('ehstyle5');
//misc
        $ehadditional = $this->params->get('ehadditional');
        $ehemulate = $this->params->get('ehemulate');
        $ehfastload = $this->params->get('ehfastload');
	$x="";

		$caption = $this->params->get('caption');
		$jquery = $this->params->get('jquery');
		$jqueryv = $this->params->get('jqueryv');
		$jqueryui = $this->params->get('jqueryui');
		$jqueryuiv = $this->params->get('jqueryuiv');
		$swfobject = $this->params->get('swfobject');
//fonts
		$serif1 = $this->params->get('serif1');
		$serif2 = $this->params->get('serif2');
		$serif3= $this->params->get('serif3');
		$serif4 = $this->params->get('serif4');

		$sanserif1 = $this->params->get('sanserif1');
		$sanserif2 = $this->params->get('sanserif2');
		$sanserif3 = $this->params->get('sanserif3');
		$sanserif4 = $this->params->get('sanserif4');

		$display1 = $this->params->get('display1');
		$display2 = $this->params->get('display2');
		$display3 = $this->params->get('display3');
		$display4 = $this->params->get('display4');

		$hand1 = $this->params->get('hand1');
		$hand2 = $this->params->get('hand2');
		$hand3 = $this->params->get('hand3');
		$hand4 = $this->params->get('hand4');
		$googleload="";
		$families = $this->params->get('customfont');
        	$families=trim($families);
//create string containing font names

		if(!empty($serif1)){$families.="'".$serif1."',";}
		if(!empty($serif2)){$families.="'".$serif2."',";}
		if(!empty($serif3)){$families.="'".$serif3."',";}
		if(!empty($serif4)){$families.="'".$serif4."',";}

		if(!empty($sanserif1)){$families.="'".$sanserif1."',";}
		if(!empty($sanserif2)){$families.="'".$sanserif2."',";}
		if(!empty($sanserif3)){$families.="'".$sanserif3."',";}
		if(!empty($sanserif4)){$families.="'".$sanserif4."',";}

		if(!empty($display1)){$families.="'".$display1."',";}
		if(!empty($display2)){$families.="'".$display2."',";}
		if(!empty($display3)){$families.="'".$display3."',";}
		if(!empty($display4)){$families.="'".$display4."',";}

		if(!empty($hand1)){$families.="'".$hand1."',";}
		if(!empty($hand2)){$families.="'".$hand2."',";}
		if(!empty($hand3)){$families.="'".$hand3."',";}
		if(!empty($hand4)){$families.="'".$hand4."',";}

        $families=substr_replace($families,"",-1,1);

//function to remove any unwanted tags in head section of page
        function delHeadTag($tagtype, $tagtext)
        {
            $document = &JFactory::getDocument();

            //retrieve and extract tagtype items reset head array
            $head = $document->getHeadData();
            $listjs = $head[$tagtype];
            $head[$tagtype] = "";

            //remove individual unwanted tag
            foreach ($listjs as $item => $tag) {
                if (strpos($item, $tagtext) == false) {
                    $head[$tagtype][$item] = $tag;
                }
            }
            //update head
            $document->setHeadData($head);
        }

//output  selected tags to page header (public site only not admin)
		$app=JFactory::getApplication();
        $document=&JFactory::getDocument();

        if($app->isSite()){
//Set IE7 emulation
        if ($ehemulate==1){$document->setMetaData('X-UA-Compatible', 'IE=EmulateIE7', true);}

//Add script references
        if (!empty($ehscript1)){$document->addScript($ehscript1);}
        if (!empty($ehscript2)){$document->addScript($ehscript2);}
        if (!empty($ehscript3)){$document->addScript($ehscript3);}
        if (!empty($ehscript4)){$document->addScript($ehscript4);}
        if (!empty($ehscript5)){$document->addScript($ehscript5);}

//Add styleheet references
        if (!empty($ehstyle1)){$document->addStyleSheet($ehstyle1);}
        if (!empty($ehstyle2)){$document->addStyleSheet($ehstyle2);}
        if (!empty($ehstyle3)){$document->addStyleSheet($ehstyle3);}
        if (!empty($ehstyle4)){$document->addStyleSheet($ehstyle4);}
        if (!empty($ehstyle5)){$document->addStyleSheet($ehstyle5);}

//Add custom tags
        if (!empty($ehadditional )){$document->addCustomTag($ehadditional);}

//remove mootools-core.js
        if ($ehfastload==2){delHeadTag("scripts", "/mootools-core.js");}

//remove caption.js
		if ($caption==2){delHeadTag("scripts", "/caption.js");}

//add compressed jquery from Google
		if ($jquery==2){$googleload.="google.load(\"jquery\", \"".$jqueryv."\");";$x="1";}

//add compressed jqueryui from Google
		if ($jqueryui==2){$googleload.="google.load(\"jqueryui\", \"".$jqueryuiv."\");";$x="1";}

//add compressed swfobject from Google
		if ($swfobject==2){ $googleload.="google.load(\"swfobject\", \"2.2\");";$x="1";}

//add webfont from Google
		if (!empty($families)){$googleload.="google.load(\"webfont\", \"1.0.21\");google.setOnLoadCallback(function(){WebFont.load({google:{families:[".$families." ]}});});";$x="1";}

//collect Google addons and publish to site pages.
		if($x==1){
		$document = &JFactory::getDocument();
		$document->addCustomTag('<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">'.$googleload.'</script>');

			}

	    }
	}
}

