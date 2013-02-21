<?php
/**
 * ------------------------------------------------------------------------
 * JA Extenstion Manager Component for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

$item=$this->item;
?>
<div style="width:500px; margin:0 auto;">
<fieldset>
<form name="adminForm" id="adminForm" action="index.php" method="post">
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="view" value="services" />
  <input type="hidden" name="task" value="saveConfig" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name='id' id='id' value="<?php echo $item->id; ?>">
  <input type="hidden" name='cid[]' id='cid[]' value="<?php echo $item->id; ?>">
  <input type="hidden" name="number" value="<?php echo $this->number; ?>">
  
  <input type="hidden" id="ws_core" name="ws_core" value="<?php echo $item->ws_core; ?>" />
  <input type="hidden" id="ws_default" name="ws_default" value="<?php echo $item->ws_default; ?>" />
  <input type="hidden" id="ws_mode" name="ws_mode" value="<?php echo $item->ws_mode; ?>" />
  <input type="hidden" id="backUrl" name="backUrl" value="<?php echo JRequest::getVar('backUrl', ''); ?>" />
    <fieldset>
        <legend> <?php echo JText::_('SERVICES_INFORMATION' ); ?> </legend>
        <table class="admintable" width="100%">
          <tr>
            <td width="30%" class="key" align="right" valign="top"><?php echo JText::_('SERVICE_NAME' ); ?>: </td>
            <td width="70%">
      			<input type="text" id="ws_name" name="ws_name" size='50' value="<?php echo $item->ws_name; ?>" />            </td>
          </tr>
  		  <?php if($item->ws_mode != 'local'): ?>
          <tr>
            <td class="key" align="right" valign="top"><?php echo JText::_('SERVICE_URL' ); ?>: </td>
            <td>
      			<input type="text" id="ws_uri" name="ws_uri" size='50' value="<?php echo $item->ws_uri; ?>" />            </td>
          </tr>
          <?php endif; ?>
    	</table>
  </fieldset>
  <?php if($item->ws_mode != 'local'): ?>
  <fieldset>
        <legend> <?php echo JText::_('AUTHENTICATION' ); ?> </legend>
        <table class="admintable" width="100%">
          <tr>
            <td colspan="2"><?php echo JText::_('LEAVE_BLANK_IF_THIS_SERVICE_DO_NOT_REQUIRE_AUTHENTICATION'); ?></td>
          </tr>
          <tr>
            <td width="30%"  class="key" align="right" valign="top"><?php echo JText::_('USERNAME' ); ?>: </td>
            <td width="70%">
      			<input type="text" id="ws_user" name="ws_user" size='40' value="<?php echo $item->ws_user; ?>" />            </td>
          </tr>
          <tr>
            <td class="key" align="right" valign="top"><?php echo JText::_('PASSWORD' ); ?>: </td>
            <td>
      			<input type="password" id="ws_pass" name="ws_pass" size='40' value="" />
                <?php if($item->id != 0): ?>
                <br /><small><?php echo JText::_('LEAVE_BLANK_IF_NO_REQUIRE_CHANGE' ); ?></small>
                <?php endif; ?>            </td>
          </tr>
    	</table>
  </fieldset>
  <?php endif; ?>
  <button id="japopup_save" name="japopup_save"><?php echo JText::_("SAVE"); ?></button>
</form>
</fieldset>
</div>


<script type="text/javascript">
/*<![CDATA[*/
jQuery(document).ready(function(){
	jQuery("#japopup_save").bind('click', function(e) {
		var mode = jQuery('#ws_mode').val();
		if(mode == 'remote') {
			if(jQuery('#ws_name').val() == '') {
				alert('<?php echo JText::_('PLEASE_ENTER_SERVICE_NAME', true); ?>');
				return false;
			}
			if(jQuery('#ws_uri').val() == '') {
				alert('<?php echo JText::_('PLEASE_ENTER_SERVICE_URL', true); ?>');
				return false;
			}
			/*if(jQuery('#ws_user').val() == '') {
				alert('<?php echo JText::_('PLEASE_ENTER_USERNAME', true); ?>');
				return false;
			}
			if(jQuery('#ws_pass').val() == '') {
				alert('<?php echo JText::_('PLEASE_ENTER_PASSWORD', true); ?>');
				return false;
			}*/
		}
		
		var form = document.adminForm;
		form.submit();
	});
});
/*]]>*/
</script>
