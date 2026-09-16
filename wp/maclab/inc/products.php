<?php
/**
 * Товары каталога: тип записи, поля и первичное наполнение.
 *
 * @package maclab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Категории каталога (совпадают с вкладками фильтра на фронте).
 *
 * @return array
 */
function maclab_cats() {
	return array(
		'air' => __( 'MacBook Air', 'maclab' ),
		'pro' => __( 'MacBook Pro', 'maclab' ),
		'ref' => __( 'Восстановленные', 'maclab' ),
		'acc' => __( 'Аксессуары', 'maclab' ),
	);
}

/**
 * Регистрация типа записи «Товар».
 */
function maclab_register_product() {
	register_post_type(
		'ml_product',
		array(
			'labels'        => array(
				'name'               => __( 'Товары', 'maclab' ),
				'singular_name'      => __( 'Товар', 'maclab' ),
				'add_new'            => __( 'Добавить товар', 'maclab' ),
				'add_new_item'       => __( 'Новый товар', 'maclab' ),
				'edit_item'          => __( 'Редактировать товар', 'maclab' ),
				'search_items'       => __( 'Искать товары', 'maclab' ),
				'not_found'          => __( 'Товаров пока нет', 'maclab' ),
				'menu_name'          => __( 'Товары', 'maclab' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'maclab',
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			'menu_icon'     => 'dashicons-laptop',
			'has_archive'   => false,
			'rewrite'       => false,
		)
	);
}
add_action( 'init', 'maclab_register_product' );

/**
 * Метабокс с полями товара.
 */
function maclab_product_metabox() {
	add_meta_box( 'maclab_product', __( 'Карточка товара', 'maclab' ), 'maclab_product_fields', 'ml_product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'maclab_product_metabox' );

/**
 * Разметка полей товара.
 *
 * @param WP_Post $post Запись.
 */
function maclab_product_fields( $post ) {
	wp_nonce_field( 'maclab_product_save', 'maclab_product_nonce' );
	$price  = get_post_meta( $post->ID, '_ml_price', true );
	$cat    = get_post_meta( $post->ID, '_ml_cat', true );
	$badge  = get_post_meta( $post->ID, '_ml_badge', true );
	$bstyle = get_post_meta( $post->ID, '_ml_badge_style', true );
	$specs  = get_post_meta( $post->ID, '_ml_specs', true );
	$img    = get_post_meta( $post->ID, '_ml_img', true );
	?>
	<style>
		.ml-row{display:flex;gap:18px;flex-wrap:wrap;margin-bottom:14px}
		.ml-row label{display:block;font-weight:600;margin-bottom:4px}
		.ml-row p{margin:4px 0 0;color:#666;font-size:12px}
	</style>
	<div class="ml-row">
		<div>
			<label for="ml_price"><?php esc_html_e( 'Цена, ₽', 'maclab' ); ?></label>
			<input type="number" id="ml_price" name="ml_price" value="<?php echo esc_attr( $price ); ?>" min="0" step="10">
		</div>
		<div>
			<label for="ml_cat"><?php esc_html_e( 'Категория', 'maclab' ); ?></label>
			<select id="ml_cat" name="ml_cat">
				<?php foreach ( maclab_cats() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $cat, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="ml_badge"><?php esc_html_e( 'Плашка', 'maclab' ); ?></label>
			<input type="text" id="ml_badge" name="ml_badge" value="<?php echo esc_attr( $badge ); ?>" placeholder="Хит, Новинка…">
		</div>
		<div>
			<label for="ml_badge_style"><?php esc_html_e( 'Цвет плашки', 'maclab' ); ?></label>
			<select id="ml_badge_style" name="ml_badge_style">
				<option value="red" <?php selected( $bstyle, 'red' ); ?>><?php esc_html_e( 'Красная', 'maclab' ); ?></option>
				<option value="gray" <?php selected( $bstyle, 'gray' ); ?>><?php esc_html_e( 'Тёмная', 'maclab' ); ?></option>
			</select>
		</div>
	</div>
	<div class="ml-row">
		<div style="flex:1;min-width:320px">
			<label for="ml_specs"><?php esc_html_e( 'Характеристики', 'maclab' ); ?></label>
			<input type="text" class="widefat" id="ml_specs" name="ml_specs" value="<?php echo esc_attr( $specs ); ?>" placeholder="M4 10-core, 16 ГБ, 256 ГБ SSD, Midnight">
			<p><?php esc_html_e( 'Через запятую, показываем первые четыре.', 'maclab' ); ?></p>
		</div>
	</div>
	<div class="ml-row">
		<div style="flex:1;min-width:320px">
			<label for="ml_img"><?php esc_html_e( 'Файл изображения в теме', 'maclab' ); ?></label>
			<input type="text" class="widefat" id="ml_img" name="ml_img" value="<?php echo esc_attr( $img ); ?>" placeholder="assets/img/p-air13.jpg">
			<p><?php esc_html_e( 'Используется, если не задано изображение записи. Загруженная картинка всегда в приоритете.', 'maclab' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Сохранение полей товара.
 *
 * @param int $post_id ID записи.
 */
function maclab_product_save( $post_id ) {
	if ( ! isset( $_POST['maclab_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['maclab_product_nonce'] ) ), 'maclab_product_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$map = array(
		'_ml_price'       => isset( $_POST['ml_price'] ) ? absint( $_POST['ml_price'] ) : 0,
		'_ml_cat'         => isset( $_POST['ml_cat'] ) ? sanitize_key( wp_unslash( $_POST['ml_cat'] ) ) : 'air',
		'_ml_badge'       => isset( $_POST['ml_badge'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_badge'] ) ) : '',
		'_ml_badge_style' => isset( $_POST['ml_badge_style'] ) ? sanitize_key( wp_unslash( $_POST['ml_badge_style'] ) ) : 'red',
		'_ml_specs'       => isset( $_POST['ml_specs'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_specs'] ) ) : '',
		'_ml_img'         => isset( $_POST['ml_img'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_img'] ) ) : '',
	);
	foreach ( $map as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post_ml_product', 'maclab_product_save' );

/**
 * Колонки в списке товаров.
 *
 * @param array $cols Колонки.
 * @return array
 */
function maclab_product_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['ml_price'] = __( 'Цена', 'maclab' );
			$new['ml_cat']   = __( 'Категория', 'maclab' );
		}
	}
	return $new;
}
add_filter( 'manage_ml_product_posts_columns', 'maclab_product_columns' );

/**
 * Вывод значений в колонках.
 *
 * @param string $col     Колонка.
 * @param int    $post_id ID записи.
 */
function maclab_product_column_value( $col, $post_id ) {
	if ( 'ml_price' === $col ) {
		echo esc_html( maclab_price( get_post_meta( $post_id, '_ml_price', true ) ) );
	}
	if ( 'ml_cat' === $col ) {
		$cats = maclab_cats();
		$cat  = get_post_meta( $post_id, '_ml_cat', true );
		echo esc_html( isset( $cats[ $cat ] ) ? $cats[ $cat ] : $cat );
	}
}
add_action( 'manage_ml_product_posts_custom_column', 'maclab_product_column_value', 10, 2 );

/**
 * Картинка товара: сначала изображение записи, потом файл из темы.
 *
 * @param int $post_id ID записи.
 * @return string
 */
function maclab_product_image( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( $url ) {
			return $url;
		}
	}
	$rel = get_post_meta( $post_id, '_ml_img', true );
	if ( $rel ) {
		return MACLAB_URI . '/' . ltrim( $rel, '/' );
	}
	return MACLAB_URI . '/assets/img/p-air13.jpg';
}

/**
 * Стартовый каталог — создаётся один раз при активации темы.
 *
 * @return array
 */
function maclab_seed_products() {
	return array(
		array( 'MacBook Air 13" M4', 109990, 'air', 'Хит', 'red', 'M4 10-core, 16 ГБ, 256 ГБ SSD, Midnight', 'assets/img/p-air13.jpg' ),
		array( 'MacBook Air 15" M4', 139990, 'air', '', 'red', 'M4 10-core, 16 ГБ, 512 ГБ SSD, Starlight', 'assets/img/p-air15.jpg' ),
		array( 'MacBook Pro 14" M4 Pro', 199990, 'pro', 'Новинка', 'gray', 'M4 Pro 12-core, 24 ГБ, 512 ГБ SSD, Space Black', 'assets/img/p-pro14.jpg' ),
		array( 'MacBook Pro 16" M4 Max', 329990, 'pro', 'Топ', 'red', 'M4 Max 16-core, 48 ГБ, 1 ТБ SSD, Space Black', 'assets/img/p-pro16.jpg' ),
		array( 'MacBook Pro 14" M3', 149990, 'ref', 'Refurbished', 'gray', 'M3 8-core, 16 ГБ, 512 ГБ SSD, Цикл АКБ < 50', 'assets/img/p-pro13.jpg' ),
		array( 'MacBook Air 13" M2', 84990, 'ref', 'Refurbished', 'gray', 'M2 8-core, 8 ГБ, 256 ГБ SSD, Silver', 'assets/img/p-air13m2.jpg' ),
		array( 'AirPods Pro 2', 24990, 'acc', 'Хит', 'red', 'Шумоподавление, USB-C, 6 ч без кейса, Адаптивный звук', 'assets/img/acc-airpods-pro.jpg' ),
		array( 'AirPods Max', 64990, 'acc', '', 'red', 'Наушные, Spatial Audio, 20 ч работы, 5 цветов', 'assets/img/acc-airpods-max.jpg' ),
		array( 'Apple Watch Series 10', 39990, 'acc', 'Новинка', 'gray', '46 мм, LTPO OLED, ЭКГ и SpO₂, Алюминий', 'assets/img/acc-watch2.jpg' ),
		array( 'Apple Watch Ultra 2', 89990, 'acc', '', 'red', '49 мм, Титан, 36 ч работы, 100 м WR', 'assets/img/acc-watch.jpg' ),
		array( 'AirPods 4', 19990, 'acc', '', 'red', 'Открытые, ANC, 30 ч с кейсом, USB-C', 'assets/img/acc-airpods.jpg' ),
		array( 'Magic Keyboard', 12990, 'acc', '', 'red', 'Touch ID, Bluetooth, Русская раскладка, 1 мес работы', 'assets/img/acc-keyboard.jpg' ),
	);
}

/**
 * Наполнение каталога при первой активации темы.
 */
function maclab_install_products() {
	if ( get_option( 'maclab_seeded' ) ) {
		return;
	}

	$order = 0;
	foreach ( maclab_seed_products() as $row ) {
		list( $title, $price, $cat, $badge, $bstyle, $specs, $img ) = $row;

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ml_product',
				'post_title'  => $title,
				'post_status' => 'publish',
				'menu_order'  => $order,
			)
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}
		update_post_meta( $post_id, '_ml_price', $price );
		update_post_meta( $post_id, '_ml_cat', $cat );
		update_post_meta( $post_id, '_ml_badge', $badge );
		update_post_meta( $post_id, '_ml_badge_style', $bstyle );
		update_post_meta( $post_id, '_ml_specs', $specs );
		update_post_meta( $post_id, '_ml_img', $img );
		$order += 10;
	}

	update_option( 'maclab_seeded', 1 );
}
add_action( 'after_switch_theme', 'maclab_install_products' );
