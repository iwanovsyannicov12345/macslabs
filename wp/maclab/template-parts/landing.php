<?php
/**
 * Содержимое лендинга.
 *
 * @package maclab
 */

?>

<!-- HERO -->
<section class="hero">
  <div class="aura"></div><div class="aura b"></div>
  <canvas class="mesh" id="mesh" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="hero-grid">
      <div class="hero-main" data-parallax>
        <div class="bgimg"><img src="<?php echo esc_url( MACLAB_URI . '/assets/img/hero-main.jpg' ); ?>" alt="MacBook Pro" fetchpriority="high"></div>
        <div class="hero-tags">
          <span class="chip">M4 · M4 Pro · M4 Max</span>
          <span class="chip">Гарантия 12 месяцев</span>
          <span class="chip">Рассрочка 0%</span>
        </div>
        <h1 class="h-xl split" data-rv>Начни новый день с нового MacBook</h1>
        <p class="lead" data-rv>Привозим, распаковываем вместе с вами и настраиваем прямо при получении. Не подошёл — забираем обратно в тот же день.</p>
        <div class="hero-cta" data-rv>
          <a href="#catalog" class="btn"><span>Смотреть каталог</span><span class="ar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>
          <a href="#config" class="btn btn--ghost"><span>Собрать свою конфигурацию</span></a>
        </div>
      </div>
      <div class="hero-side">
        <a href="#catalog"><img src="<?php echo esc_url( MACLAB_URI . '/assets/img/hero-1.jpg' ); ?>" alt="MacBook Pro"><div><b>MacBook Pro</b><s>от 199 990 ₽</s></div></a>
        <a href="#acc"><img src="<?php echo esc_url( MACLAB_URI . '/assets/img/hero-2.jpg' ); ?>" alt="AirPods"><div><b>AirPods</b><s>от 14 990 ₽</s></div></a>
      </div>
    </div>
    <div class="hero-strip" data-rv>
      <div><b data-count="36">0</b><s>сотрудников</s></div>
      <div><b data-count="2900" data-suffix="+">0</b><s>довольных клиентов</s></div>
      <div><b data-count="7">0</b><s>лет на рынке</s></div>
      <div><b data-count="3">0</b><s>дня доставка по РФ</s></div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="mq">
  <div class="mq-t" id="mqTrack">
    <span>MacBook Air M4</span><span>MacBook Pro M4 Max</span><span>Trade-in до 120 000 ₽</span>
    <span>Рассрочка 0% на 12 месяцев</span><span>Проверка при получении</span><span>Официальная гарантия</span>
    <span>Доставка за 1–3 дня</span><span>Настройка в подарок</span>
  </div>
</div>

<!-- CATALOG -->
<section class="sec" id="catalog">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <span class="eyebrow" data-rv><?php esc_html_e( 'Каталог', 'maclab' ); ?></span>
        <h2 class="h-lg split" data-rv style="margin-top:16px"><?php esc_html_e( 'Ноутбуки в наличии', 'maclab' ); ?><br><?php esc_html_e( 'в Москве', 'maclab' ); ?></h2>
      </div>
      <div class="tabs" data-rv>
        <button class="tab on" data-filter="all"><?php esc_html_e( 'Все', 'maclab' ); ?></button>
        <?php foreach ( maclab_cats() as $cat_key => $cat_label ) : ?>
          <button class="tab" data-filter="<?php echo esc_attr( $cat_key ); ?>"><?php echo esc_html( $cat_label ); ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid-p" id="grid">
      <?php
      $maclab_products = new WP_Query(
        array(
          'post_type'      => 'ml_product',
          'posts_per_page' => 60,
          'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
        )
      );
      if ( $maclab_products->have_posts() ) :
        while ( $maclab_products->have_posts() ) :
          $maclab_products->the_post();
          $ml_id    = get_the_ID();
          $ml_price = (int) get_post_meta( $ml_id, '_ml_price', true );
          $ml_cat   = get_post_meta( $ml_id, '_ml_cat', true );
          $ml_badge = get_post_meta( $ml_id, '_ml_badge', true );
          $ml_bst   = get_post_meta( $ml_id, '_ml_badge_style', true );
          $ml_specs = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $ml_id, '_ml_specs', true ) ) ) );
          $ml_img   = maclab_product_image( $ml_id );
          $ml_month = $ml_price ? (int) round( $ml_price / 12 ) : 0;
          ?>
          <article class="card prod" data-cat="<?php echo esc_attr( $ml_cat ); ?>" data-tilt data-rv
            data-name="<?php echo esc_attr( get_the_title() ); ?>"
            data-price="<?php echo esc_attr( $ml_price ); ?>"
            data-img="<?php echo esc_url( $ml_img ); ?>">
            <div class="prod-img">
              <?php if ( $ml_badge ) : ?>
                <span class="prod-badge<?php echo 'gray' === $ml_bst ? ' gray' : ''; ?>"><?php echo esc_html( $ml_badge ); ?></span>
              <?php endif; ?>
              <button class="prod-fav" aria-label="<?php esc_attr_e( 'В избранное', 'maclab' ); ?>"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l8.8 8.8 8.8-8.8a5.5 5.5 0 0 0 0-7.8z"/></svg></button>
              <img src="<?php echo esc_url( $ml_img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
            </div>
            <div class="prod-b">
              <h3><?php the_title(); ?></h3>
              <div class="prod-spec">
                <?php foreach ( array_slice( $ml_specs, 0, 4 ) as $ml_spec ) : ?>
                  <s><?php echo esc_html( $ml_spec ); ?></s>
                <?php endforeach; ?>
              </div>
              <div class="prod-foot">
                <div class="price"><?php echo esc_html( maclab_price( $ml_price ) ); ?><s><?php echo esc_html( sprintf( __( 'или %s × 12', 'maclab' ), maclab_price( $ml_month ) ) ); ?></s></div>
                <button class="add" aria-label="<?php esc_attr_e( 'В корзину', 'maclab' ); ?>"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></button>
              </div>
            </div>
          </article>
          <?php
        endwhile;
        wp_reset_postdata();
      else :
        ?>
        <p class="lead"><?php esc_html_e( 'Товары ещё не добавлены — зайдите в «mac:lab → Товары» в админке.', 'maclab' ); ?></p>
      <?php endif; ?>
    </div>
    <div class="more-wrap">
      <button class="btn btn--ghost" id="moreBtn"><span><?php esc_html_e( 'Показать ещё', 'maclab' ); ?></span><span class="ar">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M6 13l6 6 6-6"/></svg></span></button>
    </div>
  </div>
</section>

<!-- CONFIGURATOR -->
<section class="sec" id="config" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <span class="eyebrow" data-rv>Конфигуратор</span>
        <h2 class="h-lg split" data-rv style="margin-top:16px">Соберите свой MacBook</h2>
      </div>
      <p class="lead" data-rv>Цена пересчитывается сразу. Нужной сборки нет в наличии — привезём под заказ за 7–10 дней.</p>
    </div>

    <div class="cfg" data-rv>
      <figure class="cfg-vis">
        <img src="<?php echo esc_url( MACLAB_URI . '/assets/img/cfg-air13.jpg' ); ?>" alt="Выбранная модель MacBook" id="cfgImg">
        <div class="cfg-vis-top">
          <span class="chip chip--live"><i></i>В наличии в Москве</span>
          <span class="chip">Доставим завтра</span>
        </div>
        <figcaption class="cfg-vis-bot">
          <div class="cfg-name" id="cfgName">MacBook Air 13"</div>
          <div class="cfg-tags" id="cfgTags"></div>
        </figcaption>
      </figure>

      <div class="card cfg-box">
        <div class="opt-g">
          <h4>Модель</h4>
          <div class="opts" data-group="model">
            <button class="opt on" data-price="109990" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/cfg-air13.jpg' ); ?>" data-label='MacBook Air 13"' data-chip="M4">Air 13"<s>M4</s></button>
            <button class="opt" data-price="139990" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/cfg-air15.jpg' ); ?>" data-label='MacBook Air 15"' data-chip="M4">Air 15"<s>M4</s></button>
            <button class="opt" data-price="199990" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/cfg-pro14.jpg' ); ?>" data-label='MacBook Pro 14"' data-chip="M4 Pro">Pro 14"<s>M4 Pro</s></button>
            <button class="opt" data-price="329990" data-img="<?php echo esc_url( MACLAB_URI . '/assets/img/cfg-pro16.jpg' ); ?>" data-label='MacBook Pro 16"' data-chip="M4 Max">Pro 16"<s>M4 Max</s></button>
          </div>
        </div>
        <div class="opt-g">
          <h4>Оперативная память</h4>
          <div class="opts" data-group="ram">
            <button class="opt on" data-price="0" data-label="16 ГБ">16 ГБ</button>
            <button class="opt" data-price="22000" data-label="24 ГБ">24 ГБ</button>
            <button class="opt" data-price="44000" data-label="32 ГБ">32 ГБ</button>
            <button class="opt" data-price="88000" data-label="48 ГБ">48 ГБ</button>
          </div>
        </div>
        <div class="opt-g">
          <h4>Накопитель</h4>
          <div class="opts" data-group="ssd">
            <button class="opt on" data-price="0" data-label="256 ГБ">256 ГБ</button>
            <button class="opt" data-price="18000" data-label="512 ГБ">512 ГБ</button>
            <button class="opt" data-price="46000" data-label="1 ТБ">1 ТБ</button>
            <button class="opt" data-price="94000" data-label="2 ТБ">2 ТБ</button>
          </div>
        </div>
        <div class="opt-g">
          <h4>Цвет</h4>
          <div class="opts" data-group="color">
            <button class="opt on" data-price="0" data-label="Midnight"><i class="sw" style="background:#2E3641"></i>Midnight</button>
            <button class="opt" data-price="0" data-label="Starlight"><i class="sw" style="background:#F0E4D3"></i>Starlight</button>
            <button class="opt" data-price="0" data-label="Silver"><i class="sw" style="background:#E3E4E6"></i>Silver</button>
            <button class="opt" data-price="6000" data-label="Space Black"><i class="sw" style="background:#1D1D1F"></i>Space Black</button>
          </div>
        </div>

        <div class="cfg-sum" id="cfgSum"></div>

        <div class="cfg-total">
          <div class="price" id="cfgPrice">109 990 ₽<s id="cfgMonth">9 166 ₽ × 12 месяцев</s></div>
          <button class="btn" id="cfgAdd"><span>В корзину</span><span class="ar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ACCESSORIES -->
<section class="sec" id="acc" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <span class="eyebrow" data-rv>Аксессуары</span>
        <h2 class="h-lg split" data-rv style="margin-top:16px">Дополните экосистему</h2>
      </div>
      <a href="#catalog" class="btn btn--ghost" data-rv data-tab="acc"><span>Все аксессуары</span></a>
    </div>
    <div class="trio">
      <a href="#catalog" data-tab="acc" class="trio-i trio-i--big" data-rv data-parallax>
        <img src="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-airpods-max.jpg' ); ?>" alt="AirPods Max" loading="lazy">
        <div class="t"><div><span class="chip">Наушники</span><h3>AirPods Max</h3><p>Полноразмерные, Spatial Audio · от 64 990 ₽</p></div>
          <span class="go"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></div>
      </a>
      <a href="#catalog" data-tab="acc" class="trio-i" data-rv style="transition-delay:.12s" data-parallax>
        <img src="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-airpods-pro.jpg' ); ?>" alt="AirPods Pro 2" loading="lazy">
        <div class="t"><div><h3>AirPods Pro 2</h3><p>Шумоподавление · от 24 990 ₽</p></div>
          <span class="go"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></div>
      </a>
      <a href="#catalog" data-tab="acc" class="trio-i" data-rv style="transition-delay:.24s" data-parallax>
        <img src="<?php echo esc_url( MACLAB_URI . '/assets/img/acc-watch2.jpg' ); ?>" alt="Apple Watch" loading="lazy">
        <div class="t"><div><h3>Apple Watch</h3><p>Series 10 и Ultra 2 · от 39 990 ₽</p></div>
          <span class="go"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></div>
      </a>
    </div>
  </div>
</section>

<!-- MORPH: плашки собираются в кнопку -->
<section class="morph" id="morph"><a id="why" class="anchor"></a>
  <canvas class="mesh mesh--full" id="mesh2" aria-hidden="true"></canvas>
  <div class="morph-sticky">
    <div class="morph-stage">
      <div class="morph-top">
        <span class="eyebrow">Что входит в покупку</span>
        <h2 class="morph-h">без доплат<br>и мелкого шрифта</h2>
      </div>

      <div class="morph-cards">
        <span class="mcard" style="--x:-32vw;--y:-17vh"><b>Проверка при вас</b></span>
        <span class="mcard" style="--x:14vw;--y:-20vh"><b>Гарантия 12 месяцев</b></span>
        <span class="mcard" style="--x:-38vw;--y:4vh"><b>Trade-in до 120 000 ₽</b></span>
        <span class="mcard" style="--x:34vw;--y:-6vh"><b>Рассрочка 0%</b></span>
        <span class="mcard" style="--x:-32vw;--y:25vh"><b>Доставка 1–3 дня</b></span>
        <span class="mcard" style="--x:0vw;--y:25vh"><b>Настройка в подарок</b></span>
        <span class="mcard" style="--x:33vw;--y:19vh"><b>Подменный на время ремонта</b></span>
        <span class="mcard" style="--x:-9vw;--y:-23vh"><b>Свой сервис-центр</b></span>
      </div>

      <div class="morph-bottom">
        <div class="mb-txt">
          <h3 class="h-md">Осталось выбрать модель</h3>
          <p class="lead">Скажите, что вы делаете за ноутбуком, — остальное соберём сами.</p>
        </div>
        <a href="#cta" class="btn btn--lg morph-btn"><span>Подобрать MacBook</span><span class="ar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="sec" id="faq" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head"><div><span class="eyebrow" data-rv>Вопросы</span>
      <h2 class="h-lg split" data-rv style="margin-top:16px">Что обычно спрашивают</h2></div></div>
    <div data-rv>
      <div class="faq-i"><button class="faq-q">Техника новая и официальная?<span class="pm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Да. Все новые ноутбуки — запечатанные, с проверкой серийного номера по базе Apple при вас. Восстановленные модели мы честно помечаем как Refurbished и указываем количество циклов батареи.</p></div></div>
      <div class="faq-i"><button class="faq-q">Как быстро доставите?<span class="pm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>По Москве — в день заказа, если оформили до 18:00. По России — 1–3 дня курьером СДЭК с возможностью проверки до оплаты. Конфигурации под заказ — 7–10 дней.</p></div></div>
      <div class="faq-i"><button class="faq-q">Есть ли рассрочка?<span class="pm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Да, 0% на 6, 12 или 24 месяца без первого взноса и переплаты. Решение по заявке — около двух минут, нужен только паспорт.</p></div></div>
      <div class="faq-i"><button class="faq-q">Что с гарантией и сервисом?<span class="pm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>12 месяцев гарантии магазина плюс собственный сервисный центр. На время ремонта выдаём подменный ноутбук, чтобы вы не выпадали из работы.</p></div></div>
      <div class="faq-i"><button class="faq-q">Принимаете старую технику в trade-in?<span class="pm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Принимаем MacBook, iPhone и iPad — оценка до 120 000 ₽ за счёт стоимости новой покупки. Оценщик приезжает вместе с курьером.</p></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="cta" id="cta" data-rv>
      <div class="cta-txt">
        <span class="eyebrow">Отвечает человек, не бот</span>
        <h2 class="h-lg split" style="margin:20px 0 18px">Не уверены, какой Mac брать?</h2>
        <p class="lead">Напишите пару слов о задачах — разберём, где разница между M4 и M4 Pro реально заметна, а где вы просто переплатите тысяч восемьдесят. Если сейчас поздний вечер, ответим утром, как только откроемся.</p>
        <div class="cta-person">
          <span class="av"><?php echo esc_html( mb_substr( maclab_option( 'manager' ), 0, 1 ) ); ?></span>
          <div>
            <b><?php echo esc_html( maclab_option( 'manager' ) ); ?></b>
            <s>подбор техники · обычно отвечает за 15 минут</s>
          </div>
          <span class="chip chip--live"><i></i>сегодня до 21:00</span>
        </div>
      </div>

      <form class="lead-form" id="leadForm" novalidate>
        <div class="fld">
          <label for="lName">Как вас зовут</label>
          <input id="lName" name="name" type="text" placeholder="Иван" autocomplete="name" required>
        </div>
        <div class="fld">
          <label for="lPhone">Телефон</label>
          <input id="lPhone" name="phone" type="tel" placeholder="+7 (999) 123-45-67" autocomplete="tel" required>
        </div>
        <div class="fld">
          <label for="lWhat">Что нужно</label>
          <select id="lWhat" name="what">
            <option>Ноутбук для работы и учёбы</option>
            <option>Машина под монтаж, 3D или разработку</option>
            <option>Наушники, часы, аксессуары</option>
            <option>Оснастить команду</option>
            <option>Сам не знаю — нужен совет</option>
          </select>
        </div>
        <div class="fld">
          <label>Как удобнее связаться</label>
          <div class="ways" id="lWays">
            <button type="button" class="way on" data-way="Звонок">Позвонить</button>
            <button type="button" class="way" data-way="Telegram">Telegram</button>
            <button type="button" class="way" data-way="WhatsApp">WhatsApp</button>
          </div>
        </div>
        <input type="text" name="hp" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button class="btn btn--lg" type="submit"><span>Отправить</span><span class="ar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></button>
        <p class="f-msg" id="leadMsg" role="status"></p>
        <p class="f-note">Звоним с номера <a href="<?php echo esc_url( maclab_phone_href() ); ?>"><?php echo esc_html( maclab_option( 'phone' ) ); ?></a> — можете сохранить заранее. Контакты никому не передаём, рассылок не делаем.</p>
        <div class="f-done" id="leadDone" hidden>
          <span class="ok"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>
          <b>Записал, спасибо</b>
          <p><?php echo esc_html( maclab_option( 'manager' ) ); ?> наберёт в ближайшие 15 минут. Если не дозвонится — напишет в мессенджер, чтобы не отвлекать звонками.</p>
        </div>
      </form>
    </div>
  </div>
</section>
