<?php 

use EbookMode\Filter\Filter;

$url = $_GET['url'] ?? "https://www.elperiodico.com";

require_once('../filter/filter.php');

$filter = new Filter(
  file_get_contents(
    $url,
    false,
    stream_context_create([
      'http' => ['user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'EbookMode' ]
    ])
  )
);

$content = $filter();
require("../templates/base.php");
