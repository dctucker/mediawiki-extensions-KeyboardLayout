<?php
/**
 * Keyboard Layout Content Model
 *
 * @file
 *
 * @author Casey Tucker <dctucker !@! hotmail ! com>
 */

/**
 * Represents the content of a keyboard layout content.
 * @since 1.24
 */
class KeyboardLayoutContent extends JsonContent {

	/** @var \Status */
	private $status;

	public function __construct( $text, $modelId = CONTENT_MODEL_KEYBOARD_LAYOUT ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return bool Whether content is valid keyboard layout.
	 */
	public function isValid() {
		// It is possible for the valid keyboard layout to be ok but not good,
		// implying that it decodes ok but did not pass some additional validation
		return $this->getKeyboardLayout()->isGood();
	}

	/**
	 * Convert native data string into the Status object.
	 * On success, status' value is set to the decoded value.
	 * Derived classes may override this method to add additional validation
	 * @return Status
	 */
	protected function parseKeyboardLayout() {
		$data = json_decode($this->getNativeData());
		$result = $data;
		return Status::newGood( $result );
	}

	/**
	 * Return the status object that will contain parsed data if ok, or the error otherwise
	 * Note to inheritors: override parseKeyboardLayout() instead of this method
	 * @return Status
	 */
	public function getKeyboardLayout() {
		if ( $this->status === null ) {
			$this->status = $this->parseKeyboardLayout();
		}
		return $this->status;
	}

	protected function jsonToText() {
		$status = $this->getKeyboardLayout();
		if ( !$status->isOK() ) {
			return $this->getNativeData();
		}
		$text = json_encode($status->value);
		return $text;
	}

	/**
	 * Beautifies keyboard layout prior to save.
	 * @param Title $title Title
	 * @param User $user User
	 * @param ParserOptions $popts
	 * @return KeyboardLayoutContent
	 */
	public function preSaveTransform( Title $title, User $user, ParserOptions $popts ) {
		$data = $this->getKeyboardLayout();
		if ( !$data->isOK() ) {
			return $this;
		}
		// When saving, we are ok to save optimally-encoded, non-pretty-printed data
		$newText = $this->jsonToText();
		if ( $this->getNativeData() === $newText ) {
			return $this;
		}
		$new = new static( $newText, $this->getModel() );
		return $new;
	}

	/**
	 * @return string keyboard layout as HTML elements
	 */
	public function getHtml() {
		$data = $this->getKeyboardLayout();
		// Return original data if this is not a valid keyboard layout
		if (!$data->isOK()) {
			$text = $this->getNativeData();
			return $this->codeToPreElement( 'mw-keyboardlayout', $text );
		}

		$html = "<div class='mw-keyboard-layout'>";
		$layout = $this->getKeyboardLayout();
		$data = base64_encode($layout->value[0]->preview);
		$html .= "<img src=\"data:image/svg+xml;base64,$data\"/>";
		$html .= "</div>";
		return $html;
	}

	/**
	 * Converts data to a <pre>...</pre> rendering of the code
	 * @param string $cssClass CSS class, such as 'mw-css' or 'mw-js'
	 * @param string $text code to display
	 * @return string code wrapped in a <pre> tag with the specified css class
	 */
	protected function codeToPreElement( $cssClass, $text ) {
		$html = "<pre class='mw-code $cssClass' dir='ltr'>\n";
		$html .= htmlspecialchars( $text );
		$html .= "\n</pre>\n";

		return $html;
	}


	/** @var Title Hacky state to figure out which title we are talking about */
	private static $lastTitle;

	/**
	 * This is bawolff's evil hack.
	 * Lowercase t in title is to follow core.
	 * This also is called by BeforeParserFetchTemplateRevisionRecord
	 * which has same second argument, but other args are different.
	 *
	 * @param mixed $foo Ignored argument, different depending on which hook
	 * @param Title $title Title to look up
	 */
	public static function onBeforeParserFetchTemplateAndtitle( $foo, Title $title ) {
		self::$lastTitle = $title;
	}

	/**
	 * @param string $title e.g. Keyboard:Ergodox
	 * @return string Wikitext
	 */
	public function getWikitext($title) {
		$imagename = str_replace(":","--", $title);
		$link = "{{#ifeq:{{FULLPAGENAME}}|$title||{{#if:{{{link|}}}|{{{link}}}|$title}}}}";
		return "{{#if:{{{thumb|}}}|[[Image:$imagename.svg|thumb|link=$link|{{{caption|}}}]]|[[Image:$imagename.svg|link=$link]] }}";
	}

	/**
	 * Make template transclusion work
	 *
	 * This is hacky. I wish this interface was more like a parser function
	 *
	 * @see https://github.com/wikimedia/mediawiki-extensions-HTMLTemplates/blob/master/includes/Hooks.php
	 * @return string Wikitext
	 */
	public function getWikitextForTransclusion() {
		if ( !self::$lastTitle || self::$lastTitle->getNamespace() !== NS_KEYBOARD ) {
			return '<strong class="error">"'.self::$lastTitle.'"' .
				wfEscapeWikiText( wfMessage( 'htmltemplates-transclusionerror' )->inContentLanguage()->text() ) .
				'</strong>';
		}
		$pagename = self::$lastTitle->getPrefixedText();
		return $this->getWikitext($pagename);
		//return '{{#renderHTMLTemplate:' . wfEscapeWikiText( $pagename ) . '|' . '_useParentParameters=true|' . '__hash=' . wfEscapeWikiText( $hash ) . '}}';
	}
}
