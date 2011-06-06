<?php
class Kutu_Lib_ForumBB
{
    function login( $phpbb_user_id ) {
		define('IN_PHPBB', true);
		define('PBB_ROOT_PATH', "D://www/phpBB3");
		global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template;
		$phpEx = "php";
		$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : PBB_ROOT_PATH . '/';
		require_once($phpbb_root_path . 'config.' . $phpEx);
		include($phpbb_root_path . 'common.' . $phpEx);
    	
//		$session_id = $user->session_begin($phpbb_user_id, $user_ip, 0, FALSE, 0);
//		$auth->acl($user->data);
//		$user->setup();
//		
//		if ($session_id) {
//			return $session_id;
//		}
//		else
//		{
//			message_die(CRITICAL_ERROR, "Couldn't start session : login", "", __LINE__, __FILE__);
//		}

		$user->session_begin();
		$auth->acl($user->data);
		$user->setup();
		//Does user have phpBB3 account?
		$sql = 'SELECT user_id
		        FROM ' . USERS_TABLE . "
		        WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($user)) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if (!$row) {
		//Create phpBB3 user
		}
			
		//Signin automaticly for phpBB3
		$user->session_create($row['user_id'], true, true, true);
		return true;		
    }

    
    
}
?>