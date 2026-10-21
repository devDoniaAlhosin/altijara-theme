/**
 * Issues archive — carousel scroll (SSR cards).
 */
(function () {
  'use strict';

  function scrollTrack(trackId, step) {
    var el = document.getElementById(trackId);
    if (!el) return;
    el.scrollBy({ left: step, behavior: 'smooth' });
  }

  // Legacy inline handlers from prototype.
  window.scrollCarousel = function (trackId, step) {
    scrollTrack(trackId, step);
  };

  document.addEventListener('click', function (e) {
    var scrollBtn = e.target.closest('[data-scroll-track]');
    if (scrollBtn) {
      e.preventDefault();
      scrollTrack(
        scrollBtn.getAttribute('data-scroll-track'),
        parseInt(scrollBtn.getAttribute('data-scroll-step') || '360', 10)
      );
    }
  });

  // Drag-to-scroll carousels.
  document.querySelectorAll('.issues-carousel-track').forEach(function (track) {
    var down = false;
    var startX = 0;
    var scrollLeft = 0;
    track.addEventListener('mousedown', function (e) {
      down = true;
      track.classList.add('is-dragging');
      startX = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
    });
    track.addEventListener('mouseleave', function () {
      down = false;
      track.classList.remove('is-dragging');
    });
    track.addEventListener('mouseup', function () {
      down = false;
      track.classList.remove('is-dragging');
    });
    track.addEventListener('mousemove', function (e) {
      if (!down) return;
      e.preventDefault();
      var x = e.pageX - track.offsetLeft;
      track.scrollLeft = scrollLeft - (x - startX);
    });
  });
})();
