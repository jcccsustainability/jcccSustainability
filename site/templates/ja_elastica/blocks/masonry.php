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
  * This template uses Masonary Script from http://masonry.desandro.com - released under MIT license
	*/
?>
<script type="text/javascript">

jQuery(function($){
	var $container = $('#ja-main');

	var curr_layout = '';
	var colW = 0;
	
	//detect layout width
	if ($(window).width() >= 720) {
		curr_layout = 'fixed';
		colW = 240;
	} else { 
		curr_layout = 'fluid';
		colW = $container.width() / 2;
	}
	//init layout masonry
	$container.masonry({
		itemSelector: '.ja-masonry',
		columnWidth : colW,
		isAnimated: true,
		isResizable: true
	});
	
	var reloadMasonry = function () {
		$container.masonry( 'reload' );
	};
	
	//change columnWidth depend on the wrapper width, specify for this template
	$(window).bind( 'smartresize.masonry', function() { 
        //detect layout width
		if ($('#ja-main').width() >= 720) {
			//fix width layout - reload one time			
			if (curr_layout != 'fixed') {
				curr_layout = 'fixed';
				$container.masonry( 'option', { columnWidth: 240, isResizable: true } );
				$container.masonry( 'reload' );
			}
		} else {
			//update column width
			$container.masonry( 'option', { columnWidth: $container.width() / 2, isResizable: false } );
			//reload layout
			$container.masonry( 'reload' );

			curr_layout = 'fluid';
		}
  });

	// Check bricks height changed - relayout
	$(function (){
		//store height for all bricks
		$('.ja-masonry').each (function(i, el){
			var el = $(this);
			el.data('h', el.height());
		});
		
		//interval check
		$container.data('interval-timer', setInterval(function () {
			//detect change on masonry bricks height
			$('.ja-masonry').each (function(i){
				var el = $(this);
				if (el.data('h') != el.height()) {
					el.data('h', el.height());
					reloadMasonry ();
					return false;
				}
			});
		}, 2000));
	});
}); 
</script>