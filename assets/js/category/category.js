/**
 * Category Page — live WordPress content by category
 * Metadata from site-chrome.js (window.CATEGORIES_DATA)
 * Helpers from wp-api.js (window.AltijaraWP)
 */

const NEWS_PAGE_SIZE = 10;
const FEATURED_COUNT = 8;
/** Legacy fallback keys if PHP did not localize fullHero. */
const FULL_HERO_CATS = new Set(['automotive', 'interviews', 'tourism', 'exhibitions']);

function altijaraSaveButtonsOn() {
  const flag = window.AltijaraBookmarks && window.AltijaraBookmarks.showSave;
  if (flag === undefined || flag === null) return true;
  return flag === true || flag === 1 || flag === '1';
}

function isFullHeroCategory(key) {
  const meta = window.AltijaraCategory;
  if (meta && Object.prototype.hasOwnProperty.call(meta, 'fullHero')) {
    return meta.fullHero === true || meta.fullHero === 1 || meta.fullHero === '1';
  }
  return FULL_HERO_CATS.has(key || getCurrentCategoryKey());
}

function layoutCounts(key) {
  const meta = window.AltijaraCategory || {};
  const featured =
    Number(meta.featuredCount) > 0 ? Number(meta.featuredCount) : FEATURED_COUNT;
  const rec = isFullHeroCategory(key)
    ? 0
    : Number(meta.sidebarCount) > 0
      ? Number(meta.sidebarCount)
      : 3;
  const spotlight = Number(meta.spotlight) || 1 + rec + featured;
  const newsSize = Number(meta.newsSize) || NEWS_PAGE_SIZE;
  return { rec, featured, spotlight, newsSize, firstBatch: spotlight + newsSize };
}
let currentPage = 1;
let totalPages = 1;
let pagingBusy = false;

function getCategoriesData() {
  return window.CATEGORIES_DATA || {};
}

function getCurrentCategoryKey() {
  if (window.AltijaraCategory && window.AltijaraCategory.key) {
    return window.AltijaraCategory.key;
  }
  return new URLSearchParams(window.location.search).get('cat') || 'chamber';
}

function getCurrentCategory() {
  const key = getCurrentCategoryKey();
  const data = Object.assign({}, getCategoriesData()[key] || {});
  if (window.AltijaraCategory && window.AltijaraCategory.termId) {
    data.wpCategoryId = window.AltijaraCategory.termId;
  }
  if (window.AltijaraCategory && window.AltijaraCategory.name) {
    data.title = window.AltijaraCategory.name;
  }
  if (!data.docTitle && data.title) {
    data.docTitle = data.title;
  }
  return { key, data };
}

function getWp() {
  return window.AltijaraWP || {};
}

function articleHref(post) {
  const wp = getWp();
  const catKey = getCurrentCategoryKey();
  return wp.articlePageUrl ? wp.articlePageUrl(post, catKey) : 'article.html';
}

document.addEventListener('DOMContentLoaded', () => {
  getWp().localizeArticleLinks?.(document);
  initCategoryFromUrl();
  initSearchToggle();
  loadCategoryLiveContent();

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(scheduleTitleTruncation, 180);
  });
});

function initCategoryFromUrl() {
  const { key: catKey, data: catData } = getCurrentCategory();
  if (!catData) return;

  document.title = catData.docTitle;

  const catTitleEl = document.getElementById('categoryTitle');
  if (catTitleEl) catTitleEl.textContent = catData.title;

  const pillsWrap = document.getElementById('subnavPills');
  if (pillsWrap && catData.pills?.length) {
    pillsWrap.innerHTML = catData.pills
      .map(
        (pill, idx) => `
        <button type="button" class="cat-sub-pill ${idx === 0 ? 'active' : ''}" onclick="filterSubcategory('${pill.id}', this)">
          ${pill.label}
        </button>`
      )
      .join('');
  }

  applyCategoryLabels(catData.title);

  const topGrid = document.querySelector('.cat-top-grid');
  if (topGrid) {
    const full = isFullHeroCategory(catKey);
    topGrid.classList.toggle('is-hero-full', full);
    if (full) topGrid.classList.remove('is-hero-solo');
  }

  const isFollowed = localStorage.getItem(`followed_cat_${catKey}`) === 'true';
  const btnFollow = document.getElementById('btnFollowCat');
  const followText = document.getElementById('followBtnText');
  if (btnFollow && followText && isFollowed) {
    btnFollow.classList.add('is-following');
    followText.textContent = 'تمت المتابعة ✓';
  }
}

function applyCategoryLabels(title) {
  // Featured tags removed — category is already clear from nav/context.
}

async function loadCategoryLiveContent() {
  // PHP already rendered the archive — wire AJAX pagination + enhance widgets.
  if (window.AltijaraCategory && window.AltijaraCategory.ssr) {
    const { data } = getCurrentCategory();
    if (data?.title) applyCategoryLabels(data.title);
    const topGrid = document.querySelector('.cat-top-grid');
    if (topGrid) {
      const full = isFullHeroCategory();
      topGrid.classList.toggle('is-hero-full', full);
      if (full) topGrid.classList.remove('is-hero-solo');
    }
    const meta = window.AltijaraCategory;
    currentPage = Math.max(1, Number(meta.page) || 1);
    totalPages = Math.max(1, Number(meta.totalPages) || newsPagesFromTotal(meta.total || 0));
    renderPagination();
    fillNewIssue();
    scheduleTitleTruncation();
    revealLiveContent();
    window.addEventListener('popstate', (e) => {
      const page = Number(e.state?.catPage) || 1;
      if (page !== currentPage) goToCategoryPage(page, { push: false });
    });
    return;
  }

  ensureCatLoadingUI();

  const { key, data } = getCurrentCategory();
  const wpId = data?.wpCategoryId;
  const failSafe = setTimeout(revealLiveContent, 8000);
  const { fetchCategoryPage, trimAtWord, getCachedPostsByCategory } = getWp();

  if (!wpId) {
    console.warn('No wpCategoryId for category:', key);
    clearTimeout(failSafe);
    revealLiveContent();
    return;
  }

  if (!fetchCategoryPage && !getCachedPostsByCategory) {
    clearTimeout(failSafe);
    revealLiveContent();
    return;
  }

  // Instant paint from session cache when switching categories/pages
  const counts = layoutCounts(key);
  const cached = getCachedPostsByCategory?.(wpId, counts.firstBatch, 1);
  if (cached?.length) {
    applyCategoryPosts(cached, data, trimAtWord, 1, cached.length);
    clearTimeout(failSafe);
    requestAnimationFrame(() => {
      scheduleTitleTruncation();
      revealLiveContent();
    });
  }

  const pageData = fetchCategoryPage
    ? await fetchCategoryPage(wpId, counts.firstBatch, 1)
    : { posts: await getWp().fetchPostsByCategory?.(wpId, counts.firstBatch, 1) || [], totalPages: 1, total: 0 };

  if (!pageData.posts?.length) {
    clearTimeout(failSafe);
    revealLiveContent();
    return;
  }

  applyCategoryPosts(pageData.posts, data, trimAtWord, 1, pageData.total || pageData.posts.length);
  fillNewIssue();
  await fillMostRead();

  clearTimeout(failSafe);
  requestAnimationFrame(() => {
    scheduleTitleTruncation();
    revealLiveContent();
  });
}

function newsPagesFromTotal(total) {
  const counts = layoutCounts();
  const remaining = Math.max(0, (Number(total) || 0) - counts.spotlight);
  if (!remaining) return 1;
  return Math.max(1, Math.ceil(remaining / (counts.newsSize || NEWS_PAGE_SIZE)));
}

function applyCategoryPosts(posts, data, trimAtWord, page = 1, total = 0) {
  currentPage = page;
  totalPages = newsPagesFromTotal(total || posts.length);

  const counts = layoutCounts();
  const newsSize = Number(window.AltijaraCategory?.newsSize) || NEWS_PAGE_SIZE;
  if (page === 1) {
    fillHero(posts[0], data?.title, trimAtWord);
    if (counts.rec) fillRecommended(posts.slice(1, 1 + counts.rec), data?.title);
    fillFeatured(posts.slice(1 + counts.rec, counts.spotlight), data?.title);
    fillNewsList(posts.slice(counts.spotlight, counts.spotlight + newsSize), data?.title, false);
  } else {
    fillNewsList(posts.slice(0, newsSize), data?.title, false);
  }

  hideNewsSkeleton();
  renderPagination();
  getWp().localizeArticleLinks?.(document);
}

function ensureCatLoadingUI() {
  if (!document.querySelector('.live-loading-bar')) {
    const bar = document.createElement('div');
    bar.className = 'live-loading-bar';
    bar.setAttribute('aria-hidden', 'true');
    document.body.prepend(bar);
  }
  if (!document.querySelector('.live-loading-hint')) {
    const hint = document.createElement('div');
    hint.className = 'live-loading-hint';
    hint.textContent = 'جارِ تحميل أحدث المقالات...';
    document.body.appendChild(hint);
  }
}

function revealLiveContent() {
  document.documentElement.classList.remove('live-pending');
  document.documentElement.classList.add('live-ready');
}

function scheduleTitleTruncation() {
  const run = () => {
    const { truncateMiddleToLines } = getWp();
    if (!truncateMiddleToLines) return;

    const heroLink = document.querySelector('.cat-hero-headline a');
    if (heroLink) {
      const full = heroLink.getAttribute('data-full-title') || heroLink.textContent;
      truncateMiddleToLines(heroLink, full, 3);
    }

    document.querySelectorAll('.cat-rec-headline a, .cat-featured-title a').forEach((el) => {
      const full = el.getAttribute('data-full-title') || el.textContent;
      truncateMiddleToLines(el, full, 2);
    });
  };

  requestAnimationFrame(() => requestAnimationFrame(run));
}

function fillNewIssue() {
  const lists = document.querySelectorAll('.cat-new-issue-widget .cat-new-issue-list');
  (lists.length ? lists : document.querySelectorAll('#catNewIssueList')).forEach(initOneNewIssue);
}

function initOneNewIssue(list) {
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
  let busy = false;
  const prev = widget?.querySelector('.cat-new-issue-arrow--prev');
  const next = widget?.querySelector('.cat-new-issue-arrow--next');
  const arrows = widget?.querySelector('.cat-new-issue-arrows');
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

  const paintCover = (issue, { skeletonFirst = false } = {}) => {
    const href = issue.flipbook || issue.link || issue.permalink || '#';
    const title = String(issue.title || `العدد ${issue.number || ''}`).replace(/"/g, '&quot;');
    const coverSrc = String(issue.cover || issue.src || '');
    if (!coverSrc) {
      busy = false;
      return;
    }
    let cover = list.querySelector('a.cat-new-issue-cover');
    let img = cover && cover.querySelector('img');

    if (!cover || !img) {
      list.innerHTML = `
        <a href="${href}" class="cat-new-issue-cover" target="_blank" rel="noopener">
          <span class="sk sk-cover" aria-hidden="true"></span>
          <img alt="${title}" decoding="async">
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
      busy = false;
      return;
    }

    img.onload = null;
    img.onerror = null;

    // Instant skeleton overlay while the next cover preloads.
    if (skeletonFirst) {
      cover.classList.remove('is-ready');
      cover.classList.add('is-switching');
      // Force paint so shimmer is visible before a cached load finishes.
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
        busy = false;
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

    // Preload next cover, keep skeleton up until ready (+ min duration).
    const pre = new Image();
    pre.onload = reveal;
    pre.onerror = reveal;
    pre.src = coverSrc;
    if (pre.complete && pre.naturalWidth) {
      // Still defer to next frame so is-switching paints first.
      requestAnimationFrame(reveal);
    }
    safetyTimer = setTimeout(reveal, 2500);
  };

  const show = (nextIndex, fromClick = false) => {
    if (busy && fromClick) return;
    if (issues.length < 2 && fromClick) return;
    busy = !!fromClick;
    index = (nextIndex + issues.length) % issues.length;
    paintCover(issues[index], { skeletonFirst: fromClick });
  };

  // Align index with SSR first cover when possible.
  const ssrCover = list.querySelector('a.cat-new-issue-cover img[src]');
  if (ssrCover) {
    const ssrSrc = ssrCover.getAttribute('src') || '';
    const matchIdx = issues.findIndex((i) => (i.cover || i.src) === ssrSrc);
    if (matchIdx >= 0) index = matchIdx;
    const cover = ssrCover.closest('.cat-new-issue-cover');
    if (ssrCover.complete && ssrCover.naturalWidth) {
      cover && cover.classList.add('is-ready');
    } else {
      ssrCover.onload = () => cover && cover.classList.add('is-ready');
    }
  } else {
    show(0, false);
  }

  syncArrows();

  if (prev) prev.onclick = (e) => {
    e.preventDefault();
    show(index - 1, true);
  };
  if (next) next.onclick = (e) => {
    e.preventDefault();
    show(index + 1, true);
  };
}

async function fillMostRead() {
  const list = document.querySelector('.cat-mr-list');
  if (!list) return;
  // Explicit category / manual picks are SSR'd — do not replace with the current archive category.
  if (list.getAttribute('data-preserve') === '1') return;

  const { fetchPostsByCategory } = getWp();
  const { data } = getCurrentCategory();
  const pinnedId = Number(list.getAttribute('data-category-id') || 0);
  const wpId = pinnedId > 0 ? pinnedId : data?.wpCategoryId;
  if (!fetchPostsByCategory || !wpId) return;

  const posts = (await fetchPostsByCategory(wpId, 7)).slice(0, 7);
  const widget = list.closest('.cat-most-read-widget');
  if (!posts.length) {
    list.hidden = true;
    if (widget && !widget.querySelector('.cat-mr-empty')) {
      const box = document.createElement('article');
      box.className = 'cat-hero-card cat-mr-empty';
      box.innerHTML = '<div class="cat-hero-info"><h2 class="cat-hero-headline">لا مقالات في هذا القسم بعد</h2></div>';
      widget.appendChild(box);
    }
    return;
  }
  widget?.querySelector('.cat-mr-empty')?.remove();
  list.hidden = false;

  list.innerHTML = posts.map((p, idx) => `
    <li class="cat-mr-item">
      <span class="cat-mr-num">${idx + 1}</span>
      <div class="cat-mr-content">
        <h4 class="cat-mr-headline">
          <a href="${articleHref(p)}">${String(p.title || '').replace(/</g, '&lt;')}</a>
        </h4>
      </div>
    </li>
  `).join('');
}

function fillHero(post, title, trimAtWord) {
  if (!post) return;
  const hero = document.getElementById('catHeroCard');
  if (!hero) return;

  const headline = hero.querySelector('.cat-hero-headline a');
  if (headline) {
    headline.setAttribute('data-full-title', post.title);
    headline.textContent = post.title;
    headline.href = articleHref(post);
    headline.removeAttribute('target');
  }

  const synopsis = hero.querySelector('.cat-hero-synopsis');
  if (synopsis) {
    const exc = trimAtWord
      ? trimAtWord(post.fullExcerpt || post.excerpt || '', 520)
      : post.excerpt;
    if (exc) synopsis.textContent = exc;
  }

  const time = hero.querySelector('.lead-time, .cat-hero-meta .lead-time');
  if (time) time.textContent = post.dateText || post.timeText;

  const img = hero.querySelector('.cat-hero-img-wrap img');
  if (img) {
    img.src = post.img;
    img.alt = post.title;
  }
}

function fillRecommended(posts, title) {
  const list = document.getElementById('recList');
  if (!list || !posts.length) return;

  list.innerHTML = posts
    .map(
      (p) => `
    <div class="cat-rec-item">
      <img src="${p.img}" alt="${p.title}" class="cat-rec-img">
      <div class="cat-rec-content">
        <h4 class="cat-rec-headline">
          <a href="${articleHref(p)}" data-full-title="${p.title.replace(/"/g, '&quot;')}">${p.title}</a>
        </h4>
        <span class="cat-rec-time">${p.dateText || p.timeText}</span>
      </div>
    </div>`
    )
    .join('');
}

function fillFeatured(posts, title) {
  const cards = document.querySelectorAll('.cat-featured-row-4col .cat-featured-card');
  if (!cards.length || !posts.length) return;

  cards.forEach((card, idx) => {
    const p = posts[idx];
    if (!p) {
      card.hidden = true;
      return;
    }
    card.hidden = false;

    const imgLink = card.querySelector('.cat-featured-img-link');
    const img = card.querySelector('.cat-featured-img-wrap img');
    if (imgLink) {
      imgLink.href = articleHref(p);
      imgLink.removeAttribute('target');
    }
    if (img) {
      img.src = p.img;
      img.alt = p.title;
    }

    const titleLink = card.querySelector('.cat-featured-title a');
    if (titleLink) {
      titleLink.setAttribute('data-full-title', p.title);
      titleLink.textContent = p.title;
      titleLink.href = articleHref(p);
      titleLink.removeAttribute('target');
    }

    const date = card.querySelector('.cat-featured-date');
    if (date) date.textContent = p.dateText || p.timeText || '';

    const tag = card.querySelector('.cat-featured-tag');
    const listedName = title || window.AltijaraCategory?.name || p.category || '';
    if (tag && listedName) tag.textContent = listedName;
  });
}

function fillNewsList(posts, title, append = false) {
  const list = document.getElementById('catNewsList');
  if (!list) return;

  if (!append) {
    // Keep only dynamic items — clear static dummy + extras
    list.querySelectorAll('.cat-news-item').forEach((el) => el.remove());
    const extra = document.getElementById('catExtraArticles');
    if (extra) extra.remove();
  }

  posts.forEach((p) => {
    const el = document.createElement('article');
    el.className = 'cat-news-item';
    el.setAttribute('data-category', 'all');
    const { trimAtWord } = getWp();
    const excerpt = trimAtWord
      ? trimAtWord(p.excerpt || p.fullExcerpt || '', 140)
      : (p.excerpt || '');
    el.innerHTML = `
      <div class="cat-news-img-wrap">
        <img src="${p.img}" alt="${p.title}" loading="lazy">
      </div>
      <div class="cat-news-content">
        <h3 class="cat-news-headline">
          <a href="${articleHref(p)}">${p.title}</a>
        </h3>
        <p class="cat-news-excerpt">${excerpt}</p>
          <div class="cat-news-meta-row">
            <div class="cat-news-tags-group">
              <span class="cat-news-time">${p.dateText || p.timeText}</span>
            </div>
          ${altijaraSaveButtonsOn() ? `<button type="button" class="btn-cat-save" data-post-id="${p.id || ''}" onclick="typeof toggleSaveArticle==='function'&&toggleSaveArticle(this)" title="حفظ المقال">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <span>حفظ</span>
          </button>` : ''}
        </div>
      </div>
    `;
    list.appendChild(el);
  });
  window.AltijaraBookmarksAPI?.hydrate?.(list);
}

function toggleFollowCategory() {
  const catKey = getCurrentCategoryKey();
  const btn = document.getElementById('btnFollowCat');
  const text = document.getElementById('followBtnText');
  if (!btn || !text) return;

  const following = btn.classList.contains('is-following');
  btn.classList.toggle('is-following', !following);
  text.textContent = following ? 'متابعة التصنيف' : 'تمت المتابعة ✓';
  localStorage.setItem(`followed_cat_${catKey}`, following ? 'false' : 'true');
}

function filterSubcategory(subcatId, pillBtn) {
  document.querySelectorAll('.cat-sub-pill').forEach((p) => p.classList.remove('active'));
  if (pillBtn) pillBtn.classList.add('active');

  // Live WP posts are untagged by sub-pill; "all" shows everything.
  // Sub-pills keep UI state for future WP child-category filtering.
  document.querySelectorAll('.cat-news-item').forEach((item) => {
    item.style.display = 'flex';
  });
}

async function goToCategoryPage(page, opts = {}) {
  const target = Number(page);
  if (pagingBusy || !target || target < 1 || target > totalPages || target === currentPage) return;

  const pager = document.getElementById('catPagination');
  if (pager && pager.getAttribute('data-ajax') === '0') {
    const url = new URL(window.location.href);
    url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
    url.searchParams.delete('paged');
    if (target > 1) {
      url.pathname = url.pathname.replace(/\/?$/, `/page/${target}/`);
    }
    window.location.assign(url.toString());
    return;
  }

  const { data } = getCurrentCategory();
  const wpId = data?.wpCategoryId || window.AltijaraCategory?.termId;
  const { fetchCategoryPage, trimAtWord } = getWp();
  if (!wpId || !fetchCategoryPage) return;

  pagingBusy = true;
  setPaginationLoading(true);

  const counts = layoutCounts();
  const newsSize = Number(window.AltijaraCategory?.newsSize) || NEWS_PAGE_SIZE;
  const spotlight = Number(window.AltijaraCategory?.spotlight) || counts.spotlight;
  const offset = target === 1 ? 0 : spotlight + (target - 1) * newsSize;
  const perPage = target === 1 ? spotlight + newsSize : newsSize;

  showNewsSkeleton(newsSize);

  try {
    const pageData = await fetchCategoryPage(wpId, perPage, 1, offset);
    if (!pageData.posts?.length) {
      hideNewsSkeleton();
      pagingBusy = false;
      setPaginationLoading(false);
      return;
    }

    const total = pageData.total || window.AltijaraCategory?.total || pageData.posts.length;
    if (target === 1) {
      applyCategoryPosts(pageData.posts, data, trimAtWord, target, total);
    } else {
      currentPage = target;
      totalPages = newsPagesFromTotal(total);
      fillNewsList(pageData.posts.slice(0, newsSize), data?.title || '', false);
      hideNewsSkeleton();
      renderPagination();
      getWp().localizeArticleLinks?.(document);
    }
    scheduleTitleTruncation();

    if (opts.push !== false) {
      updateCategoryPageUrl(target);
    }

    requestAnimationFrame(() => {
      const newsCol =
        document.querySelector('.cat-news-col') ||
        document.querySelector('.cat-section-bar') ||
        document.getElementById('catNewsList');
      if (newsCol) {
        newsCol.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  } catch (err) {
    console.warn('Category pagination failed:', err);
    hideNewsSkeleton();
  }

  pagingBusy = false;
  setPaginationLoading(false);
}

function updateCategoryPageUrl(page) {
  try {
    const url = new URL(window.location.href);
    if (page <= 1) {
      url.searchParams.delete('paged');
      url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
    } else {
      url.searchParams.set('paged', String(page));
    }
    history.pushState({ catPage: page }, '', url);
  } catch (e) {
    /* ignore */
  }
}

function showNewsSkeleton(count = 5) {
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

  if (totalPages <= 1) {
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
    prev.addEventListener('click', () => goToCategoryPage(currentPage - 1));
    next.addEventListener('click', () => goToCategoryPage(currentPage + 1));
    nums.addEventListener('click', (e) => {
      const btn = e.target.closest('.cat-page-num');
      if (!btn || btn.classList.contains('is-active')) return;
      goToCategoryPage(Number(btn.dataset.page));
    });
  }
}

function initSearchToggle() {
  if (window.__altijaraSearchReady) return;
  if (typeof window.SiteChrome?.initGlobalSearch === 'function') {
    window.SiteChrome.initGlobalSearch();
  }
}
