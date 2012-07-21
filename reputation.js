function reputationVote(pid,rating){
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
			var newRating;
			// Set new rating
			newRating = xmlhttp.responseText;
			// Check our number
			var ratingInt = parseFloat( newRating );
			// Check it's a number we've sent back
			if ( !isNaN( ratingInt ) ){
				// Update Page
				document.getElementById("reputation_" + pid).innerHTML = "<strong>Reputation: </strong>" + newRating;
				document.getElementById("reputation_vote_up_" + pid).style.display = "none";
				document.getElementById("reputation_vote_down_" + pid).style.display = "none";
				// check what colour to make the repBox
				if(newRating < 0){
					document.getElementById("reputation_" + pid).addClassName('rep_negative');
				}else if(newRating > 0){
					document.getElementById("reputation_" + pid).addClassName('rep_positive');
				}else{
					document.getElementById("reputation_" + pid).addClassName('rep_neutral');
				}
			}else{
				// Otherwise, show Err.
				alert(newRating);
				// Hide the buttons
				document.getElementById("reputation_vote_up_" + pid).style.display = "none";
				document.getElementById("reputation_vote_down_" + pid).style.display = "none";
			}
		}//else{
			//alert('Sorry, your reputation could not be added...');
		//}
	}
	xmlhttp.open("GET","index.php?act=reputation&do=vote&inside=true&pid=" + pid + "&rating=" + rating + "&rand=" + Math.random(),true);
	xmlhttp.send();
}