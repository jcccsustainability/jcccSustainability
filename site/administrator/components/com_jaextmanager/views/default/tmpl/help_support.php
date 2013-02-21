<?php defined ( '_JEXEC' ) or die ( 'Restricted access' ); ?>
<fieldset>
<legend><?php echo JText::_("HELP_AND_SUPPORT");?></legend>
<!-- icon and title -->
<div class="smallfont"> <strong>JA Extensions Manager...</strong> </div>
<hr size="1" style="color: rgb(255, 255, 255); background-color: rgb(255, 255, 255);" />
<!-- / icon and title -->
<!-- message -->

  <br />
<p><img src="http://joomlart.s3.amazonaws.com/images/mis/ja_download_system/logo_extension.jpg" border="0" alt="JA Extension Manager" width="236" height="48" /></p>
<p><strong>JoomlArt Extension Manager </strong></p>
                    <p>This component eases difficulties faced during each upgrade to newer versions. It allows the user to compare the files across the versions, check and backup files in conflict and also rollback to older versions, if necessary. This is an extension to our <a href="http://www.joomlart.com/forums/download.php" target="_blank">New Download System</a> and <a href="http://update.joomlart.com/" target="_blank">Update / Comparison System</a>. In the upcoming versions, it would be possible to download and upgrade right from this backend. Furthermore, this component can be used for all the extensions (templates / plugins / modules / components), irrespective of developers<strong>. <br /></strong></p>
                <p>
                    <a href="http://www.youtube.com/watch?v=bruMQxudvdI&fmt=22" target="_blank" title="">
                    <img src="components/com_jaextmanager/assets/images/video1.jpg" />
                    </a>
                    <a href="http://www.youtube.com/watch?v=MaiCRUF2pQI&fmt=22" target="_blank" title="">
                    <img src="components/com_jaextmanager/assets/images/video2.jpg" />
                    </a>
                </p>
                <p><strong>Documentation:</strong></p>
                <p>Detailed Documentation and Usage instructions can be viewed at JoomlArt <a href="http://wiki.joomlart.com/wiki/JA_Exensions_Manager/Overview" target="_blank">Wiki Page</a>, Watch <a href="http://www.youtube.com/watch?v=bruMQxudvdI&amp;fmt=22" target="_blank">YouTube  Video for Local Repository Usage</a> and Watch <a href="http://www.youtube.com/watch?v=MaiCRUF2pQI&amp;fmt=22" target="_blank">YouTube  Video for See upgrades from online repository.</a></p>
                <p> </p>
				<br/>
                <p><strong>Important things to do before using this component:</strong></p>
                <ol>
                <li>Make sure you have an working backup of the site, before you use this component. Although it works fine and has been tested, but the results might vary based on user environment.</li>
                <li>If you have encountered files / folder permissions or ownership problems with other extensions. We strongly recommend to fix them before going ahead with this component.</li>
                <li>If your extension upgrade involves database upgrades, please make sure your XML file contains the details for upgrade and downgrade (rollback). Check Wiki for details.</li>
                <li>Make sure you understand the File Legends while comparing and upgrading.</li>
                <li>This component is NOT capable of merging your customization into the new file. You will still have to redo your customization manually.</li>
                <li>Based on the comparison, you can take the backup of your customized files and redo the customization on the new files.</li>
                <li>We recommend using file comparison utility such as <a href="http://winmerge.org/" target="_blank">Win Merge</a>, to compare and move your customized codes to the new file.</li>
                </ol>
                <p> </p>
                <p><strong>Requirements:</strong></p>
                <ol>
                <li>This component works well on Linux environment.</li>
                <li>You must have the original files of the version used on your site for comparison to work. If you do not have the original files with you, the comparison results will be wrong and your customization would be lost. For example, if you are using version 1.1.0 on your site, you will have to upload the original 1.1.0 into the JA Extension Manager Repository along with the new version (1.1.1).</li>
                </ol>
                <p> </p>
                <p><strong>How it works:</strong></p>
                <p>This Component compares files based on checksum values and interprets the results based on comparison between the live version (A), original version (B) and new version (C).</p>
                <p> </p>
                <p><strong>Legends and their Explanation:</strong></p>
                <p>Component needs User version in use, user version (unmodified) and Latest Version for comparison. For ease of explanation, we have classified them as A, B and C as below:</p>
                <p>(<strong>A</strong>) = Files of Live version on User site.</p>
                <p>(<strong>B</strong>) = Original files of live version.</p>
                <p>(<strong>C</strong>) = New Version Files.</p>
                <p> </p>
                <ul>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_new.gif" border="0" alt="New File" width="15" height="15" /> New File</strong>: Files introduced in New Version. File which is present in (<strong>C</strong>) only, and absent in (<strong>A</strong>) and (<strong>B</strong>). These files will be moved to user site.</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_bmodified.gif" border="0" alt="File in Conflict" width="16" height="16" /> Conflicted Files</strong>: Files Modified by User (his version) and by Developer (New Version). Files which are modified at (<strong>A</strong>) and are also modified in new version at (<strong>C</strong>). These files will be overwritten, User needs to take backup.</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_updated.gif" border="0" alt="Updated File" width="15" height="15" /> Updated File</strong>: These files are modified in the new Version (<strong>C</strong>) and are not modified by user on his live site (<strong>A</strong>). These will be moved to User site, overwriting old files (no user customization in these files).</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_removed.gif" border="0" alt="Deleted File" width="15" height="15" /> Removed File</strong>: Files deleted in new version. These files are not present in the New Version (<strong>C</strong>), but are present in (<strong>A</strong>) and (<strong>B</strong>). These will be deleted from User site.</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_umodified.gif" border="0" alt="User Modified File" width="15" height="15" /> Modified File</strong>: These files are user customization (<strong>A</strong>), but not modified at (<strong>B</strong>) or in New Version (<strong>C</strong>). These files will not be overwritten, as they have not changed in the New Version.</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_ucreated.gif" border="0" alt="User Created File" width="15" height="15" /> Created By User</strong>: These files are either user created (<strong>A</strong>)or files which needs to be moved between folders while installation. If these are unique (non-package dependent), they would be left as it is. But if these are files which are moved during installation, they would be overwritten. Be careful with these files.</li>
                <li><strong><img src="components/com_jaextmanager/assets/dtree/img/icon_nochange.gif" border="0" alt="No Change in files" width="15" height="15" /> No Change</strong>: Not modified across (<strong>A</strong>), (<strong>B</strong>) and (<strong>C</strong>). These files will not be replaced.</li>
                </ul>
                <p> </p>
                <p><strong>Known Issues:</strong></p>
                <ul>
                <li>Language files, which are moved to language folders post-installation, are not covered and should be backed up by user and replaced back after cross-examination with the new file.</li>
                <li>If your XML does not carry Database downgrade / upgrade info, then this component will only replace file and will not upgrade your Database. Ask your extension provider for such information.</li>
                <li>The component reads the new version based on Version info in XML and Date. If this is not correct, the Component may not work properly.</li>
                <li>Irrespective of how powerful this component may become, it should never replace the basic instinct of taking backup. This is just a tool.</li>
                </ul>
                <p> </p>
                <p><strong>Discussion:</strong></p>
                <ul>
                <li>Issues relating to this component can be discussed at <a href="http://www.joomlart.com/forums/forumdisplay.php?f=199" target="_blank" title="JoomlArt Forum - JA Extension Manager">JoomlArt.com Forums</a>.</li>
                </ul>
                <p> </p>
            <p><span style="color: #008000;"><strong>Links:</strong></span></p>
		<ul><li><a target="_blank" href="http://wiki.joomlart.com/wiki/JA_Extensions_Manager/Overview">JA Extension Manager Wiki</a></li><li><a target="_blank" href="http://pm.joomlart.com/browse/JAECCOMJAEXTMANAGER">Report Bug</a></li><li><a target="_blank" href="http://update.joomlart.com/">Updates &amp; Versions</a></li></ul>
				<p>Copyright 2004 - 2012 <a href="http://www.joomlart.com/" title="Visit Joomlart.com!">JoomlArt.com</a>.</p>
<!-- / message -->
</fieldset>
