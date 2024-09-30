var keyboardlayout = {
	init: () => {
		keyboardlayout.iframe = document.getElementById('wpKeyboardLayoutEditor');
		keyboardlayout.textarea = document.getElementById('wpTextbox1');
		keyboardlayout.iframe.contentWindow.onbeforeunload = () => {};
		keyboardlayout.svgdata = document.getElementById('wpSvgData1');
	},
	scope: () => {
		var iframe = document.getElementById('wpKeyboardLayoutEditor');
		var body = iframe.contentDocument.getElementsByTagName('body')[0];
		return iframe.contentWindow.angular.element(body).scope();
	},
	serialize: () => {
		var cw = keyboardlayout.iframe.contentWindow;
		var scope = keyboardlayout.scope();
		var value = cw.$serial.serialize(scope.keyboard);
		return JSON.stringify(value, undefined, "\t");
	},
	updateTextarea: () => {
		var scope = keyboardlayout.scope();
		keyboardlayout.textarea.value = keyboardlayout.serialize();
		keyboardlayout.svgdata.value = scope.getSvg().replace(/(^[ \t]*\n)/gm, "");
		scope.dirty = false;
	},
	consumeTextarea: () => {
		var scope = keyboardlayout.scope();
		var cw = keyboardlayout.iframe.contentWindow;
		var data = cw.$serial.fromJsonL(keyboardlayout.textarea.value);
		scope.deserializeAndRender(data);
	},
};

$(document).ready(e => {
	// consume edits to the textarea
	$('#wpTextbox1').on('focus', e => {
		e.target.previousValue = e.target.value;
	});
	$('#wpTextbox1').on('blur', e => {
		if (e.target.value != e.target.previousValue) {
			keyboardlayout.consumeTextarea();
		}
	});

	// populate textarea right before the form submits
	$("#wpSave").on("focus", e => {
		keyboardlayout.updateTextarea();
	});
	$("#wpPreview").on("focus", e => {
		keyboardlayout.updateTextarea();
	});
	$("#wpSave").on("click", e => {
		e.preventDefault();
		var title = mw.config.get('wgPageName').replace(":", "--");
		let filename = title + ".svg";
		var svg = keyboardlayout.scope().getSvg();
		var blob = new Blob([svg], {filename: filename, format: "svg"});
		var api = new mw.Api();
		api.uploadWithFormData(blob, {filename: filename, format: "svg", ignorewarnings: 1}).done((finish) => {
			console.log("Upload successful!");
			keyboardlayout.scope().dirty = false;
			$("#wpSave").unbind("click").click();
		}).fail((data) => {
			console.log(data);
			keyboardlayout.scope().dirty = false;
			$("#wpSave").unbind("click").click();
		});
	});

	$("#wpKeyboardLayoutEditor").on("load", (e) => {
		keyboardlayout.init();
	});
	mw.keyboardlayout = keyboardlayout;
});
