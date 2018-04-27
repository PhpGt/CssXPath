<?php

namespace Gt\CssXPath\Test;

use PHPUnit\Framework\TestCase;
use Gt\CssXPath\Test\Helper\Helper;
use Gt\CssXPath\Translator;
use Gt\Dom\HTMLDocument;

class IntegrationTest extends TestCase {
	public function testBody() {
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$bodyTranslator = new Translator("body");
		$h1Translator = new Translator("h1");
		$emTranslator = new Translator("p em");
		$allTranslator = new Translator("*");

		$body = $document->xPath($bodyTranslator)->current();
		$h1 = $document->xPath($h1Translator)->current();
		$em = $document->xPath($emTranslator)->current();

		self::assertEquals("body", $body->tagName);
		self::assertEquals("h1", $h1->tagName);
		self::assertEquals("em", $em->tagName);

		$allElements = $document->xPath($allTranslator);
		$allElementsInBody = $document->body->xPath($allTranslator);

		self::assertCount(
			3, // h1, p, em (not body, as body is the referenceNode)
			$allElementsInBody
		);
		self::assertGreaterThan(
			$allElementsInBody->length,
			$allElements->length
		);
	}

	public function testComplexSelector() {
		$selectors = [
			"body>header h1 a" => "",
			"nav.c-menu .selected>a span" => "",
			"#output td[data-cost]" => "",
			"[data-bind:text=something]" => "",
			"[data-bind:text='something']" => "",
		];

		foreach($selectors as $css => $xpathExpected) {
			$translator = new Translator($css);
			self::assertEquals(
				$xpathExpected,
				(string)$translator
			);
		}
	}
}