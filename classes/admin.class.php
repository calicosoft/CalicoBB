<?php
class forumAdmin{
	public function adminLogin(){
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// What are we doing?
		if(isset($_POST['username']) AND isset($_POST['password'])){
			// Convert password to md5 hash 
			$password = $_POST['password'];
			$password = sha1(md5($password));
			// Secure Data
			$username = $sql->real_escape_string($_POST['username']);
			$password = $sql->real_escape_string($password);
			// Find from database
			$q = "SELECT * from `users` WHERE username='$username' AND password='$password'";
			$r = $sql->query($q);
			// If we find results, do it babe
			if($r->num_rows == 1){
				$r = $r->fetch_assoc();
				if($r['group'] != 2){
					header("Location: admin.php?act=login&err=not_admin");
					exit();
				}
				$_SESSION[''.db::$config['session_prefix'].'_ADMINLOGIN'] = true;
				header("Location: admin.php");
				exit();
			}else{
				header("Location: admin.php?act=login&err=incorrect");
				exit();
			}
		}else{
			// err msg
			switch($_GET['err']){
				case 'not_admin':
					$err = 'You could not be logged in. You are not an administrator.';
				break;
				case 'incorrect':
					$err = 'Your login details were incorrect.';
				break;
			}
			if(isset($_GER['err'])){
				$err = '<div class="status error">
        	<p><img src="theme/icons/icon_error.png" alt="Error"><span>Error:</span> '.$err.'</p>
        </div>';
			}
			// Show login form
			$root = db::$config['root'];
			$content = str_replace('{err}',$err,file_get_contents("$root/acp_theme/login.html"));
		}
		$sql->close();
		return $content;
	}
	public function debug(){
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Show debug
		$content = '<p>
	<strong>Site Path:</strong> '.$_SERVER['PHP_SELF'].'<br />
	<strong>IP Address:</strong> '.$_SERVER['SERVER_ADDR'].'<br />
	<strong>Server Name:</strong> '.$_SERVER['SERVER_NAME'].'<br />
	<strong>Server Software:</strong> '.$_SERVER['SERVER_SOFTWARE'].'<br />
	<strong>Request Time:</strong> '.$_SERVER['REQUEST_TIME'].'<br />
	<strong>Query String:</strong> '.$_SERVER['QUERY_STRING'].'<br />
	<strong>Document Root:</strong> '.$_SERVER['DOCUMENT_ROOT'].'</p>
<p>
	<strong>MySQLi Version:</strong> '.$sql->server_info.'<br />
	<strong>MySQLi Client:</strong> '.$sql->client_version.'</p>
<p>
	<strong>PHP Version:</strong> '.phpversion().'<br />
	<a href="admin.php?act=phpinfo">View PHP Info</a></p>
<p>
	<strong>CalicoBB Version:</strong> '.core::getSoftwareVersion().' (<a href="http://www.calicosoft.com/versioncheck.php?p=bb&amp;v='.core::getSoftwareVersion().'">Check</a>)</p>';
		return $content;
	}
	public function phpInfo(){
		exit(phpinfo());
	}
	public function rebuild(){
		if(isset($_GET['do'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			switch($_GET['rb_action']){
				case 'topics':
					// Last Post
					$q1 = "SELECT id FROM `topics`";
					$w1 = $sql->query($q1);
					while($r1 = $w1->fetch_assoc()){
						// tid
						$tid = intval($r1['id']);
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
					$content = '<p>Done.</p><p><a href="admin.php?">Return to admin control panel &gt;</a> or
						<a href="admin.php?act=rebuild&amp;do=rebuild">Continue Rebuilding</a></p>';
				break;
				case 'postcounts':
					// Post Counts...
					// Get the forums that allow post counts :)
					$fq = "SELECT id FROM `forums` WHERE add_postcount=1";
					$fq = $sql->query($fq);
					while($fr = $fq->fetch_assoc()){
						$f_in[] = $fr['id'];
					}
					$f_in = join("','",$f_in);
					// Now, get users
					$uq = "SELECT id FROM `users`";
					$uq = $sql->query($uq);
					while($ur = $uq->fetch_assoc()){
						$uid = $ur['id'];
						// count posts
						$pq = "SELECT count(id) AS postcount FROM `posts` WHERE owner='$uid' AND fid IN ('$f_in') AND deleted=0";
						$pq = $sql->query($pq);
						$pr = $pq->fetch_assoc();
						// update
						$postcount = $pr['postcount'];
						$q = "UPDATE `users` SET postcount='$postcount' WHERE id='$uid'";
						$sql->query($q);
					}
					$content = '<p>Done.</p><p><a href="admin.php?">Return to admin control panel &gt;</a> or
						<a href="admin.php?act=rebuild&amp;do=rebuild">Continue Rebuilding</a></p>';
				break;
				case 'forums':
					// last post indicator
					$fq = "SELECT id FROM forums";
					$fq = $sql->query($fq);
					while($fr = $fq->fetch_assoc()){
						// set fid
						$fid = $fr['id'];
						// get last post
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
					}
					$content = '<p>Done.</p><p><a href="admin.php?">Return to admin control panel &gt;</a> or
						<a href="admin.php?act=rebuild&amp;do=rebuild">Continue Rebuilding</a></p>';
				break;
				case 'cleanup':
					// now, clean up tables
					$tq = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".db::$config['db']."'";
					$tq = $sql->query($tq);
					while($tr = $tq->fetch_assoc()){
						$tbl = $tr['TABLE_NAME'];
						$tq2 = "OPTIMIZE TABLE `$tbl`";
						$sql->query($tq2);
					}
					$content = '<p>Done.</p><p><a href="admin.php?">Return to admin control panel &gt;</a> or
						<a href="admin.php?act=rebuild&amp;do=rebuild">Continue Rebuilding</a></p>';
				break;
				case 'hi':
					exit('hi');
				break;
				default:
					// show the options
					$content = '
						<p>
							Choose what you wish to rebuild.</p>
						<p>
							&bull; <a href="admin.php?act=rebuild&amp;do=rebuild&amp;rb_action=topics">Topics</a><br />
							&bull; <a href="admin.php?act=rebuild&amp;do=rebuild&amp;rb_action=postcounts">Post Counts</a><br />
							&bull; <a href="admin.php?act=rebuild&amp;do=rebuild&amp;rb_action=forums">Forums</a><br />
							&bull; <a href="admin.php?act=rebuild&amp;do=rebuild&amp;rb_action=cleanup">Cleanup Database</a></p>
							';
				break;
			}
		}else{
			// Ask if we REALLY wish to proceed
			$content = '<p>
	Are you sure you wish to proceed?</p>
<p>
	Rebuilding the cache involves resetting the last post for every topic. In order to do so, the system will query every topic on your
	messageboard and update it&#39;s last post indicator. Your tables will also be optimized which will temporarily lock them.
	This can be highly resource intensive, and should only be performed when the last
	post indicators are incorrect and your board is not experiencing high traffic.</p>
<p>
	<span style="color:#ff0000;"><strong>You may notice high SQL or CPU load.</strong></span> Once you&#39;ve clicked &quot;Proceed&quot; (below), leave the page to load <strong>fully</strong> - it will say &quot;Done&quot; when it has finished.</p>
<p>
	<a href="admin.php?act=rebuild&amp;do=rebuild">Proceed &gt;</a></p>';
		}
		return $content;
	}
	public function manageForums(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Build Query
		$cq = "SELECT * FROM `forums` WHERE is_category=1 ORDER by position,id";
		// Perform Query
		$cq = $sql->query($cq);
		// Content
		$content = '<table id="rounded-corner" width="100%"> 
                	<thead> 
                    	<tr> 
                        	<th width="90%" class="rounded-company">Forum Name</th> 
                            <th width="5%">Order</th> 
                            <th width="5%" class="rounded-q4">Actions</th> 
                        </tr> 
                    </thead> 
                    <tbody> ';
		// Odd and Even
		$class = ' class="alt"';
		// Loop Through Categories
		while($cr = $cq->fetch_assoc()){
			$class = ($class==' class="alt"') ? '' : ' class="alt"';
			$content .= '<tr class="alt"> 
                        	<td><strong>'.$cr['title'].'</strong><br />'.$cr['description'].'</td> 
                            <td>'.$cr['position'].'</td> 
                            <td style="text-align: center;"> 
                            	<a href="admin.php?act=editforum&amp;fid='.$cr['id'].'" title="Edit this forum ('.$cr['id'].')"><img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
								<a href="admin.php?act=deleteforum&amp;fid='.$cr['id'].'" class="ask" title="Delete this forum ('.$cr['id'].')"><img src="acp_theme/img/icons/icon_delete.png" alt="Delete" /></a>
                            </td> 
                        </tr> ';
			// Get sub forums
			$fid = $cr['id'];
			$sq = "SELECT * FROM `forums` WHERE catid='$fid' ORDER BY position,id";
			$sq = $sql->query($sq);
			while($sr = $sq->fetch_assoc()){
				$fid2 = $sr['id'];
				$content .= '<tr> 
                        	<td><div style="padding-left: 40px;"><strong>'.$sr['title'].'</strong><br />'.$sr['description'].'</div></td> 
                            <td>'.$sr['position'].'</td> 
                            <td style="text-align: center;"> 
                            	<a href="admin.php?act=editforum&amp;fid='.$sr['id'].'" title="Edit this forum ('.$sr['id'].')"><img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
								<a href="admin.php?act=deleteforum&amp;fid='.$sr['id'].'" title="Delete this forum ('.$sr['id'].')"><img src="acp_theme/img/icons/icon_delete.png" alt="Delete" /></a>
                            </td> 
                        </tr> ';
				// Even more sub forums
				$sq2 = "SELECT * FROM `forums` WHERE parent_forum='$fid2' ORDER BY position,id";
				$sq2 = $sql->query($sq2);
				if($sq2->num_rows != 0){
					while($sr2 = $sq2->fetch_assoc()){
						$content .= '<tr> 
                        	<td><div style="padding-left: 80px;"><strong>'.$sr2['title'].'</strong><br />'.$sr2['description'].'</div></td> 
                            <td>'.$sr2['position'].'</td> 
                            <td style="text-align: center;"> 
                            	<a href="admin.php?act=editforum&amp;fid='.$sr2['id'].'" title="Edit this forum ('.$sr2['id'].')"><img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
								<a href="admin.php?act=deleteforum&amp;fid='.$sr2['id'].'" title="Delete this forum ('.$sr2['id'].')"><img src="acp_theme/img/icons/icon_delete.png" alt="Delete" /></a>
                            </td> 
                        </tr> ';
					}
				}
			}
		}
		$content .= '</tbody> 
                </table> ';
		// close
		$sql->close();
		// send back
		return $content;
	}
	public function editForum($fid){
		// Connect
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure $fid
		$fid = $sql->real_escape_string($fid);
		// Get forum info
		$fq = "SELECT * FROM `forums` WHERE id='$fid'";
		$fq = $sql->query($fq);
		$fr = $fq->fetch_assoc();
		// Does it exist, mate?
		if($fq->num_rows == 0){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','This forum does not exist. You cannot modify it.',$content);
			return $content;
		}
		// Have we submitted?
		if(isset($_POST['editforum'])){
			// First, escape string
			foreach($_POST as $k => $v){
				$_POST[$k] = $sql->real_escape_string($v);
			}
			// Set static values
			$array = array(
				"title" => $_POST['title'],
				"description" => $_POST['description'],
				"position" => $_POST['position'],
				"new_posts" => intval($_POST['new_posts']),
				"new_topics" => intval($_POST['new_topics']),
				"read_forum" => intval($_POST['read_forum']),
				"redirect_url" => $_POST['redirect_url'],
				"redirect_on" => intval($_POST['redirect_on']),
				"parent_forum" => intval($_POST['parent_forum']),
				"catid" => intval($_POST['catid']),
				"is_category" => intval($_POST['is_category']),
				"rules_title" => $_POST['rules_title'],
				"rules_text" => $_POST['rules_text'],
				"rules_show" => intval($_POST['rules_show']),
				"add_postcount" => intval($_POST['add_postcount']),
				"position" => intval($_POST['position']),
				"visible" => intval($_POST['visible'])
			);
			$comma = '';
			foreach($array as $field => $value){
				$query .= '`'.$field.'` = "'.$value.'", ';
				$comma = ', ';
			}
			// Peform query
			$q = "UPDATE `forums` SET $query id='$fid' WHERE id='$fid'";
			$sql->query($q);
			// Well done msg :)
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','This forum has been updated as required! <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// Checkbox perms
			if($fr['new_posts'] == 1){
				$cb['new_posts'] = ' checked="checked"';
			}
			if($fr['new_topics'] == 1){
				$cb['new_topics'] = ' checked="checked"';
			}
			if($fr['read_forum'] == 1){
				$cb['read_forum'] = ' checked="checked"';
			}
			if($fr['redirect_on'] == 1){
				$cb['redirect_on'] = ' checked="checked"';
			}
			if($fr['is_category'] == 1){
				$cb['is_category'] = ' checked="checked"';
			}
			if($fr['add_postcount'] == 1){
				$cb['add_postcount'] = ' checked="checked"';
			}
			if($fr['visible'] == 1){
				$cb['visible'] = ' checked="checked"';
			}
			if($fr['rules_show'] == 1){
				$cb['rules_show'] = ' checked="checked"';
			}
			// Parent Forum
			$pfq = "SELECT id,title FROM `forums` WHERE is_category='0' ORDER BY title,id";
			$pfq = $sql->query($pfq);
			$parent_forum = '<option value="">N/A (No Parent Forum)</option>';
			while($pfr = $pfq->fetch_assoc()){
				if($pfr['id'] == $fr['parent_forum']){
					$s = ' selected="selected"';
				}
				$parent_forum .= '<option value="'.$pfr['id'].'"'.$s.'>'.$pfr['title'].'</option>';
				unset($s);
			}
			// Category
			$cfq = "SELECT id,title FROM `forums` WHERE is_category='1' ORDER BY title,id";
			$cfq = $sql->query($cfq);
			$catid = '<option value="">N/A (No Category)</option>';
			while($cfr = $cfq->fetch_assoc()){
				if($cfr['id'] == $fr['catid']){
					$s = ' selected="selected"';
				}
				$catid .= '<option value="'.$cfr['id'].'"'.$s.'>'.$cfr['title'].'</option>';
				unset($s);
			}
			// Show edit form....
			$content = '<form method="post" action="">
	<h3>
		General Settings</h3>
	<p>
		<label for="title">Forum Name:</label><input class="inputbox" id="title" name="title" type="text" value="'.$fr['title'].'" /><br />
		<span class="smltxt">(The name of the category)</span></p>
	<p>
		<label for="description">Forum Description:</label><textarea id="description" class="text-input textarea" name="description" rows="8" style="width:100%">'.$fr['description'].'</textarea><br />
		<span class="smltxt">(A description of the forum - you may use HTML)</span></p>
	<p>
		<label for="is_category"><input id="is_category" type="checkbox" name="is_category" value="1" '.$cb['is_category'].' /> This forum is a category</label>
		<span class="smltxt">(Check this box if the forum is a category. It will be closed for posting, and will not display posts contained within it.)</span></p>
	<p>
		<label for="visible"><input id="visible" type="checkbox" name="visible" value="1" '.$cb['visible'].' /> This forum is visible</label>
		<span class="smltxt">(Check this box if this forum will be visible to all users. You can overwrite this setting for specific groups using permissions.)</span></p>
	<p>
		<label for="position">Display Order:</label><input class="inputbox" id="position" name="position" type="text" value="'.$fr['position'].'" /><br />
		<span class="smltxt">(The position of the forum when displayed)</span></p>
	<h3>
		Category / Parent Forum</h3>
	<p>
		Choose <strong>ONE</strong> of the following options for a regular forum.<br />
		Choose <strong>NEITHER</strong> option if the forum is a Category (set both to N/A).</p>
	<p>
		<label for="catid">Category:</label><select id="catid" name="catid" >'.$catid.'</select><br />
		<span class="smltxt">(Enter the category this forum is contained in. Select this option if the forum will be displayed on the index page)</span></p>
	<p>
		<label for="parent_forum">Parent Forum:</label><select id="parent_forum" name="parent_forum">'.$parent_forum.'</select><br />
		<span class="smltxt">(Enter the forum this forum is a subforum of. Select this option if the forum will be displayed within another)</span></p>
	<h3>
		Posting Settings</h3>
	<p>
		<label for="new_posts"><input id="new_posts" type="checkbox" name="new_posts" value="1" '.$cb['new_posts'].' /> Allow new posts</label>
		<span class="smltxt">(Check this box if this forum will accept new posts.)</span></p>
	<p>
		<label for="new_topics"><input id="new_topics" type="checkbox" name="new_topics" value="1" '.$cb['new_topics'].' /> Allow new topics</label>
		<span class="smltxt">(Check this box if this forum will accept new topics.)</span></p>
	<p>
		<label for="read_forum"><input id="read_forum" type="checkbox" name="read_forum" value="1" '.$cb['read_forum'].' /> Forum can be read</label>
		<span class="smltxt">(Check this box if this forum can be read. Note: this will overwrite all groups permissions.)</span></p>
	<p>
		<label for="add_postcount"><input id="add_postcount" type="checkbox" name="add_postcount" value="1" '.$cb['add_postcount'].' /> Increase users postcount</label>
		<span class="smltxt">(Check this box if this users post count will increase for every post they make within this forum)</span></p>
	<h3>Redirect Settings</h3>
	<p>
		<label for="redirect_on"><input id="redirect_on" type="checkbox" name="redirect_on" value="1" '.$cb['redirect_on'].' /> This forum is a redirect forum</label>
		<span class="smltxt">(Check this box if this forum will redirect to the URL below. Enabling redirects will prevent current topics from being displayed &amp; new topics being created)</span></p>
	<p>
		<label for="redirect_url">Redirection URL:</label><input class="inputbox" id="redirect_url" name="redirect_url" type="text" value="'.$fr['redirect_url'].'" /><br />
		<span class="smltxt">(The location visitors will be redirected to.)</span></p>
	<h3>
		Forum Rules</h3>
	<p>
		<label for="rules_show"><input id="rules_show" type="checkbox" name="rules_show" value="1" '.$cb['rules_show'].' /> Show forum rules</label>
		<span class="smltxt">(Check this box if this forum will display the forum rules defined below.)</span></p>
	<p>
		<label for="rules_title">Rules Title:</label><input class="inputbox" id="rules_title" name="rules_title" type="text" value="'.$fr['rules_title'].'" /><br />
		<span class="smltxt">(The title of the forum rules.)</span></p>
	<p>
		<label for="rules_text">Forum Rules:</label><textarea id="rules_text" class="text-input textarea" name="rules_text" rows="8" style="width:100%">'.$fr['rules_text'].'</textarea><br />
		<span class="smltxt">(Enter the forum rules here - you may use HTML)</span></p>
	<h3>
		Forum Permissions</h3>
	<p>
		<a href="admin.php?act=editforumpermissions&amp;fid='.$fid.'">Edit Forum Permissions</a></p>
	<p>
		<b><input class="btnalt" name="editforum" type="submit" value="Edit Forum" /></b></p>
</form>
	';
		}
		$sql->close();
		return $content;
	}
	public function newForum(){
		// Connect
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Have we submitted?
		if(isset($_POST['newforum'])){
			// First, escape string
			foreach($_POST as $k => $v){
				$_POST[$k] = $sql->real_escape_string($v);
			}
			// Set static values
			$array = array(
				"title" => $_POST['title'],
				"description" => $_POST['description'],
				"position" => $_POST['position'],
				"new_posts" => intval($_POST['new_posts']),
				"new_topics" => intval($_POST['new_topics']),
				"read_forum" => intval($_POST['read_forum']),
				"redirect_url" => $_POST['redirect_url'],
				"redirect_on" => intval($_POST['redirect_on']),
				"parent_forum" => intval($_POST['parent_forum']),
				"catid" => intval($_POST['catid']),
				"is_category" => intval($_POST['is_category']),
				"rules_title" => $_POST['rules_title'],
				"rules_text" => $_POST['rules_text'],
				"rules_show" => intval($_POST['rules_show']),
				"add_postcount" => intval($_POST['add_postcount']),
				"position" => intval($_POST['position']),
				"visible" => intval($_POST['visible'])
			);
			$fields = "`".implode("`,`", array_keys($array))."`";
			$values = implode("','", $array);
			// Peform query
			$q = "INSERT INTO `forums`($fields)
				VALUES('$values')";
			$sql->query($q);
			// Get fid
			$fid = $sql->insert_id;
			// Insert default perms
			$q = "INSERT INTO `permissions` (default_p,fid,gid)
				VALUES('1','$fid','')";
			$sql->query($q);
			// Well done msg :)
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','This forum has been added as required! <a href="admin.php?act=editforum&amp;fid='.$fid.'">Edit this forum</a>.',$content);
		}else{
			// Parent Forum
			$pfq = "SELECT * FROM `forums` WHERE is_category='0' ORDER BY title,id";
			$pfq = $sql->query($pfq);
			$parent_forum = '<option value="">N/A (No Parent Forum)</option>';
			while($pfr = $pfq->fetch_assoc()){
				if($pfr['id'] == $cb['parent_forum']){
					$s = ' selected="selected"';
				}
				$parent_forum .= '<option value="'.$pfr['id'].'"'.$s.'>'.$pfr['title'].'</option>';
				unset($s);
			}
			// Category
			$cfq = "SELECT * FROM `forums` WHERE is_category='1' ORDER BY title,id";
			$cfq = $sql->query($cfq);
			$catid = '<option value="">N/A (No Category)</option>';
			while($cfr = $cfq->fetch_assoc()){
				if($cfr['id'] == $cb['catid']){
					$s = ' selected="selected"';
				}
				$catid .= '<option value="'.$cfr['id'].'"'.$s.'>'.$cfr['title'].'</option>';
				unset($s);
			}
			// Show edit form....
			$content = '<form method="post" action="">
	<h3>
		General Settings</h3>
	<p>
		<label for="title">Forum Name:</label><input class="inputbox" id="title" name="title" type="text" value="'.$fr['title'].'" /><br />
		<span class="smltxt">(The name of the category)</span></p>
	<p>
		<label for="description">Forum Description:</label><textarea id="description" class="text-input textarea" name="description" rows="8" style="width:100%">'.$fr['description'].'</textarea><br />
		<span class="smltxt">(A description of the forum - you may use HTML)</span></p>
	<p>
		<label for="is_category"><input id="is_category" type="checkbox" name="is_category" value="1" '.$cb['is_category'].' /> This forum is a category</label>
		<span class="smltxt">(Check this box if the forum is a category. It will be closed for posting, and will not display posts contained within it.)</span></p>
	<p>
		<label for="visible"><input id="visible" type="checkbox" name="visible" value="1" '.$cb['visible'].' /> This forum is visible</label>
		<span class="smltxt">(Check this box if this forum will be visible to all users. You can overwrite this setting for specific groups using permissions.)</span></p>
	<p>
		<label for="position">Display Order:</label><input class="inputbox" id="position" name="position" type="text" value="'.$fr['position'].'" /><br />
		<span class="smltxt">(The position of the forum when displayed)</span></p>
	<h3>
		Category / Parent Forum</h3>
	<p>
		Choose <strong>ONE</strong> of the following options for a regular forum.<br />
		Choose <strong>NEITHER</strong> option if the forum is a Category (set both to N/A).</p>
	<p>
		<label for="catid">Category:</label><select id="catid" name="catid" >'.$catid.'</select><br />
		<span class="smltxt">(Enter the category this forum is contained in. Select this option if the forum will be displayed on the index page)</span></p>
	<p>
		<label for="parent_forum">Parent Forum:</label><select id="parent_forum" name="parent_forum">'.$parent_forum.'</select><br />
		<span class="smltxt">(Enter the forum this forum is a subforum of. Select this option if the forum will be displayed within another)</span></p>
	<h3>
		Posting Settings</h3>
	<p>
		<label for="new_posts"><input id="new_posts" type="checkbox" name="new_posts" value="1" '.$cb['new_posts'].' /> Allow new posts</label>
		<span class="smltxt">(Check this box if this forum will accept new posts.)</span></p>
	<p>
		<label for="new_topics"><input id="new_topics" type="checkbox" name="new_topics" value="1" '.$cb['new_topics'].' /> Allow new topics</label>
		<span class="smltxt">(Check this box if this forum will accept new topics.)</span></p>
	<p>
		<label for="read_forum"><input id="read_forum" type="checkbox" name="read_forum" value="1" '.$cb['read_forum'].' /> Forum can be read</label>
		<span class="smltxt">(Check this box if this forum can be read. Note: this will overwrite all groups permissions.)</span></p>
	<p>
		<label for="add_postcount"><input id="add_postcount" type="checkbox" name="add_postcount" value="1" '.$cb['add_postcount'].' /> Increase users postcount</label>
		<span class="smltxt">(Check this box if this users post count will increase for every post they make within this forum)</span></p>
	<h3>Redirect Settings</h3>
	<p>
		<label for="redirect_on"><input id="redirect_on" type="checkbox" name="redirect_on" value="1" '.$cb['redirect_on'].' /> This forum is a redirect forum</label>
		<span class="smltxt">(Check this box if this forum will redirect to the URL below. Enabling redirects will prevent current topics from being displayed &amp; new topics being created)</span></p>
	<p>
		<label for="redirect_url">Redirection URL:</label><input class="inputbox" id="redirect_url" name="redirect_url" type="text" value="'.$fr['redirect_url'].'" /><br />
		<span class="smltxt">(The location visitors will be redirected to.)</span></p>
	<h3>
		Forum Rules</h3>
	<p>
		<label for="rules_show"><input id="rules_show" type="checkbox" name="rules_show" value="1" '.$cb['rules_show'].' /> Show forum rules</label>
		<span class="smltxt">(Check this box if this forum will display the forum rules defined below.)</span></p>
	<p>
		<label for="rules_title">Rules Title:</label><input class="inputbox" id="rules_title" name="rules_title" type="text" value="'.$fr['rules_title'].'" /><br />
		<span class="smltxt">(The title of the forum rules.)</span></p>
	<p>
		<label for="rules_text">Forum Rules:</label><textarea id="rules_text" class="text-input textarea" name="rules_text" rows="8" style="width:100%">'.$fr['rules_text'].'</textarea><br />
		<span class="smltxt">(Enter the forum rules here - you may use HTML)</span></p>
	<p>
		<b><input class="btnalt" name="newforum" type="submit" value="Edit Forum" /></b></p>
</form>
	';
		}
		$sql->close();
		return $content;
	}
	public function forumPermissions($fid){
		// Connect
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure $fid
		$fid = $sql->real_escape_string($fid);
		// Get forum info
		$fq = "SELECT * FROM `forums` WHERE id='$fid' LIMIT 1;";
		$fq = $sql->query($fq);
		$fr = $fq->fetch_assoc();
		// Does it exist, mate?
		if($fq->num_rows == 0){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','This forum does not exist. You cannot modify it.',$content);
			return $content;
		}
		// Have we submitted?
		if(isset($_POST['permissions_u'])){
			// Perms
			/*foreach($_POST as $k => $v){
				// go deep
				foreach($_POST[$k] as $ik => $iv){
					// Make sure the variable is safe
					$p[$k][$ik] = intval($sql->real_escape_string($iv));
				}
			}*/
			// Get permissions for this fid
			$pq = "SELECT id FROM `permissions` WHERE fid='$fid'";
			$pq = $sql->query($pq);
			while($pr = $pq->fetch_assoc()){
				$pid = $pr['id'];
				// now, set static variables
				$static = array();
				$static['viewforum'] = $_POST['viewforum'][$pid];
				$static['viewthread'] = $_POST['viewthread'][$pid];
				$static['post_attachments'] = $_POST['post_attachments'][$pid];
				$static['post_threads'] = $_POST['post_threads'][$pid];
				$static['post_replies'] = $_POST['post_replies'][$pid];
				$static['delete_posts'] = $_POST['delete_posts'][$pid];
				$static['delete_topics'] = $_POST['delete_topics'][$pid];
				$static['edit_posts'] = $_POST['edit_posts'][$pid];
				$static['own_topics_only'] = $_POST['own_topics_only'][$pid];
				// ooooooooopdate it
				$comma = '';
				foreach($static as $k => $v){
					$query .= '`'.$k.'` = "'.$v.'", ';
					$comma = ', ';
				}
				// Peform query
				$q = "UPDATE `permissions` SET $query id='$pid' WHERE id='$pid'";
				$sql->query($q);
			}
			// WE'RE MUTHAFUCKIN FINISHED
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','Permissions have been updated as required. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// delete permission set
			if(isset($_GET['deletepermissionset'])){
				$dgid = intval($_GET['deletepermissionset']);
				$q = "DELETE FROM `permissions` WHERE gid='$dgid' LIMIT 1;";
				$sql->query($q);
			}
			// Odd and Even
			$class = ' class="alt"';
			// Show opening table ;)
			$content = '
<form action="" method="post">
	<table id="rounded-corner" width="100%"> 
		<thead> 
				<tr> 
					<th width:"10%"></th>
					<th width:"10%" title="Can view this forum?">View Forum</th>
					<th width:"10%" title="Can view threads within this forum?">View Threads</th>
					<th width:"10%" title="Can post attachments to this forum?">Post Attachments</th>
					<th width:"10%" title="Can post new threads to this forum?">Post Threads</th>
					<th width:"10%" title="Can post replies in this forum?">Post Replies</th>
					<th width:"10%" title="Can delete posts in this forum?">Delete Posts</th>
					<th width:"10%" title="Can delete topics in this forum?">Delete Topics</th>
					<th width:"10%" title="Can edit posts in this forum?">Edit Posts</th>
					<th width:"10%" title="Can only view own topics in this forum?">Own Topics Only</th>
				</tr> 
			</thead> 
			<tbody>';
			// Get DEFAULT forum permissions
			$gpq = "SELECT * FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1;";
			$gpq = $sql->query($gpq);
			$gpr = $gpq->fetch_assoc();
			if($gpq->num_rows == 0){
				$gpqn = "INSERT INTO `permissions` (default_p,fid,gid)
					VALUES('1','$fid','$gid')";
				$sql->query($gpqn);
				// get the new result
				$gpq = "SELECT * FROM `permissions` WHERE fid='$fid' AND default_p='1' LIMIT 1;";
				$gpq = $sql->query($gpq);
				$gpr = $gpq->fetch_assoc();
			}
			// Perms
			foreach($gpr as $k => $v){
				if($v == 1){
					$p[$k] = ' checked="checked"';
				}else{
					$p[$k] = '';
				}
			}
			$content .= '
				<tr class="alt">
					<td colspan="10">
						<strong>Default Permissions</strong><br />
						<span class="smltxt">These permissions are applied by default to all groups who do not have permissions set below.</span>
					</td>
				</tr>
				<tr>
					<td>Default</td>
					<td><input type="checkbox" name="viewforum['.$gpr['id'].']" value="1" '.$p['viewforum'].'" /></td>
					<td><input type="checkbox" name="viewthread['.$gpr['id'].']" value="1" '.$p['viewthread'].'" /></td>
					<td><input type="checkbox" name="post_attachments['.$gpr['id'].']" value="1" '.$p['post_attachments'].'" /></td>
					<td><input type="checkbox" name="post_threads['.$gpr['id'].']" value="1" '.$p['post_threads'].'" /></td>
					<td><input type="checkbox" name="post_replies['.$gpr['id'].']" value="1" '.$p['post_replies'].'" /></td>
					<td><input type="checkbox" name="delete_posts['.$gpr['id'].']" value="1" '.$p['delete_posts'].'" /></td>
					<td><input type="checkbox" name="delete_topics['.$gpr['id'].']" value="1" '.$p['delete_topics'].'" /></td>
					<td><input type="checkbox" name="edit_posts['.$gpr['id'].']" value="1" '.$p['edit_posts'].'" /></td>
					<td><input type="checkbox" name="own_topics_only['.$gpr['id'].']" value="1" '.$p['own_topics_only'].'" /></td>
				</tr>';
			// Now, we get all groups
			$gq = "SELECT id,title FROM `groups` ORDER BY `title` ASC";
			$gq = $sql->query($gq);
			while($gr = $gq->fetch_assoc()){
				// set up gid
				$gid = $gr['id'];
				// get perms
				$pq = "SELECT * FROM `permissions` WHERE gid='$gid' AND fid='$fid' AND default_p='0' LIMIT 1;";
				$pq = $sql->query($pq);
				// any results
				if($pq->num_rows == 0){
					// do we add a new permission set?
					if($_GET['addpermissionset'] == $gid){
						// we add our permission set - using the default perms...
						$pq = "INSERT INTO `permissions` (default_p,fid,gid)
					VALUES('0','$fid','$gid')";
						$sql->query($pq);
						$pid = $sql->insert_id;
						// now, set static variables
						$static = array();
						$static['viewforum'] = $gpr['viewforum'][$pid];
						$static['viewthread'] = $gpr['viewthread'][$pid];
						$static['post_attachments'] = $gpr['post_attachments'][$pid];
						$static['post_threads'] = $gpr['post_threads'][$pid];
						$static['post_replies'] = $gpr['post_replies'][$pid];
						$static['delete_posts'] = $gpr['delete_posts'][$pid];
						$static['delete_topics'] = $gpr['delete_topics'][$pid];
						$static['edit_posts'] = $gpr['edit_posts'][$pid];
						$static['own_topics_only'] = $gpr['own_topics_only'][$pid];
						// ooooooooopdate it
						$comma = '';
						foreach($static as $k => $v){
							$query .= '`'.$k.'` = "'.intval($v).'", ';
							$comma = ', ';
						}
						// Peform query
						$pq = "UPDATE `permissions` SET $query id='$pid' WHERE id='$pid'";
						$sql->query($pq);
						// now we get the perm id
						$pq = "SELECT * FROM `permissions` WHERE id='$pid';";
						$pq = $sql->query($pq);
					}else{
						// state that it doesn't have one, and ask them if they'd like to add one....
						$add[$gid] = '(This group does not have permissions set, so will use the default permissions. 
						<a href="admin.php?act=editforumpermissions&fid='.$fid.'&amp;addpermissionset='.$gid.'">Add Permissions</a>)';
					}
				}
				// load result
				$pr = $pq->fetch_assoc();
				$content .= '
				<tr class="alt">
					<td colspan="10">
						<strong>'.$gr['title'].' Permissions</strong> '.$add[$gid].' <br />
						<span class="smltxt"><a href="admin.php?act=editforumpermissions&amp;fid='.$pr['id'].'&amp;deletepermissionset='.$gid.'">Delete
						this permission set</a> (group will use the default permissions)</span>
					</td>
				</tr>';
				if($pq->num_rows == 1){
					// checkbox
					foreach($pr as $k => $v){
						if($v == 1){
							$p[$k] = ' checked="checked"';
						}else{
							$p[$k] = '';
						}
					}
					$content .= '				
					<tr>
						<td></td>
						<td><input type="checkbox" name="viewforum['.$pr['id'].']" value="1" '.$p['viewforum'].'" /></td>
						<td><input type="checkbox" name="viewthread['.$pr['id'].']" value="1" '.$p['viewthread'].'" /></td>
						<td><input type="checkbox" name="post_attachments['.$pr['id'].']" value="1" '.$p['post_attachments'].'" /></td>
						<td><input type="checkbox" name="post_threads['.$pr['id'].']" value="1" '.$p['post_threads'].'" /></td>
						<td><input type="checkbox" name="post_replies['.$pr['id'].']" value="1" '.$p['post_replies'].'" /></td>
						<td><input type="checkbox" name="delete_posts['.$pr['id'].']" value="1" '.$p['delete_posts'].'" /></td>
						<td><input type="checkbox" name="delete_topics['.$pr['id'].']" value="1" '.$p['delete_topics'].'" /></td>
						<td><input type="checkbox" name="edit_posts['.$pr['id'].']" value="1" '.$p['edit_posts'].'" /></td>
						<td><input type="checkbox" name="own_topics_only['.$pr['id'].']" value="1" '.$p['own_topics_only'].'" /></td>
					</tr>';
				}
			}
			$content .= '</tbody></table><input type="submit" name="permissions_u" value="Update Permissions" class="btnalt" />';
		}
		// close
		$sql->close();
		// send er back
		return $content;
	}
	public function deleteForum($fid){
		if(isset($_POST['delete'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure the ID
			$fid = $sql->real_escape_string($fid);
			// We delete it
			$q = "DELETE FROM `forums` WHERE id='$fid' LIMIT 1";
			$sql->query($q);
			// Delete topics
			$q  = "DELETE FROM `topics` WHERE fid='$fid'";
			$sql->query($q);
			// Delete posts
			$q  = "DELETE FROM `posts` WHERE fid='$fid'";
			$sql->query($q);
			// Show confirmation
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','This forum has been deleted. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
			// Close
			$sql->close();
		}else{
			// We need to make sure we wish to do so
			$content = '<p>Are you sure?</p>
	<p>Deleting a category is permanent - there is no undo. Please make sure you wish to delete forum ID '.$id.' before you proceed. (<a href="index.php?forum='.$fid.'">View this forum</a>)</p>
	<p><strong>Posts &amp; topics in this forum will also be deleted.</strong></p>
	<form action="" method="post"><input type="submit" name="delete" value="Delete" class="btnalt" /></form>';
		}
		// I promise!
		return $content;
	}
	public function viewDeletedTopics(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// PAGINATION SETUP
		if(isset($_GET['page'])){
			$pageno = $_GET['page'];
			$pageno = $sql->real_escape_string($pageno);
		}else{
			$pageno = 1;
		}
		// How many?
		$pq = "select count(*) as num from `topics` where deleted='1' order by lastpost,id desc";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		$numrows = $pr['num'];
		// Rows per page
		$rows_per_page = 30;
		$lastpage = ceil($numrows/$rows_per_page);
		// Is it within the range?
		$pageno = (int)$pageno;
		if($pageno > $lastpage){
			$pageno = $lastpage;
		}
		if($pageno < 1){
			$pageno = 1;
		}
		$page_setup = '<ul class="pagination">';
		// FIRST AND PREVIOUS
		if($lastpage != 1 AND $pageno == 1){
			$page_setup .= '<li class="text">First</li> ';
		}elseif($lastpage != 1){
			$prevpage = $pageno-1;
			$page_setup .= '<li class="text"><a href="admin.php?action=deletedtopics&amp;page=1">First</a></li>
			<li><a href="admin.php?action=deletedtopics&amp;page='.$prevpage.'">'.$prevpage.'</a></li> ';
		}
		// PAGE X OF X
		if($lastpage != 1){
			$page_setup .= '<li class="page"><a href="admin.php?action=deletedtopics&amp;page='.$pageno.'" title="">'.$pageno.'</a></li> ';
		}
		// NEXT AND LAST
		if($lastpage != 1 AND $pageno == $lastpage){
			$page_setup .= '<li class="text">Last</li>';
		}elseif($lastpage != 1){
			$nextpage = $pageno+1;
			$page_setup .= '<li><a href="admin.php?action=deletedtopics&amp;page='.$nextpage.'">'.$nextpage.'</a></li>
			<li class="text"><a href="admin.php?action=deletedtopics&amp;page='.$lastpage.'">Last</a></li>';
		}
		// Setup the limit...
		$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
		// Now, we actually show the shitty deleted topics
		$tq = "SELECT * FROM `topics` where deleted='1' order by lastpost,id desc $limit";
		$tq = $sql->query($tq);
		// Set up table
		$content = '
<table id="rounded-corner" width="100%"> 
   	<thead> 
       	<tr> 
           	<th>Topic</th> 
            <th>Posted by</th> 
        </tr> 
    </thead> 
    <tbody>';
		// loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooop
		while($tr = $tq->fetch_assoc()){
			$content .= '
		<tr>
			<td><a href="index.php?topic='.$tr['id'].'">'.$tr['title'].'</a></td>
			<td>'.core::getUsername($tr['owner']).'</td>
		</tr>';
		}
		// end of the line....
		$content .= '</tbody></table>
'.$page_setup.'';
		// close it
		$sql->close();
		// hey, it's time to send a parcel
		return $content;
	}
	public function searchPosts(){
		$content = '
<form action="index.php?action=searchresults" method="get" name="search"> 
		<p>
			<input type="hidden" name="act" value="searchresults" /><label for="keyword">Keyword:</label>
			<input name="act" type="hidden" value="searchresults" />
			<input name="content_type" type="hidden" value="posts" />
			<input id="keyword" name="searchterm" type="text" /></p> 
		<p>
			<input class="btnalt" name="Submit" type="submit" value="Search" /></p>
</form>';
		// hello, is it me you're looking for?
		return $content;
	}
	public function wordFilter(){
		// Have we processed the word censor?
		if(isset($_POST['update'])){
			$filename = ''.db::$config['root'].'/badwords.txt';
			$handle = fopen($filename,w);
			fwrite($handle, $_POST['censor']);
			fclose($handle);
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','Thank you, the word filter has been updated. Changes come in to effect immediately. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			$badwords = file_get_contents(''.db::$config['root'].'/badwords.txt');
			// Display current censor
			$content = '<p>
	Posts which contain the words below will automatically be censored. The word(s) will be replaced with stars representing each character. This is performed every time the post is viewed, so changes will not be made to the database.</p>
<p>
	<span style="color:#ff0000;"><strong>Note: badwords.txt in your main forum directory MUST be writeable.</strong></span></p>
<form action="" method="post">
	<p>
		<label for="censor"><strong>Bad Words:</strong></label><textarea class="text-input textarea" id="censor" rows="8" style="width:100%;" name="censor">'.$badwords.'</textarea></p>
	<p>
		<input class="btnalt" name="update" type="submit" value="Update Word Censor" /></p>
</form>
';
		}
		return $content;
	}
	public function manageUsers(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// PAGINATION SETUP
		if(isset($_GET['page'])){
			$pageno = $_GET['page'];
			$pageno = $sql->real_escape_string($pageno);
		}else{
			$pageno = 1;
		}
		// How many?
		$pq = "select count(*) as num from `users` order by username,id asc";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		$numrows = $pr['num'];
		// Rows per page
		$rows_per_page = 25;
		$lastpage = ceil($numrows/$rows_per_page);
		// Is it within the range?
		$pageno = (int)$pageno;
		if($pageno > $lastpage){
			$pageno = $lastpage;
		}
		if($pageno < 1){
			$pageno = 1;
		}
		$page_setup = '<ul class="pagination">';
		// FIRST AND PREVIOUS
		if($lastpage != 1 AND $pageno == 1){
			$page_setup .= '<li class="text">First</li> ';
		}elseif($lastpage != 1){
			$prevpage = $pageno-1;
			$page_setup .= '<li class="text"><a href="admin.php?act=users&amp;page=1">First</a></li>
			<li><a href="admin.php?act=users&amp;page='.$prevpage.'">'.$prevpage.'</a></li> ';
		}
		// PAGE X OF X
		if($lastpage != 1){
			$page_setup .= '<li class="page"><a href="admin.php?act=users&amp;page='.$pageno.'" title="">'.$pageno.'</a></li> ';
		}
		// NEXT AND LAST
		if($lastpage != 1 AND $pageno == $lastpage){
			$page_setup .= '<li class="text">Last</li>';
		}elseif($lastpage != 1){
			$nextpage = $pageno+1;
			$page_setup .= '<li><a href="admin.php?act=users&amp;page='.$nextpage.'">'.$nextpage.'</a></li>
			<li class="text"><a href="admin.php?act=users&amp;page='.$lastpage.'">Last</a></li>';
		}
		// Setup the limit...
		$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
		// Get users
		$q = "select * from `users` order by username,id asc $limit";
		$q = $sql->query($q);
		// Get groups
		$gq = "SELECT id,title FROM `groups`";
		$gq = $sql->query($gq);
		while($gr = $gq->fetch_assoc()){
			$gid = $gr['id'];
			$group[$gid] = $gr['title'];
		}
		// Set up table
		$content = '
<table id="rounded-corner" width="100%"> 
   	<thead> 
        <tr> 
            <th width="80%">Username</th> 
            <th width="10%">Group</th> 
            <th width="10%">Actions</th> 
        </tr> 
    </thead> 
    <tbody>';
		# Loop
		while($r = $q->fetch_assoc()){
			// Are we banned?
			if($r['group'] != 4){
				$ban = '<a href="index.php?act=moderate&do=ban&amp;uid='.$r['id'].'&amp;username='.$r['username'].'" title="Ban this user from your forums"><img src="acp_theme/img/icons/icon_unapprove.png" alt="Ban" /></a>';
			}
			// Are we not yet validated?
			if($r['group'] == 3){
				$validate = '<a href="admin.php?act=approveuser&amp;uid='.$r['id'].'" title="Approve this user ('.$r['username'].')"><img src="acp_theme/img/icons/icon_approve.png" alt="Approve" /></a> ';
			}
			$gid = $r['group'];
			// show the muthafuckin row
			$content .= '
			<tr>
				<td><a href="index.php?profile='.$r['username'].'&amp;uid='.$r['id'].'">'.$r['username'].'</a></td>
				<td>'.$group[$gid].'</td>
				<td> 
					<a href="admin.php?act=edituser&amp;uid='.$r['id'].'" title="Edit this user ('.$r['username'].')"><img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
                    '.$validate.''.$ban.'
				</td></tr>';
		}
		$content .= '</tbody></table>'.$page_setup.'';
		// close
		$sql->close();
		// i can see it in your eyes, but something something
		return $content;
	}
	public function approveUser($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure the User
		$uid = $sql->real_escape_string($uid);
		// JUST DO IT
		$q = "UPDATE `users` SET `group`=1 WHERE id='$user' LIMIT 1";
		$sql->query($q);
		// success
		$content = core::errorMessage('blank_info');
		$content = str_replace('{e}','Thank you, this user has been approved.. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		// CLOSE
		$sql->close();
		// Hi I'm Liz Lemon. Watch me skateboard.
		return $content;
	}
	public function editUser($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure the User
		$uid = $sql->real_escape_string($uid);
		// Check user exists
		$uq = "SELECT * FROM `users` WHERE id='$uid' LIMIT 1";
		$uq = $sql->query($uq);
		if($uq->num_rows == 0){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','This user does not exist. Please check you followed a valid link.',$content);
			return $content;
		}
		$ur = $uq->fetch_assoc();
		// have we submitted?
		if(isset($_POST['edit'])){
			// First, escape string
			foreach($_POST as $k => $v){
				$_POST[$k] = $sql->real_escape_string($v);
			}
			// Now, build our array
			$q = array();
			$q['username'] = $_POST['username'];
			$q['email'] = $_POST['email'];
			$q['group'] = $_POST['group'];
			$q['website'] = $_POST['website'];
			$q['postcount'] = $_POST['postcount'];
			$q['signature'] = $_POST['signature'];
			// now, we set up the query
			$comma = '';
			foreach($q as $k => $v){
				$query .= '`'.$k.'` = "'.$v.'", ';
				$comma = ', ';
			}
			// Peform query
			$q = "UPDATE `users` SET $query id='$uid' WHERE id='$uid' LIMIT 1;";
			$sql->query($q);
			// we're done, baby :)
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','Thank you, this user has been edited, as required. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// show edit form...
			// Get groups
			$gq = "SELECT id,title FROM `groups`";
			$gq = $sql->query($gq);
			while($gr = $gq->fetch_assoc()){
				if($gr['id'] == $ur['group']){
					$selected = ' selected="selected"';
				}
				$groups .= '<option value="'.$gr['id'].'"'.$selected.'">'.$gr['title'].'</option>';
				unset($selected);
			}
			// oor form, yoor form, a'bodys form
			$content = '
<form action="" method="post">
	<h3>
		General User Information</h3>
	<p>
		<label for="username">Username:</label>
		<input class="inputbox" type="text" id="username" name="username" value="'.$ur['username'].'" /><br />
		<span class="smltxt">(The name displayed beside posts by this user &ndash; it is not suggested that you edit this field.)</span></p>
	<p>
		<label for="email">Email Address:</label>
		<input class="inputbox" type="text" id="email" name="email" value="'.$ur['email'].'" /><br />
		<span class="smltxt">(The email address of this user)</span></p>
	<p>
		<label for="group">Group:</label>
		<select name="group" id="group">
			'.$groups.'
		</select><br />
		<span class="smltxt">(The group to which this user belongs)</span></p>
	<h3>
		Profile Information</h3>
	<p>
		<label for="location">Location:</label>
		<input class="inputbox" type="text" id="location" name="location" value="'.$ur['location'].'" /></p>
	<p>
		<label for="website">Website:</label>
		<input class="inputbox" type="text" id="website" name="website" value="'.$ur['website'].'" /></p>
	<p>
		<label for="postcount">Postcount:</label>
		<input class="inputbox" type="text" id="postcount" name="postcount" value="'.$ur['postcount'].'" /></p>
	<p>
		<label for="signature">Signature:</label>
		<textarea id="signature" class="text-input textarea" name="signature" rows="8" style="width:100%">'.$ur['signature'].'</textarea></p>
	<p>
		<input type="submit" name="edit" value="Edit User" class="btnalt" /></p>';
		}
		// close & send
		$sql->close();
		return $content;
	}
	public function ipAddresses(){
		// have we submitted
		if(isset($_POST['ip'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			$ip = $sql->real_escape_string($_POST['ip']);
			$q = "SELECT * FROM `users` WHERE ip_address='$ip'";
			$q = $sql->query($q);
			// int content
			$content = '
<table id="rounded-corner" style="width:100%;">
	<thead>
		<tr>
			<th>Username</th>
		</tr>
	</thead>
	<tbody>';
			while($r = $q->fetch_assoc()){
				$content .= '<tr><td><a href="index.php?profile='.urlencode($r['username']).'">'.$r['username'].'</a></td></tr>';
			}
			$content .= '</tbody></table>';
		}else{
			// show form
			$content = '
<form action="" method="post">
<p>
	You can search for users who have registered with the same IP address using this function. Enter the IP address in the box below to check.</p>
<p>
	<label for="ip">IP Address:</label>
	<input class="inputbox" name="ip" value="'.$_GET['ip'].'" id="ip" /></p>
<p>
	<input type="submit" value="Check IP Address" class="btnalt" /></p></form>';
		}
		// send back
		return $content;
	}
	public function massMail(){
		// have we sent?
		if(isset($_POST['message'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// GET ALL USERS
			$q = "SELECT * FROM `users` WHERE allow_emails='1' AND optout='0'";
			$q = $sql->query($q);
			while($r = $q->fetch_assoc()){
				// set er up
				$url = 'http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['SCRIPT_NAME'].'';
				$subject = $_POST['subject'];
				$message = $_POST['message'];
				$message .= "\n\nThis mail was sent by ".db::$config['site_name'].". If you do not want to receive further emails, please login to your account below and update your settings.".
							"\n$url/index.php?act=login&r=act%3Dusercp - Manage your settings";
				$site = db::$config['site_name'];
				$headers = "From: ".db::$config['site_name']." <".db::$config['email'].">";
				// hey hey, it's the postman
				mail($r['email'],$subject,str_replace('\\','',$message),$headers);
				$content .= '<p>Email send to <strong>'.$r['username'].'</strong> ('.$r['email'].')</p>';
			}
			// post man pat has finished
			$content .= core::errorMessage('blank_info');
			$content = str_replace('{e}','Your mass mail has been sent to all users. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			$content = '
<p>
	You can use this form to send a mass email to all your forum members. All fields are required<br />
	<strong>Note:</strong> Do not abuse the mass mail system! You will be marked as spam by email providers and will not receive emails.</p>
<form action="" method="post">
	<p>
		<label for="subject">Subject:</label>
		<input class="inputbox" id="subject" name="subject" type="text" /></p>
	<p>
		<label for="message">Message:</label>
		<textarea id="message" class="text-input textarea" name="message" rows="8" style="width:100%"></textarea></p>
	<p>
		<input class="btn" name="mail" type="submit" value="Send Message" /></p>';
		}
		return $content;
	}
	public function manageGroups(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// PAGINATION SETUP
		if(isset($_GET['page'])){
			$pageno = $_GET['page'];
			$pageno = $sql->real_escape_string($pageno);
		}else{
			$pageno = 1;
		}
		// How many?
		$pq = "select count(*) as num from `groups` order by title,id asc";
		$pq = $sql->query($pq);
		$pr = $pq->fetch_assoc();
		$numrows = $pr['num'];
		// Rows per page
		$rows_per_page = 25;
		$lastpage = ceil($numrows/$rows_per_page);
		// Is it within the range?
		$pageno = (int)$pageno;
		if($pageno > $lastpage){
			$pageno = $lastpage;
		}
		if($pageno < 1){
			$pageno = 1;
		}
		$page_setup = '<ul class="pagination">';
		// FIRST AND PREVIOUS
		if($lastpage != 1 AND $pageno == 1){
			$page_setup .= '<li class="text">First</li> ';
		}elseif($lastpage != 1){
			$prevpage = $pageno-1;
			$page_setup .= '<li class="text"><a href="admin.php?act=groups&amp;page=1">First</a></li>
			<li><a href="admin.php?act=groups&amp;page='.$prevpage.'">'.$prevpage.'</a></li> ';
		}
		// PAGE X OF X
		if($lastpage != 1){
			$page_setup .= '<li class="page"><a href="admin.php?act=groups&amp;page='.$pageno.'" title="">'.$pageno.'</a></li> ';
		}
		// NEXT AND LAST
		if($lastpage != 1 AND $pageno == $lastpage){
			$page_setup .= '<li class="text">Last</li>';
		}elseif($lastpage != 1){
			$nextpage = $pageno+1;
			$page_setup .= '<li><a href="admin.php?act=groups&amp;page='.$nextpage.'">'.$nextpage.'</a></li>
			<li class="text"><a href="admin.php?act=groups&amp;page='.$lastpage.'">Last</a></li>';
		}
		// Setup the limit...
		$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
		// Get users
		$q = "select * from `groups` order by title,id asc";
		$q = $sql->query($q);
		// Get groups
		$gq = "SELECT id,title,group_format FROM `groups`";
		$gq = $sql->query($gq);
		while($gr = $gq->fetch_assoc()){
			$gid = $gr['id'];
			$group[$gid] = $gr['title'];
		}
		// Set up table
		$content = '
<table id="rounded-corner" width="100%"> 
   	<thead> 
        <tr> 
            <th width="80%">Title</th> 
            <th width="20%">Actions</th> 
        </tr> 
    </thead> 
    <tbody>';
		# Loop
		while($r = $q->fetch_assoc()){
			// show the muthafuckin row
			$content .= '
			<tr>
				<td>'.str_replace('{username}',$r['title'],$r['group_format']).'</a></td>
				<td> 
					<a href="admin.php?act=editgroup&amp;gid='.$r['id'].'" title="Edit this group ('.$r['title'].')"><img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
                    <a href="admin.php?act=deletegroup&amp;gid'.$r['id'].'" title="Delete this group ('.$r['title'].')"><img src="acp_theme/img/icons/icon_unapprove.png" alt="Delete" /></a>
				</td></tr>';
		}
		$content .= '</tbody></table>'.$page_setup.'';
		// close
		$sql->close();
		// i can see it in your eyes, but something something
		return $content;
	}
	public function deleteGroup($gid){
		if(isset($_POST['delete'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure the ID
			$gid = $sql->real_escape_string($gid);
			// We delete it
			$q = "DELETE FROM `groups` WHERE id='$gid' LIMIT 1";
			$sql->query($q);
			// Move users in to the registered group
			$q = "UPDATE `users` SET `group`='1' WHERE `group` = '$gid'";
			$sql->query($q);
			// Show confirmation
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','This group has been deleted. All users within it have been moved back to "Registered". <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// We need to make sure we wish to do so
			$content = '<p>Are you sure?</p>
	<p>Are you sure you wish to delete this group? There is no undo!</p>
	<p><strong>Users within this group will be moved to the Registered group.</strong></p>
	<form action="" method="post"><input type="submit" name="delete" value="Delete" class="btnalt" /></form>';
		}
		// Oh, hiya honey. I'm not doing anything with this, er, naked woman.
		$sql->close();
		// I promise!
		return $content;
	}
	public function editGroup($gid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure the ID
		$gid = $sql->real_escape_string($gid);
		// Now, check the group exists
		$gq = "SELECT * FROM `groups` WHERE id='$gid'";
		$gq = $sql->query($gq);
		if($gq->num_rows == 0){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','This group does not exist. Please check you followed a valid link.',$content);
			return $content;
		}
		$gr = $gq->fetch_assoc();
		// Have we submitted?
		if(isset($_POST['editgroup'])){
			$q = array('title' => $_POST['title'],
				'view_board' => $_POST['view_board'],
				'search' => $_POST['search'],
				'edit_profile' => $_POST['edit_profile'],
				'view_profile' => $_POST['view_profile'],
				'new_topics' => $_POST['new_topics'],
				'new_posts' => $_POST['new_posts'],
				'use_pm' => $_POST['use_pm'],
				'supermod' => $_POST['supermod'],
				'banned' => $_POST['banned'],
				'administrator' => $_POST['administrator'],
				'access_modcp' => $_POST['access_modcp'],
				'view_deleted' => $_POST['view_deleted'],
				'reply_closed' => $_POST['reply_closed'],
				'group_format' => $_POST['group_format']
			);
			// now, we set up the query
			$comma = '';
			foreach($q as $k => $v){
				$query .= '`'.$k.'` = "'.$v.'", ';
				$comma = ', ';
			}
			// Peform query
			$q = "UPDATE `groups` SET $query id='$gid' WHERE id='$gid' LIMIT 1;";
			$sql->query($q);
			// we're done, baby :)
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','Thank you, this group has been edited, as required. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			foreach($gr as $k => $v){
				if($v == 1){
					$p[$k] = 'checked="checked"';
				}
			}
			$content = '
<form action="" method="post">
	<h3>
		Group Information</h3>
	<p>
		<label for="title">Title:</label>
		<input class="inputbox" id="title" value="'.$gr['title'].'" name="title" /><br />
		<span class="smltxt">(The name of the group)</span></p>
	<p>
		<label for="title">Group Format:</label>
		<input class="inputbox" id="group_format" value="'.htmlspecialchars($gr['group_format'],ENT_QUOTES).'" name="group_format" /><br />
		<span class="smltxt">(The format the group will be displayed in. Make sure you have {username} included. )</span></p>
	<h3>
		Global Permissions</h3>
	<p>
		These permissions will be applied to all users within this group.</p>
	<p>
		<label for="view_board"> <input type="checkbox" id="view_board" name="view_board" value="1" '.$p['view_board'].'" /> Group can view the board?</label></p>
	<p>
		<label for="search"> <input type="checkbox" id="search" name="search" value="1" '.$p['search'].'" /> Group can search the forums?</label></p>
	<p>
		<label for="edit_profile"> <input type="checkbox" id="edit_profile" name="edit_profile" value="1" '.$p['edit_profile'].'" /> Group can edit profile?</label></p>
	<p>
		<label for="view_profile"> <input type="checkbox" id="view_profile" name="view_profile" value="1" '.$p['view_profile'].'" /> Group can view profiles?</label></p>
	<p>
		<label for="new_topics"> <input type="checkbox" id="new_topics" name="new_topics" value="1" '.$p['new_topics'].'" /> Group can create new topics?</label></p>
	<p>
		<label for="new_posts"> <input type="checkbox" id="new_posts" name="new_posts" value="1" '.$p['new_posts'].'" /> Group can make new posts?</label></p>
	<p>
		<label for="use_pm"> <input type="checkbox" id="use_pm" name="use_pm" value="1" '.$p['use_pm'].'" /> Group can use the PM system?</label></p>
	<p>
		<label for="supermod"> <input type="checkbox" id="supermod" name="supermod" value="1" '.$p['supermod'].'" /> Group is a supermoderator?</label></p>
	<p>
		<label for="banned"> <input type="checkbox" id="banned" name="banned" value="1" '.$p['banned'].'" /> Group is a banned group?</label></p>
	<p>
		<label for="administrator"> <input type="checkbox" id="administrator" name="administrator" value="1" '.$p['administrator'].'" /> Group is an administrator?</label></p>
	<p>
		<label for="access_modcp"> <input type="checkbox" id="access_modcp" name="access_modcp" value="1" '.$p['access_modcp'].'" /> Group can access moderator control panel?</label></p>
	<p>
		<label for="view_deleted"> <input type="checkbox" id="view_deleted" name="view_deleted" value="1" '.$p['view_deleted'].'" /> Group can view deleted posts?</label></p>
	<p>
		<label for="reply_closed"> <input type="checkbox" id="reply_closed" name="reply_closed" value="1" '.$p['reply_closed'].'" /> Group can reply to closed topics?</label></p>
	<input type="submit" name="editgroup" value="Edit Group" class="btnalt" /></form>
	';
		}
		$sql->close();
		return $content;
	}
	public function newGroup(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Have we submitted?
		if(isset($_POST['newgroup'])){
			$q = array('title' => $_POST['title'],
				'view_board' => $_POST['view_board'],
				'search' => $_POST['search'],
				'edit_profile' => $_POST['edit_profile'],
				'view_profile' => $_POST['view_profile'],
				'new_topics' => $_POST['new_topics'],
				'new_posts' => $_POST['new_posts'],
				'use_pm' => $_POST['use_pm'],
				'supermod' => $_POST['supermod'],
				'banned' => $_POST['banned'],
				'administrator' => $_POST['administrator'],
				'access_modcp' => $_POST['access_modcp'],
				'view_deleted' => $_POST['view_deleted'],
				'reply_closed' => $_POST['reply_closed'],
				'group_format' => $_POST['group_format']
			);
			// now, we set up the query
			$fields = "`".implode("`,`", array_keys($q))."`";
			$values = implode("','", $q);
			// Peform query
			$q = "INSERT INTO `groups`($fields)
				VALUES('$values')";
			$sql->query($q);
			// we're done, baby :)
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','Thank you, this group has been added, as required. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			$content = '
<form action="" method="post">
	<h3>
		Group Information</h3>
	<p>
		<label for="title">Title:</label>
		<input class="inputbox" id="title" value="'.$gr['title'].'" name="title" /><br />
		<span class="smltxt">(The name of the group)</span></p>
	<p>
		<label for="title">Group Format:</label>
		<input class="inputbox" id="group_format" value="{username}" name="group_format" /><br />
		<span class="smltxt">(The format the group will be displayed in. Make sure you have {username} included. )</span></p>
	<h3>
		Global Permissions</h3>
	<p>
		These permissions will be applied to all users within this group.</p>
	<p>
		<label for="view_board"> <input type="checkbox" id="view_board" name="view_board" value="1" '.$p['view_board'].'" /> Group can view the board?</label></p>
	<p>
		<label for="search"> <input type="checkbox" id="search" name="search" value="1" '.$p['search'].'" /> Group can search the forums?</label></p>
	<p>
		<label for="edit_profile"> <input type="checkbox" id="edit_profile" name="edit_profile" value="1" '.$p['edit_profile'].'" /> Group can edit profile?</label></p>
	<p>
		<label for="view_profile"> <input type="checkbox" id="view_profile" name="view_profile" value="1" '.$p['view_profile'].'" /> Group can view profiles?</label></p>
	<p>
		<label for="new_topics"> <input type="checkbox" id="new_topics" name="new_topics" value="1" '.$p['new_topics'].'" /> Group can create new topics?</label></p>
	<p>
		<label for="new_posts"> <input type="checkbox" id="new_posts" name="new_posts" value="1" '.$p['new_posts'].'" /> Group can make new posts?</label></p>
	<p>
		<label for="use_pm"> <input type="checkbox" id="use_pm" name="use_pm" value="1" '.$p['use_pm'].'" /> Group can use the PM system?</label></p>
	<p>
		<label for="supermod"> <input type="checkbox" id="supermod" name="supermod" value="1" '.$p['supermod'].'" /> Group is a supermoderator?</label></p>
	<p>
		<label for="banned"> <input type="checkbox" id="banned" name="banned" value="1" '.$p['banned'].'" /> Group is a banned group?</label></p>
	<p>
		<label for="administrator"> <input type="checkbox" id="administrator" name="administrator" value="1" '.$p['administrator'].'" /> Group is an administrator?</label></p>
	<p>
		<label for="access_modcp"> <input type="checkbox" id="access_modcp" name="access_modcp" value="1" '.$p['access_modcp'].'" /> Group can access moderator control panel?</label></p>
	<p>
		<label for="view_deleted"> <input type="checkbox" id="view_deleted" name="view_deleted" value="1" '.$p['view_deleted'].'" /> Group can view deleted posts?</label></p>
	<p>
		<label for="reply_closed"> <input type="checkbox" id="reply_closed" name="reply_closed" value="1" '.$p['reply_closed'].'" /> Group can reply to closed topics?</label></p>
	<input type="submit" name="newgroup" value="Create Group" class="btnalt" /></form>
	';
		}
		$sql->close();
		return $content;
	}
	public function warningTypes(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// List all warning types...
		$q = "SELECT * FROM `warning_types`";
		$q = $sql->query($q);
		// show inital stuff
		$content = '<p>Warnings are a way to discipline users on your forum automatically. When a user is warned, they will be notified via
			private message, and a log will be kept. Below are a list of applicable warning types (which moderators must choose from).</p>
			<p><a href="admin.php?act=addwarningtype">Add Warning Type</a></p>
		<table id="rounded-corner" width="100%"> 
			<thead> 
				<tr> 
					<th width="90%">Warning Reason</th> 
					<th width="5%">Points</th> 
					<th width="5%">Actions</th> 
				</tr> 
			</thead> 
			<tbody>';
		// now, list them
		while($r = $q->fetch_assoc()){
			$content .= '
				<tr>
					<td>'.$r['title'].'</td>
					<td>'.$r['points'].'</td>
					<td style="text-align: center;"> 
						<a href="admin.php?act=editwarningtype&amp;fid='.$r['id'].'" title="Edit this warning type ('.$r['id'].')">
							<img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
						<a href="admin.php?act=deletewarningtype&amp;fid='.$r['id'].'" title="Delete this warning type ('.$r['id'].')">
							<img src="acp_theme/img/icons/icon_delete.png" alt="Delete" /></a>
					</td>
				</tr>';
		}
		$content .= '
			</tbody>
		</table>';
		// close & return
		$sql->close();
		return $content;
	}
	public function addWarningType(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Did we submit?
		if($_POST['submit']){
			// get values
			$title = $sql->real_escape_string($_POST['title']);
			$points = intval($sql->real_escape_string($_POST['points']));
			$expires = intval($sql->real_escape_string($_POST['expires']));
			// Add it
			$q = "INSERT INTO `warning_types`(title,points,expires)
				VALUES('$title','$points','$expires');";
			$sql->query($q);
			// done
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','A new warning type has been added. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// Show form
			$content = '
			<form method="post" action="">
				<p>
					<label for="title">Warning Title</label>
					<input type="text" class="inputbox" id="title" name="title" /></p>
				<p>
					<label for="points">Points Value:</label>
					<input type="text" class="inputbox" id="points" name="points" /><br />
					<span class="smltxt">How many points does this warning add? Must be a positive number, or zero.</span></p>
				<p>
					<label for="expires">Days Active:</label>
					<input type="text" class="inputbox" id="expires" name="expires" /><br />
					<span class="smltxt">How many days does this warning remain active?</span></p>
				<p>
					<input class="btnalt" name="submit" type="submit" value="Add Warning Type" /></p>
			</form>';
		}
		// close & return
		$sql->close();
		return $content;
	}
	public function subscriptionPackages(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// List all warning types...
		$q = "SELECT * FROM `subscriptions`";
		$q = $sql->query($q);
		// show inital stuff
		$content = '<p>Subscriptions allow you to sell your users upgrades which will move them to a new user group, with additional features
			not available to your normal users.</p>
			<p><a href="admin.php?act=addsubscriptionpackage">Add Subscription Package</a></p>
		<table id="rounded-corner" width="100%"> 
			<thead> 
				<tr> 
					<th width="85%">Package Name</th> 
					<th width="5%">Price</th> 
					<th width="5%">Duration</th> 
					<th width="5%">Actions</th> 
				</tr> 
			</thead> 
			<tbody>';
		// now, list them
		while($r = $q->fetch_assoc()){
			$content .= '
				<tr>
					<td>'.$r['title'].'</td>
					<td>'.db::$config['currency_sign'].$r['cost'].'</td>
					<td>'.$r['duration'].'</td>
					<td style="text-align: center;"> 
						<a href="admin.php?act=editsubscriptionpackage&amp;sid='.$r['id'].'" title="Edit this subscription package ('.$r['id'].')">
							<img src="acp_theme/img/icons/icon_edit.png" alt="Edit" /></a> 
						<a href="admin.php?act=deletesubscriptionpackage&amp;sid='.$r['id'].'" title="Delete this subscription package ('.$r['id'].')">
							<img src="acp_theme/img/icons/icon_delete.png" alt="Delete" /></a>
					</td>
				</tr>';
		}
		$content .= '
			</tbody>
		</table>';
		// close & return
		$sql->close();
		return $content;
	}
	public function addSubscriptionPackage(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Did we submit?
		if($_POST['submit']){
			// get values
			$title = $sql->real_escape_string($_POST['title']);
			$description = $sql->real_escape_string($_POST['description']);
			$price = $sql->real_escape_string($_POST['price']);
			$new_group = intval($sql->real_escape_string($_POST['new_group']));
			$duration = intval($sql->real_escape_string($_POST['duration']));
			// Add it
			$q = "INSERT INTO `subscriptions`(title,cost,duration,description,new_group)
				VALUES('$title','$price','$duration','$description',$new_group);";
			$sql->query($q);
			// done
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','A new package type has been added. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// get all groups
			$q = "SELECT id,title FROM `groups`";
			$q = $sql->query($q);
			while($r = $q->fetch_assoc()){
				// add row
				$gr .= '<option value='.$r['id'].'">'.$r['title'].'</option>';
			}
			// Show form
			$content = '
			<form method="post" action="">
				<p>
					<label for="title">Package Title</label>
					<input type="text" class="inputbox" id="title" name="title" /></p>
				<p>
					<label for="price">Price:</label>
					<input type="text" class="inputbox" id="price" name="price" /><br />
					<span class="smltxt">How much does this package cost? Do not enter currency signs.</span></p>
				<p>
					<label for="duration">Days Active:</label>
					<input type="text" class="inputbox" id="duration" name="duration" /><br />
					<span class="smltxt">How many days does this warning remain active?</span></p>
				<p>
					<label for="new_group">Move to Group:</label>
					<select class="inputbox" id="new_group" name="new_group">'.$gr.'</select><br />
					<span class="smltxt">What group should this user move to upon successful purchase?</span></p>
				<p>
					<label for="description">Description:</label>
					<textarea name="description" id="description" rows="4" style="width:100%;"></textarea>
				<p>
					<input class="btnalt" name="submit" type="submit" value="Add Package" /></p>
			</form>';
		}
		// close & return
		$sql->close();
		return $content;
	}
	public function editSubscriptionPackage(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// package
		$sid = intval($_GET['sid']);
		// Get package
		$q = "SELECT * FROM `subscriptions` WHERE id='$sid' LIMIT 1;";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		// no results
		if($q->num_rows == 0){
			exit('No results found');
		}
		// Did we submit?
		if($_POST['submit']){
			// get values
			$title = $sql->real_escape_string($_POST['title']);
			$description = $sql->real_escape_string($_POST['description']);
			$price = $sql->real_escape_string($_POST['price']);
			$new_group = intval($sql->real_escape_string($_POST['new_group']));
			$duration = intval($sql->real_escape_string($_POST['duration']));
			// Update it
			$q = "UPDATE `subscriptions`
				SET title='$title',cost='$price',duration='$duration',description='$description',new_group='$new_group' WHERE id='$sid' LIMIT 1;";
			$sql->query($q);
			// done
			$content = core::errorMessage('blank_info');
			$content = str_replace('{e}','This package has been edited. <a href="javascript:history.go(-2)">Return to the previous page</a>.',$content);
		}else{
			// get all groups
			$gpq = "SELECT id,title FROM `groups`";
			$gpq = $sql->query($gpq);
			while($gpr = $gpq->fetch_assoc()){
				// is this me
				if($gpr['id'] == $r['new_group']){
					$s[$gpr['id']] = ' selected="selected"';
				}
				// add row
				$gr .= '<option value='.$gpr['id'].'"'.$s[$gpr['id']].'>'.$gpr['title'].'</option>';
			}
			// Show form
			$content = '
			<form method="post" action="">
				<p>
					<label for="title">Package Title</label>
					<input type="text" class="inputbox" id="title" name="title" value="'.$r['title'].'" /></p>
				<p>
					<label for="price">Price:</label>
					<input type="text" class="inputbox" id="price" name="price" value="'.$r['cost'].'" /><br />
					<span class="smltxt">How much does this package cost? Do not enter currency signs.</span></p>
				<p>
					<label for="duration">Days Active:</label>
					<input type="text" class="inputbox" id="duration" name="duration" value="'.$r['duration'].'" /><br />
					<span class="smltxt">How many days does this warning remain active?</span></p>
				<p>
					<label for="new_group">Move to Group:</label>
					<select class="inputbox" id="new_group" name="new_group">'.$gr.'</select><br />
					<span class="smltxt">What group should this user move to upon successful purchase?</span></p>
				<p>
					<label for="description">Description:</label>
					<textarea name="description" id="description" rows="4" style="width:100%;">'.$r['description'].'</textarea>
				<p>
					<input class="btnalt" name="submit" type="submit" value="Add Package" /></p>
			</form>';
		}
		// close & return
		$sql->close();
		return $content;
	}
}
?>