<?php
/**
 * @version		$Id: details.php 17780 2010-06-20 09:03:02Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=folder&amp;tmpl=component&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="manager">
	<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo JText::_('PREVIEW' ); ?></th>
			<th><?php echo JText::_('NAME' ); ?></th>
			<th><?php echo JText::_('DIMENSIONS' ); ?></th>
			<th><?php echo JText::_('SIZE' ); ?></th>
			<th><?php echo JText::_('DELETE' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $this->loadTemplate('up'); ?>

		<?php for ($i=0,$n=count($this->folders); $i<$n; $i++) :
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor; ?>

		<?php for ($i=0,$n=count($this->documents); $i<$n; $i++) :
			$this->setDoc($i);
			echo $this->loadTemplate('doc');
		endfor; ?>

		<?php for ($i=0,$n=count($this->images); $i<$n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('img');
		endfor; ?>

	</tbody>
	</table>
	<input type="hidden" name="task" value="list" />
	<input type="hidden" name="username" value="" />
	<input type="hidden" name="password" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
