@extends('layouts.app')

@if($tab == "latest")
    @section('title','最新 - '.getSettings('site_name'))
@elseif($tab == "hots")
    @section('title','热点 - '.getSettings('site_name'))
@elseif(substr($tab,0,strlen("cat_")) == "cat_")
    @section('title',getCategoryName(substr($tab,strlen("cat_"))).' - '.getSettings('site_name'))
@endif

@section('content')
    <!-- section -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="section-title">
                                <h2>文章列表</h2>
                            </div>
                        </div>
                        <!-- post -->
                        @foreach($posts as $post)
                        <div class="col-md-12">
                            <div class="post post-row">
                                @if($post->getThumbnail())
                                    <a class="post-img" style="max-width: 300px;max-height: 180px" href="/post/{{$post->id}}"><img src="{{$post->getThumbnail()}}" alt=""></a>
                                @else
                                    <a class="post-img" style="max-width: 300px;max-height: 180px" href="/post/{{$post->id}}"><img src="/img/post-4.jpg" alt=""></a>
                                @endif
                                <div class="post-body">
                                    <div class="post-meta">
                                        <a class="post-category {{$post->getCatClass()}}" href="/category/{{$post->cat_id}}">{{$post->getCateName()}}</a>
                                        <span class="post-date">{{$post->getDateText()}}</span>
                                    </div>
                                    <h3 class="post-title"><a href="/post/{{$post->id}}">{{$post->title}}</a></h3>
                                    <p>{{$post->description}}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <!-- /post -->



                        <div class="col-md-12">
                            <div class="section-row">
                                {{ $posts->links() }}
                                <button class="primary-button center-block" style="display: none;">获取更多</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- ad -->
                    <div class="aside-widget text-center">
                        <a href="#" style="display: inline-block;margin: auto;">
                            <img class="img-responsive" src="/img/ad-1.jpg" alt="">
                        </a>
                    </div>
                    <!-- /ad -->

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
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /section -->
@endsection
