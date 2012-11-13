<?php
function isauthenticated() {
	if(isset($_COOKIE['twitterauth'])) {
		return true;
	}	
	else {
		return false;
	}	
}