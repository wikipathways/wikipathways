<?php
global $wgUser;
$skin = $wgUser->getSkin();
$link = $skin->makeLinkObj(Title::makeTitle( NS_HELP, 'Private_Pathways' ), ' (how does that work?)');
$allMessages = array(
		'en' => array(
				'createpathwaypage' => 'Create new pathway',
				'create_private' => "Make pathway private  $link",
                                'wpi-createpage-not-logged-in' => "You are not logged in.",
                                'wpi-createpage-permission'    => "You do not have the necessary permissions." 

		)
);
