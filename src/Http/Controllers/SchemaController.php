<?php

namespace EISALL\TaskHubConnector\Http\Controllers;

/**
 * The app describes its manageable surface. TaskHub renders its generic
 * management UI from this response, so it never needs app-specific code.
 */
class SchemaController
{
    public function __invoke()
    {
        $resources = [];

        foreach (config('taskhub.resources', []) as $name => $definition) {
            $model = new $definition['model']();

            $resources[$name] = [
                'model' => class_basename($definition['model']),
                'readonly' => (bool) ($definition['readonly'] ?? false),
                'fillable' => $model->getFillable(),
                'hidden' => $model->getHidden(),
                'casts' => array_map(
                    static fn ($cast) => is_string($cast) ? $cast : 'custom',
                    $model->getCasts()
                ),
            ];
        }

        return response()->json([
            'app' => config('taskhub.slug'),
            'resources' => $resources,
        ]);
    }
}
