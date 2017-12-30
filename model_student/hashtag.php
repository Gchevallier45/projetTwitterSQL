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
	//echo $hashtag_name."\n";
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
		':date' => date("y.m.d"),
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
    /*return ["Test"];*/
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
    return ["Hallo"];
}

/**
 * Get posts for a hashtag
 * @param hashtag the hashtag name
 * @return a list of posts objects or null if the hashtag doesn't exist
 */
function get_posts($hashtag_name) {
    return [\Model\Post\get(1)];
}

/** Get related hashtags
 * @param hashtag_name the hashtag name
 * @param length the size of the returned list at most
 * @return an array of hashtags names
 */
function get_related_hashtags($hashtag_name, $length) {
    return ["Hello"];
}
