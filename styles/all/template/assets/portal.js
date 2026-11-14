(function(window, document) {
	'use strict';

	function setCookie(name, value, expires) {
		var cookie = encodeURIComponent(name) + '=' + encodeURIComponent(String(value)) + '; path=/; SameSite=Lax';
		if (expires instanceof Date) {
			cookie += '; expires=' + expires.toUTCString();
		} else if (typeof expires === 'number' && expires > 0) {
			cookie += '; expires=' + new Date(Date.now() + expires * 86400000).toUTCString();
		}
		if (window.location.protocol === 'https:') {
			cookie += '; Secure';
		}
		document.cookie = cookie;
	}

	function getCookie(name) {
		var encodedName = encodeURIComponent(name) + '=';
		var cookies = document.cookie ? document.cookie.split('; ') : [];
		for (var i = 0; i < cookies.length; i++) {
			if (cookies[i].indexOf(encodedName) === 0) {
				return decodeURIComponent(cookies[i].substring(encodedName.length));
			}
		}
		return null;
	}

	function switchVisibility(id) {
		var element = document.getElementById(id);
		if (!element) {
			return 0;
		}
		var hidden = element.style.display === 'none';
		element.style.display = hidden ? '' : 'none';
		return hidden ? 1 : 2;
	}

	window.SetCookie = setCookie;
	window.Set_Cookie = setCookie;
	window.GetCookie = getCookie;
	window.Get_Cookie = getCookie;
	window.Delete_Cookie = function(name) { setCookie(name, '', new Date(0)); };
	window.switch_visibility = switchVisibility;
	window.ShowHide = function(id1, id2, cookieName) {
		var state = switchVisibility(id1);
		if (id2) {
			switchVisibility(id2);
		}
		if (cookieName) {
			setCookie(cookieName, state, 90);
		}
	};

	window.PortalArrange = {
		init: function(cookieName) {
			var containerNames = ['left', 'center', 'right'];
			var containers = containerNames.map(function(name) {
				return document.getElementById(name);
			}).filter(Boolean);
			var dragged = null;

			function blocks(container) {
				return Array.prototype.filter.call(container.children, function(child) {
					return /^BLOCK_[LCR]_\d+$/.test(child.id);
				});
			}

			function save() {
				containerNames.forEach(function(name) {
					var container = document.getElementById(name);
					var ids = container ? blocks(container).map(function(block) {
						var match = block.id.match(/(\d+)$/);
						return match ? match[1] : '';
					}).filter(Boolean) : [];
					setCookie(cookieName + '_sgp_' + name, ids.join(','), 90);
				});
				setCookie(cookieName + '_sgp_block_cache', '0', 1);
			}

			containers.forEach(function(container) {
				blocks(container).forEach(function(block) {
					block.draggable = true;
					block.addEventListener('dragstart', function(event) {
						dragged = block;
						block.classList.add('portal-block-dragging');
						event.dataTransfer.effectAllowed = 'move';
						event.dataTransfer.setData('text/plain', block.id);
					});
					block.addEventListener('dragend', function() {
						block.classList.remove('portal-block-dragging');
						dragged = null;
						save();
					});
				});

				container.addEventListener('dragover', function(event) {
					if (!dragged) {
						return;
					}
					event.preventDefault();
					var target = event.target.closest('[id^="BLOCK_"]');
					if (!target || target === dragged || target.parentNode !== container) {
						container.appendChild(dragged);
						return;
					}
					var box = target.getBoundingClientRect();
					container.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? target : target.nextSibling);
				});

				container.addEventListener('drop', function(event) {
					event.preventDefault();
					save();
				});
			});
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('.hidden_part').forEach(function(element) {
			element.hidden = true;
		});
		document.querySelectorAll('.bg1a[id^="T_"]').forEach(function(trigger) {
			trigger.addEventListener('click', function() {
				var content = document.getElementById('T' + trigger.id);
				if (content) {
					content.hidden = !content.hidden;
				}
			});
		});
	});
}(window, document));
