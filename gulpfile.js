var gulp = require( 'gulp' ),
	debug = require( 'gulp-debug' ),
	uglify = require( 'gulp-uglify' ),
	rename = require( 'gulp-rename' );

gulp.task( 'default', function() {
	gulp.src( [ 'assets/**/*.js', '!assets/**/*.min.js' ], { 'basePath': 'assets' } )
		.pipe( debug() )
		.pipe( uglify() )
		.pipe( rename( function( path ) {
			path.extname = '.min' + path.extname;
		} ) )
		.pipe( debug() )
		.pipe( gulp.dest( 'assets' ) );
});