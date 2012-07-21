<?php
# Session Initialization
session_start();
# Required Files
require_once('./classes/db.class.php');
# Class Initialization
$db = new db();
# DB
$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
# Title
echo '<title>CalicoBB Install</title>';
# Switch...
switch($_GET['do']){
	case "queries":
		// Here are the queries..
		$q[] = "DROP TABLE IF EXISTS `banned`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `banned` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`uid` int(10) unsigned NOT NULL,
			`gid` int(5) NOT NULL,
			`datetime` int(11) NOT NULL,
			`banned_by` int(5) unsigned NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `forums`;";
		$q[] = "  `new_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`read_forum` int(1) unsigned NOT NULL DEFAULT '1',
			`redirect_url` varchar(255) NOT NULL,
			`redirect_on` int(1) unsigned NOT NULL DEFAULT '0',
			`parent_forum` int(5) unsigned NOT NULL,
			`catid` int(5) unsigned NOT NULL,
			`is_category` int(1) unsigned NOT NULL DEFAULT '0',
			`rules_title` varchar(255) NOT NULL,
			`rules_text` mediumtext NOT NULL,
			`rules_show` int(1) unsigned NOT NULL DEFAULT '0',
			`add_postcount` int(1) unsigned NOT NULL DEFAULT '1',
			`visible` int(1) unsigned NOT NULL DEFAULT '1',
			`lp_pid` int(10) unsigned NOT NULL,
			`lp_tid` int(10) unsigned NOT NULL,
			`lp_uid` int(10) unsigned NOT NULL,
			`lp_title` varchar(30) NOT NULL,
			`lp_dateline` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `parent_forum` (`parent_forum`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `groups`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `groups` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			`view_board` int(1) NOT NULL DEFAULT '1',
			`search` int(1) NOT NULL DEFAULT '1',
			`edit_profile` int(1) NOT NULL DEFAULT '1',
			`view_profile` int(1) NOT NULL DEFAULT '1',
			`new_topics` int(1) NOT NULL DEFAULT '1',
			`new_posts` int(1) NOT NULL DEFAULT '1',
			`use_pm` int(1) unsigned NOT NULL DEFAULT '1',
			`user_title` varchar(100) NOT NULL,
			`supermod` int(1) NOT NULL DEFAULT '0',
			`banned` int(1) NOT NULL DEFAULT '0',
			`administrator` int(1) NOT NULL DEFAULT '0',
			`access_modcp` int(1) NOT NULL DEFAULT '0',
			`view_deleted` int(1) NOT NULL DEFAULT '0',
			`reply_closed` int(1) NOT NULL DEFAULT '0',
			`group_format` varchar(50) NOT NULL DEFAULT '{username}',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `moderators`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `moderators` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`mid` int(11) unsigned NOT NULL,
			`gid` int(10) unsigned NOT NULL,
			`fid` int(11) unsigned NOT NULL,
			`edit_posts` int(1) unsigned NOT NULL DEFAULT '1',
			`delete_posts` int(1) unsigned NOT NULL DEFAULT '1',
			`delete_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`view_ips` int(1) unsigned NOT NULL DEFAULT '1',
			`sticky_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`close_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`merge_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`warn_users` int(1) unsigned NOT NULL DEFAULT '1',
			`ban_users` int(1) unsigned NOT NULL DEFAULT '1',
			`move_topics` int(1) unsigned NOT NULL DEFAULT '1',
			`view_deleted` int(1) unsigned NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			KEY `mid` (`mid`,`fid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `permissions`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `permissions` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`gid` int(11) unsigned NOT NULL,
			`fid` int(11) unsigned NOT NULL,
			`default_p` int(1) unsigned NOT NULL DEFAULT '0',
			`viewforum` int(1) unsigned NOT NULL DEFAULT '1',
			`viewthread` int(1) unsigned NOT NULL DEFAULT '1',
			`post_attachments` int(1) unsigned NOT NULL DEFAULT '1',
			`post_threads` int(1) unsigned NOT NULL DEFAULT '1',
			`post_replies` int(1) unsigned NOT NULL DEFAULT '1',
			`delete_posts` int(1) unsigned NOT NULL DEFAULT '0',
			`delete_topics` int(1) unsigned NOT NULL DEFAULT '0',
			`edit_posts` int(1) unsigned NOT NULL DEFAULT '0',
			`own_topics_only` int(1) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `gid` (`gid`,`fid`),
			KEY `fid` (`fid`),
			KEY `default_p` (`default_p`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `posts`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `posts` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`tid` int(11) unsigned NOT NULL,
			`fid` int(11) unsigned NOT NULL,
			`owner` int(11) unsigned NOT NULL,
			`subject` varchar(255) NOT NULL,
			`content` mediumtext NOT NULL,
			`show_sig` int(1) unsigned NOT NULL DEFAULT '1',
			`ip` varchar(30) NOT NULL,
			`dateline` int(11) unsigned NOT NULL,
			`edit_msg` varchar(100) NOT NULL,
			`edited` varchar(100) NOT NULL,
			`deleted` int(1) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `tid` (`tid`,`fid`,`owner`),
			KEY `fid` (`fid`),
			KEY `owner` (`owner`),
			FULLTEXT KEY `content` (`content`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `private_messages`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `private_messages` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`to` int(11) unsigned NOT NULL,
			`from` int(11) unsigned NOT NULL,
			`subject` varchar(255) NOT NULL DEFAULT 'No Subject',
			`message` mediumtext NOT NULL,
			`dateline` int(25) unsigned NOT NULL,
			`read` tinyint(1) unsigned NOT NULL DEFAULT '0',
			`includesig` int(1) unsigned NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			KEY `to` (`to`,`from`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `subscriptions`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `subscriptions` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			`description` varchar(255) NOT NULL,
			`new_group` int(11) unsigned NOT NULL,
			`duration` int(10) unsigned NOT NULL DEFAULT '30',
			`cost` decimal(5,2) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `subscription_payments`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `subscription_payments` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`mid` int(11) unsigned NOT NULL,
			`sid` int(11) unsigned NOT NULL,
			`old_group` int(11) unsigned NOT NULL,
			`start_date` int(11) unsigned NOT NULL,
			`end_date` int(11) unsigned NOT NULL,
			`paid` int(1) unsigned NOT NULL DEFAULT '0',
			`trans_id` varchar(100) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `mid` (`mid`,`sid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `topics`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `topics` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`fid` int(11) unsigned NOT NULL,
			`title` varchar(255) NOT NULL,
			`owner` int(11) unsigned NOT NULL,
			`dateline` int(11) unsigned NOT NULL,
			`views` int(11) unsigned NOT NULL,
			`replies` int(10) unsigned NOT NULL,
			`closed` int(1) unsigned NOT NULL DEFAULT '0',
			`sticky` int(1) unsigned NOT NULL DEFAULT '0',
			`lastpost` int(11) unsigned NOT NULL,
			`lp_subject` varchar(50) NOT NULL,
			`lp_uid` int(10) unsigned NOT NULL,
			`lp_dateline` int(10) unsigned NOT NULL,
			`visible` int(1) unsigned NOT NULL DEFAULT '1',
			`deleted` int(1) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `fid` (`fid`,`owner`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `users`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `users` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`username` varchar(30) NOT NULL,
			`group` int(11) unsigned NOT NULL DEFAULT '1',
			`password` varchar(40) NOT NULL,
			`email` varchar(50) NOT NULL,
			`joined` int(15) unsigned NOT NULL,
			`ip_address` varchar(30) NOT NULL,
			`location` varchar(30) NOT NULL DEFAULT 'N/A',
			`signature` text NOT NULL,
			`gender` varchar(3) NOT NULL DEFAULT 'U',
			`website` varchar(100) NOT NULL,
			`title` varchar(100) NOT NULL,
			`facebook` int(1) unsigned NOT NULL,
			`aboutme` varchar(255) NOT NULL,
			`postcount` int(11) unsigned NOT NULL DEFAULT '0',
			`posts_per_page` int(3) unsigned NOT NULL DEFAULT '30',
			`topics_per_page` int(3) unsigned NOT NULL DEFAULT '25',
			`view_signatures` int(1) unsigned NOT NULL DEFAULT '1',
			`view_avatars` int(1) unsigned NOT NULL DEFAULT '1',
			`allow_pms` int(1) unsigned NOT NULL DEFAULT '1',
			`allow_emails` int(1) unsigned NOT NULL DEFAULT '1',
			`advanced_editor` int(1) unsigned NOT NULL DEFAULT '1',
			`optout` int(1) unsigned NOT NULL DEFAULT '0',
			`disable_sig` int(1) unsigned NOT NULL DEFAULT '0',
			`disable_posting` int(1) unsigned NOT NULL DEFAULT '0',
			`activation_code` varchar(100) NOT NULL,
			`lastonline` int(11) unsigned NOT NULL,
			`openid` int(1) unsigned NOT NULL,
			`openid_url` varchar(100) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `group` (`group`),
			KEY `username` (`username`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `user_session`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `user_session` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`uid` int(10) unsigned NOT NULL,
			`time` int(11) NOT NULL,
			`location` varchar(255) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uid` (`uid`),
			UNIQUE KEY `id` (`id`,`uid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `warnings`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `warnings` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`uid` int(11) unsigned NOT NULL,
			`tid` int(11) unsigned NOT NULL,
			`pid` int(11) unsigned NOT NULL,
			`title` varchar(255) NOT NULL,
			`points` int(2) unsigned NOT NULL,
			`issuer` int(11) unsigned NOT NULL,
			`expires` int(11) unsigned NOT NULL,
			`revoked` int(1) unsigned NOT NULL DEFAULT '0',
			`has_read` int(1) unsigned NOT NULL DEFAULT '0',
			`pmid` int(5) unsigned NOT NULL,
			`notes` mediumtext NOT NULL,
			PRIMARY KEY (`id`),
			KEY `uid` (`uid`,`tid`,`pid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$q[] = "DROP TABLE IF EXISTS `warning_types`;";
		$q[] = "CREATE TABLE IF NOT EXISTS `warning_types` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			`points` int(11) unsigned NOT NULL,
			`expires` int(11) unsigned NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			// Insert pre-created stuff...
			// Groups
		$q[] = "INSERT INTO `groups` (`id`, `title`, `view_board`, `search`, `edit_profile`, `view_profile`, `new_topics`, `new_posts`, `use_pm`, `user_title`, `supermod`, `banned`, `administrator`, `access_modcp`, `view_deleted`, `reply_closed`, `group_format`) VALUES
			(1, 'Registered', 1, 1, 1, 1, 1, 1, 1, '', 0, 0, 0, 0, 0, 0, '{username}'),
			(2, 'Administrators', 1, 1, 1, 1, 1, 1, 1, '', 0, 0, 1, 1, 1, 1, '<span style=\"color: #f00\"><strong>{username}</strong></span>'),
			(3, 'Validating', 0, 0, 0, 0, 0, 0, 0, 'Validating', 0, 0, 0, 0, 0, 0, '<span style=\"color:#808080;\">{username}</span>'),
			(4, 'Banned', 0, 0, 0, 0, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, '<em>{username}</em>'),
			(5, 'Guests', 1, 1, 0, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '{username}');";
			// Forums
		$q[] = "INSERT INTO `forums` (id,title,description,is_category,catid)
			VALUES
				(NULL,'My first category','This is a category, created by CalicoSoft','1','0'),
				(NULL,'My first forum', 'Hello, world! Make your posts in here to start up your forum.',0,1);";
			// Permissions
		$q[] = "INSERT INTO `permissions`(fid,default_p)
			VALUES
				(1,1),
				(2,1);";
		// Perform
		// Loop through all queries
		foreach($q as $iq){
			$sql->query($iq);
		}
		// Good ;)
		exit('Table creation complete. <a href="install.php?do=admin_account">Next Step &rarr;</a>');
	break;
	case 'admin_account';
		$msg = '<p>
	<strong>Admininstrative Account</strong></p>
<p>
	You&#39;ll need to create an admin account to proceed. Enter a username, password and email address in the fields below.</p>
<form action="install.php?do=admin_account_add" method="post">
	<p>
		Username:<br />
		<input name="username" type="text" /></p>
	<p>
		Password:<br />
		<input name="password" type="password" /></p>
	<p>
		Email Address:<br />
		<input name="email" type="text" /></p>
	<p>
		<input name="Go" type="submit" value="Create Account" /></p>
</form>
<p>
	&nbsp;</p>
';
		exit($msg);
	break;
	case 'admin_account_add':
		$username = $sql->real_escape_string($_POST['username']);
		$password = sha1(md5($sql->real_escape_string($_POST['password'])));
		$email = $sql->real_escape_string($_POST['email']);
		// Query
		$q = "INSERT INTO `users` (`username`,`password`,`email`,`group`,`joined`)
			VALUES('$username','$password','$email','2','".time()."');";
		$sql->query($q);
		// Show :)
		$msg = 'A new account has been created with the username <em>'.$username.'</em> and password <em>'.$_POST['password'].'</em>.<br />
		<a href="install.php?do=self_delete">Next Step &rarr;</a>';
		exit($msg);
	break;
	case 'self_delete':
		// delete...
		@unlink(__FILE__);
		exit('The installer has completed! CalicoBB is now installed.<br /><br />
		The installer has attempted to delete install.php. Please click <a href="">here</a> to check that the file has been deleted.<br />
		If not, <strong>DELETE INSTALL.PHP IMMEDIATELY!</strong>
		<p><a href="index.php">View your new forums</a></p>');
	break;
	default:
		$msg = '<p>
	<strong>Welcome to the CalicoBB installer!</strong></p>
<p>
	Before you begin, please ensure you have edited <code>classes/db.class.php</code> with the correct database information.
	Fields which you must changed are prefixed with <code>/* */</code>.
	If you have already installed CalicoBB on this database,&nbsp;<strong>do not continue</strong>.</p>
<p>
	<strong style="color:red;">If you are upgrading CalicoBB, please visit "upgrade.php"! Pressing install will delete your forums.</strong></p>
<p>
	<a href="install.php?do=queries">Proceed to the first stage&nbsp;&rarr;</a></p>
';
		exit($msg);
	break;
}
?>