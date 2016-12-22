<?php
/*
 * MyBB: Infobar
 *
 * File: infobar.php
 * 
 * Authors: Sebastian Wunderlich & Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.5
 *
 * 
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('pre_output_page','infobar');

function infobar_info()
{
   global $lang;

    $lang->load("infobar");
    
    $lang->infobar_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->infobar_Desc;

    return Array(
        'name' => $lang->infobar_Name,
        'description' => $lang->infobar_Desc,
        'website' => $lang->infobar_Web,
        'author' => $lang->infobar_Auth,
        'authorsite' => $lang->infobar_AuthSite,
        'version' => $lang->infobar_Ver,
        'compatibility' => $lang->infobar_Compat,
        'codename' => $lang->infobar_Codename
    );
}

function infobar_activate()
{
	global $db;
	$info=infobar_info();
	$setting_group_array=array
	(
		'name'=>$info['codename'],
		'title'=>$info['name'],
		'description'=>'Here you can edit '.$info['name'].' settings.',
		'disporder'=>1,
		'isdefault'=>0
	);
	$db->insert_query('settinggroups',$setting_group_array);
	$group=$db->insert_id();
	$settings=array
	(
		'infobar_guests'=>array
		(
			'Infobar for guests',
			'Do you want to show an infobar to your guests?',
			'yesno',
			1
		),
		'infobar_activate'=>array
		(
			'Infobar not activated users',
			'Do you want to show an infobar to your not activated users?',
			'yesno',
			1
		)
	);
	$i=1;
	foreach($settings as $name=>$sinfo)
	{
		$insert_array=array
		(
			'name'=>$name,
			'title'=>$db->escape_string($sinfo[0]),
			'description'=>$db->escape_string($sinfo[1]),
			'optionscode'=>$db->escape_string($sinfo[2]),
			'value'=>$db->escape_string($sinfo[3]),
			'gid'=>$group,
			'disporder'=>$i,
			'isdefault'=>0
		);
		$db->insert_query('settings',$insert_array);
		$i++;
	}
	rebuild_settings();
}

function infobar_deactivate()
{
	global $db;
	$info=infobar_info();
	$result=$db->simple_select('settinggroups','gid','name="'.$info['codename'].'"',array('limit'=>1));
	$group=$db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups','gid="'.$group['gid'].'"');
		$db->delete_query('settings','gid="'.$group['gid'].'"');
		rebuild_settings();
	}
}

function infobar_lang()
{
	global $lang;
	$lang->load("infobar");
}

function infobar($page)
{
	global $mybb,$session;
	if(($mybb->user['usergroup']==1||$mybb->user['usergroup']==5)&&empty($session->is_spider))
	{
		global $lang;
		infobar_lang();
		if($mybb->user['usergroup']==1&&$mybb->settings['infobar_guests'])
		{
			$infobar_message=$lang->sprintf($lang->infobar_guests_message,$mybb->settings['bburl']);
		}
		elseif($mybb->user['usergroup']==5&&$mybb->settings['infobar_activate'])
		{
			$infobar_message=$lang->sprintf($lang->infobar_activate_message,$mybb->settings['bburl']);
		}
		if($infobar_message)
		{
			$page=str_replace('</head>','<link rel="stylesheet" type="text/css" href="'.$mybb->settings['bburl'].'/inc/plugins/infobar/infobar.css" /></head>',$page);
			$page=preg_replace('#<body(.*)>#Usi','<body$1><div id="infobar"><span>'.$infobar_message.'</span></div>',$page);
			return $page;
		}
	}
}

?>