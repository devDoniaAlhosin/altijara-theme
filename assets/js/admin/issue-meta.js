/**
 * Issue CPT meta — PDF media library picker.
 */
(function ($) {
  'use strict';

  function setPdfFilename($wrap, url) {
    var $name = $wrap.find('.altijara-pdf-filename');
    if (!$name.length) return;
    if (!url) {
      $name.text('');
      return;
    }
    try {
      var path = String(url).split('?')[0];
      var base = path.split('/').pop() || path;
      $name.text(decodeURIComponent(base));
    } catch (e) {
      $name.text(url);
    }
  }

  $(document).on('click', '.altijara-pdf-field .altijara-pdf-upload', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-pdf-field');
    var $input = $wrap.find('.altijara-media-url');
    var frame = wp.media({
      title: 'Select PDF',
      button: { text: 'Use PDF' },
      multiple: false,
      library: { type: 'application/pdf' }
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var url = attachment.url || '';
      $input.val(url).trigger('change');
      setPdfFilename($wrap, url);
    });
    frame.open();
  });

  $(document).on('click', '.altijara-pdf-field .altijara-media-clear', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-pdf-field');
    $wrap.find('.altijara-media-url').val('');
    setPdfFilename($wrap, '');
  });

  $(document).on('change input', '.altijara-pdf-field .altijara-media-url', function () {
    setPdfFilename($(this).closest('.altijara-pdf-field'), $(this).val());
  });
})(jQuery);
