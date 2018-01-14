<?php
namespace Gt\CssXPath;

use DOMNode;

class Translator {
	protected $cssSelector;

	public function __construct(string $cssSelector, DOMNode $referenceNode = null) {
		$this->cssSelector = $cssSelector;
		$this->referenceNode = $referenceNode;
	}

	public function __toString():string {
		return $this->asXPath();
	}

	public function asXPath():string {
		// TODO: Implement.
		return "";
	}
}