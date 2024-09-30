<?php
/**
 * Content handler for keyboard layout pages.
 *
 * @file
 * @ingroup Content
 */

use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Html\Html;

/**
 * Content handler for keyboard layout pages.
 *
 * @since 1.24
 * @ingroup Content
 */
class KeyboardLayoutContentHandler extends JsonContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_KEYBOARD_LAYOUT ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_KEYBOARD_LAYOUT ] );
	}

	protected function getContentClass() {
		return KeyboardLayoutContent::class;
	}

	protected function fillParserOutput( Content $content, ContentParseParams $cpoParams, ParserOutput &$output ) {
		//$html = "lol";
		$title = $cpoParams->getPage()->getPrefixedText();
		$wikitext = $content->getWikitext($title);  //$content->getWikitextForTransclusion();
		$wikitext .= "\n<noinclude>[[Category:Keyboard layout]]</noinclude>\n";
		$parsed = MediaWikiServices::getInstance()->getParserFactory()->getInstance()
			->parse($wikitext, $cpoParams->getPage(), $cpoParams->getParserOptions(), true, true, $cpoParams->getRevId());
		$output = $parsed;
		//$output->setRawText($html);
		$output->addModuleStyles( [ 'ext.keyboard-layout-editor.styles' ] );
	}

	public function getActionOverrides() {
		return [
			'edit' => KeyboardLayoutEditAction::class,
		];
	}
}
