/*gruntfile.js*/

module.exports = function (grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        watch: {
            browserify: {
                files: ['js/**/*.jsx'],
                tasks: ['browserify', 'uglify']
            }
        },

        browserify: {
            dist: {
                options: {
                    transform: [['babelify', {presets: ['es2015', 'react']}]]
                },
                files: {
                    'js/view.js': 'js/**/*.jsx'
                }
            }
        },

        uglify: {
            dist: {
                options: {
                    sourceMap: true,
                    sourceMapName: 'js/view.map'
                },
                files: {
                    'js/view.min.js': ['js/view.js']
                }
            }
        }

    });

    grunt.file.defaultEncoding = 'utf8';
    grunt.file.preserveBOM = false;

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browserify');

    grunt.registerTask('default', ['watch']);

};
