var gulp = require('gulp');
var sass = require('gulp-sass');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var path = require('path');
var tap = require("gulp-tap")
var autoprefixer = require('gulp-autoprefixer');
var cleanCSS = require('gulp-clean-css');

var replace_src = function(folder){
    return 'assets'+path.sep+folder
}
var replace_dist = function(folder){
    return path.sep+'dist'+path.sep+folder
}

gulp.task('files.scss', () => {
    return gulp.src(['./**/assets/_css/**/*.scss','!./**/assets/_css/**/*.scss/*/**'],{ nodir: true, base: '.' })
        .pipe(sass().on('error', sass.logError))
        .pipe(rename(function(file) {
            file.dirname = file.dirname.replace('assets'+path.sep+'_css', 'assets'+path.sep+'css');
            console.log(" > "+file.dirname+path.sep+file.basename+file.extname)
        }))
        .pipe(gulp.dest("."));
});


gulp.task('folders.scss', () => {
    return gulp.src('./**/assets/_css/**/*.scss/index.scss',{base: '.' })
        .pipe(sass().on('error', sass.logError))
        .pipe(rename(function(file) {
            folder = file.dirname.replace('assets'+path.sep+'_css','assets'+path.sep+'css').split(path.sep)
            name = folder.pop()
            bname = name.split(".")
            bname.pop()
            bname = bname.join(".")
            folder = folder.join(path.sep)
            file.dirname = folder
            file.basename = bname
            console.log(" > "+file.dirname+path.sep+file.basename+file.extname)
          }))
        // .pipe(autoprefixer({
        //     cascade: false
        // }))
        // .pipe(cleanCSS())
        .pipe(gulp.dest("."));
});

gulp.task('files.js', () => {
    return gulp.src(['./**/assets/_js/**/*.js','!./**/assets/_js/**/*.js/*/**'],{ nodir: true, base: '.' })
        
        .pipe(rename(function(file) {
            file.dirname = file.dirname.replace('assets'+path.sep+'_js', 'assets'+path.sep+'js');
            console.log(" > "+file.dirname+path.sep+file.basename+file.extname)
        }))
        .pipe(gulp.dest("."));
});

gulp.task('folders.js', () => {
    return gulp.src('./**/assets/_js/**/*.js/index.js',{base: '.' })
        .pipe(tap(function(file, t) {
            files_to_concat = JSON.parse(file.contents.toString())

            folder = file.dirname
            outputFilename = folder.split(path.sep).pop()
            outputFolder = folder.replace('assets'+path.sep+'_js', 'assets'+path.sep+'js')
            outputFolder = outputFolder.split(path.sep)
            outputFolder.pop()
            outputFolder = outputFolder.join(path.sep)


            var orgiCWD = process.cwd();
            try {
                process.chdir(folder);
            } catch (err) {
               console.log('chdir: ' + err);
            }


            var files_to_concat_with_path = files_to_concat.map(function(e) { 
                ret = e
                resolve = require('path').resolve
                ret = resolve(ret)


                return ret;
            });
            try {
                process.chdir(orgiCWD);
            } catch (err) {
               console.log('chdir: ' + err);
            }


            return gulp.src(files_to_concat_with_path,{base: folder })
                .pipe(concat(outputFilename,{newLine: '\n\r;/*********************************/;\n\r'}))
                .pipe(rename(function(file) {
                    file.dirname = outputFolder
                    console.log(" > "+file.dirname+path.sep+file.basename+file.extname);
                }))
                .pipe(gulp.dest("."));
                
        }))
       
});

gulp.task('fonts', () => {
    return gulp.src('./node_modules/@fortawesome/fontawesome-free/webfonts/*')
        .pipe(gulp.dest("./assets/fonts/"));
});




gulp.task('build', gulp.parallel('files.scss','folders.scss','files.js','folders.js','fonts'));

// TODO: the files task runs for the folders tasks aswell
// TODO: only run for each file changed

gulp.task('watch', () => {
    gulp.watch(['./**/assets/_css/**/*.scss','!./**/assets/_css/**/*.scss/*/**'],gulp.series(['files.scss']));
    gulp.watch('./**/assets/_css/**/*.scss/**/*.scss',gulp.series(['folders.scss']));
    gulp.watch(['./**/assets/_js/**/*.js','!./**/assets/_js/*.js/*/**'],gulp.series(['files.js']));
    gulp.watch('./**/assets/_js/**/*.js/**/*.js',gulp.series(['folders.js']));
});



