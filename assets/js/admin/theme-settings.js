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
      var isParent = this.hasAttribute('data-nav-parent') || this.hasAttribute('data-archives-parent');
      var on = $(this).data('tab') === tabId && !isParent;
      $(this).toggleClass('is-active', on).attr('aria-selected', on ? 'true' : 'false');
    });

    $dash.find('.altijara-nav-group').each(function () {
      var ids = String($(this).attr('data-children') || '').split(',');
      var on = ids.indexOf(tabId) !== -1;
      $(this).toggleClass('is-current', on).toggleClass('is-open', on);
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
    $wrap.find('.altijara-media-url').val('').trigger('change');
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

  function reindexShareExtra() {
    $('.altijara-share-extra-rows .altijara-share-extra-row').each(function (i) {
      $(this).find('[name]').each(function () {
        var name = $(this).attr('name');
        if (!name) return;
        $(this).attr('name', name.replace(/share_extra\[(?:\d+|__i__)\]/, 'share_extra[' + i + ']'));
      });
    });
  }

  $(document).on('click', '.altijara-share-extra-add', function (e) {
    e.preventDefault();
    var tpl = document.getElementById('altijara-share-extra-tpl');
    var $tbody = $('.altijara-share-extra-rows');
    if (!tpl || !$tbody.length) return;
    if (tpl.content) {
      $tbody.append(tpl.content.cloneNode(true));
    } else {
      $tbody.append(tpl.innerHTML);
    }
    reindexShareExtra();
  });

  $(document).on('click', '.altijara-share-extra-remove', function (e) {
    e.preventDefault();
    var $tbody = $(this).closest('.altijara-share-extra-rows');
    $(this).closest('.altijara-share-extra-row').remove();
    if ($tbody.length && !$tbody.children().length) {
      $('.altijara-share-extra-add').trigger('click');
      $tbody.find('input[type="checkbox"]').prop('checked', false);
    }
    reindexShareExtra();
  });

  function slugifyFontFamily(name) {
    var s = String(name || '')
      .toLowerCase()
      .trim()
      .replace(/['"]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    if (!s) {
      var h = 0;
      var str = String(name || '');
      var i;
      for (i = 0; i < str.length; i++) {
        h = (h << 5) - h + str.charCodeAt(i);
        h |= 0;
      }
      s = 'font-' + String(h >>> 0);
    }
    return s;
  }

  function isFontFileUrl(url) {
    return /\.(woff2?|ttf|otf)(\?|#|$)/i.test(String(url || ''));
  }

  function ensureFontRowSlug($row) {
    var family = $.trim($row.find('.altijara-custom-font-family').val() || '');
    var $slug = $row.find('.altijara-custom-font-slug');
    if (!$slug.length) {
      return '';
    }
    if (!family) {
      $slug.val('');
      return '';
    }
    var next = slugifyFontFamily(family);
    $slug.val(next);
    return next;
  }

  function reindexCustomFonts() {
    $('.altijara-custom-fonts-rows .altijara-custom-font-row').each(function (i) {
      var $row = $(this);
      ensureFontRowSlug($row);
      $row.find('[name]').each(function () {
        var name = $(this).attr('name');
        if (!name) return;
        $(this).attr(
          'name',
          name.replace(/custom_fonts\[(?:\d+|__i__)\]/, 'custom_fonts[' + i + ']')
        );
      });
    });
    syncUploadedFontSelects();
  }

  function collectUploadedFamilies() {
    var map = {};
    $('.altijara-custom-font-row').each(function () {
      var $row = $(this);
      var family = $.trim($row.find('.altijara-custom-font-family').val() || '');
      var url = $.trim($row.find('.altijara-font-file-url').val() || '');
      if (!family || !isFontFileUrl(url)) {
        return;
      }
      var slug = ensureFontRowSlug($row);
      if (!slug) {
        return;
      }
      var key = 'upload_' + slug;
      if (!map[key]) {
        map[key] = family;
      }
    });
    return map;
  }

  function syncUploadedFontSelects() {
    var families = collectUploadedFamilies();
    $('.altijara-font-role-select').each(function () {
      var $sel = $(this);
      var current = $sel.val();
      $sel.find('option').filter(function () {
        return String($(this).val() || '').indexOf('upload_') === 0;
      }).remove();
      var $custom = $sel.find('option[value="custom"]');
      Object.keys(families).forEach(function (key) {
        var label = families[key] + ' (uploaded)';
        var $opt = $('<option></option>').attr('value', key).attr('data-uploaded', '1').text(label);
        if ($custom.length) {
          $opt.insertBefore($custom);
        } else {
          $sel.append($opt);
        }
      });
      if (current && $sel.find('option[value="' + current + '"]').length) {
        $sel.val(current);
      }
    });
  }

  $(document).on('click', '.altijara-custom-font-add', function (e) {
    e.preventDefault();
    var tpl = document.getElementById('altijara-custom-font-tpl');
    var $tbody = $('.altijara-custom-fonts-rows');
    if (!tpl || !$tbody.length) return;
    if (tpl.content) {
      $tbody.append(tpl.content.cloneNode(true));
    } else {
      $tbody.append(tpl.innerHTML);
    }
    reindexCustomFonts();
  });

  $(document).on('click', '.altijara-custom-font-remove', function (e) {
    e.preventDefault();
    var $tbody = $(this).closest('.altijara-custom-fonts-rows');
    $(this).closest('.altijara-custom-font-row').remove();
    if ($tbody.length && !$tbody.children().length) {
      $('.altijara-custom-font-add').trigger('click');
    } else {
      reindexCustomFonts();
    }
  });

  $(document).on('input change', '.altijara-custom-font-family, .altijara-font-file-url', function () {
    syncUploadedFontSelects();
  });

  $(document).on('click', '.altijara-font-upload', function (e) {
    e.preventDefault();
    var $row = $(this).closest('.altijara-custom-font-row');
    var $input = $row.find('.altijara-font-file-url');
    var frame = wp.media({
      title: 'Select font file',
      button: { text: 'Use font' },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var url = attachment && attachment.url ? attachment.url : '';
      if (!isFontFileUrl(url)) {
        window.alert('Please choose a .woff2, .woff, .ttf, or .otf font file.');
        return;
      }
      $input.val(url).trigger('change');
      var $family = $row.find('.altijara-custom-font-family');
      if ($family.length && !$.trim($family.val() || '') && attachment.filename) {
        var base = String(attachment.filename).replace(/\.(woff2?|ttf|otf)$/i, '');
        base = base.replace(/[-_]+(thin|light|regular|medium|semibold|bold|extrabold|black|italic).*$/i, '');
        base = base.replace(/[-_]+/g, ' ').replace(/\b\w/g, function (c) {
          return c.toUpperCase();
        });
        if (base) {
          $family.val(base);
        }
      }
      syncUploadedFontSelects();
    });
    frame.open();
  });

  syncUploadedFontSelects();

  function parseTagChoices($picker) {
    try {
      var raw = $picker.attr('data-choices') || '';
      if (!raw) {
        var $data = $picker.find('.altijara-tag-picker__data').first();
        raw = $data.length ? $.trim($data.text() || '') : '[]';
      }
      var list = JSON.parse(raw || '[]');
      return Array.isArray(list) ? list : [];
    } catch (err) {
      return [];
    }
  }

  function selectedTagIds($picker) {
    var ids = [];
    $picker.find('.altijara-tag-picker__chip').each(function () {
      var id = parseInt($(this).attr('data-id'), 10);
      if (id > 0) {
        ids.push(id);
      }
    });
    return ids;
  }

  function showTagResults($results) {
    $results.removeAttr('hidden').prop('hidden', false).show();
    $results.closest('.altijara-tag-picker').addClass('is-open');
  }

  function hideTagResults($results) {
    $results.empty().attr('hidden', 'hidden').prop('hidden', true).hide();
    $results.closest('.altijara-tag-picker').removeClass('is-open');
  }

  function addTagChip($picker, item) {
    var id = parseInt(item.id, 10);
    if (!id || selectedTagIds($picker).indexOf(id) !== -1) {
      return;
    }
    var field = $picker.attr('data-field-name') || 'altijara_archive_full_hero[]';
    var $chip = $(
      '<span class="altijara-tag-picker__chip">' +
        '<span class="altijara-tag-picker__chip-label"></span>' +
        '<button type="button" class="altijara-tag-picker__remove" aria-label="Remove">&times;</button>' +
        '<input type="hidden" />' +
        '</span>'
    );
    $chip.attr('data-id', String(id));
    $chip.find('.altijara-tag-picker__chip-label').text(item.name || String(id));
    $chip.find('input[type="hidden"]').attr('name', field).val(String(id));
    $picker.find('.altijara-tag-picker__selected').append($chip);
  }

  function renderTagResults($picker, query) {
    var $results = $picker.find('.altijara-tag-picker__results');
    var choices = parseTagChoices($picker);
    var taken = selectedTagIds($picker);
    var q = String(query || '')
      .trim()
      .toLowerCase();
    var matches = choices
      .filter(function (item) {
        var id = parseInt(item.id, 10);
        if (!id || taken.indexOf(id) !== -1) {
          return false;
        }
        if (!q) {
          return true;
        }
        var name = String(item.name || '').toLowerCase();
        var slug = String(item.slug || '').toLowerCase();
        return name.indexOf(q) !== -1 || slug.indexOf(q) !== -1;
      })
      .slice(0, 12);

    $results.empty();
    if (!matches.length) {
      $results.append($('<li class="altijara-tag-picker__empty"></li>').text('No matches'));
      showTagResults($results);
      return;
    }
    matches.forEach(function (item) {
      var label = item.name || '';
      if (item.slug) {
        label += ' (' + item.slug + ')';
      }
      var $btn = $('<button type="button"></button>').text(label);
      $btn.on('mousedown', function (e) {
        e.preventDefault();
        addTagChip($picker, item);
        $picker.find('.altijara-tag-picker__search').val('').trigger('focus');
        hideTagResults($results);
      });
      $results.append($('<li></li>').append($btn));
    });
    showTagResults($results);
  }

  $(document).on('input focus', '.altijara-tag-picker__search', function () {
    var $picker = $(this).closest('.altijara-tag-picker');
    if ($picker.length) {
      renderTagResults($picker, this.value);
    }
  });

  $(document).on('keydown', '.altijara-tag-picker__search', function (e) {
    if (e.key === 'Escape') {
      hideTagResults($(this).closest('.altijara-tag-picker').find('.altijara-tag-picker__results'));
    }
  });

  $(document).on('click', '.altijara-tag-picker__remove', function (e) {
    e.preventDefault();
    $(this).closest('.altijara-tag-picker__chip').remove();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('.altijara-tag-picker').length) {
      hideTagResults($('.altijara-tag-picker__results'));
    }
  });
})(jQuery);
