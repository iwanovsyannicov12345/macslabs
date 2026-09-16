<?php
/**
 * Шапка сайта.
 *
 * @package maclab
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="grain" aria-hidden="true"></div>
<div class="progress" id="prog" aria-hidden="true"></div>

<header class="hdr" id="hdr">
	<div class="wrap bar">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">mac<em>:</em>lab</a>
		<nav class="nav">
			<a href="#catalog"><?php esc_html_e( 'Каталог', 'maclab' ); ?></a>
			<a href="#config"><?php esc_html_e( 'Конфигуратор', 'maclab' ); ?></a>
			<a href="#why"><?php esc_html_e( 'Почему мы', 'maclab' ); ?></a>
			<a href="#acc"><?php esc_html_e( 'Аксессуары', 'maclab' ); ?></a>
			<a href="#faq"><?php esc_html_e( 'Вопросы', 'maclab' ); ?></a>
		</nav>
		<div class="acts">
			<a href="<?php echo esc_url( maclab_phone_href() ); ?>" class="icb" aria-label="<?php esc_attr_e( 'Позвонить', 'maclab' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
			</a>
			<button class="icb" id="cartBtn" aria-label="<?php esc_attr_e( 'Корзина', 'maclab' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
				<span class="dot" id="cartDot">0</span>
			</button>
			<button class="icb burger" id="burger" aria-label="<?php esc_attr_e( 'Меню', 'maclab' ); ?>"><i></i><i></i></button>
		</div>
	</div>
</header>

<div class="menu" id="menu">
	<div class="wrap in-w">
		<nav class="m-links" id="mLinks">
			<a href="#catalog" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/p-pro14.jpg' ); ?>"><s>01</s><?php esc_html_e( 'Каталог', 'maclab' ); ?></a>
			<a href="#config" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/p-air15.jpg' ); ?>"><s>02</s><?php esc_html_e( 'Конфигуратор', 'maclab' ); ?></a>
			<a href="#why" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/life-1.jpg' ); ?>"><s>03</s><?php esc_html_e( 'Почему мы', 'maclab' ); ?></a>
			<a href="#acc" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-airpods-max.jpg' ); ?>"><s>04</s><?php esc_html_e( 'Аксессуары', 'maclab' ); ?></a>
			<a href="#faq" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-imac.jpg' ); ?>"><s>05</s><?php esc_html_e( 'Вопросы', 'maclab' ); ?></a>
		</nav>
		<div class="m-media" id="mMedia">
			<img src="<?php echo esc_url( MACLAB_URI . '/assets/img/p-pro14.jpg' ); ?>" alt="" class="on">
			<img src="<?php echo esc_url( MACLAB_URI . '/assets/img/p-air15.jpg' ); ?>" alt="">
			<img src="<?php echo esc_url( MACLAB_URI . '/assets/img/life-1.jpg' ); ?>" alt="">
			<img src="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-airpods-max.jpg' ); ?>" alt="">
			<img src="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-imac.jpg' ); ?>" alt="">
		</div>
	</div>
	<div class="m-foot">
		<span><?php echo esc_html( maclab_option( 'address' ) . ' · ' . maclab_option( 'hours' ) ); ?></span>
		<span><?php echo esc_html( maclab_option( 'phone' ) . ' · ' . maclab_option( 'email' ) ); ?></span>
	</div>
</div>

<main>
