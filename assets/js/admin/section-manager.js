/**
 * Altijara Section Manager — AJAX picker + homepage builder (Cards/Blocks).
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
        const items = (json && json.data && json.data.items) || [];
        if (!items.length) {
          results.innerHTML =
            '<li class="altijara-picker-empty">' +
            ((cfg.i18n && cfg.i18n.noResults) || 'No results') +
            '</li>';
          return;
        }
        items.forEach((item) => {
          const li = document.createElement('li');
          li.innerHTML = '<button type="button" class="altijara-picker-pick"></button>';
          const btn = li.querySelector('button');
          btn.textContent = item.title + (item.type === 'issue' ? ' [issue]' : '');
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

  /* ——— Homepage builder ——— */

  function uniqueId(prefix) {
    return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
  }

  function openModal() {
    const modal = document.getElementById('altijara-builder-modal');
    if (modal) modal.hidden = false;
  }

  function closeModal() {
    const modal = document.getElementById('altijara-builder-modal');
    if (modal) modal.hidden = true;
  }

  function setTab(name) {
    document.querySelectorAll('.altijara-builder-tab').forEach((t) => {
      t.classList.toggle('is-active', t.getAttribute('data-tab') === name);
    });
    document.querySelectorAll('.altijara-builder-panel').forEach((p) => {
      const on = p.getAttribute('data-panel') === name;
      p.hidden = !on;
      p.classList.toggle('is-active', on);
    });
  }

  function buildCardFromTemplate(id, label, widget, variant, pairWith) {
    const tpl = document.getElementById('altijara-section-card-template');
    const list = document.getElementById('altijara-body-sections');
    if (!tpl || !list) return null;

    let html = tpl.innerHTML
      .replace(/__ID__/g, id)
      .replace(/__LABEL__/g, label || id);

    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    const card = wrap.firstElementChild;
    if (!card) return null;

    card.setAttribute('data-section-id', id);
    card.setAttribute('data-widget', widget);
    card.setAttribute('data-zone', 'body');
    card.open = true;

    const widgetInput = card.querySelector('input[name*="[widget]"]');
    if (widgetInput) widgetInput.value = widget;

    const variantSelect = card.querySelector('select[name*="[variant]"]');
    if (variantSelect && variant) variantSelect.value = variant;

    // If no variant select but we need cars_row, inject hidden.
    if (!variantSelect && variant && variant !== 'default') {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'altijara_sections[' + id + '][variant]';
      hidden.value = variant;
      card.querySelector('.altijara-section-body')?.appendChild(hidden);
    }

    const pairInput = card.querySelector('.altijara-section-pair');
    if (pairInput) pairInput.value = pairWith || '';

    const labelInput = card.querySelector('input[name*="[label]"]');
    if (labelInput) labelInput.value = label || id;

    const summaryLabel = card.querySelector('.altijara-section-summary-label');
    if (summaryLabel) summaryLabel.textContent = label || id;

    const orderItem = card.querySelector('.altijara-body-order-item');
    if (orderItem) orderItem.value = id;

    // Ensure visible checkbox checked for new items.
    const vis = card.querySelector('input[name*="[visible]"]');
    if (vis) vis.checked = true;

    list.appendChild(card);
    return card;
  }

  function addElement(btn) {
    const widget = btn.getAttribute('data-widget') || 'article_grid';
    const variant = btn.getAttribute('data-variant') || 'default';
    const label = btn.getAttribute('data-label') || widget;
    const paired = btn.getAttribute('data-paired') === '1';

    if (paired && widget === 'split_sector') {
      const idA = uniqueId('sec_split');
      const idB = uniqueId('sec_split');
      const cardA = buildCardFromTemplate(idA, label + ' A', widget, 'default', idB);
      const cardB = buildCardFromTemplate(idB, label + ' B', widget, 'default', idA);
      if (cardA && cardB) {
        closeModal();
        cardA.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      return;
    }

    const id = uniqueId('sec_' + widget.replace(/_/g, ''));
    const card = buildCardFromTemplate(id, label, widget, variant, '');
    if (card) {
      closeModal();
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  /* Drag reorder (HTML5) */
  let dragEl = null;

  function bindSortable() {
    const list = document.getElementById('altijara-body-sections');
    if (!list) return;

    list.addEventListener('dragstart', (e) => {
      const card = e.target.closest('.altijara-section-card');
      if (!card || !e.target.closest('.altijara-section-drag')) {
        e.preventDefault();
        return;
      }
      dragEl = card;
      card.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
      try {
        e.dataTransfer.setData('text/plain', card.getAttribute('data-section-id') || '');
      } catch (err) {}
    });

    list.addEventListener('dragend', () => {
      if (dragEl) dragEl.classList.remove('is-dragging');
      dragEl = null;
      list.querySelectorAll('.altijara-section-card').forEach((c) => c.classList.remove('drag-over'));
    });

    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      const over = e.target.closest('.altijara-section-card');
      if (!over || over === dragEl) return;
      list.querySelectorAll('.altijara-section-card').forEach((c) => c.classList.remove('drag-over'));
      over.classList.add('drag-over');
      const rect = over.getBoundingClientRect();
      const before = e.clientY < rect.top + rect.height / 2;
      if (before) list.insertBefore(dragEl, over);
      else list.insertBefore(dragEl, over.nextSibling);
    });

    list.querySelectorAll('.altijara-section-card').forEach((card) => {
      const handle = card.querySelector('.altijara-section-drag');
      if (handle) {
        card.setAttribute('draggable', 'true');
      }
    });
  }

  // Re-bind draggable when new cards added.
  const sortObserver = new MutationObserver(() => {
    const list = document.getElementById('altijara-body-sections');
    if (!list) return;
    list.querySelectorAll('.altijara-section-card').forEach((card) => {
      if (card.querySelector('.altijara-section-drag')) {
        card.setAttribute('draggable', 'true');
      }
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    bindSortable();
    const list = document.getElementById('altijara-body-sections');
    if (list) sortObserver.observe(list, { childList: true });
  });

  document.addEventListener('click', (e) => {
    if (e.target.closest('.altijara-builder-add-open')) {
      e.preventDefault();
      openModal();
      return;
    }
    if (e.target.closest('[data-close]')) {
      e.preventDefault();
      closeModal();
      return;
    }
    const tab = e.target.closest('.altijara-builder-tab');
    if (tab) {
      e.preventDefault();
      setTab(tab.getAttribute('data-tab'));
      return;
    }
    const elBtn = e.target.closest('.altijara-builder-element');
    if (elBtn) {
      e.preventDefault();
      addElement(elBtn);
      return;
    }
    const del = e.target.closest('.altijara-section-delete');
    if (del) {
      e.preventDefault();
      e.stopPropagation();
      const card = del.closest('.altijara-section-card');
      if (card && card.getAttribute('data-zone') === 'body') {
        if (window.confirm(cfg.i18n?.removeSection || 'Remove this section?')) {
          card.remove();
        }
      }
    }
  });
})();
