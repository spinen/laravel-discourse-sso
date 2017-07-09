var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var notify = require('gulp-notify')

var paths = {
  tests: ['tests/**/*Test.php'],
};

// Lint the shell code
gulp.task('phpunit', function () {
  return gulp.src(paths.tests, {read: false})
    .pipe(phpunit())
    .pipe(notify(function (file) {
        // TODO: Give alert of pass/fail
        return file.path
    }))
})

// Rerun the task when a file changes
gulp.task('watch', function() {
  gulp.watch(paths.tests, ['phpunit']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['phpunit']);
