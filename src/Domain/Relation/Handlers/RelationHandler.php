<?php
namespace Norgeit\AddressTreeField\Domain\Relation\Handlers;

interface RelationHandler
{
    public function relation(): string;

    public function attach( $model, $relationship, $values): void;

    public function retrieve($model, $relationship, $idKey);
}
