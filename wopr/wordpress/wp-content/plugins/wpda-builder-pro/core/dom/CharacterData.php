<?php

namespace WPDaddy\Dom;

/**
 * Represents a Node object that contains characters.
 *
 * @inheritdoc ChildNode
 */
class CharacterData extends \DOMCharacterData {
	use LiveProperty, NonDocumentTypeChildNode, ChildNode;
}
