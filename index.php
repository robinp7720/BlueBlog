<?php
session_start();

require 'vendor/BlueView/BlueView.php';
require 'simple_html_dom.php';

$config = require './config.php';

$BlueView = new Blue\View(__DIR__);

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($config['accounts'][$_POST['username']]['password'] == $_POST['password']) {
        $_SESSION['username'] = $_POST['username'];
    }
}

$mysqli = new mysqli("localhost", "root", "root", $config['database']);

$summaries = "";
$currentArticle = "";

$currentPage = "article-list";

$extra = "";
$editUrl = "";

if (isset($_SESSION['username']) && isset($_GET['save'])) {
    var_dump($_POST);
    if ($stmt = $mysqli->prepare("UPDATE `articles` SET `content` = ? WHERE `articles`.`row_id` = ? ")) {
        $stmt->bind_param('si', $_POST['data'],$_GET['article']);
        $stmt->execute();
        $result = $stmt->get_result();
    }
} else {

    if (isset($_GET['edit']) && isset($_SESSION['username'])) {
        $extra = "contenteditable=\"true\"";
    }

    if ($stmt = $mysqli->prepare("SELECT * FROM articles ORDER BY post_time DESC ")) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $html = str_get_html($row['content']);
            $article = [
                "title" => $row['title'],
                "date" => $row['post_time'],
                "full" => $row['content'],
                "author" => $row['author'],
                "tags" => $row['tags'],
                "summary" => $html->find('p', 0)->innertext,
                "category" => $row['category'],
                "link" => "?article=" . $row['row_id'],
                "content" => [
                    "extra" => $extra
                ],
                "edit" => "?article={$row['row_id']}&edit"
            ];

            if (isset($_GET['article'])) {
                $currentPage = "article";
                if ($_GET['article'] == $row['row_id'])
                    $currentArticle .= $BlueView->render(["article" => $article], "partials/article-full");
            }

            $summaries .= $BlueView->render(["article" => $article], "partials/article-summary");
        }
        $stmt->close();
    }

    /* close connection */
    $mysqli->close();

    if (isset($_GET['edit'])) {
        if (!isset($_SESSION['username'])) {
            echo $BlueView->render([], "admin/login");
        } else {
            echo $BlueView->render([
                "articles" => $summaries,
                "article" => $currentArticle
            ], "admin/editor");
        }
    } else {
        echo $BlueView->render([
            "articles" => $summaries,
            "article" => $currentArticle
        ], "views/$currentPage");
    }
}