<?php

namespace Mavericks\NovaNestedForm;

use Laravel\Nova\Panel;

class NestedFormPanel extends Panel
{
    /**
     * Nested form.
     *
     * @var NovaNestedForm
     */
    protected $nestedForm;

    /**
     * Constructor.
     * @param NovaNestedForm $nestedForm
     */
    public function __construct(NovaNestedForm $nestedForm)
    {
        $this->nestedForm = $nestedForm;

        $this->nestedForm->asPanel($this);

        parent::__construct(__('Update Related :resource', ['resource' => $this->nestedForm->name]), [$this->nestedForm]);
    }

    /**
     * Getter.
     * @param $key
     * @return
     */
    public function __get($key)
    {
        return key_exists($key, $this) ? parent::__get($key) : $this->nestedForm->$key;
    }

    /**
     * Setter.
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        key_exists($key, $this) ? parent::__set($key, $value) : $this->nestedForm->$key = $value;
    }

    /**
     * Caller.
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return method_exists($this, $method) ? parent::__call($method, $arguments) : call_user_func([$this->nestedForm, $method], ...$arguments);
    }
}
