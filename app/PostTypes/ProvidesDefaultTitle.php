<?php

namespace App\PostTypes;

interface ProvidesDefaultTitle
{
    /**
     * Title to use when the post is saved without one.
     *
     * @param  array<string, mixed>  $metaData
     * @param  array<string, mixed>  $typeData
     */
    public function defaultTitle(array $metaData, array $typeData): string;
}
