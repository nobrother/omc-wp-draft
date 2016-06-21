<?php 
use OMC\User\Main as Main;

switch( Main::current_user_page() ){
	case 'user_login':
		include 'login/layout.php';
	break;
	
	case 'user_register':
		include 'register/layout.php';
	break;
}