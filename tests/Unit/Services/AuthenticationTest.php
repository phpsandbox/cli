<?php


namespace Tests\Unit\Services;


use App\Contracts\AuthenticationContract;
use App\Services\Authentication;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
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
        config(['psb.token_storage' => $root.'/token']);
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

    /**
     * @test
     */
    public function test_valid_token_is_valid()
    {
        /* test valid token */
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user',$base_url) => Http::response([''],200)
        ]);
        $auth = new Authentication();
        $this->assertTrue($auth->tokenIsValid('rightToken'));
    }

    public function test_invalid_token_is_invalid()
    {
        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user',$base_url)=>Http::response([''],401)
        ]);
        $auth = new Authentication();
        $this->assertFalse($auth->tokenIsValid('wrongToken'));
    }

    public function test_check_user_is_logged_in()
    {
       // $this->mock
    }

    public function test_store_new_token()
    {
        $structure = [];
        $root = vfsStream::setup('root', null, $structure);
        config(['psb.token_storage' => $root->url().'/token']);
        $auth = new Authentication();
        $auth->storeNewToken('token');
        $this->assertTrue($root->hasChild('token'));
        $this->assertSame('token', file_get_contents($root->url().'/token'));
    }

    public function test_launch_browser()
    {

    }


}
