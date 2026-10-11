/**
 * Category Page — live WordPress content by category
 * Metadata from site-chrome.js (window.CATEGORIES_DATA)
 * Helpers from wp-api.js (window.AltijaraWP)
 */

const NEWS_PAGE_SIZE = 10;
const FEATURED_COUNT = 8;
const FULL_HERO_CATS = new Set(['automotive', 'interviews', 'tourism', 'exhibitions']);

function isFullHeroCategory(key) {
  return FULL_HERO_CATS.has(key || getCurrentCategoryKey());
}

function layoutCounts(key) {
  const rec = isFullHeroCategory(key) ? 0 : 3;
  const spotlight = 1 + rec + FEATURED_COUNT;
  return { rec, featured: FEATURED_COUNT, spotlight, firstBatch: spotlight + NEWS_PAGE_SIZE };
}
let currentPage = 1;
let totalPages = 1;
let pagingBusy = false;

function getCategoriesData() {
  return window.CATEGORIES_DATA || {};
}

function getCurrentCategoryKey() {
  return new URLSearchParams(window.location.search).get('cat') || 'chamber';
}

function getCurrentCategory() {
  const key = getCurrentCategoryKey();
  return { key, data: getCategoriesData()[key] };
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
  if (topGrid) topGrid.classList.toggle('is-hero-full', isFullHeroCategory(catKey));

  const isFollowed = localStorage.getItem(`followed_cat_${catKey}`) === 'true';
  const btnFollow = document.getElementById('btnFollowCat');
  const followText = document.getElementById('followBtnText');
  if (btnFollow && followText && isFollowed) {
    btnFollow.classList.add('is-following');
    followText.textContent = 'تمت المتابعة ✓';
  }
}

function applyCategoryLabels(title) {
  document.querySelectorAll('.cat-featured-tag').forEach((el) => {
    el.textContent = title;
  });
}

async function loadCategoryLiveContent() {
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
  const remaining = Math.max(0, (Number(total) || 0) - layoutCounts().spotlight);
  if (!remaining) return 1;
  return Math.max(1, Math.ceil(remaining / NEWS_PAGE_SIZE));
}

function applyCategoryPosts(posts, data, trimAtWord, page = 1, total = 0) {
  currentPage = page;
  totalPages = newsPagesFromTotal(total || posts.length);

  const counts = layoutCounts();
  if (page === 1) {
    fillHero(posts[0], data.title, trimAtWord);
    if (counts.rec) fillRecommended(posts.slice(1, 1 + counts.rec), data.title);
    fillFeatured(posts.slice(1 + counts.rec, counts.spotlight), data.title);
    fillNewsList(posts.slice(counts.spotlight, counts.firstBatch), data.title, false);
  } else {
    fillNewsList(posts.slice(0, NEWS_PAGE_SIZE), data.title, false);
  }

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
  const list = document.getElementById('catNewIssueList');
  if (!list) return;

  const issues = (window.AltijaraIssues || window.AltijaraRecentIssues || [])
    .filter((i) => i && (i.flipbook || i.link))
    .slice(0, 6);
  const widget = list.closest('.cat-new-issue-widget');
  if (!issues.length) {
    if (widget) widget.hidden = true;
    return;
  }
  if (widget) widget.hidden = false;

  let index = 0;
  const markReady = (el) => {
    if (el) el.classList.add('is-ready');
  };
  const show = (next) => {
    index = (next + issues.length) % issues.length;
    const issue = issues[index];
    const href = issue.flipbook || issue.link;
    const title = String(issue.title || `العدد ${issue.number}`).replace(/"/g, '&quot;');
    const coverSrc = issue.cover || issue.src || '';
    list.innerHTML = `
      <a href="${href}" class="cat-new-issue-cover" target="_blank" rel="noopener">
        <span class="sk sk-cover" aria-hidden="true"></span>
        <img src="${coverSrc}" alt="${title}" loading="lazy" decoding="async">
      </a>`;
    const cover = list.querySelector('.cat-new-issue-cover');
    const img = list.querySelector('img');
    if (!img) return;
    const fallback = 'assets/images/issue-730-clean.png';
    img.onerror = () => {
      img.onerror = null;
      img.src = fallback;
    };
    if (img.complete && img.naturalWidth) markReady(cover);
    else img.onload = () => markReady(cover);
  };
  show(0);

  const prev = widget?.querySelector('.cat-new-issue-arrow--prev');
  const next = widget?.querySelector('.cat-new-issue-arrow--next');
  const arrows = widget?.querySelector('.cat-new-issue-arrows');
  if (arrows) arrows.hidden = issues.length < 2;
  if (prev) prev.onclick = () => show(index - 1);
  if (next) next.onclick = () => show(index + 1);
}

async function fillMostRead() {
  const list = document.querySelector('.cat-mr-list');
  if (!list) return;

  const { fetchPostsByCategory } = getWp();
  const { data } = getCurrentCategory();
  const wpId = data?.wpCategoryId;
  if (!fetchPostsByCategory || !wpId) return;

  const posts = (await fetchPostsByCategory(wpId, 7)).slice(0, 7);
  if (!posts.length) {
    list.closest('.cat-most-read-widget')?.setAttribute('hidden', '');
    return;
  }

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

    const tag = card.querySelector('.cat-featured-tag');
    if (tag) tag.textContent = title;

    const titleLink = card.querySelector('.cat-featured-title a');
    if (titleLink) {
      titleLink.setAttribute('data-full-title', p.title);
      titleLink.textContent = p.title;
      titleLink.href = articleHref(p);
      titleLink.removeAttribute('target');
    }

    const date = card.querySelector('.cat-featured-date');
    if (date) date.textContent = p.dateText || p.timeText || '';
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
          <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <span>حفظ</span>
          </button>
        </div>
      </div>
    `;
    list.appendChild(el);
  });
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

function toggleSaveArticle(btn) {
  const isSaved = btn.classList.contains('saved');
  const span = btn.querySelector('span');
  if (isSaved) {
    btn.classList.remove('saved');
    btn.style.color = '#64748b';
    if (span) span.textContent = 'حفظ';
  } else {
    btn.classList.add('saved');
    btn.style.color = 'var(--color-brand, #1897B8)';
    if (span) span.textContent = 'تم الحفظ';
  }
}

async function goToCategoryPage(page) {
  const target = Number(page);
  if (pagingBusy || !target || target < 1 || target > totalPages || target === currentPage) return;

  const { data } = getCurrentCategory();
  const wpId = data?.wpCategoryId;
  const { fetchCategoryPage, trimAtWord } = getWp();
  if (!wpId || !fetchCategoryPage) return;

  pagingBusy = true;
  setPaginationLoading(true);

  const counts = layoutCounts();
  const offset = target === 1 ? 0 : counts.spotlight + (target - 1) * NEWS_PAGE_SIZE;
  const perPage = target === 1 ? counts.firstBatch : NEWS_PAGE_SIZE;
  const pageData = await fetchCategoryPage(wpId, perPage, 1, offset);
  if (!pageData.posts?.length) {
    pagingBusy = false;
    setPaginationLoading(false);
    return;
  }

  applyCategoryPosts(
    pageData.posts,
    data,
    trimAtWord,
    target,
    pageData.total || pageData.posts.length
  );
  scheduleTitleTruncation();

  const list = document.getElementById('catNewsList');
  if (list) {
    list.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  pagingBusy = false;
  setPaginationLoading(false);
}

function setPaginationLoading(isLoading) {
  const wrap = document.getElementById('catPagination');
  if (!wrap) return;
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
