const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const concat = require('gulp-concat');
const sourcemaps = require('gulp-sourcemaps');
const browserSync = require('browser-sync').create();
const postcss = require('gulp-postcss');
const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');
const webpack = require('webpack-stream');
const named = require('vinyl-named');

class GulpTasks {
    constructor() {
        this.paths = {
            styles: {
                src: ['src/css/**/*.scss', 'src/css/**/*.css'],
                dest: 'dist/css/'
            },
            scripts: {
                src: ['src/js/app.js', 'src/js/main.js'],
                dest: 'dist/js/'
            },
            libs: {
                css: 'node_modules/swiper/swiper-bundle.min.css',
                js: 'node_modules/swiper/swiper-bundle.min.js',
                dest: {
                    css: 'dist/css/libs/',
                    js: 'dist/js/libs/'
                }
            },
            php: '**/*.php'
        };
    }

    serve(done) {
        browserSync.init({
            proxy: "shishupackbd.local", // Change this to your local WordPress URL
            notify: false
        });
        done();
    }

    copyLibs() {
        const css = gulp.src(this.paths.libs.css)
            .pipe(gulp.dest(this.paths.libs.dest.css));
        const js = gulp.src(this.paths.libs.js)
            .pipe(gulp.dest(this.paths.libs.dest.js));
        return Promise.all([css, js]);
    }

    styles() {
        return gulp.src([this.paths.libs.css, ...this.paths.styles.src])
            .pipe(sourcemaps.init())
            .pipe(sass().on('error', sass.logError))
            .pipe(postcss([
                tailwindcss,
                autoprefixer
            ]))
            .pipe(cleanCSS())
            .pipe(concat('tailwind.min.css'))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest(this.paths.styles.dest))
            .pipe(browserSync.stream());
    }

    scripts() {
        return gulp.src(this.paths.scripts.src)
            .pipe(named())
            .pipe(webpack({
                mode: 'production',
                module: {
                    rules: [
                        {
                            test: /\.js$/,
                            exclude: /node_modules/,
                            use: {
                                loader: 'babel-loader',
                                options: {
                                    presets: ['@babel/preset-env'],
                                    plugins: ['@babel/plugin-transform-modules-commonjs']
                                }
                            }
                        },
                        {
                            test: /\.css$/,
                            use: ['style-loader', 'css-loader']
                        },
                        {
                            test: /\.(woff|woff2|eot|ttf|otf)$/,
                            type: 'asset/resource'
                        }
                    ]
                },
                output: {
                    filename: '[name].bundle.js'
                }
            }))
            .pipe(gulp.dest(this.paths.scripts.dest))
            .pipe(browserSync.stream());
    }

    watch() {
        gulp.watch(this.paths.styles.src, (cb) => { this.styles(); cb(); });
        gulp.watch(this.paths.scripts.src, (cb) => { this.scripts(); cb(); });
        gulp.watch(this.paths.php).on('change', browserSync.reload);
    }
}

const tasks = new GulpTasks();

const build = gulp.series(
    tasks.copyLibs.bind(tasks),
    gulp.parallel(tasks.styles.bind(tasks), tasks.scripts.bind(tasks))
);
const dev = gulp.series(build, tasks.serve.bind(tasks), tasks.watch.bind(tasks));

exports.styles = tasks.styles.bind(tasks);
exports.scripts = tasks.scripts.bind(tasks);
exports.watch = tasks.watch.bind(tasks);
exports.build = build;
exports.default = dev;