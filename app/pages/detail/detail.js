//获取应用实例
var app = getApp()
var WxParse = require('../../vender/wxParse/wxParse.js');
Page({
  data: {
    art: {}
  },
  onReady: function () {
    wx.setNavigationBarTitle({
      title: ''
    })
  },
  onLoad: function (options) {
    var that = this
    wx.request({
      url: app.serverUrl + '?json=get_post&id=' + options.id,
      headers: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
         var content = res.data.post.content;
         WxParse.wxParse('content', 'html', content, that,5);
         that.setData({
           info: res.data.post
         })
      }
    })
  }
})