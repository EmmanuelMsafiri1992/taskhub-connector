<?php

namespace EISALL\TaskHubConnector\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Generic CRUD over the resources the host app declared in config. Writes go
 * through fill(), so the model's fillable list, casts, mutators and events
 * keep applying -- the connector adds reach, not new rules.
 */
class ResourceController
{
    public function index(Request $request, string $resource)
    {
        $definition = $this->definition($resource);

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        return $definition['model']::query()->paginate($perPage);
    }

    public function show(string $resource, $id)
    {
        return $this->find($resource, $id);
    }

    public function store(Request $request, string $resource)
    {
        $definition = $this->definition($resource, forWrite: true);

        $data = $this->validated($request, $definition, 'store');

        $model = new $definition['model']();
        $model->fill($data)->save();

        return response()->json($model->refresh(), 201);
    }

    public function update(Request $request, string $resource, $id)
    {
        $definition = $this->definition($resource, forWrite: true);

        $model = $this->find($resource, $id);

        $data = $this->validated($request, $definition, 'update');

        $model->fill($data)->save();

        return response()->json($model->refresh());
    }

    public function destroy(string $resource, $id)
    {
        $this->definition($resource, forWrite: true);

        $this->find($resource, $id)->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array{model: class-string<Model>, readonly?: bool, rules?: array}
     */
    protected function definition(string $resource, bool $forWrite = false): array
    {
        $definition = config("taskhub.resources.$resource");

        if (! $definition) {
            throw new HttpException(404, "Unknown resource [$resource].");
        }

        if ($forWrite && ($definition['readonly'] ?? false)) {
            throw new HttpException(405, "Resource [$resource] is read only.");
        }

        return $definition;
    }

    protected function find(string $resource, $id): Model
    {
        return $this->definition($resource)['model']::query()->findOrFail($id);
    }

    protected function validated(Request $request, array $definition, string $action): array
    {
        $rules = $definition['rules'][$action] ?? null;

        return $rules ? $request->validate($rules) : $request->all();
    }
}
