<?php

namespace App\Console\Commands;
use App\Console\Kernel;
use Carbon\Carbon;
use App\Http\Controllers\Report\UpdateMessageController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;


class SendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request URL to send message';

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
        app("App\Http\Controllers\Report\UpdateMessageController")->updateMessage();
    }
}
