@extends('admin.layouts.app')


@section('content')
<link rel="stylesheet" href="/editormd/css/editormd.css" />

<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="section-block" id="basicform">
            <h3 class="section-title">编辑用户</h3>
        </div>
        <div class="card">
            <form method="post">
            <h5 class="card-header">用户详情</h5>
            <div class="card-body">

                    <div class="form-group">
                        <label for="inputText3" class="col-form-label">名字</label>
                        <input id="inputText3" type="text" class="form-control" name="name" value="{{session('name')??($user_info->name??'')}}">
                    </div>
                    <div class="form-group">
                        <label for="inputTextEmail" class="col-form-label">Email</label>
                        <input id="inputTextEmail" type="text" class="form-control" name="email" value="{{session('email')??($user_info->email??'')}}">
                    </div>
                    <div class="form-group">
                        <label for="inputTextTime" class="col-form-label">最后登录时间</label>
                        <input id="inputTextTime" type="text" class="form-control"disabled value="{{ date('Y-m-d H:i', $user_info->login_time ?? 0)  }}">
                    </div>
                @if($currentUser->isAdmin())
                    <div class="form-group">
                        <label for="inputTextIP" class="col-form-label">最后登录IP</label>
                        <input id="inputTextIP" type="text" class="form-control" disabled value="{{  $user_info->last_ip ?? ''}}">
                    </div>
                @endif
                <div class="form-group">
                    <label for="inputTextAvatar" class="col-form-label">头像</label>
                    <input id="inputTextAvatar" type="text" class="form-control" name="avatar"  value="{{ $user_info->avatar ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="inputTextPassword" class="col-form-label">密码</label>
                    <input id="inputTextPassword" type="text" class="form-control" name="password"   value="" placeholder="不更改请留空">
                </div>


            </div>
            <div class="card-body border-top">
                    {{ csrf_field() }}
                    <button class="btn btn-outline-primary" type="submit">提交</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection