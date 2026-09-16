<?php
/**
 * Базовая настройка темы и подключение ассетов.
 *
 * @package maclab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Поддержка возможностей WordPress.
 */
function maclab_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus(
		array(
			'primary' => __( 'Главное меню', 'maclab' ),
		)
	);
}
add_action( 'after_setup_theme', 'maclab_setup' );

/**
 * Версия файла по времени изменения — чтобы кэш не держал старые стили.
 *
 * @param string $rel Путь относительно темы.
 * @return string
 */
function maclab_ver( $rel ) {
	$file = MACLAB_DIR . '/' . ltrim( $rel, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : MACLAB_VERSION;
}

/**
 * Стили и скрипты фронтенда.
 */
function maclab_assets() {
	wp_enqueue_style( 'maclab-fonts', MACLAB_URI . '/assets/css/fonts.css', array(), maclab_ver( 'assets/css/fonts.css' ) );
	wp_enqueue_style( 'maclab-main', MACLAB_URI . '/assets/css/style.css', array( 'maclab-fonts' ), maclab_ver( 'assets/css/style.css' ) );

	wp_enqueue_script( 'maclab-lenis', MACLAB_URI . '/assets/js/vendor/lenis.min.js', array(), '1.1.13', true );
	wp_enqueue_script( 'maclab-gsap', MACLAB_URI . '/assets/js/vendor/gsap.min.js', array(), '3.15.0', true );
	wp_enqueue_script( 'maclab-app', MACLAB_URI . '/assets/js/app.js', array( 'maclab-lenis', 'maclab-gsap' ), maclab_ver( 'assets/js/app.js' ), true );

	wp_localize_script(
		'maclab-app',
		'MACLAB',
		array(
			'rest'  => esc_url_raw( rest_url( 'maclab/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'phone' => maclab_option( 'phone' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'maclab_assets' );

/**
 * Предзагрузка основных начертаний.
 */
function maclab_preload_fonts() {
	$fonts = array( 'unbounded-500-cyrillic.woff2', 'manrope-500-cyrillic.woff2' );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( MACLAB_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'maclab_preload_fonts', 1 );

/**
 * Иконка вкладки (SVG в data-URI, чтобы не тянуть файл).
 */
function maclab_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 32 32\'%3E%3Crect width=\'32\' height=\'32\' rx=\'8\' fill=\'%23FF3B2F\'/%3E%3Ctext x=\'16\' y=\'22\' font-size=\'16\' font-family=\'sans-serif\' font-weight=\'bold\' fill=\'white\' text-anchor=\'middle\'%3Em%3C/text%3E%3C/svg%3E">' . "\n";
}
add_action( 'wp_head', 'maclab_favicon', 2 );

/**
 * Цена в формате «109 990 ₽».
 *
 * @param int $value Сумма.
 * @return string
 */
function maclab_price( $value ) {
	return number_format( (float) $value, 0, ',', ' ' ) . ' ₽';
}

/**
 * Заголовок вкладки на главной: «Название сайта — Краткое описание».
 * Оба значения меняются в «Настройки → Общие». Приоритет 99, чтобы значение
 * не перебивал SEO-плагин.
 *
 * @param string $title Готовый заголовок.
 * @return string
 */
function maclab_front_title( $title ) {
	if ( ! is_front_page() ) {
		return $title;
	}
	$name = get_bloginfo( 'name' );
	$desc = get_bloginfo( 'description' );
	return $desc ? $name . ' — ' . $desc : $name;
}
add_filter( 'pre_get_document_title', 'maclab_front_title', 99 );

/**
 * Описание страницы для поисковиков, если SEO-плагин его не задал.
 */
function maclab_meta_description() {
	if ( ! is_front_page() || defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	printf(
		'<meta name="description" content="%s">' . "\n",
		esc_attr__( 'MacBook Air и MacBook Pro с гарантией 12 месяцев: проверка при получении, доставка по РФ за 1–3 дня, рассрочка 0%.', 'maclab' )
	);
}
add_action( 'wp_head', 'maclab_meta_description', 3 );
