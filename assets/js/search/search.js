/**
 * Search archive — live typing, skeleton list, AJAX pagination.
 */
(function () {
  'use strict';

  function saveButtonsOn() {
    const flag = window.AltijaraBookmarks && window.AltijaraBookmarks.showSave;
    if (flag === undefined || flag === null) return true;
    return flag === true || flag === 1 || flag === '1';
  }

  const cfg = window.AltijaraSearch || {};
  const FALLBACK_IMG = cfg.fallbackImg || '';
  const MIN_CHARS = 2;
  const DEBOUNCE_MS = 320;

  let currentQuery = String(cfg.query || '').trim();
  let currentPage = Math.max(1, Number(cfg.page) || 1);
  let totalPages = Math.max(1, Number(cfg.totalPages) || 1);
  let totalResults = Math.max(0, Number(cfg.total) || 0);
  let perPage = Math.max(1, Number(cfg.perPage) || 10);
  let busy = false;
  let debounceTimer = null;
  let requestSeq = 0;

  function getWp() {
    return window.AltijaraWP || {};
  }

  function decodeHtml(str) {
    const wp = getWp();
    if (typeof wp.decodeHtml === 'function') return wp.decodeHtml(str);
    const el = document.createElement('textarea');
    el.innerHTML = String(str || '');
    return el.value;
  }

  function esc(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function safeText(str) {
    return esc(decodeHtml(str));
  }

  function articleHref(post) {
    const wp = getWp();
    return wp.articlePageUrl ? wp.articlePageUrl(post) : (post && post.link) || '#';
  }

  function showNewsSkeleton(count) {
    const list = document.getElementById('catNewsList');
    if (!list) return;
    list.setAttribute('aria-busy', 'true');
    list.classList.add('is-loading');
    list.innerHTML = Array.from({ length: count }, () => `
      <article class="cat-news-item cat-news-skel" aria-hidden="true">
        <div class="cat-news-img-wrap"><span class="sk sk-thumb"></span></div>
        <div class="cat-news-content">
          <span class="sk sk-line sk-line--title"></span>
          <span class="sk sk-line"></span>
          <span class="sk sk-line sk-line--short"></span>
          <span class="sk sk-line sk-line--meta"></span>
        </div>
      </article>
    `).join('');
  }

  function hideNewsSkeleton() {
    const list = document.getElementById('catNewsList');
    if (!list) return;
    list.removeAttribute('aria-busy');
    list.classList.remove('is-loading');
  }

  function setPaginationLoading(isLoading) {
    const wrap = document.getElementById('catPagination');
    if (!wrap) return;
    wrap.classList.toggle('is-loading', !!isLoading);
    wrap.style.opacity = isLoading ? '0.55' : '1';
    wrap.querySelectorAll('button').forEach((btn) => {
      btn.disabled = isLoading || btn.dataset.keepDisabled === '1';
    });
  }

  function fillNewsList(posts) {
    const list = document.getElementById('catNewsList');
    if (!list) return;
    hideNewsSkeleton();

    if (!posts || !posts.length) {
      list.innerHTML = `<p class="search-archive-empty" id="searchArchiveEmpty">${esc(
        cfg.i18n?.empty || 'لا توجد نتائج مطابقة. جرّب كلمات أخرى.'
      )}</p>`;
      return;
    }

    const { trimAtWord } = getWp();
    list.innerHTML = posts
      .map((p) => {
        const excerpt = trimAtWord
          ? trimAtWord(p.excerpt || p.fullExcerpt || '', 140)
          : p.excerpt || '';
        const img = p.img || FALLBACK_IMG;
        const href = articleHref(p);
        return `
          <article class="cat-news-item">
            <div class="cat-news-img-wrap">
              <a href="${esc(href)}">
                <img src="${esc(img)}" alt="${safeText(p.title)}" loading="lazy">
              </a>
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="${esc(href)}">${safeText(p.title)}</a>
              </h3>
              <p class="cat-news-excerpt">${safeText(excerpt)}</p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">${safeText(p.dateText || p.timeText || '')}</span>
                </div>
                ${saveButtonsOn() ? `<button type="button" class="btn-cat-save" data-post-id="${esc(p.id || '')}" onclick="typeof toggleSaveArticle==='function'&&toggleSaveArticle(this)" title="${esc(cfg.i18n?.save || 'حفظ المقال')}">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>${esc(cfg.i18n?.saveLabel || 'حفظ')}</span>
                </button>` : ''}
              </div>
            </div>
          </article>
        `;
      })
      .join('');
    window.AltijaraBookmarksAPI?.hydrate?.(list);
  }

  function updateTitleAndMeta(query, total) {
    const titleEl = document.getElementById('searchArchiveTitle');
    const metaEl = document.getElementById('searchArchiveMeta');
    if (titleEl) {
      titleEl.textContent = query
        ? (cfg.i18n?.titleFor || 'نتائج البحث لـ “%s”').replace('%s', query)
        : cfg.i18n?.titleEmpty || 'البحث في الأرشيف';
    }
    if (metaEl) {
      if (!query) {
        metaEl.hidden = true;
        metaEl.textContent = '';
      } else {
        metaEl.hidden = false;
        metaEl.textContent = (cfg.i18n?.meta || '%1$s نتيجة لـ “%2$s”')
          .replace('%1$s', String(total))
          .replace('%2$s', query);
      }
    }
  }

  function getPaginationItems(page, pages) {
    if (pages <= 7) {
      return Array.from({ length: pages }, (_, i) => i + 1);
    }
    const items = [1];
    const start = Math.max(2, page - 1);
    const end = Math.min(pages - 1, page + 1);
    if (start > 2) items.push('…');
    for (let i = start; i <= end; i += 1) items.push(i);
    if (end < pages - 1) items.push('…');
    items.push(pages);
    return items;
  }

  function renderPagination() {
    const wrap = document.getElementById('catPagination');
    const nums = document.getElementById('catPageNumbers');
    const prev = document.getElementById('catPagePrev');
    const next = document.getElementById('catPageNext');
    if (!wrap || !nums || !prev || !next) return;

    if (totalPages <= 1 || !currentQuery) {
      wrap.hidden = true;
      return;
    }
    wrap.hidden = false;

    nums.innerHTML = getPaginationItems(currentPage, totalPages)
      .map((item) => {
        if (item === '…') return '<span class="cat-page-ellipsis">…</span>';
        const active = item === currentPage ? ' is-active' : '';
        return `<button type="button" class="cat-page-num${active}" data-page="${item}" aria-label="صفحة ${item}"${item === currentPage ? ' aria-current="page"' : ''}>${item}</button>`;
      })
      .join('');

    prev.disabled = currentPage <= 1;
    next.disabled = currentPage >= totalPages;
    prev.dataset.keepDisabled = prev.disabled ? '1' : '0';
    next.dataset.keepDisabled = next.disabled ? '1' : '0';

    if (!wrap.dataset.bound) {
      wrap.dataset.bound = '1';
      prev.addEventListener('click', () => goToPage(currentPage - 1));
      next.addEventListener('click', () => goToPage(currentPage + 1));
      nums.addEventListener('click', (e) => {
        const btn = e.target.closest('.cat-page-num');
        if (!btn || btn.classList.contains('is-active')) return;
        goToPage(Number(btn.dataset.page));
      });
    }
  }

  function updateUrl(query, page) {
    try {
      const url = new URL(window.location.href);
      if (query) {
        url.searchParams.set('s', query);
      } else {
        url.searchParams.delete('s');
      }
      if (page <= 1) {
        url.searchParams.delete('paged');
        url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
      } else {
        url.searchParams.set('paged', String(page));
      }
      history.pushState({ searchQuery: query, searchPage: page }, '', url);
    } catch (e) {
      /* ignore */
    }
  }

  function searchHomeBase() {
    const home =
      (window.AltijaraTheme && window.AltijaraTheme.urls && window.AltijaraTheme.urls.home) ||
      '';
    if (home) return String(home);
    try {
      const here = new URL(window.location.href);
      here.search = '';
      here.hash = '';
      // Strip /page/N/ so we land on the search root, not a paged archive path.
      here.pathname = here.pathname.replace(/\/page\/\d+\/?$/, '/');
      return here.toString();
    } catch (e) {
      return window.location.href;
    }
  }

  function navigateToSearch(query, page) {
    const q = String(query || '').trim();
    const p = Math.max(1, Number(page) || 1);
    try {
      const url = new URL(searchHomeBase());
      if (q) {
        url.searchParams.set('s', q);
      } else {
        url.searchParams.delete('s');
      }
      if (p > 1) {
        url.searchParams.set('paged', String(p));
      } else {
        url.searchParams.delete('paged');
      }
      window.location.assign(url.toString());
    } catch (e) {
      const base = String(
        (window.AltijaraTheme && window.AltijaraTheme.urls && window.AltijaraTheme.urls.home) ||
          window.location.pathname ||
          '/'
      ).replace(/\?.*$/, '');
      const sep = base.indexOf('?') >= 0 ? '&' : '?';
      let href = base + sep + 's=' + encodeURIComponent(q);
      if (p > 1) href += '&paged=' + encodeURIComponent(String(p));
      window.location.href = href;
    }
  }

  async function runSearch(query, page, opts) {
    const q = String(query || '').trim();
    const target = Math.max(1, Number(page) || 1);
    const push = !opts || opts.push !== false;
    const allowShort = !!(opts && opts.allowShort);
    const { fetchSearchPage } = getWp();

    if (!fetchSearchPage) {
      navigateToSearch(q, target);
      return;
    }

    if (q.length > 0 && q.length < MIN_CHARS && !allowShort && !/^\d+$/.test(q)) {
      return;
    }

    if (!q) {
      currentQuery = '';
      currentPage = 1;
      totalPages = 1;
      totalResults = 0;
      hideNewsSkeleton();
      fillNewsList([]);
      updateTitleAndMeta('', 0);
      renderPagination();
      if (push) updateUrl('', 1);
      return;
    }

    const seq = ++requestSeq;
    busy = true;
    setPaginationLoading(true);
    showNewsSkeleton(perPage);

    try {
      const data = await fetchSearchPage(q, perPage, target);
      if (seq !== requestSeq) return;

      currentQuery = q;
      currentPage = target;
      totalResults = data.total || 0;
      totalPages = Math.max(1, data.totalPages || Math.ceil(totalResults / perPage) || 1);

      fillNewsList(data.posts || []);
      updateTitleAndMeta(q, totalResults);
      renderPagination();
      getWp().localizeArticleLinks?.(document);

      if (push) updateUrl(q, target);

      if (opts && opts.scroll) {
        requestAnimationFrame(() => {
          const newsCol =
            document.querySelector('.cat-news-col') ||
            document.getElementById('catNewsList');
          if (newsCol) newsCol.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }
    } catch (err) {
      console.warn('Search archive fetch failed:', err);
      hideNewsSkeleton();
      navigateToSearch(q, target);
      return;
    }

    busy = false;
    setPaginationLoading(false);
  }

  function goToPage(page) {
    const target = Number(page);
    if (busy || !currentQuery || !target || target < 1 || target > totalPages || target === currentPage) {
      return;
    }
    runSearch(currentQuery, target, { push: true, scroll: true });
  }

  function scheduleLiveSearch(value) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const q = String(value || '').trim();
      if (q === currentQuery) return;
      if (q.length > 0 && q.length < MIN_CHARS && !/^\d+$/.test(q)) return;
      runSearch(q, 1, { push: true, scroll: false });
    }, DEBOUNCE_MS);
  }

  function bindForm() {
    const form = document.getElementById('searchArchiveForm');
    const input = document.getElementById('searchArchiveInput');
    if (!form || !input) return;

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      clearTimeout(debounceTimer);
      const q = String(input.value || '').trim();
      if (!q) {
        runSearch('', 1, { push: true, allowShort: true });
        return;
      }
      if (q === currentQuery && currentPage === 1) return;
      runSearch(q, 1, { push: true, scroll: true, allowShort: true });
    });

    input.addEventListener('input', () => {
      scheduleLiveSearch(input.value);
    });
  }

  function fillNewIssue() {
    const lists = document.querySelectorAll('.cat-new-issue-widget .cat-new-issue-list');
    (lists.length ? lists : document.querySelectorAll('#catNewIssueList')).forEach(bindNewIssueList);
  }

  function bindNewIssueList(list) {
    if (!list) return;

    const widget = list.closest('.cat-new-issue-widget');
    let source = [];
    const dataNode = widget && widget.querySelector('.cat-new-issue-source');
    if (dataNode) {
      try {
        source = JSON.parse(dataNode.textContent || '[]');
      } catch (err) {
        source = [];
      }
    }
    if (!Array.isArray(source) || !source.length) {
      const recent = Array.isArray(window.AltijaraRecentIssues) ? window.AltijaraRecentIssues : [];
      const all = Array.isArray(window.AltijaraIssues) ? window.AltijaraIssues : [];
      source = recent.length ? recent : all;
    }
    const seen = new Set();
    const cap = widget ? parseInt(widget.getAttribute('data-issue-count') || '0', 10) : 0;
    const issues = source
      .filter((i) => i && (i.cover || i.src))
      .filter((i) => {
        const key = String(i.cover || i.src);
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
      })
      .slice(0, cap > 0 ? cap : source.length);
    if (!issues.length) {
      if (widget) widget.hidden = true;
      return;
    }
    if (widget) widget.hidden = false;

    let index = 0;
    let coverBusy = false;
    const prev = widget && widget.querySelector('.cat-new-issue-arrow--prev');
    const next = widget && widget.querySelector('.cat-new-issue-arrow--next');
    const arrows = widget && widget.querySelector('.cat-new-issue-arrows');
    if (arrows) arrows.hidden = issues.length < 2;

    const syncArrows = () => {
      if (prev) {
        prev.disabled = issues.length < 2;
        prev.classList.toggle('is-accent', false);
      }
      if (next) {
        next.disabled = issues.length < 2;
        next.classList.toggle('is-accent', issues.length > 1);
      }
    };

    const paintCover = (issue, skeletonFirst) => {
      const href = issue.flipbook || issue.link || issue.permalink || '#';
      const title = issue.title || `العدد ${issue.number || ''}`;
      const coverSrc = String(issue.cover || issue.src || '');
      if (!coverSrc) {
        coverBusy = false;
        return;
      }
      let cover = list.querySelector('a.cat-new-issue-cover');
      let img = cover && cover.querySelector('img');

      if (!cover || !img) {
        list.innerHTML = `
          <a href="${esc(href)}" class="cat-new-issue-cover" target="_blank" rel="noopener">
            <span class="sk sk-cover" aria-hidden="true"></span>
            <img alt="${esc(title)}" decoding="async">
          </a>`;
        cover = list.querySelector('.cat-new-issue-cover');
        img = cover && cover.querySelector('img');
      } else {
        cover.setAttribute('href', href);
        if (!cover.querySelector('.sk-cover')) {
          cover.insertAdjacentHTML('afterbegin', '<span class="sk sk-cover" aria-hidden="true"></span>');
        }
        img.alt = title;
      }

      if (!cover || !img) {
        coverBusy = false;
        return;
      }

      img.onload = null;
      img.onerror = null;

      if (skeletonFirst) {
        cover.classList.remove('is-ready');
        cover.classList.add('is-switching');
        void cover.offsetWidth;
      }

      const startedAt = Date.now();
      const minSkeletonMs = skeletonFirst ? 420 : 0;
      let finished = false;
      let safetyTimer = 0;

      const reveal = () => {
        if (finished) return;
        finished = true;
        if (safetyTimer) clearTimeout(safetyTimer);

        const apply = () => {
          img.onload = null;
          img.onerror = null;
          if (img.getAttribute('src') !== coverSrc) {
            img.src = coverSrc;
          }
          cover.classList.remove('is-switching');
          cover.classList.add('is-ready');
          coverBusy = false;
          syncArrows();
        };

        const wait = Math.max(0, minSkeletonMs - (Date.now() - startedAt));
        if (wait) setTimeout(apply, wait);
        else apply();
      };

      if (!skeletonFirst) {
        img.src = coverSrc;
        if (img.complete && img.naturalWidth) {
          reveal();
        } else {
          img.onload = reveal;
          img.onerror = reveal;
        }
        safetyTimer = setTimeout(reveal, 2500);
        return;
      }

      const pre = new Image();
      pre.onload = reveal;
      pre.onerror = reveal;
      pre.src = coverSrc;
      if (pre.complete && pre.naturalWidth) {
        requestAnimationFrame(reveal);
      }
      safetyTimer = setTimeout(reveal, 2500);
    };

    const show = (nextIndex, fromClick) => {
      if (coverBusy && fromClick) return;
      if (issues.length < 2 && fromClick) return;
      coverBusy = !!fromClick;
      index = (nextIndex + issues.length) % issues.length;
      paintCover(issues[index], !!fromClick);
    };

    const ssrCover = list.querySelector('a.cat-new-issue-cover img[src]');
    if (ssrCover) {
      const ssrSrc = ssrCover.getAttribute('src') || '';
      const matchIdx = issues.findIndex((i) => (i.cover || i.src) === ssrSrc);
      if (matchIdx >= 0) index = matchIdx;
      const cover = ssrCover.closest('.cat-new-issue-cover');
      if (ssrCover.complete && ssrCover.naturalWidth) {
        if (cover) cover.classList.add('is-ready');
      } else {
        ssrCover.onload = () => {
          if (cover) cover.classList.add('is-ready');
        };
      }
    } else {
      show(0, false);
    }

    syncArrows();
    if (prev) {
      prev.onclick = (e) => {
        e.preventDefault();
        show(index - 1, true);
      };
    }
    if (next) {
      next.onclick = (e) => {
        e.preventDefault();
        show(index + 1, true);
      };
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    getWp().localizeArticleLinks?.(document);
    bindForm();
    renderPagination();
    fillNewIssue();

    window.addEventListener('popstate', (e) => {
      const state = e.state || {};
      const url = new URL(window.location.href);
      const q = state.searchQuery != null ? String(state.searchQuery) : (url.searchParams.get('s') || '');
      const page = state.searchPage || Number(url.searchParams.get('paged')) || 1;
      const input = document.getElementById('searchArchiveInput');
      if (input) input.value = q;
      runSearch(q, page, { push: false, scroll: false });
    });
  });
})();
