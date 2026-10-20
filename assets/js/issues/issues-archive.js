/**
 * Issues archive — carousel scroll + modal (SSR cards).
 */
(function () {
  'use strict';

  var issuesById = {};
  try {
    var raw = window.AltijaraIssuesPayload || [];
    raw.forEach(function (item) {
      if (item && item.id) issuesById[String(item.id)] = item;
    });
  } catch (e) {}

  function scrollTrack(trackId, step) {
    var el = document.getElementById(trackId);
    if (!el) return;
    el.scrollBy({ left: step, behavior: 'smooth' });
  }

  // Legacy inline handlers from prototype.
  window.scrollCarousel = function (trackId, step) {
    scrollTrack(trackId, step);
  };

  function openModal(id) {
    var data = issuesById[String(id)];
    if (!data) return;
    var backdrop = document.getElementById('issueModalBackdrop');
    if (!backdrop) return;
    var img = document.getElementById('modalCoverImg');
    var badge = document.getElementById('modalBadge');
    var tag = document.getElementById('modalTag');
    var date = document.getElementById('modalDate');
    var title = document.getElementById('modalTitle');
    var desc = document.getElementById('modalDesc');
    var readBtn = document.getElementById('modalReadBtn');
    var pdfBtn = document.getElementById('modalPdfBtn');
    if (img) {
      img.src = data.cover || '';
      img.alt = data.title || '';
    }
    if (badge) badge.textContent = data.caption || '';
    if (tag) tag.textContent = data.tag || '';
    if (date) date.textContent = data.date || '';
    if (title) title.textContent = data.title || '';
    if (desc) desc.textContent = data.excerpt || '';
    if (readBtn) readBtn.href = data.flipbook || data.link || data.permalink || '#';
    if (pdfBtn) {
      if (data.pdfLink) {
        pdfBtn.hidden = false;
        pdfBtn.href = data.pdfLink;
      } else {
        pdfBtn.hidden = true;
      }
    }
    backdrop.hidden = false;
    backdrop.classList.add('is-open');
  }

  function closeModal() {
    var backdrop = document.getElementById('issueModalBackdrop');
    if (!backdrop) return;
    backdrop.hidden = true;
    backdrop.classList.remove('is-open');
  }

  window.closeIssueModal = closeModal;
  window.openIssueModal = function (num) {
    // Prefer id lookup; fall back to number match.
    var found = null;
    Object.keys(issuesById).forEach(function (k) {
      var it = issuesById[k];
      if (String(it.id) === String(num) || String(it.number) === String(num)) found = it;
    });
    if (found) openModal(found.id);
  };

  document.addEventListener('click', function (e) {
    var scrollBtn = e.target.closest('[data-scroll-track]');
    if (scrollBtn) {
      e.preventDefault();
      scrollTrack(
        scrollBtn.getAttribute('data-scroll-track'),
        parseInt(scrollBtn.getAttribute('data-scroll-step') || '360', 10)
      );
      return;
    }
    var openBtn = e.target.closest('[data-open-issue]');
    if (openBtn) {
      e.preventDefault();
      openModal(openBtn.getAttribute('data-open-issue'));
      return;
    }
    if (e.target.id === 'issueModalClose' || e.target.id === 'issueModalBackdrop') {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
})();
