<?php
/**
 * Plugin Name: Injah – Page de téléchargement PDF (دليل الوثائق)
 * Description: Shortcode [injah_pdf_downloads] : liste de PDF + popup nom/email (lead capture), envoi du lien par email, enregistrement des leads + export CSV. Fonctionne SANS Elementor. Peut être collé tel quel dans l'extension « Code Snippets ».
 * Version:     1.0.0
 * Author:      Injah Orientation
 * Text Domain: injah-dl
 *
 * ---------------------------------------------------------------------------
 * UTILISATION
 *  1. Code Snippets > Ajouter > coller TOUT ce fichier > « Exécuter partout » > Activer.
 *  2. Menu admin « Injah PDF » > Documents : renseigner titre, description, PDF…
 *  3. Créer une page WordPress, choisir le modèle « Full Width Content Layout »
 *     et y mettre le shortcode :  [injah_pdf_downloads]
 *  4. Les leads apparaissent dans « Injah PDF > Leads » (export CSV possible).
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'INJAH_DL_LOADED' ) ) :
define( 'INJAH_DL_LOADED', '1.0.0' );

/* ==========================================================================
 * 1. Données : documents (option) + valeurs par défaut
 * ========================================================================== */

function injah_dl_default_docs() {
	return array(
		array( 'id' => 'guide', 'title' => 'دليل التوجيه الجامعي مع طاقة الاستيعاب', 'desc' => 'الشعب والمؤسسات الجامعية وعدد المقاعد المتاحة لكل شعبة', 'size' => 'PDF · 4.2 MB', 'pdf' => '', 'thumb' => '', 'cover' => 'y', 'new' => 1 ),
		array( 'id' => 'dates', 'title' => 'أبرز مواعيد التوجيه الجامعي', 'desc' => 'رزنامة المراحل والآجال حتى لا يفوتك أي موعد', 'size' => 'PDF · 0.8 MB', 'pdf' => '', 'thumb' => '', 'cover' => 't', 'new' => 1 ),
		array( 'id' => 'score', 'title' => 'آخر مجموع موجَّه للسنة الماضية', 'desc' => 'مجموع النقاط الأدنى المقبول في كل شعبة', 'size' => 'PDF · 2.1 MB', 'pdf' => '', 'thumb' => '', 'cover' => 'y', 'new' => 0 ),
		array( 'id' => 'calc', 'title' => 'طريقة احتساب مجموع النقاط', 'desc' => 'الصيغ حسب نوع الباكالوريا مع أمثلة', 'size' => 'PDF · 0.6 MB', 'pdf' => '', 'thumb' => '', 'cover' => 'n', 'new' => 0 ),
		array( 'id' => 'steps', 'title' => 'خطوات التسجيل في المنصّة', 'desc' => 'دليل مصوّر لإدخال الاختيارات خطوة بخطوة', 'size' => 'PDF · 1.3 MB', 'pdf' => '', 'thumb' => '', 'cover' => 't', 'new' => 0 ),
	);
}

function injah_dl_default_settings() {
	return array(
		'logo'       => '', // vide = logo transparent intégré (recommandé)
		'mail_logo'  => 'https://www.injah-orientation.tn/wp-content/uploads/2026/10/logo-black.png',
		'from_name'  => 'Injah Orientation',
		'subject'    => 'وثيقتك جاهزة للتحميل — Injah Orientation',
		'notify'     => '', // email admin à notifier à chaque lead (vide = aucun)
		'footer'     => 1,
	);
}

/** Logo Injah transparent intégré (PNG 200px) — utilisé sur les couvertures dessinées. */
function injah_dl_logo_src() {
	$s = injah_dl_settings();
	if ( ! empty( $s['logo'] ) ) {
		return $s['logo'];
	}
	return 'data:image/png;base64,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';
}

function injah_dl_docs() {
	$docs = get_option( 'injah_dl_docs', null );
	if ( ! is_array( $docs ) ) {
		$docs = injah_dl_default_docs();
	}
	return $docs;
}

function injah_dl_settings() {
	return wp_parse_args( (array) get_option( 'injah_dl_settings', array() ), injah_dl_default_settings() );
}

function injah_dl_find_doc( $id ) {
	foreach ( injah_dl_docs() as $d ) {
		if ( (string) $d['id'] === (string) $id ) {
			return $d;
		}
	}
	return null;
}

/* ==========================================================================
 * 2. Type de contenu « Lead » (stockage des demandes)
 * ========================================================================== */

add_action( 'init', function () {
	register_post_type( 'injah_lead', array(
		'labels'          => array(
			'name'          => 'Leads PDF',
			'singular_name' => 'Lead',
			'menu_name'     => 'Leads',
			'all_items'     => 'Leads',
			'search_items'  => 'Rechercher',
			'not_found'     => 'Aucun lead pour le moment.',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => false, // sous-menu ajouté manuellement sous « Injah PDF »
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

add_filter( 'manage_injah_lead_posts_columns', function () {
	return array(
		'cb'     => '<input type="checkbox" />',
		'title'  => 'Nom',
		'email'  => 'Email',
		'doc'    => 'Document',
		'optin'  => 'Rappels',
		'date'   => 'Date',
	);
} );

add_action( 'manage_injah_lead_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'email':
			$e = get_post_meta( $post_id, '_email', true );
			echo '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>';
			break;
		case 'doc':
			echo esc_html( get_post_meta( $post_id, '_doc_title', true ) );
			break;
		case 'optin':
			echo get_post_meta( $post_id, '_optin', true ) ? '✔' : '—';
			break;
	}
}, 10, 2 );

/* ==========================================================================
 * 3. Shortcode [injah_pdf_downloads]
 * ========================================================================== */

function injah_dl_icon( $name ) {
	$i = array(
		'dl'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5M4 19h16"/></svg>',
		'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>',
		'ok'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>',
		'x'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
		'lock'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
	);
	return isset( $i[ $name ] ) ? $i[ $name ] : '';
}

add_shortcode( 'injah_pdf_downloads', 'injah_dl_shortcode' );

function injah_dl_shortcode( $atts ) {
	static $done = false;
	if ( $done ) {
		return ''; // une seule instance par page
	}
	$done = true;

	$docs = injah_dl_docs();
	$s    = injah_dl_settings();

	wp_enqueue_style( 'injah-dl-fonts', 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Inter+Tight:wght@600;700&display=swap', array(), null );

	// Données publiques pour le JS (l'URL du PDF n'est PAS exposée : elle est renvoyée par AJAX après le formulaire).
	$public = array();
	foreach ( $docs as $d ) {
		$public[] = array(
			'id'    => (string) $d['id'],
			't'     => (string) $d['title'],
			'sz'    => (string) $d['size'],
			'c'     => in_array( $d['cover'], array( 'y', 'n', 't' ), true ) ? $d['cover'] : 'y',
			'img'   => (string) $d['thumb'],
		);
	}
	$config = array(
		'ajax' => admin_url( 'admin-ajax.php' ),
		'logo' => injah_dl_logo_src(),
		'docs' => $public,
	);

	$count = count( $docs );
	ob_start();
	?>
<div class="ijdl" dir="rtl" lang="ar" id="ijdl-root">
<div class="ij-wrap">
<section class="ij-steps">
<div class="ij-step"><b>1</b><div><h3>دليل الوثائق</h3><p>دليل واحد أو أكثر</p></div></div>
<div class="ij-step"><b>2</b><div><h3>أدخل اسمك وبريدك</h3><p>لنرسل لك التحديثات المهمّة</p></div></div>
<div class="ij-step"><b>3</b><div><h3>حمّل مجانًا</h3><p>ملف PDF جاهز للتحميل</p></div></div>
</section>
<section id="res" class="ij-res">
<div class="ij-secHead"><div><h2>الوثائق المتاحة</h2><p>اختر وثيقة واضغط على «تحميل»</p></div><span class="ij-cnt"><?php echo esc_html( $count . ' ' . ( $count > 2 && $count < 11 ? 'وثائق' : 'وثيقة' ) ); ?></span></div>
<div class="ij-list" id="ij-list">
<?php foreach ( $docs as $d ) : ?>
<div class="ij-row" data-row="<?php echo esc_attr( $d['id'] ); ?>">
<?php echo injah_dl_thumb_html( $d, '', injah_dl_logo_src() ); ?>
<div class="ij-txt"><h3><?php echo esc_html( $d['title'] ); ?><?php if ( ! empty( $d['new'] ) ) : ?><span class="ij-badge">جديد</span><?php endif; ?></h3><p><?php echo esc_html( $d['desc'] ); ?></p></div>
<span class="ij-sz"><?php echo esc_html( $d['size'] ); ?></span>
<button type="button" class="ij-btn ij-rowbtn" data-open="<?php echo esc_attr( $d['id'] ); ?>"><span class="ij-i1"><?php echo injah_dl_icon( 'dl' ); ?>تحميل</span><span class="ij-i2"><?php echo injah_dl_icon( 'check' ); ?>تحميل مجددًا</span></button>
</div>
<?php endforeach; ?>
</div>
</section>
<?php if ( ! empty( $s['footer'] ) ) : ?>
<div class="ij-foot"><span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> Injah Orientation — إنجاح للتوجيه</span><span>بياناتك لا تُشارك مع أي طرف ثالث</span></div>
<?php endif; ?>
</div>
<div class="ij-mbar" id="ij-mbar"><button type="button" class="ij-btn ij-pri" id="ij-toList">اختر دليلك</button></div>
</div>
<div class="ijdl ij-ov" id="ij-ov" role="dialog" aria-modal="true" aria-labelledby="ij-sTitle" dir="rtl" lang="ar">
<div class="ij-sheet" id="ij-sheet">
<div class="ij-grab"></div>
<button type="button" class="ij-x" id="ij-x" aria-label="إغلاق"><?php echo injah_dl_icon( 'x' ); ?></button>
<form id="ij-f" novalidate>
<div class="ij-kicker">خطوة أخيرة</div>
<h3 id="ij-sTitle">احصل على وثائقك</h3>
<p class="ij-sub">أدخل اسمك وبريدك الإلكتروني لبدء التحميل.</p>
<div class="ij-chosen" id="ij-chosen"></div>
<div class="ij-fld" id="ij-fn"><label for="ij-name">الاسم</label><input id="ij-name" name="name" autocomplete="name" placeholder="مثال: أميرة بن علي"><div class="ij-em">يرجى إدخال اسمك</div></div>
<div class="ij-fld" id="ij-fe"><label for="ij-email">البريد الإلكتروني</label><input id="ij-email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="name@email.com"><div class="ij-em">يرجى إدخال بريد إلكتروني صحيح</div></div>
<div class="ij-hp" aria-hidden="true"><input type="text" name="website" id="ij-hp" tabindex="-1" autocomplete="off"></div>
<label class="ij-chk"><input type="checkbox" id="ij-optin" checked> أرغب في تلقّي تذكيرات بمواعيد التوجيه الجامعي</label>
<div class="ij-ferr" id="ij-ferr"></div>
<button class="ij-btn ij-pri" id="ij-go" type="submit"><?php echo injah_dl_icon( 'dl' ); ?><span>إرسال وتحميل</span></button>
<div class="ij-priv"><?php echo injah_dl_icon( 'lock' ); ?>بياناتك محمية ولن تُشارك</div>
</form>
<div class="ij-ok">
<div class="ij-ring"><?php echo injah_dl_icon( 'ok' ); ?></div>
<h3 id="ij-okT">شكرًا، بدأ التحميل!</h3>
<p id="ij-okP">أرسلنا الرابط أيضًا إلى بريدك الإلكتروني.</p>
<div class="ij-got" id="ij-got"></div>
<button type="button" class="ij-btn ij-sec" id="ij-close2">إغلاق</button>
</div>
</div>
</div>
<div class="ijdl ij-toast" id="ij-toast" dir="rtl"></div>
<?php
	$html = ob_get_clean();

	add_action( 'wp_footer', function () use ( $config ) {
		echo '<script>window.INJAH_DL=' . wp_json_encode( $config ) . ';</script>';
		echo '<script>' . injah_dl_js() . '</script>';
	}, 50 );

	// On retire les retours à la ligne pour que wpautop / l'éditeur ne casse pas le HTML.
	return '<style>' . injah_dl_css() . '</style>' . str_replace( array( "\r", "\n" ), '', $html );
}

function injah_dl_thumb_html( $d, $cls, $logo ) {
	if ( ! empty( $d['thumb'] ) ) {
		return '<div class="ij-th ' . esc_attr( $cls ) . '"><img src="' . esc_url( $d['thumb'] ) . '" alt="" loading="lazy"></div>';
	}
	$c = in_array( $d['cover'], array( 'y', 'n', 't' ), true ) ? $d['cover'] : 'y';
	return '<div class="ij-th cv-' . $c . ' ' . esc_attr( $cls ) . '"><div class="ij-th-top"><img src="' . esc_url( $logo, array( 'http', 'https', 'data' ) ) . '" alt=""></div><div class="ij-th-t">' . esc_html( $d['title'] ) . '</div><div class="ij-th-l"></div><div class="ij-th-l s"></div><div class="ij-th-l"></div><span class="ij-th-b">PDF</span></div>';
}

/* ==========================================================================
 * 4. CSS (tout est préfixé .ijdl pour ne pas entrer en conflit avec Monstroid2)
 * ========================================================================== */

function injah_dl_css() {
	return <<<'CSS'
.ijdl{--ink:#1a1a1a;--ink2:#4a4949;--mute:#7f7d8e;--bg:#fff9f0;--card:#fff;--line:#efe9dc;--acc:#ffba0b;--acc-d:#d99a00;--teal:#5f45ea;--teal-l:#eeeafd;font-family:"Tajawal",sans-serif;color:var(--ink);-webkit-font-smoothing:antialiased;direction:rtl;text-align:right;line-height:1.5}
.ijdl *,.ijdl *::before,.ijdl *::after{box-sizing:border-box}
.ijdl h2,.ijdl h3,.ijdl p{margin:0;padding:0;font-family:"Tajawal",sans-serif;color:inherit;letter-spacing:0;text-transform:none}
#ijdl-root{background:var(--bg);width:100%}
.ijdl .ij-wrap{max-width:1120px;margin:0 auto;padding:1px 28px}
.ijdl .ij-btn{all:unset;box-sizing:border-box;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;gap:10px;height:54px;padding:0 26px;border-radius:14px;font-family:"Tajawal",sans-serif;font-weight:600;font-size:16px;line-height:1;cursor:pointer;color:var(--ink);text-transform:none;letter-spacing:0;transition:transform .15s,background .15s,box-shadow .15s,color .15s,border-color .15s}
.ijdl .ij-btn:active{transform:scale(.98)}
.ijdl .ij-btn:focus-visible{outline:2px solid var(--ink);outline-offset:2px}
.ijdl .ij-btn svg{width:20px;height:20px;flex:none}
.ijdl .ij-btn.ij-pri{background:var(--acc);color:var(--ink);font-weight:700;box-shadow:0 8px 20px rgba(255,186,11,.35)}
.ijdl .ij-btn.ij-pri:hover{background:#ffc93d;color:var(--ink)}
.ijdl .ij-btn.ij-sec{background:#fff;border:1px solid var(--line);color:var(--ink)}
.ijdl .ij-btn.ij-sec:hover{border-color:var(--ink2);background:#fff;color:var(--ink)}
.ijdl .ij-steps{display:grid;grid-template-columns:repeat(3,1fr);background:var(--card);border:1px solid var(--line);border-radius:18px;margin:36px 0 40px}
.ijdl .ij-step{padding:22px 24px;display:flex;gap:14px;align-items:flex-start}
.ijdl .ij-step+.ij-step{border-right:1px solid var(--line)}
.ijdl .ij-step b{flex:none;width:32px;height:32px;border-radius:50%;background:var(--acc);color:var(--ink);font-weight:700;display:grid;place-items:center;font-size:14px}
.ijdl .ij-step h3{font-size:15px;font-weight:600;line-height:1.5}
.ijdl .ij-step p{font-size:13.5px;color:var(--mute);margin-top:3px;line-height:1.6}
.ijdl .ij-secHead{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:22px;gap:20px}
.ijdl .ij-secHead h2{font-size:30px;font-weight:700;line-height:1.3}
.ijdl .ij-secHead p{color:var(--mute);font-size:15px;margin-top:6px}
.ijdl .ij-cnt{font-size:13px;color:var(--mute);white-space:nowrap}
.ijdl .ij-list{background:var(--card);border:1px solid var(--line);border-radius:22px;margin-bottom:72px;overflow:hidden}
.ijdl .ij-row{display:grid;grid-template-columns:auto 1fr auto auto;gap:18px;align-items:center;padding:18px 22px;transition:background .15s}
.ijdl .ij-row+.ij-row{border-top:1px solid var(--line)}
.ijdl .ij-row:hover{background:#fffaf0}
.ijdl .ij-row h3{font-size:16.5px;font-weight:600;line-height:1.5}
.ijdl .ij-row p{font-size:13.5px;color:var(--mute);margin-top:2px;line-height:1.6}
.ijdl .ij-badge{font-size:11.5px;font-weight:600;color:var(--acc-d);background:#fff1cc;padding:2px 8px;border-radius:6px;margin-inline-start:8px;vertical-align:middle;white-space:nowrap}
.ijdl .ij-sz{font-size:12.5px;color:var(--mute);direction:ltr;white-space:nowrap;font-family:"Inter Tight","Tajawal",sans-serif}
.ijdl .ij-rowbtn{height:44px;padding:0 18px;font-size:14.5px;border-radius:12px;background:#fff;border:1px solid var(--line)}
.ijdl .ij-rowbtn:hover{border-color:var(--ink);background:var(--ink);color:#fff}
.ijdl .ij-rowbtn svg{width:18px;height:18px}
.ijdl .ij-rowbtn span{display:inline-flex;align-items:center;gap:10px}
.ijdl .ij-rowbtn .ij-i2{display:none}
.ijdl .ij-row.dl .ij-rowbtn{border-color:#d4ccfb;color:var(--teal)}
.ijdl .ij-row.dl .ij-rowbtn:hover{background:var(--teal);color:#fff;border-color:var(--teal)}
.ijdl .ij-row.dl .ij-i1{display:none}
.ijdl .ij-row.dl .ij-i2{display:inline-flex}
.ijdl .ij-th{width:84px;aspect-ratio:3/4;border-radius:8px;background:#fff;border:1px solid var(--line);box-shadow:0 6px 14px rgba(26,26,26,.08),0 1px 2px rgba(26,26,26,.06);overflow:hidden;position:relative;display:flex;flex-direction:column;padding:0 0 8px;flex:none;transition:transform .2s}
.ijdl .ij-row:hover .ij-th{transform:rotate(-2deg) translateY(-2px)}
.ijdl .ij-th>img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;margin:0;max-width:none}
.ijdl .ij-th-top{height:30%;background:var(--acc);display:flex;align-items:center;justify-content:center;padding:4px}
.ijdl .ij-th-top img{height:70%;width:auto;max-width:100%;mix-blend-mode:multiply;margin:0}
.ijdl .cv-n .ij-th-top{background:var(--ink)}
.ijdl .cv-n .ij-th-top img,.ijdl .cv-t .ij-th-top img{mix-blend-mode:normal;filter:brightness(0) invert(1)}
.ijdl .cv-t .ij-th-top{background:var(--teal)}
.ijdl .ij-th-t{font-size:7.5px;line-height:1.35;font-weight:700;padding:6px 7px 4px;color:var(--ink);display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.ijdl .ij-th-l{height:3px;border-radius:2px;background:#efe9dc;margin:3px 7px 0}
.ijdl .ij-th-l.s{width:55%}
.ijdl .ij-th-b{position:absolute;bottom:5px;left:5px;font-size:7px;font-weight:800;font-family:"Inter Tight",sans-serif;background:#e2412f;color:#fff;padding:1px 4px;border-radius:3px;letter-spacing:.3px;line-height:1.3}
.ijdl .ij-th.sm{width:52px}
.ijdl .ij-th.sm .ij-th-t{font-size:5px;padding:4px 4px 2px}
.ijdl .ij-th.sm .ij-th-l{margin:2px 4px 0;height:2px}
.ijdl .ij-th.sm .ij-th-b{font-size:5.5px;bottom:3px;left:3px}
.ijdl .ij-foot{border-top:1px solid var(--line);padding:26px 0;color:var(--mute);font-size:13px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.ijdl.ij-ov{position:fixed;inset:0;background:rgba(26,26,26,.45);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);z-index:999999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .25s}
.ijdl.ij-ov.open{opacity:1;pointer-events:auto}
.ijdl .ij-sheet{width:min(460px,92vw);max-height:92vh;overflow-y:auto;background:var(--card);border-radius:24px;padding:30px;transform:translateY(16px) scale(.98);transition:transform .3s cubic-bezier(.2,.8,.2,1);position:relative}
.ijdl.ij-ov.open .ij-sheet{transform:none}
.ijdl .ij-grab{display:none}
.ijdl .ij-x{all:unset;box-sizing:border-box;cursor:pointer;position:absolute;top:18px;left:18px;width:38px;height:38px;border-radius:50%;background:var(--bg);color:var(--ink);display:grid;place-items:center}
.ijdl .ij-x:hover{background:#f3efe4}
.ijdl .ij-x svg{width:18px;height:18px}
.ijdl .ij-kicker{font-size:13px;color:var(--teal);font-weight:600}
.ijdl .ij-sheet h3{font-size:22px;margin:6px 0;font-weight:600;padding-left:40px;line-height:1.4}
.ijdl .ij-sub{color:var(--mute);font-size:14px;line-height:1.7;margin-bottom:22px}
.ijdl .ij-chosen{display:flex;gap:12px;align-items:center;padding:14px;border-radius:14px;background:#fff1cc;border:1.5px solid var(--acc);margin-bottom:16px}
.ijdl .ij-chosen .ct{flex:1;font-weight:700;font-size:14.5px;line-height:1.5}
.ijdl .ij-chosen .cs{display:block;font-weight:400;font-size:12px;color:var(--ink2);margin-top:2px;direction:ltr;text-align:right}
.ijdl .ij-chosen .ck{width:26px;height:26px;border-radius:50%;background:var(--ink);color:var(--acc);display:grid;place-items:center;flex:none}
.ijdl .ij-chosen .ck svg{width:14px;height:14px}
.ijdl .ij-fld{margin-bottom:16px}
.ijdl .ij-fld label{display:block;font-size:14px;font-weight:500;margin-bottom:7px;color:var(--ink)}
.ijdl .ij-fld input{width:100%;height:52px;border-radius:13px;border:1.5px solid var(--line);background:#fff;padding:0 16px;margin:0;font-family:"Tajawal",sans-serif;font-size:16px;color:var(--ink);transition:border-color .15s,box-shadow .15s;outline:none;box-shadow:none;text-align:right;direction:rtl}
.ijdl .ij-fld input:focus{border-color:var(--ink);box-shadow:0 0 0 4px rgba(26,26,26,.08)}
.ijdl .ij-fld input[type=email]{direction:ltr;text-align:right}
.ijdl .ij-fld.err input{border-color:#c4442f}
.ijdl .ij-em{display:none;color:#c4442f;font-size:12.5px;margin-top:6px}
.ijdl .ij-fld.err .ij-em{display:block}
.ijdl .ij-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
.ijdl .ij-chk{display:flex;gap:10px;align-items:flex-start;font-size:13px;color:var(--ink2);margin:4px 0 20px;line-height:1.6;cursor:pointer;font-weight:400}
.ijdl .ij-chk input{width:18px;height:18px;accent-color:var(--ink);margin:2px 0 0;flex:none}
.ijdl .ij-ferr{display:none;color:#c4442f;font-size:13px;margin:-8px 0 14px}
.ijdl .ij-ferr.on{display:block}
.ijdl .ij-sheet .ij-btn{width:100%}
.ijdl .ij-btn.load{pointer-events:none;opacity:.8}
.ijdl .ij-spin{width:18px;height:18px;border:2.5px solid rgba(0,0,0,.2);border-top-color:#1a1a1a;border-radius:50%;animation:ijsp .7s linear infinite}
@keyframes ijsp{to{transform:rotate(360deg)}}
.ijdl .ij-priv{display:flex;align-items:center;gap:6px;justify-content:center;font-size:12px;color:var(--mute);margin-top:14px}
.ijdl .ij-priv svg{width:14px;height:14px}
.ijdl .ij-ok{display:none;text-align:center;padding:10px 0 4px}
.ijdl .ij-ring{width:76px;height:76px;border-radius:50%;background:var(--teal-l);color:var(--teal);display:grid;place-items:center;margin:0 auto 18px;animation:ijpop .45s cubic-bezier(.2,1.4,.4,1)}
.ijdl .ij-ring svg{width:36px;height:36px}
@keyframes ijpop{from{transform:scale(.5);opacity:0}}
.ijdl .ij-sheet .ij-ok h3{padding:0;font-size:22px}
.ijdl .ij-ok p{color:var(--mute);font-size:14.5px;line-height:1.7;margin:6px 0 22px}
.ijdl .ij-sheet.success form{display:none}
.ijdl .ij-sheet.success .ij-ok{display:block}
.ijdl .ij-got{text-align:right;border:1px solid var(--line);border-radius:14px;margin-bottom:16px;overflow:hidden}
.ijdl .ij-got a{display:flex;gap:10px;align-items:center;padding:11px 14px;font-size:14px;text-decoration:none;color:var(--ink);box-shadow:none}
.ijdl .ij-got a:hover{background:#fffaf0;color:var(--ink)}
.ijdl .ij-got a span{flex:1}
.ijdl .ij-got a svg{width:18px;height:18px;color:var(--teal);flex:none}
.ijdl.ij-toast{position:fixed;bottom:24px;left:50%;transform:translate(-50%,30px);background:#1a1a1a;color:#fff;padding:12px 18px;border-radius:12px;font-size:14px;opacity:0;transition:.3s;z-index:1000000;pointer-events:none;white-space:nowrap}
.ijdl.ij-toast.on{opacity:1;transform:translate(-50%,0)}
.ijdl .ij-mbar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:9990;padding:12px 16px calc(12px + env(safe-area-inset-bottom));background:rgba(255,249,240,.92);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-top:1px solid var(--line);gap:10px;transform:translateY(110%);transition:transform .3s}
.ijdl .ij-mbar.show{transform:none}
.ijdl .ij-mbar .ij-btn{flex:1;height:50px;font-size:14.5px;padding:0 12px}
@media (max-width:760px){
.ijdl .ij-wrap{padding:1px 20px}
.ijdl .ij-steps{grid-template-columns:1fr;margin:20px 0 28px}
.ijdl .ij-step{padding:16px 18px}
.ijdl .ij-step+.ij-step{border-right:0;border-top:1px solid var(--line)}
.ijdl .ij-secHead{align-items:flex-start}
.ijdl .ij-secHead h2{font-size:24px}
.ijdl .ij-cnt{display:none}
.ijdl .ij-row{grid-template-columns:auto 1fr;gap:6px 14px;padding:16px}
.ijdl .ij-row .ij-th{width:72px;grid-row:span 2;align-self:start}
.ijdl .ij-row h3{font-size:15px}
.ijdl .ij-sz{display:none}
.ijdl .ij-rowbtn{grid-column:2;justify-self:start;height:42px;margin-top:6px}
.ijdl .ij-list{margin-bottom:48px}
.ijdl .ij-foot{padding-bottom:100px}
.ijdl .ij-mbar{display:flex}
.ijdl.ij-ov{align-items:flex-end}
.ijdl .ij-sheet{width:100%;max-height:92vh;border-radius:26px 26px 0 0;padding:14px 22px 28px;transform:translateY(100%)}
.ijdl .ij-grab{display:block;width:44px;height:5px;border-radius:3px;background:var(--line);margin:0 auto 16px}
.ijdl .ij-x{top:22px}
}
CSS;
}

/* ==========================================================================
 * 5. JavaScript (vanilla, aucune dépendance)
 * ========================================================================== */

function injah_dl_js() {
	return <<<'JS'
(function(){
var C=window.INJAH_DL;if(!C)return;
var $=function(id){return document.getElementById(id)};
var root=$('ijdl-root'),ov=$('ij-ov'),sheet=$('ij-sheet'),toastEl=$('ij-toast');
if(!root||!ov)return;
document.body.appendChild(ov);document.body.appendChild(toastEl);
var byId={};C.docs.forEach(function(d){byId[d.id]=d});
var ICDL='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5M4 19h16"/></svg>';
var ICCK='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>';
function esc(s){return String(s).replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]})}
function ls(k,v){try{if(v===undefined)return JSON.parse(localStorage.getItem(k)||'null');localStorage.setItem(k,JSON.stringify(v))}catch(e){return null}}
var downloaded=ls('ou_dl')||[];
function mark(){[].forEach.call(root.querySelectorAll('[data-row]'),function(r){r.classList.toggle('dl',downloaded.indexOf(r.getAttribute('data-row'))>-1)})}
mark();
function thumb(d,cls){
  if(d.img)return '<div class="ij-th '+cls+'"><img src="'+esc(d.img)+'" alt=""></div>';
  return '<div class="ij-th cv-'+d.c+' '+cls+'"><div class="ij-th-top"><img src="'+esc(C.logo)+'" alt=""></div><div class="ij-th-t">'+esc(d.t)+'</div><div class="ij-th-l"></div><div class="ij-th-l s"></div><div class="ij-th-l"></div><span class="ij-th-b">PDF</span></div>';
}
var nameI=$('ij-name'),emailI=$('ij-email'),go=$('ij-go'),goHtml=go.innerHTML,cur=null,lastFocus=null;
function open(k){
  var d=byId[k];if(!d)return;cur=k;lastFocus=document.activeElement;
  sheet.classList.remove('success');sheet.scrollTop=0;$('ij-ferr').classList.remove('on');
  $('ij-chosen').innerHTML=thumb(d,'sm')+'<div class="ct">'+esc(d.t)+'<span class="cs">'+esc(d.sz)+'</span></div><span class="ck">'+ICCK+'</span>';
  var u=ls('ou_user');if(u){nameI.value=u.n||'';emailI.value=u.e||''}
  ov.classList.add('open');document.documentElement.style.overflow='hidden';
  setTimeout(function(){if(innerWidth>760)(nameI.value?emailI:nameI).focus()},250);
}
function close(){ov.classList.remove('open');document.documentElement.style.overflow='';if(lastFocus&&lastFocus.focus)lastFocus.focus()}
document.addEventListener('click',function(e){var b=e.target.closest&&e.target.closest('[data-open]');if(b&&root.contains(b)){e.preventDefault();open(b.getAttribute('data-open'))}});
$('ij-x').onclick=close;$('ij-close2').onclick=close;
ov.addEventListener('click',function(e){if(e.target===ov)close()});
addEventListener('keydown',function(e){if(e.key==='Escape'&&ov.classList.contains('open'))close()});
[nameI,emailI].forEach(function(i){i.addEventListener('input',function(){i.parentNode.classList.remove('err')})});
function toast(t){toastEl.textContent=t;toastEl.classList.add('on');clearTimeout(toast._t);toast._t=setTimeout(function(){toastEl.classList.remove('on')},2000)}
function trigger(url){var a=document.createElement('a');a.href=url;a.setAttribute('download','');a.target='_blank';a.rel='noopener';document.body.appendChild(a);a.click();a.remove()}
$('ij-f').addEventListener('submit',function(e){
  e.preventDefault();
  var n=nameI.value.trim(),em=emailI.value.trim(),ok=true;
  if(n.length<2){$('ij-fn').classList.add('err');ok=false}
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(em)){$('ij-fe').classList.add('err');ok=false}
  if(!ok)return;
  $('ij-ferr').classList.remove('on');
  go.classList.add('load');go.innerHTML='<div class="ij-spin"></div><span>جارٍ التحضير…</span>';
  var fd=new FormData();
  fd.append('action','injah_dl_request');fd.append('name',n);fd.append('email',em);fd.append('doc',cur);
  fd.append('optin',$('ij-optin').checked?'1':'0');fd.append('website',$('ij-hp').value);
  fetch(C.ajax,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(res){
    go.classList.remove('load');go.innerHTML=goHtml;
    if(!res||!res.success){var m=(res&&res.data&&res.data.message)||'حدث خطأ، يرجى المحاولة مجددًا';$('ij-ferr').textContent=m;$('ij-ferr').classList.add('on');return}
    var url=res.data.url,d=byId[cur];
    ls('ou_user',{n:n,e:em});
    if(downloaded.indexOf(cur)<0)downloaded.push(cur);ls('ou_dl',downloaded);mark();
    $('ij-okT').textContent='شكرًا '+n.split(/\s+/)[0]+'، وثيقتك جاهزة!';
    $('ij-okP').textContent='وثيقة واحدة — '+(res.data.mailed?'أرسلنا الرابط أيضًا إلى '+em:'يمكنك تحميلها الآن');
    $('ij-got').innerHTML='<a href="'+esc(url)+'" download target="_blank" rel="noopener">'+ICDL+'<span>'+esc(d.t)+'</span></a>';
    sheet.classList.add('success');sheet.scrollTop=0;
    trigger(url);
  }).catch(function(){
    go.classList.remove('load');go.innerHTML=goHtml;
    $('ij-ferr').textContent='تعذّر الاتصال، يرجى المحاولة مجددًا';$('ij-ferr').classList.add('on');
  });
});
var mbar=$('ij-mbar'),res=$('res');
$('ij-toList').onclick=function(){scrollTo({top:res.getBoundingClientRect().top+scrollY-80,behavior:'smooth'})};
addEventListener('scroll',function(){mbar.classList.toggle('show',scrollY>60)},{passive:true});
})();
JS;
}

/* ==========================================================================
 * 6. AJAX : enregistre le lead, envoie l'email, renvoie l'URL du PDF
 * ========================================================================== */

add_action( 'wp_ajax_injah_dl_request', 'injah_dl_ajax' );
add_action( 'wp_ajax_nopriv_injah_dl_request', 'injah_dl_ajax' );

function injah_dl_ajax() {
	// Anti-spam : champ piège (honeypot).
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( array( 'message' => 'طلب غير صالح' ), 400 );
	}

	// Anti-spam : max 15 demandes / 10 min par IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'injah_dl_rl_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 15 ) {
		wp_send_json_error( array( 'message' => 'عدد كبير من الطلبات، يرجى المحاولة بعد قليل' ), 429 );
	}
	set_transient( $key, $hit + 1, 10 * MINUTE_IN_SECONDS );

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$doc   = isset( $_POST['doc'] ) ? sanitize_text_field( wp_unslash( $_POST['doc'] ) ) : '';
	$optin = ! empty( $_POST['optin'] ) && '1' === $_POST['optin'];

	if ( mb_strlen( $name ) < 2 ) {
		wp_send_json_error( array( 'message' => 'يرجى إدخال اسمك' ), 400 );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'يرجى إدخال بريد إلكتروني صحيح' ), 400 );
	}
	$d = injah_dl_find_doc( $doc );
	if ( ! $d ) {
		wp_send_json_error( array( 'message' => 'الوثيقة غير موجودة' ), 404 );
	}
	if ( empty( $d['pdf'] ) ) {
		wp_send_json_error( array( 'message' => 'هذه الوثيقة غير متوفرة حاليًا، يرجى المحاولة لاحقًا' ), 404 );
	}

	// Enregistrement du lead.
	$lead_id = wp_insert_post( array(
		'post_type'   => 'injah_lead',
		'post_status' => 'publish',
		'post_title'  => $name,
	) );
	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_email', $email );
		update_post_meta( $lead_id, '_doc_id', $d['id'] );
		update_post_meta( $lead_id, '_doc_title', $d['title'] );
		update_post_meta( $lead_id, '_optin', $optin ? 1 : 0 );
	}

	$s   = injah_dl_settings();
	$url = esc_url_raw( $d['pdf'] );

	// Email à l'utilisateur.
	$first = preg_split( '/\s+/u', $name );
	$first = $first ? $first[0] : $name;
	$body  = '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;font-size:15px;color:#1a1a1a;line-height:1.8;background:#fff9f0;padding:24px">'
		. '<div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #efe9dc;border-radius:16px;padding:28px">'
		. ( $s['mail_logo'] ? '<img src="' . esc_url( $s['mail_logo'] ) . '" alt="Injah Orientation" style="height:54px;width:auto;display:block;margin-bottom:16px">' : '' )
		. '<p style="margin:0 0 10px">مرحبًا ' . esc_html( $first ) . '،</p>'
		. '<p style="margin:0 0 18px">شكرًا لاهتمامك! يمكنك تحميل وثيقتك من الرابط أسفله:</p>'
		. '<p style="margin:0 0 22px"><a href="' . esc_url( $url ) . '" style="display:inline-block;background:#ffba0b;color:#1a1a1a;font-weight:bold;text-decoration:none;padding:12px 22px;border-radius:12px">⬇ ' . esc_html( $d['title'] ) . '</a></p>'
		. '<p style="margin:0;color:#7f7d8e;font-size:13px">' . esc_html( home_url( '/' ) ) . ' — إنجاح للتوجيه</p>'
		. '</div></div>';

	$from_name = $s['from_name'] ? $s['from_name'] : get_bloginfo( 'name' );
	$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
	$fn        = function () use ( $from_name ) { return $from_name; };
	add_filter( 'wp_mail_from_name', $fn );
	$mailed = wp_mail( $email, $s['subject'], $body, $headers );
	remove_filter( 'wp_mail_from_name', $fn );

	// Notification admin (optionnelle).
	if ( ! empty( $s['notify'] ) && is_email( $s['notify'] ) ) {
		wp_mail( $s['notify'], 'Nouveau lead PDF : ' . $d['title'], "Nom : $name\nEmail : $email\nDocument : {$d['title']}\nRappels : " . ( $optin ? 'oui' : 'non' ) );
	}

	wp_send_json_success( array( 'url' => $url, 'mailed' => (bool) $mailed ) );
}

/* ==========================================================================
 * 7. Administration : menu « Injah PDF » (documents, réglages, export CSV)
 * ========================================================================== */

add_action( 'admin_menu', function () {
	add_menu_page( 'Injah PDF', 'Injah PDF', 'manage_options', 'injah-dl', 'injah_dl_admin_page', 'dashicons-media-document', 26 );
	add_submenu_page( 'injah-dl', 'Documents PDF', 'Documents', 'manage_options', 'injah-dl', 'injah_dl_admin_page' );
	add_submenu_page( 'injah-dl', 'Leads', 'Leads', 'manage_options', 'edit.php?post_type=injah_lead' );
} );

// Garde le menu « Injah PDF » ouvert sur l'écran des leads.
add_filter( 'parent_file', function ( $parent ) {
	global $typenow;
	return 'injah_lead' === $typenow ? 'injah-dl' : $parent;
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_injah-dl' === $hook ) {
		wp_enqueue_media();
	}
} );

add_action( 'admin_post_injah_dl_save', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé' );
	}
	check_admin_referer( 'injah_dl_save' );

	$in   = isset( $_POST['docs'] ) && is_array( $_POST['docs'] ) ? wp_unslash( $_POST['docs'] ) : array();
	$docs = array();
	$used = array();
	foreach ( $in as $row ) {
		$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
		if ( '' === $title ) {
			continue;
		}
		$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
		if ( '' === $id || isset( $used[ $id ] ) ) {
			$id = 'doc' . substr( md5( $title . wp_rand() ), 0, 6 );
		}
		$used[ $id ] = true;
		$docs[]      = array(
			'id'    => $id,
			'title' => $title,
			'desc'  => isset( $row['desc'] ) ? sanitize_text_field( $row['desc'] ) : '',
			'size'  => isset( $row['size'] ) ? sanitize_text_field( $row['size'] ) : '',
			'pdf'   => isset( $row['pdf'] ) ? esc_url_raw( $row['pdf'] ) : '',
			'thumb' => isset( $row['thumb'] ) ? esc_url_raw( $row['thumb'] ) : '',
			'cover' => isset( $row['cover'] ) && in_array( $row['cover'], array( 'y', 'n', 't' ), true ) ? $row['cover'] : 'y',
			'new'   => ! empty( $row['new'] ) ? 1 : 0,
		);
	}
	update_option( 'injah_dl_docs', $docs, false );

	$s = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
	update_option( 'injah_dl_settings', array(
		'logo'      => isset( $s['logo'] ) ? esc_url_raw( $s['logo'] ) : '',
		'mail_logo' => isset( $s['mail_logo'] ) ? esc_url_raw( $s['mail_logo'] ) : '',
		'from_name' => isset( $s['from_name'] ) ? sanitize_text_field( $s['from_name'] ) : '',
		'subject'   => isset( $s['subject'] ) ? sanitize_text_field( $s['subject'] ) : '',
		'notify'    => isset( $s['notify'] ) ? sanitize_email( $s['notify'] ) : '',
		'footer'    => ! empty( $s['footer'] ) ? 1 : 0,
	), false );

	wp_safe_redirect( admin_url( 'admin.php?page=injah-dl&saved=1' ) );
	exit;
} );

add_action( 'admin_post_injah_dl_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé' );
	}
	check_admin_referer( 'injah_dl_export' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=injah-leads-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM UTF-8 pour Excel (arabe)
	fputcsv( $out, array( 'Date', 'Nom', 'Email', 'Document', 'Rappels' ) );
	$q = new WP_Query( array( 'post_type' => 'injah_lead', 'posts_per_page' => -1, 'post_status' => 'any', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
	foreach ( $q->posts as $p ) {
		fputcsv( $out, array(
			get_the_date( 'Y-m-d H:i', $p ),
			$p->post_title,
			get_post_meta( $p->ID, '_email', true ),
			get_post_meta( $p->ID, '_doc_title', true ),
			get_post_meta( $p->ID, '_optin', true ) ? 'oui' : 'non',
		) );
	}
	fclose( $out );
	exit;
} );

function injah_dl_admin_row( $i, $d ) {
	$n = 'docs[' . $i . ']';
	ob_start();
	?>
	<tr class="ijdl-r">
		<td><input type="hidden" name="<?php echo $n; ?>[id]" value="<?php echo esc_attr( $d['id'] ); ?>">
			<input type="text" name="<?php echo $n; ?>[title]" value="<?php echo esc_attr( $d['title'] ); ?>" placeholder="Titre" dir="rtl" style="width:100%"><br>
			<input type="text" name="<?php echo $n; ?>[desc]" value="<?php echo esc_attr( $d['desc'] ); ?>" placeholder="Description" dir="rtl" style="width:100%;margin-top:4px"></td>
		<td><input type="text" name="<?php echo $n; ?>[size]" value="<?php echo esc_attr( $d['size'] ); ?>" placeholder="PDF · 1.2 MB" style="width:110px"></td>
		<td><input type="url" name="<?php echo $n; ?>[pdf]" value="<?php echo esc_attr( $d['pdf'] ); ?>" placeholder="https://…/fichier.pdf" style="width:100%"> <button type="button" class="button ijdl-media" data-type="application/pdf">Choisir PDF</button></td>
		<td><input type="url" name="<?php echo $n; ?>[thumb]" value="<?php echo esc_attr( $d['thumb'] ); ?>" placeholder="(optionnel)" style="width:100%"> <button type="button" class="button ijdl-media" data-type="image">Choisir image</button></td>
		<td><select name="<?php echo $n; ?>[cover]">
			<option value="y" <?php selected( $d['cover'], 'y' ); ?>>Jaune</option>
			<option value="n" <?php selected( $d['cover'], 'n' ); ?>>Noir</option>
			<option value="t" <?php selected( $d['cover'], 't' ); ?>>Violet</option>
		</select></td>
		<td style="text-align:center"><input type="checkbox" name="<?php echo $n; ?>[new]" value="1" <?php checked( ! empty( $d['new'] ) ); ?>></td>
		<td><button type="button" class="button-link ijdl-up" title="Monter">▲</button> <button type="button" class="button-link ijdl-down" title="Descendre">▼</button> <button type="button" class="button-link-delete ijdl-del">Supprimer</button></td>
	</tr>
	<?php
	return ob_get_clean();
}

function injah_dl_admin_page() {
	$docs = injah_dl_docs();
	$s    = injah_dl_settings();
	$tpl  = injah_dl_admin_row( '__i__', array( 'id' => '', 'title' => '', 'desc' => '', 'size' => 'PDF · ', 'pdf' => '', 'thumb' => '', 'cover' => 'y', 'new' => 0 ) );
	$leads = wp_count_posts( 'injah_lead' );
	?>
	<div class="wrap">
		<h1>Injah PDF — Documents à télécharger</h1>
		<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Enregistré.</p></div><?php endif; ?>
		<p>Shortcode à placer dans une page (modèle conseillé : <strong>Full Width Content Layout</strong>) : <code>[injah_pdf_downloads]</code></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="injah_dl_save">
			<?php wp_nonce_field( 'injah_dl_save' ); ?>
			<table class="widefat striped" id="ijdl-table">
				<thead><tr><th style="width:26%">Titre / description (arabe)</th><th>Taille</th><th style="width:22%">URL du PDF</th><th style="width:18%">Miniature (1re page)</th><th>Couverture</th><th>« جديد »</th><th></th></tr></thead>
				<tbody>
				<?php foreach ( $docs as $i => $d ) { echo injah_dl_admin_row( $i, wp_parse_args( $d, array( 'thumb' => '', 'cover' => 'y', 'new' => 0 ) ) ); } ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="ijdl-add">+ Ajouter un document</button></p>

			<h2>Réglages</h2>
			<table class="form-table">
				<tr><th>Logo des couvertures</th><td><input type="url" name="settings[logo]" value="<?php echo esc_attr( $s['logo'] ); ?>" class="regular-text" placeholder="(vide = logo Injah intégré)"> <button type="button" class="button ijdl-media" data-type="image">Choisir</button><p class="description">PNG à fond transparent. Laisser vide pour utiliser le logo intégré.</p></td></tr>
				<tr><th>Logo dans l'email</th><td><input type="url" name="settings[mail_logo]" value="<?php echo esc_attr( $s['mail_logo'] ); ?>" class="regular-text"> <button type="button" class="button ijdl-media" data-type="image">Choisir</button></td></tr>
				<tr><th>Nom de l'expéditeur</th><td><input type="text" name="settings[from_name]" value="<?php echo esc_attr( $s['from_name'] ); ?>" class="regular-text"></td></tr>
				<tr><th>Objet de l'email</th><td><input type="text" name="settings[subject]" value="<?php echo esc_attr( $s['subject'] ); ?>" class="large-text" dir="rtl"></td></tr>
				<tr><th>Me notifier à chaque lead</th><td><input type="email" name="settings[notify]" value="<?php echo esc_attr( $s['notify'] ); ?>" class="regular-text" placeholder="vous@exemple.com (optionnel)"></td></tr>
				<tr><th>Petit pied de page</th><td><label><input type="checkbox" name="settings[footer]" value="1" <?php checked( ! empty( $s['footer'] ) ); ?>> Afficher « © Injah Orientation — بياناتك لا تُشارك… »</label></td></tr>
			</table>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>

		<h2>Leads</h2>
		<p><?php echo (int) ( isset( $leads->publish ) ? $leads->publish : 0 ); ?> demande(s) enregistrée(s).
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=injah_lead' ) ); ?>">Voir les leads</a>
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=injah_dl_export' ), 'injah_dl_export' ) ); ?>">Exporter en CSV</a></p>
	</div>
	<script type="text/html" id="ijdl-tpl"><?php echo $tpl; // phpcs:ignore ?></script>
	<script>
	(function(){
		var tb=document.querySelector('#ijdl-table tbody'),n=<?php echo (int) count( $docs ) + 1000; ?>;
		document.getElementById('ijdl-add').onclick=function(){tb.insertAdjacentHTML('beforeend',document.getElementById('ijdl-tpl').innerHTML.split('__i__').join(n++));};
		document.addEventListener('click',function(e){
			var t=e.target,r=t.closest('tr.ijdl-r');
			if(t.classList.contains('ijdl-del')&&r){if(confirm('Supprimer ce document ?'))r.remove();}
			if(t.classList.contains('ijdl-up')&&r&&r.previousElementSibling)r.parentNode.insertBefore(r,r.previousElementSibling);
			if(t.classList.contains('ijdl-down')&&r&&r.nextElementSibling)r.parentNode.insertBefore(r.nextElementSibling,r);
			if(t.classList.contains('ijdl-media')&&window.wp&&wp.media){
				e.preventDefault();var input=t.previousElementSibling;
				var f=wp.media({title:'Choisir un fichier',library:{type:t.getAttribute('data-type')},multiple:false});
				f.on('select',function(){var a=f.state().get('selection').first().toJSON();input.value=a.url;
					if(a.mime==='application/pdf'&&a.filesizeHumanReadable){var sz=r&&r.querySelector('[name$="[size]"]');if(sz)sz.value='PDF · '+a.filesizeHumanReadable.replace(/\s*(KB|MB)/i,' $1');}
				});f.open();
			}
		});
	})();
	</script>
	<?php
}

endif; // INJAH_DL_LOADED
