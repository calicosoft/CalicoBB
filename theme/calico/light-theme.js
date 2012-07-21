function checkLogout(logoutUrl){
	// this function checks if the user really wants to logout :)
	// did we?
	if(confirm("Are you sure you wish to logout?")){
		window.location = logoutUrl;
	}else{
		return false;
	}
}