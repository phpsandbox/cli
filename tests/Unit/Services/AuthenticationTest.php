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
        $this->assertTrue($auth->retrieveToken() == 'token');
    }

    /**
     * @test
     */
    public function test_logout()
    {
        $structure = [
            'token'=>'token'
        ];
        $root = vfsStream::setup('home',null);
        $token_storage = vfsStream::create($structure,$root)->url();
        config(['psb.token_storage'=>$token_storage.'/token']);
        $auth = new Authentication();
        $auth->logout();
        $this->assertFalse($root->hasChild('token'));
    }
}
