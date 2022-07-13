<?php

namespace Ebookmode\Filter;
use parse_url;

class LinkRewriter {
	private const SCHEME = 'scheme';
	private const HOST = 'host';
	private const PORT = 'port';

	private string $baseUrl;
	private string $rootUrl;

	public function __construct(string $baseUrl) {
		$this->baseUrl = rtrim($baseUrl, '/');
		$components = parse_url($baseUrl);
		$this->rootUrl = sprintf(
			"%s://%s%s",
			$components[self::SCHEME],
			$components[self::HOST],
			isset($components[self::PORT])? sprintf(':%s', $components[self::PORT]) : ''
		);
	}

	public function rewrite($url): string
	{
		if(!parse_url($url, PHP_URL_HOST)) {
			if($url && $url[0] === '/') {
				$url = sprintf("%s%s", $this->rootUrl, $url);
			} else {
				$url = sprintf("%s/%s", $this->baseUrl, ltrim($url, '/'));	
			}
			
		}
		return sprintf("/?%s", http_build_query(['url' => $url], '', null, PHP_QUERY_RFC3986));
	}

}