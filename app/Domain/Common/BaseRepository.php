<?php

namespace App\Domain\Common;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public abstract function model(): string;

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return call_user_func($this->model().'::query');
    }

    /**
     * @param $id
     * @param array $columns
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * @param string $attribute
     * @param $value
     * @param array $columns
     * @return Builder|Model|object|null
     */
    public function findBy(string $attribute, $value, array $columns = ['*'])
    {
        return $this->query()->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return Collection
     */
    public function getByManyId(array $ids, array $columns = ['*']): Collection
    {
        return $this->query()->whereKey($ids)->get($columns);
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * @param callable|null $callback
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(callable $callback = null, int $perPage = 10, array $columns = array('*')): LengthAwarePaginator
    {
        $query = $this->query();
        !is_null($callback) && $query = call_user_func($callback, $query);
        return $query->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return Builder|Model
     */
    public function create(array $data)
    {
        return $this->query()->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @param string $attribute
     * @return int
     */
    public function update($id, array $data, string $attribute = ''): int
    {
        if ($attribute)
            return $this->query()->where($attribute, '=', $id)->update($data);
        else
            return $this->query()->whereKey($id)->update($data);
    }

    /**
     * @param $id
     * @return int
     */
    public function delete($id): int
    {
        return $this->query()->whereKey($id)->delete();
    }
}
