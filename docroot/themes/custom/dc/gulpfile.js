var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var browserSync = require('browser-sync').create();

var input = './src/scss/**/*.scss';
var output = './dist/css';

gulp.task('sass', function () {
  return gulp.src(input)
    .pipe(sourcemaps.init())
    .pipe(sass({errLogToConsole: true, outputStyle: 'compressed'}))
    .pipe(autoprefixer({browsers: ['last 2 versions', '> 5%', 'Firefox ESR']}))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(output))
    .pipe(browserSync.stream());
});

// Watch files for change and set Browser Sync
gulp.task('watch', function () {
  // BrowserSync settings
  browserSync.init({
    proxy: "dcd8.dev",
    files: "./dist/css/dc.css"
  });

// Scss file watcher
  gulp.watch(input, ['sass'])
    .on('change', function (event) {
      console.log(
        'File' + event.path + ' was ' + event.type + ', running tasks...')
    });
});

// Default task
gulp.task('default', ['sass', 'watch']);