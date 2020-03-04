<?php

namespace Mavericks\NovaNestedForm\Requests;

use Laravel\Nova\Http\Requests\CreateResourceRequest;

class CustomCreateResourceRequest extends CreateResourceRequest
{
    protected $customResource = null;

    public function setCustomResource($customResourceClass)
    {
        $this->customResource = $customResourceClass;
    }

    public function resource()
    {
        return $this->customResource;
    }
}
