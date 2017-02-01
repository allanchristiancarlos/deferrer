'use strict';
(function(document) {
	
	appendHtmlToElement(document.head, WP_Plugin_Deferrer_Localize.styles);

	setTimeout(function() {
		appendHtmlToElement(document.body, WP_Plugin_Deferrer_Localize.scripts);
	}, 300);

	function appendHtmlToElement(element, html) {
		var div = document.createElement('div');
		div.innerHTML = html;

		if (div.childNodes.length === 0) {
			return;
		}

		for (var i = 0; i < div.childNodes.length; i++) {
			var node = div.childNodes[i];
			var tagName = node.tagName || "";
			tagName = tagName.toLowerCase();

			if (tagName === "link") {
			    console.log(node);
				element.appendChild(node);
			}

			if (tagName === "script") {
				var script = document.createElement('script'); 
				script.type = 'text/javascript'; 
			    script.src = node.src;
			    script.async = false;
			    element.appendChild(script);
			}
		}

		div.remove();
	}

})(window.document);