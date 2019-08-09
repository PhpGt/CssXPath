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
		foreach($thread as $k => $item) {
			$next = isset($thread[$k + 1])
				? $thread[$k + 1]
				: false;

			switch ($item['type']) {
			case 'star':
			case 'element':
				$xpath []= $item['content'];
				break;

			case 'pseudo':
				$specifier = '';
				if ($next && $next['type'] == 'pseudospecifier') {
					$specifier = "{$next['content']}";
				}

				switch ($item['content']) {
				case 'disabled':
				case 'checked':
				case 'selected':
					$xpath []= "[@{$item['content']}]";
					break;

				case 'text':
					$xpath []= '[@type="text"]';
					break;

				case 'contains':
					if (empty($specifier)) {
						continue;
					}

					$xpath []= "[contains(text(),$specifier)]";
					break;

				case 'first-child':
					$prev = count($xpath) - 1;
					$xpath[$prev] = '*[1]/self::' . $xpath[$prev];
					break;

				case 'nth-child':
					if (empty($specifier)) {
						continue;
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
						continue;
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
				$xpath []= ($prevType != 'element'  ? '*' : '') . "[@id='{$item['content']}']";
				break;

			case 'class':
				// https://devhints.io/xpath#class-check
				$xpath []= (($prevType != 'element' && $prevType != 'class') ? '*' : '')
					. "[contains(concat(' ',normalize-space(@class),' '),' {$item['content']} ')]";
				break;

			case 'sibling':
				$xpath []= "/following-sibling::*[1]/self::";
				break;

			case 'attribute':
				if(!$prevType) {
					$xpath []= "*";
				}

				if (!$next || $next['type'] != 'attribute_equals') {
					$xpath []= "[@{$item['content']}]";
					continue;
				}

				$value = $thread[$k+2];
				$valueString = trim(
					$value['content'],
					" '\""
				);

				$equalsType = $next['content'];
				switch ($equalsType) {
				case '=':
					$xpath []= "[@{$item['content']}=\"{$valueString}\"]";
					break;

				case '~=':
					$xpath []= "["
						. "contains("
						. "concat(\" \",@{$item['content']},\" \"),"
						. "concat(\" \",\"{$valueString}\",\" \")"
						. ")"
						. "]";
					break;

				case '$=':
					$xpath []= "["
						. "substring("
						. "@{$item['content']},"
						. "string-length(@{$item['content']}) - "
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

			$prevType = $item['type'];
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
					$set []= $toSet;
				}
			}
		}

		return $set;
	}
}