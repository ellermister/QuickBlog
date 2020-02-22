<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class AdminCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '首次安装时用于建立管理员';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = "quickblog@eller.tech";
        $pass = "admin888";
        try{
            if(User::create([
                'name' => 'admin',
                'email' => $email,
                'password' => bcrypt($pass)
            ])){
                $this->info('>>新建管理员成功<<');
                $this->info(sprintf('用户名：%s', $email));
                $this->info(sprintf('密码：%s', $pass));
                $this->info(sprintf('登录地址：%s/login', env('APP_URL')));
            }else{
                $this->error('新建管理员失败');
            }
        }catch (\Exception $exception){
            $this->error(sprintf('新建管理员出错:%s', $exception->getMessage()));
        }

    }
}
