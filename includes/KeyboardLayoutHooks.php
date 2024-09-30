<?php

use MediaWiki\Hook\EditPage__showEditForm_initialHook;
use MediaWiki\Hook\EditPage__importFormDataHook;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Request\WebRequest;
use MediaWiki\EditPage\EditPage;
use MediaWiki\MediaWikiServices;

class KeyboardLayoutHooks // implements EditPage__showEditForm_initialHook
	implements BeforePageDisplayHook
{
	/** @var string CSS class for keyboard layout data. */
	const KEYBOARD_LAYOUT_CSS_CLASS = 'mw-keyboard-layout';

	/** @var int Cache version. Increment whenever the HTML changes. */
	const CACHE_VERSION = 1;

	/**
	 * Define content handler constants upon extension registration
	 */
	public static function onRegistration() {
		define( 'CONTENT_MODEL_KEYBOARD_LAYOUT', 'keyboardlayout' );
		define( 'CONTENT_FORMAT_KEYBOARD_LAYOUT', 'application/json' );
	}

	/**
	 * Register parser hook
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		//$parser->setHook( 'keyboardlayout', [ 'Hooks', 'parserHook' ] );
	}

	/**
	 * @param Title $title
	 * @param string &$languageCode
	 * @return bool
	 */
	public static function onCodeEditorGetPageLanguage( Title $title, &$languageCode ) {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'CodeEditor' ) ) {
			return true;
		}
		if ( $title->hasContentModel( CONTENT_MODEL_KEYBOARD_LAYOUT ) )
		{
			$languageCode = 'json';
			return false;
		}

		return true;
	}

	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModuleStyles(["ext.keyboard-layout-editor.styles"]);
		//$out->addHtml($out->getContext()->getTitle());
	}

	public static function onEditPage__importFormData( EditPage &$editpage, WebRequest $request ) {
		//$file = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->newFile( $nt );
		//$file->upload();
		//TODO backend upload instead of AJAX call
		file_put_contents("/tmp/test.svg", $request->getVal("wpSvgData1"));
	}
}
