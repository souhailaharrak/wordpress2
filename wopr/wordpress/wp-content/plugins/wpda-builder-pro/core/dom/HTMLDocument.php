<?php

namespace WPDaddy\Dom;

use DOMDocument;

/**
 * Provides access to special properties and methods not present by default
 * on a regular document.
 *
 * @property-read HTMLCollection $anchors List of all of the anchors
 *  in the document. Anchors are <a> Elements with the `name` attribute.
 * @property-read Element        $body    The <body> element. Returns new Element if there
 *  was no body in the source HTML.
 * @property-read HTMLCollection $forms   List of all <form> elements.
 * @property-read Element        $head    The <head> element. Returns new Element if there
 *  was no head in the source HTML.
 * @property-read HTMLCollection $images  List of all <img> elements.
 * @property-read HTMLCollection $links   List of all links in the document.
 *  Links are <a> Elements with the `href` attribute.
 * @property-read HTMLCollection $scripts List of all <script> elements.
 * @property string              $title   The title of the document, defined using <title>.
 */
class HTMLDocument extends Document {
	use LiveProperty, ParentNode;

	/**
	 * An option passed to loadHTML() and loadHTMLFile() to disable duplicate element IDs exception.
	 */
	const ALLOW_DUPLICATE_IDS = 67108864;

	/**
	 * A modification (passed to modify()) that removes all but the last title elements.
	 */
	const FIX_MULTIPLE_TITLES = 2;

	/**
	 * A modification (passed to modify()) that removes all but the last metatags with matching name or property attributes.
	 */
	const FIX_DUPLICATE_METATAGS = 4;

	/**
	 * A modification (passed to modify()) that merges multiple head elements.
	 */
	const FIX_MULTIPLE_HEADS = 8;

	/**
	 * A modification (passed to modify()) that merges multiple body elements.
	 */
	const FIX_MULTIPLE_BODIES = 16;

	/**
	 * A modification (passed to modify()) that moves charset metatag and title elements first.
	 */
	const OPTIMIZE_HEAD = 32;

	/**
	 *
	 * @var array
	 */
	static private $newObjectsCache = [];

	/**
	 * Indicates whether an HTML code is loaded.
	 *
	 * @var boolean
	 */
	private $loaded = false;

	public function __construct($document = ""){
		parent::__construct($document);

		if(!($document instanceof DOMDocument)) {
			if(empty($document)) {
				$this->fillEmptyDocumentElement();
			} else {
// loadHTML expects an ISO-8859-1 encoded string.
// http://stackoverflow.com/questions/11309194/php-domdocument-failing-to-handle-utf-8-characters
				if(function_exists('mb_convert_encoding')) {
					$document = mb_convert_encoding(
						$document,
						"HTML-ENTITIES",
						"UTF-8"
					);
					$this->loadHTML($document);
				} else {
					$this->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$document);
				}
			}
		}
	}

	public function getElementsByClassName($names){
		return $this->documentElement->getElementsByClassName($names);
	}

	protected function prop_get_head(){
		return $this->getOrCreateElement("head");
	}

	protected function prop_get_body(){
		return $this->getOrCreateElement("body");
	}

	protected function prop_get_forms(){
		return $this->getElementsByTagName("form");
	}

	protected function prop_get_anchors(){
		return $this->querySelectorAll("a[name]");
	}

	protected function prop_get_images(){
		return $this->getElementsByTagName("img");
	}

	protected function prop_get_links(){
		return $this->querySelectorAll("a[href]");
	}

	protected function prop_get_title(){
		$title = $this->head->getElementsByTagName("title")->item(0);

		if(is_null($title)) {
			return "";
		} else {
			return $title->textContent;
		}
	}

	protected function prop_set_title($value){
		$title = $this->head->getElementsByTagName("title")->item(0);

		if(is_null($title)) {
			$title = $this->createElement("title");
			$this->head->appendChild($title);
		}

		$title->textContent = $value;
	}

	private function getOrCreateElement($tagName){
		$element = $this->querySelector($tagName);
		if(is_null($element)) {
			$element = $this->createElement($tagName);
			$this->documentElement->appendChild($element);
		}

		return $element;
	}

	public function loadHTML($source, $options = 0)
	{
		$options = $options | self::ALLOW_DUPLICATE_IDS;
		// Enables libxml errors handling
		$internalErrorsOptionValue = libxml_use_internal_errors();
		if ($internalErrorsOptionValue === false) {
			libxml_use_internal_errors(true);
		}

		$source = trim($source);

		// Add CDATA around script tags content
		$matches = null;
		preg_match_all('/<script(.*?)>/', $source, $matches);
		if (isset($matches[0])) {
			$matches[0] = array_unique($matches[0]);
			foreach ($matches[0] as $match) {
				if (substr($match, -2, 1) !== '/') { // check if ends with />
					$source = str_replace($match, $match . '<![CDATA[html5-dom-document-internal-cdata', $source);
				}
			}
		}
		$source = str_replace('</script>', 'html5-dom-document-internal-cdata]]></script>', $source);
		$source = str_replace('<![CDATA[html5-dom-document-internal-cdatahtml5-dom-document-internal-cdata]]>', '', $source); // clean empty script tags
		$matches = null;
		preg_match_all('/\<!\[CDATA\[html5-dom-document-internal-cdata.*?html5-dom-document-internal-cdata\]\]>/s', $source, $matches);
		if (isset($matches[0])) {
			$matches[0] = array_unique($matches[0]);
			foreach ($matches[0] as $match) {
				if (strpos($match, '</') !== false) { // check if contains </
					$source = str_replace($match, str_replace('</', '<html5-dom-document-internal-cdata-endtagfix/', $match), $source);
				}
			}
		}

		$autoAddHtmlAndBodyTags = !defined('LIBXML_HTML_NOIMPLIED') || ($options & LIBXML_HTML_NOIMPLIED) === 0;
		$autoAddDoctype = !defined('LIBXML_HTML_NODEFDTD') || ($options & LIBXML_HTML_NODEFDTD) === 0;

		$allowDuplicateIDs = ($options & self::ALLOW_DUPLICATE_IDS) !== 0;

		// Add body tag if missing
		if ($autoAddHtmlAndBodyTags && $source !== '' && preg_match('/\<!DOCTYPE.*?\>/', $source) === 0 && preg_match('/\<html.*?\>/', $source) === 0 && preg_match('/\<body.*?\>/', $source) === 0 && preg_match('/\<head.*?\>/', $source) === 0) {
			$source = '<body>' . $source . '</body>';
		}

		// Add DOCTYPE if missing
		if ($autoAddDoctype && strtoupper(substr($source, 0, 9)) !== '<!DOCTYPE') {
			$source = "<!DOCTYPE html>\n" . $source;
		}

		// Adds temporary head tag
		$charsetTag = '<meta data-html5-dom-document-internal-attribute="charset-meta" http-equiv="content-type" content="text/html; charset=utf-8" />';
		$matches = [];
		preg_match('/\<head.*?\>/', $source, $matches);
		$removeHeadTag = false;
		$removeHtmlTag = false;
		if (isset($matches[0])) { // has head tag
			$insertPosition = strpos($source, $matches[0]) + strlen($matches[0]);
			$source = substr($source, 0, $insertPosition) . $charsetTag . substr($source, $insertPosition);
		} else {
			$matches = [];
			preg_match('/\<html.*?\>/', $source, $matches);
			if (isset($matches[0])) { // has html tag
				$source = str_replace($matches[0], $matches[0] . '<head>' . $charsetTag . '</head>', $source);
			} else {
				$source = '<head>' . $charsetTag . '</head>' . $source;
				$removeHtmlTag = true;
			}
			$removeHeadTag = true;
		}

		// Preserve html entities
		$source = preg_replace('/&([a-zA-Z]*);/', 'html5-dom-document-internal-entity1-$1-end', $source);
		$source = preg_replace('/&#([0-9]*);/', 'html5-dom-document-internal-entity2-$1-end', $source);

		$result = parent::loadHTML('<?xml encoding="utf-8" ?>' . $source, $options);
		if ($internalErrorsOptionValue === false) {
			libxml_use_internal_errors(false);
		}
		if ($result === false) {
			return false;
		}
		$this->encoding = 'utf-8';
		foreach ($this->childNodes as $item) {
			if ($item->nodeType === XML_PI_NODE) {
				$this->removeChild($item);
				break;
			}
		}
		$metaTagElement = $this->getElementsByTagName('meta')->item(0);
		if ($metaTagElement !== null) {
			if ($metaTagElement->getAttribute('data-html5-dom-document-internal-attribute') === 'charset-meta') {
				$headElement = $metaTagElement->parentNode;
				$htmlElement = $headElement->parentNode;
				$metaTagElement->parentNode->removeChild($metaTagElement);
				if ($removeHeadTag && $headElement !== null && $headElement->parentNode !== null && ($headElement->firstChild === null || ($headElement->childNodes->length === 1 && $headElement->firstChild instanceof \DOMText))) {
					$headElement->parentNode->removeChild($headElement);
				}
				if ($removeHtmlTag && $htmlElement !== null && $htmlElement->parentNode !== null && $htmlElement->firstChild === null) {
					$htmlElement->parentNode->removeChild($htmlElement);
				}
			}
		}

		if (!$allowDuplicateIDs) {
			$matches = [];
			preg_match_all('/\sid[\s]*=[\s]*(["\'])(.*?)\1/', $source, $matches);
			if (!empty($matches[2]) && max(array_count_values($matches[2])) > 1) {
				$elementIDs = [];
				$walkChildren = function ($element) use (&$walkChildren, &$elementIDs) {
					foreach ($element->childNodes as $child) {
						if ($child instanceof \DOMElement) {
							if ($child->attributes->length > 0) { // Performance optimization
								$id = $child->getAttribute('id');
								if ($id !== '') {
									if (isset($elementIDs[$id])) {
										throw new \Exception('A DOM node with an ID value "' . $id . '" already exists!');
									} else {
										$elementIDs[$id] = true;
									}
								}
							}
							$walkChildren($child);
						}
					}
				};
				$walkChildren($this);
			}
		}

		$this->loaded = true;
		return true;
	}

	public function saveHTML(\DOMNode $node = null)
	{
		if (!$this->loaded) {
			return '<!DOCTYPE html>';
		}

		$nodeMode = $node !== null;
		if ($nodeMode && $node instanceof \DOMDocument) {
			$nodeMode = false;
		}

		if ($nodeMode) {
			if (!isset(self::$newObjectsCache['html5domdocument'])) {
				self::$newObjectsCache['html5domdocument'] = new HTMLDocument();
			}
			$tempDomDocument = clone (self::$newObjectsCache['html5domdocument']);
			if ($node->nodeName === 'html') {
				$tempDomDocument->loadHTML('<!DOCTYPE html>');
				$tempDomDocument->appendChild($tempDomDocument->importNode(clone ($node), true));
				$html = $tempDomDocument->saveHTML();
				$html = substr($html, 16); // remove the DOCTYPE + the new line after
			} elseif ($node->nodeName === 'head' || $node->nodeName === 'body') {
				$tempDomDocument->loadHTML("<!DOCTYPE html>\n<html></html>");
				$tempDomDocument->childNodes[1]->appendChild($tempDomDocument->importNode(clone ($node), true));
				$html = $tempDomDocument->saveHTML();
				$html = substr($html, 22, -7); // remove the DOCTYPE + the new line after + html tag
			} else {
				$isInHead = false;
				$parentNode = $node;
				for ($i = 0; $i < 1000; $i++) {
					$parentNode = $parentNode->parentNode;
					if ($parentNode === null) {
						break;
					}
					if ($parentNode->nodeName === 'body') {
						break;
					} elseif ($parentNode->nodeName === 'head') {
						$isInHead = true;
						break;
					}
				}
				$tempDomDocument->loadHTML("<!DOCTYPE html>\n<html>" . ($isInHead ? '<head></head>' : '<body></body>') . '</html>');
				$tempDomDocument->childNodes[1]->childNodes[0]->appendChild($tempDomDocument->importNode(clone ($node), true));
				$html = $tempDomDocument->saveHTML();
				$html = substr($html, 28, -14); // remove the DOCTYPE + the new line + html + body or head tags
			}
			$html = trim($html);
		} else {
			$removeHtmlElement = false;
			$removeHeadElement = false;
			$headElement = $this->getElementsByTagName('head')->item(0);
			if ($headElement === null) {
				if ($this->addHtmlElementIfMissing()) {
					$removeHtmlElement = true;
				}
				if ($this->addHeadElementIfMissing()) {
					$removeHeadElement = true;
				}
				$headElement = $this->getElementsByTagName('head')->item(0);
			}
			$meta = $this->createElement('meta');
			$meta->setAttribute('data-html5-dom-document-internal-attribute', 'charset-meta');
			$meta->setAttribute('http-equiv', 'content-type');
			$meta->setAttribute('content', 'text/html; charset=utf-8');
			if ($headElement->firstChild !== null) {
				$headElement->insertBefore($meta, $headElement->firstChild);
			} else {
				$headElement->appendChild($meta);
			}
			$html = parent::saveHTML();
			$html = rtrim($html, "\n");

			if ($removeHeadElement) {
				$headElement->parentNode->removeChild($headElement);
			} else {
				$meta->parentNode->removeChild($meta);
			}

			if (strpos($html, 'html5-dom-document-internal-entity') !== false) {
				$html = preg_replace('/html5-dom-document-internal-entity1-(.*?)-end/', '&$1;', $html);
				$html = preg_replace('/html5-dom-document-internal-entity2-(.*?)-end/', '&#$1;', $html);
			}

			$codeToRemove = [
				'html5-dom-document-internal-content',
				'<meta data-html5-dom-document-internal-attribute="charset-meta" http-equiv="content-type" content="text/html; charset=utf-8">',
				'</area>', '</base>', '</br>', '</col>', '</command>', '</embed>', '</hr>', '</img>', '</input>', '</keygen>', '</link>', '</meta>', '</param>', '</source>', '</track>', '</wbr>',
				'<![CDATA[html5-dom-document-internal-cdata', 'html5-dom-document-internal-cdata]]>', 'html5-dom-document-internal-cdata-endtagfix'
			];
			if ($removeHeadElement) {
				$codeToRemove[] = '<head></head>';
			}
			if ($removeHtmlElement) {
				$codeToRemove[] = '<html></html>';
			}

			$html = str_replace($codeToRemove, '', $html);
		}
		return $html;
	}

	private function addHtmlElementIfMissing()
	{
		if ($this->getElementsByTagName('html')->length === 0) {
			if (!isset(self::$newObjectsCache['htmlelement'])) {
				self::$newObjectsCache['htmlelement'] = new \DOMElement('html');
			}
			$this->appendChild(clone (self::$newObjectsCache['htmlelement']));
			return true;
		}
		return false;
	}

	/**
	 * Adds the HEAD tag to the document if missing.
	 *
	 * @return boolean TRUE on success, FALSE otherwise.
	 */
	private function addHeadElementIfMissing()
	{
		if ($this->getElementsByTagName('head')->length === 0) {
			$htmlElement = $this->getElementsByTagName('html')->item(0);
			if (!isset(self::$newObjectsCache['headelement'])) {
				self::$newObjectsCache['headelement'] = new \DOMElement('head');
			}
			$headElement = clone (self::$newObjectsCache['headelement']);
			if ($htmlElement->firstChild === null) {
				$htmlElement->appendChild($headElement);
			} else {
				$htmlElement->insertBefore($headElement, $htmlElement->firstChild);
			}
			return true;
		}
		return false;
	}

	/**
	 * Adds the BODY tag to the document if missing.
	 *
	 * @return boolean TRUE on success, FALSE otherwise.
	 */
	private function addBodyElementIfMissing()
	{
		if ($this->getElementsByTagName('body')->length === 0) {
			if (!isset(self::$newObjectsCache['bodyelement'])) {
				self::$newObjectsCache['bodyelement'] = new \DOMElement('body');
			}
			$this->getElementsByTagName('html')->item(0)->appendChild(clone (self::$newObjectsCache['bodyelement']));
			return true;
		}
		return false;
	}



	private function fillEmptyDocumentElement(){
		$this->loadHTML("<!doctype html><html></html>");
		$tagsToCreate = [ "head", "body" ];

		foreach($tagsToCreate as $tag) {
			$node = $this->createElement($tag);
			$this->documentElement->appendChild($node);
		}
	}
}
