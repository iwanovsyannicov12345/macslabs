<?php
/**
 * Подвал, корзина и служебные скрипты.
 *
 * @package maclab
 */

?>
</main>

<footer class="wrap ftr">
	<div class="ftr-top">
		<div>
			<div class="logo" style="font-size:22px">mac<em>:</em>lab</div>
			<p class="lead" style="margin-top:12px;font-size:14px"><?php esc_html_e( 'Техника Apple с проверкой при получении', 'maclab' ); ?></p>
			<a href="mailto:<?php echo esc_attr( maclab_option( 'email' ) ); ?>" class="mail"><?php echo esc_html( maclab_option( 'email' ) ); ?></a>
		</div>
		<div>
			<h5><?php esc_html_e( 'Компания', 'maclab' ); ?></h5>
			<ul>
				<li><a href="#why"><?php esc_html_e( 'О нас', 'maclab' ); ?></a></li>
				<li><a href="#why"><?php esc_html_e( 'Как мы работаем', 'maclab' ); ?></a></li>
				<li><a href="#config"><?php esc_html_e( 'Конфигуратор', 'maclab' ); ?></a></li>
				<li><a href="#faq"><?php esc_html_e( 'Спецусловия', 'maclab' ); ?></a></li>
				<li><a href="#cta"><?php esc_html_e( 'Контакты', 'maclab' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h5><?php esc_html_e( 'Продукция', 'maclab' ); ?></h5>
			<ul>
				<li><a href="#catalog" data-tab="air"><?php esc_html_e( 'MacBook Air', 'maclab' ); ?></a></li>
				<li><a href="#catalog" data-tab="pro"><?php esc_html_e( 'MacBook Pro', 'maclab' ); ?></a></li>
				<li><a href="#catalog" data-tab="ref"><?php esc_html_e( 'Восстановленные', 'maclab' ); ?></a></li>
				<li><a href="#catalog" data-tab="acc"><?php esc_html_e( 'AirPods и Apple Watch', 'maclab' ); ?></a></li>
				<li><a href="#acc"><?php esc_html_e( 'Аксессуары', 'maclab' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h5><?php esc_html_e( 'Контакты', 'maclab' ); ?></h5>
			<ul>
				<li><a href="<?php echo esc_url( maclab_phone_href() ); ?>"><?php echo esc_html( maclab_option( 'phone' ) ); ?></a></li>
				<li><a href="mailto:<?php echo esc_attr( maclab_option( 'email' ) ); ?>"><?php echo esc_html( maclab_option( 'email' ) ); ?></a></li>
				<li><span><?php echo esc_html( maclab_option( 'address' ) ); ?></span></li>
				<li><span><?php echo esc_html( maclab_option( 'hours' ) ); ?></span></li>
			</ul>
		</div>
	</div>
	<div class="ftr-bot">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> mac:lab. <?php esc_html_e( 'Все права защищены', 'maclab' ); ?></span>
		<div class="socs"><a href="#">Telegram</a> · <a href="#">VK</a> · <a href="#">YouTube</a></div>
		<a href="#"><?php esc_html_e( 'Политика конфиденциальности', 'maclab' ); ?></a>
	</div>
</footer>

<div class="scrim" id="scrim"></div>
<aside class="cart" id="cart" aria-label="<?php esc_attr_e( 'Корзина', 'maclab' ); ?>">
	<div class="cart-h">
		<b style="font-family:var(--f-disp);font-size:17px;letter-spacing:-.03em"><?php esc_html_e( 'Корзина', 'maclab' ); ?></b>
		<button class="icb" id="cartClose" aria-label="<?php esc_attr_e( 'Закрыть', 'maclab' ); ?>">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
	</div>
	<div class="cart-b" id="cartBody"><p class="cart-empty"><?php esc_html_e( 'Пока пусто — добавьте технику из каталога', 'maclab' ); ?></p></div>
	<div class="cart-f">
		<div style="display:flex;justify-content:space-between;align-items:baseline">
			<span style="color:var(--txt-dim);font-size:14px"><?php esc_html_e( 'Итого', 'maclab' ); ?></span>
			<span class="price" id="cartTotal">0 ₽</span>
		</div>

		<form class="cart-form" id="orderForm" novalidate>
			<div class="fld">
				<label for="oName"><?php esc_html_e( 'Ваше имя', 'maclab' ); ?></label>
				<input id="oName" name="name" type="text" placeholder="<?php esc_attr_e( 'Иван', 'maclab' ); ?>" autocomplete="name" required>
			</div>
			<div class="fld">
				<label for="oPhone"><?php esc_html_e( 'Телефон', 'maclab' ); ?></label>
				<input id="oPhone" name="phone" type="tel" placeholder="+7 (999) 123-45-67" autocomplete="tel" required>
			</div>
			<input type="text" name="hp" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<button class="btn" style="justify-content:center" type="submit"><span><?php esc_html_e( 'Оформить заказ', 'maclab' ); ?></span></button>
			<p class="f-msg" id="orderMsg" role="status"></p>
		</form>
	</div>
</aside>

<?php wp_footer(); ?>
</body>
</html>
