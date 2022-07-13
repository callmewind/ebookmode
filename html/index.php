<?php 

use EbookMode\Filter\Filter;

$content = 'Enter URL to browse';

if($url = $_GET['url'] ?? '') {
  require_once('../filter/Filter.php');

  $filter = new Filter(
    file_get_contents(
      $url,
      false,
      stream_context_create([
        'http' => ['user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'EbookMode' ]
      ])
    ), $url
  );

  $content = $filter();  
}


require("../templates/base.php");
