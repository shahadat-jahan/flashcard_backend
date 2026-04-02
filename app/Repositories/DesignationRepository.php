<?php

namespace App\Repositories;

use App\Enums\DesignationStatus;
use App\Models\Designation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DesignationRepository extends Repository
{
    /**
     * Get all designation with optional filters.
     */
    public function getDesignationsWithFilter(?array $filters = null): LengthAwarePaginator
    {
        $query = Designation::query();

        if (Auth::user()->isUser()) {
            $query->where('status', DesignationStatus::ACTIVE);
        }

        if (isset($filters['name'])) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($filters['name']).'%']);
        }

        return $query->orderByDesc('id')->paginate($this->_limit);
    }

    /**
     * Find a designation
     */
    public function findById(int $id): Designation
    {
        return Designation::findOrFail($id);
    }

    /**
     * Get designation's posts.
     */
    public function getUsersByDesignationWithPagination(Designation $designation, ?int $limit = null): LengthAwarePaginator
    {
        return $designation->users()->orderByDesc('id')->paginate($limit ?? $this->_limit);
    }

    /**
     * Create a new designation.
     */
    public function createDesignation(array $data): Designation
    {
        return Designation::create($data)->refresh();
    }

    /**
     * Update a designation's information.
     */
    public function updateDesignation(Designation $designation, array $data): Designation
    {
        $designation->update($data);

        return $designation->refresh();
    }

    /**
     * Delete a Designations.
     */
    public function deleteDesignation(Designation $designation): bool
    {
        return $designation->delete();
    }
}
