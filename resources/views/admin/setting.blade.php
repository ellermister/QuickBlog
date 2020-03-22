@extends('admin.layouts.app')


@section('content')
    <link rel="stylesheet" href="/editormd/css/editormd.css" />

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="section-block" id="basicform">
                <h3 class="section-title">平台配置</h3>
            </div>
            <div class="card">
                <form method="post">
                    <h5 class="card-header">网站基础</h5>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">网站名称</label>
                            <input id="inputText3" type="text" class="form-control" name="site_name" value="{{session('site_name')??($setting->get('site_name'))}}">
                        </div>
                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">关键字</label>
                            <input id="inputText3" type="text" class="form-control" name="site_keyword" value="{{session('site_keyword')??($setting->get('site_keyword'))}}">
                        </div>
                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">描述</label>
                            <input id="inputText3" type="text" class="form-control" name="site_describe" value="{{session('site_describe')??($setting->get('site_describe'))}}">
                        </div>
                    </div>
                    <div class="card-body border-top">
                        @csrf
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
    </script>
@endsection