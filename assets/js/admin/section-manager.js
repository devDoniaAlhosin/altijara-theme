/**
 * Altijara Section Manager — AJAX post/issue picker.
 */
(function () {
  'use strict';

  const cfg = window.AltijaraAdmin || {};
  const ajaxUrl = cfg.ajaxUrl || '';
  const nonce = cfg.nonce || '';

  function parseIds(value) {
    return String(value || '')
      .split(/[\s,]+/)
      .map((n) => parseInt(n, 10))
      .filter((n) => n > 0);
  }

  function writeIds(hidden, ids) {
    hidden.value = ids.join(',');
  }

  function addChip(picker, id, title) {
    const list = picker.querySelector('.altijara-picker-selected');
    const hidden = picker.querySelector('.altijara-picker-ids');
    if (!list || !hidden) return;
    const ids = parseIds(hidden.value);
    if (ids.includes(id)) return;
    ids.push(id);
    writeIds(hidden, ids);
    const li = document.createElement('li');
    li.dataset.id = String(id);
    li.innerHTML =
      '<span></span><button type="button" class="button-link-delete altijara-picker-remove">&times;</button>';
    li.querySelector('span').textContent = title || '#' + id;
    list.appendChild(li);
  }

  function removeChip(picker, id) {
    const hidden = picker.querySelector('.altijara-picker-ids');
    if (!hidden) return;
    const ids = parseIds(hidden.value).filter((n) => n !== id);
    writeIds(hidden, ids);
    const li = picker.querySelector('.altijara-picker-selected li[data-id="' + id + '"]');
    if (li) li.remove();
  }

  let timer = null;

  function search(picker, q) {
    const results = picker.querySelector('.altijara-picker-results');
    if (!results || !ajaxUrl) return;
    const postType = picker.getAttribute('data-post-type') || 'post';
    const url =
      ajaxUrl +
      '?action=altijara_search_posts&nonce=' +
      encodeURIComponent(nonce) +
      '&q=' +
      encodeURIComponent(q) +
      '&post_type=' +
      encodeURIComponent(postType);

    results.hidden = false;
    results.innerHTML = '<li class="altijara-picker-loading">…</li>';

    fetch(url, { credentials: 'same-origin' })
      .then((r) => r.json())
      .then((json) => {
        results.innerHTML = '';
        const items = json?.data?.items || [];
        if (!items.length) {
          results.innerHTML =
            '<li class="altijara-picker-empty">' +
            (cfg.i18n?.noResults || 'لا نتائج') +
            '</li>';
          return;
        }
        items.forEach((item) => {
          const li = document.createElement('li');
          li.innerHTML = '<button type="button" class="altijara-picker-pick"></button>';
          const btn = li.querySelector('button');
          btn.textContent = item.title + (item.type === 'issue' ? ' [عدد]' : '');
          btn.addEventListener('click', () => {
            addChip(picker, item.id, item.title);
            results.hidden = true;
            const searchInput = picker.querySelector('.altijara-picker-search');
            if (searchInput) searchInput.value = '';
          });
          results.appendChild(li);
        });
      })
      .catch(() => {
        results.innerHTML = '<li class="altijara-picker-empty">Error</li>';
      });
  }

  document.addEventListener('input', (e) => {
    const input = e.target.closest('.altijara-picker-search');
    if (!input) return;
    const picker = input.closest('.altijara-post-picker');
    if (!picker) return;
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) {
      const results = picker.querySelector('.altijara-picker-results');
      if (results) results.hidden = true;
      return;
    }
    timer = setTimeout(() => search(picker, q), 280);
  });

  document.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.altijara-picker-remove');
    if (removeBtn) {
      const li = removeBtn.closest('li[data-id]');
      const picker = removeBtn.closest('.altijara-post-picker');
      if (li && picker) {
        removeChip(picker, parseInt(li.dataset.id, 10));
      }
      return;
    }
    if (!e.target.closest('.altijara-post-picker')) {
      document.querySelectorAll('.altijara-picker-results').forEach((el) => {
        el.hidden = true;
      });
    }
  });
})();
