'use strict';
(function(document) {
	
	appendHtmlToElement(document.head, WP_Plugin_Deferrer_Localize.styles);
	appendHtmlToElement(document.body, WP_Plugin_Deferrer_Localize.scripts);

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
				element.appendChild(node);
			}

			if (tagName === "script") {
				var script = document.createElement('script'); 
				script.type = 'text/javascript'; 
				script.async = true;
			    script.src = node.src;
			    element.appendChild(script);
			}
		}

		div.remove();
	}
})(window.document);