<?php
namespace Gt\CssXPath;

class Translator {
	protected $cssSelector;

	public function __construct(string $cssSelector) {
		$this->cssSelector = $cssSelector;
	}

	public function __toString():string {
		return $this->asXPath();
	}

	public function asXPath():string {
		// TODO: Implement.
	}
}