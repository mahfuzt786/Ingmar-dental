const BundleTracker = require("webpack-bundle-tracker");

module.exports = {
  "transpileDependencies": [
    "vuetify"
  ],
    // baseUrl: "/surojit/dist/",
    publicPath: process.env.NODE_ENV === 'production'
                // ? '/surojit/dist/'
                // ? '/dist/'
                ? '/surojit/dist/'
                :'/surojit/dist/',
  outputDir: './dist/',
  runtimeCompiler: true,

  chainWebpack: config => {

    config.optimization
        .splitChunks(false)

    config
        .plugin('BundleTracker')
        .use(BundleTracker, [{filename: '../chairsight/webpack-stats.json'}])

    config.resolve.alias
        .set('__STATIC__', 'static')

    config.devServer
        .public('http://127.0.0.1:8080')
        .host('0.0.0.0')
        .port(8080)
        .hotOnly(true)
        .watchOptions({poll: 1000})
        .https(false)
        .headers({"Access-Control-Allow-Origin": "*"})
  },
}