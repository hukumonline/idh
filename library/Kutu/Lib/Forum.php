<?php

/**

PHPBB Forum manipulation Class

Idea By Felix Manea (felix.manea@gmail.com)

Licensed under LGPL

NOTE: You are required to leave this header intact.



Minor changes & (a lot of) bug fixes : Dawid Makowski http://neart.pl/ dawid@neart.pl

*/

class Kutu_Lib_Forum {

	//various table fields

	public $table_fields = array();



	//constructor
	
	// public function __construct($path, $php_extension = "php"){ // original constructor
	
	
	
	// $params = array(); // NOT NULL!
	
	// $params['phpbb3_path'] = '??';
	
	// $params['php_scripts_extensions'] = 'php';



	// public function __construct($params){
	public function __construct(){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		define('IN_PHPBB', true);
		
		// $phpbb_root_path = $params['phpbb3_path'];
		$phpbb_root_path = "D://www/phpBB3/";
		
		// $phpEx = $params['php_scripts_extensions'];
		$phpEx = 'php';

	}



	//initialize phpbb

	public function init($prepare_for_login = false){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		if($prepare_for_login && !defined("IN_LOGIN")) define("IN_LOGIN", true);
		
		require_once($phpbb_root_path.'common.'.$phpEx);
		
		include_once($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
		
		
		//session management
		
		$user->session_begin();
		
		$auth->acl($user->data);

	}



	//user_login

	public function user_login($phpbb_vars) {

//		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
//		
//		//prezumtia de fail
//		
//		$phpbb_result = 'FAIL';
//		
//		
//		
//		//general info
//		
//		$this->init(true);
//		
//		
//		
//		if(!isset($phpbb_vars['autologin'])) $phpbb_vars['autologin'] = false;
//		
//		if(!isset($phpbb_vars['viewonline'])) $phpbb_vars['viewonline'] = 1;
//		
//		if(!isset($phpbb_vars['admin'])) $phpbb_vars['admin'] = 0;
//		
//		
//		
//		//validate and authenticate
//		
//		$validation = login_db($phpbb_vars['username'], $phpbb_vars['password']);
//		
//		
//		
//		if(
//		
//			$validation['status'] == 3
//		
//			&& $auth->login(
//			
//				$phpbb_vars['username'],
//				
//				$phpbb_vars['password'],
//				
//				$phpbb_vars['autologin'],
//				
//				$phpbb_vars['viewonline'],
//				
//				$phpbb_vars['admin']
//			
//			)
//		
//		) $phpbb_result = 'SUCCESS';
//		
//		
//		
//		return $phpbb_result;
			
		/*
		 * Ver.0.2
		 */
		
		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template, $_SID;
		//prezumtia de fail
		$phpbb_result = "FAIL";
		//general info
		$this->init(true);
		$user->setup();
		
		if($user->data['is_registered']) {
		return;
		}
		if(!isset($phpbb_vars["autologin"])) $phpbb_vars["autologin"] = true;
		if(!isset($phpbb_vars["viewonline"])) $phpbb_vars["viewonline"] = 1;
		if(!isset($phpbb_vars["admin"])) $phpbb_vars["admin"] = 0;
		
		//validate and authenticate
		$validation = login_db($phpbb_vars["username"], $phpbb_vars["password"]);
		$login = $auth->login($phpbb_vars["username"], $phpbb_vars["password"], $phpbb_vars["autologin"], $phpbb_vars["viewonline"], $phpbb_vars["admin"]);
		if($validation['status'] == LOGIN_SUCCESS && $login['status'] == LOGIN_SUCCESS) {
		$phpbb_result = "SUCCESS";
		}
		$_SESSION['sid'] = $_SID;
		return $phpbb_result;
		
	}



	//user_logout

	public function user_logout(){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;

		//prezumtia de fail
		
		$phpbb_result = "FAIL";
		
		
		
		//general info
		
		$this->init(true);
		
		
		
		//session management
		
		$user->session_begin();
		
		$auth->acl($user->data);
		
		
		
		//destroy session if needed
		
		if($user->data['user_id'] != ANONYMOUS){
		
			$user->session_kill();
			
			$user->session_begin();
			
			$phpbb_result = "SUCCESS";

		}



		return $phpbb_result;

	}



	//user_loggedin

	public function user_loggedin() {

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//fail presumtion
		
		$phpbb_result = "FAIL";
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//session management
		
		$user->session_begin();
		
		if(is_array($user->data) && $user->data["user_id"] != ANONYMOUS && $user->data["user_id"] > 0) $phpbb_result = "SUCCESS";
		
		//if(is_array($user->data) && isset($user->data["user_id"]) && $user->data["user_id"] > 0) $phpbb_result = "SUCCESS";
		
		
		
		return $phpbb_result;

	}



	public function get_user_data() {

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//session management
		
		$user->session_begin();
		
		
		
		return $user->data;

	}


	public function get_avatar_real_filename($user_avatar) {

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		$this->init(false);
		
		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		
		
		
		$rv = null;
		
		if ( isset($user_avatar) && strlen($user_avatar) ) {
		
			$rv = get_avatar_filename($user_avatar);
		
		}
		
		return $rv;

	}



	public function avatar_upload() {

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		$this->init(false);
		
		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		
		$error = null;
		
		avatar_process_user($error);
		
		return $error;

	}



	//user_add
	
	public function user_add($phpbb_vars){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//fail presumtion
		
		$phpbb_result = "FAIL";
		
		
		
		//if the mandatory parameters are not given fail
		
		if(@empty($phpbb_vars['username']) || !isset($phpbb_vars['group_id']) || !isset($phpbb_vars['user_email']) )
		
		return $phpbb_result;
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//user functions
		
		require_once($phpbb_root_path ."includes/functions_user.".$phpEx);
		
		
		
		//default user info
		
		$user_row = array(
		
			"username" => $phpbb_vars["username"],
			
			"user_password" => $phpbb_vars["password"],
			
			"user_email" => $phpbb_vars["user_email"],
			
			"group_id" => !isset($phpbb_vars["group_id"])?"2":$phpbb_vars["group_id"],
			
			"user_timezone" => "1.00",
			
			"user_dst" => 0,
			
			"user_lang" => "pl",
			
			"user_type" => !isset($phpbb_vars["user_type"])?"0":$phpbb_vars["user_type"],
			
			"user_actkey" => "",
			
			"user_dateformat" => "D M d, Y g:i a",
			
			"user_style" => "1",
			
			"user_regdate" => time(),
			
			"user_colour" => "",
		
		);
		
		
		
		//replace default values with the ones in phpbb_vars array (not yet tested / implemented)
		
		//foreach($user_row as $key => $value) if(isset($phpbb_vars[$key])) $user_row[$key] = $phpbb_vars[$key];
		
		
		
		//register user
		
		if($phpbb_user_id = user_add($user_row)) $phpbb_result = "SUCCESS";
		
		
		
		//update the rest of the fields
		
		$this->user_update($phpbb_vars);
		
		
		
		return $phpbb_result;

	}



	//user_delete
	
	public function user_delete($phpbb_vars){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//fail presumtion
		
		$phpbb_result = "FAIL";
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//user functions
		
		require_once($phpbb_root_path ."includes/functions_user.".$phpEx);
		
		
		
		//get user_id if possible
		
		if(!isset($phpbb_vars["user_id"]))
		
		if(!$phpbb_vars["user_id"] = $this->get_user_id_from_name($phpbb_vars["username"]))
		
		return $phpbb_result;
		
		
		
		//delete user (always returns false)
		
		user_delete("remove", $phpbb_vars["user_id"]);
		
		$phpbb_result = "SUCCESS";
		
		
		
		return $phpbb_result;

	}



	//user_update
	
	public function user_update($phpbb_vars, $old_name) {

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//fail presumtion
		
		$phpbb_result = "FAIL";
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//user functions
		
		require_once($phpbb_root_path ."includes/functions_user.".$phpEx);
		
		
		
		//get user_id if possible
		
		// w produkcjach Hanza/Neart Email jest niezmienny
		
		
		
		if(!isset($phpbb_vars["user_id"]))
		
		if(!$phpbb_vars["user_id"] = $this->get_user_id_from_name($phpbb_vars["username"]))
		
		return $phpbb_result;
		
		
		
		$this->get_table_fields(USERS_TABLE);
		
		
		
		$phpbb_vars['username_clean'] = utf8_clean_string($phpbb_vars['username']);
		
		$ignore_fields = array("user_id");
		
		//if(isset($phpbb_vars["user_password"])) $phpbb_vars["user_password"] = md5($phpbb_vars["user_password"]);
		
		if(isset($phpbb_vars["user_newpasswd"])) $phpbb_vars["user_newpasswd"] = md5($phpbb_vars["user_newpasswd"]);
		
		$sql = "";
		
		//generate sql
		
		for($i = 0;$i < count($this->table_fields[USERS_TABLE]); $i++)
		
		if(isset($phpbb_vars[$this->table_fields[USERS_TABLE][$i]]) && !in_array($this->table_fields[USERS_TABLE][$i], $ignore_fields))
		
		$sql .= ", ".$this->table_fields[USERS_TABLE][$i]." = '".$db->sql_escape($phpbb_vars[$this->table_fields[USERS_TABLE][$i]])."'";
		
		
		
		if(strlen($sql) != 0){
		
			$db->sql_query("UPDATE ".USERS_TABLE." SET ".substr($sql, 2)." WHERE user_id = '".$phpbb_vars["user_id"]."'");
			
			$phpbb_result = "SUCCESS";

		}



		user_update_name($old_name, $phpbb_vars["username"]);
		
		update_last_username();



		return $phpbb_result;

	}



	//user_change_password
	
	public function user_change_password($phpbb_vars){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//fail presumtion
		
		$phpbb_result = "FAIL";
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//user functions
		
		require_once($phpbb_root_path ."includes/functions_user.".$phpEx);
		
		
		
		//get user_id if possible
		
		if(!isset($phpbb_vars["user_id"]))
		
		if(!$phpbb_vars["user_id"] = $this->get_user_id_from_name($phpbb_vars["username"]))
		
		return $phpbb_result;
		
		
		
		$db->sql_query("UPDATE ".USERS_TABLE." SET user_password = '".md5($phpbb_vars["password"])."' WHERE user_id = '".$phpbb_vars["user_id"]."'");
		
		$phpbb_result = "SUCCESS";
		
		
		
		return $phpbb_result;

	}



	private function get_table_fields($table){

		//if already got table fields once
		
		if(isset($this->table_fields[$table])) return true;
		
		
		
		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		
		
		//general info
		
		$this->init(false);
		
		
		
		//get table fields
		
		$this->table_fields[$table] = array();
		
		/* CODE for MYSQL:
		
		* $sql = "SHOW FIELDS FROM ".$table;
		
		* if(!$result = $db->sql_query($sql)) return false;
		
		while($row = $db->sql_fetchrow($result)) $this->table_fields[$table][] = $row["Field"];
		
		*/
		
		
		
		$sql = 'SELECT column_name ' .
		
		'FROM information_schema.columns ' .
		
		'WHERE table_name =\'' . $table . '\'';
		
		if(!$result = $db->sql_query($sql)) return false;
		
		while($row = $db->sql_fetchrow($result)) $this->table_fields[$table][] = $row["column_name"];
		
		$db->sql_freeresult($result);
		
		
		
		return true;

	}



	//get user id if we know username
	
	public function get_user_id_from_name($username){

		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		
		
		//user functions
		
		require_once($phpbb_root_path ."includes/functions_user.".$phpEx);
		
		
		
		$user_id = false;
		
		if(!isset($username)) return false;
		
		user_get_id_name($user_id, $username);
		
		if(!isset($user_id[0])) return false;
		
		return $user_id[0];

	}



	public function get_recent_topics($topics_count = 10) {

		$rv = array();
		
		global $phpbb_root_path, $phpEx, $db, $config, $user, $auth, $cache, $template;
		
		//general info
		
		$this->init(false);
		
		$sql = 'SELECT * FROM phpbb_topics ORDER BY topic_time DESC LIMIT ' . $topics_count;
		
		if(!$result = $db->sql_query($sql)) return false;
		
		while($row = $db->sql_fetchrow($result)) $rv[] = $row;
		
		$db->sql_freeresult($result);
		
		
		
		return $rv;

	}


}