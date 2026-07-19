<?php
namespace App\Repositories;
use App\Core\v1\Division\Models\Division;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Providers\AuthServiceProvider;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Mockery\Exception;

abstract class ModelRepository
{
    protected Model $model;
    public Builder $query;
    protected int $paginate = 10;
    protected int $page = 1;
    protected string $orderBy = 'desc';
    protected string $orderField = 'id';
    protected array $filters = [];
    protected bool $simpleFilter = true;
    protected string $filterMode = 'and';
    abstract public function model(): Model;
    protected AuthorizationService $authService;

    protected array $wheres = [];

    public function __construct()
    {
        $this->model = $this->model();
        $this->query = ($this->model())->newQuery();
    }

    public function useAuthService(AuthorizationService $authService): self
    {
        $this->authService = $authService;

        return $this;
    }
    /*
     * Need to find a way to declare an abstract function with dynamic arguments
     * abstract public function store(): Model;
     * abstract public function update(): Model;
     */

    public function use(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function alterQuery(Builder $query)
    {
        $this->query = $query;

        return $this;
    }

    public function newQuery($useAuthService = false, string $userIdQuery = 'user_id'): self
    {
        $this->query = $this->model()->newQuery();

        if ($useAuthService) {
            $this->query = $this->authService->scopedQuery($this->query, $userIdQuery);
        }

        return $this;
    }

    public function join(string $table, string $foreignKey)
    {
        $this->query = $this->query->join($table, "{$table}.{$foreignKey}", "=", $this->query->getModel()->getTable() . '.id');
        $this->query = $this->query->select([
            "{$this->query->getModel()->getTable()}.*"
        ]);

        return $this;
    }

    public function find(int $id, bool $continueQuery = false)
    {
        $model = $this->query->withoutGlobalScopes()->find($id);

        if (empty($model)) {
            return null;
        }

        $this->model = $model;

        if (!$continueQuery) {
            return $this->model;
        }

        return $this;
    }

    public function page(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function paginate(int $paginate): self
    {
        $this->paginate = $paginate;
        return $this;
    }

    public function search($query, array $fields): self
    {
        if (!empty($query) && ($query != 'null')) {
            $this->query = $this->query->where(function($q) use ($fields, $query) {
                foreach ($fields as $field) {
                    $q->orWhereRaw('LOWER(' . $field . ') LIKE ?', ['%' . strtolower($query) . '%']);
                }
            });
        }
        return $this;
    }

    public function orderBy(string $field, string $order): self
    {
        $this->orderField = $field;
        $this->orderBy = $order;

        return $this;
    }

    public function whereInMyArray(string $field, array $values): self
    {
        // Remove duplicates & reindex
        $values = array_values(array_unique($values));

        if (!empty($values)) {
            $this->query->whereIn($field, $values);
        }

        return $this;
    }

    public function where(string $field, $value): self
    {
        $this->wheres[] = [$field, '=', $value];
        return $this;
    }

    public function whereV2(string $field, $value): self
    {
        $this->query->where($field, '=', $value);
        return $this;
    }

    public function filterDate(string $field, string $date, string $operation)
    {
        $this->query = $this->query->where($field, $operation, $date);

        return $this;
    }

    public function filterRelation($relationship, $field, $value): self
    {
        $this->query = $this->query->{$relationship}()->where($field, $value);

        return $this;
    }

    public function filterHas($relationship, $field, $value): self
    {
        $this->query = $this->query->whereHas($relationship, function($q) use ($field, $value){
            $q->where($field, $value);
        });

        return $this;
    }

    public function filter(array $fields, bool $simple = true, $filterImmidiately = false, $filterMode = 'and'): self
    {
        $this->filters = $fields;
        $this->simpleFilter = $simple;
        $this->filterMode = $filterMode;

        if ($filterImmidiately) {
            if ($this->simpleFilter && !empty($this->filters)) {
                $this->query = $this->simpleFilter($this->query, $this->filters);
            }
        }
        return $this;
    }

    public function filterWhereIn($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value); // <-- handles arrays like ['pending_0', 'pending_50']
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    public function filters(array $fields, bool $simple = true, $filterImmediately = false, $filterMode = 'and'): self
    {
        $this->filters = $fields;
        $this->simpleFilter = $simple;
        $this->filterMode = $filterMode;

        if ($filterImmediately) {

            if ($this->simpleFilter && !empty($this->filters)) {
                $this->query = $this->applyFilters($this->query, $this->filters);
            }
        }
        return $this;
    }

    /*
    private function simpleFilter(Builder $query, array $fields): Builder
    {
        if ($this->filterMode === 'or') {
            $query->where(function($q) use ($fields) {
                foreach ($fields as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $q->orWhere($key, 'LIKE', "%{$v}%");
                        }
                    } else {
                        $q->orWhere($key, 'LIKE', "%{$value}%");
                    }
                }
            });

            return $query;
        } else {
            foreach ($fields as $key => $value) {
                $query->where($key, $value);
            }
            return $query;
        }
    }
    */

    private function simpleFilter(Builder $query, array $fields): Builder
    {
        if ($this->filterMode === 'or') {
            $query->where(function($q) use ($fields) {
                foreach ($fields as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $q->orWhere($key, 'LIKE', "%{$v}%");
                        }
                    } else {
                        $q->orWhere($key, 'LIKE', "%{$value}%");
                    }
                }
            });

            return $query;

        } else { // AND mode
            foreach ($fields as $key => $value) {
                if (is_array($value)) {
                    $query->whereIn($key, $value); // ✅ this adds array support for exact matching
                } else {
                    $query->where($key, $value);   // ✅ standard behavior for single value
                }
            }

            return $query;
        }
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {

        foreach ($filters as $column => $value) {
            if ($column === 'contracts.created_at' && is_array($value) && count($value) === 2) {
                // Format dates properly to match TIMESTAMP format
                $startDate = date('Y-m-d H:i:s', strtotime($value[0] . ' 00:00:00'));
                $endDate = date('Y-m-d H:i:s', strtotime($value[1] . ' 23:59:59'));

                // Only apply whereBetween, do NOT add extra where
                $query->whereBetween($column, [$startDate, $endDate]);
            }
        }

        // Exclude records that have a value in deleted_at (soft deleted)
        if (Schema::hasColumn($this->model()->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        return $query;
    }


    public function count(): int
    {
        if ($this->simpleFilter && !empty($this->filters)) {
            $this->query = $this->simpleFilter($this->query, $this->filters);
        } // Else -> complex OR custom filters

        return $this->query->paginate()->total();
    }

    public function get(): Paginator
    {
        if ($this->simpleFilter && !empty($this->filters)) {
            $this->query = $this->simpleFilter($this->query, $this->filters);
        } // Else -> complex OR custom filters
        return $this->query
            ->orderBy($this->orderField, $this->orderBy)
            ->simplePaginate($this->paginate, ['*'], 'page', $this->page);
    }

    public function all(): Collection
    {
        return $this->query->orderBy($this->orderField, $this->orderBy)->get();
    }

    public function allV2(): Collection
    {
        return $this->query->orderBy($this->orderField, $this->orderBy)->get();
    }

    public function allActive(): Collection
    {
        $query = Division::query();

        foreach ($this->wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        if ($this->orderField && $this->orderBy) {
            $query->orderBy($this->orderField, $this->orderBy);
        }

        return $query->get();
    }


    public function item(): Model
    {
        return $this->query->first();
    }

    public function save(array $fields): Model
    {
        $newModel = $this->model();
        foreach ($fields as $key => $value) {
            $newModel->{$key} = $value;
        }
        $newModel->save();
        return $newModel;
    }

    public function trash(): bool
    {
        return $this->model->delete();
    }

    public function delete(): bool
    {
        return $this->model->forceDelete();
    }
}
