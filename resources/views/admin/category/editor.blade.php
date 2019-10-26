@extends('admin.layouts.app')


@section('content')
    <link rel="stylesheet" href="/editormd/css/editormd.css" />

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="section-block" id="basicform">
                <h3 class="section-title">新分类</h3>
            </div>
            <div class="card">
                <form method="post">
                    <h5 class="card-header">基本信息</h5>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="inputText3" class="col-form-label">分类名称</label>
                            <input id="inputText3" type="text" class="form-control" name="name" value="{{session('title')??($category->name??'')}}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">是否显示</label>
                            <div class="switch-button switch-button-success">
                                @if(session('is_show')??($category->is_show??'1'))
                                    <input type="checkbox" checked="" name="is_show" id="switch16"><span><label for="switch16"></label></span>
                                @else
                                    <input type="checkbox" name="is_show" id="switch16"><span><label for="switch16"></label></span>
                                @endif
                            </div>
                        </div>

                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                <button class="btn btn-primary" type="submit">提交</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection