<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    public function scopeFilter(Builder $query)
    {
        $request = request();
        $operator = [
            'eq' => '=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'like' => 'like',
            'contains' => 'like',
            'in' => 'in'
        ];

        if ($request->filled('filters')) {
            foreach ($request->filters as $column => $value) {

                if (!in_array($column, $this->fillable ?? [])  || !in_array($column, $this->filterable ?? []))
                    continue;

                $value = !is_array($value) ? ['eq' => $value] : $value;

                foreach ($value as $ops => $value1) {
                    if (!isset($operator[$ops]))
                        continue;
                    if ($ops == 'in')
                        $query->whereIn($column, is_array($value1) ? $value1 : explode(',', $value1));
                    elseif ($ops == 'contains')
                        $query->where($column, $operator[$ops], "%$value1%");
                    else
                        $query->where($column, $operator[$ops], $value1);

                    $query->orderBy($column);
                }
            }
        }
    }
}
