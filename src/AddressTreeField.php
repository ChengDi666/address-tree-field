<?php

namespace Norgeit\AddressTreeField;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Authorizable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Norgeit\AddressTreeField\Domain\Relation\RelationHandlerFactory;
use Norgeit\AddressTreeField\Rules\ArrayRules;

class AddressTreeField extends Field
{
    use Authorizable;

    private $resourceClass;
    private $resourceName;
    private $manyToManyRelationship;

    public $component = 'address-tree-field';

    public $showOnIndex = false;

    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);
        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resource = $resource;

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function($request, $model, $attribute, $requestAttribute) use($resource) {
            if(is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $model::saved(function($model) use($attribute, $request) {

                    $factory = App::make(RelationHandlerFactory::class);

                    $handler = $factory->make($model->{$attribute}());

                    $handler->attach($model, $attribute, json_decode($request->{$attribute}, true));

                });
                unset($request->{$attribute});
            }
        });

        $this->withMeta([
            'idKey'             => 'id',
            'labelKey'          => 'name',
            'childrenKey'       => 'children',
            'activeKey'         => 'is_active',
            'multiple'          => true,
            'flatten'            => true,
            'searchable'        => true,
            'placeholder'       => __('Select Category'),
            'alwaysOpen'        => true,
            'sortValueBy'       => 'LEVEL',
            'disabled'          => false,
            'rtl'               => false,
            'maxHeight'         => 500,
            'isActiveFalse'     => false
        ]);

        /** @var Domain\Cache\Cache $requestCache */
        $forRequestCache = App::make(Domain\Cache\Cache::class);

        $tag = get_class($this->resourceClass::newModel());

        if(!$forRequestCache->has($tag))
        {
            $query = $this->resourceClass::buildIndexQuery(
                App::make(NovaRequest::class), $this->resourceClass::newModel()->newQuery()
            );

            $forRequestCache->put($tag, $query->where('type_value','!=', 101007)->get()->toTree());
        }

        $this->withMeta([
            'options' => $forRequestCache->get($tag)
        ]);
    }

    public function searchable(bool $searchable): AddressTreeField
    {
        $this->withMeta([
            'searchable' => $searchable,
        ]);

        return $this;
    }

    public function withIdKey(string $idKey = 'id'): AddressTreeField
    {
        $this->withMeta([
            'idKey' => $idKey,
        ]);

        return $this;
    }

    public function withLabelKey(string $labelKey = 'name'): AddressTreeField
    {
        $this->withMeta([
            'labelKey' => $labelKey,
        ]);

        return $this;
    }

    public function withChildrenKey(string $childrenKey): AddressTreeField
    {
        $this->withMeta([
            'childrenKey' => $childrenKey,
        ]);

        return $this;
    }

    public function withActiveKey(string $activeKey): AddressTreeField
    {
        $this->withMeta([
            'activeKey' => $activeKey,
        ]);

        return $this;
    }


    public function withPlaceholder(string $placeholder): AddressTreeField
    {
        $this->withMeta([
            'placeholder' => $placeholder,
        ]);

        return $this;
    }

    public function withMaxHeight(int $maxHeight): AddressTreeField
    {
        $this->withMeta([
            'maxHeight' => $maxHeight,
        ]);

        return $this;
    }

    public function withAlwaysOpen(bool $alwaysOpen): AddressTreeField
    {
        $this->withMeta([
            'alwaysOpen' => $alwaysOpen,
        ]);

        return $this;
    }

    public function withSortValueBy(string $sortBy): AddressTreeField
    {
        $this->withMeta([
            'sortValueBy' => $sortBy,
        ]);

        return $this;
    }

    public function withFlatten(bool $flatten): AddressTreeField
    {
        $this->withMeta([
            'flatten' => $flatten,
        ]);

        return $this;
    }

    public function isActiveFalseValue( $value = false ): AddressTreeField
    {
        $this->withMeta([
            'isActiveFalse' => $value,
        ]);

        return $this;
    }

    public function useSingleSelect(): AddressTreeField
    {
        $this->withMeta([
            'multiple' => false,
            'flatten' => false
        ]);

        return $this;
    }

    public function authorize(Request $request)
    {
        if(! $this->resourceClass::authorizable()) {
            return true;
        }

        if(! isset($request->resource)) {
            return false;
        }

        return call_user_func([ $this->resourceClass, 'authorizedToViewAny'], $request)
            && $request->newResource()->authorizedToAttachAny($request, $this->resourceClass::newModel())
            && parent::authorize($request);
    }

    public function rules($rules)
    {
        $rules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : (array)$rules;

        $this->rules = [ new ArrayRules($rules) ];

        return $this;
    }
}
