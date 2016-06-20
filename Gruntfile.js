module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
      uglify: {
        my_target: {
          files: {
            'js/nosto/iframeHandler.min.js': ['js/nosto-src/iframeHandler.js']
          }
        }
      }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default task(s).
    grunt.registerTask('default', ['uglify']);
};