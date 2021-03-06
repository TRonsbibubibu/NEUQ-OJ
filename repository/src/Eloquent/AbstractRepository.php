<?php
namespace NEUQOJ\Repository\Eloquent;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use NEUQOJ\Repository\Contracts\RepositoryInterface;
use NEUQOJ\Repository\Models\News;

/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-11
 * Time: 下午9:39
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /** @var Model $model */
    private $model;

    function __construct(Container $app)
    {
        $this->model = $app->make($this->model());
    }

    abstract function model();

    function all(array $columns = ['*']):array
    {
        return $this->model->get($columns);
    }

    function get(int $id, array $columns = ['*'],string $primary = 'id'):array
    {
        return $this->model
            ->where($primary, $id)
            ->get($columns);
    }

    function getBy(string $param,string $value,array $columns = ['*']):array {
        return $this->model
            ->where($param, $value)
            ->get($columns);
    }

    function getByMult(array $params, array $columns = ['*']):array {
        return $this->model
            ->where($params)
            ->get($columns);
    }

    function insert(array $data):bool
    {
        if($this->model->timestamps){
            $current = new Carbon();

            if(! is_array(reset($data))){
                $data = array_merge($data,
                    [
                        'created_at' => $current,
                        'updated_at' => $current,
                    ]);
            }else{
                foreach ($data as  $key => $value) {
                    $data[$key] = array_merge($value,
                        [
                            'created_at' => $current,
                            'updated_at' => $current,
                        ]);
                }
            }

        }
        return $this->model
            ->insert($data);
    }

    function update(array $data,int $id, string $attribute="id"):int
    {
        if($this->model->timestamps){
            $current = new Carbon();

            if(! is_array(reset($data))){
                $data = array_merge($data,
                    [
                        'updated_at' => $current,
                    ]);
            }else{
                foreach ($data as  $key => $value) {
                    $data[$key] = array_merge($value,
                        [
                            'updated_at' => $current,
                        ]);
                }
            }

        }
        return $this->model
            ->where($attribute, '=', $id)
            ->update($data);
    }

    function delete(int $id):int
    {
        return $this->model
            ->destory($id);
    }

    function paginate(int $page = 1,int $size = 15,array $param = [],array $columns = ['*']):array
    {
        $qb = $this->model;
        if(!empty($size))
            $qb->where($param);
        return $qb
            ->skip($size * --$page)
            ->take($size)
            ->get($columns);

    }

    private function freshTimestamp():Carbon
    {
        return new Carbon;
    }
}