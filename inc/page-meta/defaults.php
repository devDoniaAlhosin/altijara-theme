<?php
/**
 * Default field trees for page meta schemas.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default field trees matching current hardcoded template copy.
 *
 * @param string $schema home|about|contact|advertise|subscribe|login.
 * @return array<string, mixed>
 */
function altijara_page_meta_defaults( $schema ) {
	$defaults = array(
		'home'      => array(
			'layout'   => function_exists( 'altijara_layout_preset_home' )
				? altijara_layout_preset_home()
				: array( 'sections' => array(), 'order' => array() ),
			'sections' => function_exists( 'altijara_home_meta_default_sections' )
				? altijara_home_meta_default_sections()
				: array(),
		),
		'about'     => array(
			'lead'              => 'تصدر عن غرفة جدة منذ عام 1948م، وتُعد إحدى أبرز المنصات الإعلامية في المملكة لقطاع المال والأعمال.',
			'highlight_kicker'  => 'غرفة جدة',
			'highlight_title'   => '“التجارة” – مجلة غرفة جدة | بيت أصحاب الأعمال',
			'highlight_intro'   => 'تمثّل مجلة «التجارة» منبراً رئيسياً للتجار ورواد الأعمال والشركات، وتسهم في تعزيز التواصل معهم ودعم أنشطتهم وقضاياهم. تقدم المجلة محتوى متخصصاً يواكب مستجدات الاقتصاد السعودي.',
			'stats'             => array(
				array(
					'value' => '1948',
					'label' => 'تصدر عن غرفة جدة منذ عام 1948',
				),
				array(
					'value' => '+78',
					'label' => 'اكثر من 78 عاما في خدمة قطاع الاعمال',
				),
				array(
					'value' => 'إلكتروني + مطبوع',
					'label' => 'وصول لمختلف قطاعات الاعمال',
				),
			),
			'offers_title'      => 'ماذا نقدّم؟',
			'offers'            => array(
				array(
					'title' => 'محتوى مميز',
					'text'  => 'يعكس مستجدات عالم المال والأعمال',
				),
				array(
					'title' => 'حوارات صحافية',
					'text'  => 'مع نخبة من رجال وسيدات الأعمال وشباب الأعمال',
				),
				array(
					'title' => 'أخبار القطاعات',
					'text'  => 'التجارية والصناعية والعقارية',
				),
				array(
					'title' => 'تحقيقات صحفية',
					'text'  => 'حول أبرز القضايا الاقتصادية',
				),
				array(
					'title' => 'تغطيات الفعاليات',
					'text'  => 'تغطية المنتديات والأنشطة التي تنظمها غرفة جدة',
				),
			),
			'mission'           => 'وتسعى المجلة إلى تقديم محتوى يلبي اهتمامات مختلف شرائح القرّاء، من المهتمين بالأنشطة التجارية إلى المختصين، من خلال طرح معلومات موثوقة وتحليلات تسهم في دعم وتطوير الأعمال.',
			'dist_intro'        => 'تصدر المجلة إلكترونياً عبر موقعها الرسمي، إلى جانب نسخة مطبوعة يتم توزيعها على:',
			'dist_items'        => array(
				array( 'text' => 'الجهات الحكومية والخاصة' ),
				array( 'text' => 'البنوك والشركات والمؤسسات التجارية والصناعية' ),
				array( 'text' => 'رجال وسيدات الأعمال' ),
				array( 'text' => 'الوزارات وإمارات المناطق في مختلف مدن المملكة' ),
				array( 'text' => 'ضيوف غرفة جدة في الفعاليات والمنتديات على مدار العام' ),
			),
			'hq_kicker'         => 'المقر الرئيسي',
			'hq_title'          => 'غرفة جدة',
			'hq_address'        => 'جدة – شارع الأمانة – البغدادية الغربية – الدور الخامس',
			'hq_phone'          => '+966 12 239 8148',
			'hq_phone_label'    => 'هاتف',
			'hq_po_label'       => 'ص.ب . 1264',
			'hq_po_value'       => 'جدة . 21431',
			'hq_contact_label'  => 'تواصل معنا',
			'hq_advertise_label'=> 'أعلن معنا',
		),
		'contact'   => array(
			'lead'            => 'للاستفسارات التحريرية أو الاعلانية — يسعدنا تواصلكم عبر النموذج أو قنوات الاتصال المباشرة.',
			'chip_email'      => 'salah.abulwafa@jcci.org.sa',
			'chip_name'       => 'صلاح أبو الوفا',
			'chip_org'        => 'الغرفة التجارية بجدة',
			'form_title'      => 'أرسل رسالة',
			'success_message' => 'شكراً لتواصلكم — سيتم الرد في أقرب وقت.',
			'agent_kicker'    => 'للإعلانات',
			'agent_title'     => 'غرفة جدة',
			'agent_intro'     => 'لمبيعات الإعلانات في الموقع والنسخة المطبوعة.',
			'agent_name'      => 'صلاح أبو الوفا رمضان',
			'agent_role'      => 'مدير التسويق والعلاقات العامة',
			'agent_phone'     => '+966 12 239 8148',
			'agent_mobile'    => '+966 50 438 4206',
			'agent_email'     => 'salah.abulwafa@jcci.org.sa',
			'route_editorial' => 'editor@altijaramagazine.com',
			'route_ads'       => 'ads@altijaramagazine.com',
			'route_subscribe' => 'subscribe@altijaramagazine.com',
			'route_tech'      => 'tech@altijaramagazine.com',
		),
		'advertise' => array(
			'lead'              => 'مساحتان إعلانيتان فقط بهوية الموقع الجديدة — ظهور واضح وفعّال عبر النسخة الإلكترونية والمطبوعة.',
			'agent_kicker'      => 'غرفة جدة',
			'agent_title'       => 'الوكيل الحصري لمبيعات الإعلانات',
			'agent_intro'       => 'غرفة جدة هي الوكيل الحصري لمبيعات الإعلانات في موقع مجلة «التجارة» الإلكتروني والنسخة المطبوعة. لإعلاناتكم ونشر أخباركم بالموقع الإلكتروني أو النسخة المطبوعة يُرجى التواصل مع:',
			'agent_name'        => 'صلاح أبو الوفا رمضان',
			'agent_role'        => 'مدير التسويق والعلاقات العامة',
			'agent_phone'       => '+966 12 239 8148',
			'agent_mobile'      => '+966 50 438 4206',
			'agent_email'       => 'salah.abulwafa@jcci.org.sa',
			'ad_specs'          => array(
				array(
					'title'  => 'إعلان ليدربورد',
					'subtitle' => 'Leaderboard · 728 × 90',
					'size'   => 'المقاس: 728 × 90 بكسل',
					'variant'=> 'leader',
					'points' => "إعلان أعلى يسار الموقع بجوار اللوجو\nيظهر في جميع الأقسام و المقالات\nتكلفة النشر حسب الفترة المتعاقد عليها",
					'price'  => '3,500',
					'price_note' => '/ شهرياً',
				),
				array(
					'title'  => 'إعلان العمود الجانبي',
					'subtitle' => 'Medium Rectangle · 300 × 250',
					'size'   => 'المقاس: 300 × 250 بكسل',
					'variant'=> 'medium',
					'points' => "إعلان العمود الجانبي للصفحات\nيظهر في الصفحة الرئيسية والمقالات\nتكلفة النشر حسب الفترة المتعاقد عليها",
					'price'  => '3,000',
					'price_note' => '/ شهرياً',
				),
			),
			'form_title'        => 'اطلب عرضاً إعلانياً',
			'form_lead'         => 'أرسل بياناتك وسيتواصل فريق المبيعات بخصوص التوافر والمواقع المتاحة.',
			'success_message'   => 'تم استلام طلبكم — سيتواصل فريق الإعلانات في غرفة جدة قريباً.',
			'why_title'         => 'لماذا تعلن معنا؟',
			'why_text'          => 'وصول مباشر إلى مجتمع الأعمال في جدة عبر منصة عريقة منذ 1948 — إلكتروني ومطبوع.',
			'media_kit_url'     => '',
			'guidelines'        => '',
		),
		'subscribe' => array(
			'lead'            => 'أبرز التقارير والحوارات وأعداد المجلة — مباشرة إلى بريدك.',
			'success_message' => 'تم تأكيد اشتراكك — راقب بريدك قريباً.',
			'consent_text'    => 'أوافق على استلام الرسائل من مجلة التجارة ويمكنني إلغاء الاشتراك في أي وقت.',
			'plans'           => array(
				array(
					'value' => 'newsletter',
					'title' => 'النشرة الأسبوعية',
					'text'  => 'مجاناً — ملخص أسبوعي لأبرز الأخبار',
				),
				array(
					'value' => 'digital',
					'title' => 'النسخة الرقمية',
					'text'  => 'تصفح أعداد المجلة والتقارير الخاصة عبر الموقع',
				),
				array(
					'value' => 'full',
					'title' => 'النسخة المطبوعة',
					'text'  => 'تصلك النسخة الورقية من المجلة عند صدورها',
				),
			),
		),
		'login'     => array(
			'lead'            => 'ادخل إلى حسابك لمتابعة المحتوى والاشتراكات المخصصة.',
			'success_message' => 'تم تسجيل الدخول بنجاح (وضع تجريبي).',
			'forgot_label'    => 'نسيت كلمة المرور؟',
			'subscribe_note'  => 'ليس لديك حساب؟',
			'subscribe_link_label' => 'اشترك',
		),
	);

	return isset( $defaults[ $schema ] ) ? $defaults[ $schema ] : array();
}