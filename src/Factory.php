<?php

namespace Dubroquin\Bouncer;

use Illuminate\Auth\Access\Gate;
use Illuminate\Cache\ArrayStore;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Dubroquin\Bouncer\Contracts\Clipboard as ClipboardContract;

class Factory
{
    /**
     * The cache instance to use for the clipboard.
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $cache;

    /**
     * The clipboard instance to use.
     *
     * @var \Dubroquin\Bouncer\Contracts\Clipboard
     */
    protected $clipboard;

    /**
     * The gate instance to use.
     *
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    protected $gate;

    /**
     * The user model to use for the gate.
     *
     * @var \Illuminate\Datbase\Eloquent\Model
     */
    protected $user;

    /**
     * Create an instance of Bouncer.
     *
     * @param  \Illuminate\Datbase\Eloquent\Model
     * @return \Dubroquin\Bouncer\Bouncer
     */
    public function create(Model $user = null)
    {
        if ($user) {
            $this->user = $user;
        }

        $clipboard = $this->getClipboard();

        $clipboard->registerAt($gate = $this->getGate());

        return (new Bouncer($clipboard))->setGate($gate);
    }

    /**
     * Set the cache instance to use for the clipboard.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $cache
     * @return $this
     */
    public function withCache(Store $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Set the instance of the clipboard to use.
     *
     * @param  \Dubroquin\Bouncer\Contracts\Clipboard  $clipboard
     * @return $this
     */
    public function withClipboard(ClipboardContract $clipboard)
    {
        $this->clipboard = $clipboard;

        return $this;
    }

    /**
     * Set the gate instance to use.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return $this
     */
    public function withGate(GateContract $gate)
    {
        $this->gate = $gate;

        return $this;
    }

    /**
     * Set the user model to use for the gate.
     *
     * @param  \Illuminate\Datbase\Eloquent\Model  $user
     * @return $this
     */
    public function withUser(Model $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get an instance of the clipboard.
     *
     * @return \Dubroquin\Bouncer\Contracts\Clipboard
     */
    protected function getClipboard()
    {
        return $this->clipboard ?: new CachedClipboard($this->getCacheStore());
    }

    /**
     * Get an instance of the cache store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    protected function getCacheStore()
    {
        return $this->cache ?: new ArrayStore;
    }

    /**
     * Get an instance of the gate.
     *
     * @return \Illuminate\Contracts\Auth\Access\Gate
     */
    protected function getGate()
    {
        if ($this->gate) {
            return $this->gate;
        }

        return new Gate($this->getContainer(), function () {
            return $this->user;
        });
    }

    /**
     * Get the container singleton.
     *
     * @return \Illuminate\Container\Container
     */
    protected function getContainer()
    {
        if (is_null(Container::getInstance())) {
            Container::setInstance(new Container);
        }

        return Container::getInstance();
    }
}
