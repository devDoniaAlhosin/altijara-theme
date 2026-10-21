<?php
/**
 * Build theme zip for WordPress Appearance → Themes → Upload.
 */
$theme_dir = __DIR__;
$version   = '2.5.0';
$out_zip   = dirname( $theme_dir ) . '/altijara-theme-' . $version . '.zip';
$root_name = 'altijara-theme';
$skip_dirs = array( '.git', '.cursor', 'node_modules' );

if ( file_exists( $out_zip ) ) {
	unlink( $out_zip );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $out_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
	fwrite( STDERR, "Failed to open zip\n" );
	exit( 1 );
}

$dir = new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS );
$it  = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

foreach ( $it as $file ) {
	$path = $file->getPathname();
	$rel  = substr( $path, strlen( $theme_dir ) + 1 );
	$rel  = str_replace( '\\', '/', $rel );
	$parts = explode( '/', $rel );

	if ( in_array( $parts[0], $skip_dirs, true ) ) {
		continue;
	}
	if ( preg_match( '/^tmp-/i', basename( $rel ) ) ) {
		continue;
	}
	if ( '_build-zip.php' === basename( $rel ) ) {
		continue;
	}
	if ( preg_match( '/\.zip$/i', $rel ) ) {
		continue;
	}

	$local = $root_name . '/' . $rel;
	if ( $file->isDir() ) {
		$zip->addEmptyDir( $local );
	} else {
		$zip->addFile( $path, $local );
	}
}

$zip->close();

$check = new ZipArchive();
$n     = ( true === $check->open( $out_zip ) ) ? $check->numFiles : 0;
if ( $n ) {
	$check->close();
}

echo $out_zip . PHP_EOL;
echo 'bytes=' . filesize( $out_zip ) . PHP_EOL;
echo 'files=' . $n . PHP_EOL;
