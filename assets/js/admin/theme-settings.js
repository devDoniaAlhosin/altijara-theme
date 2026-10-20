/**
 * Altijara admin — media picker + SPA tab switching.
 */
(function ($) {
  'use strict';

  function switchTab(tabId, pushUrl) {
    var $dash = $('#altijara-theme-dashboard');
    if (!$dash.length || !tabId) {
      return;
    }

    $dash.attr('data-active-tab', tabId);

    $dash.find('.altijara-nav-item').each(function () {
      var on = $(this).data('tab') === tabId;
      $(this).toggleClass('is-active', on).attr('aria-selected', on ? 'true' : 'false');
    });

    $dash.find('.altijara-panel').each(function () {
      var on = $(this).data('tab') === tabId;
      $(this).toggleClass('is-active', on);
      if (on) {
        this.removeAttribute('hidden');
      } else {
        this.setAttribute('hidden', 'hidden');
      }
    });

    if (pushUrl) {
      var base = $dash.data('base-url') || window.location.href.split('&tab=')[0].split('?tab=')[0];
      var url = base + (base.indexOf('?') >= 0 ? '&' : '?') + 'tab=' + encodeURIComponent(tabId);
      if (window.history && window.history.replaceState) {
        window.history.replaceState({ altijaraTab: tabId }, '', url);
      }
    }
  }

  $(document).on('click', '.altijara-nav-item', function (e) {
    e.preventDefault();
    switchTab($(this).data('tab'), true);
  });

  $(window).on('popstate', function () {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab') || $('#altijara-theme-dashboard').data('active-tab') || 'general';
    switchTab(tab, false);
  });

  function updateMediaPreview($wrap, url) {
    var $preview = $wrap.find('.altijara-media-preview');
    if (!$preview.length) {
      return;
    }
    if (url) {
      $preview.addClass('has-image').html('<img src="' + url + '" alt="">');
    } else {
      $preview.removeClass('has-image').html('<span class="altijara-media-preview__empty">No image selected</span>');
    }
  }

  $(document).on('click', '.altijara-media-upload', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-media-field');
    var $input = $wrap.find('.altijara-media-url');
    var frame = wp.media({
      title: 'Select image',
      button: { text: 'Use image' },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $input.val(attachment.url).trigger('change');
      updateMediaPreview($wrap, attachment.url);
    });
    frame.open();
  });

  $(document).on('click', '.altijara-media-clear', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-media-field');
    $wrap.find('.altijara-media-url').val('');
    updateMediaPreview($wrap, '');
  });

  $(document).on('change input', '.altijara-media-url', function () {
    updateMediaPreview($(this).closest('.altijara-media-field'), $(this).val());
  });

  function syncColorUI(inputId, hex) {
    var picker = $('.altijara-color-picker[data-target="' + inputId + '"]');
    var swatch = $('.altijara-color-swatch[data-swatch-for="' + inputId + '"]');
    if (picker.length && /^#[0-9A-Fa-f]{6}$/.test(hex)) {
      picker.val(hex);
    }
    if (swatch.length) {
      swatch.css('background', /^#[0-9A-Fa-f]{6}$/.test(hex) ? hex : 'transparent');
    }
  }

  $(document).on('input', '.altijara-color-picker', function () {
    var target = $(this).data('target');
    if (target) {
      $('#' + target).val(this.value);
      syncColorUI(target, this.value);
    }
  });

  $(document).on('change input', '.altijara-color-text', function () {
    syncColorUI(this.id, this.value);
  });
})(jQuery);
