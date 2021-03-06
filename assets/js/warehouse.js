//Modules.php JS
var locked;

function addHTML(html, id, replace) {
	if (locked !== false) {
		if (replace === true) document.getElementById(id).innerHTML = html;
		else document.getElementById(id).innerHTML = document.getElementById(id).innerHTML + html;
	}
}

function checkAll(form, value, name_like) {
	for (i = 0; i < form.elements.length; i++) {
		chk = form.elements[i];
		if (chk.type == 'checkbox' && chk.name.substr(0, name_like.length) == name_like) chk.checked = value;
	}
}

function switchMenu(el) {
	$(el).nextAll('table').first().toggle();
	$(el).toggleClass('switched');
}

//IE8 HTML5 tags fix
var tags = 'article|aside|footer|header|hgroup|nav|section'.split('|'),
	i = 0,
	max = tags.length;
for (; i < max; i++) {
	document.createElement(tags[i]);
}

//touchScroll, enables overflow:auto on mobile
//https://gist.github.com/chrismbarr/4107472
function touchScroll(el) {
	var startY = 0,
		startX = 0;

	el.addEventListener("touchstart", function (e) {
		startY = this.scrollTop + e.touches[0].pageY;
		startX = this.scrollLeft + e.touches[0].pageX;
	}, false);

	el.addEventListener("touchmove", function (e) {
		var tch = e.touches[0];
		if ((this.scrollTop < this.scrollHeight - this.offsetHeight && this.scrollTop + tch.pageY < startY - 5) || (this.scrollTop !== 0 && this.scrollTop + tch.pageY > startY + 5)) e.preventDefault();
		if ((this.scrollLeft < this.scrollWidth - this.offsetWidth && this.scrollLeft + tch.pageX < startX - 5) || (this.scrollLeft !== 0 && this.scrollLeft + tch.pageX > startX + 5)) e.preventDefault();
		this.scrollTop = startY - tch.pageY;
		this.scrollLeft = startX - tch.pageX;
	}, false);
}

function isTouchDevice() {
	try {
		document.createEvent("TouchEvent");
		return true;
	} catch (e) {
		return false;
	}
}
if (isTouchDevice()) $(document).bind("cbox_complete", function () {
	touchScroll(document.getElementById("cboxLoadedContent"));
});
else // add .no-touch CSS class
	document.documentElement.className += " no-touch";


function ajaxOptions(target, url, form) {
	return {
		beforeSend: function (data) {
			$('#BottomSpinner').css('visibility', 'visible');
		},
		success: function (data) {
			if (form && form.method == 'get') {

				var i,max,el,getStr,formArray;

				// Fix advanced search forms (student & user) URL > 2000 chars
				if (form.name == 'search') {
					formArray = $(form).formToArray();

					for(i=0, getStr='', max=formArray.length; i < max; i++) {
						el = formArray[i];
						// only add not empty values
						if (el.value !== '')
							getStr += '&' + el.name + '=' + el.value;
					}
				}
				else {
					getStr = $(form).formSerialize();
				}

				url += (url.indexOf('?') != -1 ? '&' : '?') + getStr;

			}

			ajaxSuccess(data, target, url);
		},
		error: function (x, st, err) {
			alert("Ajax get error\nStatus: " + st + "\nHTTP status: " + err + "\nURL: " + url);
		},
		complete: function () {
			$('#BottomSpinner').css('visibility', 'hidden');

			hideHelp();
		}
	};
}

function ajaxLink(link) {
	//will work only if in the onclick there is no error!
	var target = link.target;
	if (link.href.indexOf('#') != -1 || target == '_blank' || target == '_top') //internal/external/index.php anchor
		return true;
	if (!target) {
		if (link.href.indexOf('Modules.php') != -1) target = 'body';
		else return true;
	}

	$.ajax(link.href, ajaxOptions(target, link.href, false));
	return false;
}

function ajaxPostForm(form, submit) {
	var target = form.target;
	if (!target) target = 'body';
	if (form.action.indexOf('_ROSARIO_PDF') != -1) //print PDF
	{
		form.target = '_blank';
		form.method = 'post';
		return true;
	}

	var options = ajaxOptions(target, form.action, form);
	if (submit) $(form).ajaxSubmit(options);
	else $(form).ajaxForm(options);
	return false;
}

function ajaxSuccess(data, target, url) {
	//change URL after AJAX
	//http://stackoverflow.com/questions/5525890/how-to-change-url-after-an-ajax-request#5527095
	$('#' + target).html(data);

	if (history.pushState && target == 'body' && document.URL != url) history.pushState(null, document.title, url);

	ajaxPrepare('#' + target);
}

function ajaxPrepare(target) {
	if (scrollTop == 'Y' && target) body.scrollIntoView();

	$(target + ' form').each(function () {
		ajaxPostForm(this, false);
	});
	$(target + ' a').click(function (e) {
		return $(this).css('pointer-events') == 'none' ? e.preventDefault() : ajaxLink(this);
	});

	if (target == '#menu' && window.modname) openMenu(modname);

	if (isTouchDevice()) $('.rt').each(function (i, e) {
		touchScroll(e.tBodies[0]);
	});

	var h3 = $('#body h3.title').text().trim();
	document.title = $('#body h2').text() + (h3 ? ' | ' + h3 : '');

	submenuOffset();
}

//disable links while AJAX
$(document).ajaxStart(function () {
	$('input[type="submit"],input[type="button"],a').css('pointer-events', 'none').attr('disabled', true);
});
$(document).ajaxStop(function () {
	$('input[type="submit"],input[type="button"],a').css('pointer-events', '').attr('disabled', false);
});

//onload
window.onload = function () {
	ajaxPrepare('');

	//AJAX after browser history
	if (history.pushState) window.setTimeout(function () {
		window.addEventListener('popstate', function (e) {
			var pop = document.createElement('a');
			pop.target = 'body';
			pop.href = document.URL;
			ajaxLink(pop);
		}, false);
	}, 1);
};

//Side.php JS
var old_modcat = false;
var menu_link = document.createElement("a");
menu_link.href = "Side.php";
menu_link.target = "menu";

function openMenu(modname) {
	if (modname != 'misc/Portal.php') {
		if ((oldA = document.getElementById("selectedMenuLink"))) oldA.id = "";
		$('.wp-submenu a[href$="' + modname + '"]:first').each(function () {
			this.id = "selectedMenuLink";
		});
		//add selectedModuleLink
		if ((oldA = document.getElementById("selectedModuleLink"))) oldA.id = "";

		var modcat;
		if (modname === '') modcat = old_modcat;
		else $('#selectedMenuLink').parents('.wp-submenu').each(function () {
			modcat = this.id.replace('menu_', '');
		});

		$('a[href*="' + modcat + '"].menu-top').each(function () {
			this.id = "selectedModuleLink";
		});

		old_modcat = modcat;
	}
}

// adjust Side.php submenu bottom offset
function submenuOffset() {
	$(".adminmenu .menu-top").mouseover(function(){
		var submenu = $(this).next(".wp-submenu");
		var moveup = $("#footer").offset().top - $(this).offset().top - submenu.outerHeight();
		submenu.css("margin-top", (moveup < 0 ? moveup : 0) + 'px');
	});
}

//Bottom.php JS
function toggleHelp() {
	if ($('#footerhelp').css('display') !== 'block') showHelp();
	else hideHelp();
}

var old_modname = false;

function showHelp() {
	if (modname !== old_modname) {
		$.get("Bottom.php?modfunc=help&modname=" + modname, function (data) {
			$('#footerhelp').html(data);
			if (isTouchDevice()) touchScroll(document.getElementById('footerhelp'));
		}).fail(function () {
			alert('Error: expandHelp ' + modname);
		});
		old_modname = modname;
	}
	$('#footerhelp').show();
	$('#footer').css('height', function (i, val) {
		return parseInt(val) + parseInt($('#footerhelp').css('height'));
	});
}

function hideHelp() {
	$('#footerhelp').hide();
	$('#footer').css('height', '');
}

function expandMenu() {
	$('#menu,#menuback').toggle();
}
