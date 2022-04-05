<?php

namespace App\Container\Swow;

use App\Container\Swow\Exception\CoroutineDestroyedException;
use ArrayObject;
use Swow\Coroutine as SwowCo;

class Coroutine extends SwowCo
{
    /**
     * @var ArrayObject
     */
    protected $context;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var callable[]
     */
    protected $deferCallbacks = [];

    /**
     * @var ArrayObject|null
     */
    protected static $mainContext = null;

    public function __construct(callable $callable)
    {
        parent::__construct($callable);
        $this->context = new ArrayObject();
        $this->parentId = static::getCurrent()->getId();
    }

    public function __destruct()
    {
        while (!empty($this->deferCallbacks)) {
            array_shift($this->deferCallbacks)();
        }
    }

    /**
     * @param ...$data
     * @return static
     */
    public function execute(...$data)
    {
        $this->resume(...$data);

        return $this;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function addDefer(callable $callable)
    {
        array_unshift($this->deferCallbacks, $callable);
    }

    /**
     * @param callable $callable
     * @param ...$data
     * @return static
     */
    public static function create(callable $callable, ...$data)
    {
        $coroutine = new self($callable);
        $coroutine->resume(...$data);
        return $coroutine;
    }

    public static function id(): int
    {
        return static::getCurrent()->getId();
    }

    public static function pid(?int $id = null): int
    {
        if ($id === null) {
            $coroutine = static::getCurrent();
            if ($coroutine instanceof static) {
                return static::getCurrent()->getParentId();
            }
            return 0;
        }

        $coroutine = static::get($id);
        if ($coroutine === null) {
            throw new CoroutineDestroyedException(sprintf('Coroutine #%d has been destroyed.', $id));
        }

        return $coroutine->getParentId();
    }

    public static function set(array $config): void
    {
    }

    /**
     * @param int|null $id
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        $coroutine = is_null($id) ? static::getCurrent() : static::get($id);
        if ($coroutine === null) {
            return null;
        }
        if ($coroutine instanceof static) {
            return $coroutine->getContext();
        }
        if (static::$mainContext === null) {
            static::$mainContext = new ArrayObject();
        }
        return static::$mainContext;
    }

    public static function defer(callable $callable): void
    {
        $coroutine = static::getCurrent();
        if ($coroutine instanceof static) {
            $coroutine->addDefer($callable);
        }
    }
}