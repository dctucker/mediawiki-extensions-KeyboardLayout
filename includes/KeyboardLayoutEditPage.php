<?php

class KeyboardLayoutEditPage extends \MediaWiki\EditPage\EditPage {

	protected function showContentForm() {
		$out = $this->context->getOutput();
		$out->addModules("ext.keyboardlayout");

		$data = $this->getCurrentContent()->getNativeData();
		//$kb = file_get_contents(__DIR__."/../modules/kb.html");
		//$kb = str_replace('&', '&amp;', $kb);
		//$kb = str_replace('"', '&quot;', $kb);
		//$out->addHTML("<iframe style='width:100%;height:800px' srcdoc=\"$kb\"</iframe>");
		/*
		$encoded = rawurlencode(
			implode(",\n", array_map(function($x){
				return json_encode($x);
			}, json_decode($data)))."\n"
		);
		*/
		$encoded = rawurlencode(json_encode(json_decode($data)));
		#$out->addHTML($encoded);
		$uri = "/kle/kb.html##$encoded";
		$out->addHTML("<iframe id='wpKeyboardLayoutEditor' style='width:100%;min-width:1192px;height:914px' src=\"$uri\"></iframe>");
		$out->addHTML('<textarea id="wpTextbox1" name="wpTextbox1">'.$data.'</textarea>');
		$out->addHTML('<textarea id="wpSvgData1" name="wpSvgData1"></textarea>');
	}

	protected function importContentFormData( &$request ) {
	}
}
