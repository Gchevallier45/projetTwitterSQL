<?php
namespace Controller\Search;

function search_user($query_txt) {
    try {
        $users = \Model\User\search($query_txt);
    }
    catch(\Exception $e) {
        echo "An error occured :".$e->getMessage();
        exit();
    }
    require "../view/search_user.php";
}

function search_post($query_txt) {
    try {
        $posts = \Model\Post\search($query_txt);
    }
    catch(\Exception $e) {
        echo "An error occured :".$e->getMessage();
        exit();
    }
    require "../view/search_post.php";
}
