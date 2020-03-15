@extends('admin.layouts.app')


@section('content')

<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="section-block" id="basicform">
            <h3 class="section-title">登录助手</h3>
        </div>
        <div class="card">
            <form method="post">
                <h5 class="card-header">介绍</h5>
                <div class="card-body">

                    <div class="form-group">
                        <label for="inputText3" class="col-form-label">Chrome插件下载</label>
                        <a class="btn btn-link" href="https://eller.tech/to/helper" target="_blank">下载插件</a>
                        <p>新版Chrome需要解压并通过 “开发者模式-加载已解压的扩展程序” 进行安装</p>
                    </div>
                </div>
                <div class="card-body border-top">
                    <h3>配置信息</h3>
                    <div class="form-group">
                        <label for="inputDefault" class="col-form-label">URL</label>
                        <input id="inputDefault" type="text" value="{{ env("APP_URL") }}/api/plugin/cookie?token={{env('TOKEN')}}" class="form-control" name="url">
                    </div>
                    <div class="form-group">
                        <p>token的配置方法在站点目录下 <code>.env</code> 文件中</p>
                    </div>

                    {{ csrf_field() }}
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection