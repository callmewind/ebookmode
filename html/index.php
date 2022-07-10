<?php 

$blockElements = [ 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'hr', 'div', 'ul', 'ol'];
$blockElementsXpath = ".//".implode("|.//", $blockElements);

$url = $_GET['url'] ?? "https://www.meneame.net";

$doc = new DOMDocument();
$doc->loadHTMLFile($url, LIBXML_NOERROR);
$xpath = new DOMXPath($doc);

function hasTextOrImage(DomNode $element): bool 
{
  global $xpath;
  return strlen(trim($element->nodeValue)) > 2 || $xpath->query(".//img", $element)->count() > 0;
}

function traverseChildren(DomNode $element): void
{
  foreach($element->childNodes as $child) {
        traverse($child);
  }
}

function isHidden(DomNode $element): bool
{
  return 
    $element instanceof DomElement && (
      trim($element->getAttribute("aria-hidden")) === 'true' ||
      stripos($element->getAttribute("class"), 'hidden') !== false ||
      $element->hasAttribute('hidden')
    );
}

function traverse(DomNode $element) {
  global $blockElements;
  global $blockElementsXpath;
  global $xpath;
  if(in_array($element->nodeValue, $blockElements)) {
    echo "\n";
  }

  if(isHidden($element)) {
    return;
  }

  switch($element->nodeName) {
    case 'xml':
    case '#comment':
    case 'script':
    case 'svg':
    case 'iframe':
    case 'object':
    case 'form':
    case 'button':
    case 'input':
    case 'select':
    case 'textarea':
    case 'footer':
    case 'head':
      break;
    case 'article':
    case 'main':
    case 'section':
    case 'sup':
    case 'sub':
    case 'strong':
    case 'time':
    case 'var':
    case 'i':
    case 'b':
    case 'u':
    case 'address':
    case 'blockquote':
    case 'h1':
    case 'h2':
    case 'h3':
    case 'h4':
    case 'h5':
    case 'h6':
    case 'p':
    case 'hr':
    case 'li':
    case 'ol':
    case 'ul':
    case 'article':
      if(hasTextOrImage($element)){
        echo "<$element->nodeName>";
        traverseChildren($element);
        echo "</$element->nodeName>";
      }
      break;
    case 'html':
    case 'body':
    case 'span':
      traverseChildren($element);
      break;
    case '#text':
      if(trim($element->nodeValue)) {
        echo $element->nodeValue;
      }
      break;
    case 'a':
      $href = trim($element->getAttribute('href'));
      if($href && strpos($href, '#') !== 0) {
        echo "<a href='$href'>";
        traverseChildren($element);
        echo "</a>";
      }
      break;
    case 'div':
      if(hasTextOrImage($element)){
        if ($xpath->query($blockElementsXpath, $element)->count()) {
          traverseChildren($element);        
        } else {
          echo "<hr><div style='margin-bottom:20px'>";
          traverseChildren($element);
          echo "</div>";
        }
      }
      break;
    case 'img':
        echo "<img src='".$element->getAttribute('src')."'>";
      break;
    default:
      if($text = trim($element->nodeValue)) {
        echo "[$element->nodeName]\n";
        traverseChildren($element);
      }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ebook Mode</title>
    <style type="text/css">
      body {
        font-family: sans-serif;
        font-size: 18px;
      }
    </style>
  </head>
  <body>
    <?php traverse($doc->documentElement) ?>
    <footer>
      <?= sprintf('Memory used %sKb', memory_get_peak_usage(true)/1024) ?>  
    </footer>
  </body>
</html>