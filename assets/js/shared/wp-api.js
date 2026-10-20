/**
 * Shared WordPress REST helpers for مجلة التجارة
 * IIFE avoids clashing with app.js / category.js top-level names.
 * Includes sessionStorage cache for instant page switches.
 */
(function (global) {
  const API_BASE = (global.AltijaraTheme && global.AltijaraTheme.urls && global.AltijaraTheme.urls.rest)
    ? String(global.AltijaraTheme.urls.rest).replace(/\/$/, '')
    : 'https://altijaramagazine.com/wp-json/wp/v2';
  const CACHE_PREFIX = 'altijara_wp_';
  const CACHE_TTL_MS = 10 * 60 * 1000; // 10 minutes

  function cacheKey(endpoint) {
    return CACHE_PREFIX + endpoint;
  }

  function readCache(endpoint) {
    try {
      const raw = sessionStorage.getItem(cacheKey(endpoint));
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || !Array.isArray(parsed.data)) return null;
      if (Date.now() - (parsed.ts || 0) > CACHE_TTL_MS) return null;
      return parsed.data;
    } catch (e) {
      return null;
    }
  }

  function writeCache(endpoint, data) {
    try {
      sessionStorage.setItem(
        cacheKey(endpoint),
        JSON.stringify({ ts: Date.now(), data })
      );
    } catch (e) {
      /* quota / private mode */
    }
  }

  /** Sync: return cached posts immediately (or null) */
  function getCachedArticles(endpoint = '/posts?per_page=15&_embed') {
    return readCache(endpoint);
  }

  function decodeHtml(html) {
    if (!html) return '';
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
  }

  const MONTHS_AR = [
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
  ];

  function formatRelativeTime(dateString) {
    return formatShortDate(dateString);
  }

  /** Always: "16 سبتمبر 2026" (Gregorian, Latin digits) */
  function formatShortDate(dateString) {
    try {
      const raw = String(dateString || '');
      const iso = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (iso) {
        const day = parseInt(iso[3], 10);
        const month = parseInt(iso[2], 10) - 1;
        const year = iso[1];
        return `${day} ${MONTHS_AR[month]} ${year}`;
      }
      const d = new Date(dateString);
      if (Number.isNaN(d.getTime())) return '';
      return `${d.getDate()} ${MONTHS_AR[d.getMonth()]} ${d.getFullYear()}`;
    } catch (e) {
      return '';
    }
  }

  function trimAtWord(text, maxLen) {
    if (!text || text.length <= maxLen) return text || '';
    const cut = text.slice(0, maxLen);
    const lastSpace = cut.lastIndexOf(' ');
    return (lastSpace > 100 ? cut.slice(0, lastSpace) : cut).trim() + '...';
  }

  function truncateMiddleToLines(el, text, maxLines) {
    if (!el || !text) return;
    const full = String(text).trim();
    el.setAttribute('title', full);
    el.setAttribute('data-full-title', full);

    const prev = {
      display: el.style.display,
      webkitBoxOrient: el.style.webkitBoxOrient,
      webkitLineClamp: el.style.webkitLineClamp,
      overflow: el.style.overflow,
      maxHeight: el.style.maxHeight
    };
    el.style.display = 'block';
    el.style.webkitBoxOrient = 'initial';
    el.style.webkitLineClamp = 'unset';
    el.style.overflow = 'visible';
    el.style.maxHeight = 'none';
    el.textContent = full;

    const style = window.getComputedStyle(el);
    let lineHeight = parseFloat(style.lineHeight);
    if (!lineHeight || Number.isNaN(lineHeight)) {
      lineHeight = parseFloat(style.fontSize) * 1.35;
    }
    const maxHeight = lineHeight * maxLines + 2;

    if (el.scrollHeight <= maxHeight) {
      Object.assign(el.style, {
        display: prev.display,
        webkitBoxOrient: prev.webkitBoxOrient,
        webkitLineClamp: prev.webkitLineClamp,
        overflow: prev.overflow,
        maxHeight: prev.maxHeight
      });
      return;
    }

    const words = full.split(/\s+/).filter(Boolean);
    if (words.length < 4) {
      Object.assign(el.style, {
        display: prev.display,
        webkitBoxOrient: prev.webkitBoxOrient,
        webkitLineClamp: prev.webkitLineClamp,
        overflow: prev.overflow,
        maxHeight: prev.maxHeight
      });
      return;
    }

    const endCount = Math.min(3, Math.max(2, Math.floor(words.length / 4)));
    const end = words.slice(-endCount).join(' ');
    const maxStart = words.length - endCount - 1;

    let lo = 1;
    let hi = maxStart;
    let best = words.slice(0, 1).join(' ') + ' ... ' + end;

    while (lo <= hi) {
      const mid = Math.floor((lo + hi) / 2);
      const candidate = words.slice(0, mid).join(' ') + ' ... ' + end;
      el.textContent = candidate;
      if (el.scrollHeight <= maxHeight) {
        best = candidate;
        lo = mid + 1;
      } else {
        hi = mid - 1;
      }
    }

    el.textContent = best;
    el.style.display = 'block';
    el.style.webkitLineClamp = 'unset';
    el.style.overflow = 'hidden';
    el.style.maxHeight = `${maxHeight}px`;
  }

  function formatPostData(p) {
    // Already formatted (from cache)
    if (p && p.title && p.link && !p.title?.rendered) {
      return p;
    }

    const title = decodeHtml(p.title?.rendered || '');
    const cleanExcerpt = decodeHtml(
      (p.excerpt?.rendered || '').replace(/<[^>]+>/g, '').replace(/\[&hellip;\]/g, '').trim()
    );
    const cleanContent = decodeHtml(
      (p.content?.rendered || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
    );
    const fullExcerpt = cleanContent || cleanExcerpt;
    const excerpt = trimAtWord(cleanExcerpt || fullExcerpt, 280);
    const link = p.link || '#';
    const slug = p.slug || slugFromPermalink(link);
    const date = p.date;
    const timeText = formatRelativeTime(date);
    const dateText = formatShortDate(date);

    // Prefer a real site section (سيارات, مال وأعمال, …) over generic منوعات
    let category = 'متابعات';
    const terms = p._embedded?.['wp:term']?.[0] || [];
    if (terms.length) {
      const sectionTitles = new Set(
        Object.values(global.CATEGORIES_DATA || {}).map((d) => d.title).filter(Boolean)
      );
      const preferred = terms.find((t) => {
        const name = decodeHtml(t?.name || '');
        return name && name !== 'منوعات' && sectionTitles.has(name);
      }) || terms.find((t) => {
        const name = decodeHtml(t?.name || '');
        return name && name !== 'منوعات';
      }) || terms[0];
      category = decodeHtml(preferred?.name || category);
      if (category.startsWith('_')) category = category.replace('_', '');
    }

    let img = 'https://altijaramagazine.com/wp-content/uploads/2026/09/altijara-magazine-issue-730-3.jpg';
    if (p._embedded?.['wp:featuredmedia']?.[0]) {
      const media = p._embedded['wp:featuredmedia'][0];
      img = media.source_url || media.media_details?.sizes?.medium?.source_url || img;
    }

    return { id: p.id, slug, title, excerpt, fullExcerpt, link, date, timeText, dateText, category, img };
  }

  async function fetchLiveArticles(endpoint = '/posts?per_page=15&_embed', options = {}) {
    const { bypassCache = false } = options;

    if (!bypassCache) {
      const cached = readCache(endpoint);
      if (cached && cached.length) {
        // Return cache immediately; refresh quietly in background
        refreshInBackground(endpoint);
        return cached;
      }
    }

    try {
      const res = await fetch(`${API_BASE}${endpoint}`, {
        headers: { Accept: 'application/json' }
      });
      if (!res.ok) throw new Error(`API returned status ${res.status}`);
      const data = await res.json();
      if (!Array.isArray(data)) return [];
      const formatted = data.map(formatPostData);
      writeCache(endpoint, formatted);
      return formatted;
    } catch (err) {
      console.warn('Altijara live fetch notice:', err.message);
      const stale = readCache(endpoint);
      return stale || [];
    }
  }

  function refreshInBackground(endpoint) {
    if (refreshInBackground._pending?.[endpoint]) return;
    refreshInBackground._pending = refreshInBackground._pending || {};
    refreshInBackground._pending[endpoint] = true;

    fetch(`${API_BASE}${endpoint}`, { headers: { Accept: 'application/json' } })
      .then((res) => (res.ok ? res.json() : Promise.reject()))
      .then((data) => {
        if (Array.isArray(data)) writeCache(endpoint, data.map(formatPostData));
      })
      .catch(() => {})
      .finally(() => {
        delete refreshInBackground._pending[endpoint];
      });
  }

  function slugFromPermalink(link) {
    try {
      const url = new URL(link, 'https://altijaramagazine.com');
      if (!/altijaramagazine\.com$/i.test(url.hostname)) return '';
      const parts = url.pathname.split('/').filter(Boolean);
      if (parts.length !== 1) return '';
      const slug = decodeURIComponent(parts[0]);
      if (/\.(html?|php|pdf|jpe?g|png|webp|gif)$/i.test(slug)) return '';
      return slug;
    } catch (e) {
      return '';
    }
  }

  function isArticlePermalink(href) {
    return Boolean(slugFromPermalink(href));
  }

  /** Prefer native permalinks (SSR / REST link|url). */
  function articlePageUrl(post, catKey) {
    if (post && (post.url || post.link)) {
      return post.url || post.link;
    }
    const slug = (post && post.slug) || slugFromPermalink(post && post.link);
    let url = 'article.html';
    if (slug) url += '?slug=' + encodeURIComponent(slug);
    else if (post && post.id) url += '?id=' + encodeURIComponent(post.id);
    const key = catKey || (post && post.catKey) || '';
    if (key && global.CATEGORIES_DATA?.[key]) {
      url += (url.indexOf('?') >= 0 ? '&' : '?') + 'cat=' + encodeURIComponent(key);
    }
    return url;
  }

  function localizeArticleLinks(root) {
    const scope = root && root.querySelectorAll ? root : document;
    const pageCat = new URLSearchParams(global.location.search).get('cat') || '';
    scope.querySelectorAll('a[href]').forEach((anchor) => {
      if (anchor.classList.contains('section-more-nav')) return;
      if (anchor.classList.contains('cat-sidebar-ad-link')) return;
      if (anchor.classList.contains('btn-card-read-action')) return;
      if (anchor.closest('.issue-card-ft')) return;
      const href = anchor.getAttribute('href') || '';
      if (/article\.html/i.test(href)) {
        // Keep existing cat= if present; otherwise attach page cat
        if (pageCat && !/[?&]cat=/.test(href) && global.CATEGORIES_DATA?.[pageCat]) {
          anchor.href = href + (href.indexOf('?') >= 0 ? '&' : '?') + 'cat=' + encodeURIComponent(pageCat);
        }
        anchor.removeAttribute('target');
        return;
      }
      if (isArticlePermalink(href)) {
        anchor.href = articlePageUrl({ link: href }, pageCat || undefined);
        anchor.removeAttribute('target');
        return;
      }
      if (href.indexOf('article.html') !== -1) anchor.removeAttribute('target');
    });
  }

  function getWpCategoryId(catKey) {
    const data = global.CATEGORIES_DATA?.[catKey];
    return data?.wpCategoryId || null;
  }

  function categoryEndpoint(wpId, perPage = 6, page = 1, offset = 0) {
    const off = Number(offset) || 0;
    if (off > 0) {
      return `/posts?categories=${wpId}&per_page=${perPage}&offset=${off}&_embed`;
    }
    return `/posts?categories=${wpId}&per_page=${perPage}&page=${page}&_embed`;
  }

  function fetchPostsByCategory(wpId, perPage = 6, page = 1, options) {
    if (!wpId) return Promise.resolve([]);
    return fetchLiveArticles(categoryEndpoint(wpId, perPage, page), options);
  }

  function getCachedPostsByCategory(wpId, perPage = 6, page = 1) {
    if (!wpId) return null;
    return getCachedArticles(categoryEndpoint(wpId, perPage, page));
  }

  function readPageMeta(endpoint) {
    try {
      const raw = sessionStorage.getItem(cacheKey(endpoint) + '_meta');
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || Date.now() - (parsed.ts || 0) > CACHE_TTL_MS) return null;
      return parsed.data || null;
    } catch (e) {
      return null;
    }
  }

  function writePageMeta(endpoint, meta) {
    try {
      sessionStorage.setItem(
        cacheKey(endpoint) + '_meta',
        JSON.stringify({ ts: Date.now(), data: meta })
      );
    } catch (e) {
      /* ignore */
    }
  }

  /** Returns { posts, totalPages, total } including WP pagination headers */
  async function fetchCategoryPage(wpId, perPage = 10, page = 1, offset = 0) {
    if (!wpId) return { posts: [], totalPages: 1, total: 0 };
    const endpoint = categoryEndpoint(wpId, perPage, page, offset);

    try {
      const res = await fetch(`${API_BASE}${endpoint}`, {
        headers: { Accept: 'application/json' }
      });
      if (!res.ok) throw new Error(`API returned status ${res.status}`);
      const data = await res.json();
      const posts = Array.isArray(data) ? data.map(formatPostData) : [];
      const totalPages = Math.max(1, parseInt(res.headers.get('X-WP-TotalPages') || '1', 10) || 1);
      const total = parseInt(res.headers.get('X-WP-Total') || String(posts.length), 10) || posts.length;
      writeCache(endpoint, posts);
      writePageMeta(endpoint, { totalPages, total });
      return { posts, totalPages, total };
    } catch (err) {
      console.warn('Altijara category page fetch notice:', err.message);
      const posts = readCache(endpoint) || [];
      const meta = readPageMeta(endpoint) || {};
      return {
        posts,
        totalPages: meta.totalPages || (posts.length < perPage ? 1 : Math.max(1, page)),
        total: meta.total || posts.length
      };
    }
  }

  global.AltijaraWP = {
    API_BASE,
    decodeHtml,
    formatRelativeTime,
    formatShortDate,
    trimAtWord,
    truncateMiddleToLines,
    formatPostData,
    fetchLiveArticles,
    getCachedArticles,
    getWpCategoryId,
    fetchPostsByCategory,
    getCachedPostsByCategory,
    fetchCategoryPage,
    categoryEndpoint,
    articlePageUrl,
    localizeArticleLinks
  };
})(window);
