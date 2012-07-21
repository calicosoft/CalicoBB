/* Jumps between pages */
function pageJump(currentUrl,currentPage,lastPage,pageVar){
	// first, make sure the last page is not 1
	if(lastPage != 1){
		// Declare variables
		var newPage; // the page we wish to view
		// Prompt user for a page number
		promptMsg = "Please enter a page number to go to. (Between 1 and " + lastPage + ")";
		newPage = prompt(promptMsg,currentPage);
		// Check we don't want to go to the current page
		if(newPage == currentPage){
			alert("You are already on page " + currentPage + "!");
		}else{
			// have we entered a number?
			newPage = parseInt(newPage);
			/*if(!isNaN(newPage)){
				alert("The page number you entered is not a number! Please only enter an integer variable.");
			}else{*/
				// Is the new page greater than the last page?
				if(newPage > lastPage){
					alert("The page number you entered is greater than the last page. You will be taken to page " + lastPage + ".");
				}
				// Now, redirect us
				window.location = currentUrl + pageVar + "=" + newPage;
			//}
		}
	}else{
		alert("Sorry, you cannot go to another page. This topic only has one page to view!");
	}
}

/* Delete Function */
function moderatePost(pid,uid,key,act,pageVar){
	var deleteContent;
	var doWeDelete = false;
	var deleteFunction;
	// is this a deletion?
	if(act == "hard_delete_post"){
		// hard delete
		doWeDelete = confirm("Are you sure you wish to delete this post? Once it has been deleted, it cannot be restored.")
		deleteContent = "This post has been hard deleted, and will no longer be visible."
	}else if(act == "deletepost"){
		// soft delete
		doWeDelete = confirm("Are you sure you wish to delete this post? It will no longer be available to public view, but can be restored.")
		deleteContent = "This post has been soft deleted. Only administrators and those with special permissions will be able to view or restore."
	}else if(act == "hard_delete_topic"){
		// hard delete TOPIC
		doWeDelete = confirm("Are you sure you wish to delete this topic? Once it has been deleted, it cannot be restored.")
	}else{
		doWeDelete = true;
	}
	// did we say yes?
	if(doWeDelete){
		var xmlhttp;
		if(window.XMLHttpRequest){
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}else{
			// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function(){
			if(xmlhttp.readyState==4 && xmlhttp.status==200){
				// set response
				var response
				response = xmlhttp.responseText;
				if(response == 1){
					if(deleteFunction == true){
						var container = document.getElementById("post" + pid);
						container.innerHTML = "<td colspan=\"2\">" + deleteContent + "</td>";
						// hide post
						document.getElementById("post" + pid + "_content").style.display = 'none';
					}
					// soft delete - restore post
					if(act == "restorepost"){
						alert("This post has been restored, as is now available to view for all viewers.");
					}
					// ban user
					if(act == 'banuser'){
						alert("This user has been banned as required!");
					}
					// hard delete topic
					if(act == 'hard_delete_topic'){
						// hide topic
						document.getElementById("topic" + pid).style.display = 'none';
					}
					if(act == 'lock' || act == 'unlock'){
						// inform
						alert("This topic has been " + act + "ed as requested.");
					}
				}else{
					alert(response);
					window.location.href = "index.php?act=moderate&do=" + act + "&inside=0&" + pageVar + "=" + pid + "&uid=" + uid + "&k=" + key;
				}
			}/*else{
				alert("Sorry, this post could not be deleted.")
			}*/
		}
		xmlhttp.open("GET","index.php?act=moderate&do=" + act + "&inside=1&" + pageVar + "=" + pid + "&uid=" + uid + "&k=" + key + "&ok=1&rand=" + Math.random(),true);
		xmlhttp.send();
	}
}

/* Edit Topic Title Function */
function editTopicTitle(tid,title){
	// prompt user for new title
	title = prompt("To change the topic title, enter a new title in the field below.",title);
	// set up the url
	var url = "index.php?act=moderate&do=topictitle&tid=" + tid + "&inside=true&title=" + encodeURIComponent(title);
	var xmlhttp;
	if(window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function(){
		if(xmlhttp.readyState==4 && xmlhttp.status==200){
			// set response
			var response
			response = xmlhttp.responseText;
			if(response == 1){
				// update title
				var container = document.getElementById("topic" + tid + "Title");
				container.innerHTML = title;
			}else{
				alert(response);
				window.location.href = url + "&inside=false";
			}
		}/*else{
			alert("Sorry, this post could not be deleted.")
		}*/
	}
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}

/* Quick Reply Function */
/* Requires additional work before it can be used - any JS developers eager to help? */
function quickReply(tid,fid,subject,message){
	// set up the url
	var url = "index.php";
	var params = "act=do_post&type=reply&tid=" & tid & "&fid=" & fid & "&inside=true";
	var alert;
	var xmlhttp;
	if(window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	// post it
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.onreadystatechange = function() {//Call a function when the state changes.
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			// set response
			alert = xmlhttp.responseText;
			if(alert == 1){
				
			}else{
				alert(alert);
			}
		}
	}
	http.send(params);
}