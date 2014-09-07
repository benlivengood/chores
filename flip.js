function getXMLRequest() {

    var xmlhttp=false;
    /*@cc_on @*/
    /*@if (@_jscript_version >= 5)
    // JScript gives us Conditional compilation, we can cope with old IE versions.
    // and security blocked creation of the objects.
    try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
    try {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
    xmlhttp = false;
    }
    }
    @end @*/
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
	try {
	    xmlhttp = new XMLHttpRequest();
	} catch (e) {
	    xmlhttp=false;
	}
    }
    if (!xmlhttp && window.createRequest) {
	try {
	    xmlhttp = window.createRequest();
	} catch (e) {
	    xmlhttp=false;
	}
    }
    return xmlhttp;
}

function flip(aid) {
    var xmlhttp = getXMLRequest();
    if (!xmlhttp) {
	return;
    }

    xmlhttp.onerror = function() {
	return false;
    }

    xmlhttp.onreadystatechange = function() {
	if (xmlhttp.readyState == 4) {
	    if (xmlhttp.status!=200) {
		return false;
	    }
	    var ret = xmlhttp.responseText;
	    if (ret == 'Flipped.') {
		if (document.getElementById(aid).className == 'done') {
		    document.getElementById(aid).className = 'undone';
		} else {
		    document.getElementById(aid).className = 'done';
		}
	    }
	}
    }

    xmlhttp.open("POST", "flip.php", true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlhttp.send("id=" + aid);
}
