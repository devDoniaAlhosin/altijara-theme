<?php
/**
 * Issue archive — from issues.html
 * @package Altijara
 */
get_header();
?>
<!-- Main Container -->
  <main class="container issues-main-wrapper">

    <!-- ====================================================================
         HERO FEATURED ISSUE (Top Lead Box - Like FT Video Lead Card)
         ==================================================================== -->
    <section class="issues-lead-hero" style="margin-top: 24px;">
      <div class="issues-lead-grid">
        <!-- Media / Cover Side (Right in RTL) -->
        <div class="lead-media-wrap">
          <div class="lead-cover-frame">
            <img src="https://online.fliphtml5.com/puhxek/kihy/files/shot.jpg?v=2" onerror="this.src='assets/images/issue-730-clean.png'" alt="غلاف مجلة التجارة العدد 730" class="lead-cover-img">
            <span class="lead-issue-tag">العدد 730 — الأحدث</span>
          </div>
        </div>

        <!-- Text / Info Side (Left in RTL) -->
        <div class="lead-info-wrap">
          <div class="lead-meta-row">
            <span class="lead-type-badge">مجلة غرفة جدة</span>
          </div>
          <h2 class="lead-headline">
            <a href="https://altijaramagazine.com/altijara-magazine-issue-730/" target="_blank">
              العدد (730): الشراكات الاستثمارية ومستقبل التعليم والتدريب في المملكة
            </a>
          </h2>
          <p class="lead-synopsis">
            يرصد العدد الجديد مسارات التنمية والتحول في قطاع التعليم والتدريب والاستثمار البشري والتقني، مع حوارات حصرية لرواد الأعمال، ووثائق استراتيجية حول تمكين المنشآت الوطنية وسلاسل الإمداد في منطقة مكة المكرمة.
          </p>
          <div class="lead-stats-row">
            <div class="lead-stat-chip">
              <strong>94</strong>
              <span>صفحة</span>
            </div>
            <div class="lead-stat-chip">
              <strong>2</strong>
              <span>ملفات</span>
            </div>
            <div class="lead-stat-chip">
              <strong>5</strong>
              <span>حوارات</span>
            </div>
          </div>
          <div class="lead-actions-row">
            <a href="https://online.fliphtml5.com/puhxek/kihy/" class="btn-lead-primary" target="_blank">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
              <span>تصفح النسخة الرقمية</span>
            </a>
            <a href="downloads/issue-730.pdf" class="btn-lead-secondary js-pdf-download" data-pdf-url="downloads/issue-730.pdf" data-pdf-name="مجلة-التجارة-العدد-730.pdf" download="مجلة-التجارة-العدد-730.pdf">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              <span>تحميل ملف PDF</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ====================================================================
         CAROUSEL 1: ISSUES (الأعداد 730 إلى 718 — الإصدارات التفاعلية)
         ==================================================================== -->
    <section class="issues-carousel-section" id="secRecent">
      <div class="section-title-bar">
        <div class="section-title-group" style="display: flex; align-items: center; gap: 14px;">
          <h2 class="section-ft-heading">إصدارات مجلة التجارة (الأعداد 730 – 718)</h2>
        </div>
        <div class="carousel-nav-arrows">
          <button type="button" class="nav-arrow-btn prev" aria-label="السابق" onclick="scrollCarousel('recentCarouselTrack', 360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
          <button type="button" class="nav-arrow-btn next" aria-label="التالي" onclick="scrollCarousel('recentCarouselTrack', -360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
        </div>
      </div>
      <div class="issues-carousel-track" id="recentCarouselTrack">
        <!-- Injected via issues.js -->
      </div>
    </section>

    <!-- ====================================================================
         CAROUSEL 2: 2019-2020 ISSUES (الأعداد 715 إلى 704 — أرشيف الغرفة المعتمد)
         ==================================================================== -->
    <section class="issues-carousel-section" id="secArchive2020">
      <div class="section-title-bar">
        <div class="section-title-group" style="display: flex; align-items: center; gap: 14px;">
          <h2 class="section-ft-heading">إصدارات مجلة التجارة (الأعداد 715 – 704)</h2>
        </div>
        <div class="carousel-nav-arrows">
          <button type="button" class="nav-arrow-btn prev" aria-label="السابق" onclick="scrollCarousel('archive2020CarouselTrack', 360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
          <button type="button" class="nav-arrow-btn next" aria-label="التالي" onclick="scrollCarousel('archive2020CarouselTrack', -360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
        </div>
      </div>
      <div class="issues-carousel-track" id="archive2020CarouselTrack">
        <!-- Injected via issues.js -->
      </div>
    </section>

    <!-- ====================================================================
         CAROUSEL 3: HISTORIC & COMMEMORATIVE ISSUES (الأعداد 703 إلى 681)
         ==================================================================== -->
    <section class="issues-carousel-section" id="secHistoric">
      <div class="section-title-bar">
        <div class="section-title-group" style="display: flex; align-items: center; gap: 14px;">
          <h2 class="section-ft-heading">إصدارات مجلة التجارة (الأعداد 703 – 681)</h2>
        </div>
        <div class="carousel-nav-arrows">
          <button type="button" class="nav-arrow-btn prev" aria-label="السابق" onclick="scrollCarousel('historicCarouselTrack', 360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
          <button type="button" class="nav-arrow-btn next" aria-label="التالي" onclick="scrollCarousel('historicCarouselTrack', -360)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
        </div>
      </div>
      <div class="issues-carousel-track" id="historicCarouselTrack">
        <!-- Injected via issues.js -->
      </div>
    </section>

  </main>

  <!-- ====================================================================
       INTERACTIVE MODAL: ISSUE PREVIEW & TABLE OF CONTENTS
       ==================================================================== -->
  <div class="issue-modal-backdrop" id="issueModalBackdrop">
    <div class="issue-modal-card">
      <button class="modal-close-btn" onclick="closeIssueModal()" aria-label="إغلاق">✕</button>
      <div class="modal-body-grid">
        <div class="modal-cover-column">
          <img src="" alt="" id="modalCoverImg" class="modal-cover-preview">
          <span class="modal-issue-badge" id="modalBadge">العدد 730</span>
        </div>
        <div class="modal-details-column">
          <div class="modal-header-meta">
            <span class="modal-tag" id="modalTag">ملف خاص</span>
            <span class="modal-date" id="modalDate">سبتمبر 2026</span>
          </div>
          <h2 class="modal-title" id="modalTitle">عنوان العدد</h2>
          <p class="modal-desc" id="modalDesc">تفاصيل وملف العدد الاقتصادي الموسع...</p>
          
          <div class="modal-toc-box">
            <h4>فهرس الأبواب وأبرز الموضوعات:</h4>
            <ul class="modal-toc-list">
              <li><strong>افتتاحية العدد:</strong> كلمة رئيس مجلس إدارة الغرفة التجارية بجدة</li>
              <li><strong>ملف الغلاف:</strong> التحليلات الاستراتيجية والمؤشرات الاقتصادية</li>
              <li><strong>تحقيقات مال وأعمال:</strong> استطلاعات آراء كبار التنفيذيين والمستثمرين</li>
              <li><strong>المشاريع والفرص:</strong> أحدث المشاريع الاستثمارية الواعدة في المنطقة الغربية</li>
              <li><strong>ريادة وتقنية:</strong> مبادرات المنشآت الصغيرة والمتوسطة والتحول الرقمي</li>
            </ul>
          </div>

          <div class="modal-actions-bar">
            <a href="#" id="modalReadBtn" class="btn-modal-primary" target="_blank">
              <span>قراءة العدد بالكامل في موقع المجلة</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <a href="#" id="modalPdfBtn" class="btn-modal-secondary js-pdf-download" download>
              <span>تحميل نسخة PDF</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php get_footer(); ?>
