<?php
/**
 * Issues archive — preview modal shell.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="issue-modal-backdrop" id="issueModalBackdrop" hidden>
	<div class="issue-modal-card">
		<button type="button" class="modal-close-btn" id="issueModalClose" aria-label="<?php esc_attr_e( 'إغلاق', 'altijara' ); ?>">✕</button>
		<div class="modal-body-grid">
			<div class="modal-cover-column">
				<img src="" alt="" id="modalCoverImg" class="modal-cover-preview">
				<span class="modal-issue-badge" id="modalBadge"></span>
			</div>
			<div class="modal-details-column">
				<div class="modal-header-meta">
					<span class="modal-tag" id="modalTag"></span>
					<span class="modal-date" id="modalDate"></span>
				</div>
				<h2 class="modal-title" id="modalTitle"></h2>
				<p class="modal-desc" id="modalDesc"></p>
				<div class="modal-actions-bar">
					<a href="#" id="modalReadBtn" class="btn-modal-primary" target="_blank" rel="noopener">
						<span><?php esc_html_e( 'قراءة العدد', 'altijara' ); ?></span>
					</a>
					<a href="#" id="modalPdfBtn" class="btn-modal-secondary js-pdf-download" download hidden>
						<span><?php esc_html_e( 'تحميل نسخة PDF', 'altijara' ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
