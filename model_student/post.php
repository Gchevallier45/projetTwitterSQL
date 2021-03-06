<?php
namespace Model\Post;
use \Db;
use \PDOException;
/**
 * Post
 *
 * This file contains every db action regarding the posts
 */

/**
 * Get a post in db
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 */
function get($id) {
	$db = \Db::dbc();
	$sql = "SELECT IDTWEET,IDUSER,DATE_P,TEXTE,IDTWEET_REPONDRE FROM TWEET WHERE IDTWEET = :id";
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
		"text" => $result[3],
		"date" => new \DateTime($result[2]),
		"author" => \Model\User\get($result[1]),
		"responds_to" => get($result[4]),
	    );
	}
}

/**
 * Get a post with its likes, responses, the hashtags used and the post it was the response of
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the likes attribute is an array of users objects
 * @warning the hashtags attribute is an of hashtags objects
 * @warning the responds_to attribute is either null (if the post is not a response) or a post object
 */
function get_with_joins($id) {
	$post = get($id);
	if($post == null){
		return false;
	}
	else{	
		return (object) array(
			"id" => $post->id,
			"text" => $post->text,
			"date" => $post->date,
			"author" => $post->author,
			"likes" => get_likes($id),
			"hashtags" => \Model\Post\extract_hashtags($post->text),
			"responds_to" => $post->responds_to,
		);
	}
}
 
/**
 * Create a post in db
 * @param author_id the author user's id
 * @param text the message
 * @param mentioned_authors the array of ids of users who are mentioned in the post
 * @param response_to the id of the post which the creating post responds to
 * @return the id which was assigned to the created post, null if anything got wrong
 * @warning this function computes the date
 * @warning this function adds the mentions (after checking the users' existence)
 * @warning this function adds the hashtags
 * @warning this function takes care to rollback if one of the queries comes to fail.
 */
function create($author_id, $text, $response_to=null) {
	$db = \Db::dbc();
	$sql = "INSERT INTO TWEET (IDUSER,IDTWEET_REPONDRE,DATE_P,TEXTE) VALUES (:user,:responseto,:date,:text)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':user' => $author_id,
	':responseto' => $response_to,
	':date' => date("y.m.d H.i.s"),
	':text' => $text,
	)
	);
	$postId = $db->lastInsertId();

	$hashtagMatches = \Model\Post\extract_hashtags($text);
	foreach($hashtagMatches as $match){
		\Model\Hashtag\attach($postId,$match);
	}

	$mentionMatches = \Model\Post\extract_mentions($text);
	foreach($mentionMatches as $match){
		$user = \Model\User\get_by_username($match);
		if($user != null){
			mention_user($postId,$user->id);
		}
	}	

	return $postId;
}

/**
 * Mention a user in a post
 * @param pid the post id
 * @param uid the user id to mention
 */
function mention_user($pid, $uid) {
	$db = \Db::dbc();

	$sql = "INSERT INTO MENTIONNER (IDUSER,IDTWEET,DATE_MENTION) VALUES (:user,:post,:date)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':user' => $uid,
	':post' => $pid,
	':date' => date("y.m.d H.i.s"),
	)
	);

}

/**
 * Get mentioned user in post
 * @param pid the post id
 * @return the array of user objects mentioned
 */
function get_mentioned($pid) {
	$db = \Db::dbc();	
	$sql = "SELECT IDUSER FROM MENTIONNER WHERE IDTWEET = :post";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':post' => $pid,
	)
	);
	
	$result = $sth->fetchAll();
	$userFull = array();
	foreach ($result as $line){
		$userFull [] = \Model\User\get($line[0]);
	}

	return $userFull;
}

/**
 * Delete a post in db
 * @param id the id of the post to delete
 */
function destroy($id) {
	$db = \Db::dbc();
	$sql = "DELETE FROM TWEET WHERE IDTWEET = :id";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':id' => $id,
	)
	);	
}

/**
 * Search for posts
 * @param string the string to search in the text
 * @return an array of find objects
 */
function search($string) {
	$db = \Db::dbc();
	$sql = "SELECT IDTWEET FROM TWEET WHERE TEXTE like :string"; 
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':string' => "%".$string."%",
	)
	);	
	$result = $sth->fetchAll();
	$tweetArray = array();
	foreach ($result as $line){
		$tweetArray [] = get($line[0]);
	}
	if ($sth == null){
		return null;
	}else{
		return $tweetArray;
    	}
}

/**
 * List posts
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return an array of the objects of each post
 */
function list_all($date_sorted=false) {
	$db = \Db::dbc();

	if($date_sorted == "DESC"){
		$sql = "SELECT IDTWEET FROM TWEET ORDER BY DATE_P DESC"; 
		$sth = $db->prepare($sql);
		$sth->execute(array(	
		)
		);
	}
	else if($date_sorted == "ASC"){
		$sql = "SELECT IDTWEET FROM TWEET ORDER BY DATE_P ASC"; 
		$sth = $db->prepare($sql);
		$sth->execute(array(	
		)
		);		
	}
	else{
		$sql = "SELECT IDTWEET FROM TWEET";
		$sth = $db->prepare($sql);
		$sth->execute(array(	
		)
		);
	}

	$postArray = array();
	$results = $sth->fetchall();
	foreach($results as $line){
		$postArray [] = get($line[0]);
	}

    	return $postArray;
}

/**
 * Get a user's posts
 * @param id the user's id
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return the list of posts objects
 */
function list_user_posts($id, $date_sorted="DESC") {
	$db = \Db::dbc();

	if($date_sorted == "DESC"){
		$sql = "SELECT IDTWEET FROM TWEET WHERE IDUSER = :uid ORDER BY DATE_P DESC"; 
		$sth = $db->prepare($sql);
		$sth->execute(array(
			':uid' => $id,	
		)
		);
	}
	else if($date_sorted == "ASC"){
		$sql = "SELECT IDTWEET FROM TWEET WHERE IDUSER = :uid ORDER BY DATE_P ASC"; 
		$sth = $db->prepare($sql);
		$sth->execute(array(
			':uid' => $id,		
		)
		);		
	}
	else{
		$sql = "SELECT IDTWEET FROM TWEET WHERE IDUSER = :uid";
		$sth = $db->prepare($sql);
		$sth->execute(array(	
			':uid' => $id,	
		)
		);
	}

	$postArray = array();
	$results = $sth->fetchall();
	foreach($results as $line){
		$postArray [] = get($line[0]);
	}

    	return $postArray;
}

/**
 * Get a post's likes
 * @param pid the post's id
 * @return the users objects who liked the post
 */
function get_likes($pid) {
	$db = \Db::dbc();
	$sql = "SELECT IDUSER FROM AIMER WHERE IDTWEET = :pid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':pid' => $pid
	)
	);
	$likesarray = array();
	$result = $sth->fetchAll();
	foreach($result as $line){
		$likesarray[] = \Model\User\get($line[0]);
	}
    return $likesarray;
}

/**
 * Get a post's responses
 * @param pid the post's id
 * @return the posts objects which are a response to the actual post
 */
function get_responses($pid) {
	$db = \Db::dbc();
	$sql = "SELECT IDTWEET FROM TWEET WHERE IDTWEET_REPONDRE = :pid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':pid' => $pid
	)
	);
	$postarray = array();
	$result = $sth->fetchAll();	
	foreach($result as $line){
		$postarray[] = get($line[0]);
	}
    return $postarray;    
}

/**
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {
    return (object) array(
        "nb_likes" => count(get_likes($pid)),
        "nb_responses" => count(get_responses($pid))
    );
}

/**
 * Like a post
 * @param uid the user's id to like the post
 * @param pid the post's id to be liked
 */
function like($uid, $pid) {
	$db = \Db::dbc();

	$sql = "INSERT INTO AIMER (IDTWEET,IDUSER,DATE_LIKE) VALUES (:pid,:uid,:date)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':uid' => $uid,
	':pid' => $pid,
	':date' => date("y.m.d H.i.s"),
	)
	);	
}

/**
 * Unlike a post
 * @param uid the user's id to unlike the post
 * @param pid the post's id to be unliked
 */
function unlike($uid, $pid) {
	$db = \Db::dbc();
	$sql = "DELETE FROM AIMER WHERE IDUSER = :uid AND IDTWEET = :pid";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':uid' => $uid,
	':pid' => $pid,
	)
	);
}

