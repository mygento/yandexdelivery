var gulp = require('gulp');
var sass = require('gulp-sass');
var cssnano = require('gulp-cssnano');
var autoprefixer = require('gulp-autoprefixer');
var uglify = require('gulp-uglify');

gulp.task('css', function () {
    return gulp.src('./skin/frontend/base/default/_scss/yandexdelivery*.scss')
            .pipe(sass().on('error', sass.logError))
            .pipe(cssnano({autoprefixer: {add: true, browsers: ['last 4 versions']}, zindex: false}))
            .pipe(gulp.dest('./skin/frontend/base/default/css/mygento/'))
            .pipe(gulp.dest('./skin/adminhtml/default/default/css/mygento/'));
});

gulp.task('default', ['compile']);

gulp.task('js', function () {
    return gulp.src('./skin/frontend/base/default/_js/yandexdelivery.js')
            .pipe(uglify())
            .pipe(gulp.dest('./skin/frontend/base/default/js/mygento/'))
            .pipe(gulp.dest('./skin/adminhtml/default/default/js/mygento/'));
});

gulp.task('compile', ['css', 'js'], function () {
});