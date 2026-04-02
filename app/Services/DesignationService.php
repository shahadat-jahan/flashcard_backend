<?php

namespace App\Services;

use App\Models\Designation;
use App\Repositories\DesignationRepository;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class DesignationService extends Service
{
    public function __construct(private readonly DesignationRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Get designation list
     */
    public function getDesignations(Request $request): ServiceResult
    {
        try {
            $designations = $this->repository->getDesignationsWithFilter($request->get('filter'));
            $this->result->setData($designations);
        } catch (Exception $exception) {
            $message = 'Failed to fetch designations';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Create New Designation
     */
    public function createDesignation(array $data): ServiceResult
    {
        try {
            $designation = $this->repository->createDesignation($data);
            $this->result->setData($designation);
        } catch (Exception $exception) {
            $message = 'Designations creation Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Update Designation info
     */
    public function updateDesignation(Designation $designation, array $data): ServiceResult
    {
        try {
            $designation = $this->repository->updateDesignation($designation, $data);
            $this->result->setData($designation);
        } catch (Exception $exception) {
            $message = 'Designations update Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Soft delete a Designation
     */
    public function deleteDesignation(Designation $designation): ServiceResult
    {
        try {
            $this->repository->deleteDesignation($designation);
            $this->result->setDeleted();
        } catch (Exception $exception) {
            $message = 'Designations deletion Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Get a Designation's posts.
     */
    public function getUsersByDesignation(Designation $designation): ServiceResult
    {
        try {
            $user = $this->repository->getUsersByDesignationWithPagination($designation, 10);
            $this->result->setData($user);
        } catch (Exception $exception) {
            $message = "Designation's post fetching failed";

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }
}
