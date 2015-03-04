jQuery(document).ready( function($){
	jQuery(".footer_container").click(function(){
		SetCookie( 'hide_popup', '1', 14);
		window.location = $('#target_url').val();
	});

	// Magnific Popup
	$('.spp-ddl-popup').magnificPopup({
		type: 'iframe',
		iframe: {
			patterns: {
				sppddl: {
					index: '',
					src: '%id%'
				}
			}
		}
	});
	  
}) // global end

function SetCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=1;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString()+"; path=/";;
}

function readCookie(cookieName) {
 var re = new RegExp('[; ]'+cookieName+'=([^\\s;]*)');
 var sMatch = (' '+document.cookie).match(re);
 if (cookieName && sMatch) return unescape(sMatch[1]);
 return '';
}



