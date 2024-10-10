const path = require('path');

module.exports = {
    entry: './blocks/index.js',
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'blocks.js',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@wordpress/babel-preset-default'],
                    },
                },
            },
        ],
    },
};
