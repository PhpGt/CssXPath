<?php
namespace Gt\CssXPath;

class Translator {
	const cssRegex =
		'/'
		. '(?P<star>\*)'
		. '|(:(?P<pseudo>[\w-]*))'
		. '|\(*(?P<pseudospecifier>["\']*[\w\h-]*["\']*)\)'
		. '|(?P<element>[\w-]*)'
		. '|(?P<child>\s*>\s*)'
		. '|(#(?P<id>[\w-]*))'
		. '|(\.(?P<class>[\w-]*))'
		. '|(?P<sibling>\s*\+\s*)'
		. "|(\[(?P<attribute>[\w-]*)((?P<attribute_equals>[=~$]+)(?P<attribute_value>[^\]]+))*\])+"
		. '|(?P<descendant>\s+)'
		. '/';

	const EQUALS_EXACT = "=";
	const EQUALS_CONTAINS_WORD = "~=";
	const EQUALS_ENDS_WITH = "$=";
	const EQUALS_CONTAINS = "*=";
	const EQUALS_STARTS_WITH_OR_STARTS_WITH_HYPHENATED = "|=";
	const EQUALS_STARTS_WITH = "^=";

	protected $cssSelector;
	protected $prefix;

	public function __construct(string $cssSelector, string $prefix = ".//") {
		$this->cssSelector = $cssSelector;
		$this->prefix = $prefix;
	}

	public function __toString():string {
		return $this->asXPath();
	}

	public function asXPath():string {
		return $this->convert($this->cssSelector);
	}

	protected function convert(string $css):string {
		$cssArray = preg_split(
			'/(["\']).*?\1(*SKIP)(*F)|,/',
			$css
		);
		$xPathArray = [];

		foreach($cssArray as $input) {
			$output = $this->convertSingleSelector(trim($input));
			$xPathArray []= $output;
		}

		return implode(" | ", $xPathArray);
	}

	protected function convertSingleSelector(string $css):string {
		$thread = $this->preg_match_collated(self::cssRegex, $css);
		$thread = array_values($thread);

		$xpath = [$this->prefix];
		$prevType = "";
		foreach($thread as $threadKey => $currentThreadItem) {
			$next = isset($thread[$threadKey + 1])
				? $thread[$threadKey + 1]
				: false;

			switch ($currentThreadItem['type']) {
			case 'star':
			case 'element':
				$xpath []= $currentThreadItem['content'];
				break;

			case 'pseudo':
				$specifier = '';
				if ($next && $next['type'] == 'pseudospecifier') {
					$specifier = "{$next['content']}";
				}

				switch ($currentThreadItem['content']) {
				case 'disabled':
				case 'checked':
				case 'selected':
					$xpath []= "[@{$currentThreadItem['content']}]";
					break;

				case 'text':
					$xpath []= '[@type="text"]';
					break;

				case 'contains':
					if (empty($specifier)) {
						continue 3;
					}

					$xpath []= "[contains(text(),$specifier)]";
					break;

				case 'first-child':
					$prev = count($xpath) - 1;
					$xpath[$prev] = '*[1]/self::' . $xpath[$prev];
					break;

				case 'nth-child':
					if (empty($specifier)) {
						continue 3;
					}

					$prev = count($xpath) - 1;
					$previous = $xpath[$prev];

					if (substr($previous, -1, 1) === ']') {
						$xpath[$prev] = str_replace(']', " and position() = $specifier]", $xpath[$prev]);
					}
					else {
						$xpath []= "[$specifier]";
					}
					break;
				case 'nth-of-type':
					if (empty($specifier)) {
						continue 3;
					}

					$prev = count($xpath) - 1;
					$previous = $xpath[$prev];

					if (substr($previous, -1, 1) === ']') {
						$xpath []= "[$specifier]";
					} else {
						$xpath []= "[$specifier]";
					}
					break;
				}
				break;

			case 'child':
				$xpath []= '/';
				break;

			case 'id':
				$xpath []= ($prevType != 'element'  ? '*' : '') . "[@id='{$currentThreadItem['content']}']";
				break;

			case 'class':
				// https://devhints.io/xpath#class-check
				$xpath []= (($prevType != 'element' && $prevType != 'class') ? '*' : '')
					. "[contains(concat(' ',normalize-space(@class),' '),' {$currentThreadItem['content']} ')]";
				break;

			case 'sibling':
				$xpath []= "/following-sibling::*[1]/self::";
				break;

			case 'attribute':
				if(!$prevType) {
					$xpath []= "*";
				}

				$detail = $currentThreadItem["detail"] ?? null;
				$detailType = $detail[0] ?? null;
				$detailValue = $detail[1] ?? null;

				if(!$detailType
					|| $detailType["type"] !== "attribute_equals") {
					$xpath []= "[@{$currentThreadItem['content']}]";
					continue 2;
				}

				$valueString = trim(
					$detailValue["content"],
					" '\""
				);

				$equalsType = $detailType["content"];
				switch ($equalsType) {
				case self::EQUALS_EXACT:
					$xpath []= "[@{$currentThreadItem['content']}=\"{$valueString}\"]";
					break;

				case self::EQUALS_CONTAINS:
					// TODO.
					break;

				case self::EQUALS_CONTAINS_WORD:
					$xpath []= "["
						. "contains("
						. "concat(\" \",@{$currentThreadItem['content']},\" \"),"
						. "concat(\" \",\"{$valueString}\",\" \")"
						. ")"
						. "]";
					break;

				case self::EQUALS_STARTS_WITH_OR_STARTS_WITH_HYPHENATED:
					// TODO.
					break;

				case self::EQUALS_STARTS_WITH:
					// TODO.
					break;

				case self::EQUALS_ENDS_WITH:
					$xpath []= "["
						. "substring("
						. "@{$currentThreadItem['content']},"
						. "string-length(@{$currentThreadItem['content']}) - "
						. "string-length(\"{$valueString}\") + 1)"
						. "=\"{$valueString}\""
						. "]";
					break;
				}
				break;

			case 'descendant':
				$xpath []= '//';
				break;
			}

			$prevType = $currentThreadItem['type'];
		}

		return implode("", $xpath);
	}

	protected function preg_match_collated(
		string $regex,
		string $string,
		callable $transform = null
	):array {
		preg_match_all(
			$regex,
			$string,
			$matches,
			PREG_PATTERN_ORDER
		);

		$set = [];
		foreach($matches[0] as $k => $v) {
			if(!empty($v)) {
				$set[$k] = null;
			}
		}

		foreach($matches as $k => $m) {
			if(is_numeric($k)) {
				continue;
			}

			foreach($m as $i => $match) {
				if($match === '') {
					continue;
				}

				$toSet = null;

				if($transform) {
					$toSet = $transform($k, $match);
				}
				else {
					$toSet = ['type' => $k, 'content' => $match];
				}

				if(!isset($set[$i])) {
					$set [$i]= $toSet;
				}
				else {
					if(!isset($set[$i]["detail"])) {
						$set[$i]["detail"] = [];
					}

					$set[$i]["detail"] []= $toSet;
				}
			}
		}

		return $set;
	}
}