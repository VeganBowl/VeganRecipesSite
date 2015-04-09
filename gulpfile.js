var
    gulp             = require('gulp'),
    assets           = require('elao-assets-gulp')({'dest': 'build/assets'});

// Assets - Layouts
assets
    .addLayout('npm')
    .addLayout('bower')
    .addLayout('components')
    .addLayout('symfony');

// Assets - Plugins
assets
    .addPlugin('list')
    .addPlugin('clean')
    .addPlugin('fonts', 'copy', {dir: 'fonts'})
    .addPlugin('images')
    .addPlugin('sass')
    .addPlugin('browserify');

// Assets - Pools
assets
    .addPoolPattern('font-awesome', {
        'fonts': {src: 'font-awesome/fonts/**', dest: 'font-awesome'}
    });

// Gulp - Tasks
gulp.task('list',   assets.plugins.list.gulpTask);
gulp.task('clean',  assets.plugins.clean.gulpTask);

gulp.task('install', ['fonts', 'images', 'sass', 'js']);
gulp.task('fonts',  assets.plugins.fonts.gulpTask);
gulp.task('images', assets.plugins.images.gulpTask);
gulp.task('sass',   assets.plugins.sass.gulpTask);
gulp.task('js',     assets.plugins.browserify.gulpTask);

gulp.task('watch', ['watch:sass', 'watch:js']);
gulp.task('watch:sass', assets.plugins.sass.gulpWatch);
gulp.task('watch:js',   assets.plugins.browserify.gulpWatch);

gulp.task('default', ['install', 'watch']);
