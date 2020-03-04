<?php

namespace Mavericks\NovaNestedForm;

class NestedFormChild extends NestedFormSchema
{
    /**
     * Name of the fields' filter method.
     *
     * @var string
     */
    protected static $filterMethod = 'removeNonUpdateFields';

    /**
     * Get the current heading.
     */
    protected function heading()
    {
        $heading = isset($this->parentForm->heading) ? $this->parentForm->heading : $this->parentForm::wrapIndex() . '. ' . $this->parentForm->singularLabel;

        return str_replace($this->parentForm::wrapIndex(), $this->index + 1, $heading);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'resourceId' => $this->model->id,
            $this->parentForm->keyName => $this->model->id
        ]);
    }
}
