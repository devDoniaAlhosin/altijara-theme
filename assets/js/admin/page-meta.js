/**
 * Altijara page meta — accordion, repeater, media picker, editor notice.
 */
(function ($) {
  'use strict';

  var cfg = window.altijaraPageMeta || {};
  var itemLabel = cfg.itemLabel || 'Item';

  function reindex(repeater) {
    var rows = repeater.querySelectorAll('.altijara-pm-row');
    rows.forEach(function (row, i) {
      row.setAttribute('data-index', String(i));
      var strong = row.querySelector('.altijara-pm-row-title');
      if (strong) {
        strong.textContent = itemLabel + ' ' + (i + 1);
      }
      row.querySelectorAll('input, textarea, select').forEach(function (el) {
        if (!el.name) return;
        el.name = el.name.replace(/\[\d+\]/, '[' + i + ']');
      });
    });
  }

  function insertEditorNotice() {
    if (!cfg.isUtility || !cfg.notice) return;
    if (document.querySelector('.altijara-pm-editor-notice')) return;

    var notice = document.createElement('div');
    notice.className = 'altijara-pm-editor-notice';
    notice.setAttribute('role', 'status');
    notice.textContent = cfg.notice;

    var targets = [
      document.querySelector('.edit-post-visual-editor'),
      document.querySelector('.editor-styles-wrapper'),
      document.querySelector('#postdivrich'),
      document.querySelector('.block-editor'),
    ];
    for (var i = 0; i < targets.length; i++) {
      if (targets[i]) {
        targets[i].parentNode.insertBefore(notice, targets[i]);
        return;
      }
    }
  }

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('.altijara-pm-section__toggle');
    if (toggle) {
      e.preventDefault();
      var section = toggle.closest('.altijara-pm-section');
      if (!section) return;
      var open = section.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      return;
    }

    var addBtn = e.target.closest('.altijara-pm-add');
    if (addBtn) {
      e.preventDefault();
      var repeater = addBtn.closest('.altijara-pm-repeater');
      if (!repeater) return;
      var tpl = repeater.querySelector('.altijara-pm-row-tpl');
      var list = repeater.querySelector('.altijara-pm-repeater-rows');
      if (!tpl || !list) return;
      var i = list.querySelectorAll('.altijara-pm-row').length;
      var html = tpl.innerHTML
        .replace(/__i__/g, String(i))
        .replace(/__n__/g, String(i + 1));
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      list.appendChild(wrap.firstElementChild);
      reindex(repeater);
      return;
    }

    var removeBtn = e.target.closest('.altijara-pm-remove');
    if (removeBtn) {
      e.preventDefault();
      var row = removeBtn.closest('.altijara-pm-row');
      var repeaterRm = removeBtn.closest('.altijara-pm-repeater');
      if (!row || !repeaterRm) return;
      var listRm = repeaterRm.querySelector('.altijara-pm-repeater-rows');
      if (listRm && listRm.querySelectorAll('.altijara-pm-row').length <= 1) {
        row.querySelectorAll('input, textarea').forEach(function (el) {
          el.value = '';
        });
        row.querySelectorAll('select').forEach(function (el) {
          if (el.options.length) el.selectedIndex = 0;
        });
        return;
      }
      row.remove();
      reindex(repeaterRm);
      return;
    }

    var upBtn = e.target.closest('.altijara-pm-move-up');
    if (upBtn) {
      e.preventDefault();
      var rowUp = upBtn.closest('.altijara-pm-row');
      var repeaterUp = upBtn.closest('.altijara-pm-repeater');
      if (!rowUp || !repeaterUp || !rowUp.previousElementSibling) return;
      rowUp.parentNode.insertBefore(rowUp, rowUp.previousElementSibling);
      reindex(repeaterUp);
      return;
    }

    var downBtn = e.target.closest('.altijara-pm-move-down');
    if (downBtn) {
      e.preventDefault();
      var rowDown = downBtn.closest('.altijara-pm-row');
      var repeaterDown = downBtn.closest('.altijara-pm-repeater');
      if (!rowDown || !repeaterDown || !rowDown.nextElementSibling) return;
      rowDown.parentNode.insertBefore(rowDown.nextElementSibling, rowDown);
      reindex(repeaterDown);
    }
  });

  $(document).on('click', '.altijara-pm-media-upload', function (e) {
    e.preventDefault();
    if (typeof wp === 'undefined' || !wp.media) return;
    var $wrap = $(this).closest('.altijara-pm-media-field');
    var $input = $wrap.find('.altijara-pm-media-url');
    var frame = wp.media({
      title: 'Select file',
      button: { text: 'Use file' },
      multiple: false,
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $input.val(attachment.url).trigger('change');
    });
    frame.open();
  });

  $(document).on('click', '.altijara-pm-media-clear', function (e) {
    e.preventDefault();
    $(this).closest('.altijara-pm-media-field').find('.altijara-pm-media-url').val('');
  });

  function bootNotice() {
    insertEditorNotice();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootNotice);
  } else {
    bootNotice();
  }
  // Block editor mounts late.
  setTimeout(bootNotice, 600);
  setTimeout(bootNotice, 1600);
})(jQuery);
