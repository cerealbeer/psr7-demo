<?php
namespace Demo4;

use DebugBar\JavascriptRenderer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Zend\Diactoros\Response;

class Controller
{
    private $client;
    private $jsRenderer;
    private $usernames = ['andreascreten', 'xavierbarbosa', 'sambego', '4xel', 'sdemoffarts', 'Anne-Julie', 'sjimi', 'tonysm', 'hannesvdvreken', 'anahkiasen', 'bramdevries', 'mogetutu', 'dieterve', 'eliedevlieger', 'jdrieghe', 'jonasvanschoote', 'kaiobrito', 'maartenscholz', 'mangamaui', 'miggynascimento', 'yannickdepauw', 'vanbrabantf'];
    private $profiles = [];

    public function __construct(ClientInterface $client, JavascriptRenderer $jsRenderer)
    {
        $this->client = $client;
        $this->jsRenderer = $jsRenderer;
    }

    public function index()
    {
        $promises = call_user_func(function () {
            foreach ($this->usernames as $username) {
                yield $this->client->requestAsync('GET', 'https://api.github.com/users/'.$username);
            }
        });

        // Wait till all the requests are finished.
        \GuzzleHttp\Promise\all($promises)->then(function (array $responses) {
            $this->profiles = array_map(function ($response) {
                return json_decode($response->getBody(), true);
            }, $responses);
        })->wait();

        // Return JSON response
        $response = new Response();

        // StreamInterface objects are not immutable!
        $response->getBody()->write($this->html());

        return $response
            ->withHeader('Content-type', 'text/html');
    }

    public function html()
    {
        $head = "<html><head>{$this->jsRenderer->renderHead()}</head>";
        $body = join('', array_map(function (array $profile) {
            return "<img src='{$profile['avatar_url']}' width='100px'><br>";
        }, $this->profiles));
        $footer = "</html>";

        return $head."<body>".$body."{$this->jsRenderer->render()}</body>".$footer;
    }
}
