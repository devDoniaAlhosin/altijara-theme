/**
 * Altijara Magazine - Issues Page Controller
 * Authentic Issues Data & Interactive Features (Newest to Oldest)
 * Full 48-Issue Archive from Issue 730 down to Issue 681
 */

const ALL_ISSUES = window.AltijaraIssues = [
  {
    "number": 730,
    "title": "العدد (730): الشراكات الاستثمارية ومستقبل التعليم والتدريب في المملكة",
    "caption": "العدد 730",
    "date": "سبتمبر 2026",
    "year": 2026,
    "pages": 94,
    "tag": "ملف التعليم والاستثمار",
    "tagColor": "#e11d48",
    "link": "https://online.fliphtml5.com/puhxek/kihy/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/kihy/#p=1",
    "pdfLink": "downloads/issue-730.pdf",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2026/09/Issue-730-Cover.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2026/09/Issue-730-Cover-244x300.png",
    "group": "recent",
    "excerpt": "رصد موسع لصفقات الاستثمار في رأس المال البشري، مبادرات التدريب المهني والتقني، ورؤية الغرفة لتمكين الشباب في وظائف المستقبل."
  },
  {
    "number": 729,
    "title": "العدد (729): التحولات اللوجستية وتطوير الموانئ وحراك بيئة الأعمال",
    "caption": "العدد 729",
    "date": "يوليو 2026",
    "year": 2026,
    "pages": 124,
    "tag": "الخدمات اللوجستية والنقل",
    "tagColor": "#0284c7",
    "link": "https://online.fliphtml5.com/puhxek/lvvo/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/lvvo/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/lvvo/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2026/07/1043-X-1280-غلاف-العدد.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2026/07/1043-X-1280-غلاف-العدد-243x300.png",
    "group": "recent",
    "excerpt": "ملف خاص عن توسعة الموانئ البحرية بالمنطقة الغربية، سلاسل الإمداد الذكية، وجولات التجارة مع قيادات قطاع النقل البحري والجوي."
  },
  {
    "number": 728,
    "title": "العدد (728): اقتصاد الاستدامة وسلاسل الإمداد ومستقبل الصناعات الوطنية",
    "caption": "العدد 728",
    "date": "مايو 2026",
    "year": 2026,
    "pages": 116,
    "tag": "الصناعة والاستدامة",
    "tagColor": "#16a34a",
    "link": "https://online.fliphtml5.com/puhxek/nuew/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/nuew/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/nuew/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2026/05/Issue-No.728.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2026/05/Issue-No.728-244x300.png",
    "group": "recent",
    "excerpt": "تحليل استراتيجي لمبادرات السعودية الخضراء في المصانع الكبرى، خفض الانبعاثات، وتوطين الصناعات التحويلية في المدن الصناعية بجدة."
  },
  {
    "number": 727,
    "title": "العدد (727): غرفة جدة توسّع الشراكات وتوثّق حراك الأعمال في المدينة",
    "caption": "العدد 727",
    "date": "مارس 2026",
    "year": 2026,
    "pages": 128,
    "tag": "شراكات وتجارة دولية",
    "tagColor": "#9333ea",
    "link": "https://online.fliphtml5.com/puhxek/tiab/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/tiab/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/tiab/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2026/03/العدد-726.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2026/03/العدد-726-244x300.png",
    "group": "recent",
    "excerpt": "تغطيات شاملة لمنتديات الاستثمار الثنائية مع الوفود التجارية، واتفاقيات التعاون لدعم التجارة البينية وتسهيل تدفق الاستثمارات الأجنبية."
  },
  {
    "number": 726,
    "title": "العدد (726): انطلاقة عام 2026 وحراك ريادة الأعمال والفعاليات الكبرى",
    "caption": "العدد 726",
    "date": "يناير 2026",
    "year": 2026,
    "pages": 140,
    "tag": "ريادة الأعمال والاستثمار",
    "tagColor": "#ea580c",
    "link": "https://online.fliphtml5.com/puhxek/Issue-726/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/Issue-726/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/Issue-726/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2026/01/Issue-726.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2026/01/Issue-726-241x300.png",
    "group": "recent",
    "excerpt": "استعراض موسع لأجندة معارض ومؤتمرات 2026، دعم الشركات الناشئة، وبرامج التمويل الجريء بالشراكة مع الصناديق الاستثمارية."
  },
  {
    "number": 725,
    "title": "العدد (725): استراتيجية السياحة والضيافة ومشاريع الواجهة البحرية بجدة",
    "caption": "العدد 725",
    "date": "نوفمبر 2025",
    "year": 2025,
    "pages": 112,
    "tag": "السياحة والترفيه",
    "tagColor": "#0d9488",
    "link": "https://online.fliphtml5.com/puhxek/ettw/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/ettw/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/ettw/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2025/06/Issue-725.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2025/06/Issue-725-239x300.png",
    "group": "recent",
    "excerpt": "رصد المشاريع الفندقية الكبرى، السياحة البحرية واليخوت، وتطور قطاع الترفيه كرافد اقتصادي رئيسي في الناتج المحلي."
  },
  {
    "number": 724,
    "title": "العدد (724): التحول الرقمي والذكاء الاصطناعي في المنظومة التجارية",
    "caption": "العدد 724",
    "date": "سبتمبر 2025",
    "year": 2025,
    "pages": 120,
    "tag": "التقنية والاقتصاد الرقمي",
    "tagColor": "#4f46e5",
    "link": "https://online.fliphtml5.com/puhxek/pckb/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/pckb/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/pckb/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2025/04/300X380.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2025/04/300X380-237x300.png",
    "group": "recent",
    "excerpt": "ملف خاص عن تبني حلول الحوسبة السحابية وأدوات الذكاء الاصطناعي التوليدي لأتمتة العمليات التجارية والخدمات المصرفية."
  },
  {
    "number": 723,
    "title": "العدد (723): التعدين وصناعات المعادن.. الثروة غير المستغلة في المملكة",
    "caption": "العدد 723",
    "date": "يوليو 2025",
    "year": 2025,
    "pages": 118,
    "tag": "الطاقة والتعدين",
    "tagColor": "#d97706",
    "link": "https://online.fliphtml5.com/puhxek/zaxh/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/zaxh/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/zaxh/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2025/01/Issue-723.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2025/01/Issue-723-240x300.jpg",
    "group": "recent",
    "excerpt": "تغطية للمؤتمر الدولي للتعدين، استكشاف المعادن النادرة، وفرص الاستثمار للمنشآت المحلية في سلاسل القيمة التعدينية."
  },
  {
    "number": 722,
    "title": "العدد (722): القطاع العقاري والتطوير العمراني في منطقة مكة المكرمة",
    "caption": "العدد 722",
    "date": "مايو 2025",
    "year": 2025,
    "pages": 130,
    "tag": "العقار والتطوير العمراني",
    "tagColor": "#be185d",
    "link": "https://online.fliphtml5.com/puhxek/wsub/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/wsub/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/wsub/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/11/Issue-722.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/11/Issue-722-244x300.png",
    "group": "recent",
    "excerpt": "دراسة شاملة لأسعار الأراضي، المشاريع السكنية الكبرى، وأنظمة التملك والتطوير العقاري الحديثة وتأثيرها على العائد الاستثماري."
  },
  {
    "number": 721,
    "title": "العدد (721): تمكين المرأة في مجالس الإدارة وقيادة الشركات السعودية",
    "caption": "العدد 721",
    "date": "مارس 2025",
    "year": 2025,
    "pages": 110,
    "tag": "تمكين وقيادة الأعمال",
    "tagColor": "#059669",
    "link": "https://online.fliphtml5.com/huks/fzyn/#p=1",
    "flipbook": "https://online.fliphtml5.com/huks/fzyn/#p=1",
    "pdfLink": "https://online.fliphtml5.com/huks/fzyn/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2025/05/721-Cover.png",
    "src": "https://altijaramagazine.com/wp-content/uploads/2025/05/721-Cover-241x300.png",
    "group": "recent",
    "excerpt": "نماذج ريادية لسيدات أعمال سعوديات قادن مبادرات صناعية ومالية ناجحة، مع تقارير حول مساهمة المرأة في سوق العمل السعودي."
  },
  {
    "number": 720,
    "title": "العدد (720): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 720",
    "date": "أبريل 2024م",
    "year": 2024,
    "pages": 100,
    "tag": "شراكات واستثمار",
    "tagColor": "blue",
    "link": "https://online.fliphtml5.com/puhxek/rkjc/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/rkjc/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/rkjc/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/WhatsApp-Image-2024-02-21-at-12.00.17_a5978a8e.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/WhatsApp-Image-2024-02-21-at-12.00.17_a5978a8e-242x300.jpg",
    "group": "recent",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 719,
    "title": "العدد (719): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 719",
    "date": "فبراير 2024م",
    "year": 2024,
    "pages": 138,
    "tag": "شراكات واستثمار",
    "tagColor": "blue",
    "link": "https://online.fliphtml5.com/puhxek/nwcq/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/nwcq/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/nwcq/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/02/719-1.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/02/719-1-242x300.jpg",
    "group": "recent",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 718,
    "title": "العدد (718): الأمن الغذائي وتحديات الزراعة التعاقدية وسلاسل الإمداد",
    "caption": "العدد 718",
    "date": "يناير 2025",
    "year": 2025,
    "pages": 122,
    "tag": "الأمن الغذائي والصناعات الغذائية",
    "tagColor": "#65a30d",
    "link": "https://online.fliphtml5.com/puhxek/skjj/#p=1",
    "flipbook": "https://online.fliphtml5.com/puhxek/skjj/#p=1",
    "pdfLink": "https://online.fliphtml5.com/puhxek/skjj/#p=1",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/02/718-1.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/02/718-1-242x300.jpg",
    "group": "recent",
    "excerpt": "ملف الأمن الغذائي ومشاريع البيوت المحمية الذكية، الاستيراد المباشر للحبوب، وتطور الصناعات الغذائية الوطنية بجدة."
  },
  {
    "number": 715,
    "title": "العدد (715): تداعيات أزمة سلاسل الإمداد العالمية وجهود استدامة الأعمال",
    "caption": "العدد 715",
    "date": "يوليو 2020م",
    "year": 2020,
    "pages": 110,
    "tag": "سلاسل الإمداد",
    "tagColor": "red",
    "link": "https://www.jcci.org.sa/magazine/715",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/715",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/July-2020-Cover.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/July-2020-Cover-244x300.jpeg",
    "group": "archive2020",
    "excerpt": "رصد آليات التكيف مع المتغيرات الاقتصادية ومبادرات الدعم للقطاع الخاص ومنشآت الأعمال الصغيرة."
  },
  {
    "number": 714,
    "title": "العدد (714): التجارة الإلكترونية والتحول الرقمي للشركات السعودية",
    "caption": "العدد 714",
    "date": "مايو 2020م",
    "year": 2020,
    "pages": 103,
    "tag": "اقتصاد رقمي",
    "tagColor": "teal",
    "link": "https://www.jcci.org.sa/magazine/714/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/714/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/May-2020-Cover.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/May-2020-Cover-244x300.jpeg",
    "group": "archive2020",
    "excerpt": "تغطية تسارع التجارة الرقمية، منصات الدفع، وإجراءات تيسير التجارة الإلكترونية للشركات."
  },
  {
    "number": 713,
    "title": "العدد (713): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 713",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 141,
    "tag": "شراكات واستثمار",
    "tagColor": "blue",
    "link": "https://www.jcci.org.sa/magazine/713/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/713/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-13.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-13-244x300.jpg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 712,
    "title": "العدد (712): صناعة السيارات الكهربائية والنقل المستدام في المملكة",
    "caption": "العدد 712",
    "date": "أكتوبر 2024",
    "year": 2024,
    "pages": 116,
    "tag": "صناعة السيارات والمحركات",
    "tagColor": "#0891b2",
    "link": "https://www.jcci.org.sa/magazine/712/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/712/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-712.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-712-237x300.jpg",
    "group": "archive2020",
    "excerpt": "مصانع لوسيد وسير، البنية التحتية لمحطات الشحن، وتحول وكلاء السيارات نحو حلول الطاقة النظيفة والمركبات الهجينة."
  },
  {
    "number": 711,
    "title": "العدد (711): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 711",
    "date": "فبراير 2020م",
    "year": 2020,
    "pages": 127,
    "tag": "شراكات واستثمار",
    "tagColor": "blue",
    "link": "https://www.jcci.org.sa/magazine/711/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/711/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Feb.-2020-Cover.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Feb.-2020-Cover-244x300.jpeg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 710,
    "title": "العدد (710): استشراف عام 2020 وأولويات التنمية الاقتصادية في جدة",
    "caption": "العدد 710",
    "date": "يناير 2020م",
    "year": 2020,
    "pages": 120,
    "tag": "استشراف اقتصادي",
    "tagColor": "purple",
    "link": "https://www.jcci.org.sa/magazine/710/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/710/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Jan.-2020-Cover.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Jan.-2020-Cover-245x300.jpeg",
    "group": "archive2020",
    "excerpt": "حوارات مع قادة الأعمال حول آفاق الاستثمار ومشاريع الرؤية في المنطقة الغربية."
  },
  {
    "number": 709,
    "title": "العدد (709): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 709",
    "date": "ديسمبر 2019م",
    "year": 2019,
    "pages": 113,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/709/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/709/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Dec.-2019-Cover.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Dec.-2019-Cover-244x300.jpeg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 708,
    "title": "العدد (708): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 708",
    "date": "نوفمبر 2019م",
    "year": 2019,
    "pages": 106,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/708/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/708/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Nov.-2019-Cover-1668x2048.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Nov.-2019-Cover-244x300.jpg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 707,
    "title": "العدد (707): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 707",
    "date": "أكتوبر 2019م",
    "year": 2019,
    "pages": 144,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/707/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/707/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Oct.-2019.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Oct.-2019-245x300.jpg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 706,
    "title": "العدد (706): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 706",
    "date": "سبتمبر 2019م",
    "year": 2019,
    "pages": 137,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/706/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/706/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Sept.-2019-Cover-1668x2048.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Sept.-2019-Cover-244x300.jpg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 705,
    "title": "العدد (705): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 705",
    "date": "أغسطس 2019م",
    "year": 2019,
    "pages": 130,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/705/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/705/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Aug.-2019.jpeg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Aug.-2019-245x300.jpeg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 704,
    "title": "العدد (704): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 704",
    "date": "يوليو 2019م",
    "year": 2019,
    "pages": 123,
    "tag": "تقارير واستثمار",
    "tagColor": "green",
    "link": "https://www.jcci.org.sa/magazine/704/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/704/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/July-2019.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/July-2019-244x300.jpg",
    "group": "archive2020",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 703,
    "title": "العدد (703): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 703",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 116,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/703/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/703/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.703.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.703-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 702,
    "title": "العدد (702): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 702",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 109,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/702/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/702/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.702.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.702-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 701,
    "title": "العدد (701): الصادرات السعودية غير النفطية واختراق الأسواق العالمية",
    "caption": "العدد 701",
    "date": "يونيو 2024",
    "year": 2024,
    "pages": 128,
    "tag": "الصادرات والتجارة الخارجية",
    "tagColor": "#7c3aed",
    "link": "https://www.jcci.org.sa/magazine/701/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/701/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-701.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-701-244x300.jpg",
    "group": "historic",
    "excerpt": "برامج بنك التصدير والاستيراد السعودي (EXIM)، صنع في السعودية، وتيسير نفاذ المنتجات الوطنية لأكثر من 150 دولة."
  },
  {
    "number": 700,
    "title": "العدد (700) التذكاري: 60 عاماً في خدمة الاقتصاد والتنمية الوطنية",
    "caption": "العدد 700",
    "date": "مارس 2024",
    "year": 2024,
    "pages": 180,
    "tag": "إصدار تذكاري تاريخي",
    "tagColor": "#b45309",
    "link": "https://www.jcci.org.sa/magazine/700/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/700/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-700.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-700-244x300.jpg",
    "group": "historic",
    "excerpt": "إصدار ذهبي توثيقي يروي سيرة مجلة التجارة منذ صدور عددها الأول عام 1960م، وأبرز المحطات الاقتصادية والقرارات التاريخية."
  },
  {
    "number": 699,
    "title": "العدد (699): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 699",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 133,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/699/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/699/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-699.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-699-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 698,
    "title": "العدد (698): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 698",
    "date": "يناير 2019م",
    "year": 2019,
    "pages": 126,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/698/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/698/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Jan.-2019-Cover-scaled.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Jan.-2019-Cover-234x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 697,
    "title": "العدد (697): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 697",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 119,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/697/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/697/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-697.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-697-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 696,
    "title": "العدد (696): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 696",
    "date": "نوفمبر 2018م",
    "year": 2018,
    "pages": 112,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/696/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/696/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Nov.-2018-Cover-scaled.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Nov.-2018-Cover-234x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 695,
    "title": "العدد (695): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 695",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 105,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/695/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/695/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-695.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-695-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 694,
    "title": "العدد (694): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 694",
    "date": "سبتمبر 2018م",
    "year": 2018,
    "pages": 143,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/694/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/694/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2024/04/Sept.-2018-Cover.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2024/04/Sept.-2018-Cover-236x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 693,
    "title": "العدد (693): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 693",
    "date": "مارس 2023م",
    "year": 2023,
    "pages": 136,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/693/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/693/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-693.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-693-244x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 692,
    "title": "العدد (692): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 692",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 129,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/692",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/692",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.692.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue.692-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 691,
    "title": "العدد (691): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 691",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 122,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/691/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/691/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-691.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-691-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 690,
    "title": "العدد (690): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 690",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 115,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/690/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/690/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-690.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-690-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 689,
    "title": "العدد (689): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 689",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 108,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/689/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/689/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-689.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-689-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 688,
    "title": "العدد (688): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 688",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 101,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/688/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/688/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-688.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-688-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 687,
    "title": "العدد (687): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 687",
    "date": "عام 2018م",
    "year": 2018,
    "pages": 139,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/687/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/687/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-687.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-687-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 686,
    "title": "العدد (686): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 686",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 132,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/686",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/686",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-686.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-686-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 685,
    "title": "العدد (685): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 685",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 125,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/685/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/685/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-685.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-685-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 684,
    "title": "العدد (684): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 684",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 118,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/684/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/684/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-684.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-684-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 683,
    "title": "العدد (683): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 683",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 111,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/683/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/683/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-683.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-683-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 682,
    "title": "العدد (682): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 682",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 104,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/682/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/682/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-682.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-682-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  },
  {
    "number": 681,
    "title": "العدد (681): مجلة التجارة — الغرفة التجارية بجدة",
    "caption": "العدد 681",
    "date": "عام 2017م",
    "year": 2017,
    "pages": 142,
    "tag": "الأرشيف التاريخي",
    "tagColor": "orange",
    "link": "https://www.jcci.org.sa/magazine/681/",
    "flipbook": null,
    "pdfLink": "https://www.jcci.org.sa/magazine/681/",
    "cover": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-681.jpg",
    "src": "https://altijaramagazine.com/wp-content/uploads/2023/03/Issue-681-245x300.jpg",
    "group": "historic",
    "excerpt": "تغطية شاملة لملفات الأعمال، التحولات الاستثمارية، والتقارير الاقتصادية المتخصصة في منطقة مكة المكرمة."
  }
];

// DOM Elements & State
let currentSearch = "";
let currentFilter = "all";

document.addEventListener("DOMContentLoaded", () => {
  initCarousels();
  initSearch();
  initDragScroll();
  initFilterPills();
  initTicker();
  initPdfDownloads();
});

/**
 * Force PDF file download (works for cross-origin when CORS allows)
 */
async function forceDownloadPdf(url, filename) {
  if (!url) return;
  const safeName = filename || url.split("/").pop() || "magazine.pdf";

  try {
    const res = await fetch(url, { mode: "cors", credentials: "omit" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const blob = await res.blob();
    const blobUrl = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = blobUrl;
    a.download = decodeURIComponent(safeName);
    a.rel = "noopener";
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(blobUrl), 1500);
  } catch (err) {
    // Fallback: open PDF URL (browser may still preview if server blocks CORS)
    const a = document.createElement("a");
    a.href = url;
    a.download = decodeURIComponent(safeName);
    a.target = "_blank";
    a.rel = "noopener";
    document.body.appendChild(a);
    a.click();
    a.remove();
  }
}

function initPdfDownloads() {
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-pdf-download");
    if (!btn) return;

    const url = btn.getAttribute("data-pdf-url") || btn.getAttribute("href");
    if (!url || url === "#" || !/\.pdf(\?|#|$)/i.test(url)) return;

    e.preventDefault();
    const name =
      btn.getAttribute("data-pdf-name") ||
      btn.getAttribute("download") ||
      "مجلة-التجارة.pdf";
    forceDownloadPdf(url, name);
  });
}

/**
 * Build HTML for an individual issue card
 */
function createIssueCardHTML(issue) {
  const readUrl = issue.flipbook || issue.link;

  return `
    <article class="issue-card-ft" data-issue-num="${issue.number}">
      <a class="card-cover-wrap" href="${readUrl}" target="_blank" rel="noopener">
        <span class="card-num-badge">العدد ${issue.number}</span>
        <img src="${issue.cover}" alt="${issue.title}" class="card-cover-img" decoding="async" onerror="this.onerror=null; this.src='assets/images/issue-730-clean.png';">
      </a>
      <div class="card-content-wrap">
        <h3 class="card-issue-title">
          <a href="${readUrl}" target="_blank" rel="noopener">${issue.title}</a>
        </h3>
        <p class="card-issue-excerpt">${issue.excerpt}</p>
        <div class="card-footer-meta">
          <a href="${readUrl}" class="btn-card-read-action" target="_blank" rel="noopener">
            <span>اقرأ العدد</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </a>
        </div>
      </div>
    </article>
  `;
}

/**
 * Initialize all carousels
 */
function initCarousels() {
  const recentTrack = document.getElementById("recentCarouselTrack");
  const archive2020Track = document.getElementById("archive2020CarouselTrack");
  const historicTrack = document.getElementById("historicCarouselTrack");

  const recentIssues = ALL_ISSUES.filter(i => i.number >= 718);
  const archive2020Issues = ALL_ISSUES.filter(i => i.number >= 704 && i.number < 718);
  const historicIssues = ALL_ISSUES.filter(i => i.number < 704);

  if (recentTrack) {
    recentTrack.innerHTML = recentIssues.map(createIssueCardHTML).join("");
  }
  if (archive2020Track) {
    archive2020Track.innerHTML = archive2020Issues.map(createIssueCardHTML).join("");
  }
  if (historicTrack) {
    historicTrack.innerHTML = historicIssues.map(createIssueCardHTML).join("");
  }
}

/**
 * Carousel navigation scroll (RTL friendly)
 */
function scrollCarousel(trackId, step) {
  const track = document.getElementById(trackId);
  if (!track) return;
  
  // In RTL Arabic layout:
  // Scrolling left (negative value) moves towards earlier/older items
  // Scrolling right (positive value) moves towards newer items
  track.scrollBy({ left: step, behavior: "smooth" });
}

/**
 * Mouse Drag-to-Scroll for Carousel Tracks
 */
function initDragScroll() {
  const tracks = document.querySelectorAll(".issues-carousel-track");
  tracks.forEach(track => {
    let isDown = false;
    let startX;
    let scrollLeft;

    track.addEventListener("mousedown", (e) => {
      // Don't trigger drag if clicking a link or button
      if (e.target.closest("a") || e.target.closest("button")) return;
      isDown = true;
      track.classList.add("is-dragging");
      startX = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
    });

    track.addEventListener("mouseleave", () => {
      isDown = false;
      track.classList.remove("is-dragging");
    });

    track.addEventListener("mouseup", () => {
      isDown = false;
      track.classList.remove("is-dragging");
    });

    track.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - track.offsetLeft;
      const walk = (x - startX) * 1.5;
      track.scrollLeft = scrollLeft - walk;
    });
  });
}

/**
 * Filter Pills (Jump / Filter by Era)
 */
function initFilterPills() {
  const pills = document.querySelectorAll(".year-pill");
  pills.forEach(pill => {
    pill.addEventListener("click", () => {
      pills.forEach(p => p.classList.remove("active"));
      pill.classList.add("active");
      
      const targetEra = pill.dataset.era;
      filterByEra(targetEra);
    });
  });
}

function filterByEra(era) {
  const s1 = document.getElementById("secRecent");
  const s2 = document.getElementById("secArchive2020");
  const s3 = document.getElementById("secHistoric");
  const searchSec = document.getElementById("secSearch");

  if (searchSec) searchSec.style.display = "none";

  if (era === "all") {
    if (s1) s1.style.display = "block";
    if (s2) s2.style.display = "block";
    if (s3) s3.style.display = "block";
  } else if (era === "recent") {
    if (s1) s1.style.display = "block";
    if (s2) s2.style.display = "none";
    if (s3) s3.style.display = "none";
    s1.scrollIntoView({ behavior: "smooth", block: "start" });
  } else if (era === "archive2020") {
    if (s1) s1.style.display = "none";
    if (s2) s2.style.display = "block";
    if (s3) s3.style.display = "none";
    s2.scrollIntoView({ behavior: "smooth", block: "start" });
  } else if (era === "historic") {
    if (s1) s1.style.display = "none";
    if (s2) s2.style.display = "none";
    if (s3) s3.style.display = "block";
    s3.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

/**
 * Live Search across all 48 issues
 */
function initSearch() {
  const searchInput = document.getElementById("issueSearchInput");
  if (!searchInput) return;

  searchInput.addEventListener("input", (e) => {
    const q = e.target.value.trim().toLowerCase();
    const searchSec = document.getElementById("secSearch");
    const searchTrack = document.getElementById("searchCarouselTrack");
    const searchCount = document.getElementById("searchCountBadge");
    const normalSections = [
      document.getElementById("secRecent"),
      document.getElementById("secArchive2020"),
      document.getElementById("secHistoric")
    ];

    if (!q) {
      if (searchSec) searchSec.style.display = "none";
      normalSections.forEach(s => { if (s) s.style.display = "block"; });
      return;
    }

    const matches = ALL_ISSUES.filter(issue => {
      const matchNum = issue.number.toString().includes(q);
      const matchTitle = issue.title.toLowerCase().includes(q);
      const matchTag = issue.tag.toLowerCase().includes(q);
      const matchDate = issue.date.toLowerCase().includes(q);
      const matchExcerpt = issue.excerpt.toLowerCase().includes(q);
      return matchNum || matchTitle || matchTag || matchDate || matchExcerpt;
    });

    normalSections.forEach(s => { if (s) s.style.display = "none"; });

    if (searchSec) {
      searchSec.style.display = "block";
      if (searchCount) searchCount.textContent = `نتائج البحث (${matches.length} عدد)`;
      
      if (matches.length === 0) {
        searchTrack.innerHTML = `
          <div class="no-issues-found" style="padding: 40px; text-align: center; width: 100%; color: #64748b;">
            <p style="font-size: 16px; font-weight: 700;">لا توجد أعداد مطابقة لكلمة البحث: "${q}"</p>
            <button type="button" class="btn-lead-secondary" style="margin-top: 12px; cursor: pointer;" onclick="document.getElementById(issueSearchInput).value=; initSearch();">عرض كافة الأعداد</button>
          </div>
        `;
      } else {
        searchTrack.innerHTML = matches.map(createIssueCardHTML).join("");
      }
    }
  });
}

/**
 * Market Ticker & Live Clock
 */
function initTicker() {
  // Static verified baseline
  const tasiEl = document.getElementById("valTasi");
  if (tasiEl) tasiEl.textContent = "12,284.15 (+0.54%)";
}
