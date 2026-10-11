/**
 * Altijara admin — media library picker for theme settings.
 */
(function ($) {
  'use strict';

  $(document).on('click', '.altijara-media-upload', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-media-field');
    var $input = $wrap.find('.altijara-media-url');
    var frame = wp.media({
      title: 'اختر صورة',
      button: { text: 'استخدام الصورة' },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $input.val(attachment.url).trigger('change');
      var $img = $wrap.find('img');
      if (!$img.length) {
        $wrap.append('<p><img src="" alt="" style="max-height:48px;margin-top:8px;"></p>');
        $img = $wrap.find('img');
      }
      $img.attr('src', attachment.url);
    });
    frame.open();
  });

  $(document).on('click', '.altijara-media-clear', function (e) {
    e.preventDefault();
    var $wrap = $(this).closest('.altijara-media-field');
    $wrap.find('.altijara-media-url').val('');
    $wrap.find('img').remove();
  });
})(jQuery);
