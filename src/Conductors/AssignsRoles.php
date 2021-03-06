<?php

namespace Dubroquin\Bouncer\Conductors;

use Dubroquin\Bouncer\Helpers;
use Illuminate\Support\Collection;
use Dubroquin\Bouncer\Database\Models;
use Illuminate\Database\Eloquent\Model;

class AssignsRoles
{
    /**
     * The roles to be assigned.
     *
     * @var array
     */
    protected $roles;

    /**
     * Constructor.
     *
     * @param \Dubroquin\Bouncer\Database\Role|string|array  $role
     */
    public function __construct($roles)
    {
        $this->roles = is_array($roles) ? $roles : [$roles];
    }

    /**
     * Assign the roles to the given authority.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array|int  $authority
     * @return bool
     */
    public function to($authority)
    {
        $authorities = is_array($authority) ? $authority : [$authority];

        $roles = $this->roles();

        foreach (Helpers::mapAuthorityByClass($authorities) as $class => $ids) {
            $this->assignRoles($roles, $class, new Collection($ids));
        }

        return true;
    }

    /**
     * Get the provided roles (creating the non-existent ones).
     *
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    protected function roles()
    {
        list($models, $names) = Helpers::partition($this->roles, function ($role) {
            return $role instanceof Model;
        });

        if ($names->count()) {
            $models = $models->merge($this->findOrCreateRoles($names));
        }

        return $models;
    }

    /**
     * Find or create roles by name.
     *
     * @param  \Illuminate\Support\Collection<string>  $names
     * @return array
     */
    protected function findOrCreateRoles(Collection $names)
    {
        if ($names->count() == 0) {
            return [];
        }

        $existing = Models::role()->whereIn('name', $names->all())->get();

        $names = $names->diff($existing->pluck('name'));

        return $existing->merge($this->createRoles($names))->all();
    }

    /**
     * Create roles with the given names.
     *
     * @param  \Illuminate\Support\Collection  $names
     * @return \Illuminate\Support\Collection<\Illuminate\Database\Eloquent\Model>
     */
    protected function createRoles(Collection $names)
    {
        return $names->map(function ($name) {
            return Models::role()->create(compact('name'));
        });
    }

    /**
     * Assign the given roles to the given authorities.
     *
     * @param  \Illuminate\Suuport\Collection  $roles
     * @param  string $authorityClass
     * @param  \Illuminate\Suuport\Collection  $authorityIds
     * @return void
     */
    protected function assignRoles(Collection $roles, $authorityClass, Collection $authorityIds)
    {
        $roleIds = $roles->map(function ($model) {
            return $model->getKey();
        });

        $morphType = (new $authorityClass)->getMorphClass();

        $records = $this->buildAttachRecords($roleIds, $morphType, $authorityIds);

        $existing = $this->getExistingAttachRecords($roleIds, $morphType, $authorityIds);

        $this->createMissingAssignRecords($records, $existing);
    }

    /**
     * Get the pivot table records for the roles already assigned.
     *
     * @param  \Illuminate\Suuport\Collection  $roleIds
     * @param  string $morphType
     * @param  \Illuminate\Suuport\Collection  $authorityIds
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingAttachRecords($roleIds, $morphType, $authorityIds)
    {
        $records = $this->newPivotTableQuery()
                        ->whereIn('role_id', $roleIds->all())
                        ->whereIn('entity_id', $authorityIds->all())
                        ->where('entity_type', $morphType)
                        ->get();

        return new Collection($records);
    }

    /**
     * Build the raw attach records for the assigned roles pivot table.
     *
     * @param  \Illuminate\Suuport\Collection  $roleIds
     * @param  string $morphType
     * @param  \Illuminate\Suuport\Collection  $authorityIds
     * @return \Illuminate\Support\Collection
     */
    protected function buildAttachRecords($roleIds, $morphType, $authorityIds)
    {
        return $roleIds->map(function ($roleId) use ($morphType, $authorityIds) {
            return $authorityIds->map(function ($authorityId) use ($roleId, $morphType) {
                return [
                    'role_id' => $roleId,
                    'entity_id' => $authorityId,
                    'entity_type' => $morphType,
                ];
            });
        })->collapse();
    }

    /**
     * Save the non-existing attach records in the DB.
     *
     * @param  \Illuminate\Support\Collection  $records
     * @param  \Illuminate\Support\Collection  $existing
     * @return void
     */
    protected function createMissingAssignRecords(Collection $records, Collection $existing)
    {
        $existing = $existing->keyBy(function ($record) {
            return $this->getAttachRecordHash((array) $record);
        });

        $records = $records->reject(function ($record) use ($existing) {
            return $existing->has($this->getAttachRecordHash($record));
        });

        $this->newPivotTableQuery()->insert($records->all());
    }

    /**
     * Get a string identifying the given attach record.
     *
     * @param  array  $record
     * @return string
     */
    protected function getAttachRecordHash(array $record)
    {
        return $record['role_id'].$record['entity_id'].$record['entity_type'];
    }

    /**
     * Get a query builder instance for the assigned roles pivot table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newPivotTableQuery()
    {
        return Models::newQueryBuilder()->from(Models::table('assigned_roles'));
    }
}
