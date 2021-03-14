const Dotenv = require('dotenv-webpack')
const path = require('path')
const merge = require('webpack-merge')

var config = {
  entry: {
    bundle: './scripts/js2/index.js',
    //reader: './reader/index.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist', 'js'),
    filename: '[name].js'
  },
  plugins: [
    new Dotenv({
      path: './.env.local',
      defaults: './.env',
    })
  ]
}

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config = merge(config, {
      mode: 'development',
      devtool: 'source-map',
      output: {
        filename: '[name].dev.js'
      },
      watch: true,
    })
  }
  else if (argv.mode === 'production') {
    config = merge(config, {
      mode: 'production',
      entry: {
        polyfills: ['@babel/polyfill', 'url-polyfill', 'whatwg-fetch', 'eligrey-classlist-js-polyfill', 'polyfill-queryselector', 'formdata-polyfill', './scripts/js2/polyfills.js'],
      },
      output: {
        filename: '[name].prod.js'
      },
      module: {
        rules: [
          {
            test: /\.m?js$/,
            // include: [ path.resolve(__dirname, './reader') ],
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                plugins: [
                  ['@babel/transform-runtime']
                ],
                presets: ['@babel/env'],
              }
            }
          }
        ]
      }
    })
  }

  return config
}