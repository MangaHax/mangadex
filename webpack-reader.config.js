const Dotenv = require('dotenv-webpack')
const path = require('path')
const merge = require('webpack-merge')

var config = {
  entry: {
    reader: './scripts/reader/index.js',
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
      output: {
        filename: '[name].prod.js'
      },
      module: {
        rules: [
          {
            test: /\.m?js$/,
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