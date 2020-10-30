<?php


namespace Tests\Unit\Services;


use App\Contracts\AuthenticationContract;
use App\Services\Authentication;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

class AuthenticationTest  extends TestCase
{
    /**
     * @var mixed
     */
    private $auth;

    public function setUp(): void
    {
       $this->auth =  $this->createApplication()->make(AuthenticationContract::class);
    }


    /**
     * @test
     */
    public function test_retrieve_token()
    {
        $structure = [
           'token'=>'token'
        ];
        $root = vfsStream::setup('home',null);
        $root = vfsStream::create($structure,$root)->url();
        config(['psb.token_storage'=>$root.'/token']);
        $auth = new Authentication();
       // dd(file_get_contents($root.'/token'));
       // dd(config('psb.token_storage'));
       // dd($root);
       // dd(is_dir($root."/token/g"));

        $this->assertTrue($auth->retrieveToken() == 'token');
    }
}
