@extends('admin.layouts.app')


@section('content')
<link rel="stylesheet" href="/editormd/css/editormd.css" />

<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="section-block" id="basicform">
            <h3 class="section-title">发布新文章</h3>
        </div>
        <div class="card">
            <form method="post">
            <h5 class="card-header">文章详情</h5>
            <div class="card-body">

                    <div class="form-group">
                        <label for="inputText3" class="col-form-label">标题</label>
                        <input id="inputText3" type="text" class="form-control" name="title" value="{{session('title')??($post->title??'')}}">
                    </div>
                    <div class="form-group">
                        <label for="input-select">分类</label>
                        <select class="form-control" id="input-select" name="cat_id">
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
                    <div class="form-group"  id="test-editor">
                        <label for="exampleFormControlTextarea1">内容</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="13" name="contents">{{session('contents')??($post->contents??'')}}</textarea>
                    </div>

            </div>
            <div class="card-body border-top">
                <h3>附加</h3>
                    <div class="form-group">
                        <label class="col-form-label">是否显示</label>
                        <div class="switch-button switch-button-success">
                            @if(session('is_show')??($post->is_show??'1'))
                                <input type="checkbox" checked="" name="is_show" id="switch16"><span><label for="switch16"></label></span>
                                @else
                                <input type="checkbox" name="is_show" id="switch16"><span><label for="switch16"></label></span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">允许同步</label>
                        <div class="switch-button switch-button-success">
                            @if(session('is_sync')??($post->is_sync??'1'))
                                <input type="checkbox" checked="" name="is_sync" id="is_sync"><span><label for="is_sync"></label></span>
                                @else
                                <input type="checkbox" name="is_sync" id="is_sync"><span><label for="is_sync"></label></span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputDefault" class="col-form-label">关键词</label>
                        <input id="inputDefault" type="text" value="{{session('keywords')??($post->keywords??'')}}" class="form-control" name="keywords">
                    </div>
                    <div class="form-group">
                        <label for="inputDefault" class="col-form-label">描述</label>
                        <input id="inputDefault" type="text" value="{{session('description')??($post->description??'')}}" class="form-control" name="description">
                    </div>

                    {{ csrf_field() }}
                    <button class="btn btn-outline-primary" type="submit">提交</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/editormd/editormd.min.js"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers : {
                'X-CSRF-TOKEN' : $("meta[name='x-csrf-token']").attr('content')
            }
        });
        $(function() {
            var editor = editormd("test-editor", {
                // width  : "100%",
                height : "600px",
                path   : "/editormd/lib/",
                imageUpload : true,
                imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
                imageUploadURL : "/admin/upload",

            });
        });
    </script>
@endsection