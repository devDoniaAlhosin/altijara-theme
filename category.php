<?php
/**
 * Category archive — from category.html
 * @package Altijara
 */
get_header();
$term = get_queried_object();
$term_id = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;
$posts_main = $term_id ? altijara_get_category_posts( $term_id, 20, 0 ) : array();
?>
<!-- ====================================================================
       MAIN CATEGORY CONTENT WRAPPER
       ==================================================================== -->
  <main class="container category-main-wrapper">

    <!-- 1. TOP GRID: HERO (Latest Article) + RECOMMENDED SIDEBAR -->
    <section class="cat-top-grid">
      
      <!-- HERO CARD: Latest Article (Clean Editorial Card) -->
      <article class="cat-hero-card" id="catHeroCard">
        <div class="cat-hero-info">
          <h2 class="cat-hero-headline">
            <a href="https://altijaramagazine.com/made-in-saudi-food-security/" target="_blank">
              صُنِعَ في السعودية: ترسيخ الأمن الغذائي والارتقاء بالتميُّز التصنيعي
            </a>
          </h2>
          <p class="cat-hero-synopsis">
            استراتيجية وطنية لبناء منظومة تصنيع محلية رائدة تواكب تطلعات الاكتفاء الذاتي، ومبادرات التميز الصناعي تدعم الصادرات وتنافسية المنتجات الوطنية في الأسواق الإقليمية والدولية، مع التركيز على رفع كفاءة سلاسل التوريد وتحفيز الاستثمار في الصناعات التحويلية بما يسهم في تنويع القاعدة الاقتصادية وتعزيز مكانة المملكة كمركز صناعي ولوجستي إقليمي.
          </p>
          <div class="cat-hero-meta">
            <span class="lead-time">22 سبتمبر 2026</span>
          </div>
        </div>

        <div class="cat-hero-img-wrap">
          <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/made-in-saudi-food-security.jpg" alt="صنع في السعودية" loading="eager" onerror="this.src='https://altijaramagazine.com/wp-content/uploads/2026/09/Asset-1@4x.png'">
        </div>
      </article>

      <!-- RECOMMENDED SIDEBAR (Like Recommended in FT reference) -->
      <aside class="cat-recommended-col">
        <div class="cat-rec-list" id="recList">
          
          <div class="cat-rec-item">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/desert-technologies-clean-energy-ai.jpg" alt="أخبار الغرفة" class="cat-rec-img">
            <div class="cat-rec-content">
              <h4 class="cat-rec-headline">
                <a href="https://altijaramagazine.com/desert-technologies-clean-energy-ai/" target="_blank">«تكنولوجيات الصحراء» تُعزز ريادتها في الطاقة وتوسّع توظيف الذكاء الاصطناعي</a>
              </h4>
              <span class="cat-rec-time">21 سبتمبر 2026</span>
            </div>
          </div>

          <div class="cat-rec-item">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/starlinks-swisslog-aviation-logistics.jpg" alt="أخبار الغرفة" class="cat-rec-img">
            <div class="cat-rec-content">
              <h4 class="cat-rec-headline">
                <a href="https://altijaramagazine.com/starlinks-swisslog-automation/" target="_blank">«ستارلينكس» تعتمد «سويس لوج» لأتمتة العمليات اللوجستية بقطاع الطيران بالمملكة</a>
              </h4>
              <span class="cat-rec-time">20 سبتمبر 2026</span>
            </div>
          </div>

          <div class="cat-rec-item">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/07/Issue-729-PR-Head-Photo.jpg" alt="أخبار الغرفة" class="cat-rec-img">
            <div class="cat-rec-content">
              <h4 class="cat-rec-headline">
                <a href="https://altijaramagazine.com/the-chamber-news/" target="_blank">توقيع اتفاقية إطارية برعاية سمو محافظ جدة والمجلس التنسيقي للغرفة</a>
              </h4>
              <span class="cat-rec-time">19 سبتمبر 2026</span>
            </div>
          </div>

        </div>
      </aside>

    </section>

    <!-- 2. FEATURED 4-COLUMN CARDS (same data as home news-grid-4col / متابعات) -->
    <section class="cat-featured-row-4col">
      <!-- Card 1 -->
      <article class="cat-featured-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/made-in-saudi-food-security.jpg" alt="صنع في السعودية" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">تجارة وصناعة</span>
          <h3 class="cat-featured-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">صُنِعَ في السعودية: ترسيخ الأمن الغذائي والارتقاء بالتميُّز التصنيعي</a>
          </h3>
          <span class="cat-featured-date">18 سبتمبر 2026</span>
        </div>
      </article>

      <!-- Card 2 -->
      <article class="cat-featured-card">
        <a href="https://altijaramagazine.com/desert-technologies-clean-energy-ai/" target="_blank" rel="noopener" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/desert-technologies-clean-energy-ai.jpg" alt="طاقة متجددة" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">طاقة متجددة</span>
          <h3 class="cat-featured-title">
            <a href="https://altijaramagazine.com/desert-technologies-clean-energy-ai/" target="_blank" rel="noopener">«تكنولوجيات الصحراء» تُعزز ريادتها في الطاقة وتوسّع توظيف الذكاء الاصطناعي</a>
          </h3>
          <span class="cat-featured-date">17 سبتمبر 2026</span>
        </div>
      </article>

      <!-- Card 3 -->
      <article class="cat-featured-card">
        <a href="https://altijaramagazine.com/taiba-wtm-spotlight-riyadh/" target="_blank" rel="noopener" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/taiba-wtm-spotlight-riyadh.jpg" alt="سياحة وضيافة" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">سياحة وضيافة</span>
          <h3 class="cat-featured-title">
            <a href="https://altijaramagazine.com/taiba-wtm-spotlight-riyadh/" target="_blank" rel="noopener">«طيبة» تستعرض رؤيتها لمستقبل الضيافة السعودية بـ«سوق السفر العالمي»</a>
          </h3>
          <span class="cat-featured-date">16 سبتمبر 2026</span>
        </div>
      </article>

      <!-- Card 4 -->
      <article class="cat-featured-card">
        <a href="https://altijaramagazine.com/magna-ai-strategic-partnerships/" target="_blank" rel="noopener" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/magna-ai-strategic-partnerships.jpg" alt="ذكاء اصطناعي" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">ذكاء اصطناعي</span>
          <h3 class="cat-featured-title">
            <a href="https://altijaramagazine.com/magna-ai-strategic-partnerships/" target="_blank" rel="noopener">«Magna AI» تبرم 6 شراكات استراتيجية لدعم تطبيقات الذكاء الاصطناعي</a>
          </h3>
          <span class="cat-featured-date">15 سبتمبر 2026</span>
        </div>
      </article>

      <article class="cat-featured-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/starlinks-swisslog-aviation-logistics.jpg" alt="" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">أخبار الغرفة</span>
          <h3 class="cat-featured-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">«ستارلينكس» تعتمد «سويس لوج» لأتمتة العمليات اللوجستية بقطاع الطيران</a>
          </h3>
          <span class="cat-featured-date">14 سبتمبر 2026</span>
        </div>
      </article>

      <article class="cat-featured-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/india-saudi-non-oil-trade.jpg" alt="" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">أخبار الغرفة</span>
          <h3 class="cat-featured-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">الهند شريك استراتيجي لدعم نمو التجارة غير النفطية مع المملكة</a>
          </h3>
          <span class="cat-featured-date">13 سبتمبر 2026</span>
        </div>
      </article>

      <article class="cat-featured-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/hid-digital-identity-saudi-arabia.jpg" alt="" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">أخبار الغرفة</span>
          <h3 class="cat-featured-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">وزارة وهيئة الاتصالات تكرم «إتش آي دي» لحلول الهوية الرقمية</a>
          </h3>
          <span class="cat-featured-date">12 سبتمبر 2026</span>
        </div>
      </article>

      <article class="cat-featured-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cat-featured-img-link">
          <div class="cat-featured-img-wrap">
            <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/icaur-alghanim-jeddah-showroom.jpg" alt="" loading="lazy">
          </div>
        </a>
        <div class="cat-featured-body">
          <span class="cat-featured-tag">أخبار الغرفة</span>
          <h3 class="cat-featured-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">افتتاح صالات العرض الجديدة لمركبات النقل في المنطقة الغربية</a>
          </h3>
          <span class="cat-featured-date">11 سبتمبر 2026</span>
        </div>
      </article>
    </section>

    <!-- 3. LOWER SECTION: NEWS LIST (Right in RTL) + OPINION/SIDEBAR (Left in RTL) -->
    <section class="cat-editorial-lower-grid">

      <!-- MAIN NEWS LIST (Like FT News List in screenshot) -->
      <div class="cat-news-col">
        <div class="cat-section-bar">
          <h2 class="cat-section-title">أحدث الأخبار</h2>
        </div>

        <div class="cat-news-list" id="catNewsList">

          <!-- Item 1 -->
          <article class="cat-news-item" data-category="companies">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/08/rhett-maxwell-jaguar-land-rover-saudi.jpg" alt="لقاء ريت ماكسويل" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/rhett-maxwell-jaguar-land-rover-customer-experience/" target="_blank">
                  ريت ماكسويل لـ«مجلة التجارة»: تجربة العميل تصنع الفارق الحقيقي في سوق السيارات الفاخرة
                </a>
              </h3>
              <p class="cat-news-excerpt">
                المدير التنفيذي لجاكوار لاند روفر يؤكد أن معايير الفخامة وتكامل الخدمات الرقمية لما بعد البيع تعيد تشكيل تطلعات العملاء في السوق السعودي المتنامي.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">18 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 2 -->
          <article class="cat-news-item" data-category="banking">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/pearson-egypt-higher-education.jpg" alt="تعليم وتطوير" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/pearson-egypt-higher-education/" target="_blank">
                  «بيرسون» تتعاون مع التعليم العالي لتوسيع مسارات التعلم التطبيقي العالمية
                </a>
              </h3>
              <p class="cat-news-excerpt">
                مذكرة تفاهم استراتيجية تهدف إلى تطوير مسارات التعليم والتدريب التطبيقي المتوافق مع متطلبات سوق العمل والمهارات الحديثة.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">17 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 3 -->
          <article class="cat-news-item" data-category="companies">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/hid-digital-identity-saudi-arabia.jpg" alt="اقتصاد رقمي" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/hid-digital-identity-saudi-arabia/" target="_blank">
                  وزارة وهيئة الاتصالات وهيئة الحكومة الرقمية تُكرّم «إتش آي دي» (HID)
                </a>
              </h3>
              <p class="cat-news-excerpt">
                التكريم يعكس ريادة تقنيات التحقق من الهوية الرقمية الموثوقة ودورها في تعزيز بيئة الأعمال الرقمية والمدفوعات الآمنة بالمملكة.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">16 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 4 -->
          <article class="cat-news-item" data-category="markets">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/india-saudi-non-oil-trade.jpg" alt="تجارة دولية" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/india-saudi-non-oil-trade/" target="_blank">
                  الهند شريك استراتيجي لدعم نمو التجارة غير النفطية مع المملكة العربية السعودية
                </a>
              </h3>
              <p class="cat-news-excerpt">
                العلاقات الاقتصادية والتجارية تسجل نمواً متسارعاً لتنويع الصادرات المشتركة وتوسيع استثمارات القطاع الخاص وسلاسل التوريد الصناعية.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">15 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 5 -->
          <article class="cat-news-item" data-category="startups">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/magna-ai-strategic-partnerships.jpg" alt="ذكاء اصطناعي" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/magna-ai-strategic-partnerships/" target="_blank">
                  «Magna AI» تبرم 6 شراكات استراتيجية لدعم تطبيقات الذكاء الاصطناعي في قطاع الأعمال
                </a>
              </h3>
              <p class="cat-news-excerpt">
                تطوير حلول المؤسسات الذكية وتسريع تبني التقنيات المتقدمة في السوق السعودي لدعم الكفاءة التشغيلية وتحليل البيانات الضخمة.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">14 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 6 -->
          <article class="cat-news-item" data-category="companies">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/taiba-wtm-spotlight-riyadh.jpg" alt="سياحة واستثمار" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/taiba-wtm-spotlight-riyadh/" target="_blank">
                  «طيبة» تستعرض رؤيتها لمستقبل الضيافة السعودية بـ«سوق السفر العالمي»
                </a>
              </h3>
              <p class="cat-news-excerpt">
                اتفاقيات استراتيجية ومشاريع نوعية لتطوير وجهات سياحية واستقطاب الاستثمارات الفندقية الرائدة في المدن الرئيسية.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">13 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>

          <!-- Item 7 -->
          <article class="cat-news-item" data-category="markets">
            <div class="cat-news-img-wrap">
              <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/ford-everest-v6-middle-east-testing.jpg" alt="أسواق وتجارب" loading="lazy">
            </div>
            <div class="cat-news-content">
              <h3 class="cat-news-headline">
                <a href="https://altijaramagazine.com/ford-everest-v6-testing/" target="_blank">
                  فورد تُخضع سياراتها لاختبارات قاسية في الشرق الأوسط استعداداً لإطلاق إيفرست V6
                </a>
              </h3>
              <p class="cat-news-excerpt">
                اختبارات ميدانية مكثفة للتحقق من كفاءة التبريد ومتانة أنظمة التعليق في أقسى الظروف الصحراوية بالمنطقة.
              </p>
              <div class="cat-news-meta-row">
                <div class="cat-news-tags-group">
                  <span class="cat-news-time">12 سبتمبر 2026</span>
                </div>
                <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                  <span>حفظ</span>
                </button>
              </div>
            </div>
          </article>
<!-- Item 8 -->
            <article class="cat-news-item" data-category="companies">
              <div class="cat-news-img-wrap">
                <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/icaur-alghanim-jeddah-showroom.jpg" alt="صالات عرض" loading="lazy">
              </div>
              <div class="cat-news-content">
                <h3 class="cat-news-headline">
                  <a href="https://altijaramagazine.com/icaur-alghanim-jeddah-showroom/" target="_blank">
                    افتتاح صالات العرض الجديدة لمركبات النقل والدفع الرباعي في المنطقة الغربية
                  </a>
                </h3>
                <p class="cat-news-excerpt">
                  توسع استثماري يواكب النهضة العمرانية والمشاريع اللوجستية الكبرى التي تشهدها محافظة جدة والمنطقة الغربية.
                </p>
                <div class="cat-news-meta-row">
                  <div class="cat-news-tags-group">
                    <span class="cat-news-time">11 سبتمبر 2026</span>
                  </div>
                  <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span>حفظ</span>
                  </button>
                </div>
              </div>
            </article>

            <!-- Item 9 -->
            <article class="cat-news-item" data-category="companies">
              <div class="cat-news-img-wrap">
                <img src="https://altijaramagazine.com/wp-content/uploads/2026/09/alkhorayef-sany-trucks-agreement.jpg" alt="شاحنات ساني الخريف" loading="lazy">
              </div>
              <div class="cat-news-content">
                <h3 class="cat-news-headline">
                  <a href="https://altijaramagazine.com/alkhorayef-sany-trucks-agreement/" target="_blank">
                    «الخريف التجارية» توقّع اتفاقية وكالة حصرية لرؤوس شاحنات «ساني» الثقيلة في المملكة
                  </a>
                </h3>
                <p class="cat-news-excerpt">
                  اتفاقية استراتيجية تعزز حضور شاحنات ساني العالمية في السوق السعودي وتوفر حلول نقل متطورة لقطاعات المقاولات واللوجستيات.
                </p>
                <div class="cat-news-meta-row">
                  <div class="cat-news-tags-group">
                    <span class="cat-news-time">10 سبتمبر 2026</span>
                  </div>
                  <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span>حفظ</span>
                  </button>
                </div>
              </div>
            </article>

            <!-- Item 10 -->
            <article class="cat-news-item" data-category="companies">
              <div class="cat-news-img-wrap">
                <img src="https://altijaramagazine.com/wp-content/uploads/2026/06/26126.gif" alt="حوكمة الشركات العائلية" loading="lazy">
              </div>
              <div class="cat-news-content">
                <h3 class="cat-news-headline">
                  <a href="https://altijaramagazine.com/%d8%b9%d8%a8%d8%af%d8%a7%d9%84%d8%b1%d8%ad%d9%85%d9%86-%d8%a7%d9%84%d9%87%d8%af%d9%84%d9%82-%d9%84%d9%85%d8%a7%d8%b0%d8%a7-%d8%aa%d9%81%d8%b4%d9%84-%d9%83%d8%ab%d9%8a%d8%b1-%d9%85%d9%86-%d8%a7%d9%84/" target="_blank">
                    حوكمة الشركات العائلية: صمام أمان لاستدامة الاقتصاد الوطني واستمرارية الأعمال
                  </a>
                </h3>
                <p class="cat-news-excerpt">
                  قراءة في أدوات الحوكمة ومواثيق العائلة التي تضمن استمرارية الأعمال وانتقال الإدارة بنجاح عبر الأجيال المتعاقبة.
                </p>
                <div class="cat-news-meta-row">
                  <div class="cat-news-tags-group">
                    <span class="cat-news-time">22 سبتمبر 2026</span>
                  </div>
                  <button type="button" class="btn-cat-save" onclick="toggleSaveArticle(this)" title="حفظ المقال">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span>حفظ</span>
                  </button>
                </div>
              </div>
            </article>

            <!-- Item 11 -->
            

            <!-- Item 12 -->
            
          

        </div>

        <div class="cat-pagination-wrap" id="catPagination" aria-label="ترقيم الصفحات">
          <button type="button" class="cat-page-btn cat-page-prev" id="catPagePrev" aria-label="الصفحة السابقة">‹</button>
          <div class="cat-page-numbers" id="catPageNumbers"></div>
          <button type="button" class="cat-page-btn cat-page-next" id="catPageNext" aria-label="الصفحة التالية">›</button>
        </div>
      </div>

      <!-- SIDEBAR COLUMN: COMMERCIAL AD + MOST READ (Matches Target Design) -->
      <aside class="cat-opinion-col">

        <!-- Commercial Ad: Chamber Services (Natural Image Dimensions) -->
        <div class="cat-sidebar-ad-widget">
          <div class="cat-sidebar-ad-label">
            <span>مساحة إعلانية</span>
            <span class="cat-sidebar-ad-badge">إعلان</span>
          </div>
          <a href="https://altijaramagazine.com/" target="_blank" rel="noopener" class="cat-sidebar-ad-link">
            <img src="https://altijaramagazine.com/wp-content/uploads/angwp/items/31121/%D8%AE%D8%AF%D9%85%D8%A7%D8%AA%20%D9%85%D9%8F%D8%AA%D9%85%D9%8A%D8%B2%D8%A9_%20300x600%20copy.png" alt="إعلان خدمات متميزة" class="cat-sidebar-ad-img" loading="lazy">
          </a>
        </div>

        <div class="cat-new-issue-widget">
          <div class="cat-section-bar cat-new-issue-bar">
            <h3 class="cat-section-title">من إصدارات المجلة</h3>
            <div class="cat-new-issue-arrows">
              <button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--prev" aria-label="العدد السابق">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 6l6 6-6 6"></path></svg>
              </button>
              <button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--next" aria-label="العدد التالي">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 6l-6 6 6 6"></path></svg>
              </button>
            </div>
          </div>
          <div class="cat-new-issue-list" id="catNewIssueList">
            <div class="cat-new-issue-cover" aria-hidden="true">
              <span class="sk sk-cover"></span>
            </div>
          </div>
        </div>

        <!-- Most Read List (Matches Financial Times Most Read reference) -->
        <div class="cat-most-read-widget">
          <div class="cat-section-bar cat-mr-bar">
            <h3 class="cat-section-title">الأكثر قراءة</h3>
          </div>

          <ol class="cat-mr-list">
            <!-- Item 1 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">1</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/money-and-business/" target="_blank">
                    حوكمة الشركات العائلية: صمام أمان لاستدامة الاقتصاد الوطني واستمرارية الأعمال
                  </a>
                </h4>
              </div>
            </li>

            <!-- Item 2 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">2</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/alkhorayef-sany-trucks/" target="_blank">
                    «الخريف التجارية» توقّع وكالة حصرية لرؤوس شاحنات «ساني» الثقيلة بالمملكة
                  </a>
                </h4>
              </div>
            </li>

            <!-- Item 3 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">3</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/starlinks-swisslog-automation/" target="_blank">
                    «ستارلينكس» تعتمد حلول «سويس لوج» لأتمتة العمليات اللوجستية بمستودعات الطيران
                  </a>
                </h4>
              </div>
            </li>

            <!-- Item 4 (With quote styling like reference image) -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">4</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline cat-mr-quote">
                  <span class="cat-mr-quote-icon">“</span>
                  <a href="https://altijaramagazine.com/rhett-maxwell-jaguar-land-rover-customer-experience/" target="_blank">
                    تجربة العميل أصبحت المعيار الحقيقي للفخامة والريادة في قطاع السيارات
                  </a>
                </h4>
                <div class="cat-mr-author">ريت ماكسويل — العضو المنتدب لـ JLR السعودية</div>
              </div>
            </li>

            <!-- Item 5 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">5</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/desert-technologies-clean-energy-ai/" target="_blank">
                    «تكنولوجيات الصحراء» تعزز ريادتها في الطاقة النظيفة وتوسع توظيف الذكاء الاصطناعي
                  </a>
                </h4>
              </div>
            </li>

            <!-- Item 6 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">6</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/hid-saudi-smart-identity/" target="_blank">
                    «إتش آي دي» تدشن أول منشأة لإنتاج حلول الهوية الرقمية الذكية بالرياض
                  </a>
                </h4>
              </div>
            </li>

            <!-- Item 7 -->
            <li class="cat-mr-item">
              <span class="cat-mr-num">7</span>
              <div class="cat-mr-content">
                <h4 class="cat-mr-headline">
                  <a href="https://altijaramagazine.com/pearson-saudi-universities-workforce/" target="_blank">
                    «بيرسون» توقع اتفاقيات استراتيجية مع جامعات كبرى لتأهيل الكفاءات الوطنية
                  </a>
                </h4>
              </div>
            </li>
          </ol>
        </div>

      </aside>

    </section>

  </main>
<?php get_footer(); ?>
