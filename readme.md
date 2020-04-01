## QuickBlog

一文多发系统，即一个平台文章以及维护编辑内容，文章自动同步到多个平台并更新。

有了它你只需要在一个平台完成文章内容创作，程序会自动帮助你实现发布、更新到其他平台的工作。同时QuickBlog 本身还是一个具备基本功能的博客程序，你可以将它部署在互联网上很轻易的作为独立博客吸引读者以及搜索引擎的青睐。



### 基本特点

- 程序采用PHP编写，使用Laravel框架为基础
- 代码开源可自行部署，不丢失数据隐私
- 采用插件形式，极易扩展新增发布平台
- 支持Markdown编写
- 可指定同步平台分类



### 支持的平台

目前QuickBlog支持以下平台。

- [x] [OSCHINA](https://www.oschina.net/)
- [x] [CSDN](https://www.csdn.net/)
- [x] [SegmentFault](https://segmentfault.com/)
- [x] [简书](https://www.jianshu.com/)
- [x] [博客园](https://www.cnblogs.com/)
- [x] [知乎](https://www.zhihu.com)
- [ ] [掘金](https://juejin.im/)
- [ ] [WordPress](https://wordpress.org)



### 预览图
![3lpLFJ.png](https://s2.ax1x.com/2020/02/23/3lpLFJ.png)
![3lT3If.png](https://s2.ax1x.com/2020/02/23/3lT3If.png)
![3lTDoV.png](https://s2.ax1x.com/2020/02/23/3lTDoV.png)
![3lTLyd.png](https://s2.ax1x.com/2020/02/23/3lTLyd.png)



## 安装使用

### **环境要求**

PHP：建议7.0以上

MySQL：5.7 以上



### 1.下载程序

通过 github 获取最新的源代码

```bash
git clone https://github.com/ellermister/QuickBlog.git
```

如果没有安装 git ，建议你安装，以方便以后通过 <code>git</code> 进行更新。当然这也丝毫不影响你通过直接下载zip包的形式使用。



### 2.通过composer安装依赖

你需要引入composer，如果没有安装请参考：https://getcomposer.org/download/

```
composer update
```

### 3.生成KEY和创建数据库

复制一份配置`.env.example`为`.env`，并修改其中的数据库参数。

```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### 4.安装插件

平台同步采用插件的形式集成在系统中，执行命令将予以安装并将信息存储到数据库。

```bash
php artisan plugin:install
```

### 5.写入管理员信息

最后，通过写入默认管理员信息获得后台管理的访问权限。

```
php artisan admin:create
```

执行完毕后，你会得到默认的管理员信息以及访问地址。

```
>>新建管理员成功<<
用户名：quickblog@eller.tech
密码：admin888
登录地址：http://localhost/login
```

### 6.设置计划任务

通过linux系统的计划任务来运行系统以及调度同步任务计划，目前同步功能10分钟进行一次。

通过 `crontab -e` 写入以下命令，其中路径需要自行替换。

```
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```



## License

需注明程序名称（QuickBlog）及开源地址！



## 插件

博客平台发布采用插件式开发，隔三差五零散拾写，很多细节不太规范。

具体参考如下：

所有插件必须继承于 `App\Services\Plugin`

所有插件必须存在于`app/Services/Plugins`目录下，以大驼峰法命名。

目前插件是以单文件的形式存在。

如：`app/Services/Plugins/Oschina.php`



### 同步文章

作为一个插件，最重要的是实现同步文章的这个过程。

所以，首先实现这个方法。

```php
/**
 * 更新同步计划
 * 插件需要继承并实现
 * @param PostsSchemes $postsScheme
 * @return bool|string
 */
abstract public function updateScheme(PostsSchemes $postsScheme);
// 同步过程，需要自行try catch捕获，防止发生错误。

// 首先判断是否是需要同步的计划
// if( $postsScheme->isWaitSyncStatus() ) {}

// 开始同步时需要将计划设置为正在同步，防止重复同步。
// $postsScheme->setSynching();

// 同步成功，需要将计划设置为成功。
// $postsScheme->setSynced(); //设置已经同步完成

// 如果同步失败，则需要将计划设置为失败状态。
// $postsScheme->setSyncFailed();// 同步失败，设置状态

// 如果同步出错，需要返回错误字符串，将会在命令行展示。
// return $exception->getMessage();
```



在同步文章时，可以通过`$postsScheme->third_id`是否为空来分辨当前任务是更新还是新增。

当然，`$postsScheme->third_url`这个值也是可以的，不过需要注意的是，这两个值都需要你自行维护。

当文章新增更新完成时，你都要及时将这两个值更新为最新的。

third_id为第三方平台对应博文的ID，third_url为第三方平台对应博文的URL。



### 分类关联

如果你所对接的博客平台拥有自己的分类，那么你可以通过此方法和QuickBlog内置的分类进行对接关联。

在这里你只需要实现获取第三方平台分类的名称和id就好，id是以varchar(255)存储于数据库的，所以不局限于int型的ID。

```php
/**
 * 分类列表接口
 * 必须返回键值对 [ '分类ID' => '分类名]
 * @return array
 */
abstract function categoryList();
```

需要记住的是，就算你的平台没有分类，也要在这里实现，并返回空数组。

当你的插件编写成功后，你可以在对应平台的设置中找到关联分类。

那么假设你的分类关联成功了，当平台进行同步文章时，你的插件将会被动调起。

当你在update方法中更新文章时，就就可以通过如下方法获得这篇文章关联的平台ID和名称。

```php
$postsScheme->getUnionCategory()
//  [ name => '', id => '']
```

