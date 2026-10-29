(function ($) {
	'use strict';

	var cfg = window.AltijaraMailBuilder || {};
	var $root = $('#altijaraMailBuilder');
	if (!$root.length) {
		return;
	}

	var $iframe = $('#altijaraMailPreviewFrame');
	var $subject = $root.find('[data-mail-subject]');
	var $status = $root.find('[data-mail-status]');
	var $frameWrap = $root.find('[data-mail-device-frame]');
	var timer = null;
	var req = null;
	var i18n = cfg.i18n || {};

	function setStatus(state, label) {
		$status.attr('data-state', state).text(label || '');
	}

	function collectEmails() {
		var data = {};
		$root.closest('form').find('[name^="altijara_emails["]').each(function () {
			var $el = $(this);
			var name = String($el.attr('name') || '');
			var match = name.match(/^altijara_emails\[([^\]]+)\]$/);
			if (!match) {
				return;
			}
			var key = match[1];
			if ($el.is(':checkbox')) {
				if ($el.is(':checked')) {
					data[key] = '1';
				}
				return;
			}
			data[key] = $el.val();
		});
		return data;
	}

	function writePreview(html, subject) {
		if (typeof subject === 'string') {
			$subject.text(subject);
		}
		var doc = $iframe[0] && $iframe[0].contentDocument;
		if (!doc) {
			$iframe.attr('srcdoc', html || '');
			return;
		}
		doc.open();
		doc.write(html || '');
		doc.close();
	}

	function refreshPreview(immediate) {
		var delay = immediate ? 0 : 320;
		window.clearTimeout(timer);
		timer = window.setTimeout(function () {
			if (req && typeof req.abort === 'function') {
				req.abort();
			}
			setStatus('updating', i18n.updating || 'Updating…');
			req = $.ajax({
				url: cfg.ajaxUrl || window.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'altijara_mail_preview',
					nonce: cfg.nonce || '',
					type: $root.attr('data-preview-type') || 'reset',
					emails: collectEmails()
				}
			})
				.done(function (res) {
					if (!res || !res.success || !res.data) {
						setStatus('error', i18n.error || 'Preview failed');
						return;
					}
					writePreview(res.data.html || '', res.data.subject || '');
					setStatus('ready', i18n.ready || 'Ready');
				})
				.fail(function (xhr, status) {
					if (status === 'abort') {
						return;
					}
					setStatus('error', i18n.error || 'Preview failed');
				});
		}, delay);
	}

	$root.on('click', '[data-mail-type]', function () {
		var type = String($(this).data('mail-type') || 'reset');
		$root.attr('data-preview-type', type);
		$root.find('[data-mail-type]').removeClass('is-active').attr('aria-selected', 'false');
		$(this).addClass('is-active').attr('aria-selected', 'true');
		$root.find('[data-mail-pane]').each(function () {
			var $pane = $(this);
			var active = String($pane.data('mail-pane')) === type;
			$pane.toggleClass('is-active', active);
			if (active) {
				$pane.removeAttr('hidden');
			} else {
				$pane.attr('hidden', 'hidden');
			}
		});
		refreshPreview(true);
	});

	$root.on('click', '[data-mail-device]', function () {
		var device = String($(this).data('mail-device') || 'desktop');
		$root.find('[data-mail-device]').removeClass('is-active').attr('aria-pressed', 'false');
		$(this).addClass('is-active').attr('aria-pressed', 'true');
		$frameWrap.attr('data-mail-device-frame', device);
	});

	$root.closest('form').on(
		'input change',
		'[name^="altijara_emails["]',
		function () {
			refreshPreview(false);
		}
	);

	// Media library updates the URL input programmatically — watch it.
	var logoInput = $root.closest('form').find('input[name="altijara_emails[logo_url]"]')[0];
	if (logoInput && typeof MutationObserver !== 'undefined') {
		var obs = new MutationObserver(function () {
			refreshPreview(false);
		});
		obs.observe(logoInput, { attributes: true, attributeFilter: ['value'] });
	}
	$root.closest('form').on('change', 'input[name="altijara_emails[logo_url]"]', function () {
		refreshPreview(true);
	});

	var seed = document.getElementById('altijaraMailPreviewSeed');
	if (seed && seed.value) {
		writePreview(seed.value, $subject.text());
		setStatus('ready', i18n.ready || 'Ready');
	} else {
		refreshPreview(true);
	}
})(jQuery);
