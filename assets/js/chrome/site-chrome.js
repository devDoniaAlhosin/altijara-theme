/**
 * Site chrome — shared header + footer + navigation.
 * Source copies also live in components/header.html and components/footer.html.
 */
const CATEGORIES_DATA = {
  chamber: {
    title: "أخبار الغرفة",
    docTitle: "أخبار الغرفة التجارية بجدة — فعاليات ومبادرات | مجلة التجارة",
    wpCategoryId: 188,
    pills: [
      { id: "all", label: "الكل" },
      { id: "events", label: "مؤتمرات وفعاليات" },
      { id: "agreements", label: "اتفاقيات وشراكات" },
      { id: "committees", label: "اللجان القطاعية" },
      { id: "services", label: "خدمات المشتركين" }
    ]
  },
  exhibitions: {
    title: "المعارض",
    docTitle: "المعارض — فعاليات وملتقيات الأعمال | مجلة التجارة",
    wpCategoryId: 189,
    pills: [
      { id: "all", label: "الكل" },
      { id: "trade", label: "معارض تجارية" },
      { id: "industry", label: "معارض صناعية" },
      { id: "local", label: "فعاليات جدة" }
    ]
  },
  followups: {
    title: "متابعات",
    docTitle: "متابعات — أبرز الأخبار الاقتصادية | مجلة التجارة",
    wpCategoryId: 190,
    pills: [
      { id: "all", label: "الكل" },
      { id: "local", label: "محليات" },
      { id: "markets", label: "أسواق" },
      { id: "policy", label: "سياسات وتشريعات" }
    ]
  },
  interviews: {
    title: "لقاءات",
    docTitle: "لقاءات — حوارات وشخصيات الأعمال | مجلة التجارة",
    wpCategoryId: 319,
    pills: [
      { id: "all", label: "الكل" },
      { id: "ceos", label: "رؤساء تنفيذيون" },
      { id: "leaders", label: "قيادات وطنية" },
      { id: "experts", label: "خبراء وتحليلات" }
    ]
  },
  business: {
    title: "مال وأعمال",
    docTitle: "مال وأعمال — تغطية الأسواق والشركات والاستثمار | مجلة التجارة",
    wpCategoryId: 320,
    pills: [
      { id: "all", label: "الكل" },
      { id: "companies", label: "شركات واستثمار" },
      { id: "banking", label: "بنوك وتمويل" },
      { id: "markets", label: "أسواق وعقارات" },
      { id: "startups", label: "شركات ناشئة وريادة" }
    ]
  },
  logistics: {
    title: "لوجستيات",
    docTitle: "لوجستيات — موانئ وسلاسل إمداد ونقل | مجلة التجارة",
    wpCategoryId: 321,
    pills: [
      { id: "all", label: "الكل" },
      { id: "ports", label: "الموانئ والنقل البحري" },
      { id: "supply", label: "سلاسل الإمداد" },
      { id: "air", label: "الشحن الجوي والبري" },
      { id: "zones", label: "المناطق اللوجستية" }
    ]
  },
  industry: {
    title: "الصناعة",
    docTitle: "الصناعة — توطين ومدن صناعية | مجلة التجارة",
    wpCategoryId: 373,
    pills: [
      { id: "all", label: "الكل" },
      { id: "manufacturing", label: "الصناعات التحويلية" },
      { id: "modon", label: "مدن ومناطق صناعية" },
      { id: "mining", label: "التعدين والموارد" }
    ]
  },
  energy: {
    title: "الطاقة",
    docTitle: "الطاقة — استدامة وطاقة متجددة | مجلة التجارة",
    wpCategoryId: 372,
    pills: [
      { id: "all", label: "الكل" },
      { id: "renewables", label: "الطاقة المتجددة" },
      { id: "oilgas", label: "النفط والغاز" },
      { id: "hydrogen", label: "الهيدروجين النظيف" }
    ]
  },
  automotive: {
    title: "سيارات",
    docTitle: "سيارات — صناعة وتنقل مستدام | مجلة التجارة",
    wpCategoryId: 194,
    pills: [
      { id: "all", label: "الكل" },
      { id: "ev", label: "مركبات كهربائية" },
      { id: "dealers", label: "وكلاء وتوزيع" },
      { id: "manufacturing", label: "تجميع وتوطين" }
    ]
  },
  health: {
    title: "صحة",
    docTitle: "صحة — رعاية صحية وابتكار طبي | مجلة التجارة",
    wpCategoryId: 326,
    pills: [
      { id: "all", label: "الكل" },
      { id: "hospitals", label: "مستشفيات ورعاية" },
      { id: "pharma", label: "صناعات دوائية" },
      { id: "wellness", label: "صحة عامة" }
    ]
  },
  tech: {
    title: "تقنية",
    docTitle: "تقنية — ذكاء اصطناعي وتحول رقمي | مجلة التجارة",
    wpCategoryId: 275,
    pills: [
      { id: "all", label: "الكل" },
      { id: "ai", label: "الذكاء الاصطناعي" },
      { id: "cyber", label: "الأمن السيبراني" },
      { id: "fintech", label: "التقنية المالية" }
    ]
  },
  tourism: {
    title: "سياحة وترفيه",
    docTitle: "سياحة وترفيه — ضيافة ووجهات واستثمار | مجلة التجارة",
    wpCategoryId: 374,
    pills: [
      { id: "all", label: "الكل" },
      { id: "hospitality", label: "ضيافة وفنادق" },
      { id: "destinations", label: "وجهات سياحية" },
      { id: "entertainment", label: "ترفيه وفعاليات" }
    ]
  },
  misc: {
    title: "منوعات",
    docTitle: "منوعات — ثقافة ومجتمع وأسلوب حياة | مجلة التجارة",
    wpCategoryId: 196,
    pills: [
      { id: "all", label: "الكل" },
      { id: "culture", label: "ثقافة" },
      { id: "society", label: "مجتمع" },
      { id: "lifestyle", label: "أسلوب حياة" }
    ]
  }
};

const THEME_URLS = (typeof window !== "undefined" && window.AltijaraTheme && window.AltijaraTheme.urls) || {};
const THEME_CAT_LINKS = (typeof window !== "undefined" && window.AltijaraTheme && window.AltijaraTheme.categoryLinks) || {};
const catHref = (key) => {
  if (THEME_CAT_LINKS[key]) return THEME_CAT_LINKS[key];
  return `category.html?cat=${key}`;
};

const PRIMARY_NAV = [
  { key: "home", label: "الرئيسية", href: THEME_URLS.home || "index.html" },
  ...Object.keys(CATEGORIES_DATA).map((key) => ({
    key,
    label: CATEGORIES_DATA[key].title,
    href: catHref(key)
  })),
  { key: "issues", label: "أعداد المجلة", href: THEME_URLS.issues || "issues.html" }
];

const UTILITY_NAV = [
  { label: "من نحن", href: THEME_URLS.about || "about.html" },
  { label: "اتصل بنا", href: THEME_URLS.contact || "contact.html" },
  { label: "أعلن معنا", href: THEME_URLS.advertise || "advertise.html" }
];

const HEADER_HTML = "\u003cdiv class=\"top-utility-bar\"\u003e\n  \u003cdiv class=\"container top-utility-inner\"\u003e\n    \u003cul class=\"top-nav-links\"\u003e\u003c/ul\u003e\n    \u003cdiv class=\"top-left-actions\"\u003e\n      \u003cdiv class=\"top-date-display\"\u003e\n        \u003csvg class=\"top-date-icon\" width=\"13\" height=\"13\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"\u003e\u003crect x=\"3\" y=\"4\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"\u003e\u003c/rect\u003e\u003cline x1=\"16\" y1=\"2\" x2=\"16\" y2=\"6\"\u003e\u003c/line\u003e\u003cline x1=\"8\" y1=\"2\" x2=\"8\" y2=\"6\"\u003e\u003c/line\u003e\u003cline x1=\"3\" y1=\"10\" x2=\"21\" y2=\"10\"\u003e\u003c/line\u003e\u003c/svg\u003e\n        \u003cspan id=\"topDateText\"\u003e\u003c/span\u003e\n      \u003c/div\u003e\n      \u003cspan class=\"top-tools-divider\"\u003e\u003c/span\u003e\n      \u003cdiv class=\"top-social-links\"\u003e\n        \u003ca href=\"https://x.com/altijaramag\" target=\"_blank\" rel=\"noopener\" aria-label=\"منصة X\" title=\"منصة X\"\u003e\n          \u003csvg width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z\"/\u003e\u003c/svg\u003e\n        \u003c/a\u003e\n        \u003ca href=\"https://www.linkedin.com/company/altijara-magazine\" target=\"_blank\" rel=\"noopener\" aria-label=\"LinkedIn\" title=\"لينكد إن\"\u003e\n          \u003csvg width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.2a1.66 1.66 0 0 0-1.66 1.66c0 .92.74 1.66 1.66 1.66.92 0 1.66-.74 1.66-1.66 0-.92-.74-1.66-1.66-1.66Z\"/\u003e\u003c/svg\u003e\n        \u003c/a\u003e\n        \u003ca href=\"https://www.youtube.com/@JeddahChamber\" target=\"_blank\" rel=\"noopener\" aria-label=\"YouTube\" title=\"يوتيوب\"\u003e\n          \u003csvg width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M21.58 7.19a2.51 2.51 0 0 0-1.77-1.78C18.25 5 12 5 12 5s-6.25 0-7.81.41a2.51 2.51 0 0 0-1.77 1.78C2 8.75 2 12 2 12s0 3.25.42 4.81c.23.88.94 1.58 1.77 1.78 1.56.41 7.81.41 7.81.41s6.25 0 7.81-.41a2.51 2.51 0 0 0 1.77-1.78C22 15.25 22 12 22 12s0-3.25-.42-4.81zM9.75 15.02V8.98L15 12l-5.25 3.02z\"/\u003e\u003c/svg\u003e\n        \u003c/a\u003e\n        \u003ca href=\"https://whatsapp.com/channel/altijaramag\" target=\"_blank\" rel=\"noopener\" aria-label=\"WhatsApp\" title=\"واتساب للأعمال\"\u003e\n          \u003csvg width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.23 8.23 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.98-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43l-.47-.01c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.22-.17-.47-.29z\"/\u003e\u003c/svg\u003e\n        \u003c/a\u003e\n      \u003c/div\u003e\n    \u003c/div\u003e\n  \u003c/div\u003e\n\u003c/div\u003e\n\n\u003cheader class=\"main-header\"\u003e\n  \u003cdiv class=\"container header-grid\"\u003e\n    \u003cdiv class=\"header-brand\"\u003e\n      \u003cbutton class=\"menu-toggle-btn\" aria-label=\"القائمة الرئيسية\" type=\"button\"\u003e\n        \u003cspan\u003e\u003c/span\u003e\u003cspan\u003e\u003c/span\u003e\u003cspan\u003e\u003c/span\u003e\n      \u003c/button\u003e\n      \u003ca href=\"index.html\" class=\"brand-logo-link\" aria-label=\"مجلة التجارة\"\u003e\n        \u003cimg src=\"https://altijaramagazine.com/wp-content/uploads/2026/09/Asset-1@4x.png\" alt=\"مجلة التجارة\" class=\"brand-logo-img brand-logo-default\"\u003e\n        \u003cimg src=\"https://altijaramagazine.com/wp-content/uploads/2026/09/Logo-%D8%A3%D8%B2%D8%B1%D9%82.png\" alt=\"مجلة التجارة\" class=\"brand-logo-img brand-logo-hover\"\u003e\n      \u003c/a\u003e\n    \u003c/div\u003e\n    \u003cdiv class=\"header-sponsor-banner\"\u003e\n      \u003ca href=\"advertise.html\" class=\"sponsor-banner-img-link\"\u003e\n        \u003cimg src=\"https://altijaramagazine.com/wp-content/uploads/angwp/items/29873/728-x90.png\" alt=\"إعلان مجلة التجارة\" class=\"header-ad-img\"\u003e\n      \u003c/a\u003e\n    \u003c/div\u003e\n    \u003cdiv class=\"header-actions o-header__top-column o-header__top-column--right\"\u003e\n      \u003cbutton class=\"header-search-btn o-header__top-icon-link--search\" id=\"headerSearchBtn\" aria-label=\"بحث في الموقع\" title=\"بحث\" type=\"button\"\u003e\n        \u003csvg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"\u003e\n          \u003ccircle cx=\"11\" cy=\"11\" r=\"8\"\u003e\u003c/circle\u003e\n          \u003cline x1=\"21\" y1=\"21\" x2=\"16.65\" y2=\"16.65\"\u003e\u003c/line\u003e\n        \u003c/svg\u003e\n      \u003c/button\u003e\n      \u003ca class=\"o-header__top-myaccount login-link\" id=\"o-header-top-link-signin\" href=\"login.html\"\u003e\n        \u003csvg class=\"o-header__myaccount-icon\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"\u003e\n          \u003cpath d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"\u003e\u003c/path\u003e\n          \u003ccircle cx=\"12\" cy=\"7\" r=\"4\"\u003e\u003c/circle\u003e\n        \u003c/svg\u003e\n        \u003cspan\u003eتسجيل الدخول\u003c/span\u003e\n      \u003c/a\u003e\n      \u003ca class=\"o-header__top-button o-header__top-button--hide-m btn-subscribe\" href=\"subscribe.html\"\u003eاشترك\u003c/a\u003e\n    \u003c/div\u003e\n  \u003c/div\u003e\n\u003c/header\u003e\n\n\u003cdiv class=\"header-search-overlay\" id=\"headerSearchOverlay\" hidden\u003e\u003c/div\u003e\n\u003cdiv class=\"header-search-bar\" id=\"headerSearchBar\" role=\"dialog\" aria-modal=\"true\" aria-label=\"بحث في مجلة التجارة\" hidden\u003e\n  \u003cdiv class=\"container header-search-inner\"\u003e\n    \u003cform action=\"https://altijaramagazine.com/\" method=\"get\" class=\"header-search-form\" role=\"search\"\u003e\n      \u003csvg class=\"header-search-form-icon\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"\u003e\n        \u003ccircle cx=\"11\" cy=\"11\" r=\"8\"\u003e\u003c/circle\u003e\n        \u003cline x1=\"21\" y1=\"21\" x2=\"16.65\" y2=\"16.65\"\u003e\u003c/line\u003e\n      \u003c/svg\u003e\n      \u003cinput type=\"search\" name=\"s\" class=\"header-search-input\" id=\"headerSearchInput\" placeholder=\"ابحث في مجلة التجارة (الأخبار، الشركات، التقارير، أعداد المجلة...)\" autocomplete=\"off\" enterkeyhint=\"search\"\u003e\n      \u003cbutton type=\"submit\" class=\"header-search-submit\"\u003eبحث\u003c/button\u003e\n      \u003cbutton type=\"button\" class=\"header-search-close\" id=\"headerSearchClose\" aria-label=\"إغلاق البحث\"\u003e\n        \u003csvg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.4\" stroke-linecap=\"round\" aria-hidden=\"true\"\u003e\u003cline x1=\"18\" y1=\"6\" x2=\"6\" y2=\"18\"/\u003e\u003cline x1=\"6\" y1=\"6\" x2=\"18\" y2=\"18\"/\u003e\u003c/svg\u003e\n      \u003c/button\u003e\n    \u003c/form\u003e\n    \u003cdiv class=\"header-search-results\" id=\"headerSearchResults\"\u003e\u003c/div\u003e\n    \u003cp class=\"header-search-hint\"\u003eاضغط Esc للإغلاق · اكتب حرفين على الأقل للنتائج\u003c/p\u003e\n  \u003c/div\u003e\n\u003c/div\u003e\n\n\u003cnav class=\"primary-nav-bar\" aria-label=\"الأقسام الرئيسية\"\u003e\n  \u003cdiv class=\"container\"\u003e\n    \u003cul class=\"primary-nav-list\"\u003e\u003c/ul\u003e\n  \u003c/div\u003e\n\u003c/nav\u003e";

const FOOTER_HTML = "\u003cfooter class=\"editorial-dark-footer\"\u003e\n  \u003cdiv class=\"container\"\u003e\n    \u003cdiv class=\"footer-grid\"\u003e\n      \u003cdiv class=\"footer-col-brand\"\u003e\n        \u003ca href=\"index.html\" class=\"footer-logo-link\"\u003e\n          \u003cimg src=\"https://altijaramagazine.com/wp-content/uploads/2026/09/Asset-14@4x.png\" alt=\"مجلة التجارة\" class=\"footer-logo-img\"\u003e\n        \u003c/a\u003e\n        \u003cp class=\"footer-brand-desc\"\u003eتصدر عن غرفة جدة منذ عام 1948\u003c/p\u003e\n      \u003c/div\u003e\n\n      \u003cdiv class=\"footer-col-sections\"\u003e\n        \u003ch4 class=\"footer-heading\"\u003eالأقسام\u003c/h4\u003e\n        \u003cdiv class=\"footer-sections-subcols\" id=\"footerSections\"\u003e\u003c/div\u003e\n      \u003c/div\u003e\n\n      \u003cdiv class=\"footer-col-about\"\u003e\n        \u003ch4 class=\"footer-heading\"\u003eروابط تهمك\u003c/h4\u003e\n        \u003cul class=\"footer-links-list\"\u003e\n          \u003cli\u003e\u003ca href=\"about.html\"\u003e\u003cspan\u003eمن نحن\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n          \u003cli\u003e\u003ca href=\"contact.html\"\u003e\u003cspan\u003eاتصل بنا\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n          \u003cli\u003e\u003ca href=\"advertise.html\"\u003e\u003cspan\u003eأعلن معنا\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n          \u003cli\u003e\u003ca href=\"subscribe.html\"\u003e\u003cspan\u003eاشترك\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n          \u003cli\u003e\u003ca href=\"issues.html\"\u003e\u003cspan\u003eأعداد المجلة\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n          \u003cli\u003e\u003ca href=\"https://www.jcci.org.sa/\" target=\"_blank\" rel=\"noopener\"\u003e\u003cspan\u003eغرفة جدة\u003c/span\u003e\u003cspan class=\"footer-caret\"\u003e‹\u003c/span\u003e\u003c/a\u003e\u003c/li\u003e\n        \u003c/ul\u003e\n      \u003c/div\u003e\n\n      \u003cdiv class=\"footer-col-social\"\u003e\n        \u003ch4 class=\"footer-heading\"\u003eتابعونا على\u003c/h4\u003e\n        \u003cdiv class=\"footer-social-icons\"\u003e\n          \u003ca href=\"https://www.linkedin.com/company/altijara-magazine\" target=\"_blank\" rel=\"noopener\" aria-label=\"LinkedIn\" title=\"LinkedIn\"\u003e\n            \u003csvg width=\"19\" height=\"19\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.2a1.66 1.66 0 0 0-1.66 1.66c0 .92.74 1.66 1.66 1.66.92 0 1.66-.74 1.66-1.66 0-.92-.74-1.66-1.66-1.66Z\"/\u003e\u003c/svg\u003e\n          \u003c/a\u003e\n          \u003ca href=\"https://x.com/altijaramag\" target=\"_blank\" rel=\"noopener\" aria-label=\"X\" title=\"X\"\u003e\n            \u003csvg width=\"17\" height=\"17\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z\"/\u003e\u003c/svg\u003e\n          \u003c/a\u003e\n          \u003ca href=\"https://www.facebook.com/altijaramag\" target=\"_blank\" rel=\"noopener\" aria-label=\"Facebook\" title=\"Facebook\"\u003e\n            \u003csvg width=\"19\" height=\"19\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z\"/\u003e\u003c/svg\u003e\n          \u003c/a\u003e\n          \u003ca href=\"https://instagram.com/altijaramag\" target=\"_blank\" rel=\"noopener\" aria-label=\"Instagram\" title=\"Instagram\"\u003e\n            \u003csvg width=\"19\" height=\"19\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z\"/\u003e\u003c/svg\u003e\n          \u003c/a\u003e\n          \u003ca href=\"https://www.youtube.com/@JeddahChamber\" target=\"_blank\" rel=\"noopener\" aria-label=\"YouTube\" title=\"YouTube\"\u003e\n            \u003csvg width=\"21\" height=\"21\" viewBox=\"0 0 24 24\" fill=\"currentColor\"\u003e\u003cpath d=\"M21.58 7.19a2.51 2.51 0 0 0-1.77-1.78C18.25 5 12 5 12 5s-6.25 0-7.81.41a2.51 2.51 0 0 0-1.77 1.78C2 8.75 2 12 2 12s0 3.25.42 4.81c.23.88.94 1.58 1.77 1.78 1.56.41 7.81.41 7.81.41s6.25 0 7.81-.41a2.51 2.51 0 0 0 1.77-1.78C22 15.25 22 12 22 12s0-3.25-.42-4.81zM9.75 15.02V8.98L15 12l-5.25 3.02z\"/\u003e\u003c/svg\u003e\n          \u003c/a\u003e\n        \u003c/div\u003e\n      \u003c/div\u003e\n    \u003c/div\u003e\n\n    \u003cdiv class=\"footer-bottom-row\"\u003e\n      \u003cp class=\"footer-copyright-text\"\u003e2026@ التجارة جميع الحقوق محفوظة\u003c/p\u003e\n    \u003c/div\u003e\n  \u003c/div\u003e\n\u003c/footer\u003e";

function getActiveNavKey() {
  const path = (window.location.pathname.split("/").pop() || "").toLowerCase();
  const params = new URLSearchParams(window.location.search);
  const cat = params.get("cat");
  if (path.includes("category") && cat && CATEGORIES_DATA[cat]) return cat;
  if (path.includes("issues")) return "issues";
  if (path.includes("about")) return "about";
  if (path.includes("contact")) return "contact";
  if (path.includes("advertise")) return "advertise";
  if (path.includes("login")) return "login";
  if (path.includes("subscribe")) return "subscribe";
  if (!path || path === "index.html" || path === "") return "home";
  return "";
}

function isWpMenuReady(el) {
  if (!el) return false;
  if (el.getAttribute("data-wp-menu") === "1") return true;
  return el.children && el.children.length > 0;
}

function renderUtilityNav() {
  const path = (window.location.pathname.split("/").pop() || "").toLowerCase();
  document.querySelectorAll(".top-nav-links").forEach((list) => {
    if (isWpMenuReady(list)) return;
    list.innerHTML = UTILITY_NAV.map((item) => {
      const current = path === item.href.toLowerCase() ? ' class="is-current"' : "";
      return `<li><a href="${item.href}"${current}>${item.label}</a></li>`;
    }).join("");
  });
}

function renderPrimaryNav() {
  const activeKey = getActiveNavKey();
  document.querySelectorAll(".primary-nav-list").forEach((list) => {
    if (isWpMenuReady(list)) return;
    list.innerHTML = PRIMARY_NAV.map((item) => {
      const isActive = item.key === activeKey ? ' class="active"' : "";
      return `<li${isActive}><a href="${item.href}">${item.label}</a></li>`;
    }).join("");
  });
}

function renderFooterSections() {
  const host = document.getElementById("footerSections");
  if (!host || isWpMenuReady(host)) return;
  const keys = Object.keys(CATEGORIES_DATA).filter((key) => key !== "industry");
  const mid = Math.ceil(keys.length / 2);
  const cols = [keys.slice(0, mid), ["industry", ...keys.slice(mid)]];
  const link = (href, label) =>
    `<li><a href="${href}"><span>${label}</span><span class="footer-caret">‹</span></a></li>`;
  const lists = cols.map((col) => {
    const items = col.map((key) => link(catHref(key), CATEGORIES_DATA[key].title));
    return `<ul class="footer-links-list">${items.join("")}</ul>`;
  });
  const heading = host.parentElement?.querySelector(":scope > .footer-heading");
  if (heading) heading.remove();
  host.innerHTML = `
    <div class="footer-sections-col">
      <h4 class="footer-heading">الأقسام</h4>
      ${lists[0] || ""}
    </div>
    ${lists[1] || ""}
  `;
}

function setTopDate() {
  const el = document.getElementById("topDateText");
  if (!el) return;
  try {
    const fmt = new Intl.DateTimeFormat("ar-SA", {
      weekday: "long", year: "numeric", month: "long", day: "numeric"
    });
    el.textContent = fmt.format(new Date());
  } catch (_) {
    el.textContent = new Date().toLocaleDateString("ar");
  }
}

function initSiteNav() {
  renderUtilityNav();
  renderPrimaryNav();
  renderFooterSections();
  setTopDate();
  initSectionsDrawer();
}

function ensureSectionsDrawer() {
  if (document.getElementById("sectionsDrawer")) return;

  const overlay = document.createElement("div");
  overlay.className = "sections-drawer-overlay";
  overlay.id = "sectionsDrawerOverlay";
  overlay.setAttribute("aria-hidden", "true");

  const drawer = document.createElement("aside");
  drawer.className = "sections-drawer";
  drawer.id = "sectionsDrawer";
  drawer.setAttribute("role", "dialog");
  drawer.setAttribute("aria-modal", "true");
  drawer.setAttribute("aria-label", "أقسام المجلة");
  drawer.innerHTML = `
    <div class="sections-drawer-header">
      <h2 class="sections-drawer-title">أقسام المجلة</h2>
      <button type="button" class="sections-drawer-close" id="sectionsDrawerClose" aria-label="إغلاق القائمة">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="sections-drawer-body">
      <p class="sections-drawer-group-label">تصفح الأقسام</p>
      <ul class="sections-drawer-list" id="sectionsDrawerList"></ul>
      <p class="sections-drawer-group-label">روابط تهمك</p>
      <ul class="sections-drawer-list" id="sectionsDrawerUtility"></ul>
    </div>
  `;

  document.body.appendChild(overlay);
  document.body.appendChild(drawer);
}

function renderSectionsDrawerLinks() {
  const activeKey = getActiveNavKey();
  const mainList = document.getElementById("sectionsDrawerList");
  const utilList = document.getElementById("sectionsDrawerUtility");
  const drawer = document.getElementById("sectionsDrawer");
  // PHP-rendered mobile menu — do not overwrite.
  if (drawer && drawer.getAttribute("data-wp-menu") === "1") return;
  if (mainList && !isWpMenuReady(mainList)) {
    mainList.innerHTML = PRIMARY_NAV.map((item) => {
      const active = item.key === activeKey ? " is-active" : "";
      return `<li><a href="${item.href}" class="${active.trim()}"><span>${item.label}</span><span class="drawer-caret" aria-hidden="true">‹</span></a></li>`;
    }).join("");
  }
  if (utilList && !isWpMenuReady(utilList)) {
    utilList.innerHTML = UTILITY_NAV.map((item) =>
      `<li><a href="${item.href}"><span>${item.label}</span><span class="drawer-caret" aria-hidden="true">‹</span></a></li>`
    ).join("");
  }
}

function openSectionsDrawer() {
  ensureSectionsDrawer();
  renderSectionsDrawerLinks();
  const drawer = document.getElementById("sectionsDrawer");
  const overlay = document.getElementById("sectionsDrawerOverlay");
  const btn = document.querySelector(".menu-toggle-btn");
  if (!drawer) return;
  drawer.classList.add("is-open");
  if (overlay) {
    overlay.classList.add("is-open");
    overlay.setAttribute("aria-hidden", "false");
  }
  if (btn) {
    btn.classList.add("is-open");
    btn.setAttribute("aria-expanded", "true");
  }
  document.body.classList.add("sections-drawer-open");
}

function closeSectionsDrawer() {
  const drawer = document.getElementById("sectionsDrawer");
  const overlay = document.getElementById("sectionsDrawerOverlay");
  const btn = document.querySelector(".menu-toggle-btn");
  if (drawer) drawer.classList.remove("is-open");
  if (overlay) {
    overlay.classList.remove("is-open");
    overlay.setAttribute("aria-hidden", "true");
  }
  if (btn) {
    btn.classList.remove("is-open");
    btn.setAttribute("aria-expanded", "false");
  }
  document.body.classList.remove("sections-drawer-open");
}

function initSectionsDrawer() {
  if (window.__altijaraSectionsDrawerReady) return;
  ensureSectionsDrawer();
  renderSectionsDrawerLinks();

  const btn = document.querySelector(".menu-toggle-btn");
  const overlay = document.getElementById("sectionsDrawerOverlay");
  const closeBtn = document.getElementById("sectionsDrawerClose");
  if (!btn) return;

  window.__altijaraSectionsDrawerReady = true;

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    if (document.getElementById("sectionsDrawer")?.classList.contains("is-open")) {
      closeSectionsDrawer();
    } else {
      closeGlobalSearch();
      openSectionsDrawer();
    }
  });

  if (overlay) overlay.addEventListener("click", () => closeSectionsDrawer());
  if (closeBtn) {
    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      closeSectionsDrawer();
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && document.getElementById("sectionsDrawer")?.classList.contains("is-open")) {
      closeSectionsDrawer();
    }
  });
}

function mountHeader() {
  if (document.querySelector(".main-header")) return;
  const slot = document.getElementById("site-header");
  if (!slot) return;
  slot.insertAdjacentHTML("afterend", HEADER_HTML);
  slot.remove();
}

function mountFooter() {
  if (document.querySelector(".editorial-dark-footer")) return;
  const slot = document.getElementById("site-footer");
  if (!slot) return;
  slot.insertAdjacentHTML("afterend", FOOTER_HTML);
  slot.remove();
}


/* ---- Global header search ---- */
const WP_SEARCH_API = (THEME_URLS.rest || "https://altijaramagazine.com/wp-json/wp/v2").replace(/\/$/, "");

function openGlobalSearch() {
  closeSectionsDrawer();
  const bar = document.getElementById("headerSearchBar");
  const overlay = document.getElementById("headerSearchOverlay");
  const input = document.getElementById("headerSearchInput");
  if (!bar) return;
  bar.hidden = false;
  bar.classList.add("active");
  if (overlay) {
    overlay.hidden = false;
    overlay.classList.add("active");
  }
  document.body.style.overflow = "hidden";
  if (input) setTimeout(() => input.focus(), 60);
}

function closeGlobalSearch() {
  const bar = document.getElementById("headerSearchBar");
  const overlay = document.getElementById("headerSearchOverlay");
  const results = document.getElementById("headerSearchResults");
  if (bar) {
    bar.classList.remove("active");
    bar.hidden = true;
  }
  if (overlay) {
    overlay.classList.remove("active");
    overlay.hidden = true;
  }
  if (results) {
    results.classList.remove("active");
    results.innerHTML = "";
  }
  document.body.style.overflow = "";
}

function initGlobalSearch() {
  if (window.__altijaraSearchReady) return;
  const searchBtn = document.getElementById("headerSearchBtn");
  const searchBar = document.getElementById("headerSearchBar");
  const searchClose = document.getElementById("headerSearchClose");
  const searchInput = document.getElementById("headerSearchInput");
  const searchResults = document.getElementById("headerSearchResults");
  const overlay = document.getElementById("headerSearchOverlay");
  if (!searchBtn || !searchBar) return;

  window.__altijaraSearchReady = true;

  searchBtn.addEventListener("click", (e) => {
    e.preventDefault();
    if (searchBar.classList.contains("active")) closeGlobalSearch();
    else openGlobalSearch();
  });

  if (searchClose) {
    searchClose.addEventListener("click", (e) => {
      e.preventDefault();
      closeGlobalSearch();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => closeGlobalSearch());
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && searchBar.classList.contains("active")) {
      closeGlobalSearch();
    }
  });

  if (searchInput && searchResults) {
    let searchTimeout = null;
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.trim();
      clearTimeout(searchTimeout);
      if (query.length < 2) {
        searchResults.innerHTML = "";
        searchResults.classList.remove("active");
        return;
      }
      searchResults.innerHTML = '<div class="header-search-status">جارِ البحث...</div>';
      searchResults.classList.add("active");
      searchTimeout = setTimeout(async () => {
        try {
          const res = await fetch(`${WP_SEARCH_API}/posts?search=${encodeURIComponent(query)}&per_page=5&_embed`);
          if (!res.ok) throw new Error("Search failed");
          const posts = await res.json();
          if (!Array.isArray(posts) || posts.length === 0) {
            searchResults.innerHTML = '<div class="header-search-status">لا توجد نتائج مطابقة</div>';
            return;
          }
          searchResults.innerHTML = posts.map((post) => {
            const title = (post.title && post.title.rendered) ? post.title.rendered.replace(/<[^>]+>/g, "") : "بدون عنوان";
            const slug = post.slug || "";
            let link = "article.html";
            if (slug) link = "article.html?slug=" + encodeURIComponent(slug);
            else if (post.link) {
              try {
                const parts = new URL(post.link).pathname.split("/").filter(Boolean);
                if (parts.length === 1) link = "article.html?slug=" + encodeURIComponent(decodeURIComponent(parts[0]));
              } catch (_) {}
            }
            let img = "assets/images/issue-730-f2dfce.jpg";
            try {
              img = post._embedded["wp:featuredmedia"][0].source_url || img;
            } catch (_) {}
            let category = "مجلة التجارة";
            try {
              category = post._embedded["wp:term"][0][0].name || category;
            } catch (_) {}
            return `<a href="${link}" class="header-search-item">
              <img src="${img}" alt="" class="header-search-item-thumb" loading="lazy">
              <div class="header-search-item-info">
                <h5 class="header-search-item-title">${title}</h5>
                <div class="header-search-item-meta"><span>${category}</span></div>
              </div>
            </a>`;
          }).join("");
        } catch (_) {
          searchResults.innerHTML = '<div class="header-search-status header-search-status--error">تعذر جلب النتائج حالياً</div>';
        }
      }, 280);
    });
  }
}

function bootChrome() {
  mountHeader();
  const finish = () => {
    mountFooter();
    initSiteNav();
    initGlobalSearch();
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", finish);
  } else {
    finish();
  }
}

bootChrome();

window.CATEGORIES_DATA = CATEGORIES_DATA;
window.PRIMARY_NAV = PRIMARY_NAV;
window.initSiteNav = initSiteNav;
window.SiteChrome = {
  mountHeader,
  mountFooter,
  initSiteNav,
  initGlobalSearch,
  openGlobalSearch,
  closeGlobalSearch,
  openSectionsDrawer,
  closeSectionsDrawer
};