<?php

namespace Dubroquin\Bouncer\Database\Concerns;

use Dubroquin\Bouncer\Clipboard;
use Illuminate\Container\Container;

trait Authorizable
{
    /**
     * Determine if the authority has a given ability.
     *
     * @param  string  $ability
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function can($ability, $model = null)
    {
        return $this->getClipboardInstance()->check($this, $ability, $model);
    }

    /**
     * Determine if the authority does not have a given ability.
     *
     * @param  string  $ability
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function cant($ability, $model = null)
    {
        return ! $this->can($ability, $model);
    }

    /**
     * Determine if the authority does not have a given ability.
     *
     * @param  string  $ability
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function cannot($ability, $model = null)
    {
        return $this->cant($ability, $model);
    }

    /**
     * Get an instance of the bouncer's clipboard.
     *
     * @return \Dubroquin\Bouncer\Clipboard
     */
    protected function getClipboardInstance()
    {
        $container = Container::getInstance() ?: new Container;

        return $container->make(Clipboard::class);
    }
}
