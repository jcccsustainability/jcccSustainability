<?php
/**
 * ------------------------------------------------------------------------
 * JA Elastica Template for Joomla 2.5
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 


/** 
 * This is an extra block which is only available when the screen is extra-wide (>1440px)
 * The content of this block is loaded using ajax
 */
?>
<?php
$positions = preg_split ('/,/', T3Common::node_data($block));
$parent = T3Common::node_attributes($block, 'parent', 'middle');
$style = $this->getBlockStyle ($block, $parent);
if (!$this->countModules (T3Common::node_data($block))) return;
?>
<?php
// Add css for this extra-wide layout
$this->addCSS ('css/layout-extra-wide.css', 'only screen and (min-width:1440px)');
$this->addStyleDeclaration ('/* hide by default */
#ja-extra-col {
	display: none;
	width: 240px;
}

#ja-extra-col-loading {
	display: none;
}');
?>

<div id="ja-extra-col" class="clearfix">
	<div id="ja-extra-col-loading">Loading ... </div>
</div>

<script type="text/javascript">
  // <![CDATA[ 
	/**
	 * call ajax to update content for this block 
	 * Ajax content is loaded only once time
	 */
	var jaLoadExtraCol = function () {
		// do nothing if extra-col not shown
		if (!$('ja-extra-col') || $('ja-extra-col').getStyle ('display')=='none') {
			return;
		}
		// do nothing if loaded
		if (this.loaded) {
			return;
		}
		this.loaded = true;
		// show progress
		$('ja-extra-col-loading').setStyle ('display', 'block');
		// using request.html to load ajax content. The content will be appended into ja-extra-col block
		var ajax = new Request.HTML ({
			'url': '<?php echo JURI::current() ?>',
			append: $('ja-extra-col'),
			onComplete: function () {$('ja-extra-col-loading').setStyle ('display', 'none');}
		});
		// load content by ajax
		<?php foreach ($positions as $position) :
			if ($this->countModules($position)) : ?>
			ajax.get ('ajax=modules&style=jaxhtml&name=<?php echo $position ?>');
		<?php endif;
		endforeach ?>
	};
	
	window.addEvent ('load', jaLoadExtraCol);
	window.addEvent ('resize', jaLoadExtraCol);
  // ]]> 		
</script>