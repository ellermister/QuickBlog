@extends('admin.layouts.app')


@section('content')
    <link rel="stylesheet" href="/editormd/css/editormd.css" />

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="section-block" id="basicform">
                <h3 class="section-title">同步日志</h3>
            </div>
            <div class="card">
                <form method="post">
                    <h5 class="card-header">日志详情</h5>
                    <div class="card-body">
                        <div class="form-group"  id="test-editor">
                            <a class="btn btn-danger" href="/admin/log/clear">清理日志</a>
                        </div>
                        <div class="form-group"  id="test-editor">
                            <label for="exampleFormControlTextarea1">内容</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="13" name="contents">{{$logText??''}}</textarea>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection