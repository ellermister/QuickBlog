@extends('admin.layouts.app')

@section('content')
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">平台管理 </h2>
            <p class="pageheader-text">所支持的平台列表</p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">首页</a></li>
                        <li class="breadcrumb-item active" aria-current="page">平台管理</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- end pageheader -->
<!-- ============================================================== -->

<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

        @include('admin.shared.error')

        <div class="card">
            <h5 class="card-header">所有平台</h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">图标</th>
                        <th scope="col">平台代号</th>
                        <th scope="col">平台名称</th>
                        <th scope="col">平台描述</th>
                        <th scope="col">Cookie状态</th>
                        <th scope="col">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($platforms as $item)
                        <tr>
                            <th scope="row"><img style="width: 32px;" src="/img/platforms/{{$item->img}}"></th>
                            <td>{{$item->name}}</td>
                            <td>{{$item->title}}</td>
                            <td>{{$item->describe}}</td>
                            <td>{{$item->cookieStatusText()}}</td>
                            <td>
                                <a href="/admin/platforms/{{$item->id}}/sync" class="btn btn-info active">导入文章</a>
                                <a href="/admin/platforms/{{$item->id}}/account" class="btn btn-success active">设置账户</a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection