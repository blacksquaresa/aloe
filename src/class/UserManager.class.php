<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Text.lib.php');

/**
 * The User Manager class provides a base class for managing the creation, authentication and management of all users on the site. 
 * This class can be extended to provide additional functionality. To do this, also ensure that the extendUsersClass global setting is set to the name of your class.
 * 
 * This class replaces the Users.lib.php library
 * 
 * @package Classes
 * @subpackage Users
 * @since 2.4
 **/
class UserManager{
	
	/**
	 * Get the details of a user from the user ID
	 *
	 * @param int $id The ID of the required user
	 * @return array The details of the user
	 *
	 */
	public function getUserById($id){
		if(empty($id) || !is_numeric($id)) $id = 0;
		$usr = $GLOBALS['db']->select("select * from user u where u.id = $id");
		if($usr && count($usr) >= 1){
			return $usr[0];
		}else{
			return false;
		}	
	}
	
	/**
	 * Get the details of a user from their email address
	 *
	 * @param string $email The email address of the required user
	 * @return array The details of the user
	 *
	 */
	public function getUserByEmail($email){
		$email = mysql_real_escape_string($email);
		$usr = $GLOBALS['db']->select("select * from user u	where u.email = '" . strtolower($email) . "'");
		if($usr && count($usr) >= 1){
			return $usr[0];
		}else{
			return false;
		}	
	}
	
	/**
	 * Get the details of a user from their user name
	 *
	 * @param string $username The user name of the requested user
	 * @return array The details of the user
	 *
	 */
	public function getUserByUsername($username){
		$username = mysql_real_escape_string($username);
		$usr = $GLOBALS['db']->select("select * from user u where u.username = '" . strtolower($username) . "'");
		if($usr && count($usr) >= 1){
			return $usr[0];
		}else{
			return false;
		}	
	}
	
	/**
	 * Get the details of a user from their remember code
	 *
	 * @param string $code The randomly assigned code used to represent this user in cookies
	 * @return array The details of the user
	 *
	 */
	public function getUserByCode($code){
		$code = mysql_real_escape_string($code);
		$usr = $GLOBALS['db']->select("select * from user u where u.remembercode = '" . $code . "' and status <> 'suspended'");
		if($usr && count($usr) >= 1){
			return $usr[0];
		}else{
			return false;
		}	
	}
	
	/**
	 * Get a list of users, filtered with the supplied filters, and within the supplied limits
	 * 
	 * If the first parameter is a PageState object, all other parameters will be ignored. Instead, the parameter values will be taken from the PageState object.
	 *
	 * @param mixed $name A string to compare to the name of the user, or a PageState variable containing all filter data
	 * @param string $email A string to compare to the email address of the user
	 * @param string $username A string to compare to the username of the user
	 * @param mixed $status An array of statuses to include, or a status as a string if there is only one. Null includes all statuses
	 * @param string $sort The ORDER BY string to pass to the query
	 * @param int $start The start position for the returned list, if the $lim parameter is also supplied
	 * @param int $lim The number of results to return
	 * @return array An array of the resulting users
	 *
	 */
	public function getUsers($name=null,$email=null,$username=null,$status=null,$sort=null,$start=0,$lim=0){
		if(strtolower(get_class($name)) == 'pagestate'){
			$email = $name->values['email'];
			$username = $name->values['username'];
			$status = $name->values['status'];
			$sort = $name->values['sort'];
			$start = $name->values['start'];
			$lim = $name->values['lim'];
			$name = $name->values['name'];
		}
		$where = $this->getUserListWhere($name, $username, $email, $status);
		$order = '';
		$limit = '';
		if(!empty($sort)){
			$sort = mysql_real_escape_string(urldecode($sort));
			$order = ' order by ' . $sort;
		}
		if(!empty($lim) && is_numeric($lim)){
			$limit = ' limit ' . (empty($start) || !is_numeric($start)?'':" $start,") . $lim;
		}
		$sql = "select u.* from user u 
				$where$order$limit";
		$list = $GLOBALS['db']->select($sql);
		return $list;	
	}
	
	/**
	 * Count the number of results that would be returned by the getUsers method with the same parameters, without limits
	 * 
	 * If the first parameter is a PageState object, all other parameters will be ignored. Instead, the parameter values will be taken from the PageState object.
	 *
	 * @param mixed $name A string to compare to the name of the user, or a PageState variable containing all filter data
	 * @param string $email A string to compare to the email address of the user
	 * @param string $username A string to compare to the username of the user
	 * @param mixed $status An array of statuses to include, or a status as a string if there is only one. Null includes all statuses
	 * @return int The number of rows that would be returned.
	 *
	 */
	public function countUsers($name=null,$email=null,$username=null,$status=null){
		if(strtolower(get_class($name)) == 'pagestate'){
			$email = $name->values['email'];
			$username = $name->values['username'];
			$status = $name->values['status'];
			$name = $name->values['name'];
		}
		$where = $this->getUserListWhere($name, $username, $email, $status);
		$ret = $GLOBALS['db']->selectsingle("select count(*) from user u $where");
		return $ret;	
	}
	
	/**
	 * Construct a WHERE clause for the getUsers and countUsers methods, given the supplied parameters
	 *
	 * @param string $name A string to compare to the name of the user
	 * @param string $email A string to compare to the email address of the user
	 * @param string $username A string to compare to the username of the user
	 * @param mixed $status An array of statuses to include, or a status as a string if there is only one. Null includes all statuses
	 * @return string The constructed WHERE clause, including the "WHERE" itself, or empty if there should be no filters
	 *
	 */
	public function getUserListWhere($name, $username, $email, $status){
		$where = $GLOBALS['db']->getSearchStringWhere($name,'name','u','','');	
		$where = $GLOBALS['db']->getSearchStringWhere($username,'username','u','',$where);
		$where = $GLOBALS['db']->getSearchStringWhere($email,'email','u','',$where);
		$where = $GLOBALS['db']->getSearchStringArrayWhere($status,'status','u',$where);
		return $where;
	}
	
	/**
	 * Create a new user
	 *
	 * @param string $name The full name of the user
	 * @param string $username The username of the user
	 * @param string $email The email address of the user
	 * @param string $phone The phone number of the user
	 * @param string $password The password to use for this account
	 * @param string $status The status of the new user account
	 * @param mixed $rights The user access rights of the user. Either an integer, or an array of individual rights.
	 * @param string $error A container for any error messages that might be generated by the method
	 * @return int The ID of the new user
	 *
	 */
	public function createUser($name, $username, $email, $phone, $password, $status='active', $rights=0, &$error){
		if(empty($username)){
			$error = 'No username supplied';
			return false;	
		}
		if(!empty($email)) $emailuser = $this->getUserByEmail($email);
		$useruser = $this->getUserByUsername($username);
		if(!empty($emailuser) || !empty($useruser)){
			$error = 'User already exists';
			return false;
		}else{
			$values = array();
			$values['name'] = $name;
			$values['phone'] = $phone;
			$values['email'] = strtolower($email);
			$values['status'] = $status;
			$values['username'] = $username;
			$values['password'] = crypt($password);
			$values['datecreated'] = time();
			$values['remembercode'] = createRandomCode(9,16);
			if(!empty($rights) && $GLOBALS['settings']->useadminrights && $GLOBALS['usermanager']->checkUserRights(RIGHT_ADMADMIN)){
				if(is_array($rights)){
					$rgt = 0;
					foreach($rights as $right){
						$rgt |= $right;	
					}	
					$rights = $rgt;
				}
				$values['rights'] = $rights;
			}
			$id = $GLOBALS['db']->insert('user',$values);	
			if($id===false){
				$error = mysql_error();
			}
			return $id;
		}
	}
	
	/**
	 * Update an existing user
	 *
	 * @param int $id The ID of the user to be updated
	 * @param string $name The name of the user
	 * @param string $email The email address of the user
	 * @param string $phone The phone number of the user
	 * @param string $status The status of the user
	 * @param mixed $rights The user access rights of the user. Either an integer, or an array of individual rights.
	 * @param string $error A container for any error messages that might be generated by this method
	 * @return int The ID of the updated user, or false on error
	 *
	 */
	public function updateUser($id, $name, $email=null, $phone=null, $status=null, $rights=null, &$error){
		
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['name'] = $name;
		if($phone!=null) $values['phone'] = $phone;
		if($email!=null) $values['email'] = strtolower($email);
		if($status!=null) $values['status'] = $status;
		if($rights!==null && $id != ADM_USERID && $GLOBALS['settings']->useadminrights && $GLOBALS['usermanager']->checkUserRights(RIGHT_ADMADMIN)){
			if(is_array($rights)){
				$rgt = 0;
				foreach($rights as $right){
					$rgt |= $right;	
				}	
				$rights = $rgt;
			}
			$values['rights'] = $rights;
		}
		$pks = array();
		$pks['id'] = $id;
		$res = $GLOBALS['db']->update('user',$values,$pks);	
		if($res===false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return $id;	
	}
	
	/**
	 * Delete an existing user
	 * 
	 * NOTE: The global administrator cannot be deleted
	 *
	 * @param int $id The ID of the user to be deleted
	 * @param string $error A container for any error messages that might be generated by this method
	 * @return mixed 1 if the user was deleted, 0 if no user could be found with that ID, or false on error.
	 *
	 */
	public function deleteUser($id, &$error){
		if($id != ADM_USERID){
			$res = $GLOBALS['db']->delete('user',$id);
			if($res === false){
				$error = mysql_error();
				return false;
			}
			return $res;
		}else{
			$error = "Cannot delete the Root Admin account";
			return false;	
		} 
	}
	
	/**
	 * Update the status of a user
	 *
	 * @param int $id The ID of the user
	 * @param string $status The new status
	 * @param string $error A container for any error messages that might be generated by this method
	 * @return mixed The ID of the user if successful, otherwise false
	 *
	 */
	public function updateUserStatus($id, $status, &$error){
		if($id != ADM_USERID){
			$values = array();
			$values['status'] = $status;
			$pks = array();
			$pks['id'] = $id;
			$res = $GLOBALS['db']->update('user',$values,$pks);	
			if($res === false){
				$error = mysql_error();
				return false;
			}
			return $id;
		}else{
			$error = "Cannot change the status of the Root Admin account";
			return false;	
		} 	
	}
	
	/**
	 * Reset a user's password
	 *
	 * @param int $id The ID of the user
	 * @param string $oldpassword The old password. This will be validated.
	 * @param string $newpassword The new password
	 * @param string $error A container for any error messages that might be generated by this method
	 * @return mixed 1 if successful, or false on error
	 *
	 */
	public function resetUserPassword($id, $oldpassword, $newpassword, &$error){
		$usr = $this->getUserById($id);
		if($usr){
			if($this->validatePassword($oldpassword,$usr['password'])){		
				$values = array();
				$values['password'] = crypt($newpassword);
				$pks = array();
				$pks['id'] = $id;
				$res = $GLOBALS['db']->update('user',$values,$pks);
				if($res === false) $error = mysql_error();
				return $res;
			}
		}
		return false;
	}
	
	/**
	 * Reset a user's password with a new random password. 
	 * 
	 * This method is used with the "forgotten password" system. The user has already received an email, and clicked on a link in that 
	 * confirming they want their password reset.
	 *
	 * @param mixed $usr The user whose password need resetting, or the user's ID
	 * @param string $error A container for any error messages that might be generated by this method
	 * @return string The new password
	 *
	 */
	public function resetUserPasswordByCode($usr, &$error){	
		if(!empty($usr)){
			if(is_numeric($usr)) $usr = $this->getUserById($usr);
			$newpassword = createRandomCode(6,8);
			$values = array();
			$values['password'] = crypt($newpassword);
			$values['remembercode'] = createRandomCode(9,16);
			$pks = array();
			$pks['id'] = $usr['id'];
			$res = $GLOBALS['db']->update('user',$values,$pks);
			if($res){
				sendUserNewPassword($usr,$newpassword);
				return $newpassword;
			}else{
				if($res === false) $error = mysql_error();
				return $res;
			}
		}
		return false;
	}
	
	/**
	 * Validate a password
	 *
	 * @param string $test The password being tested
	 * @param string $password The encrypted password from the database
	 * @return bool True if the passwords are the same, false otherwise
	 *
	 */
	public function validatePassword($test,$password){
		return crypt($test,$password) == $password;
	}
	
	/**
	 * Validate a user by username and password
	 *
	 * @param string $username The username
	 * @param string $password The password
	 * @return mixed The user whose credentials match those supplied, or false if none do.
	 *
	 */
	public function validateUser($username, $password){
		$usr = $this->getUserByUsername($username);
		if($this->validatePassword($password,$usr['password'])){
			@$GLOBALS['db']->update('user',array('lastlogin'=>time()),array('id'=>$usr['id']));
			return $usr;
		}
		else return false;	
	}
	
	/**
	 * Check that a valid user is logged in already
	 * 
	 * If no user is found logged in, the requested is sent to the login page. 
	 * If the user is found to be suspended, the request is passed to an error page
	 *
	 * @return bool True if a valid user is already logged in for this session, otherwise false
	 *
	 */
	public function authenticateUser($rights=null){
		session_start();
		$wr = $GLOBALS['webroot'];
		if(empty($_SESSION['user'])){
			$usr = $this->checkRememberCookie();
			if(empty($usr)){
				header('Location: ' . $wr . 'admin/login.php?ret=' . $_SERVER['REQUEST_URI']);
				exit;
			}
			else $_SESSION['user'] = $usr;
		}		
		if($_SESSION['user']['status'] == 'suspended'){
			header('Location: ' . $wr . 'admin/login.php?mes=usrsus');
			exit;					
		}
		if($GLOBALS['settings']->useadminrights && !empty($rights) && !$this->checkUserRights($rights,$_SESSION['user'])){
			header('Location: ' . $GLOBALS['webroot'] . 'admin/index.php?mes=rgtno');
			exit;					
		}	
		return true;
	}
	
	/**
	 * Check that a valid user is logged in already. Fail behaviour is designed for AJAX use.
	 * 
	 * If the user is not logged in for an AJAX call, the user cannot be redirected to another page, but a simple message should be returned instead.
	 * 
	 * If no user is found logged in, the requested is sent to the login page. 
	 * If the user is found to be suspended, the request is passed to an error page
	 *
	 * @return bool True if a valid user is already logged in for this session, otherwise false
	 *
	 */
	public function authenticateUserForAjax($rights=null){
		session_start();
		$wr = $GLOBALS['webroot'];
		if(empty($_SESSION['user'])){
			$usr = $this->checkRememberCookie();
			if(empty($usr)){
				echo 'Sorry, you must be logged in to perform this action';
				exit;
			}
			else $_SESSION['user'] = $usr;
		}		
		if($_SESSION['user']['status'] == 'suspended'){
			echo 'Your user account has been suspended.  Please check your email for the reasons for this, or contact <a href="mailto:' . $GLOBALS['settings']->adminemail . '">the system administrator</a> if you feel that this is in error.';
			exit;					
		}
		if($GLOBALS['settings']->useadminrights && !empty($rights) && !$this->checkUserRights($rights,$_SESSION['user'])){
			header('Location: ' . $GLOBALS['webroot'] . 'admin/index.php?mes=rgtno');
			exit;					
		}	
		return true;
	}
	
	/**
	 * Check to see whether or not a user has the required rights
	 *
	 * @param int $right The required right
	 * @param array $usr The user to check against
	 * @return bool True if the user has the required rights, otherwise false
	 *
	 */
	public function checkUserRights($right,$usr=null){
		if(empty($usr)) $usr = $_SESSION['user'];
		$right = intval($right);
		return (($right & (int)$usr['rights']) == $right);
	}
	
	/**
	 * Fetch a list of all available user rights
	 *
	 * @return array A list of all rights, each including a name and bit value
	 *
	 */
	public function getUserRights(){
		return $GLOBALS['db']->select("select * from user_rights order by id");	
	}
	
	/**
	 * Set a cookie to keep the current user logged in past the end of this session
	 * 
	 * The cookie is set for 180 days.
	 * This method uses a remember code, which is a random generated code between 9 and 16 characters.
	 *
	 * @param array $user The user to remember
	 * @return bool True if the cookie was set, otherwise false
	 *
	 */
	public function setRememberCookie($user){
		if(empty($user['remembercode'])){
			$code = createRandomCode(9,16);
			$res = $GLOBALS['db']->update('user',array('remembercode'=>$code),array('id'=>$user['id']));
			if(!$res) return false;
			else $user['remembercode'] = $code;
		}
		return setcookie('userid',$user['remembercode'],time()+60*60*24*180,'/');
	}
	
	/**
	 * Check the existance of a cookie set with setRememberCookie and, if found, log that user in automatically
	 *
	 * @return mixed The user who was logged in, or false
	 *
	 */
	public function checkRememberCookie(){
		if(empty($_COOKIE['userid'])) return false;
		$cookie = mysql_real_escape_string($_COOKIE['userid']);
		
		$usr = $GLOBALS['db']->select("select * from user where remembercode = '$cookie' and status = 'active'");
		if($usr && count($usr) >= 1){
			$usr = $usr[0];
			@$GLOBALS['db']->update('user',array('lastlogin'=>time()),array('id'=>$usr['id']));
			return $usr;
		}else{
			return false;
		}
	}
	
	/**
	 * Update the user array stored in the $_SESSION. Use this when details of the user have been updated.
	 *
	 * @return void 
	 *
	 */
	public function refreshSessionUser(){		
		$usr = $this->getUserById($_SESSION['user']['id']);
		if($usr && count($usr) >= 1){
			$_SESSION['user'] = $usr;
		}else{
			$this->logout();
		}
	}
	
	/**
	 * Log the current user out.
	 * 
	 * All session variables are cleared, and the relevant cookie is expired.
	 *
	 * @return void 
	 *
	 */
	public function logout(){
		foreach(array_keys($_SESSION) as $key){
			unset($_SESSION[$key]);
		}
		setcookie('userid','',time(),'/');
	}
	
	/**
	 * Creates an instance of the UserManager class, or a predefined subclass of it. Set the "extendUsersClass" global variable with the name of the subclass.
	 *
	 * @return UserManager The instance. Also added to the $GLOBALS set.
	 *
	 */
	final public static function getUserManager(){
		if(!empty($GLOBALS['usermanager'])) return $GLOBALS['usermanager'];
		if(!empty($GLOBALS['settings']->extendUsersClass) && class_exists($GLOBALS['settings']->extendUsersClass) && is_subclass_of($GLOBALS['settings']->extendUsersClass,'UserManager')){
			$GLOBALS['usermanager'] = new $GLOBALS['settings']->extendUsersClass();
		}else{
			$GLOBALS['usermanager'] = new UserManager();
		}
		return $GLOBALS['usermanager'];
	}
}
?>