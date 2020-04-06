/*
 * Made Blog
 * Copyright (c) 2019-2020 Made
 * Copyright (c) 2020 GameplayJDK
 *   https://github.com/GameplayJDK/gulpfile
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

'use strict';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Follow https://gulpjs.com/docs/en/getting-started/quick-start for first steps with gulp.
 * See https://github.com/gulpjs/gulp/blob/master/docs/recipes/delete-files-folder.md for the clean workflow setup.
 * See https://goede.site/setting-up-gulp-4-for-automatic-sass-compilation-and-css-injection for the style workflow setup.
 * See https://www.toptal.com/javascript/optimize-js-and-css-with-gulp and https://stackoverflow.com/a/24597914 for the
 * script workflow setup.
 * See https://medium.freecodecamp.org/how-to-minify-images-with-gulp-gulp-imagemin-and-boost-your-sites-performance-6c226046e08e
 * for the image workflow setup.
 * See https://github.com/mahnunchik/gulp-responsive/blob/HEAD/examples/multiple-resolutions.md and https://stackoverflow.com/a/37459616
 * and https://getbootstrap.com/docs/4.0/layout/grid/#grid-options for the responsive image workflow setup.
 * See https://github.com/gulpjs/gulp/blob/master/docs/recipes/pass-arguments-from-cli.md for cli argv setup.
 */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const gulp = require("gulp");
const gulp_sass = require("gulp-sass");
const gulp_postcss = require("gulp-postcss");
const gulp_sourcemaps = require("gulp-sourcemaps");
const gulp_concat = require('gulp-concat');
const gulp_minify = require('gulp-minify');
const gulp_imagemin = require('gulp-imagemin');
const gulp_responsive = require('gulp-responsive');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const del = require('del');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const postcss_autoprefixer = require("autoprefixer");
const postcss_cssnano = require("cssnano");

const minimist = require('minimist');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const cp_cwd = require('./cp_cwd.js');

var cwd = cp_cwd();
cwd = (function (cwd) {
    return cwd.trim();
}(cwd));

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var option = minimist(process.argv.slice(2), {
    string: [
        'env',
    ],
    default: {
        'env': (process.env.NODE_ENV || 'dev')
    },
});
option = (function (option) {
    // Set default if env is invalid.
    if (![
        'dev',
        'prod',
    ].includes(option.env)) {
        option.env = 'dev';
    }

    return option;
}(option));

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const path = {
    style: {
        // Additional scss dependencies are added through @import statements.
        src: './asset/style/main.scss',
        dest: '../../public/asset/style',
        map: './map',
        // Delete this when cleaning up.
        del: '../../public/asset/style/**',
    },
    script: {
        // Additional javascript dependencies are prepended to the final js.
        add: [
            './node_modules/jquery/dist/jquery.js',
            './node_modules/popper.js/dist/umd/popper.js',
            './node_modules/bootstrap/dist/js/bootstrap.js',
            './node_modules/lazysizes/lazysizes.js',
            './node_modules/lazysizes/plugins/bgset/ls.bgset.js',
            // Prepend module function definition.
            './node_modules/small-module-js/src/main.js',
        ],
        src: './asset/script/module/*.js',
        dest: '../../public/asset/script',
        map: './map',
        // The names for each step.
        rename: {
            // The normal js file.
            concat: 'main.js',
            // The options for the minification.
            src: '.js',
            min: '.min.js',
        },
        // Delete this when cleaning up.
        del: '../../public/asset/script/**',
    },
    font: {
        add: [
            './node_modules/@fortawesome/fontawesome-free/webfonts/*',
        ],
        src: './asset/font/**/*',
        dest: '../../public/asset/font/',
        del: '../../public/asset/font/**',
    },
    image: {
        src: './asset/image/**/*.{png,jpg,jpeg,gif,svg}',
        dest: '../../public/asset/image',
        // Delete this when cleaning up.
        del: '../../public/asset/image/**',
        // The responsive configuration, which is different from the normal one.
        responsive: {
            src: [
                './asset/image/**/*.{png,jpg}',
                '!./asset/image/responsive',
            ],
            dest: './asset/image/responsive',
            del: './asset/image/responsive/**',
            config: {
                '**/*.png': [
                    {
                        width: 540,
                        rename: {
                            suffix: '-540',
                        },
                    },
                    {
                        width: 720,
                        rename: {
                            suffix: '-720',
                        },
                    },
                    {
                        width: 960,
                        rename: {
                            suffix: '-960',
                        },
                    },
                    {
                        width: 1140,
                        rename: {
                            suffix: '-1140',
                        },
                    },
                    {
                        rename: {
                            suffix: '-original',
                        },
                    },
                ],
                '**/*.jpg': [
                    {
                        width: 540,
                        rename: {
                            suffix: '-540',
                        },
                    },
                    {
                        width: 720,
                        rename: {
                            suffix: '-720',
                        },
                    },
                    {
                        width: 960,
                        rename: {
                            suffix: '-960',
                        },
                    },
                    {
                        width: 1140,
                        rename: {
                            suffix: '-1140',
                        },
                    },
                    {
                        rename: {
                            suffix: '-original',
                        },
                    },
                ],
            },
        },
    },
};

const delOverridePath = '../../public/asset';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const postcssPlugin = (function (option) {
    var plugin = [
        postcss_autoprefixer(),
    ];

    // Only include postcss_cssnano in prod.
    if (option.env === 'prod') {
        plugin.push(
            postcss_cssnano()
        );
    }

    return plugin;
}(option));

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cleanStyleTask() {
    return (
        del(path.style.del, {
            force: 0 === path.style.del.indexOf(delOverridePath),
            cwd: cwd,
        })
    );
}

function compileStyleTask() {
    return (
        gulp
            .src(path.style.src, {
                cwd: cwd,
                // follow: true,
            })
            .pipe(
                gulp_sourcemaps.init()
            )
            .pipe(
                gulp_sass()
            )
            .on('error', gulp_sass.logError)
            .pipe(
                gulp_postcss(postcssPlugin)
            )
            .pipe(
                gulp_sourcemaps.write(path.style.map)
            )
            .pipe(
                gulp.dest(path.style.dest, {
                    cwd: cwd,
                })
            )
    );
}

function cleanCompileStyleTask() {
    return (
        gulp
            .series([
                cleanStyleTask,
                compileStyleTask,
            ])
    );
}

function watchStyleTask() {
    function fixPath(path) {
        var segment = path.split('/');
        var name = segment.pop();

        if ('main.scss' === name) {
            segment.push('**', '*.scss');
        } else {
            segment.push(name);
        }

        return segment.join('/');
    }

    function watchStyleTask() {
        var src = fixPath(path.style.src);

        return (
            gulp
                .watch(src, compileStyleTask, {
                    cwd: cwd,
                })
        );
    }

    return (
        gulp
            .series([
                compileStyleTask,
                watchStyleTask,
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cleanScriptTask() {
    return (
        del(path.script.del, {
            force: 0 === path.script.del.indexOf(delOverridePath),
            cwd: cwd,
        })
    );
}

function compileScriptTask() {
    return (
        gulp
            .src(function () {
                var src = path.script.add;

                src.push(path.script.src);

                return src;
            }(), {
                cwd: cwd,
                // follow: true,
            })
            .pipe(
                gulp_sourcemaps.init()
            )
            .pipe(
                gulp_concat(path.script.rename.concat)
            )
            .pipe(
                gulp_minify({
                    ext: path.script.rename,
                })
            )
            .pipe(
                gulp_sourcemaps.write(path.script.map)
            )
            .pipe(
                gulp.dest(path.script.dest, {
                    cwd: cwd,
                })
            )
    );
}

function cleanCompileScriptTask() {
    return (
        gulp
            .series([
                cleanScriptTask,
                compileScriptTask,
            ])
    );
}

function watchScriptTask() {
    function watchScriptTask() {
        return (
            gulp
                .watch(path.script.src, compileScriptTask, {
                    cwd: cwd,
                })
        );
    }

    return (
        gulp
            .series([
                compileScriptTask,
                watchScriptTask,
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cleanFontTask() {
    return (
        del(path.font.del, {
            force: 0 === path.font.del.indexOf(delOverridePath),
            cwd: cwd,
        })
    );
}

function compileFontTask() {
    return (
        gulp
            .src(function () {
                var src = path.font.add;

                src.push(path.font.src);

                return src;
            }(), {
                cwd: cwd,
                // follow: true,
            })
            .pipe(
                gulp.dest(path.font.dest, {
                    cwd: cwd,
                })
            )
    );
}

function cleanCompileFontTask() {

    return (
        gulp
            .series([
                cleanFontTask,
                compileFontTask,
            ])
    );
}

function watchFontTask() {
    function watchFontTask() {
        return (
            gulp
                .watch(path.font.src, compileFontTask, {
                    cwd: cwd,
                })
        );
    }

    return (
        gulp
            .series([
                compileFontTask,
                watchFontTask,
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cleanImageTask() {
    return (
        del(path.image.del, {
            force: 0 === path.image.del.indexOf(delOverridePath),
            cwd: cwd,
        })
    );
}

function compileImageTask() {
    return (
        gulp
            .src(path.image.src, {
                cwd: cwd,
                // follow: true,
            })
            .pipe(
                gulp_imagemin()
            )
            .pipe(
                gulp.dest(path.image.dest, {
                    cwd: cwd,
                })
            )
    );
}

function cleanCompileImageTask() {
    return (
        gulp
            .series([
                cleanImageTask,
                compileImageTask,
            ])
    );
}

function watchImageTask() {
    function watchImageTask() {
        return (
            gulp
                .watch(path.image.src, compileImageTask, {
                    cwd: cwd,
                })
        );
    }

    return (
        gulp
            .series([
                compileImageTask,
                watchImageTask,
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function responsiveImageTask() {
    function cleanImageTask() {
        return del(path.image.responsive.del, {
            cwd: cwd,
        });
    }

    function compileImageTask() {
        return (
            gulp
                .src(path.image.responsive.src, {
                    cwd: cwd,
                    // follow: true,
                })
                .pipe(
                    gulp_responsive(path.image.responsive.config, {
                        quality: 70,
                        progressive: true,

                        withMetadata: false,

                        // skipOnEnlargement: true,

                        errorOnUnusedConfig: false,
                        errorOnUnusedImage: false,
                        errorOnEnlargement: false
                    })
                )
                .pipe(
                    gulp.dest(path.image.responsive.dest, {
                        cwd: cwd,
                    })
                )
        );
    }

    return (
        gulp
            .series([
                cleanImageTask,
                compileImageTask,
            ])
    )
}

function responsiveCompileImageTask() {
    return (
        gulp
            .series([
                responsiveImageTask(),
                compileImageTask,
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cleanDefaultTask() {
    return (
        gulp
            .parallel([
                cleanStyleTask,
                cleanScriptTask,
                cleanFontTask,
                cleanImageTask,
            ])
    );
}

function compileDefaultTask() {
    return (
        gulp
            .parallel([
                compileStyleTask,
                compileScriptTask,
                compileFontTask,
                compileImageTask,
            ])
    );
}

function cleanCompileDefaultTask() {
    return (
        gulp
            .series([
                cleanDefaultTask(),
                compileDefaultTask(),
            ])
    );
}

function watchDefaultTask() {
    return (
        gulp
            .parallel([
                watchStyleTask(),
                watchScriptTask(),
                watchFontTask(),
                watchImageTask(),
            ])
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

gulp.task('default', cleanCompileDefaultTask());
gulp.task('default:clean', cleanDefaultTask());
gulp.task('default:compile', compileDefaultTask());
gulp.task('default:watch', watchDefaultTask());

gulp.task('style', cleanCompileStyleTask());
gulp.task('style:clean', cleanStyleTask);
gulp.task('style:compile', compileStyleTask);
gulp.task('style:watch', watchStyleTask());

gulp.task('script', cleanCompileScriptTask());
gulp.task('script:clean', cleanScriptTask);
gulp.task('script:compile', compileScriptTask);
gulp.task('script:watch', watchScriptTask());

gulp.task('font', cleanCompileFontTask());
gulp.task('font:clean', cleanFontTask);
gulp.task('font:compile', compileFontTask);
gulp.task('font:watch', watchFontTask());

gulp.task('image', cleanCompileImageTask());
gulp.task('image:clean', cleanImageTask);
gulp.task('image:compile', compileImageTask);
gulp.task('image:watch', watchImageTask());

gulp.task('image:responsive', responsiveImageTask());
gulp.task('image:responsive-compile', responsiveCompileImageTask());
