<?php
/**
 * Настройки темы: контакты и уведомления.
 *
 * @package maclab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Значения по умолчанию.
 *
 * @return array
 */
function maclab_defaults() {
	return array(
		'phone'       => '+7 495 123-45-67',
		'email'       => 'hello@maclab.ru',
		'address'     => 'Москва, Пресненская наб. 12',
		'hours'       => 'Ежедневно 10:00–21:00',
		'manager'     => 'Артём Веснин',
		'notify_mail' => get_option( 'admin_email' ),
		'tg_token'    => '',
		'tg_chat'     => '',
	);
}

/**
 * Значение настройки.
 *
 * @param string $key Ключ.
 * @return string
 */
function maclab_option( $key ) {
	$saved    = get_option( 'maclab_settings', array() );
	$defaults = maclab_defaults();
	if ( isset( $saved[ $key ] ) && '' !== $saved[ $key ] ) {
		return $saved[ $key ];
	}
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Телефон в виде, пригодном для tel:.
 *
 * @return string
 */
function maclab_phone_href() {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', maclab_option( 'phone' ) );
}

/**
 * Пункт меню в админке.
 */
function maclab_settings_menu() {
	add_menu_page(
		__( 'mac:lab', 'maclab' ),
		__( 'mac:lab', 'maclab' ),
		'manage_options',
		'maclab',
		'maclab_settings_page',
		'dashicons-laptop',
		3
	);
	add_submenu_page( 'maclab', __( 'Настройки', 'maclab' ), __( 'Настройки', 'maclab' ), 'manage_options', 'maclab', 'maclab_settings_page' );
}
add_action( 'admin_menu', 'maclab_settings_menu' );

/**
 * Регистрация настроек.
 */
function maclab_register_settings() {
	register_setting(
		'maclab_settings_group',
		'maclab_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'maclab_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'maclab_register_settings' );

/**
 * Очистка сохраняемых настроек.
 *
 * @param array $input Входные данные.
 * @return array
 */
function maclab_sanitize_settings( $input ) {
	$out = array();
	foreach ( maclab_defaults() as $key => $default ) {
		$value = isset( $input[ $key ] ) ? $input[ $key ] : '';
		if ( 'notify_mail' === $key ) {
			$out[ $key ] = sanitize_email( $value );
		} else {
			$out[ $key ] = sanitize_text_field( $value );
		}
	}
	return $out;
}

/**
 * Страница настроек.
 */
function maclab_settings_page() {
	$fields = array(
		'phone'       => __( 'Телефон', 'maclab' ),
		'email'       => __( 'E-mail для клиентов', 'maclab' ),
		'address'     => __( 'Адрес', 'maclab' ),
		'hours'       => __( 'Часы работы', 'maclab' ),
		'manager'     => __( 'Имя менеджера в форме', 'maclab' ),
		'notify_mail' => __( 'Куда слать заявки', 'maclab' ),
		'tg_token'    => __( 'Telegram: токен бота', 'maclab' ),
		'tg_chat'     => __( 'Telegram: chat id', 'maclab' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Настройки mac:lab', 'maclab' ); ?></h1>
		<p><?php esc_html_e( 'Контакты подставляются в шапку, футер и форму заявки. Telegram можно не заполнять — тогда заявки приходят только на почту и в раздел «Заявки».', 'maclab' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'maclab_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( $fields as $key => $label ) : ?>
					<tr>
						<th scope="row"><label for="maclab-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input
								type="text"
								class="regular-text"
								id="maclab-<?php echo esc_attr( $key ); ?>"
								name="maclab_settings[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( maclab_option( $key ) ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
