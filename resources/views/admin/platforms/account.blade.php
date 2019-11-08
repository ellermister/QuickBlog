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
        $(function() {
            var editor = editormd("test-editor", {
                // width  : "100%",
                height : "600px",
                path   : "/editormd/lib/"
            });
        });
    </script>
@endsection