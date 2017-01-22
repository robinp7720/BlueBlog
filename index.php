<?php
require 'vendor/BlueView/BlueView.php';

require 'simple_html_dom.php';

$config = require('./config.php');


$BlueView = new Blue\View();
$BlueView->setAppPath(__DIR__);

$mysqli = new mysqli("localhost", "root", "root", $config['database']);

$summaries = "";
$currentArticle = "";

$currentPage = "article-list";

if (isset($_GET['article'])) {
    $currentPage = "article";
}

/* create a prepared statement */
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
            "link" => "?article=".$row['row_id']
        ];

        if (isset($_GET['article'])) {
            if ($_GET['article'] == $row['row_id']) {
                $currentArticle .= $BlueView->render([
                    "article" => $article
                ], "partials/article-full");
            }
        }

        $summaries .= $BlueView->render([
            "article" => $article
        ],"partials/article-summary");
    }
    $stmt->close();
}

/* close connection */
$mysqli->close();


echo $BlueView->render([
    "articles" => [
        "summary" => $summaries
    ],
    "article" => $currentArticle
],"views/$currentPage");