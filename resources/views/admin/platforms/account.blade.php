@extends('admin.layouts.app')


@section('content')
    <link rel="stylesheet" href="/editormd/css/editormd.css" />

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="section-block" id="basicform">
                <h3 class="section-title">{{$platforms->title}} ({{$platforms->name}})</h3>
            </div>
            <div class="card">
                <form method="post">
                    <h5 class="card-header">设置账户</h5>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">用户名</label>
                            <input id="inputText3" type="text" class="form-control" name="username" value="{{session('username')??($platforms->account('username'))}}">
                        </div>
                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">密码</label>
                            <input id="inputText3" type="text" class="form-control" name="password" value="{{session('password')??($platforms->account('password'))}}">
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <h3>COOKIE</h3>
                        <div class="form-group">
                            <label for="inputDefault" class="col-form-label">cookie文本</label>
                            <textarea class="form-control" name="cookie" rows="4">{{session('cookie')??($platforms->cookie ?? '')}}</textarea>
                        </div>
                        <button class="btn btn-outline-primary" type="submit">提交</button>
                    </div>
                    <div class="card-body border-top">
                        <div class="row">
                            <div class="col-md-5">
                                <h3>本站分类</h3>
                                <div class="form-group">
                                    <label for="inputDefault" class="col-form-label">选择分类</label>
                                    <select class="form-control" id="site-category-select" >
                                        <option value="0">默认分类</option>
                                        @foreach($category as $item)
                                            @if(session('cat_id')??($post->cat_id??'0') == $item->id)
                                                <option value="{{$item->id}}" selected>{{$item->name}}</option>
                                            @else
                                                <option value="{{$item->id}}">{{$item->name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h3>平台分类</h3>
                                <div class="form-group">
                                    <label for="inputDefault" class="col-form-label">选择分类</label>
                                    <select class="form-control" id="platform-category-select" >
                                        @foreach($platforms->getCategoryList() as  $catId => $catText)
                                                <option value="{{$catId}}">{{$catText}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <h3>　</h3>
                                <div class="form-group">
                                    <label for="inputDefault" class="col-form-label">　</label>
                                    <a href="javascript:joinCategory()" class="form-control btn btn-primary">关联</a>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12" id="category-union-html">

                            </div>
                        </div>

                        {{ csrf_field() }}

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/editormd/editormd.min.js"></script>
    <script type="text/javascript">
        var platfrom_id = "{{$platforms->id}}";
        $.ajaxSetup({
            headers : {
                'X-CSRF-TOKEN' : $("meta[name='x-csrf-token']").attr('content')
            }
        });
        updateCategory();
        function joinCategory()
        {
            let site_cat_id = $('#site-category-select').val();
            let platform_cat_id = $('#platform-category-select').val();
            let platform_cat_name = $('#platform-category-select option:selected').text();
            $.ajax({
                url: '/admin/platforms/' + platfrom_id + '/category/union',
                method: "post",
                dataType:'json',
                data: {
                    site_cat_id: site_cat_id,
                    platform_cat_id: platform_cat_id,
                    platform_cat_name: platform_cat_name
                },
                async: true, success: function (i) {
                    if(i.response.code == 200){
                        alert(i.response.message);;
                        updateCategory();
                    }
                }
            });
        }
        function updateCategory()
        {
            $.ajax({
                url: '/admin/platforms/' + platfrom_id + '/category/union',
                method: "GET",
                data: {
                },
                async: true, success: function (i) {
                    $("#category-union-html").html(i);
                    console.log(i);
                }
            });
        }
        $(function() {
            var editor = editormd("test-editor", {
                // width  : "100%",
                height : "600px",
                path   : "/editormd/lib/"
            });
        });
    </script>
@endsection