const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'admin-ui-gutenberg': './assets/admin-ui-gutenberg.js'
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve( process.cwd(), 'assets' ),
    }
};