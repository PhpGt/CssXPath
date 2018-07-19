<?php

namespace Gt\CssXPath\Test;

use PHPUnit\Framework\TestCase;
use Gt\CssXPath\Test\Helper\Helper;
use Gt\CssXPath\Translator;
use Gt\Dom\HTMLDocument;

class TranslatorTest extends TestCase {
	public function testSimple() {
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

	public function testComplex() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);

		$titleTranslator = new Translator("head>title");
		$logoLinkText = new Translator(".c-logo a>span");
		$selectedNavMenu = new Translator(".c-menu li.selected");
		$articleParagraphs = new Translator("main>article .content p");
		$contactFormEmailInput = new Translator(
			"body>footer form input[name='email']"
		);
		$contactFormEmailInputNoQuotes = new Translator(
			"body>footer form input[name=email]"
		);
		$contactFormEmailInputDoubleQuotes = new Translator(
			"body>footer form input[name=\"email\"]"
		);
		$contactFormButton = new Translator(
			"body>footer form button[name=do][value=contact]"
		);

		$titleEl = $document->xPath($titleTranslator)->current();
		self::assertEquals("HTML Complex", $titleEl->innerText);

		$logoLinkTextEl = $document->xPath($logoLinkText)->current();
		self::assertEquals("Site logo", $logoLinkTextEl->innerText);

		$selectedNavMenuEl = $document->xPath($selectedNavMenu)->current();
		self::assertEquals("Home", trim($selectedNavMenuEl->innerText));

		$articleParagraphsList = $document->xPath($articleParagraphs);
		self::assertEquals(3, $articleParagraphsList->length);

		$contactFormEmailInputElArray = [
			$document->xPath($contactFormEmailInput)->current(),
			$document->xPath($contactFormEmailInputNoQuotes)->current(),
			$document->xPath($contactFormEmailInputDoubleQuotes)->current(),
		];
		foreach($contactFormEmailInputElArray as $el) {
			self::assertEquals(
				"email",
				$el->getAttribute("name")
			);
		}

		$contactFormButtonEl = $document->xPath($contactFormButton)->current();
		self::assertEquals("Send", $contactFormButtonEl->innerText);
	}
}