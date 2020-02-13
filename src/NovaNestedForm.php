<?php

namespace Mavericks\NovaNestedForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Mavericks\NovaNestedForm\Traits\CanBeCollapsed;
use Mavericks\NovaNestedForm\Traits\FillsSubAttributes;
use Mavericks\NovaNestedForm\Traits\HasChildren;
use Mavericks\NovaNestedForm\Traits\HasHeading;
use Mavericks\NovaNestedForm\Traits\HasLimits;
use Mavericks\NovaNestedForm\Traits\HasSchema;
use Mavericks\NovaNestedForm\Traits\HasSubfields;

class NovaNestedForm extends Field
{
    use HasChildren, HasSchema, HasSubfields, HasLimits, HasHeading, CanBeCollapsed, FillsSubAttributes;

    /**
     * Constants for placeholders.
     */
    const INDEX = '{{index}}';
    const ATTRIBUTE = '__attribute';
    const ID = '__id';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-nested-form';

    /**
     * The class of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The instance of the related resource.
     *
     * @var string
     */
    public $resourceInstance;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * The name of the Eloquent relationship.
     *
     * @var string
     */
    public $viaRelationship;

    /**
     * The type of the Eloquent relationship.
     *
     * @var string
     */
    public $relationType;

    /**
     * The current request instance.
     *
     * @var NovaRequest
     */
    protected $request;

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var bool
     */
    public $showOnDetail = false;

    /**
     * Registered after callback.
     *
     * @var array
     */
    public $afterFillCallback;

    /**
     * Registered before callback.
     *
     * @var array
     */
    public $beforeFillCallback;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct(string $name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceInstance = new $resource($resource::newModel());
        $this->resourceName = $resource::uriKey();
        $this->viaRelationship = $this->attribute;
        $this->setRequest();
    }

    /**
     * Determine if the field should be displayed for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return call_user_func([$this->resourceClass, 'authorizedToViewAny'], $request) && parent::authorize($request);
    }

    /**
     * Register a global callback or a callback for
     * specific attributes (children) after it has been filled.
     *
     * @param callable $callback
     *
     * @return self
     */
    public function afterFill(callable $callback)
    {
        $this->afterFillCallback = $callback;

        return $this;
    }

    /**
     * Register a global callback or a callback for
     * specific attributes (children) before it has been filled.
     *
     * @param callable $callback
     *
     * @return self
     */
    public function beforeFill(callable $callback)
    {
        $this->beforeFillCallback = $callback;

        return $this;
    }

    /**
     * Resolve the form fields.
     *
     * @param $resource
     * @param $attribute
     *
     * @return void
     * @throws \Exception
     */
    public function resolve($resource, $attribute = null)
    {
        try {
            $this->setRelationType($resource)->setViaResourceInformation($resource)->setSchema()->setChildren($resource);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Guess the type of relationship for the nested form.
     *
     * @param Model $resource
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function setRelationType(Model $resource)
    {
        if (!method_exists($resource, $this->viaRelationship)) {
            throw new \Exception('The relation "' . $this->viaRelationship . '" does not exist on resource ' . get_class($resource) . '.');
        }

        return $this->withMeta([
            Str::snake((new \ReflectionClass($resource->{$this->viaRelationship}()))->getShortName()) => true,
        ]);
    }

    /**
     * Set the viaResource information as meta.
     *
     * @param Model $resource
     *
     * @return self
     */
    protected function setViaResourceInformation(Model $resource)
    {
        return $this->withMeta([
            'viaResource' => Nova::resourceForModel($resource)::uriKey(),
            'viaResourceId' => $resource->id,
            'INDEX' => self::INDEX,
            'ATTRIBUTE' => self::ATTRIBUTE,
            'ID' => self::ID,
        ]);
    }

    /**
     * Set the current request instance.
     *
     * @param Request $request
     * @return self
     */
    protected function setRequest(Request $request = null)
    {
        $this->request = $request ?? NovaRequest::createFrom(RequestFacade::instance());

        return $this;
    }

    /**
     * Checks whether the current relationship has many children.
     *
     * @return bool
     */
    protected function isManyRelationship()
    {
        return isset($this->meta['has_many']) || isset($this->meta['morph_many']);
    }

    /**
     * Checks whether the user is using Nova > 2.
     *
     * @return  bool
     */
    protected function isUsingNova2()
    {
        return Str::startsWith(Nova::version(), '2');
    }


}
