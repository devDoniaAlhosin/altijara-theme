/**
 * Featured video — media library picker (video/*).
 */
(function ($) {
  'use strict';

  function setFileLabel($box, name) {
    var $label = $box.find('.altijara-featured-video-file-label');
    if (!$label.length) return;
    if (name) {
      $label.text(name);
    } else {
      $label.html('<span class="description">No file selected</span>');
    }
  }

  $(document).on('click', '.altijara-featured-video-upload', function (e) {
    e.preventDefault();
    var $box = $(this).closest('.altijara-featured-video-field');
    var $id = $('#altijara_featured_video_id');
    var frame = wp.media({
      title: 'Select video',
      button: { text: 'Use video' },
      multiple: false,
      library: { type: 'video' }
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $id.val(attachment.id || '');
      setFileLabel($box, attachment.filename || attachment.title || attachment.url || '');
    });
    frame.open();
  });

  $(document).on('click', '.altijara-featured-video-clear-file', function (e) {
    e.preventDefault();
    var $box = $(this).closest('.altijara-featured-video-field');
    $('#altijara_featured_video_id').val('');
    setFileLabel($box, '');
  });
})(jQuery);
