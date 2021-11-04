module.exports = {
  pluginOptions: {
    moment: {
      locales: [
        'en'
      ]
    }
  },
  devServer: {
    proxy: {
      '^/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      },
    }
  }
}
