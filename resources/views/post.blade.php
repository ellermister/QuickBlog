@extends('layouts.app')
@section('title', $post->title.' - '.getSettings('site_name'))
@section('keyword', $post->keywords)
@section('description', $post->description)

@section("page_header")
    <div id="post-header" class="page-header">
        <div class="background-img" style="background-image: url('/img/post-page.jpg');"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-10">
                    <div class="post-meta">
                        <a class="post-category {{$post->getCatClass()}}" href="/category/{{$post->cat_id}}">{{$post->getCateName()}}</a>
                        <span class="post-date">{{$post->getDateText()}}</span>
                    </div>
                    <h1>{{$post->title}}</h1>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- section -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- Post content -->
                <div class="col-md-8">
                    <div class="section-row sticky-container">
                        <div class="main-post">
                            {!! $post->getHtmlBody() !!}
                        </div>
                        <div class="post-shares sticky-shares">
                            <a href="#" class="share-facebook"><i class="fa fa-facebook"></i></a>
                            <a href="#" class="share-twitter"><i class="fa fa-twitter"></i></a>
                            <a href="#" class="share-google-plus"><i class="fa fa-google-plus"></i></a>
                            <a href="#" class="share-pinterest"><i class="fa fa-pinterest"></i></a>
                            <a href="#" class="share-linkedin"><i class="fa fa-linkedin"></i></a>
                            <a href="#"><i class="fa fa-envelope"></i></a>
                        </div>
                    </div>

                    <!-- ad -->
                    <div class="section-row text-center">
                        <a href="#" style="display: inline-block;margin: auto;">
                            <img class="img-responsive" src="/img/ad-2.jpg" alt="">
                        </a>
                    </div>
                    <!-- ad -->

                    <!-- author -->
                    @include("shared.author")
                    <!-- /author -->

                    <!-- comments -->
                    @include("shared.comments")
                    <!-- /comments -->

                    <!-- reply -->
                    @include("shared.reply")
                    <!-- /reply -->
                </div>
                <!-- /Post content -->

                <!-- aside -->
                <div class="col-md-4">
                    <!-- ad -->
                    <div class="aside-widget text-center">
                        <a href="#" style="display: inline-block;margin: auto;">
                            <img class="img-responsive" src="/img/ad-1.jpg" alt="">
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

                    <!-- post widget -->
                    <div class="aside-widget">
                        <div class="section-title">
                            <h2>精选文章</h2>
                        </div>
                        @foreach(getFeaturedPosts() as $post)
                        <div class="post post-thumb">
                            @if($post->getThumbnail())
                                <a class="post-img" href="/post/{{$post->id}}"><img src="{{$post->getThumbnail()}}" alt=""></a>
                            @else
                                <a class="post-img" href="/post/{{$post->id}}"><img src="/img/post-2.jpg" alt=""></a>
                            @endif
                            <div class="post-body">
                                <div class="post-meta">
                                    <a class="post-category {{$post->getCatClass()}}" href="/category/{{$post->cat_id}}">{{$post->getCateName()}}</a>
                                    <span class="post-date">{{$post->getDateText()}}</span>
                                </div>
                                <h3 class="post-title"><a href="/post/{{$post->id}}">{{$post->title}}</a></h3>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- /post widget -->

                    <!-- catagories -->
                    <div class="aside-widget">
                        <div class="section-title">
                            <h2>分类列表</h2>
                        </div>
                        <div class="category-widget">
                            <ul>
                                @foreach($category as $item)
                                    <li><a href="/category/{{$item->id}}" class="{{$item->getCatClass()}}">{{$item->name}}<span>{{$item->count}}</span></a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- /catagories -->

                    <!-- tags -->
                    <div class="aside-widget">
                        <div class="tags-widget">
                            <ul>
                                @foreach($tags as $tag)
                                    <li><a href="/tag/{{$tag}}">{{$tag}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- /tags -->

                    <!-- archive -->
                    <div class="aside-widget">
                        <div class="section-title">
                            <h2>归档</h2>
                        </div>
                        <div class="archive-widget">
                            <ul>
                                @foreach($archiveList as $item)
                                <li><a href="/archive/{{$item->date}}">{{$item->date}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- /archive -->
                </div>
                <!-- /aside -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /section -->
@endsection
