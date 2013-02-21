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

// No direct access
defined('_JEXEC') or die;
?>
<?php if ($this->isIE() && ($this->isRTL())) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php } else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php } ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>">

<head>
    <?php //gen head base on theme info
    $this->showBlock ('head');
    ?>

    <?php
    $blocks = T3Common::node_children($this->getBlocksXML ('head'), 'block');
    foreach ($blocks as $block) :
        $this->showBlock ($block);
    endforeach;
    ?>

    <?php echo $this->showBlock ('css') ?>
</head>

<body id="bd" class="<?php if (!T3Common::mobile_device_detect()):?>bd<?php endif;?> <?php echo $this->getBodyClass();?>">
<a name="Top" id="Top"></a>
<div id="ja-wrapper">

	<?php
	$blks = &$this->getBlocksXML ('top');
	$blocks = &T3Common::node_children($blks, 'block');
	foreach ($blocks as $block) :
			$this->showBlock ($block);
	endforeach;
	?>

	<!-- MAIN CONTAINER -->
	<div id="ja-container" class="wrap <?php echo $this->getColumnWidth('cls_w')?$this->getColumnWidth('cls_w'):'ja-mf'; ?> clearfix">
		<div id="ja-main-wrap" class="main clearfix">
			<div id="ja-main" class="clearfix">
				<?php if (!$this->getParam ('hide_content_block', 0)): ?>
					<div id="ja-content" class="ja-content ja-masonry">
						<?php
						//content-top
						if($this->hasBlock('content-top')) :
						$block = &$this->getBlockXML ('content-top');
						?>
						<div id="ja-content-top" class="ja-content-top clearfix">
							<?php $this->showBlock ($block); ?>
						</div>
						<?php endif; ?>
					
						<div id="ja-content-main" class="ja-content-main cleafix">
							<?php echo $this->loadBlock ('message') ?>
							<?php echo $this->showBlock ('content') ?>
						</div>
						
						<?php
						//content-bottom
						if($this->hasBlock('content-bottom')) :
						$block = &$this->getBlockXML ('content-bottom');
						?>
						<div id="ja-content-bottom" class="ja-content-bottom clearfix">
							<?php $this->showBlock ($block); ?>
						</div>
						<?php endif; ?>
						
					</div>
				<?php endif ?>
				<?php if ($this->hasBlock('right1')):
					$block = &$this->getBlockXML('right1');
					?>
					
						<?php $this->showBlock ($block); ?>
					
				<?php endif ?>
			</div>
			<?php if ($this->hasBlock('right2')):
				$block = &$this->getBlockXML('right2');
				?>
					<?php $this->showBlock ($block); ?>
			<?php endif ?>			
		</div>
	</div>
    <!-- //MAIN CONTAINER -->

    <?php
    $blks = &$this->getBlocksXML ('bottom');
    $blocks = &T3Common::node_children($blks, 'block');
    foreach ($blocks as $block) :
        if (T3Common::getBrowserSortName() == 'ie' && T3Common::getBrowserMajorVersion() == 7) echo "<br class=\"clearfix\"/>";
        $this->showBlock ($block);
    endforeach;
    ?>

</div>

<?php if ($this->isIE6()) : ?>
    <?php $this->showBlock('ie6/ie6warning') ?>
<?php endif; ?>

<?php $this->showBlock('debug') ?>

</body>

</html>