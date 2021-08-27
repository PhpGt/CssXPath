<?php

namespace Gt\CssXPath\Test;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use Gt\CssXPath\Test\Helper\Helper;
use Gt\CssXPath\Translator;

class TranslatorTest extends TestCase {
	protected function setUp():void {
		libxml_use_internal_errors(true);
	}

	public function testStar() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);
		$xpath = new DOMXPath($document);

		$starSelector = new Translator("*");
		$allStarNodeList = $xpath->query($starSelector, $document);
		$bodyStarNodeList = $xpath->query($starSelector, $document->getElementsByTagName("body")->item(0));

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
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);
		$xpath = new DOMXPath($document);

		$nodeNames = ["title", "body", "h1", "em"];
		$nonNodeNames = ["li", "table"];

		foreach($nodeNames as $nodeName) {
			$selector = new Translator($nodeName);
			$element = $xpath->query($selector)->item(0);
			self::assertEquals(
				$nodeName,
				strtolower($element->tagName)
			);
		}

		foreach($nonNodeNames as $nodeName) {
			$selector = new Translator($nodeName);
			self::assertEquals(
				0,
				$xpath->query($selector)->length
			);
		}
	}

	public function testChild() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);
		$pWithEmChild = new Translator("p>em");
		$bodyWithEmChild = new Translator("body>em");
		$xpath = new DOMXPath($document);

		$element = $xpath->query($pWithEmChild)->item(0);
		self::assertEquals("em", strtolower($element->tagName));

		self::assertEquals(
			0,
			$xpath->query($bodyWithEmChild)->length
		);
	}

	public function testId() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);

		$idSelector = new Translator("#the-title");
		$specificIdSelector = new Translator("h1#the-title");
		$wrongIdSelector = new Translator("h1#not-the-title");

		$xpath = new DOMXPath($document);

		self::assertEquals(
			1,
			$xpath->query($idSelector)->length
		);
		self::assertEquals(
			1,
			$xpath->query($specificIdSelector)->length
		);
		self::assertEquals(
			0,
			$xpath->query($wrongIdSelector)->length
		);
	}

	public function testSibling() {
// Note: "+" is the adjacent sibling selector - only matching elements that come immediately after
// another. The "~" operator is general sibling selector.
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
// In this selector example, we should be selecting the first div after the header
// (appearing in the body>main>article element)
		$inputSiblingSelector = new Translator("header + div");
		$inputSiblingSelectorNoWs = new Translator("header+div");

		$xpath = new DOMXPath($document);

		self::assertEquals(
			1,
			$xpath->query($inputSiblingSelector)->length
		);
		self::assertEquals(
			1,
			$xpath->query($inputSiblingSelectorNoWs)->length
		);
	}

	public function testDescendant() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$articlePSelector = new Translator("article p");

		$xpath = new DOMXPath($document);

		self::assertEquals(
			4,
			$xpath->query($articlePSelector)->length
		);
	}

	public function testAttribute() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$attributeRequiredKeySelector = new Translator("[required]");

		$xpath = new DOMXPath($document);

		self::assertEquals(
			2,
			$xpath->query($attributeRequiredKeySelector)->length
		);

		$attributeNameSelector = new Translator("[name=your-name]");
		self::assertEquals(
			1,
			$xpath->query($attributeNameSelector)->length
		);

		$attributeNameSelectorWithQuotes = new Translator(
			"[name='your-name']"
		);
		self::assertEquals(
			1,
			$xpath->query($attributeNameSelectorWithQuotes)->length
		);

		$attributeNameSelectorWithDoubleQuotes = new Translator(
			"[name=\"your-name\"]"
		);
		self::assertEquals(
			1,
			$xpath->query($attributeNameSelectorWithDoubleQuotes)->length
		);
	}

	public function testAttributeTildeSelector() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$contentElement = $document->getElementById("content-element");
		$xpath = new DOMXPath($document);

		$selector = new Translator("[data-categories~=test]");
		self::assertSame(
			$contentElement,
			$xpath->query($selector)->item(0)
		);

		$selector = new Translator("[data-categories~=blog-test]");
		self::assertSame(
			$contentElement,
			$xpath->query($selector)->item(0)
		);

		$selector = new Translator("[data-categories~=example]");
		self::assertSame(
			$contentElement,
			$xpath->query($selector)->item(0)
		);
	}

	public function testAttributeDollarSelector() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$xpath = new DOMXPath($document);

		$selector = new Translator("[data-test-thing$=test]");
		self::assertEquals(
			2,
			$xpath->query($selector)->length
		);
	}

	public function testClassSelector() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$xpath = new DOMXPath($document);

		$navElement = $xpath->query(
			new Translator(".c-menu")
		)->item(0);
		self::assertEquals(
			"NAV",
			strtoupper($navElement->tagName)
		);

		$navElement2 = $xpath->query(
			new Translator("nav.c-menu")
		)->item(0);
		self::assertSame($navElement, $navElement2);

 		$navElement3 = $xpath->query(
			new Translator("nav.c-menu.main-selection")
		)->item(0);
		self::assertSame($navElement, $navElement3);

		$firstNavItem = $xpath->query(
			new Translator("nav.c-menu.main-selection li")
		)->item(0);
		$selectedNavItem = $xpath->query(
			new Translator("nav.c-menu.main-selection .selected")
		)->item(0);

		self::assertEquals("Home", trim($firstNavItem->nodeValue));
		self::assertEquals("Blog", trim($selectedNavItem->nodeValue));
	}

	public function testSimple() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);

		$bodyTranslator = new Translator("body");
		$h1Translator = new Translator("h1");
		$emTranslator = new Translator("p em");
		$allTranslator = new Translator("*");

		$xpath = new DOMXPath($document);

		$body = $xpath->query($bodyTranslator)->item(0);
		$h1 = $xpath->query($h1Translator)->item(0);
		$em = $xpath->query($emTranslator)->item(0);

		self::assertEquals("body", $body->tagName);
		self::assertEquals("h1", $h1->tagName);
		self::assertEquals("em", $em->tagName);

		$allElements = $xpath->query($allTranslator);
		$allElementsInBody = $xpath->query($allTranslator, $document->getElementsByTagName("body")->item(0));

		self::assertEquals(
			3, // h1, p, em (not body, as body is the referenceNode)
			$allElementsInBody->length
		);
		self::assertGreaterThan(
			$allElementsInBody->length,
			$allElements->length
		);
	}

	public function testComplex() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$xpath = new DOMXPath($document);

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

		$titleEl = $xpath->query($titleTranslator)->item(0);
		self::assertEquals("HTML Complex", $titleEl->nodeValue);

		$logoLinkTextEl = $xpath->query($logoLinkText)->item(0);
		self::assertEquals("Site logo", $logoLinkTextEl->nodeValue);

		$selectedNavMenuEl = $xpath->query($selectedNavMenu)->item(0);
		self::assertEquals("Blog", trim($selectedNavMenuEl->nodeValue));

		$articleParagraphsList = $xpath->query($articleParagraphs);
		self::assertEquals(3, $articleParagraphsList->length);

		$contactFormEmailInputElArray = [
			$xpath->query($contactFormEmailInput)->item(0),
			$xpath->query($contactFormEmailInputNoQuotes)->item(0),
			$xpath->query($contactFormEmailInputDoubleQuotes)->item(0),
		];
		foreach($contactFormEmailInputElArray as $el) {
			self::assertEquals(
				"email",
				$el->getAttribute("name")
			);
		}

		$contactFormButtonEl = $xpath->query($contactFormButton)->item(0);
		self::assertEquals("Send", $contactFormButtonEl->nodeValue);
	}

	public function testCheckedPseudoSelector() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$xpath = new DOMXPath($document);

		$translator = new Translator("input:checked");
		$checkedEl = $xpath->query($translator)->item(0);
		self::assertEquals("input", $checkedEl->tagName);
	}

	public function testCommaSeparatedSelectors() {
// Multiple XPath selectors are separated by a pipe (|), so the CSS selector
// `div, form` should translate to descendant-or-self::div | descendant-or-self::form`
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SIMPLE);
		$xpath = new DOMXPath($document);

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
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_COMPLEX);
		$xpath = new DOMXPath($document);

		$emailTranslator = new Translator("[name=email]");
		$messageTranslator = new Translator("[data-ga-client='(Test) Message, this has a comma']");

		$emailItems = $xpath->query($emailTranslator);
		$messageItems = $xpath->query($messageTranslator);

		self::assertEquals(1, $emailItems->length);
		self::assertEquals(1, $messageItems->length);

		self::assertEquals("input", $emailItems->item(0)->tagName);
		self::assertEquals("span", $messageItems->item(0)->tagName);
	}

	public function testHierarchyIsRespectedForChildSelectors() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_SELECTS);
		$xpath = new DOMXPath($document);
		$fromOptionTranslator = new Translator("[name=from] option");
		$toOptionTranslator = new Translator("[name=to] option");

		$fromOptions = $xpath->query($fromOptionTranslator);
		$toOptions = $xpath->query($toOptionTranslator);

		self::assertEquals(0, $fromOptions->item(0)->nodeValue);
		self::assertEquals(5, $toOptions->item(0)->nodeValue);
	}

	public function testSquareBracketsNameAttribute() {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->loadHTML(Helper::HTML_CHECKBOX);
		$xpath = new DOMXPath($document);
		$choiceTranslator = new Translator("[name='choice[]']");
		$choiceInputs = $xpath->query($choiceTranslator);

		self::assertEquals(3, $choiceInputs->length);
	}
}
