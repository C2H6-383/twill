<?php

namespace A17\Twill\Services\Listings\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * This filter is used internally and will not have any effect when used in your quickfilters/filters list.
 */
class FreeTextSearch extends TwillBaseFilter
{
    protected array $searchColumns = [];
    protected mixed $searchQuery = null;
    protected ?string $searchString = null;

    public function applyFilter(Builder $builder): Builder
    {
        if (!empty($this->searchString) && ($this->searchColumns !== [] || isset($this->searchQuery))) {
            /** @var \A17\Twill\Models\Model $builderModel */
            $translatedAttributes = $builder->getModel()->getTranslatedAttributes();
            $builder->where(function (Builder $builder) use ($translatedAttributes) {
                if (isset($this->searchQuery)) {
                    call_user_func($this->searchQuery, $builder, $this->searchString, $translatedAttributes);
                }
                foreach ($this->searchColumns as $column) {
                    if (in_array($column, $translatedAttributes, true)) {
                        $builder->orWhereTranslationLike($column, "%$this->searchString%");
                    } else {
                        $builder->orWhere($column, getLikeOperator(), "%$this->searchString%");
                    }
                }
            });
        }

        return $builder;
    }

    public function searchQuery(?callable $query): static
    {
        $this->searchQuery = $query;

        return $this;
    }

    public function searchFor(string $searchString): static
    {
        $this->searchString = $searchString;

        return $this;
    }

    public function searchColumns(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }
}
