<?php

namespace app\common\components\entity;


use yii\db\ActiveQuery;

class EntityNestedCollection
{
    protected $rawData;

    protected $entityCollectionName;
    protected $junctionTableName;
    protected $primaryKey;
    protected $foreignKey;
    protected $isPlural = false;

    protected $linkFields = [];
    protected $junctionFields = [];
    protected $additionalFields = [];

    public function init(array $data, $plural = false)
    {
        $this->rawData = $data;
        $this->build($plural);
    }

    /**
     * @return mixed
     */
    public function getEntityCollectionName()
    {
        return $this->entityCollectionName;
    }

    /**
     * @return mixed
     */
    public function getJunctionTableName()
    {
        return $this->junctionTableName;
    }

    /**
     * @return array
     */
    public function getLinkFields(): array
    {
        return $this->linkFields;
    }

    /**
     * @return array
     */
    public function getJunctionFields(): array
    {
        return $this->junctionFields;
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return $this->isPlural;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @return array
     */
    public function getAdditionalFields(): array
    {
        return $this->additionalFields;
    }

    /**
     * @return callable
     */
    public function getJunctionRelationDecorator()
    {
        return function(ActiveQuery $query) {
            $coldata = current($this->rawData);

            foreach ($coldata['relations'] as $relation) {
                if (isset($relation['value'])) {
                    $query->andWhere([$relation['property'] => $relation['value']]);
                }
            }

            return $query;
        };
    }

    private function build($plural = false)
    {
        if ($plural) {
            $this->buildPlural();
        }
        else {
            $this->buildManyToMany();
        }
    }

    private function buildPlural()
    {
        $this->isPlural = true;

        $this->entityCollectionName = $this->rawData['link'];
        $this->foreignKey = $this->rawData['property'];

        $this->linkFields = [$this->foreignKey => 'id'];

        if (isset($this->rawData['additional_fields'])) {
            $this->additionalFields = $this->rawData['additional_fields'];
        }
    }

    private function buildManyToMany()
    {
        $this->entityCollectionName = $this->extractCollectionName();
        $this->junctionTableName = EntityInstance::tableNameFromEntityName(key($this->rawData));

        $coldata = current($this->rawData);

        $this->primaryKey = $coldata['relations'][0]['property'];
        $this->foreignKey = $coldata['relations'][1]['property'];

        $this->linkFields = ['id' => $this->primaryKey];
        $this->junctionFields = [$this->foreignKey => 'id'];
    }

    private function extractCollectionName()
    {
        $name = key($this->rawData);
        $parts = explode('-', $name);

        array_shift($parts);
        $colname = implode('-', $parts);

        return $colname;
    }
}