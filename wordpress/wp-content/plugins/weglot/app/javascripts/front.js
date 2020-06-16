
document.addEventListener("DOMContentLoaded", function(event) {

	function getOffset(element) {
		let top = 0, left = 0;
		do {
			top += element.offsetTop || 0;
			left += element.offsetLeft || 0;
			element = element.offsetParent;
		} while (element);

		return {
			top: top,
			left: left
		};
	}

	const button = document.querySelector(".country-selector");
	if(!button){
		return;
	}
    const h = getOffset( button ).top;
    const body = document.body,html = document.documentElement;
    const page_height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

    const position = window.getComputedStyle( button ).getPropertyValue( "position" );
    const bottom = window.getComputedStyle( button ).getPropertyValue( "bottom" );
    const top = window.getComputedStyle( button ).getPropertyValue( "top" );

    if ((position !== "fixed" && h > page_height / 2) || (position === "fixed" && h > 100)) {
        button.className += " weglot-invert";
    }
    return false;
});

