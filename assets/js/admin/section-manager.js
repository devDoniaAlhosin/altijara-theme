/**
 * Altijara Section Manager — AJAX picker + multi-context layout builder.
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

  function readControl(root, selector) {
    const el = (root || document).querySelector(selector);
    if (!el) return 0;
    const n = parseInt(el.value, 10);
    return Number.isFinite(n) ? n : 0;
  }

  function cardCount(card) {
    if (!card) return 0;
    return readControl(card, 'input[name$="[count]"], select[name$="[count]"]');
  }

  function pickerMax(picker) {
    const card = picker.closest('.altijara-section-card');
    const widget = card ? card.getAttribute('data-widget') || '' : '';
    if (widget === 'cat_archive_featured') {
      const n = cardCount(card);
      return Math.max(1, n || 8);
    }
    if (widget === 'cat_archive_news') {
      const n = cardCount(card);
      return Math.max(1, n || 10);
    }
    if (widget === 'issues_lead') return 1;
    if (widget === 'cat_archive_hero') {
      const side = Math.max(0, cardCount(card));
      return Math.max(1, 1 + side);
    }
    const scope = card || picker.parentElement;
    const countEl = scope && scope.querySelector('input[name$="[count]"], select[name$="[count]"]');
    if (!countEl) return 0;
    const n = parseInt(countEl.value, 10);
    return Number.isFinite(n) && n > 0 ? n : 0;
  }

  function pickerNote(picker, max, count) {
    let note = picker.querySelector('.altijara-picker-limit');
    if (!note) {
      note = document.createElement('p');
      note.className = 'description altijara-picker-limit';
      const label = picker.querySelector('label');
      if (label && label.nextSibling) picker.insertBefore(note, label.nextSibling);
      else picker.appendChild(note);
    }
    if (!max) {
      note.textContent = '';
      return;
    }
    note.textContent = count + ' / ' + max;
    note.classList.toggle('is-full', count >= max);
  }

  function trimPicker(picker) {
    const hidden = picker.querySelector('.altijara-picker-ids');
    const list = picker.querySelector('.altijara-picker-selected');
    if (!hidden || !list) return;
    const max = pickerMax(picker);
    let ids = parseIds(hidden.value);
    if (max > 0 && ids.length > max) {
      ids = ids.slice(0, max);
      writeIds(hidden, ids);
      const keep = new Set(ids);
      list.querySelectorAll('li[data-id]').forEach((li) => {
        if (!keep.has(parseInt(li.dataset.id, 10))) li.remove();
      });
    }
    pickerNote(picker, max, ids.length);
  }

  function refreshPickerLimits(root) {
    (root || document).querySelectorAll('.altijara-post-picker').forEach(trimPicker);
  }

  function addChip(picker, id, title) {
    const list = picker.querySelector('.altijara-picker-selected');
    const hidden = picker.querySelector('.altijara-picker-ids');
    if (!list || !hidden) return;
    const ids = parseIds(hidden.value);
    if (ids.includes(id)) return;
    const max = pickerMax(picker);
    if (max > 0 && ids.length >= max) {
      pickerNote(picker, max, ids.length);
      return;
    }
    ids.push(id);
    writeIds(hidden, ids);
    const li = document.createElement('li');
    li.dataset.id = String(id);
    li.innerHTML =
      '<span></span><button type="button" class="button-link-delete altijara-picker-remove">&times;</button>';
    li.querySelector('span').textContent = title || '#' + id;
    list.appendChild(li);
    pickerNote(picker, max, ids.length);
  }

  function removeChip(picker, id) {
    const hidden = picker.querySelector('.altijara-picker-ids');
    if (!hidden) return;
    const ids = parseIds(hidden.value).filter((n) => n !== id);
    writeIds(hidden, ids);
    const li = picker.querySelector('.altijara-picker-selected li[data-id="' + id + '"]');
    if (li) li.remove();
    pickerNote(picker, pickerMax(picker), ids.length);
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
    const isId = /^\d+$/.test(q);
    if ((!isId && q.length < 2) || (isId && q.length < 1)) {
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

  /* ——— Layout builder (multi-instance) ——— */

  function uniqueId(prefix) {
    return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
  }

  function getBuilder(el) {
    return el ? el.closest('.altijara-layout-builder') : null;
  }

  function openModal(builder) {
    if (!builder) return;
    const mid = builder.getAttribute('data-modal-id');
    const modal = mid ? document.getElementById(mid) : null;
    if (modal) modal.hidden = false;
  }

  function closeModal(builderOrModal) {
    let modal = null;
    if (builderOrModal && builderOrModal.classList && builderOrModal.classList.contains('altijara-builder-modal')) {
      modal = builderOrModal;
    } else if (builderOrModal) {
      const mid = builderOrModal.getAttribute('data-modal-id');
      modal = mid ? document.getElementById(mid) : null;
    }
    if (modal) modal.hidden = true;
  }

  function setTab(modal, name) {
    if (!modal) return;
    modal.querySelectorAll('.altijara-builder-tab').forEach((t) => {
      t.classList.toggle('is-active', t.getAttribute('data-tab') === name);
    });
    modal.querySelectorAll('.altijara-builder-panel').forEach((p) => {
      const on = p.getAttribute('data-panel') === name;
      p.hidden = !on;
      p.classList.toggle('is-active', on);
    });
  }

  function shapeKey(widget, variant, paired) {
    widget = widget || 'article_grid';
    variant = variant || 'default';
    const pairedFlag = paired === true || paired === '1' || paired === 'tech_most_read';
    if (widget === 'article_grid' && variant === 'cars_row' && pairedFlag) return 'card-row-pair';
    if (widget === 'article_grid' && variant === 'cars_row') return 'card-row';
    if (widget === 'split_sector' && pairedFlag) return 'split-pair';
    const map = {
      article_grid: 'grid-4',
      split_sector: 'sector',
      article_list: 'list',
      lead_stack: 'lead-stack',
      most_read: 'ranked',
      chamber_column: 'chamber',
      hero_featured: 'hero-text',
      magazine_slider: 'cover',
      issues_lead: 'issue-lead',
      issues_carousel: 'carousel',
      cat_archive_hero: 'chamber',
      cat_archive_featured: 'grid-4',
      cat_archive_news: 'list',
      cat_archive_lower: 'ranked',
      cat_sidebar_ad: 'block',
      cat_sidebar_magazine: 'cover',
      cat_sidebar_most_read: 'ranked',
      article_sidebar_latest: 'list',
      article_sidebar_most_read: 'ranked',
      article_related: 'list',
    };
    return map[widget] || 'block';
  }

  function applyShape(card, shape) {
    if (!card || !shape) return;
    card.setAttribute('data-shape', shape);
    const el = card.querySelector('.altijara-el-shape');
    if (!el) return;
    el.className = 'altijara-el-shape altijara-el-shape--sm altijara-el-shape--' + shape;
    while (el.children.length < 6) {
      const i = document.createElement('i');
      i.className = 'altijara-el-shape__c altijara-el-shape__c--' + (el.children.length + 1);
      el.appendChild(i);
    }
  }

  function buildCardFromTemplate(builder, id, label, widget, variant, pairWith, shape) {
    if (!builder) return null;
    const tplId = builder.getAttribute('data-tpl-id');
    const listId = builder.getAttribute('data-list-id');
    const fieldPrefix = builder.getAttribute('data-field-prefix') || 'altijara_sections';
    const specific = tplId && widget ? document.getElementById(tplId + '_' + widget) : null;
    const tpl = specific || (tplId ? document.getElementById(tplId) : null);
    const list = listId ? document.getElementById(listId) : null;
    if (!tpl || !list) return null;

    let html = tpl.innerHTML.replace(/__ID__/g, id).replace(/__LABEL__/g, label || id);

    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    const card = wrap.firstElementChild;
    if (!card) return null;

    card.setAttribute('data-section-id', id);
    card.setAttribute('data-widget', widget);
    card.setAttribute('data-zone', 'body');
    card.setAttribute('data-pair-with', pairWith || '');
    card.open = false;
    applyShape(card, shape || shapeKey(widget, variant, !!pairWith));

    const widgetInput = card.querySelector('input[name*="[widget]"]');
    if (widgetInput) widgetInput.value = widget;

    const variantSelect = card.querySelector('select[name*="[variant]"]');
    if (variantSelect && variant) variantSelect.value = variant;

    const variantHidden = card.querySelector('input[type="hidden"][name*="[variant]"]');
    if (variantHidden) {
      variantHidden.value = variant || 'default';
    } else if (!variantSelect && variant && variant !== 'default') {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = fieldPrefix + '[' + id + '][variant]';
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

    const vis = card.querySelector('input[name*="[visible]"]');
    if (vis) vis.checked = true;

    list.appendChild(card);
    return card;
  }

  function addElement(builder, btn) {
    const widget = btn.getAttribute('data-widget') || 'article_grid';
    const variant = btn.getAttribute('data-variant') || 'default';
    const label = btn.getAttribute('data-label') || widget;
    const pairedAttr = btn.getAttribute('data-paired') || '';
    const paired = pairedAttr === '1' || pairedAttr === 'tech_most_read';

    const shape = btn.getAttribute('data-shape') || shapeKey(widget, variant, pairedAttr || paired);

    if (paired && widget === 'split_sector') {
      const idA = uniqueId('sec_split');
      const idB = uniqueId('sec_split');
      const listId = builder.getAttribute('data-list-id');
      const list = listId ? document.getElementById(listId) : null;
      const cardA = buildCardFromTemplate(builder, idA, label + ' A', widget, 'default', idB, 'sector');
      const cardB = buildCardFromTemplate(builder, idB, label + ' B', widget, 'default', idA, 'sector');
      if (cardA && cardB) {
        if (list) wrapCardsInPairGroup(list, cardA, cardB);
        closeModal(builder);
        refreshPickerLimits(cardA);
        refreshPickerLimits(cardB);
        enhanceAllCatSelects(cardA);
        enhanceAllCatSelects(cardB);
        bindTermPickers(cardA);
        bindTermPickers(cardB);
        cardA.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
        cardB.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
        cardA.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      return;
    }

    // Full تقنية layout: card row + Most Read sidebar (same as default home tech block).
    if (pairedAttr === 'tech_most_read' && widget === 'article_grid' && variant === 'cars_row') {
      const idTech = uniqueId('sec_tech');
      const idMost = uniqueId('sec_mostread');
      const techLabel = 'تقنية';
      const mostLabel = 'الأكثر قراءة في قطاع الأعمال';
      const cardTech = buildCardFromTemplate(builder, idTech, techLabel, 'article_grid', 'cars_row', idMost, 'card-row-pair');
      const cardMost = buildCardFromTemplate(builder, idMost, mostLabel, 'most_read', 'default', idTech, 'ranked');
      if (cardTech) {
        const countInput = cardTech.querySelector('input[name*="[count]"]');
        if (countInput) countInput.value = '3';
        const catSelect = cardTech.querySelector('select[name*="[category]"]');
        if (catSelect) {
          const techOpt = Array.from(catSelect.options).find((o) => /tech|technique|تقنية/i.test(o.value + ' ' + o.textContent));
          if (techOpt) catSelect.value = techOpt.value;
        }
      }
      if (cardMost) {
        const countInput = cardMost.querySelector('input[name*="[count]"]');
        if (countInput) countInput.value = '5';
      }
      if (cardTech && cardMost) {
        const listId = builder.getAttribute('data-list-id');
        const list = listId ? document.getElementById(listId) : null;
        if (list) wrapCardsInPairGroup(list, cardTech, cardMost);
        closeModal(builder);
        refreshPickerLimits(cardTech);
        refreshPickerLimits(cardMost);
        enhanceAllCatSelects(cardTech);
        enhanceAllCatSelects(cardMost);
        bindTermPickers(cardTech);
        bindTermPickers(cardMost);
        cardTech.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
        cardMost.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
        cardTech.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      return;
    }

    const id = uniqueId('sec_' + widget.replace(/_/g, ''));
    const card = buildCardFromTemplate(builder, id, label, widget, variant, '', shape);
    if (card) {
      closeModal(builder);
      refreshPickerLimits(card);
      syncSlideSource(card);
      enhanceAllCatSelects(card);
      bindTermPickers(card);
      card.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  let dragEl = null;
  let dragList = null;

  function endDrag() {
    if (dragEl) dragEl.classList.remove('is-dragging');
    dragEl = null;
    dragList = null;
  }

  function sortableUnit(node) {
    if (!node) return null;
    if (node.classList && node.classList.contains('altijara-section-pair-group')) return node;
    if (node.classList && node.classList.contains('altijara-section-card')) {
      const group = node.closest('.altijara-section-pair-group');
      return group || node;
    }
    return null;
  }

  function wrapCardsInPairGroup(list, cardA, cardB) {
    if (!list || !cardA || !cardB) return null;
    // Capture anchor before moving cards out of the list.
    const anchor =
      cardA.parentElement === list ? cardA : cardB.parentElement === list ? cardB : null;
    const group = document.createElement('div');
    group.className = 'altijara-section-pair-group';
    const head = document.createElement('div');
    head.className = 'altijara-section-pair-group__head';
    head.innerHTML =
      '<span class="altijara-section-pair-group__badge">' +
      (cfg.i18n?.pairedModules || 'Paired modules') +
      '</span>';
    if (anchor) {
      list.insertBefore(group, anchor);
    } else {
      list.appendChild(group);
    }
    group.appendChild(head);
    group.appendChild(cardA);
    group.appendChild(cardB);
    return group;
  }

  function unwrapPairGroup(group) {
    if (!group || !group.classList.contains('altijara-section-pair-group')) return;
    const parent = group.parentElement;
    if (!parent) return;
    const cards = Array.from(group.querySelectorAll(':scope > .altijara-section-card'));
    cards.forEach((card) => parent.insertBefore(card, group));
    group.remove();
  }

  function clearCardPair(card) {
    if (!card) return;
    card.setAttribute('data-pair-with', '');
    const pairInput = card.querySelector('.altijara-section-pair');
    if (pairInput) pairInput.value = '';
  }

  function findCardById(list, id) {
    if (!list || !id) return null;
    return list.querySelector('.altijara-section-card[data-section-id="' + id + '"]');
  }

  function groupPairedCards(list) {
    if (!list) return;
    const cards = Array.from(list.querySelectorAll('.altijara-section-card'));
    const seen = {};
    cards.forEach((card) => {
      const id = card.getAttribute('data-section-id') || '';
      if (!id || seen[id]) return;
      const pairId = card.getAttribute('data-pair-with') || '';
      if (!pairId) return;
      const partner = findCardById(list, pairId);
      if (!partner) return;
      const back = partner.getAttribute('data-pair-with') || '';
      if (back !== id) return;
      if (seen[pairId]) return;
      // Already grouped together.
      const g1 = card.closest('.altijara-section-pair-group');
      const g2 = partner.closest('.altijara-section-pair-group');
      if (g1 && g1 === g2) {
        seen[id] = true;
        seen[pairId] = true;
        return;
      }
      if (g1) unwrapPairGroup(g1);
      if (g2) unwrapPairGroup(g2);
      wrapCardsInPairGroup(list, card, partner);
      seen[id] = true;
      seen[pairId] = true;
    });
  }

  function groupAllPairedLists(root) {
    (root || document).querySelectorAll('.altijara-body-sortable').forEach(groupPairedCards);
  }

  function bindSortable(list) {
    if (!list || list.dataset.sortBound) return;
    list.dataset.sortBound = '1';

    list.addEventListener('pointerdown', (e) => {
      const handle = e.target.closest('.altijara-section-drag');
      if (!handle || !list.contains(handle)) return;
      const card = handle.closest('.altijara-section-card');
      if (!card) return;
      const unit = sortableUnit(card);
      if (!unit || (unit.parentElement !== list && !list.contains(unit))) return;
      // Unit must be a direct child of the list (card or pair-group).
      if (unit.parentElement !== list) return;
      e.preventDefault();
      e.stopPropagation();
      dragEl = unit;
      dragList = list;
      unit.classList.add('is-dragging');
      if (handle.setPointerCapture) {
        try {
          handle.setPointerCapture(e.pointerId);
        } catch (err) {}
      }
    });

    list.addEventListener('pointermove', (e) => {
      if (!dragEl || dragList !== list) return;
      const over = document.elementFromPoint(e.clientX, e.clientY);
      if (!over || !over.closest) return;
      const overCard = over.closest('.altijara-section-card');
      const overUnit = sortableUnit(overCard);
      if (!overUnit || overUnit === dragEl || overUnit.parentElement !== list) return;
      const rect = overUnit.getBoundingClientRect();
      const before = e.clientY < rect.top + rect.height / 2;
      list.insertBefore(dragEl, before ? overUnit : overUnit.nextSibling);
    });

    list.addEventListener('pointerup', endDrag);
    list.addEventListener('pointercancel', endDrag);

    list.addEventListener('click', (e) => {
      if (e.target.closest('.altijara-section-drag')) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  function syncEmptyTagWarning(el) {
    if (!el) return;
    const field = el.closest('.altijara-field');
    const warn = field && field.querySelector('.altijara-empty-tag-warning');
    if (!warn) return;
    let emptyChosen = false;

    if (el.classList && el.classList.contains('altijara-term-picker')) {
      // Warn only when every selected tag has zero posts (queries use OR across tags).
      const chips = el.querySelectorAll('.altijara-term-picker__chip');
      if (chips.length) {
        let anyEmpty = false;
        let anyWithPosts = false;
        chips.forEach((chip) => {
          const raw = chip.getAttribute('data-count');
          const count = raw === null || raw === '' ? -1 : Number(raw);
          if (count === 0) anyEmpty = true;
          if (count > 0) anyWithPosts = true;
        });
        emptyChosen = anyEmpty && !anyWithPosts;
      }
    } else if (el.multiple) {
      Array.from(el.selectedOptions || []).forEach((opt) => {
        const raw = opt.getAttribute('data-count');
        const count = raw === null || raw === '' ? -1 : Number(raw);
        if (String(opt.value || '0') !== '0' && count === 0) {
          emptyChosen = true;
        }
      });
    } else if (el.tagName === 'SELECT') {
      const opt = el.options[el.selectedIndex];
      const raw = opt ? opt.getAttribute('data-count') : null;
      const count = raw === null || raw === '' ? -1 : Number(raw);
      const chosen = String(el.value || '0') !== '0';
      emptyChosen = chosen && count === 0;
    }
    warn.hidden = !emptyChosen;
  }

  function readTermPickerChoices(picker) {
    if (!picker) return [];
    const dataEl = picker.querySelector('.altijara-term-picker__data');
    if (!dataEl) return [];
    try {
      const parsed = JSON.parse(dataEl.textContent || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (err) {
      return [];
    }
  }

  function selectedTermIds(picker) {
    return Array.from(picker.querySelectorAll('.altijara-term-picker__chip'))
      .map((chip) => parseInt(chip.getAttribute('data-id') || '0', 10))
      .filter((n) => n > 0);
  }

  function hideTermResults(picker) {
    const results = picker && picker.querySelector('.altijara-term-picker__results');
    if (results) results.hidden = true;
    if (picker) picker.classList.remove('is-open');
  }

  function showTermResults(picker) {
    const results = picker && picker.querySelector('.altijara-term-picker__results');
    if (results) results.hidden = false;
    if (picker) picker.classList.add('is-open');
  }

  function addTermChip(picker, item) {
    if (!picker || !item) return;
    const id = parseInt(item.id || item.value || '0', 10);
    if (!id || selectedTermIds(picker).indexOf(id) !== -1) return;
    const fieldName = picker.getAttribute('data-field-name') || '';
    const count = typeof item.count === 'number' ? item.count : -1;
    const chip = document.createElement('span');
    chip.className = 'altijara-term-picker__chip';
    chip.setAttribute('data-id', String(id));
    chip.setAttribute('data-count', String(count));
    chip.innerHTML =
      '<span class="altijara-term-picker__chip-label"></span>' +
      '<button type="button" class="altijara-term-picker__remove" aria-label="Remove">&times;</button>';
    const label = chip.querySelector('.altijara-term-picker__chip-label');
    if (label) label.textContent = item.name || item.label || String(id);
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = fieldName;
    hidden.value = String(id);
    chip.appendChild(hidden);
    const selected = picker.querySelector('.altijara-term-picker__selected');
    if (selected) selected.appendChild(chip);
    syncEmptyTagWarning(picker);
  }

  function renderTermResults(picker, query) {
    const results = picker.querySelector('.altijara-term-picker__results');
    if (!results) return;
    const q = String(query || '')
      .trim()
      .toLowerCase();
    const chosen = selectedTermIds(picker);
    const choices = readTermPickerChoices(picker).filter((item) => {
      const id = parseInt(item.id || '0', 10);
      if (!id || chosen.indexOf(id) !== -1) return false;
      if (!q) return true;
      const hay = [item.name, item.label, item.slug, String(id)]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();
      return hay.indexOf(q) !== -1;
    });

    results.innerHTML = '';
    if (!choices.length) {
      const empty = document.createElement('li');
      empty.className = 'altijara-term-picker__empty';
      empty.textContent = cfg.i18n?.noCategoryMatches || cfg.i18n?.noResults || 'No matches';
      results.appendChild(empty);
      showTermResults(picker);
      return;
    }

    choices.slice(0, 40).forEach((item) => {
      const li = document.createElement('li');
      const btn = document.createElement('button');
      btn.type = 'button';
      const name = item.name || item.label || String(item.id);
      const meta = [];
      if (typeof item.count === 'number' && item.count >= 0) meta.push(String(item.count));
      btn.innerHTML =
        '<span class="altijara-term-picker__opt-name"></span>' +
        (meta.length ? '<span class="altijara-term-picker__opt-meta"></span>' : '');
      const nameEl = btn.querySelector('.altijara-term-picker__opt-name');
      if (nameEl) nameEl.textContent = name;
      const metaEl = btn.querySelector('.altijara-term-picker__opt-meta');
      if (metaEl) metaEl.textContent = meta.join(' · ');
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        addTermChip(picker, item);
        const search = picker.querySelector('.altijara-term-picker__search');
        if (search) {
          search.value = '';
          search.focus();
        }
        renderTermResults(picker, '');
      });
      li.appendChild(btn);
      results.appendChild(li);
    });
    showTermResults(picker);
  }

  function bindTermPickers(root) {
    (root || document).querySelectorAll('.altijara-term-picker').forEach((picker) => {
      if (picker.dataset.bound === '1') return;
      picker.dataset.bound = '1';
      const search = picker.querySelector('.altijara-term-picker__search');
      if (!search) return;

      search.addEventListener('focus', () => renderTermResults(picker, search.value));
      search.addEventListener('click', () => renderTermResults(picker, search.value));
      search.addEventListener('input', () => renderTermResults(picker, search.value));
      search.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          hideTermResults(picker);
          search.blur();
        }
      });

      picker.addEventListener('click', (e) => {
        const remove = e.target.closest('.altijara-term-picker__remove');
        if (!remove) return;
        e.preventDefault();
        const chip = remove.closest('.altijara-term-picker__chip');
        if (chip) chip.remove();
        syncEmptyTagWarning(picker);
        if (picker.querySelector('.altijara-term-picker__results') && !picker.querySelector('.altijara-term-picker__results').hidden) {
          renderTermResults(picker, search.value);
        }
      });
    });
  }

  function catOptionMeta(opt) {
    if (!opt) {
      return { value: '', label: '', name: '', slug: '', depth: 0, kind: '', count: -1 };
    }
    const depth = parseInt(opt.getAttribute('data-depth') || '0', 10);
    const countRaw = opt.getAttribute('data-count');
    return {
      value: String(opt.value || ''),
      label: String(opt.textContent || '').trim(),
      name: String(opt.getAttribute('data-name') || opt.textContent || '').trim(),
      slug: String(opt.getAttribute('data-slug') || '').trim(),
      depth: Number.isFinite(depth) ? depth : 0,
      kind: String(opt.getAttribute('data-kind') || '').trim(),
      count: countRaw === null || countRaw === '' ? -1 : Number(countRaw),
    };
  }

  function catDisplayLabel(meta) {
    if (!meta) return '';
    if (!meta.value) return meta.label || '';
    return meta.name || meta.label || meta.value;
  }

  function syncCatComboLabel(wrap) {
    if (!wrap) return;
    const select = wrap.querySelector('select.altijara-cat-select');
    const labelEl = wrap.querySelector('.altijara-cat-combo__label');
    if (!select || !labelEl) return;
    const opt = select.options[select.selectedIndex];
    const meta = catOptionMeta(opt);
    labelEl.textContent = catDisplayLabel(meta) || (cfg.i18n?.chooseCategory || 'Choose category');
    wrap.classList.toggle('is-empty', !meta.value);
  }

  function clearCatPanelPlace(wrap) {
    const panel = wrap && wrap.querySelector('.altijara-cat-combo__panel');
    if (!panel) return;
    panel.style.position = '';
    panel.style.left = '';
    panel.style.width = '';
    panel.style.right = '';
    panel.style.top = '';
    panel.style.bottom = '';
    panel.style.maxHeight = '';
    panel.style.zIndex = '';
  }

  function placeCatPanel(wrap) {
    const panel = wrap && wrap.querySelector('.altijara-cat-combo__panel');
    const toggle = wrap && wrap.querySelector('.altijara-cat-combo__toggle');
    if (!panel || !toggle || panel.hidden) return;
    const rect = toggle.getBoundingClientRect();
    const gap = 4;
    const spaceBelow = window.innerHeight - rect.bottom - gap - 8;
    const spaceAbove = rect.top - gap - 8;
    const openUp = spaceBelow < 220 && spaceAbove > spaceBelow;
    const available = Math.max(120, openUp ? spaceAbove : spaceBelow);
    panel.style.position = 'fixed';
    panel.style.left = Math.max(8, rect.left) + 'px';
    panel.style.width = Math.max(160, rect.width) + 'px';
    panel.style.right = 'auto';
    panel.style.zIndex = '100000';
    panel.style.maxHeight = Math.min(320, available) + 'px';
    if (openUp) {
      panel.style.top = 'auto';
      panel.style.bottom = window.innerHeight - rect.top + gap + 'px';
    } else {
      panel.style.bottom = 'auto';
      panel.style.top = rect.bottom + gap + 'px';
    }
  }

  function repositionOpenCatCombos() {
    document.querySelectorAll('.altijara-cat-combo.is-open').forEach(placeCatPanel);
  }

  function closeCatCombo(wrap) {
    if (!wrap) return;
    const panel = wrap.querySelector('.altijara-cat-combo__panel');
    const search = wrap.querySelector('.altijara-cat-combo__search');
    if (panel) panel.hidden = true;
    wrap.classList.remove('is-open');
    clearCatPanelPlace(wrap);
    if (search) search.value = '';
  }

  function filterCatCombo(wrap, query) {
    const list = wrap && wrap.querySelector('.altijara-cat-combo__list');
    if (!list) return;
    const q = String(query || '')
      .trim()
      .toLowerCase();
    let shown = 0;
    list.querySelectorAll('.altijara-cat-combo__opt').forEach((btn) => {
      const hay = String(btn.getAttribute('data-search') || btn.textContent || '').toLowerCase();
      const match = !q || hay.indexOf(q) !== -1;
      btn.hidden = !match;
      if (match) shown += 1;
    });
    const empty = list.querySelector('.altijara-cat-combo__empty');
    if (empty) empty.hidden = shown > 0;
  }

  function openCatCombo(wrap) {
    if (!wrap) return;
    document.querySelectorAll('.altijara-cat-combo.is-open').forEach((other) => {
      if (other !== wrap) closeCatCombo(other);
    });
    const panel = wrap.querySelector('.altijara-cat-combo__panel');
    const search = wrap.querySelector('.altijara-cat-combo__search');
    if (panel) panel.hidden = false;
    wrap.classList.add('is-open');
    filterCatCombo(wrap, '');
    placeCatPanel(wrap);
    if (search) {
      search.value = '';
      window.setTimeout(() => search.focus(), 0);
    }
  }

  function enhanceCatSelect(select) {
    if (!select || select.multiple || select.dataset.catCombo === '1') return;
    if (!select.classList.contains('altijara-cat-select')) return;
    select.dataset.catCombo = '1';

    const wrap = document.createElement('div');
    wrap.className = 'altijara-cat-combo';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'altijara-cat-combo__toggle';
    toggle.setAttribute('aria-haspopup', 'listbox');
    toggle.setAttribute('aria-expanded', 'false');

    const labelEl = document.createElement('span');
    labelEl.className = 'altijara-cat-combo__label';
    const chevron = document.createElement('span');
    chevron.className = 'altijara-cat-combo__chevron';
    chevron.setAttribute('aria-hidden', 'true');
    chevron.textContent = '▾';
    toggle.appendChild(labelEl);
    toggle.appendChild(chevron);

    const panel = document.createElement('div');
    panel.className = 'altijara-cat-combo__panel';
    panel.hidden = true;

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'altijara-cat-combo__search';
    search.setAttribute('autocomplete', 'off');
    search.setAttribute('spellcheck', 'false');
    search.placeholder = cfg.i18n?.searchCategories || 'Search categories…';

    const list = document.createElement('div');
    list.className = 'altijara-cat-combo__list';
    list.setAttribute('role', 'listbox');

    Array.from(select.options || []).forEach((opt) => {
      const meta = catOptionMeta(opt);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className =
        'altijara-cat-combo__opt' +
        (meta.depth > 0 ? ' is-child' : ' is-main') +
        (meta.depth > 0 ? ' is-depth-' + Math.min(meta.depth, 4) : '');
      btn.setAttribute('role', 'option');
      btn.setAttribute('data-value', meta.value);
      btn.setAttribute(
        'data-search',
        [meta.name, meta.slug, meta.label, meta.kind].filter(Boolean).join(' ')
      );
      btn.style.setProperty('--cat-depth', String(meta.depth));

      const main = document.createElement('span');
      main.className = 'altijara-cat-combo__opt-main';
      if (meta.depth > 0) {
        const branch = document.createElement('span');
        branch.className = 'altijara-cat-combo__branch';
        branch.setAttribute('aria-hidden', 'true');
        branch.textContent = '↳';
        main.appendChild(branch);
      }
      const name = document.createElement('span');
      name.className = 'altijara-cat-combo__opt-name';
      name.textContent = meta.value ? meta.name || meta.label : meta.label;
      main.appendChild(name);

      const metaRow = document.createElement('span');
      metaRow.className = 'altijara-cat-combo__opt-meta';
      const bits = [];
      if (meta.kind) bits.push(meta.kind);
      if (meta.slug) bits.push(meta.slug);
      if (meta.count >= 0) bits.push(String(meta.count));
      metaRow.textContent = bits.join(' · ');

      btn.appendChild(main);
      if (bits.length) btn.appendChild(metaRow);
      if (String(select.value) === meta.value) btn.classList.add('is-selected');

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        select.value = meta.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        list.querySelectorAll('.altijara-cat-combo__opt').forEach((b) => {
          b.classList.toggle('is-selected', b.getAttribute('data-value') === meta.value);
        });
        syncCatComboLabel(wrap);
        closeCatCombo(wrap);
        toggle.setAttribute('aria-expanded', 'false');
      });

      list.appendChild(btn);
    });

    const empty = document.createElement('div');
    empty.className = 'altijara-cat-combo__empty';
    empty.hidden = true;
    empty.textContent = cfg.i18n?.noCategoryMatches || 'No matches';
    list.appendChild(empty);

    panel.appendChild(search);
    panel.appendChild(list);

    const parent = select.parentNode;
    parent.insertBefore(wrap, select);
    wrap.appendChild(toggle);
    wrap.appendChild(panel);
    wrap.appendChild(select);
    select.classList.add('altijara-cat-select--native');

    syncCatComboLabel(wrap);

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const open = wrap.classList.contains('is-open');
      if (open) {
        closeCatCombo(wrap);
        toggle.setAttribute('aria-expanded', 'false');
      } else {
        openCatCombo(wrap);
        toggle.setAttribute('aria-expanded', 'true');
      }
    });

    search.addEventListener('input', () => filterCatCombo(wrap, search.value));
    search.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeCatCombo(wrap);
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });

    select.addEventListener('change', () => syncCatComboLabel(wrap));
  }

  function enhanceAllCatSelects(root) {
    (root || document).querySelectorAll('select.altijara-cat-select').forEach(enhanceCatSelect);
  }

  function syncSlideSource(card) {
    if (!card) return;
    const select = card.querySelector('.altijara-slide-source');
    if (!select) return;
    const mode = select.value === 'issues' ? 'issues' : 'posts';
    card.querySelectorAll('.altijara-source-panel').forEach((panel) => {
      const want = panel.getAttribute('data-source-panel') || '';
      const show = !want || want === mode;
      panel.hidden = !show;
    });
  }

  function syncAllSlideSources(root) {
    (root || document).querySelectorAll('.altijara-section-card').forEach(syncSlideSource);
  }

  function syncCategorySource(card) {
    if (!card) return;
    const select = card.querySelector('.altijara-category-source');
    if (!select) return;
    const selected = select.value === 'selected';
    card.querySelectorAll('.altijara-category-source-selected').forEach((panel) => {
      panel.hidden = !selected;
    });
  }

  function syncAllCategorySources(root) {
    (root || document).querySelectorAll('.altijara-section-card').forEach(syncCategorySource);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.altijara-body-sortable').forEach(bindSortable);
    groupAllPairedLists(document);
    refreshPickerLimits(document);
    syncAllSlideSources(document);
    syncAllCategorySources(document);
    enhanceAllCatSelects(document);
    bindTermPickers(document);
    document.querySelectorAll('.altijara-tag-select, .altijara-cat-select').forEach(syncEmptyTagWarning);
    document.addEventListener('change', (e) => {
      const t = e.target;
      if (!t) return;
      if (t.classList && t.classList.contains('altijara-category-source')) {
        syncCategorySource(t.closest('.altijara-section-card'));
        return;
      }
      if (t.classList && t.classList.contains('altijara-slide-source')) {
        syncSlideSource(t.closest('.altijara-section-card'));
        return;
      }
      if (t.classList && (t.classList.contains('altijara-tag-select') || t.classList.contains('altijara-cat-select'))) {
        syncEmptyTagWarning(t);
        return;
      }
      const name = t.name || '';
      if (/\[visible\]$/.test(name) && t.type === 'checkbox') {
        const card = t.closest('.altijara-section-card');
        const status = card && card.querySelector('.altijara-section-status');
        if (status) {
          const on = !!t.checked;
          const onLabel = status.getAttribute('data-label-on') || cfg.i18n?.visible || 'Visible';
          const offLabel = status.getAttribute('data-label-off') || cfg.i18n?.hidden || 'Hidden';
          status.classList.toggle('is-on', on);
          status.classList.toggle('is-off', !on);
          const dot = status.querySelector('.altijara-section-status__dot');
          status.textContent = '';
          if (dot) status.appendChild(dot);
          status.appendChild(document.createTextNode(on ? onLabel : offLabel));
        }
        return;
      }
      if (/\[count\]$/.test(name)) {
        refreshPickerLimits(document);
      }
    });
    document.addEventListener('scroll', repositionOpenCatCombos, true);
    window.addEventListener('resize', repositionOpenCatCombos);
    document.addEventListener('click', (e) => {
      const t = e.target;
      if (t && t.closest && t.closest('.altijara-cat-combo')) {
        // keep open
      } else {
        document.querySelectorAll('.altijara-cat-combo.is-open').forEach((wrap) => {
          closeCatCombo(wrap);
          const toggle = wrap.querySelector('.altijara-cat-combo__toggle');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
      }
      if (!(t && t.closest && t.closest('.altijara-term-picker'))) {
        document.querySelectorAll('.altijara-term-picker').forEach((picker) => {
          hideTermResults(picker);
        });
      }
    });
  });

  document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.altijara-builder-add-open');
    if (addBtn) {
      e.preventDefault();
      openModal(getBuilder(addBtn));
      return;
    }

    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) {
      e.preventDefault();
      const modal = closeBtn.closest('.altijara-builder-modal');
      closeModal(modal);
      return;
    }

    const tab = e.target.closest('.altijara-builder-tab');
    if (tab) {
      e.preventDefault();
      setTab(tab.closest('.altijara-builder-modal'), tab.getAttribute('data-tab'));
      return;
    }

    const elBtn = e.target.closest('.altijara-builder-element');
    if (elBtn) {
      e.preventDefault();
      const modal = elBtn.closest('.altijara-builder-modal');
      const mid = modal ? modal.id : '';
      const builder = mid
        ? document.querySelector('.altijara-layout-builder[data-modal-id="' + mid + '"]')
        : null;
      addElement(builder, elBtn);
      return;
    }

    const del = e.target.closest('.altijara-section-delete');
    if (del) {
      e.preventDefault();
      e.stopPropagation();
      const card = del.closest('.altijara-section-card');
      if (card && card.getAttribute('data-zone') === 'body') {
        if (window.confirm(cfg.i18n?.removeSection || 'Remove this section?')) {
          const list = card.closest('.altijara-body-sortable');
          const pairId = card.getAttribute('data-pair-with') || '';
          const group = card.closest('.altijara-section-pair-group');
          const partner = pairId && list ? findCardById(list, pairId) : null;
          if (partner) clearCardPair(partner);
          clearCardPair(card);
          card.remove();
          if (group) {
            const left = group.querySelectorAll(':scope > .altijara-section-card');
            if (left.length < 2) unwrapPairGroup(group);
          }
        }
      }
    }
  });
})();
