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
	$sql = "SELECT IDTWEET,IDUSER,DATE_P,TEXTE FROM TWEET WHERE IDTWEET = :id";
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
		"date" => new \DateTime($result[2]),/*new \DateTime('2011-01-01T15:03:01'),*/
		"author" => \Model\User\get($result[1])
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
    return (object) array(
        "id" => 1337,
        "text" => "Ima writing a post !",
        "date" => new \DateTime('2011-01-01T15:03:01'),
        "author" => \Model\User\get(2),
        "likes" => [],
        "hashtags" => [],
        "responds_to" => null
    );
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
	':date' => date("y.m.d"),
	':text' => $text,
	)
	);
	$postId = $db->lastInsertId();

	$matches = array();
	preg_match_all('/#([^\s]+)/', $text, $matches);	
	foreach($matches[1] as $match){
		//echo $match;
		\Model\Hashtag\attach($postId,$match);
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

	$sql = "INSERT INTO TWEET (ID_USER_MENTIONNER,IDTWEET,NOTIFMENTION) VALUES (:user,:post,:date)";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':user' => $uid,
	':post' => $pid,
	':date' => date("y.m.d"),
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
	
	$sql = "SELECT DISTINCT ID_USER_MENTIONNER FROM MENTIONNER WHERE IDTWEET = :post";
	$sth = $db->prepare($sql);
	$sth->execute(array(
	':post' => $pid,
	)
	);
	
	$user = $sth->fetch();
	$userFull = get($user);

	return $userFull;
}

/**
 * Delete a post in db
 * @param id the id of the post to delete
 */
function destroy($id) {
}

/**
 * Search for posts
 * @param string the string to search in the text
 * @return an array of find objects
 */
function search($string) {
    return [];
}

/**
 * List posts
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return an array of the objects of each post
 */
function list_all($date_sorted=false) {
    return [];
}

/**
 * Get a user's posts
 * @param id the user's id
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return the list of posts objects
 */
function list_user_posts($id, $date_sorted="DESC") {
    return [];
}

/**
 * Get a post's likes
 * @param pid the post's id
 * @return the users objects who liked the post
 */
function get_likes($pid) {
    return [];
}

/**
 * Get a post's responses
 * @param pid the post's id
 * @return the posts objects which are a response to the actual post
 */
function get_responses($pid) {
    return [];
}

/**
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {
    return (object) array(
        "nb_likes" => 10,
        "nb_responses" => 40
    );
}

/**
 * Like a post
 * @param uid the user's id to like the post
 * @param pid the post's id to be liked
 */
function like($uid, $pid) {
}

/**
 * Unlike a post
 * @param uid the user's id to unlike the post
 * @param pid the post's id to be unliked
 */
function unlike($uid, $pid) {
}

