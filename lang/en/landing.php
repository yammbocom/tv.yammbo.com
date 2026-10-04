<?php

return [
    'seo' => [
        'title' => 'Yammbo Tv — All entertainment, in one place',
        'description' => 'Movies, series, live TV and more. An infinite catalog in a polished app, available on your phone, web, Android TV and more.',
    ],

    'hero' => [
        'badge' => 'Unlimited streaming',
        'heading_1' => 'All entertainment,',
        'heading_2' => 'in one place',
        'tagline' => 'Thousands of movies, series, documentaries and live TV channels. Discover, organize and watch everything from Yammbo Tv — on your phone, TV, browser and more.',
        'cta_primary' => 'Get started',
        'cta_secondary' => 'See plans',
        'hint' => 'Cancel anytime · No lock-in',
    ],

    // Caption and alt text for the player screenshots. These used to be locale
    // ternaries inside index.blade.php: fine in ES and EN, impossible to
    // translate without editing the template.
    'shots' => [
        'discover_alt' => 'Yammbo Tv home screen showing the movie and series catalog',
        'discover_caption' => 'Catalog — discover what to watch',
        'detail_alt' => 'Title detail view in Yammbo Tv with synopsis, genres and cast',
        'detail_caption' => 'Title page — synopsis, genres and cast',
        'search_alt' => 'Search in Yammbo Tv showing movie and series results',
        'search_caption' => 'Instant search across the whole catalog',
        'board_alt' => 'Home screen with continue watching in Yammbo Tv',
        'board_caption' => 'Home — pick up right where you left off',
    ],

    'ui' => [
        'close' => 'Close',
    ],

    'features' => [
        'heading' => 'An experience built to enjoy',
        'subheading' => 'Yammbo Tv brings the best of streaming together in a clean, fast and thoughtful interface.',
        'group3_heading' => 'Home, with everything where you left it',

        'f1_title' => 'Infinite catalog',
        'f1_body' => 'Access hundreds of thousands of movies and series thanks to our expandable catalogs. There is always something new to discover.',

        'f2_title' => 'Multi-device',
        'f2_body' => 'Use it on Android phones, Android TV, Google TV and Fire TV, Windows or in the browser. Your account, all your devices.',

        'f3_title' => 'Subtitles in your language',
        'f3_body' => 'Automatic subtitles in dozens of languages so you never miss a line, no matter where the content comes from.',

        'f4_title' => 'High quality',
        'f4_body' => 'High quality playback sources come with every plan. The final quality depends on the source of each title.',

        'f5_title' => 'Chromecast',
        'f5_body' => 'Send what you watch in the web player to your TV with Chromecast.',

        'f6_title' => 'Ad-free',
        'f6_body' => 'Your time matters. No plan interrupts playback with ads.',

        'f7_title' => 'Early access',
        'f7_body' => 'Premium members get early access to new app features and improvements.',

        'f8_title' => '24/7 Support',
        'f8_body' => 'Our team is ready to help by email and chat, with VIP priority on Premium plans.',
    ],

    'pricing' => [
        'heading' => 'Choose the perfect plan for you',
        'subheading' => 'Pay monthly or yearly. Switch or cancel anytime, no commitment.',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'save' => 'Save up to 25%',
        'recommended' => 'Recommended',
        'per_month' => '/mo',
        'per_year' => '/yr',
        'cta' => 'Subscribe',
        'cta_choose' => 'Choose plan',
        'cta_current' => 'Current plan',
        'cta_login' => 'Sign in to subscribe',
    ],

    // Plan blurbs live here, not in the `plans.description` column: that column
    // holds a single string, so the English page rendered three Spanish cards.
    // Features de los planes (plans.features en BD, en español). Clave = slug del texto.
    'plan_features' => [
        'peliculas-y-series-a-la-carta' => 'Movies and series on demand',
        '1-dispositivo-a-la-vez' => '1 device at a time',
        '2-dispositivos-a-la-vez' => '2 devices at a time',
        '3-dispositivos-a-la-vez' => '3 devices at a time',
        'apps-para-movil-y-smart-tv' => 'Apps for mobile and Smart TV',
        'subtitulos-en-espanol' => 'Spanish subtitles',
        'sin-publicidad' => 'No ads',
        'cancela-cuando-quieras' => 'Cancel anytime',
        'tv-en-vivo' => 'Live TV',
        'tv-en-vivo-incluida' => 'Live TV included',
    ],

    'plans' => [
        'basic' => 'Perfect for getting started with streaming',
        'standard' => 'The best pick for families and film lovers',
        'premium' => 'The ultimate streaming experience, no limits',
    ],

    'faq' => [
        'heading' => 'Frequently asked questions',
        'q1_q' => 'How do I get started?',
        'q1_a' => 'Create your account, pick the plan that fits you and start watching right away. You can switch plans or cancel anytime.',
        'q2_q' => 'Which devices can I use Yammbo Tv on?',
        'q2_a' => 'Android phones, Android TV, Google TV and Fire TV, Windows, and the web browser on any computer. There is no app for iPhone or Apple TV. The web player works with Chromecast.',
        'q3_q' => 'Can I cancel anytime?',
        'q3_a' => 'Yes. Cancel from the "My account" section in a single click. You keep access until the end of the paid period.',
        'q4_q' => 'How many devices can I use at once?',
        'q4_a' => 'Basic: 1 device at a time. Standard: 2 at a time. Premium: 3 at a time.',
        'q5_q' => 'What image quality do you offer?',
        'q5_a' => 'Quality is not limited by plan: every plan gets the same high quality sources, and the final quality depends on the source of each title.',
    ],

    'cta' => [
        'heading' => 'Your next binge starts here',
        'subheading' => 'Sign up free and find out why thousands enjoy Yammbo Tv every day.',
        'button' => 'Start for free',
    ],

    'nav' => [
        'app' => 'Open app',
        'pricing' => 'Plans',
        'help' => 'Help',
        'login' => 'Sign in',
        'register' => 'Sign up',
        'my_account' => 'My account',
    ],

    'footer' => [
        'tagline' => 'All entertainment, in one place.',
        'section_product' => 'Product',
        'section_company' => 'Company',
        'section_legal' => 'Legal',
        'section_follow' => 'Follow us',

        'link_app' => 'Open application',
        'link_pricing' => 'Plans & pricing',
        'link_download' => 'Download',
        'link_features' => 'Features',

        'link_about' => 'About us',
        'link_blog' => 'Blog',
        'link_help' => 'Help center',
        'link_contact' => 'Contact',

        'link_terms' => 'Terms of service',
        'link_privacy' => 'Privacy policy',
        'link_refund' => 'Refund policy',

        'copyright' => '© :year Yammbo Tv. All rights reserved.',
    ],
];
