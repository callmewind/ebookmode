<?php 

namespace Ebookmode\Filter;
use DOMDocument;
use DOMXPath;
use DomNode;
use LIBXML_NOERROR;
require('LinkRewriter.php');

class Filter {

	private DOMDocument $doc;
	private DOMXPath $xpath;
	private LinkRewriter $linkRewriter;
	private const IGNORED_ELEMENTS = [
		'xml', '#comment', 'script', 'svg', 'iframe', 'object', 'form',
		'button', 'input', 'select',  'textarea', 'footer', 'head'
	];

	private const BLOCK_ELEMENTS = [ 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'hr', 'div', 'ul', 'ol'];
	
	private $blockElementsXpath;

	public function __construct(string $source, string $baseUrl) {
		$this->doc = new DOMDocument();
		$this->doc->loadHTML($source, LIBXML_NOERROR);
		$this->xpath = new DOMXPath($this->doc);
		$this->blockElementsXpath =  ".//".implode("|.//", self::BLOCK_ELEMENTS);
		$this->linkRewriter = new LinkRewriter($baseUrl);
	}


	public function __invoke(): string
	{
		return $this->traverse($this->doc->documentElement);
	}

	private function hasTextOrImage(DomNode $element): bool 
	{
		return strlen(trim($element->nodeValue)) > 2 || $this->xpath->query(".//img", $element)->count() > 0;
	}

	private function isHidden(DomNode $element): bool
	{
		return 
		    $element instanceof DomElement && (
		      trim($element->getAttribute("aria-hidden")) === 'true' ||
		      stripos($element->getAttribute("class"), 'hidden') !== false ||
		      $element->hasAttribute('hidden')
		    );
	}

	private function traverseChildren(DomNode $element): string
	{
	  return implode(
	  	array_map(
	  		fn($child) => $this->traverse($child), 
	  		iterator_to_array($element->childNodes)
	  	)
	  );
	}

	private function traverse(DomNode $element): string
	{
		if(in_array($element->nodeName, self::IGNORED_ELEMENTS) || $this->isHidden($element)) {
			return '';
		}

		switch($element->nodeName) {
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
				if(!$this->hasTextOrImage($element)) {
					return '';
				}      
				return in_array($element->nodeName, self::BLOCK_ELEMENTS)?
				  sprintf('<%s style="clear:both">%s</%s>', $element->nodeName, $this->traverseChildren($element), $element->nodeName):
				  sprintf('<%s>%s</%s>', $element->nodeName, $this->traverseChildren($element), $element->nodeName);
			case 'html':
			case 'body':
			case 'span':
				return $this->traverseChildren($element);
			case '#text':
				return trim($element->nodeValue)? $element->nodeValue : '';
			case 'a':
				$href = $this->linkRewriter->rewrite(trim($element->getAttribute('href')));
				return $href && strpos($href, '#') !== 0 ?
					sprintf('<a href="%s">%s</a>', $href, $this->traverseChildren($element)) : '';
			case 'div':
				if(!$this->hasTextOrImage($element)) {
					return '';
				}
				if ($this->xpath->query($this->blockElementsXpath, $element)->count()) {
					return $this->traverseChildren($element);        
				}
				return sprintf('<hr><div style="margin-bottom:20px">%s</div>',  $this->traverseChildren($element));
			case 'img':
				return sprintf('<img style="float:right" src="%s">', $element->getAttribute('src'));
			default:
				if($text = trim($element->nodeValue)) {
					return  implode(["[$element->nodeName]\n", $this->traverseChildren($element)]);
				}
				return '';
		}
	}
}
