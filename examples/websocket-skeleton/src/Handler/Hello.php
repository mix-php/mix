<?php

namespace App\Handler;

use App\Service\Session;

class Hello
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * Hello constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function index(string $message): void
    {
        $this->session->send('hello, world!');
    }

}
