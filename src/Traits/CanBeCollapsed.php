<?php

namespace Mavericks\NovaNestedForm\Traits;

trait CanBeCollapsed
{

    /**
     * Set whether the forms should be opened on display.
     *
     * @param bool|string
     *
     * @return self
     */
    public function open($opened = true)
    {
        return $this->withMeta([
            'opened' => $opened,
        ]);
    }
}
