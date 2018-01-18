<?php
namespace Model\Hashtag;
use \Db;
use \PDOException;
/**
 * Hashtag model
 *
 * This file contains every db action regarding the hashtags
 */

/**
 * Attach a hashtag to a post
 * @param pid the post id to which attach the hashtag
 * @param hashtag_name the name of the hashtag to attach
 */
function attach($pid, $hashtag_name) {
	$db = \Db::dbc();
	$sql = "SELECT TAG FROM HASHTAG WHERE TAG=:tag";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':tag' => $hashtag_name,
	)
	);
	$result = $sth->fetch();
	if($result[0] == null){
		$sql = "INSERT INTO HASHTAG (TAG,DATE_P) VALUES (:tag,:date)";
		$sth = $db->prepare($sql);
		$sth->execute(array(
		':tag' => $hashtag_name,
		':date' => date("y.m.d H.i.s"),
		)
		);
	}
	$sql = "INSERT INTO REFERENCE (TAG,IDTWEET) VALUES (:tag,:idtweet)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':tag' => $hashtag_name,
	':idtweet' => $pid,
	)
	);	
}

/**
 * List hashtags
 * @return a list of hashtags names
 */
function list_hashtags() {
	$db = \Db::dbc();
	$sql = "SELECT TAG FROM HASHTAG";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	)
	);
	$result = $sth->fetchAll();

	$return = array();
	foreach($result as $res){
		$return[] = $res[0];	
	}

    return $return;
}

/**
 * List hashtags sorted per popularity (number of posts using each)
 * @param length number of hashtags to get at most
 * @return a list of hashtags
 */
function list_popular_hashtags($length) {
	$db = \Db::dbc();
	$sql = "SELECT TAG, COUNT(TAG) AS cnt FROM `REFERENCE` GROUP BY TAG ORDER BY cnt DESC LIMIT :limit ";
	$sth = $db->prepare($sql);
	$sth->bindValue(':limit', intval($length), \PDO::PARAM_INT);
	$sth->execute();
	$result = $sth->fetchAll();

	$return = array();

	foreach($result as $res){
		$return[] = $res[0];	
	}

    return $return;
}

/**
 * Get posts for a hashtag
 * @param hashtag the hashtag name
 * @return a list of posts objects or null if the hashtag doesn't exist
 */
function get_posts($hashtag_name) {
	$db = \Db::dbc();
	$sql = "SELECT IDTWEET FROM REFERENCE
		WHERE TAG = :hash";
	$sth = $db->prepare($sql);
	$sth->execute(array(
		':hash' => $hashtag_name,
		)
		);
	$result = $sth->fetchAll();

	$posts = array();

	foreach($result as $res){
		$posts[] = \Model\Post\get($res[0]);	
	}

    return $posts;
}

/** Get related hashtags
 * @param hashtag_name the hashtag name
 * @param length the size of the returned list at most
 * @return an array of hashtags names
 */
function get_related_hashtags($hashtag_name, $length) {
	$db = \Db::dbc();
	$sql = "SELECT TAG,IDTWEET FROM REFERENCE
		WHERE IDTWEET IN (SELECT IDTWEET FROM REFERENCE WHERE TAG = :hash) AND TAG NOT IN
		(
    			SELECT TAG FROM REFERENCE WHERE TAG = :hash
		)
		LIMIT :limit";
	$sth = $db->prepare($sql);
	$sth->bindValue(':limit', intval($length), \PDO::PARAM_INT);
	$sth->bindValue(':hash', $hashtag_name);
	$sth->execute();
	$result = $sth->fetchAll();

	$return = array();

	foreach($result as $res){
		$return[] = $res[0];	
	}

    return $return;		
}
