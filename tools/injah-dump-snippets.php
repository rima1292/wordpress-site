<?php
/**
 * Injah — READ-ONLY dump of Code Snippets data.
 *
 * Purpose: let the operator download every snippet as a single file,
 * without touching the database and without loading WordPress.
 *
 * USAGE
 *   1. Upload this file into the WordPress root (same folder as wp-config.php).
 *   2. Open: /injah-dump-snippets.php?key=<YOUR_KEY>
 *        -> human readable listing + metadata
 *      Open: /injah-dump-snippets.php?key=<YOUR_KEY>&format=json
 *        -> downloads injah-snippets-YYYYMMDD-HHMM.json (full code included)
 *   3. Delete the file afterwards (or hit &selfdestruct=1 at the very end).
 *
 * SAFETY
 *   - Only SELECT / DESCRIBE / SHOW. No INSERT, UPDATE or DELETE.
 *   - Does not load wp-load.php (a broken snippet would fatal the request).
 *   - No code is executed: snippet bodies are only read as strings.
 */

header( 'Content-Type: text/plain; charset=utf-8' );
@ini_set( 'display_errors', '1' );
error_reporting( E_ALL );

/* -------------------------------------------------------------------------
 * 1. Access key — CHANGE THIS before uploading.
 * ---------------------------------------------------------------------- */
const INJAH_KEY = 'injah-dump-2026-11-17';

if ( ! isset( $_GET['key'] ) || ! hash_equals( INJAH_KEY, (string) $_GET['key'] ) ) {
	http_response_code( 403 );
	exit( "forbidden\n" );
}

$format = isset( $_GET['format'] ) ? strtolower( (string) $_GET['format'] ) : 'text';
if ( 'json' !== $format ) {
	$format = 'text';
}

/* -------------------------------------------------------------------------
 * 2. Read DB credentials straight from wp-config.php
 * ---------------------------------------------------------------------- */
function injah_cfg_const( string $raw, string $name ): string {
	$pattern = "/define\s*\(\s*['\"]" . preg_quote( $name, '/' ) . "['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/";
	return preg_match( $pattern, $raw, $m ) ? $m[1] : '';
}

$cfg_file = __DIR__ . '/wp-config.php';
if ( ! is_readable( $cfg_file ) ) {
	http_response_code( 500 );
	exit( "no wp-config.php in " . __DIR__ . "\n" );
}

$raw = (string) file_get_contents( $cfg_file );

$db_name = injah_cfg_const( $raw, 'DB_NAME' );
$db_user = injah_cfg_const( $raw, 'DB_USER' );
$db_pass = injah_cfg_const( $raw, 'DB_PASSWORD' );
$db_host = injah_cfg_const( $raw, 'DB_HOST' );

$prefix = 'wp_';
if ( preg_match( "/\\\$table_prefix\s*=\s*['\"]([^'\"]+)['\"]/", $raw, $m ) ) {
	$prefix = $m[1];
}

/* -------------------------------------------------------------------------
 * 3. Connect
 * ---------------------------------------------------------------------- */
mysqli_report( MYSQLI_REPORT_OFF );
$db = @new mysqli( $db_host, $db_user, $db_pass, $db_name );
if ( $db->connect_errno ) {
	http_response_code( 500 );
	exit( 'db_connect_fail #' . $db->connect_errno . "\n" );
}
$db->set_charset( 'utf8mb4' );

/* -------------------------------------------------------------------------
 * 4. Discover every table that looks like a snippets table
 *    (wp_snippets, wp_ms_snippets, wp_2_snippets, ...)
 * ---------------------------------------------------------------------- */
$tables = array();
$like   = $db->real_escape_string( '%snippets%' );
$res    = $db->query( "SHOW TABLES LIKE '{$like}'" );
if ( $res ) {
	while ( $row = $res->fetch_row() ) {
		$name = (string) $row[0];
		// keep only real snippet tables, skip e.g. wp_snippets_meta_backup
		if ( preg_match( '/(^|_)snippets$/', $name ) ) {
			$tables[] = $name;
		}
	}
}
if ( ! $tables ) {
	exit( "no snippets table found (prefix={$prefix})\n" );
}

/* -------------------------------------------------------------------------
 * 5. Read every snippet from every table
 * ---------------------------------------------------------------------- */
$export = array(
	'generated_at' => gmdate( 'c' ),
	'db_name'      => $db_name,
	'table_prefix' => $prefix,
	'tables'       => array(),
);

foreach ( $tables as $table ) {
	$cols = array();
	if ( $cr = $db->query( "DESCRIBE `{$table}`" ) ) {
		while ( $row = $cr->fetch_assoc() ) {
			$cols[] = $row['Field'];
		}
	}
	if ( ! $cols ) {
		continue;
	}

	$order = in_array( 'id', $cols, true ) ? 'id ASC' : '1';
	$rows  = array();
	$q     = $db->query( "SELECT * FROM `{$table}` ORDER BY {$order}" );
	if ( $q ) {
		while ( $row = $q->fetch_assoc() ) {
			$rows[] = $row;
		}
	}

	$export['tables'][] = array(
		'table'       => $table,
		'columns'     => $cols,
		'row_count'   => count( $rows ),
		'snippets'    => $rows,
	);
}

/* -------------------------------------------------------------------------
 * 6. Optional: list flat-file storage (Code Snippets >= 3.8 file execution)
 * ---------------------------------------------------------------------- */
$flat_root = __DIR__ . '/wp-content/code-snippets';
$flat      = array(
	'path'    => $flat_root,
	'exists'  => is_dir( $flat_root ),
	'enabled' => is_file( $flat_root . '/flat-files-enabled.flag' ),
	'files'   => array(),
);

if ( $flat['exists'] ) {
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $flat_root, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ( $it as $file ) {
		$rel = ltrim( str_replace( $flat_root, '', $file->getPathname() ), '/' );
		$flat['files'][] = $rel . ( $file->isDir() ? '/' : '' );
		if ( count( $flat['files'] ) > 500 ) {
			$flat['files'][] = '... (truncated)';
			break;
		}
	}
}
$export['flat_files'] = $flat;

/* -------------------------------------------------------------------------
 * 7. Output
 * ---------------------------------------------------------------------- */
if ( 'json' === $format ) {
	$filename = 'injah-snippets-' . gmdate( 'Ymd-His' ) . '.json';
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	echo json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( isset( $_GET['selfdestruct'] ) && '1' === (string) $_GET['selfdestruct'] ) {
		@unlink( __FILE__ );
	}
	exit;
}

/* --- human readable ---------------------------------------------------- */
echo "generated_at={$export['generated_at']}\n";
echo "db={$db_name} prefix={$prefix}\n";
echo 'tables=' . implode( ',', $tables ) . "\n";
echo "flat_files_exists=" . ( $flat['exists'] ? '1' : '0' )
	. " flat_files_enabled=" . ( $flat['enabled'] ? '1' : '0' ) . "\n";
echo str_repeat( '-', 72 ) . "\n";

foreach ( $export['tables'] as $block ) {
	echo "\n### TABLE {$block['table']} — {$block['row_count']} snippet(s)\n";
	echo 'columns=' . implode( ',', $block['columns'] ) . "\n\n";

	foreach ( $block['snippets'] as $s ) {
		$id      = $s['id'] ?? '?';
		$name    = $s['name'] ?? '(sans nom)';
		$active  = isset( $s['active'] ) ? (string) $s['active'] : '?';
		$scope   = $s['scope'] ?? '-';
		$prio    = $s['priority'] ?? '-';
		$mod     = $s['modified'] ?? '-';
		$len     = isset( $s['code'] ) ? strlen( (string) $s['code'] ) : 0;
		$has_nav = isset( $s['code'] ) && false !== stripos( (string) $s['code'], 'mobile-nav' );

		echo "id={$id} active={$active} scope={$scope} prio={$prio} bytes={$len}\n";
		echo "  name={$name}\n";
		echo "  modified={$mod}" . ( $has_nav ? '  <<< CONTIENT mobile-nav' : '' ) . "\n";
	}
}

if ( $flat['exists'] ) {
	echo "\n### FLAT FILES ({$flat['path']})\n";
	foreach ( array_slice( $flat['files'], 0, 100 ) as $f ) {
		echo "  {$f}\n";
	}
}

echo "\n" . str_repeat( '-', 72 ) . "\n";
echo "Pour le fichier complet (avec le code) :\n";
echo "  " . basename( __FILE__ ) . "?key=" . INJAH_KEY . "&format=json\n";
echo "Puis supprimez ce script du serveur.\n";
