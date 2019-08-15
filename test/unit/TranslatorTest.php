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

	public function testDescendant() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$articlePSelector = new Translator("article p");

		self::assertCount(
			4,
			$document->xPath($articlePSelector)
		);
	}

	public function testAttribute() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$attributeRequiredKeySelector = new Translator("[required]");
		self::assertCount(
			2,
			$document->xPath($attributeRequiredKeySelector)
		);

		$attributeNameSelector = new Translator("[name=your-name]");
		self::assertCount(
			1,
			$document->xPath($attributeNameSelector)
		);

		$attributeNameSelectorWithQuotes = new Translator(
			"[name='your-name']"
		);
		self::assertCount(
			1,
			$document->xPath($attributeNameSelectorWithQuotes)
		);

		$attributeNameSelectorWithDoubleQuotes = new Translator(
			"[name=\"your-name\"]"
		);
		self::assertCount(
			1,
			$document->xPath($attributeNameSelectorWithDoubleQuotes)
		);
	}

	public function testAttributeTildeSelector() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$contentElement = $document->querySelector("article .content");

		$selector = new Translator("[data-categories~=test]");
		self::assertSame(
			$contentElement,
			$document->xPath($selector)[0]
		);

		$selector = new Translator("[data-categories~=blog-test]");
		self::assertSame(
			$contentElement,
			$document->xpath($selector)[0]
		);

		$selector = new Translator("[data-categories~=example]");
		self::assertSame(
			$contentElement,
			$document->xpath($selector)[0]
		);
	}

	public function testAttributeDollarSelector() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$selector = new Translator("[data-test-thing$=test]");
		self::assertCount(
			2,
			$document->xPath($selector)
		);
	}

	public function testClassSelector() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$navElement = $document->xPath(
			new Translator(".c-menu")
		)[0];
		self::assertEquals(
			"NAV",
			strtoupper($navElement->tagName)
		);

		$navElement2 = $document->xPath(
			new Translator("nav.c-menu")
		)[0];
		self::assertSame($navElement, $navElement2);

 		$navElement3 = $document->xPath(
			new Translator("nav.c-menu.main-selection")
		)[0];
		self::assertSame($navElement, $navElement3);

		$firstNavItem = $document->xPath(
			new Translator("nav.c-menu.main-selection li")
		)[0];
		$selectedNavItem = $document->xPath(
			new Translator("nav.c-menu.main-selection .selected")
		)[0];
		self::assertEquals("Home", trim($firstNavItem->innerText));
		self::assertEquals("Blog", trim($selectedNavItem->innerText));
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
		self::assertEquals("Blog", trim($selectedNavMenuEl->innerText));

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

	public function testCheckedPseudoSelector() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$translator = new Translator("input:checked");
		$checkedEl = $document->xPath($translator)->current();
		self::assertEquals("input", $checkedEl->tagName);
	}

	public function testCommaSeparatedSelectors() {
// Multiple XPath selectors are separated by a pipe (|), so the CSS selector
// `div, form` should translate to descendant-or-self::div | descendant-or-self::form`
		$document = new HTMLDocument(Helper::HTML_SIMPLE);
		$translator = new Translator("h1, p");
		self::assertEquals(".//h1 | .//p", $translator);

		$translator = new Translator(
			"h1, p",
			"descendant-or-self::"
		);
		self::assertEquals(
			"descendant-or-self::h1 | descendant-or-self::p",
			$translator
		);
	}

	public function testCommaInAttributeDoesNotSeparate() {
		$document = new HTMLDocument(Helper::HTML_COMPLEX);
		$emailTranslator = new Translator("[name=email]");
		$messageTranslator = new Translator("[data-ga-client='(Test) Message, this has a comma']");

		$emailItems = $document->xPath($emailTranslator);
		$messageItems = $document->xPath($messageTranslator);

		self::assertCount(1, $emailItems);
		self::assertCount(1, $messageItems);

		self::assertEquals("INPUT", strtoupper($emailItems[0]->tagName));
		self::assertEquals("SPAN", strtoupper($messageItems[0]->tagName));
	}

	public function testHierarchyIsRespectedForChildSelectors() {
		$document = new HTMLDocument(Helper::HTML_SELECTS);
		$fromOptionTranslator = new Translator("[name=from] option");
		$toOptionTranslator = new Translator("[name=to] option");

		$fromOptions = $document->xPath($fromOptionTranslator);
		$toOptions = $document->xPath($toOptionTranslator);

		self::assertEquals(0, $fromOptions[0]->value);
		self::assertEquals(5, $toOptions[0]->value);
	}
}