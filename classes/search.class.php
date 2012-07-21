<?php
class search extends calicobb{
	public function canUseSearch($uid){
		// Connect to Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($uid);
		// Get User info
		$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group info
		$gid = $ur['group'];
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$gid = 5;
		}
		$gq = "SELECT search FROM `groups` WHERE id='$gid'";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		// Can we use search?
		if($gr['search'] == 0){
			return false;
		}else{
			return true;
		}
		// Close
		$sql->close();
	}
	public function search(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Ensure we secure the UID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we search?
		if(search::canUseSearch($uid) == false){
			$content = core::errorMessage('search_disabled');
			return $content;
		}
		// Friendly URL Redirect
		if(db::$config['seo_urls'] == true AND $_SERVER['QUERY_STRING'] == 'act=search'){
			if($_GET['friendly_url_title'] != core::friendlyTitle($tr['title']) OR $_GET['friendly_url_used'] != 1){
				// Put it together
				$new_url = 'search.html';
				// Redirect
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: $new_url");
				exit();
			}
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// First, the search using CalicoBB option...
		$template['file'] = $template['path'].'search_search_form1.html';
		$template['loaded'] = file_get_contents($template['file']);
		// Now, search via Google..
		$template['file2'] = $template['path'].'search_search_formg.html';
		$template['loaded2'] = file_get_contents($template['file2']);
		// Container Replacements
		$container_f = $template['container'];
		$container_f = str_replace('{header_title}','Search Forums via Keyword',$container_f);
		$container_f = str_replace('{content}',$template['loaded'],$container_f);
		$container_g = $template['container'];
		$container_g = str_replace('{header_title}','Search Forums via Google',$container_g);
		$container_g = str_replace('{content}',$template['loaded2'],$container_g);
		// Downloads Search
		if(file_exists('downloads.php')){
			// Content type
			$content_type = '
			<tr>
				<td><label for="content_type">Content Type:</label></td>
				<td><select name="content_type" id="content_type">
						<option value="posts">Forum Posts</option>
						<option value="files">Download Center Files</option>
					</select>
				</td>
			</tr>';
		}else{
			$content_type = '<input name="content_type" type="hidden" value="posts" />';
		}
		// Put it all together....
		$content = '<form action="index.php?act=search&do=searchresults" method="get">'.str_replace('{content_type}',$content_type,$container_f).'</form>
		<form action="index.php?act=search&do=search_google" target="_blank" method="post">'.$container_g.'</form>';
		// Close conn.
		$sql->close();
		// Send er back
		return $content;
	}
	public function searchResults(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Ensure we secure the UID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we search?
		if(search::canUseSearch($uid) == false){
			$content = core::errorMessage('search_disabled');
			return $content;
		}
		// Is the search term set?
		if(!isset($_GET['searchterm'])){
			$content = core::errorMessage('search_keyword_missing');
			return $content;
		}
		// Is it greater than 3 chars?
		if(strlen($_GET['searchterm']) < 3){
			$content = core::errorMessage('search_keyword_small');
			return $content;
		}
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$gid = 5;
		}else{
			// get Group
			$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Get group info
			$gid = $ur['group'];
		}
		// Forums we can view - v2
		$fq = "SELECT f.id
			FROM forums f
			INNER JOIN permissions p ON ( p.fid = f.id ) 
			WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1)
				OR(p.gid = $gid AND viewforum = 1 AND f.visible = 1)
			GROUP BY f.id
			ORDER BY f.position, f.id";
		$fq = $sql->query($fq);
		while($fr = $fq->fetch_assoc()){
			$f_in[] = $fr['id'];
		}
		$f_in = join("','",$f_in);
		// Secure it
		$searchterm = $sql->real_escape_string($_GET['searchterm']);
		$searchterm = trim(str_replace('*','%',$searchterm));
		// Search Limit
		$search_limit = db::$config['search_limit'];
		// Search now...
		$sq = "SELECT * FROM posts WHERE MATCH (content) AGAINST ('+$searchterm' IN BOOLEAN MODE) AND deleted=0 AND fid IN ('$f_in') LIMIT 50";
		$sq = "SELECT * FROM posts WHERE content LIKE '%$searchterm%' AND deleted=0 AND fid IN ('$f_in') LIMIT $search_limit";
		// chose one of the search options from above. the first gives better results.
		$sq = $sql->query($sq);
		// Any results?
		if($sq->num_rows == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['file'] = $template['path'].'search_searchresults_row.html';
		$template['loaded'] = file_get_contents($template['file']);
		// Go through the results...
		while($sr = $sq->fetch_assoc()){
			// Set topic ID
			$tid = $sr['tid'];
			// Get topic info
			$tq = "SELECT * FROM `topics` WHERE id='$tid' AND deleted=0 AND visible=1 LIMIT 1";
			$tq = $sql->query($tq);
			$tr = $tq->fetch_assoc();
			// Parse & Strip
			$postresult = strip_tags(core::parsePost($sr['content']));
			if(strlen($postresult) < 100){
				$postresult = $postresult;
			}else{
				$postresult = substr($postresult,0,100).'&hellip;';
			}
			// Load Template
			$result = $template['loaded'];
			// Replace "on the fly"
			$result = str_replace('{pid}',$sr['id'],$result);
			$result = str_replace('{tid}',$tid,$result);
			$result = str_replace('{subject}',htmlspecialchars($sr['subject']),$result);
			$result = str_replace('{post_content}',$postresult,$result);
			// Join to current results
			$results .= $result;
			$found = $found + 1;
			// Unset results
			unset($result);
		}
		if($found == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}else{
			// Load Global Container
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Set the $content var
			$content = $template['container'];
			// Replacements
			$content = str_replace('{header_title}','Search Results [Criteria: "'.$searchterm.'"]',$content);
			$content = str_replace('{content}',$results,$content);
		}
		// Close SQL
		$sql->close();
		// Return
		return $content;
	}
	public function googleSearch($q){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Ensure we secure the UID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we search?
		if(search::canUseSearch($uid) == false){
			$content = core::errorMessage('search_disabled');
			return $content;
		}
		// Close SQL
		$sql->close();
		// Get domain
		$domain = $_SERVER['SERVER_NAME'];
		// Put the Query together
		$q =  'site:'.$domain.' '.trim($_POST['q']);
		// Redirect to google
		header('Location: http://www.google.com/search?q='.urlencode($q));
		exit();
	}
	public function findUsersPosts($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Ensure we secure the UID
		$cuid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we search?
		if(search::canUseSearch($cuid) == false){
			$content = core::errorMessage('search_disabled');
			return $content;
		}
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$gid = 5;
		}else{
			// get Group
			$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Get group info
			$gid = $ur['group'];
		}
		// Forums we can view - v2
		$fq = "SELECT f.id
			FROM forums f
			INNER JOIN permissions p ON ( p.fid = f.id ) 
			WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1)
				OR(p.gid = $gid AND viewforum = 1 AND f.visible = 1)
			GROUP BY f.id
			ORDER BY f.position, f.id";
		$fq = $sql->query($fq);
		while($fr = $fq->fetch_assoc()){
			$f_in[] = $fr['id'];
		}
		// add to one array
		$f_in = join("','",$f_in);
		// Secure the user we're searching for...
		$uid = intval($sql->real_escape_string($uid));
		// Search Limit
		$search_limit = db::$config['search_limit'];
		// Set the query
		$sq = "SELECT * FROM `posts` WHERE owner='$uid' AND deleted=0 AND fid IN ('$f_in') ORDER BY id DESC LIMIT $search_limit";
		$sq = $sql->query($sq);
		// Any results?
		if($sq->num_rows == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['file'] = $template['path'].'search_searchresults_row.html';
		$template['loaded'] = file_get_contents($template['file']);
		// Go through the results...
		while($sr = $sq->fetch_assoc()){
			// Set topic ID
			$tid = $sr['tid'];
			// Parse & Strip
			$postresult = strip_tags(core::parsePost($sr['content']));
			if(strlen($postresult) < 100){
				$postresult = $postresult;
			}else{
				$postresult = substr($postresult,0,100).'&hellip;';
			}
			// Load Template
			$result = $template['loaded'];
			// Replace "on the fly"
			$result = str_replace('{pid}',$sr['id'],$result);
			$result = str_replace('{tid}',$tid,$result);
			$result = str_replace('{subject}',htmlspecialchars($sr['subject']),$result);
			$result = str_replace('{post_content}',$postresult,$result);
			// Join to current results
			$results .= $result;
			$found = $found + 1;
			// Unset results
			unset($result);
		}
		if($found == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}else{
			// Load Global Container
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Set the $content var
			$content = $template['container'];
			// Replacements
			$content = str_replace('{header_title}','Search Results [Criteria: Posts by '.strip_tags(core::getUsername($uid)).']',$content);
			$content = str_replace('{content}',$results,$content);
		}
		// Close SQL
		$sql->close();
		// Return
		return $content;
	}
	public function getNewPosts(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Ensure we secure the UID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we search?
		if(search::canUseSearch($uid) == false){
			$content = core::errorMessage('search_disabled');
			return $content;
		}
		// Guest group
		if(!isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			$gid = 5;
		}else{
			// get Group
			$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Get group info
			$gid = $ur['group'];
		}
		// Forums we can view - v2
		$fq = "SELECT f.id
			FROM forums f
			INNER JOIN permissions p ON ( p.fid = f.id ) 
			WHERE (p.default_p = 1 AND viewforum = 1 AND f.visible = 1)
				OR(p.gid = $gid AND viewforum = 1 AND f.visible = 1)
			GROUP BY f.id
			ORDER BY f.position, f.id";
		$fq = $sql->query($fq);
		while($fr = $fq->fetch_assoc()){
			$f_in[] = $fr['id'];
		}
		// add to one array
		$f_in = join("','",$f_in);
		// Search Limit
		$search_limit = db::$config['search_limit'];
		// Search now...
		$sq = "SELECT * FROM posts WHERE deleted=0 AND fid IN ('$f_in') ORDER BY id DESC LIMIT $search_limit";
		// chose one of the search options from above. the first gives better results.
		$sq = $sql->query($sq);
		// Any results?
		if($sq->num_rows == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}
		// How many found?
		$found = 0;
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['file'] = $template['path'].'search_searchresults_row.html';
		$template['loaded'] = file_get_contents($template['file']);
		// Go through the results...
		while($sr = $sq->fetch_assoc()){
			// Set topic ID
			$tid = $sr['tid'];
			// Parse & Strip
			$postresult = strip_tags(core::parsePost($sr['content']));
			if(strlen($postresult) < 100){
				$postresult = $postresult;
			}else{
				$postresult = substr($postresult,0,100).'&hellip;';
			}
			// Load Template
			$result = $template['loaded'];
			// Replace "on the fly"
			$result = str_replace('{pid}',$sr['id'],$result);
			$result = str_replace('{tid}',$tid,$result);
			$result = str_replace('{subject}',htmlspecialchars($sr['subject']),$result);
			$result = str_replace('{post_content}',$postresult,$result);
			// Join to current results
			$results .= $result;
			$found = $found + 1;
			// Unset results
			unset($result);
		}
		if($found == 0){
			$content = core::errorMessage('search_no_results');
			return $content;
		}else{
			// Load Global Container
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Set the $content var
			$content = $template['container'];
			// Replacements
			$content = str_replace('{header_title}','Search Results [Criteria: New Posts]',$content);
			$content = str_replace('{content}',$results,$content);
		}
		// Close SQL
		$sql->close();
		// Return
		return $content;
	}
}
?>