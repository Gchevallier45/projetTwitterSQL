<?php
namespace Model\User;
use \Db;
use \PDOException;

/**
 * User model
 *
 * This file contains every db action regarding the users
 */

/**
 * Get a user in db
 * @param id the id of the user in db
 * @return an object containing the attributes of the user or null if error or the user doesn't exist
 */
function get($id) {
	$db = \Db::dbc();
	$sql = "SELECT IDUSER,USERNAME,NAME,SIGNUP_DATE,EMAIL,PASS FROM UTILISATEUR WHERE IDUSER = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $id
	)
	);
	$result = $sth->fetch();
	if($result[0] == null){
		return null;
	}
	else{
		return (object) array(
		"id" => $result[0],
		"username" => $result[1],
		"name" => $result[2],
		"signup" => $result[3],
		"email" => $result[4],
		"password" => $result[5],
		"avatar" => "" 
		);
	}
}

/**
 * Create a user in db
 * @param username the user's username
 * @param name the user's name
 * @param password the user's password
 * r: parameter was not defined in /home/ubuntu/db-project/model_student/user.php:21
Stack trace:
@param email the user's email
 * @param avatar_path the temporary path to the user's avatar
 * @return the id which was assigned to the created user, null if an error occured
 * @warning this function doesn't check whether a user with a similar username exists
 * @warning this function hashes the password
 */
function create($username, $name, $password, $email, $avatar_path) {
    /**return 1337;*/
	$db = \Db::dbc();
	$sql = "INSERT INTO UTILISATEUR (USERNAME,NAME,SIGNUP_DATE,EMAIL,PASS) VALUES (:username,
	:name, :signup, :email, :password)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':username' => $username,
	':name' => $name,
	':signup' => date("y.m.d H.i.s"),
	':email' => $email,
	':password' => hash_password($password),
	)
	);
	return $db->lastInsertId();
	
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param username the user's username
 * @param name the user's name
 * @param email the user's email
 * @warning this function doesn't check whether a user with a similar username exists
 */
function modify($uid, $username, $name, $email) {
	$db = \Db::dbc();
	$sql = "UPDATE UTILISATEUR SET USERNAME = :username, NAME = :name, EMAIL = :email WHERE IDUSER = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':username' => $username,
	':name' => $name,
	':email' => $email,
	':id' => $uid,
	)
	);	
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param new_password the new password
 * @warning this function hashes the password
 */
function change_password($uid, $new_password) {
	$db = \Db::dbc();
	$sql = "UPDATE UTILISATEUR SET PASS = :pass WHERE IDUSER = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':pass' => hash_password($new_password),
	':id' => $uid,
	)
	);	
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param avatar_path the temporary path to the user's avatar
 */
function change_avatar($uid, $avatar_path) {
	// No avatar in db
}

/**
 * Delete a user in db
 * @param id the id of the user to delete
 * @return true if the user has been correctly deleted, false else
 */
function destroy($id) {
	$db = \Db::dbc();
	$sql = "DELETE FROM UTILISATEUR WHERE IDUSER = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $id,
	)
	);
}

/**
 * Hash a user password
 * @param password the clear password to hash
 * @return the hashed password
 */
function hash_password($password) {
	return md5($password);
}

/**
 * Search a user
 * @param string the string to search in the name or username
 * @return an array of find objects
 */
function search($string) {
	$userarray = list_all();
	$searcharray = array();
	foreach($userarray as $line){
		if(stristr($line->username, $string) == TRUE or stristr($line->name, $string) == TRUE) {  			
			$searcharray[] = $line;
  		}
	}
	return $searcharray;
}

/**
 * List users
 * @return an array of the objects of every users
 */
function list_all() {
	$db = \Db::dbc();
	$sql = "SELECT max(IDUSER) FROM UTILISATEUR";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	)
	);
	$count = $sth->fetch();
	$userarray = array();
	for($i=1;$i<=$count[0];$i++){
		$result = get($i);
		if($result != null){
			$userarray[] = $result;
		}		
	}
	return $userarray;
}

/**
 * Get a user from its username
 * @param username the searched user's username
 * @return the user object or null if the user doesn't exist
 */
function get_by_username($username) {
	$db = \Db::dbc();
	$sql = "SELECT IDUSER,USERNAME,NAME,SIGNUP_DATE,EMAIL,PASS FROM UTILISATEUR WHERE USERNAME = :username";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':username' => $username
	)
	);
	$result = $sth->fetch();
	if($result[1] != null){ 
		return (object) array(
		"id" => $result[0],
		"username" => $result[1],
		"name" => $result[2],
		"signup" => $result[3],
		"email" => $result[4],
		"password" => $result[5],
		"avatar" => "" 
		);
	}
	else{
		return null;	
	}
}

/**
 * Get a user's followers
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followers($uid) {
	$db = \Db::dbc();
	$sql = "SELECT IDUSER FROM SUIVRE WHERE IDUSER_1 = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $uid
	)
	);
	$followerarray = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		$followerarray[] = get($line[0]);
	}
    return $followerarray;
}

/**
 * Get the users our user is following
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followings($uid) {
	$db = \Db::dbc();
	$sql = "SELECT IDUSER_1 FROM SUIVRE WHERE IDUSER = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $uid
	)
	);
	$followingarray = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		$followingarray[] = get($line[0]);
	}
    return $followingarray;
}

/**
 * Get a user's stats
 * @param uid the user's id
 * @return an object which describes the stats
 */
function get_stats($uid) {
	$db = \Db::dbc();
	$sql = "SELECT Count(IDTWEET) FROM TWEET WHERE IDUSER = :uid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
		':uid' => $id
	)
	$nb_posts = fetch()
    return (object) array(
        "nb_posts" => 10,
        "nb_followers" => count(get_followers($uid)),
        "nb_following" => count(get_followings($uid))
    );
}

/**
 * Verify the user authentification
 * @param username the user's username
 * @param password the user's password
 * @return the user object or null if authentification failed
 * @warning this function must perform the password hashing   
 */
function check_auth($username, $password) {
	$user = get_by_username($username);	
	if(hash_password($password) == $user->password){
		return $user;
	}
	else if($password == $user->password){
		return $user;
	}
	return null;
}

/**
 * Verify the user authentification based on id
 * @param id the user's id
 * @param password the user's password (already hashed)
 * @return the user object or null if authentification failed
 */
function check_auth_id($id, $password) {
	$db = \Db::dbc();
	$sql = "SELECT USERNAME FROM UTILISATEUR WHERE IDUSER = :uid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':uid' => $id
	)
	);
	$result = $sth->fetch(); 	
	return check_auth($result[0],$password);
}

/**
 * Follow another user
 * @param id the current user's id
 * @param id_to_follow the user's id to follow
 */
function follow($id, $id_to_follow) {
	$db = \Db::dbc();
	$sql = "INSERT INTO SUIVRE (IDUSER,IDUSER_1,DATE_SUIVI) VALUES (:id,
	:id1,:date)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $id,
	':id1' => $id_to_follow,
	':date' => date("y.m.d H.i.s"),
	)
	);
}

/**
 * Unfollow a user
 * @param id the current user's id
 * @param id_to_follow the user's id to unfollow
 */
function unfollow($id, $id_to_unfollow) {
	$db = \Db::dbc();
	$sql = "DELETE FROM SUIVRE WHERE IDUSER = :id AND IDUSER_1 = :id1";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $id,
	':id1' => $id_to_unfollow,
	)
	);
}

