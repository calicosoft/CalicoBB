<?php
class forum extends calicobb{
	public function viewTopic($tid){
		// Get assorted details (topic, forum, etc)
		// Get topic details
		$tid = intval($tid);
		$tq = "SELECT * FROM `topics` WHERE id='$tid' LIMIT 1";
		$tq = $this->DB->query($tq);
		$tr = $tq->fetch_assoc();
		// Does this topic exist?
		if($tq->num_rows == 0){
			// show error
			$content['html'] = core::errorMessage('topic_not_found');
			$content['title'] = 'Board Message';
			// do the 404
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");
			// send it back
			return $content;
		}
		// Get forum details
		$fid = $tr['fid'];
		$fq = "SELECT id,title,catid,parent_forum,visible,read_forum FROM `forums` WHERE id=$fid LIMIT 1";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		$catid = $fr['catid'];
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			header("HTTP/1.0 404 Not Found");
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Is this forum invisible?
		if($fr['visible'] == 0 OR $fr['read_forum'] == 0){
			// show error
			$content['html'] = core::errorMessage('forum_no_permission');
			$content['title'] = 'Board Message';
			// do the 404
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");
			// send it bacl
			return $content;
		}
		// Get the users information
		if(isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$uq = "SELECT * FROM `users` WHERE id='".$this->uid."' LIMIT 1";
			$uq = $this->DB->query($uq);
			$ur = $uq->fetch_assoc();
		}
		$gq = "SELECT supermod, administrator, view_deleted, reply_closed, access_modcp FROM `groups` WHERE id='".$this->gid."' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT own_topics_only,viewforum,viewthread FROM `permissions` WHERE (gid='".$this->gid."' AND fid='$fid') OR (fid='$fid' AND default_p='1') ORDER BY `viewforum` DESC LIMIT 1;";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// Get moderator details
		$mq = "SELECT delete_posts,edit_posts,ban_users,warn_users,view_ips FROM `moderators` WHERE mid='$mid' AND fid='$fid' LIMIT 1";
		$mq = $this->DB->query($mq);
		$mr = $mq->fetch_assoc();
		if($mq->num_rows == 0){
			$mr = 0;
		}
		
		// Friendly URL Redirect
		if(db::$config['seo_urls'] == true){
			if($_GET['friendly_url_title'] != core::friendlyTitle($tr['title']) OR $_GET['friendly_url_used'] != 1){
				// Pagination
				if(isset($_GET['page'])){
					$new_url_page = 'page='.intval($_GET['page']);
				}
				// Put it together
				$new_url = core::generateUrl('index.php?topic='.$tr['id'],'topic',$tr['id'],$tr['title'],$new_url_page);
				// Redirect
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: $new_url");
				exit();
			}
		}
		// Parent forum
		if($fr['parent_forum'] > 0){
			// We are a sub forum
			$parent = $fr['parent_forum'];
			$bq = "SELECT id,title,catid,parent_forum FROM `forums` WHERE id='$parent' LIMIT 1";
			$bq = $this->DB->query($bq);
			$br = $bq->fetch_assoc();
			// Breadcrumb
			$parent_url = core::generateUrl('index.php?forum='.$br['id'],'forum',$br['id'],$br['title'],'');
			$breadcrumb['parent'] = '&gt;
			<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
				<a href="'.$parent_url.'" itemprop="url"><span itemprop="title">'.$br['title'].'</span></a>
			</span>';
			// Set catID
			$catid = $br['catid'];
			$parent = $br['parent_forum'];
			// Get category details
			$cq = "SELECT id,title FROM `forums` WHERE id='$catid' OR id='$parent' LIMIT 1";
			$cq = $this->DB->query($cq);
			$cr = $cq->fetch_assoc();
		}else{
			// Get category details
			$cq = "SELECT id,title FROM `forums` WHERE id='$catid' LIMIT 1";
			$cq = $this->DB->query($cq);
			$cr = $cq->fetch_assoc();
		}
		

		// Breadcrumb
		// Homepage
		$breadcrumb['homepage'] = '
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="index.php?" itemprop="url"><span itemprop="title">'.db::$config['site_name'].'</span></a>
	</span>';
		$category_url = core::generateUrl('index.php?forum='.$cr['id'],'forum',$cr['id'],$cr['title'],'');
		// Category
		$breadcrumb['category'] = '
	&gt;
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="'.$category_url.'" itemprop="url"><span itemprop="title">'.$cr['title'].'</span></a>
	</span>';
		$forum_url = core::generateUrl('index.php?forum='.$fr['id'],'forum',$fr['id'],$fr['title'],'');
		// Forum
		$breadcrumb['forum'] = '
	&gt;
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="'.$forum_url.'" itemprop="url"><span itemprop="title">'.$fr['title'].'</span></a>
	</span>';
		$topic_url = core::generateUrl('index.php?topic='.$tr['id'],'topic',$tr['id'],$tr['title'],'');
		// Topic
		$breadcrumb['topic'] = '
	&gt;
	<a href="'.$topic_url.'">'.htmlspecialchars($tr['title']).'</a>';
		// Put the breadcrumb altogether
		$breadcrumb['altogether'] = '<p>'.$breadcrumb['homepage'].''.$breadcrumb['category'].''.$breadcrumb['parent'].''.$breadcrumb['forum'].''.$breadcrumb['topic'].'</p>';
		
		// Permissions Checks
		// Can we only view our own topics?
		if($pr['own_topics_only'] == 1){
			if($tr['owner'] != $_SESSION[db::$config['session_prefix'].'_userid']){
				$content = core::errorMessage('own_topics_only');
				$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			}
		}
		// Can we view this forum or topic?
		if($pr['viewforum'] == 0 OR $pr['viewthread'] == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Has this topic been deleted?
		if($tr['deleted'] == 1 AND $gr['supermod'] == 0 OR $tr['deleted'] AND $mr->num_rows == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		
		// Update the View Count
		$vcq = "UPDATE `topics` SET views=views+1 WHERE id='$tid'";
		$this->DB->query($vcq);
		
		// Moderator Security Key
		$k = core::moderationKey();
		
		// Moderator tools (Topic)
		if($mq->num_rows == 1 OR $gr['supermod'] == 1 OR $gr['administrator'] == 1){
			$modtools = '
			<form class="modTools" action="index.php" method="get">
				<input name="act" type="hidden" value="moderate" />
				<label for="do">Topic Moderation:</label><select id="do" name="do">
					<option selected="selected" value="---">---Topic Moderation ---</option>
					<optgroup label="Delete Topic">
						<option value="deletetopic">Delete Topic</option>
						<option value="hard_delete_topic">Hard Delete Topic</option>
					</optgroup>
					<optgroup label="Open / Close / Sticky Topic">
						<option value="lock">Close Topic</option>
						<option value="unlock">Re-open Topic</option>
						<option value="sticky">Sticky Topic</option>
						<option value="unsticky">Unsticky Topic</option>
					</optgroup>
					<optgroup label="Moderate Topic">
						<option value="move">Move Topic</option>
						<option value="topictitle">Edit Topic Title</option>
						<option value="merge">Merge Topic</option>
					</optgroup>
				</select>
				<input name="tid" type="hidden" value="'.$tid.'" />
				<input name="k" type="hidden" value="'.$k.'" />
				<input name="mod" type="submit" value="Moderate" class="smallButton" /></form>';
		}
		
		//////////////////////////////////////////////
		// PAGINATON 								//
		//////////////////////////////////////////////
		// Count the records...
		if($gr['view_deleted'] == 0 OR $mr['view_deleted']){
			$cq = "SELECT count(*) AS num FROM `posts` WHERE tid=$tid";
		}else{
			$cq = "SELECT count(*) AS num FROM `posts` WHERE tid=$tid AND deleted='0'";
		}
		$cq = $this->DB->query($cq);
		$cr = $cq->fetch_assoc();
		// No Posts
		if($cr['num'] == 0){
			// Delete Topic
			$q = "DELETE FROM `topics` WHERE id='$tid' LIMIT 1;";
			$this->DB->query($q);
			// Msg
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Load Core
		require_once('./classes/core.class.php');
		$core = new core();
		// Setup required variables
		$records = $cr['num'];
		$pp = $ur['posts_per_page'];
		$current_page = intval($_GET['page']);
		$pagevar = '&amp;page';
		if(db::$config['seo_urls'] == true){
			$pagevar = '?page';
		}
		$current_url = $topic_url;
		// Setup the pages
		$pages = $core->paginationGenerate($records,$pp,$current_page,$pagevar,$current_url);
		// Returned values
		$limit = $pages['limit'];
		$page_setup = $pages['selection'];
		
		// Reply Link
		if($tr['closed'] == 0){
			$replylink = '<a href="index.php?act=reply&amp;tid='.$tid.'" class="buttonEnabled">Reply</a>';
			$canreply = true;
		}elseif($tr['closed'] == 1 AND $gr['reply_closed'] == 1 OR $tr['closed'] == 1 AND $gr['supermod'] == 1 OR $tr['closed'] == 1 AND $mr->num_rows == 1){
			$replylink = '<a href="index.php?act=reply&amp;tid='.$tid.'" class="buttonEnabled">Topic Closed (Reply)</a>';
			$canreply = true;
		}else{
			$replylink = '<span class="buttonDisabled">Topic Closed</span>';
		}
		
		// SEO Functions
		// Main URL
		if(db::$config['seo_urls'] == true){
			$home = 'http://'.$_SERVER['SERVER_NAME'].str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
		}else{
			$home = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
		}
		// We need to get this FIRST post in this topic
		$fpq = "SELECT content FROM `posts` WHERE tid='$tid' ORDER BY id DESC LIMIT 1";
		$fpq = $this->DB->query($fpq);
		$fpr = $fpq->fetch_assoc();
		// Parse the post via core::parsePost
		$desc = core::parsePost($fpr['content']);
		$desc = strip_tags($desc);
		if(strlen($desc) > 150){
			$desc = substr($desc,0,148).'...';
		}
		// Topic Description
		$data['desc'] = $desc;
		// Up Url (set the page 1 variable)
		$data['up'] = $home.$forum_url;
		// Canon Url 
		$data['canon'] = $home.$topic_url;
		// Set the title
		$data['title'] = $tr['title'] . ' - ' . $fr['title'];
		
		// Twitter & Facebook Share Links
		$topic_url = urlencode($home.$topic_url);
		
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'forum_show_topic_container.html';
		$template['container'] = file_get_contents($template['container_p']);
		
		// Is one a moderator?
		$ismod = false;
		if($mq->num_rows == 1 OR $gr['supermod'] == 1 OR $gr['access_modcp'] == 1 OR $gr['administrator'] == 1){
			$ismod = true;
		}
		
		// Get the Posts
		if($gr['view_deleted'] == 1 OR $mr['view_deleted'] == 1 OR $gr['administrator'] == 1 OR $gr['supermod'] == 1 OR $ismod == true){
			$q = "SELECT * FROM `posts` WHERE tid=$tid ORDER BY id ASC $limit";
		}else{
			$q = "SELECT * FROM `posts` WHERE tid=$tid AND deleted=0 ORDER BY id ASC $limit";
		}
		$q = $this->DB->query($q);
		// Loop Through Result
		while($r = $q->fetch_assoc()){
			$posts .= forum::showPost($r,$tid,$mr,$ur,$gr,$tr,$ismod);
		}
		// Quick Reply
		if($canreply == true){
			// get quick reply
			$template['container_qr_p'] = $template['path'].'forum_show_topic_quick_reply.html';
			$template['container_qr'] = file_get_contents($template['container_qr_p']);
			// do the edits
			$qr = $template['container_qr'];
			$qr = str_replace('{fid}',$fid,$qr);
			$qr = str_replace('{tid}',$tid,$qr);
			$qr = str_replace('{title}','RE: '.$tr['title'],$qr);
		}
		// Put the content together
		$content = $template['container'];
		$content = str_replace('{title}',htmlspecialchars($tr['title']),$content);
		$content = str_replace('{breadcrumb}',$breadcrumb['altogether'],$content);
		$content = str_replace('{page_setup}',$page_setup,$content);
		$content = str_replace('{reply}',$replylink,$content);
		$content = str_replace('{topic_url}',$topic_url,$content);
		$content = str_replace('{mod_tools}',$modtools,$content);
		$content = str_replace('{content}',$posts,$content);
		$content = str_replace('{quickreply}',$qr,$content);
		// Mix it up
		$data['html'] = $content;
		// Return Content
		return $data;
	}
	public function showPost($pr = array(),$tid,$mr = array(),$ur = array(),$gr = array(),$tr = array(),$ismod){
		if(!$this->cache->get('forum_showPost_p'.$pr['id'].'_g'.$this->gid,$this->config['post_cache'])){
			// Post ID
			$pid = $pr['id'];
			// Quote link...
			if($tr['closed'] == 0){
				$quotelink = ' <a class="smallButton" href="index.php?act=reply&amp;tid='.$tid.'&amp;quote='.$pr['id'].'" title="Reply to this topic, quoting this post">Quote</a>';
			}elseif($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $gr['reply_closed'] == 1 OR $ismod == true){
				$quotelink = ' <a class="smallButton" href="index.php?act=reply&amp;tid='.$tid.'&amp;quote='.$pr['id'].'" title="Reply to this topic, quoting this post">Quote</a>';
			}else{
				$quotelink = ' Topic Closed';
			}
			if($_SESSION[db::$config['session_prefix'].'_loggedin'] == 'yes'){
				$complainlink = '<a title="Report abusive or spam post" class="smallButton" href="index.php?act=report&amp;pid='.$pr['id'].'&amp;tid='.$tid.'">Report Post</a> ';
			}
			// edit time
			$edittime = ($pr['dateline'] + (db::$config['edit_time'] * 60));
			// Edit link for post owners....
			if($edittime > time() AND $pr['owner'] == $_SESSION[db::$config['session_prefix'].'_userid']){
				$moderate['edit_u'] = '<a title="Edit this post" class="smallButton" href="index.php?act=moderate&amp;do=editpost&amp;pid='.$pr['id'].'">Edit Post</a>';
				$weedit = true;
			}
			// Make content non HTML-able
			$postcontent = $pr['content'];
			// Deleted post background
			if($pr['deleted'] == 1){
				$delBG = ' deletedPost';
			}
			// Get author information
			$author = $pr['owner'];
			$aq = "SELECT * FROM `users` WHERE id='$author'";
			$aq = $this->DB->query($aq);
			$ar = $aq->fetch_assoc();
			// Username
			$username = core::getUsername($pr['owner']);
			// Group
			$group = core::getGroup($ar['group']);
			
			// Moderator Security Key
			$k = core::moderationKey();
			
			// Can we delete?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $ismod == true){
				$moderate['delete'] = ':: <a onClick="moderatePost(\''.$pid.'\',\''.$author.'\',\''.$k.'\',\'deletepost\',\'pid\'); return false;" href="index.php?act=moderate&amp;do=deletepost&amp;pid='.$pr['id'].'&amp;k='.$k.'">Delete Post</a>';
			}
			// Can we Hard Delete?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1){
				$moderate['delete'] .= ':: <a onClick="moderatePost(\''.$pid.'\',\''.$author.'\',\''.$k.'\',\'hard_delete_post\',\'pid\'); return false;" href="index.php?act=moderate&amp;do=hard_delete_post&amp;pid='.$pr['id'].'&amp;k='.$k.'">Hard Delete Post</a>';
			}
			// Should we revert this post (if its been deleted)?
			if($pr['deleted'] == 1){
				if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $mr['delete_posts'] == 1){
					$moderate['delete'] = ':: <a onClick="moderatePost(\''.$pid.'\',\''.$author.'\',\''.$k.'\',\'restorepost\',\'pid\'); return false;" href="index.php?act=moderate&amp;do=restorepost&amp;pid='.$pr['id'].'&amp;k='.$k.'">Post Deleted - Restore</a>';
				}
			}
			// Can we edit?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $mr['edit_posts'] == 1 OR $weedit == true){
				$moderate['edit'] = ':: <a href="index.php?act=moderate&amp;do=editpost&amp;pid='.$pr['id'].'">Edit Post</a>';
				$moderate['edit_u'] = '<a class="smallButton" href="index.php?act=moderate&amp;do=editpost&amp;pid='.$pr['id'].'">Edit Post</a>';
			}
			// Can we ban users?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $mr['ban_users'] == 1){
				$moderate['ban'] = ':: <a onClick="moderatePost(\''.$pid.'\',\''.$author.'\',\''.$k.'\',\'banuser\',\'uid\'); return false;" href="index.php?act=moderate&amp;do=banuser&amp;uid='.$author.'&amp;username='.strip_tags($username).'&amp;k='.$k.'">Ban '.strip_tags($username).'</a>';
			}
			// Can we warn users?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $mr['warn_users'] == 1){
				$moderate['warn'] = ':: <a href="index.php?act=moderate&amp;do=warn&amp;pid='.$pid.'&amp;uid='.$author.'&amp;k='.$k.'">Warn '.strip_tags($username).'</a>';
			}
			// Can we see the IP address?
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $mr['view_ips'] == 1){
				$ip = ' &ndash; IP: '.$pr['ip'];
			}
			// Put all the Moderator tools together
			if(isset($moderate)){
				$moderate['tools'] = '<div class="postinfo" id="moderate_'.$pid.'"><strong>Post Moderation</strong> '.$moderate['delete'].' '.$moderate['edit'].' '.$moderate['ban'].' '.$moderate['warn'].'</div>';
			}
			
			// Moderator Popup
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $ismod == true){
				$moderator['popup'] = '&ndash; <a href="index.php?act=moderate&amp;do=moderate_post&amp;pid='.$pid.'">Moderator Tools</a>';
			}
			
			// Moderator Checkbox
			if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $ismod == true){
				$moderator_checkbox = ' <input type="checkbox" name="moderate_'.$pid.'" id="moderate_'.$pid.'" value='.$pid.'" title="Moderate this post ('.$pid.')" onClick="inlineModeration(\''.$tid.'\',\''.$pid.'\');" />';
			}
			
			// Do we show gravatar?
			if($ur['view_avatars'] != 0){
				// Gravatar
				$size = "80";
				$grav_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower($ar['email']) )."&amp;d=404&amp;size=".$size;
			}
			// Do we show signature?
			if($ur['view_signatures'] == 1){
				if(strlen($ar['signature']) > 0 AND $ar['disable_sig'] == 0){
					$signature = '<div class="post_sig">'.nl2br(core::parsePost($ar['signature'])).'</div>';
				}
			}
			// Edit Reason
			if(!empty($pr['edit_msg']) AND !empty($pr['edited'])){
				$signature .= '
					<!-- Edit Message -->
					<div class="editMessage">'.$pr['edited'] .' Reason: '. htmlspecialchars($pr['edit_msg']).'</div>
					<!-- / Edit Message -->';
			}
			
			// Is this user banned?
			$bq = "SELECT count(id) AS isBanned FROM `banned` WHERE uid='$author' LIMIT 1;";
			$bq = $this->DB->query($bq);
			$br = $bq->fetch_assoc();
			if($br['isBanned'] != 0){
				$banned = 'bannedRow';
			}
			
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'forum_show_topic_post.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Now, we put the post together :)
			$content = $template['container'];
			$content = str_replace('{pid}',$pid,$content);
			$content = str_replace('{tid}',$tid,$content);
			$content = str_replace('{deletedRow}',$delBG,$content);
			$content = str_replace('{bannedRow}',$banned,$content);
			$content = str_replace('{uid}',$ar['id'],$content);
			
			$content = str_replace('{avatar}',$avatar,$content);
			$content = str_replace('{group}',$group,$content);
			$content = str_replace('{postcount}',$ar['postcount'],$content);
			$content = str_replace('{joined}',date("j F Y",$ar['joined']),$content);
			
			$content = str_replace('{username_u}',$author,$content);
			$content = str_replace('{username_s}',strip_tags($username),$content);
			$content = str_replace('{username}',$username,$content);
			$content = str_replace('{username_url}',core::generateUrl('index.php?profile='.$author,'user',$author,strip_tags($username),NULL),$content);
			
			$content = str_replace('{dateline}',date("j F Y, G:H",$pr['dateline']),$content);
			$content = str_replace('{report_post}',$complainlink,$content);
			$content = str_replace('{quote}',$quotelink,$content);
			$content = str_replace('{edit_u}',$moderate['edit_u'],$content);
			$content = str_replace('{moderator_tools}',$moderate['tools'],$content);
			$content = str_replace('{moderator_checkbox}','',$content);
			$content = str_replace('{moderator_popup}','',$content);
			$content = str_replace('{ip}',$ip,$content);
			
			$content = str_replace('{postcontent}',nl2br(core::parsePost($postcontent)),$content);
			$content = str_replace('{subject}',strip_tags($pr['subject']),$content);
			$content = str_replace('{signature}',$signature,$content);
			// set the cache
			$this->cache->set('forum_showPost_p'.$pr['id'].'_g'.$this->gid,$content);
			echo 'NOT LOADED FROM CACHE';
		}else{
			$content = $this->cache->get('forum_showPost_p'.$pr['id'].'_g'.$this->gid,$this->config['post_cache']);
			echo 'LOADED FROM CACHE';
		}
		// Send Back the Content
		return $content;
	}
	public function showIndivPost($pid, $tid){
		// Secure
		$pid = $this->DB->real_escape_string(intval($pid));
		$tid = $this->DB->real_escape_string(intval($tid));
		// Get post details
		$poq = "SELECT * FROM `posts` WHERE id='$pid' AND tid='$tid' LIMIT 1;";
		$poq = $this->DB->query($poq);
		$por = $poq->fetch_assoc();
		// Does it exist?
		if($poq->num_rows == 0){
			$content = core::errorMessage('post_deleted');
			header("HTTP/1.0 404 Not Found");
			return $content;
		}
		// Get topic details
		$tid = $this->DB->real_escape_string($tid);
		$tq = "SELECT * FROM `topics` WHERE id='$tid'";
		$tq = $this->DB->query($tq);
		$tr = $tq->fetch_assoc();
		// Does this topic exist?
		if($tq->num_rows == 0){
			$content = core::errorMessage('topic_not_found');
			header("HTTP/1.0 404 Not Found");
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Get forum details
		$fid = $tr['fid'];
		$fq = "SELECT * FROM `forums` WHERE id=$fid LIMIT 1;";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			header("HTTP/1.0 404 Not Found");
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Is this forum invisible?
		if($fr['visible'] == 0 OR $fr['read_forum'] == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Get the users information
		$this->uid = $this->DB->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$uq = "SELECT * FROM `users` WHERE id='".$this->uid."' LIMIT 1;";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group details
		$this->gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$this->gid = 5;
		}
		$gq = "SELECT * FROM `groups` WHERE id='".$this->gid."' LIMIT 1;";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT * FROM `permissions` WHERE gid='".$this->gid."' AND fid='$fid'";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// If there are no results, load the default permissions
		if($pq->num_rows == 0){
			$pq = "SELECT * FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1;";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
		}
		// Get moderator details
		$mq = "SELECT * FROM `moderators` WHERE mid='$mid' AND fid='$fid' LIMIT 1;";
		$mq = $this->DB->query($mq);
		$mr = $mq->fetch_assoc();
		// Can we only view our own topics?
		if($pr['own_topics_only'] == 1){
			if($tr['owner'] != $_SESSION[db::$config['session_prefix'].'_userid']){
				$content = core::errorMessage('own_topics_only');
				$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			}
		}
		// Can we view this forum or topic?
		if($pr['viewforum'] == 0 OR $pr['viewthread'] == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Has this topic been deleted?
		if($tr['deleted'] == 1 AND $gr['supermod'] == 0 OR $tr['deleted'] AND $mr->num_rows == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Has this post been deleted?
		if($pr['deleted'] == 1 AND $gr['supermod'] == 0 OR $pr['deleted'] AND $mr->num_rows == 0){
			$content = core::errorMessage('forum_no_permission');
			$_SESSION[db::$config['session_prefix'].'_topictitle'] = 'Board Message';
			return $content;
		}
		// Is one a moderator?
		if($mq->num_rows != 0){
			$ismod = true;
		}
		// Are we showing the permalink post, or are we showing it in the full topic?
		if($_GET['pl']){
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Set URL
			$turl = core::generateUrl('index.php?topic='.$tid,'topic',$tid,$tr['title'],'');
			// Show Permalinked post
			$content = '<p>This is just one post within this topic. <a href="'.$turl.'">View original topic &amp; all posts &uarr;</a></p>'.$template['container'].
			'<p><a href="'.$turl.'">Reply to this topic, or view the complete topic</a>.</p>';
			// The Post
			$post = forum::showPost($por,$tid,$mr,$ur,$gr,$tr,$ismod);
			// Do some replacements
			$content = str_replace('{header_title}','Show Post: '.$pid,$content);
			$content = str_replace('{content}',$post,$content);
		}else{
			// We redirect to the post within the topic...
			// Get how many posts there are
			if($gr['view_deleted'] == 0 OR $mr['view_deleted']){
				$cq = "SELECT count(*) AS num FROM `posts` WHERE tid=$tid LIMIT 1;";
			}else{
				$cq = "SELECT count(*) AS num FROM `posts` WHERE tid=$tid AND deleted='0' LIMIT 1;";
			}
			$cq = $this->DB->query($cq);
			$cr = $cq->fetch_assoc();
			// Per page
			$pp = $ur['posts_per_page'];
			if(!isset($_SESSION[db::$config['session_prefix'].'_userid'])){
				$pp = 25;
			}
			// Page Var
			$pagevar = '&page=';
			if(db::$config['seo_urls'] == true){
				$pagevar = '?page=';
			}
			// Page Number
			$pg = ceil($cr['num'] / $pp);
			if($pg != 1){
				$pg = $pagevar.$pg;
			}else{
				unset($pg);
			}
			// Set URL
			$turl = core::generateUrl('index.php?topic='.$tid,'topic',$tid,$tr['title'],'');
			// Redirect
			header('Location: '.$turl.''.$pg.'#post'.$pid);
		}
		return $content;
	}
	public function newTopic($fid){
		// We MUST secure this variable... or else ;)
		$fid = $this->DB->real_escape_string(intval($fid));
		// Get forum details
		// Get forum details
		$fq = "SELECT new_topics,add_postcount,title,rules_show,rules_title,rules_text FROM `forums` WHERE id=$fid LIMIT 1;";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		// are we showing the forum rules?
		if($_GET['fr'] > 0 AND $fr['rules_show'] == 1){
			// show the forum rules
			$rules = core::errorMessage('blank_info');
			// Do some replacements
			$rules = str_replace('alt="Information"','alt="Form Rules"',$rules);
			$rules = str_replace('Information:',$fr['rules_title'],$rules);
			$rules = str_replace('{e}','<br />'.$fr['rules_text'].'<br /><br />
				<a href="index.php?act=newtopic&amp;fid='.$fid.'&amp;fr=0">Agree to Forum Rules</a>',$rules);
			// send back content
			return $rules;
		}
		// Is this forum accepting new posts?
		if($fr['new_topics'] == 0){
			$content = core::errorMessage('forum_closed');
			return $content;
		}
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			return $content;
		}
		// Get the users information
		$this->uid = $this->DB->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$uq = "SELECT `group`,disable_posting FROM `users` WHERE id='".$this->uid."' LIMIT 1";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group details
		$this->gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$this->gid = 5;
		}
		$gq = "SELECT new_topics FROM `groups` WHERE id='".$this->gid."' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT post_threads FROM `permissions` WHERE gid='".$this->gid."' AND fid='$fid' LIMIT 1";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// If there are no results, load the default permissions
		if($pq->num_rows == 0){
			$pq = "SELECT post_threads FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
		}
		// Is this user allowed to post new topics?
		if($ur['disable_posting'] == 1 OR $gr['new_topics'] == 0 OR $pr['post_threads'] == 0){
			$content = core::errorMessage('user_posting_disabled');
			return $content;
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Set Up Form
		$content = '<form action="index.php?act=do_post&type=newtopic&fid='.$fid.'" method="post">'.$template['container'].'</form>';
		// Load the New Topic Form
		$template['newtopic_p']  = $template['path'].'forum_post_newtopic.html';
		$template['newtopic'] = file_get_contents($template['newtopic_p']);
		// Replacements
		$content = str_replace('{header_title}','Post New Topic in '.$fr['title'],$content);
		$content = str_replace('{content}',$template['newtopic'],$content);
		$content = str_replace('{editor}',core::generateEditor('messagebody',''),$content);
		// Return Content
		return $content;
	}
	public function reply($tid){
		// We MUST secure this variable... or else ;)
		$tid = $this->DB->real_escape_string(intval($tid));
		// Get topic information...
		$tq = "SELECT closed,fid,title FROM `topics` WHERE id='$tid' LIMIT 1";
		$tq = $this->DB->query($tq);
		$tr = $tq->fetch_assoc();
		// Is this topic closed?
		if($tr['closed'] == 1){
			$content = core::errorMessage('topic_closed');
			return $content;
		}
		// Get forum details
		$fid = intval($tr['fid']);
		$fq = "SELECT new_posts,rules_show,rules_title,rules_text FROM `forums` WHERE id=$fid LIMIT 1;";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		// are we showing the forum rules?
		if($_GET['fr'] > 1 AND $fr['rules_show'] == 1){
			// show the forum rules
			$rules = core::errorMessage('blank_info');
			// Do some replacements
			$rules = str_replace('alt="Information"','alt="Form Rules"',$rules);
			$rules = str_replace('Information:',$fr['rules_title'],$rules);
			$rules = str_replace('{e}','<br />'.$fr['rules_text'].'<br /><br />
				<a href="index.php?act=reply&amp;tid='.$tid.'&amp;fid='.$fid.'&amp;fr=0">Agree to Forum Rules</a>',$rules);
			// send back content
			return $rules;
		}
		// Is this forum accepting new posts?
		if($fr['new_posts'] == 0){
			$content = core::errorMessage('forum_closed');
			return $content;
		}
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			return $content;
		}
		// Does this topic exist?
		if($tq->num_rows == 0){
			$content = core::errorMessage('topic_not_found');
			return $content;
		}
		// Get the users information
		$this->uid = $this->DB->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$uq = "SELECT `group`,disable_posting FROM `users` WHERE id='".$this->uid."' LIMIT 1";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group details
		$this->gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$this->gid = 5;
		}
		$gq = "SELECT new_posts FROM `groups` WHERE id='".$this->gid."' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT post_replies FROM `permissions` WHERE gid='".$this->gid."' AND fid='$fid' LIMIT 1";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// If there are no results, load the default permissions
		if($pq->num_rows == 0){
			$pq = "SELECT post_replies FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
		}
		// Is this user allowed to post new topics?
		if($ur['disable_posting'] == 1 OR $gr['new_posts'] == 0 OR $pr['post_replies'] == 0){
			$content = core::errorMessage('user_posting_disabled');
			return $content;
		}
		// Are we quoting?
		if(isset($_GET['quote'])){
			$pid = $this->DB->real_escape_string($_GET['quote']);
			$pq = "SELECT owner,content FROM `posts` WHERE id='$pid' AND deleted='0' AND tid='$tid' LIMIT 1";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
			$quote = $pr['content'];
			// Remove existing quotes :P
			$qu_f = '/\[quote(.*?)\[\/quote\]/is';
			$quote = preg_replace($qu_f,'',$quote);
			// Set up the quote
			$quote = '[quote author='.strip_tags(core::getUsername($pr['owner'])).' pid='.$pid.' tid='.$tid.']'.$quote.'[/quote]';
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Set Up Form
		$content = '<form action="index.php?act=do_post&type=reply&tid='.$tid.'&amp;fid='.$fid.'" method="post">'.$template['container'].'</form>';
		// Load the New Topic Form
		$template['reply_p']  = $template['path'].'forum_post_reply.html';
		$template['reply'] = file_get_contents($template['reply_p']);
		// Replacements
		$content = str_replace('{header_title}','Reply to '.htmlspecialchars($tr['title']),$content);
		$content = str_replace('{content}',$template['reply'],$content);
		$content = str_replace('{t_title}',htmlspecialchars($tr['title']),$content);
		$content = str_replace('{editor}',core::generateEditor('messagebody',$quote),$content);
		// Return
		return $content;
	}
	public function newTopicSubmit($fid,$data = array()){
		// temp msg
		$temp_msg = str_replace('\\','',$data['title']);
		// Make sure we've filled in all fields..
		if(empty($data['title']) OR empty($_GET['fid']) OR empty($data['messagebody']) OR strlen($temp_msg) < 1){
			$content = core::errorMessage("empty_fields");
			return $content;
		}
		// Secure the assorted data
		$fid = intval($this->DB->real_escape_string($_GET['fid']));
		$message = $this->DB->real_escape_string($_POST['messagebody']);
		$title = substr(wordwrap(trim($this->DB->real_escape_string($_POST['title'])),12,' ',true),0,65);
		$this->uid = intval($_SESSION[db::$config['session_prefix'].'_userid']);
		/* New Post Submit - Perform */
		// Get forum details
		$fq = "SELECT new_topics,add_postcount FROM `forums` WHERE id=$fid LIMIT 1;";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		// Is this forum accepting new posts?
		if($fr['new_topics'] == 0){
			$content = core::errorMessage('forum_closed');
			return $content;
		}
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			return $content;
		}
		// Get the users information
		$uq = "SELECT `group`,disable_posting FROM `users` WHERE id='".$this->uid."' LIMIT 1";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group details
		$this->gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$this->gid = 5;
			$ur['disable_posting'] = 0;
		}
		$gq = "SELECT new_topics FROM `groups` WHERE id='".$this->gid."' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT post_threads FROM `permissions` WHERE gid='".$this->gid."' AND fid='$fid' LIMIT 1";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// If there are no results, load the default permissions
		if($pq->num_rows == 0){
			$pq = "SELECT post_threads FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
		}
		// Is this user allowed to post new topics?
		if($ur['disable_posting'] == 1 OR $gr['new_topics'] == 0 OR $pr['post_threads'] == 0){
			$content = core::errorMessage('user_posting_disabled');
			return $content;
		}
		$date = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		// Now we can insert the topic :)
		$q = "INSERT INTO `topics`(fid,title,owner,dateline,views,replies,lp_subject,lp_dateline,lp_uid)
		 VALUES($fid,'$title',$this->uid,'$date',0,1,'$title','$date',$this->uid)";
		$this->DB->query($q);
		// Get the topic ID
		$tid = $this->DB->insert_id;
		// Now we can insert the message
		$q = "INSERT INTO `posts`(tid,owner,subject,content,ip,dateline,fid)
		 VALUES($tid,$this->uid,'$title','$message','$ip','$date','$fid')";
		$this->DB->query($q);
		// Get last post ID
		$pid = $this->DB->insert_id;
		// Update lastpost
		$q = "UPDATE `topics` SET lastpost='$pid' WHERE id='$tid'";
		$this->DB->query($q);
		// Do we increase post count?
		if($fr['add_postcount'] == 1){
			$q = "UPDATE `users` SET postcount=postcount+1 WHERE id='".$this->uid."'";
			$this->DB->query($q);
		}
		// update last post within the forum
		$q = "UPDATE `forums` SET lp_pid='$pid', lp_tid='$tid', lp_uid='".$this->uid."', lp_title='$title' WHERE id='$fid' LIMIT 1;";
		$this->DB->query($q);
		// Generate Topic URL
		$t_url = core::generateUrl('index.php?topic='.$tid,'topic',$tid,$title,'');
		// Show well done message
		$content = core::errorMessage("topic_success");
		$content = str_replace('{t_url}',$t_url,$content);
		// Redirect User
		header('Refresh: 5; url='.$t_url);
		// Return
		return $content;
	}
	public function replySubmit($tid,$data = array()){
		// temp msg
		$temp_msg = str_replace('\\','',$data['title']);
		// Make sure we've filled in all fields..
		if(empty($data['title']) OR empty($_GET['tid']) OR empty($data['messagebody']) OR strlen($temp_msg) < 1){
			$content = core::errorMessage("empty_fields");
			return $content;
		}
		// Secure the assorted data
		$tid = intval($this->DB->real_escape_string($_GET['tid']));
		$message = $this->DB->real_escape_string($data['messagebody']);
		$title = wordwrap(trim($this->DB->real_escape_string($_POST['title'])),12,' ',true);
		$fid = intval($this->DB->real_escape_string($_GET['fid']));
		$this->uid = intval($_SESSION[db::$config['session_prefix'].'_userid']);
		/* New Reply Submit - Perform */
		// Get topic information...
		$tq = "SELECT closed,fid FROM `topics` WHERE id='$tid' LIMIT 1";
		$tq = $this->DB->query($tq);
		$tr = $tq->fetch_assoc();
		// Is this topic closed?
		if($tr['closed'] == 1){
			$content = core::errorMessage('topic_closed');
			return $content;
		}
		// Get forum details
		$fid = $tr['fid'];
		$fq = "SELECT new_posts,add_postcount FROM `forums` WHERE id=$fid LIMIT 1";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		// Is this forum accepting new posts?
		if($fr['new_posts'] == 0){
			$content = core::errorMessage('forum_closed');
			return $content;
		}
		// Does this forum exist?
		if($fq->num_rows == 0){
			$content = core::errorMessage('forum_deleted');
			return $content;
		}
		// Does this topic exist?
		if($tq->num_rows == 0){
			$content = core::errorMessage('topic_not_found');
			return $content;
		}
		// Get the users information
		$this->uid = $this->DB->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$uq = "SELECT `group`,disable_posting FROM `users` WHERE id='".$this->uid."' LIMIT 1;";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group details
		$this->gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$this->gid = 5;
		}
		$gq = "SELECT new_posts FROM `groups` WHERE id='".$this->gid."' LIMIT 1;";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT post_replies FROM `permissions` WHERE gid='".$this->gid."' AND fid='$fid' LIMIT 1";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// If there are no results, load the default permissions
		if($pq->num_rows == 0){
			$pq = "SELECT post_replies FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
		}
		// Is this user allowed to post new topics?
		if($ur['disable_posting'] == 1 OR $gr['new_posts'] == 0 OR $pr['post_replies'] == 0){
			$content = core::errorMessage('user_posting_disabled');
			return $content;
		}
		$date = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		// Now we can insert the message
		$q = "INSERT INTO `posts`(tid,owner,subject,content,ip,dateline,fid)
		 VALUES('$tid','".$this->uid."','$title','$message','$ip','$date','$fid')";
		$this->DB->query($q);
		// Get the PID
		$pid = $this->DB->insert_id;
		// Update lastpost indicator
		$q = "UPDATE `topics` SET lastpost='$pid',lp_uid=$this->uid,lp_subject='$title',lp_dateline='$date',replies=replies+1 WHERE id='$tid'";
		$this->DB->query($q);
		// Do we increase post count?
		if($fr['add_postcount'] == 1){
			$q = "UPDATE `users` SET postcount=postcount+1 WHERE id='".$this->uid."'";
			$this->DB->query($q);
		}
		// update last post within the forum
		$q = "UPDATE `forums` SET lp_pid='$pid', lp_tid='$tid', lp_uid='".$this->uid."', lp_title='$title' WHERE id='$fid' LIMIT 1;";
		$this->DB->query($q);
		// Show well done message
		$content = core::errorMessage("post_success");
		$content = str_replace('{tid}',$tid,$content);
		$content = str_replace('{pid}',$pid,$content);
		// do we redirect without a message?
		if($_POST['no_confirm'] == 1){
			// Redirect User
			header('Location: index.php?post='.$pid.'&tid='.$tid);
			exit();
		}else{
			// Redirect User after 5 secs...
			header('Refresh: 5; url=index.php?post='.$pid.'&tid='.$tid);
		}
		// Return
		return $content;
	}
	public function reportPost($pid,$tid){
		// Escape
		$pid = $this->DB->real_escape_string(intval($pid));
		$tid = $this->DB->real_escape_string(intval($tid));
		// Are we sending it?
		if(isset($_POST['submitcomplaint']) AND isset($_POST['complaint'])){
			$q = "SELECT * FROM `posts` WHERE id='$pid'";
			$w = $this->DB->query($q);
			$r = $w->fetch_assoc();
			$postContent = $r['content'];
			$postSubject = $r['subject'];
			// User
			$userID = strip_tags(core::getUsername($r['owner']));
			$created = $r['created'];
			// Posted Data
			$username = strip_tags(core::getUsername($this->DB->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid'])));
			$complaint = $_POST['complaint'];
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			// Main Topic
			$sq = "SELECT * FROM `topics` WHERE id='$tid' LIMIT 1;";
			$sw = $this->DB->query($sq);
			$sr = $sw->fetch_assoc();
			// Topic Detail
			$topicSubject = $sr['title'];
			// Topic URL
			$postUrl = '[url]http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['PHP_SELF'].'?post='.$pid.'&tid='.$tid.'[/url]';
			$topicUrl = '[url]http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['PHP_SELF'].'?topic='.$tid.'[/url]';
			// Report PM Template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'forum_report_pm.html';
			$template['container'] = file_get_contents($template['container_p']);
			$msg = $template['container'];
			// Replacements
			$msg = str_replace('{username}',$username,$msg);
			$msg = str_replace('{ip}',$ipaddress,$msg);
			$msg = str_replace('{owner}',$userID,$msg);
			$msg = str_replace('{post_subject}',$postSubject,$msg);
			$msg = str_replace('{datetime}',$created,$msg);
			$msg = str_replace('{topic_url}',$topicUrl,$msg);
			$msg = str_replace('{post_content}',$postContent,$msg);
			$msg = str_replace('{complaint}',$complaint,$msg);
			$msg = str_replace('{post_url}',$postUrl,$msg);
			// Get moderators from this forum
			$q = "SELECT * FROM `moderators` WHERE fid='$fid'";
			$q = $this->DB->query($q);
			while($r = $q->fetch_assoc()){
				$mid = $r['mid'];
				user::insertNewPrivateMessage($r['mid'], 'Reported Post (Post: '.$post.')', $msg);
				$moderator[$mid] = true;
			}
			// Get super moderators Groups
			$q = "SELECT id FROM `groups` WHERE supermod=1 OR administrator=1";
			$q = $this->DB->query($q);
			while($r = $q->fetch_assoc()){
				// get members of this group
				$this->gid = $r['id'];
				$q2 = "SELECT id FROM `users` WHERE `group`='".$this->gid."'";
				$q2 = $this->DB->query($q2);
				while($r2 = $q2->fetch_assoc()){
					$mid = $r2['id'];
					if($moderator[$mid] == false){
						user::insertNewPrivateMessage($r2['id'], 'Reported Post (Post: '.$pid.')', $msg);
						$moderator[$mid] = true;
					}
					$moderator[$mid] = true;
				}
			}
			// show success message
			$content = core::errorMessage("report_sent");
		}else{
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Get the report Post Template
			$content = '<form method="post" action="">'.$template['container'].'</form>';
			// Get the form
			$template['form_p'] = $template['path'].'forum_report_form.html';
			$template['form'] = file_get_contents($template['form_p']);
			// Replacements
			$content = str_replace('{header_title}','Report Post '.$pid,$content);
			$content = str_replace('{content}',$template['form'],$content);
		}
		return $content;
	}

	public function viewForum($fid){
		// Connect To Database
		$this->DB = $this->DB;
		// Secure FID
		$fid = $this->DB->real_escape_string(intval($fid));
		// Get forum details
		$fq = "SELECT id,catid,title,description,is_category,parent_forum,redirect_on,redirect_url,visible,new_topics,rules_show,rules_title,rules_text
			FROM `forums` WHERE id='$fid' LIMIT 1";
		$fq = $this->DB->query($fq);
		$fr = $fq->fetch_assoc();
		$catid = $fr['catid'];
		// Does the forum exist?
		if($fq->num_rows == 0){
			// show error
			$content['html'] = core::errorMessage('forum_deleted');
			$content['title'] = 'Board Message';
			// do the 404
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");
			// send it back
			return $content;
		}
		// Forum name
		$data['title'] = htmlspecialchars($fr['title']);
		
		// Friendly URL Redirect
		if(db::$config['seo_urls'] == true){
			if($_GET['friendly_url_title'] != core::friendlyTitle($fr['title']) OR $_GET['friendly_url_used'] != 1){
				// Pagination
				if(isset($_GET['page'])){
					$new_url_page = 'page='.intval($_GET['page']);
				}
				// Put it together
				$new_url = core::generateUrl('index.php?forum='.$fr['id'],'forum',$fr['id'],$fr['title'],$new_url_page);
				// Redirect
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: $new_url");
				exit();
			}
		}
		
		// Redirects
		// Is this a category? If so, redirect...
		if($fr['is_category'] == 1){
			header('Location: index.php?#f_'.$fid.'');
			return $content;
		}
		// Does this forum redirect? If so, redirect...
		if($fr['redirect_on'] == 1){
			// redirect user
			header('Location: '.$fr['redirect_url'].'');
			exit();
		}
		// Forum Description
		$desc = strip_tags($fr['description']);
		$data['desc'] = $desc;
		// Get the users information
		$this->uid = $this->uid;
		$uq = "SELECT * FROM `users` WHERE id='".$this->uid."' LIMIT 1";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Set group ID
		$this->gid = $this->gid;
		// Get group details
		$gq = "SELECT * FROM `groups` WHERE id='".$this->gid."' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Get permission details
		$pq = "SELECT viewforum,post_threads FROM `permissions` WHERE (gid='".$this->gid."' AND fid='$fid') OR (default_p='1' AND fid='$fid') ORDER BY gid DESC LIMIT 1";
		$pq = $this->DB->query($pq);
		$pr = $pq->fetch_assoc();
		// Is this user allowed to view forum?
		if($fr['visible'] == 0 OR $pr['viewforum'] == 0){
			// show error
			$content['html'] = core::errorMessage('forum_no_permission');
			$content['title'] = 'Board Message';
			// send it back
			return $content;
		}
		
		// Sorting
		$sortby = array('owner','dateline','laspost','title','replies','views');
		$order = array('asc','desc');
		// are we choosing something correct?
		if(in_array($_GET['sortby'],$sortby)){
			$sortby = $_GET['sortby'];
			$sorting['sortby'][$_GET['sortby']] = 'selected="selected"';
		}else{
			$sortby = 'lastpost';
			$sorting['sortby']['lastpost'] = 'selected="selected"';
		}
		if(in_array($_GET['order'],$order)){
			$order = $_GET['order'];
			$sorting['order'][$_GET['order']] = 'selected="selected"';
		}else{
			$order = 'desc';
			$sorting['order']['desc'] = 'selected="selected"';
		}
		// Own Topics Only?
		if($_GET['mytopics'] == 1){
			$mytopics = ' AND owner='.$this->uid;
			$mytopics_checked = 'checked="checked"';
		}
		
		//////////////////////////////////////////////
		// PAGINATON 								//
		//////////////////////////////////////////////
		// Count the records...
		$cq = "SELECT count(id) AS num FROM `topics` WHERE fid='$fid' $mytopics LIMIT 1";
		$cq = $this->DB->query($cq);
		$cr = $cq->fetch_assoc();
		// Load Core
		require_once('./classes/core.class.php');
		$core = new core();
		// Setup required variables
		$records = $cr['num'];
		$pp = $ur['topics_per_page'];
		$current_page = intval($_GET['page']);
		$pagevar = '&amp;page';
		// current page
		if($current_page == 0){
			$current_page = 1;
		}
		if(db::$config['seo_urls'] == true){
			$pagevar = '?page';
		}
		$getvalues = str_replace(array('forum='.$fr['id'],'&page='.$current_page),'',$_SERVER['QUERY_STRING']);
		$current_url = core::generateUrl('index.php?forum='.$fr['id'],'forum',$fr['id'],$fr['title'],$getvalues);
		// Setup the pages
		$pages = $core->paginationGenerate($records,$pp,$current_page,$pagevar,$current_url);
		// Returned values
		$limit = $pages['limit'];
		
		// Parent forum
		if($fr['parent_forum'] > 0){
			// We are a sub forum
			$parent = $fr['parent_forum'];
			$bq = "SELECT id,title,catid,parent_forum FROM `forums` WHERE id='$parent' LIMIT 1";
			$bq = $this->DB->query($bq);
			$br = $bq->fetch_assoc();
			// Breadcrumb
			$parent_url = core::generateUrl('index.php?forum='.$br['id'],'forum',$br['id'],$br['title'],'');
			$breadcrumb['parent'] = '&gt;
			<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
				<a href="'.$parent_url.'" itemprop="url"><span itemprop="title">'.$br['title'].'</span></a>
			</span>';
			// Set catID
			$catid = $br['catid'];
			$parent = $br['parent_forum'];
			// Get category details
			$cq = "SELECT id,title FROM `forums` WHERE id='$catid' OR id='$parent' LIMIT 1";
			$cq = $this->DB->query($cq);
			$cr = $cq->fetch_assoc();
		}else{
			// Get category details
			$cq = "SELECT id,title FROM `forums` WHERE id='$catid' LIMIT 1";
			$cq = $this->DB->query($cq);
			$cr = $cq->fetch_assoc();
		}
		
		// Get category
		$cq = "SELECT id,title FROM `forums` WHERE id='$catid' LIMIT 1";
		$cq = $this->DB->query($cq);
		$cr = $cq->fetch_assoc();
		
		// Breadcrumb
		// Homepage
		$breadcrumb['homepage'] = '
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="index.php?" itemprop="url"><span itemprop="title">'.db::$config['site_name'].'</span></a>
	</span>';
		// Category
		$category_url = core::generateUrl('index.php?forum='.$cr['id'],'forum',$cr['id'],$cr['title'],'');
		$data['up'] = ($fr['parent_forum'] > 0 ? $parent_url : $category_url);
		$breadcrumb['category'] = '
	&gt;
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="'.$category_url.'" itemprop="url"><span itemprop="title">'.$cr['title'].'</span></a>
	</span>';
		// Forum
		$forum_url = core::generateUrl('index.php?forum='.$fr['id'],'forum',$fr['id'],$fr['title'],'');
		$breadcrumb['forum'] = '
	&gt;
	<span itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="'.$forum_url.'" itemprop="url"><span itemprop="title">'.$fr['title'].'</span></a>
	</span>';
		// Put the breadcrumb altogether
		$breadcrumb['altogether'] = '<p>'.$breadcrumb['homepage'].''.$breadcrumb['category'].''.$breadcrumb['parent'].''.$breadcrumb['forum'].'</p>';
		
		// New topic link
		if($fr['new_topics'] == 0 OR $pr['post_threads'] == 0){
			$newtopic = '<span class="buttonDisabled">You can&#39;t start a new topic</span>';
		}else{
			$newtopic = '<a href="index.php?act=newtopic&amp;fid='.$fid.'" class="buttonEnabled">New Topic</a>';
		}
		
		// Forum Rules
		if($fr['rules_show'] == 1){
			// Get Template
			$rules = '<br />'.core::errorMessage('blank_info');
			// Do some replacements
			$rules = str_replace('alt="Information"','alt="Form Rules"',$rules);
			$rules = str_replace('Information:',$fr['rules_title'],$rules);
			$rules = str_replace('{e}','<br />'.$fr['rules_text'],$rules);
		}
		// Get Forums that are within this forum (sub forums, baby)
		$cfq = "SELECT f.id, f.title, f.description, f.redirect_on, f.lp_pid, f.lp_tid, f.lp_uid, f.lp_title
			FROM forums f
			INNER JOIN permissions p ON ( p.fid = f.id ) 
			WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1 AND is_category = 0 AND parent_forum=$fid)
				OR(p.gid = $this->gid AND viewforum = 1 AND f.visible = 1 AND is_category = 0 AND parent_forum=$fid)
			GROUP BY f.id
			ORDER BY f.position, f.id";
		$cfq = $this->DB->query($cfq);
		// check there still are results
		if($cfq->num_rows > 0){
			// Tmpl
			$template['path'] = core::getCurrentThemeLocation();
			// Sub Forum Template
			$template['container_s_p'] = $template['path'].'forum_home_container.html';
			$template['container_s'] = file_get_contents($template['container_s_p']);
			// Row Template
			$template['forum_s_p'] = $template['path'].'forum_home_row.html';
			$template['forum_s'] = file_get_contents($template['forum_s_p']);
			while($cfr = $cfq->fetch_assoc()){
				unset($sub);
				$sid = $cfr['id'];
				// is it a redirector?
				if($cfr['redirect_on'] == 1){
					$sub['topics'] = '--';
					$sub['posts'] = '--';
					$sub['lastpost'] = '<em>Redirect Forum</em>';
				}else{
					// No of topics
					$tq = "SELECT count(*) AS topics FROM `topics` WHERE fid='$sid' AND deleted='0'";
					$tq = $this->DB->query($tq);
					$tr = $tq->fetch_assoc();
					$qc ++;
					$sub['topics'] = $tr['topics'];
					// No of posts
					$pq = "SELECT count(*) AS posts FROM `posts` WHERE fid='$sid' AND deleted='0'";
					$pq = $this->DB->query($pq);
					$pr = $pq->fetch_assoc();
					$qc ++;
					$sub['posts'] = $pr['posts'];
					// Last post
					if($cfr['lp_pid'] < 1){
						$sub['lastpost'] = '<div style="text-align:center;"><em>No Posts</em></div>';
					}else{
						$lpr['subject'] = htmlspecialchars(str_replace('RE: ','',$cfr['lp_title']));
						if(strlen($lpr['subject']) > 28){
							$subject = substr($lpr['subject'],0,25);
							$subject .= '...';
						}else{
							$subject = $lpr['subject'];
						}
						$sub['lastpost'] = '<a title="'.$lpr['subject'].'" href="index.php?post='.$cfr['lp_pid'].'&amp;tid='.$cfr['lp_tid'].'">'.$subject.'</a>
						<br />by: <a style="text-decoration:none;" href="'.core::generateUrl('index.php?profile='.$cfr['lp_uid'],'user',$cfr['lp_uid'],strip_tags(core::getUsername($cfr['lp_uid'])),NULL).'">'.core::getUsername($cfr['lp_uid']).'</a>';
					}
				}
				// Sub Forums
				$sfq = "SELECT f.id, f.title
					FROM forums f
					INNER JOIN permissions p ON ( p.fid = f.id ) 
					WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1 AND is_category = 0 AND parent_forum=$sid)
						OR(p.gid = $this->gid AND viewforum = 1 AND f.visible = 1 AND is_category = 0 AND parent_forum=$sid)
					GROUP BY f.id
					ORDER BY f.position, f.id";
				$sfq = $this->DB->query($sfq);
				$num = 1;
				while($sfr = $sfq->fetch_assoc()){
					if($num != 1){
						$comma = ', ';
					}elseif($num == 1){
						$comma = '';
					}
					// Friendly URL
					$s_url = core::generateUrl('index.php?forum='.$sfr['id'],'forum',$sfr['id'],$sfr['title'],'');
					// Sub Forum
					$sub_forum .= $comma.' <a href="'.$s_url.'">'.$sfr['title'].'</a>';
					// Comma
					$num ++;
				}
				// Sub Forum Container
				if($sfq->num_rows != 0){
					$sub_forum = '<div class="subForum"><strong>Sub Forum(s): </strong> '.$sub_forum.'</div>';
				}
				// Set forum Url
				$forum_url = core::generateUrl('index.php?forum='.$cfr['id'],'forum',$cfr['id'],$cfr['title'],'');
				// Get template
				$forum_r = $template['forum_s'];
				// Replacements
				$forum_r = str_replace('{fid}',$cfr['id'],$forum_r);
				$forum_r = str_replace('{title}',htmlspecialchars($cfr['title']),$forum_r);
				$forum_r = str_replace('{description}',str_replace('& ','&amp; ',$cfr['description']) . $sub_forum,$forum_r);
				$forum_r = str_replace('{topics}',$sub['topics'],$forum_r);
				$forum_r = str_replace('{posts}',$sub['posts'],$forum_r);
				$forum_r = str_replace('{lastpost}',$sub['lastpost'],$forum_r);
				$forum_r = str_replace('{forum_url}',$forum_url,$forum_r);
				// Send back
				$forums .= $forum_r;
				// Reset stuff...
				unset($comma);
				unset($num);
				unset($sub_forum);
			}
			// Put it altogether
			// Set up the template
			$subforums = $template['container_s'];
			// Do the Replacements
			$subforums = str_replace('{fid}',$fid,$subforums);
			$subforums = str_replace('{c_title}','Sub Forums in: '.htmlspecialchars($fr['title']),$subforums);
			$subforums = str_replace('{content}',$forums,$subforums);
		}
		
		// Moderation...
		if($gr['supermod'] == 1 OR $gr['administrator'] == 1){
			// set up moderation stuff
			$canMod = true;
			$mod = '
				<span class="moderateTopic">
					<a onClick="moderatePost(\'{tid}\',\'1\',\'{key}\',\'{lock_unlock}\',\'tid\'); return false;"
						href="index.php?act=moderate&amp;do={lock_unlock}&amp;tid={tid}&amp;k={key}">{lock_unlock_text}</a> :: 
					<a onClick="moderatePost(\'{tid}\',\'1\',\'{key}\',\'hard_delete_topic\',\'tid\'); return false;"
						href="index.php?act=moderate&amp;do=hard_delete_topic&amp;tid={tid}&amp;k={key}">Delete Topic</a> :: 
					<a onClick="editTopicTitle(\'{tid}\',\'{title}\'); return false;" href="index.php?act=topictitle&amp;tid={tid}">Edit Title</a></span>';
		}
		
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'forum_forum_container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$content = $template['container'];
		// Get post template
		$template['post_p'] = $template['path'].'forum_topic_row.html';
		$template['post'] = file_get_contents($template['post_p']);
		
		// Sticky Topics
		$tq = "SELECT id,title,owner,views,lastpost,dateline,closed,lp_uid,lp_dateline,lp_subject,replies FROM `topics` WHERE fid='$fid' AND sticky='1' AND deleted='0' AND visible='1' ORDER BY $sortby $order";
		$tq = $this->DB->query($tq);
		$sticky = 0;
		while($tr = $tq->fetch_assoc()){
			// Pinned Var
			$pinned = '<span class="topic_sticky">Pinned:</span>';
			$sticky = $sticky + 1;
			// Topic ID
			$tid = $tr['id'];
			// Locked
			if($tr['closed'] == 1){
				$locked = '<img src="theme/default/icons/topic_closed.png" alt="Topic Closed" />';
			}else{
				unset($locked);
			}
			// Hot topic
			if($tr['views'] > 100){
				$hot = '<span class="topic_hot">Hot:</span>';
			}else{
				unset($hot);
			}
			// My topic
			if($tr['owner'] == $this->uid){
				$my = '<span class="topic_mine">My Topic:</span>';
			}else{
				unset($my);
			}
			// Setup the Content
			$post = $template['post'];
			// Usernames
			$username = core::getUsername($tr['owner']);
			$username_lp = core::getUsername($tr['lp_uid']);
			// Topic Url
			$t_url = core::generateUrl('index.php?topic='.$tr['id'],'topic',$tr['id'],$tr['title'],'');
			$u_url = core::generateUrl('index.php?profile='.$tr['owner'],'user',$tr['owner'],strip_tags($username),NULL);
			$l_url = core::generateUrl('index.php?profile='.$tr['lp_uid'],'user',$tr['lp_uid'],strip_tags($username_lp),NULL);
			// last topic subject
			if(strlen($tr['lp_subject']) > 28){
				$lp_subject = substr($tr['lp_subject'],0,25);
				$lp_subject .= '...';
			}else{
				$lp_subject = $tr['lp_subject'];
			}
			// Moderation
			if($canMod == true){
				// set up key
				$k = core::moderationKey();
				// locked and unlocked
				if($tr['closed'] == '1'){
					$lock_unlock_text = 'Unlock';
					$lock_unlock = 'unlock';
				}else{
					$lock_unlock_text = 'Lock';
					$lock_unlock = 'lock';
				}
				// set up options
				$mod_t = $mod;
				$mod_t = str_replace('{tid}',$tid,$mod_t);
				$mod_t = str_replace('{key}',$k,$mod_t);
				$mod_t = str_replace('{lock_unlock_text}',$lock_unlock_text,$mod_t);
				$mod_t = str_replace('{lock_unlock}',$lock_unlock,$mod_t);
				$mod_t = str_replace('{title}',htmlspecialchars($tr['title']),$mod_t);
			}else{
				$mod_t = '';
			}
			// Replacements
			$post = str_replace('{pinned}',$pinned,$post);
			$post = str_replace('{tid}',$tid,$post);
			$post = str_replace('{t_title}',htmlspecialchars($tr['title']),$post);
			$post = str_replace('{t_url}',$t_url,$post);
			$post = str_replace('{dateline}',date("j F Y, G:H",$tr['dateline']),$post);
			$post = str_replace('{owner_u}',intval($tr['owner']),$post);
			$post = str_replace('{owner_url}',$u_url,$post);
			$post = str_replace('{owner}',$username,$post);
			$post = str_replace('{views}',number_format($tr['views']),$post);
			$post = str_replace('{postcount}',number_format($tr['replies']),$post);
			$post = str_replace('{lp_dateline}',date("j F Y, G:H",$tr['lp_dateline']),$post);
			$post = str_replace('{lp_pid}',$tr['lastpost'],$post);
			$post = str_replace('{lp_owner_u}',intval($tr['lp_uid']),$post);
			$post = str_replace('{lp_owner_url}',$l_url,$post);
			$post = str_replace('{lp_owner}',$username_lp,$post);
			$post = str_replace('{lp_subject}',htmlspecialchars($lp_subject),$post);
			$post = str_replace('{closed}',$locked,$post);
			$post = str_replace('{hot}',$hot,$post);
			$post = str_replace('{my}',$my,$post);
			$post = str_replace('{mod}',$mod_t,$post);
			// Join...
			$topics .= $post;
		}
		unset($pinned);
		
		// Normal topics
		$tq = "SELECT id,title,owner,views,lastpost,dateline,closed,lp_uid,lp_dateline,lp_subject,replies FROM `topics` WHERE fid='$fid' AND sticky='0' AND deleted='0' AND visible='1' $mytopics ORDER BY $sortby $order $limit";
		$tq = $this->DB->query($tq);
		while($tr = $tq->fetch_assoc()){
			// Topic ID
			$tid = $tr['id'];
			// Locked
			if($tr['closed'] == 1){
				$locked = '<img src="theme/default/icons/topic_closed.png" alt="Topic Closed" />';
			}else{
				unset($locked);
			}
			// Hot topic
			if($tr['views'] > 100){
				$hot = '<span class="topic_hot">Hot:</span>';
			}else{
				unset($hot);
			}
			// My topic
			if($tr['owner'] == $this->uid){
				$my = '<span class="topic_mine">My Topic:</span>';
			}else{
				unset($my);
			}
			// Setup the Content
			$post = $template['post'];
			// Usernames
			$username = core::getUsername($tr['owner']);
			$username_lp = core::getUsername($tr['lp_uid']);
			// Topic Url
			$t_url = core::generateUrl('index.php?topic='.$tr['id'],'topic',$tr['id'],$tr['title'],'');
			$u_url = core::generateUrl('index.php?profile='.$tr['owner'],'user',$tr['owner'],strip_tags($username),NULL);
			$l_url = core::generateUrl('index.php?profile='.$tr['lp_uid'],'user',$tr['lp_uid'],strip_tags($username_lp),NULL);
			// last topic subject
			if(strlen($tr['lp_subject']) > 28){
				$lp_subject = substr($tr['lp_subject'],0,25);
				$lp_subject .= '...';
			}else{
				$lp_subject = $tr['lp_subject'];
			}
			// Moderation
			if($canMod == true){
				// set up key
				$k = core::moderationKey();
				// locked and unlocked
				if($tr['closed'] == '1'){
					$lock_unlock_text = 'Unlock';
					$lock_unlock = 'unlock';
				}else{
					$lock_unlock_text = 'Lock';
					$lock_unlock = 'lock';
				}
				// set up options
				$mod_t = $mod;
				$mod_t = str_replace('{tid}',$tid,$mod_t);
				$mod_t = str_replace('{key}',$k,$mod_t);
				$mod_t = str_replace('{lock_unlock_text}',$lock_unlock_text,$mod_t);
				$mod_t = str_replace('{lock_unlock}',$lock_unlock,$mod_t);
				$mod_t = str_replace('{title}',htmlspecialchars($tr['title']),$mod_t);
			}else{
				$mod_t = '';
			}
			// Replacements
			$post = str_replace('{pinned}',$pinned,$post);
			$post = str_replace('{tid}',$tid,$post);
			$post = str_replace('{t_title}',htmlspecialchars($tr['title']),$post);
			$post = str_replace('{t_url}',$t_url,$post);
			$post = str_replace('{dateline}',date("j F Y, G:H",$tr['dateline']),$post);
			$post = str_replace('{owner_u}',intval($tr['owner']),$post);
			$post = str_replace('{owner_url}',$u_url,$post);
			$post = str_replace('{owner}',$username,$post);
			$post = str_replace('{views}',number_format($tr['views']),$post);
			$post = str_replace('{postcount}',number_format($tr['replies']),$post);
			$post = str_replace('{lp_dateline}',date("j F Y, G:H",$tr['lp_dateline']),$post);
			$post = str_replace('{lp_pid}',$tr['lastpost'],$post);
			$post = str_replace('{lp_owner_u}',intval($tr['lp_uid']),$post);
			$post = str_replace('{lp_owner_url}',$l_url,$post);
			$post = str_replace('{lp_owner}',$username_lp,$post);
			$post = str_replace('{lp_subject}',htmlspecialchars($lp_subject),$post);
			$post = str_replace('{closed}',$locked,$post);
			$post = str_replace('{hot}',$hot,$post);
			$post = str_replace('{my}',$my,$post);
			$post = str_replace('{mod}',$mod_t,$post);
			// Join...
			$topics .= $post;
		}
		if($tq->num_rows == 0 AND $sticky == 0){
			$topics .= 'Sorry, there are no topics in this forum available to view. If you have changed your viewing settings, <a href="index.php?forum='.$fid.'">reset them</a>.';
		}
		
		// The Sorting
		$sorting = '
		<form action="index.php" method="get" class="sorting">
			<button class="smallButton">Change Preferences</button>
			<input type="hidden" name="forum" value="'.$fid.'" />
			<input type="hidden" name="page" value="'.intval($current_page).'" />
			<label for="sortby" class="sortinglabel">Sort By:</label>
			<select name="sortby" id="sortby">
				<option value="owner"'.$sorting['sortby']['owner'].'>Author</option>
				<option value="dateline"'.$sorting['sortby']['dateline'].'>Date</option>
				<option value="lastpost"'.$sorting['sortby']['lastpost'].'>Last Post</option>
				<option value="title"'.$sorting['sortby']['title'].'>Subject</option>
				<option value="replies"'.$sorting['sortby']['replies'].'>Replies</option>
				<option value="views"'.$sorting['sortby']['views'].'>Views</option>
			</select>
			<label for="order" class="sortinglabel">Display Order:</label>
			<select name="order" id="order">
				<option value="asc"'.$sorting['order']['asc'].'>Ascending</option>
				<option value="desc"'.$sorting['order']['desc'].'>Descending</option>
			</select>
			<label for="mytopics" class="sortinglabel"><input type="checkbox" id="mytopics" name="mytopics" value="1" '.$mytopics_checked.'/> Show My Topics Only</label>
		</form>';
		
		// Content replace
		$content = str_replace('{breadcrumb}',$breadcrumb['altogether'],$content);
		$content = str_replace('{rules}',$rules,$content);
		$content = str_replace('{pages}',$pages['selection'],$content);
		$content = str_replace('{newtopic}',$newtopic,$content);
		$content = str_replace('{title}',htmlspecialchars($fr['title']),$content);
		$content = str_replace('{content}',$topics,$content);
		$content = str_replace('{subforums}',$subforums,$content);
		$content = str_replace('{sorting}',$sorting,$content);
		
		// Set up contetn
		$data['html'] = $content;
		// We hereby sentence you to 1 month in jail for murder...good old British justice :D
		return $data;
	}
	public function forumHomepage(){
		// Does it exist in the cache?
		if(!$this->cache->get('forum_forumHomepage_g'.$this->gid,$this->config['homepage_cache'])){
			// Get userinfo
			if(isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
				// get Group
				$uq = "SELECT `group` FROM `users` WHERE id='".$this->uid."' LIMIT 1";
				$uq = $this->DB->query($uq);
				$ur = $uq->fetch_assoc();
			}
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'forum_home_container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Row Template
			$template['forum_p'] = $template['path'].'forum_home_row.html';
			$template['forum'] = file_get_contents($template['forum_p']);
			// Get all categories
			$cq = "SELECT f.id, f.title
				FROM forums f
				INNER JOIN permissions p ON ( p.fid = f.id ) 
				WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1 AND is_category = 1)
					OR(p.gid = $this->gid AND viewforum = 1 AND f.visible = 1 AND is_category = 1)
				GROUP BY f.id
				ORDER BY f.position, f.id";
			$cq = $this->DB->query($cq);
			while($cr = $cq->fetch_assoc()){
				// add to forums array
				$categories[$cr['id']]['id'] = $cr['id'];
				$categories[$cr['id']]['title'] = $cr['title'];
			}
			// now, get all forums
			$fq = "SELECT f.id, f.title, f.description, f.redirect_on, f.parent_forum, f.catid, f.lp_pid, f.lp_tid, f.lp_uid, f.lp_title
				FROM forums f
				INNER JOIN permissions p ON ( p.fid = f.id ) 
				WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1 AND is_category = 0)
					OR(p.gid = $this->gid AND viewforum = 1 AND f.visible = 1 AND is_category = 0)
				GROUP BY f.id
				ORDER BY f.position, f.id";
			$fq = $this->DB->query($fq);
			while($fr = $fq->fetch_assoc()){
				// which is the parent?
				if($fr['parent_forum'] == 0){
					$parent = $fr['catid'];
				}else{
					$parent = $fr['parent_forum'];
				}
				// add to the forums array
				$forums[$parent][$fr['id']]['id'] = $fr['id'];
				$forums[$parent][$fr['id']]['title'] = $fr['title'];
				$forums[$parent][$fr['id']]['description'] = $fr['description'];
				$forums[$parent][$fr['id']]['redirect_on'] = $fr['redirect_on'];
				$forums[$parent][$fr['id']]['lp_pid'] = $fr['lp_pid'];
				$forums[$parent][$fr['id']]['lp_uid'] = $fr['lp_uid'];
				$forums[$parent][$fr['id']]['lp_tid'] = $fr['lp_tid'];
				$forums[$parent][$fr['id']]['lp_title'] = $fr['lp_title'];
			}
			// now we loop through 'em all
			foreach($categories as $cat){
				// cat ID
				$cid = $cat['id'];
				// get forums inside this forum...
				foreach($forums[$cid] as $forum){
					// Set forum ID
					$fid = $forum['id'];
					// is it a redirector?
					if($forum['redirect_on'] == 1){
						$sub['topics'] = '--';
						$sub['posts'] = '--';
						$sub['lastpost'] = '<em>Redirect Forum</em>';
					}else{
						// No of topics
						$tq = "SELECT count(*) AS topics FROM `topics` WHERE fid='$fid' AND deleted='0'";
						$tq = $this->DB->query($tq);
						$tr = $tq->fetch_assoc();
						$qc ++;
						$sub['topics'] = $tr['topics'];
						// No of posts
						$pq = "SELECT count(*) AS posts FROM `posts` WHERE fid='$fid' AND deleted='0'";
						$pq = $this->DB->query($pq);
						$pr = $pq->fetch_assoc();
						$qc ++;
						$sub['posts'] = $pr['posts'];
						// Last post
						if($forum['lp_pid'] < 1){
							$sub['lastpost'] = '<div style="text-align:center;"><em>No Posts</em></div>';
						}else{
							$lpr['subject'] = htmlspecialchars(str_replace('RE: ','',$forum['lp_title']));
							if(strlen($lpr['subject']) > 28){
								$subject = substr($lpr['subject'],0,25);
								$subject .= '...';
							}else{
								$subject = $lpr['subject'];
							}
							$sub['lastpost'] = '<a title="'.$lpr['subject'].'" href="index.php?post='.$forum['lp_pid'].'&amp;tid='.$forum['lp_tid'].'">'.$subject.'</a>
							<br />by: <a style="text-decoration:none;" href="'.core::generateUrl('index.php?profile='.$forum['lp_uid'],'user',$forum['lp_uid'],strip_tags(core::getUsername($forum['lp_uid'])),NULL).'">'.core::getUsername($forum['lp_uid']).'</a>';
						}
					}
					// Set num
					$num = 0;
					// Get sub forums
					foreach($forums[$fid] as $subforum){
						if($num > 0){
							$comma = ', ';
						}elseif($num == 0){
							$comma = '';
						}
						// Friendly URL
						$s_url = core::generateUrl('index.php?forum='.$subforum['id'],'forum',$subforum['id'],$subforum['title'],'');
						// Sub Forum
						$sub_forum .= $comma.' <a href="'.$s_url.'">'.$subforum['title'].'</a>';
						// Incriment number by 1
						$num ++;
					}
					// Sub Forum Container
					if($num != 0){
						$sub_forum = '<div class="subForum"><strong>Sub Forum(s): </strong> '.$sub_forum.'</div>';
					}
					// Set forum Url
					$forum_url = core::generateUrl('index.php?forum='.$forum['id'],'forum',$forum['id'],$forum['title'],'');
					// Get template
					$forum_r = $template['forum'];
					// Replacements
					$forum_r = str_replace('{fid}',$forum['id'],$forum_r);
					$forum_r = str_replace('{title}',htmlspecialchars($forum['title']),$forum_r);
					$forum_r = str_replace('{description}',str_replace('& ','&amp; ',$forum['description']) . $sub_forum,$forum_r);
					$forum_r = str_replace('{topics}',$sub['topics'],$forum_r);
					$forum_r = str_replace('{posts}',$sub['posts'],$forum_r);
					$forum_r = str_replace('{lastpost}',$sub['lastpost'],$forum_r);
					$forum_r = str_replace('{forum_url}',$forum_url,$forum_r);
					// Send back
					$forums_html .= $forum_r;
					// Reset stuff...
					unset($comma);
					unset($num);
					unset($sub_forum);
				}
				// Set up the template
				$category = $template['container'];
				// Do the Replacements
				$category = str_replace('{fid}',$cid,$category);
				$category = str_replace('{c_title}',strip_tags($cat['title']),$category);
				$category = str_replace('{content}',$forums_html,$category);
				// Join
				$content .= $category;
				// Unset the forums
				unset($forums_html);
			}
			// set the cache
			$this->cache->set('forum_forumHomepage_g'.$this->gid,$content);
			// get the stats
			$content .= core::forumStats();
		}else{
			$content = $this->cache->get('forum_forumHomepage_g'.$this->gid,$this->config['homepage_cache']);
		}
		// send er back
		return $content;
	}
}
?>