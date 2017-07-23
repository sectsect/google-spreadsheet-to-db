var gulp			     = require('gulp');
var cssmin			     = require('gulp-cssmin');
var spritesmith		     = require('gulp.spritesmith');
var buffer               = require('vinyl-buffer');
var sass			     = require('gulp-sass');
var autoprefixer	     = require('gulp-autoprefixer');
var mmq                  = require('gulp-merge-media-queries');
var plumber			     = require("gulp-plumber");
var notify			     = require('gulp-notify');
var imagemin		     = require('gulp-imagemin');
var pngquant		     = require('imagemin-pngquant');
var csscomb			     = require('gulp-csscomb');
var prettify		     = require('gulp-jsbeautifier');
var svgmin               = require('gulp-svgmin');
var iconfontCss          = require('gulp-iconfont-css');
var iconfont             = require('gulp-iconfont');

/*==================================================
	sprite smith
================================================== */
gulp.task('sprite-icon', function () {
	var spriteData = gulp.src('images/sprites/icon/*.png')
	.pipe(spritesmith({
		imgPath			: '../images/sprites/icon.png',
		imgName			: 'icon.png',
		retinaSrcFilter	: 'images/sprites/icon/*-2x.{png,jpg}',
		retinaImgName   : 'icon-2x.png',
		retinaImgPath	: '../images/sprites/icon-2x.png',
		algorithm		: 'binary-tree',
		padding			: 5,
		cssName         : '_sprite.scss'
	}));
	spriteData.img
	.pipe(plumber())
	.pipe(buffer())
//	.pipe(imagemin())
	.pipe(imagemin({
		progressive: true,
		svgoPlugins: [{removeViewBox: false}],
		use: [pngquant()]
	}))
	.pipe(gulp.dest('images/sprites/'));
	spriteData.css
	.pipe(plumber())
	.pipe(gulp.dest('sass/'));

});
/*==================================================
	icon-font	@ http://qiita.com/MAL09/items/1b383fbb62e241ed6e1b
================================================== */
gulp.task('icon-font', function (callback) {
	var svgminData = gulp.src('./fonts/svg/*.svg')
	.pipe(svgmin());									// minify the svg

	svgminData.pipe(plumber())
	.pipe(iconfontCss({									// Create a scss file
		fontName  : 'iconfont',
		path      : './sass/_icon-font-template.scss',	// Path to template file
		targetPath: '../sass/_icon-font.scss',			// Path to created scss file
		fontPath  : '../fonts/'							// Path to font file from scss
	}))
	.pipe(iconfont({									// Create a font
		fontName        : 'iconfont',
		formats         : ['ttf', 'eot', 'woff', 'woff2', 'svg'],
		appendCodepoints:false
	}))
	.pipe(gulp.dest('./fonts'))							// Output the font
	.on('end', function(){
		callback();
	});
});
/*==================================================
	image minify
================================================== */
gulp.task('image-min', function () {
	gulp.src(['./images/*.+(jpg|jpeg|png|gif|svg)', '!./images/sprites/*.+(jpg|jpeg|png|gif|svg)'])
//	.pipe(imagemin())
	.pipe(imagemin({
		progressive: true,
		svgoPlugins: [{removeViewBox: false}],
		use: [pngquant()]
	}))
	.pipe(gulp.dest("./images"));
});
/*==================================================
	JS format
================================================== */
gulp.task('format-js', function() {
	gulp.src(["./js/*.js", "!./js/*.min.js", "./js/**/*.js", "!./js/**/*.min.js"])
	.pipe(plumber())
	.pipe(prettify({mode: 'VERIFY_AND_WRITE', indentWithTabs: true, maxPreserveNewlines: 1}))
	.pipe(gulp.dest('./js'));
});
/*==================================================
	sass
================================================== */
gulp.task("sass", function() {
	gulp.src("./sass/*scss")
	.pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
	.pipe(sass({outputStyle: 'compressed'}))
	// .pipe(pleeease({
	//     sass: true,
	// 	fallbacks: {
	//         autoprefixer: ['last 2 versions', "ie 8", "ie 9", 'android 2.3']
	//     },
	// 	minifier: false,
	// 	mqpacker: true
	// }))
	.pipe(csscomb())
	.pipe(autoprefixer("last 2 version", "ie 8", "ie 9", 'android 4'))
	.pipe(mmq())
	.pipe(gulp.dest("./css"))
	.pipe(cssmin())
	.pipe(gulp.dest("./css"));
});
/*==================================================
	watch
================================================== */
gulp.task("default", ['sass'], function() {
	gulp.watch("./sass/*.scss",["sass"]);
});
