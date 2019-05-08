/*
 * gulp -> minify assets
 * gulp release -> create package for WP repo
 * //TODO: integrate webpack build
 */

var gulp = require( 'gulp' ),
	debug = require( 'gulp-debug' ),
	uglify = require( 'gulp-uglify' ),
	rename = require( 'gulp-rename' );

gulp.task( 'default', function() {
	return gulp.src( [ 'assets/**/*.js', '!assets/**/*.min.js' ], { 'basePath': 'assets' } )
		.pipe( debug() )
		.pipe( uglify() )
		.pipe( rename( function( path ) {
			path.extname = '.min' + path.extname;
		} ) )
		.pipe( debug() )
		.pipe( gulp.dest( 'assets' ) );
});

gulp.task( 'release', function() {
	return gulp.src( [
		'assets/**/*.min.js',
		'Controllers/**/*',
		'lang/**/*',
		'Config.php',
		'ProviderInterface.php',
		'readme.txt',
		'secure-post-with-link.php',
		'SingletonTrait.php'
	], { 'base' : '.' } )
		.pipe( debug() )
		.pipe( gulp.dest( 'wp_repo_package' ) ); //TODO: files are not in subfolders
} );