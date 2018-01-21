<?php
namespace Model\Notification;
use \Db;
use \PDOException;
/**
 * Notification model
 *
 * This file contains every db action regarding the notifications
 */

/**
 * Get a liked notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the liked_by attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the reading_date attribute is either a DateTime object or null (if it hasn't been read)
 */
function get_liked_notifications($uid) {
	$db = \Db::dbc();
	//echo "uid:".$uid;
	//$sql = "SELECT A.IDTWEET, A.IDUSER, T.DATE_P FROM AIMER A INNER JOIN TWEET T ON A.IDUSER = T.IDUSER WHERE A.DATE_LU IS NULL AND T.IDUSER = :uid";
	$sql = "SELECT A.IDTWEET, A.IDUSER, A.DATE_LU, T.DATE_P FROM AIMER A INNER JOIN TWEET T ON A.IDTWEET = T.IDTWEET WHERE A.IDTWEET IN (SELECT IDTWEET FROM TWEET WHERE IDUSER = :uid)";
	$sth = $db->prepare($sql);
	/*$sth->bindValue(':uid', intval($uid), \PDO::PARAM_INT);*/
	$sth->execute(array(
	':uid' => $uid
	)
	);
	$likednotifs = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		//echo "found";
		if($line[2]==null){
			$likednotifs[] = (object) array(
						"type" => "liked",
						"post" => \Model\Post\get($line[0]),
						"liked_by" => \Model\User\get($line[1]),
						"date" => new \DateTime($line[3]),
						"reading_date" => null
	    				);
		}
		else{
			$likednotifs[] = (object) array(
						"type" => "liked",
						"post" => \Model\Post\get($line[0]),
						"liked_by" => \Model\User\get($line[1]),
						"date" => new \DateTime($line[3]),
						"reading_date" => new \DateTime($line[2]),
	    				);
		}
		//liked_notification_seen($line[0], $line[1]);
	}
	//echo "\n";
	return $likednotifs;    


	/*return [(object) array(
        "type" => "liked",
        "post" => \Model\Post\get(1),
        "liked_by" => \Model\User\get(3),
        "date" => new \DateTime("NOW"),
        "reading_date" => new \DateTime("NOW")
    )];*/
}

/**
 * Mark a like notification as read (with date of reading)
 * @param pid the post id that has been liked
 * @param uid the user id that has liked the post
 */
function liked_notification_seen($pid, $uid) {
	$db = \Db::dbc();
	$sql = "UPDATE AIMER SET DATE_LU = :date WHERE IDUSER = :uid and IDTWEET = :pid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':date' => date("y.m.d H.i.s"),
	':uid' => $uid,
	':pid' => $pid,
	)
	);
}

/**
 * Get a mentioned notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the mentioned_by attribute is a user object
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_mentioned_notifications($uid) {

	$db = \Db::dbc();
	//echo "uid:".$uid;
	//$sql = "SELECT A.IDTWEET, A.IDUSER, T.DATE_P FROM AIMER A INNER JOIN TWEET T ON A.IDUSER = T.IDUSER WHERE A.DATE_LU IS NULL AND T.IDUSER = :uid";
	$sql = "SELECT A.IDTWEET, A.DATE_LU, T.DATE_P, T.IDUSER AS ISUSRTWEET FROM MENTIONNER A INNER JOIN TWEET T ON A.IDTWEET = T.IDTWEET WHERE A.IDUSER = :uid";
	$sth = $db->prepare($sql);
	/*$sth->bindValue(':uid', intval($uid), \PDO::PARAM_INT);*/
	$sth->execute(array(
	':uid' => $uid
	)
	);
	$mentionednotifs = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		//echo "found";
		if($line[1]==null){
			$mentionednotifs[] = (object) array(
						"type" => "mentioned",
						"post" => \Model\Post\get($line[0]),
						"mentioned_by" => \Model\User\get($line[3]),
						"date" => new \DateTime($line[2]),
						"reading_date" => null
	    				);
		}
		else{
			$mentionednotifs[] = (object) array(
						"type" => "mentioned",
						"post" => \Model\Post\get($line[0]),
						"mentioned_by" => \Model\User\get($line[3]),
						"date" => new \DateTime($line[2]),
						"reading_date" => new \DateTime($line[1]),
	    				);
		}
		mentioned_notification_seen($line[3], $line[0]);
	}
	//echo "\n";
	return $mentionednotifs; 

}

/**
 * Mark a mentioned notification as read (with date of reading)
 * @param uid the user that has been mentioned
 * @param pid the post where the user was mentioned
 */
function mentioned_notification_seen($uid, $pid) {
	$db = \Db::dbc();
	$sql = "UPDATE MENTIONNER SET DATE_LU = :date WHERE IDUSER = :uid and IDTWEET = :pid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':date' => date("y.m.d H.i.s"),
	':uid' => $uid,
	':pid' => $pid,
	)
	);
}

/**
 * Get a followed notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the user attribute is a user object which corresponds to the user following.
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_followed_notifications($uid) {
	$db = \Db::dbc();
	//echo "uid:".$uid;
	//$sql = "SELECT A.IDTWEET, A.IDUSER, T.DATE_P FROM AIMER A INNER JOIN TWEET T ON A.IDUSER = T.IDUSER WHERE A.DATE_LU IS NULL AND T.IDUSER = :uid";
	$sql = "SELECT IDUSER, IDUSER_1, DATE_SUIVI, DATE_LU FROM SUIVRE WHERE IDUSER_1 = :uid";
	$sth = $db->prepare($sql);
	/*$sth->bindValue(':uid', intval($uid), \PDO::PARAM_INT);*/
	$sth->execute(array(
	':uid' => $uid
	)
	);
	$followednotifs = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		//echo "found";
		if($line[3]==null){
			$followednotifs[] = (object) array(
						"type" => "followed",
						"user" => \Model\User\get($line[0]),
						"date" => new \DateTime($line[2]),
						"reading_date" => null
	    				);
		}
		else{
			$followednotifs[] = (object) array(
						"type" => "followed",
						"user" => \Model\User\get($line[0]),
						"date" => new \DateTime($line[2]),
						"reading_date" => new \DateTime($line[3]),
	    				);
		}
		//mentioned_notification_seen($line[0], $line[1]);
	}
	//echo "\n";
	return $followednotifs; 


    /*return [(object) array(
        "type" => "followed",
        "user" => \Model\User\get(1),
        "date" => new \DateTime("NOW"),
        "reading_date" => new \DateTime("NOW")
    )];*/
}

/**
 * Mark a followed notification as read (with date of reading)
 * @param followed_id the user id which has been followed
 * @param follower_id the user id that is following
 */
function followed_notification_seen($followed_id, $follower_id) {
	$db = \Db::dbc();
	$sql = "UPDATE SUIVRE SET DATE_LU = :date WHERE IDUSER = :uidfol and IDUSER_1 = :uidfed";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':date' => date("y.m.d H.i.s"),
	':uidfol' => $follower_id,
	':uidfed' => $followed_id,
	)
	);
}
