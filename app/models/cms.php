<?php
namespace APP\Models;

use API\DB\ORM;
use API\DB\ORM\Entity;
use API\DB\Query;
use APP\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\ResultStatement;
use Exception;

/**
 * Class CMSModel
 * @package APP\Models
 */
class CMSModel extends Model {
    /**
     * @var ORM
     */
    private $orm;

    /**
     * @var array
     */
    private static $_instances = [];

    /**
     * CMSModel constructor.
     * @param string $name App name
     * @param bool $inheritance
     * @throws DBALException
     * @throws Exception
     */
    public function __construct($name, $inheritance = true) {
        parent::__construct();
        $this->orm = new ORM($name, $this->getConfigFromDB($name));
        if ($inheritance) {
            self::$_instances[$name] = $this;
        }
    }

    /**
     * @param string $name App name
     * @param bool $inheritance
     * @return CMSModel
     * @throws DBALException
     */
    public static function getInstance($name, $inheritance = true) {
        if ($inheritance && isset(self::$_instances[$name])) {
            return self::$_instances[$name];
        }
        return new static($name, $inheritance);
    }

    /**
     * Get model application ORM
     * @return ORM
     */
    public function getORM() {
        return $this->orm;
    }

    /**
     * Get application config from DB
     * @param string $name App name
     * @return object|null
     * @throws DBALException
     */
    public function getConfigFromDB($name) {
        /**
         * @var array|null $row
         */
        $row = $this->query
            ::getInstance()
            ->select('config')
            ->from('apps')
            ->where(['name' => $name])
            ->fetch()
            ->first();
        return isset($row['config']) ? json_decode($row['config']) : null;
    }

    /**
     * Save ORM config to DB
     * @param ORM $orm Actual Object-relational Model
     * @param bool $is_new Insert (true) or update (false) strategy
     * @return ResultStatement|int
     */
    public function saveConfig($orm, $is_new = false) {
        $keys = ['name' => $orm->name];
        $values = ['config' => json_encode($orm->getConfig())];
        return $is_new
            ? $this->query
                ::getInstance()
                ->insert('apps')
                ->values(array_merge($keys, $values))
                ->execute()
            : $this->query
                ::getInstance()
                ->update('apps')
                ->set($values)
                ->where($keys)
                ->execute();
    }

    /**
     * Get all data from ORM Entity
     * @param string $entityName Entity name
     * @param string|array $where Predicate 'id = 5' | ['id' => 5] | ['id' => [5,6,7]]
     * @param array $order Ordering ['id' => 'ASC'] | ['ordering' => 'DESC']
     * @param array $limit Limit [<count>] | [<offset>, <count>]
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getItems($entityName, $where = [], array $order = [], array $limit = []) {
        $entity = $this->getEntity($entityName);
        $table = !$entity->root ? $this->orm->name . '_' . $entity->name : $entity->name;
        $query = $this->query::getInstance();

        $keys = array_keys(
            array_filter($entity->tables[$table]->keys, function ($item) {
                return startsWith($item, 'PRIMARY KEY');
            })
        );
        if (!count($keys)) {
            throw new Exception("Can't determine primary key of entity " . $entity->name);
        }
        $groups = [
            $entity->name => [
                'name' => $entity->name,
                'key' => $keys[0],
            ],
        ];

        $query
            ->select([$entity->name => array_keys($entity->tables[$table]->fields)])
            ->from($table, $entity->name)
            ->where($where)
            ->order($order)
            ->limit($limit);

        $this->computeEntityRelations($entity, $groups, $query);
        //dbg($query->getQuery()->getSQL(), true);
        //dbg($this->group($query->fetch()->getValues(), !is_null($groups) ? $groups : [], $entity->name));
        return $this->group($query->fetch()->getValues(), !is_null($groups) ? $groups : [], $entity->name);
    }

    /**
     * Select data from ORM Entity
     * @param string $entityName Entity name
     * @param array $select Select
     * @param string|array $where Predicate 'id = 5' | ['id' => 5] | ['id' => [5,6,7]]
     * @param array $order Ordering ['id' => 'ASC'] | ['ordering' => 'DESC']
     * @param array $group Grouping ['id']
     * @param array $limit Limit [<count>] | [<offset>, <count>]
     * @return ArrayCollection
     * @throws DBALException
     * @throws Exception
     */
    public function selectItems($entityName, $select = [], $where = [], $order = [], $group = [], $limit = []) {
        $entity = $this->getEntity($entityName);
        $table = !$entity->root ? $this->orm->name . '_' . $entity->name : $entity->name;
        $query = $this->query::getInstance();

        $query
            ->select([$entity->name => array_keys($entity->tables[$table]->fields)])
            ->from($table, $entity->name)
            ->where($where)
            ->order($order);
        $this->computeEntityRelations($entity, $groups, $query);

        $query->getQuery()->resetQueryPart('select');
        $query
            ->select($select)
            ->group($group)
            ->limit($limit);
        //dbg($query->getQuery()->getSQL(), true);

        return $query->fetch();
    }

    /**
     * Get entity from ORM by name
     * @param string $entityName Entity name
     * @return Entity
     * @throws Exception
     */
    private function getEntity($entityName) {
        $index = array_search($entityName, array_column($this->orm->entities, 'name')); // entity
        if ($index !== false) {
            return $this->orm->entities[$index];
        } else {
            throw new Exception("Invalid entity name: {$entityName}");
        }
    }

    /**
     * Adding groups, query selections and joins based on entity relations
     * - [RU] Составление групп, и в query, select-ов и join-ов на основе связей сущности
     * @param Entity $entity
     * @param array|null $groups
     * @param Query $query
     */
    private function computeEntityRelations($entity, &$groups, &$query) {
        foreach ($this->orm->entities as $e) {
            $tmp = $this->getRelationsByEntity($e, $entity->name);
            if (!is_array($tmp)) {
                dbg(['computeEntityRelations', $tmp, $e, $entity->name]);
            }
            foreach ($tmp as $relation) {
                // add entity relation to structural grouping array
                if (isset($groups[$entity->name])) {
                    $groups[$e->name] = [
                        'name' => $e->name,
                        'key' => $relation->key,
                        'relation' => [
                            $entity->name => array_merge($groups[$entity->name], [
                                'relationKey' => $relation->field,
                            ]),
                        ],
                    ];
                }
                $query->select([
                    $e->name => array_keys($e->tables[$this->orm->name . '_' . $e->name]->fields),
                ]);
                $query->leftJoin(
                    $entity->name,
                    $this->orm->name . '_' . $e->name,
                    $e->name,
                    "{$e->name}.{$relation->field} = {$relation->entity}.{$relation->key}"
                );

                $this->computeEntityRelations($e, $groups, $query);
            }
        }
    }

    /**
     * @param Entity $entity
     * @param string $relationEntityName
     * @return array
     */
    private function getRelationsByEntity($entity, $relationEntityName) {
        $relations = [];
        if (isset($entity->relations) && $entity->relations) {
            // find entity relations
            foreach ($entity->relations as $relation) {
                if ($relationEntityName == $relation->entity) {
                    $relations[] = $relation;
                }
            }
        }
        return $relations;
    }

    /**
     * Update data of entity
     * @param string $entityName Entity name
     * @param array $items Entity data
     * @param array $additional Additional item data
     * @return boolean
     * @throws Exception
     */
    public function updateItems($entityName, $items, $additional = []) {
        $index = array_search($entityName, array_column($this->orm->entities, 'name')); // entity
        if ($index !== false) {
            $entity = $this->orm->entities[$index];
            $table = !$entity->root ? $this->orm->name . '_' . $entity->name : $entity->name;

            // entity options
            $options = array_keys(get_object_vars((object) $entity->options));

            // relation options
            // (keys - relation entities names, values - relations to entity)
            $relations = [];
            foreach ($this->orm->entities as $e) {
                $entityRelations = $this->getRelationsByEntity($e, $entityName);
                if (count($entityRelations)) {
                    $relations[$e->name] = $entityRelations;
                }
            }

            $ormConfig = $this->orm->getConfig();
            if (is_null($ormConfig) || !isset($ormConfig->orm)) {
                throw new Exception("Can't update entity {$entityName}, application has no entities");
            }

            // primary key
            $keys = [];
            $ormEntity = null;
            $ormEntities = array_values(
                array_filter($ormConfig->orm, function ($item) use ($entityName) {
                    return $item->name == $entityName;
                })
            );
            //if ($entity->name === 'entities') return [$ormEntity];
            if (count($ormEntities)) {
                $ormEntity = $ormEntities[0];
                foreach ($ormEntity->keys as $keyName => $ormKey) {
                    if (isset($ormKey->type) && $ormKey->type === 'PRIMARY') {
                        if (isset($ormKey->fields)) {
                            $keys = array_merge($keys, $ormKey->fields);
                        } else {
                            $keys[] = $keyName;
                        }
                    }
                }
            } else {
                throw new Exception("Can't update entity {$entityName}, entity not found");
            }
            if (count($keys) === 0) {
                throw new Exception("Can't update entity {$entityName}, entity not have a primary key");
            }

            // saving
            foreach ($items as $item) {
                $keyFields = array_intersect_key((array) $item, array_flip($keys));
                $dataFields = array_merge(
                    $additional,
                    array_intersect_key((array) $item, array_diff_key(array_flip($options), array_flip($keys)))
                );
                $relationsFields = array_intersect_key((array) $item, $relations);
                $query = $this->query::getInstance();
                if (count($keyFields) == count($keys)) {
                    if (count($dataFields)) {
                        // updating
                        $query
                            ->update($table)
                            ->set($dataFields)
                            ->where($keyFields)
                            ->execute();
                    }
                } else {
                    // inserting
                    $query
                        ->insert($table)
                        ->values($dataFields)
                        ->execute();
                    foreach ($ormEntity->options as $field => $option) {
                        if (isset($option->auto_increment) && $option->auto_increment) {
                            $keyFields[$field] = $query->lastInsertId();
                        }
                    }
                }
                foreach ($relationsFields as $entityName => $entityItems) {
                    $entityAdditional = [];
                    foreach ($relations[$entityName] as $relation) {
                        $entityAdditional[$relation->field] = $keyFields[$relation->key];
                    }
                    $res = $this->updateItems($entityName, $entityItems, $entityAdditional);
                    if ($res !== true) {
                        return $res;
                    }
                }
            }
        } else {
            throw new Exception("Invalid entity name: {$entityName}");
        }

        return true;
    }
}
