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

function traverseChildren(DomNode $element): string
{
  return implode(array_map(fn($child) => traverse($child), iterator_to_array($element->childNodes)));
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

function traverse(DomNode $element): string {
  global $blockElements;
  global $blockElementsXpath;
  global $xpath;
  
  if(isHidden($element)) {
    return '';
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
      if(!hasTextOrImage($element)) {
        return '';
      }      
      return in_array($element->nodeName, $blockElements)?
          sprintf('<%s style="clear:both">%s</%s>', $element->nodeName, traverseChildren($element), $element->nodeName):
          sprintf('<%s>%s</%s>', $element->nodeName, traverseChildren($element), $element->nodeName);
    case 'html':
    case 'body':
    case 'span':
      return traverseChildren($element);
    case '#text':
      return trim($element->nodeValue)? $element->nodeValue : '';
    case 'a':
      $href = trim($element->getAttribute('href'));
      return $href && strpos($href, '#') !== 0 ?
        sprintf('<a href="%s">%s</a>', $href, traverseChildren($element)) : '';
    case 'div':
      if(!hasTextOrImage($element)) {
        return '';
      }
      if ($xpath->query($blockElementsXpath, $element)->count()) {
        return traverseChildren($element);        
      }
      return sprintf('<hr><div style="margin-bottom:20px">%s</div>',  traverseChildren($element));
    case 'img':
      return sprintf('<img style="float:right" src="%s">', $element->getAttribute('src'));
    default:
      if($text = trim($element->nodeValue)) {
        return  implode(["[$element->nodeName]\n", traverseChildren($element)]);
      }
  }
  return '';
}

$content = traverse($doc->documentElement);
require("../templates/base.php");
