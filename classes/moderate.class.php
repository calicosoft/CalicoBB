<?php
class moderate extends calicobb{
	public function canWeModerate(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Make the $tid safe..
		$tid = $sql->real_escape_string($tid);
		// Get user details
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1;";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Set group id
		$_SESSION[db::$config['session_prefix'].'_groupid'] = $ur['group'];
		// Get group
		$gid = $ur['group'];
		$gq = "SELECT supermod,administrator,access_modcp FROM `groups` WHERE id='$gid' LIMIT 1";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		// Forums we can moderate in...
		$mq = "SELECT fid FROM `moderators` WHERE gid='$gid'";
		$mq = $sql->query($mq);
		// Can we moderate?
		if($mq->num_rows != 0 OR $gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $gr['access_modcp'] == 1){
			$sql->close();
			return true;
		}else{
			$sql->close();
			return false;
		}
	}
	public function moderatorForums(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Get user details
		$uid = $_SESSION[''.db::$config['session_prefix'].'_userid'];
		$uq = "SELECT * FROM `users` WHERE id='$uid'";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group
		$gid = $ur['group'];
		$gq = "SELECT supermod,administrator,access_modcp FROM `groups` WHERE id='$gid' LIMIT 1";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		// Is on supermod or admin?
		if($gr['supermod'] == 1 OR $gr['administrator'] == 1){
			$mq = "SELECT `id` FROM `forums`";
			$mq = $sql->query($mq);
			while($mr = $mq->fetch_assoc()){
				$f_in[] = $mr['id'];
			}
		}else{
			// Forums we can moderate in...
			$mq = "SELECT `fid` FROM `moderators` WHERE gid='$gid'";
			$mq = $sql->query($mq);
			while($mr = $mq->fetch_assoc()){
				$f_in[] = $mr['fid'];
			}
		}
		if($mq->num_rows != 0){
			$f_in = join("','",$f_in);
		}
		// close
		$sql->close();
		// send back
		return $f_in;
	}
	public function moveTopic($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Make the $tid safe..
		$tid = $sql->real_escape_string($tid);
		// Get topic info
		$tq = "SELECT * FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$tq = $sql->query($tq);
		// Any results?
		if($tq->num_rows == 0){
			$content = core::errorMessage('moderator_not_found');
			return $content;
		}
		// What do we do?
		if(isset($_POST['Submit'])){
			// Secure posted data
			$fid = $sql->real_escape_string($_POST['forum']);
			// update the FID for the topic
			$tq = "UPDATE `topics` SET fid='$fid' WHERE id=$tid";
			$sql->query($tq);
			// Update the FID for the posts
			$pq = "UPDATE `posts` SET fid='$fid' WHERE tid='$tid'";
			$sql->query($pq);
			// Get fid
			$q = "SELECT fid FROM `topics` WHERE id='$tid' LIMIT 1;";
			$q = $sql->query($q);
			$r = $q->fetch_assoc();
			// Update last post within forum
			$fid = $r['fid'];
			$this->updateLastPostForum($fid);
			// Show confirmation
			$content = str_replace('{tid}',$tid,core::errorMessage('moderate_topic_moved'));
		}else{
			// Get the template
		   $template['path'] = core::getCurrentThemeLocation();
		   $template['container_p'] = $template['path'].'moderate_move_topic.html';
		   $template['container'] = file_get_contents($template['container_p']);
			// List categories we can moderate in...
			$fq = "SELECT id,title FROM `forums` WHERE id IN ('$f_in') AND is_category=0 AND redirect_on=0";
			$fq = $sql->query($fq);
			while($fr = $fq->fetch_assoc()){
				$flist .= '<tr><td><label for="'.$fr['id'].'"><input name="forum" type="radio" id="'.$fr['id'].'" value="'.$fr['id'].'" />'.$fr['title'].'</label></td></tr>';
			}
			// Put it all together
			$content = str_replace('{forums}',$flist,$template['container']);
		}
		return $content;
	}
	public function mergeTopic($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		if(isset($_POST['submit'])){
			$topic1 = $sql->real_escape_string($_POST['topic_1']);
			$topic2 = $sql->real_escape_string($_POST['topic_2']);
			// Check we can moderate both topics
			// T1
			$t1q = "SELECT id,fid FROM `topics` WHERE id='$topic1' AND fid IN ('$f_in')";
			$t1q = $sql->query($t1q);
			$t1r = $t1q->fetch_assoc();
			$fid = $t1r['fid'];
			// T2
			$t2q = "SELECT id,fid FROM `topics` WHERE id='$topic2' AND fid IN ('$f_in')";
			$t2q = $sql->query($t2q);
			$t2r = $t2q->fetch_assoc();
			// Can we?
			if($t2q->num_rows == 0 OR $t1q->num_rows == 0){
				$content = core::errorMessage('moderate_topic_no_permission');
				return $content;
			}
			// Merge...
			// Update posts
			$pq = "UPDATE `posts` SET tid='$topic1' WHERE tid='$topic2' AND fid='$fid'";
			$sql->query($pq);
			// Delete topic
			$q = "DELETE FROM `topics` WHERE id='$topic2'";
			$sql->query($q);
			// Update last post within forum
			$this->updateLastPostForum($fid);
			// Confirm
			$content = str_replace('{tid}',$topic1,core::errorMessage('moderate_topic_merged'));
		}else{
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'moderate_merge_topics.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Replacements
			$content = str_replace('{tid}',$_GET['tid'],$template['container']);
		}
		return $content;
	}
	public function sticky($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Check we can sticky..
		$tq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$tq = $sql->query($tq);
		$tr = $tq->fetch_assoc();
		if($tr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			$sq = "UPDATE `topics` SET sticky='1' WHERE id='$tid'";
			$sql->query($sq);
			$content = core::errorMessage('moderate_stickied');
		}
		return $content;
	}
	public function unsticky($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Check we can sticky..
		$tq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$tq = $sql->query($tq);
		$tr = $tq->fetch_assoc();
		if($tr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			$sq = "UPDATE `topics` SET sticky='0' WHERE id='$tid'";
			$sql->query($sq);
			$content = core::errorMessage('moderate_unstickied');
		}
		return $content;
	}
	public function lock($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Check we can sticky..
		$tq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$tq = $sql->query($tq);
		$tr = $tq->fetch_assoc();
		if($tr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			$sq = "UPDATE `topics` SET closed='1' WHERE id='$tid'";
			$sql->query($sq);
			$content = core::errorMessage('moderate_closed');
			// inside
			if($_GET['inside'] == '1'){
				exit('1');
			}
		}
		return $content;
	}
	public function unlock($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Check we can sticky..
		$tq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$tq = $sql->query($tq);
		$tr = $tq->fetch_assoc();
		if($tr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			$sq = "UPDATE `topics` SET closed='0' WHERE id='$tid'";
			$sql->query($sq);
			$content = core::errorMessage('moderate_not_closed');
			// inside
			if($_GET['inside'] == '1'){
				exit('1');
			}
		}
		return $content;
	}
	public function deleteTopic($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			if($_GET['hard'] != 1){
				$sq = "UPDATE `topics` SET deleted='1' WHERE id='$tid' LIMIT 1;";
				$sql->query($sq);
				$sq = "UPDATE `posts` SET deleted='1' WHERE tid='$tid'";
				$sql->query($sq);
			}else{
				$sq = "DELETE FROM `topics` WHERE id='$tid' LIMIT 1;";
				$sql->query($sq);
				$sq = "DELETE FROM `posts` WHERE tid='$tid'";
				$sql->query($sq);
			}
			// Get fid
			$q = "SELECT fid FROM `topics` WHERE id='$tid' LIMIT 1;";
			$q = $sql->query($q);
			$r = $q->fetch_assoc();
			// Update last post within forum
			$fid = $r['fid'];
			$this->updateLastPostForum($fid);
			$content = core::errorMessage('moderate_post_deleted');
			// Did we use Javascript?
			if($_GET['inside'] == '1'){
				exit('1');
			}
		}
		return $content;
	}
	public function hardDeleteTopic($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			if($_GET['ok'] == 1){
				$sq = "DELETE FROM `topics` WHERE id='$tid' LIMIT 1;";
				$sql->query($sq);
				$sq = "DELETE FROM `posts` WHERE tid='$tid'";
				$sql->query($sq);
				// Show success ...
				$content = core::errorMessage('moderate_post_deleted');
				// Did we use Javascript?
				if($_GET['inside'] == '1'){
					exit('1');
				}
				// Get fid
				$q = "SELECT fid FROM `topics` WHERE id='$tid' LIMIT 1;";
				$q = $sql->query($q);
				$r = $q->fetch_assoc();
				// Update last post within forum
				$fid = $r['fid'];
				$this->updateLastPostForum($fid);
			}else{
				$content = '<strong>Are you sure you wish to hard delete this topic?</strong><br /><br />
				Hard deleting a topic is permanent - there is no undo<br /><br />
				<a class="smallButton" href="index.php?'.$_SERVER['QUERY_STRING'].'&amp;ok=1">Hard Delete Topic</a>';
			}
		}
		return $content;
	}
	public function hardDeletePost($pid){
		// Post Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			if($_GET['ok'] == 1){
				// hard delete post
				$sq = "DELETE FROM `posts` WHERE id='$pid' LIMIT 1;";
				$sql->query($sq);
				// Show success
				$content = core::errorMessage('moderate_post_deleted');
				// Get fid
				$q = "SELECT fid FROM `posts` WHERE id='$pid' LIMIT 1;";
				$q = $sql->query($q);
				$r = $q->fetch_assoc();
				// Update last post within forum
				$fid = $r['fid'];
				$this->updateLastPostForum($fid);
				// Did we use Javascript?
				if($_GET['inside'] == '1'){
					exit('1');
				}
			}else{
				$content = '<strong>Are you sure you wish to hard delete this post?</strong><br /><br />
				Hard deleting a post is permanent - there is no undo<br /><br />
				<a class="smallButton" href="index.php?'.$_SERVER['QUERY_STRING'].'&amp;ok=1">Hard Delete Post</a>';
			}
		}
		return $content;
	}
	public function deletePost($pid){
		// Post Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			if($_GET['hard'] != 1){
				// soft delete post
				$sq = "UPDATE `posts` SET deleted='1' WHERE id='$pid' LIMIT 1;";
				$sql->query($sq);
			}else{
				// hard delete post
				$sq = "DELETE FROM `posts` WHERE id='$pid' LIMIT 1;";
				$sql->query($sq);
			}
			// Get fid
			$q = "SELECT fid FROM `posts` WHERE id='$pid' LIMIT 1;";
			$q = $sql->query($q);
			$r = $q->fetch_assoc();
			// Update last post within forum
			$fid = $r['fid'];
			$this->updateLastPostForum($fid);
			// Did we use Javascript?
			if($_GET['inside'] == '1'){
				exit('1');
			}
			$content = core::errorMessage('moderate_post_deleted');
		}
		return $content;
	}
	public function revertPost($pid){
		// Post Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key']){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			$sq = "UPDATE `posts` SET deleted='0' WHERE id='$pid'";
			$sql->query($sq);
			$content = core::errorMessage('moderate_post_restored');
			// Get fid
			$q = "SELECT fid FROM `posts` WHERE id='$pid' LIMIT 1;";
			$q = $sql->query($q);
			$r = $q->fetch_assoc();
			// Update last post within forum
			$fid = $r['fid'];
			$this->updateLastPostForum($fid);
		}
		// Did we use Javascript?
		if($_GET['inside'] == '1'){
			exit('1');
		}
		return $content;
	}
	public function editTopicTitle($tid){
		// Topic Security Key
		if($_GET['k'] != $_SESSION[db::$config['session_prefix'].'_mod_key'] AND $_GET['inside'] != true){
			$content = core::errorMessage('moderate_security_key');
			return $content;
		}
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		$tid = $sql->real_escape_string($tid);
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `topics` WHERE id='$tid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0){
			$content = core::errorMessage('moderate_topic_no_permission');
			// Wait ... is this inside?
			if($_GET['inside'] == '1'){
				// notify
				exit(strip_tags(core::errorMessage('moderate_topic_no_permission')));
			}
			return $content;
		}else{
			if(isset($_POST['title']) OR isset($_GET['title'])){
				// Secure the assorted data
				$tid = $sql->real_escape_string($_GET['tid']);
				$title = $sql->real_escape_string($_POST['title']);
				// inside
				if($_GET['inside'] == '1'){
					// Update
					$title = $sql->real_escape_string(urldecode($_GET['title']));
				}
				// Update
				$eq = "UPDATE `topics` SET title='$title' WHERE id='$tid'";
				$sql->query($eq);
				// Success
				$content = core::errorMessage('moderate_topic_title_edited');
				// Wait ... is this inside?
				if($_GET['inside'] == '1'){
					// notify
					exit('1');
				}
			}else{
				// get post detail
				$pq = "SELECT title FROM `topics` WHERE id='$tid'";
				$pq = $sql->query($pq);
				$pr = $pq->fetch_assoc();
				//$tid = $pr['tid'];
				// Get the template
				$template['path'] = core::getCurrentThemeLocation();
				$template['container_p'] = $template['path'].'moderate_topic_title.html';
				$template['container'] = file_get_contents($template['container_p']);
				// Do replacements
				$content = $template['container'];
				$content = str_replace('{tid}',$tid,$content);
				$content = str_replace('{title}',$pr['title'],$content);
			}
		}
		return $content;
	}
	public function editPost($pid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Get post info
		$pid = $sql->real_escape_string(intval($pid));
		$q = "SELECT owner,dateline,fid FROM `posts` WHERE id='$pid' LIMIT 1;";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		if($r['dateline'] + (db::$config['edit_time'] * 60) > time() AND $r['owner'] == $_SESSION[db::$config['session_prefix'].'_userid']){
			$canedit = true;
		}
		if($canedit == false){
			if(moderate::canWeModerate() == false){
				$content = core::errorMessage('moderator_only');
				return $content;
			}elseif(moderate::canWeModerate() == true){
				$canedit = true;
			}else{
				$content = core::errorMessage('edit_time_expired');
				return $content;
			}
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// check we can do it to this Post.
		$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		if($pr['do_permission'] == 0 AND $canedit == false){
			$content = core::errorMessage('moderate_topic_no_permission');
			return $content;
		}else{
			if(isset($_POST['messageBody'])){
				// Secure the assorted data
				$tid = $sql->real_escape_string($_GET['tid']);
				$message = $sql->real_escape_string($_POST['messageBody']);
				$title = $sql->real_escape_string($_POST['title']);
				$edit_msg = strip_tags($sql->real_escape_string($_POST['edit_msg']));
				// Edit msg
				$edited = 'Edited by '.strip_tags(core::getUsername($_SESSION[''.db::$config['session_prefix'].'_userid'])).' on '.date("j F Y, g:H").'.';
				// Update
				$eq = "UPDATE `posts` SET content='$message',subject='$title',edit_msg='$edit_msg',edited='$edited' WHERE id='$pid'";
				$sql->query($eq);
				// Update last post within forum
				$fid = $r['fid'];
				$this->updateLastPostForum($fid);
				// Success
				$content = core::errorMessage('moderate_post_edited');
			}else{
				// get post detail
				$pq = "SELECT * FROM `posts` WHERE id='$pid' LIMIT 1;";
				$pq = $sql->query($pq);
				$pr = $pq->fetch_assoc();
				$tid = $pr['tid'];
				// Get the template
				$template['path'] = core::getCurrentThemeLocation();
				$template['container_p'] = $template['path'].'moderate_edit_post.html';
				$template['container'] = file_get_contents($template['container_p']);
				// Do replacements
				$content = $template['container'];
				$content = str_replace('{tid}',$tid,$content);
				$content = str_replace('{pid}',$pid,$content);
				$content = str_replace('{subject}',htmlspecialchars($pr['subject']),$content);
				$content = str_replace('{edit_msg}',htmlspecialchars($pr['edit_msg']),$content);
				$content = str_replace('{editor}',core::generateEditor('messageBody',$pr['content']),$content);
			}
		}
		return $content;
	}
	public function warnUser(){
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// What type of warning is it?
		if(isset($_GET['pid'])){
			$pid = $sql->real_escape_string($_GET['pid']);
			// post based warning
			// check we can do it to this Post.
			$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
			$pq = $sql->query($pq);
			$pr = $pq->fetch_assoc();
			if($pr['do_permission'] == 0){
				$content = core::errorMessage('moderate_topic_no_permission');
				return $content;
			}
		}
		$qs = $_SERVER['QUERY_STRING'];
		$qs = str_replace('do=warn','',$qs);
		$qs = str_replace('act=moderate','',$qs);
		// Get warning types...
		$wq = "SELECT * FROM `warning_types`";
		$wq = $sql->query($wq);
		while($wr = $wq->fetch_assoc()){
			$reasons .= '<label for="i_'.$wr['id'].'" style="font-weight:normal;"><input name="wid" type="radio" id="i_'.$wr['id'].'" value="'.$wr['id'].'" /> '.$wr['title'].' ('.$wr['points'].' points - '.$wr['expires'].' day(s))</label>';
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'moderate_warn_user.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Do replacements
		$content = $template['container'];
		$content = str_replace('{qs}',$qs,$content);
		$content = str_replace('{reasons}',$reasons,$content);
		$content = str_replace('{max_points}',db::$config['max_warning_points'],$content);
		$content = str_replace('{editor}',core::generateEditor('message',''),$content);
		// Close & Return
		$sql->close();
		return $content;
	}
	public function doWarn($data = array()){
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Forums we can mod in
		$f_in = moderate::moderatorForums();
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Encode data
		$pid = $sql->real_escape_string($_GET['pid']);
		$uid = $sql->real_escape_string($_GET['uid']);
		$wid = $sql->real_escape_string($_POST['wid']);
		$message = $sql->real_escape_string($_POST['message']);
		// What type of warning is it?
		if(isset($_GET['pid'])){
			// post based warning
			// check we can do it to this Post.
			$pq = "SELECT count(*) AS do_permission FROM `posts` WHERE id='$pid' AND fid IN ('$f_in')";
			$pq = $sql->query($pq);
			$pr = $pq->fetch_assoc();
			if($pr['do_permission'] == 0){
				$content = core::errorMessage('moderate_topic_no_permission');
				return $content;
			}
			// post
			$pq = "SELECT `content`,`tid` FROM `posts` WHERE id='$pid' LIMIT 1";
			$pq = $sql->query($pq);
			$pr = $pq->fetch_assoc();
			$post = '[quote]'.$pr['content'].'[/quote]';
			$tid = $pr['tid'];
			$pid = $pid;
			$postlink = "\n\n[url=index.php?post=$pid&tid=$tid]View original post[/url]";
		}elseif(isset($_GET['profile_warning'])){
			$pid = 0;
			$tid = 0;
		}
		// Get warning detail
		$wq = "SELECT * FROM `warning_types` WHERE id='$wid'";
		$wq = $sql->query($wq);
		$wr = $wq->fetch_assoc();
		// when does it expire?
		$expire = time() + ($wr['expires'] * 24 * 60 * 60);
		// other stuff
		$points = $wr['points'];
		$title = $wr['title'];
		$issuer = $_SESSION[db::$config['session_prefix'].'_userid'];
		$time = time();
		// Add to DB
		$q = "INSERT INTO `warnings`(uid,tid,pid,title,points,issuer,expires,notes)
		 VALUES('$uid','$tid','$pid','$title','$points','$issuer','$expire','$message')";
		$sql->query($q);
		// warning ID
		$wid = $sql->insert_id;
		// get user group ID
		$gq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		$gid = $gr['group'];
		// PM user
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'moderate_warn_user_pm.html';
		$template['container'] = file_get_contents($template['container_p']);
		// do replacements
		$pm = $template['container'];
		$pm = str_replace('{title}',$title,$pm);
		$pm = str_replace('{message}',$message,$pm);
		$pm = str_replace('{post}',$post,$pm);
		$pm = str_replace('{postlink}',$postlink,$pm);
		// send Pm
		user::insertNewPrivateMessage($uid,'You have received a warning',$pm);
		// get the PM ID
		$pmid = $sql->insert_id;
		// update the PM ID
		$q = "UPDATE `warnings` SET pmid='$pmid' WHERE id='$wid' LIMIT 1;";
		$sql->query($q);
		// get current warning level
		$wlq = "SELECT sum(points) AS userPoints FROM `warnings` WHERE uid='$uid' AND revoked='0'";
		$wlq = $sql->query($wlq);
		$wlr = $wlq->fetch_assoc();
		$new_pts = $wlr['userPoints'];
		if($new_pts >= intval(db::$config['max_warning_points'])){
			// ban user
			moderate::banUser($uid);
			// show confirm
			$content = core::errorMessage('moderate_warning_added_banned');
		}else{
			// show confirm
			$content = core::errorMessage('moderate_warning_added');
		}
		// close
		$sql->close();
		// send er back
		return $content;
	}
	public function expireWarning($wid){
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Encode data
		$wid = $sql->real_escape_string(intval($wid));
		$uid = $sql->real_escape_string(intval($_GET['uid']));
		// do we confirm?
		if(isset($_GET['do_confirm'])){
			$q = "UPDATE `warnings` SET revoked=1 WHERE id='$wid'";
			$sql->query($q);
			// get current warning level
			$wlq = "SELECT sum(points) AS newPts FROM `warnings` WHERE uid='$uid' AND revoked='0'";
			$wlq = $sql->query($wlq);
			$wlr = $wlq->fetch_assoc();
			$new_pts = $wlr['newPts'];
			if($new_pts < intval(db::$config['max_warning_points'])){
				$content = core::errorMessage('warning_revoked_unbanned');
				// update group
				moderate::unBanUser($uid);
			}else{
				$content = core::errorMessage('warning_revoked');
			}
		}else{
			$content = core::errorMessage('warning_revoke_confirm');
		}
		$sql->close();
		return $content;
	}
	public function banUser($uid){
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Safe variables
		$uid = $sql->real_escape_string(intval($uid));
		// First, get current gid
		$q = "SELECT `group` FROM users WHERE id='$uid' LIMIT 1";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		// Secondly, check this user isn't in the same group as the moderator
		if($r['group'] == $_SESSION[db::$config['session_prefix'].'_groupid']){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','You cannot ban a user who belongs to the same group as yourself.',$content);
		}
		// Thirdly, Check they aren't already banned
		$q2 = "SELECT count(uid) AS alreadyBanned FROM `banned` WHERE uid='$uid' LIMIT 1;";
		$q2 = $sql->query($q2);
		$r2 = $q2->fetch_assoc();
		if($r2['alreadyBanned'] == 1){
			// Unban user
			return moderate::unBanUser($uid);
		}
		// Now, we may proceed
		$time = time();
		$bb = $_SESSION[db::$config['session_prefix'].'_userid'];
		$gid = $r['group'];
		$q3 = "INSERT INTO `banned`(uid,gid,banned_by,datetime)
			VALUES('$uid','$gid','$bb','$time')";
		$sql->query($q3);
		// Update group ID
		$q4 = "UPDATE `users` SET `group`='4' WHERE id='$uid' LIMIT 1;";
		$sql->query($q4);
		// Return
		$content = core::errorMessage('blank_info');
		$content = str_replace('{e}','This user has been banned as requested.',$content);
		if($_GET['inside'] == '1'){
			exit('1');
		}
		return $content;
	}
	public function unBanUser($uid){
		// Check we can moderate...
		if(moderate::canWeModerate() == false){
			$content = core::errorMessage('moderator_only');
			return $content;
		}
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Safe variables
		$uid = $sql->real_escape_string(intval($uid));
		// First, get current gid
		$q = "SELECT `gid` FROM banned WHERE uid='$uid' LIMIT 1";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		// New Group
		$gid = $r['gid'];
		if($gid == 0){
			$gid = 1;
		}
		// Delete the ban
		$q2 = "DELETE FROM `banned` WHERE uid='$uid' LIMIT 1;";
		$sql->query($q2);
		// Update group ID
		$q3 = "UPDATE `users` SET `group`='$gid' WHERE id='$uid' LIMIT 1;";
		$sql->query($q3);
		// Return
		$content = core::errorMessage('blank_info');
		$content = str_replace('{e}','This user has been unbanned as requested.',$content);
		if($_GET['inside'] == '1'){
			exit('1');
		}
		return $content;
	}
	public function updateLastPostForum($fid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Fid
		$fid = intval($fid);
		// Get post
		$pq = "SELECT id,tid,owner,subject FROM `posts` WHERE fid='$fid' AND deleted='0' ORDER BY id DESC LIMIT 1;";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		// set it up
		$pid = $pr['id'];
		$tid = $pr['tid'];
		$uid = $pr['owner'];
		$sub = $pr['subject'];
		// update lp
		$q = "UPDATE `forums` SET lp_pid='$pid', lp_tid='$tid', lp_uid='$uid', lp_title='$sub' WHERE id='$fid' LIMIT 1;";
		$sql->query($q);
		// update last post within the forum
		$q = "UPDATE `forums` SET lp_pid='$pid', lp_tid='$tid', lp_uid='$uid', lp_title='$title' WHERE id='$fid' LIMIT 1;";
		$sql->query($q);
	}
	public updateTopicDetails($tid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// tid
		$tid = intval($tid);
		// Get the last post...
		$lp = "SELECT id,owner,subject,dateline FROM `posts` WHERE tid='$tid' ORDER BY id DESC LIMIT 1;";
		$lp = $sql->query($lp);
		$lp = $lp->fetch_assoc();
		// Get the number of posts
		$pc = "SELECT count(id) AS replies FROM `posts` WHERE tid='$tid' AND deleted=0";
		$pc = $sql->query($pc);
		$pc = $pc->fetch_assoc();
		// Update it all...
		$q = "UPDATE `topics` SET lastpost='".$lp['id']."',lp_uid=".$lp['owner'].",lp_subject='".$lp['subject']."',lp_dateline='".$lp['dateline']."',replies=".$pc['replies']." WHERE id='$tid'";
		$sql->query($q);
	}
	public function moderationKey(){
		// do we have one?
		if(!isset($_SESSION[db::$config['session_prefix'].'_mod_key'])){
			$k = sha1(mt_rand(1,100000000));
		}else{
			$k = $_SESSION[db::$config['session_prefix'].'_mod_key'];
		}
	}
}
?>