<?php
/**
 * Заявки и заказы: хранение, приём через REST, уведомления.
 *
 * @package maclab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Типы записей для заявок и заказов.
 */
function maclab_register_leads() {
	register_post_type(
		'ml_lead',
		array(
			'labels'       => array(
				'name'          => __( 'Заявки', 'maclab' ),
				'singular_name' => __( 'Заявка', 'maclab' ),
				'menu_name'     => __( 'Заявки', 'maclab' ),
				'not_found'     => __( 'Заявок пока нет', 'maclab' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'maclab',
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);

	register_post_type(
		'ml_order',
		array(
			'labels'       => array(
				'name'          => __( 'Заказы', 'maclab' ),
				'singular_name' => __( 'Заказ', 'maclab' ),
				'menu_name'     => __( 'Заказы', 'maclab' ),
				'not_found'     => __( 'Заказов пока нет', 'maclab' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'maclab',
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);
}
add_action( 'init', 'maclab_register_leads' );

/**
 * Регистрация REST-маршрутов.
 */
function maclab_rest_routes() {
	register_rest_route(
		'maclab/v1',
		'/lead',
		array(
			'methods'             => 'POST',
			'callback'            => 'maclab_handle_lead',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'maclab/v1',
		'/order',
		array(
			'methods'             => 'POST',
			'callback'            => 'maclab_handle_order',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'maclab_rest_routes' );

/**
 * IP отправителя (для ограничения частоты).
 *
 * @return string
 */
function maclab_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ? $ip : 'unknown';
}

/**
 * Простая защита от спама: ловушка, скорость и частота отправок.
 *
 * @param WP_REST_Request $request Запрос.
 * @return true|WP_Error
 */
function maclab_guard( $request ) {
	if ( $request->get_param( 'hp' ) ) {
		return new WP_Error( 'maclab_spam', __( 'Заявка не принята.', 'maclab' ), array( 'status' => 400 ) );
	}

	$elapsed = (int) $request->get_param( 'elapsed' );
	if ( $elapsed > 0 && $elapsed < 1500 ) {
		return new WP_Error( 'maclab_fast', __( 'Слишком быстрая отправка, попробуйте ещё раз.', 'maclab' ), array( 'status' => 400 ) );
	}

	$key   = 'maclab_rl_' . md5( maclab_ip() );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return new WP_Error( 'maclab_limit', __( 'Слишком много заявок подряд. Позвоните нам — ответим сразу.', 'maclab' ), array( 'status' => 429 ) );
	}
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	return true;
}

/**
 * Проверка телефона: 11 цифр.
 *
 * @param string $phone Телефон.
 * @return bool
 */
function maclab_valid_phone( $phone ) {
	return strlen( preg_replace( '/\D/', '', $phone ) ) >= 11;
}

/**
 * Приём заявки из формы консультации.
 *
 * @param WP_REST_Request $request Запрос.
 * @return WP_REST_Response|WP_Error
 */
function maclab_handle_lead( $request ) {
	$guard = maclab_guard( $request );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}

	$name  = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$phone = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$what  = sanitize_text_field( (string) $request->get_param( 'what' ) );
	$way   = sanitize_text_field( (string) $request->get_param( 'way' ) );
	$page  = esc_url_raw( (string) $request->get_param( 'page' ) );

	if ( mb_strlen( $name ) < 2 ) {
		return new WP_Error( 'maclab_name', __( 'Напишите, как к вам обращаться.', 'maclab' ), array( 'status' => 422 ) );
	}
	if ( ! maclab_valid_phone( $phone ) ) {
		return new WP_Error( 'maclab_phone', __( 'Проверьте номер телефона.', 'maclab' ), array( 'status' => 422 ) );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'ml_lead',
			'post_status' => 'publish',
			/* translators: 1: имя клиента, 2: телефон */
			'post_title'  => sprintf( __( '%1$s — %2$s', 'maclab' ), $name, $phone ),
		)
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return new WP_Error( 'maclab_save', __( 'Не удалось сохранить заявку.', 'maclab' ), array( 'status' => 500 ) );
	}

	update_post_meta( $post_id, '_ml_name', $name );
	update_post_meta( $post_id, '_ml_phone', $phone );
	update_post_meta( $post_id, '_ml_what', $what );
	update_post_meta( $post_id, '_ml_way', $way );
	update_post_meta( $post_id, '_ml_page', $page );
	update_post_meta( $post_id, '_ml_ip', maclab_ip() );

	$lines = array(
		__( 'Новая заявка на подбор', 'maclab' ),
		__( 'Имя: ', 'maclab' ) . $name,
		__( 'Телефон: ', 'maclab' ) . $phone,
		__( 'Что нужно: ', 'maclab' ) . $what,
		__( 'Связь: ', 'maclab' ) . $way,
		__( 'Страница: ', 'maclab' ) . $page,
	);
	maclab_notify( __( 'Заявка с сайта', 'maclab' ), $lines );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => __( 'Заявка принята', 'maclab' ),
		),
		200
	);
}

/**
 * Приём заказа из корзины.
 *
 * @param WP_REST_Request $request Запрос.
 * @return WP_REST_Response|WP_Error
 */
function maclab_handle_order( $request ) {
	$guard = maclab_guard( $request );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}

	$name  = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$phone = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$items = $request->get_param( 'items' );

	if ( mb_strlen( $name ) < 2 ) {
		return new WP_Error( 'maclab_name', __( 'Напишите, как к вам обращаться.', 'maclab' ), array( 'status' => 422 ) );
	}
	if ( ! maclab_valid_phone( $phone ) ) {
		return new WP_Error( 'maclab_phone', __( 'Проверьте номер телефона.', 'maclab' ), array( 'status' => 422 ) );
	}
	if ( ! is_array( $items ) || ! count( $items ) ) {
		return new WP_Error( 'maclab_empty', __( 'Корзина пуста.', 'maclab' ), array( 'status' => 422 ) );
	}

	$clean = array();
	$total = 0;
	foreach ( array_slice( $items, 0, 30 ) as $item ) {
		$title = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : '';
		$price = isset( $item['price'] ) ? absint( $item['price'] ) : 0;
		if ( '' === $title ) {
			continue;
		}
		$clean[] = array(
			'name'  => $title,
			'price' => $price,
		);
		$total  += $price;
	}
	if ( ! count( $clean ) ) {
		return new WP_Error( 'maclab_empty', __( 'Корзина пуста.', 'maclab' ), array( 'status' => 422 ) );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'ml_order',
			'post_status' => 'publish',
			/* translators: 1: имя клиента, 2: сумма заказа */
			'post_title'  => sprintf( __( '%1$s — %2$s', 'maclab' ), $name, maclab_price( $total ) ),
		)
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return new WP_Error( 'maclab_save', __( 'Не удалось сохранить заказ.', 'maclab' ), array( 'status' => 500 ) );
	}

	update_post_meta( $post_id, '_ml_name', $name );
	update_post_meta( $post_id, '_ml_phone', $phone );
	update_post_meta( $post_id, '_ml_total', $total );
	update_post_meta( $post_id, '_ml_items', wp_json_encode( $clean ) );
	update_post_meta( $post_id, '_ml_ip', maclab_ip() );

	$lines = array( __( 'Новый заказ', 'maclab' ), __( 'Имя: ', 'maclab' ) . $name, __( 'Телефон: ', 'maclab' ) . $phone );
	foreach ( $clean as $item ) {
		$lines[] = '• ' . $item['name'] . ' — ' . maclab_price( $item['price'] );
	}
	$lines[] = __( 'Итого: ', 'maclab' ) . maclab_price( $total );
	maclab_notify( __( 'Заказ с сайта', 'maclab' ), $lines );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => __( 'Заказ принят', 'maclab' ),
			'total'   => $total,
		),
		200
	);
}

/**
 * Отправка уведомлений: почта и, если настроен, Telegram.
 *
 * @param string $subject Тема.
 * @param array  $lines   Строки письма.
 */
function maclab_notify( $subject, $lines ) {
	$body = implode( "\n", array_map( 'wp_strip_all_tags', $lines ) );

	$to = maclab_option( 'notify_mail' );
	if ( $to ) {
		wp_mail( $to, $subject, $body );
	}

	$token = maclab_option( 'tg_token' );
	$chat  = maclab_option( 'tg_chat' );
	if ( $token && $chat ) {
		wp_remote_post(
			'https://api.telegram.org/bot' . rawurlencode( $token ) . '/sendMessage',
			array(
				'timeout'  => 8,
				'blocking' => false,
				'body'     => array(
					'chat_id' => $chat,
					'text'    => $subject . "\n\n" . $body,
				),
			)
		);
	}
}

/**
 * Колонки в списках заявок и заказов.
 *
 * @param array $cols Колонки.
 * @return array
 */
function maclab_lead_columns( $cols ) {
	return array(
		'cb'       => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'    => __( 'Клиент', 'maclab' ),
		'ml_phone' => __( 'Телефон', 'maclab' ),
		'ml_what'  => __( 'Запрос', 'maclab' ),
		'ml_way'   => __( 'Связь', 'maclab' ),
		'date'     => __( 'Когда', 'maclab' ),
	);
}
add_filter( 'manage_ml_lead_posts_columns', 'maclab_lead_columns' );

/**
 * Колонки заказов.
 *
 * @param array $cols Колонки.
 * @return array
 */
function maclab_order_columns( $cols ) {
	return array(
		'cb'       => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'    => __( 'Клиент', 'maclab' ),
		'ml_phone' => __( 'Телефон', 'maclab' ),
		'ml_items' => __( 'Состав', 'maclab' ),
		'ml_total' => __( 'Сумма', 'maclab' ),
		'date'     => __( 'Когда', 'maclab' ),
	);
}
add_filter( 'manage_ml_order_posts_columns', 'maclab_order_columns' );

/**
 * Значения колонок.
 *
 * @param string $col     Колонка.
 * @param int    $post_id ID записи.
 */
function maclab_lead_column_value( $col, $post_id ) {
	switch ( $col ) {
		case 'ml_phone':
			$phone = get_post_meta( $post_id, '_ml_phone', true );
			printf( '<a href="tel:%1$s">%2$s</a>', esc_attr( preg_replace( '/\D/', '', $phone ) ), esc_html( $phone ) );
			break;
		case 'ml_what':
			echo esc_html( get_post_meta( $post_id, '_ml_what', true ) );
			break;
		case 'ml_way':
			echo esc_html( get_post_meta( $post_id, '_ml_way', true ) );
			break;
		case 'ml_total':
			echo esc_html( maclab_price( get_post_meta( $post_id, '_ml_total', true ) ) );
			break;
		case 'ml_items':
			$items = json_decode( (string) get_post_meta( $post_id, '_ml_items', true ), true );
			if ( is_array( $items ) ) {
				$names = wp_list_pluck( $items, 'name' );
				echo esc_html( implode( ', ', $names ) );
			}
			break;
	}
}
add_action( 'manage_ml_lead_posts_custom_column', 'maclab_lead_column_value', 10, 2 );
add_action( 'manage_ml_order_posts_custom_column', 'maclab_lead_column_value', 10, 2 );
