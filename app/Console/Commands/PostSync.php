<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步博文';

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
        //
        $this->info('done!');
    }
}
