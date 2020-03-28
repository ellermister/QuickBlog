@extends('layouts.app')

@section('content')
<!-- section -->
<div class="section">
    <!-- container -->
    <div class="container">
        <!-- row -->
        <div class="row">
            <div class="col-md-8">
                <div class="section-row">
                    <h1>QuickBlog</h1>
                    <p> 一文多发系统，即一个平台文章以及维护编辑内容，文章自动同步到多个平台并更新。</p>
                    <p>有了它你只需要在一个平台完成文章内容创作，程序会自动帮助你实现发布、更新到其他平台的工作。同时QuickBlog本身还是一个具备基本功能的博客程序，你可以将它部署在互联网上很轻易的作为独立博客吸引读者以及搜索引擎的青睐。</p>
                    <h3>基本特点</h3>
                    <ul class="list-style">
                    <li><p>程序采用PHP编写，使用Laravel框架为基础</p></li>
                    <li><p>代码开源可自行部署，不丢失数据隐私</p></li>
                    <li><p>采用插件形式，极易扩展新增发布平台</p></li>
                    <li><p>支持Markdown编写</p></li>
                    <li><p>可指定同步平台分类</p></li>
                    </ul>
                </div>
                <div class="row section-row">
                    <div class="col-md-12">
                        <h3>支持平台</h3>
                        <p>目前支持以下平台，部分其他平台也会考虑在开发计划之中，也欢迎各位伙伴自行开发，平台同步是采用内置插件形式集成的，整个过程非常简单。</p>
                        <ul class="list-style">
                            <li><p>OSHINA(开源中国).</p></li>
                            <li><p>CSDN.</p></li>
                            <li><p>SegmentFault</p></li>
                            <li><p>简书</p></li>
                            <li><p>博客园</p></li>
                            <li><p>知乎</p></li>
                        </ul>
                    </div>
                </div>
                <div class="row section-row">
                    <div class="col-md-12">
                        <h3>下载地址</h3>
                        <p>如果你做好准备了，请使用github下载最新的包，以及阅读安装说明。</p>
                        <p>Github地址：<a target="_blank" href="https://github.com/ellermister/QuickBlog">https://github.com/ellermister/QuickBlog</a></p>
                    </div>
                </div>
            </div>

            <!-- aside -->
            <div class="col-md-4">
                <!-- ad -->
                <div class="aside-widget text-center">
                    <a href="#" style="display: inline-block;margin: auto;">
                        <img class="img-responsive" src="./img/ad-1.jpg" alt="">
                    </a>
                </div>
                <!-- /ad -->

                <!-- post widget -->
                <div class="aside-widget">
                    <div class="section-title">
                        <h2>浏览最多</h2>
                    </div>

                    @foreach(getMostRead() as $post)
                        <div class="post post-widget">
                            @if($post->getThumbnail())
                                <a class="post-img" href="/post/{{$post->id}}"><img src="{{$post->getThumbnail()}}" alt="{{$post->title}}"></a>
                            @else
                                <a class="post-img" href="/post/{{$post->id}}"><img src="/img/widget-2.jpg" alt="{{$post->title}}"></a>
                            @endif
                            <div class="post-body">
                                <h3 class="post-title"><a href="/post/{{$post->id}}">{{$post->title}}</a></h3>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- /post widget -->

            </div>
            <!-- /aside -->
        </div>
        <!-- /row -->
    </div>
    <!-- /container -->
</div>
<!-- /section -->
@endsection