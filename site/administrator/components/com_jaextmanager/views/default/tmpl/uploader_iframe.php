<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
//check for fix bug jQuery undefined after submit
	jQuery(document).ready(function(){
		var btnSubmit = jQuery('#japopup_as', window.parent.document);
		btnSubmit.remove();
		
		if(jQuery("#japopup_upload", window.parent.document).size() > 0) {
			jQuery("#japopup_upload", window.parent.document).remove();
		}
		jQuery('<button>').attr( {
				'id' :'japopup_upload',
				'class':'japopup_btn'
			}).html('<?php echo JText::_('UPLOAD', true); ?>').appendTo(jQuery('#jaFormContentBottom', window.parent.document));
			
		jQuery("#japopup_upload", window.parent.document).bind('click', function(e) {
			var form = document.adminForm;
			var type = jQuery('input[name=installtype][checked]').val();
			if(type == 'folder') {
				if (form.install_directory.value == ""){
					alert( "<?php echo JText::_('PLEASE_SELECT_A_DIRECTORY', true ); ?>" );
					return false;
				}
			} else if(type == 'url') {
				// do field validation
				if (form.install_url.value == "" || form.install_url.value == "http://"){
					alert( "<?php echo JText::_('PLEASE_ENTER_A_URL', true ); ?>" );
					return false;
				}
			}
			//jQuery(this).hide();
			form.submit();
		});
		
		
		jQuery('#install_package').focus(function(){
			jQuery('input[name=installtype][value=upload]').attr('checked', 'checked');
		});
		jQuery('#install_directory').focus(function(){
			jQuery('input[name=installtype][value=folder]').attr('checked', 'checked');
		});
		jQuery('#install_url').focus(function(){
			jQuery('input[name=installtype][value=url]').attr('checked', 'checked');
		});
	});
/*]]>*/
</script>
<fieldset>
<legend><?php echo JText::_('JOOMLART_EXTENSION_UPLOADER' ); ?></legend>
<form enctype="multipart/form-data" method="post" action="index.php" id="adminForm" name="adminForm">
  <input type="hidden" name="type" value="" />
  <input type="hidden" name="task" value="doUpload" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name="view" value="default" />
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" value="1" id="hasFileUpload" name="hasFileUpload" />
  <?php echo JHTML::_( 'form.token' ); ?>
  <table align="center">
    <tr>
      <td>
      <input type="radio" name="installtype" id="installtype_upload" value="upload" checked="checked" />
      <label for="installtype_upload"><?php echo JText::_('UPLOAD_PACKAGE_FILE' ); ?></label>      </td>
    </tr>
    <tr>
      <td><input class="input_box" id="install_package" name="install_package" type="file" size="57" />      </td>
    </tr>
    <tr>
      <td>
      <input type="radio" name="installtype" id="installtype_folder" value="folder" />
      <label for="installtype_folder"><?php echo JText::_('UPLOAD_FROM_DIRECTORY' ); ?></label>      </td>
    </tr>
    <tr>
      <td>
      <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->state->get('install.directory'); ?>" />      </td>
    </tr>
    <tr>
      <td>
      <input type="radio" name="installtype" id="installtype_url" value="url" />
      <label for="installtype_url"><?php echo JText::_('UPLOAD_FROM_URL' ); ?></label>      </td>
    </tr>
    <tr>
      <td><input type="text" id="install_url" name="install_url" class="input_box" size="70" value="http://" />      </td>
    </tr>
  </table>
</form>
</fieldset>
