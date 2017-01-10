//获取应用实例
var app = getApp();
Page({
    data: {
      list: []
    },
    onLoad: function () {
      this.index = 1;
      this.noMore = false;
      var that = this;
      wx.request({
        url: app.serverUrl + '?json=get_category_posts&id=3&page=' + that.index,
        headers: {
          'Content-Type': 'application/json'
        },
        success: function (res) {
          that.setData({
            list: res.data.posts
          })
        }
      })
    },
      //加载更多
      onReachBottom: function() {
          var that = this;
          that.nextPage = that.index + 1;
          if(that.noMore == false){
            that.setData({ loading: true });
            wx.request({
              url: app.serverUrl + '?json=get_category_posts&id=3&page=' + that.nextPage,
              headers: {
                'Content-Type': 'application/json'
              },
              success: function (res) {
                 if(res.data.count != 0){
                     that.setData({
                       loading: false,
                       list: that.data.list.concat(res.data.posts),
                     });
                 } else {
                     that.setData({
                       loading: false,
                       noMore: true,
                     });
                 }
              }
            });
            that.index++;
          }
      }
})