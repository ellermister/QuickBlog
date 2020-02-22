<!-- Header -->
<header id="header">
    <!-- Nav -->
    <div id="nav">
        <!-- Main Nav -->
        <div id="nav-fixed">
            <div class="container">
                <!-- logo -->
                <div class="nav-logo">
                    <a href="/" class="logo"><img src="/img/logo.png" alt=""></a>
                </div>
                <!-- /logo -->

                <!-- nav -->
                <ul class="nav-menu nav navbar-nav">
                    @if($tab == "latest")
                        <li><a href="/latest" class="on">最新</a></li>
                        @else
                        <li><a href="/latest">最新</a></li>
                    @endif
                    @if($tab == "hots")
                        <li><a href="/hots" class="on">热点</a></li>
                        @else
                        <li><a href="/hots">热点</a></li>
                    @endif
                    @foreach(getCateList(4) as  $item)
                        @if("cat_".$item->id == $tab)
                            <li class="{{$item->getCatClass()}}"><a href="/category/{{$item->id}}" class="on">{{$item->name}}</a></li>
                        @else
                            <li class="{{$item->getCatClass()}}"><a href="/category/{{$item->id}}">{{$item->name}}</a></li>
                        @endif
                    @endforeach
                </ul>
                <!-- /nav -->

                <!-- search & aside toggle -->
                <div class="nav-btns">
                    <button class="aside-btn"><i class="fa fa-bars"></i></button>
                    <button class="search-btn"><i class="fa fa-search"></i></button>
                    <div class="search-form">
                        <input class="search-input" type="text" name="search" placeholder="Enter Your Search ...">
                        <button class="search-close"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <!-- /search & aside toggle -->
            </div>
        </div>
        <!-- /Main Nav -->

        <!-- Aside Nav -->
        <div id="nav-aside">
            <!-- nav -->
            <div class="section-row">
                <ul class="nav-aside-menu">
                    <li><a href="/">首页</a></li>
                    <li><a href="/about">关于我们</a></li>
                    <li><a href="/Advertisement">广告</a></li>
                    <li><a href="/contact">联系</a></li>
                </ul>
            </div>
            <!-- /nav -->

            <!-- widget posts -->
            <div class="section-row">
                <h3>最近的博文</h3>
                @foreach(getRecentPosts() as $post)
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
            <!-- /widget posts -->

            <!-- social links -->
            <div class="section-row">
                <h3>Follow us</h3>
                <ul class="nav-aside-social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                    <li><a href="#"><i class="fa fa-pinterest"></i></a></li>
                </ul>
            </div>
            <!-- /social links -->

            <!-- aside nav close -->
            <button class="nav-aside-close"><i class="fa fa-times"></i></button>
            <!-- /aside nav close -->
        </div>
        <!-- Aside Nav -->
    </div>
    <!-- /Nav -->

    <!-- Page Header -->
    @yield('page_header')
    <!-- /Page Header -->
</header>
<!-- /Header -->