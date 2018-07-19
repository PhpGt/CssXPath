<?php

namespace Gt\CssXPath\Test;

use PHPUnit\Framework\TestCase;
use Gt\CssXPath\Test\Helper\Helper;
use Gt\CssXPath\Translator;
use Gt\Dom\HTMLDocument;

class TranslatorTest extends TestCase {
	public function testStar() {
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$starSelector = new Translator("*");
		$allStarNodeList = $document->xPath($starSelector);
		$bodyStarNodeList = $document->body->xPath($starSelector);

		$expectedNodeNames = [
			"outer" => ["html", "head", "meta", "title", "body"],
			"inner" => ["h1", "p", "em"],
		];

		$totalExpectedNodes = count($expectedNodeNames["outer"])
			+ count($expectedNodeNames["inner"]);

		self::assertCount($totalExpectedNodes, $allStarNodeList);
		self::assertCount(
			count($expectedNodeNames["inner"]),
			$bodyStarNodeList
		);
	}

	public function testElement() {
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$nodeNames = ["title", "body", "h1", "em"];
		$nonNodeNames = ["li", "table"];

		foreach($nodeNames as $nodeName) {
			$selector = new Translator($nodeName);
			$element = $document->xPath($selector)->current();
			self::assertEquals(
				$nodeName,
				strtolower(
						$element->tagName
					)
			);
		}

		foreach($nonNodeNames as $nodeName) {
			$selector = new Translator($nodeName);
			self::assertCount(
				0,
				$document->xPath($selector)
			);
		}
	}

	public function testChild() {
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$pWithEmChild = new Translator("p>em");
		$bodyWithEmChild = new Translator("body>em");

		$element = $document->xPath($pWithEmChild)->current();
		self::assertEquals("em", strtolower($element->tagName));

		self::assertCount(
			0,
			$document->xPath($bodyWithEmChild)
		);
	}

	public function testId() {
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$idSelector = new Translator("#the-title");
		$specificIdSelector = new Translator("h1#the-title");
		$wrongIdSelector = new Translator("h1#not-the-title");

		self::assertCount(
			1,
			$document->xPath($idSelector)
		);
		self::assertCount(
			1,
			$document->xPath($specificIdSelector)
		);
		self::assertCount(
			0,
			$document->xPath($wrongIdSelector)
		);
	}

	public function testSibling() {
// Note: "+" is the adjacent sibling selector - only matching elements that come immediately after
// another. The "~" operator is general sibling selector.
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
// In this selector example, we should be selecting the first div after the header
// (appearing in the body>main>article element)
		$inputSiblingSelector = new Translator("header + div");
		$inputSiblingSelectorNoWs = new Translator("header+div");

		self::assertCount(
			1,
			$document->xPath($inputSiblingSelector)
		);
		self::assertCount(
			1,
			$document->xPath($inputSiblingSelectorNoWs)
		);
	}

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