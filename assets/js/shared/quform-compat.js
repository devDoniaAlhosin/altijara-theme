/**
 * Strip Quform float / pixel-column inline styles inside theme shortcode wrappers.
 * Layout itself lives in CSS (static-pages.css + footer compat). This script only
 * removes Quform's destructive inline rules and fixed-size classes so CSS can win.
 */
(function () {
	'use strict';

	var ROOT = '.static-form--shortcode';
	var STRIP = [
		'width',
		'max-width',
		'min-width',
		'float',
		'clear',
		'height',
		'left',
		'right',
		'top',
		'bottom',
		'inset',
		'position',
		'display',
		'margin',
		'margin-left',
		'margin-right',
		'margin-top',
		'margin-bottom',
		'padding',
		'padding-left',
		'padding-right',
		'grid-column',
		'grid-template-columns',
		'grid-template-rows',
		'grid-auto-flow',
		'flex',
		'flex-direction',
		'flex-wrap',
		'flex-basis',
		'flex-grow',
		'flex-shrink',
		'align-items',
		'justify-content',
		'justify-self',
		'align-self',
		'gap',
		'box-sizing'
	];
	var BLOCKED = {
		width: 1,
		'max-width': 1,
		'min-width': 1,
		float: 1,
		clear: 1,
		height: 1,
		left: 1,
		right: 1,
		top: 1,
		bottom: 1,
		position: 1,
		display: 1,
		margin: 1,
		'margin-left': 1,
		'margin-right': 1,
		padding: 1,
		'padding-left': 1,
		'padding-right': 1
	};
	var fixing = false;

	function roots(scope) {
		var base = scope && scope.nodeType === 1 ? scope : document;
		if (base.classList && base.classList.contains('static-form--shortcode')) {
			return [base];
		}
		var closest = base.closest ? base.closest(ROOT) : null;
		if (closest) {
			return [closest];
		}
		if (base.querySelectorAll) {
			var nested = base.querySelectorAll(ROOT);
			if (nested && nested.length) {
				return Array.prototype.slice.call(nested);
			}
		}
		return Array.prototype.slice.call(document.querySelectorAll(ROOT));
	}

	function stripEl(el) {
		if (!el || el.nodeType !== 1) return;
		el.classList.remove('quform-element-row-size-fixed');
		el.classList.remove('quform-responsive');
		for (var i = 0; i < STRIP.length; i++) {
			el.style.removeProperty(STRIP[i]);
		}
		if (!(el.getAttribute('style') || '').trim()) {
			el.removeAttribute('style');
		}
	}

	function reset(scope) {
		if (fixing) return;
		var list = roots(scope);
		if (!list.length) return;

		fixing = true;
		try {
			for (var r = 0; r < list.length; r++) {
				var root = list[r];
				var styled = root.querySelectorAll('[style]');
				for (var i = 0; i < styled.length; i++) {
					stripEl(styled[i]);
				}
				var fixed = root.querySelectorAll('.quform-element-row-size-fixed, .quform-responsive');
				for (var f = 0; f < fixed.length; f++) {
					fixed[f].classList.remove('quform-element-row-size-fixed');
					fixed[f].classList.remove('quform-responsive');
				}
			}
		} finally {
			fixing = false;
		}
	}

	function schedule(scope) {
		reset(scope);
		if (window.requestAnimationFrame) {
			window.requestAnimationFrame(function () { reset(scope); });
		}
		[0, 50, 200, 600, 1500].forEach(function (ms) {
			window.setTimeout(function () { reset(scope); }, ms);
		});
	}

	function guardJquery() {
		if (!window.jQuery || window.jQuery.fn._altijaraQuformGuarded) return;
		var $ = window.jQuery;
		var orig = $.fn.css;
		$.fn.css = function (prop, val) {
			var hit = false;
			try {
				hit = this.closest && this.closest(ROOT).length > 0;
			} catch (e) {
				hit = false;
			}
			if (hit) {
				if (typeof prop === 'string' && BLOCKED[prop]) {
					return val === undefined ? orig.apply(this, arguments) : this;
				}
				if (prop && typeof prop === 'object') {
					var clean = {};
					var blocked = false;
					for (var k in prop) {
						if (!Object.prototype.hasOwnProperty.call(prop, k)) continue;
						if (BLOCKED[k]) {
							blocked = true;
							continue;
						}
						clean[k] = prop[k];
					}
					if (blocked) {
						var ret = Object.keys(clean).length ? orig.call(this, clean) : this;
						var root = this.closest(ROOT).get(0);
						window.setTimeout(function () { reset(root || document); }, 0);
						return ret;
					}
				}
			}
			return orig.apply(this, arguments);
		};
		$.fn._altijaraQuformGuarded = true;
	}

	schedule();
	document.addEventListener('DOMContentLoaded', function () {
		guardJquery();
		schedule();
	});
	window.addEventListener('load', function () {
		guardJquery();
		schedule();
	});
	window.addEventListener('resize', function () { reset(); });

	if (window.jQuery) {
		guardJquery();
		window.jQuery(function ($) {
			guardJquery();
			schedule();
			$(document).on('quformReady quform.success quform:ready', function (e) {
				schedule((e && e.target) || document);
			});
		});
	}

	if (window.MutationObserver) {
		var t = null;
		var mo = new MutationObserver(function (records) {
			if (fixing) return;
			var needs = false;
			for (var i = 0; i < records.length; i++) {
				var rec = records[i];
				if (rec.type === 'childList' && rec.addedNodes.length) {
					needs = true;
					break;
				}
				if (rec.type === 'attributes' && rec.attributeName === 'style' && rec.target && rec.target.style) {
					var w = rec.target.style.getPropertyValue('width');
					var fl = rec.target.style.getPropertyValue('float');
					var disp = rec.target.style.getPropertyValue('display');
					if ((fl && fl !== 'none') || (w && w.indexOf('px') !== -1) || disp === 'table' || disp === 'table-cell') {
						needs = true;
						break;
					}
				}
				if (rec.type === 'attributes' && rec.attributeName === 'class') {
					var cn = String((rec.target && rec.target.className) || '');
					if (cn.indexOf('row-size-fixed') !== -1 || cn.indexOf('quform-responsive') !== -1) {
						needs = true;
						break;
					}
				}
			}
			if (!needs) return;
			if (t) window.clearTimeout(t);
			t = window.setTimeout(function () {
				t = null;
				reset();
			}, 20);
		});
		function watch() {
			roots().forEach(function (el) {
				mo.observe(el, { attributes: true, attributeFilter: ['style', 'class'], childList: true, subtree: true });
			});
		}
		watch();
		document.addEventListener('DOMContentLoaded', watch);
		window.addEventListener('load', watch);
	}
})();
